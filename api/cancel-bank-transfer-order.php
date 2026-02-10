<?php
/**
 * Cancel bank transfer order when payment window expires (customer-facing, no auth)
 * Called from order-confirmation page when 15-min countdown hits zero.
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/functions.php';

$orderId = (int)($_POST['order_id'] ?? $_GET['order_id'] ?? 0);
$slug = trim($_POST['slug'] ?? $_GET['slug'] ?? '');

if (!$orderId || !$slug) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

$restaurant = getRestaurantBySlug($slug);
if (!$restaurant) {
    echo json_encode(['success' => false, 'message' => 'Restaurant not found']);
    exit;
}

$pdo = getDBConnection();
if (!$pdo) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
}

$stmt = $pdo->prepare("SELECT id, payment_method, status FROM orders WHERE id = ? AND restaurant_id = ?");
$stmt->execute([$orderId, $restaurant['id']]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    echo json_encode(['success' => false, 'message' => 'Order not found']);
    exit;
}

// Only allow cancelling bank_transfer orders that are still pending
if (($order['payment_method'] ?? '') !== 'bank_transfer') {
    echo json_encode(['success' => false, 'message' => 'Invalid order type']);
    exit;
}

if (($order['status'] ?? '') !== 'pending') {
    echo json_encode(['success' => true, 'message' => 'Order already updated']);
    exit;
}

$stmt = $pdo->prepare("UPDATE orders SET status = 'cancelled', updated_at = NOW() WHERE id = ? AND restaurant_id = ?");
$stmt->execute([$orderId, $restaurant['id']]);

echo json_encode(['success' => true, 'message' => 'Order cancelled']);
exit;
