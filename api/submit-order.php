<?php
/**
 * Submit Order API
 * Accepts POST with cart JSON and customer details, creates order
 * Returns JSON response
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/order-functions.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/subscription.php';
require_once __DIR__ . '/../includes/subscription-middleware.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$slug = trim($_POST['slug'] ?? $_GET['slug'] ?? '');
$cartJson = $_POST['cart_json'] ?? '';

if (empty($slug)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Restaurant slug required']);
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

$result = createOrder($restaurant['id'], $cart, [
    'customer_name' => trim($_POST['customer_name'] ?? ''),
    'customer_phone' => trim($_POST['customer_phone'] ?? ''),
    'customer_email' => trim($_POST['customer_email'] ?? ''),
    'delivery_address' => trim($_POST['delivery_address'] ?? ''),
], (float) ($_POST['delivery_fee'] ?? 0), (float) ($_POST['tax_rate'] ?? 0));

if ($result['success']) {
    $menuUrl = (defined('SITE_URL') ? rtrim(SITE_URL, '/') : '') . '/restaurant/' . $slug;
    echo json_encode([
        'success' => true,
        'order_id' => $result['order_id'],
        'redirect' => (defined('SITE_URL') ? rtrim(SITE_URL, '/') : '') . '/order-confirmation.php?slug=' . urlencode($slug) . '&order_id=' . (int)$result['order_id'],
    ]);
} else {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => implode(' ', $result['errors']),
        'errors' => $result['errors'],
    ]);
}
