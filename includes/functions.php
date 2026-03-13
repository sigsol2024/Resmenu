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
 * Sanitize input for database storage (strip tags, trim).
 * Do NOT use htmlspecialchars here - that encodes & to &amp; and causes double-encoding
 * when output. Use htmlspecialchars only when outputting to HTML.
 * @param mixed $data
 * @return mixed
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return strip_tags(trim((string) $data));
}

/**
 * Sanitize string for safe HTML output (trim, strip tags, optional max length).
 * Use with htmlspecialchars when echoing to HTML.
 * @param string $str
 * @param int $maxLength Max length (0 = no limit)
 * @return string
 */
function sanitizeForHtml($str, $maxLength = 0) {
    $s = strip_tags(trim((string) $str));
    if ($maxLength > 0 && mb_strlen($s) > $maxLength) {
        $s = mb_substr($s, 0, $maxLength, 'UTF-8');
    }
    return $s;
}

/**
 * Sanitize and validate URL. Returns empty string if invalid.
 * @param string $url
 * @return string
 */
function sanitizeUrl($url) {
    $url = trim((string) $url);
    if ($url === '') return '';
    $url = strip_tags($url);
    return filter_var($url, FILTER_VALIDATE_URL) !== false ? $url : '';
}

/**
 * Sanitize and validate email. Returns null if invalid.
 * @param string $email
 * @return string|null
 */
function sanitizeEmail($email) {
    $email = trim((string) $email);
    if ($email === '') return null;
    $email = strip_tags($email);
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false ? $email : null;
}

/**
 * Sanitize slug: only lowercase letters, numbers, hyphens.
 * @param string $slug
 * @param int $maxLength Max length (0 = no limit)
 * @return string
 */
