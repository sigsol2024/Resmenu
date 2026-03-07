<?php
/**
 * Admin Subscription Plans Management
 * CRUD operations for subscription plans
 */

require_once __DIR__ . '/../includes/auth.php';
requireSuperAdmin();

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/subscription.php';

$pdo = getDBConnection();
$message = '';
$messageType = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create' || $action === 'update') {
        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $monthlyPrice = floatval($_POST['monthly_price'] ?? 0);
        $annualPrice = floatval($_POST['annual_price'] ?? 0);
        $maxCategories = intval($_POST['max_categories'] ?? 5);
        $maxMenuItems = intval($_POST['max_menu_items'] ?? 50);
        $maxQrStyles = intval($_POST['max_qr_styles'] ?? 3);
        $maxTemplates = intval($_POST['max_templates'] ?? 3);
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $displayOrder = intval($_POST['display_order'] ?? 0);
        
        // Handle features JSON
        $features = [
            'priority_support' => isset($_POST['feature_priority_support']),
            'custom_domain' => isset($_POST['feature_custom_domain']),
            'analytics_advanced' => isset($_POST['feature_analytics_advanced'])
        ];
        
        // Auto-generate slug if empty
        if (empty($slug)) {
            $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));
        }
        
        // Validate
        if (empty($name)) {
            $message = 'Plan name is required.';
            $messageType = 'error';
        } else {
            try {
                if ($action === 'create') {
                    $stmt = $pdo->prepare("
                        INSERT INTO subscription_plans 
                        (name, slug, description, monthly_price, annual_price, max_categories, max_menu_items, max_qr_styles, max_templates, features, is_active, display_order)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $name, $slug, $description, $monthlyPrice, $annualPrice,
                        $maxCategories, $maxMenuItems, $maxQrStyles, $maxTemplates,
                        json_encode($features), $isActive, $displayOrder
                    ]);
                    $message = 'Plan created successfully!';
                    $messageType = 'success';
                } else {
                    $planId = intval($_POST['plan_id'] ?? 0);
                    $stmt = $pdo->prepare("
                        UPDATE subscription_plans SET
                        name = ?, slug = ?, description = ?, monthly_price = ?, annual_price = ?,
                        max_categories = ?, max_menu_items = ?, max_qr_styles = ?, max_templates = ?,
                        features = ?, is_active = ?, display_order = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $name, $slug, $description, $monthlyPrice, $annualPrice,
                        $maxCategories, $maxMenuItems, $maxQrStyles, $maxTemplates,
                        json_encode($features), $isActive, $displayOrder, $planId
                    ]);
                    $message = 'Plan updated successfully!';
                    $messageType = 'success';
                }
            } catch (PDOException $e) {
                $message = 'Error: ' . $e->getMessage();
                $messageType = 'error';
            }
        }
    } elseif ($action === 'delete') {
        $planId = intval($_POST['plan_id'] ?? 0);
        try {
            // Check if plan has active subscriptions
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM subscriptions WHERE plan_id = ? AND status IN ('trial', 'active')");
            $stmt->execute([$planId]);
            $activeCount = $stmt->fetchColumn();
            
            if ($activeCount > 0) {
                $message = 'Cannot delete plan with active subscriptions. Deactivate the plan instead.';
                $messageType = 'error';
            } else {
                $stmt = $pdo->prepare("DELETE FROM subscription_plans WHERE id = ?");
                $stmt->execute([$planId]);
                $message = 'Plan deleted successfully!';
                $messageType = 'success';
            }
        } catch (PDOException $e) {
            $message = 'Error: ' . $e->getMessage();
            $messageType = 'error';
        }
    } elseif ($action === 'toggle_status') {
        $planId = intval($_POST['plan_id'] ?? 0);
        try {
            $stmt = $pdo->prepare("UPDATE subscription_plans SET is_active = NOT is_active WHERE id = ?");
            $stmt->execute([$planId]);
            $message = 'Plan status updated!';
            $messageType = 'success';
        } catch (PDOException $e) {
            $message = 'Error: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// Get all plans
$plans = getSubscriptionPlans(false);

// Get plan for editing
$editPlan = null;
if (isset($_GET['edit'])) {
    $editPlan = getSubscriptionPlan(intval($_GET['edit']));
}

$pageTitle = 'Subscription Plans';
include __DIR__ . '/../includes/admin-layout.php';
?>

<style>
/* Clean Subscription Plans Styles */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    flex-wrap: wrap;
    gap: 16px;
}

.page-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--text);
    margin: 0;
}

