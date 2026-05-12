<?php
/**
 * Application Configuration
 * Secrets: set via environment variables or config.local.php (gitignored).
 */

if (file_exists(__DIR__ . '/config.local.php')) {
    require __DIR__ . '/config.local.php';
}

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
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB (general cap; images use IMAGE_* below)
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
// Image size limits: max stored 500KB; reject uploads > 1MB; auto-resize 500KB–1MB to ~500KB
define('IMAGE_MAX_BYTES', 500 * 1024);       // 500KB max stored size
define('IMAGE_UPLOAD_MAX_BYTES', 1024 * 1024); // 1MB max upload; over this = reject

// Timezone
date_default_timezone_set('UTC');

// Email (SMTP) - set MAIL_ENABLED to false to use PHP mail() fallback
define('MAIL_ENABLED', true);
if (!defined('MAIL_FROM_EMAIL')) define('MAIL_FROM_EMAIL', getenv('MAIL_FROM_EMAIL') ?: 'noreply@resmenu.net');
if (!defined('MAIL_FROM_NAME')) define('MAIL_FROM_NAME', getenv('MAIL_FROM_NAME') ?: 'Resmenu');
if (!defined('SMTP_HOST')) define('SMTP_HOST', getenv('SMTP_HOST') ?: '');
if (!defined('SMTP_PORT')) define('SMTP_PORT', getenv('SMTP_PORT') ?: '465');
if (!defined('SMTP_SECURE')) define('SMTP_SECURE', getenv('SMTP_SECURE') ?: 'ssl');
if (!defined('SMTP_USERNAME')) define('SMTP_USERNAME', getenv('SMTP_USERNAME') ?: '');
if (!defined('SMTP_PASSWORD')) define('SMTP_PASSWORD', getenv('SMTP_PASSWORD') ?: '');

// ZeptoMail (API) - primary transactional mail transport when configured
// Set ZEPTOMAIL_SENDMAIL_TOKEN to enable (Agent -> SMTP/API -> Send Mail Token).
if (!defined('ZEPTOMAIL_SENDMAIL_TOKEN')) define('ZEPTOMAIL_SENDMAIL_TOKEN', getenv('ZEPTOMAIL_SENDMAIL_TOKEN') ?: '');
if (!defined('ZEPTOMAIL_URL')) define('ZEPTOMAIL_URL', getenv('ZEPTOMAIL_URL') ?: 'https://api.zeptomail.com/v1.1/email');
if (!defined('ZEPTOMAIL_FROM_ADDRESS')) define('ZEPTOMAIL_FROM_ADDRESS', getenv('ZEPTOMAIL_FROM_ADDRESS') ?: 'noreply@resmenu.net');
if (!defined('ZEPTOMAIL_FROM_NAME')) define('ZEPTOMAIL_FROM_NAME', getenv('ZEPTOMAIL_FROM_NAME') ?: '');
if (!defined('ZEPTOMAIL_REPLY_TO')) define('ZEPTOMAIL_REPLY_TO', getenv('ZEPTOMAIL_REPLY_TO') ?: 'support@resmenu.net');
if (!defined('ZEPTOMAIL_TIMEOUT_SECONDS')) define('ZEPTOMAIL_TIMEOUT_SECONDS', (int)(getenv('ZEPTOMAIL_TIMEOUT_SECONDS') ?: 30));

// Last-resort fallback: PHP mail()
if (!defined('MAIL_PHP_FALLBACK_ENABLED')) define('MAIL_PHP_FALLBACK_ENABLED', (getenv('MAIL_PHP_FALLBACK_ENABLED') === false || getenv('MAIL_PHP_FALLBACK_ENABLED') === '') ? true : (filter_var(getenv('MAIL_PHP_FALLBACK_ENABLED'), FILTER_VALIDATE_BOOLEAN)));

// Rate limiting storage override (optional)
// If empty, the app auto-detects a writable directory.
if (!defined('RATE_LIMIT_DIR')) define('RATE_LIMIT_DIR', getenv('RATE_LIMIT_DIR') ?: '');

