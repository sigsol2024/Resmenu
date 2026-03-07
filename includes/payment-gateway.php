<?php
/**
 * Payment Gateway Integration
 * 
 * Functions for Paystack and Flutterwave payment processing
 */

require_once __DIR__ . '/subscription.php';

// Ensure SITE_URL is defined
if (!defined('SITE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptPath = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
    $scriptDir = dirname($scriptPath);
    if ($scriptDir === '/admin' || $scriptDir === '/manager' || strpos($scriptDir, '/admin/') === 0 || strpos($scriptDir, '/manager/') === 0) {
        $basePath = dirname($scriptDir);
    } else {
        $basePath = $scriptDir;
    }
    $basePath = ($basePath === '/' || $basePath === '\\' || $basePath === '.') ? '' : $basePath;
    define('SITE_URL', $protocol . $host . $basePath);
}

/**
 * Initialize Paystack payment
 * 
 * @param array $data Payment data (amount, email, metadata)
 * @return array ['success' => bool, 'authorization_url' => string, 'reference' => string]
 */
function initializePaystackPayment($data) {
    $keys = getGatewayKeys('paystack');
    
    if (empty($keys['secret_key'])) {
        return ['success' => false, 'error' => 'Paystack is not configured'];
    }
    
    $reference = 'PS_' . time() . '_' . bin2hex(random_bytes(8));
    
    $payload = [
        'email' => $data['email'],
        'amount' => $data['amount'] * 100, // Convert to kobo
        'reference' => $reference,
        'callback_url' => $data['callback_url'] ?? (rtrim(SITE_URL, '/') . '/manager/payment-callback.php?gateway=paystack'),
        'metadata' => $data['metadata'] ?? []
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.paystack.co/transaction/initialize');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $keys['secret_key'],
        'Content-Type: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        error_log("Paystack init failed: " . $response);
        return ['success' => false, 'error' => 'Failed to initialize payment'];
    }
    
    $result = json_decode($response, true);
    
    if ($result['status'] === true) {
        return [
            'success' => true,
            'authorization_url' => $result['data']['authorization_url'],
            'access_code' => $result['data']['access_code'],
            'reference' => $result['data']['reference']
        ];
    }
    
    return ['success' => false, 'error' => $result['message'] ?? 'Unknown error'];
}

/**
 * Verify Paystack payment
 * 
 * @param string $reference Transaction reference
 * @return array
 */
function verifyPaystackPayment($reference) {
    $keys = getGatewayKeys('paystack');
    
    if (empty($keys['secret_key'])) {
        return ['success' => false, 'error' => 'Paystack is not configured'];
    }
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.paystack.co/transaction/verify/' . urlencode($reference));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $keys['secret_key']
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        error_log("Paystack verify failed: " . $response);
        return ['success' => false, 'error' => 'Failed to verify payment'];
    }
    
    $result = json_decode($response, true);
    
    if ($result['status'] === true && $result['data']['status'] === 'success') {
        return [
            'success' => true,
            'data' => $result['data'],
            'amount' => $result['data']['amount'] / 100, // Convert from kobo
            'reference' => $result['data']['reference'],
            'metadata' => $result['data']['metadata'] ?? []
        ];
    }
    
    return [
        'success' => false, 
        'error' => $result['data']['gateway_response'] ?? 'Payment not successful',
        'data' => $result['data'] ?? []
    ];
}

/**
 * Initialize Flutterwave payment
 * 
 * @param array $data Payment data (amount, email, name, metadata)
 * @return array
 */
function initializeFlutterwavePayment($data) {
    $keys = getGatewayKeys('flutterwave');
    
    if (empty($keys['secret_key'])) {
        return ['success' => false, 'error' => 'Flutterwave is not configured'];
    }
    
    $reference = 'FLW_' . time() . '_' . bin2hex(random_bytes(8));
    
    $payload = [
        'tx_ref' => $reference,
        'amount' => $data['amount'],
        'currency' => $data['currency'] ?? 'NGN',
        'redirect_url' => $data['callback_url'] ?? (rtrim(SITE_URL, '/') . '/manager/payment-callback.php?gateway=flutterwave'),
        'customer' => [
            'email' => $data['email'],
            'name' => $data['name'] ?? '',
            'phonenumber' => $data['phone'] ?? ''
        ],
        'customizations' => [
            'title' => 'Restaurant Menu Platform',
            'description' => 'Subscription Payment',
            'logo' => rtrim(SITE_URL, '/') . '/assets/images/logo.png'
        ],
        'meta' => $data['metadata'] ?? []
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.flutterwave.com/v3/payments');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $keys['secret_key'],
        'Content-Type: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        error_log("Flutterwave init failed: " . $response);
        return ['success' => false, 'error' => 'Failed to initialize payment'];
    }
    
    $result = json_decode($response, true);
    
    if ($result['status'] === 'success') {
        return [
            'success' => true,
            'authorization_url' => $result['data']['link'],
            'reference' => $reference
        ];
    }
    
    return ['success' => false, 'error' => $result['message'] ?? 'Unknown error'];
}

