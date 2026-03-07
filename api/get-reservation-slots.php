<?php
/**
 * Get available time slots for table reservations (AJAX)
 * Public endpoint - no auth required
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/subscription.php';
require_once __DIR__ . '/../includes/subscription-middleware.php';

$slug = trim($_GET['slug'] ?? '');
$date = trim($_GET['date'] ?? '');

if (empty($slug) || empty($date)) {
    echo json_encode(['success' => false, 'message' => 'Missing slug or date']);
    exit;
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || strtotime($date) === false) {
    echo json_encode(['success' => false, 'message' => 'Invalid date format']);
    exit;
}

$restaurant = getRestaurantBySlug($slug);
if (!$restaurant) {
    echo json_encode(['success' => false, 'message' => 'Restaurant not found']);
    exit;
}

$templateId = (int) ($restaurant['template_id'] ?? 1);
if ($templateId !== 4) {
    echo json_encode(['success' => false, 'message' => 'Reservations not available']);
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

$slots = getAvailableTimeSlots($restaurant['id'], $date);
$availability = getTableAvailabilityForDate($restaurant['id'], $date);
echo json_encode([
    'success' => true,
    'slots' => $slots,
    'tables_left' => $availability['available'],
    'day_available' => $availability['available'] > 0,
]);
