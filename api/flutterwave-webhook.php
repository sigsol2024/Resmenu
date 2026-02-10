<?php
/**
 * Flutterwave Webhook Handler
 * Receives payment notifications from Flutterwave
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
$signature = $_SERVER['HTTP_VERIF_HASH'] ?? '';

// Validate webhook signature
if (!validateFlutterwaveWebhook($signature)) {
    http_response_code(401);
    error_log('Flutterwave webhook: Invalid signature');
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
error_log('Flutterwave webhook received: ' . $event['event']);

// Handle different event types
switch ($event['event']) {
    case 'charge.completed':
        handleFlutterwaveChargeCompleted($event['data'], $pdo);
        break;
        
    default:
        // Log unhandled events
        error_log('Flutterwave webhook: Unhandled event type - ' . $event['event']);
}

// Acknowledge receipt
http_response_code(200);
echo 'OK';

/**
 * Handle completed charge
 */
function handleFlutterwaveChargeCompleted($data, $pdo) {
    $txRef = $data['tx_ref'] ?? '';
    $status = $data['status'] ?? '';
    
    if (empty($txRef)) {
        error_log('Flutterwave webhook: Missing tx_ref');
        return;
    }
    
    // Find payment by reference
    $stmt = $pdo->prepare("SELECT * FROM payments WHERE transaction_reference = ?");
    $stmt->execute([$txRef]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$payment) {
        error_log('Flutterwave webhook: Payment not found for reference ' . $txRef);
        return;
    }
    
    // Skip if already processed
    if ($payment['status'] === 'success') {
        error_log('Flutterwave webhook: Payment already processed - ' . $txRef);
        return;
    }
    
    if ($status !== 'successful') {
        updatePaymentStatus($payment['id'], 'failed', $data);
        error_log('Flutterwave webhook: Payment not successful - ' . $txRef);
        return;
    }
    
    // Verify the amount matches
    $expectedAmount = $payment['amount'];
    $receivedAmount = $data['amount'] ?? 0;
    
    if ($receivedAmount < $expectedAmount) {
        error_log('Flutterwave webhook: Amount mismatch - expected ' . $expectedAmount . ', received ' . $receivedAmount);
        updatePaymentStatus($payment['id'], 'failed', ['error' => 'Amount mismatch']);
        return;
    }
    
    // Verify with Flutterwave API
    $transactionId = $data['id'] ?? '';
    if ($transactionId) {
        $verifyResult = verifyFlutterwavePayment($transactionId);
        
        if (!$verifyResult['success']) {
            error_log('Flutterwave webhook: Verification failed - ' . $txRef);
            updatePaymentStatus($payment['id'], 'failed', ['error' => 'Verification failed']);
            return;
        }
    }
    
    // Process successful payment
    $success = processSuccessfulPayment($payment['id'], ['data' => $data]);
    
    if ($success) {
        error_log('Flutterwave webhook: Payment processed successfully - ' . $txRef);
    } else {
        error_log('Flutterwave webhook: Failed to process payment - ' . $txRef);
    }
}

