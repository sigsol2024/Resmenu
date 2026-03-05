<?php
/**
 * API: Template list for marketing (resmenu.net)
 * Returns id, name, description, preview_image (full URL) for all active templates.
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $pdo = getDBConnection();
    $baseUrl = defined('SITE_URL') ? rtrim(SITE_URL, '/') : '';
    $uploadBase = $baseUrl . '/uploads/template-previews';

    if (!$pdo) {
        jsonResponse(false, 'Database unavailable', null);
        exit;
    }

    $stmt = $pdo->query("SELECT id, name, description, preview_image, listing_image FROM templates WHERE is_active = 1 ORDER BY id DESC LIMIT 5");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $list = [];
    foreach ($rows as $row) {
        $previewUrl = null;
        if (!empty($row['preview_image'])) {
            $previewUrl = $uploadBase . '/' . $row['preview_image'];
        }
        $listingUrl = null;
        if (!empty($row['listing_image'])) {
            $listingUrl = $uploadBase . '/' . $row['listing_image'];
        }
        $list[] = [
            'id' => (int) $row['id'],
            'name' => $row['name'],
            'description' => $row['description'] ?? '',
            'preview_image' => $previewUrl,
            'listing_image' => $listingUrl,
        ];
    }

    jsonResponse(true, 'Templates retrieved successfully', $list);
} catch (Exception $e) {
    error_log("API templates.php: " . $e->getMessage());
    jsonResponse(false, 'Failed to retrieve templates', null);
}
