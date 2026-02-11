<?php
/**
 * Subscription Middleware
 * 
 * Enforces subscription limits and feature restrictions
 */

require_once __DIR__ . '/subscription.php';

/**
 * Check if restaurant can add more categories
 * 
 * @param int $restaurantId
 * @return array ['allowed' => bool, 'message' => string, 'usage' => array]
 */
function canAddCategory($restaurantId) {
    $usage = getRemainingUsage($restaurantId, 'categories');
    
    if ($usage['unlimited']) {
        return [
            'allowed' => true,
            'message' => '',
            'usage' => $usage
        ];
    }
    
    if ($usage['remaining'] <= 0) {
        return [
            'allowed' => false,
            'message' => "You've reached your category limit ({$usage['limit']}). Please upgrade your plan to add more categories.",
            'usage' => $usage
        ];
    }
    
    return [
        'allowed' => true,
        'message' => '',
        'usage' => $usage
    ];
}

/**
 * Check if restaurant can add more menu items
 * 
 * @param int $restaurantId
 * @return array ['allowed' => bool, 'message' => string, 'usage' => array]
 */
function canAddMenuItem($restaurantId) {
    $usage = getRemainingUsage($restaurantId, 'menu_items');
    
    if ($usage['unlimited']) {
        return [
            'allowed' => true,
            'message' => '',
            'usage' => $usage
        ];
    }
    
    if ($usage['remaining'] <= 0) {
        return [
            'allowed' => false,
            'message' => "You've reached your menu item limit ({$usage['limit']}). Please upgrade your plan to add more items.",
            'usage' => $usage
        ];
    }
    
    return [
        'allowed' => true,
        'message' => '',
        'usage' => $usage
    ];
}

/**
 * Check if restaurant can use a specific QR template
 * 
 * @param int $restaurantId
 * @param int $templateId (optional) Specific template to check
 * @return array ['allowed' => bool, 'message' => string, 'usage' => array]
 */
function canUseQrStyle($restaurantId, $templateId = null) {
    $usage = getRemainingUsage($restaurantId, 'qr_styles');
    
    if ($usage['unlimited']) {
        return [
            'allowed' => true,
            'message' => '',
            'usage' => $usage
        ];
    }
    
    // For now, just check the limit
    // In a more advanced implementation, you could restrict specific premium templates
    if ($usage['used'] >= $usage['limit']) {
        return [
            'allowed' => false,
            'message' => "You've reached your QR style limit ({$usage['limit']}). Please upgrade your plan to access more styles.",
            'usage' => $usage
        ];
    }
    
    return [
        'allowed' => true,
        'message' => '',
        'usage' => $usage
    ];
}

/**
 * Check if restaurant can use a specific menu template
 * 
 * @param int $restaurantId
 * @param int $templateId (optional) Specific template to check
 * @return array ['allowed' => bool, 'message' => string, 'available_templates' => array]
 */
function canUseTemplate($restaurantId, $templateId = null) {
    global $pdo;
    
    $subscription = getRestaurantSubscription($restaurantId);
    
    if (!$subscription) {
        return [
            'allowed' => false,
            'message' => 'No active subscription found.',
            'available_templates' => []
        ];
    }
    
    $maxTemplates = (int)$subscription['max_templates'];
    
    // Unlimited templates
    if ($maxTemplates === -1) {
        return [
            'allowed' => true,
            'message' => '',
            'available_templates' => []
        ];
    }
    
    // Get available templates based on limit
    try {
        $stmt = $pdo->prepare("SELECT id, name FROM templates WHERE is_active = 1 ORDER BY id LIMIT ?");
        $stmt->execute([$maxTemplates]);
        $availableTemplates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $availableIds = array_column($availableTemplates, 'id');
        
        if ($templateId !== null && !in_array($templateId, $availableIds)) {
            return [
                'allowed' => false,
                'message' => "This template is not available on your current plan. Please upgrade to access more templates.",
                'available_templates' => $availableTemplates
            ];
        }
        
        return [
            'allowed' => true,
            'message' => '',
            'available_templates' => $availableTemplates
        ];
    } catch (PDOException $e) {
        return [
            'allowed' => true,
            'message' => '',
            'available_templates' => []
        ];
    }
}

/**
 * Check if subscription is active and not expired
 * Returns lockout page HTML if subscription is not valid
 * 
 * @param int $restaurantId
 * @return array ['valid' => bool, 'subscription' => array|null, 'lockout_reason' => string]
 */
