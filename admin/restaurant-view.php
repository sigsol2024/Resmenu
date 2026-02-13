<?php
/**
 * Admin Restaurant View Page
 * Comprehensive management interface for a specific restaurant
 */

require_once __DIR__ . '/../includes/auth.php';
requireSuperAdmin();

require_once __DIR__ . '/../includes/functions.php';

$pdo = getDBConnection();
$message = '';
$error = '';

// Get restaurant slug from URL
$restaurantSlug = $_GET['slug'] ?? '';

if (empty($restaurantSlug)) {
    header('Location: dashboard.php');
    exit;
}

// Get restaurant data
$restaurant = getRestaurantBySlug($restaurantSlug);

if (!$restaurant) {
    die('Restaurant not found.');
}

$restaurantId = $restaurant['id'];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // #region agent log
    $logDir = __DIR__ . '/../.cursor';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    @file_put_contents($logDir . '/debug.log', json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'E','location'=>'admin/restaurant-view.php:34','message'=>'Form submission received','data'=>['restaurantId'=>$restaurantId,'restaurantSlug'=>$restaurantSlug,'restaurantExists'=>($restaurant !== null)],'timestamp'=>time()*1000]) . "\n", FILE_APPEND);
    // #endregion
    
    // Re-validate restaurant exists before processing form
    $restaurantCheck = getRestaurantBySlug($restaurantSlug);
    if (!$restaurantCheck) {
        $error = 'Restaurant no longer exists';
    } else {
        $action = $_POST['action'] ?? '';
        
        // #region agent log
        $logDir = __DIR__ . '/../.cursor';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        @file_put_contents($logDir . '/debug.log', json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'E','location'=>'admin/restaurant-view.php:42','message'=>'Restaurant validation passed','data'=>['restaurantId'=>$restaurantId,'action'=>$action],'timestamp'=>time()*1000]) . "\n", FILE_APPEND);
        // #endregion
        
        // Handle template selection
        if ($action === 'save_template') {
            $templateId = intval($_POST['template_id'] ?? 1);
            try {
                $stmt = $pdo->prepare("UPDATE restaurants SET template_id = ? WHERE id = ?");
                $stmt->execute([$templateId, $restaurantId]);
                $message = 'Template updated successfully';
                // Refresh restaurant data
                $restaurant = getRestaurantBySlug($restaurantSlug);
            } catch (PDOException $e) {
                $error = 'Error updating template: ' . $e->getMessage();
            }
        }
        
        // Handle customization save
        if ($action === 'save_customization') {
        $customizationData = [
            'menu_title_color' => sanitize($_POST['menu_title_color'] ?? '#000000'),
            'menu_title_size' => intval($_POST['menu_title_size'] ?? 24),
            'menu_title_font' => sanitize($_POST['menu_title_font'] ?? 'Inter'),
            'price_color' => sanitize($_POST['price_color'] ?? '#000000'),
            'price_size' => intval($_POST['price_size'] ?? 18),
            'price_font' => sanitize($_POST['price_font'] ?? 'Inter'),
            'description_color' => sanitize($_POST['description_color'] ?? '#666666'),
            'description_size' => intval($_POST['description_size'] ?? 14),
            'description_font' => sanitize($_POST['description_font'] ?? 'Inter'),
            'category_title_color' => sanitize($_POST['category_title_color'] ?? '#000000'),
            'category_title_size' => intval($_POST['category_title_size'] ?? 20),
            'category_title_font' => sanitize($_POST['category_title_font'] ?? 'Inter'),
            'background_color' => sanitize($_POST['background_color'] ?? '#FFFFFF'),
            'header_background_color' => sanitize($_POST['header_background_color'] ?? '#FFFFFF'),
            'primary_color' => sanitize($_POST['primary_color'] ?? '#111111'),
            'secondary_color' => sanitize($_POST['secondary_color'] ?? '#FFFFFF'),
        ];
        
        $templateId = (int)($_POST['template_id'] ?? $restaurant['template_id'] ?? 1);
        try {
            $stmt = $pdo->prepare("UPDATE customization_settings SET menu_title_color = ?, menu_title_size = ?, menu_title_font = ?, price_color = ?, price_size = ?, price_font = ?, description_color = ?, description_size = ?, description_font = ?, category_title_color = ?, category_title_size = ?, category_title_font = ?, background_color = ?, header_background_color = ?, primary_color = ?, secondary_color = ? WHERE restaurant_id = ? AND template_id = ?");
            $stmt->execute([
                $customizationData['menu_title_color'], $customizationData['menu_title_size'], $customizationData['menu_title_font'],
                $customizationData['price_color'], $customizationData['price_size'], $customizationData['price_font'],
                $customizationData['description_color'], $customizationData['description_size'], $customizationData['description_font'],
                $customizationData['category_title_color'], $customizationData['category_title_size'], $customizationData['category_title_font'],
                $customizationData['background_color'], $customizationData['header_background_color'],
                $customizationData['primary_color'], $customizationData['secondary_color'],
                $restaurantId, $templateId
            ]);
            if ($stmt->rowCount() === 0) {
                $stmt = $pdo->prepare("INSERT INTO customization_settings (restaurant_id, template_id, menu_title_color, menu_title_size, menu_title_font, price_color, price_size, price_font, description_color, description_size, description_font, category_title_color, category_title_size, category_title_font, background_color, header_background_color, primary_color, secondary_color) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $restaurantId, $templateId,
                    $customizationData['menu_title_color'], $customizationData['menu_title_size'], $customizationData['menu_title_font'],
                    $customizationData['price_color'], $customizationData['price_size'], $customizationData['price_font'],
                    $customizationData['description_color'], $customizationData['description_size'], $customizationData['description_font'],
                    $customizationData['category_title_color'], $customizationData['category_title_size'], $customizationData['category_title_font'],
                    $customizationData['background_color'], $customizationData['header_background_color'],
                    $customizationData['primary_color'], $customizationData['secondary_color']
                ]);
            }
            $message = 'Customization settings saved successfully';
        } catch (PDOException $e) {
            $error = 'Error saving customization: ' . $e->getMessage();
        }
    }
    
    // Handle header/footer save
    if ($action === 'save_header_footer') {
        $headerMenuItems = sanitize($_POST['header_menu_items'] ?? '[]');
        $footerContent = sanitize($_POST['footer_content'] ?? '');
        $instagramUrl = sanitize($_POST['instagram_url'] ?? '');
        $facebookUrl = sanitize($_POST['facebook_url'] ?? '');
        $twitterUrl = sanitize($_POST['twitter_url'] ?? '');
        $whatsappLink = sanitize($_POST['whatsapp_link'] ?? '');
        $mapLatitude = !empty($_POST['map_latitude']) ? floatval($_POST['map_latitude']) : null;
        $mapLongitude = !empty($_POST['map_longitude']) ? floatval($_POST['map_longitude']) : null;
        
        // Validate JSON
        $decoded = json_decode($headerMenuItems, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $error = 'Invalid JSON format for header menu items';
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE restaurants SET header_menu_items = ?, footer_content = ?, instagram_url = ?, facebook_url = ?, twitter_url = ?, whatsapp_link = ?, map_latitude = ?, map_longitude = ? WHERE id = ?");
                $stmt->execute([$headerMenuItems, $footerContent, $instagramUrl, $facebookUrl, $twitterUrl, $whatsappLink, $mapLatitude, $mapLongitude, $restaurantId]);
                $message = 'Header and footer settings saved successfully';
            } catch (PDOException $e) {
                $error = 'Error saving header/footer: ' . $e->getMessage();
            }
        }
    }
    
    // Handle menu item operations
    if ($action === 'create_menu_item' || $action === 'update_menu_item') {
        require_once __DIR__ . '/../includes/functions.php';
        
        $name = sanitize($_POST['name'] ?? '');
        $slug = sanitize($_POST['slug'] ?? '');
        $category_id = intval($_POST['category_id'] ?? 0);
        $description = sanitize($_POST['description'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $display_order = intval($_POST['display_order'] ?? 0);
        $is_available = isset($_POST['is_available']) ? 1 : 0;
        
        if (empty($name) || empty($slug) || $category_id === 0 || $price <= 0) {
            $error = 'Name, slug, category, and price are required';
        } else {
            // Validate that category belongs to this restaurant
            $categoryCheck = null;
            if ($pdo && $restaurantId) {
                $checkStmt = $pdo->prepare("SELECT id FROM categories WHERE id = ? AND restaurant_id = ?");
                $checkStmt->execute([$category_id, $restaurantId]);
                $categoryCheck = $checkStmt->fetch();
            }
            
            if (!$categoryCheck && $category_id > 0) {
                $error = 'Category does not belong to this restaurant';
            } else {
                try {
                    $image = null;
                    
                    // Handle image upload
                    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                        $uploadResult = uploadFile($_FILES['image'], UPLOAD_PATH . '/menu-items');
                        if ($uploadResult['success']) {
                            $image = $uploadResult['filename'];
                            
                            // Delete old image if updating
                            if ($action === 'update_menu_item' && isset($_POST['id'])) {
                                $stmt = $pdo->prepare("SELECT image FROM menu_items WHERE id = ? AND restaurant_id = ?");
                                $stmt->execute([$_POST['id'], $restaurantId]);
                                $oldItem = $stmt->fetch();
                                if ($oldItem && $oldItem['image']) {
                                    deleteFile(UPLOAD_PATH . '/menu-items/' . $oldItem['image']);
                                }
                            }
                        } else {
                            $error = $uploadResult['message'];
                        }
                    } else {
                        // Keep existing image if updating and no new file uploaded
                        if ($action === 'update_menu_item' && isset($_POST['id'])) {
                            $stmt = $pdo->prepare("SELECT image FROM menu_items WHERE id = ? AND restaurant_id = ?");
                            $stmt->execute([$_POST['id'], $restaurantId]);
                            $oldItem = $stmt->fetch();
                            $image = $oldItem['image'] ?? null;
                        }
                    }
                    
                    if (!$error) {
                        if ($action === 'create_menu_item') {
                            $stmt = $pdo->prepare("INSERT INTO menu_items (restaurant_id, category_id, name, slug, description, price, image, display_order, is_available) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                            $stmt->execute([$restaurantId, $category_id, $name, $slug, $description, $price, $image, $display_order, $is_available]);
                            
                            updateRestaurantStats($restaurantId);
                            
                            header('Location: restaurant-view.php?slug=' . urlencode($restaurantSlug) . '&tab=menu&success=created');
                            exit;
                        } else {
                            $id = intval($_POST['id'] ?? 0);
                            if ($id > 0) {
                                if ($image) {
                                    $stmt = $pdo->prepare("UPDATE menu_items SET category_id = ?, name = ?, slug = ?, description = ?, price = ?, image = ?, display_order = ?, is_available = ? WHERE id = ? AND restaurant_id = ?");
                                    $stmt->execute([$category_id, $name, $slug, $description, $price, $image, $display_order, $is_available, $id, $restaurantId]);
                                } else {
                                    $stmt = $pdo->prepare("UPDATE menu_items SET category_id = ?, name = ?, slug = ?, description = ?, price = ?, display_order = ?, is_available = ? WHERE id = ? AND restaurant_id = ?");
                                    $stmt->execute([$category_id, $name, $slug, $description, $price, $display_order, $is_available, $id, $restaurantId]);
                                }
                                
                                updateRestaurantStats($restaurantId);
                                
                                header('Location: restaurant-view.php?slug=' . urlencode($restaurantSlug) . '&tab=menu&success=updated');
                                exit;
                            } else {
                                $error = 'Invalid menu item ID';
                            }
                        }
                    }
                } catch (PDOException $e) {
                    error_log("Menu item error: " . $e->getMessage());
                    $error = 'Error saving menu item: ' . $e->getMessage();
                }
            }
        }
    }
    
    // Handle menu item delete
    if ($action === 'delete_menu_item') {
        require_once __DIR__ . '/../includes/functions.php';
        
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0 && $restaurantId) {
            try {
                // Get menu item data before deletion
                $stmt = $pdo->prepare("SELECT image FROM menu_items WHERE id = ? AND restaurant_id = ?");
                $stmt->execute([$id, $restaurantId]);
                $menuItem = $stmt->fetch();
                
                if ($menuItem) {
                    // Delete menu item
                    $pdo->prepare("DELETE FROM menu_items WHERE id = ? AND restaurant_id = ?")->execute([$id, $restaurantId]);
                    
                    // Delete uploaded file
                    if ($menuItem['image']) {
                        deleteFile(UPLOAD_PATH . '/menu-items/' . $menuItem['image']);
                    }
                    
                    updateRestaurantStats($restaurantId);
                    
                    header('Location: restaurant-view.php?slug=' . urlencode($restaurantSlug) . '&tab=menu&success=deleted');
                    exit;
                } else {
                    $error = 'Menu item not found';
                }
            } catch (PDOException $e) {
                $error = 'Error deleting menu item: ' . $e->getMessage();
            }
        }
    }
    
    // Handle category operations
    if ($action === 'create_category' || $action === 'update_category') {
        require_once __DIR__ . '/../includes/functions.php';
        
        $name = sanitize($_POST['name'] ?? '');
        $slug = sanitize($_POST['slug'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $display_order = intval($_POST['display_order'] ?? 0);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        if (empty($name) || empty($slug)) {
            $error = 'Name and slug are required';
        } else {
            try {
                $image = null;
                
                // Handle image upload
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $uploadResult = uploadFile($_FILES['image'], UPLOAD_PATH . '/categories');
                    if ($uploadResult['success']) {
                        $image = $uploadResult['filename'];
                        
                        // Delete old image if updating
                        if ($action === 'update_category' && isset($_POST['id'])) {
                            $stmt = $pdo->prepare("SELECT image FROM categories WHERE id = ? AND restaurant_id = ?");
                            $stmt->execute([$_POST['id'], $restaurantId]);
                            $oldCategory = $stmt->fetch();
                            if ($oldCategory && $oldCategory['image']) {
                                deleteFile(UPLOAD_PATH . '/categories/' . $oldCategory['image']);
                            }
                        }
                    } else {
                        $error = $uploadResult['message'];
                    }
                } else {
                    // Keep existing image if updating and no new file uploaded
                    if ($action === 'update_category' && isset($_POST['id'])) {
                        $stmt = $pdo->prepare("SELECT image FROM categories WHERE id = ? AND restaurant_id = ?");
                        $stmt->execute([$_POST['id'], $restaurantId]);
                        $oldCategory = $stmt->fetch();
                        $image = $oldCategory['image'] ?? null;
                    }
                }
                
                if (!$error) {
                    if ($action === 'create_category') {
                        $stmt = $pdo->prepare("INSERT INTO categories (restaurant_id, name, slug, description, image, display_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$restaurantId, $name, $slug, $description, $image, $display_order, $is_active]);
                        
                        header('Location: restaurant-view.php?slug=' . urlencode($restaurantSlug) . '&tab=categories&success=category_created');
                        exit;
                    } else {
                        $id = intval($_POST['id'] ?? 0);
                        if ($id > 0) {
                            if ($image) {
                                $stmt = $pdo->prepare("UPDATE categories SET name = ?, slug = ?, description = ?, image = ?, display_order = ?, is_active = ? WHERE id = ? AND restaurant_id = ?");
                                $stmt->execute([$name, $slug, $description, $image, $display_order, $is_active, $id, $restaurantId]);
                            } else {
                                $stmt = $pdo->prepare("UPDATE categories SET name = ?, slug = ?, description = ?, display_order = ?, is_active = ? WHERE id = ? AND restaurant_id = ?");
                                $stmt->execute([$name, $slug, $description, $display_order, $is_active, $id, $restaurantId]);
                            }
                            
                            header('Location: restaurant-view.php?slug=' . urlencode($restaurantSlug) . '&tab=categories&success=category_updated');
                            exit;
                        } else {
                            $error = 'Invalid category ID';
                        }
                    }
                }
            } catch (PDOException $e) {
                error_log("Category error: " . $e->getMessage());
                $error = 'Error saving category: ' . $e->getMessage();
            }
        }
    }
    
    // Handle category delete
    if ($action === 'delete_category') {
        require_once __DIR__ . '/../includes/functions.php';
        
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0 && $restaurantId) {
            try {
                // Get category data before deletion
                $stmt = $pdo->prepare("SELECT image FROM categories WHERE id = ? AND restaurant_id = ?");
                $stmt->execute([$id, $restaurantId]);
                $category = $stmt->fetch();
                
                if ($category) {
                    // Delete associated menu items
                    $pdo->prepare("DELETE FROM menu_items WHERE category_id = ? AND restaurant_id = ?")->execute([$id, $restaurantId]);
                    // Delete category
                    $pdo->prepare("DELETE FROM categories WHERE id = ? AND restaurant_id = ?")->execute([$id, $restaurantId]);
                    
                    // Delete uploaded file
                    if ($category['image']) {
                        deleteFile(UPLOAD_PATH . '/categories/' . $category['image']);
                    }
                    
                    updateRestaurantStats($restaurantId);
                    
                    header('Location: restaurant-view.php?slug=' . urlencode($restaurantSlug) . '&tab=categories&success=category_deleted');
                    exit;
                } else {
                    $error = 'Category not found';
                }
            } catch (PDOException $e) {
                $error = 'Error deleting category: ' . $e->getMessage();
            }
        }
    }
    }
}

