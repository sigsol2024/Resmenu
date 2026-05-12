<?php
/**
 * Submit Order API
 * Accepts POST with cart JSON and customer details, creates order
 * Returns JSON response
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/order-functions.php';
require_once __DIR__ . '/../includes/order-cancel-token.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/public-api-rate-limit.php';
require_once __DIR__ . '/../includes/subscription.php';
require_once __DIR__ . '/../includes/subscription-middleware.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$slugRaw = trim((string) ($_POST['slug'] ?? $_GET['slug'] ?? ''));
$slug = sanitizeSlug($slugRaw, 128);
$cartJson = $_POST['cart_json'] ?? '';

if ($slug === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Restaurant slug required']);
    exit;
}

// Limit cart_json size to prevent oversized payloads (e.g. 256KB)
if (strlen($cartJson) > 262144) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Cart data too large']);
    exit;
}

$restaurant = getRestaurantBySlug($slug);
if (!$restaurant) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Restaurant not found']);
    exit;
}

// Subscription + feature gating: block order placement if subscription is invalid OR plan doesn't include ordering.
$restaurantId = (int)$restaurant['id'];
$subscriptionAccess = checkSubscriptionAccess($restaurantId);
if (!$subscriptionAccess['valid']) {
    http_response_code(402);
    echo json_encode(['success' => false, 'message' => $subscriptionAccess['message'] ?: 'Subscription required']);
    exit;
}
if (!hasFeatureAccess($restaurantId, 'food_ordering')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Food ordering is not available for this restaurant plan.']);
    exit;
}

$cart = [];
if (!empty($cartJson)) {
    $decoded = json_decode($cartJson, true);
    if (is_array($decoded)) $cart = $decoded;
}

if (empty($cart)) {
    echo json_encode(['success' => false, 'message' => 'Cart is empty']);
    exit;
}

$pdo = getDBConnection();
if ($pdo && isPublicApiRateLimited($pdo, 'submit_order', getClientIpAddress(), 40, 300)) {
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'Too many requests. Please try again shortly.']);
    exit;
}

$customerName = sanitizeForHtml($_POST['customer_name'] ?? '', 200);
$customerPhone = trim((string) ($_POST['customer_phone'] ?? ''));
$customerPhone = preg_replace('/[^0-9+\s-]/', '', $customerPhone);
$customerPhone = mb_substr($customerPhone, 0, 20, 'UTF-8');
$customerEmailRaw = trim((string) ($_POST['customer_email'] ?? ''));
$customerEmail = sanitizeEmail($customerEmailRaw) ?? '';
$deliveryAddress = sanitizeForHtml($_POST['delivery_address'] ?? '', 500);

// Delivery/tax are not customer-controlled; enforce server defaults until real pricing exists in DB/UI.
$deliveryFeeServer = 0.0;
$taxRateServer = 0.0;

$result = createOrder($restaurant['id'], $cart, [
    'customer_name' => $customerName,
    'customer_phone' => $customerPhone,
    'customer_email' => $customerEmail,
    'delivery_address' => $deliveryAddress,
], $deliveryFeeServer, $taxRateServer);

if ($result['success']) {
    $orderId = (int)$result['order_id'];
    $cancelTok = buildPendingOrderCancelToken($orderId, $slug, 900);
    $payload = [
        'success' => true,
        'order_id' => $orderId,
        'redirect' => (defined('SITE_URL') ? rtrim(SITE_URL, '/') : '') . '/order-confirmation.php?slug=' . urlencode($slug) . '&order_id=' . $orderId,
    ];
    if ($cancelTok) {
        $payload['cancel_order'] = [
            'exp' => $cancelTok['exp'],
            'sig' => $cancelTok['sig'],
        ];
    }
    echo json_encode($payload);
} else {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => implode(' ', $result['errors']),
        'errors' => $result['errors'],
    ]);
}
