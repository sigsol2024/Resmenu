<?php
/**
 * Disposable / throwaway email domains (blocklist file).
 * Data: Resmenu/data/disposable-email-domains.txt (one domain per line, from disposable-email-domains project).
 */

require_once __DIR__ . '/../config/config.php';

/**
 * @return array<string, true> domain => true
 */
function registrationOtpDisposableDomainSet(): array {
    static $set = null;
    if (is_array($set)) {
        return $set;
    }
    $set = [];
    $path = rtrim(BASE_PATH, "\\/") . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'disposable-email-domains.txt';
    if (!is_readable($path)) {
        return $set;
    }
    $fh = fopen($path, 'rb');
    if (!$fh) {
        return $set;
    }
    while (($line = fgets($fh)) !== false) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') {
            continue;
        }
        $line = strtolower($line);
        if (preg_match('/^[a-z0-9.-]+$/', $line)) {
            $set[$line] = true;
        }
    }
    fclose($fh);
    return $set;
}

function registrationOtpIsDisposableDomain(string $domain): bool {
    $domain = strtolower(trim($domain));
    if ($domain === '') {
        return false;
    }
    $set = registrationOtpDisposableDomainSet();
    return isset($set[$domain]);
}

/**
 * Optional heuristic: very long local part with mostly digits (bots).
 */
function registrationOtpLocalPartSuspicious(string $email): bool {
    if (!defined('REG_OTP_STRICT_LOCAL_PART') || !REG_OTP_STRICT_LOCAL_PART) {
        return false;
    }
    $at = strpos($email, '@');
    if ($at === false || $at < 1) {
        return false;
    }
    $local = substr($email, 0, $at);
    if (strlen($local) < 20) {
        return false;
    }
    $digits = preg_match_all('/\d/', $local);
    return ($digits / strlen($local)) >= 0.75;
}
