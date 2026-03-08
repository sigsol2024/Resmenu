<?php
/**
 * Process Payment
 * Initialize payment with selected gateway
 */

require_once __DIR__ . '/../includes/auth.php';
requireManager();

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/subscription.php';
require_once __DIR__ . '/../includes/payment-gateway.php';

$pdo = getDBConnection();
$restaurantId = getCurrentUserRestaurantId();

if (!$restaurantId || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: billing.php');
    exit;
}

if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    $_SESSION['error'] = 'Invalid security token. Please refresh and try again.';
    header('Location: checkout.php');
    exit;
}

// Get form data
$planId = intval($_POST['plan_id'] ?? 0);
$billingCycle = $_POST['billing_cycle'] ?? 'monthly';
$gateway = $_POST['gateway'] ?? '';

// Validate
if ($planId <= 0 || !in_array($billingCycle, ['monthly', 'annual']) || !in_array($gateway, ['paystack', 'flutterwave'])) {
    $_SESSION['error'] = 'Invalid payment data. Please try again.';
    header('Location: checkout.php');
    exit;
}

// Get plan
$plan = getSubscriptionPlan($planId);
if (!$plan) {
    $_SESSION['error'] = 'Selected plan not found.';
    header('Location: checkout.php');
    exit;
}

// Get or create subscription
$subscription = getRestaurantSubscription($restaurantId);
if (!$subscription) {
    // Create new subscription
    $subscriptionId = createSubscription($restaurantId, $planId, $billingCycle, false);
    if (!$subscriptionId) {
        $_SESSION['error'] = 'Failed to create subscription. Please try again.';
        header('Location: checkout.php');
        exit;
    }
} else {
    $decision = getSubscriptionChangeDecision($subscription, $plan, $billingCycle);

    if ($decision['mode'] === 'none') {
        $_SESSION['info'] = 'You are already on that plan and billing cycle.';
        header('Location: billing.php');
        exit;
    }

    if ($decision['mode'] === 'scheduled') {
        $effectiveAt = $subscription['current_period_end'] ?? $subscription['trial_ends_at'] ?? date('Y-m-d H:i:s');
        $scheduled = createOrUpdateScheduledSubscriptionChange(
            $restaurantId,
            (int)$subscription['id'],
            (int)$plan['id'],
            $billingCycle,
            $effectiveAt,
            $decision['type']
        );

        if ($scheduled) {
            $_SESSION['success'] = 'Plan change has been scheduled and will take effect at the end of your current billing period.';
        } else {
            $_SESSION['error'] = 'Failed to schedule the plan change. Please try again.';
        }
        header('Location: billing.php');
        exit;
    }

    $subscriptionId = $subscription['id'];

    // Immediate change flow: keep current subscription active until payment is confirmed.
    cancelScheduledSubscriptionChange((int)$subscriptionId);
}

// Calculate amount
$amount = $billingCycle === 'annual' ? $plan['annual_price'] : $plan['monthly_price'];

// Get manager email
$stmt = $pdo->prepare("SELECT email FROM managers WHERE id = ?");
$stmt->execute([getCurrentUserId()]);
$manager = $stmt->fetch();
if (!$manager || empty($manager['email'])) {
    $_SESSION['error'] = 'Unable to resolve manager email for payment.';
    header('Location: checkout.php');
    exit;
}

// Get restaurant name
$stmt = $pdo->prepare("SELECT name FROM restaurants WHERE id = ?");
$stmt->execute([$restaurantId]);
$restaurant = $stmt->fetch();
if (!$restaurant || empty($restaurant['name'])) {
    $_SESSION['error'] = 'Unable to resolve restaurant details for payment.';
    header('Location: checkout.php');
    exit;
}

// Create payment record
$paymentId = createPayment([
    'restaurant_id' => $restaurantId,
    'subscription_id' => $subscriptionId,
    'amount' => $amount,
    'payment_gateway' => $gateway,
    'status' => 'pending'
]);

if (!$paymentId) {
    $_SESSION['error'] = 'Failed to create payment record. Please try again.';
    header('Location: checkout.php');
    exit;
}

// Build full callback URL so Paystack/Flutterwave always redirect back here (not dashboard default)
$baseUrl = defined('SITE_URL') ? rtrim(SITE_URL, '/') : '';
if ($baseUrl === '') {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $baseUrl = $protocol . $host;
}
$callbackUrl = $baseUrl . '/manager/payment-callback.php?gateway=' . urlencode($gateway);

// Prepare payment data
$paymentData = [
    'amount' => $amount,
    'email' => $manager['email'],
    'name' => $restaurant['name'],
    'callback_url' => $callbackUrl,
    'metadata' => [
        'payment_id' => $paymentId,
        'subscription_id' => $subscriptionId,
        'restaurant_id' => $restaurantId,
        'plan_id' => $planId,
        'billing_cycle' => $billingCycle
    ]
];

// Initialize payment with selected gateway
if ($gateway === 'paystack') {
    $result = initializePaystackPayment($paymentData);
} else {
    $result = initializeFlutterwavePayment($paymentData);
}

if ($result['success']) {
    // Update payment with transaction reference
    $stmt = $pdo->prepare("UPDATE payments SET transaction_reference = ? WHERE id = ?");
    $stmt->execute([$result['reference'], $paymentId]);
    
    // Store payment ID in session for callback verification
    $_SESSION['pending_payment_id'] = $paymentId;
    
    // Redirect to payment page
    header('Location: ' . $result['authorization_url']);
    exit;
} else {
    // Update payment status to failed
    updatePaymentStatus($paymentId, 'failed');
    
    $_SESSION['error'] = 'Payment initialization failed: ' . ($result['error'] ?? 'Unknown error');
    header('Location: checkout.php');
    exit;
}

