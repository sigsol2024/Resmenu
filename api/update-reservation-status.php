<?php
/**
 * Update Reservation Status (Manager only)
 * Approve, reject, or change status of a table reservation.
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/config.php';

$slug = trim($_POST['slug'] ?? $_GET['slug'] ?? '');
$returnTo = trim($_POST['return_to'] ?? $_GET['return_to'] ?? 'reservations');
$slugPart = $slug ? '?slug=' . urlencode($slug) : '';

if (!isLoggedIn() || !isManager()) {
    header('Location: /manager/reservations.php' . $slugPart);
    exit;
}

$restaurantId = getCurrentUserRestaurantId();
if (!$restaurantId) {
    header('Location: /manager/reservations.php' . $slugPart);
    exit;
}

$reservationId = isset($_POST['reservation_id']) ? (int)$_POST['reservation_id'] : 0;
$status = trim($_POST['status'] ?? '');

$allowedStatuses = ['pending', 'confirmed', 'rejected', 'cancelled', 'completed'];
if (!$reservationId || !in_array($status, $allowedStatuses)) {
    header('Location: ' . ($returnTo === 'restaurant-reservations' ? '/manager/restaurant-reservations.php' : '/manager/reservations.php') . $slugPart);
    exit;
}

$pdo = getDBConnection();
if (!$pdo) {
    header('Location: ' . ($returnTo === 'restaurant-reservations' ? '/manager/restaurant-reservations.php' : '/manager/reservations.php') . $slugPart);
    exit;
}

$stmt = $pdo->prepare("UPDATE table_reservations SET status = ?, updated_at = NOW() WHERE id = ? AND restaurant_id = ?");
$stmt->execute([$status, $reservationId, $restaurantId]);

if ($returnTo === 'restaurant-reservations') {
    header('Location: /manager/restaurant-reservations.php' . $slugPart);
} else {
    header('Location: /manager/reservations.php' . $slugPart);
}
exit;