// Get menu item for editing
$editMenuItem = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit_menu_item' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE id = ? AND restaurant_id = ?");
    $stmt->execute([$_GET['id'], $restaurantId]);
    $editMenuItem = $stmt->fetch();
}

// Get category for editing
$editCategory = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit_category' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ? AND restaurant_id = ?");
    $stmt->execute([$_GET['id'], $restaurantId]);
    $editCategory = $stmt->fetch();
}

// Get all menu items for this restaurant
$allMenuItems = [];
if ($pdo) {
    $stmt = $pdo->prepare("SELECT mi.*, c.name as category_name FROM menu_items mi LEFT JOIN categories c ON mi.category_id = c.id WHERE mi.restaurant_id = ? ORDER BY c.display_order ASC, mi.display_order ASC");
    $stmt->execute([$restaurantId]);
    $allMenuItems = $stmt->fetchAll();
}

// Get all categories (admin can see all, not just active)
$categories = [];
if ($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE restaurant_id = ? ORDER BY display_order ASC, name ASC");
    $stmt->execute([$restaurantId]);
    $categories = $stmt->fetchAll();
}

// Get customization settings (per-template - uses restaurant's current template)
$customization = getCustomizationSettings($restaurantId, $restaurant['template_id'] ?? 1);

// Parse header menu items
$headerMenuItems = [];
if (!empty($restaurant['header_menu_items'])) {
    $decoded = json_decode($restaurant['header_menu_items'], true);
    if (is_array($decoded)) {
        $headerMenuItems = $decoded;
    }
}

