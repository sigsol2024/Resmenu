<?php
/**
 * Send security-related HTTP headers for all responses.
 * Include this once per request (e.g. from config.php).
 * Only sends headers if they have not already been sent.
 */
if (headers_sent()) {
    return;
}
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}
