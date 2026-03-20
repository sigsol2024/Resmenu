<?php
/**
 * QR Code Redirect Page
 * Tracks scans and redirects to restaurant menu
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/qr-analytics.php';
require_once __DIR__ . '/../config/config.php';

$restaurantSlug = $_GET['slug'] ?? '';
$sectionSlug = $_GET['section'] ?? '';

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

// Redirect to restaurant menu (or a specific section)
$restaurantSlugEnc = urlencode($restaurantSlug);
if (!empty($sectionSlug)) {
    // Match section slugs used in the app: lowercase letters/numbers/dashes
    $sectionSlug = strtolower(trim((string)$sectionSlug));
    $sectionSlug = preg_replace('/[^a-z0-9-]/', '', $sectionSlug);
}

if (!empty($sectionSlug)) {
    // Section pages use path-style URLs: /restaurant/{slug}/{section}
    header('Location: ' . SITE_URL . '/restaurant/' . $restaurantSlugEnc . '/' . urlencode($sectionSlug));
} else {
    header('Location: ' . SITE_URL . '/restaurant/' . $restaurantSlugEnc);
}
exit;


