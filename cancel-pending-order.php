<?php
/**
 * Cancel Pending Order
 * Cancels a pending Paystack/Flutterwave order and redirects to menu or checkout.
 */

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/config.php';

$slug = trim($_GET['slug'] ?? '');
$orderId = (int)($_GET['order_id'] ?? 0);
$returnTo = trim($_GET['return'] ?? 'menu');
$baseUrl = defined('SITE_URL') ? rtrim(SITE_URL, '/') : '';

$redirectUrl = !empty($slug) ? ($baseUrl . '/restaurant/' . $slug) : $baseUrl . '/';
if ($returnTo === 'checkout' && !empty($slug)) {
    $redirectUrl = $baseUrl . '/checkout.php?slug=' . urlencode($slug);
}

if (!empty($slug) && $orderId) {
    $restaurant = getRestaurantBySlug($slug);
    if ($restaurant) {
        $pdo = getDBConnection();
        if ($pdo) {
            $stmt = $pdo->prepare("UPDATE orders SET status = 'cancelled', updated_at = NOW() WHERE id = ? AND restaurant_id = ? AND status = 'pending'");
            $stmt->execute([$orderId, $restaurant['id']]);
        }
    }
}

setcookie('resmenu_pending', '', time() - 3600, '/');
header('Location: ' . $redirectUrl);
exit;
