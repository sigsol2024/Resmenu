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
require_once __DIR__ . '/../includes/manager-feature-access.php';

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
    if (!managerRestaurantFoodOrderingUsable((int) $restaurantId)) {
        echo json_encode(['success' => false, 'message' => 'Food ordering is not available for this restaurant.']);
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

$allowedRanges = ['today', '2days', '3days', '7days', '1month', 'all'];
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
        case '2days':
            $from = (clone $now)->modify('-2 days');
            $dateFrom = $from->format('Y-m-d') . ' 00:00:00';
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

// Restaurants overview (Super Admin): aggregated totals per restaurant + menu_items, categories
if ($action === 'restaurants_overview' && $isSuperAdmin()) {
    try {
        // Use subqueries (no GROUP BY) for MySQL compatibility
        $ordersWhereCnt = "o.restaurant_id = r.id";
        $ordersWhereRev = "o.restaurant_id = r.id AND o.status IN ('pending','confirmed','on_hold','completed')";
        $oparams = [];
        if ($dateFrom) {
            $ordersWhereCnt .= " AND o.created_at >= ?";
            $ordersWhereRev .= " AND o.created_at >= ?";
        }
        if ($dateTo) {
            $ordersWhereCnt .= " AND o.created_at <= ?";
            $ordersWhereRev .= " AND o.created_at <= ?";
        }
        // Params for both subqueries (each needs dateFrom, dateTo in order)
        if ($dateFrom) { $oparams[] = $dateFrom; }
        if ($dateTo) { $oparams[] = $dateTo; }
        if ($dateFrom) { $oparams[] = $dateFrom; }
        if ($dateTo) { $oparams[] = $dateTo; }
        $overviewSql = "SELECT r.id, r.name, r.slug, r.is_active,
            (SELECT COUNT(*) FROM orders o WHERE " . $ordersWhereCnt . ") AS total_orders,
            (SELECT COALESCE(SUM(o.total), 0) FROM orders o WHERE " . $ordersWhereRev . ") AS total_revenue,
            (SELECT COUNT(*) FROM menu_items m WHERE m.restaurant_id = r.id) AS menu_items,
            (SELECT COUNT(*) FROM categories c WHERE c.restaurant_id = r.id) AS categories
            FROM restaurants r";
        if ($restaurantId) {
            $overviewSql .= " WHERE r.id = ?";
            $oparams[] = $restaurantId;
        }
        $overviewSql .= " ORDER BY r.name ASC";

        $stmt = $pdo->prepare($overviewSql);
        $stmt->execute($oparams);
        $restaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $response['restaurants'] = $restaurants;

        // Summary totals across filtered restaurants
        $summary = [
            'total_revenue' => 0,
            'total_orders' => 0,
            'total_menu_items' => 0,
            'total_categories' => 0
        ];
        foreach ($restaurants as $r) {
            $summary['total_revenue'] += (float)($r['total_revenue'] ?? 0);
            $summary['total_orders'] += (int)($r['total_orders'] ?? 0);
            $summary['total_menu_items'] += (int)($r['menu_items'] ?? 0);
            $summary['total_categories'] += (int)($r['categories'] ?? 0);
        }
        $response['summary'] = $summary;
    } catch (Throwable $e) {
        error_log("orders-analytics restaurants_overview: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
        $errMsg = $e->getMessage();
        if (stripos($errMsg, 'orders') !== false && (stripos($errMsg, "doesn't exist") !== false || stripos($errMsg, 'exist') !== false)) {
            try {
                $fallbackSql = "SELECT r.id, r.name, r.slug, r.is_active, 0 AS total_orders, 0 AS total_revenue,
                    (SELECT COUNT(*) FROM menu_items m WHERE m.restaurant_id = r.id) AS menu_items,
                    (SELECT COUNT(*) FROM categories c WHERE c.restaurant_id = r.id) AS categories
                    FROM restaurants r" . ($restaurantId ? " WHERE r.id = ?" : "") . " ORDER BY r.name ASC";
                $stmt = $pdo->prepare($fallbackSql);
                $stmt->execute($restaurantId ? [$restaurantId] : []);
                $restaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $response['restaurants'] = $restaurants;
                $summary = ['total_revenue' => 0, 'total_orders' => 0, 'total_menu_items' => 0, 'total_categories' => 0];
                foreach ($restaurants as $r) {
                    $summary['total_menu_items'] += (int)($r['menu_items'] ?? 0);
                    $summary['total_categories'] += (int)($r['categories'] ?? 0);
                }
                $response['summary'] = $summary;
            } catch (Throwable $e2) {
                $response['success'] = false;
                $response['message'] = 'Failed to load restaurants overview';
                $response['restaurants'] = [];
                $response['summary'] = ['total_revenue' => 0, 'total_orders' => 0, 'total_menu_items' => 0, 'total_categories' => 0];
            }
        } else {
            $response['success'] = false;
            $response['message'] = 'Failed to load restaurants overview';
            $response['restaurants'] = [];
            $response['summary'] = ['total_revenue' => 0, 'total_orders' => 0, 'total_menu_items' => 0, 'total_categories' => 0];
        }
    }
}

echo json_encode($response);

} catch (Throwable $e) {
    error_log("orders-analytics error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.', 'restaurants' => [], 'summary' => ['total_revenue' => 0, 'total_orders' => 0, 'total_menu_items' => 0, 'total_categories' => 0], 'orders' => [], 'revenue_by_date' => [], 'counts_by_status' => []]);
}
