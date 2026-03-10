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

if (!$config || !is_array($config)) {
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

// Normalize config with safe defaults so preview never throws on missing keys
$colors = isset($config['colors']) && is_array($config['colors']) ? $config['colors'] : [];
$foregroundColor = isset($colors['foreground']) && is_string($colors['foreground']) && preg_match('/^#[0-9A-Fa-f]{6}$/', $colors['foreground'])
    ? $colors['foreground'] : '#000000';
$backgroundColor = isset($colors['background']) && is_string($colors['background']) && preg_match('/^#[0-9A-Fa-f]{6}$/', $colors['background'])
    ? $colors['background'] : '#FFFFFF';

$testURL = 'https://menu.example.com/preview';

try {
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

    if (isset($config['pattern']) && is_string($config['pattern']) && function_exists('applyPatternStyle')) {
        $svgContent = applyPatternStyle($svgContent, $config['pattern']);
    }
    if (isset($config['eyes']) && function_exists('applyEyeShape')) {
        $svgContent = applyEyeShape($svgContent, $config['eyes']);
    }
    $frame = isset($config['frame']) && is_array($config['frame']) ? $config['frame'] : [];
    $frameType = isset($frame['type']) ? $frame['type'] : 'none';
    if ($frameType !== 'none' && $frameType !== '' && function_exists('applyFrame')) {
        $svgContent = applyFrame($svgContent, $frame);
    }
    if (!empty($frame['text']) && function_exists('addFrameTextToSVG')) {
        $svgContent = addFrameTextToSVG($svgContent, $frame, $size);
    }

    echo $svgContent;

} catch (Throwable $e) {
    error_log("QR Preview generation error: " . $e->getMessage());
    echo '<svg width="' . $size . '" height="' . $size . '" xmlns="http://www.w3.org/2000/svg">
        <rect width="100%" height="100%" fill="#fef2f2"/>
        <text x="50%" y="50%" text-anchor="middle" fill="#991b1b" font-size="10" font-family="sans-serif">Error generating preview</text>
    </svg>';
}
