<?php
/**
 * Restaurants Overview - All restaurants with total orders and total revenue (Super Admin)
 */

require_once __DIR__ . '/../includes/auth.php';
requireSuperAdmin();

require_once __DIR__ . '/../includes/functions.php';

$pdo = getDBConnection();

// Get all restaurants for the filter dropdown
$restaurants = [];
if ($pdo) {
    $stmt = $pdo->query("SELECT id, name, slug FROM restaurants ORDER BY name ASC");
    $restaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$pageTitle = 'Restaurants Overview';
include __DIR__ . '/../includes/admin-layout.php';
?>

<section class="restaurants-overview" style="margin-bottom:24px;">
    <h1 class="page-title" style="font-size:1.5rem;font-weight:600;margin-bottom:24px;color:#111827;">Restaurants Overview</h1>

    <div class="filters card" style="margin-bottom:24px;">
        <h3 style="font-size:0.875rem;font-weight:600;margin-bottom:12px;color:#111827;">Filters</h3>
        <form id="overview-filter-form" style="display:flex;flex-wrap:wrap;gap:16px;align-items:end;">
            <div>
                <label for="start_date" style="display:block;font-size:0.75rem;color:#6b7280;margin-bottom:4px;">Start Date</label>
                <input type="date" id="start_date" name="start_date" class="form-input" style="padding:8px 12px;">
            </div>
            <div>
                <label for="end_date" style="display:block;font-size:0.75rem;color:#6b7280;margin-bottom:4px;">End Date</label>
                <input type="date" id="end_date" name="end_date" class="form-input" style="padding:8px 12px;">
            </div>
            <div>
                <label for="restaurant_filter" style="display:block;font-size:0.75rem;color:#6b7280;margin-bottom:4px;">Restaurant</label>
                <select id="restaurant_filter" name="restaurant_id" class="form-select" style="padding:8px 12px;min-width:200px;">
                    <option value="">All Restaurants</option>
                    <?php foreach ($restaurants as $r): ?>
                    <option value="<?php echo (int)$r['id']; ?>"><?php echo htmlspecialchars($r['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="padding:8px 16px;">Apply</button>
        </form>
    </div>

    <div class="table-wrapper" style="background:#fff;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,0.1);overflow-x:auto;">
        <table class="restaurants-table" style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:2px solid #e5e7eb;">
                    <th style="text-align:left;padding:12px 16px;font-size:0.75rem;color:#6b7280;font-weight:600;">Restaurant Name</th>
                    <th style="text-align:left;padding:12px 16px;font-size:0.75rem;color:#6b7280;font-weight:600;">Slug</th>
                    <th style="text-align:left;padding:12px 16px;font-size:0.75rem;color:#6b7280;font-weight:600;">Total Orders</th>
                    <th style="text-align:left;padding:12px 16px;font-size:0.75rem;color:#6b7280;font-weight:600;">Total Revenue</th>
                    <th style="text-align:left;padding:12px 16px;font-size:0.75rem;color:#6b7280;font-weight:600;">Actions</th>
                </tr>
            </thead>
            <tbody id="overview-tbody">
                <tr><td colspan="5" style="padding:24px;text-align:center;color:#6b7280;">Loading...</td></tr>
            </tbody>
        </table>
    </div>
</section>

<script>
(function() {
    const restaurants = <?php echo json_encode($restaurants); ?>;

    function esc(s) {
        if (s == null || s === '') return '';
        const t = String(s);
        return t.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
    }

    function loadOverview() {
        const start = document.getElementById('start_date').value || '';
        const end = document.getElementById('end_date').value || '';
        const restaurantId = document.getElementById('restaurant_filter').value || '';
        let url = '../api/orders-analytics.php?action=restaurants_overview&';
        if (start) url += 'start_date=' + encodeURIComponent(start) + '&';
        if (end) url += 'end_date=' + encodeURIComponent(end) + '&';
        if (restaurantId) url += 'restaurant_id=' + encodeURIComponent(restaurantId) + '&';

        const tbody = document.getElementById('overview-tbody');
        tbody.innerHTML = '<tr><td colspan="5" style="padding:24px;text-align:center;color:#6b7280;">Loading...</td></tr>';

        fetch(url).then(r => r.json()).then(function(data) {
            if (!data.success) {
                const msg = data.message || 'Failed to load data.';
                tbody.innerHTML = '<tr><td colspan="5" style="padding:24px;text-align:center;color:#ef4444;">' + esc(msg) + '</td></tr>';
                return;
            }
            const rows = data.restaurants || [];
            if (rows.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" style="padding:24px;text-align:center;color:#6b7280;">No data found.</td></tr>';
                return;
            }
            tbody.innerHTML = rows.map(function(r) {
                return '<tr style="border-bottom:1px solid #f3f4f6;">' +
                    '<td style="padding:12px 16px;font-size:0.875rem;font-weight:500;">' + esc(r.name) + '</td>' +
                    '<td style="padding:12px 16px;font-size:0.75rem;font-family:monospace;color:#6b7280;">' + esc(r.slug) + '</td>' +
                    '<td style="padding:12px 16px;font-size:0.875rem;">' + parseInt(r.total_orders || 0, 10) + '</td>' +
                    '<td style="padding:12px 16px;font-size:0.875rem;font-weight:600;">₦' + parseFloat(r.total_revenue || 0).toFixed(2) + '</td>' +
                    '<td style="padding:12px 16px;"><a href="restaurant-view.php?slug=' + encodeURIComponent(r.slug || '') + '" class="btn btn-primary" style="padding:6px 12px;font-size:0.8rem;">View / Manage</a></td></tr>';
            }).join('');
        }).catch(function() {
            tbody.innerHTML = '<tr><td colspan="5" style="padding:24px;text-align:center;color:#ef4444;">Failed to load data.</td></tr>';
        });
    }

    document.getElementById('overview-filter-form').addEventListener('submit', function(e) {
        e.preventDefault();
        loadOverview();
    });

    loadOverview();
})();
</script>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
