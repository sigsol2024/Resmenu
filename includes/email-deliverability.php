<?php
/**
 * Registration OTP: recipient domain mail-host checks (MX / A).
 * Distinguishes permanent "no mail path" from transient resolver failures.
 *
 * RFC 7505 Null MX: MX with exchange "." means the domain explicitly does not accept mail.
 * Must not treat as deliverable or fall through to A-record mail heuristics.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/rate-limit.php';

/**
 * @return array{state:string} state one of: ok, permanent_bad, transient_unavailable
 */
function registrationOtpMxEvaluate(string $email): array {
    $email = trim($email);
    if ($email === '' || strpos($email, '@') === false) {
        return ['state' => 'permanent_bad'];
    }
    $domain = strtolower(trim(substr(strrchr($email, '@'), 1)));
    if ($domain === '' || !preg_match('/^[a-z0-9]([a-z0-9-]*[a-z0-9])?(\.[a-z0-9]([a-z0-9-]*[a-z0-9])?)+$/i', $domain)) {
        return ['state' => 'permanent_bad'];
    }

    $cacheDir = getRateLimitDir();
    $cacheFile = null;
    if ($cacheDir) {
        $cacheDir = rtrim($cacheDir, "\\/") . DIRECTORY_SEPARATOR . 'mx-reg-cache';
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0755, true);
        }
        if (is_dir($cacheDir)) {
            $cacheFile = $cacheDir . DIRECTORY_SEPARATOR . hash('sha256', $domain) . '.json';
            if (is_readable($cacheFile)) {
                $raw = @file_get_contents($cacheFile);
                $j = is_string($raw) ? json_decode($raw, true) : null;
                if (is_array($j) && isset($j['state'], $j['exp']) && (int)$j['exp'] > time()) {
                    $st = (string)$j['state'];
                    if (in_array($st, ['ok', 'permanent_bad', 'transient_unavailable'], true)) {
                        return ['state' => $st];
                    }
                }
            }
        }
    }

    $result = registrationOtpMxProbeWithRetries($domain);

    if ($cacheFile && is_string($cacheFile) && is_dir(dirname($cacheFile))) {
        $ttl = $result['state'] === 'transient_unavailable' ? 60 : ($result['state'] === 'permanent_bad' ? 86400 : 3600);
        @file_put_contents(
            $cacheFile,
            json_encode(['state' => $result['state'], 'exp' => time() + $ttl]),
            LOCK_EX
        );
    }

    return $result;
}

/**
 * RFC 7505 null MX: exchange "." means the domain does not accept email.
 * Preference is normally 0; we key off the dot exchanger as the definitive signal.
 *
 * @param array<string, mixed> $rec One element from dns_get_record(..., DNS_MX)
 */
function registrationOtpMxRecordIsNullMx(array $rec): bool {
    $target = $rec['target'] ?? $rec['exchange'] ?? '';
    $target = strtolower(trim((string)$target));
    return $target === '.';
}

/**
 * @param list<array<string, mixed>> $mxRecords
 */
function registrationOtpMxHasDeliverableMx(array $mxRecords): bool {
    foreach ($mxRecords as $rec) {
        if (!is_array($rec)) {
            continue;
        }
        if (!registrationOtpMxRecordIsNullMx($rec)) {
            return true;
        }
    }
    return false;
}

/**
 * @return array{state:string}
 */
function registrationOtpMxProbeWithRetries(string $domain): array {
    $maxAttempts = 3;
    $pauseUs = 120000;

    $lastWasHardFalse = false;
    for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
        $mx = @dns_get_record($domain, DNS_MX);
        if ($mx === false) {
            $lastWasHardFalse = true;
            if ($attempt < $maxAttempts) {
                usleep($pauseUs);
            }
            continue;
        }
        $lastWasHardFalse = false;
        if (is_array($mx) && count($mx) > 0) {
            if (registrationOtpMxHasDeliverableMx($mx)) {
                return ['state' => 'ok'];
            }
            // Only Null MX (RFC 7505) or no usable exchangers — do not use A fallback for mail.
            return ['state' => 'permanent_bad'];
        }
        // Empty MX list: try A for mail host
        $a = @dns_get_record($domain, DNS_A);
        if ($a === false) {
            if ($attempt < $maxAttempts) {
                usleep($pauseUs);
                continue;
            }
            return ['state' => 'transient_unavailable'];
        }
        if (is_array($a) && count($a) > 0) {
            return ['state' => 'ok'];
        }
        // No MX and no A: treat as permanent bad (unregistered / no mail path)
        return ['state' => 'permanent_bad'];
    }

    if ($lastWasHardFalse) {
        return ['state' => 'transient_unavailable'];
    }
    return ['state' => 'transient_unavailable'];
}
