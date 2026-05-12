<?php
/**
 * Stateless HMAC for customer-facing cancel actions (pending orders / bank transfer).
 * Set APP_HMAC_SECRET in config.local.php or environment (see config.example.php).
 */

function pendingOrderCancelPayload(int $orderId, string $slug, int $exp): string {
    return (string)$orderId . '|' . $slug . '|' . (string)$exp;
}

function signPendingOrderCancel(int $orderId, string $slug, int $exp): string {
    if (!defined('APP_HMAC_SECRET') || (string)APP_HMAC_SECRET === '') {
        return '';
    }
    return hash_hmac('sha256', pendingOrderCancelPayload($orderId, $slug, $exp), (string)APP_HMAC_SECRET, false);
}

/**
 * @return array{exp:int, sig:string}|null null if secret not configured
 */
function buildPendingOrderCancelToken(int $orderId, string $slug, int $ttlSeconds = 900): ?array {
    $ttlSeconds = max(60, min(3600, $ttlSeconds));
    $exp = time() + $ttlSeconds;
    $sig = signPendingOrderCancel($orderId, $slug, $exp);
    if ($sig === '') {
        return null;
    }
    return ['exp' => $exp, 'sig' => $sig];
}

function verifyPendingOrderCancel(int $orderId, string $slug, int $exp, string $sig): bool {
    if ($orderId < 1 || $slug === '' || $exp < 1 || $sig === '') {
        return false;
    }
    if (time() > $exp) {
        return false;
    }
    $expected = signPendingOrderCancel($orderId, $slug, $exp);
    if ($expected === '') {
        return false;
    }
    return hash_equals($expected, $sig);
}
