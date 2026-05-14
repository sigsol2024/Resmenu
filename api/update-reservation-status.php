<?php
/**
 * Update Reservation Status (Manager only)
 * Approve, reject, or change status of a table reservation.
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!validateCSRFToken($token)) {
        http_response_code(403);
        header('Content-Type: text/plain');
        echo 'Invalid security token. Please refresh and try again.';
        exit;
    }
}

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

require_once __DIR__ . '/../includes/manager-feature-access.php';
if (!managerRestaurantTableReservationsUsable((int) $restaurantId)) {
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

require_once __DIR__ . '/../includes/order-functions.php';
$pdo = getDBConnection();
if (!$pdo) {
    header('Location: ' . ($returnTo === 'restaurant-reservations' ? '/manager/restaurant-reservations.php' : '/manager/reservations.php') . $slugPart);
    exit;
}

$stmt = $pdo->prepare("UPDATE table_reservations SET status = ?, updated_at = NOW() WHERE id = ? AND restaurant_id = ?");
$stmt->execute([$status, $reservationId, $restaurantId]);

try {
    sendReservationStatusChangeEmail($reservationId, $restaurantId, $status);
} catch (Exception $e) {
    error_log("Reservation status email failed: " . $e->getMessage());
}

if ($returnTo === 'restaurant-reservations') {
    header('Location: /manager/restaurant-reservations.php' . $slugPart);
} else {
    header('Location: /manager/reservations.php' . $slugPart);
}
exit;