.alert {
    padding: 12px 16px;
    border-radius: 6px;
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

.btn-primary {
    background: #111827;
    color: #fff;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
    font-size: 0.875rem;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: background 0.2s;
}

.btn-primary:hover {
    background: #374151;
}

.btn-primary svg {
    width: 18px;
    height: 18px;
}

/* Form Card */
.form-card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    padding: 24px;
    margin-bottom: 24px;
}

.form-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--text);
    margin-bottom: 24px;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    font-weight: 500;
    color: #374151;
    margin-bottom: 6px;
    font-size: 0.875rem;
}

.form-group input,
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 0.875rem;
    transition: border-color 0.2s;
    background: #fff;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
    outline: none;
    border-color: #111827;
}

.form-group textarea {
    resize: vertical;
    min-height: 80px;
}

.form-hint {
    font-size: 0.75rem;
    color: #6b7280;
    margin-top: 4px;
}

.section-title {
    font-size: 0.875rem;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 16px;
    padding-bottom: 8px;
    border-bottom: 1px solid #e5e7eb;
}

.checkbox-group {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
}

.checkbox-item {
    display: flex;
    align-items: center;
    gap: 8px;
}

.checkbox-item input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.checkbox-item label {
    margin: 0;
    cursor: pointer;
    font-weight: 400;
}

.form-actions {
    display: flex;
    gap: 12px;
    margin-top: 24px;
    padding-top: 24px;
    border-top: 1px solid #e5e7eb;
}

.btn-secondary {
    background: #f3f4f6;
    color: #374151;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
    font-size: 0.875rem;
    text-decoration: none;
    transition: background 0.2s;
}

.btn-secondary:hover {
    background: #e5e7eb;
}

/* Plans Grid */
.plans-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 20px;
}

.plan-card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    /* Allow actions dropdown to render outside the card */
    overflow: visible;
    position: relative;
    border: 1px solid #e5e7eb;
}

.plan-card.inactive {
    opacity: 0.7;
}

.plan-header {
    padding: 20px;
    background: #f9fafb;
    border-bottom: 1px solid #e5e7eb;
}

.plan-name {
    font-size: 1.25rem;
    font-weight: 600;
    color: #111827;
    margin-bottom: 4px;
}

.plan-description {
    font-size: 0.875rem;
    color: #6b7280;
}

.status-badge {
    position: absolute;
    top: 16px;
    right: 16px;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 500;
}

.status-active {
    background: #d1fae5;
    color: #065f46;
}

.status-inactive {
    background: #f3f4f6;
    color: #6b7280;
}

.plan-pricing {
    padding: 20px;
    background: #fff;
    border-bottom: 1px solid #e5e7eb;
}

.price-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}

.price-row:last-child {
    margin-bottom: 0;
}

.price-label {
    color: #6b7280;
    font-size: 0.875rem;
}

.price-value {
    font-size: 1.125rem;
    font-weight: 600;
    color: #111827;
}

.plan-limits {
    padding: 20px;
}

.limit-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid #f3f4f6;
}

.limit-item:last-child {
    border-bottom: none;
}

.limit-label {
    color: #6b7280;
    font-size: 0.875rem;
}

.limit-value {
    font-weight: 500;
    color: #111827;
}

.limit-unlimited {
    color: #059669;
}

.plan-features {
    padding: 0 20px 20px;
}

.feature-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 500;
    margin-right: 8px;
    margin-bottom: 8px;
}

.feature-enabled {
    background: #d1fae5;
    color: #065f46;
}

.feature-disabled {
    background: #f3f4f6;
    color: #6b7280;
}

