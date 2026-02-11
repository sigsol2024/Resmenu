<?php
/**
 * Orders Management (Manager)
 */

require_once __DIR__ . '/../includes/auth.php';
requireManager();
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/order-functions.php';

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

// Recent orders limit (full list is on restaurant-orders.php)
$limit = 5;
$stmt = $pdo->prepare("SELECT * FROM orders WHERE restaurant_id = ? ORDER BY created_at DESC LIMIT ?");
$stmt->execute([$restaurantId, $limit]);
$orders = $stmt->fetchAll();

$currencySymbol = '₦';

$pageTitle = 'Orders - ' . htmlspecialchars($restaurant['name']);
include __DIR__ . '/../includes/manager-layout.php';
?>

<section class="orders-overview" style="margin-bottom:24px;">
    <h2 class="section-title" style="font-size:1.125rem;font-weight:600;margin-bottom:16px;color:#111827;">Orders Overview</h2>
    <!-- Revenue Chart -->
    <div class="chart-card" style="background:#fff;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,0.1);padding:24px;margin-bottom:24px;">
        <h3 class="chart-title" style="font-size:1rem;font-weight:600;margin-bottom:12px;color:#111827;">Revenue by Period</h3>
        <div class="revenue-chart-filters" style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:16px;">
            <button type="button" class="revenue-range-btn btn-active" data-range="today">Today</button>
            <button type="button" class="revenue-range-btn" data-range="3days">Last 3 days</button>
            <button type="button" class="revenue-range-btn" data-range="7days">Last 7 days</button>
            <button type="button" class="revenue-range-btn" data-range="1month">One month</button>
            <button type="button" class="revenue-range-btn" data-range="all">All time</button>
        </div>
        <div id="revenue-chart" class="simple-bar-chart" style="height:10rem;display:grid;grid-auto-flow:column;gap:2%;align-items:end;padding-inline:2%;position:relative;">
            <!-- Filled by JS -->
        </div>
        <p id="revenue-chart-empty" style="display:none;color:#6b7280;padding:16px 0;">No revenue data for this period.</p>
    </div>
    <!-- Order Status Chart -->
    <?php
    $statusColors = ['pending' => '#f59e0b', 'confirmed' => '#3b82f6', 'on_hold' => '#6b7280', 'cancelled' => '#ef4444', 'completed' => '#10b981'];
    $statusChartData = [];
    $statusMax = max(array_values($statsByStatus)) ?: 1;
    foreach ($statuses as $s) {
        $statusChartData[] = ['label' => ucfirst(str_replace('_',' ',$s)), 'value' => $statsByStatus[$s], 'color' => $statusColors[$s], 'pct' => ($statsByStatus[$s] / $statusMax) * 100];
    }
    ?>
    <div class="chart-card" style="background:#fff;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,0.1);padding:24px;margin-bottom:24px;">
        <h3 class="chart-title" style="font-size:1rem;font-weight:600;margin-bottom:16px;color:#111827;">Orders by Status</h3>
        <div class="simple-bar-chart">
            <?php foreach ($statusChartData as $item): ?>
            <div class="item" style="--clr: <?php echo htmlspecialchars($item['color']); ?>; --val: <?php echo round($item['pct'], 1); ?>">
                <div class="label"><?php echo htmlspecialchars($item['label']); ?></div>
                <div class="value"><?php echo $item['value']; ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
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
        <h2 class="section-title" style="font-size:1.125rem;font-weight:600;color:#111827;">Recent Orders</h2>
        <?php if ($totalOrdersCount > 0): ?>
        <a href="restaurant-orders.php<?php echo $slugParam; ?>" class="btn btn-primary" style="padding:8px 16px;font-size:0.875rem;">View All Orders</a>
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
                    <td style="padding:12px 16px;font-size:0.875rem;">#<?php echo htmlspecialchars(getOrderDisplayNumber($o)); ?></td>
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

