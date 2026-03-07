<?php
/**
 * Manager Billing Dashboard
 * View subscription details, usage, and payment history
 */

require_once __DIR__ . '/../includes/auth.php';
requireManager();

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/subscription.php';
require_once __DIR__ . '/../includes/subscription-middleware.php';

$pdo = getDBConnection();
$restaurantId = getCurrentUserRestaurantId();

if (!$restaurantId) {
    die('No restaurant associated with your account.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = 'Invalid security token. Please refresh and try again.';
        header('Location: billing.php');
        exit;
    }

    $currentSubscription = getRestaurantSubscription($restaurantId);
    if (!$currentSubscription) {
        $_SESSION['error'] = 'No active subscription found to update.';
        header('Location: billing.php');
        exit;
    }

    if ($action === 'schedule_change') {
        $targetPlanId = (int)($_POST['target_plan_id'] ?? 0);
        $targetCycleRaw = strtolower(trim((string)($_POST['target_cycle'] ?? 'monthly')));
        $targetCycle = $targetCycleRaw === 'annual' ? 'annual' : 'monthly';
        $targetPlan = getSubscriptionPlan($targetPlanId);

        if (!$targetPlan) {
            $_SESSION['error'] = 'Selected plan could not be found.';
            header('Location: billing.php');
            exit;
        }

        $decision = getSubscriptionChangeDecision($currentSubscription, $targetPlan, $targetCycle);
        if ($decision['mode'] === 'immediate') {
            header('Location: checkout.php?plan=' . urlencode($targetPlan['slug']) . '&cycle=' . urlencode($targetCycle));
            exit;
        }
        if ($decision['mode'] === 'none') {
            $_SESSION['info'] = 'You are already on that plan and billing cycle.';
            header('Location: billing.php');
            exit;
        }

        $effectiveAt = $currentSubscription['current_period_end'] ?? $currentSubscription['trial_ends_at'] ?? date('Y-m-d H:i:s');
        $scheduled = createOrUpdateScheduledSubscriptionChange(
            $restaurantId,
            (int)$currentSubscription['id'],
            (int)$targetPlan['id'],
            $targetCycle,
            $effectiveAt,
            $decision['type']
        );

        if ($scheduled) {
            $_SESSION['success'] = 'Plan change scheduled successfully. It will take effect at the end of your current billing period.';
        } else {
            $_SESSION['error'] = 'Unable to schedule the change. Please try again.';
        }
        header('Location: billing.php');
        exit;
    }

    if ($action === 'cancel_scheduled_change') {
        $cancelled = cancelScheduledSubscriptionChange((int)$currentSubscription['id']);
        if ($cancelled) {
            $_SESSION['success'] = 'Scheduled change cancelled.';
        } else {
            $_SESSION['error'] = 'Unable to cancel scheduled change.';
        }
        header('Location: billing.php');
        exit;
    }
}

// Handle payment callback messages from URL (in case session expired)
if (isset($_GET['payment_success']) && $_GET['payment_success'] == '1') {
    $reference = $_GET['reference'] ?? '';
    if ($reference) {
        // Verify payment was processed
        $stmt = $pdo->prepare("SELECT status FROM payments WHERE transaction_reference = ?");
        $stmt->execute([$reference]);
        $payment = $stmt->fetch();
        
        if ($payment && $payment['status'] === 'success') {
            $_SESSION['success'] = 'Payment successful! Your subscription is now active.';
            // Refresh subscription data
            $subscription = getRestaurantSubscription($restaurantId);
            $statusInfo = getSubscriptionStatusInfo($subscription);
        } else {
            $_SESSION['info'] = 'Payment is being processed. Please refresh in a moment.';
        }
    } else {
        $_SESSION['success'] = 'Payment successful! Your subscription is now active.';
    }
    
    // Redirect to remove URL parameters
    header('Location: billing.php');
    exit;
}

if (isset($_GET['payment_error']) && $_GET['payment_error'] == '1') {
    $_SESSION['error'] = $_GET['message'] ?? 'Payment processing error occurred.';
    header('Location: billing.php');
    exit;
}

if (isset($_GET['welcome']) && $_GET['welcome'] == '1') {
    $_SESSION['success'] = 'Welcome! Your 7-day Professional trial is active. Choose a paid plan anytime from this page.';
}

if (isset($_GET['upgrade_required']) && $_GET['upgrade_required'] == '1') {
    $_SESSION['error'] = 'Your trial or subscription is no longer active. Please choose a plan to continue using manager features.';
}

