<?php
/**
 * QR Code Redirect Page
 * Tracks scans and redirects to restaurant menu
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/qr-analytics.php';
require_once __DIR__ . '/../config/config.php';

$restaurantSlug = $_GET['slug'] ?? '';

if (empty($restaurantSlug)) {
    http_response_code(404);
    die('Invalid QR code.');
}

// Get restaurant
$restaurant = getRestaurantBySlug($restaurantSlug);

if (!$restaurant || !$restaurant['is_active']) {
    http_response_code(404);
    die('Restaurant not found or inactive.');
}

// Track scan
trackQRCodeScan($restaurant['id']);

// Redirect to restaurant menu
header('Location: ' . SITE_URL . '/restaurant/' . urlencode($restaurantSlug));
exit;


