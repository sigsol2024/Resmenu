<?php
/**
 * Payment Callback Handler
 * Handles redirects from payment gateways after payment
 */

require_once __DIR__ . '/../includes/auth.php';
// Don't require login here - session might expire during payment redirect
// We'll verify payment by reference instead

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/subscription.php';
require_once __DIR__ . '/../includes/payment-gateway.php';

$pdo = getDBConnection();
$gateway = $_GET['gateway'] ?? '';

// Get payment ID from session (if available)
$paymentId = $_SESSION['pending_payment_id'] ?? 0;
unset($_SESSION['pending_payment_id']);

$success = false;
$message = '';
$redirectUrl = 'billing.php';

if ($gateway === 'paystack') {
    // Paystack returns reference in URL
    $reference = $_GET['reference'] ?? $_GET['trxref'] ?? '';
    
    if (empty($reference)) {
        $message = 'Payment reference not found.';
    } else {
        // Verify payment
        $result = verifyPaystackPayment($reference);
        
        if ($result['success']) {
            // Find payment by reference (more reliable than session)
            if (!$paymentId) {
                $stmt = $pdo->prepare("SELECT id, restaurant_id FROM payments WHERE transaction_reference = ?");
                $stmt->execute([$reference]);
                $payment = $stmt->fetch();
                $paymentId = $payment ? $payment['id'] : 0;
                
                // If payment found but user not logged in, redirect with reference
                if ($paymentId && !isLoggedIn()) {
                    $redirectUrl = 'billing.php?payment_success=1&reference=' . urlencode($reference);
                    header('Location: ' . $redirectUrl);
                    exit;
                }
            }
            
            if ($paymentId) {
                $success = processSuccessfulPayment($paymentId, $result);
                $message = $success ? 'Payment successful! Your subscription is now active.' : 'Payment verified but activation failed. Please contact support.';
            } else {
                $message = 'Payment record not found. Please contact support with reference: ' . htmlspecialchars($reference);
            }
        } else {
            // Update payment as failed
            if (!$paymentId) {
                $stmt = $pdo->prepare("SELECT id FROM payments WHERE transaction_reference = ?");
                $stmt->execute([$reference]);
                $payment = $stmt->fetch();
                $paymentId = $payment ? $payment['id'] : 0;
            }
            
            if ($paymentId) {
                updatePaymentStatus($paymentId, 'failed', ['error' => $result['error'] ?? 'Verification failed']);
            }
            $message = 'Payment verification failed: ' . ($result['error'] ?? 'Unknown error');
        }
    }
    
} elseif ($gateway === 'flutterwave') {
    // Flutterwave returns status and transaction_id
    $status = $_GET['status'] ?? '';
    $transactionId = $_GET['transaction_id'] ?? $_GET['tx_id'] ?? '';
    $txRef = $_GET['tx_ref'] ?? '';
    
    if ($status === 'successful' && $transactionId) {
        // Verify payment
        $result = verifyFlutterwavePayment($transactionId);
        
        if ($result['success']) {
            // Find payment by reference
            if (!$paymentId) {
                $stmt = $pdo->prepare("SELECT id, restaurant_id FROM payments WHERE transaction_reference = ?");
                $stmt->execute([$txRef]);
                $payment = $stmt->fetch();
                $paymentId = $payment ? $payment['id'] : 0;
                
                // If payment found but user not logged in, redirect with reference
                if ($paymentId && !isLoggedIn()) {
                    $redirectUrl = 'billing.php?payment_success=1&reference=' . urlencode($txRef);
                    header('Location: ' . $redirectUrl);
                    exit;
                }
            }
            
            if ($paymentId) {
                $success = processSuccessfulPayment($paymentId, $result);
                $message = $success ? 'Payment successful! Your subscription is now active.' : 'Payment verified but activation failed. Please contact support.';
            } else {
                $message = 'Payment record not found. Please contact support with reference: ' . htmlspecialchars($txRef);
            }
        } else {
            if (!$paymentId) {
                $stmt = $pdo->prepare("SELECT id FROM payments WHERE transaction_reference = ?");
                $stmt->execute([$txRef]);
                $payment = $stmt->fetch();
                $paymentId = $payment ? $payment['id'] : 0;
            }
            
            if ($paymentId) {
                updatePaymentStatus($paymentId, 'failed', ['error' => $result['error'] ?? 'Verification failed']);
            }
            $message = 'Payment verification failed: ' . ($result['error'] ?? 'Unknown error');
        }
    } elseif ($status === 'cancelled') {
        if (!$paymentId && $txRef) {
            $stmt = $pdo->prepare("SELECT id FROM payments WHERE transaction_reference = ?");
            $stmt->execute([$txRef]);
            $payment = $stmt->fetch();
            $paymentId = $payment ? $payment['id'] : 0;
        }
        
        if ($paymentId) {
            updatePaymentStatus($paymentId, 'failed', ['error' => 'Payment cancelled by user']);
        }
        $message = 'Payment was cancelled.';
    } else {
        if (!$paymentId && $txRef) {
            $stmt = $pdo->prepare("SELECT id FROM payments WHERE transaction_reference = ?");
            $stmt->execute([$txRef]);
            $payment = $stmt->fetch();
            $paymentId = $payment ? $payment['id'] : 0;
        }
        
        if ($paymentId) {
            updatePaymentStatus($paymentId, 'failed', ['error' => 'Payment failed', 'status' => $status]);
        }
        $message = 'Payment failed. Please try again.';
    }
    
} else {
    $message = 'Invalid payment gateway.';
}

// Store result in session if user is logged in, otherwise use GET parameter
if (isLoggedIn()) {
    $_SESSION[$success ? 'success' : 'error'] = $message;
    header('Location: ' . $redirectUrl);
} else {
    // User not logged in - redirect with message in URL
    $redirectUrl .= ($success ? '?payment_success=1' : '?payment_error=1') . '&message=' . urlencode($message);
    
    // Add reference if available
    $ref = $reference ?? $txRef ?? '';
    if ($ref) {
        $redirectUrl .= '&reference=' . urlencode($ref);
    }
    
    header('Location: ' . $redirectUrl);
}
exit;

