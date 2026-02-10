<?php
/**
 * Transaction History
 * View all payment transactions with export options
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

// Handle exports
if (isset($_GET['export'])) {
    $exportType = $_GET['export'];
    
    // Get all payments
    $stmt = $pdo->prepare("
        SELECT p.*, sp.name as plan_name
        FROM payments p
        LEFT JOIN subscriptions s ON p.subscription_id = s.id
        LEFT JOIN subscription_plans sp ON s.plan_id = sp.id
        WHERE p.restaurant_id = ?
        ORDER BY p.created_at DESC
    ");
    $stmt->execute([$restaurantId]);
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get restaurant info
    $stmt = $pdo->prepare("SELECT name FROM restaurants WHERE id = ?");
    $stmt->execute([$restaurantId]);
    $restaurant = $stmt->fetch();
    
    if ($exportType === 'csv') {
        // CSV Export
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="transaction-history-' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // BOM for UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Headers
        fputcsv($output, ['Date', 'Transaction ID', 'Plan', 'Amount', 'Currency', 'Gateway', 'Status', 'Paid Date']);
        
        // Data
        foreach ($payments as $payment) {
            fputcsv($output, [
                date('Y-m-d H:i:s', strtotime($payment['created_at'])),
                $payment['transaction_reference'] ?? 'N/A',
                $payment['plan_name'] ?? 'N/A',
                $payment['amount'],
                $payment['currency'] ?? 'NGN',
                ucfirst($payment['payment_gateway']),
                ucfirst($payment['status']),
                $payment['paid_at'] ? date('Y-m-d H:i:s', strtotime($payment['paid_at'])) : 'N/A'
            ]);
        }
        
        fclose($output);
        exit;
        
    } elseif ($exportType === 'pdf') {
        // PDF Export
        $pdf = generateTransactionHistoryPDF($payments, $restaurant['name']);
        
        if ($pdf) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="transaction-history-' . date('Y-m-d') . '.pdf"');
            echo $pdf;
            exit;
        } else {
            $_SESSION['error'] = 'Failed to generate PDF. Please try again.';
            header('Location: transaction-history.php');
            exit;
        }
    }
}

// Get all payments for display
$stmt = $pdo->prepare("
    SELECT p.*, sp.name as plan_name
    FROM payments p
    LEFT JOIN subscriptions s ON p.subscription_id = s.id
    LEFT JOIN subscription_plans sp ON s.plan_id = sp.id
    WHERE p.restaurant_id = ?
    ORDER BY p.created_at DESC
");
$stmt->execute([$restaurantId]);
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Transaction History';
include __DIR__ . '/../includes/manager-layout.php';
?>

<style>
/* Transaction History Styles */
.page-header {
    margin-bottom: 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
}

.page-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--text);
    margin: 0;
}

.export-buttons {
    display: flex;
    gap: 12px;
}

.btn-export {
    padding: 10px 20px;
    border-radius: 6px;
    font-weight: 500;
    font-size: 0.875rem;
    cursor: pointer;
    border: none;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: background 0.2s;
    background: #111827;
    color: #fff;
}

.btn-export:hover {
    background: #374151;
}

.btn-export.secondary {
    background: #f3f4f6;
    color: #374151;
}

.btn-export.secondary:hover {
    background: #e5e7eb;
}

.table-card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.transactions-table {
    width: 100%;
    border-collapse: collapse;
}

.transactions-table th {
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

.transactions-table td {
    padding: 16px;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: middle;
    font-size: 0.875rem;
}

.transactions-table tr:last-child td {
    border-bottom: none;
}

.transactions-table tr:hover {
    background: #f9fafb;
}

.transaction-date {
    color: #111827;
    font-weight: 500;
}

.transaction-id {
    font-family: monospace;
    font-size: 0.813rem;
    color: #6b7280;
}

.amount-cell {
    font-weight: 600;
    color: #111827;
    font-size: 1rem;
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
.gateway-flutterwave { background: #fef3c7; color: #92400e; }
.gateway-manual { background: #f3f4f6; color: #6b7280; }

.status-pending { background: #fef3c7; color: #92400e; }
.status-success { background: #d1fae5; color: #065f46; }
.status-failed { background: #fee2e2; color: #991b1b; }
.status-refunded { background: #e0e7ff; color: #4338ca; }

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #6b7280;
}

.empty-state svg {
    width: 64px;
    height: 64px;
    margin-bottom: 16px;
    opacity: 0.5;
}

.empty-state h3 {
    font-size: 1.125rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 8px;
}

.empty-state p {
    font-size: 0.875rem;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .export-buttons {
        width: 100%;
        flex-direction: column;
    }
    
    .btn-export {
        width: 100%;
        justify-content: center;
    }
    
    .table-card {
        overflow-x: auto;
    }
    
    .transactions-table {
        min-width: 800px;
    }
    
    .transactions-table th,
    .transactions-table td {
        padding: 12px 8px;
        font-size: 0.813rem;
    }
}
</style>

<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">Transaction History</h1>
    <div class="export-buttons">
        <a href="?export=csv" class="btn-export secondary">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Export CSV
        </a>
        <a href="?export=pdf" class="btn-export">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
            Export PDF
        </a>
    </div>
</div>

<!-- Transactions Table -->
<div class="table-card">
    <?php if (empty($payments)): ?>
        <div class="empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3>No Transactions Yet</h3>
            <p>Your payment history will appear here once you make your first payment.</p>
        </div>
    <?php else: ?>
        <table class="transactions-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Transaction ID</th>
                    <th>Plan</th>
                    <th>Amount</th>
                    <th>Gateway</th>
                    <th>Status</th>
                    <th>Paid Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($payments as $payment): ?>
                    <tr>
                        <td>
                            <div class="transaction-date">
                                <?php echo date('M j, Y', strtotime($payment['created_at'])); ?>
                            </div>
                            <div style="font-size: 0.75rem; color: #6b7280;">
                                <?php echo date('g:i A', strtotime($payment['created_at'])); ?>
                            </div>
                        </td>
                        <td>
                            <span class="transaction-id"><?php echo htmlspecialchars($payment['transaction_reference'] ?? 'N/A'); ?></span>
                        </td>
                        <td><?php echo htmlspecialchars($payment['plan_name'] ?? 'N/A'); ?></td>
                        <td>
                            <div class="amount-cell"><?php echo formatSubscriptionPrice($payment['amount'], $payment['currency'] ?? 'NGN'); ?></div>
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
                            <?php if ($payment['paid_at']): ?>
                                <div style="font-size: 0.813rem;">
                                    <?php echo date('M j, Y', strtotime($payment['paid_at'])); ?><br>
                                    <?php echo date('g:i A', strtotime($payment['paid_at'])); ?>
                                </div>
                            <?php else: ?>
                                <span style="color: #9ca3af;">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>

