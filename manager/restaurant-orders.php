<?php
/**
 * Restaurant Orders - Full orders list for one restaurant with filters (Manager)
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

$stmt = $pdo->prepare("SELECT * FROM restaurants WHERE id = ?");
$stmt->execute([$restaurantId]);
$restaurant = $stmt->fetch();
if (!$restaurant) die('Restaurant not found.');
if (empty($restaurantSlug)) $restaurantSlug = $restaurant['slug'];

$slugParam = $restaurantSlug ? '?slug=' . urlencode($restaurantSlug) : '';
$statuses = ['pending', 'confirmed', 'on_hold', 'cancelled', 'completed'];
$statusColors = ['pending' => '#f59e0b', 'confirmed' => '#3b82f6', 'on_hold' => '#6b7280', 'cancelled' => '#ef4444', 'completed' => '#10b981'];
$currencySymbol = '₦';

$pageTitle = 'All Orders - ' . htmlspecialchars($restaurant['name']);
include __DIR__ . '/../includes/manager-layout.php';
?>

<div class="page-header" style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px;">
    <div>
        <h1 class="page-title">All Orders</h1>
        <p class="page-subtitle">Full order list with filters for <?php echo htmlspecialchars($restaurant['name']); ?></p>
    </div>
    <a href="orders.php<?php echo $slugParam; ?>" class="btn btn-secondary" style="padding:8px 16px;font-size:0.875rem;">Back to Orders Overview</a>
</div>

<section class="restaurant-orders">
    <div class="settings-card" style="margin-bottom:24px;">
        <h3 style="font-size:0.875rem;font-weight:600;margin-bottom:12px;color:#111827;">Filters</h3>
        <form id="orders-filter-form" style="display:flex;flex-wrap:wrap;gap:16px;align-items:end;">
            <div>
                <label for="start_date" style="display:block;font-size:0.75rem;color:#6b7280;margin-bottom:4px;">Start Date</label>
                <input type="date" id="start_date" name="start_date" style="padding:8px 12px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.875rem;">
            </div>
            <div>
                <label for="end_date" style="display:block;font-size:0.75rem;color:#6b7280;margin-bottom:4px;">End Date</label>
                <input type="date" id="end_date" name="end_date" style="padding:8px 12px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.875rem;">
            </div>
            <div>
                <label for="status_filter" style="display:block;font-size:0.75rem;color:#6b7280;margin-bottom:4px;">Status</label>
                <select id="status_filter" name="status" style="padding:8px 12px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.875rem;min-width:120px;">
                    <option value="all">All</option>
                    <?php foreach ($statuses as $s): ?>
                    <option value="<?php echo htmlspecialchars($s); ?>"><?php echo ucfirst(str_replace('_',' ',$s)); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="padding:8px 16px;">Apply</button>
        </form>
    </div>

    <div class="table-wrapper">
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
            <tbody id="orders-tbody">
                <tr><td colspan="6" style="padding:24px;text-align:center;color:#6b7280;">Loading...</td></tr>
            </tbody>
        </table>
    </div>
</section>

<div id="order-modal" class="order-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center;padding:24px;">
    <div class="order-modal-content" style="background:#fff;border-radius:12px;max-width:560px;width:100%;max-height:90vh;overflow-y:auto;padding:24px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
            <h3 id="order-modal-title" style="font-size:1.25rem;font-weight:600;color:#111827;">Order Details</h3>
            <button type="button" id="order-modal-close" style="background:0;border:0;cursor:pointer;padding:4px;color:#6b7280;font-size:1.5rem;">&times;</button>
        </div>
        <div id="order-modal-body"></div>
    </div>
</div>

<script>
(function() {
    const slug = <?php echo json_encode($restaurantSlug); ?>;
    const symbol = <?php echo json_encode($currencySymbol); ?>;
    const statusColors = <?php echo json_encode($statusColors); ?>;

    function esc(s) {
        if (s == null || s === '') return '';
        const t = String(s);
        return t.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
    }

    function loadOrders() {
        const start = document.getElementById('start_date').value || '';
        const end = document.getElementById('end_date').value || '';
        const status = document.getElementById('status_filter').value || 'all';
        let url = '../api/orders-analytics.php?action=orders';
        if (start) url += '&start_date=' + encodeURIComponent(start);
        if (end) url += '&end_date=' + encodeURIComponent(end);
        if (status && status !== 'all') url += '&status=' + encodeURIComponent(status);

        const tbody = document.getElementById('orders-tbody');
        tbody.innerHTML = '<tr><td colspan="6" style="padding:24px;text-align:center;color:#6b7280;">Loading...</td></tr>';

        fetch(url).then(r => r.json()).then(function(data) {
            const orders = data.success && data.orders ? data.orders : [];
            if (orders.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="padding:24px;text-align:center;color:#6b7280;">No orders found.</td></tr>';
                return;
            }
            tbody.innerHTML = orders.map(function(o) {
                const st = o.status || 'pending';
                const clr = statusColors[st] || '#6b7280';
                const label = (st.charAt(0).toUpperCase() + st.slice(1)).replace('_', ' ');
                const orderId = parseInt(o.id, 10) || 0;
                const dispNum = esc(o.order_display_number || orderId);
                const statusOpts = ['pending','confirmed','on_hold','cancelled','completed'];
                const statusForms = statusOpts.filter(function(s){ return s !== st; }).map(function(s){
                    return '<form method="post" action="../api/update-order-status.php" style="display:contents;"><input type="hidden" name="order_id" value="' + orderId + '"><input type="hidden" name="slug" value="' + esc(slug) + '"><input type="hidden" name="return_to" value="restaurant-orders"><input type="hidden" name="status" value="' + s + '"><button type="submit" class="actions-dropdown-item">Set to ' + (s.charAt(0).toUpperCase() + s.slice(1)).replace('_',' ') + '</button></form>';
                }).join('');
                return '<tr style="border-bottom:1px solid #f3f4f6;">' +
                    '<td style="padding:12px 16px;font-size:0.875rem;">#' + dispNum + '</td>' +
                    '<td style="padding:12px 16px;font-size:0.875rem;">' + esc(o.customer_name) + '</td>' +
                    '<td style="padding:12px 16px;font-size:0.875rem;">' + (o.created_at ? new Date(o.created_at).toLocaleString() : '') + '</td>' +
                    '<td style="padding:12px 16px;font-size:0.875rem;font-weight:600;">' + symbol + parseFloat(o.total || 0).toFixed(2) + '</td>' +
                    '<td style="padding:12px 16px;"><span class="badge" style="background:' + esc(clr) + '22;color:' + esc(clr) + ';padding:4px 10px;border-radius:6px;font-size:0.75rem;font-weight:600;">' + esc(label) + '</span></td>' +
                    '<td class="actions-cell" style="padding:12px 16px;">' +
                    '<button type="button" class="actions-btn" onclick="toggleOrdersDropdown(this)" title="Actions"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/></svg></button>' +
                    '<div class="actions-dropdown"><button type="button" class="view-order-btn actions-dropdown-item" data-order-id="' + orderId + '">View</button><div class="actions-dropdown-divider"></div><div class="actions-dropdown-title">Change Status</div>' + statusForms + '</div>' +
                    '</td></tr>';
            }).join('');
            initOrderHandlers();
        }).catch(function() {
            tbody.innerHTML = '<tr><td colspan="6" style="padding:24px;text-align:center;color:#ef4444;">Failed to load orders.</td></tr>';
        });
    }

    function initOrderHandlers() {
        document.querySelectorAll('.view-order-btn').forEach(function(btn) {
            btn.removeEventListener('click', handleViewOrder);
            btn.addEventListener('click', handleViewOrder);
        });
    }
    window.toggleOrdersDropdown = function(btn) {
        document.querySelectorAll('.actions-dropdown.show').forEach(function(d){ d.classList.remove('show'); });
        var dropdown = btn.nextElementSibling;
        dropdown.classList.toggle('show');
        document.addEventListener('click', function closeDropdown(e) {
            if (!btn.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.classList.remove('show');
                document.removeEventListener('click', closeDropdown);
            }
        });
    };
    function handleViewOrder() {
        const orderId = this.getAttribute('data-order-id');
        fetch('../api/get-order-details.php?order_id=' + encodeURIComponent(orderId) + '&slug=' + encodeURIComponent(slug))
            .then(r => r.json())
            .then(function(data) {
                if (data.success) {
                    const o = data.order;
                    const items = o.items || [];
                    const itemsHtml = items.map(function(i){ return '<tr><td>' + esc(i.name) + '</td><td>' + (parseInt(i.quantity,10)||1) + '</td><td>' + symbol + parseFloat(i.price||0).toFixed(2) + '</td><td>' + symbol + (parseFloat(i.price||0)*(parseInt(i.quantity,10)||1)).toFixed(2) + '</td></tr>'; }).join('');
                    document.getElementById('order-modal-title').textContent = 'Order #' + (o.order_display_number || orderId);
                    document.getElementById('order-modal-body').innerHTML = '<table style="width:100%;margin-bottom:16px;"><tr><th style="text-align:left;">Customer</th><td>' + esc(o.customer_name) + '</td></tr><tr><th style="text-align:left;">Phone</th><td>' + esc(o.customer_phone) + '</td></tr><tr><th style="text-align:left;">Email</th><td>' + esc(o.customer_email) + '</td></tr><tr><th style="text-align:left;">Address</th><td>' + esc(o.delivery_address) + '</td></tr><tr><th style="text-align:left;">Status</th><td>' + esc(o.status) + '</td></tr></table><h4 style="margin:16px 0 8px;">Items</h4><table style="width:100%;border-collapse:collapse;"><thead><tr style="border-bottom:1px solid #e5e7eb;"><th style="text-align:left;padding:8px;">Item</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead><tbody>' + itemsHtml + '</tbody></table><p style="margin-top:12px;font-weight:600;">Total: ' + esc(symbol + parseFloat(o.total||0).toFixed(2)) + '</p>';
                    document.getElementById('order-modal').style.display = 'flex';
                }
            })
            .catch(function() { alert('Failed to load order details.'); });
    }

    document.getElementById('orders-filter-form').addEventListener('submit', function(e) {
        e.preventDefault();
        loadOrders();
    });

    document.getElementById('order-modal-close').onclick = function() {
        document.getElementById('order-modal').style.display = 'none';
    };
    document.getElementById('order-modal').onclick = function(e) {
        if (e.target === this) this.style.display = 'none';
    };

    loadOrders();
})();
</script>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
