<?php
/**
 * Example local config (copy to config.local.php and set values).
 * config.local.php is gitignored. Do not commit real secrets.
 *
 * Production: set environment variables (DB_PASS, SMTP_PASSWORD, etc.)
 * or define them here in config.local.php.
 */
// define('DB_HOST', 'localhost');
// define('DB_NAME', 'sigsolmenu_resmenu');
// define('DB_USER', 'your-db-user');
// define('DB_PASS', 'your-db-password');
// define('DB_CHARSET', 'utf8mb4');
// define('SMTP_HOST', 'smtp.example.com');
// define('SMTP_PORT', '465');
// define('SMTP_SECURE', 'ssl');
// define('SMTP_USERNAME', '');
// define('SMTP_PASSWORD', 'your-smtp-password');
// define('MAIL_FROM_EMAIL', 'noreply@resmenu.net');
// define('MAIL_FROM_NAME', 'Your Site');

// ZeptoMail (API) - primary transactional mail transport (recommended)
// define('ZEPTOMAIL_SENDMAIL_TOKEN', 'your-send-mail-token'); // you can also paste the full \"Zoho-enczapikey <token>\" value; it will be normalized
// define('ZEPTOMAIL_URL', 'https://api.zeptomail.com/v1.1/email');
// define('ZEPTOMAIL_FROM_ADDRESS', 'noreply@resmenu.net');
// define('ZEPTOMAIL_FROM_NAME', 'Resmenu');
// define('ZEPTOMAIL_REPLY_TO', 'support@resmenu.net');
// define('ZEPTOMAIL_TIMEOUT_SECONDS', 30);
// define('MAIL_PHP_FALLBACK_ENABLED', true); // last resort only
// define('RATE_LIMIT_DIR', '/tmp/resmenu-rate-limits'); // optional override for file-based rate limiting

// Google reCAPTCHA (recommended for registration)
// define('RECAPTCHA_SITE_KEY', 'your-recaptcha-site-key');
// define('RECAPTCHA_SECRET_KEY', 'your-recaptcha-secret-key');

// Registration OTP (deliverability / abuse; defaults are B2B-safe — override via env if needed)
// define('REG_OTP_LIMIT_PER_EMAIL', 3);           // max OTP sends per email per window
// define('REG_OTP_EMAIL_WINDOW_SECONDS', 3600);  // window for per-email limit (1 hour)
// define('REG_OTP_LIMIT_PER_IP', 5);
// define('REG_OTP_IP_WINDOW_SECONDS', 3600);
// define('REG_OTP_LIMIT_GLOBAL', 8);             // max OTP sends per minute site-wide
// define('REG_OTP_GLOBAL_WINDOW_SECONDS', 60);
// define('REG_OTP_COOLDOWN_EMAIL_SECONDS', 60);
// define('REG_OTP_COOLDOWN_IP_SECONDS', 60);
// define('REG_OTP_TTL_MINUTES', 10);             // OTP validity (clamped 5–10 in code)
// define('REG_OTP_STRICT_LOCAL_PART', false);     // optional aggressive local-part heuristic
// define('REG_OTP_BOUNCE_WEBHOOK_SECRET', '');   // Bearer / X-Webhook-Secret for api/email-suppression-webhook.php (prefer X-Webhook-Secret if Apache strips Authorization)

// Hard-bounce suppression for registration OTP: run database/migration_registration_email_suppression.sql once.

// Public cancel-order / cancel-bank-transfer-order HMAC (generate a long random secret; never commit to Git)
// define('APP_HMAC_SECRET', getenv('APP_HMAC_SECRET') ?: '');

// Trust X-Forwarded-For / CF-Connecting-IP for getClientIpAddress() (set true only behind a trusted reverse proxy)
// define('TRUST_PROXY_HEADERS', false);

// Admin/manager idle session timeout in seconds (requireLogin). Default 3600; 0 disables idle logout.
// define('AUTH_SESSION_IDLE_SECONDS', 3600);
