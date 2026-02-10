<?php
/**
 * Get order details for manager view
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/auth.php';
requireManager();

$orderId = (int) ($_GET['order_id'] ?? 0);
$slug = trim($_GET['slug'] ?? '');

if (!$orderId) {
    echo json_encode(['success' => false, 'message' => 'Order ID required']);
    exit;
}

$restaurantId = getCurrentUserRestaurantId();
if (!$restaurantId) {
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/order-functions.php';
$pdo = getDBConnection();
if (!$pdo) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND restaurant_id = ?");
$stmt->execute([$orderId, $restaurantId]);
$order = $stmt->fetch();

if (!$order) {
    echo json_encode(['success' => false, 'message' => 'Order not found']);
    exit;
}

$stmt = $pdo->prepare("SELECT name, price, quantity FROM order_items WHERE order_id = ?");
$stmt->execute([$orderId]);
$order['items'] = $stmt->fetchAll();
$order['order_display_number'] = getOrderDisplayNumber($order);

echo json_encode(['success' => true, 'order' => $order]);