// Get active tab
$activeTab = $_GET['tab'] ?? 'menu';

// Handle success messages
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'created':
            $message = 'Menu item created successfully';
            break;
        case 'updated':
            $message = 'Menu item updated successfully';
            break;
        case 'deleted':
            $message = 'Menu item deleted successfully';
            break;
        case 'category_created':
            $message = 'Category created successfully';
            break;
        case 'category_updated':
            $message = 'Category updated successfully';
            break;
        case 'category_deleted':
            $message = 'Category and all associated menu items deleted successfully';
            break;
    }
}

$pageTitle = 'Manage: ' . htmlspecialchars($restaurant['name']);
$showBackToDashboard = true;
include __DIR__ . '/../includes/admin-layout.php';
?>

<style>
/* Clean Restaurant View Styles */
.alert {
    padding: 12px 16px;
    border-radius: 6px;
    margin-bottom: 20px;
    font-size: 0.875rem;
}

.alert-success {
    background: #d1fae5;
    color: #065f46;
    border: 1px solid #a7f3d0;
}

.alert-error {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fecaca;
}

.card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    margin-bottom: 24px;
    overflow: hidden;
}

.card-header {
    padding: 20px 24px;
    border-bottom: 1px solid #e5e7eb;
}

.card-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #111827;
    margin: 0;
}

