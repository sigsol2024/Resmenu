<?php
/**
 * Confirm Bank Transfer
 * Creates order when user clicks "I have made this payment" - only then is the order recorded.
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/order-functions.php';
require_once __DIR__ . '/../includes/restaurant-payment-functions.php';
require_once __DIR__ . '/../config/config.php';

$token = trim($_POST['token'] ?? '');
$baseUrl = defined('SITE_URL') ? rtrim(SITE_URL, '/') : '';

if (empty($token)) {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

$pdo = getDBConnection();
if (!$pdo) {
    echo json_encode(['success' => false, 'message' => 'Server error. Please try again.']);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM pending_bank_transfers WHERE token = ?");
$stmt->execute([$token]);
$draft = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$draft) {
    echo json_encode(['success' => false, 'message' => 'Session expired. Please place a new order.']);
    exit;
}

// Check if within 15 minutes
$createdAt = strtotime($draft['created_at']);
if (time() - $createdAt > 15 * 60) {
    $pdo->prepare("DELETE FROM pending_bank_transfers WHERE token = ?")->execute([$token]);
    echo json_encode(['success' => false, 'message' => 'Payment window has expired. Please place a new order.']);
    exit;
}

$paymentType = $draft['payment_type'] ?? 'order';
$reservationId = isset($draft['reservation_id']) ? (int)$draft['reservation_id'] : 0;

if ($paymentType === 'reservation' && $reservationId > 0) {
    $pdo->prepare("UPDATE table_reservations SET deposit_paid = 1, status = 'confirmed', updated_at = NOW() WHERE id = ? AND restaurant_id = ?")->execute([$reservationId, $draft['restaurant_id']]);
    $pdo->prepare("DELETE FROM pending_bank_transfers WHERE token = ?")->execute([$token]);
    try {
        sendReservationDepositPaidEmail($reservationId, $draft['restaurant_id']);
    } catch (Exception $e) {
        error_log("Reservation deposit email failed: " . $e->getMessage());
    }
    $restaurant = getRestaurant($draft['restaurant_id']);
    $slug = $restaurant['slug'] ?? '';
    echo json_encode([
        'success' => true,
        'redirect' => $baseUrl . '/reservation-confirmation.php?slug=' . urlencode($slug) . '&reservation_id=' . $reservationId
    ]);
    exit;
}

$cart = json_decode($draft['cart_json'] ?? '[]', true);
if (!is_array($cart)) $cart = [];

$subtotal = (float)$draft['subtotal'];
$taxRate = $subtotal > 0 ? (float)$draft['tax'] / $subtotal : 0;

$result = createOrder($draft['restaurant_id'], $cart, [
    'customer_name' => $draft['customer_name'],
    'customer_phone' => $draft['customer_phone'],
    'customer_email' => $draft['customer_email'],
    'delivery_address' => $draft['delivery_address'],
    'payment_method' => 'bank_transfer',
], (float)$draft['delivery_fee'], $taxRate);

if (!$result['success']) {
    echo json_encode(['success' => false, 'message' => $result['errors'][0] ?? 'Failed to create order.']);
    exit;
}

$orderId = (int)$result['order_id'];
$restaurant = getRestaurant($draft['restaurant_id']);
$slug = $restaurant['slug'] ?? '';

$pdo->prepare("DELETE FROM pending_bank_transfers WHERE token = ?")->execute([$token]);

echo json_encode([
    'success' => true,
    'redirect' => $baseUrl . '/order-confirmation.php?slug=' . urlencode($slug) . '&order_id=' . $orderId
]);
