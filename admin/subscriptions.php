<?php
/**
 * Admin Subscriptions Management
 * View and manage restaurant subscriptions
 */

require_once __DIR__ . '/../includes/auth.php';
requireSuperAdmin();

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/subscription.php';

$pdo = getDBConnection();
$message = '';
$messageType = '';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_status') {
        $subscriptionId = intval($_POST['subscription_id'] ?? 0);
        $newStatus = $_POST['new_status'] ?? '';
        
        $validStatuses = ['trial', 'active', 'expired', 'cancelled', 'pending'];
        
        if ($subscriptionId > 0 && in_array($newStatus, $validStatuses)) {
            $additionalData = [];
            
            // If activating, set period dates
            if ($newStatus === 'active') {
                // Get the subscription to determine billing cycle
                $stmt = $pdo->prepare("SELECT billing_cycle FROM subscriptions WHERE id = ?");
                $stmt->execute([$subscriptionId]);
                $sub = $stmt->fetch();
                
                if ($sub) {
                    $periodEnd = $sub['billing_cycle'] === 'annual' 
                        ? date('Y-m-d H:i:s', strtotime('+1 year'))
                        : date('Y-m-d H:i:s', strtotime('+1 month'));
                    
                    $additionalData['current_period_start'] = date('Y-m-d H:i:s');
                    $additionalData['current_period_end'] = $periodEnd;
                }
            }
            
            if (updateSubscriptionStatus($subscriptionId, $newStatus, $additionalData)) {
                $message = 'Subscription status updated successfully!';
                $messageType = 'success';
            } else {
                $message = 'Failed to update subscription status.';
                $messageType = 'error';
            }
        }
    } elseif ($action === 'extend_period') {
        $subscriptionId = intval($_POST['subscription_id'] ?? 0);
        $days = intval($_POST['days'] ?? 0);
        
        if ($subscriptionId > 0 && $days > 0) {
            try {
                $stmt = $pdo->prepare("
                    UPDATE subscriptions 
                    SET current_period_end = DATE_ADD(COALESCE(current_period_end, NOW()), INTERVAL ? DAY)
                    WHERE id = ?
                ");
                $stmt->execute([$days, $subscriptionId]);
                $message = "Subscription extended by {$days} days!";
                $messageType = 'success';
            } catch (PDOException $e) {
                $message = 'Failed to extend subscription.';
                $messageType = 'error';
            }
        }
    } elseif ($action === 'change_plan') {
        $subscriptionId = intval($_POST['subscription_id'] ?? 0);
        $newPlanId = intval($_POST['new_plan_id'] ?? 0);
        
        if ($subscriptionId > 0 && $newPlanId > 0) {
            try {
                // Verify plan exists
                $stmt = $pdo->prepare("SELECT id FROM subscription_plans WHERE id = ?");
                $stmt->execute([$newPlanId]);
                if (!$stmt->fetch()) {
                    $message = 'Selected plan does not exist.';
                    $messageType = 'error';
                } else {
                    // Update subscription plan
                    $stmt = $pdo->prepare("UPDATE subscriptions SET plan_id = ? WHERE id = ?");
                    $stmt->execute([$newPlanId, $subscriptionId]);
                    $message = 'Subscription plan updated successfully!';
                    $messageType = 'success';
                }
            } catch (PDOException $e) {
                $message = 'Failed to change subscription plan: ' . $e->getMessage();
                $messageType = 'error';
            }
        } else {
            $message = 'Invalid subscription or plan ID.';
            $messageType = 'error';
        }
    }
}

// Filters
$statusFilter = $_GET['status'] ?? '';
$searchQuery = trim($_GET['search'] ?? '');
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 15;
$offset = ($page - 1) * $perPage;

// Build query
$where = [];
$params = [];

if (!empty($statusFilter)) {
    $where[] = "s.status = ?";
    $params[] = $statusFilter;
}

