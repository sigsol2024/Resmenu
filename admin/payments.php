<?php
/**
 * Admin Payments Management
 * View payment history and manage payment statuses
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
        $paymentId = intval($_POST['payment_id'] ?? 0);
        $newStatus = $_POST['new_status'] ?? '';
        
        $validStatuses = ['pending', 'success', 'failed', 'refunded'];
        
        if ($paymentId > 0 && in_array($newStatus, $validStatuses)) {
            if (updatePaymentStatus($paymentId, $newStatus)) {
                // If marking as success, also update the subscription
                if ($newStatus === 'success') {
                    $stmt = $pdo->prepare("SELECT subscription_id FROM payments WHERE id = ?");
                    $stmt->execute([$paymentId]);
                    $payment = $stmt->fetch();
                    
                    if ($payment && $payment['subscription_id']) {
                        $stmt = $pdo->prepare("SELECT billing_cycle FROM subscriptions WHERE id = ?");
                        $stmt->execute([$payment['subscription_id']]);
                        $sub = $stmt->fetch();
                        
                        if ($sub) {
                            activateSubscription($payment['subscription_id'], $sub['billing_cycle']);
                        }
                    }
                }
                
                $message = 'Payment status updated successfully!';
                $messageType = 'success';
            } else {
                $message = 'Failed to update payment status.';
                $messageType = 'error';
            }
        }
    } elseif ($action === 'create_manual') {
        $restaurantId = intval($_POST['restaurant_id'] ?? 0);
        $subscriptionId = intval($_POST['subscription_id'] ?? 0);
        $amount = floatval($_POST['amount'] ?? 0);
        $status = $_POST['status'] ?? 'success';
        
        if ($restaurantId > 0 && $subscriptionId > 0 && $amount > 0) {
            $paymentId = createPayment([
                'restaurant_id' => $restaurantId,
                'subscription_id' => $subscriptionId,
                'amount' => $amount,
                'payment_gateway' => 'manual',
                'transaction_reference' => 'MANUAL-' . time(),
                'status' => $status
            ]);
            
            if ($paymentId) {
                // Activate subscription if payment is successful
                if ($status === 'success') {
                    $stmt = $pdo->prepare("SELECT billing_cycle FROM subscriptions WHERE id = ?");
                    $stmt->execute([$subscriptionId]);
                    $sub = $stmt->fetch();
                    
                    if ($sub) {
                        activateSubscription($subscriptionId, $sub['billing_cycle']);
                    }
                }
                
                $message = 'Manual payment recorded successfully!';
                $messageType = 'success';
            } else {
                $message = 'Failed to record payment.';
                $messageType = 'error';
            }
        }
    }
}

// Filters
$statusFilter = $_GET['status'] ?? '';
$gatewayFilter = $_GET['gateway'] ?? '';
$restaurantFilter = intval($_GET['restaurant_id'] ?? 0);
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Build query
$where = [];
$params = [];

if (!empty($statusFilter)) {
    $where[] = "p.status = ?";
    $params[] = $statusFilter;
}

if (!empty($gatewayFilter)) {
    $where[] = "p.payment_gateway = ?";
    $params[] = $gatewayFilter;
}

if ($restaurantFilter > 0) {
    $where[] = "p.restaurant_id = ?";
    $params[] = $restaurantFilter;
}

if (!empty($dateFrom)) {
    $where[] = "DATE(p.created_at) >= ?";
    $params[] = $dateFrom;
}

if (!empty($dateTo)) {
    $where[] = "DATE(p.created_at) <= ?";
    $params[] = $dateTo;
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Get total count
$countSql = "SELECT COUNT(*) FROM payments p {$whereClause}";
$stmt = $pdo->prepare($countSql);
$stmt->execute($params);
$totalCount = $stmt->fetchColumn();
$totalPages = ceil($totalCount / $perPage);

// Get payments
$sql = "
    SELECT p.*, r.name as restaurant_name, r.slug as restaurant_slug,
           sp.name as plan_name
    FROM payments p
    JOIN restaurants r ON p.restaurant_id = r.id
    LEFT JOIN subscriptions s ON p.subscription_id = s.id
    LEFT JOIN subscription_plans sp ON s.plan_id = sp.id
    {$whereClause}
    ORDER BY p.created_at DESC
    LIMIT {$perPage} OFFSET {$offset}
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get totals
$totalSuccess = 0;
$totalPending = 0;
try {
    $stmt = $pdo->query("SELECT SUM(amount) FROM payments WHERE status = 'success'");
    $totalSuccess = floatval($stmt->fetchColumn());
    
    $stmt = $pdo->query("SELECT SUM(amount) FROM payments WHERE status = 'pending'");
    $totalPending = floatval($stmt->fetchColumn());
} catch (PDOException $e) {}

// Get restaurants for dropdown
$restaurants = [];
try {
    $stmt = $pdo->query("SELECT id, name FROM restaurants ORDER BY name");
    $restaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}

$pageTitle = 'Payments';
include __DIR__ . '/../includes/admin-layout.php';
?>

<style>
/* Clean Payments Styles */
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

