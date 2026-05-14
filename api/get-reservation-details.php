<?php
/**
 * Get single reservation details (Manager only)
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/auth.php';
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

require_once __DIR__ . '/../includes/manager-feature-access.php';
if (!managerRestaurantTableReservationsUsable((int) $restaurantId)) {
    echo json_encode(['success' => false, 'message' => 'Table reservations are not available for this restaurant.']);
    exit;
}

$reservationId = isset($_GET['reservation_id']) ? (int)$_GET['reservation_id'] : 0;
if (!$reservationId) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$pdo = getDBConnection();
if (!$pdo) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM table_reservations WHERE id = ? AND restaurant_id = ?");
$stmt->execute([$reservationId, $restaurantId]);
$reservation = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reservation) {
    echo json_encode(['success' => false, 'message' => 'Reservation not found']);
    exit;
}

echo json_encode(['success' => true, 'reservation' => $reservation]);
