<?php
/**
 * Reservations Management (Manager)
 * Overview: stats, deposit settings, recent reservations. Template 4 only.
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

require_once __DIR__ . '/../includes/subscription.php';
$showManagerUpgradeOverlay = false;
$managerUpgradePlans = [];
$managerUpgradeBillingUrl = (defined('SITE_URL') && SITE_URL !== '') ? rtrim(SITE_URL, '/') . '/manager/billing.php' : '/manager/billing.php';
if (!hasFeatureAccess($restaurantId, 'table_reservations')) {
    $showManagerUpgradeOverlay = true;
    $managerUpgradeFeature = 'table_reservations';
    $allPlans = getSubscriptionPlans(true);
    foreach ($allPlans as $p) {
        $slug = strtolower((string)($p['slug'] ?? ''));
        if ($slug === 'enterprise') $managerUpgradePlans[] = $p;
    }
}

$reservationSettings = getReservationSettings($restaurantId);
$depositAmount = (float)($reservationSettings['deposit_amount'] ?? 0);

$statuses = ['pending', 'confirmed', 'rejected', 'cancelled', 'completed'];
$statsByStatus = [];
foreach ($statuses as $s) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM table_reservations WHERE restaurant_id = ? AND status = ?");
    $stmt->execute([$restaurantId, $s]);
    $statsByStatus[$s] = (int) $stmt->fetchColumn();
}

$stmt = $pdo->prepare("SELECT COUNT(*) FROM table_reservations WHERE restaurant_id = ? AND reservation_date = CURDATE()");
$stmt->execute([$restaurantId]);
$todayCount = (int) $stmt->fetchColumn();

$totalReservations = array_sum($statsByStatus);

$limit = 5;
$stmt = $pdo->prepare("SELECT * FROM table_reservations WHERE restaurant_id = ? ORDER BY reservation_date DESC, reservation_time DESC, created_at DESC LIMIT ?");
$stmt->execute([$restaurantId, $limit]);
$reservations = $stmt->fetchAll();

$currencySymbol = '₦';

$pageTitle = 'Reservations - ' . htmlspecialchars($restaurant['name']);
include __DIR__ . '/../includes/manager-layout.php';
?>
<div class="resmenu-manager-content-wrap <?php echo $showManagerUpgradeOverlay ? 'resmenu-manager-blurred' : ''; ?>">
<div class="page-header" style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px;">
    <div>
        <h1 class="page-title">Reservations</h1>
        <p class="page-subtitle">Manage table reservations for <?php echo htmlspecialchars($restaurant['name']); ?></p>
    </div>
    <a href="table-inventory.php<?php echo $slugParam; ?>" class="btn btn-primary" style="padding:8px 16px;font-size:0.875rem;">Manage Table Inventory</a>
</div>

<?php
$statusLabels = ['pending' => 'Pending', 'confirmed' => 'Confirmed', 'rejected' => 'Rejected', 'cancelled' => 'Cancelled', 'completed' => 'Completed'];
$statusColors = ['pending' => '#f59e0b', 'confirmed' => '#10b981', 'rejected' => '#ef4444', 'cancelled' => '#6b7280', 'completed' => '#3b82f6'];
?>
<section class="reservations-overview" style="margin-bottom:24px;">
    <div class="stats reservations-stats" style="display:grid;grid-template-columns:repeat(5, minmax(0, 1fr));gap:16px;margin-bottom:24px;">
        <?php foreach ($statuses as $s): ?>
        <div class="stat-card" style="background:#fff;padding:16px;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,0.1);border-left:3px solid <?php echo $statusColors[$s] ?? '#e5e7eb'; ?>;">
            <div class="stat-label" style="font-size:0.7rem;color:<?php echo $statusColors[$s] ?? '#6b7280'; ?>;text-transform:uppercase;margin-bottom:4px;font-weight:600;"><?php echo htmlspecialchars($statusLabels[$s]); ?></div>
            <div class="stat-value" style="font-size:1.5rem;font-weight:700;color:#111827;"><?php echo $statsByStatus[$s]; ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="settings-card" style="padding:24px;margin-bottom:24px;">
        <h3 style="font-size:1rem;font-weight:600;margin-bottom:16px;color:#111827;">Deposit Settings</h3>
        <form id="deposit-form" style="display:flex;flex-wrap:wrap;gap:16px;align-items:flex-end;">
            <div>
                <label for="deposit_amount" style="display:block;font-size:0.75rem;color:#6b7280;margin-bottom:4px;">Deposit Amount (₦)</label>
                <input type="number" id="deposit_amount" name="deposit_amount" min="0" step="0.01" value="<?php echo number_format($depositAmount, 2, '.', ''); ?>" style="padding:8px 12px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.875rem;min-width:120px;"/>
            </div>
            <div style="display:flex;flex-wrap:wrap;align-items:center;gap:12px;">
                <button type="submit" id="deposit-save-btn" class="btn btn-primary" style="padding:8px 16px;">Save Deposit</button>
                <span id="deposit-status" role="status" aria-live="polite" style="font-size:0.875rem;font-weight:500;display:none;"></span>
            </div>
        </form>
        <p style="font-size:0.75rem;color:#6b7280;margin-top:8px;">Amount charged at checkout when customers make a reservation. Set to 0 for no deposit.</p>
    </div>

    <!-- Revenue Chart (deposits collected) -->
    <div class="settings-card revenue-chart-card" style="padding:24px;margin-bottom:24px;">
        <div style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:16px;margin-bottom:16px;">
            <div>
                <h3 class="chart-title" style="font-size:1rem;font-weight:600;margin:0;color:#111827;">Deposit Revenue Over Time</h3>
                <p id="res-revenue-trend" style="font-size:0.75rem;margin:4px 0 0;color:#6b7280;display:flex;align-items:center;gap:4px;"></p>
            </div>
            <div class="revenue-chart-filters" style="display:flex;flex-wrap:wrap;gap:6px;">
                <button type="button" class="res-revenue-range-btn" data-range="today">Today</button>
                <button type="button" class="res-revenue-range-btn" data-range="3days">3 Days</button>
                <button type="button" class="res-revenue-range-btn" data-range="7days">7 Days</button>
                <button type="button" class="res-revenue-range-btn" data-range="1month">1 Month</button>
                <button type="button" class="res-revenue-range-btn btn-active" data-range="all">All Time</button>
            </div>
        </div>
        <div id="res-revenue-chart-wrapper" style="position:relative;height:280px;min-width:0;">
            <div id="res-revenue-chart-empty" style="display:none;color:#6b7280;padding:60px 24px;text-align:center;font-size:0.875rem;">No deposit revenue for this period.</div>
            <div id="res-revenue-line-chart" style="display:none;height:100%;width:100%;position:relative;">
                <svg id="res-revenue-svg" style="width:100%;height:100%;" preserveAspectRatio="none" viewBox="0 0 800 280"></svg>
                <div id="res-revenue-tooltip" style="display:none;position:absolute;background:#111827;color:#fff;padding:8px 12px;border-radius:8px;font-size:0.75rem;font-weight:500;pointer-events:none;z-index:10;box-shadow:0 4px 12px rgba(0,0,0,0.15);white-space:nowrap;min-width:100px;line-height:1.4;"></div>
            </div>
        </div>
    </div>
</section>

<section class="reservations-list">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
        <h2 class="section-title" style="font-size:1.125rem;font-weight:600;color:#111827;">Recent Reservations</h2>
        <?php if ($totalReservations > 0): ?>
        <a href="restaurant-reservations.php<?php echo $slugParam; ?>" class="btn btn-primary" style="padding:8px 16px;font-size:0.875rem;">View All Reservations</a>
        <?php endif; ?>
    </div>

    <?php if (empty($reservations)): ?>
    <p style="color:#6b7280;padding:24px;text-align:center;">No reservations yet.</p>
    <?php else: ?>
    <div class="table-wrapper reservations-table-desktop">
        <table class="orders-table" style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:1px solid #e5e7eb;">
                    <th style="text-align:left;padding:12px 16px;font-size:0.75rem;color:#6b7280;font-weight:600;">#</th>
                    <th style="text-align:left;padding:12px 16px;font-size:0.75rem;color:#6b7280;font-weight:600;">Date & Time</th>
                    <th style="text-align:left;padding:12px 16px;font-size:0.75rem;color:#6b7280;font-weight:600;">Guest</th>
                    <th style="text-align:left;padding:12px 16px;font-size:0.75rem;color:#6b7280;font-weight:600;">Guests</th>
                    <th style="text-align:left;padding:12px 16px;font-size:0.75rem;color:#6b7280;font-weight:600;">Deposit</th>
                    <th style="text-align:left;padding:12px 16px;font-size:0.75rem;color:#6b7280;font-weight:600;">Status</th>
                    <th style="text-align:left;padding:12px 16px;font-size:0.75rem;color:#6b7280;font-weight:600;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reservations as $r): ?>
                <tr style="border-bottom:1px solid #f3f4f6;">
                    <td style="padding:12px 16px;font-size:0.875rem;font-weight:600;">#<?php echo htmlspecialchars(getReservationDisplayNumber($r)); ?></td>
                    <td style="padding:12px 16px;font-size:0.875rem;"><?php echo date('M j, Y', strtotime($r['reservation_date'])); ?> <?php echo date('g:i A', strtotime($r['reservation_time'])); ?></td>
                    <td style="padding:12px 16px;font-size:0.875rem;"><?php echo htmlspecialchars($r['guest_name']); ?></td>
                    <td style="padding:12px 16px;font-size:0.875rem;"><?php echo (int)$r['party_size']; ?></td>
                    <td style="padding:12px 16px;font-size:0.875rem;"><?php echo $currencySymbol . number_format((float)($r['deposit_amount'] ?? 0), 2); ?> <?php echo !empty($r['deposit_paid']) ? '<span style="color:#10b981;">(Paid)</span>' : ''; ?></td>
                    <td style="padding:12px 16px;">
                        <?php
                        $rstatus = $r['status'] ?? 'pending';
                        $rstyle = $statusColors[$rstatus] ?? '#6b7280';
                        $rlabel = $statusLabels[$rstatus] ?? ucfirst($rstatus);
                        ?>
                        <span class="badge" style="background:<?php echo $rstyle; ?>20;color:<?php echo $rstyle; ?>;padding:4px 10px;border-radius:6px;font-size:0.75rem;font-weight:600;"><?php echo htmlspecialchars($rlabel); ?></span>
                    </td>
                    <td class="actions-cell" style="padding:12px 16px;position:relative;">
                        <button type="button" class="actions-btn res-actions-btn" title="Actions">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                            </svg>
                        </button>
                        <div class="actions-dropdown">
                            <button type="button" class="view-reservation-btn actions-dropdown-item" data-reservation-id="<?php echo (int)$r['id']; ?>">View</button>
                            <div class="actions-dropdown-divider"></div>
                            <div class="actions-dropdown-title">Change Status</div>
                            <?php foreach (['confirmed', 'rejected'] as $s): ?>
                            <?php if (($r['status'] ?? 'pending') !== $s): ?>
                            <form method="post" action="../api/update-reservation-status.php" style="display:contents;">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(getCSRFToken()); ?>"/>
                                <input type="hidden" name="reservation_id" value="<?php echo (int)$r['id']; ?>"/>
                                <input type="hidden" name="slug" value="<?php echo htmlspecialchars($restaurantSlug); ?>"/>
                                <input type="hidden" name="status" value="<?php echo htmlspecialchars($s); ?>"/>
                                <button type="submit" class="actions-dropdown-item"><?php echo $s === 'confirmed' ? 'Approve' : 'Reject'; ?></button>
                            </form>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="reservations-list-mobile" aria-label="Recent reservations (mobile)">
        <?php foreach ($reservations as $r): ?>
            <?php
            $rstatus = $r['status'] ?? 'pending';
            $rstyle = $statusColors[$rstatus] ?? '#6b7280';
            $rlabel = $statusLabels[$rstatus] ?? ucfirst($rstatus);
            $dateTime = date('M j, Y', strtotime($r['reservation_date'])) . ' ' . date('g:i A', strtotime($r['reservation_time']));
            $depositText = $currencySymbol . number_format((float)($r['deposit_amount'] ?? 0), 2) . (!empty($r['deposit_paid']) ? ' (Paid)' : '');
            ?>
            <details class="res-card">
                <summary class="res-card-summary">
                    <div class="res-card-left">
                        <div class="res-card-top">
                            <span class="res-card-id">#<?php echo htmlspecialchars(getReservationDisplayNumber($r)); ?></span>
                            <span class="res-card-date"><?php echo htmlspecialchars($dateTime); ?></span>
                        </div>
                        <div class="res-card-bottom">
                            <span class="res-card-guest"><?php echo htmlspecialchars($r['guest_name']); ?></span>
                            <span class="res-card-dot">•</span>
                            <span class="res-card-party"><?php echo (int)$r['party_size']; ?> guests</span>
                        </div>
                    </div>
                    <div class="res-card-right">
                        <span class="res-badge" style="background:<?php echo $rstyle; ?>20;color:<?php echo $rstyle; ?>;"><?php echo htmlspecialchars($rlabel); ?></span>
                        <span class="res-card-chevron" aria-hidden="true">▾</span>
                    </div>
                </summary>
                <div class="res-card-body">
                    <div class="res-card-metrics">
                        <div class="res-metric">
                            <span class="res-metric-label">Deposit</span>
                            <span class="res-metric-value" style="<?php echo !empty($r['deposit_paid']) ? 'color:#10b981;' : ''; ?>"><?php echo htmlspecialchars($depositText); ?></span>
                        </div>
                        <div class="res-metric">
                            <span class="res-metric-label">Guests</span>
                            <span class="res-metric-value"><?php echo (int)$r['party_size']; ?></span>
                        </div>
                    </div>
                    <div class="res-card-actions">
                        <button type="button" class="btn btn-secondary view-reservation-btn" data-reservation-id="<?php echo (int)$r['id']; ?>">View</button>
                        <?php foreach (['confirmed', 'rejected'] as $s): ?>
                            <?php if (($r['status'] ?? 'pending') !== $s): ?>
                                <form method="post" action="../api/update-reservation-status.php">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(getCSRFToken()); ?>"/>
                                    <input type="hidden" name="reservation_id" value="<?php echo (int)$r['id']; ?>"/>
                                    <input type="hidden" name="slug" value="<?php echo htmlspecialchars($restaurantSlug); ?>"/>
                                    <input type="hidden" name="status" value="<?php echo htmlspecialchars($s); ?>"/>
                                    <button type="submit" class="btn <?php echo $s === 'confirmed' ? 'btn-primary' : 'btn-danger'; ?>">
                                        <?php echo $s === 'confirmed' ? 'Approve' : 'Reject'; ?>
                                    </button>
                                </form>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </details>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>

<div id="reservation-modal" class="order-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center;padding:24px;">
    <div class="order-modal-content" style="background:#fff;border-radius:12px;max-width:560px;width:100%;max-height:90vh;overflow-y:auto;padding:24px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
            <h3 id="reservation-modal-title" style="font-size:1.25rem;font-weight:600;color:#111827;">Reservation Details</h3>
            <button type="button" id="reservation-modal-close" style="background:0;border:0;cursor:pointer;padding:4px;color:#6b7280;font-size:1.5rem;">&times;</button>
        </div>
        <div id="reservation-modal-body"></div>
    </div>
</div>

<style>
.reservations-stats .stat-card { min-height: 70px; }
@media (max-width: 1200px) { .reservations-stats { grid-template-columns: repeat(3, 1fr) !important; } }
@media (max-width: 768px) { .reservations-stats { grid-template-columns: repeat(2, 1fr) !important; } }
.reservations-list .actions-cell { position: relative; }
.reservations-list .actions-dropdown { z-index: 50; right: 100%; left: auto; top: 0; margin-right: 6px; min-width: 160px; }
.reservations-list-mobile { display:none; }
.res-card { background:#fff; border:1px solid #e5e7eb; border-radius:12px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,0.06); }
.res-card + .res-card { margin-top:12px; }
.res-card-summary { list-style:none; cursor:pointer; padding:14px 14px; display:flex; align-items:center; justify-content:space-between; gap:10px; }
.res-card-summary::-webkit-details-marker { display:none; }
.res-card-left { min-width:0; display:flex; flex-direction:column; gap:6px; }
.res-card-top { display:flex; gap:10px; align-items:baseline; min-width:0; }
.res-card-id { font-weight:700; color:#111827; font-size:0.95rem; flex-shrink:0; }
.res-card-date { color:#6b7280; font-size:0.8rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.res-card-bottom { display:flex; gap:8px; align-items:center; color:#374151; font-size:0.85rem; min-width:0; }
.res-card-guest { font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:180px; }
.res-card-dot { color:#9ca3af; }
.res-card-right { display:flex; align-items:center; gap:10px; flex-shrink:0; }
.res-badge { padding:4px 10px; border-radius:999px; font-size:0.75rem; font-weight:700; }
.res-card-chevron { color:#6b7280; font-size:0.9rem; transition:transform .15s ease; }
.res-card[open] .res-card-chevron { transform:rotate(180deg); }
.res-card-body { border-top:1px solid #f3f4f6; padding:12px 14px 14px; }
.res-card-metrics { display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:12px; }
.res-metric { background:#f9fafb; border:1px solid #e5e7eb; border-radius:10px; padding:10px 10px; }
.res-metric-label { display:block; font-size:0.7rem; text-transform:uppercase; color:#6b7280; font-weight:700; letter-spacing:.04em; margin-bottom:4px; }
.res-metric-value { font-size:0.9rem; color:#111827; font-weight:700; }
.res-card-actions { display:flex; gap:8px; flex-wrap:wrap; }
.res-card-actions .btn { padding:8px 12px; border-radius:10px; font-size:0.85rem; }
.res-card-actions form { margin:0; }
@media (max-width: 768px) {
    .reservations-table-desktop { display:none; }
    .reservations-list-mobile { display:block; }
    .reservations-list .actions-dropdown { right: 0; left: auto; top: 100%; margin-right: 0; margin-top: 6px; } /* safety if table shows */
    .table-wrapper { overflow: visible; } /* prevent horizontal scroll wrappers */
}
.res-revenue-range-btn { padding: 6px 12px; border-radius: 6px; border: 1px solid #e5e7eb; background: #fff; font-size: 0.75rem; font-weight: 500; cursor: pointer; transition: all 0.2s; }
.res-revenue-range-btn:hover { background: #f3f4f6; }
.res-revenue-range-btn.btn-active { background: var(--primary); color: #fff; border-color: var(--primary); }
.detail-modal { display: flex; flex-direction: column; gap: 20px; }
.detail-modal-section { padding: 16px; background: #f9fafb; border-radius: 8px; border: 1px solid #e5e7eb; }
.detail-modal-heading { margin: 0 0 12px 0; font-size: 0.8rem; font-weight: 600; color: #374151; text-transform: uppercase; letter-spacing: 0.05em; }
.detail-modal-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 12px 20px; }
.detail-modal-item { display: flex; flex-direction: column; gap: 4px; }
.detail-label { font-size: 0.7rem; color: #6b7280; font-weight: 500; text-transform: uppercase; }
.detail-value { font-size: 0.9rem; color: #111827; font-weight: 500; }
.detail-badge { display: inline-block; padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 600; width: fit-content; }
.detail-link { color: var(--primary); text-decoration: none; }
.detail-link:hover { text-decoration: underline; }
.detail-modal-footer { background: #f3f4f6; }
.detail-total { font-size: 1.1rem; font-weight: 700; }
</style>

<script>
(function() {
    const slug = <?php echo json_encode($restaurantSlug); ?>;
    const symbol = <?php echo json_encode($currencySymbol); ?>;

    function loadResRevenueChart(range) {
        const wrapper = document.getElementById('res-revenue-chart-wrapper');
        const chartEl = document.getElementById('res-revenue-line-chart');
        const emptyEl = document.getElementById('res-revenue-chart-empty');
        const svgEl = document.getElementById('res-revenue-svg');
        const tooltipEl = document.getElementById('res-revenue-tooltip');
        const trendEl = document.getElementById('res-revenue-trend');
        const url = '../api/reservations-analytics.php?range=' + encodeURIComponent(range || 'all');
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
            const chartW = 800, chartH = 280;
            const padL = 48, padR = 24, padT = 24, padB = 40;
            const plotW = chartW - padL - padR, plotH = chartH - padT - padB;
            const firstVal = revenues[0] || 0, lastVal = revenues[revenues.length - 1] || 0;
            const isGrowth = lastVal >= firstVal;
            const lineColor = isGrowth ? '#059669' : '#DC2626';
            const gradientId = 'resRevGrad-' + (isGrowth ? 'up' : 'down');
            const pts = rows.map(function(r, i) {
                const val = parseFloat(r.revenue) || 0;
                const y = padT + plotH - ((val - minRev) / rangeRev) * plotH;
                const x = padL + (rows.length > 1 ? (i / (rows.length - 1)) * plotW : plotW / 2);
                return { x: x, y: y, val: val, date: r.date };
            });
            const pathD = pts.map(function(p, i) { return (i === 0 ? 'M' : 'L') + p.x + ' ' + p.y; }).join(' ');
            const areaD = pathD + ' L' + (padL + plotW) + ' ' + (padT + plotH) + ' L' + padL + ' ' + (padT + plotH) + ' Z';
            const yTicks = 5;
            let html = '<defs><linearGradient id="' + gradientId + '" x1="0%" y1="0%" x2="0%" y2="100%"><stop offset="0%" style="stop-color:' + lineColor + ';stop-opacity:0.25"/><stop offset="100%" style="stop-color:' + lineColor + ';stop-opacity:0"/></linearGradient></defs>';
            for (var g = 0; g <= yTicks; g++) {
                var gy = padT + (g / yTicks) * plotH;
                var gval = (maxRev - (g / yTicks) * (maxRev - minRev)).toFixed(0);
                html += '<line x1="' + padL + '" y1="' + gy + '" x2="' + (padL + plotW) + '" y2="' + gy + '" stroke="#e5e7eb" stroke-width="1"/>';
                html += '<text x="' + (padL - 8) + '" y="' + (gy + 4) + '" text-anchor="end" font-size="10" fill="#6b7280">' + symbol + gval + '</text>';
            }
            html += '<path d="' + areaD + '" fill="url(#' + gradientId + ')"/><path d="' + pathD + '" fill="none" stroke="' + lineColor + '" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>';
            for (var i = 0; i < pts.length; i++) {
                var p = pts[i];
                var d = p.date ? new Date(p.date + 'T12:00:00').toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: pts.length > 14 ? '2-digit' : undefined }) : p.date;
                html += '<circle class="res-revenue-point" cx="' + p.x + '" cy="' + p.y + '" r="6" fill="' + lineColor + '" stroke="#fff" stroke-width="2" opacity="0.9" data-date="' + (d || '') + '" data-rev="' + symbol + p.val.toFixed(2) + '"/>';
            }
            var step = Math.max(1, Math.floor(pts.length / 6));
            for (var xi = 0; xi < pts.length; xi += step) {
                var px = pts[xi];
                var dx = px.date ? new Date(px.date + 'T12:00:00').toLocaleDateString(undefined, { month: 'short', day: 'numeric' }) : '';
                html += '<text x="' + px.x + '" y="' + (chartH - 8) + '" text-anchor="middle" font-size="10" fill="#6b7280">' + dx + '</text>';
            }
            svgEl.innerHTML = html;
            var trendText = rows.length >= 2 && firstVal > 0 ? ((parseFloat(((lastVal - firstVal) / firstVal * 100).toFixed(1)) >= 0 ? '+' : '') + ((lastVal - firstVal) / firstVal * 100).toFixed(1) + '% vs first day') : (rows.length >= 1 ? symbol + lastVal.toFixed(2) + ' total' : '');
            trendEl.innerHTML = isGrowth ? '<span style="color:#059669">&#9650;</span> ' + trendText : '<span style="color:#DC2626">&#9660;</span> ' + trendText;
            function showTooltip(el, e) {
                var rect = wrapper.getBoundingClientRect();
                tooltipEl.innerHTML = '<strong>' + (el.getAttribute('data-date') || '') + '</strong><br/>Deposits: ' + (el.getAttribute('data-rev') || '');
                tooltipEl.style.display = 'block';
                tooltipEl.style.left = Math.min(e.clientX - rect.left + 12, rect.width - 140) + 'px';
                tooltipEl.style.top = Math.max(e.clientY - rect.top - 50, 8) + 'px';
            }
            wrapper.querySelectorAll('.res-revenue-point').forEach(function(el) {
                el.addEventListener('mouseenter', function(e) { showTooltip(el, e); });
                el.addEventListener('mouseleave', function() { tooltipEl.style.display = 'none'; });
                el.addEventListener('mousemove', function(e) { showTooltip(el, e); });
            });
        }).catch(function() {
            chartEl.style.display = 'none';
            emptyEl.style.display = 'block';
            trendEl.innerHTML = '';
        });
    }

    document.querySelectorAll('.res-revenue-range-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.res-revenue-range-btn').forEach(function(b) { b.classList.remove('btn-active'); });
            this.classList.add('btn-active');
            loadResRevenueChart(this.getAttribute('data-range'));
        });
    });
    loadResRevenueChart('all');

    (function() {
        const form = document.getElementById('deposit-form');
        const btn = document.getElementById('deposit-save-btn');
        const statusEl = document.getElementById('deposit-status');
        function showStatus(msg, isError) {
            statusEl.textContent = msg;
            statusEl.style.display = 'inline';
            statusEl.style.color = isError ? '#dc2626' : '#059669';
            if (!isError) {
                clearTimeout(form._statusTimer);
                form._statusTimer = setTimeout(function() { statusEl.style.display = 'none'; statusEl.textContent = ''; }, 4000);
            }
        }
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const amt = parseFloat(document.getElementById('deposit_amount').value) || 0;
            const fd = new FormData();
            fd.append('deposit_amount', amt);
            btn.disabled = true;
            btn.textContent = 'Saving…';
            statusEl.style.display = 'none';
            statusEl.textContent = '';
            fetch('../api/update-reservation-deposit.php', { method: 'POST', body: fd })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    btn.disabled = false;
                    btn.textContent = 'Save Deposit';
                    if (data.success) {
                        showStatus('Deposit setting saved.', false);
                    } else {
                        showStatus(data.message || 'Failed to save.', true);
                    }
                })
                .catch(function() {
                    btn.disabled = false;
                    btn.textContent = 'Save Deposit';
                    showStatus('Failed to save. Please try again.', true);
                });
        });
    })();

    document.querySelectorAll('.view-reservation-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-reservation-id');
            fetch('../api/get-reservation-details.php?reservation_id=' + encodeURIComponent(id)).then(r => r.json()).then(function(data) {
                if (!data.success || !data.reservation) return;
                const r = data.reservation;
                function esc(s){ if(s==null||s==='')return ''; const t=String(s); return t.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;'); }
                const dateStr = r.reservation_date ? new Date(r.reservation_date + 'T12:00:00').toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' }) : '-';
                const timeStr = r.reservation_time ? r.reservation_time.substring(0, 5) : '-';
                const statusClr = { pending: '#f59e0b', confirmed: '#10b981', rejected: '#ef4444', cancelled: '#6b7280', completed: '#3b82f6' }[r.status] || '#6b7280';
                const body = document.getElementById('reservation-modal-body');
                const resNum = (r.reservation_number && r.reservation_number.trim()) ? r.reservation_number : ('0' + (parseInt(r.id,10)||0).toString(36)).toUpperCase().slice(-8);
                body.innerHTML = '<div class="detail-modal">' +
                    '<div class="detail-modal-section">' +
                    '<h4 class="detail-modal-heading">Reservation Details</h4>' +
                    '<div class="detail-modal-grid">' +
                    '<div class="detail-modal-item"><span class="detail-label">Reservation #</span><span class="detail-value">' + resNum + '</span></div>' +
                    '<div class="detail-modal-item"><span class="detail-label">Date</span><span class="detail-value">' + dateStr + '</span></div>' +
                    '<div class="detail-modal-item"><span class="detail-label">Time</span><span class="detail-value">' + timeStr + '</span></div>' +
                    '<div class="detail-modal-item"><span class="detail-label">Guests</span><span class="detail-value">' + (r.party_size || '-') + '</span></div>' +
                    '<div class="detail-modal-item"><span class="detail-label">Status</span><span class="detail-badge" style="background:' + statusClr + '22;color:' + statusClr + '">' + esc(r.status || 'pending') + '</span></div>' +
                    '</div></div>' +
                    '<div class="detail-modal-section">' +
                    '<h4 class="detail-modal-heading">Guest Information</h4>' +
                    '<div class="detail-modal-grid">' +
                    '<div class="detail-modal-item"><span class="detail-label">Name</span><span class="detail-value">' + (esc(r.guest_name) || '-') + '</span></div>' +
                    '<div class="detail-modal-item"><span class="detail-label">Email</span><span class="detail-value">' + ((r.guest_email && r.guest_email.trim()) ? '<a href="mailto:' + esc(r.guest_email) + '" class="detail-link">' + esc(r.guest_email) + '</a>' : '-') + '</span></div>' +
                    '<div class="detail-modal-item"><span class="detail-label">Phone</span><span class="detail-value">' + ((r.guest_phone && r.guest_phone.trim()) ? '<a href="tel:' + esc((r.guest_phone||'').replace(/\s/g,'')) + '" class="detail-link">' + esc(r.guest_phone) + '</a>' : '-') + '</span></div>' +
                    '</div></div>' +
                    (r.special_occasion || r.notes ? '<div class="detail-modal-section"><h4 class="detail-modal-heading">Additional Information</h4>' +
                    (r.special_occasion ? '<div class="detail-modal-item"><span class="detail-label">Occasion</span><span class="detail-value">' + esc(r.special_occasion) + '</span></div>' : '') +
                    (r.notes ? '<div class="detail-modal-item"><span class="detail-label">Notes</span><span class="detail-value">' + esc(r.notes) + '</span></div>' : '') +
                    '</div>' : '') +
                    '<div class="detail-modal-section detail-modal-footer">' +
                    '<div class="detail-modal-item"><span class="detail-label">Deposit</span><span class="detail-value detail-total">' + symbol + (parseFloat(r.deposit_amount || 0)).toFixed(2) + (r.deposit_paid ? ' <span style="color:#10b981;font-size:0.8em;">(Paid)</span>' : '') + '</span></div>' +
                    '</div></div>';
                document.getElementById('reservation-modal').style.display = 'flex';
            });
        });
    });

    document.getElementById('reservation-modal-close').addEventListener('click', function() {
        document.getElementById('reservation-modal').style.display = 'none';
    });
    document.getElementById('reservation-modal').addEventListener('click', function(e) {
        if (e.target === this) this.style.display = 'none';
    });
})();
</script>
</div>
<?php
if (!empty($showManagerUpgradeOverlay)) {
    include __DIR__ . '/../includes/manager-upgrade-overlay.php';
}
include __DIR__ . '/../includes/admin-footer.php';
?>
