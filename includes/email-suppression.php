<?php
/**
 * Persistent email suppression for registration OTP (hard bounces, complaints).
 * Requires migration: database/migration_registration_email_suppression.sql
 */

require_once __DIR__ . '/../config/config.php';

/**
 * Normalize email for hashing (lowercase, trim).
 */
function registrationOtpEmailNormalized(string $email): string {
    return strtolower(trim($email));
}

/**
 * SHA-256 of normalized email (64 hex chars).
 */
function registrationOtpEmailSha256(string $email): string {
    return hash('sha256', registrationOtpEmailNormalized($email));
}

/**
 * True if this address must not receive registration OTP (suppressed).
 * If the table is missing, returns false so registration keeps working until migration is applied.
 */
function registrationOtpIsSuppressed(?PDO $pdo, string $email): bool {
    if (!$pdo || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    $hash = registrationOtpEmailSha256($email);
    try {
        $stmt = $pdo->prepare('SELECT 1 FROM email_delivery_suppressions WHERE email_sha256 = ? LIMIT 1');
        $stmt->execute([$hash]);
        return (bool)$stmt->fetchColumn();
    } catch (Throwable $e) {
        error_log('registrationOtpIsSuppressed: ' . $e->getMessage());
        return false;
    }
}

/**
 * Add suppression row (idempotent on duplicate email hash).
 */
function registrationOtpAddSuppression(PDO $pdo, string $email, string $reason = 'hard_bounce', string $source = 'manual'): bool {
    $email = trim($email);
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    $hash = registrationOtpEmailSha256($email);
    $reason = preg_replace('/[^a-z0-9_\-]/i', '', $reason) ?: 'hard_bounce';
    $source = preg_replace('/[^a-z0-9_\-]/i', '', $source) ?: 'manual';
    try {
        $stmt = $pdo->prepare(
            'INSERT INTO email_delivery_suppressions (email_sha256, reason, source) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE reason = ?, source = ?'
        );
        $stmt->execute([$hash, substr($reason, 0, 64), substr($source, 0, 64), substr($reason, 0, 64), substr($source, 0, 64)]);
        return true;
    } catch (Throwable $e) {
        error_log('registrationOtpAddSuppression: ' . $e->getMessage());
        return false;
    }
}
