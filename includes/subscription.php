<?php
/**
 * Subscription System Helper Functions
 * 
 * Core functions for managing restaurant subscriptions, plans, and feature access.
 */

require_once __DIR__ . '/functions.php';

// Encryption key for API keys - should be stored in config in production
define('ENCRYPTION_KEY', 'your-32-character-secret-key-here');
define('ENCRYPTION_METHOD', 'AES-256-CBC');

/**
 * Get a restaurant's current subscription
 * 
 * @param int $restaurantId
 * @return array|null Subscription data with plan details or null
 */
function getRestaurantSubscription($restaurantId) {
    global $pdo;
    
    if (!$pdo) return null;
    
    try {
        $stmt = $pdo->prepare("
            SELECT s.*, p.name as plan_name, p.slug as plan_slug, 
                   p.monthly_price, p.annual_price,
                   p.max_categories, p.max_menu_items, p.max_qr_styles, p.max_templates,
                   p.features as plan_features
            FROM subscriptions s
            JOIN subscription_plans p ON s.plan_id = p.id
            WHERE s.restaurant_id = ?
            ORDER BY s.created_at DESC
            LIMIT 1
        ");
        $stmt->execute([$restaurantId]);
        $subscription = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($subscription && $subscription['plan_features']) {
            $subscription['plan_features'] = json_decode($subscription['plan_features'], true);
        }
        
        return $subscription ?: null;
    } catch (PDOException $e) {
        error_log("Error getting subscription: " . $e->getMessage());
        return null;
    }
}

/**
 * Get all available subscription plans
 * 
 * @param bool $activeOnly Only return active plans
 * @return array
 */
function getSubscriptionPlans($activeOnly = true) {
    global $pdo;
    
    if (!$pdo) return [];
    
    try {
        $sql = "SELECT * FROM subscription_plans";
        if ($activeOnly) {
            $sql .= " WHERE is_active = 1";
        }
        $sql .= " ORDER BY display_order ASC";
        
        $stmt = $pdo->query($sql);
        $plans = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($plans as &$plan) {
            if ($plan['features']) {
                $plan['features'] = json_decode($plan['features'], true);
            }
        }
        
        return $plans;
    } catch (PDOException $e) {
        error_log("Error getting plans: " . $e->getMessage());
        return [];
    }
}

/**
 * Get a single subscription plan by ID or slug
 * 
 * @param mixed $identifier Plan ID or slug
 * @return array|null
 */
function getSubscriptionPlan($identifier) {
    global $pdo;
    
    if (!$pdo) return null;
    
    try {
        $column = is_numeric($identifier) ? 'id' : 'slug';
        $stmt = $pdo->prepare("SELECT * FROM subscription_plans WHERE {$column} = ?");
        $stmt->execute([$identifier]);
        $plan = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($plan && $plan['features']) {
            $plan['features'] = json_decode($plan['features'], true);
        }
        
        return $plan ?: null;
    } catch (PDOException $e) {
        error_log("Error getting plan: " . $e->getMessage());
        return null;
    }
}

/**
 * Check if a subscription is currently active (including trial)
 * 
 * @param int $restaurantId
 * @return bool
 */
function isSubscriptionActive($restaurantId) {
    $subscription = getRestaurantSubscription($restaurantId);
    
    if (!$subscription) return false;
    
    $status = $subscription['status'];
    
    // Check if trial is still valid
    if ($status === 'trial') {
        return isTrialActive($subscription);
    }
    
    // Check if subscription is active and not expired
    if ($status === 'active') {
        if ($subscription['current_period_end']) {
            return strtotime($subscription['current_period_end']) > time();
        }
        return true;
    }
    
    return false;
}

/**
 * Check if trial period is still active
 * 
 * @param array $subscription Subscription data
 * @return bool
 */
function isTrialActive($subscription) {
    if (!$subscription || $subscription['status'] !== 'trial') {
        return false;
    }
    
    if (!$subscription['trial_ends_at']) {
        return false;
    }
    
    return strtotime($subscription['trial_ends_at']) > time();
}

/**
 * Get remaining trial days
 * 
 * @param array $subscription
 * @return int Days remaining (0 if expired or not on trial)
 */
function getTrialDaysRemaining($subscription) {
    if (!$subscription || $subscription['status'] !== 'trial' || !$subscription['trial_ends_at']) {
        return 0;
    }
    
    $trialEnd = strtotime($subscription['trial_ends_at']);
    $now = time();
    
    if ($trialEnd <= $now) return 0;
    
    return ceil(($trialEnd - $now) / 86400);
}

/**
 * Check if a restaurant has access to a specific feature
 * 
 * @param int $restaurantId
 * @param string $feature Feature name (e.g., 'categories', 'menu_items', 'qr_styles', 'templates')
 * @param int $currentCount Current usage count (optional, for limit checks)
 * @return bool
 */
function hasFeatureAccess($restaurantId, $feature, $currentCount = null) {
    $subscription = getRestaurantSubscription($restaurantId);
    
    // No subscription = no access (unless trial)
    if (!$subscription) return false;
    
    // Check if subscription is active
    if (!isSubscriptionActive($restaurantId)) return false;
    
    // Map feature names to plan columns
    $featureMap = [
        'categories' => 'max_categories',
        'menu_items' => 'max_menu_items',
        'qr_styles' => 'max_qr_styles',
        'templates' => 'max_templates'
    ];
    
    // Check if it's a countable feature
    if (isset($featureMap[$feature])) {
        $maxAllowed = $subscription[$featureMap[$feature]];
        
        // -1 means unlimited
        if ($maxAllowed === -1 || $maxAllowed === '-1') return true;
        
        // If current count provided, check against limit
        if ($currentCount !== null) {
            return $currentCount < (int)$maxAllowed;
        }
        
        return true;
    }
    
    // Check JSON features
    if (isset($subscription['plan_features'][$feature])) {
        return (bool)$subscription['plan_features'][$feature];
    }
    
    return false;
}

/**
 * Get remaining usage for a feature
 * 
 * @param int $restaurantId
 * @param string $feature Feature name
 * @return array ['used' => int, 'limit' => int|string, 'remaining' => int|string]
 */
function getRemainingUsage($restaurantId, $feature) {
    global $pdo;
    
    $subscription = getRestaurantSubscription($restaurantId);
    
    $result = [
        'used' => 0,
        'limit' => 0,
        'remaining' => 0,
        'unlimited' => false
    ];
    
    if (!$subscription || !$pdo) return $result;
    
    $featureMap = [
        'categories' => 'max_categories',
        'menu_items' => 'max_menu_items',
        'qr_styles' => 'max_qr_styles',
        'templates' => 'max_templates'
    ];
    
    if (!isset($featureMap[$feature])) return $result;
    
    $maxAllowed = (int)$subscription[$featureMap[$feature]];
    
    // Get current usage
    try {
        switch ($feature) {
            case 'categories':
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE restaurant_id = ?");
                $stmt->execute([$restaurantId]);
                $result['used'] = (int)$stmt->fetchColumn();
                break;
                
            case 'menu_items':
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM menu_items WHERE restaurant_id = ?");
                $stmt->execute([$restaurantId]);
                $result['used'] = (int)$stmt->fetchColumn();
                break;
                
            case 'qr_styles':
                // Count QR templates used by restaurant
                $stmt = $pdo->prepare("SELECT COUNT(DISTINCT qr_template_id) FROM restaurant_qr_codes WHERE restaurant_id = ? AND qr_template_id IS NOT NULL");
                $stmt->execute([$restaurantId]);
                $result['used'] = (int)$stmt->fetchColumn();
                break;
                
            case 'templates':
                // Restaurant can only use one template at a time, but track available count
                $result['used'] = 1;
                break;
        }
    } catch (PDOException $e) {
        error_log("Error getting usage: " . $e->getMessage());
    }
    
    if ($maxAllowed === -1) {
        $result['limit'] = 'Unlimited';
        $result['remaining'] = 'Unlimited';
        $result['unlimited'] = true;
    } else {
        $result['limit'] = $maxAllowed;
        $result['remaining'] = max(0, $maxAllowed - $result['used']);
    }
    
    return $result;
}

/**
 * Create a new subscription for a restaurant
 * 
 * @param int $restaurantId
 * @param int $planId
 * @param string $billingCycle 'monthly' or 'annual'
 * @param bool $isTrial Start with trial period
 * @return int|false Subscription ID or false on failure
 */
function createSubscription($restaurantId, $planId, $billingCycle = 'monthly', $isTrial = true) {
    global $pdo;
    
    if (!$pdo) return false;
    
    try {
        $status = $isTrial ? 'trial' : 'pending';
        $trialEndsAt = $isTrial ? date('Y-m-d H:i:s', strtotime('+7 days')) : null;
        
        $stmt = $pdo->prepare("
            INSERT INTO subscriptions (restaurant_id, plan_id, billing_cycle, status, trial_ends_at)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$restaurantId, $planId, $billingCycle, $status, $trialEndsAt]);
        
        $subscriptionId = $pdo->lastInsertId();
        
        // Update restaurant with subscription_id
        $stmt = $pdo->prepare("UPDATE restaurants SET subscription_id = ? WHERE id = ?");
        $stmt->execute([$subscriptionId, $restaurantId]);
        
        return $subscriptionId;
    } catch (PDOException $e) {
        error_log("Error creating subscription: " . $e->getMessage());
        return false;
    }
}

