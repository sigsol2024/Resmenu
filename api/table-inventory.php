<?php
/**
 * Table Inventory API (Manager only)
 * Get/set daily table capacity, add walk-ins, view availability.
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/manager-feature-access.php';
require_once __DIR__ . '/../config/config.php';

if (!isLoggedIn() || !isManager()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$restaurantId = getCurrentUserRestaurantId();
if (!$restaurantId) {
    echo json_encode(['success' => false, 'message' => 'No restaurant associated']);
    exit;
}

if (!managerRestaurantTableReservationsUsable((int) $restaurantId)) {
    echo json_encode(['success' => false, 'message' => 'Table reservations are not available for this restaurant.']);
    exit;
}

$pdo = getDBConnection();
if (!$pdo) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
}

$action = trim($_GET['action'] ?? $_POST['action'] ?? '');
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// GET actions
if ($method === 'GET') {
    switch ($action) {
        case 'availability':
            $date = trim($_GET['date'] ?? '');
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                echo json_encode(['success' => false, 'message' => 'Invalid date']);
                exit;
            }
            $data = getTableAvailabilityForDate($restaurantId, $date);
            echo json_encode(['success' => true, 'availability' => $data]);
            exit;

        case 'month':
            $year = (int)($_GET['year'] ?? date('Y'));
            $month = (int)($_GET['month'] ?? date('n'));
            $startDate = sprintf('%04d-%02d-01', $year, $month);
            $lastDay = (int)date('t', strtotime($startDate));
            $endDate = sprintf('%04d-%02d-%02d', $year, $month, $lastDay);
            $data = getDateAvailabilityRange($restaurantId, $startDate, $endDate);
            echo json_encode(['success' => true, 'dates' => $data]);
            exit;

        case 'day_detail':
            $date = trim($_GET['date'] ?? '');
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                echo json_encode(['success' => false, 'message' => 'Invalid date']);
                exit;
            }
            $availability = getTableAvailabilityForDate($restaurantId, $date);
            // Fetch reservation list for this day (is_walkin requires migration)
            try {
                $stmt = $pdo->prepare("
                    SELECT id, reservation_number, reservation_time, party_size, guest_name, status, is_walkin, deposit_amount, deposit_paid
                    FROM table_reservations
                    WHERE restaurant_id = ? AND reservation_date = ?
                    ORDER BY reservation_time ASC
                ");
                $stmt->execute([$restaurantId, $date]);
                $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                $stmt = $pdo->prepare("
                    SELECT id, reservation_time, party_size, guest_name, status, deposit_amount, deposit_paid
                    FROM table_reservations
                    WHERE restaurant_id = ? AND reservation_date = ?
                    ORDER BY reservation_time ASC
                ");
                $stmt->execute([$restaurantId, $date]);
                $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($reservations as &$r) { $r['is_walkin'] = 0; $r['reservation_number'] = null; }
            }
            echo json_encode([
                'success' => true,
                'availability' => $availability,
                'reservations' => $reservations,
            ]);
            exit;
    }
}

// POST actions
if ($method === 'POST') {
    $csrf = trim((string)($_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '')));
    if (!validateCSRFToken($csrf)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Invalid security token']);
        exit;
    }
    switch ($action) {
        case 'set_total':
            $date = trim($_POST['date'] ?? '');
            $total = (int)($_POST['total_tables'] ?? 0);
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || $total < 1 || $total > 999) {
                echo json_encode(['success' => false, 'message' => 'Invalid date or total']);
                exit;
            }
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO table_inventory_daily (restaurant_id, inventory_date, total_tables)
                    VALUES (?, ?, ?)
                    ON DUPLICATE KEY UPDATE total_tables = ?, updated_at = NOW()
                ");
                $stmt->execute([$restaurantId, $date, $total, $total]);
            } catch (PDOException $e) {
                error_log("table-inventory set_total: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Database error. Please run the table inventory migration.']);
                exit;
            }
            $data = getTableAvailabilityForDate($restaurantId, $date);
            echo json_encode(['success' => true, 'availability' => $data]);
            exit;

        case 'bulk_set_total':
            $startDate = trim($_POST['start_date'] ?? '');
            $endDate = trim($_POST['end_date'] ?? '');
            $total = (int)($_POST['total_tables'] ?? 0);
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate) || $total < 1 || $total > 999) {
                echo json_encode(['success' => false, 'message' => 'Invalid date range or total']);
                exit;
            }
            $start = strtotime($startDate);
            $end = strtotime($endDate);
            if ($start === false || $end === false || $start > $end) {
                echo json_encode(['success' => false, 'message' => 'Invalid date range']);
                exit;
            }
            $updatedCount = 0;
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO table_inventory_daily (restaurant_id, inventory_date, total_tables)
                    VALUES (?, ?, ?)
                    ON DUPLICATE KEY UPDATE total_tables = ?, updated_at = NOW()
                ");
                for ($t = $start; $t <= $end; $t += 86400) {
                    $d = date('Y-m-d', $t);
                    $stmt->execute([$restaurantId, $d, $total, $total]);
                    $updatedCount++;
                }
            } catch (PDOException $e) {
                error_log("table-inventory bulk_set_total: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Database error. Please run the table inventory migration.']);
                exit;
            }
            echo json_encode(['success' => true, 'updated_count' => $updatedCount]);
            exit;

        case 'add_walkin':
            $date = trim($_POST['date'] ?? '');
            $time = trim($_POST['time'] ?? '18:00:00');
            $guestName = trim($_POST['guest_name'] ?? 'Walk-in');
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                echo json_encode(['success' => false, 'message' => 'Invalid date']);
                exit;
            }
            $availability = getTableAvailabilityForDate($restaurantId, $date);
            if ($availability['available'] <= 0) {
                echo json_encode(['success' => false, 'message' => 'No tables available for this date']);
                exit;
            }
            if (strlen($time) === 5) $time .= ':00';
            require_once __DIR__ . '/../includes/order-functions.php';
            $reservationNumber = generateReservationNumber();
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO table_reservations (restaurant_id, reservation_number, reservation_date, reservation_time, party_size, guest_name, guest_email, guest_phone, status, is_walkin, deposit_amount, deposit_paid)
                    VALUES (?, ?, ?, ?, 1, ?, '', '', 'confirmed', 1, 0, 0)
                ");
                $stmt->execute([$restaurantId, $reservationNumber, $date, $time, $guestName]);
            } catch (PDOException $e) {
                error_log("table-inventory add_walkin: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Database error. Please run the table inventory migration.']);
                exit;
            }
            $data = getTableAvailabilityForDate($restaurantId, $date);
            echo json_encode(['success' => true, 'availability' => $data]);
            exit;
    }
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);
