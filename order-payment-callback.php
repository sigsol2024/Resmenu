<?php
/**
 * Order Payment Callback
 * Handles redirect from Paystack/Flutterwave after customer completes or cancels payment.
 * Order is created ONLY when payment succeeds. On failure/cancel, no order is recorded.
 */

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/restaurant-payment-functions.php';
require_once __DIR__ . '/includes/order-functions.php';
require_once __DIR__ . '/config/config.php';

$gateway = trim($_GET['gateway'] ?? '');
$baseUrl = defined('SITE_URL') ? rtrim(SITE_URL, '/') : '';
$slug = trim($_GET['slug'] ?? '');

if (!in_array($gateway, ['paystack', 'flutterwave'])) {
    header('Location: ' . $baseUrl . '/payment-failed.php?slug=' . urlencode($slug) . '&reason=invalid');
    exit;
}

if ($gateway === 'paystack') {
    $reference = trim($_GET['reference'] ?? '');

    if (empty($reference)) {
        header('Location: ' . $baseUrl . '/payment-failed.php?slug=' . urlencode($slug) . '&reason=cancelled');
        exit;
    }

    $pdo = getDBConnection();
    if (!$pdo) {
        header('Location: ' . $baseUrl . '/payment-failed.php?slug=' . urlencode($slug) . '&reason=error');
        exit;
    }

    $stmt = $pdo->prepare("SELECT restaurant_id FROM pending_online_payments WHERE reference = ? AND gateway = 'paystack'");
    $stmt->execute([$reference]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        header('Location: ' . $baseUrl . '/payment-failed.php?slug=' . urlencode($slug) . '&reason=error');
        exit;
    }

    $restaurantId = (int)$row['restaurant_id'];
    $verify = verifyRestaurantPaystackPayment($restaurantId, $reference);

    if ($verify && $verify['success']) {
        $result = createOrderFromPendingOnlinePayment($reference, 'paystack');
        if ($result['success']) {
            header('Location: ' . $baseUrl . '/order-confirmation.php?slug=' . urlencode($result['slug']) . '&order_id=' . (int)$result['order_id']);
            exit;
        }
    }

    header('Location: ' . $baseUrl . '/payment-failed.php?slug=' . urlencode($slug) . '&reason=failed');
    exit;
}

if ($gateway === 'flutterwave') {
    $status = trim($_GET['status'] ?? '');
    $txRef = trim($_GET['tx_ref'] ?? '');
    $transactionId = trim($_GET['transaction_id'] ?? '');

    if (empty($txRef) || $status !== 'successful') {
        header('Location: ' . $baseUrl . '/payment-failed.php?slug=' . urlencode($slug) . '&reason=' . (empty($status) ? 'cancelled' : 'failed'));
        exit;
    }

    $pdo = getDBConnection();
    if (!$pdo) {
        header('Location: ' . $baseUrl . '/payment-failed.php?slug=' . urlencode($slug) . '&reason=error');
        exit;
    }

    $stmt = $pdo->prepare("SELECT restaurant_id FROM pending_online_payments WHERE reference = ? AND gateway = 'flutterwave'");
    $stmt->execute([$txRef]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        header('Location: ' . $baseUrl . '/payment-failed.php?slug=' . urlencode($slug) . '&reason=error');
        exit;
    }

    $restaurantId = (int)$row['restaurant_id'];
    $verify = verifyRestaurantFlutterwavePayment($restaurantId, $transactionId);

    if ($verify && $verify['success']) {
        $result = createOrderFromPendingOnlinePayment($txRef, 'flutterwave');
        if ($result['success']) {
            header('Location: ' . $baseUrl . '/order-confirmation.php?slug=' . urlencode($result['slug']) . '&order_id=' . (int)$result['order_id']);
            exit;
        }
    }

    header('Location: ' . $baseUrl . '/payment-failed.php?slug=' . urlencode($slug) . '&reason=failed');
    exit;
}

header('Location: ' . $baseUrl . '/payment-failed.php?slug=' . urlencode($slug) . '&reason=error');
exit;
