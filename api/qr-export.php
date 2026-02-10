<?php
/**
 * API Endpoint: Export QR Code Analytics to CSV
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/qr-analytics.php';

// Check if manager or admin
$restaurantId = null;

if (isManager()) {
    $restaurantId = getCurrentUserRestaurantId();
} elseif (isSuperAdmin()) {
    $restaurantId = isset($_GET['restaurant_id']) ? intval($_GET['restaurant_id']) : null;
}

if (!$restaurantId) {
    http_response_code(403);
    die('Access denied.');
}

$format = $_GET['format'] ?? 'csv';
$startDate = $_GET['start_date'] ?? null;
$endDate = $_GET['end_date'] ?? null;

if ($format !== 'csv') {
    http_response_code(400);
    die('Only CSV format is supported.');
}

// Get analytics
$analytics = getQRCodeAnalytics($restaurantId, $startDate, $endDate);

if (!$analytics) {
    http_response_code(500);
    die('Failed to retrieve analytics.');
}

// Get restaurant name
$pdo = getDBConnection();
$stmt = $pdo->prepare("SELECT name FROM restaurants WHERE id = ?");
$stmt->execute([$restaurantId]);
$restaurant = $stmt->fetch();
$restaurantName = $restaurant['name'] ?? 'Restaurant';

// Output CSV
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="qr-analytics-' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');

// Write header
fputcsv($output, ['QR Code Analytics - ' . $restaurantName]);
fputcsv($output, ['Generated: ' . date('Y-m-d H:i:s')]);
fputcsv($output, []);

// Summary
fputcsv($output, ['Summary']);
fputcsv($output, ['Total Scans', $analytics['total_scans']]);
fputcsv($output, []);

// Scans by device
fputcsv($output, ['Scans by Device Type']);
fputcsv($output, ['Device Type', 'Count']);
foreach ($analytics['scans_by_device'] as $device) {
    fputcsv($output, [$device['device_type'], $device['count']]);
}
fputcsv($output, []);

// Scans by browser
fputcsv($output, ['Scans by Browser']);
fputcsv($output, ['Browser', 'Count']);
foreach ($analytics['scans_by_browser'] as $browser) {
    fputcsv($output, [$browser['browser'], $browser['count']]);
}
fputcsv($output, []);

// Scans by location
fputcsv($output, ['Scans by Location']);
fputcsv($output, ['Country', 'City', 'Count']);
foreach ($analytics['scans_by_location'] as $location) {
    fputcsv($output, [
        $location['country'] ?? 'Unknown',
        $location['city'] ?? 'Unknown',
        $location['count']
    ]);
}
fputcsv($output, []);

// Recent scans
fputcsv($output, ['Recent Scans']);
fputcsv($output, ['Date & Time', 'Device', 'Browser', 'OS', 'Country', 'City']);
foreach ($analytics['recent_scans'] as $scan) {
    fputcsv($output, [
        $scan['scanned_at'],
        $scan['device_type'] ?? 'Unknown',
        $scan['browser'] ?? 'Unknown',
        $scan['os'] ?? 'Unknown',
        $scan['country'] ?? 'Unknown',
        $scan['city'] ?? 'Unknown'
    ]);
}

fclose($output);
exit;