// Convert session messages to variables for display in layout
$message = $_SESSION['success'] ?? $_SESSION['info'] ?? '';
$error = $_SESSION['error'] ?? '';

// Clear session messages after displaying
if (isset($_SESSION['success'])) unset($_SESSION['success']);
if (isset($_SESSION['info'])) unset($_SESSION['info']);
if (isset($_SESSION['error'])) unset($_SESSION['error']);

// Get subscription data
$subscription = getRestaurantSubscription($restaurantId);
$statusInfo = getSubscriptionStatusInfo($subscription);
$plans = getSubscriptionPlans(true);
$usage = getUsageSummary($restaurantId);
$paymentHistory = getPaymentHistory($restaurantId, 3);
$activeGateways = getActivePaymentGateways();
$scheduledChange = $subscription ? getScheduledSubscriptionChange((int)$subscription['id']) : null;

// Get restaurant info
$restaurant = null;
if ($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM restaurants WHERE id = ?");
    $stmt->execute([$restaurantId]);
    $restaurant = $stmt->fetch();
}

$pageTitle = 'Billing & Subscription';
include __DIR__ . '/../includes/manager-layout.php';
?>

<style>
/* Clean Button and Icon Styles */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border-radius: 6px;
    font-weight: 500;
    font-size: 0.875rem;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
    background: #111827;
    color: #fff;
}

.btn svg {
    width: 16px;
    height: 16px;
    flex-shrink: 0;
}

.btn-primary {
    background: #111827;
    color: #fff;
}

.btn-primary:hover {
    background: #374151;
}

.btn-secondary {
    background: #f3f4f6;
    color: #111827;
}

.btn-secondary:hover {
    background: #e5e7eb;
}

.btn-small {
    padding: 6px 12px;
    font-size: 0.813rem;
}

.btn-small svg {
    width: 14px;
    height: 14px;
}

.alert {
    padding: 12px 16px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-size: 0.875rem;
}

.alert-success {
    background: #d1fae5;
    color: #065f46;
    border: 1px solid #a7f3d0;
}

.alert-error {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fecaca;
}

/* Billing Page Styles */
.page-header {
    margin-bottom: 30px;
}

.page-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--text);
    margin-bottom: 8px;
}

.page-subtitle {
    color: var(--muted);
    font-size: 0.875rem;
}

/* Current Plan Card */
.plan-card-current {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    border-radius: var(--radius);
    padding: 32px;
    color: #fff;
    margin-bottom: 30px;
    position: relative;
    overflow: hidden;
}

.plan-card-current::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 100%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
}

.plan-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 24px;
    position: relative;
    z-index: 1;
}

.plan-name-large {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 4px;
}

.plan-billing-cycle {
    opacity: 0.9;
    font-size: 0.875rem;
}

.status-badge-large {
    padding: 8px 16px;
    border-radius: 999px;
    font-size: 0.875rem;
    font-weight: 600;
    background: rgba(255,255,255,0.2);
}

.plan-price-display {
    font-size: 3rem;
    font-weight: 700;
    margin-bottom: 8px;
    position: relative;
    z-index: 1;
}

.plan-price-period {
    opacity: 0.8;
    font-size: 1rem;
    font-weight: 400;
}

.plan-details {
    display: flex;
    gap: 32px;
    margin-top: 24px;
    position: relative;
    z-index: 1;
}

.plan-detail-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.plan-detail-label {
    opacity: 0.8;
    font-size: 0.75rem;
    text-transform: uppercase;
}

.plan-detail-value {
    font-weight: 600;
    font-size: 1rem;
}

.plan-actions {
    margin-top: 24px;
    display: flex;
    gap: 12px;
    position: relative;
    z-index: 1;
}

.btn-upgrade {
    background: var(--card);
    color: var(--primary);
    border: none;
    padding: 12px 24px;
    border-radius: var(--radius);
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.2s;
}

.btn-upgrade:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.15);
}

.btn-manage {
    background: rgba(255,255,255,0.2);
    color: #fff;
    border: none;
    padding: 12px 24px;
    border-radius: var(--radius);
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
}

/* Trial Banner */
.trial-banner {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    border-radius: var(--radius);
    padding: 24px;
    margin-bottom: 30px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
    flex-wrap: wrap;
}

.trial-info {
    display: flex;
    align-items: center;
    gap: 16px;
}

.trial-icon {
    width: 48px;
    height: 48px;
    background: rgba(255,255,255,0.3);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
}

