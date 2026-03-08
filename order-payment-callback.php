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
if ($baseUrl === '') {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $baseUrl = $protocol . $host;
}
$slug = trim($_GET['slug'] ?? '');

if (!in_array($gateway, ['paystack', 'flutterwave'])) {
    header('Location: ' . $baseUrl . '/payment-failed.php?slug=' . urlencode($slug) . '&reason=invalid');
    exit;
}

if ($gateway === 'paystack') {
    $reference = trim($_GET['reference'] ?? $_GET['trxref'] ?? '');

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
    if ($slug === '') {
        $stmtSlug = $pdo->prepare("SELECT slug FROM restaurants WHERE id = ? LIMIT 1");
        $stmtSlug->execute([$restaurantId]);
        $slugRow = $stmtSlug->fetch(PDO::FETCH_ASSOC);
        if ($slugRow) $slug = (string)$slugRow['slug'];
    }
    $verify = verifyRestaurantPaystackPayment($restaurantId, $reference);

    if ($verify && $verify['success']) {
        $result = createOrderFromPendingOnlinePayment($reference, 'paystack');
        if ($result['success']) {
            if (($result['type'] ?? 'order') === 'reservation' && !empty($result['reservation_id'])) {
                header('Location: ' . $baseUrl . '/reservation-confirmation.php?slug=' . urlencode($result['slug']) . '&reservation_id=' . (int)$result['reservation_id']);
            } else {
                header('Location: ' . $baseUrl . '/order-confirmation.php?slug=' . urlencode($result['slug']) . '&order_id=' . (int)$result['order_id']);
            }
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
    if ($slug === '') {
        $stmtSlug = $pdo->prepare("SELECT slug FROM restaurants WHERE id = ? LIMIT 1");
        $stmtSlug->execute([$restaurantId]);
        $slugRow = $stmtSlug->fetch(PDO::FETCH_ASSOC);
        if ($slugRow) $slug = (string)$slugRow['slug'];
    }
    $verify = verifyRestaurantFlutterwavePayment($restaurantId, $transactionId);

    if ($verify && $verify['success']) {
        $result = createOrderFromPendingOnlinePayment($txRef, 'flutterwave');
        if ($result['success']) {
            if (($result['type'] ?? 'order') === 'reservation' && !empty($result['reservation_id'])) {
                header('Location: ' . $baseUrl . '/reservation-confirmation.php?slug=' . urlencode($result['slug']) . '&reservation_id=' . (int)$result['reservation_id']);
            } else {
                header('Location: ' . $baseUrl . '/order-confirmation.php?slug=' . urlencode($result['slug']) . '&order_id=' . (int)$result['order_id']);
            }
            exit;
        }
    }

    header('Location: ' . $baseUrl . '/payment-failed.php?slug=' . urlencode($slug) . '&reason=failed');
    exit;
}

header('Location: ' . $baseUrl . '/payment-failed.php?slug=' . urlencode($slug) . '&reason=error');
exit;
