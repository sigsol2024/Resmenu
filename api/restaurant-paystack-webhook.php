<?php
/**
 * Restaurant Order Paystack Webhook Handler
 * Order is created ONLY when payment succeeds (charge.success). No order exists before that.
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/restaurant-payment-functions.php';
require_once __DIR__ . '/../includes/order-functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] ?? '';
$event = json_decode($payload, true);

if (!$event || !isset($event['event'])) {
    http_response_code(400);
    exit('Invalid payload');
}

$metadata = $event['data']['metadata'] ?? [];
$dataRef = $event['data']['reference'] ?? '';
$reference = $dataRef ?: ($metadata['reference'] ?? '');
$restaurantId = (int)($metadata['restaurant_id'] ?? 0);

// Resolve restaurant_id from pending payment if not in metadata
if (!$restaurantId && $reference && $pdo = getDBConnection()) {
    $stmt = $pdo->prepare("SELECT restaurant_id FROM pending_online_payments WHERE reference = ? AND gateway = 'paystack'");
    $stmt->execute([$reference]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $restaurantId = (int)$row['restaurant_id'];
    }
}

$valid = false;
if ($restaurantId) {
    $keys = getRestaurantGatewayKeys($restaurantId, 'paystack');
    if (!empty($keys['secret_key'])) {
        $computed = hash_hmac('sha512', $payload, $keys['secret_key']);
        $valid = hash_equals($computed, $signature);
    }
}

if (!$valid && $restaurantId) {
    http_response_code(401);
    error_log('Restaurant Paystack webhook: Invalid signature');
    exit('Invalid signature');
}

$pdo = getDBConnection();
error_log('Restaurant Paystack webhook received: ' . $event['event']);

switch ($event['event']) {
    case 'charge.success':
        if ($reference && $restaurantId && $pdo) {
            $result = createOrderFromPendingOnlinePayment($reference, 'paystack');
            if ($result['success']) {
                error_log("Restaurant Paystack webhook: Order created from pending ref $reference (order_id: {$result['order_id']})");
            } else {
                error_log("Restaurant Paystack webhook: Failed to create order from ref $reference - " . implode(', ', $result['errors'] ?? []));
            }
        }
        break;
    case 'charge.failed':
        if ($reference && $pdo) {
            $stmt = $pdo->prepare("DELETE FROM pending_online_payments WHERE reference = ? AND gateway = 'paystack'");
            $stmt->execute([$reference]);
            error_log("Restaurant Paystack webhook: Pending payment $reference removed (payment failed)");
        }
        break;
    default:
        error_log('Restaurant Paystack webhook: Unhandled event - ' . $event['event']);
}

http_response_code(200);
echo 'OK';
