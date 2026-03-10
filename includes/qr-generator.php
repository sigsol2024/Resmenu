<?php
/**
 * QR Code Generation Functions
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/qr-template-helper.php';

// Ensure SITE_URL is defined to avoid fatal errors when generating QR URLs
if (!defined('SITE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptPath = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
    $scriptDir = dirname(dirname($scriptPath));
    $basePath = ($scriptDir === '/' || $scriptDir === '\\' || $scriptDir === '.') ? '' : $scriptDir;
    define('SITE_URL', $protocol . $host . $basePath);
}

// Check if QR Code library is available
$qrCodeAvailable = false;
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
    $qrCodeAvailable = class_exists('Endroid\QrCode\QrCode');
}

/**
 * Get restaurant QR code settings
 * @param int $restaurantId
 * @return array|null
 */
function getRestaurantQRCodeSettings($restaurantId) {
    $pdo = getDBConnection();
    if (!$pdo) return null;
    
    $stmt = $pdo->prepare("
        SELECT rqc.*, qt.name as template_name, qt.has_text, qt.config_json as template_config_json
        FROM restaurant_qr_codes rqc
        LEFT JOIN qr_templates qt ON rqc.qr_template_id = qt.id
        WHERE rqc.restaurant_id = ? AND rqc.is_active = 1
    ");
    $stmt->execute([$restaurantId]);
    $result = $stmt->fetch();
    
    if ($result && !empty($result['template_config_json'])) {
        // Parse template config if present
        $result['template_config'] = is_string($result['template_config_json']) 
            ? json_decode($result['template_config_json'], true)
            : $result['template_config_json'];
    }
    
    return $result;
}

/**
 * Get restaurant by ID
 * @param int $restaurantId
 * @return array|null
 */
function getRestaurantById($restaurantId) {
    $pdo = getDBConnection();
    if (!$pdo) return null;
    
    $stmt = $pdo->prepare("SELECT * FROM restaurants WHERE id = ?");
    $stmt->execute([$restaurantId]);
    return $stmt->fetch();
}

/**
 * Generate QR code URL for restaurant
 * @param string $restaurantSlug
 * @return string
 */
function getRestaurantQRCodeURL($restaurantSlug) {
    return SITE_URL . '/qr/' . urlencode($restaurantSlug);
}

/**
 * Create default QR code settings
 * @param int $restaurantId
 * @return bool
 */
function createDefaultQRCodeSettings($restaurantId) {
    $pdo = getDBConnection();
    if (!$pdo) return false;
    
    $stmt = $pdo->prepare("
        INSERT INTO restaurant_qr_codes 
        (restaurant_id, qr_template_id, background_color, qr_color, text_content, text_color, text_size, qr_size, margin)
        VALUES (?, 1, '#FFFFFF', '#000000', 'Scan to view menu', '#000000', 16, 300, 20)
        ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP
    ");
    return $stmt->execute([$restaurantId]);
}

/**
 * Apply pattern style to SVG QR code
 * @param string $svgContent SVG content
 * @param string $pattern Pattern type (square, dots, rounded, extra-rounded)
 * @return string Modified SVG
 */
function applyPatternStyle($svgContent, $pattern) {
    if ($pattern === 'square') {
        return $svgContent; // Default, no change needed
    }
    
    // For dots pattern, replace rectangles with circles
    if ($pattern === 'dots') {
        // Find all rect elements (QR blocks) and replace with circles
        $svgContent = preg_replace_callback(
            '/<rect\s+([^>]*?)x="([^"]*?)"\s+y="([^"]*?)"\s+width="([^"]*?)"\s+height="([^"]*?)"([^>]*?)>/i',
            function($matches) {
                $x = floatval($matches[2]) + floatval($matches[4]) / 2;
                $y = floatval($matches[3]) + floatval($matches[5]) / 2;
                $radius = min(floatval($matches[4]), floatval($matches[5])) / 2;
                $attrs = $matches[1] . $matches[6];
                return "<circle cx=\"{$x}\" cy=\"{$y}\" r=\"{$radius}\" {$attrs}>";
            },
            $svgContent
        );
        // Close circles properly
        $svgContent = str_replace('</rect>', '</circle>', $svgContent);
    }
    
    // For rounded patterns, add rx/ry attributes to rectangles
    if ($pattern === 'rounded' || $pattern === 'extra-rounded') {
        $radius = $pattern === 'extra-rounded' ? '8' : '4';
        $svgContent = preg_replace_callback(
            '/<rect\s+([^>]*?)x="([^"]*?)"\s+y="([^"]*?)"\s+width="([^"]*?)"\s+height="([^"]*?)"([^>]*?)>/i',
            function($matches) use ($radius) {
                $attrs = $matches[0];
                if (strpos($attrs, 'rx=') === false) {
                    $attrs = str_replace('>', " rx=\"{$radius}\" ry=\"{$radius}\">", $attrs);
                }
                return $attrs;
            },
            $svgContent
        );
    }
    
    return $svgContent;
}

/**
 * Apply eye shape to SVG QR code
 * @param string $svgContent SVG content
 * @param string $eyeShape Eye shape type (square, rounded, leaf, circle)
 * @return string Modified SVG
 */
function applyEyeShape($svgContent, $eyeShape) {
    if ($eyeShape === 'square') {
        return $svgContent; // Default, no change needed
    }
    
    // QR codes have 3 eye patterns (top-left, top-right, bottom-left)
    // Each eye consists of outer square, middle square, and inner square
    // For rounded eyes, we need to round the corners of these squares
    
    if ($eyeShape === 'rounded') {
        // Add rx/ry to eye squares (larger radius for eyes)
        $svgContent = preg_replace_callback(
            '/<rect\s+([^>]*?)x="([^"]*?)"\s+y="([^"]*?)"\s+width="([^"]*?)"\s+height="([^"]*?)"([^>]*?)(fill="black"|fill="#000000")([^>]*?)>/i',
            function($matches) {
                $width = floatval($matches[4]);
                $height = floatval($matches[5]);
                // Only round if it's a large square (likely an eye)
                if ($width > 20 && $height > 20) {
                    $radius = min($width, $height) * 0.2; // 20% radius
                    $attrs = $matches[0];
                    if (strpos($attrs, 'rx=') === false) {
                        $attrs = str_replace('>', " rx=\"{$radius}\" ry=\"{$radius}\">", $attrs);
                    }
                    return $attrs;
                }
                return $matches[0];
            },
            $svgContent
        );
    }
    
    // For circle and leaf shapes, more complex SVG path manipulation would be needed
    // For MVP, we'll keep it simple with rounded corners
    
    return $svgContent;
}

/**
 * Apply frame to SVG QR code
 * @param string $svgContent SVG content
 * @param array $frameConfig Frame configuration
 * @return string Modified SVG
 */
function applyFrame($svgContent, $frameConfig) {
    if (empty($frameConfig) || $frameConfig['type'] === 'none') {
        return $svgContent;
    }
    
    // Extract SVG dimensions
    preg_match('/viewBox="([^"]*)"/', $svgContent, $viewBoxMatch);
    preg_match('/width="([^"]*)"/', $svgContent, $widthMatch);
    preg_match('/height="([^"]*)"/', $svgContent, $heightMatch);
    
    $width = isset($widthMatch[1]) ? floatval($widthMatch[1]) : 300;
    $height = isset($heightMatch[1]) ? floatval($heightMatch[1]) : 300;
    $viewBox = isset($viewBoxMatch[1]) ? $viewBoxMatch[1] : "0 0 {$width} {$height}";
    
    $frameType = $frameConfig['type'];
    $frameColor = $frameConfig['color'] ?? '#000000';
    $frameText = $frameConfig['text'] ?? '';
    $padding = 40; // Frame padding
    
    $frameSvg = '';
    
    switch ($frameType) {
        case 'square':
            $frameSvg = "<rect x=\"0\" y=\"0\" width=\"{$width}\" height=\"{$height}\" fill=\"none\" stroke=\"{$frameColor}\" stroke-width=\"4\"/>";
            break;
        case 'rounded':
            $radius = 20;
            $frameSvg = "<rect x=\"0\" y=\"0\" width=\"{$width}\" height=\"{$height}\" rx=\"{$radius}\" ry=\"{$radius}\" fill=\"none\" stroke=\"{$frameColor}\" stroke-width=\"4\"/>";
            break;
        case 'circle':
            $centerX = $width / 2;
            $centerY = $height / 2;
            $radius = min($width, $height) / 2 - 2;
            $frameSvg = "<circle cx=\"{$centerX}\" cy=\"{$centerY}\" r=\"{$radius}\" fill=\"none\" stroke=\"{$frameColor}\" stroke-width=\"4\"/>";
            break;
        case 'badge':
            // Badge shape (rounded rectangle with notch)
            $radius = 15;
            $frameSvg = "<rect x=\"0\" y=\"0\" width=\"{$width}\" height=\"{$height}\" rx=\"{$radius}\" ry=\"{$radius}\" fill=\"none\" stroke=\"{$frameColor}\" stroke-width=\"4\"/>";
            // Add badge notch at top
            $notchWidth = 60;
            $notchHeight = 20;
            $notchX = ($width - $notchWidth) / 2;
            $frameSvg .= "<rect x=\"{$notchX}\" y=\"-2\" width=\"{$notchWidth}\" height=\"{$notchHeight}\" rx=\"10\" ry=\"10\" fill=\"white\" stroke=\"{$frameColor}\" stroke-width=\"4\"/>";
            break;
    }
    
    // Note: Frame text is handled separately outside the QR code SVG
    // We only add the frame border here, not the text
    
    // Insert frame before closing </svg> tag
    $svgContent = str_replace('</svg>', $frameSvg . '</svg>', $svgContent);
    
    return $svgContent;
}

/**
 * Add frame text to SVG (outside QR code)
 * @param string $svgContent SVG content
 * @param array $frameConfig Frame configuration with text
 * @param int $qrSize QR code size
 * @return string Modified SVG
 */
function addFrameTextToSVG($svgContent, $frameConfig, $qrSize) {
    if (empty($frameConfig['text'])) {
        return $svgContent;
    }
    
    $text = $frameConfig['text'];
    $textColor = $frameConfig['text_color'] ?? '#000000';
    $textSize = $frameConfig['text_size'] ?? 14;
    $bgEnabled = $frameConfig['bg_enabled'] ?? false;
    $bgColor = $frameConfig['bg_color'] ?? '#FFFFFF';
    
    // Extract SVG dimensions
    preg_match('/width="([^"]*)"/', $svgContent, $widthMatch);
    $width = isset($widthMatch[1]) ? floatval($widthMatch[1]) : $qrSize;
    
    // Calculate text area height
    $textHeight = $textSize + 16; // Text size + padding
    
    // Create wrapper SVG with text below
    $newSvg = '<svg width="' . $width . '" height="' . ($width + ($bgEnabled ? $textHeight + 8 : $textHeight)) . '" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' . $width . ' ' . ($width + ($bgEnabled ? $textHeight + 8 : $textHeight)) . '">';
    
    // Add background for text if enabled
    if ($bgEnabled) {
        $textBgY = $width + 4;
        $newSvg .= '<rect x="0" y="' . $textBgY . '" width="' . $width . '" height="' . ($textHeight + 4) . '" fill="' . htmlspecialchars($bgColor) . '" rx="4"/>';
    }
    
    // Add original QR code SVG (remove outer svg tags)
    $qrSvgContent = preg_replace('/<svg[^>]*>/', '', $svgContent);
    $qrSvgContent = preg_replace('/<\/svg>/', '', $qrSvgContent);
    $newSvg .= $qrSvgContent;
    
    // Add text below QR code
    $textY = $width + ($bgEnabled ? $textHeight - 4 : $textSize + 4);
    $newSvg .= '<text x="' . ($width / 2) . '" y="' . $textY . '" text-anchor="middle" fill="' . htmlspecialchars($textColor) . '" font-family="Arial, sans-serif" font-size="' . $textSize . '" font-weight="600">' . htmlspecialchars($text) . '</text>';
    
    $newSvg .= '</svg>';
    
    return $newSvg;
}

/**
 * Find a TrueType font file for text rendering
 * @return string|null Path to font file or null if not found
 */
function findTTFFont() {
    // Common font paths to check
    $fontPaths = [
        // Windows
        'C:/Windows/Fonts/arial.ttf',
        'C:/Windows/Fonts/arialbd.ttf',
        'C:/Windows/Fonts/calibri.ttf',
        'C:/Windows/Fonts/segoeui.ttf',
        // Linux
        '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
        '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
        '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf',
        '/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf',
        '/usr/share/fonts/truetype/freefont/FreeSansBold.ttf',
        '/usr/share/fonts/TTF/DejaVuSans-Bold.ttf',
        '/usr/share/fonts/dejavu/DejaVuSans-Bold.ttf',
        // macOS
        '/System/Library/Fonts/Helvetica.ttc',
        '/Library/Fonts/Arial.ttf',
        '/System/Library/Fonts/SFNSDisplay.ttf',
    ];
    
    foreach ($fontPaths as $path) {
        if (file_exists($path) && is_readable($path)) {
            return $path;
        }
    }
    
    return null;
}

/**
 * Apply frame border to image using GD
 * Used when Imagick is not available
 * @param string $imageData Binary image data
 * @param array $frameConfig Frame configuration
 * @param string $format Image format
 * @return string Modified image data
 */
function applyFrameToImage($imageData, $frameConfig, $format) {
    if (empty($frameConfig) || empty($frameConfig['type']) || $frameConfig['type'] === 'none') {
        return $imageData;
    }
    
    $image = @imagecreatefromstring($imageData);
    if (!$image) {
        return $imageData;
    }
    
    $width = imagesx($image);
    $height = imagesy($image);
    $frameColor = $frameConfig['color'] ?? '#000000';
    $frameType = $frameConfig['type'];
    
    // Parse frame color
    $frameRgb = sscanf($frameColor, '#%02x%02x%02x');
    if (!$frameRgb || count($frameRgb) < 3) {
        $frameRgb = [0, 0, 0]; // Default to black
    }
    
    $borderWidth = max(3, (int)($width / 75)); // Border width scales with size
    $padding = $borderWidth * 4; // Padding between QR and frame
    
    // Create new larger image with padding for frame
    $newWidth = $width + ($padding * 2);
    $newHeight = $height + ($padding * 2);
    $newImage = imagecreatetruecolor($newWidth, $newHeight);
    
    // Enable anti-aliasing
    imagealphablending($newImage, true);
    imagesavealpha($newImage, true);
    imageantialias($newImage, true);
    
    // Fill with white background
    $white = imagecolorallocate($newImage, 255, 255, 255);
    imagefill($newImage, 0, 0, $white);
    
    // Copy QR code to center
    imagecopy($newImage, $image, $padding, $padding, 0, 0, $width, $height);
    
    // Allocate frame color
    $colorRes = imagecolorallocate($newImage, $frameRgb[0], $frameRgb[1], $frameRgb[2]);
    
    // Draw frame based on type
    switch ($frameType) {
        case 'square':
            // Draw rectangle border
            imagesetthickness($newImage, $borderWidth);
            imagerectangle($newImage, $borderWidth/2, $borderWidth/2, $newWidth - $borderWidth/2 - 1, $newHeight - $borderWidth/2 - 1, $colorRes);
            break;
            
        case 'rounded':
            // Draw rounded rectangle border
            $radius = (int)($newWidth / 15);
            drawRoundedRectangle($newImage, $borderWidth/2, $borderWidth/2, $newWidth - $borderWidth/2 - 1, $newHeight - $borderWidth/2 - 1, $radius, $colorRes, $borderWidth);
            break;
            
        case 'circle':
            // Draw circle border (ellipse for non-square)
            imagesetthickness($newImage, $borderWidth);
            imageellipse($newImage, $newWidth/2, $newHeight/2, $newWidth - $borderWidth, $newHeight - $borderWidth, $colorRes);
            break;
            
        case 'badge':
            // Draw badge shape (rounded rect with notch at top)
            $radius = (int)($newWidth / 20);
            drawRoundedRectangle($newImage, $borderWidth/2, $borderWidth/2, $newWidth - $borderWidth/2 - 1, $newHeight - $borderWidth/2 - 1, $radius, $colorRes, $borderWidth);
            
            // Draw notch at top
            $notchWidth = (int)($newWidth * 0.2);
            $notchHeight = (int)($newHeight * 0.06);
            $notchX = ($newWidth - $notchWidth) / 2;
            $notchY = -$notchHeight / 2;
            
            // Cover border line with white where notch will be
            imagefilledrectangle($newImage, (int)$notchX + 5, 0, (int)($notchX + $notchWidth - 5), $borderWidth + 2, $white);
            
            // Draw notch rectangle
            drawRoundedRectangle($newImage, (int)$notchX, (int)$notchY, (int)($notchX + $notchWidth), (int)($notchY + $notchHeight * 2), 8, $colorRes, $borderWidth);
            break;
    }
    
    // Output
    ob_start();
    if ($format === 'jpeg' || $format === 'jpg') {
        imagejpeg($newImage, null, 95);
    } else {
        imagepng($newImage, null, 6);
    }
    $newImageData = ob_get_clean();
    
    imagedestroy($newImage);
    imagedestroy($image);
    
    return $newImageData;
}

/**
 * Draw a rounded rectangle (border only, not filled)
 * @param resource $image GD image resource
 * @param int $x1 Top-left X
 * @param int $y1 Top-left Y
 * @param int $x2 Bottom-right X
 * @param int $y2 Bottom-right Y
 * @param int $radius Corner radius
 * @param int $color GD color resource
 * @param int $thickness Line thickness
 */
function drawRoundedRectangle($image, $x1, $y1, $x2, $y2, $radius, $color, $thickness) {
    // Ensure radius isn't too large
    $radius = min($radius, abs($x2 - $x1) / 2, abs($y2 - $y1) / 2);
    
    imagesetthickness($image, $thickness);
    
    // Draw 4 corners (arcs)
    imagearc($image, $x1 + $radius, $y1 + $radius, $radius * 2, $radius * 2, 180, 270, $color);
    imagearc($image, $x2 - $radius, $y1 + $radius, $radius * 2, $radius * 2, 270, 360, $color);
    imagearc($image, $x2 - $radius, $y2 - $radius, $radius * 2, $radius * 2, 0, 90, $color);
    imagearc($image, $x1 + $radius, $y2 - $radius, $radius * 2, $radius * 2, 90, 180, $color);
    
    // Draw 4 sides
    imageline($image, $x1 + $radius, $y1, $x2 - $radius, $y1, $color); // Top
    imageline($image, $x1 + $radius, $y2, $x2 - $radius, $y2, $color); // Bottom
    imageline($image, $x1, $y1 + $radius, $x1, $y2 - $radius, $color); // Left
    imageline($image, $x2, $y1 + $radius, $x2, $y2 - $radius, $color); // Right
}

/**
 * Apply logo to image using GD
 * Used when Imagick is not available
 * @param string $imageData Binary image data
 * @param array $logoConfig Logo configuration
 * @param array $restaurant Restaurant data
 * @param string $format Image format
 * @return string Modified image data
 */
function applyLogoToImage($imageData, $logoConfig, $restaurant, $format) {
    if (empty($logoConfig['enabled']) || empty($restaurant['logo'])) {
        return $imageData;
    }
    
    $logoPath = UPLOAD_PATH . '/logos/' . $restaurant['logo'];
    if (!file_exists($logoPath)) {
        return $imageData;
    }
    
    $image = @imagecreatefromstring($imageData);
    if (!$image) {
        return $imageData;
    }
    
    // Load logo image
    $logoImageInfo = @getimagesize($logoPath);
    if (!$logoImageInfo) {
        imagedestroy($image);
        return $imageData;
    }
    
    $logoImage = null;
    switch ($logoImageInfo['mime']) {
        case 'image/png':
            $logoImage = @imagecreatefrompng($logoPath);
            break;
        case 'image/jpeg':
            $logoImage = @imagecreatefromjpeg($logoPath);
            break;
        case 'image/gif':
            $logoImage = @imagecreatefromgif($logoPath);
            break;
        case 'image/webp':
            if (function_exists('imagecreatefromwebp')) {
                $logoImage = @imagecreatefromwebp($logoPath);
            }
            break;
    }
    
    if (!$logoImage) {
        imagedestroy($image);
        return $imageData;
    }
    
    $width = imagesx($image);
    $height = imagesy($image);
    $logoWidth = imagesx($logoImage);
    $logoHeight = imagesy($logoImage);
    
    // Calculate logo size (typically 20% of QR code)
    $logoSizePercent = $logoConfig['size'] ?? 0.2;
    $targetLogoSize = (int)(min($width, $height) * $logoSizePercent);
    
    // Scale logo while preserving aspect ratio
    $ratio = min($targetLogoSize / $logoWidth, $targetLogoSize / $logoHeight);
    $newLogoWidth = (int)($logoWidth * $ratio);
    $newLogoHeight = (int)($logoHeight * $ratio);
    
    // Center position
    $destX = ($width - $newLogoWidth) / 2;
    $destY = ($height - $newLogoHeight) / 2;
    
    // Create white background behind logo (for contrast)
    $padding = 4;
    $white = imagecolorallocate($image, 255, 255, 255);
    imagefilledrectangle(
        $image, 
        (int)($destX - $padding), 
        (int)($destY - $padding), 
        (int)($destX + $newLogoWidth + $padding), 
        (int)($destY + $newLogoHeight + $padding), 
        $white
    );
    
    // Resize and copy logo to center
    imagecopyresampled(
        $image, $logoImage,
        (int)$destX, (int)$destY, 0, 0,
        $newLogoWidth, $newLogoHeight,
        $logoWidth, $logoHeight
    );
    
    // Output
    ob_start();
    if ($format === 'jpeg' || $format === 'jpg') {
        imagejpeg($image, null, 95);
    } else {
        imagepng($image, null, 6);
    }
    $newImageData = ob_get_clean();
    
    imagedestroy($image);
    imagedestroy($logoImage);
    
    return $newImageData;
}

/**
 * Add frame text to image (outside QR code)
 * Uses TrueType fonts for smooth text rendering
 * @param string $imageData Binary image data
 * @param array $frameConfig Frame configuration with text
 * @param string $format Image format
 * @return string Modified image data
 */
function addFrameTextToImage($imageData, $frameConfig, $format) {
    if (empty($frameConfig['text']) || !function_exists('imagecreatefromstring')) {
        return $imageData;
    }
    
    $text = $frameConfig['text'];
    $textColor = $frameConfig['text_color'] ?? '#FFFFFF';
    $textSize = $frameConfig['text_size'] ?? 14;
    $bgEnabled = $frameConfig['bg_enabled'] ?? true;
    $bgColor = $frameConfig['bg_color'] ?? '#000000';
    
    $image = @imagecreatefromstring($imageData);
    if (!$image) {
        return $imageData;
    }
    
    $width = imagesx($image);
    $height = imagesy($image);
    
    // Scale text size based on QR code size (for better proportions)
    $scaledTextSize = max(12, min(24, $width / 15));
    $textBarHeight = $scaledTextSize + 20; // Text bar height
    
    // Create new image with space for text bar
    $newHeight = $height + $textBarHeight;
    $newImage = imagecreatetruecolor($width, $newHeight);
    
    // Enable alpha blending for smoother rendering
    imagealphablending($newImage, true);
    imagesavealpha($newImage, true);
    
    // Fill background with white
    $white = imagecolorallocate($newImage, 255, 255, 255);
    imagefill($newImage, 0, 0, $white);
    
    // Copy QR code to top
    imagecopy($newImage, $image, 0, 0, 0, 0, $width, $height);
    
    // Draw text background bar
    $bgRgb = sscanf($bgColor, '#%02x%02x%02x');
    if (!$bgRgb || count($bgRgb) < 3) {
        $bgRgb = [0, 0, 0]; // Default to black
    }
    $bgColorRes = imagecolorallocate($newImage, $bgRgb[0], $bgRgb[1], $bgRgb[2]);
    imagefilledrectangle($newImage, 0, $height, $width, $newHeight, $bgColorRes);
    
    // Prepare text color
    $textRgb = sscanf($textColor, '#%02x%02x%02x');
    if (!$textRgb || count($textRgb) < 3) {
        $textRgb = [255, 255, 255]; // Default to white
    }
    $textColorRes = imagecolorallocate($newImage, $textRgb[0], $textRgb[1], $textRgb[2]);
    
    // Try to use TrueType font for smooth text
    $fontPath = findTTFFont();
    
    if ($fontPath && function_exists('imagettftext')) {
        // Use TrueType font (smooth, anti-aliased text)
        $bbox = imagettfbbox($scaledTextSize, 0, $fontPath, $text);
        $textWidth = abs($bbox[4] - $bbox[0]);
        $textHeight = abs($bbox[5] - $bbox[1]);
        
    $x = ($width - $textWidth) / 2;
        $y = $height + ($textBarHeight / 2) + ($textHeight / 2);
    
        imagettftext($newImage, $scaledTextSize, 0, (int)$x, (int)$y, $textColorRes, $fontPath, $text);
    } else {
        // Fallback to built-in font (less smooth but works everywhere)
        $font = 5; // Largest built-in font
        $charWidth = imagefontwidth($font);
        $charHeight = imagefontheight($font);
        $textWidth = $charWidth * strlen($text);
        
        $x = ($width - $textWidth) / 2;
        $y = $height + ($textBarHeight / 2) - ($charHeight / 2);
        
        imagestring($newImage, $font, (int)$x, (int)$y, $text, $textColorRes);
    }
    
    // Output
    ob_start();
    if ($format === 'jpeg' || $format === 'jpg') {
        imagejpeg($newImage, null, 95);
    } else {
        imagepng($newImage, null, 6);
    }
    $newImageData = ob_get_clean();
    
    imagedestroy($newImage);
    imagedestroy($image);
    
    return $newImageData;
}

/**
 * Apply logo to SVG QR code
 * @param string $svgContent SVG content
 * @param array $logoConfig Logo configuration
 * @param array $restaurant Restaurant data
 * @return string Modified SVG
 */
function applyLogo($svgContent, $logoConfig, $restaurant) {
    if (empty($logoConfig['enabled']) || empty($restaurant['logo'])) {
        return $svgContent;
    }
    
    $logoPath = UPLOAD_PATH . '/logos/' . $restaurant['logo'];
    if (!file_exists($logoPath)) {
        return $svgContent;
    }
    
    // Get SVG dimensions
    preg_match('/width="([^"]*)"/', $svgContent, $widthMatch);
    preg_match('/height="([^"]*)"/', $svgContent, $heightMatch);
    
    $width = isset($widthMatch[1]) ? floatval($widthMatch[1]) : 300;
    $height = isset($heightMatch[1]) ? floatval($heightMatch[1]) : 300;
    
    // Calculate logo size
    $logoSizePercent = $logoConfig['size'] ?? 0.2;
    $logoSize = min($width, $height) * $logoSizePercent;
    
    // Center position
    $centerX = $width / 2;
    $centerY = $height / 2;
    
    // Convert logo to base64
    $logoData = file_get_contents($logoPath);
    $logoBase64 = base64_encode($logoData);
    $logoMime = mime_content_type($logoPath) ?: 'image/png';
    
    // Create image element
    $logoX = $centerX - ($logoSize / 2);
    $logoY = $centerY - ($logoSize / 2);
    
    $logoSvg = "<image x=\"{$logoX}\" y=\"{$logoY}\" width=\"{$logoSize}\" height=\"{$logoSize}\" href=\"data:{$logoMime};base64,{$logoBase64}\"/>";
    
    // Insert logo before closing </svg> tag
    $svgContent = str_replace('</svg>', $logoSvg . '</svg>', $svgContent);
    
    return $svgContent;
}

/**
 * Convert SVG to PNG/JPEG using Imagick
 * @param string $svgContent SVG content
 * @param string $format Target format (png, jpeg)
 * @return string Image data or SVG if conversion fails
 */
function convertSVGToImage($svgContent, $format = 'png') {
    // Require Imagick for conversion
    if (!class_exists('Imagick')) {
        error_log("Imagick not available - cannot convert SVG to " . $format);
        return $svgContent; // Return SVG as fallback
    }
    
    try {
        $imagick = new Imagick();
        $imagick->setBackgroundColor(new ImagickPixel('white'));
        $imagick->readImageBlob($svgContent);
        
        // Set format
        if ($format === 'jpeg' || $format === 'jpg') {
            $imagick->setImageFormat('jpeg');
            $imagick->setImageCompressionQuality(90);
        } else {
            $imagick->setImageFormat('png');
        }
        
        return $imagick->getImageBlob();
    } catch (Exception $e) {
        error_log("Imagick SVG conversion error: " . $e->getMessage());
        return $svgContent; // Return SVG as fallback
    }
}

/**
 * Generate QR code image using library or API fallback
 * Now uses template configs and SVG manipulation
 * @param int $restaurantId
 * @param string $format
 * @param int|null $size
 * @return object|string|null
 */
function generateQRCodeImage($restaurantId, $format = 'png', $size = null) {
    global $qrCodeAvailable;
    
    if (!$qrCodeAvailable) {
        // Fallback: Generate simple QR code using API
        return generateQRCodeViaAPI($restaurantId, $format, $size);
    }
    
    // Get final config (cached or generated from template + overrides)
    $finalConfig = getFinalQRConfig($restaurantId);
    
    // If no template is selected, return null (no QR code available)
    if (!$finalConfig) {
        return null;
    }
    
    // Fallback to old settings if config not available
    $settings = getRestaurantQRCodeSettings($restaurantId);
    if (!$settings) {
        createDefaultQRCodeSettings($restaurantId);
        $settings = getRestaurantQRCodeSettings($restaurantId);
    }
    
    $restaurant = getRestaurantById($restaurantId);
    if (!$restaurant) return null;
    
    $qrCodeURL = getRestaurantQRCodeURL($restaurant['slug']);
    $qrSize = $size ?? ($settings['qr_size'] ?? 300);
    
    try {
        // Use colors from final config if available, otherwise from settings
        $foregroundColor = $finalConfig['colors']['foreground'] ?? $settings['qr_color'] ?? '#000000';
        $backgroundColor = $finalConfig['colors']['background'] ?? $settings['background_color'] ?? '#FFFFFF';
        
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
        
        $qrCode = \Endroid\QrCode\QrCode::create($qrCodeURL)
            ->setEncoding(new \Endroid\QrCode\Encoding\Encoding('UTF-8'))
            ->setErrorCorrectionLevel(\Endroid\QrCode\ErrorCorrectionLevel::Medium)
            ->setSize($qrSize)
            ->setMargin($settings['margin'] ?? 20)
            ->setForegroundColor($qrColor)
            ->setBackgroundColor($bgColor)
            ->setRoundBlockSizeMode(\Endroid\QrCode\RoundBlockSizeMode::Margin);
        
        // Always generate as SVG first for manipulation
        $svgWriter = new \Endroid\QrCode\Writer\SvgWriter();
        $result = $svgWriter->write($qrCode);
        $svgContent = $result->getString();
        
        // Apply template config customizations
        if ($finalConfig) {
            // Apply pattern
            if (isset($finalConfig['pattern'])) {
                $svgContent = applyPatternStyle($svgContent, $finalConfig['pattern']);
            }
            
            // Apply eye shape
            if (isset($finalConfig['eyes'])) {
                $svgContent = applyEyeShape($svgContent, $finalConfig['eyes']);
            }
            
            // Apply frame
            if (isset($finalConfig['frame'])) {
                $svgContent = applyFrame($svgContent, $finalConfig['frame']);
            }
            
            // Apply logo
            if (isset($finalConfig['logo'])) {
                $svgContent = applyLogo($svgContent, $finalConfig['logo'], $restaurant);
            }
        }
        
        // Handle frame text outside QR code (if frame has text)
        $frameTextData = null;
        if ($finalConfig && isset($finalConfig['frame']) && !empty($finalConfig['frame']['text'])) {
            $frameTextData = $finalConfig['frame'];
        }
        
        // Convert to requested format
        if ($format === 'svg') {
            // For SVG, we can add frame text as part of the SVG
            if ($frameTextData && $frameTextData['text']) {
                $svgContent = addFrameTextToSVG($svgContent, $frameTextData, $qrSize);
            }
            return $svgContent;
        } else {
            // Try to convert SVG to PNG/JPEG using Imagick (preserves custom patterns/eyes)
            if (class_exists('Imagick')) {
            $imageData = convertSVGToImage($svgContent, $format);
            
                // If conversion succeeded
                if ($imageData !== $svgContent) {
                    // For PNG/JPEG, add frame text as image overlay if needed
                    if ($frameTextData && $frameTextData['text'] && function_exists('imagecreatefromstring')) {
                        $imageData = addFrameTextToImage($imageData, $frameTextData, $format);
                    }
                    return $imageData;
                }
            }
            
            // Fallback: Use endroid's PNG writer directly (GD-based)
            // Apply frame borders using GD functions
            error_log("Using GD fallback for PNG/JPEG generation (Imagick not available)");
            
            try {
                $pngWriter = new \Endroid\QrCode\Writer\PngWriter();
                $result = $pngWriter->write($qrCode);
                $imageData = $result->getString();
                
                // Apply frame border styling using GD
                if ($finalConfig && isset($finalConfig['frame']) && function_exists('imagecreatefromstring')) {
                    $imageData = applyFrameToImage($imageData, $finalConfig['frame'], $format);
                }
                
                // Apply logo using GD if configured
                if ($finalConfig && isset($finalConfig['logo']) && !empty($finalConfig['logo']['enabled']) && function_exists('imagecreatefromstring')) {
                    $imageData = applyLogoToImage($imageData, $finalConfig['logo'], $restaurant, $format);
            }
            
            // For PNG/JPEG, add frame text as image overlay if needed
            if ($frameTextData && $frameTextData['text'] && function_exists('imagecreatefromstring')) {
                $imageData = addFrameTextToImage($imageData, $frameTextData, $format);
            }
                
                // Convert to JPEG if requested
                if (($format === 'jpeg' || $format === 'jpg') && function_exists('imagecreatefromstring')) {
                    $image = @imagecreatefromstring($imageData);
                    if ($image) {
                        ob_start();
                        imagejpeg($image, null, 90);
                        $imageData = ob_get_clean();
                        imagedestroy($image);
                    }
                }
            
            return $imageData;
            } catch (Exception $e) {
                error_log("GD PNG writer fallback failed: " . $e->getMessage());
                // Last resort: use external API
                return generateQRCodeViaAPI($restaurantId, $format, $size);
            }
        }
        
    } catch (Exception $e) {
        error_log("QR Code generation error: " . $e->getMessage());
        return generateQRCodeViaAPI($restaurantId, $format, $size);
    }
}

/**
 * Generate QR code using online API (fallback)
 * @param int $restaurantId
 * @param string $format
 * @param int $size
 * @return string|null
 */
function generateQRCodeViaAPI($restaurantId, $format = 'png', $size = 300) {
    $restaurant = getRestaurantById($restaurantId);
    if (!$restaurant) return null;
    
    $qrCodeURL = getRestaurantQRCodeURL($restaurant['slug']);
    $size = $size ?? 300;
    
    // Using qrcode.tec-it.com API (free, no API key needed)
    $apiURL = "https://qrcode.tec-it.com/API/QRCode?data=" . urlencode($qrCodeURL) . "&size=" . $size;
    
    $imageData = @file_get_contents($apiURL);
    return $imageData ? $imageData : null;
}

/**
 * Generate QR code with text (for templates with text)
 * @param int $restaurantId
 * @param string $format
 * @return string|null
 */
function generateQRCodeWithText($restaurantId, $format = 'png') {
    $settings = getRestaurantQRCodeSettings($restaurantId);
    if (!$settings || !$settings['has_text']) {
        $result = generateQRCodeImage($restaurantId, 'png');
        return is_object($result) ? $result->getString() : $result;
    }
    
    $qrImageData = generateQRCodeImage($restaurantId, 'png');
    if (!$qrImageData) return null;
    
    // Handle both object and string returns
    if (is_object($qrImageData)) {
        $qrImageData = $qrImageData->getString();
    }
    
    // Create image with text
    $qrResource = @imagecreatefromstring($qrImageData);
    if (!$qrResource) return $qrImageData; // Return original if GD not available
    
    $width = imagesx($qrResource);
    $height = imagesy($qrResource);
    $textHeight = 100; // Space for text
    $newHeight = $height + $textHeight;
    
    $image = imagecreatetruecolor($width, $newHeight);
    $bgColor = imagecolorallocate($image, 
        hexdec(substr($settings['background_color'], 1, 2)),
        hexdec(substr($settings['background_color'], 3, 2)),
        hexdec(substr($settings['background_color'], 5, 2))
    );
    imagefill($image, 0, 0, $bgColor);
    
    // Copy QR code
    imagecopy($image, $qrResource, 0, 0, 0, 0, $width, $height);
    
    // Add text
    $textColor = imagecolorallocate($image,
        hexdec(substr($settings['text_color'], 1, 2)),
        hexdec(substr($settings['text_color'], 3, 2)),
        hexdec(substr($settings['text_color'], 5, 2))
    );
    
    $text = $settings['text_content'] ?? 'Scan to view menu';
    $fontSize = $settings['text_size'];
    
    // Use built-in font if TTF not available
    $font = 5; // Built-in font
    $textWidth = imagefontwidth($font) * strlen($text);
    $x = ($width - $textWidth) / 2;
    $y = $height + ($textHeight / 2);
    
    imagestring($image, $font, $x, $y, $text, $textColor);
    
    // Output
    ob_start();
    imagepng($image);
    $imageData = ob_get_clean();
    imagedestroy($image);
    imagedestroy($qrResource);
    
    return $imageData;
}

/**
 * Generate PDF with QR code
 * @param int $restaurantId
 * @return string|null
 */
function generateQRCodePDF($restaurantId) {
    $restaurant = getRestaurantById($restaurantId);
    if (!$restaurant) return null;
    
    $settings = getRestaurantQRCodeSettings($restaurantId);
    if (!$settings) {
        createDefaultQRCodeSettings($restaurantId);
        $settings = getRestaurantQRCodeSettings($restaurantId);
    }
    
    // Generate QR code image (PNG format)
    $qrImageData = null;
    if ($settings['has_text']) {
        $qrImageData = generateQRCodeWithText($restaurantId, 'png');
    } else {
        $qrResult = generateQRCodeImage($restaurantId, 'png');
        $qrImageData = is_object($qrResult) ? $qrResult->getString() : $qrResult;
    }
    
    if (!$qrImageData) {
        error_log("Failed to generate QR code image for PDF");
        return null;
    }
    
    // Ensure we have PNG data, not SVG
    if (is_string($qrImageData) && strpos($qrImageData, '<svg') !== false) {
        error_log("PDF generation received SVG instead of PNG - Imagick required");
        return null;
    }
    
    // Generate PDF using pure PHP (no external library required)
    try {
        return generateSimplePDF($qrImageData, $restaurant['name']);
    } catch (Exception $e) {
        error_log("PDF generation error: " . $e->getMessage());
                return null;
            }
}

/**
 * Generate a simple PDF with QR code using pure PHP (no external libraries)
 * @param string $pngData PNG image data
 * @param string $title Title text
 * @return string PDF data
 */
function generateSimplePDF($pngData, $title = 'QR Code') {
    // Get PNG dimensions
    $img = @imagecreatefromstring($pngData);
    if (!$img) {
        throw new Exception("Failed to create image from PNG data");
    }
    $imgWidth = imagesx($img);
    $imgHeight = imagesy($img);
    imagedestroy($img);
    
    // PDF dimensions (A4 in points: 595 x 842)
    $pageWidth = 595;
    $pageHeight = 842;
    
    // Scale QR code to fit nicely (about 200 points = ~70mm)
    $qrSize = 200;
    $qrX = ($pageWidth - $qrSize) / 2;
    $qrY = 300; // Position from top
    
    // Build PDF structure
    $objects = [];
    $objectCount = 0;
    
    // Object 1: Catalog
    $objectCount++;
    $objects[$objectCount] = "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
    
    // Object 2: Pages
    $objectCount++;
    $objects[$objectCount] = "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";
    
    // Object 3: Page
    $objectCount++;
    $objects[$objectCount] = "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 $pageWidth $pageHeight] /Contents 4 0 R /Resources << /XObject << /QR 5 0 R >> /Font << /F1 6 0 R >> >> >>\nendobj\n";
    
    // Object 4: Content stream (drawing commands)
    $titleX = $pageWidth / 2;
    $titleY = $pageHeight - 100;
    $subtitleY = $qrY - 50;
    
    // Escape special characters in title for PDF
    $safeTitle = str_replace(['(', ')', '\\'], ['\\(', '\\)', '\\\\'], $title);
    
    $content = "BT\n";
    $content .= "/F1 24 Tf\n";
    $content .= "$titleX $titleY Td\n";
    $content .= "(" . $safeTitle . ") Tj\n";
    $content .= "ET\n";
    $content .= "q\n";
    $content .= "$qrSize 0 0 $qrSize $qrX " . ($pageHeight - $qrY - $qrSize) . " cm\n";
    $content .= "/QR Do\n";
    $content .= "Q\n";
    $content .= "BT\n";
    $content .= "/F1 14 Tf\n";
    $content .= "$titleX $subtitleY Td\n";
    $content .= "(Scan to view menu) Tj\n";
    $content .= "ET\n";
    
    $contentLength = strlen($content);
    $objectCount++;
    $objects[$objectCount] = "4 0 obj\n<< /Length $contentLength >>\nstream\n$content\nendstream\nendobj\n";
    
    // Object 5: Image XObject (PNG)
    $pngBase64 = base64_encode($pngData);
    $pngDecoded = $pngData;
    $pngLength = strlen($pngDecoded);
    
    // For simplicity, we'll embed the PNG as a JPEG (more compatible)
    // Convert PNG to JPEG
    $img = @imagecreatefromstring($pngData);
    if ($img) {
        ob_start();
        imagejpeg($img, null, 95);
        $jpegData = ob_get_clean();
        imagedestroy($img);
        
        $jpegLength = strlen($jpegData);
        $objectCount++;
        $objects[$objectCount] = "5 0 obj\n<< /Type /XObject /Subtype /Image /Width $imgWidth /Height $imgHeight /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length $jpegLength >>\nstream\n" . $jpegData . "\nendstream\nendobj\n";
    } else {
        throw new Exception("Failed to process image for PDF");
    }
    
    // Object 6: Font
    $objectCount++;
    $objects[$objectCount] = "6 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n";
    
    // Build PDF file
    $pdf = "%PDF-1.4\n";
    $offsets = [];
    
    foreach ($objects as $num => $obj) {
        $offsets[$num] = strlen($pdf);
        $pdf .= $obj;
    }
    
    // Cross-reference table
    $xrefOffset = strlen($pdf);
    $pdf .= "xref\n";
    $pdf .= "0 " . ($objectCount + 1) . "\n";
    $pdf .= "0000000000 65535 f \n";
    
    for ($i = 1; $i <= $objectCount; $i++) {
        $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
    }
    
    // Trailer
    $pdf .= "trailer\n";
    $pdf .= "<< /Size " . ($objectCount + 1) . " /Root 1 0 R >>\n";
    $pdf .= "startxref\n";
    $pdf .= "$xrefOffset\n";
    $pdf .= "%%EOF";
    
    return $pdf;
}

/**
 * Save QR code to file
 * @param int $restaurantId
 * @param string $format
 * @return string|null
 */
function saveQRCodeFile($restaurantId, $format = 'png') {
    $restaurant = getRestaurantById($restaurantId);
    if (!$restaurant) return null;
    
    $settings = getRestaurantQRCodeSettings($restaurantId);
    $hasText = $settings && $settings['has_text'];
    
    if ($format === 'pdf') {
        $fileData = generateQRCodePDF($restaurantId);
    } elseif ($hasText) {
        $fileData = generateQRCodeWithText($restaurantId, $format);
    } else {
        $result = generateQRCodeImage($restaurantId, $format);
        $fileData = is_object($result) ? $result->getString() : $result;
    }
    
    if (!$fileData) return null;
    
    $filename = 'qr_' . $restaurant['slug'] . '_' . time() . '.' . $format;
    $filepath = UPLOAD_PATH . '/qr-codes/' . $filename;
    
    if (!is_dir(UPLOAD_PATH . '/qr-codes')) {
        mkdir(UPLOAD_PATH . '/qr-codes', 0755, true);
    }
    
    file_put_contents($filepath, $fileData);
    return $filename;
}