function checkSubscriptionAccess($restaurantId) {
    $subscription = getRestaurantSubscription($restaurantId);
    
    // No subscription at all
    if (!$subscription) {
        return [
            'valid' => false,
            'subscription' => null,
            'lockout_reason' => 'no_subscription',
            'message' => 'Please subscribe to a plan to access this feature.'
        ];
    }
    
    // Check trial
    if ($subscription['status'] === 'trial') {
        if (isTrialActive($subscription)) {
            return [
                'valid' => true,
                'subscription' => $subscription,
                'lockout_reason' => null,
                'message' => ''
            ];
        } else {
            return [
                'valid' => false,
                'subscription' => $subscription,
                'lockout_reason' => 'trial_expired',
                'message' => 'Your trial period has ended. Please subscribe to continue using the platform.'
            ];
        }
    }
    
    // Check active subscription
    if ($subscription['status'] === 'active') {
        // Check if period has ended
        if ($subscription['current_period_end'] && strtotime($subscription['current_period_end']) < time()) {
            return [
                'valid' => false,
                'subscription' => $subscription,
                'lockout_reason' => 'subscription_expired',
                'message' => 'Your subscription has expired. Please renew to continue using the platform.'
            ];
        }
        
        return [
            'valid' => true,
            'subscription' => $subscription,
            'lockout_reason' => null,
            'message' => ''
        ];
    }
    
    // Expired or cancelled
    if ($subscription['status'] === 'expired') {
        return [
            'valid' => false,
            'subscription' => $subscription,
            'lockout_reason' => 'subscription_expired',
            'message' => 'Your subscription has expired. Please renew to continue using the platform.'
        ];
    }
    
    if ($subscription['status'] === 'cancelled') {
        return [
            'valid' => false,
            'subscription' => $subscription,
            'lockout_reason' => 'subscription_cancelled',
            'message' => 'Your subscription has been cancelled. Please subscribe again to access this feature.'
        ];
    }
    
    if ($subscription['status'] === 'pending') {
        return [
            'valid' => false,
            'subscription' => $subscription,
            'lockout_reason' => 'payment_pending',
            'message' => 'Your payment is being processed. Please wait or contact support if this persists.'
        ];
    }
    
    return [
        'valid' => false,
        'subscription' => $subscription,
        'lockout_reason' => 'unknown',
        'message' => 'There was an issue with your subscription. Please contact support.'
    ];
}

/**
 * Render subscription lockout page
 * 
 * @param array $access The access check result
 * @param string $returnUrl URL to return to after subscribing
 */