/**
 * Update subscription status
 * 
 * @param int $subscriptionId
 * @param string $status New status
 * @param array $additionalData Additional fields to update
 * @return bool
 */
function updateSubscriptionStatus($subscriptionId, $status, $additionalData = []) {
    global $pdo;
    
    if (!$pdo) return false;
    
    try {
        $updates = ['status = ?'];
        $params = [$status];
        
        if (isset($additionalData['current_period_start'])) {
            $updates[] = 'current_period_start = ?';
            $params[] = $additionalData['current_period_start'];
        }
        
        if (isset($additionalData['current_period_end'])) {
            $updates[] = 'current_period_end = ?';
            $params[] = $additionalData['current_period_end'];
        }
        
        if ($status === 'cancelled') {
            $updates[] = 'cancelled_at = NOW()';
        }
        
        $params[] = $subscriptionId;
        
        $stmt = $pdo->prepare("UPDATE subscriptions SET " . implode(', ', $updates) . " WHERE id = ?");
        return $stmt->execute($params);
    } catch (PDOException $e) {
        error_log("Error updating subscription: " . $e->getMessage());
        return false;
    }
}

/**
 * Activate subscription after successful payment
 * 
 * @param int $subscriptionId
 * @param string $billingCycle 'monthly' or 'annual'
 * @return bool
 */
