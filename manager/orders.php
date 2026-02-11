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
    <!-- Revenue Line Chart -->
    <div class="chart-card revenue-chart-card" style="background:#fff;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,0.1);padding:24px;margin-bottom:24px;">
        <div style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:16px;margin-bottom:16px;">
            <div>
                <h3 class="chart-title" style="font-size:1rem;font-weight:600;margin:0;color:#111827;">Revenue Growth Over Time</h3>
                <p id="revenue-trend" style="font-size:0.75rem;margin:4px 0 0;color:#6b7280;display:flex;align-items:center;gap:4px;"></p>
            </div>
            <div class="revenue-chart-filters" style="display:flex;flex-wrap:wrap;gap:6px;">
                <button type="button" class="revenue-range-btn btn-active" data-range="today">Today</button>
                <button type="button" class="revenue-range-btn" data-range="3days">3 Days</button>
                <button type="button" class="revenue-range-btn" data-range="7days">7 Days</button>
                <button type="button" class="revenue-range-btn" data-range="1month">1 Month</button>
                <button type="button" class="revenue-range-btn" data-range="all">All Time</button>
            </div>
        </div>
        <div id="revenue-chart-wrapper" style="position:relative;height:280px;min-width:0;">
            <div id="revenue-chart-empty" style="display:none;color:#6b7280;padding:60px 24px;text-align:center;font-size:0.875rem;">No revenue data for this period.</div>
            <div id="revenue-line-chart" style="display:none;height:100%;width:100%;position:relative;">
                <svg id="revenue-svg" style="width:100%;height:100%;" preserveAspectRatio="none" viewBox="0 0 800 280"></svg>
                <div id="revenue-tooltip" class="revenue-tooltip" style="display:none;position:absolute;background:#111827;color:#fff;padding:8px 12px;border-radius:8px;font-size:0.75rem;font-weight:500;pointer-events:none;z-index:10;box-shadow:0 4px 12px rgba(0,0,0,0.15);white-space:nowrap;min-width:100px;line-height:1.4;"></div>
            </div>
        </div>
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
                        <button type="button" class="view-order-btn btn btn-primary" data-order-id="<?php echo (int)$o['id']; ?>" style="padding:6px 12px;font-size:0.8rem;">View</button>
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
.revenue-range-btn { padding: 6px 12px; border-radius: 6px; border: 1px solid #e5e7eb; background: #fff; font-size: 0.75rem; font-weight: 500; cursor: pointer; transition: all 0.2s; }
.revenue-range-btn:hover { background: #f3f4f6; }
.revenue-range-btn.btn-active { background: var(--primary, #D97706); color: #fff; border-color: var(--primary, #D97706); }
.revenue-chart-card .revenue-point { cursor: pointer; }
.revenue-chart-card .revenue-point:hover { opacity: 1; }
.simple-bar-chart > .item { --line-count: 10; --line-color: currentcolor; --line-opacity: 0.25; --item-gap: 2%; --padding-block: 1.5rem; position: relative; isolation: isolate; height: calc(1% * var(--val)); animation: item-height 1s ease forwards; border-radius: 4px 4px 0 0; }
.simple-bar-chart > .item > .label { position: absolute; inset: 100% 0 auto 0; font-size: 0.7rem; color: #6b7280; text-align: center; margin-top: 4px; }
.simple-bar-chart > .item > .value { position: absolute; inset: auto 0 100% 0; font-size: 0.75rem; font-weight: 600; color: #111827; text-align: center; margin-bottom: 4px; }
@keyframes item-height { from { height: 0 } }
</style>
<script>
(function() {
    const slug = <?php echo json_encode($restaurantSlug); ?>;
    const symbol = <?php echo json_encode($currencySymbol); ?>;

    // Revenue line chart - fetch and render with hover tooltips
    function loadRevenueChart(range) {
        const wrapper = document.getElementById('revenue-chart-wrapper');
        const chartEl = document.getElementById('revenue-line-chart');
        const emptyEl = document.getElementById('revenue-chart-empty');
        const svgEl = document.getElementById('revenue-svg');
        const tooltipEl = document.getElementById('revenue-tooltip');
        const trendEl = document.getElementById('revenue-trend');
        const url = '../api/orders-analytics.php?range=' + encodeURIComponent(range || 'today');
        fetch(url).then(r => r.json()).then(function(data) {
            if (!data.success || !data.revenue_by_date) return;
            const rows = data.revenue_by_date || [];
            if (rows.length === 0) {
                chartEl.style.display = 'none';
                emptyEl.style.display = 'block';
                trendEl.innerHTML = '';
                return;
            }
            emptyEl.style.display = 'none';
            chartEl.style.display = 'block';
            const revenues = rows.map(function(r) { return parseFloat(r.revenue) || 0; });
            const maxRev = Math.max.apply(null, revenues) || 1;
            const minRev = Math.min.apply(null, revenues);
            const rangeRev = maxRev - minRev || 1;
            const pad = 0.1;
            const chartW = 800; const chartH = 280;
            const padL = 48; const padR = 24; const padT = 24; const padB = 40;
            const plotW = chartW - padL - padR;
            const plotH = chartH - padT - padB;
            const firstVal = revenues[0] || 0;
            const lastVal = revenues[revenues.length - 1] || 0;
            const isGrowth = lastVal >= firstVal;
            const lineColor = isGrowth ? '#059669' : '#DC2626';
            const gradientId = 'revGrad-' + (isGrowth ? 'up' : 'down');
            const pts = rows.map(function(r, i) {
                const val = parseFloat(r.revenue) || 0;
                const y = padT + plotH - ((val - minRev) / rangeRev) * plotH;
                const x = padL + (rows.length > 1 ? (i / (rows.length - 1)) * plotW : plotW / 2);
                return { x: x, y: y, val: val, date: r.date };
            });
            const pathD = pts.map(function(p, i) { return (i === 0 ? 'M' : 'L') + p.x + ' ' + p.y; }).join(' ');
            const areaD = pathD + ' L' + (padL + plotW) + ' ' + (padT + plotH) + ' L' + padL + ' ' + (padT + plotH) + ' Z';
            const yTicks = 5;
            let html = '<defs><linearGradient id="' + gradientId + '" x1="0%" y1="0%" x2="0%" y2="100%">' +
                '<stop offset="0%" style="stop-color:' + lineColor + ';stop-opacity:0.25"/>' +
                '<stop offset="100%" style="stop-color:' + lineColor + ';stop-opacity:0"/></linearGradient></defs>';
            for (var g = 0; g <= yTicks; g++) {
                var gy = padT + (g / yTicks) * plotH;
                var gval = (maxRev - (g / yTicks) * (maxRev - minRev)).toFixed(0);
                html += '<line x1="' + padL + '" y1="' + gy + '" x2="' + (padL + plotW) + '" y2="' + gy + '" stroke="#e5e7eb" stroke-width="1"/>';
                html += '<text x="' + (padL - 8) + '" y="' + (gy + 4) + '" text-anchor="end" font-size="10" fill="#6b7280">' + symbol + gval + '</text>';
            }
            html += '<path d="' + areaD + '" fill="url(#' + gradientId + ')"/>';
            html += '<path d="' + pathD + '" fill="none" stroke="' + lineColor + '" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>';
            for (var i = 0; i < pts.length; i++) {
                var p = pts[i];
                var d = p.date ? new Date(p.date + 'T12:00:00').toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: pts.length > 14 ? '2-digit' : undefined }) : p.date;
                html += '<circle class="revenue-point" cx="' + p.x + '" cy="' + p.y + '" r="6" fill="' + lineColor + '" stroke="#fff" stroke-width="2" opacity="0.9" data-date="' + (d || '') + '" data-rev="' + symbol + p.val.toFixed(2) + '"/>';
            }
            var step = Math.max(1, Math.floor(pts.length / 6));
            for (var xi = 0; xi < pts.length; xi += step) {
                var px = pts[xi];
                var dx = px.date ? new Date(px.date + 'T12:00:00').toLocaleDateString(undefined, { month: 'short', day: 'numeric' }) : '';
                html += '<text x="' + px.x + '" y="' + (chartH - 8) + '" text-anchor="middle" font-size="10" fill="#6b7280">' + dx + '</text>';
            }
            svgEl.innerHTML = html;
            var trendText = '';
            if (rows.length >= 2 && firstVal > 0) {
                var pct = ((lastVal - firstVal) / firstVal * 100).toFixed(1);
                trendText = (parseFloat(pct) >= 0 ? '+' : '') + pct + '% vs first day';
            } else if (rows.length >= 1) {
                trendText = symbol + lastVal.toFixed(2) + ' total';
            }
            trendEl.innerHTML = isGrowth ? '<span style="color:#059669">&#9650;</span> ' + trendText : '<span style="color:#DC2626">&#9660;</span> ' + trendText;
            function showTooltip(el, e) {
                var rect = wrapper.getBoundingClientRect();
                var x = e.clientX - rect.left;
                var y = e.clientY - rect.top;
                tooltipEl.innerHTML = '<strong>' + (el.getAttribute('data-date') || '') + '</strong><br/>Revenue: ' + (el.getAttribute('data-rev') || '');
                tooltipEl.style.display = 'block';
                tooltipEl.style.left = Math.min(x + 12, rect.width - 140) + 'px';
                tooltipEl.style.top = Math.max(y - 50, 8) + 'px';
            }
            function hideTooltip() { tooltipEl.style.display = 'none'; }
            wrapper.querySelectorAll('.revenue-point').forEach(function(el) {
                el.addEventListener('mouseenter', function(e) { showTooltip(el, e); });
                el.addEventListener('mouseleave', hideTooltip);
                el.addEventListener('mousemove', function(e) { showTooltip(el, e); });
            });
        }).catch(function() {
            document.getElementById('revenue-line-chart').style.display = 'none';
            emptyEl.style.display = 'block';
            trendEl.innerHTML = '';
        });
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