function sanitizeSlug($slug, $maxLength = 255) {
    $s = trim((string) $slug);
    $s = mb_strtolower($s, 'UTF-8');
    $s = preg_replace('/[^a-z0-9-]/', '', $s);
    $s = trim($s, '-');
    if ($maxLength > 0 && mb_strlen($s) > $maxLength) {
        $s = mb_substr($s, 0, $maxLength, 'UTF-8');
    }
    return $s;
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
 * Resize image at $tmpPath to fit within $maxBytes (target ~$maxBytes).
 * Overwrites $tmpPath with the resized image. Uses GD.
 * @param string $tmpPath Path to temp image file
 * @param int $maxBytes Target max size in bytes (e.g. IMAGE_MAX_BYTES)
 * @param string $mimeType MIME type (image/jpeg, image/png, etc.)
 * @return bool True on success, false on failure
 */
function resizeImageToMaxSize($tmpPath, $maxBytes, $mimeType) {
    if (!function_exists('imagecreatefromjpeg')) {
        return false;
    }
    $img = null;
    switch ($mimeType) {
        case 'image/jpeg':
        case 'image/jpg':
            $img = @imagecreatefromjpeg($tmpPath);
            break;
        case 'image/png':
            $img = @imagecreatefrompng($tmpPath);
            break;
        case 'image/gif':
            $img = @imagecreatefromgif($tmpPath);
            break;
        case 'image/webp':
            if (function_exists('imagecreatefromwebp')) {
                $img = @imagecreatefromwebp($tmpPath);
            }
            break;
        default:
            return false;
    }
    if (!$img) {
        return false;
    }
    $w = imagesx($img);
    $h = imagesy($img);
    if ($w < 1 || $h < 1) {
        imagedestroy($img);
        return false;
    }
    $currentSize = filesize($tmpPath);
    if ($currentSize <= $maxBytes) {
        imagedestroy($img);
        return true;
    }
    // Scale down dimensions to reduce file size (rough: size ~ width*height * factor)
    $ratio = sqrt($maxBytes / (float) max($currentSize, 1));
    $ratio = min($ratio, 0.95);
    $nw = max(1, (int) round($w * $ratio));
    $nh = max(1, (int) round($h * $ratio));
    $scaled = imagescale($img, $nw, $nh);
    imagedestroy($img);
    if (!$scaled) {
        return false;
    }
    $quality = 82;
    $outPath = $tmpPath . '.resized';
    $saved = false;
    while ($quality >= 30) {
        if ($mimeType === 'image/png') {
            $compression = (int) round((100 - $quality) * 9 / 100);
            $saved = imagepng($scaled, $outPath, min(9, $compression));
        } elseif ($mimeType === 'image/gif') {
            $saved = imagegif($scaled, $outPath);
        } elseif ($mimeType === 'image/webp' && function_exists('imagewebp')) {
            $saved = imagewebp($scaled, $outPath, $quality);
        } else {
            $saved = imagejpeg($scaled, $outPath, $quality);
        }
        if ($saved && file_exists($outPath) && filesize($outPath) <= $maxBytes) {
            break;
        }
        $quality -= 10;
    }
    imagedestroy($scaled);
    if (!$saved || !file_exists($outPath)) {
        if (file_exists($outPath)) {
            @unlink($outPath);
        }
        return false;
    }
    $finalSize = filesize($outPath);
    if ($finalSize > $maxBytes) {
        @unlink($outPath);
        return false;
    }
    if (!rename($outPath, $tmpPath)) {
        @unlink($outPath);
        return false;
    }
    return true;
}

/**
 * Upload file. For images: reject > 1MB; auto-resize to max 500KB when possible.
 * @param array $file $_FILES array element
 * @param string $destination Directory path
 * @param array $allowedTypes Allowed MIME types
 * @param array $extraExtensions Extra extensions (e.g. 'ico' for favicon)
 * @return array ['success' => bool, 'message' => string, 'filename' => string|null]
 */
function uploadFile($file, $destination, $allowedTypes = null, $extraExtensions = []) {
    if ($allowedTypes === null) {
        $allowedTypes = ALLOWED_IMAGE_TYPES;
    }
    
    if (!isset($file['error']) || is_array($file['error'])) {
        return ['success' => false, 'message' => 'Invalid file upload', 'filename' => null];
    }
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'File upload error', 'filename' => null];
    }
    
    // Image size cap: reject uploads over 1MB
    if (defined('IMAGE_UPLOAD_MAX_BYTES') && $file['size'] > IMAGE_UPLOAD_MAX_BYTES) {
        return ['success' => false, 'message' => 'Image must be 1 MB or smaller. Please reduce the file size and try again.', 'filename' => null];
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'File too large', 'filename' => null];
    }
    
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    if ($mimeType === false || !is_string($mimeType) || $mimeType === '') {
        return ['success' => false, 'message' => 'Invalid file type', 'filename' => null];
    }
    if (!in_array($mimeType, $allowedTypes)) {
        return ['success' => false, 'message' => 'Invalid file type', 'filename' => null];
    }
    
    // Whitelist allowed extensions (merge with extra for favicon .ico etc)
    $allowedExtensions = array_merge(['jpg', 'jpeg', 'png', 'gif', 'webp'], (array)$extraExtensions);
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // Validate extension against whitelist
    if (!in_array($extension, $allowedExtensions)) {
        return ['success' => false, 'message' => 'Invalid file extension', 'filename' => null];
    }
    
    // Auto-resize images over 500KB when type is resizable (jpeg, png, gif, webp). Favicon .ico / x-icon is not resized.
    $resizableTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    if (defined('IMAGE_MAX_BYTES') && $file['size'] > IMAGE_MAX_BYTES && in_array($mimeType, $resizableTypes)) {
        $resized = resizeImageToMaxSize($file['tmp_name'], IMAGE_MAX_BYTES, $mimeType);
        if ($resized && file_exists($file['tmp_name']) && filesize($file['tmp_name']) <= IMAGE_MAX_BYTES) {
            $file['size'] = filesize($file['tmp_name']);
        }
        // If resize failed, still allow save if under 1MB (already passed); final file may be >500KB
        // Optionally reject if still over 500KB: uncomment below to enforce strict 500KB after failed resize
        // if ($resized === false && $file['size'] > IMAGE_MAX_BYTES) {
        //     return ['success' => false, 'message' => 'Image could not be resized. Please use an image under 500 KB.', 'filename' => null];
        // }
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
    
    // Enforce max stored size for resizable types: if still over 500KB after resize attempt, reject
    if (defined('IMAGE_MAX_BYTES') && in_array($mimeType, $resizableTypes) && filesize($filepath) > IMAGE_MAX_BYTES) {
        deleteFile($filepath);
        return ['success' => false, 'message' => 'Image could not be resized to the allowed size. Please use an image under 500 KB.', 'filename' => null];
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
 * Build a unique manager username for a new restaurant manager.
 *
 * @param PDO $pdo
 * @param string $restaurantName
 * @return string
 * @throws Exception
 */
function generateUniqueManagerUsername(PDO $pdo, $restaurantName) {
    $base = strtolower(preg_replace('/[^a-z0-9]/', '', (string)$restaurantName));
    if ($base === '') {
        $base = 'restaurant';
    }

    $base .= '_manager';
    $username = $base;
    $counter = 1;
    $maxIterations = 1000;

    while ($counter <= $maxIterations) {
        $checkStmt = $pdo->prepare("SELECT id FROM managers WHERE username = ? LIMIT 1");
        $checkStmt->execute([$username]);
        if (!$checkStmt->fetch(PDO::FETCH_ASSOC)) {
            return $username;
        }
        $username = $base . $counter;
        $counter++;
    }

    throw new Exception('Unable to generate a unique manager username.');
}

/**
 * Create restaurant + manager + defaults + trial subscription.
 * Used by both admin flow and public self-registration flow.
 *
 * @param PDO $pdo
 * @param array $input
 * @param array $files ['logo' => $_FILES entry, 'hero_image' => $_FILES entry]
 * @param array $options ['default_template_id' => int, 'trial_plan_slug' => string, 'trial_days' => int]
 * @return array ['success' => bool, 'message' => string, 'restaurant_id' => int|null, 'manager_id' => int|null, 'slug' => string|null]
 */
function createRestaurantWithManager(PDO $pdo, array $input, array $files = [], array $options = []) {
    require_once __DIR__ . '/auth.php';
    require_once __DIR__ . '/subscription.php';

    $name = sanitize($input['name'] ?? '');
    $slugInput = sanitize($input['slug'] ?? '');
    $slug = generateSlug($slugInput !== '' ? $slugInput : $name);
    $description = sanitize($input['description'] ?? '');
    $phone = sanitize($input['phone'] ?? '');
    $managerEmail = sanitize($input['manager_email'] ?? '');
    $email = $managerEmail;
    $address = sanitize($input['address'] ?? '');
    $whatsappLink = sanitize($input['whatsapp_link'] ?? '');
    $instagramUrl = sanitize($input['instagram_url'] ?? '');
    $facebookUrl = sanitize($input['facebook_url'] ?? '');
    $twitterUrl = sanitize($input['twitter_url'] ?? '');
    $ratingSource = sanitize($input['rating_source'] ?? 'Google');
    $googleRatingRaw = $input['google_rating'] ?? '';
    $managerPassword = (string)($input['manager_password'] ?? '');
    $managerPasswordConfirm = (string)($input['manager_password_confirm'] ?? '');
    // Supports checkbox-style form submit and explicit numeric/boolean value from server callers.
    $isActive = 0;
    if (array_key_exists('is_active', $input)) {
        $rawActive = $input['is_active'];
        $isActive = in_array((string)$rawActive, ['1', 'true', 'on', 'yes'], true) || $rawActive === 1 || $rawActive === true ? 1 : 0;
    }

    if ($name === '' || $slug === '') {
        return ['success' => false, 'message' => 'Restaurant name and slug are required.', 'restaurant_id' => null, 'manager_id' => null, 'slug' => null];
    }
    if (!preg_match('/^[a-z0-9-]+$/', $slug)) {
        return ['success' => false, 'message' => 'Slug can only contain lowercase letters, numbers, and hyphens.', 'restaurant_id' => null, 'manager_id' => null, 'slug' => null];
    }
    if ($managerEmail === '' || $managerPassword === '') {
        return ['success' => false, 'message' => 'Manager email and password are required.', 'restaurant_id' => null, 'manager_id' => null, 'slug' => null];
    }
    if (!isValidEmail($managerEmail)) {
        return ['success' => false, 'message' => 'Invalid manager email address.', 'restaurant_id' => null, 'manager_id' => null, 'slug' => null];
    }
    if (strlen($managerPassword) < PASSWORD_MIN_LENGTH) {
        return ['success' => false, 'message' => 'Manager password is too short.', 'restaurant_id' => null, 'manager_id' => null, 'slug' => null];
    }
    if ($managerPassword !== $managerPasswordConfirm) {
        return ['success' => false, 'message' => 'Manager passwords do not match.', 'restaurant_id' => null, 'manager_id' => null, 'slug' => null];
    }
    if ($email !== '' && !isValidEmail($email)) {
        return ['success' => false, 'message' => 'Invalid restaurant email address.', 'restaurant_id' => null, 'manager_id' => null, 'slug' => null];
    }
    if ($phone !== '' && !preg_match('/^[0-9]{7,15}$/', $phone)) {
        return ['success' => false, 'message' => 'Phone number must be 7-15 digits (numbers only).', 'restaurant_id' => null, 'manager_id' => null, 'slug' => null];
    }
    foreach (['whatsapp_link' => $whatsappLink, 'instagram_url' => $instagramUrl, 'facebook_url' => $facebookUrl, 'twitter_url' => $twitterUrl] as $label => $url) {
        if ($url !== '' && filter_var($url, FILTER_VALIDATE_URL) === false) {
            return ['success' => false, 'message' => "Invalid URL in {$label}.", 'restaurant_id' => null, 'manager_id' => null, 'slug' => null];
        }
    }

    $googleRating = null;
    if ($googleRatingRaw !== '' && $googleRatingRaw !== null) {
        $googleRating = (float)$googleRatingRaw;
        if ($googleRating < 0 || $googleRating > 5) {
            return ['success' => false, 'message' => 'Rating must be between 0 and 5.', 'restaurant_id' => null, 'manager_id' => null, 'slug' => null];
        }
    }

    // Pre-check slug/email uniqueness for friendly errors.
    $stmt = $pdo->prepare("SELECT id FROM restaurants WHERE slug = ? LIMIT 1");
    $stmt->execute([$slug]);
    if ($stmt->fetch(PDO::FETCH_ASSOC)) {
        return ['success' => false, 'message' => 'This restaurant slug is already in use.', 'restaurant_id' => null, 'manager_id' => null, 'slug' => null];
    }
    $stmt = $pdo->prepare("SELECT id FROM managers WHERE email = ? LIMIT 1");
    $stmt->execute([$managerEmail]);
    if ($stmt->fetch(PDO::FETCH_ASSOC)) {
        return ['success' => false, 'message' => 'This manager email already exists.', 'restaurant_id' => null, 'manager_id' => null, 'slug' => null];
    }

    $logo = null;
    $heroImage = null;
    $uploadedPaths = [];

    if (isset($files['logo']) && is_array($files['logo']) && ($files['logo']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
        $uploadResult = uploadFile($files['logo'], UPLOAD_PATH . '/logos');
        if (!$uploadResult['success']) {
            return ['success' => false, 'message' => $uploadResult['message'], 'restaurant_id' => null, 'manager_id' => null, 'slug' => null];
        }
        $logo = $uploadResult['filename'];
        $uploadedPaths[] = UPLOAD_PATH . '/logos/' . $logo;
    }

    if (isset($files['hero_image']) && is_array($files['hero_image']) && ($files['hero_image']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
        $uploadResult = uploadFile($files['hero_image'], UPLOAD_PATH . '/heroes');
        if (!$uploadResult['success']) {
            foreach ($uploadedPaths as $path) {
                deleteFile($path);
            }
            return ['success' => false, 'message' => $uploadResult['message'], 'restaurant_id' => null, 'manager_id' => null, 'slug' => null];
        }
        $heroImage = $uploadResult['filename'];
        $uploadedPaths[] = UPLOAD_PATH . '/heroes/' . $heroImage;
    }

    $defaultTemplateId = (int)($options['default_template_id'] ?? 1);
    if ($defaultTemplateId < 1) {
        $defaultTemplateId = 1;
    }
    $trialPlanSlug = sanitize($options['trial_plan_slug'] ?? 'enterprise');
    $trialDays = max(1, (int)($options['trial_days'] ?? 7));

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            INSERT INTO restaurants (
                name, slug, description, phone, email, address,
                whatsapp_link, instagram_url, facebook_url, twitter_url,
                logo, hero_image, manager_email, google_rating, rating_source, is_active
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $name, $slug, $description, $phone, $email, $address,
            $whatsappLink, $instagramUrl, $facebookUrl, $twitterUrl,
            $logo, $heroImage, $managerEmail, $googleRating, $ratingSource, $isActive
        ]);
        $restaurantId = (int)$pdo->lastInsertId();

        $passwordHash = hashPassword($managerPassword);
        $username = generateUniqueManagerUsername($pdo, $name);

        $stmt = $pdo->prepare("INSERT INTO managers (username, email, password_hash, restaurant_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, $managerEmail, $passwordHash, $restaurantId]);
        $managerId = (int)$pdo->lastInsertId();

        $stmt = $pdo->prepare("INSERT INTO customization_settings (restaurant_id, template_id) VALUES (?, ?)");
        $stmt->execute([$restaurantId, $defaultTemplateId]);

        $subscriptionResult = createSubscriptionByPlanSlug($restaurantId, $trialPlanSlug, 'monthly', true, $trialDays, $pdo);
        if (!$subscriptionResult['success']) {
            throw new Exception($subscriptionResult['message']);
        }

        $pdo->commit();

        return [
            'success' => true,
            'message' => 'Restaurant created successfully.',
            'restaurant_id' => $restaurantId,
            'manager_id' => $managerId,
            'slug' => $slug
        ];
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        foreach ($uploadedPaths as $path) {
            deleteFile($path);
        }
        return [
            'success' => false,
            'message' => 'Failed to create account: ' . $e->getMessage(),
            'restaurant_id' => null,
            'manager_id' => null,
            'slug' => null
        ];
    }
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
 * Get sections for restaurant
 * @param int $restaurantId
 * @param bool $activeOnly If true, only return active sections (for public menu and dropdowns)
 * @return array
 */
function getSections($restaurantId, $activeOnly = true) {
    $pdo = getDBConnection();
    if (!$pdo) return [];
    try {
        $sql = "SELECT * FROM sections WHERE restaurant_id = ?";
        if ($activeOnly) {
            $sql .= " AND is_active = 1";
        }
        $sql .= " ORDER BY display_order ASC, name ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$restaurantId]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error getting sections: " . $e->getMessage());
        return [];
    }
}

/**
 * Get one section by restaurant and slug with its categories and menu items (for section sub-page).
 * @param int $restaurantId
 * @param string $sectionSlug Section slug (e.g. 'food', 'a-la-carte-menu')
 * @return array|null Section with 'categories' (each with 'menu_items'), or null if not found/inactive
 */
function getSectionWithCategoriesAndItemsBySlug($restaurantId, $sectionSlug) {
    $restaurantId = (int) $restaurantId;
    $sectionSlug = trim((string) $sectionSlug);
    if ($sectionSlug === '') return null;
    $pdo = getDBConnection();
    if (!$pdo) return null;
    try {
        $stmt = $pdo->prepare("SELECT * FROM sections WHERE restaurant_id = ? AND slug = ? AND is_active = 1 LIMIT 1");
        $stmt->execute([$restaurantId, $sectionSlug]);
        $section = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$section) return null;
        $stmt = $pdo->prepare("SELECT * FROM categories WHERE section_id = ? AND is_active = 1 ORDER BY display_order ASC, name ASC");
        $stmt->execute([$section['id']]);
        $section['categories'] = $stmt->fetchAll();
        foreach ($section['categories'] as &$cat) {
            $stmt2 = $pdo->prepare("SELECT * FROM menu_items WHERE category_id = ? AND is_available = 1 ORDER BY display_order ASC, name ASC");
            $stmt2->execute([$cat['id']]);
            $cat['menu_items'] = $stmt2->fetchAll();
        }
        return $section;
    } catch (PDOException $e) {
        error_log("getSectionWithCategoriesAndItemsBySlug: " . $e->getMessage());
        return null;
    }
}

/**
 * Get sections with categories and menu items for public menu (ordered: section → category → items)
 * @param int $restaurantId
 * @return array Array of sections, each with 'categories' (each category with 'menu_items')
 */
function getSectionsWithCategoriesAndItems($restaurantId) {
    $pdo = getDBConnection();
    if (!$pdo) return [];
    try {
        $sections = getSections($restaurantId, true);
        if (empty($sections)) {
            // Fallback: no sections yet (e.g. before migration) — use flat categories as one virtual section
            $categories = getCategoriesWithMenuItems($restaurantId);
            if (empty($categories)) return [];
            return [['id' => 0, 'name' => 'Menu', 'slug' => 'menu', 'display_order' => 1, 'is_active' => 1, 'categories' => $categories]];
        }
        foreach ($sections as &$section) {
            $stmt = $pdo->prepare("SELECT * FROM categories WHERE section_id = ? AND is_active = 1 ORDER BY display_order ASC, name ASC");
            $stmt->execute([$section['id']]);
            $section['categories'] = $stmt->fetchAll();
            foreach ($section['categories'] as &$cat) {
                $stmt2 = $pdo->prepare("SELECT * FROM menu_items WHERE category_id = ? AND is_available = 1 ORDER BY display_order ASC, name ASC");
                $stmt2->execute([$cat['id']]);
                $cat['menu_items'] = $stmt2->fetchAll();
            }
        }
        return $sections;
    } catch (PDOException $e) {
        error_log("Error getting sections with categories and items: " . $e->getMessage());
        // Fallback when sections table missing or error: show categories as one virtual section
        $categories = getCategoriesWithMenuItems($restaurantId);
        if (empty($categories)) return [];
        return [['id' => 0, 'name' => 'Menu', 'slug' => 'menu', 'display_order' => 1, 'is_active' => 1, 'categories' => $categories]];
    }
}

/**
 * Normalize section display_order for a restaurant (renumber 1, 2, 3...)
 * @param int $restaurantId
 */
function normalizeSectionDisplayOrder($restaurantId) {
    $pdo = getDBConnection();
    if (!$pdo || !$restaurantId) return;
    try {
        $check = $pdo->prepare("SELECT COUNT(*) as total, COUNT(DISTINCT display_order) as distinct_orders FROM sections WHERE restaurant_id = ?");
        $check->execute([$restaurantId]);
        $r = $check->fetch(PDO::FETCH_ASSOC);
        if (!$r || $r['total'] == $r['distinct_orders']) return;
        $stmt = $pdo->prepare("SELECT id FROM sections WHERE restaurant_id = ? ORDER BY display_order ASC, id ASC");
        $stmt->execute([$restaurantId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $order = 1;
        foreach ($rows as $row) {
            $pdo->prepare("UPDATE sections SET display_order = ? WHERE id = ?")->execute([$order++, $row['id']]);
        }
    } catch (PDOException $e) {
        error_log("normalizeSectionDisplayOrder: " . $e->getMessage());
    }
}

/**
 * Make space for a new section at the given display_order.
 * @param int $restaurantId
 * @param int $newOrder
 */
function reorderSectionsForInsert($restaurantId, $newOrder) {
    $pdo = getDBConnection();
    if (!$pdo || $newOrder < 1) return;
    $stmt = $pdo->prepare("UPDATE sections SET display_order = display_order + 1 WHERE restaurant_id = ? AND display_order >= ?");
    $stmt->execute([$restaurantId, $newOrder]);
}

/**
 * Reorder sections when updating one section's display_order.
 * @param int $restaurantId
 * @param int $sectionId
 * @param int $oldOrder
 * @param int $newOrder
 */
function reorderSectionsForUpdate($restaurantId, $sectionId, $oldOrder, $newOrder) {
    $pdo = getDBConnection();
    if (!$pdo || $oldOrder == $newOrder) return;
    if ($newOrder < $oldOrder) {
        $stmt = $pdo->prepare("UPDATE sections SET display_order = display_order + 1 WHERE restaurant_id = ? AND display_order >= ? AND display_order < ? AND id != ?");
        $stmt->execute([$restaurantId, $newOrder, $oldOrder, $sectionId]);
    } else {
        $stmt = $pdo->prepare("UPDATE sections SET display_order = display_order - 1 WHERE restaurant_id = ? AND display_order > ? AND display_order <= ? AND id != ?");
        $stmt->execute([$restaurantId, $oldOrder, $newOrder, $sectionId]);
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
 * Normalize display_order for all categories in a restaurant: renumber to 1, 2, 3...
 * Fixes existing duplicates. Call when loading categories list.
 * Only runs if duplicates exist (avoids unnecessary writes).
 * @param int $restaurantId
 */
function normalizeCategoryDisplayOrder($restaurantId) {
    $pdo = getDBConnection();
    if (!$pdo || !$restaurantId) return;
    try {
        $check = $pdo->prepare("SELECT COUNT(*) as total, COUNT(DISTINCT display_order) as distinct_orders FROM categories WHERE restaurant_id = ?");
        $check->execute([$restaurantId]);
        $r = $check->fetch(PDO::FETCH_ASSOC);
        if (!$r || $r['total'] == $r['distinct_orders']) return;
        $stmt = $pdo->prepare("SELECT id FROM categories WHERE restaurant_id = ? ORDER BY display_order ASC, id ASC");
        $stmt->execute([$restaurantId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $order = 1;
        foreach ($rows as $row) {
            $pdo->prepare("UPDATE categories SET display_order = ? WHERE id = ?")->execute([$order++, $row['id']]);
        }
    } catch (PDOException $e) {
        error_log("normalizeCategoryDisplayOrder: " . $e->getMessage());
    }
}

/**
 * Make space for a new category at the given display_order (shift others down).
 * Call before INSERT when creating a category.
 * @param int $restaurantId
 * @param int $newOrder
 */
function reorderCategoriesForInsert($restaurantId, $newOrder) {
    $pdo = getDBConnection();
    if (!$pdo || $newOrder < 1) return;
    $stmt = $pdo->prepare("UPDATE categories SET display_order = display_order + 1 WHERE restaurant_id = ? AND display_order >= ?");
    $stmt->execute([$restaurantId, $newOrder]);
}

/**
 * Reorder categories when updating one category's display_order. Shifts others to avoid duplicates.
 * @param int $restaurantId
 * @param int $categoryId
 * @param int $oldOrder
 * @param int $newOrder
 */
function reorderCategoriesForUpdate($restaurantId, $categoryId, $oldOrder, $newOrder) {
    $pdo = getDBConnection();
    if (!$pdo || $oldOrder == $newOrder) return;
    if ($newOrder < $oldOrder) {
        $stmt = $pdo->prepare("UPDATE categories SET display_order = display_order + 1 WHERE restaurant_id = ? AND display_order >= ? AND display_order < ? AND id != ?");
        $stmt->execute([$restaurantId, $newOrder, $oldOrder, $categoryId]);
    } else {
        $stmt = $pdo->prepare("UPDATE categories SET display_order = display_order - 1 WHERE restaurant_id = ? AND display_order > ? AND display_order <= ? AND id != ?");
        $stmt->execute([$restaurantId, $oldOrder, $newOrder, $categoryId]);
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
 * Get manager email for restaurant (for order/reservation alerts)
 * @param int $restaurantId
 * @return string|null
 */
function getManagerEmailForRestaurant($restaurantId) {
    $pdo = getDBConnection();
    if (!$pdo) return null;
    try {
        $stmt = $pdo->prepare("SELECT manager_email FROM restaurants WHERE id = ?");
        $stmt->execute([$restaurantId]);
        $row = $stmt->fetch();
        if (!empty($row['manager_email'])) {
            return filter_var($row['manager_email'], FILTER_VALIDATE_EMAIL) ? $row['manager_email'] : null;
        }
        $stmt = $pdo->prepare("SELECT email FROM managers WHERE restaurant_id = ? LIMIT 1");
        $stmt->execute([$restaurantId]);
        $m = $stmt->fetch();
        return ($m && filter_var($m['email'], FILTER_VALIDATE_EMAIL)) ? $m['email'] : null;
    } catch (PDOException $e) {
        error_log("getManagerEmailForRestaurant: " . $e->getMessage());
        return null;
    }
}

/**
 * Get template default colors/styles from template_customizations (or hardcoded fallbacks)
 * @param int $templateId
 * @return array
 */
function getTemplateDefaults($templateId) {
    $templateId = (int)$templateId;
    $pdo = getDBConnection();
    if ($pdo) {
        $stmt = $pdo->prepare("SELECT * FROM template_customizations WHERE template_id = ?");
        $stmt->execute([$templateId]);
        $row = $stmt->fetch();
        if ($row) {
            return [
                'menu_title_color' => $row['menu_title_color'] ?? '#000000',
                'menu_title_size' => (int)($row['menu_title_size'] ?? 24),
                'menu_title_font' => $row['menu_title_font'] ?? 'Inter',
                'price_color' => $row['price_color'] ?? '#000000',
                'price_size' => (int)($row['price_size'] ?? 18),
                'price_font' => $row['price_font'] ?? 'Inter',
                'description_color' => $row['description_color'] ?? '#666666',
                'description_size' => (int)($row['description_size'] ?? 14),
                'description_font' => $row['description_font'] ?? 'Inter',
                'category_title_color' => $row['category_title_color'] ?? '#000000',
                'category_title_size' => (int)($row['category_title_size'] ?? 20),
                'category_title_font' => $row['category_title_font'] ?? 'Inter',
                'background_color' => $row['background_color'] ?? '#FFFFFF',
                'header_background_color' => $row['header_background_color'] ?? '#FFFFFF',
                'primary_color' => $row['primary_color'] ?? '#111111',
                'secondary_color' => $row['secondary_color'] ?? '#FFFFFF',
            ];
        }
    }
    // Hardcoded fallbacks per template (from template files)
    $fallbacks = [
        1 => ['menu_title_color'=>'#1A1A1A','menu_title_size'=>24,'menu_title_font'=>'Inter','price_color'=>'#1A1A1A','price_size'=>18,'price_font'=>'Inter','description_color'=>'#666666','description_size'=>14,'description_font'=>'Inter','category_title_color'=>'#1A1A1A','category_title_size'=>20,'category_title_font'=>'Inter','background_color'=>'#FFFFFF','header_background_color'=>'#FFFFFF','primary_color'=>'#1A1A1A','secondary_color'=>'#FAF3E6'],
        2 => ['menu_title_color'=>'#1A1A1A','menu_title_size'=>24,'menu_title_font'=>'Inter','price_color'=>'#ea2a33','price_size'=>18,'price_font'=>'Inter','description_color'=>'#666666','description_size'=>14,'description_font'=>'Inter','category_title_color'=>'#1A1A1A','category_title_size'=>20,'category_title_font'=>'Inter','background_color'=>'#f8f6f6','header_background_color'=>'#f8f6f6','primary_color'=>'#ea2a33','secondary_color'=>'#FFFFFF'],
        3 => ['menu_title_color'=>'#1A1A1A','menu_title_size'=>24,'menu_title_font'=>'Inter','price_color'=>'#ea2a33','price_size'=>18,'price_font'=>'Inter','description_color'=>'#666666','description_size'=>14,'description_font'=>'Inter','category_title_color'=>'#1A1A1A','category_title_size'=>20,'category_title_font'=>'Inter','background_color'=>'#f8f6f6','header_background_color'=>'#f8f6f6','primary_color'=>'#ea2a33','secondary_color'=>'#FFFFFF'],
        4 => ['menu_title_color'=>'#121212','menu_title_size'=>24,'menu_title_font'=>'Epilogue','price_color'=>'#f20d0d','price_size'=>18,'price_font'=>'Epilogue','description_color'=>'#666666','description_size'=>14,'description_font'=>'Epilogue','category_title_color'=>'#121212','category_title_size'=>20,'category_title_font'=>'Epilogue','background_color'=>'#f8f5f5','header_background_color'=>'#121212','primary_color'=>'#f20d0d','secondary_color'=>'#FFFFFF'],
    ];
    return $fallbacks[$templateId] ?? $fallbacks[1];
}

/**
 * Get customization settings for restaurant (per-template)
 * Merges template defaults with user overrides so displayed colors match template design
 * @param int $restaurantId
 * @param int|null $templateId If null, uses restaurant's current template_id
 * @return array
 */
function getCustomizationSettings($restaurantId, $templateId = null) {
    $pdo = getDBConnection();
    if (!$pdo) return getTemplateDefaults($templateId ?? 1);
    
    try {
        if ($templateId === null) {
            $stmt = $pdo->prepare("SELECT template_id FROM restaurants WHERE id = ?");
            $stmt->execute([$restaurantId]);
            $row = $stmt->fetch();
            $templateId = (int)($row['template_id'] ?? 1);
        } else {
            $templateId = (int)$templateId;
        }
        
        $templateDefaults = getTemplateDefaults($templateId);
        
        $stmt = $pdo->prepare("SELECT * FROM customization_settings WHERE restaurant_id = ? AND template_id = ?");
        $stmt->execute([$restaurantId, $templateId]);
        $userSettings = $stmt->fetch();
        
        if (!$userSettings) {
            return $templateDefaults;
        }
        
        $genericDefaults = ['menu_title_color'=>'#000000','price_color'=>'#000000','description_color'=>'#666666','category_title_color'=>'#000000','background_color'=>'#FFFFFF','header_background_color'=>'#FFFFFF','primary_color'=>'#111111','secondary_color'=>'#FFFFFF','menu_title_size'=>24,'price_size'=>18,'description_size'=>14,'category_title_size'=>20,'menu_title_font'=>'Inter','price_font'=>'Inter','description_font'=>'Inter','category_title_font'=>'Inter'];
        $allKeys = array_keys($genericDefaults);
        $allGeneric = true;
        foreach ($allKeys as $k) {
            $v = $userSettings[$k] ?? null;
            if ($v === null || $v === '') continue;
            if (!isset($genericDefaults[$k]) || (string)$v !== (string)$genericDefaults[$k]) {
                $allGeneric = false;
                break;
            }
        }
        if ($allGeneric) {
            return $templateDefaults;
        }
        $sizeKeys = ['menu_title_size','price_size','description_size','category_title_size'];
        $merged = $templateDefaults;
        foreach ($allKeys as $k) {
            $v = $userSettings[$k] ?? null;
            if ($v === null || $v === '') continue;
            if (in_array($k, $sizeKeys)) $merged[$k] = (int)$v;
            else $merged[$k] = $v;
        }
        return $merged;
    } catch (PDOException $e) {
        error_log("Error getting customization settings: " . $e->getMessage());
        return getTemplateDefaults($templateId ?? 1);
    }
}

/**
 * Get site settings (site name, logo, favicon)
 * @return array
 */
function getSiteSettings() {
    $pdo = getDBConnection();
    if (!$pdo) return ['site_name' => 'Resmenu', 'site_logo' => null, 'favicon' => null];
    try {
        $stmt = $pdo->query("SELECT * FROM site_settings WHERE id = 1");
        $row = $stmt->fetch();
        return $row ?: ['site_name' => 'Resmenu', 'site_logo' => null, 'favicon' => null];
    } catch (PDOException $e) {
        error_log("getSiteSettings: " . $e->getMessage());
        return ['site_name' => 'Resmenu', 'site_logo' => null, 'favicon' => null];
    }
}

/**
 * Update site settings
 * @param array $data site_name, site_logo, favicon and optional contact_* fields
 * @return bool
 */
function updateSiteSettings($data) {
    $pdo = getDBConnection();
    if (!$pdo) return false;
    try {
        $siteName = sanitizeForHtml($data['site_name'] ?? 'Resmenu', 200);
        if ($siteName === '') $siteName = 'Resmenu';
        $siteLogo = !empty($data['site_logo']) ? $data['site_logo'] : null;
        $favicon = !empty($data['favicon']) ? $data['favicon'] : null;
        $contactSalesEmail = sanitizeEmail($data['contact_sales_email'] ?? '');
        $contactSalesPhone = sanitizeForHtml($data['contact_sales_phone'] ?? '', 30);
        $contactSupportEmail = sanitizeEmail($data['contact_support_email'] ?? '');
        $contactSupportPhone = sanitizeForHtml($data['contact_support_phone'] ?? '', 30);
        $contactPartnersEmail = sanitizeEmail($data['contact_partners_email'] ?? '');
        $contactFormRecipient = sanitizeEmail($data['contact_form_recipient'] ?? '');
        $contactHqTitle = sanitizeForHtml($data['contact_hq_title'] ?? '', 200);
        $contactHqAddress = sanitizeForHtml($data['contact_hq_address'] ?? '', 500);
        $contactMapEmbed = sanitizeForHtml($data['contact_map_embed'] ?? '', 2000);
        $contactSocialFacebook = sanitizeUrl($data['contact_social_facebook'] ?? '');
        $contactSocialTwitter = sanitizeUrl($data['contact_social_twitter'] ?? '');
        $contactSocialInstagram = sanitizeUrl($data['contact_social_instagram'] ?? '');

        $stmt = $pdo->prepare(
            "INSERT INTO site_settings (
                id, site_name, site_logo, favicon,
                contact_sales_email, contact_sales_phone,
                contact_support_email, contact_support_phone,
                contact_partners_email, contact_form_recipient,
                contact_hq_title, contact_hq_address, contact_map_embed,
                contact_social_facebook, contact_social_twitter, contact_social_instagram
            ) VALUES (
                1, ?, ?, ?,
                ?, ?,
                ?, ?,
                ?, ?,
                ?, ?, ?,
                ?, ?, ?
            )
            ON DUPLICATE KEY UPDATE
                site_name = VALUES(site_name),
                site_logo = VALUES(site_logo),
                favicon = VALUES(favicon),
                contact_sales_email = VALUES(contact_sales_email),
                contact_sales_phone = VALUES(contact_sales_phone),
                contact_support_email = VALUES(contact_support_email),
                contact_support_phone = VALUES(contact_support_phone),
                contact_partners_email = VALUES(contact_partners_email),
                contact_form_recipient = VALUES(contact_form_recipient),
                contact_hq_title = VALUES(contact_hq_title),
                contact_hq_address = VALUES(contact_hq_address),
                contact_map_embed = VALUES(contact_map_embed),
                contact_social_facebook = VALUES(contact_social_facebook),
                contact_social_twitter = VALUES(contact_social_twitter),
                contact_social_instagram = VALUES(contact_social_instagram)"
        );

        $stmt->execute([
            $siteName,
            $siteLogo,
            $favicon,
            $contactSalesEmail,
            $contactSalesPhone,
            $contactSupportEmail,
            $contactSupportPhone,
            $contactPartnersEmail,
            $contactFormRecipient,
            $contactHqTitle,
            $contactHqAddress,
            $contactMapEmbed,
            $contactSocialFacebook,
            $contactSocialTwitter,
            $contactSocialInstagram,
        ]);
        return true;
    } catch (PDOException $e) {
        error_log("updateSiteSettings: " . $e->getMessage());
        return false;
    }
}

/**
 * Get site-branded email template (for admin/test emails)
 * Uses admin dashboard colors: #1e3a5f, #0f172a
 *
 * @param string $title Email title
 * @param string $bodyContent HTML body content
 * @param array $siteSettings Optional - from getSiteSettings()
 * @return string Full HTML email
 */
function getSiteEmailTemplate($title, $bodyContent, $siteSettings = null) {
    if ($siteSettings === null) {
        $siteSettings = getSiteSettings();
    }
    $siteName = htmlspecialchars($siteSettings['site_name'] ?? 'Resmenu');
    $baseUrl = defined('SITE_URL') ? rtrim(SITE_URL, '/') : '';
    $uploadUrl = defined('UPLOAD_URL') ? rtrim(UPLOAD_URL, '/') : $baseUrl . '/uploads';
    $logoUrl = '';
    if (!empty($siteSettings['site_logo'])) {
        $logoUrl = $uploadUrl . '/site/' . $siteSettings['site_logo'];
    }

    $primary = '#1e3a5f';
    $primaryDark = '#0f172a';
    $headerBg = $primaryDark;
    $headerBorder = $primary;

    $headerContent = $logoUrl
        ? '<img src="' . htmlspecialchars($logoUrl) . '" alt="' . $siteName . '" style="max-height:52px;max-width:100%;display:block;margin:0 auto;">'
        : '<div style="display:flex;align-items:center;justify-content:center;gap:12px;"><div style="width:44px;height:44px;background:' . $primary . ';border-radius:50%;display:flex;align-items:center;justify-content:center;"><span style="color:#fff;font-size:20px;">&#9776;</span></div><h1 style="color:#fff;font-size:24px;font-weight:700;margin:0;font-family:Inter,sans-serif;">' . $siteName . '</h1></div>';

    return '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>' . htmlspecialchars($title) . '</title>
<style>
body{margin:0;padding:0;font-family:Inter,-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;background:#f8f5f5;}
</style>
</head>
<body style="margin:0;padding:0;font-family:Inter,-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,sans-serif;background:#f8f5f5;">
<div style="max-width:640px;margin:0 auto;padding:24px 16px;">
<div style="background:#fff;border-radius:12px;box-shadow:0 4px 20px rgba(0,0,0,0.08);overflow:hidden;border:1px solid #e5e7eb;">
<header style="background:' . $headerBg . ';padding:28px 24px;text-align:center;border-bottom:4px solid ' . $headerBorder . ';">
' . $headerContent . '
<p style="color:#9ca3af;font-size:11px;margin:8px 0 0;text-transform:uppercase;letter-spacing:0.1em;">Restaurant Menu Platform</p>
</header>
<section style="padding:32px 28px;color:#374151;line-height:1.6;font-size:15px;">
' . $bodyContent . '
</section>
<footer style="background:' . $headerBg . ';padding:24px 28px;text-align:center;">
<p style="color:#9ca3af;font-size:12px;margin:0 0 8px;">' . $siteName . '</p>
<p style="color:#9ca3af;font-size:11px;margin:0;"><a href="' . htmlspecialchars($baseUrl) . '" style="color:' . $primary . ';text-decoration:none;">Visit site</a> &bull; &copy; ' . date('Y') . ' ' . $siteName . '. All rights reserved.</p>
</footer>
</div>
</div>
</body>
</html>';
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
 * Get total tables configured for a date (from table_inventory_daily)
 * @param int $restaurantId
 * @param string $date Y-m-d format
 * @return int
 */
function getTotalTablesForDate($restaurantId, $date) {
    $pdo = getDBConnection();
    if (!$pdo) return 10;
    try {
        $stmt = $pdo->prepare("SELECT total_tables FROM table_inventory_daily WHERE restaurant_id = ? AND inventory_date = ?");
        $stmt->execute([$restaurantId, $date]);
        $row = $stmt->fetch();
        return $row ? max(1, (int)$row['total_tables']) : 10;
    } catch (PDOException $e) {
        return 10;
    }
}

/**
 * Get table availability breakdown for a date
 * Available = Total - (Confirmed + Pending + Walk-ins). Cancelled/rejected do NOT reduce availability.
 *
 * @param int $restaurantId
 * @param string $date Y-m-d format
 * @return array ['total','confirmed','pending','walkins','cancelled','available']
 */
function getTableAvailabilityForDate($restaurantId, $date) {
    $pdo = getDBConnection();
    if (!$pdo) {
        return ['total' => 10, 'confirmed' => 0, 'pending' => 0, 'walkins' => 0, 'cancelled' => 0, 'available' => 10];
    }
    $total = getTotalTablesForDate($restaurantId, $date);

    try {
        $stmt = $pdo->prepare("
            SELECT
                SUM(CASE WHEN status = 'confirmed' AND COALESCE(is_walkin, 0) = 0 THEN 1 ELSE 0 END) AS confirmed,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending,
                SUM(CASE WHEN COALESCE(is_walkin, 0) = 1 THEN 1 ELSE 0 END) AS walkins,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled
            FROM table_reservations
            WHERE restaurant_id = ? AND reservation_date = ?
        ");
        $stmt->execute([$restaurantId, $date]);
    } catch (PDOException $e) {
        error_log("getTableAvailabilityForDate: " . $e->getMessage());
        $stmt = $pdo->prepare("
            SELECT
                SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) AS confirmed,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending,
                0 AS walkins,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled
            FROM table_reservations
            WHERE restaurant_id = ? AND reservation_date = ?
        ");
        $stmt->execute([$restaurantId, $date]);
    }
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $confirmed = (int)($row['confirmed'] ?? 0);
    $pending = (int)($row['pending'] ?? 0);
    $walkins = (int)($row['walkins'] ?? 0);
    $cancelled = (int)($row['cancelled'] ?? 0);
    $booked = $confirmed + $pending + $walkins;
    $available = max(0, $total - $booked);

    return [
        'total' => $total,
        'confirmed' => $confirmed,
        'pending' => $pending,
        'walkins' => $walkins,
        'cancelled' => $cancelled,
        'available' => $available,
    ];
}

/**
 * Get availability for each date in a range (for calendar APIs)
 * @param int $restaurantId
 * @param string $startDate Y-m-d
 * @param string $endDate Y-m-d
 * @return array ['YYYY-MM-DD' => ['total',...,'available'], ...]
 */
function getDateAvailabilityRange($restaurantId, $startDate, $endDate) {
    $result = [];
    $start = strtotime($startDate);
    $end = strtotime($endDate);
    if ($start === false || $end === false || $start > $end) return $result;
    for ($t = $start; $t <= $end; $t += 86400) {
        $d = date('Y-m-d', $t);
        $result[$d] = getTableAvailabilityForDate($restaurantId, $d);
    }
    return $result;
}

/**
 * Get available time slots for table reservations
 * Uses day-level inventory: slot available if (a) not past and (b) tables left for day > 0.
 *
 * @param int $restaurantId
 * @param string $date Y-m-d format
 * @param int $maxPerSlot Max reservations per slot (legacy, used only when inventory not in use)
 * @return array [['time' => '17:30', 'available' => true, 'count' => N], ...] plus day-level 'tables_left'
 */
function getAvailableTimeSlots($restaurantId, $date, $maxPerSlot = 5) {
    $pdo = getDBConnection();
    if (!$pdo) return [];

    $availability = getTableAvailabilityForDate($restaurantId, $date);
    $tablesLeft = $availability['available'];

    $open = '17:00';
    $close = '23:00';
    $interval = 30; // minutes

    $slots = [];
    $start = strtotime($date . ' ' . $open);
    $end = strtotime($date . ' ' . $close);
    $now = time();

    for ($t = $start; $t < $end; $t += $interval * 60) {
        $timeStr = date('H:i', $t);
        $slotDateTime = strtotime($date . ' ' . $timeStr);
        $isPast = ($slotDateTime < $now);
        $available = !$isPast && $tablesLeft > 0;

        $slots[] = [
            'time' => $timeStr,
            'available' => $available,
            'count' => 0,
        ];
    }

    return $slots;
}

/**
 * Get reservation settings for restaurant (deposit amount)
 * @param int $restaurantId
 * @return array
 */
function getReservationSettings($restaurantId) {
    $pdo = getDBConnection();
    if (!$pdo) return ['deposit_amount' => 0];

    try {
        $stmt = $pdo->prepare("SELECT * FROM restaurant_reservation_settings WHERE restaurant_id = ?");
        $stmt->execute([$restaurantId]);
        $row = $stmt->fetch();
        return $row ?: ['deposit_amount' => 0];
    } catch (PDOException $e) {
        error_log("getReservationSettings: " . $e->getMessage());
        return ['deposit_amount' => 0];
    }
}

/**
 * Ensure Template 4 (hotel) restaurants have reservation settings so they redirect to checkout.
 * Auto-creates a row with default deposit when none exists.
 * @param int $restaurantId
 * @param int $templateId
 * @param float $defaultDeposit Default 5000
 */
function ensureHotelReservationSettings($restaurantId, $templateId, $defaultDeposit = 5000) {
    if ((int)$templateId !== 4 || $defaultDeposit <= 0) return;
    $pdo = getDBConnection();
    if (!$pdo) return;
    try {
        $stmt = $pdo->prepare("SELECT id FROM restaurant_reservation_settings WHERE restaurant_id = ?");
        $stmt->execute([$restaurantId]);
        if ($stmt->fetch()) return;
        $stmt = $pdo->prepare("INSERT INTO restaurant_reservation_settings (restaurant_id, deposit_amount) VALUES (?, ?)");
        $stmt->execute([$restaurantId, $defaultDeposit]);
    } catch (PDOException $e) {
        error_log("ensureHotelReservationSettings: " . $e->getMessage());
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

