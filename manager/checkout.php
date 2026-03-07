<?php
/**
 * Manager Checkout Page
 * Select plan and process payment
 */

require_once __DIR__ . '/../includes/auth.php';
requireManager();

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/subscription.php';

$pdo = getDBConnection();
$restaurantId = getCurrentUserRestaurantId();

if (!$restaurantId) {
    die('No restaurant associated with your account.');
}

// Get current subscription
$subscription = getRestaurantSubscription($restaurantId);

// Get available plans
$plans = getSubscriptionPlans(true);

// Get active payment gateways
$activeGateways = getActivePaymentGateways();

// Get selected plan
$planQuery = trim((string)($_GET['plan'] ?? ''));
$selectedPlanId = ctype_digit($planQuery) ? (int)$planQuery : 0;
$selectedPlan = null;
$requestedCycle = strtolower(trim((string)($_GET['cycle'] ?? 'monthly')));
if ($requestedCycle === 'yearly') {
    $requestedCycle = 'annual';
}
if (!in_array($requestedCycle, ['monthly', 'annual'], true)) {
    $requestedCycle = 'monthly';
}

if ($selectedPlanId > 0) {
    $selectedPlan = getSubscriptionPlan($selectedPlanId);
} elseif ($planQuery !== '') {
    $selectedPlan = getSubscriptionPlan($planQuery);
}

// Default to professional if no plan selected
if (!$selectedPlan) {
    foreach ($plans as $candidatePlan) {
        if (($candidatePlan['slug'] ?? '') === 'professional') {
            $selectedPlan = $candidatePlan;
            break;
        }
    }
}
if (!$selectedPlan && count($plans) > 0) {
    $selectedPlan = $plans[0];
}

// Get restaurant info
$restaurant = null;
if ($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM restaurants WHERE id = ?");
    $stmt->execute([$restaurantId]);
    $restaurant = $stmt->fetch();
}

// Get manager info for payment
$manager = null;
if ($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM managers WHERE id = ?");
    $stmt->execute([getCurrentUserId()]);
    $manager = $stmt->fetch();
}

$pageTitle = 'Checkout';
include __DIR__ . '/../includes/manager-layout.php';
?>

<style>
/* Align with manager layout: use var(--primary), var(--primary-dark), var(--card), var(--radius), var(--bg) */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border-radius: var(--radius);
    font-weight: 500;
    font-size: 0.875rem;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
    background: var(--primary);
    color: #fff;
}

.btn svg {
    width: 16px;
    height: 16px;
    flex-shrink: 0;
}

.btn-primary {
    background: var(--primary);
    color: #fff;
}

.btn-primary:hover {
    background: var(--primary-dark);
}

.btn-secondary {
    background: var(--bg);
    color: var(--text);
}

.btn-secondary:hover {
    background: var(--border-light);
}

.btn-pay {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    width: 100%;
    padding: 14px 24px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    color: #fff;
    border: none;
    border-radius: var(--radius);
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.2s;
    justify-content: center;
}

.btn-pay svg {
    width: 20px;
    height: 20px;
    flex-shrink: 0;
}

.btn-pay:hover:not(:disabled) {
    filter: brightness(1.08);
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.25);
}

.btn-pay:disabled {
    background: var(--muted);
    cursor: not-allowed;
    filter: none;
}

/* Checkout Page Styles */
.checkout-container {
    max-width: 1000px;
    margin: 0 auto;
}

.page-header {
    margin-bottom: 30px;
    display: flex;
    align-items: center;
    gap: 16px;
}

.btn-back {
    width: 40px;
    height: 40px;
    border-radius: var(--radius);
    background: var(--bg);
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: var(--muted);
    text-decoration: none;
}

.btn-back:hover {
    background: var(--border-light);
    color: var(--text);
}

.page-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--text);
}

/* Checkout Grid */
.checkout-grid {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 30px;
}

@media (max-width: 900px) {
    .checkout-grid {
        grid-template-columns: 1fr;
    }
}

/* Plan Selection */
.plan-selection {
    background: var(--card);
    border-radius: var(--radius);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    padding: 24px;
}

.section-title {
    font-size: 1.125rem;
    font-weight: 700;
    color: var(--text);
    margin-bottom: 20px;
}

