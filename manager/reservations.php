<?php
/**
 * Reservations Management (Manager)
 * Overview: stats, deposit settings, recent reservations. Template 4 only.
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

$stmt = $pdo->prepare("SELECT * FROM restaurants WHERE id = ?");
$stmt->execute([$restaurantId]);
$restaurant = $stmt->fetch();
if (!$restaurant) die('Restaurant not found.');
if (empty($restaurantSlug)) $restaurantSlug = $restaurant['slug'];

$templateId = (int)($restaurant['template_id'] ?? 1);
$slugParam = $restaurantSlug ? '?slug=' . urlencode($restaurantSlug) : '';

// Reservations are Template 4 only
if ($templateId !== 4) {
    $pageTitle = 'Reservations - ' . htmlspecialchars($restaurant['name']);
    include __DIR__ . '/../includes/manager-layout.php';
    ?>
    <div class="page-header">
        <h1 class="page-title">Reservations</h1>
        <p class="page-subtitle">Table reservations are only available for restaurants using Template 4 (The Gourmet Grill).</p>
    </div>
    <div class="settings-card" style="padding:24px;">
        <p style="color:#6b7280;">Your restaurant is using Template <?php echo $templateId; ?>. To enable table reservations, switch to Template 4 in the Templates / Customization section.</p>
    </div>
    <?php
    exit;
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

<div class="page-header">
    <h1 class="page-title">Reservations</h1>
    <p class="page-subtitle">Manage table reservations for <?php echo htmlspecialchars($restaurant['name']); ?></p>
</div>

<?php
$statusLabels = ['pending' => 'Pending', 'confirmed' => 'Confirmed', 'rejected' => 'Rejected', 'cancelled' => 'Cancelled', 'completed' => 'Completed'];
$statusColors = ['pending' => '#f59e0b', 'confirmed' => '#10b981', 'rejected' => '#ef4444', 'cancelled' => '#6b7280', 'completed' => '#3b82f6'];
?>
<section class="reservations-overview" style="margin-bottom:24px;">
    <div class="stats reservations-stats" style="display:grid;grid-template-columns:repeat(6, minmax(0, 1fr));gap:16px;margin-bottom:24px;">
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
            <button type="submit" class="btn btn-primary" style="padding:8px 16px;">Save Deposit</button>
        </form>
        <p style="font-size:0.75rem;color:#6b7280;margin-top:8px;">Amount charged at checkout when customers make a reservation. Set to 0 for no deposit.</p>
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
    <div class="table-wrapper">
        <table class="orders-table" style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:1px solid #e5e7eb;">
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
</style>

<script>
(function() {
    const slug = <?php echo json_encode($restaurantSlug); ?>;
    const symbol = <?php echo json_encode($currencySymbol); ?>;

    document.getElementById('deposit-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const amt = parseFloat(document.getElementById('deposit_amount').value) || 0;
        const fd = new FormData();
        fd.append('deposit_amount', amt);
        fetch('../api/update-reservation-deposit.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(function(data) {
                if (data.success) alert('Deposit setting saved.');
                else alert(data.message || 'Failed to save.');
            })
            .catch(function() { alert('Failed to save.'); });
    });

    document.querySelectorAll('.actions-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const dd = this.nextElementSibling;
            document.querySelectorAll('.actions-dropdown').forEach(function(d) { if (d !== dd) d.classList.remove('show'); });
            dd.classList.toggle('show');
        });
    });
    document.addEventListener('click', function() {
        document.querySelectorAll('.actions-dropdown').forEach(function(d) { d.classList.remove('show'); });
    });

    document.querySelectorAll('.view-reservation-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-reservation-id');
            fetch('../api/get-reservation-details.php?reservation_id=' + encodeURIComponent(id)).then(r => r.json()).then(function(data) {
                if (!data.success || !data.reservation) return;
                const r = data.reservation;
                const body = document.getElementById('reservation-modal-body');
                body.innerHTML = '<p><strong>Date:</strong> ' + (r.reservation_date || '-') + '</p>' +
                    '<p><strong>Time:</strong> ' + (r.reservation_time ? r.reservation_time.substring(0, 5) : '-') + '</p>' +
                    '<p><strong>Guests:</strong> ' + (r.party_size || '-') + '</p>' +
                    '<p><strong>Name:</strong> ' + (r.guest_name || '-') + '</p>' +
                    '<p><strong>Email:</strong> ' + (r.guest_email || '-') + '</p>' +
                    '<p><strong>Phone:</strong> ' + (r.guest_phone || '-') + '</p>' +
                    (r.special_occasion ? '<p><strong>Occasion:</strong> ' + r.special_occasion + '</p>' : '') +
                    (r.notes ? '<p><strong>Notes:</strong> ' + r.notes + '</p>' : '') +
                    '<p><strong>Deposit:</strong> ' + symbol + (parseFloat(r.deposit_amount || 0)).toFixed(2) + (r.deposit_paid ? ' (Paid)' : '') + '</p>' +
                    '<p><strong>Status:</strong> ' + (r.status || 'pending') + '</p>';
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
