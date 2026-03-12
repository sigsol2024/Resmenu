<?php
/**
 * Reservation Date Availability API (Public)
 * Returns availability status per date for customer calendar.
 * No cancelled/pending/walkin breakdown - only available count and status.
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/subscription.php';
require_once __DIR__ . '/../includes/subscription-middleware.php';

$slug = trim($_GET['slug'] ?? '');
$start = trim($_GET['start'] ?? '');
$end = trim($_GET['end'] ?? '');

if (empty($slug) || empty($start) || empty($end)) {
    echo json_encode(['success' => false, 'message' => 'Missing slug, start, or end']);
    exit;
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end)) {
    echo json_encode(['success' => false, 'message' => 'Invalid date format']);
    exit;
}

$restaurant = getRestaurantBySlug($slug);
if (!$restaurant) {
    echo json_encode(['success' => false, 'message' => 'Restaurant not found']);
    exit;
}

$restaurantId = (int)$restaurant['id'];
$subscriptionAccess = checkSubscriptionAccess($restaurantId);
if (!$subscriptionAccess['valid']) {
    http_response_code(402);
    echo json_encode(['success' => false, 'message' => $subscriptionAccess['message'] ?: 'Subscription required']);
    exit;
}
if (!hasFeatureAccess($restaurantId, 'table_reservations')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Table reservations are not available for this restaurant plan.']);
    exit;
}

$restaurantId = (int)$restaurant['id'];
$availability = getDateAvailabilityRange($restaurantId, $start, $end);

$dates = [];
$today = date('Y-m-d');
foreach ($availability as $dateStr => $data) {
    $available = (int)$data['available'];
    $total = (int)$data['total'];
    if ($dateStr < $today) {
        $status = 'past';
    } elseif ($available <= 0) {
        $status = 'full';
    } elseif ($available < 3) {
        $status = 'limited';
    } else {
        $status = 'available';
    }
    $dates[$dateStr] = [
        'available' => $available,
        'total' => $total,
        'status' => $status,
    ];
}

echo json_encode(['success' => true, 'dates' => $dates]);