.tabs {
    display: flex;
    gap: 0;
    border-bottom: 1px solid #e5e7eb;
    background: #f9fafb;
    overflow-x: auto;
}

.tab {
    padding: 14px 20px;
    background: transparent;
    border: none;
    border-bottom: 2px solid transparent;
    cursor: pointer;
    font-size: 0.875rem;
    font-weight: 500;
    color: #6b7280;
    transition: all 0.2s;
    white-space: nowrap;
}

.tab:hover {
    background: #f3f4f6;
    color: #374151;
}

.tab.active {
    color: #111827;
    border-bottom-color: #111827;
    background: #fff;
}

.tab-content {
    display: none;
    padding: 24px;
}

.tab-content.active {
    display: block;
}

.search-filter-bar {
    display: flex;
    gap: 12px;
    margin-bottom: 20px;
    flex-wrap: wrap;
    align-items: center;
}

.search-input {
    flex: 1;
    min-width: 200px;
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 0.875rem;
    transition: border-color 0.2s;
}

.search-input:focus {
    outline: none;
    border-color: #111827;
}

.category-filter {
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 0.875rem;
    background: white;
    transition: border-color 0.2s;
}

.category-filter:focus {
    outline: none;
    border-color: #111827;
}

.btn {
    padding: 10px 20px;
    border-radius: 6px;
    font-weight: 500;
    font-size: 0.875rem;
    cursor: pointer;
    border: none;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: background 0.2s;
}

.btn svg {
    width: 16px;
    height: 16px;
    flex-shrink: 0;
}

.btn-small svg {
    width: 14px;
    height: 14px;
}

.btn-primary {
    background: #111827;
    color: #fff;
}

.btn-primary:hover {
    background: #374151;
}

.btn-secondary {
    background: #f3f4f6;
    color: #374151;
}

.btn-secondary:hover {
    background: #e5e7eb;
}

.btn-danger {
    background: #dc2626;
    color: white;
}

.btn-danger:hover {
    background: #b91c1c;
}

.btn-small {
    padding: 6px 12px;
    font-size: 0.813rem;
}

.table {
    width: 100%;
    border-collapse: collapse;
}

.table th {
    background: #f9fafb;
    padding: 12px 16px;
    text-align: left;
    font-size: 0.75rem;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 1px solid #e5e7eb;
}

.table td {
    padding: 16px;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: middle;
    font-size: 0.875rem;
}

.table tr:last-child td {
    border-bottom: none;
}

.table tr:hover {
    background: #f9fafb;
}

.menu-item-image {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 6px;
}

.json-editor {
    width: 100%;
    min-height: 200px;
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-family: monospace;
    font-size: 0.875rem;
    transition: border-color 0.2s;
}

.json-editor:focus {
    outline: none;
    border-color: #111827;
}

.color-input-group {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 16px;
    margin-bottom: 20px;
}

.form-group-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.form-group {
    margin-bottom: 16px;
}

.form-group:last-child {
    margin-bottom: 0;
}

.form-label {
    display: block;
    font-weight: 500;
    color: #374151;
    margin-bottom: 6px;
    font-size: 0.875rem;
}

.form-input,
.form-textarea,
.form-select {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 0.875rem;
    transition: border-color 0.2s;
    background: #fff;
    font-family: inherit;
}

.form-input:focus,
.form-textarea:focus,
.form-select:focus {
    outline: none;
    border-color: #111827;
}

.form-textarea {
    resize: vertical;
    min-height: 80px;
}

/* Modal Styles */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 3000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 3001;
}

.modal-content {
    position: relative;
    background: white;
    border-radius: 8px;
    max-width: 700px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    z-index: 3002;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    border-bottom: 1px solid #e5e7eb;
}

.modal-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #111827;
    margin: 0;
}

.modal-close {
    background: none;
    border: none;
    font-size: 24px;
    color: #6b7280;
    cursor: pointer;
    padding: 0;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
    transition: color 0.2s;
}

.modal-close:hover {
    color: #111827;
}

.modal-body {
    padding: 24px;
}

.modal-footer {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    padding: 20px 24px;
    border-top: 1px solid #e5e7eb;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .tabs {
        flex-direction: column;
    }
    
    .tab {
        border-bottom: 1px solid #e5e7eb;
        border-right: none;
    }
    
    .tab.active {
        border-bottom-color: #e5e7eb;
        border-left: 2px solid #111827;
    }
    
    .tab-content {
        padding: 20px 16px;
    }
    
    .search-filter-bar {
        flex-direction: column;
    }
    
    .search-input,
    .category-filter {
        width: 100%;
    }
    
    .form-group-row {
        grid-template-columns: 1fr;
    }
    
    .color-input-group {
        grid-template-columns: 1fr;
    }
    
    .modal-content {
        width: 95%;
        max-height: 95vh;
    }
    
    .modal-footer {
        flex-direction: column;
    }
    
    .modal-footer .btn {
        width: 100%;
        justify-content: center;
    }
}
</style>
        
