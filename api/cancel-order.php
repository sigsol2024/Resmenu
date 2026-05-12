<?php
/**
 * Cancel pending order (e.g. abandoned online payment flow).
 * Requires stateless HMAC (order_id|slug|exp) + short expiry. POST preferred; GET with token only for compatibility.
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/order-cancel-token.php';
require_once __DIR__ . '/../includes/public-api-rate-limit.php';
require_once __DIR__ . '/../includes/order-functions.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$src = $method === 'POST' ? $_POST : $_GET;

if ($method !== 'POST' && $method !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if ($method === 'GET' && empty($src['sig'])) {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Use POST for this action']);
    exit;
}

$orderId = (int)($src['order_id'] ?? 0);
$slug = trim((string)($src['slug'] ?? ''));
$exp = (int)($src['exp'] ?? 0);
$sig = trim((string)($src['sig'] ?? ''));

if (!$orderId || $slug === '' || !$exp || $sig === '') {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

$pdo = getDBConnection();
if (!$pdo) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
}

if (isPublicApiRateLimited($pdo, 'cancel_order', getClientIpAddress(), 30, 600)) {
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'Too many requests. Please try again shortly.']);
    exit;
}

if (!defined('APP_HMAC_SECRET') || (string)APP_HMAC_SECRET === '') {
    http_response_code(503);
    echo json_encode(['success' => false, 'message' => 'Cancel is temporarily unavailable']);
    error_log('cancel-order: APP_HMAC_SECRET not configured');
    exit;
}

if (!verifyPendingOrderCancel($orderId, $slug, $exp, $sig)) {
    error_log('cancel-order: invalid or expired token order_id=' . $orderId . ' slug=' . $slug);
    echo json_encode(['success' => false, 'message' => 'Invalid or expired token']);
    exit;
}

$restaurant = getRestaurantBySlug($slug);
if (!$restaurant) {
    echo json_encode(['success' => false, 'message' => 'Restaurant not found']);
    exit;
}

$stmt = $pdo->prepare("SELECT id, payment_method, status FROM orders WHERE id = ? AND restaurant_id = ?");
$stmt->execute([$orderId, $restaurant['id']]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    echo json_encode(['success' => false, 'message' => 'Order not found']);
    exit;
}

if (($order['status'] ?? '') !== 'pending') {
    echo json_encode(['success' => true, 'message' => 'Order already updated']);
    exit;
}

$stmt = $pdo->prepare("UPDATE orders SET status = 'cancelled', updated_at = NOW() WHERE id = ? AND restaurant_id = ?");
$stmt->execute([$orderId, $restaurant['id']]);

try {
    sendOrderStatusChangeEmail($orderId, $restaurant['id'], 'cancelled');
} catch (Exception $e) {
    error_log("Order cancel email failed: " . $e->getMessage());
}

echo json_encode(['success' => true, 'message' => 'Order cancelled']);
exit;
