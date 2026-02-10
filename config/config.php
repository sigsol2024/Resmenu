<?php
/**
 * Application Configuration
 */

// Site URL - Dynamically detect protocol and domain
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
// Get base path - config.php is in root, so we need to find root from any subdirectory
$scriptPath = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
$scriptDir = dirname($scriptPath);
// If script is in /admin, /manager, /api, or /qr, go up one level
if ($scriptDir === '/admin' || $scriptDir === '/manager' || $scriptDir === '/api' || $scriptDir === '/qr' ||
    strpos($scriptDir, '/admin/') === 0 || strpos($scriptDir, '/manager/') === 0 || strpos($scriptDir, '/api/') === 0 || strpos($scriptDir, '/qr/') === 0) {
    $basePath = dirname($scriptDir);
} else {
    $basePath = $scriptDir;
}
// Normalize: root should be empty string
$basePath = ($basePath === '/' || $basePath === '\\' || $basePath === '.') ? '' : $basePath;
define('SITE_URL', $protocol . $host . $basePath);

// Paths
define('BASE_PATH', dirname(__DIR__));
define('UPLOAD_PATH', BASE_PATH . '/uploads');
define('UPLOAD_URL', SITE_URL . '/uploads');

// Session configuration
define('SESSION_LIFETIME', 3600 * 24); // 24 hours

// Security
define('PASSWORD_MIN_LENGTH', 8);

// File upload settings
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);

// Timezone
date_default_timezone_set('UTC');

// Error reporting (set to 0 in production)
// IMPORTANT: Set these to 0 in production!
error_reporting(E_ALL);
ini_set('display_errors', 0); // Changed to 0 for security
ini_set('log_errors', 1);
ini_set('error_log', BASE_PATH . '/logs/php_errors.log');

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'sigsolmenu_resmenu');
define('DB_USER', 'sigsolmenu_resmenu');
define('DB_PASS', 'Secretpass0931//');
define('DB_CHARSET', 'utf8mb4');

/**
 * Get database connection
 * @return PDO|null
 */
function getDBConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            return null;
        }
    }
    
    return $pdo;
}
