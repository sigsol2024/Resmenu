<?php
/**
 * Update order status (manager)
 */

require_once __DIR__ . '/../includes/auth.php';
requireManager();

$orderId = (int) ($_POST['order_id'] ?? 0);
$status = trim($_POST['status'] ?? '');
$slug = trim($_POST['slug'] ?? $_GET['slug'] ?? '');

$allowed = ['pending', 'confirmed', 'on_hold', 'cancelled', 'completed'];
if (!$orderId || !in_array($status, $allowed)) {
    header('Location: /manager/orders.php' . ($slug ? '?slug=' . urlencode($slug) : ''));
    exit;
}

$restaurantId = getCurrentUserRestaurantId();
if (!$restaurantId) {
    header('Location: /admin/login.php');
    exit;
}

require_once __DIR__ . '/../includes/functions.php';
$pdo = getDBConnection();
if (!$pdo) {
    header('Location: /manager/orders.php' . ($slug ? '?slug=' . urlencode($slug) : '') . '&error=db');
    exit;
}

$stmt = $pdo->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ? AND restaurant_id = ?");
$stmt->execute([$status, $orderId, $restaurantId]);

header('Location: /manager/orders.php' . ($slug ? '?slug=' . urlencode($slug) : ''));
exit;
