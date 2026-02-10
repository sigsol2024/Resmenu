<?php
/**
 * Orders Management (Manager)
 */

require_once __DIR__ . '/../includes/auth.php';
requireManager();
require_once __DIR__ . '/../includes/functions.php';

$restaurantId = getCurrentUserRestaurantId();
if (!$restaurantId) {
    die('No restaurant associated with your account.');
}

$pdo = getDBConnection();
$restaurantSlug = $_GET['slug'] ?? '';

// Get restaurant
$stmt = $pdo->prepare("SELECT * FROM restaurants WHERE id = ?");
$stmt->execute([$restaurantId]);
$restaurant = $stmt->fetch();
if (!$restaurant) die('Restaurant not found.');
if (empty($restaurantSlug)) $restaurantSlug = $restaurant['slug'];

$slugParam = $restaurantSlug ? '?slug=' . urlencode($restaurantSlug) : '';

// Order stats by status
$statuses = ['pending', 'confirmed', 'on_hold', 'cancelled', 'completed'];
$statsByStatus = [];
$totalOrdersAmount = 0;
foreach ($statuses as $s) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE restaurant_id = ? AND status = ?");
    $stmt->execute([$restaurantId, $s]);
    $statsByStatus[$s] = (int) $stmt->fetchColumn();
}
$stmt = $pdo->prepare("SELECT COALESCE(SUM(total), 0) FROM orders WHERE restaurant_id = ? AND status IN ('pending','confirmed','on_hold','completed')");
$stmt->execute([$restaurantId]);
$totalOrdersAmount = (float) $stmt->fetchColumn();

$totalOrdersCount = array_sum($statsByStatus);

// Pagination / show all
$showAll = isset($_GET['all']) && $_GET['all'] === '1';
$limit = $showAll ? 999 : 10;
$stmt = $pdo->prepare("SELECT * FROM orders WHERE restaurant_id = ? ORDER BY created_at DESC LIMIT ?");
$stmt->execute([$restaurantId, $limit]);
$orders = $stmt->fetchAll();

$currencySymbol = '₦';

$pageTitle = 'Orders - ' . htmlspecialchars($restaurant['name']);
include __DIR__ . '/../includes/manager-layout.php';
?>

<section class="orders-overview" style="margin-bottom:24px;">
    <h2 class="section-title" style="font-size:1.125rem;font-weight:600;margin-bottom:16px;color:#111827;">Orders Overview</h2>
    <div class="stats" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:16px;margin-bottom:24px;">
        <?php foreach ($statuses as $s): $label = ucfirst(str_replace('_', ' ', $s)); ?>
        <div class="stat-card" style="background:#fff;padding:16px;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
            <div class="stat-label" style="font-size:0.7rem;color:#6b7280;text-transform:uppercase;margin-bottom:4px;"><?php echo htmlspecialchars($label); ?></div>
            <div class="stat-value" style="font-size:1.5rem;font-weight:700;color:#111827;"><?php echo $statsByStatus[$s]; ?></div>
        </div>
        <?php endforeach; ?>
        <div class="stat-card" style="background:#fff;padding:16px;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
            <div class="stat-label" style="font-size:0.7rem;color:#6b7280;text-transform:uppercase;margin-bottom:4px;">Total Amount</div>
            <div class="stat-value" style="font-size:1rem;font-weight:700;color:#111827;"><?php echo $currencySymbol . number_format($totalOrdersAmount, 2); ?></div>
        </div>
    </div>
</section>

