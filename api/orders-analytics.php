<?php
/**
 * Order Analytics API
 * Returns revenue by date, order counts by status, and filtered order list.
 * Used by Manager Orders page, Manager restaurant-orders, and Admin Restaurants Overview.
 */

header('Content-Type: application/json');

try {
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/order-functions.php';

// Require either manager or super admin
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$isManager = isManager();
$isSuperAdmin = isSuperAdmin();

if (!$isManager && !$isSuperAdmin) {
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

$pdo = getDBConnection();
if (!$pdo) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
}

// Determine restaurant_id: managers restricted to their restaurant
$restaurantId = null;
if ($isManager) {
    $restaurantId = getCurrentUserRestaurantId();
    if (!$restaurantId) {
        echo json_encode(['success' => false, 'message' => 'No restaurant associated']);
        exit;
    }
} else {
    // Super admin: optional restaurant filter
    $restaurantId = isset($_GET['restaurant_id']) ? (int) $_GET['restaurant_id'] : null;
}

$range = trim($_GET['range'] ?? '');
$startDate = trim($_GET['start_date'] ?? '');
$endDate = trim($_GET['end_date'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');
$action = trim($_GET['action'] ?? 'analytics'); // 'analytics' | 'orders' | 'restaurants_overview'

$allowedStatuses = ['pending', 'confirmed', 'on_hold', 'cancelled', 'completed'];
if ($statusFilter && !in_array($statusFilter, $allowedStatuses)) {
    $statusFilter = '';
}

$allowedRanges = ['today', '3days', '7days', '1month', 'all'];
if ($range && !in_array($range, $allowedRanges)) {
    $range = '';
}

$allowedActions = ['analytics', 'orders', 'restaurants_overview'];
if ($action && !in_array($action, $allowedActions)) {
    $action = 'analytics';
}

// Validate date format (YYYY-MM-DD)
function _ordersAnalyticsDateValid($d) {
    return preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) && strtotime($d) !== false;
}

// Build date range for SQL
$dateFrom = null;
$dateTo = null;

if (!empty($startDate) && !empty($endDate) && _ordersAnalyticsDateValid($startDate) && _ordersAnalyticsDateValid($endDate)) {
    $dateFrom = $startDate . ' 00:00:00';
    $dateTo = $endDate . ' 23:59:59';
} else {
    $now = new DateTime('now');
    $todayEnd = $now->format('Y-m-d') . ' 23:59:59';
    switch ($range) {
        case 'today':
            $dateFrom = $now->format('Y-m-d') . ' 00:00:00';
            $dateTo = $todayEnd;
            break;
        case '3days':
            $from = (clone $now)->modify('-3 days');
            $dateFrom = $from->format('Y-m-d') . ' 00:00:00';
            $dateTo = $todayEnd;
            break;
        case '7days':
            $from = (clone $now)->modify('-7 days');
            $dateFrom = $from->format('Y-m-d') . ' 00:00:00';
            $dateTo = $todayEnd;
            break;
        case '1month':
            $from = (clone $now)->modify('-1 month');
            $dateFrom = $from->format('Y-m-d') . ' 00:00:00';
            $dateTo = $todayEnd;
            break;
        case 'all':
        default:
            break;
    }
}

$response = ['success' => true];

// Revenue by date (for revenue chart)
if ($action === 'analytics' || $action === '') {
    $revenueSql = "SELECT DATE(created_at) AS date, COALESCE(SUM(total), 0) AS revenue
        FROM orders WHERE status IN ('pending','confirmed','on_hold','completed')";
    $params = [];
    if ($restaurantId) {
        $revenueSql .= " AND restaurant_id = ?";
        $params[] = $restaurantId;
    }
    if ($dateFrom) {
        $revenueSql .= " AND created_at >= ?";
        $params[] = $dateFrom;
    }
    if ($dateTo) {
        $revenueSql .= " AND created_at <= ?";
        $params[] = $dateTo;
    }
    $revenueSql .= " GROUP BY DATE(created_at) ORDER BY date ASC";

    $stmt = $params ? $pdo->prepare($revenueSql) : $pdo->query($revenueSql);
    if ($params) $stmt->execute($params);
    $response['revenue_by_date'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Order counts by status
    $statusSql = "SELECT status, COUNT(*) AS cnt FROM orders WHERE 1=1";
    $sparams = [];
    if ($restaurantId) {
        $statusSql .= " AND restaurant_id = ?";
        $sparams[] = $restaurantId;
    }
    if ($dateFrom) {
        $statusSql .= " AND created_at >= ?";
        $sparams[] = $dateFrom;
    }
    if ($dateTo) {
        $statusSql .= " AND created_at <= ?";
        $sparams[] = $dateTo;
    }
    $statusSql .= " GROUP BY status";

    $stmt = $pdo->prepare($statusSql);
    $stmt->execute($sparams);
    $statusRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $statuses = ['pending', 'confirmed', 'on_hold', 'cancelled', 'completed'];
    $response['counts_by_status'] = array_fill_keys($statuses, 0);
    foreach ($statusRows as $row) {
        if (in_array($row['status'], $statuses)) {
            $response['counts_by_status'][$row['status']] = (int) $row['cnt'];
        }
    }
}

// Filtered order list (for restaurant-orders page)
if ($action === 'orders' || $action === '') {
    $ordersSql = "SELECT o.*, r.name AS restaurant_name, r.slug AS restaurant_slug
        FROM orders o
        LEFT JOIN restaurants r ON r.id = o.restaurant_id
        WHERE 1=1";
    $oparams = [];
    if ($restaurantId) {
        $ordersSql .= " AND o.restaurant_id = ?";
        $oparams[] = $restaurantId;
    }
    if ($dateFrom) {
        $ordersSql .= " AND o.created_at >= ?";
        $oparams[] = $dateFrom;
    }
    if ($dateTo) {
        $ordersSql .= " AND o.created_at <= ?";
        $oparams[] = $dateTo;
    }
    if ($statusFilter && $statusFilter !== 'all') {
        $ordersSql .= " AND o.status = ?";
        $oparams[] = $statusFilter;
    }
    $ordersSql .= " ORDER BY o.created_at DESC LIMIT 500";

    $stmt = $pdo->prepare($ordersSql);
    $stmt->execute($oparams);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($orders as &$o) {
        $o['order_display_number'] = getOrderDisplayNumber($o);
    }
    unset($o);

    $response['orders'] = $orders;
}

// Restaurants overview (Super Admin): aggregated totals per restaurant
if ($action === 'restaurants_overview' && $isSuperAdmin()) {
    try {
        $joinCond = "o.restaurant_id = r.id";
        if ($dateFrom) $joinCond .= " AND o.created_at >= ?";
        if ($dateTo) $joinCond .= " AND o.created_at <= ?";
        $overviewSql = "SELECT r.id, r.name, r.slug,
            COUNT(o.id) AS total_orders,
            COALESCE(SUM(CASE WHEN o.status IN ('pending','confirmed','on_hold','completed') THEN o.total ELSE 0 END), 0) AS total_revenue
            FROM restaurants r
            LEFT JOIN orders o ON " . $joinCond;
        $oparams = [];
        if ($dateFrom) $oparams[] = $dateFrom;
        if ($dateTo) $oparams[] = $dateTo;
        if ($restaurantId) {
            $overviewSql .= " WHERE r.id = ?";
            $oparams[] = $restaurantId;
        }
        $overviewSql .= " GROUP BY r.id, r.name, r.slug ORDER BY r.name ASC";

        $stmt = $pdo->prepare($overviewSql);
        $stmt->execute($oparams);
        $response['restaurants'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("orders-analytics restaurants_overview: " . $e->getMessage());
        $response['success'] = false;
        $response['message'] = 'Failed to load restaurants overview';
        $response['restaurants'] = [];
    }
}

echo json_encode($response);

} catch (Throwable $e) {
    error_log("orders-analytics error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.', 'restaurants' => [], 'orders' => [], 'revenue_by_date' => [], 'counts_by_status' => []]);
}