.plan-options {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-bottom: 24px;
}

.plan-radio {
    display: none;
}

.plan-label {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px 20px;
    border: 2px solid var(--border-light);
    border-radius: var(--radius);
    cursor: pointer;
    transition: all 0.2s;
}

.plan-label:hover {
    border-color: var(--primary);
    background: var(--bg);
}

.plan-radio:checked + .plan-label {
    border-color: var(--primary);
    background: rgba(30, 58, 95, 0.08);
}

.plan-radio-circle {
    width: 20px;
    height: 20px;
    border: 2px solid var(--border-light);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.plan-radio:checked + .plan-label .plan-radio-circle {
    border-color: var(--primary);
}

.plan-radio:checked + .plan-label .plan-radio-circle::after {
    content: '';
    width: 10px;
    height: 10px;
    background: var(--primary);
    border-radius: 50%;
}

.plan-label-content {
    flex: 1;
}

.plan-label-name {
    font-weight: 600;
    color: var(--text);
    margin-bottom: 4px;
}

.plan-label-features {
    font-size: 0.8rem;
    color: var(--muted);
}

.plan-label-price {
    text-align: right;
}

.plan-label-amount {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--text);
}

.plan-label-period {
    font-size: 0.75rem;
    color: var(--muted);
}

/* Billing Cycle Toggle */
.billing-toggle {
    display: flex;
    background: var(--bg);
    border-radius: var(--radius);
    padding: 4px;
    margin-bottom: 24px;
}

.billing-option {
    flex: 1;
    padding: 12px;
    text-align: center;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
    font-size: 0.875rem;
    transition: all 0.2s;
    border: none;
    background: transparent;
    color: var(--muted);
}