.trial-text h3 {
    font-size: 1.125rem;
    font-weight: 700;
    color: #fff;
    margin-bottom: 4px;
}

.trial-text p {
    color: rgba(255,255,255,0.9);
    font-size: 0.875rem;
}

.trial-action .btn-upgrade {
    background: var(--card);
    color: var(--primary);
}

/* Grid Layout */
.billing-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 24px;
    margin-bottom: 30px;
}

/* Usage Card */
.usage-card {
    background: var(--card);
    border-radius: var(--radius);
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
    padding: 24px;
}

.card-title {
    font-size: 1.125rem;
    font-weight: 700;
    color: var(--text);
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.card-title svg {
    color: var(--primary);
}

.usage-item {
    margin-bottom: 20px;
}

.usage-item:last-child {
    margin-bottom: 0;
}

.usage-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.usage-label {
    font-weight: 500;
    color: var(--text);
    font-size: 0.875rem;
}

.usage-value {
    font-weight: 600;
    color: var(--muted);
    font-size: 0.875rem;
}

.usage-bar {
    height: 8px;
    background: #e5e7eb;
    border-radius: 4px;
    overflow: hidden;
}

.usage-fill {
    height: 100%;
    border-radius: 4px;
    transition: width 0.5s ease;
}

.usage-fill.success { background: var(--primary); }
.usage-fill.warning { background: #b45309; }
.usage-fill.danger { background: var(--danger); }

/* Payment History */
.payment-card {
    background: var(--card);
    border-radius: var(--radius);
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
    overflow: hidden;
}

.payment-header {
    padding: 20px 24px;
    border-bottom: 1px solid var(--bg);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.payment-list {
    max-height: 400px;
    overflow-y: auto;
}

.payment-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 24px;
    border-bottom: 1px solid var(--bg);
    transition: background 0.2s;
}

.payment-item:hover {
    background: var(--bg);
}

.payment-item:last-child {
    border-bottom: none;
}

.payment-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.payment-plan {
    font-weight: 600;
    color: var(--text);
}

.payment-date {
    font-size: 0.75rem;
    color: var(--muted);
}

.payment-amount {
    text-align: right;
}

.payment-value {
    font-weight: 700;
    color: var(--text);
    font-size: 1.125rem;
}

.payment-status {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 999px;
    font-size: 0.7rem;
    font-weight: 600;
    margin-top: 4px;
}

.payment-status.success { background: #d1fae5; color: #065f46; }
.payment-status.pending { background: var(--bg); color: var(--muted); }
.payment-status.failed { background: #fee2e2; color: #991b1b; }

.empty-payments {
    padding: 40px;
    text-align: center;
    color: var(--muted);
}

/* Plans Grid */
.plans-section {
    margin-top: 40px;
}

.section-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text);
    margin-bottom: 24px;
}

.plans-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 24px;
}

.plan-option {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    padding: 24px;
    border: 2px solid transparent;
    transition: all 0.3s;
    position: relative;
}

.plan-option:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.12);
}

.plan-option.current {
    border-color: var(--primary);
}

.plan-option.popular {
    border-color: #f59e0b;
}

.popular-badge {
    position: absolute;
    top: -12px;
    left: 50%;
    transform: translateX(-50%);
    background: linear-gradient(135deg, #f59e0b, #f97316);
    color: #fff;
    padding: 4px 16px;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 600;
}

.plan-option-name {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--text);
    margin-bottom: 8px;
}

.plan-option-price {
    font-size: 2rem;
    font-weight: 700;
    color: var(--primary);
    margin-bottom: 4px;
}

.plan-option-period {
    font-size: 0.875rem;
    color: var(--muted);
    margin-bottom: 20px;
}

.plan-features {
    list-style: none;
    padding: 0;
    margin: 0 0 24px;
}

.plan-features li {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 0;
    font-size: 0.875rem;
    color: var(--text);
}

.plan-features li svg {
    color: var(--primary);
    flex-shrink: 0;
}

.billing-cycle-label {
    display: block;
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--muted);
    text-transform: uppercase;
    margin-bottom: 6px;
}

.plan-cycle-select {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid var(--bg);
    border-radius: 8px;
    font-size: 0.875rem;
    color: var(--text);
    background: var(--card);
}

.btn-select-plan {
    width: 100%;
    padding: 12px;
    border-radius: var(--radius);
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
    text-align: center;
    display: block;
}

