<?php
/**
 * QR Code Analytics Tracking Functions
 */

require_once __DIR__ . '/functions.php';

/**
 * Track QR code scan
 * @param int $restaurantId
 * @param string|null $ipAddress
 * @param string|null $userAgent
 * @return bool
 */
function trackQRCodeScan($restaurantId, $ipAddress = null, $userAgent = null) {
    $pdo = getDBConnection();
    if (!$pdo) return false;
    
    $ipAddress = $ipAddress ?? $_SERVER['REMOTE_ADDR'] ?? null;
    $userAgent = $userAgent ?? $_SERVER['HTTP_USER_AGENT'] ?? null;
    
    // Parse user agent
    $deviceInfo = parseUserAgent($userAgent);
    
    // Get location from IP (optional - requires GeoIP service)
    $location = getLocationFromIP($ipAddress);
    
    $stmt = $pdo->prepare("
        INSERT INTO qr_code_scans 
        (restaurant_id, ip_address, user_agent, device_type, browser, os, country, city, latitude, longitude)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    return $stmt->execute([
        $restaurantId,
        $ipAddress,
        $userAgent,
        $deviceInfo['device_type'],
        $deviceInfo['browser'],
        $deviceInfo['os'],
        $location['country'] ?? null,
        $location['city'] ?? null,
        $location['latitude'] ?? null,
        $location['longitude'] ?? null
    ]);
}

/**
 * Parse user agent string
 * @param string|null $userAgent
 * @return array
 */
function parseUserAgent($userAgent) {
    if (empty($userAgent)) {
        return ['device_type' => 'Unknown', 'browser' => 'Unknown', 'os' => 'Unknown'];
    }
    
    $deviceType = 'Desktop';
    $browser = 'Unknown';
    $os = 'Unknown';
    
    $ua = strtolower($userAgent);
    
    // Device detection
    if (preg_match('/mobile|android|iphone|ipod/i', $userAgent)) {
        $deviceType = 'Mobile';
    } elseif (preg_match('/tablet|ipad/i', $userAgent)) {
        $deviceType = 'Tablet';
    }
    
    // Browser detection
    if (preg_match('/chrome/i', $userAgent) && !preg_match('/edg/i', $userAgent)) {
        $browser = 'Chrome';
    } elseif (preg_match('/firefox/i', $userAgent)) {
        $browser = 'Firefox';
    } elseif (preg_match('/safari/i', $userAgent) && !preg_match('/chrome/i', $userAgent)) {
        $browser = 'Safari';
    } elseif (preg_match('/edg/i', $userAgent)) {
        $browser = 'Edge';
    } elseif (preg_match('/opera|opr/i', $userAgent)) {
        $browser = 'Opera';
    }
    
    // OS detection
    if (preg_match('/windows/i', $userAgent)) {
        $os = 'Windows';
    } elseif (preg_match('/macintosh|mac os/i', $userAgent)) {
        $os = 'macOS';
    } elseif (preg_match('/linux/i', $userAgent)) {
        $os = 'Linux';
    } elseif (preg_match('/android/i', $userAgent)) {
        $os = 'Android';
    } elseif (preg_match('/ios|iphone|ipad/i', $userAgent)) {
        $os = 'iOS';
    }
    
    return ['device_type' => $deviceType, 'browser' => $browser, 'os' => $os];
}

/**
 * Get location from IP (requires GeoIP service)
 * @param string|null $ipAddress
 * @return array
 */
function getLocationFromIP($ipAddress) {
    // Skip localhost and private IPs
    if (empty($ipAddress) || $ipAddress === '127.0.0.1' || strpos($ipAddress, '192.168.') === 0 || strpos($ipAddress, '10.') === 0) {
        return [];
    }
    
    // Option 1: Use free API (ip-api.com) - 45 requests/minute limit
    try {
        $response = @file_get_contents("http://ip-api.com/json/{$ipAddress}?fields=status,country,city,lat,lon", false, stream_context_create([
            'http' => ['timeout' => 2]
        ]));
        
        if ($response) {
            $data = json_decode($response, true);
            if ($data && isset($data['status']) && $data['status'] === 'success') {
                return [
                    'country' => $data['country'] ?? null,
                    'city' => $data['city'] ?? null,
                    'latitude' => $data['lat'] ?? null,
                    'longitude' => $data['lon'] ?? null
                ];
            }
        }
    } catch (Exception $e) {
        // Silently fail - location is optional
    }
    
    return [];
}

/**
 * Get QR code analytics for restaurant
 * @param int $restaurantId
 * @param string|null $startDate
 * @param string|null $endDate
 * @return array|null
 */
function getQRCodeAnalytics($restaurantId, $startDate = null, $endDate = null) {
    $pdo = getDBConnection();
    if (!$pdo) return null;
    
    $whereClause = "WHERE restaurant_id = ?";
    $params = [$restaurantId];
    
    if ($startDate) {
        $whereClause .= " AND scanned_at >= ?";
        $params[] = $startDate;
    }
    if ($endDate) {
        $whereClause .= " AND scanned_at <= ?";
        $params[] = $endDate . ' 23:59:59';
    }
    
    // Total scans
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM qr_code_scans {$whereClause}");
    $stmt->execute($params);
    $scans = $stmt->fetch()['total'] ?? 0;
    
    // Scans by device
    $stmt = $pdo->prepare("
        SELECT device_type, COUNT(*) as count 
        FROM qr_code_scans {$whereClause}
        GROUP BY device_type
    ");
    $stmt->execute($params);
    $scansByDevice = $stmt->fetchAll();
    
    // Scans by browser
    $stmt = $pdo->prepare("
        SELECT browser, COUNT(*) as count 
        FROM qr_code_scans {$whereClause}
        GROUP BY browser
        ORDER BY count DESC
        LIMIT 10
    ");
    $stmt->execute($params);
    $scansByBrowser = $stmt->fetchAll();
    
    // Scans by location
    $stmt = $pdo->prepare("
        SELECT country, city, COUNT(*) as count 
        FROM qr_code_scans {$whereClause}
        AND country IS NOT NULL
        GROUP BY country, city
        ORDER BY count DESC
        LIMIT 10
    ");
    $stmt->execute($params);
    $scansByLocation = $stmt->fetchAll();
    
    // Scans by date (last 30 days)
    $stmt = $pdo->prepare("
        SELECT DATE(scanned_at) as date, COUNT(*) as count 
        FROM qr_code_scans {$whereClause}
        GROUP BY DATE(scanned_at)
        ORDER BY date DESC
        LIMIT 30
    ");
    $stmt->execute($params);
    $scansByDate = $stmt->fetchAll();
    
    // Recent scans
    $stmt = $pdo->prepare("
        SELECT * FROM qr_code_scans {$whereClause}
        ORDER BY scanned_at DESC
        LIMIT 50
    ");
    $stmt->execute($params);
    $recentScans = $stmt->fetchAll();
    
    return [
        'total_scans' => $scans,
        'scans_by_device' => $scansByDevice,
        'scans_by_browser' => $scansByBrowser,
        'scans_by_location' => $scansByLocation,
        'scans_by_date' => $scansByDate,
        'recent_scans' => $recentScans
    ];
}


