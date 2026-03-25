<?php
/**
 * Google reCAPTCHA helpers (server-side verification).
 */

require_once __DIR__ . '/../config/config.php';

function recaptchaIsEnabled() {
    return !empty((string)RECAPTCHA_SITE_KEY) && !empty((string)RECAPTCHA_SECRET_KEY);
}

/**
 * Verify a reCAPTCHA token with Google.
 * Supports reCAPTCHA v2 checkbox (g-recaptcha-response) and v3 tokens.
 *
 * @param string $token
 * @param string $remoteIp
 * @return array ['success' => bool, 'error' => string|null, 'data' => array|null]
 */
function verifyRecaptchaToken($token, $remoteIp = '') {
    $token = trim((string)$token);
    if ($token === '') {
        return ['success' => false, 'error' => 'missing-token', 'data' => null];
    }
    if (!recaptchaIsEnabled()) {
        return ['success' => true, 'error' => null, 'data' => ['skipped' => true]];
    }

    $endpoint = 'https://www.google.com/recaptcha/api/siteverify';
    $postFields = [
        'secret' => (string)RECAPTCHA_SECRET_KEY,
        'response' => $token,
    ];
    $remoteIp = trim((string)$remoteIp);
    if ($remoteIp !== '') {
        $postFields['remoteip'] = $remoteIp;
    }

    $raw = null;
    // Prefer cURL when available
    if (function_exists('curl_init')) {
        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 8);
        $raw = curl_exec($ch);
        curl_close($ch);
    } else {
        $ctx = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => http_build_query($postFields),
                'timeout' => 8,
            ],
        ]);
        $raw = @file_get_contents($endpoint, false, $ctx);
    }

    if (!is_string($raw) || $raw === '') {
        return ['success' => false, 'error' => 'verify-failed', 'data' => null];
    }
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        return ['success' => false, 'error' => 'bad-response', 'data' => null];
    }
    if (!empty($data['success'])) {
        return ['success' => true, 'error' => null, 'data' => $data];
    }
    return ['success' => false, 'error' => 'invalid', 'data' => $data];
}