.btn-select-plan.primary {
    background: var(--primary);
    color: #fff;
    border: none;
}

.btn-select-plan.primary:hover {
    background: var(--primary-dark);
}

.btn-select-plan.secondary {
    background: var(--bg);
    color: var(--text);
    border: none;
}

.btn-select-plan.current {
    background: var(--bg);
    color: var(--muted);
    cursor: default;
}

/* No Subscription State */
.no-subscription {
    background: var(--card);
    border-radius: var(--radius);
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
    padding: 60px 40px;
    text-align: center;
    margin-bottom: 40px;
}

.no-subscription-icon {
    width: 80px;
    height: 80px;
    background: var(--bg);
    border-radius: var(--radius);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 24px;
    color: var(--muted);
}

.no-subscription h2 {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text);
    margin-bottom: 12px;
}

.no-subscription p {
    color: var(--muted);
    margin-bottom: 24px;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
}

@media (max-width: 768px) {
    .plan-card-current {
        padding: 24px;
    }
    
    .plan-price-display {
        font-size: 2rem;
    }
    
    .plan-details {
        flex-wrap: wrap;
        gap: 16px;
    }
    
    .billing-grid {
        grid-template-columns: 1fr;
    }
    
    .trial-banner {
        flex-direction: column;
        text-align: center;
    }
}
</style>

<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">Billing & Subscription</h1>
    <p class="page-subtitle">Manage your subscription plan and view payment history</p>
</div>

<?php if ($subscription): ?>
    
    <?php if ($subscription['status'] === 'trial'): ?>
        <?php $daysLeft = getTrialDaysRemaining($subscription); ?>
        <div class="trial-banner">
            <div class="trial-info">
                <div class="trial-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="trial-text">
                    <h3><?php echo $daysLeft; ?> Days Left in Your Trial</h3>
                    <p>Your trial ends on <?php echo date('F j, Y', strtotime($subscription['trial_ends_at'])); ?>. Subscribe now to keep all features.</p>
                </div>
            </div>
            <div class="trial-action">
                <a href="checkout.php" class="btn-upgrade">Subscribe Now</a>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Current Plan Card -->
    <div class="plan-card-current">
        <div class="plan-card-header">
            <div>
                <div class="plan-name-large"><?php echo htmlspecialchars($subscription['plan_name']); ?> Plan</div>
                <div class="plan-billing-cycle"><?php echo ucfirst($subscription['billing_cycle']); ?> billing</div>
            </div>
            <span class="status-badge-large"><?php echo $statusInfo['label']; ?></span>
        </div>
        
        <?php 
        $currentPrice = $subscription['billing_cycle'] === 'annual' 
            ? $subscription['annual_price'] 
            : $subscription['monthly_price'];
        ?>
        <div class="plan-price-display">
            <?php echo formatSubscriptionPrice($currentPrice); ?>
            <span class="plan-price-period">/ <?php echo $subscription['billing_cycle'] === 'annual' ? 'year' : 'month'; ?></span>
        </div>
        
        <div class="plan-details">
            <?php if ($subscription['status'] === 'trial'): ?>
                <div class="plan-detail-item">
                    <span class="plan-detail-label">Trial Ends</span>
                    <span class="plan-detail-value"><?php echo date('M j, Y', strtotime($subscription['trial_ends_at'])); ?></span>
                </div>
            <?php elseif ($subscription['current_period_end']): ?>
                <div class="plan-detail-item">
                    <span class="plan-detail-label">Next Billing</span>
                    <span class="plan-detail-value"><?php echo date('M j, Y', strtotime($subscription['current_period_end'])); ?></span>
                </div>
            <?php endif; ?>
            <div class="plan-detail-item">
                <span class="plan-detail-label">Categories</span>
                <span class="plan-detail-value">
                    <?php echo $subscription['max_categories'] == -1 ? 'Unlimited' : $subscription['max_categories']; ?>
                </span>
            </div>
            <div class="plan-detail-item">
                <span class="plan-detail-label">Menu Items</span>
                <span class="plan-detail-value">
                    <?php echo $subscription['max_menu_items'] == -1 ? 'Unlimited' : $subscription['max_menu_items']; ?>
                </span>
            </div>
        </div>
        
        <div class="plan-actions">
            <?php if ($subscription['plan_slug'] !== 'enterprise'): ?>
                <a href="checkout.php?upgrade=1" class="btn-upgrade">Upgrade Plan</a>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($scheduledChange): ?>
        <div class="alert alert-success" style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
            <div>
                <strong>Scheduled change:</strong>
                Switch to <?php echo htmlspecialchars($scheduledChange['to_plan_name']); ?>
                (<?php echo htmlspecialchars(ucfirst($scheduledChange['to_billing_cycle'])); ?>)
                on <?php echo date('M j, Y', strtotime($scheduledChange['effective_at'])); ?>.
            </div>
            <form method="post" action="billing.php" style="margin:0;">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(getCSRFToken()); ?>">
                <input type="hidden" name="action" value="cancel_scheduled_change">
                <button type="submit" class="btn btn-secondary btn-small">Cancel</button>
            </form>
        </div>
    <?php endif; ?>
    
    <!-- Usage & Payment Grid -->
    <div class="billing-grid">
        <!-- Usage Card -->
        <div class="usage-card">
            <h3 class="card-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                Current Usage
            </h3>
            
            <?php renderUsageBar('Categories', $usage['categories']); ?>
            <?php renderUsageBar('Menu Items', $usage['menu_items']); ?>
            <?php renderUsageBar('QR Styles', $usage['qr_styles']); ?>
            <?php renderUsageBar('Templates', $usage['templates']); ?>
        </div>
        
        <!-- Payment History Card -->
        <div class="payment-card">
            <div class="payment-header">
                <h3 class="card-title" style="margin: 0;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Payment History
                </h3>
                <a href="transaction-history.php" style="font-size: 0.875rem; color: var(--primary); text-decoration: none; font-weight: 500;">View All</a>
            </div>
            
            <div class="payment-list">
                <?php if (empty($paymentHistory)): ?>
                    <div class="empty-payments">
                        <p>No payment history yet</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($paymentHistory as $payment): ?>
                        <div class="payment-item">
                            <div class="payment-info">
                                <span class="payment-plan"><?php echo htmlspecialchars($payment['plan_name'] ?? 'Subscription'); ?></span>
                                <span class="payment-date"><?php echo date('M j, Y', strtotime($payment['created_at'])); ?></span>
                            </div>
                            <div class="payment-amount">
                                <div class="payment-value"><?php echo formatSubscriptionPrice($payment['amount'], $payment['currency']); ?></div>
                                <span class="payment-status <?php echo $payment['status']; ?>"><?php echo ucfirst($payment['status']); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

