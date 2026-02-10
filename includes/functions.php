<?php
/**
 * Helper Functions
 */

require_once __DIR__ . '/../config/config.php';

/**
 * Generate URL-friendly slug from string
 * @param string $text
 * @return string
 */
function generateSlug($text) {
    $text = trim($text);
    $text = mb_strtolower($text, 'UTF-8');
    
    // Replace spaces and special characters with hyphens
    $text = preg_replace('/[^\p{L}\p{N}\s-]/u', '', $text);
    $text = preg_replace('/[\s-]+/u', '-', $text);
    $text = trim($text, '-');
    
    return $text;
}

/**
 * Sanitize input
 * @param mixed $data
 * @return mixed
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email
 * @param string $email
 * @return bool
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Upload file
 * @param array $file $_FILES array element
 * @param string $destination Directory path
 * @param array $allowedTypes Allowed MIME types
 * @return array ['success' => bool, 'message' => string, 'filename' => string|null]
 */
function uploadFile($file, $destination, $allowedTypes = null) {
    if ($allowedTypes === null) {
        $allowedTypes = ALLOWED_IMAGE_TYPES;
    }
    
    if (!isset($file['error']) || is_array($file['error'])) {
        return ['success' => false, 'message' => 'Invalid file upload', 'filename' => null];
    }
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'File upload error', 'filename' => null];
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'File too large', 'filename' => null];
    }
    
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    
    if (!in_array($mimeType, $allowedTypes)) {
        return ['success' => false, 'message' => 'Invalid file type', 'filename' => null];
    }
    
    // Whitelist allowed extensions
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // Validate extension against whitelist
    if (!in_array($extension, $allowedExtensions)) {
        return ['success' => false, 'message' => 'Invalid file extension', 'filename' => null];
    }
    
    // Generate safe filename
    $filename = uniqid() . '.' . $extension;
    $filepath = rtrim($destination, '/') . '/' . $filename;
    
    if (!is_dir($destination)) {
        mkdir($destination, 0755, true);
    }
    
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => false, 'message' => 'Failed to save file', 'filename' => null];
    }
    
    return ['success' => true, 'message' => 'File uploaded successfully', 'filename' => $filename];
}

/**
 * Delete file
 * @param string $filepath
 * @return bool
 */
function deleteFile($filepath) {
    if (file_exists($filepath)) {
        return unlink($filepath);
    }
    return false;
}

/**
 * Format price
 * @param float $price
 * @param string $currency
 * @return string
 */
function formatPrice($price, $currency = '₦') {
    return $currency . number_format($price, 2);
}

/**
 * Get restaurant by ID
 * @param int $id
 * @return array|null
 */
function getRestaurant($id) {
    $pdo = getDBConnection();
    if (!$pdo) return null;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM restaurants WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error getting restaurant: " . $e->getMessage());
        return null;
    }
}

/**
 * Get restaurant by slug
 * @param string $slug
 * @return array|null
 */
function getRestaurantBySlug($slug) {
    $pdo = getDBConnection();
    if (!$pdo) return null;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM restaurants WHERE slug = ? AND is_active = 1");
        $stmt->execute([$slug]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error getting restaurant by slug: " . $e->getMessage());
        return null;
    }
}

/**
 * Get all active restaurants
 * @return array
 */
function getAllActiveRestaurants() {
    $pdo = getDBConnection();
    if (!$pdo) return [];
    
    try {
        $stmt = $pdo->query("SELECT * FROM restaurants WHERE is_active = 1 ORDER BY name ASC");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error getting all active restaurants: " . $e->getMessage());
        return [];
    }
}

/**
 * Get categories for restaurant
 * @param int $restaurantId
 * @return array
 */
function getCategories($restaurantId) {
    $pdo = getDBConnection();
    if (!$pdo) return [];
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM categories WHERE restaurant_id = ? AND is_active = 1 ORDER BY display_order ASC, name ASC");
        $stmt->execute([$restaurantId]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error getting categories: " . $e->getMessage());
        return [];
    }
}

/**
 * Get menu items for category
 * @param int $categoryId
 * @return array
 */
function getMenuItems($categoryId) {
    $pdo = getDBConnection();
    if (!$pdo) return [];
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE category_id = ? AND is_available = 1 ORDER BY display_order ASC, name ASC");
        $stmt->execute([$categoryId]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error getting menu items: " . $e->getMessage());
        return [];
    }
}

/**
 * Get customization settings for restaurant
 * @param int $restaurantId
 * @return array
 */
function getCustomizationSettings($restaurantId) {
    $pdo = getDBConnection();
    if (!$pdo) return [];
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM customization_settings WHERE restaurant_id = ?");
        $stmt->execute([$restaurantId]);
        $settings = $stmt->fetch();
        
        if (!$settings) {
            // Create default settings
            $stmt = $pdo->prepare("INSERT INTO customization_settings (restaurant_id) VALUES (?)");
            $stmt->execute([$restaurantId]);
            return getCustomizationSettings($restaurantId);
        }
        
        return $settings;
    } catch (PDOException $e) {
        error_log("Error getting customization settings: " . $e->getMessage());
        return [];
    }
}

/**
 * JSON response helper
 * @param bool $success
 * @param string $message
 * @param mixed $data
 */
function jsonResponse($success, $message, $data = null) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

/**
 * Get categories with menu items for a restaurant
 * @param int $restaurantId
 * @return array
 */
function getCategoriesWithMenuItems($restaurantId) {
    $pdo = getDBConnection();
    if (!$pdo) return [];
    
    try {
        // Get categories
        $categories = getCategories($restaurantId);
        
        // Get menu items for each category
        foreach ($categories as &$category) {
            $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE category_id = ? AND is_available = 1 ORDER BY display_order ASC, name ASC");
            $stmt->execute([$category['id']]);
            $category['menu_items'] = $stmt->fetchAll();
        }
        
        return $categories;
    } catch (PDOException $e) {
        error_log("Error getting categories with menu items: " . $e->getMessage());
        return [];
    }
}

/**
 * Update restaurant menu item statistics
 * @param int $restaurantId
 * @return bool
 */
function updateRestaurantStats($restaurantId) {
    $pdo = getDBConnection();
    if (!$pdo) return false;
    
    try {
        // Count available menu items
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM menu_items WHERE restaurant_id = ? AND is_available = 1");
        $stmt->execute([$restaurantId]);
        $availableItems = (int)$stmt->fetchColumn();
        
        // Count unavailable menu items
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM menu_items WHERE restaurant_id = ? AND is_available = 0");
        $stmt->execute([$restaurantId]);
        $unavailableItems = (int)$stmt->fetchColumn();
        
        // Update restaurant stats (check if columns exist first)
        try {
            $stmt = $pdo->prepare("UPDATE restaurants SET available_items_count = ?, unavailable_items_count = ? WHERE id = ?");
            $stmt->execute([$availableItems, $unavailableItems, $restaurantId]);
        } catch (PDOException $e) {
            // If columns don't exist, they'll be added by migration
            error_log("Error updating restaurant stats (columns may not exist yet): " . $e->getMessage());
            return false;
        }
        
        return true;
    } catch (PDOException $e) {
        error_log("Error updating restaurant stats: " . $e->getMessage());
        return false;
    }
}

