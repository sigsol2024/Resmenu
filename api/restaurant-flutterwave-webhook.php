<?php
/**
 * Restaurant Order Flutterwave Webhook Handler
 * Receives payment notifications for customer food orders (per-restaurant)
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/restaurant-payment-functions.php';

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

// Get restaurant_id and order_id from meta (set when initiating order payment)
$data = $event['data'] ?? [];
$meta = $data['meta'] ?? $data;
$restaurantId = (int)($meta['restaurant_id'] ?? 0);
$orderId = (int)($meta['order_id'] ?? 0);

// Validate signature using restaurant's webhook secret
$valid = false;
if ($restaurantId) {
    $keys = getRestaurantGatewayKeys($restaurantId, 'flutterwave');
    if (!empty($keys['webhook_secret'])) {
        $valid = hash_equals($keys['webhook_secret'], $signature);
    } else {
        $valid = true; // No secret configured, accept
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
        if ($orderId && $restaurantId && $pdo) {
            $txRef = $event['data']['tx_ref'] ?? '';
            $status = $event['data']['status'] ?? '';
            if ($status === 'successful') {
                $stmt = $pdo->prepare("UPDATE orders SET status = 'confirmed' WHERE id = ? AND restaurant_id = ? AND status = 'pending'");
                $stmt->execute([$orderId, $restaurantId]);
                if ($stmt->rowCount() > 0) {
                    error_log("Restaurant Flutterwave webhook: Order $orderId confirmed (ref: $txRef)");
                }
            } else {
                $stmt = $pdo->prepare("UPDATE orders SET status = 'cancelled', updated_at = NOW() WHERE id = ? AND restaurant_id = ? AND status = 'pending'");
                $stmt->execute([$orderId, $restaurantId]);
                if ($stmt->rowCount() > 0) {
                    error_log("Restaurant Flutterwave webhook: Order $orderId cancelled (payment failed, status: $status)");
                }
            }
        }
        break;
    default:
        error_log('Restaurant Flutterwave webhook: Unhandled event - ' . $event['event']);
}

http_response_code(200);
echo 'OK';