function activateSubscription($subscriptionId, $billingCycle = 'monthly') {
    $periodEnd = $billingCycle === 'annual' 
        ? date('Y-m-d H:i:s', strtotime('+1 year'))
        : date('Y-m-d H:i:s', strtotime('+1 month'));
    
    return updateSubscriptionStatus($subscriptionId, 'active', [
        'current_period_start' => date('Y-m-d H:i:s'),
        'current_period_end' => $periodEnd
    ]);
}

/**
 * Get subscription status display info
 * 
 * @param array $subscription
 * @return array ['label' => string, 'class' => string, 'description' => string]
 */
function getSubscriptionStatusInfo($subscription) {
    if (!$subscription) {
        return [
            'label' => 'No Subscription',
            'class' => 'badge-secondary',
            'description' => 'No active subscription'
        ];
    }
    
    $status = $subscription['status'];
    
    switch ($status) {
        case 'trial':
            $daysLeft = getTrialDaysRemaining($subscription);
            if ($daysLeft > 0) {
                return [
                    'label' => 'Trial',
                    'class' => 'badge-info',
                    'description' => "{$daysLeft} days remaining"
                ];
            } else {
                return [
                    'label' => 'Trial Expired',
                    'class' => 'badge-warning',
                    'description' => 'Please subscribe to continue'
                ];
            }
            
        case 'active':
            $endDate = $subscription['current_period_end'] 
                ? date('M j, Y', strtotime($subscription['current_period_end']))
                : 'N/A';
            return [
                'label' => 'Active',
                'class' => 'badge-success',
                'description' => "Renews on {$endDate}"
            ];
            
        case 'expired':
            return [
                'label' => 'Expired',
                'class' => 'badge-danger',
                'description' => 'Please renew to continue'
            ];
            
        case 'cancelled':
            return [
                'label' => 'Cancelled',
                'class' => 'badge-secondary',
                'description' => 'Subscription has been cancelled'
            ];
            
        case 'pending':
            return [
                'label' => 'Pending',
                'class' => 'badge-warning',
                'description' => 'Awaiting payment confirmation'
            ];
            
        default:
            return [
                'label' => ucfirst($status),
                'class' => 'badge-secondary',
                'description' => ''
            ];
    }
}