.billing-option.active {
    background: var(--card);
    color: var(--text);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.billing-option .save-badge {
    display: inline-block;
    background: #d1fae5;
    color: #065f46;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 0.65rem;
    margin-left: 6px;
    font-weight: 600;
}

/* Payment Methods */
.payment-methods {
    margin-top: 24px;
    padding-top: 24px;
    border-top: 1px solid var(--border-light);
}

.gateway-options {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.gateway-radio {
    display: none;
}

.gateway-label {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px 20px;
    border: 2px solid var(--border-light);
    border-radius: var(--radius);
    cursor: pointer;
    transition: all 0.2s;
}

.gateway-label:hover {
    border-color: var(--primary);
}

.gateway-radio:checked + .gateway-label {
    border-color: var(--primary);
    background: rgba(30, 58, 95, 0.08);
}

.gateway-logo {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    color: #fff;
    font-size: 1rem;
}

.gateway-logo.paystack {
    background: linear-gradient(135deg, #00c3f7 0%, #0070ba 100%);
}

.gateway-logo.flutterwave {
    background: linear-gradient(135deg, #f5a623 0%, #e67e22 100%);
}

.gateway-name {
    font-weight: 600;
    color: var(--text);
}

.no-gateways {
    background: #fef3c7;
    border: 1px solid #fde68a;
    border-radius: 12px;
    padding: 20px;
    text-align: center;
    color: #4b5563;
}

/* Order Summary */
.order-summary {
    background: var(--card);
    border-radius: var(--radius);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    padding: 24px;
    position: sticky;
    top: 100px;
}

.summary-title {
    font-size: 1.125rem;
    font-weight: 700;
    color: var(--text);
    margin-bottom: 20px;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid var(--bg);
}

.summary-item:last-of-type {
    border-bottom: 2px solid var(--border-light);
}

.summary-label {
    color: var(--muted);
    font-size: 0.875rem;
}

.summary-value {
    font-weight: 600;
    color: var(--text);
}

.summary-total {
    display: flex;
    justify-content: space-between;
    padding: 16px 0;
    margin-bottom: 24px;
}

.summary-total .summary-label {
    font-size: 1rem;
    font-weight: 600;
    color: var(--text);
}

.summary-total .summary-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--primary);
}

.order-summary .btn-pay {
    width: 100%;
    padding: 16px;
    margin-top: 0;
}

.security-note {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin-top: 16px;
    font-size: 0.75rem;
    color: var(--muted);
}

.security-note svg {
    color: #10b981;
}

/* Features List */
.features-list {
    margin-top: 24px;
    padding-top: 24px;
    border-top: 1px solid var(--border-light);
}

.features-title {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--text);
    margin-bottom: 12px;
}

.features-list ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.features-list li {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 0;
    font-size: 0.8rem;
    color: var(--muted);
}

.features-list li svg {
    color: #10b981;
    flex-shrink: 0;
}
</style>

<div class="checkout-container">
    <!-- Page Header -->
    <div class="page-header">
        <a href="billing.php" class="btn-back">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h1 class="page-title">Checkout</h1>
            <p class="page-subtitle">Select a plan and complete your subscription payment</p>
        </div>
    </div>
    
    <form id="checkoutForm" method="POST" action="process-payment.php">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(getCSRFToken()); ?>">
        <div class="checkout-grid">
            <!-- Left Column - Plan Selection -->
            <div>
                <div class="plan-selection">
                    <h2 class="section-title">Select a Plan</h2>
                    
                    <!-- Plan Options -->
                    <div class="plan-options">
                        <?php foreach ($plans as $plan): ?>
                            <input type="radio" name="plan_id" id="plan_<?php echo $plan['id']; ?>" 
                                   value="<?php echo $plan['id']; ?>" class="plan-radio"
                                   data-monthly="<?php echo $plan['monthly_price']; ?>"
                                   data-annual="<?php echo $plan['annual_price']; ?>"
                                   data-name="<?php echo htmlspecialchars($plan['name']); ?>"
                                   <?php echo ($selectedPlan && $selectedPlan['id'] == $plan['id']) ? 'checked' : ''; ?>>
                            <label for="plan_<?php echo $plan['id']; ?>" class="plan-label">
                                <div class="plan-radio-circle"></div>
                                <div class="plan-label-content">
                                    <div class="plan-label-name"><?php echo htmlspecialchars($plan['name']); ?></div>
                                    <div class="plan-label-features">
                                        <?php echo $plan['max_categories'] == -1 ? 'Unlimited' : $plan['max_categories']; ?> categories, 
                                        <?php echo $plan['max_menu_items'] == -1 ? 'Unlimited' : $plan['max_menu_items']; ?> items
                                    </div>
                                </div>
                                <div class="plan-label-price">
                                    <div class="plan-label-amount monthly-price"><?php echo formatSubscriptionPrice($plan['monthly_price']); ?></div>
                                    <div class="plan-label-amount annual-price" style="display: none;"><?php echo formatSubscriptionPrice($plan['annual_price']); ?></div>
                                    <div class="plan-label-period">/month</div>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Billing Cycle Toggle -->
                    <h2 class="section-title">Billing Cycle</h2>
                    <div class="billing-toggle">
                        <button type="button" class="billing-option <?php echo $requestedCycle === 'monthly' ? 'active' : ''; ?>" data-cycle="monthly">
                            Monthly
                        </button>
                        <button type="button" class="billing-option <?php echo $requestedCycle === 'annual' ? 'active' : ''; ?>" data-cycle="annual">
                            Annual <span class="save-badge">Save 20%</span>
                        </button>
                    </div>
                    <input type="hidden" name="billing_cycle" id="billing_cycle" value="<?php echo htmlspecialchars($requestedCycle); ?>">
                    
                    <!-- Payment Methods -->
                    <div class="payment-methods">
                        <h2 class="section-title">Payment Method</h2>
                        
                        <?php if (empty($activeGateways)): ?>
                            <div class="no-gateways">
                                <p>No payment gateways are currently available. Please contact support.</p>
                            </div>
                        <?php else: ?>
                            <div class="gateway-options">
                                <?php foreach ($activeGateways as $gateway): ?>
                                    <input type="radio" name="gateway" id="gateway_<?php echo $gateway['gateway']; ?>" 
                                           value="<?php echo $gateway['gateway']; ?>" class="gateway-radio"
                                           <?php echo $gateway === reset($activeGateways) ? 'checked' : ''; ?>>
                                    <label for="gateway_<?php echo $gateway['gateway']; ?>" class="gateway-label">
                                        <div class="gateway-logo <?php echo $gateway['gateway']; ?>">
                                            <?php echo strtoupper(substr($gateway['gateway'], 0, 1)); ?>
                                        </div>
                                        <span class="gateway-name"><?php echo ucfirst($gateway['gateway']); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Right Column - Order Summary -->
            <div>
                <div class="order-summary">
                    <h2 class="summary-title">Order Summary</h2>
                    
                    <div class="summary-item">
                        <span class="summary-label">Plan</span>
                        <span class="summary-value" id="summary-plan"><?php echo htmlspecialchars($selectedPlan['name'] ?? 'Select a plan'); ?></span>
                    </div>
                    
                    <div class="summary-item">
                        <span class="summary-label">Billing</span>
                        <span class="summary-value" id="summary-billing">Monthly</span>
                    </div>
                    
                    <div class="summary-item">
                        <span class="summary-label">Restaurant</span>
                        <span class="summary-value"><?php echo htmlspecialchars($restaurant['name'] ?? 'N/A'); ?></span>
                    </div>
                    
                    <div class="summary-total">
                        <span class="summary-label">Total</span>
                        <span class="summary-value" id="summary-total">
                            <?php
                            $defaultAmount = 0;
                            if ($selectedPlan) {
                                $defaultAmount = $requestedCycle === 'annual' ? (float)$selectedPlan['annual_price'] : (float)$selectedPlan['monthly_price'];
                            }
                            echo formatSubscriptionPrice($defaultAmount);
                            ?>
                        </span>
                    </div>
                    
                    <button type="submit" class="btn-pay" id="payButton" <?php echo empty($activeGateways) ? 'disabled' : ''; ?>>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                        Pay Now
                    </button>
                    
                    <div class="security-note">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        Secure payment powered by trusted gateways
                    </div>
                    
                    <div class="features-list">
                        <h4 class="features-title">What you'll get:</h4>
                        <ul>
                            <li>
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Full access to all plan features
                            </li>
                            <li>
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                QR code generator for your menu
                            </li>
                            <li>
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Analytics and insights
                            </li>
                            <li>
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Cancel anytime
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// Format currency
function formatPrice(amount) {
    return '₦' + new Intl.NumberFormat('en-NG').format(amount);
}

// Get selected plan data
function getSelectedPlanData() {
    const selectedPlan = document.querySelector('input[name="plan_id"]:checked');
    if (!selectedPlan) return null;
    
    return {
        id: selectedPlan.value,
        name: selectedPlan.dataset.name,
        monthly: parseFloat(selectedPlan.dataset.monthly),
        annual: parseFloat(selectedPlan.dataset.annual)
    };
}

// Update summary
function updateSummary() {
    const plan = getSelectedPlanData();
    const billingCycle = document.getElementById('billing_cycle').value;
    
    if (!plan) return;
    
    const price = billingCycle === 'annual' ? plan.annual : plan.monthly;
    const period = billingCycle === 'annual' ? 'Annual' : 'Monthly';
    
    document.getElementById('summary-plan').textContent = plan.name;
    document.getElementById('summary-billing').textContent = period;
    document.getElementById('summary-total').textContent = formatPrice(price);
}

// Update displayed prices based on billing cycle
function updatePrices(cycle) {
    const monthlyPrices = document.querySelectorAll('.monthly-price');
    const annualPrices = document.querySelectorAll('.annual-price');
    const periods = document.querySelectorAll('.plan-label-period');
    
    if (cycle === 'annual') {
        monthlyPrices.forEach(el => el.style.display = 'none');
        annualPrices.forEach(el => el.style.display = 'block');
        periods.forEach(el => el.textContent = '/year');
    } else {
        monthlyPrices.forEach(el => el.style.display = 'block');
        annualPrices.forEach(el => el.style.display = 'none');
        periods.forEach(el => el.textContent = '/month');
    }
}

// Billing cycle toggle
document.querySelectorAll('.billing-option').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.billing-option').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        const cycle = this.dataset.cycle;
        document.getElementById('billing_cycle').value = cycle;
        
        updatePrices(cycle);
        updateSummary();
    });
});

// Plan selection
document.querySelectorAll('input[name="plan_id"]').forEach(radio => {
    radio.addEventListener('change', updateSummary);
});

// Initial update
updatePrices(document.getElementById('billing_cycle').value || 'monthly');
updateSummary();
</script>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>

