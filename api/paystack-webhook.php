<?php
/**
 * Paystack Webhook Handler
 * Receives payment notifications from Paystack
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/subscription.php';
require_once __DIR__ . '/../includes/payment-gateway.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

// Get raw POST data
$payload = file_get_contents('php://input');

// Get signature from headers
$signature = $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] ?? '';

// Validate webhook signature
if (!validatePaystackWebhook($payload, $signature)) {
    http_response_code(401);
    error_log('Paystack webhook: Invalid signature');
    exit('Invalid signature');
}

// Parse event
$event = json_decode($payload, true);

if (!$event || !isset($event['event'])) {
    http_response_code(400);
    exit('Invalid payload');
}

$pdo = getDBConnection();

// Log webhook event
error_log('Paystack webhook received: ' . $event['event']);

// Handle different event types
switch ($event['event']) {
    case 'charge.success':
        handlePaystackChargeSuccess($event['data'], $pdo);
        break;
        
    case 'charge.failed':
        handlePaystackChargeFailed($event['data'], $pdo);
        break;
        
    default:
        // Log unhandled events
        error_log('Paystack webhook: Unhandled event type - ' . $event['event']);
}

// Acknowledge receipt
http_response_code(200);
echo 'OK';

/**
 * Handle successful charge
 */
function handlePaystackChargeSuccess($data, $pdo) {
    $reference = $data['reference'] ?? '';
    
    if (empty($reference)) {
        error_log('Paystack webhook: Missing reference');
        return;
    }
    
    // Find payment by reference
    $stmt = $pdo->prepare("SELECT * FROM payments WHERE transaction_reference = ?");
    $stmt->execute([$reference]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$payment) {
        error_log('Paystack webhook: Payment not found for reference ' . $reference);
        return;
    }
    
    // Skip if already processed
    if ($payment['status'] === 'success') {
        error_log('Paystack webhook: Payment already processed - ' . $reference);
        return;
    }
    
    // Verify the amount matches
    $expectedAmount = $payment['amount'] * 100; // Convert to kobo
    $receivedAmount = $data['amount'] ?? 0;
    
    if ($receivedAmount < $expectedAmount) {
        error_log('Paystack webhook: Amount mismatch - expected ' . $expectedAmount . ', received ' . $receivedAmount);
        updatePaymentStatus($payment['id'], 'failed', ['error' => 'Amount mismatch']);
        return;
    }
    
    // Process successful payment
    $success = processSuccessfulPayment($payment['id'], ['data' => $data]);
    
    if ($success) {
        error_log('Paystack webhook: Payment processed successfully - ' . $reference);
    } else {
        error_log('Paystack webhook: Failed to process payment - ' . $reference);
    }
}

/**
 * Handle failed charge
 */
function handlePaystackChargeFailed($data, $pdo) {
    $reference = $data['reference'] ?? '';
    
    if (empty($reference)) {
        return;
    }
    
    // Find payment by reference
    $stmt = $pdo->prepare("SELECT id FROM payments WHERE transaction_reference = ?");
    $stmt->execute([$reference]);
    $payment = $stmt->fetch();
    
    if ($payment) {
        updatePaymentStatus($payment['id'], 'failed', $data);
        error_log('Paystack webhook: Payment failed - ' . $reference);
    }
}