function renderLockoutPage($access, $returnUrl = '/manager/billing.php') {
    $reason = $access['lockout_reason'];
    $message = $access['message'];
    $subscription = $access['subscription'];
    
    $titles = [
        'no_subscription' => 'Subscription Required',
        'trial_expired' => 'Trial Period Ended',
        'subscription_expired' => 'Subscription Expired',
        'subscription_cancelled' => 'Subscription Cancelled',
        'payment_pending' => 'Payment Pending',
        'unknown' => 'Access Restricted'
    ];
    
    $title = $titles[$reason] ?? 'Access Restricted';
    
    ?>
    <div class="lockout-container">
        <div class="lockout-card">
            <div class="lockout-icon">
                <?php if ($reason === 'trial_expired' || $reason === 'subscription_expired'): ?>
                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                <?php elseif ($reason === 'payment_pending'): ?>
                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                <?php else: ?>
                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                <?php endif; ?>
            </div>
            
            <h1 class="lockout-title"><?php echo htmlspecialchars($title); ?></h1>
            <p class="lockout-message"><?php echo htmlspecialchars($message); ?></p>
            
            <?php if ($subscription && $subscription['status'] === 'trial'): ?>
                <p class="lockout-info">
                    Your trial ended on <?php echo date('F j, Y', strtotime($subscription['trial_ends_at'])); ?>.
                </p>
            <?php elseif ($subscription && $subscription['current_period_end']): ?>
                <p class="lockout-info">
                    Your subscription ended on <?php echo date('F j, Y', strtotime($subscription['current_period_end'])); ?>.
                </p>
            <?php endif; ?>
            
            <div class="lockout-actions">
                <a href="<?php echo htmlspecialchars($returnUrl); ?>" class="btn-subscribe">
                    <?php echo $reason === 'no_subscription' ? 'View Plans & Subscribe' : 'Renew Subscription'; ?>
                </a>
                <a href="/manager/dashboard.php" class="btn-back">Back to Dashboard</a>
            </div>
        </div>
    </div>
    
    <style>
    .lockout-container {
        min-height: 60vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 20px;
    }
    
    .lockout-card {
        background: #fff;
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
        padding: 48px;
        text-align: center;
        max-width: 500px;
        width: 100%;
    }
    
    .lockout-icon {
        color: #f59e0b;
        margin-bottom: 24px;
    }
    
    .lockout-title {
        font-size: 1.75rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 12px;
    }
    
    .lockout-message {
        color: #6b7280;
        font-size: 1rem;
        line-height: 1.6;
        margin-bottom: 16px;
    }
    
    .lockout-info {
        color: #9ca3af;
        font-size: 0.875rem;
        margin-bottom: 32px;
    }
    
    .lockout-actions {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    
    .btn-subscribe {
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        color: #fff;
        padding: 16px 32px;
        border-radius: 12px;
        text-decoration: none;
        font-weight: 600;
        font-size: 1rem;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .btn-subscribe:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(99, 102, 241, 0.3);
    }
    
    .btn-back {
        color: #6b7280;
        text-decoration: none;
        font-size: 0.875rem;
        padding: 12px;
    }
    
    .btn-back:hover {
        color: #374151;
    }
    </style>
    <?php
}

/**
 * Get usage summary for dashboard display
 * 
 * @param int $restaurantId
 * @return array
 */
function getUsageSummary($restaurantId) {
    return [
        'categories' => getRemainingUsage($restaurantId, 'categories'),
        'menu_items' => getRemainingUsage($restaurantId, 'menu_items'),
        'qr_styles' => getRemainingUsage($restaurantId, 'qr_styles'),
        'templates' => getRemainingUsage($restaurantId, 'templates')
    ];
}

/**
 * Render upgrade prompt when limit is reached
 * 
 * @param string $feature Feature name
 * @param array $usage Usage data
 */
function renderUpgradePrompt($feature, $usage) {
    $featureNames = [
        'categories' => 'categories',
        'menu_items' => 'menu items',
        'qr_styles' => 'QR styles',
        'templates' => 'templates'
    ];
    
    $featureName = $featureNames[$feature] ?? $feature;
    ?>
    <div class="upgrade-prompt">
        <div class="upgrade-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
            </svg>
        </div>
        <div class="upgrade-content">
            <h4>Limit Reached</h4>
            <p>You're using <?php echo $usage['used']; ?> of <?php echo $usage['limit']; ?> <?php echo $featureName; ?>.</p>
            <a href="/manager/billing.php" class="btn-upgrade">Upgrade Plan</a>
        </div>
    </div>
    
    <style>
    .upgrade-prompt {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        border-radius: 12px;
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 20px;
        border: 1px solid #fbbf24;
    }
    
    .upgrade-icon {
        background: #fff;
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #1e3a5f;
        flex-shrink: 0;
    }
    
    .upgrade-content h4 {
        font-size: 1rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 4px;
    }
    
    .upgrade-content p {
        font-size: 0.875rem;
        color: #6b7280;
        margin-bottom: 12px;
    }
    
    .btn-upgrade {
        display: inline-block;
        background: #1e3a5f;
        color: #fff;
        padding: 8px 16px;
        border-radius: 6px;
        text-decoration: none;
        font-size: 0.875rem;
        font-weight: 500;
    }
    
    .btn-upgrade:hover {
        background: #2d4a6f;
    }
    </style>
    <?php
}

/**
 * Render usage bar for dashboard
 * 
 * @param string $label
 * @param array $usage
 */
function renderUsageBar($label, $usage) {
    if ($usage['unlimited']) {
        $percentage = 0;
        $display = 'Unlimited';
    } else {
        $percentage = ($usage['used'] / max(1, $usage['limit'])) * 100;
        $display = "{$usage['used']} / {$usage['limit']}";
    }
    
    $colorClass = $percentage >= 90 ? 'danger' : ($percentage >= 70 ? 'warning' : 'success');
    ?>
    <div class="usage-item">
        <div class="usage-header">
            <span class="usage-label"><?php echo htmlspecialchars($label); ?></span>
            <span class="usage-value"><?php echo $display; ?></span>
        </div>
        <?php if (!$usage['unlimited']): ?>
            <div class="usage-bar">
                <div class="usage-fill <?php echo $colorClass; ?>" style="width: <?php echo min(100, $percentage); ?>%"></div>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