<?php else: ?>
    
    <!-- No Subscription State -->
    <div class="no-subscription">
        <div class="no-subscription-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
            </svg>
        </div>
        <h2>No Active Subscription</h2>
        <p>Choose a plan to unlock all features and start managing your digital menu.</p>
        <a href="checkout.php" class="btn-upgrade" style="display: inline-block; background: var(--primary); color: #fff;">View Plans & Subscribe</a>
    </div>

<?php endif; ?>

<!-- Available Plans -->
<div class="plans-section">
    <h2 class="section-title">Available Plans</h2>
    
    <div class="plans-grid">
        <?php foreach ($plans as $plan): ?>
            <?php 
            $isCurrent = $subscription && $subscription['plan_id'] == $plan['id'];
            $isPopular = $plan['slug'] === 'professional';
            $monthlyDecision = $subscription ? getSubscriptionChangeDecision($subscription, $plan, 'monthly') : ['mode' => 'immediate', 'type' => 'new'];
            $annualDecision = $subscription ? getSubscriptionChangeDecision($subscription, $plan, 'annual') : ['mode' => 'immediate', 'type' => 'new'];
            ?>
            <div class="plan-option <?php echo $isCurrent ? 'current' : ''; ?> <?php echo $isPopular ? 'popular' : ''; ?>">
                <?php if ($isPopular): ?>
                    <span class="popular-badge">Most Popular</span>
                <?php endif; ?>
                
                <div class="plan-option-name"><?php echo htmlspecialchars($plan['name']); ?></div>
                <div class="plan-option-price"><?php echo formatSubscriptionPrice($plan['monthly_price']); ?></div>
                <div class="plan-option-period">per month (<?php echo formatSubscriptionPrice($plan['annual_price']); ?>/year)</div>
                <?php $billingDiscountPct = (int)($plan['yearly_discount_percent'] ?? 20); if ($billingDiscountPct > 0): ?>
                <div class="plan-option-save" style="font-size:0.85em;color:var(--muted);margin-top:2px;">Save <?php echo $billingDiscountPct; ?>% off</div>
                <?php endif; ?>
                
                <ul class="plan-features">
                    <li>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <?php echo $plan['max_categories'] == -1 ? 'Unlimited' : $plan['max_categories']; ?> Categories
                    </li>
                    <li>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <?php echo $plan['max_menu_items'] == -1 ? 'Unlimited' : $plan['max_menu_items']; ?> Menu Items
                    </li>
                    <li>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <?php echo $plan['max_qr_styles'] == -1 ? 'Unlimited' : $plan['max_qr_styles']; ?> QR Styles
                    </li>
                    <li>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <?php echo $plan['max_templates'] == -1 ? 'All' : $plan['max_templates']; ?> Templates
                    </li>
                </ul>

                <?php
                $currentCycle = $subscription && $isCurrent ? ($subscription['billing_cycle'] ?? 'monthly') : 'monthly';
                $defaultCycle = ($currentCycle === 'annual' ? 'annual' : 'monthly');
                ?>
                <form method="post" action="billing.php" class="plan-select-form" style="margin:0;" data-monthly-mode="<?php echo htmlspecialchars($monthlyDecision['mode']); ?>" data-annual-mode="<?php echo htmlspecialchars($annualDecision['mode']); ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(getCSRFToken()); ?>">
                    <input type="hidden" name="action" value="schedule_change">
                    <input type="hidden" name="target_plan_id" value="<?php echo (int)$plan['id']; ?>">
                    <label class="billing-cycle-label">Billing</label>
                    <select name="target_cycle" class="plan-cycle-select" required>
                        <option value="monthly" <?php echo $defaultCycle === 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                        <option value="annual" <?php echo $defaultCycle === 'annual' ? 'selected' : ''; ?>>Yearly</option>
                    </select>
                    <div class="plan-action-wrap" style="margin-top:8px;">
                        <?php
                        $decisionForDefault = $defaultCycle === 'annual' ? $annualDecision : $monthlyDecision;
                        if ($decisionForDefault['mode'] === 'none'): ?>
                            <span class="btn-select-plan current plan-action-current" style="display:inline-block;width:100%;text-align:center;">Current plan</span>
                            <button type="submit" class="btn-select-plan primary plan-action-btn" style="width:100%;border:none;cursor:pointer;display:none;">Subscribe</button>
                        <?php else: ?>
                            <span class="btn-select-plan current plan-action-current" style="display:none;width:100%;text-align:center;">Current plan</span>
                            <button type="submit" class="btn-select-plan primary plan-action-btn" style="width:100%;border:none;cursor:pointer;"><?php echo $decisionForDefault['mode'] === 'scheduled' ? 'Schedule for period end' : 'Subscribe'; ?></button>
                        <?php endif; ?>
                    </div>
                </form>

                <?php
                $changeHint = '';
                if ($isCurrent) {
                    $changeHint = 'Current plan. Upgrades are immediate; downgrades or billing-cycle changes are scheduled.';
                } elseif ($monthlyDecision['mode'] === 'scheduled' || $annualDecision['mode'] === 'scheduled') {
                    $changeHint = 'Downgrades and billing-cycle changes are scheduled for your period end.';
                } else {
                    $changeHint = 'Upgrade changes are applied immediately after successful payment.';
                }
                ?>
                <p style="font-size:0.78rem;color:var(--muted);margin-top:8px;"><?php echo htmlspecialchars($changeHint); ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
(function() {
    function updatePlanAction(form) {
        var sel = form.querySelector('.plan-cycle-select');
        var wrap = form.querySelector('.plan-action-wrap');
        if (!sel || !wrap) return;
        var mode = sel.value === 'annual' ? form.getAttribute('data-annual-mode') : form.getAttribute('data-monthly-mode');
        var currentSpan = wrap.querySelector('.plan-action-current');
        var btn = wrap.querySelector('.plan-action-btn');
        if (mode === 'none') {
            if (currentSpan) currentSpan.style.display = 'inline-block';
            if (btn) { btn.style.display = 'none'; btn.disabled = true; }
        } else {
            if (currentSpan) currentSpan.style.display = 'none';
            if (btn) {
                btn.style.display = 'block';
                btn.disabled = false;
                btn.textContent = mode === 'scheduled' ? 'Schedule for period end' : 'Subscribe';
            }
        }
    }
    document.querySelectorAll('.plan-select-form').forEach(function(form) {
        var sel = form.querySelector('.plan-cycle-select');
        if (sel) sel.addEventListener('change', function() { updatePlanAction(form); });
    });
})();
</script>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>