<section class="orders-list">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
        <h2 class="section-title" style="font-size:1.125rem;font-weight:600;color:#111827;"><?php echo $showAll ? 'All Orders' : 'Recent Orders'; ?></h2>
        <?php if (!$showAll && $totalOrdersCount > 10): ?>
        <a href="orders.php<?php echo $slugParam ? $slugParam . '&' : '?'; ?>all=1" style="color:#4f46e5;font-weight:500;font-size:0.875rem;">View all orders</a>
        <?php elseif ($showAll): ?>
        <a href="orders.php<?php echo $slugParam; ?>" style="color:#4f46e5;font-weight:500;font-size:0.875rem;">Show recent only</a>
        <?php endif; ?>
    </div>

    <?php if (empty($orders)): ?>
    <p style="color:#6b7280;padding:24px;text-align:center;">No orders yet.</p>
    <?php else: ?>
    <div class="table-wrapper" style="background:#fff;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,0.1);overflow-x:auto;">
        <table class="orders-table" style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:1px solid #e5e7eb;">
                    <th style="text-align:left;padding:12px 16px;font-size:0.75rem;color:#6b7280;font-weight:600;">Order #</th>
                    <th style="text-align:left;padding:12px 16px;font-size:0.75rem;color:#6b7280;font-weight:600;">Customer</th>
                    <th style="text-align:left;padding:12px 16px;font-size:0.75rem;color:#6b7280;font-weight:600;">Date</th>
                    <th style="text-align:left;padding:12px 16px;font-size:0.75rem;color:#6b7280;font-weight:600;">Total</th>
                    <th style="text-align:left;padding:12px 16px;font-size:0.75rem;color:#6b7280;font-weight:600;">Status</th>
                    <th style="text-align:left;padding:12px 16px;font-size:0.75rem;color:#6b7280;font-weight:600;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $o): ?>
                <tr style="border-bottom:1px solid #f3f4f6;">
                    <td style="padding:12px 16px;font-size:0.875rem;">#<?php echo (int)$o['id']; ?></td>
                    <td style="padding:12px 16px;font-size:0.875rem;"><?php echo htmlspecialchars($o['customer_name']); ?></td>
                    <td style="padding:12px 16px;font-size:0.875rem;"><?php echo date('M j, Y H:i', strtotime($o['created_at'])); ?></td>
                    <td style="padding:12px 16px;font-size:0.875rem;font-weight:600;"><?php echo $currencySymbol . number_format((float)$o['total'], 2); ?></td>
                    <td style="padding:12px 16px;">
                        <form class="order-status-form" method="post" action="../api/update-order-status.php" style="display:inline;">
                            <input type="hidden" name="order_id" value="<?php echo (int)$o['id']; ?>"/>
                            <input type="hidden" name="slug" value="<?php echo htmlspecialchars($restaurantSlug); ?>"/>
                            <select name="status" class="order-status-select" style="padding:6px 10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.8rem;cursor:pointer;">
                                <?php foreach ($statuses as $s): ?>
                                <option value="<?php echo htmlspecialchars($s); ?>" <?php echo ($o['status'] ?? 'pending') === $s ? 'selected' : ''; ?>><?php echo ucfirst(str_replace('_',' ',$s)); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" style="display:none;">Update</button>
                        </form>
                    </td>
                    <td style="padding:12px 16px;">
                        <button type="button" class="view-order-btn" data-order-id="<?php echo (int)$o['id']; ?>" style="padding:6px 12px;background:#4f46e5;color:#fff;border:0;border-radius:6px;font-size:0.8rem;cursor:pointer;font-weight:500;">View</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</section>

<div id="order-modal" class="order-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center;padding:24px;">
    <div class="order-modal-content" style="background:#fff;border-radius:12px;max-width:560px;width:100%;max-height:90vh;overflow-y:auto;padding:24px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
            <h3 id="order-modal-title" style="font-size:1.25rem;font-weight:600;color:#111827;">Order Details</h3>
            <button type="button" id="order-modal-close" style="background:0;border:0;cursor:pointer;padding:4px;color:#6b7280;">&times;</button>
        </div>
        <div id="order-modal-body"></div>
    </div>
</div>

<script>
(function() {
    const slug = <?php echo json_encode($restaurantSlug); ?>;
    const symbol = <?php echo json_encode($currencySymbol); ?>;

    document.querySelectorAll('.order-status-select').forEach(function(sel) {
        sel.addEventListener('change', function() { this.closest('form').submit(); });
    });

    const modal = document.getElementById('order-modal');
    const modalBody = document.getElementById('order-modal-body');
    const modalTitle = document.getElementById('order-modal-title');
    const modalClose = document.getElementById('order-modal-close');

    function closeModal() { modal.style.display = 'none'; }
    modalClose.onclick = closeModal;
    modal.onclick = function(e) { if (e.target === modal) closeModal(); };

    document.querySelectorAll('.view-order-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const orderId = this.getAttribute('data-order-id');
            fetch('../api/get-order-details.php?order_id=' + orderId + '&slug=' + encodeURIComponent(slug))
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        const o = data.order;
                        let itemsHtml = (o.items || []).map(i => '<tr><td>' + (i.name||'') + '</td><td>' + (i.quantity||1) + '</td><td>' + symbol + parseFloat(i.price||0).toFixed(2) + '</td><td>' + symbol + (parseFloat(i.price||0)*(i.quantity||1)).toFixed(2) + '</td></tr>').join('');
                        modalTitle.textContent = 'Order #' + orderId;
                        modalBody.innerHTML = '<table style="width:100%;margin-bottom:16px;"><tr><th style="text-align:left;">Customer</th><td>' + (o.customer_name||'') + '</td></tr><tr><th style="text-align:left;">Phone</th><td>' + (o.customer_phone||'') + '</td></tr><tr><th style="text-align:left;">Email</th><td>' + (o.customer_email||'') + '</td></tr><tr><th style="text-align:left;">Address</th><td>' + (o.delivery_address||'') + '</td></tr><tr><th style="text-align:left;">Status</th><td>' + (o.status||'') + '</td></tr></table><h4 style="margin:16px 0 8px;">Items</h4><table style="width:100%;border-collapse:collapse;"><thead><tr style="border-bottom:1px solid #e5e7eb;"><th style="text-align:left;padding:8px;">Item</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead><tbody>' + itemsHtml + '</tbody></table><p style="margin-top:12px;font-weight:600;">Total: ' + symbol + parseFloat(o.total||0).toFixed(2) + '</p>';
                        modal.style.display = 'flex';
                    }
                })
                .catch(() => { alert('Failed to load order details.'); });
        });
    });
})();
</script>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
