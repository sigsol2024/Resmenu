<?php
/**
 * Restaurant Reservations - Full reservations list with filters (Manager)
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
if ($templateId !== 4) {
    header('Location: /manager/reservations.php?slug=' . urlencode($restaurantSlug));
    exit;
}

$slugParam = $restaurantSlug ? '?slug=' . urlencode($restaurantSlug) : '';
$statuses = ['pending', 'confirmed', 'rejected', 'cancelled', 'completed'];
$statusColors = ['pending' => '#f59e0b', 'confirmed' => '#10b981', 'rejected' => '#ef4444', 'cancelled' => '#6b7280', 'completed' => '#3b82f6'];
$currencySymbol = '₦';

$pageTitle = 'All Reservations - ' . htmlspecialchars($restaurant['name']);
include __DIR__ . '/../includes/manager-layout.php';
?>

<div class="page-header" style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px;">
    <div>
        <h1 class="page-title">All Reservations</h1>
        <p class="page-subtitle">Full reservation list with filters for <?php echo htmlspecialchars($restaurant['name']); ?></p>
    </div>
    <a href="reservations.php<?php echo $slugParam; ?>" class="btn btn-secondary" style="padding:8px 16px;font-size:0.875rem;">Back to Reservations Overview</a>
</div>

<section class="restaurant-reservations">
    <div class="settings-card" style="margin-bottom:24px;">
        <h3 style="font-size:0.875rem;font-weight:600;margin-bottom:12px;color:#111827;">Filters</h3>
        <form id="reservations-filter-form" style="display:flex;flex-wrap:wrap;gap:16px;align-items:end;">
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
                    <option value="pending">Pending</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="rejected">Rejected</option>
                    <option value="cancelled">Cancelled</option>
                    <option value="completed">Completed</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="padding:8px 16px;">Apply</button>
        </form>
    </div>

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
            <tbody id="reservations-tbody">
                <tr><td colspan="6" style="padding:24px;text-align:center;color:#6b7280;">Loading...</td></tr>
            </tbody>
        </table>
    </div>
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
.restaurant-reservations .actions-cell { position: relative; }
.restaurant-reservations .actions-dropdown { z-index: 50; right: 100%; left: auto; top: 0; margin-right: 6px; min-width: 160px; }
</style>

<script>
(function() {
    const slug = <?php echo json_encode($restaurantSlug); ?>;
    const symbol = <?php echo json_encode($currencySymbol); ?>;
    const statusColors = <?php echo json_encode($statusColors); ?>;

    function esc(s) {
        if (s == null || s === '') return '';
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
    }

    function formatTime(t) {
        if (!t) return '-';
        return String(t).substring(0, 5);
    }

    function loadReservations() {
        const start = document.getElementById('start_date').value || '';
        const end = document.getElementById('end_date').value || '';
        const status = document.getElementById('status_filter').value || 'all';
        let url = '../api/reservations-analytics.php?action=reservations';
        if (start) url += '&start_date=' + encodeURIComponent(start);
        if (end) url += '&end_date=' + encodeURIComponent(end);
        if (status && status !== 'all') url += '&status=' + encodeURIComponent(status);

        const tbody = document.getElementById('reservations-tbody');
        tbody.innerHTML = '<tr><td colspan="6" style="padding:24px;text-align:center;color:#6b7280;">Loading...</td></tr>';

        fetch(url).then(r => r.json()).then(function(data) {
            const list = data.success && data.reservations ? data.reservations : [];
            if (list.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="padding:24px;text-align:center;color:#6b7280;">No reservations found.</td></tr>';
                return;
            }
            tbody.innerHTML = list.map(function(r) {
                const st = r.status || 'pending';
                const clr = statusColors[st] || '#6b7280';
                const label = (st.charAt(0).toUpperCase() + st.slice(1));
                const id = parseInt(r.id, 10) || 0;
                const dateStr = r.reservation_date ? new Date(r.reservation_date + 'T12:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '-';
                const timeStr = formatTime(r.reservation_time);
                const depositPaid = r.deposit_paid ? ' <span style="color:#10b981;">(Paid)</span>' : '';
                const approveReject = st !== 'confirmed' ? '<form method="post" action="../api/update-reservation-status.php" style="display:contents;"><input type="hidden" name="reservation_id" value="' + id + '"><input type="hidden" name="slug" value="' + esc(slug) + '"><input type="hidden" name="return_to" value="restaurant-reservations"><input type="hidden" name="status" value="confirmed"><button type="submit" class="actions-dropdown-item">Approve</button></form>' : '';
                const rejectForm = st !== 'rejected' ? '<form method="post" action="../api/update-reservation-status.php" style="display:contents;"><input type="hidden" name="reservation_id" value="' + id + '"><input type="hidden" name="slug" value="' + esc(slug) + '"><input type="hidden" name="return_to" value="restaurant-reservations"><input type="hidden" name="status" value="rejected"><button type="submit" class="actions-dropdown-item">Reject</button></form>' : '';
                return '<tr style="border-bottom:1px solid #f3f4f6;">' +
                    '<td style="padding:12px 16px;font-size:0.875rem;">' + dateStr + ' ' + timeStr + '</td>' +
                    '<td style="padding:12px 16px;font-size:0.875rem;">' + esc(r.guest_name) + '</td>' +
                    '<td style="padding:12px 16px;font-size:0.875rem;">' + (parseInt(r.party_size,10)||'-') + '</td>' +
                    '<td style="padding:12px 16px;font-size:0.875rem;">' + symbol + parseFloat(r.deposit_amount||0).toFixed(2) + depositPaid + '</td>' +
                    '<td style="padding:12px 16px;"><span class="badge" style="background:' + esc(clr) + '22;color:' + esc(clr) + ';padding:4px 10px;border-radius:6px;font-size:0.75rem;font-weight:600;">' + esc(label) + '</span></td>' +
                    '<td class="actions-cell" style="padding:12px 16px;">' +
                    '<button type="button" class="actions-btn" onclick="toggleResDropdown(this)" title="Actions"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/></svg></button>' +
                    '<div class="actions-dropdown"><button type="button" class="view-res-btn actions-dropdown-item" data-id="' + id + '">View</button><div class="actions-dropdown-divider"></div><div class="actions-dropdown-title">Change Status</div>' + approveReject + rejectForm + '</div>' +
                    '</td></tr>';
            }).join('');
            initViewHandlers();
        }).catch(function() {
            tbody.innerHTML = '<tr><td colspan="6" style="padding:24px;text-align:center;color:#ef4444;">Failed to load reservations.</td></tr>';
        });
    }

    window.toggleResDropdown = function(btn) {
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

    function initViewHandlers() {
        document.querySelectorAll('.view-res-btn').forEach(function(btn) {
            btn.onclick = function() {
                const id = this.getAttribute('data-id');
                fetch('../api/get-reservation-details.php?reservation_id=' + encodeURIComponent(id))
                    .then(r => r.json())
                    .then(function(data) {
                        if (!data.success || !data.reservation) return;
                        const r = data.reservation;
                        const body = document.getElementById('reservation-modal-body');
                        body.innerHTML = '<p><strong>Date:</strong> ' + esc(r.reservation_date) + '</p>' +
                            '<p><strong>Time:</strong> ' + formatTime(r.reservation_time) + '</p>' +
                            '<p><strong>Guests:</strong> ' + (r.party_size||'-') + '</p>' +
                            '<p><strong>Name:</strong> ' + esc(r.guest_name) + '</p>' +
                            '<p><strong>Email:</strong> ' + esc(r.guest_email) + '</p>' +
                            '<p><strong>Phone:</strong> ' + esc(r.guest_phone) + '</p>' +
                            (r.special_occasion ? '<p><strong>Occasion:</strong> ' + esc(r.special_occasion) + '</p>' : '') +
                            (r.notes ? '<p><strong>Notes:</strong> ' + esc(r.notes) + '</p>' : '') +
                            '<p><strong>Deposit:</strong> ' + symbol + parseFloat(r.deposit_amount||0).toFixed(2) + (r.deposit_paid ? ' (Paid)' : '') + '</p>' +
                            '<p><strong>Status:</strong> ' + esc(r.status) + '</p>';
                        document.getElementById('reservation-modal').style.display = 'flex';
                    })
                    .catch(function() { alert('Failed to load details.'); });
            };
        });
    }

    document.getElementById('reservations-filter-form').addEventListener('submit', function(e) {
        e.preventDefault();
        loadReservations();
    });

    document.getElementById('reservation-modal-close').onclick = function() {
        document.getElementById('reservation-modal').style.display = 'none';
    };
    document.getElementById('reservation-modal').onclick = function(e) {
        if (e.target === this) this.style.display = 'none';
    };

    loadReservations();
})();
</script>
