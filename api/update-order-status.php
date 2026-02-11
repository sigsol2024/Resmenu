<?php
/**
 * Update order status (manager)
 */

require_once __DIR__ . '/../includes/auth.php';
requireManager();

$orderId = (int) ($_POST['order_id'] ?? 0);
$status = trim($_POST['status'] ?? '');
$slug = trim($_POST['slug'] ?? $_GET['slug'] ?? '');
$returnTo = trim($_POST['return_to'] ?? $_GET['return_to'] ?? 'orders');

$allowed = ['pending', 'confirmed', 'on_hold', 'cancelled', 'completed'];
$slugPart = $slug ? '?slug=' . urlencode($slug) : '';
$defaultRedirect = '/manager/orders.php' . $slugPart;

if (!$orderId || !in_array($status, $allowed)) {
    if ($returnTo === 'restaurant-orders') {
        header('Location: /manager/restaurant-orders.php' . $slugPart);
    } else {
        header('Location: ' . $defaultRedirect);
    }
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
    header('Location: ' . ($returnTo === 'restaurant-orders' ? '/manager/restaurant-orders.php' . $slugPart : $defaultRedirect) . '&error=db');
    exit;
}

$stmt = $pdo->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ? AND restaurant_id = ?");
$stmt->execute([$status, $orderId, $restaurantId]);

if ($returnTo === 'restaurant-orders') {
    header('Location: /manager/restaurant-orders.php' . $slugPart);
} else {
    header('Location: ' . $defaultRedirect);
}
exit;
