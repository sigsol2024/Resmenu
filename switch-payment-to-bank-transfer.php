<?php
/**
 * Switch Payment to Bank Transfer
 * For pending Paystack/Flutterwave orders when customer cancels online payment.
 * Updates the order to bank_transfer and redirects to order confirmation.
 */

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/restaurant-payment-functions.php';
require_once __DIR__ . '/config/config.php';

$slug = trim($_GET['slug'] ?? '');
$orderId = (int)($_GET['order_id'] ?? 0);
$baseUrl = defined('SITE_URL') ? rtrim(SITE_URL, '/') : '';

if (empty($slug) || !$orderId) {
    header('Location: ' . $baseUrl . '/payment-failed.php?reason=error');
    exit;
}

$restaurant = getRestaurantBySlug($slug);
if (!$restaurant) {
    header('Location: ' . $baseUrl . '/payment-failed.php?reason=error');
    exit;
}

$pdo = getDBConnection();
if (!$pdo) {
    header('Location: ' . $baseUrl . '/payment-failed.php?slug=' . urlencode($slug) . '&order_id=' . $orderId . '&reason=error');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND restaurant_id = ?");
$stmt->execute([$orderId, $restaurant['id']]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order || ($order['status'] ?? '') !== 'pending') {
    header('Location: ' . $baseUrl . '/payment-failed.php?slug=' . urlencode($slug) . '&order_id=' . $orderId . '&reason=error');
    exit;
}

$currentMethod = $order['payment_method'] ?? '';
if (!in_array($currentMethod, ['paystack', 'flutterwave'])) {
    header('Location: ' . $baseUrl . '/payment-failed.php?slug=' . urlencode($slug) . '&order_id=' . $orderId . '&reason=error');
    exit;
}

$bankTransferSettings = getRestaurantPaymentSettings($restaurant['id'], 'bank_transfer');
if (empty($bankTransferSettings['is_active']) || empty($bankTransferSettings['account_number'])) {
    header('Location: ' . $baseUrl . '/payment-failed.php?slug=' . urlencode($slug) . '&order_id=' . $orderId . '&reason=init_failed');
    exit;
}

$stmt = $pdo->prepare("UPDATE orders SET payment_method = 'bank_transfer', updated_at = NOW() WHERE id = ? AND restaurant_id = ? AND status = 'pending'");
$stmt->execute([$orderId, $restaurant['id']]);

setcookie('resmenu_pending', '', time() - 3600, '/');
header('Location: ' . $baseUrl . '/order-confirmation.php?slug=' . urlencode($slug) . '&order_id=' . $orderId);
exit;
