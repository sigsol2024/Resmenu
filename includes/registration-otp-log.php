<?php
/**
 * Structured, low-PII logging for registration OTP abuse blocks.
 */

require_once __DIR__ . '/auth.php';

function registerOtpLogBlocked(string $reason, string $email): void {
    $norm = strtolower(trim($email));
    $toHash = $norm !== '' ? substr(hash('sha256', $norm), 0, 16) : '0';
    $ip = getClientIpAddress();
    $ipHash = substr(hash('sha256', (string)$ip), 0, 16);
    $reasonSafe = preg_replace('/[^a-z0-9_\-]/', '', strtolower($reason));
    error_log('register_otp_blocked reason=' . $reasonSafe . ' to_hash=' . $toHash . ' ip_hash=' . $ipHash);
}
