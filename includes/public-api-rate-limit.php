<?php
/**
 * Lightweight DB-backed rate limiting for unauthenticated public APIs.
 * Requires migration_public_api_rate_events.sql applied.
 */

/**
 * @param PDO $pdo
 * @param string $action e.g. submit_order, cancel_order
 * @param string $ip from getClientIpAddress()
 * @param int $maxAttempts max requests allowed in the window (including this one after insert)
 * @param int $windowSeconds sliding window length
 * @return bool true if request should be blocked (rate limited)
 */
function isPublicApiRateLimited(PDO $pdo, $action, $ip, $maxAttempts = 40, $windowSeconds = 300) {
    $action = mb_substr(trim((string)$action), 0, 64, 'UTF-8');
    $ip = mb_substr(trim((string)$ip), 0, 45, 'UTF-8');
    if ($action === '' || $ip === '') {
        return false;
    }
    $maxAttempts = max(1, (int)$maxAttempts);
    $windowSeconds = max(30, min(3600, (int)$windowSeconds));

    try {
        $sql = "SELECT COUNT(*) FROM public_api_rate_events
            WHERE action = ?
            AND ip_address = ?
            AND created_at >= (NOW() - INTERVAL " . $windowSeconds . " SECOND)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$action, $ip]);
        $count = (int)$stmt->fetchColumn();
        if ($count >= $maxAttempts) {
            return true;
        }
        $ins = $pdo->prepare("INSERT INTO public_api_rate_events (action, ip_address, created_at) VALUES (?, ?, NOW())");
        $ins->execute([$action, $ip]);
        // Best-effort trim to limit table growth
        $pdo->exec("DELETE FROM public_api_rate_events WHERE created_at < (NOW() - INTERVAL 2 HOUR) LIMIT 50000");
    } catch (Throwable $e) {
        error_log('public_api_rate_events: ' . $e->getMessage());
        return false;
    }
    return false;
}
