<?php
/**
 * Reservations Analytics API
 * Returns stats and filtered reservation list for Manager.
 */

header('Content-Type: application/json');

try {
    require_once __DIR__ . '/../includes/auth.php';
    require_once __DIR__ . '/../includes/functions.php';
    require_once __DIR__ . '/../includes/manager-feature-access.php';

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

    $action = trim($_GET['action'] ?? 'analytics');
    $startDate = trim($_GET['start_date'] ?? '');
    $endDate = trim($_GET['end_date'] ?? '');
    $statusFilter = trim($_GET['status'] ?? '');
    $range = trim($_GET['range'] ?? 'all');

    $allowedStatuses = ['pending', 'confirmed', 'rejected', 'cancelled', 'completed'];
    if ($statusFilter && !in_array($statusFilter, $allowedStatuses)) {
        $statusFilter = '';
    }

    $allowedRanges = ['today', '3days', '7days', '1month', 'all'];
    if ($range && !in_array($range, $allowedRanges)) {
        $range = 'all';
    }

    // Build date range for revenue chart
    $dateFrom = null;
    $dateTo = null;
    if ($range && $range !== 'all') {
        $now = new DateTime('now');
        $todayEnd = $now->format('Y-m-d');
        switch ($range) {
            case 'today':
                $dateFrom = $todayEnd;
                $dateTo = $todayEnd;
                break;
            case '3days':
                $from = (clone $now)->modify('-3 days');
                $dateFrom = $from->format('Y-m-d');
                $dateTo = $todayEnd;
                break;
            case '7days':
                $from = (clone $now)->modify('-7 days');
                $dateFrom = $from->format('Y-m-d');
                $dateTo = $todayEnd;
                break;
            case '1month':
                $from = (clone $now)->modify('-1 month');
                $dateFrom = $from->format('Y-m-d');
                $dateTo = $todayEnd;
                break;
        }
    }

    $response = ['success' => true];

    // Revenue by date (deposit amounts collected - deposit_paid=1)
    // Use DATE(updated_at) = payment date (when deposit was marked paid), not reservation_date
    $revenueSql = "SELECT DATE(updated_at) AS date, COALESCE(SUM(deposit_amount), 0) AS revenue
        FROM table_reservations WHERE restaurant_id = ? AND deposit_paid = 1";
    $revParams = [$restaurantId];
    if ($dateFrom) {
        $revenueSql .= " AND DATE(updated_at) >= ?";
        $revParams[] = $dateFrom;
    }
    if ($dateTo) {
        $revenueSql .= " AND DATE(updated_at) <= ?";
        $revParams[] = $dateTo;
    }
    $revenueSql .= " GROUP BY DATE(updated_at) ORDER BY date ASC";
    $stmt = $pdo->prepare($revenueSql);
    $stmt->execute($revParams);
    $response['revenue_by_date'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Stats
    $statsByStatus = [];
    foreach ($allowedStatuses as $s) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM table_reservations WHERE restaurant_id = ? AND status = ?");
        $stmt->execute([$restaurantId, $s]);
        $statsByStatus[$s] = (int) $stmt->fetchColumn();
    }

    $todayStart = date('Y-m-d 00:00:00');
    $todayEnd = date('Y-m-d 23:59:59');
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM table_reservations WHERE restaurant_id = ? AND reservation_date = CURDATE()");
    $stmt->execute([$restaurantId]);
    $todayCount = (int) $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM table_reservations WHERE restaurant_id = ?");
    $stmt->execute([$restaurantId]);
    $totalCount = (int) $stmt->fetchColumn();

    $response['stats'] = [
        'by_status' => $statsByStatus,
        'today' => $todayCount,
        'total' => $totalCount
    ];

    // Reservation list (filtered)
    if ($action === 'reservations') {
        $sql = "SELECT * FROM table_reservations WHERE restaurant_id = ?";
        $params = [$restaurantId];

        if ($startDate && $endDate && preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
            $sql .= " AND reservation_date BETWEEN ? AND ?";
            $params[] = $startDate;
            $params[] = $endDate;
        }
        if ($statusFilter) {
            $sql .= " AND status = ?";
            $params[] = $statusFilter;
        }

        $sql .= " ORDER BY reservation_date DESC, reservation_time DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $response['reservations'] = $rows;
    }

    echo json_encode($response);
} catch (Exception $e) {
    error_log("reservations-analytics: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
