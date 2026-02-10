<?php
/**
 * Restaurant Order Paystack Webhook Handler
 * Receives payment notifications for customer food orders (per-restaurant)
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/restaurant-payment-functions.php';

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

// Get restaurant_id from metadata (set when initiating order payment)
$metadata = $event['data']['metadata'] ?? [];
$restaurantId = (int)($metadata['restaurant_id'] ?? $metadata['restaurant_id'] ?? 0);
$orderId = (int)($metadata['order_id'] ?? $metadata['order_id'] ?? 0);

// Validate signature using restaurant's keys
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
        if ($orderId && $restaurantId && $pdo) {
            $reference = $event['data']['reference'] ?? '';
            $stmt = $pdo->prepare("UPDATE orders SET status = 'confirmed' WHERE id = ? AND restaurant_id = ? AND status = 'pending'");
            $stmt->execute([$orderId, $restaurantId]);
            if ($stmt->rowCount() > 0) {
                error_log("Restaurant Paystack webhook: Order $orderId confirmed (ref: $reference)");
            }
        }
        break;
    case 'charge.failed':
        if ($orderId && $restaurantId && $pdo) {
            $stmt = $pdo->prepare("UPDATE orders SET status = 'cancelled', updated_at = NOW() WHERE id = ? AND restaurant_id = ? AND status = 'pending'");
            $stmt->execute([$orderId, $restaurantId]);
            if ($stmt->rowCount() > 0) {
                error_log("Restaurant Paystack webhook: Order $orderId cancelled (payment failed)");
            }
        }
        break;
    default:
        error_log('Restaurant Paystack webhook: Unhandled event - ' . $event['event']);
}

http_response_code(200);
echo 'OK';
