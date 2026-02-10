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