if (!empty($searchQuery)) {
    $where[] = "(r.name LIKE ? OR r.slug LIKE ?)";
    $params[] = "%{$searchQuery}%";
    $params[] = "%{$searchQuery}%";
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Get total count
$countSql = "SELECT COUNT(*) FROM subscriptions s JOIN restaurants r ON s.restaurant_id = r.id {$whereClause}";
$stmt = $pdo->prepare($countSql);
$stmt->execute($params);
$totalCount = $stmt->fetchColumn();
$totalPages = ceil($totalCount / $perPage);

// Get subscriptions
$sql = "
    SELECT s.*, r.name as restaurant_name, r.slug as restaurant_slug, r.email as restaurant_email,
           p.name as plan_name, p.slug as plan_slug, p.monthly_price, p.annual_price
    FROM subscriptions s
    JOIN restaurants r ON s.restaurant_id = r.id
    JOIN subscription_plans p ON s.plan_id = p.id
    {$whereClause}
    ORDER BY s.created_at DESC
    LIMIT {$perPage} OFFSET {$offset}
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all plans for plan change dropdown
$allPlans = getSubscriptionPlans(false);

// Get status counts
$statusCounts = [];
$stmt = $pdo->query("SELECT status, COUNT(*) as count FROM subscriptions GROUP BY status");
while ($row = $stmt->fetch()) {
    $statusCounts[$row['status']] = $row['count'];
}

$pageTitle = 'Subscriptions Management';
include __DIR__ . '/../includes/admin-layout.php';
?>

<style>
/* Clean Subscriptions Styles */
.page-header {
    margin-bottom: 24px;
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

/* Status Stats */
.status-stats {
    display: flex;
    gap: 12px;
    margin-bottom: 24px;
    flex-wrap: wrap;
}

.status-stat {
    background: #fff;
    padding: 16px 20px;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
    border: 1px solid #e5e7eb;
    flex: 1;
    min-width: 140px;
}

.status-stat:hover {
    border-color: #111827;
}

.status-stat.active {
    border-color: #111827;
    background: #f9fafb;
}

.status-stat-icon {
    width: 36px;
    height: 36px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.status-stat-icon svg {
    width: 20px;
    height: 20px;
}

.status-stat-icon.trial { background: #dbeafe; color: #2563eb; }
.status-stat-icon.active-status { background: #d1fae5; color: #059669; }
.status-stat-icon.expired { background: #fee2e2; color: #dc2626; }
.status-stat-icon.cancelled { background: #f3f4f6; color: #6b7280; }
.status-stat-icon.pending { background: #fef3c7; color: #d97706; }
.status-stat-icon.all { background: #e0e7ff; color: #4f46e5; }

.status-stat-info {
    display: flex;
    flex-direction: column;
    flex: 1;
}

.status-stat-count {
    font-size: 1.25rem;
    font-weight: 600;
    color: #111827;
}

.status-stat-label {
    font-size: 0.75rem;
    color: #6b7280;
    text-transform: uppercase;
    font-weight: 500;
}

/* Filters */
.filters-bar {
    background: #fff;
    padding: 16px;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    margin-bottom: 24px;
    display: flex;
    gap: 12px;
    align-items: center;
    flex-wrap: wrap;
}

.search-box {
    flex: 1;
    min-width: 200px;
}

.search-box input {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 0.875rem;
}

.search-box input:focus {
    outline: none;
    border-color: #111827;
}

.btn-search,
.btn-clear {
    padding: 10px 20px;
    border-radius: 6px;
    font-weight: 500;
    font-size: 0.875rem;
    cursor: pointer;
    border: none;
    transition: background 0.2s;
    text-decoration: none;
}

.btn-search {
    background: #111827;
    color: #fff;
}

.btn-search:hover {
    background: #374151;
}

.btn-clear {
    background: #f3f4f6;
    color: #374151;
}

.btn-clear:hover {
    background: #e5e7eb;
}

/* Table */
.table-card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.subscriptions-table {
    width: 100%;
    border-collapse: collapse;
}

.subscriptions-table th {
    background: #f9fafb;
    padding: 12px 16px;
    text-align: left;
    font-size: 0.75rem;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 1px solid #e5e7eb;
}

.subscriptions-table td {
    padding: 16px;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: middle;
}

.subscriptions-table tr:last-child td {
    border-bottom: none;
}

.subscriptions-table tr:hover {
    background: #f9fafb;
}

.restaurant-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.restaurant-name {
    font-weight: 500;
    color: #111827;
}

.restaurant-slug {
    font-size: 0.75rem;
    color: #6b7280;
    font-family: monospace;
}

.plan-badge,
.status-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 500;
}

.plan-basic { background: #d1fae5; color: #065f46; }
.plan-professional { background: #dbeafe; color: #1e40af; }
.plan-enterprise { background: #ede9fe; color: #5b21b6; }

.status-trial { background: #dbeafe; color: #1e40af; }
.status-active { background: #d1fae5; color: #065f46; }
.status-expired { background: #fee2e2; color: #991b1b; }
.status-cancelled { background: #f3f4f6; color: #6b7280; }
.status-pending { background: #fef3c7; color: #92400e; }

.billing-info {
    font-size: 0.875rem;
}

.billing-cycle {
    font-weight: 500;
    color: #111827;
}

.billing-price {
    font-size: 0.75rem;
    color: #6b7280;
}

.date-info {
    font-size: 0.813rem;
    color: #6b7280;
}

.date-label {
    font-weight: 500;
    color: #111827;
}

/* Actions Dropdown */
.actions-cell {
    position: relative;
}

.actions-btn {
    background: #f3f4f6;
    border: none;
    padding: 8px 12px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.813rem;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: background 0.2s;
}

.actions-btn:hover {
    background: #e5e7eb;
}

.actions-dropdown {
    position: absolute;
    right: 0;
    top: 100%;
    background: #fff;
    border-radius: 6px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    min-width: 200px;
    z-index: 100;
    display: none;
    overflow: hidden;
    border: 1px solid #e5e7eb;
    margin-top: 4px;
}

.actions-dropdown.show {
    display: block;
}

.actions-dropdown-item {
    display: block;
    padding: 10px 16px;
    font-size: 0.813rem;
    color: #374151;
    text-decoration: none;
    border: none;
    background: none;
    width: 100%;
    text-align: left;
    cursor: pointer;
    transition: background 0.2s;
}

.actions-dropdown-item:hover {
    background: #f9fafb;
}

.actions-dropdown-divider {
    height: 1px;
    background: #e5e7eb;
    margin: 4px 0;
}

.actions-dropdown-title {
    padding: 8px 16px;
    font-size: 0.7rem;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Pagination */
.pagination {
    display: flex;
    justify-content: center;
    gap: 8px;
    padding: 20px;
}

.pagination a,
.pagination span {
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 0.813rem;
    text-decoration: none;
}

.pagination a {
    background: #f3f4f6;
    color: #374151;
    transition: background 0.2s;
}

.pagination a:hover {
    background: #e5e7eb;
}

.pagination span.current {
    background: #111827;
    color: #fff;
}

.pagination span.disabled {
    color: #9ca3af;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #6b7280;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .status-stats {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .status-stat {
        min-width: auto;
    }
    
    .filters-bar {
        flex-direction: column;
    }
    
    .search-box {
        width: 100%;
    }
    
    .btn-search,
    .btn-clear {
        width: 100%;
    }
    
    .subscriptions-table {
        font-size: 0.813rem;
    }
    
    .subscriptions-table th,
    .subscriptions-table td {
        padding: 12px 8px;
    }
    
    .actions-dropdown {
        right: auto;
        left: 0;
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
    <h1 class="page-title">Subscriptions Management</h1>
</div>

<!-- Status Stats -->
<div class="status-stats">
    <a href="subscriptions.php" class="status-stat <?php echo empty($statusFilter) ? 'active' : ''; ?>">
        <div class="status-stat-icon all">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
            </svg>
        </div>
        <div class="status-stat-info">
            <span class="status-stat-count"><?php echo array_sum($statusCounts); ?></span>
            <span class="status-stat-label">All</span>
        </div>
    </a>
    
    <a href="?status=trial" class="status-stat <?php echo $statusFilter === 'trial' ? 'active' : ''; ?>">
        <div class="status-stat-icon trial">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <div class="status-stat-info">
            <span class="status-stat-count"><?php echo $statusCounts['trial'] ?? 0; ?></span>
            <span class="status-stat-label">Trial</span>
        </div>
    </a>
    
    <a href="?status=active" class="status-stat <?php echo $statusFilter === 'active' ? 'active' : ''; ?>">
        <div class="status-stat-icon active-status">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <div class="status-stat-info">
            <span class="status-stat-count"><?php echo $statusCounts['active'] ?? 0; ?></span>
            <span class="status-stat-label">Active</span>
        </div>
    </a>
    
    <a href="?status=expired" class="status-stat <?php echo $statusFilter === 'expired' ? 'active' : ''; ?>">
        <div class="status-stat-icon expired">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
        </div>
        <div class="status-stat-info">
            <span class="status-stat-count"><?php echo $statusCounts['expired'] ?? 0; ?></span>
            <span class="status-stat-label">Expired</span>
        </div>
    </a>
    
    <a href="?status=pending" class="status-stat <?php echo $statusFilter === 'pending' ? 'active' : ''; ?>">
        <div class="status-stat-icon pending">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <div class="status-stat-info">
            <span class="status-stat-count"><?php echo $statusCounts['pending'] ?? 0; ?></span>
            <span class="status-stat-label">Pending</span>
        </div>
    </a>
</div>

<!-- Filters -->
<form class="filters-bar" method="GET" action="">
    <?php if ($statusFilter): ?>
        <input type="hidden" name="status" value="<?php echo htmlspecialchars($statusFilter); ?>">
    <?php endif; ?>
    <div class="search-box">
        <input type="text" name="search" placeholder="Search by restaurant name..." 
               value="<?php echo htmlspecialchars($searchQuery); ?>">
    </div>
    <button type="submit" class="btn-search">Search</button>
    <?php if ($searchQuery || $statusFilter): ?>
        <a href="subscriptions.php" class="btn-clear">Clear</a>
    <?php endif; ?>
</form>

<!-- Subscriptions Table -->
<div class="table-card">
    <?php if (empty($subscriptions)): ?>
        <div class="empty-state">
            <p>No subscriptions found.</p>
        </div>
    <?php else: ?>
        <table class="subscriptions-table">
            <thead>
                <tr>
                    <th>Restaurant</th>
                    <th>Plan</th>
                    <th>Status</th>
                    <th>Billing</th>
                    <th>Period</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($subscriptions as $sub): ?>
                    <?php
                    $statusInfo = getSubscriptionStatusInfo($sub);
                    $periodEnd = $sub['current_period_end'] ? date('M j, Y', strtotime($sub['current_period_end'])) : 'N/A';
                    $trialEnd = $sub['trial_ends_at'] ? date('M j, Y', strtotime($sub['trial_ends_at'])) : 'N/A';
                    $price = $sub['billing_cycle'] === 'annual' ? $sub['annual_price'] : $sub['monthly_price'];
                    ?>
                    <tr>
                        <td>
                            <div class="restaurant-info">
                                <span class="restaurant-name"><?php echo htmlspecialchars($sub['restaurant_name']); ?></span>
                                <span class="restaurant-slug"><?php echo htmlspecialchars($sub['restaurant_slug']); ?></span>
                            </div>
                        </td>
                        <td>
                            <span class="plan-badge plan-<?php echo $sub['plan_slug']; ?>">
                                <?php echo htmlspecialchars($sub['plan_name']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="status-badge status-<?php echo $sub['status']; ?>">
                                <?php echo ucfirst($sub['status']); ?>
                            </span>
                        </td>
                        <td>
                            <div class="billing-info">
                                <div class="billing-cycle"><?php echo ucfirst($sub['billing_cycle']); ?></div>
                                <div class="billing-price"><?php echo formatSubscriptionPrice($price); ?></div>
                            </div>
                        </td>
                        <td>
                            <div class="date-info">
                                <?php if ($sub['status'] === 'trial'): ?>
                                    <span class="date-label">Trial ends:</span><br>
                                    <?php echo $trialEnd; ?>
                                <?php else: ?>
                                    <span class="date-label">Renews:</span><br>
                                    <?php echo $periodEnd; ?>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="actions-cell">
                            <button class="actions-btn" onclick="toggleDropdown(this)">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                </svg>
                                Actions
                            </button>
                            <div class="actions-dropdown">
                                <div class="actions-dropdown-title">Change Status</div>
                                <?php foreach (['trial', 'active', 'expired', 'cancelled', 'pending'] as $status): ?>
                                    <?php if ($status !== $sub['status']): ?>
                                        <form method="POST" style="display: contents;">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="subscription_id" value="<?php echo $sub['id']; ?>">
                                            <input type="hidden" name="new_status" value="<?php echo $status; ?>">
                                            <button type="submit" class="actions-dropdown-item">
                                                Set to <?php echo ucfirst($status); ?>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                                <div class="actions-dropdown-divider"></div>
                                <div class="actions-dropdown-title">Change Plan</div>
                                <?php foreach ($allPlans as $plan): ?>
                                    <?php if ($plan['id'] != $sub['plan_id']): ?>
                                        <form method="POST" style="display: contents;" onsubmit="return confirm('Change subscription plan to <?php echo htmlspecialchars($plan['name']); ?>?')">
                                            <input type="hidden" name="action" value="change_plan">
                                            <input type="hidden" name="subscription_id" value="<?php echo $sub['id']; ?>">
                                            <input type="hidden" name="new_plan_id" value="<?php echo $plan['id']; ?>">
                                            <button type="submit" class="actions-dropdown-item">
                                                <?php echo htmlspecialchars($plan['name']); ?>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                                <div class="actions-dropdown-divider"></div>
                                <div class="actions-dropdown-title">Extend Period</div>
                                <?php foreach ([7, 30, 90, 365] as $days): ?>
                                    <form method="POST" style="display: contents;">
                                        <input type="hidden" name="action" value="extend_period">
                                        <input type="hidden" name="subscription_id" value="<?php echo $sub['id']; ?>">
                                        <input type="hidden" name="days" value="<?php echo $days; ?>">
                                        <button type="submit" class="actions-dropdown-item">
                                            + <?php echo $days; ?> days
                                        </button>
                                    </form>
                                <?php endforeach; ?>
                                <div class="actions-dropdown-divider"></div>
                                <a href="restaurant-view.php?slug=<?php echo htmlspecialchars($sub['restaurant_slug']); ?>" 
                                   class="actions-dropdown-item">View Restaurant</a>
                                <a href="payments.php?restaurant_id=<?php echo $sub['restaurant_id']; ?>" 
                                   class="actions-dropdown-item">View Payments</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>&status=<?php echo urlencode($statusFilter); ?>&search=<?php echo urlencode($searchQuery); ?>">← Prev</a>
                <?php else: ?>
                    <span class="disabled">← Prev</span>
                <?php endif; ?>
                
                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <?php if ($i === $page): ?>
                        <span class="current"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?page=<?php echo $i; ?>&status=<?php echo urlencode($statusFilter); ?>&search=<?php echo urlencode($searchQuery); ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?php echo $page + 1; ?>&status=<?php echo urlencode($statusFilter); ?>&search=<?php echo urlencode($searchQuery); ?>">Next →</a>
                <?php else: ?>
                    <span class="disabled">Next →</span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
function toggleDropdown(btn) {
    // Close all other dropdowns
    document.querySelectorAll('.actions-dropdown.show').forEach(d => d.classList.remove('show'));
    
    // Toggle this dropdown
    const dropdown = btn.nextElementSibling;
    dropdown.classList.toggle('show');
    
    // Close on click outside
    document.addEventListener('click', function closeDropdown(e) {
        if (!btn.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.remove('show');
            document.removeEventListener('click', closeDropdown);
        }
    });
}
</script>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
