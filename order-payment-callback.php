<?php
/**
 * Order Payment Callback
 * Handles redirect from Paystack/Flutterwave after customer completes or cancels payment.
 * Verifies payment and redirects to order-confirmation (success) or payment-failed (failure).
 */

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/restaurant-payment-functions.php';
require_once __DIR__ . '/config/config.php';

$gateway = trim($_GET['gateway'] ?? '');
$baseUrl = defined('SITE_URL') ? rtrim(SITE_URL, '/') : '';
$menuUrl = $baseUrl . '/';

if (!in_array($gateway, ['paystack', 'flutterwave'])) {
    header('Location: ' . $baseUrl . '/payment-failed.php?reason=invalid');
    exit;
}

$slug = '';
$orderId = 0;
$restaurantId = 0;

if ($gateway === 'paystack') {
    $reference = trim($_GET['reference'] ?? '');
    $slug = trim($_GET['slug'] ?? '');
    $orderId = (int)($_GET['order_id'] ?? 0);

    if (empty($reference)) {
        header('Location: ' . $baseUrl . '/payment-failed.php?slug=' . urlencode($slug) . '&order_id=' . $orderId . '&reason=cancelled');
        exit;
    }
    // Verify with Paystack - we need restaurant_id from somewhere; Paystack callback doesn't return it in URL
    // We must have passed it in metadata; verification returns metadata
    $pdo = getDBConnection();
    if (!$pdo) {
        header('Location: ' . $baseUrl . '/payment-failed.php?reason=error');
        exit;
    }
    if (!$orderId && preg_match('/^ORD_(\d+)_/', $reference, $m)) {
        $orderId = (int)$m[1];
    }
    if (!$orderId) {
        header('Location: ' . $baseUrl . '/payment-failed.php?reason=error');
        exit;
    }
    $stmt = $pdo->prepare("SELECT restaurant_id FROM orders WHERE id = ? AND payment_method = 'paystack'");
    $stmt->execute([$orderId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        header('Location: ' . $baseUrl . '/payment-failed.php?reason=error');
        exit;
    }
    $restaurantId = (int)$row['restaurant_id'];
    if (empty($slug)) {
        $restaurant = getRestaurant($restaurantId);
        $slug = $restaurant['slug'] ?? '';
    }

    $verify = verifyRestaurantPaystackPayment($restaurantId, $reference);
    if ($verify['success']) {
        $meta = $verify['metadata'] ?? [];
        $slug = $meta['slug'] ?? $slug;
        header('Location: ' . $baseUrl . '/order-confirmation.php?slug=' . urlencode($slug) . '&order_id=' . $orderId);
        exit;
    }
    header('Location: ' . $baseUrl . '/payment-failed.php?slug=' . urlencode($slug) . '&order_id=' . $orderId . '&reason=failed');
    exit;
}

if ($gateway === 'flutterwave') {
    $status = trim($_GET['status'] ?? '');
    $txRef = trim($_GET['tx_ref'] ?? '');
    $transactionId = trim($_GET['transaction_id'] ?? '');
    $slug = trim($_GET['slug'] ?? '');
    $orderId = (int)($_GET['order_id'] ?? 0);

    if (empty($txRef) || $status !== 'successful') {
        if (!$orderId && preg_match('/^ORD_(\d+)_/', $txRef, $m)) {
            $orderId = (int)$m[1];
        }
        if ($orderId && empty($slug)) {
            $pdo = getDBConnection();
            if ($pdo) {
                $stmt = $pdo->prepare("SELECT r.slug FROM orders o JOIN restaurants r ON r.id = o.restaurant_id WHERE o.id = ?");
                $stmt->execute([$orderId]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($row) $slug = $row['slug'];
            }
        }
        header('Location: ' . $baseUrl . '/payment-failed.php?slug=' . urlencode($slug) . '&order_id=' . $orderId . '&reason=' . (empty($status) ? 'cancelled' : 'failed'));
        exit;
    }

    if (!$orderId && preg_match('/^ORD_(\d+)_/', $txRef, $m)) {
        $orderId = (int)$m[1];
    }
    if (!$orderId) {
        header('Location: ' . $baseUrl . '/payment-failed.php?reason=error');
        exit;
    }

    $pdo = getDBConnection();
    if (!$pdo) {
        header('Location: ' . $baseUrl . '/payment-failed.php?reason=error');
        exit;
    }
    $stmt = $pdo->prepare("SELECT restaurant_id FROM orders WHERE id = ? AND payment_method = 'flutterwave'");
    $stmt->execute([$orderId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        header('Location: ' . $baseUrl . '/payment-failed.php?reason=error');
        exit;
    }
    $restaurantId = (int)$row['restaurant_id'];
    if (empty($slug)) {
        $restaurant = getRestaurant($restaurantId);
        $slug = $restaurant['slug'] ?? '';
    }

    $verify = verifyRestaurantFlutterwavePayment($restaurantId, $transactionId);
    if ($verify['success']) {
        $meta = $verify['metadata'] ?? [];
        $slug = $meta['slug'] ?? $slug;
        header('Location: ' . $baseUrl . '/order-confirmation.php?slug=' . urlencode($slug) . '&order_id=' . $orderId);
        exit;
    }
    header('Location: ' . $baseUrl . '/payment-failed.php?slug=' . urlencode($slug) . '&order_id=' . $orderId . '&reason=failed');
    exit;
}

header('Location: ' . $baseUrl . '/payment-failed.php?reason=error');
exit;
