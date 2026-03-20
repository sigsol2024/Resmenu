<?php
/**
 * API Endpoint: Generate QR Code Image
 * Uses template config directly (no manager overrides)
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/qr-generator.php';
require_once __DIR__ . '/../includes/qr-template-helper.php';
require_once __DIR__ . '/../config/config.php';

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

$format = $_GET['format'] ?? 'png';
$size = isset($_GET['size']) ? intval($_GET['size']) : null;
$download = isset($_GET['download']) && $_GET['download'] == '1';
$sectionSlug = $_GET['section_slug'] ?? ($_GET['section'] ?? '');

// Validate format
$allowedFormats = ['png', 'jpeg', 'jpg', 'svg', 'pdf'];
if (!in_array(strtolower($format), $allowedFormats)) {
    $format = 'png';
}

$sectionSlug = strtolower(trim((string)$sectionSlug));
$sectionSlug = preg_replace('/[^a-z0-9-]/', '', $sectionSlug);
if (!empty($sectionSlug)) {
    $GLOBALS['qr_target_section_slug'] = $sectionSlug;
} else {
    $GLOBALS['qr_target_section_slug'] = null;
}

$pdo = getDBConnection();

// Get restaurant's selected template
$stmt = $pdo->prepare("
    SELECT rqc.qr_template_id, rqc.final_config_json, qt.config_json as template_config_json, qt.is_active
    FROM restaurant_qr_codes rqc
    LEFT JOIN qr_templates qt ON rqc.qr_template_id = qt.id
    WHERE rqc.restaurant_id = ? AND rqc.is_active = 1
");
$stmt->execute([$restaurantId]);
$qrData = $stmt->fetch();

// Check if template is selected
if (!$qrData || !$qrData['qr_template_id']) {
    http_response_code(404);
    die('No QR code template selected. Please select a template first.');
}

// Get config - prefer final_config_json, fallback to template_config_json
$finalConfig = null;

if (!empty($qrData['final_config_json'])) {
    $finalConfig = is_string($qrData['final_config_json']) 
        ? json_decode($qrData['final_config_json'], true)
        : $qrData['final_config_json'];
}

// If final_config_json is missing or invalid, use template config directly
if (!$finalConfig && !empty($qrData['template_config_json'])) {
    $finalConfig = is_string($qrData['template_config_json']) 
        ? json_decode($qrData['template_config_json'], true)
        : $qrData['template_config_json'];
    
    // Save it as final_config for next time
    if ($finalConfig) {
        $configJson = is_string($qrData['template_config_json']) 
            ? $qrData['template_config_json'] 
            : json_encode($finalConfig);
        $stmt = $pdo->prepare("UPDATE restaurant_qr_codes SET final_config_json = ? WHERE restaurant_id = ?");
        $stmt->execute([$configJson, $restaurantId]);
    }
}

if (!$finalConfig) {
    http_response_code(404);
    die('QR code template configuration not found. Please contact your administrator.');
}

// Store config globally for the generator functions
$GLOBALS['qr_final_config'] = $finalConfig;

try {
    if ($format === 'pdf') {
        $fileData = generateQRCodePDF($restaurantId);
        if ($fileData) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: ' . ($download ? 'attachment' : 'inline') . '; filename="qr-code.pdf"');
            echo $fileData;
            exit;
        } else {
            http_response_code(500);
            die('Failed to generate QR code PDF. Please try again.');
        }
    } else {
        $result = generateQRCodeImage($restaurantId, $format, $size);
        $fileData = is_object($result) ? $result->getString() : $result;
        
        if ($fileData === null) {
            http_response_code(500);
            die('Failed to generate QR code. Please try again.');
        }
        
        // Check if we got SVG when we expected an image format
        if (($format === 'png' || $format === 'jpeg' || $format === 'jpg') && 
            is_string($fileData) && 
            strpos($fileData, '<svg') !== false) {
            http_response_code(500);
            die('Image conversion failed. Server configuration issue.');
        }
        
        $mimeTypes = [
            'png' => 'image/png',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'svg' => 'image/svg+xml'
        ];
        
        header('Content-Type: ' . ($mimeTypes[$format] ?? 'image/png'));
        header('Content-Disposition: ' . ($download ? 'attachment' : 'inline') . '; filename="qr-code.' . $format . '"');
        header('Cache-Control: no-cache'); // Don't cache - template might change
        echo $fileData;
        exit;
    }
} catch (Exception $e) {
    error_log("QR Code generation error: " . $e->getMessage());
    http_response_code(500);
    die('Error generating QR code: ' . htmlspecialchars($e->getMessage()));
}
