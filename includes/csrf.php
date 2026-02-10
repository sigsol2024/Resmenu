<?php
/**
 * CSRF Protection Functions
 */

/**
 * Generate CSRF token
 * @return string
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Get CSRF token for forms
 * @return string
 */
function getCSRFToken() {
    return generateCSRFToken();
}

/**
 * Validate CSRF token
 * @param string $token
 * @return bool
 */
function validateCSRFToken($token) {
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Require CSRF token for POST requests
 * @param string $tokenFieldName Field name containing token (default: 'csrf_token')
 * @return bool
 */
function requireCSRFToken($tokenFieldName = 'csrf_token') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return true; // Only validate POST requests
    }
    
    $token = $_POST[$tokenFieldName] ?? '';
    if (!validateCSRFToken($token)) {
        http_response_code(403);
        die('Invalid security token. Please refresh the page and try again.');
    }
    return true;
}

