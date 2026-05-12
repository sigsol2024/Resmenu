<?php
/**
 * Table Inventory Management (Manager)
 * Daily table capacity, walk-ins, calendar view. Template 4 only.
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
requireManager();
require_once __DIR__ . '/../includes/functions.php';

$tableInventoryCsrf = getCSRFToken();

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

$pageTitle = 'Table Inventory - ' . htmlspecialchars($restaurant['name']);
include __DIR__ . '/../includes/manager-layout.php';
?>

<div class="page-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;">
    <div>
        <a href="reservations.php<?php echo $slugParam; ?>" class="btn btn-secondary" style="font-size:0.8rem;margin-bottom:8px;display:inline-flex;align-items:center;gap:6px;">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Reservations
        </a>
        <h1 class="page-title" style="margin:0;">Table Inventory</h1>
        <p class="page-subtitle" style="margin:4px 0 0;">Manage daily table availability for <?php echo htmlspecialchars($restaurant['name']); ?></p>
    </div>
</div>

<div class="settings-card" style="padding:24px;margin-bottom:24px;">
    <h3 style="font-size:0.875rem;font-weight:600;margin:0 0 12px;color:#374151;">Bulk Update</h3>
    <p style="font-size:0.75rem;color:#6b7280;margin:0 0 16px;">Set the same table quantity for multiple days (entire month or a date range).</p>
    <div style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end;margin-bottom:24px;padding:16px;background:#f9fafb;border-radius:8px;border:1px solid #e5e7eb;">
        <div>
            <label style="display:block;font-size:0.75rem;color:#6b7280;margin-bottom:4px;">Start Date</label>
            <input type="date" id="bulk-start-date" style="padding:8px 12px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.875rem;"/>
        </div>
        <div>
            <label style="display:block;font-size:0.75rem;color:#6b7280;margin-bottom:4px;">End Date</label>
            <input type="date" id="bulk-end-date" style="padding:8px 12px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.875rem;"/>
        </div>
        <div>
            <label style="display:block;font-size:0.75rem;color:#6b7280;margin-bottom:4px;">Total Tables</label>
            <input type="number" id="bulk-total-tables" min="1" max="999" value="10" style="padding:8px 12px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.875rem;width:80px;"/>
        </div>
        <button type="button" id="bulk-save-btn" class="btn btn-primary" style="padding:8px 16px;">Bulk Update</button>
        <button type="button" id="bulk-fill-month-btn" class="btn btn-secondary" style="padding:8px 16px;">Fill Entire Month</button>
        <span id="bulk-status" role="status" aria-live="polite" style="font-size:0.875rem;font-weight:500;display:none;"></span>
    </div>
    <div style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:16px;margin-bottom:20px;">
        <h3 style="font-size:1rem;font-weight:600;margin:0;color:#111827;" id="inventory-month-title">February 2025</h3>
        <div style="display:flex;gap:8px;">
            <button type="button" id="inv-prev-month" class="btn btn-secondary" style="padding:6px 12px;font-size:0.8rem;">Previous</button>
            <button type="button" id="inv-next-month" class="btn btn-secondary" style="padding:6px 12px;font-size:0.8rem;">Next</button>
        </div>
    </div>
    <div style="display:flex;flex-wrap:wrap;gap:12px;margin-bottom:20px;font-size:0.75rem;color:#6b7280;">
        <span style="display:flex;align-items:center;gap:6px;"><span style="width:12px;height:12px;border-radius:4px;background:#10b981;"></span> Available</span>
        <span style="display:flex;align-items:center;gap:6px;"><span style="width:12px;height:12px;border-radius:4px;background:#ef4444;"></span> Booked</span>
        <span style="display:flex;align-items:center;gap:6px;"><span style="width:12px;height:12px;border-radius:4px;background:#f59e0b;"></span> Pending</span>
        <span style="display:flex;align-items:center;gap:6px;"><span style="width:12px;height:12px;border-radius:4px;background:#6b7280;"></span> Cancelled</span>
    </div>
    <div id="inventory-calendar" class="inventory-calendar" style="display:grid;grid-template-columns:repeat(7, 1fr);gap:8px;"></div>
</div>

<div id="inventory-day-panel" class="settings-card" style="padding:24px;margin-bottom:24px;display:none;">
    <h3 style="font-size:1rem;font-weight:600;margin:0 0 16px;color:#111827;">Day Details: <span id="day-panel-date"></span></h3>
    <div class="inventory-day-stats" style="display:grid;grid-template-columns:repeat(auto-fill, minmax(120px, 1fr));gap:16px;margin-bottom:20px;"></div>
    <div style="display:flex;flex-wrap:wrap;gap:16px;align-items:flex-end;margin-bottom:20px;">
        <div>
            <label style="display:block;font-size:0.75rem;color:#6b7280;margin-bottom:4px;">Total Tables</label>
            <input type="number" id="day-total-tables" min="1" max="999" value="10" style="padding:8px 12px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.875rem;width:100px;"/>
        </div>
        <button type="button" id="day-save-total" class="btn btn-primary" style="padding:8px 16px;">Save Total</button>
        <span id="day-save-status" role="status" aria-live="polite" style="font-size:0.875rem;font-weight:500;display:none;"></span>
    </div>
    <div style="margin-bottom:16px;">
        <button type="button" id="day-add-walkin" class="btn btn-secondary" style="padding:8px 16px;">Add Walk-in</button>
    </div>
    <div>
        <h4 style="font-size:0.875rem;font-weight:600;margin:0 0 8px;color:#374151;">Reservations</h4>
        <div id="day-reservations-list" style="font-size:0.8rem;color:#6b7280;">Loading...</div>
    </div>
</div>

<div id="walkin-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center;padding:24px;">
    <div style="background:#fff;border-radius:12px;max-width:400px;width:100%;padding:24px;box-shadow:0 20px 25px -5px rgba(0,0,0,0.1),0 10px 10px -5px rgba(0,0,0,0.04);">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
            <h3 style="font-size:1.25rem;font-weight:600;color:#111827;margin:0;">Add Walk-in Guest</h3>
            <button type="button" id="walkin-modal-close" style="background:0;border:0;cursor:pointer;padding:4px;color:#6b7280;font-size:1.5rem;line-height:1;">&times;</button>
        </div>
        <label style="display:block;font-size:0.875rem;font-weight:500;color:#374151;margin-bottom:8px;">Guest name</label>
        <input type="text" id="walkin-guest-name" placeholder="Walk-in" value="Walk-in" style="width:100%;padding:10px 12px;border:1px solid #e5e7eb;border-radius:8px;font-size:0.875rem;margin-bottom:20px;box-sizing:border-box;"/>
        <div style="display:flex;gap:8px;justify-content:flex-end;">
            <button type="button" id="walkin-modal-cancel" class="btn btn-secondary" style="padding:8px 16px;">Cancel</button>
            <button type="button" id="walkin-modal-confirm" class="btn btn-primary" style="padding:8px 16px;">Add Walk-in</button>
        </div>
    </div>
</div>

<style>
.inventory-calendar .inv-day { padding:10px;border-radius:8px;min-height:60px;cursor:pointer;font-size:0.8rem;font-weight:500;border:1px solid #e5e7eb;transition:all 0.2s; }
.inventory-calendar .inv-day:hover { border-color:var(--primary); }
.inventory-calendar .inv-day.other-month { opacity:0.4; }
.inventory-calendar .inv-day.past { opacity:0.6;cursor:default; }
.inventory-calendar .inv-day .inv-date { font-weight:700;margin-bottom:4px; }
.inventory-calendar .inv-day .inv-summary { font-size:0.7rem;color:#6b7280; }
.inventory-calendar .inv-day.avail-dominant { background:#d1fae5; }
.inventory-calendar .inv-day.booked-dominant { background:#fee2e2; }
.inventory-calendar .inv-day.pending-dominant { background:#fef3c7; }
.inventory-calendar .inv-day.cancelled-dominant { background:#f3f4f6; }
.inventory-calendar .inv-header { font-size:0.7rem;font-weight:600;color:#6b7280;text-align:center;padding:4px; }
</style>

<script>
(function() {
    const CSRF_TOKEN = <?php echo json_encode($tableInventoryCsrf); ?>;
    const slug = <?php echo json_encode($restaurantSlug); ?>;
    let currentYear = new Date().getFullYear();
    let currentMonth = new Date().getMonth();
    let selectedDate = null;

    const monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];

    function getMonthRange() {
        const start = new Date(currentYear, currentMonth, 1);
        const end = new Date(currentYear, currentMonth + 1, 0);
        return {
            start: start.getFullYear() + '-' + String(start.getMonth() + 1).padStart(2, '0') + '-' + String(start.getDate()).padStart(2, '0'),
            end: end.getFullYear() + '-' + String(end.getMonth() + 1).padStart(2, '0') + '-' + String(end.getDate()).padStart(2, '0')
        };
    }

    function loadMonth() {
        const r = getMonthRange();
        document.getElementById('inventory-month-title').textContent = monthNames[currentMonth] + ' ' + currentYear;
        fetch('../api/table-inventory.php?action=month&year=' + currentYear + '&month=' + (currentMonth + 1))
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (!data.success || !data.dates) return;
                renderCalendar(data.dates);
            })
            .catch(function() { renderCalendar({}); });
        setBulkDateRange();
    }

    function renderCalendar(dates) {
        const first = new Date(currentYear, currentMonth, 1);
        const last = new Date(currentYear, currentMonth + 1, 0);
        const startPad = first.getDay();
        const daysInMonth = last.getDate();
        const prevMonth = currentMonth === 0 ? 11 : currentMonth - 1;
        const prevYear = currentMonth === 0 ? currentYear - 1 : currentYear;
        const prevLast = new Date(prevYear, prevMonth + 1, 0).getDate();

        let html = '';
        for (let i = 0; i < 7; i++) html += '<div class="inv-header">' + ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'][i] + '</div>';
        for (let i = 0; i < startPad; i++) {
            const d = prevLast - startPad + i + 1;
            const dateStr = prevYear + '-' + String(prevMonth + 1).padStart(2, '0') + '-' + String(d).padStart(2, '0');
            html += '<div class="inv-day other-month" data-date="' + dateStr + '"><span class="inv-date">' + d + '</span><span class="inv-summary">-</span></div>';
        }
        const today = new Date().toISOString().slice(0, 10);
        for (let d = 1; d <= daysInMonth; d++) {
            const dateStr = currentYear + '-' + String(currentMonth + 1).padStart(2, '0') + '-' + String(d).padStart(2, '0');
            const a = dates[dateStr] || { total: 10, available: 10, confirmed: 0, pending: 0, walkins: 0, cancelled: 0 };
            let css = 'inv-day';
            if (dateStr < today) css += ' past';
            const booked = a.confirmed + a.walkins;
            if (a.available >= a.total / 2) css += ' avail-dominant';
            else if (booked > 0) css += ' booked-dominant';
            else if (a.pending > 0) css += ' pending-dominant';
            else if (a.cancelled > 0) css += ' cancelled-dominant';
            const summary = a.available + '/' + a.total + ' left';
            html += '<div class="' + css + '" data-date="' + dateStr + '"><span class="inv-date">' + d + '</span><span class="inv-summary">' + summary + '</span></div>';
        }
        const totalCells = startPad + daysInMonth;
        const remainder = totalCells % 7;
        const nextDays = remainder === 0 ? 0 : 7 - remainder;
        const nextMonth = currentMonth === 11 ? 0 : currentMonth + 1;
        const nextYear = currentMonth === 11 ? currentYear + 1 : currentYear;
        for (let i = 1; i <= nextDays; i++) {
            const dateStr = nextYear + '-' + String(nextMonth + 1).padStart(2, '0') + '-' + String(i).padStart(2, '0');
            html += '<div class="inv-day other-month" data-date="' + dateStr + '"><span class="inv-date">' + i + '</span><span class="inv-summary">-</span></div>';
        }
        document.getElementById('inventory-calendar').innerHTML = html;

        document.querySelectorAll('.inv-day:not(.past)').forEach(function(el) {
            el.addEventListener('click', function() {
                selectedDate = this.getAttribute('data-date');
                showDayPanel(selectedDate);
            });
        });
    }

    function showDayPanel(dateStr) {
        document.getElementById('inventory-day-panel').style.display = 'block';
        const d = new Date(dateStr + 'T12:00:00');
        document.getElementById('day-panel-date').textContent = d.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });

        fetch('../api/table-inventory.php?action=day_detail&date=' + encodeURIComponent(dateStr))
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (!data.success) return;
                const a = data.availability;
                const booked = a.confirmed + a.walkins;
                document.querySelector('.inventory-day-stats').innerHTML =
                    '<div><div style="font-size:0.7rem;color:#6b7280;">Total</div><div style="font-size:1.25rem;font-weight:700;">' + a.total + '</div></div>' +
                    '<div><div style="font-size:0.7rem;color:#6b7280;">Booked</div><div style="font-size:1.25rem;font-weight:700;color:#ef4444;">' + booked + '</div></div>' +
                    '<div><div style="font-size:0.7rem;color:#6b7280;">Pending</div><div style="font-size:1.25rem;font-weight:700;color:#f59e0b;">' + a.pending + '</div></div>' +
                    '<div><div style="font-size:0.7rem;color:#6b7280;">Walk-ins</div><div style="font-size:1.25rem;font-weight:700;">' + a.walkins + '</div></div>' +
                    '<div><div style="font-size:0.7rem;color:#6b7280;">Cancelled</div><div style="font-size:1.25rem;font-weight:700;color:#6b7280;">' + a.cancelled + '</div></div>' +
                    '<div><div style="font-size:0.7rem;color:#6b7280;">Available</div><div style="font-size:1.25rem;font-weight:700;color:#10b981;">' + a.available + '</div></div>';
                document.getElementById('day-total-tables').value = a.total;

                const list = data.reservations || [];
                let listHtml = list.length === 0 ? 'No reservations.' : '<ul style="list-style:none;padding:0;margin:0;">' + list.map(function(r) {
                    const time = r.reservation_time ? String(r.reservation_time).substring(0, 5) : '-';
                    const ref = r.reservation_number ? ('#' + r.reservation_number) : ('#' + r.id);
                    const label = ref + ' – ' + (r.is_walkin == 1 ? '[Walk-in] ' : '') + (r.guest_name || '-') + ' @ ' + time + ' (' + (r.status || '-') + ')';
                    return '<li style="padding:6px 0;border-bottom:1px solid #f3f4f6;">' + label + '</li>';
                }).join('') + '</ul>';
                document.getElementById('day-reservations-list').innerHTML = listHtml;
            });
    }

    document.getElementById('inv-prev-month').onclick = function() {
        if (currentMonth === 0) { currentMonth = 11; currentYear--; } else currentMonth--;
        loadMonth();
    };
    document.getElementById('inv-next-month').onclick = function() {
        if (currentMonth === 11) { currentMonth = 0; currentYear++; } else currentMonth++;
        loadMonth();
    };

    function setBulkDateRange() {
        const r = getMonthRange();
        document.getElementById('bulk-start-date').value = r.start;
        document.getElementById('bulk-end-date').value = r.end;
    }
    function showBulkStatus(msg, isError) {
        const el = document.getElementById('bulk-status');
        el.textContent = msg;
        el.style.display = 'inline';
        el.style.color = isError ? '#dc2626' : '#059669';
        clearTimeout(window._bulkStatusTimer);
        window._bulkStatusTimer = setTimeout(function() { el.style.display = 'none'; el.textContent = ''; }, 5000);
    }
    function doBulkUpdate(startDate, endDate, total) {
        const fd = new FormData();
        fd.append('csrf_token', CSRF_TOKEN);
        fd.append('action', 'bulk_set_total');
        fd.append('start_date', startDate);
        fd.append('end_date', endDate);
        fd.append('total_tables', total);
        const btn = document.getElementById('bulk-save-btn');
        const fillBtn = document.getElementById('bulk-fill-month-btn');
        btn.disabled = true;
        fillBtn.disabled = true;
        btn.textContent = 'Updating…';
        fillBtn.textContent = 'Updating…';
        document.getElementById('bulk-status').style.display = 'none';
        fetch('../api/table-inventory.php', { method: 'POST', body: fd })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                btn.disabled = false;
                fillBtn.disabled = false;
                btn.textContent = 'Bulk Update';
                fillBtn.textContent = 'Fill Entire Month';
                if (data.success) {
                    showBulkStatus('Updated ' + (data.updated_count || 0) + ' day(s) successfully.', false);
                    loadMonth();
                    if (selectedDate && selectedDate >= startDate && selectedDate <= endDate) showDayPanel(selectedDate);
                } else {
                    showBulkStatus(data.message || 'Failed to update', true);
                }
            })
            .catch(function() {
                btn.disabled = false;
                fillBtn.disabled = false;
                btn.textContent = 'Bulk Update';
                fillBtn.textContent = 'Fill Entire Month';
                showBulkStatus('Failed to update. Please try again.', true);
            });
    }
    document.getElementById('bulk-save-btn').onclick = function() {
        const start = document.getElementById('bulk-start-date').value;
        const end = document.getElementById('bulk-end-date').value;
        const total = parseInt(document.getElementById('bulk-total-tables').value, 10) || 10;
        if (!start || !end) {
            showBulkStatus('Please select start and end dates.', true);
            return;
        }
        if (start > end) {
            showBulkStatus('Start date must be before or equal to end date.', true);
            return;
        }
        doBulkUpdate(start, end, total);
    };
    document.getElementById('bulk-fill-month-btn').onclick = function() {
        const r = getMonthRange();
        const total = parseInt(document.getElementById('bulk-total-tables').value, 10) || 10;
        doBulkUpdate(r.start, r.end, total);
    };

    function showSaveStatus(msg, isError) {
        const el = document.getElementById('day-save-status');
        el.textContent = msg;
        el.style.display = 'inline';
        el.style.color = isError ? '#dc2626' : '#059669';
        if (!isError) {
            clearTimeout(window._saveStatusTimer);
            window._saveStatusTimer = setTimeout(function() { el.style.display = 'none'; el.textContent = ''; }, 4000);
        }
    }
    document.getElementById('day-save-total').onclick = function() {
        if (!selectedDate) return;
        const total = parseInt(document.getElementById('day-total-tables').value, 10) || 10;
        const fd = new FormData();
        fd.append('csrf_token', CSRF_TOKEN);
        fd.append('action', 'set_total');
        fd.append('date', selectedDate);
        fd.append('total_tables', total);
        const btn = this;
        btn.disabled = true;
        btn.textContent = 'Saving…';
        document.getElementById('day-save-status').style.display = 'none';
        fetch('../api/table-inventory.php', { method: 'POST', body: fd })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                btn.disabled = false;
                btn.textContent = 'Save Total';
                if (data.success) {
                    showSaveStatus('Saved successfully.', false);
                    loadMonth();
                    showDayPanel(selectedDate);
                } else {
                    showSaveStatus(data.message || 'Failed to save', true);
                }
            })
            .catch(function() {
                btn.disabled = false;
                btn.textContent = 'Save Total';
                showSaveStatus('Failed to save. Please try again.', true);
            });
    };

    document.getElementById('day-add-walkin').onclick = function() {
        if (!selectedDate) return;
        const modal = document.getElementById('walkin-modal');
        const input = document.getElementById('walkin-guest-name');
        input.value = 'Walk-in';
        modal.style.display = 'flex';
        input.focus();
        input.select();
    };
    function closeWalkinModal() {
        document.getElementById('walkin-modal').style.display = 'none';
    }
    document.getElementById('walkin-modal-close').onclick = closeWalkinModal;
    document.getElementById('walkin-modal-cancel').onclick = closeWalkinModal;
    document.getElementById('walkin-modal').onclick = function(e) { if (e.target === this) closeWalkinModal(); };
    document.getElementById('walkin-modal-confirm').onclick = function() {
        const name = document.getElementById('walkin-guest-name').value.trim() || 'Walk-in';
        closeWalkinModal();
        const fd = new FormData();
        fd.append('csrf_token', CSRF_TOKEN);
        fd.append('action', 'add_walkin');
        fd.append('date', selectedDate);
        fd.append('guest_name', name);
        fetch('../api/table-inventory.php', { method: 'POST', body: fd })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.success) {
                    loadMonth();
                    showDayPanel(selectedDate);
                } else alert(data.message || 'Failed to add walk-in');
            });
    };

    loadMonth();
})();
</script>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
