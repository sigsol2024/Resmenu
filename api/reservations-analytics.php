<?php
/**
 * Reservations Analytics API
 * Returns stats and filtered reservation list for Manager.
 */

header('Content-Type: application/json');

try {
    require_once __DIR__ . '/../includes/auth.php';
    require_once __DIR__ . '/../includes/functions.php';

    if (!isLoggedIn() || !isManager()) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    $restaurantId = getCurrentUserRestaurantId();
    if (!$restaurantId) {
        echo json_encode(['success' => false, 'message' => 'No restaurant associated']);
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

    $allowedStatuses = ['pending', 'confirmed', 'rejected', 'cancelled', 'completed'];
    if ($statusFilter && !in_array($statusFilter, $allowedStatuses)) {
        $statusFilter = '';
    }

    $response = ['success' => true];

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