// reCAPTCHA (Google) - set via env or config.local.php
// Leave empty to disable (registration will not enforce CAPTCHA).
if (!defined('RECAPTCHA_SITE_KEY')) define('RECAPTCHA_SITE_KEY', getenv('RECAPTCHA_SITE_KEY') ?: '');
if (!defined('RECAPTCHA_SECRET_KEY')) define('RECAPTCHA_SECRET_KEY', getenv('RECAPTCHA_SECRET_KEY') ?: '');

// Registration OTP rate limits (deliverability / abuse; B2B restaurant defaults)
if (!defined('REG_OTP_LIMIT_PER_EMAIL')) define('REG_OTP_LIMIT_PER_EMAIL', (int)(getenv('REG_OTP_LIMIT_PER_EMAIL') ?: 3));
if (!defined('REG_OTP_EMAIL_WINDOW_SECONDS')) define('REG_OTP_EMAIL_WINDOW_SECONDS', (int)(getenv('REG_OTP_EMAIL_WINDOW_SECONDS') ?: 3600));
if (!defined('REG_OTP_LIMIT_PER_IP')) define('REG_OTP_LIMIT_PER_IP', (int)(getenv('REG_OTP_LIMIT_PER_IP') ?: 5));
if (!defined('REG_OTP_IP_WINDOW_SECONDS')) define('REG_OTP_IP_WINDOW_SECONDS', (int)(getenv('REG_OTP_IP_WINDOW_SECONDS') ?: 3600));
if (!defined('REG_OTP_LIMIT_GLOBAL')) define('REG_OTP_LIMIT_GLOBAL', (int)(getenv('REG_OTP_LIMIT_GLOBAL') ?: 8));
if (!defined('REG_OTP_GLOBAL_WINDOW_SECONDS')) define('REG_OTP_GLOBAL_WINDOW_SECONDS', (int)(getenv('REG_OTP_GLOBAL_WINDOW_SECONDS') ?: 60));
if (!defined('REG_OTP_COOLDOWN_EMAIL_SECONDS')) define('REG_OTP_COOLDOWN_EMAIL_SECONDS', (int)(getenv('REG_OTP_COOLDOWN_EMAIL_SECONDS') ?: 60));
if (!defined('REG_OTP_COOLDOWN_IP_SECONDS')) define('REG_OTP_COOLDOWN_IP_SECONDS', (int)(getenv('REG_OTP_COOLDOWN_IP_SECONDS') ?: 60));
if (!defined('REG_OTP_TTL_MINUTES')) define('REG_OTP_TTL_MINUTES', (int)(getenv('REG_OTP_TTL_MINUTES') ?: 10));
if (!defined('REG_OTP_STRICT_LOCAL_PART')) {
    $strict = getenv('REG_OTP_STRICT_LOCAL_PART');
    define('REG_OTP_STRICT_LOCAL_PART', $strict !== false && $strict !== '' ? filter_var($strict, FILTER_VALIDATE_BOOLEAN) : false);
}
if (!defined('REG_OTP_BOUNCE_WEBHOOK_SECRET')) define('REG_OTP_BOUNCE_WEBHOOK_SECRET', getenv('REG_OTP_BOUNCE_WEBHOOK_SECRET') ?: '');

// Error reporting (set to 0 in production)
// IMPORTANT: Set these to 0 in production!
error_reporting(E_ALL);
ini_set('display_errors', 0); // Changed to 0 for security
ini_set('log_errors', 1);
ini_set('error_log', BASE_PATH . '/logs/php_errors.log');

// Database Configuration (use env or config.local.php; empty = connection will fail)
if (!defined('DB_HOST')) define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
if (!defined('DB_NAME')) define('DB_NAME', getenv('DB_NAME') ?: 'sigsolmenu_resmenu');
if (!defined('DB_USER')) define('DB_USER', getenv('DB_USER') ?: '');
if (!defined('DB_PASS')) define('DB_PASS', getenv('DB_PASS') ?: '');
if (!defined('DB_CHARSET')) define('DB_CHARSET', getenv('DB_CHARSET') ?: 'utf8mb4');

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

require_once __DIR__ . '/../includes/security-headers.php';
