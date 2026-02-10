<?php
/**
 * QR Template Preview API
 * Generates preview QR code based on template config
 * Works for both admin (POST with config) and manager/public (GET with template_id)
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/qr-generator.php';
require_once __DIR__ . '/../includes/qr-template-helper.php';

$config = null;
$size = intval($_GET['size'] ?? 300);

// Method 1: GET request with template_id (for viewing template previews)
// This is public because templates are public designs and previews don't contain sensitive data
if (isset($_GET['template_id'])) {
    $templateId = intval($_GET['template_id']);
    
    // Get template config from database (only active templates)
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT config_json FROM qr_templates WHERE id = ? AND is_active = 1");
    $stmt->execute([$templateId]);
    $template = $stmt->fetch();
    
    if ($template && !empty($template['config_json'])) {
        $config = is_string($template['config_json']) 
            ? json_decode($template['config_json'], true)
            : $template['config_json'];
    }
}

// Method 2: POST request with config (for admin live preview)
if (!$config && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../includes/auth.php';
    requireSuperAdmin(); // Only admins can use POST method
    
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);
    $config = $input['config'] ?? null;
}

// Set content type for SVG
header('Content-Type: image/svg+xml');
header('Cache-Control: public, max-age=300'); // Cache for 5 minutes

if (!$config) {
    // Return error SVG
    echo '<svg width="' . $size . '" height="' . $size . '" xmlns="http://www.w3.org/2000/svg">
        <rect width="100%" height="100%" fill="#f9fafb"/>
        <text x="50%" y="50%" text-anchor="middle" fill="#6b7280" font-size="12" font-family="sans-serif">No preview available</text>
    </svg>';
    exit;
}

// Check if QR Code library is available
$qrCodeAvailable = false;
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
    $qrCodeAvailable = class_exists('Endroid\QrCode\QrCode');
}

if (!$qrCodeAvailable) {
    echo '<svg width="' . $size . '" height="' . $size . '" xmlns="http://www.w3.org/2000/svg">
        <rect width="100%" height="100%" fill="#fef2f2"/>
        <text x="50%" y="50%" text-anchor="middle" fill="#991b1b" font-size="11" font-family="sans-serif">QR library not available</text>
    </svg>';
    exit;
}

// Use test data for preview
$testURL = 'https://menu.example.com/preview';

try {
    // Get colors from config
    $foregroundColor = $config['colors']['foreground'] ?? '#000000';
    $backgroundColor = $config['colors']['background'] ?? '#FFFFFF';
    
    $qrColor = new \Endroid\QrCode\Color\Color(
        hexdec(substr($foregroundColor, 1, 2)),
        hexdec(substr($foregroundColor, 3, 2)),
        hexdec(substr($foregroundColor, 5, 2))
    );
    $bgColor = new \Endroid\QrCode\Color\Color(
        hexdec(substr($backgroundColor, 1, 2)),
        hexdec(substr($backgroundColor, 3, 2)),
        hexdec(substr($backgroundColor, 5, 2))
    );
    
    // Generate base QR code as SVG
    $qrCode = \Endroid\QrCode\QrCode::create($testURL)
        ->setEncoding(new \Endroid\QrCode\Encoding\Encoding('UTF-8'))
        ->setErrorCorrectionLevel(\Endroid\QrCode\ErrorCorrectionLevel::Medium)
        ->setSize($size)
        ->setMargin(10)
        ->setForegroundColor($qrColor)
        ->setBackgroundColor($bgColor)
        ->setRoundBlockSizeMode(\Endroid\QrCode\RoundBlockSizeMode::Margin);
    
    $svgWriter = new \Endroid\QrCode\Writer\SvgWriter();
    $result = $svgWriter->write($qrCode);
    $svgContent = $result->getString();
    
    // Apply template config customizations
    if (isset($config['pattern']) && function_exists('applyPatternStyle')) {
        $svgContent = applyPatternStyle($svgContent, $config['pattern']);
    }
    
    if (isset($config['eyes']) && function_exists('applyEyeShape')) {
        $svgContent = applyEyeShape($svgContent, $config['eyes']);
    }
    
    if (isset($config['frame']) && $config['frame']['type'] !== 'none' && function_exists('applyFrame')) {
        $svgContent = applyFrame($svgContent, $config['frame']);
    }
    
    // Add frame text if present
    if (isset($config['frame']) && !empty($config['frame']['text']) && function_exists('addFrameTextToSVG')) {
        $svgContent = addFrameTextToSVG($svgContent, $config['frame'], $size);
    }
    
    echo $svgContent;
    
} catch (Exception $e) {
    error_log("QR Preview generation error: " . $e->getMessage());
    echo '<svg width="' . $size . '" height="' . $size . '" xmlns="http://www.w3.org/2000/svg">
        <rect width="100%" height="100%" fill="#fef2f2"/>
        <text x="50%" y="50%" text-anchor="middle" fill="#991b1b" font-size="10" font-family="sans-serif">Error generating preview</text>
    </svg>';
}