/**
 * Verify Flutterwave payment
 * 
 * @param string $transactionId Flutterwave transaction ID
 * @return array
 */
function verifyFlutterwavePayment($transactionId) {
    $keys = getGatewayKeys('flutterwave');
    
    if (empty($keys['secret_key'])) {
        return ['success' => false, 'error' => 'Flutterwave is not configured'];
    }
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.flutterwave.com/v3/transactions/' . urlencode($transactionId) . '/verify');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $keys['secret_key']
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        error_log("Flutterwave verify failed: " . $response);
        return ['success' => false, 'error' => 'Failed to verify payment'];
    }
    
    $result = json_decode($response, true);
    
    if ($result['status'] === 'success' && $result['data']['status'] === 'successful') {
        return [
            'success' => true,
            'data' => $result['data'],
            'amount' => $result['data']['amount'],
            'reference' => $result['data']['tx_ref'],
            'metadata' => $result['data']['meta'] ?? []
        ];
    }
    
    return [
        'success' => false,
        'error' => $result['data']['processor_response'] ?? 'Payment not successful',
        'data' => $result['data'] ?? []
    ];
}

/**
 * Process successful payment - activate subscription
 * 
 * @param int $paymentId
 * @param array $verificationData
 * @return bool
 */
function processSuccessfulPayment($paymentId, $verificationData) {
    global $pdo;
    
    if (!$pdo) return false;
    
    try {
        // Update payment status
        updatePaymentStatus($paymentId, 'success', $verificationData['data'] ?? []);
        
        // Get payment details
        $stmt = $pdo->prepare("SELECT * FROM payments WHERE id = ?");
        $stmt->execute([$paymentId]);
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$payment) return false;
        
        // Get subscription
        $stmt = $pdo->prepare("SELECT * FROM subscriptions WHERE id = ?");
        $stmt->execute([$payment['subscription_id']]);
        $subscription = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$subscription) return false;

        // Apply target plan/cycle from gateway metadata only after successful payment.
        $meta = [];
        if (isset($verificationData['metadata']) && is_array($verificationData['metadata'])) {
            $meta = $verificationData['metadata'];
        } elseif (isset($verificationData['data']['metadata']) && is_array($verificationData['data']['metadata'])) {
            $meta = $verificationData['data']['metadata'];
        } elseif (isset($verificationData['data']['meta']) && is_array($verificationData['data']['meta'])) {
            $meta = $verificationData['data']['meta'];
        }

        $targetPlanId = (int)($meta['plan_id'] ?? 0);
        $targetCycleRaw = strtolower((string)($meta['billing_cycle'] ?? ''));
        $targetCycle = $targetCycleRaw === 'annual' ? 'annual' : ($targetCycleRaw === 'monthly' ? 'monthly' : '');

        if ($targetPlanId > 0 && $targetCycle !== '') {
            $planStmt = $pdo->prepare("SELECT id FROM subscription_plans WHERE id = ? AND is_active = 1 LIMIT 1");
            $planStmt->execute([$targetPlanId]);
            $planExists = $planStmt->fetch(PDO::FETCH_ASSOC);
            if ($planExists) {
                $updateTarget = $pdo->prepare("UPDATE subscriptions SET plan_id = ?, billing_cycle = ? WHERE id = ?");
                $updateTarget->execute([$targetPlanId, $targetCycle, $subscription['id']]);
                $subscription['billing_cycle'] = $targetCycle;
            }
        }
        
        // Activate subscription
        return activateSubscription($subscription['id'], $subscription['billing_cycle']);
        
    } catch (PDOException $e) {
        error_log("Error processing payment: " . $e->getMessage());
        return false;
    }
}

/**
 * Validate Paystack webhook signature
 * 
 * @param string $payload Raw POST body
 * @param string $signature X-Paystack-Signature header
 * @return bool
 */
function validatePaystackWebhook($payload, $signature) {
    $keys = getGatewayKeys('paystack');
    
    if (empty($keys['secret_key'])) {
        return false;
    }
    
    $computed = hash_hmac('sha512', $payload, $keys['secret_key']);
    return hash_equals($computed, $signature);
}

/**
 * Validate Flutterwave webhook signature
 * 
 * @param string $signature verif-hash header
 * @return bool
 */
function validateFlutterwaveWebhook($signature) {
    $keys = getGatewayKeys('flutterwave');
    
    if (empty($keys['webhook_secret'])) {
        return false; // Reject when secret not configured to avoid accepting forged webhooks
    }
    
    return hash_equals($keys['webhook_secret'], $signature);
}

