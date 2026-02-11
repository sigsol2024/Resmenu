<?php
/**
 * Restaurant Order Flutterwave Webhook Handler
 * Order is created ONLY when payment succeeds (charge.completed, status=successful). No order exists before that.
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/restaurant-payment-functions.php';
require_once __DIR__ . '/../includes/order-functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_VERIF_HASH'] ?? '';
$event = json_decode($payload, true);

if (!$event || !isset($event['event'])) {
    http_response_code(400);
    exit('Invalid payload');
}

$data = $event['data'] ?? [];
$meta = $data['meta'] ?? $data;
$restaurantId = (int)($meta['restaurant_id'] ?? 0);
$reference = $meta['reference'] ?? $data['tx_ref'] ?? '';

$valid = false;
if ($restaurantId) {
    $keys = getRestaurantGatewayKeys($restaurantId, 'flutterwave');
    if (!empty($keys['webhook_secret'])) {
        $valid = hash_equals($keys['webhook_secret'], $signature);
    } else {
        $valid = true;
    }
}

if (!$valid && $restaurantId) {
    http_response_code(401);
    error_log('Restaurant Flutterwave webhook: Invalid signature');
    exit('Invalid signature');
}

$pdo = getDBConnection();
error_log('Restaurant Flutterwave webhook received: ' . $event['event']);

switch ($event['event']) {
    case 'charge.completed':
        $status = $data['status'] ?? '';
        if ($status === 'successful' && $reference && $restaurantId && $pdo) {
            $result = createOrderFromPendingOnlinePayment($reference, 'flutterwave');
            if ($result['success']) {
                error_log("Restaurant Flutterwave webhook: Order created from pending ref $reference (order_id: {$result['order_id']})");
            } else {
                error_log("Restaurant Flutterwave webhook: Failed to create order from ref $reference - " . implode(', ', $result['errors'] ?? []));
            }
        } elseif ($reference && $pdo) {
            $stmt = $pdo->prepare("DELETE FROM pending_online_payments WHERE reference = ? AND gateway = 'flutterwave'");
            $stmt->execute([$reference]);
            error_log("Restaurant Flutterwave webhook: Pending payment $reference removed (payment not successful, status: $status)");
        }
        break;
    default:
        error_log('Restaurant Flutterwave webhook: Unhandled event - ' . $event['event']);
}

http_response_code(200);
echo 'OK';
