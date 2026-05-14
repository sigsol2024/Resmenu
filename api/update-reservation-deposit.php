<?php
/**
 * Update Reservation Deposit Amount (Manager only)
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../config/config.php';

if (!isLoggedIn() || !isManager()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$csrf = trim((string)($_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '')));
if (!validateCSRFToken($csrf)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid security token']);
    exit;
}

$restaurantId = getCurrentUserRestaurantId();
if (!$restaurantId) {
    echo json_encode(['success' => false, 'message' => 'No restaurant associated']);
    exit;
}

require_once __DIR__ . '/../includes/manager-feature-access.php';
if (!managerRestaurantTableReservationsUsable((int) $restaurantId)) {
    echo json_encode(['success' => false, 'message' => 'Table reservations are not available for this restaurant.']);
    exit;
}

$depositAmount = isset($_POST['deposit_amount']) ? (float)$_POST['deposit_amount'] : 0;
if ($depositAmount < 0) $depositAmount = 0;

$pdo = getDBConnection();
if (!$pdo) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
}

$stmt = $pdo->prepare("SELECT id FROM restaurant_reservation_settings WHERE restaurant_id = ?");
$stmt->execute([$restaurantId]);
$exists = $stmt->fetch();

if ($exists) {
    $stmt = $pdo->prepare("UPDATE restaurant_reservation_settings SET deposit_amount = ?, updated_at = NOW() WHERE restaurant_id = ?");
    $stmt->execute([$depositAmount, $restaurantId]);
} else {
    $stmt = $pdo->prepare("INSERT INTO restaurant_reservation_settings (restaurant_id, deposit_amount) VALUES (?, ?)");
    $stmt->execute([$restaurantId, $depositAmount]);
}

echo json_encode(['success' => true, 'deposit_amount' => $depositAmount]);