/* Stats */
.payment-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}

.stat-card {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    border: 1px solid #e5e7eb;
}

.stat-label {
    font-size: 0.75rem;
    color: #6b7280;
    text-transform: uppercase;
    font-weight: 500;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: 600;
    color: #111827;
}

.stat-value.success { color: #059669; }
.stat-value.pending { color: #6b7280; }

/* Filters */
.filters-bar {
    background: #fff;
    padding: 16px;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    margin-bottom: 24px;
}

.filters-row {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    align-items: flex-end;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.filter-group label {
    font-size: 0.75rem;
    font-weight: 500;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.filter-group select,
.filter-group input {
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 0.875rem;
    min-width: 150px;
    transition: border-color 0.2s;
}

.filter-group select:focus,
.filter-group input:focus {
    outline: none;
    border-color: #111827;
}

.filter-actions {
    display: flex;
    gap: 8px;
}

.btn-filter,
.btn-clear,
.btn-add {
    padding: 10px 20px;
    border-radius: 6px;
    font-weight: 500;
    font-size: 0.875rem;
    cursor: pointer;
    border: none;
    transition: background 0.2s;
    text-decoration: none;
}

.btn-filter {
    background: #111827;
    color: #fff;
}

.btn-filter:hover {
    background: #374151;
}

.btn-clear {
    background: #f3f4f6;
    color: #374151;
}

.btn-clear:hover {
    background: #e5e7eb;
}

.btn-add {
    background: #111827;
    color: #fff;
    margin-left: auto;
}

.btn-add:hover {
    background: #374151;
}

/* Table */
.table-card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.payments-table {
    width: 100%;
    border-collapse: collapse;
}

.payments-table th {
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

.payments-table td {
    padding: 16px;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: middle;
}

.payments-table tr:last-child td {
    border-bottom: none;
}

.payments-table tr:hover {
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

.transaction-ref {
    font-size: 0.75rem;
    color: #6b7280;
    font-family: monospace;
}

.amount {
    font-size: 1rem;
    font-weight: 600;
    color: #111827;
}

.gateway-badge,
.status-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 500;
}

.gateway-paystack { background: #dbeafe; color: #1e40af; }
.gateway-flutterwave { background: #f3f4f6; color: #4b5563; }
.gateway-manual { background: #f3f4f6; color: #6b7280; }

.status-pending { background: #f3f4f6; color: #4b5563; }
.status-success { background: #d1fae5; color: #065f46; }
.status-failed { background: #fee2e2; color: #991b1b; }
.status-refunded { background: #e0e7ff; color: #4338ca; }

.date-info {
    font-size: 0.813rem;
    color: #6b7280;
}

/* Actions */
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
    transition: background 0.2s;
}

.actions-btn:hover {
    background: #e5e7eb;
}

.actions-dropdown {
    position: absolute;
    right: 100%;
    top: 0;
    left: auto;
    background: #fff;
    border-radius: 6px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    min-width: 180px;
    z-index: 100;
    display: none;
    overflow: hidden;
    border: 1px solid #e5e7eb;
    margin-right: 4px;
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

.actions-dropdown-title {
    padding: 8px 16px;
    font-size: 0.7rem;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Modal */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-overlay.show {
    display: flex;
}

.modal {
    background: #fff;
    border-radius: 8px;
    max-width: 500px;
    width: 90%;
    padding: 24px;
}

.modal-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #111827;
    margin-bottom: 20px;
}

.modal-body {
    margin-bottom: 24px;
}

.form-group {
    margin-bottom: 16px;
}

.form-group label {
    display: block;
    font-weight: 500;
    color: #374151;
    margin-bottom: 6px;
    font-size: 0.875rem;
}

.form-group select,
.form-group input {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 0.875rem;
    transition: border-color 0.2s;
}

.form-group select:focus,
.form-group input:focus {
    outline: none;
    border-color: #111827;
}

.modal-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
}

.btn-modal {
    padding: 10px 20px;
    border-radius: 6px;
    font-weight: 500;
    font-size: 0.875rem;
    cursor: pointer;
    border: none;
    transition: background 0.2s;
}

.btn-modal-cancel {
    background: #f3f4f6;
    color: #374151;
}

.btn-modal-cancel:hover {
    background: #e5e7eb;
}

.btn-modal-confirm {
    background: #111827;
    color: #fff;
}

.btn-modal-confirm:hover {
    background: #374151;
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
    .payment-stats {
        grid-template-columns: 1fr;
    }
    
    .filters-row {
        flex-direction: column;
    }
    
    .filter-group {
        width: 100%;
    }
    
    .filter-group select,
    .filter-group input {
        width: 100%;
        min-width: auto;
    }
    
    .filter-actions {
        width: 100%;
    }
    
    .btn-filter,
    .btn-clear,
    .btn-add {
        flex: 1;
    }
    
    .btn-add {
        margin-left: 0;
    }
    
    .payments-table {
        font-size: 0.813rem;
    }
    
    .payments-table th,
    .payments-table td {
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
    <h1 class="page-title">Payments</h1>
    <p class="page-subtitle">View subscription payment history and transactions</p>
</div>

<!-- Stats -->
<div class="payment-stats">
    <div class="stat-card">
        <div class="stat-label">Total Successful</div>
        <div class="stat-value success"><?php echo formatSubscriptionPrice($totalSuccess); ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Pending</div>
        <div class="stat-value pending"><?php echo formatSubscriptionPrice($totalPending); ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Total Transactions</div>
        <div class="stat-value"><?php echo number_format($totalCount); ?></div>
    </div>
</div>

<!-- Filters -->
<form class="filters-bar" method="GET" action="">
    <div class="filters-row">
        <div class="filter-group">
            <label>Status</label>
            <select name="status">
                <option value="">All Statuses</option>
                <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="success" <?php echo $statusFilter === 'success' ? 'selected' : ''; ?>>Success</option>
                <option value="failed" <?php echo $statusFilter === 'failed' ? 'selected' : ''; ?>>Failed</option>
                <option value="refunded" <?php echo $statusFilter === 'refunded' ? 'selected' : ''; ?>>Refunded</option>
            </select>
        </div>
        
        <div class="filter-group">
            <label>Gateway</label>
            <select name="gateway">
                <option value="">All Gateways</option>
                <option value="paystack" <?php echo $gatewayFilter === 'paystack' ? 'selected' : ''; ?>>Paystack</option>
                <option value="flutterwave" <?php echo $gatewayFilter === 'flutterwave' ? 'selected' : ''; ?>>Flutterwave</option>
                <option value="manual" <?php echo $gatewayFilter === 'manual' ? 'selected' : ''; ?>>Manual</option>
            </select>
        </div>
        
        <div class="filter-group">
            <label>Restaurant</label>
            <select name="restaurant_id">
                <option value="">All Restaurants</option>
                <?php foreach ($restaurants as $r): ?>
                    <option value="<?php echo $r['id']; ?>" <?php echo $restaurantFilter === $r['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($r['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="filter-group">
            <label>From Date</label>
            <input type="date" name="date_from" value="<?php echo htmlspecialchars($dateFrom); ?>">
        </div>
        
        <div class="filter-group">
            <label>To Date</label>
            <input type="date" name="date_to" value="<?php echo htmlspecialchars($dateTo); ?>">
        </div>
        
        <div class="filter-actions">
            <button type="submit" class="btn-filter">Filter</button>
            <a href="payments.php" class="btn-clear">Clear</a>
        </div>
        
        <button type="button" class="btn-add" onclick="openManualPaymentModal()">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Manual Payment
        </button>
    </div>
</form>

<!-- Payments Table -->
<div class="table-card">
    <?php if (empty($payments)): ?>
        <div class="empty-state">
            <p>No payments found.</p>
        </div>
    <?php else: ?>
        <table class="payments-table">
            <thead>
                <tr>
                    <th>Restaurant</th>
                    <th>Amount</th>
                    <th>Gateway</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($payments as $payment): ?>
                    <tr>
                        <td>
                            <div class="restaurant-info">
                                <span class="restaurant-name"><?php echo htmlspecialchars($payment['restaurant_name']); ?></span>
                                <span class="transaction-ref"><?php echo htmlspecialchars($payment['transaction_reference'] ?? 'N/A'); ?></span>
                            </div>
                        </td>
                        <td>
                            <span class="amount"><?php echo formatSubscriptionPrice($payment['amount'], $payment['currency'] ?? 'NGN'); ?></span>
                        </td>
                        <td>
                            <span class="gateway-badge gateway-<?php echo $payment['payment_gateway']; ?>">
                                <?php echo ucfirst($payment['payment_gateway']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="status-badge status-<?php echo $payment['status']; ?>">
                                <?php echo ucfirst($payment['status']); ?>
                            </span>
                        </td>
                        <td>
                            <div class="date-info">
                                <?php echo date('M j, Y', strtotime($payment['created_at'])); ?><br>
                                <?php echo date('g:i A', strtotime($payment['created_at'])); ?>
                            </div>
                        </td>
                        <td class="actions-cell">
                            <button class="actions-btn" onclick="toggleDropdown(this)" title="Actions">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                </svg>
                            </button>
                            <div class="actions-dropdown">
                                <div class="actions-dropdown-title">Change Status</div>
                                <?php foreach (['pending', 'success', 'failed', 'refunded'] as $status): ?>
                                    <?php if ($status !== $payment['status']): ?>
                                        <form method="POST" style="display: contents;">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="payment_id" value="<?php echo $payment['id']; ?>">
                                            <input type="hidden" name="new_status" value="<?php echo $status; ?>">
                                            <button type="submit" class="actions-dropdown-item">
                                                Mark as <?php echo ucfirst($status); ?>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php 
                $queryParams = array_filter([
                    'status' => $statusFilter,
                    'gateway' => $gatewayFilter,
                    'restaurant_id' => $restaurantFilter,
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo
                ]);
                $queryString = http_build_query($queryParams);
                ?>
                
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>&<?php echo $queryString; ?>">← Prev</a>
                <?php else: ?>
                    <span class="disabled">← Prev</span>
                <?php endif; ?>
                
                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <?php if ($i === $page): ?>
                        <span class="current"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?page=<?php echo $i; ?>&<?php echo $queryString; ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?php echo $page + 1; ?>&<?php echo $queryString; ?>">Next →</a>
                <?php else: ?>
                    <span class="disabled">Next →</span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Manual Payment Modal -->
<div class="modal-overlay" id="manualPaymentModal">
    <div class="modal">
        <h3 class="modal-title">Record Manual Payment</h3>
        <form method="POST" action="">
            <input type="hidden" name="action" value="create_manual">
            <div class="modal-body">
                <div class="form-group">
                    <label>Restaurant</label>
                    <select name="restaurant_id" required id="modalRestaurantSelect">
                        <option value="">Select Restaurant</option>
                        <?php foreach ($restaurants as $r): ?>
                            <option value="<?php echo $r['id']; ?>"><?php echo htmlspecialchars($r['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Subscription</label>
                    <select name="subscription_id" required id="modalSubscriptionSelect">
                        <option value="">Select Restaurant First</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Amount (NGN)</label>
                    <input type="number" name="amount" required min="1" step="0.01" placeholder="10000">
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="success">Success (Activate Subscription)</option>
                        <option value="pending">Pending</option>
                    </select>
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-modal btn-modal-cancel" onclick="closeManualPaymentModal()">Cancel</button>
                <button type="submit" class="btn-modal btn-modal-confirm">Record Payment</button>
            </div>
        </form>
    </div>
</div>

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

function openManualPaymentModal() {
    document.getElementById('manualPaymentModal').classList.add('show');
}

function closeManualPaymentModal() {
    document.getElementById('manualPaymentModal').classList.remove('show');
}

// Load subscriptions when restaurant is selected
document.getElementById('modalRestaurantSelect').addEventListener('change', function() {
    const restaurantId = this.value;
    const subscriptionSelect = document.getElementById('modalSubscriptionSelect');
    
    if (!restaurantId) {
        subscriptionSelect.innerHTML = '<option value="">Select Restaurant First</option>';
        return;
    }
    
    subscriptionSelect.innerHTML = '<option value="">Loading...</option>';
    
    fetch('subscriptions.php?get_subscriptions=' + restaurantId)
        .then(response => response.json())
        .then(data => {
            subscriptionSelect.innerHTML = '<option value="">Select Subscription</option>';
            data.forEach(sub => {
                subscriptionSelect.innerHTML += `<option value="${sub.id}">${sub.plan_name} (${sub.status})</option>`;
            });
        })
        .catch(() => {
            subscriptionSelect.innerHTML = '<option value="">Error loading subscriptions</option>';
        });
});

// Close modal on overlay click
document.getElementById('manualPaymentModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeManualPaymentModal();
    }
});
</script>

<?php
// Handle AJAX request for subscriptions
if (isset($_GET['get_subscriptions'])) {
    header('Content-Type: application/json');
    $restaurantId = intval($_GET['get_subscriptions']);
    $stmt = $pdo->prepare("
        SELECT s.id, s.status, p.name as plan_name
        FROM subscriptions s
        JOIN subscription_plans p ON s.plan_id = p.id
        WHERE s.restaurant_id = ?
        ORDER BY s.created_at DESC
    ");
    $stmt->execute([$restaurantId]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}
?>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