/**
 * Encrypt an API key for secure storage
 * 
 * @param string $key The API key to encrypt
 * @return string Encrypted key (base64 encoded)
 */
function encryptApiKey($key) {
    if (empty($key)) return '';
    
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(ENCRYPTION_METHOD));
    $encrypted = openssl_encrypt($key, ENCRYPTION_METHOD, ENCRYPTION_KEY, 0, $iv);
    
    return base64_encode($iv . '::' . $encrypted);
}

/**
 * Decrypt an API key
 * 
 * @param string $encryptedKey The encrypted key (base64 encoded)
 * @return string Decrypted key
 */
function decryptApiKey($encryptedKey) {
    if (empty($encryptedKey)) return '';
    
    $data = base64_decode($encryptedKey);
    $parts = explode('::', $data, 2);
    
    if (count($parts) !== 2) return '';
    
    list($iv, $encrypted) = $parts;
    return openssl_decrypt($encrypted, ENCRYPTION_METHOD, ENCRYPTION_KEY, 0, $iv);
}

/**
 * Get payment gateway settings
 * 
 * @param string|null $gateway Specific gateway or null for all
 * @return array
 */
function getPaymentSettings($gateway = null) {
    global $pdo;
    
    if (!$pdo) return [];
    
    try {
        if ($gateway) {
            $stmt = $pdo->prepare("SELECT * FROM payment_settings WHERE gateway = ?");
            $stmt->execute([$gateway]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } else {
            $stmt = $pdo->query("SELECT * FROM payment_settings");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        error_log("Error getting payment settings: " . $e->getMessage());
        return [];
    }
}

/**
 * Get active payment gateways
 * 
 * @return array Active gateways with their settings
 */
function getActivePaymentGateways() {
    global $pdo;
    
    if (!$pdo) return [];
    
    try {
        $stmt = $pdo->query("SELECT * FROM payment_settings WHERE is_active = 1");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting active gateways: " . $e->getMessage());
        return [];
    }
}

/**
 * Get gateway API keys (decrypted, based on test/live mode)
 * 
 * @param string $gateway Gateway name
 * @return array ['public_key' => string, 'secret_key' => string, 'webhook_secret' => string]
 */
function getGatewayKeys($gateway) {
    $settings = getPaymentSettings($gateway);
    
    if (!$settings) {
        return ['public_key' => '', 'secret_key' => '', 'webhook_secret' => ''];
    }
    
    $isTestMode = (bool)$settings['test_mode'];
    
    if ($isTestMode) {
        return [
            'public_key' => $settings['public_key_test'] ?? '',
            'secret_key' => decryptApiKey($settings['secret_key_test'] ?? ''),
            'webhook_secret' => $settings['webhook_secret_test'] ?? ''
        ];
    } else {
        return [
            'public_key' => $settings['public_key_live'] ?? '',
            'secret_key' => decryptApiKey($settings['secret_key_live'] ?? ''),
            'webhook_secret' => $settings['webhook_secret_live'] ?? ''
        ];
    }
}

/**
 * Update payment gateway settings
 * 
 * @param string $gateway
 * @param array $settings
 * @return bool
 */
function updatePaymentSettings($gateway, $settings) {
    global $pdo;
    
    if (!$pdo) return false;
    
    try {
        $updates = [];
        $params = [];
        
        $allowedFields = [
            'is_active', 'test_mode', 
            'public_key_live', 'secret_key_live', 'webhook_secret_live',
            'public_key_test', 'secret_key_test', 'webhook_secret_test'
        ];
        
        foreach ($allowedFields as $field) {
            if (isset($settings[$field])) {
                $value = $settings[$field];
                
                // Encrypt secret keys
                if (strpos($field, 'secret_key') !== false && !empty($value)) {
                    $value = encryptApiKey($value);
                }
                
                $updates[] = "{$field} = ?";
                $params[] = $value;
            }
        }
        
        if (empty($updates)) return true;
        
        $params[] = $gateway;
        
        $stmt = $pdo->prepare("UPDATE payment_settings SET " . implode(', ', $updates) . " WHERE gateway = ?");
        return $stmt->execute($params);
    } catch (PDOException $e) {
        error_log("Error updating payment settings: " . $e->getMessage());
        return false;
    }
}

/**
 * Create a payment record
 * 
 * @param array $data Payment data
 * @return int|false Payment ID or false
 */
function createPayment($data) {
    global $pdo;
    
    if (!$pdo) return false;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO payments (restaurant_id, subscription_id, amount, currency, payment_gateway, transaction_reference, status)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['restaurant_id'],
            $data['subscription_id'],
            $data['amount'],
            $data['currency'] ?? 'NGN',
            $data['payment_gateway'],
            $data['transaction_reference'] ?? null,
            $data['status'] ?? 'pending'
        ]);
        
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        error_log("Error creating payment: " . $e->getMessage());
        return false;
    }
}

