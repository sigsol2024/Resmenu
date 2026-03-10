<?php
/**
 * Serve QR template preview image for manager "Select Template" cards.
 * 1. If template has a saved preview file (preview_image), stream it.
 * 2. Else generate on the fly and output (PNG or SVG).
 * No auth required; templates are public designs.
 */
ob_start();

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/qr-generator.php';
require_once __DIR__ . '/../includes/qr-template-helper.php';

$templateId = isset($_GET['template_id']) ? (int)$_GET['template_id'] : 0;
$size = (int)($_GET['size'] ?? 200);
if ($templateId < 1) {
    ob_end_clean();
    header('HTTP/1.0 400 Bad Request');
    exit;
}

$pdo = getDBConnection();
if (!$pdo) {
    ob_end_clean();
    header('HTTP/1.0 503 Service Unavailable');
    exit;
}

$stmt = $pdo->prepare("SELECT preview_image, config_json FROM qr_templates WHERE id = ? AND is_active = 1");
$stmt->execute([$templateId]);
$row = $stmt->fetch();
if (!$row) {
    ob_end_clean();
    header('HTTP/1.0 404 Not Found');
    exit;
}

// 1. Serve saved preview file if it exists
$previewImage = $row['preview_image'] ?? '';
if ($previewImage !== '' && preg_match('/^[a-z0-9_.-]+\.(png|svg)$/i', $previewImage)) {
    $dir = defined('UPLOAD_PATH') ? (UPLOAD_PATH . '/qr-templates') : (dirname(__DIR__) . '/uploads/qr-templates');
    $path = $dir . '/' . $previewImage;
    if (file_exists($path) && is_readable($path)) {
        ob_end_clean();
        $ext = strtolower(pathinfo($previewImage, PATHINFO_EXTENSION));
        if ($ext === 'svg') {
            header('Content-Type: image/svg+xml');
        } else {
            header('Content-Type: image/png');
        }
        header('Cache-Control: public, max-age=86400');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }
}

// 2. Generate on the fly
$config = null;
if (!empty($row['config_json'])) {
    $config = is_string($row['config_json']) ? json_decode($row['config_json'], true) : $row['config_json'];
}
if (!$config || !is_array($config)) {
    ob_end_clean();
    header('Content-Type: image/svg+xml');
    header('Cache-Control: public, max-age=60');
    echo '<svg width="' . $size . '" height="' . $size . '" xmlns="http://www.w3.org/2000/svg"><rect width="100%" height="100%" fill="#f9fafb"/><text x="50%" y="50%" text-anchor="middle" fill="#6b7280" font-size="12" font-family="sans-serif">No preview</text></svg>';
    exit;
}

$result = generateQRImageFromConfig($config, 'https://menu.example.com/preview', $size, 'png');
if (!$result) {
    ob_end_clean();
    header('Content-Type: image/svg+xml');
    header('Cache-Control: public, max-age=60');
    echo '<svg width="' . $size . '" height="' . $size . '" xmlns="http://www.w3.org/2000/svg"><rect width="100%" height="100%" fill="#fef2f2"/><text x="50%" y="50%" text-anchor="middle" fill="#991b1b" font-size="10" font-family="sans-serif">Error</text></svg>';
    exit;
}

ob_end_clean();
if ($result['format'] === 'svg') {
    header('Content-Type: image/svg+xml');
} else {
    header('Content-Type: image/png');
}
header('Cache-Control: public, max-age=300');
echo $result['data'];
