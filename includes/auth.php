<?php
/**
 * Authentication Functions
 */

// Configure session security before starting session
if (session_status() === PHP_SESSION_NONE) {
    // Set secure session cookie parameters
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_samesite', 'Strict');
    // Only set secure flag if using HTTPS
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', 1);
    }
    // Use strict session ID regeneration
    ini_set('session.use_strict_mode', 1);
    
    session_start();
}

// Regenerate session ID periodically for security (after session started)
if (session_status() === PHP_SESSION_ACTIVE) {
    if (!isset($_SESSION['created'])) {
        $_SESSION['created'] = time();
    } else if (time() - $_SESSION['created'] > 1800) {
        // Regenerate every 30 minutes
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/csrf.php';

/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
}

/**
 * Check if user is super admin
 * @return bool
 */
function isSuperAdmin() {
    return isLoggedIn() && $_SESSION['user_role'] === 'super_admin';
}

/**
 * Check if user is manager
 * @return bool
 */
function isManager() {
    return isLoggedIn() && $_SESSION['user_role'] === 'manager';
}

/**
 * Get current user ID
 * @return int|null
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user restaurant ID
 * @return int|null
 */
function getCurrentUserRestaurantId() {
    return $_SESSION['restaurant_id'] ?? null;
}

/**
 * Require login - redirect if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /admin/login.php');
        exit;
    }
}

/**
 * Require super admin - redirect if not super admin
 */
function requireSuperAdmin() {
    requireLogin();
    if (!isSuperAdmin()) {
        header('Location: /admin/dashboard.php');
        exit;
    }
}

/**
 * Require manager - redirect if not manager
 */
function requireManager() {
    requireLogin();
    if (!isManager()) {
        header('Location: /admin/dashboard.php');
        exit;
    }
}

/**
 * Login user
 * @param string $username
 * @param string $password
 * @return array ['success' => bool, 'message' => string, 'user' => array|null]
 */
function loginUser($username, $password) {
    $pdo = getDBConnection();
    if (!$pdo) {
        return ['success' => false, 'message' => 'Database connection failed', 'user' => null];
    }
    
    try {
        // Try admin first
        $stmt = $pdo->prepare("SELECT id, username, email, password_hash FROM admins WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        
        if ($user && ($user['password_hash'] === $password || password_verify($password, $user['password_hash']))) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = 'super_admin';
            $_SESSION['username'] = $user['username'];
            $user['role'] = 'super_admin';
            $user['restaurant_id'] = null;
            
            return ['success' => true, 'message' => 'Login successful', 'user' => $user];
        }
        
        // Try manager if admin login failed
        $stmt = $pdo->prepare("SELECT id, username, email, password_hash, restaurant_id FROM managers WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        
        if ($user && ($user['password_hash'] === $password || password_verify($password, $user['password_hash']))) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = 'manager';
            $_SESSION['username'] = $user['username'];
            $_SESSION['restaurant_id'] = $user['restaurant_id'];
            $user['role'] = 'manager';
            
            return ['success' => true, 'message' => 'Login successful', 'user' => $user];
        }
        
        return ['success' => false, 'message' => 'Invalid username or password', 'user' => null];
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Login failed', 'user' => null];
    }
}

/**
 * Logout user
 */
function logoutUser() {
    $_SESSION = [];
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    session_destroy();
}

/**
 * Hash password
 * @param string $password
 * @return string
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Get restaurant slug by user ID
 * @param int $userId
 * @return string|null
 */
function getRestaurantSlugByUserId($userId) {
    $pdo = getDBConnection();
    if (!$pdo) return null;
    
    try {
        $stmt = $pdo->prepare("SELECT r.slug FROM restaurants r INNER JOIN managers m ON r.id = m.restaurant_id WHERE m.id = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        return $result ? $result['slug'] : null;
    } catch (PDOException $e) {
        error_log("Error getting restaurant slug by user ID: " . $e->getMessage());
        return null;
    }
}

/**
 * Build a safe client IP string for logs/rate limits.
 *
 * @return string
 */
function getClientIpAddress() {
    $candidates = [
        $_SERVER['HTTP_CF_CONNECTING_IP'] ?? '',
        $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '',
        $_SERVER['REMOTE_ADDR'] ?? '',
    ];

    foreach ($candidates as $value) {
        if (!$value) {
            continue;
        }
        $ip = trim(explode(',', $value)[0]);
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }
    }

    return '0.0.0.0';
}

/**
 * Hash reset token for DB storage.
 *
 * @param string $token
 * @return string
 */
function hashPasswordResetToken($token) {
    return hash('sha256', (string)$token);
}

/**
 * Try to find an auth user by username/email.
 *
 * @param PDO $pdo
 * @param string $identifier
 * @return array|null
 */
function findUserForPasswordReset(PDO $pdo, $identifier) {
    $identifier = trim((string)$identifier);
    if ($identifier === '') {
        return null;
    }

    // Admin first to mirror login precedence.
    $stmt = $pdo->prepare("SELECT id, username, email FROM admins WHERE username = ? OR email = ? LIMIT 1");
    $stmt->execute([$identifier, $identifier]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $user['user_type'] = 'admin';
        return $user;
    }

    $stmt = $pdo->prepare("SELECT id, username, email FROM managers WHERE username = ? OR email = ? LIMIT 1");
    $stmt->execute([$identifier, $identifier]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $user['user_type'] = 'manager';
        return $user;
    }

    return null;
}

/**
 * Check reset request rate limit by identifier/IP.
 *
 * @param PDO $pdo
 * @param string $identifier
 * @param string $ipAddress
 * @return bool
 */
function isPasswordResetRateLimited(PDO $pdo, $identifier, $ipAddress) {
    $identifier = mb_strtolower(trim((string)$identifier), 'UTF-8');
    $ipAddress = trim((string)$ipAddress);

    // Identifier limit: max 3 requests in 15 minutes.
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM password_reset_tokens
        WHERE identifier = ?
        AND created_at >= (NOW() - INTERVAL 15 MINUTE)
    ");
    $stmt->execute([$identifier]);
    $identifierCount = (int)$stmt->fetchColumn();
    if ($identifierCount >= 3) {
        return true;
    }

    // IP limit: max 10 requests in 15 minutes.
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM password_reset_tokens
        WHERE request_ip = ?
        AND created_at >= (NOW() - INTERVAL 15 MINUTE)
    ");
    $stmt->execute([$ipAddress]);
    $ipCount = (int)$stmt->fetchColumn();

    return $ipCount >= 10;
}

/**
 * Start password reset flow.
 * Returns true even when account is not found to avoid enumeration.
 *
 * @param string $identifier
 * @return bool
 */
function requestPasswordReset($identifier) {
    $pdo = getDBConnection();
    if (!$pdo) {
        return false;
    }

    $identifier = trim((string)$identifier);
    if ($identifier === '') {
        return true;
    }

    $ipAddress = getClientIpAddress();
    $userAgent = substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);

    try {
        if (isPasswordResetRateLimited($pdo, $identifier, $ipAddress)) {
            return true;
        }

        $user = findUserForPasswordReset($pdo, $identifier);
        if (!$user || empty($user['email']) || !filter_var($user['email'], FILTER_VALIDATE_EMAIL)) {
            return true;
        }

        $token = bin2hex(random_bytes(32));
        $tokenHash = hashPasswordResetToken($token);
        $expiresAt = date('Y-m-d H:i:s', time() + 3600);

        $stmt = $pdo->prepare("
            INSERT INTO password_reset_tokens
            (user_type, user_id, identifier, email, token_hash, expires_at, request_ip, user_agent, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $user['user_type'],
            (int)$user['id'],
            mb_strtolower($identifier, 'UTF-8'),
            $user['email'],
            $tokenHash,
            $expiresAt,
            $ipAddress,
            $userAgent,
        ]);

        require_once __DIR__ . '/functions.php';
        require_once __DIR__ . '/email-templates.php';
        require_once __DIR__ . '/mail.php';

        $siteSettings = getSiteSettings();
        $siteName = $siteSettings['site_name'] ?? 'Resmenu';
        $resetUrl = rtrim(SITE_URL, '/') . '/reset-password.php?token=' . urlencode($token);
        $emailBody = getPasswordResetEmail($siteName, $user['username'] ?: $user['email'], $resetUrl, 60);
        $subject = 'Password reset request - ' . $siteName;

        sendEmail($user['email'], $user['username'] ?? '', $subject, $emailBody, [
            'from_name' => $siteName,
        ]);

        return true;
    } catch (Throwable $e) {
        error_log('requestPasswordReset error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Validate and consume a reset token.
 *
 * @param string $token
 * @param string $newPassword
 * @param string|null $errorMessage
 * @return bool
 */
function resetPasswordWithToken($token, $newPassword, &$errorMessage = null) {
    $pdo = getDBConnection();
    if (!$pdo) {
        $errorMessage = 'Database connection failed. Please try again.';
        return false;
    }

    $token = trim((string)$token);
    if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
        $errorMessage = 'Invalid or expired reset link.';
        return false;
    }

    $newPassword = (string)$newPassword;
    if (strlen($newPassword) < PASSWORD_MIN_LENGTH) {
        $errorMessage = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters.';
        return false;
    }

    $tokenHash = hashPasswordResetToken($token);

    try {
        $stmt = $pdo->prepare("
            SELECT id, user_type, user_id
            FROM password_reset_tokens
            WHERE token_hash = ?
            AND used_at IS NULL
            AND expires_at > NOW()
            LIMIT 1
        ");
        $stmt->execute([$tokenHash]);
        $resetRow = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$resetRow) {
            $errorMessage = 'Invalid or expired reset link.';
            return false;
        }

        $table = $resetRow['user_type'] === 'admin' ? 'admins' : 'managers';
        $passwordHash = hashPassword($newPassword);

        $pdo->beginTransaction();

        $updateUser = $pdo->prepare("UPDATE {$table} SET password_hash = ? WHERE id = ? LIMIT 1");
        $updateUser->execute([$passwordHash, (int)$resetRow['user_id']]);

        $markUsed = $pdo->prepare("UPDATE password_reset_tokens SET used_at = NOW() WHERE id = ? LIMIT 1");
        $markUsed->execute([(int)$resetRow['id']]);

        $invalidateOthers = $pdo->prepare("
            UPDATE password_reset_tokens
            SET used_at = NOW()
            WHERE user_type = ?
            AND user_id = ?
            AND used_at IS NULL
            AND id <> ?
        ");
        $invalidateOthers->execute([
            $resetRow['user_type'],
            (int)$resetRow['user_id'],
            (int)$resetRow['id'],
        ]);

        $pdo->commit();
        return true;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('resetPasswordWithToken error: ' . $e->getMessage());
        $errorMessage = 'Unable to reset password. Please try again.';
        return false;
    }
}