/**
 * Update payment status
 * 
 * @param int $paymentId
 * @param string $status
 * @param array $gatewayResponse Optional gateway response data
 * @return bool
 */
function updatePaymentStatus($paymentId, $status, $gatewayResponse = null) {
    global $pdo;
    
    if (!$pdo) return false;
    
    try {
        $sql = "UPDATE payments SET status = ?";
        $params = [$status];
        
        if ($gatewayResponse) {
            $sql .= ", gateway_response = ?";
            $params[] = json_encode($gatewayResponse);
        }
        
        if ($status === 'success') {
            $sql .= ", paid_at = NOW()";
        }
        
        $sql .= " WHERE id = ?";
        $params[] = $paymentId;
        
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    } catch (PDOException $e) {
        error_log("Error updating payment: " . $e->getMessage());
        return false;
    }
}

/**
 * Get payment history for a restaurant
 * 
 * @param int $restaurantId
 * @param int $limit
 * @return array
 */
function getPaymentHistory($restaurantId, $limit = 10) {
    global $pdo;
    
    if (!$pdo) return [];
    
    try {
        $stmt = $pdo->prepare("
            SELECT p.*, sp.name as plan_name
            FROM payments p
            LEFT JOIN subscriptions s ON p.subscription_id = s.id
            LEFT JOIN subscription_plans sp ON s.plan_id = sp.id
            WHERE p.restaurant_id = ?
            ORDER BY p.created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$restaurantId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting payment history: " . $e->getMessage());
        return [];
    }
}

/**
 * Check if subscription email was already sent
 * 
 * @param int $subscriptionId
 * @param string $emailType
 * @param int|null $daysBefore
 * @return bool
 */
function wasEmailSent($subscriptionId, $emailType, $daysBefore = null) {
    global $pdo;
    
    if (!$pdo) return false;
    
    try {
        $sql = "SELECT id FROM subscription_emails 
                WHERE subscription_id = ? 
                AND email_type = ?";
        $params = [$subscriptionId, $emailType];
        
        if ($daysBefore !== null) {
            $sql .= " AND days_before = ?";
            $params[] = $daysBefore;
        }
        
        // Only check today's emails to allow re-sending on different days
        $sql .= " AND DATE(sent_at) = CURDATE()";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (bool)$stmt->fetch();
    } catch (PDOException $e) {
        error_log('Error checking email sent status: ' . $e->getMessage());
        return false;
    }
}

/**
 * Record that an email was sent
 * 
 * @param int $subscriptionId
 * @param string $emailType
 * @param int|null $daysBefore
 * @return bool
 */
function recordEmailSent($subscriptionId, $emailType, $daysBefore = null) {
    global $pdo;
    
    if (!$pdo) return false;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO subscription_emails (subscription_id, email_type, days_before, sent_at)
            VALUES (?, ?, ?, NOW())
        ");
        return $stmt->execute([$subscriptionId, $emailType, $daysBefore]);
    } catch (PDOException $e) {
        error_log('Error recording email sent: ' . $e->getMessage());
        return false;
    }
}

/**
 * Format price for display
 * 
 * @param float $amount
 * @param string $currency
 * @return string
 */
function formatSubscriptionPrice($amount, $currency = 'NGN') {
    $symbols = [
        'NGN' => '₦',
        'USD' => '$',
        'GBP' => '£',
        'EUR' => '€'
    ];
    
    $symbol = $symbols[$currency] ?? $currency . ' ';
    return $symbol . number_format($amount, 2);
}