<style>
.revenue-range-btn { padding: 6px 12px; border-radius: 6px; border: 1px solid #e5e7eb; background: #fff; font-size: 0.8rem; cursor: pointer; transition: all 0.2s; }
.revenue-range-btn:hover { background: #f3f4f6; }
.revenue-range-btn.btn-active { background: #4f46e5; color: #fff; border-color: #4f46e5; }
.simple-bar-chart > .item { --line-count: 10; --line-color: currentcolor; --line-opacity: 0.25; --item-gap: 2%; --padding-block: 1.5rem; position: relative; isolation: isolate; height: calc(1% * var(--val)); animation: item-height 1s ease forwards; border-radius: 4px 4px 0 0; }
.simple-bar-chart > .item > .label { position: absolute; inset: 100% 0 auto 0; font-size: 0.7rem; color: #6b7280; text-align: center; margin-top: 4px; }
.simple-bar-chart > .item > .value { position: absolute; inset: auto 0 100% 0; font-size: 0.75rem; font-weight: 600; color: #111827; text-align: center; margin-bottom: 4px; }
@keyframes item-height { from { height: 0 } }
</style>
<script>
(function() {
    const slug = <?php echo json_encode($restaurantSlug); ?>;
    const symbol = <?php echo json_encode($currencySymbol); ?>;

    // Revenue chart - fetch and render
    function loadRevenueChart(range) {
        const chartEl = document.getElementById('revenue-chart');
        const emptyEl = document.getElementById('revenue-chart-empty');
        const url = '../api/orders-analytics.php?range=' + encodeURIComponent(range || 'today');
        fetch(url).then(r => r.json()).then(function(data) {
            if (!data.success || !data.revenue_by_date) return;
            const rows = data.revenue_by_date || [];
            chartEl.innerHTML = '';
            if (rows.length === 0) {
                chartEl.style.display = 'none';
                emptyEl.style.display = 'block';
                return;
            }
            emptyEl.style.display = 'none';
            chartEl.style.display = 'grid';
            const maxRev = Math.max.apply(null, rows.map(r => parseFloat(r.revenue))) || 1;
            rows.forEach(function(r) {
                const pct = (parseFloat(r.revenue) / maxRev) * 100;
                const d = r.date ? new Date(r.date + 'T12:00:00').toLocaleDateString(undefined, { month: 'short', day: 'numeric' }) : r.date;
                const div = document.createElement('div');
                div.className = 'item';
                div.style.setProperty('--clr', '#10b981');
                div.style.setProperty('--val', pct);
                div.innerHTML = '<div class="label">' + (d || '') + '</div><div class="value">' + symbol + parseFloat(r.revenue).toFixed(0) + '</div>';
                chartEl.appendChild(div);
            });
        }).catch(function() { chartEl.innerHTML = ''; emptyEl.style.display = 'block'; chartEl.style.display = 'none'; });
    }

    document.querySelectorAll('.revenue-range-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.revenue-range-btn').forEach(function(b) { b.classList.remove('btn-active'); });
            this.classList.add('btn-active');
            loadRevenueChart(this.getAttribute('data-range'));
        });
    });
    loadRevenueChart('today');

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

    function esc(s) {
        if (s == null || s === '') return '';
        const t = String(s);
        return t.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
    }

    document.querySelectorAll('.view-order-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const orderId = this.getAttribute('data-order-id');
            fetch('../api/get-order-details.php?order_id=' + encodeURIComponent(orderId) + '&slug=' + encodeURIComponent(slug))
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        const o = data.order;
                        const items = o.items || [];
                        const itemsHtml = items.map(function(i) {
                            return '<tr><td>' + esc(i.name) + '</td><td>' + (parseInt(i.quantity,10)||1) + '</td><td>' + symbol + parseFloat(i.price||0).toFixed(2) + '</td><td>' + symbol + (parseFloat(i.price||0)*(parseInt(i.quantity,10)||1)).toFixed(2) + '</td></tr>';
                        }).join('');
                        modalTitle.textContent = 'Order #' + (o.order_display_number || orderId);
                        modalBody.innerHTML = '<table style="width:100%;margin-bottom:16px;"><tr><th style="text-align:left;">Customer</th><td>' + esc(o.customer_name) + '</td></tr><tr><th style="text-align:left;">Phone</th><td>' + esc(o.customer_phone) + '</td></tr><tr><th style="text-align:left;">Email</th><td>' + esc(o.customer_email) + '</td></tr><tr><th style="text-align:left;">Address</th><td>' + esc(o.delivery_address) + '</td></tr><tr><th style="text-align:left;">Status</th><td>' + esc(o.status) + '</td></tr></table><h4 style="margin:16px 0 8px;">Items</h4><table style="width:100%;border-collapse:collapse;"><thead><tr style="border-bottom:1px solid #e5e7eb;"><th style="text-align:left;padding:8px;">Item</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead><tbody>' + itemsHtml + '</tbody></table><p style="margin-top:12px;font-weight:600;">Total: ' + esc(symbol + parseFloat(o.total||0).toFixed(2)) + '</p>';
                        modal.style.display = 'flex';
                    }
                })
                .catch(() => { alert('Failed to load order details.'); });
        });
    });
})();
</script>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