.plan-actions {
    padding: 16px 20px;
    border-top: 1px solid #e5e7eb;
    display: flex;
    gap: 8px;
    background: #f9fafb;
}

.btn-sm {
    padding: 8px 14px;
    border-radius: 6px;
    font-size: 0.813rem;
    font-weight: 500;
    cursor: pointer;
    border: none;
    transition: background 0.2s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.btn-edit {
    background: #fff;
    color: #374151;
    border: 1px solid #d1d5db;
}

.btn-edit:hover {
    background: #f9fafb;
}

.btn-toggle {
    background: #fff;
    color: #4b5563;
    border: 1px solid #e5e7eb;
}

.btn-toggle:hover {
    background: #fef3c7;
}

.btn-delete {
    background: #fff;
    color: #991b1b;
    border: 1px solid #fecaca;
}

.btn-delete:hover {
    background: #fee2e2;
}

.btn-sm svg {
    width: 16px;
    height: 16px;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .form-card {
        padding: 20px 16px;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .plans-grid {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .btn-primary,
    .btn-secondary {
        width: 100%;
        justify-content: center;
    }
}
</style>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'error'; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<!-- Page Header -->
<div class="page-header">
    <div>
        <h1 class="page-title">Subscription Plans</h1>
        <p class="page-subtitle">Create and manage subscription plans for restaurants</p>
    </div>
    <?php if (!$editPlan): ?>
        <a href="?new=1" class="btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add New Plan
        </a>
    <?php endif; ?>
</div>

<!-- Create/Edit Form -->
<?php if (isset($_GET['new']) || $editPlan): ?>
<div class="form-card">
    <h2 class="form-title"><?php echo $editPlan ? 'Edit Plan: ' . htmlspecialchars($editPlan['name']) : 'Create New Plan'; ?></h2>
    
    <form method="POST" action="">
        <input type="hidden" name="action" value="<?php echo $editPlan ? 'update' : 'create'; ?>">
        <?php if ($editPlan): ?>
            <input type="hidden" name="plan_id" value="<?php echo $editPlan['id']; ?>">
        <?php endif; ?>
        
        <!-- Basic Info -->
        <div class="section-title">Basic Information</div>
        <div class="form-grid">
            <div class="form-group">
                <label for="name">Plan Name *</label>
                <input type="text" id="name" name="name" required 
                       value="<?php echo htmlspecialchars($editPlan['name'] ?? ''); ?>"
                       placeholder="e.g., Basic, Professional, Enterprise">
            </div>
            
            <div class="form-group">
                <label for="slug">Slug</label>
                <input type="text" id="slug" name="slug" 
                       value="<?php echo htmlspecialchars($editPlan['slug'] ?? ''); ?>"
                       placeholder="auto-generated if empty">
                <div class="form-hint">URL-friendly identifier (lowercase, no spaces)</div>
            </div>
            
            <div class="form-group" style="grid-column: 1 / -1;">
                <label for="description">Description</label>
                <textarea id="description" name="description" 
                          placeholder="Brief description of what this plan offers"><?php echo htmlspecialchars($editPlan['description'] ?? ''); ?></textarea>
            </div>
        </div>
        
        <!-- Pricing -->
        <div class="section-title">Pricing (NGN)</div>
        <div class="form-grid">
            <div class="form-group">
                <label for="monthly_price">Monthly Price (₦)</label>
                <input type="number" id="monthly_price" name="monthly_price" min="0" step="0.01"
                       value="<?php echo $editPlan['monthly_price'] ?? '10000'; ?>">
            </div>
            
            <div class="form-group">
                <label for="annual_price">Annual Price (₦)</label>
                <input type="number" id="annual_price" name="annual_price" min="0" step="0.01"
                       value="<?php echo $editPlan['annual_price'] ?? '96000'; ?>">
                <div class="form-hint">Typically 20% off monthly × 12</div>
            </div>
        </div>
        
        <!-- Feature Limits -->
        <div class="section-title">Feature Limits</div>
        <div class="form-grid">
            <div class="form-group">
                <label for="max_categories">Max Categories</label>
                <input type="number" id="max_categories" name="max_categories" min="-1"
                       value="<?php echo $editPlan['max_categories'] ?? '5'; ?>">
                <div class="form-hint">-1 for unlimited</div>
            </div>
            
            <div class="form-group">
                <label for="max_menu_items">Max Menu Items</label>
                <input type="number" id="max_menu_items" name="max_menu_items" min="-1"
                       value="<?php echo $editPlan['max_menu_items'] ?? '50'; ?>">
                <div class="form-hint">-1 for unlimited</div>
            </div>
            
            <div class="form-group">
                <label for="max_qr_styles">Max QR Styles</label>
                <input type="number" id="max_qr_styles" name="max_qr_styles" min="-1"
                       value="<?php echo $editPlan['max_qr_styles'] ?? '3'; ?>">
                <div class="form-hint">-1 for unlimited</div>
            </div>
            
            <div class="form-group">
                <label for="max_templates">Max Templates</label>
                <input type="number" id="max_templates" name="max_templates" min="-1"
                       value="<?php echo $editPlan['max_templates'] ?? '3'; ?>">
                <div class="form-hint">-1 for unlimited</div>
            </div>
        </div>
        
        <!-- Additional Features -->
        <div class="section-title">Additional Features</div>
        <div class="form-group">
            <div class="checkbox-group">
                <?php 
                $features = $editPlan['features'] ?? [];
                ?>
                <div class="checkbox-item">
                    <input type="checkbox" id="feature_priority_support" name="feature_priority_support"
                           <?php echo !empty($features['priority_support']) ? 'checked' : ''; ?>>
                    <label for="feature_priority_support">Priority Support</label>
                </div>
                
                <div class="checkbox-item">
                    <input type="checkbox" id="feature_custom_domain" name="feature_custom_domain"
                           <?php echo !empty($features['custom_domain']) ? 'checked' : ''; ?>>
                    <label for="feature_custom_domain">Custom Domain</label>
                </div>
                
                <div class="checkbox-item">
                    <input type="checkbox" id="feature_analytics_advanced" name="feature_analytics_advanced"
                           <?php echo !empty($features['analytics_advanced']) ? 'checked' : ''; ?>>
                    <label for="feature_analytics_advanced">Advanced Analytics</label>
                </div>
            </div>
        </div>
        
        <!-- Settings -->
        <div class="section-title">Settings</div>
        <div class="form-grid">
            <div class="form-group">
                <label for="display_order">Display Order</label>
                <input type="number" id="display_order" name="display_order" min="0"
                       value="<?php echo $editPlan['display_order'] ?? '0'; ?>">
                <div class="form-hint">Lower numbers appear first</div>
            </div>
            
            <div class="form-group">
                <div class="checkbox-item" style="margin-top: 32px;">
                    <input type="checkbox" id="is_active" name="is_active"
                           <?php echo ($editPlan['is_active'] ?? 1) ? 'checked' : ''; ?>>
                    <label for="is_active">Plan is Active</label>
                </div>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn-primary">
                <?php echo $editPlan ? 'Update Plan' : 'Create Plan'; ?>
            </button>
            <a href="subscription-plans.php" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
<?php endif; ?>

<!-- Plans List -->
<div class="plans-grid">
    <?php foreach ($plans as $plan): ?>
        <?php 
        $features = $plan['features'] ?? [];
        ?>
        <div class="plan-card <?php echo !$plan['is_active'] ? 'inactive' : ''; ?>">
            <span class="status-badge <?php echo $plan['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                <?php echo $plan['is_active'] ? 'Active' : 'Inactive'; ?>
            </span>
            
            <div class="plan-header">
                <div class="plan-name"><?php echo htmlspecialchars($plan['name']); ?></div>
                <div class="plan-description"><?php echo htmlspecialchars($plan['description'] ?? ''); ?></div>
            </div>
            
            <div class="plan-pricing">
                <div class="price-row">
                    <span class="price-label">Monthly</span>
                    <span class="price-value"><?php echo formatSubscriptionPrice($plan['monthly_price']); ?></span>
                </div>
                <div class="price-row">
                    <span class="price-label">Annually</span>
                    <span class="price-value"><?php echo formatSubscriptionPrice($plan['annual_price']); ?></span>
                </div>
            </div>
            
            <div class="plan-limits">
                <div class="limit-item">
                    <span class="limit-label">Categories</span>
                    <span class="limit-value <?php echo $plan['max_categories'] == -1 ? 'limit-unlimited' : ''; ?>">
                        <?php echo $plan['max_categories'] == -1 ? 'Unlimited' : $plan['max_categories']; ?>
                    </span>
                </div>
                <div class="limit-item">
                    <span class="limit-label">Menu Items</span>
                    <span class="limit-value <?php echo $plan['max_menu_items'] == -1 ? 'limit-unlimited' : ''; ?>">
                        <?php echo $plan['max_menu_items'] == -1 ? 'Unlimited' : $plan['max_menu_items']; ?>
                    </span>
                </div>
                <div class="limit-item">
                    <span class="limit-label">QR Styles</span>
                    <span class="limit-value <?php echo $plan['max_qr_styles'] == -1 ? 'limit-unlimited' : ''; ?>">
                        <?php echo $plan['max_qr_styles'] == -1 ? 'Unlimited' : $plan['max_qr_styles']; ?>
                    </span>
                </div>
                <div class="limit-item">
                    <span class="limit-label">Templates</span>
                    <span class="limit-value <?php echo $plan['max_templates'] == -1 ? 'limit-unlimited' : ''; ?>">
                        <?php echo $plan['max_templates'] == -1 ? 'Unlimited' : $plan['max_templates']; ?>
                    </span>
                </div>
            </div>
            
            <div class="plan-features">
                <span class="feature-badge <?php echo !empty($features['priority_support']) ? 'feature-enabled' : 'feature-disabled'; ?>">
                    <?php echo !empty($features['priority_support']) ? '✓' : '✗'; ?> Priority Support
                </span>
                <span class="feature-badge <?php echo !empty($features['custom_domain']) ? 'feature-enabled' : 'feature-disabled'; ?>">
                    <?php echo !empty($features['custom_domain']) ? '✓' : '✗'; ?> Custom Domain
                </span>
                <span class="feature-badge <?php echo !empty($features['analytics_advanced']) ? 'feature-enabled' : 'feature-disabled'; ?>">
                    <?php echo !empty($features['analytics_advanced']) ? '✓' : '✗'; ?> Advanced Analytics
                </span>
            </div>
            
            <div class="plan-actions actions-cell" style="position:relative;">
                <button class="actions-btn" onclick="toggleDropdown(this)" title="Actions">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                    </svg>
                </button>
                <div class="actions-dropdown">
                    <a href="?edit=<?php echo $plan['id']; ?>" class="actions-dropdown-item">Edit</a>
                    <form method="POST" style="display:contents;">
                        <input type="hidden" name="action" value="toggle_status">
                        <input type="hidden" name="plan_id" value="<?php echo $plan['id']; ?>">
                        <button type="submit" class="actions-dropdown-item"><?php echo $plan['is_active'] ? 'Deactivate' : 'Activate'; ?></button>
                    </form>
                    <div class="actions-dropdown-divider"></div>
                    <form method="POST" style="display:contents;" onsubmit="return confirm('Are you sure you want to delete this plan?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="plan_id" value="<?php echo $plan['id']; ?>">
                        <button type="submit" class="actions-dropdown-item danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php if (empty($plans)): ?>
<div class="form-card" style="text-align: center; padding: 60px;">
    <p style="color: #6b7280; margin-bottom: 20px;">No subscription plans found.</p>
    <a href="?new=1" class="btn-primary">Create Your First Plan</a>
</div>
<?php endif; ?>

<script>
function toggleDropdown(btn) {
    document.querySelectorAll('.actions-dropdown.show').forEach(d => d.classList.remove('show'));
    const dropdown = btn.nextElementSibling;
    dropdown.classList.toggle('show');
    document.addEventListener('click', function closeDropdown(e) {
        if (!btn.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.remove('show');
            document.removeEventListener('click', closeDropdown);
        }
    });
}
</script>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