<?php if ($message): ?>
    <div class="alert alert-success">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-error">
        <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

        <div class="card">
            <div class="tabs">
                <button class="tab <?php echo $activeTab === 'menu' ? 'active' : ''; ?>" onclick="showTab('menu')">Menu Items</button>
                <button class="tab <?php echo $activeTab === 'categories' ? 'active' : ''; ?>" onclick="showTab('categories')">Categories</button>
                <button class="tab <?php echo $activeTab === 'customization' ? 'active' : ''; ?>" onclick="showTab('customization')">Customization</button>
                <button class="tab <?php echo $activeTab === 'header-footer' ? 'active' : ''; ?>" onclick="showTab('header-footer')">Header & Footer</button>
            </div>
            
            <!-- Menu Items Tab -->
            <div id="tab-menu" class="tab-content <?php echo $activeTab === 'menu' ? 'active' : ''; ?>">
                <div class="search-filter-bar">
                    <input type="text" id="menuSearch" class="search-input" placeholder="Search menu items...">
                    <select id="categoryFilter" class="category-filter">
                        <option value="all">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button onclick="openMenuItemModal()" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Menu Item
                    </button>
                </div>
                
                <table class="table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="menuItemsTableBody">
                    <?php foreach ($allMenuItems as $item): ?>
                        <tr data-category-id="<?php echo $item['category_id']; ?>" data-item-name="<?php echo strtolower(htmlspecialchars($item['name'])); ?>">
                            <td>
                                <?php if ($item['image']): ?>
                                    <img src="<?php echo UPLOAD_URL . '/menu-items/' . htmlspecialchars($item['image']); ?>" alt="" class="menu-item-image">
                                <?php else: ?>
                                    <div class="menu-item-image" style="background: #f0f0f0; display: flex; align-items: center; justify-content: center; color: #999;">No Image</div>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td><?php echo htmlspecialchars($item['category_name'] ?? 'Uncategorized'); ?></td>
                            <td><?php echo formatPrice($item['price']); ?></td>
                            <td class="actions-cell">
                                <button class="actions-btn" onclick="toggleDropdown(this)" title="Actions">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                    </svg>
                                </button>
                                <div class="actions-dropdown">
                                    <button type="button" onclick="openMenuItemModal(<?php echo $item['id']; ?>)" class="actions-dropdown-item">Edit</button>
                                    <div class="actions-dropdown-divider"></div>
                                    <button type="button" onclick="openDeleteMenuItemModal(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars(addslashes($item['name'])); ?>')" class="actions-dropdown-item danger">Delete</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
            <!-- Categories Tab -->
            <div id="tab-categories" class="tab-content <?php echo $activeTab === 'categories' ? 'active' : ''; ?>">
                <button onclick="openCategoryModal()" class="btn btn-primary" style="margin-bottom: 20px;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Category
                </button>
                
                <table class="table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Order</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td>
                                <?php if ($cat['image']): ?>
                                    <img src="<?php echo UPLOAD_URL . '/categories/' . htmlspecialchars($cat['image']); ?>" alt="" class="menu-item-image">
                                <?php else: ?>
                                    <div class="menu-item-image" style="background: #f0f0f0; display: flex; align-items: center; justify-content: center; color: #999;">No Image</div>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($cat['name']); ?></td>
                            <td><?php echo $cat['display_order']; ?></td>
                            <td><?php echo $cat['is_active'] ? 'Active' : 'Inactive'; ?></td>
                            <td class="actions-cell">
                                <button class="actions-btn" onclick="toggleDropdown(this)" title="Actions">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                    </svg>
                                </button>
                                <div class="actions-dropdown">
                                    <button type="button" onclick="openCategoryModal(<?php echo $cat['id']; ?>)" class="actions-dropdown-item">Edit</button>
                                    <div class="actions-dropdown-divider"></div>
                                    <button type="button" onclick="openDeleteCategoryModal(<?php echo $cat['id']; ?>, '<?php echo htmlspecialchars(addslashes($cat['name'])); ?>')" class="actions-dropdown-item danger">Delete</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
            
            <!-- Customization Tab -->
            <div id="tab-customization" class="tab-content <?php echo $activeTab === 'customization' ? 'active' : ''; ?>">
                <?php
                require_once __DIR__ . '/../includes/template-loader.php';
                $availableTemplates = getAvailableTemplates();
                $currentTemplateId = $restaurant['template_id'] ?? 1;
                ?>
                
                <!-- Template Selection -->
                <div class="card" style="margin-bottom: 24px;">
                    <div class="card-header">
                        <h2 class="card-title">Template Selection</h2>
                    </div>
                    <p style="margin-bottom: 20px; color: var(--muted);">Choose a design template for this restaurant's menu page.</p>
                    <form method="POST" action="" style="margin-bottom: 20px;">
                        <input type="hidden" name="action" value="save_template">
                        <div class="form-group">
                            <label class="form-label">Select Template</label>
                            <select name="template_id" class="form-select">
                                <?php foreach ($availableTemplates as $template): ?>
                                    <option value="<?php echo $template['id']; ?>" <?php echo $currentTemplateId == $template['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($template['name']); ?>
                                        <?php if ($template['description']): ?>
                                            - <?php echo htmlspecialchars($template['description']); ?>
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary" style="margin-top: 15px;">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Save Template
                        </button>
                    </form>
                    <p style="color: var(--muted); font-size: 14px;">
                        <strong>Current Template:</strong> Template <?php echo $currentTemplateId; ?>
                        <?php 
                        $currentTemplate = array_filter($availableTemplates, function($t) use ($currentTemplateId) { return $t['id'] == $currentTemplateId; });
                        if (!empty($currentTemplate)) {
                            $currentTemplate = reset($currentTemplate);
                            echo ' - ' . htmlspecialchars($currentTemplate['name']);
                        }
                        ?>
                    </p>
                </div>
                
                <form method="POST" action="" style="margin-top: 24px;">
                    <input type="hidden" name="action" value="save_customization">
                    <input type="hidden" name="template_id" value="<?php echo (int)($restaurant['template_id'] ?? 1); ?>">
                    
                    <div class="card" style="margin-bottom: 24px;">
                        <div class="card-header">
                            <h2 class="card-title">Menu Title</h2>
                        </div>
                        <div class="color-input-group">
                            <div class="form-group">
                                <label class="form-label">Color</label>
                                <input type="color" name="menu_title_color" value="<?php echo htmlspecialchars($customization['menu_title_color'] ?? '#000000'); ?>" style="width: 60px; height: 40px; border: 2px solid #e5e7eb; border-radius: 8px; cursor: pointer;">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Size (px)</label>
                                <input type="number" name="menu_title_size" class="form-input" value="<?php echo htmlspecialchars($customization['menu_title_size'] ?? 24); ?>" min="12" max="72">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Font</label>
                                <select name="menu_title_font" class="form-select">
                                    <option value="Inter" <?php echo ($customization['menu_title_font'] ?? 'Inter') === 'Inter' ? 'selected' : ''; ?>>Inter</option>
                                    <option value="Poppins" <?php echo ($customization['menu_title_font'] ?? 'Inter') === 'Poppins' ? 'selected' : ''; ?>>Poppins</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card" style="margin-bottom: 24px;">
                        <div class="card-header">
                            <h2 class="card-title">Price</h2>
                        </div>
                        <div class="color-input-group">
                            <div class="form-group">
                                <label class="form-label">Color</label>
                                <input type="color" name="price_color" value="<?php echo htmlspecialchars($customization['price_color'] ?? '#000000'); ?>" style="width: 60px; height: 40px; border: 2px solid #e5e7eb; border-radius: 8px; cursor: pointer;">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Size (px)</label>
                                <input type="number" name="price_size" class="form-input" value="<?php echo htmlspecialchars($customization['price_size'] ?? 18); ?>" min="12" max="48">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Font</label>
                                <select name="price_font" class="form-select">
                                    <option value="Inter" <?php echo ($customization['price_font'] ?? 'Inter') === 'Inter' ? 'selected' : ''; ?>>Inter</option>
                                    <option value="Poppins" <?php echo ($customization['price_font'] ?? 'Inter') === 'Poppins' ? 'selected' : ''; ?>>Poppins</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card" style="margin-bottom: 24px;">
                        <div class="card-header">
                            <h2 class="card-title">Description</h2>
                        </div>
                        <div class="color-input-group">
                            <div class="form-group">
                                <label class="form-label">Color</label>
                                <input type="color" name="description_color" value="<?php echo htmlspecialchars($customization['description_color'] ?? '#666666'); ?>" style="width: 60px; height: 40px; border: 2px solid #e5e7eb; border-radius: 8px; cursor: pointer;">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Size (px)</label>
                                <input type="number" name="description_size" class="form-input" value="<?php echo htmlspecialchars($customization['description_size'] ?? 14); ?>" min="10" max="24">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Font</label>
                                <select name="description_font" class="form-select">
                                    <option value="Inter" <?php echo ($customization['description_font'] ?? 'Inter') === 'Inter' ? 'selected' : ''; ?>>Inter</option>
                                    <option value="Poppins" <?php echo ($customization['description_font'] ?? 'Inter') === 'Poppins' ? 'selected' : ''; ?>>Poppins</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card" style="margin-bottom: 24px;">
                        <div class="card-header">
                            <h2 class="card-title">Category Title</h2>
                        </div>
                        <div class="color-input-group">
                            <div class="form-group">
                                <label class="form-label">Color</label>
                                <input type="color" name="category_title_color" value="<?php echo htmlspecialchars($customization['category_title_color'] ?? '#000000'); ?>" style="width: 60px; height: 40px; border: 2px solid #e5e7eb; border-radius: 8px; cursor: pointer;">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Size (px)</label>
                                <input type="number" name="category_title_size" class="form-input" value="<?php echo htmlspecialchars($customization['category_title_size'] ?? 20); ?>" min="12" max="48">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Font</label>
                                <select name="category_title_font" class="form-select">
                                    <option value="Inter" <?php echo ($customization['category_title_font'] ?? 'Inter') === 'Inter' ? 'selected' : ''; ?>>Inter</option>
                                    <option value="Poppins" <?php echo ($customization['category_title_font'] ?? 'Inter') === 'Poppins' ? 'selected' : ''; ?>>Poppins</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card" style="margin-bottom: 24px;">
                        <div class="card-header">
                            <h2 class="card-title">Background Colors</h2>
                        </div>
                        <div class="color-input-group">
                            <div class="form-group">
                                <label class="form-label">Page Background</label>
                                <input type="color" name="background_color" value="<?php echo htmlspecialchars($customization['background_color'] ?? '#FFFFFF'); ?>" style="width: 60px; height: 40px; border: 2px solid #e5e7eb; border-radius: 8px; cursor: pointer;">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Header Background</label>
                                <input type="color" name="header_background_color" value="<?php echo htmlspecialchars($customization['header_background_color'] ?? '#FFFFFF'); ?>" style="width: 60px; height: 40px; border: 2px solid #e5e7eb; border-radius: 8px; cursor: pointer;">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Primary Color</label>
                                <input type="color" name="primary_color" value="<?php echo htmlspecialchars($customization['primary_color'] ?? '#111111'); ?>" style="width: 60px; height: 40px; border: 2px solid #e5e7eb; border-radius: 8px; cursor: pointer;">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Secondary Color</label>
                                <input type="color" name="secondary_color" value="<?php echo htmlspecialchars($customization['secondary_color'] ?? '#FFFFFF'); ?>" style="width: 60px; height: 40px; border: 2px solid #e5e7eb; border-radius: 8px; cursor: pointer;">
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Save Customization
                    </button>
                </form>
            </div>
            
            <!-- Header & Footer Tab -->
            <div id="tab-header-footer" class="tab-content <?php echo $activeTab === 'header-footer' ? 'active' : ''; ?>">
                <div class="card" style="margin-bottom: 24px; background: #f0f9ff; border-left: 4px solid #2563eb;">
                    <p style="margin: 0; color: #1e40af; font-size: 14px;">
                        <strong>Note:</strong> The Header Menu Items and Footer Content fields are available for future template customization. 
                        Currently, templates use category-based navigation. These fields will be used when templates are updated to support custom header/footer content.
                    </p>
                </div>
                
                <form method="POST" action="">
                    <input type="hidden" name="action" value="save_header_footer">
                    
                    <div class="card" style="margin-bottom: 24px;">
                        <div class="card-header">
                            <h2 class="card-title">Header Menu Items</h2>
                        </div>
                        <p style="margin-bottom: 10px; color: var(--muted);">Enter JSON array format: [{"label": "Home", "url": "/"}, {"label": "About", "url": "/about"}]</p>
                        <p style="margin-bottom: 10px; color: var(--muted); font-size: 13px;"><em>Note: This feature is reserved for future template updates.</em></p>
                        <textarea name="header_menu_items" class="json-editor"><?php echo htmlspecialchars($restaurant['header_menu_items'] ?? '[]'); ?></textarea>
                    </div>
                    
                    <div class="card" style="margin-bottom: 24px;">
                        <div class="card-header">
                            <h2 class="card-title">Map Coordinates</h2>
                        </div>
                        <div class="form-group-row">
                            <div class="form-group">
                                <label class="form-label">Latitude</label>
                                <input type="number" step="any" name="map_latitude" class="form-input" value="<?php echo htmlspecialchars($restaurant['map_latitude'] ?? ''); ?>" placeholder="e.g., 40.7128">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Longitude</label>
                                <input type="number" step="any" name="map_longitude" class="form-input" value="<?php echo htmlspecialchars($restaurant['map_longitude'] ?? ''); ?>" placeholder="e.g., -74.0060">
                            </div>
                        </div>
                    </div>
                    
                    <div class="card" style="margin-bottom: 24px;">
                        <div class="card-header">
                            <h2 class="card-title">Social Media Links</h2>
                        </div>
                        <p style="margin-bottom: 12px; color: var(--muted); font-size: 13px;">Only links with values will appear as icons in the menu footer.</p>
                        <div class="form-group-row">
                            <div class="form-group">
                                <label class="form-label">WhatsApp Link</label>
                                <input type="url" name="whatsapp_link" class="form-input" value="<?php echo htmlspecialchars($restaurant['whatsapp_link'] ?? ''); ?>" placeholder="https://wa.me/...">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Instagram URL</label>
                                <input type="url" name="instagram_url" class="form-input" value="<?php echo htmlspecialchars($restaurant['instagram_url'] ?? ''); ?>" placeholder="https://instagram.com/...">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Facebook URL</label>
                                <input type="url" name="facebook_url" class="form-input" value="<?php echo htmlspecialchars($restaurant['facebook_url'] ?? ''); ?>" placeholder="https://facebook.com/...">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Twitter URL</label>
                                <input type="url" name="twitter_url" class="form-input" value="<?php echo htmlspecialchars($restaurant['twitter_url'] ?? ''); ?>" placeholder="https://twitter.com/...">
                            </div>
                        </div>
                    </div>
                    
                    <div class="card" style="margin-bottom: 24px;">
                        <div class="card-header">
                            <h2 class="card-title">Footer Content</h2>
                        </div>
                        <p style="margin-bottom: 10px; color: var(--muted);">HTML content to display in footer</p>
                        <p style="margin-bottom: 10px; color: var(--muted); font-size: 13px;"><em>Note: This feature is reserved for future template updates.</em></p>
                        <textarea name="footer_content" class="json-editor" style="min-height: 150px;"><?php echo htmlspecialchars($restaurant['footer_content'] ?? ''); ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Save Header & Footer
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Menu Item Modal -->
        <div class="modal" id="menuItemModal" style="display: <?php echo $editMenuItem ? 'flex' : 'none'; ?>;">
            <div class="modal-overlay" onclick="closeMenuItemModal()"></div>
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title"><?php echo $editMenuItem ? 'Edit Menu Item' : 'Create New Menu Item'; ?></h2>
                    <button class="modal-close" onclick="closeMenuItemModal()" aria-label="Close">&times;</button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="<?php echo $editMenuItem ? 'update_menu_item' : 'create_menu_item'; ?>">
                        <?php if ($editMenuItem): ?>
                            <input type="hidden" name="id" value="<?php echo $editMenuItem['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label class="form-label" for="category_id">Category *</label>
                            <select id="category_id" name="category_id" class="form-select" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo ($editMenuItem && $editMenuItem['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="name">Item Name *</label>
                            <input type="text" id="name" name="name" class="form-input" required value="<?php echo htmlspecialchars($editMenuItem['name'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="slug">Slug *</label>
                            <input type="text" id="slug" name="slug" class="form-input" required value="<?php echo htmlspecialchars($editMenuItem['slug'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="description">Description</label>
                            <textarea id="description" name="description" class="form-textarea" rows="3"><?php echo htmlspecialchars($editMenuItem['description'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="image">Item Image</label>
                            <input type="file" id="image" name="image" class="form-input" accept="image/*">
                            <?php if ($editMenuItem && $editMenuItem['image']): ?>
                                <div style="margin-top: 10px;">
                                    <p style="margin-bottom: 5px; color: var(--muted);">Current image:</p>
                                    <img src="<?php echo UPLOAD_URL . '/menu-items/' . htmlspecialchars($editMenuItem['image']); ?>" alt="Current image" style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 2px solid #e5e7eb;">
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="price">Price *</label>
                            <input type="number" id="price" name="price" class="form-input" step="0.01" min="0" required value="<?php echo $editMenuItem['price'] ?? ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="display_order">Display Order</label>
                            <input type="number" id="display_order" name="display_order" class="form-input" value="<?php echo $editMenuItem['display_order'] ?? 0; ?>">
                        </div>
                        
                        <div class="form-group">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <input type="checkbox" id="is_available" name="is_available" style="width: 20px; height: 20px;" <?php echo ($editMenuItem['is_available'] ?? 1) ? 'checked' : ''; ?>>
                                <label class="form-label" for="is_available" style="margin: 0;">Available</label>
                            </div>
                        </div>
                        
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" onclick="closeMenuItemModal()">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <?php echo $editMenuItem ? 'Update Menu Item' : 'Create Menu Item'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Delete Menu Item Modal -->
        <div class="modal" id="deleteMenuItemModal" style="display: none;">
            <div class="modal-overlay" onclick="closeDeleteMenuItemModal()"></div>
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title">Delete Menu Item</h2>
                    <button class="modal-close" onclick="closeDeleteMenuItemModal()" aria-label="Close">&times;</button>
                </div>
                <div class="modal-body">
                    <p style="margin-bottom: 20px; font-size: 16px;" id="deleteMenuItemText">Are you sure you want to delete this menu item?</p>
                    <p style="margin-bottom: 20px; color: var(--danger); font-weight: 600;">This action cannot be undone.</p>
                    <form method="POST" action="" id="deleteMenuItemForm">
                        <input type="hidden" name="action" value="delete_menu_item">
                        <input type="hidden" name="id" id="deleteMenuItemId" value="">
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" onclick="closeDeleteMenuItemModal()">Cancel</button>
                            <button type="submit" class="btn btn-danger">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Yes, Delete Menu Item
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Category Modal -->
        <div class="modal" id="categoryModal" style="display: <?php echo $editCategory ? 'flex' : 'none'; ?>;">
            <div class="modal-overlay" onclick="closeCategoryModal()"></div>
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title"><?php echo $editCategory ? 'Edit Category' : 'Create New Category'; ?></h2>
                    <button class="modal-close" onclick="closeCategoryModal()" aria-label="Close">&times;</button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="<?php echo $editCategory ? 'update_category' : 'create_category'; ?>">
                        <?php if ($editCategory): ?>
                            <input type="hidden" name="id" value="<?php echo $editCategory['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label class="form-label" for="cat_name">Category Name *</label>
                            <input type="text" id="cat_name" name="name" class="form-input" required value="<?php echo htmlspecialchars($editCategory['name'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="cat_slug">Slug *</label>
                            <input type="text" id="cat_slug" name="slug" class="form-input" required value="<?php echo htmlspecialchars($editCategory['slug'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="cat_description">Description</label>
                            <textarea id="cat_description" name="description" class="form-textarea" rows="3"><?php echo htmlspecialchars($editCategory['description'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="cat_image">Category Image</label>
                            <input type="file" id="cat_image" name="image" class="form-input" accept="image/*">
                            <?php if ($editCategory && $editCategory['image']): ?>
                                <div style="margin-top: 10px;">
                                    <p style="margin-bottom: 5px; color: var(--muted);">Current image:</p>
                                    <img src="<?php echo UPLOAD_URL . '/categories/' . htmlspecialchars($editCategory['image']); ?>" alt="Current image" style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 2px solid #e5e7eb;">
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="cat_display_order">Display Order</label>
                            <input type="number" id="cat_display_order" name="display_order" class="form-input" value="<?php echo $editCategory['display_order'] ?? 0; ?>">
                        </div>
                        
                        <div class="form-group">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <input type="checkbox" id="cat_is_active" name="is_active" style="width: 20px; height: 20px;" <?php echo ($editCategory['is_active'] ?? 1) ? 'checked' : ''; ?>>
                                <label class="form-label" for="cat_is_active" style="margin: 0;">Active</label>
                            </div>
                        </div>
                        
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" onclick="closeCategoryModal()">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <?php echo $editCategory ? 'Update Category' : 'Create Category'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Delete Category Modal -->
        <div class="modal" id="deleteCategoryModal" style="display: none;">
            <div class="modal-overlay" onclick="closeDeleteCategoryModal()"></div>
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title">Delete Category</h2>
                    <button class="modal-close" onclick="closeDeleteCategoryModal()" aria-label="Close">&times;</button>
                </div>
                <div class="modal-body">
                    <p style="margin-bottom: 20px; font-size: 16px;" id="deleteCategoryText">Are you sure you want to delete this category?</p>
                    <p style="margin-bottom: 20px; color: var(--danger); font-weight: 600;">This action cannot be undone. This will delete:</p>
                    <ul style="margin-left: 20px; margin-bottom: 20px; color: var(--muted);">
                        <li>The category and all its information</li>
                        <li>All menu items in this category</li>
                        <li>The category image</li>
                    </ul>
                    <form method="POST" action="" id="deleteCategoryForm">
                        <input type="hidden" name="action" value="delete_category">
                        <input type="hidden" name="id" id="deleteCategoryId" value="">
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" onclick="closeDeleteCategoryModal()">Cancel</button>
                            <button type="submit" class="btn btn-danger">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Yes, Delete Category
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    
    <script>
        function toggleDropdown(btn) {
            document.querySelectorAll('.actions-dropdown.show').forEach(d => d.classList.remove('show'));
            const dropdown = btn.nextElementSibling;
            dropdown.classList.toggle('show');
            document.addEventListener('click', function closeDropdown(e) {
                if (!btn.contains(e.target) && !dropdown.contains(e.target)) {
                    dropdown.classList.remove('show');
                    document.removeEventListener('click', closeDropdown);
                }
            });
        }
        
        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById('tab-' + tabName).classList.add('active');
            event.target.classList.add('active');
            
            // Update URL without reload
            const url = new URL(window.location);
            url.searchParams.set('tab', tabName);
            window.history.pushState({}, '', url);
        }
        
        // Menu items search and filter
        const menuSearch = document.getElementById('menuSearch');
        const categoryFilter = document.getElementById('categoryFilter');
        const menuItemsTableBody = document.getElementById('menuItemsTableBody');
        
        function filterMenuItems() {
            const searchTerm = menuSearch.value.toLowerCase();
            const categoryId = categoryFilter.value;
            const rows = menuItemsTableBody.querySelectorAll('tr');
            
            rows.forEach(row => {
                const itemName = row.getAttribute('data-item-name') || '';
                const rowCategoryId = row.getAttribute('data-category-id');
                
                const matchesSearch = !searchTerm || itemName.includes(searchTerm);
                const matchesCategory = categoryId === 'all' || rowCategoryId === categoryId;
                
                row.style.display = (matchesSearch && matchesCategory) ? '' : 'none';
            });
        }
        
        if (menuSearch) menuSearch.addEventListener('input', filterMenuItems);
        if (categoryFilter) categoryFilter.addEventListener('change', filterMenuItems);
        
        // Menu Item Modal Functions
        function openMenuItemModal(itemId = null) {
            if (itemId) {
                window.location.href = 'restaurant-view.php?slug=<?php echo urlencode($restaurantSlug); ?>&tab=menu&action=edit_menu_item&id=' + itemId;
            } else {
                document.getElementById('menuItemModal').style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        }
        
        function closeMenuItemModal() {
            document.getElementById('menuItemModal').style.display = 'none';
            document.body.style.overflow = '';
            // Redirect to clear edit mode
            if (window.location.search.includes('action=edit_menu_item')) {
                window.location.href = 'restaurant-view.php?slug=<?php echo urlencode($restaurantSlug); ?>&tab=menu';
            }
        }
        
        // Delete Menu Item Modal Functions
        function openDeleteMenuItemModal(menuItemId, menuItemName) {
            document.getElementById('deleteMenuItemId').value = menuItemId;
            const deleteText = document.getElementById('deleteMenuItemText');
            if (deleteText) {
                deleteText.innerHTML = 'Are you sure you want to delete <strong>"' + menuItemName + '"</strong>?';
            }
            document.getElementById('deleteMenuItemModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
        
        function closeDeleteMenuItemModal() {
            document.getElementById('deleteMenuItemModal').style.display = 'none';
            document.body.style.overflow = '';
        }
        
        // Auto-generate slug from name
        document.getElementById('name')?.addEventListener('input', function() {
            const slugInput = document.getElementById('slug');
            if (slugInput && !slugInput.value) {
                slugInput.value = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
            }
        });
        
        // Open modal if editing menu item
        <?php if ($editMenuItem): ?>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('menuItemModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        });
        <?php endif; ?>
        
        // Category Modal Functions
        function openCategoryModal(categoryId = null) {
            if (categoryId) {
                window.location.href = 'restaurant-view.php?slug=<?php echo urlencode($restaurantSlug); ?>&tab=categories&action=edit_category&id=' + categoryId;
            } else {
                document.getElementById('categoryModal').style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        }
        
        function closeCategoryModal() {
            document.getElementById('categoryModal').style.display = 'none';
            document.body.style.overflow = '';
            // Redirect to clear edit mode
            if (window.location.search.includes('action=edit_category')) {
                window.location.href = 'restaurant-view.php?slug=<?php echo urlencode($restaurantSlug); ?>&tab=categories';
            }
        }
        
        // Delete Category Modal Functions
        function openDeleteCategoryModal(categoryId, categoryName) {
            document.getElementById('deleteCategoryId').value = categoryId;
            const deleteText = document.getElementById('deleteCategoryText');
            if (deleteText) {
                deleteText.innerHTML = 'Are you sure you want to delete <strong>"' + categoryName + '"</strong>?';
            }
            document.getElementById('deleteCategoryModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
        
        function closeDeleteCategoryModal() {
            document.getElementById('deleteCategoryModal').style.display = 'none';
            document.body.style.overflow = '';
        }
        
        // Auto-generate slug from category name
        document.getElementById('cat_name')?.addEventListener('input', function() {
            const slugInput = document.getElementById('cat_slug');
            if (slugInput && !slugInput.value) {
                slugInput.value = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
            }
        });
        
        // Open modal if editing category
        <?php if ($editCategory): ?>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('categoryModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        });
        <?php endif; ?>
    </script>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>

