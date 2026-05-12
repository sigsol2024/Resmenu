<?php
/**
 * Optional webhook to record hard bounces / complaints for registration OTP suppression.
 * Configure REG_OTP_BOUNCE_WEBHOOK_SECRET in config.local.php or env.
 * POST JSON: {"email":"user@domain.com","reason":"hard_bounce"}
 * Header: Authorization: Bearer <secret> OR X-Webhook-Secret: <secret>
 *
 * Ops: On some Apache/CGI setups the Authorization header is stripped. Use X-Webhook-Secret,
 * or rewrite to REDIRECT_HTTP_AUTHORIZATION in server config. Test this endpoint after deploy.
 */

header('Content-Type: application/json; charset=utf-8');

if (!defined('SITE_URL')) {
    require_once __DIR__ . '/../config/config.php';
}

$secret = defined('REG_OTP_BOUNCE_WEBHOOK_SECRET') ? trim((string)REG_OTP_BOUNCE_WEBHOOK_SECRET) : '';
if ($secret === '') {
    http_response_code(503);
    echo json_encode(['ok' => false, 'error' => 'webhook_not_configured']);
    exit;
}

$auth = '';
$authHeader = trim((string)($_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? ''));
if ($authHeader !== '' && stripos($authHeader, 'Bearer ') === 0) {
    $auth = trim(substr($authHeader, 7));
}
if ($auth === '' && !empty($_SERVER['HTTP_X_WEBHOOK_SECRET'])) {
    $auth = trim((string)$_SERVER['HTTP_X_WEBHOOK_SECRET']);
}

if ($auth === '' || !hash_equals($secret, $auth)) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'method_not_allowed']);
    exit;
}

$raw = file_get_contents('php://input');
$data = is_string($raw) ? json_decode($raw, true) : null;
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'invalid_json']);
    exit;
}

$email = trim((string)($data['email'] ?? ''));
$reason = trim((string)($data['reason'] ?? 'hard_bounce'));
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'invalid_email']);
    exit;
}

require_once __DIR__ . '/../includes/email-suppression.php';

$pdo = getDBConnection();
if (!$pdo) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'database_unavailable']);
    exit;
}

$ok = registrationOtpAddSuppression($pdo, $email, $reason !== '' ? $reason : 'hard_bounce', 'webhook');
http_response_code($ok ? 200 : 500);
echo json_encode(['ok' => (bool)$ok]);
