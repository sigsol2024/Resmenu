<?php
/**
 * Menu Item Management (Manager)
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/subscription-middleware.php';

// Require either admin or manager
requireLogin();
if (!isSuperAdmin() && !isManager()) {
    header('Location: /admin/login.php');
    exit;
}

// Get restaurant_id: admin can specify via URL, manager uses session
$restaurantId = null;
if (isSuperAdmin()) {
    // Admin can manage any restaurant via restaurant_id parameter
    // Check GET first (URL parameter)
    $restaurantId = isset($_GET['restaurant_id']) ? intval($_GET['restaurant_id']) : null;
    // Check POST (form submission)
    if (!$restaurantId && isset($_POST['restaurant_id'])) {
        $restaurantId = intval($_POST['restaurant_id']);
    }
    // Check session (persist across page navigations)
    if (!$restaurantId && isset($_SESSION['admin_restaurant_id'])) {
        $restaurantId = intval($_SESSION['admin_restaurant_id']);
    }
    // If still no restaurant_id, try to get from referrer
    if (!$restaurantId && isset($_SERVER['HTTP_REFERER'])) {
        $referer = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_QUERY);
        if ($referer) {
            parse_str($referer, $refererParams);
            if (isset($refererParams['restaurant_id'])) {
                $restaurantId = intval($refererParams['restaurant_id']);
            }
        }
    }
    if (!$restaurantId) {
        die('Restaurant ID required for admin access. Please provide restaurant_id parameter.');
    }
    // Store in session for persistence
    $_SESSION['admin_restaurant_id'] = $restaurantId;
} else {
    // Manager uses their assigned restaurant
    $restaurantId = getCurrentUserRestaurantId();
    if (!$restaurantId) {
        die('No restaurant associated with your account. Please contact administrator.');
    }
}
$pdo = getDBConnection();
$message = '';
$error = '';

// Check if restaurant_id is set
if (!$restaurantId) {
    $error = 'No restaurant associated with your account. Please contact administrator.';
    // Try to get restaurant_id from database
    $userId = getCurrentUserId();
    if ($userId && $pdo) {
        $stmt = $pdo->prepare("SELECT restaurant_id FROM managers WHERE id = ?");
        $stmt->execute([$userId]);
        $manager = $stmt->fetch();
        if ($manager && $manager['restaurant_id']) {
            $_SESSION['restaurant_id'] = $manager['restaurant_id'];
            $restaurantId = $manager['restaurant_id'];
            $error = '';
        }
    }
}

// Handle delete action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
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
                
                // Update restaurant stats
                updateRestaurantStats($restaurantId);
                
                // Get restaurant slug for redirect (if super admin)
                $deleteRestaurantSlug = null;
                if (isSuperAdmin() && $restaurantId && $pdo) {
                    $stmt = $pdo->prepare("SELECT slug FROM restaurants WHERE id = ?");
                    $stmt->execute([$restaurantId]);
                    $restaurant = $stmt->fetch();
                    if ($restaurant) {
                        $deleteRestaurantSlug = $restaurant['slug'];
                    }
                }
                
                // Redirect to prevent form resubmission
                $redirectUrl = 'menu-items.php';
                if (isSuperAdmin() && $restaurantId && $deleteRestaurantSlug) {
                    // Redirect back to admin restaurant view
                    $redirectUrl = '../admin/restaurant-view.php?slug=' . urlencode($deleteRestaurantSlug) . '&tab=menu&success=deleted';
                } elseif (isSuperAdmin() && $restaurantId) {
                    $redirectUrl .= '?restaurant_id=' . urlencode($restaurantId) . '&success=deleted';
                } else {
                    $redirectUrl .= '?success=deleted';
                }
                header('Location: ' . $redirectUrl);
                exit;
            } else {
                $error = 'Menu item not found';
            }
        } catch (PDOException $e) {
            $error = 'Error deleting menu item: ' . $e->getMessage();
        }
    }
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate restaurant_id before processing
    if (!$restaurantId) {
        $error = 'No restaurant associated with your account. Please contact administrator.';
    } else {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create' || $action === 'update') {
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
            // Check subscription limits for new menu items (skip for admins)
            if ($action === 'create' && !isSuperAdmin()) {
                $canAdd = canAddMenuItem($restaurantId);
                if (!$canAdd['allowed']) {
                    $error = $canAdd['message'];
                }
            }
        }
        
        if (!$error) {
            // #region agent log
            $logDir = __DIR__ . '/../.cursor';
            if (!is_dir($logDir)) {
                @mkdir($logDir, 0755, true);
            }
            @file_put_contents($logDir . '/debug.log', json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'A','location'=>'manager/menu-items.php:31','message'=>'Menu item create/update - checking category validation','data'=>['restaurantId'=>$restaurantId,'categoryId'=>$category_id,'action'=>$action],'timestamp'=>time()*1000]) . "\n", FILE_APPEND);
            // #endregion
            
            // Validate that category belongs to this restaurant
            $categoryCheck = null;
            if ($pdo && $restaurantId) {
                $checkStmt = $pdo->prepare("SELECT id FROM categories WHERE id = ? AND restaurant_id = ?");
                $checkStmt->execute([$category_id, $restaurantId]);
                $categoryCheck = $checkStmt->fetch();
            }
            
            // #region agent log
            $logDir = __DIR__ . '/../.cursor';
            if (!is_dir($logDir)) {
                @mkdir($logDir, 0755, true);
            }
            @file_put_contents($logDir . '/debug.log', json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'A','location'=>'manager/menu-items.php:40','message'=>'Category validation result','data'=>['categoryExists'=>($categoryCheck !== false),'categoryId'=>$category_id,'restaurantId'=>$restaurantId],'timestamp'=>time()*1000]) . "\n", FILE_APPEND);
            // #endregion
            
            if (!$categoryCheck && $category_id > 0) {
                $error = 'Category does not belong to your restaurant';
            } else {
                if (!$pdo) {
                    $error = 'Database connection failed';
                } else {
                    try {
                        $image = null;
                    
                        // Handle image upload
                        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                            $uploadResult = uploadFile($_FILES['image'], UPLOAD_PATH . '/menu-items');
                            if ($uploadResult['success']) {
                                $image = $uploadResult['filename'];
                                
                                // Delete old image if updating
                                if ($action === 'update' && isset($_POST['id'])) {
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
                            if ($action === 'update' && isset($_POST['id'])) {
                                $stmt = $pdo->prepare("SELECT image FROM menu_items WHERE id = ? AND restaurant_id = ?");
                                $stmt->execute([$_POST['id'], $restaurantId]);
                                $oldItem = $stmt->fetch();
                                $image = $oldItem['image'] ?? null;
                            }
                        }
                        
                        if (!$error) {
                            if ($action === 'create') {
                                $stmt = $pdo->prepare("INSERT INTO menu_items (restaurant_id, category_id, name, slug, description, price, image, display_order, is_available) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                                $stmt->execute([$restaurantId, $category_id, $name, $slug, $description, $price, $image, $display_order, $is_available]);
                                
                // Update restaurant stats
                updateRestaurantStats($restaurantId);
                
                // Redirect to prevent form resubmission
                $redirectUrl = 'menu-items.php';
                if (isSuperAdmin() && $restaurantId && $restaurantSlug) {
                    // Redirect back to admin restaurant view
                    $redirectUrl = '../admin/restaurant-view.php?slug=' . urlencode($restaurantSlug) . '&tab=menu&success=created';
                } elseif (isSuperAdmin() && $restaurantId) {
                    $redirectUrl .= '?restaurant_id=' . urlencode($restaurantId) . '&success=created';
                } else {
                    $redirectUrl .= '?success=created';
                }
                header('Location: ' . $redirectUrl);
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
                                    
                                    // Update restaurant stats
                                    updateRestaurantStats($restaurantId);
                                    
                                    // Redirect to prevent form resubmission
                                    $redirectUrl = 'menu-items.php';
                                    if (isSuperAdmin() && $restaurantId && $restaurantSlug) {
                                        // Redirect back to admin restaurant view
                                        $redirectUrl = '../admin/restaurant-view.php?slug=' . urlencode($restaurantSlug) . '&tab=menu&success=updated';
                                    } elseif (isSuperAdmin() && $restaurantId) {
                                        $redirectUrl .= '?restaurant_id=' . urlencode($restaurantId) . '&success=updated';
                                    } else {
                                        $redirectUrl .= '?success=updated';
                                    }
                                    header('Location: ' . $redirectUrl);
                                    exit;
                                } else {
                                    $error = 'Invalid menu item ID';
                                }
                            }
                        }
                    } catch (PDOException $e) {
                        error_log("Menu item error: " . $e->getMessage());
                        $error = 'Error saving menu item: ' . $e->getMessage();
                    } catch (Exception $e) {
                        error_log("Menu item error: " . $e->getMessage());
                        $error = 'Error: ' . $e->getMessage();
                    }
                }
            }
        }
    }
    }
}

// Get restaurant slug for return URL (if coming from admin dashboard)
$restaurantSlug = null;
$returnTo = $_GET['return_to'] ?? $_POST['return_to'] ?? '';
$returnSlug = $_GET['return_slug'] ?? $_POST['return_slug'] ?? '';
if (isSuperAdmin() && $restaurantId && $pdo) {
    // Check if return parameters are provided
    if ($returnTo === 'admin' && !empty($returnSlug)) {
        $restaurantSlug = $returnSlug;
    } else {
        // Fallback: get slug from database
        $stmt = $pdo->prepare("SELECT slug FROM restaurants WHERE id = ?");
        $stmt->execute([$restaurantId]);
        $restaurant = $stmt->fetch();
        if ($restaurant) {
            $restaurantSlug = $restaurant['slug'];
        }
    }
}

// Get menu item for editing
$editItem = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE id = ? AND restaurant_id = ?");
    $stmt->execute([$_GET['id'], $restaurantId]);
    $editItem = $stmt->fetch();
}

// Get categories for dropdown
$categories = [];
if ($pdo && $restaurantId) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE restaurant_id = ? AND is_active = 1 ORDER BY display_order ASC, name ASC");
    $stmt->execute([$restaurantId]);
    $categories = $stmt->fetchAll();
}

// Get all menu items
$menuItems = [];
$selectedCategoryId = $_GET['category_id'] ?? null;
if ($pdo && $restaurantId) {
    if ($selectedCategoryId) {
        $stmt = $pdo->prepare("SELECT mi.*, c.name as category_name FROM menu_items mi JOIN categories c ON mi.category_id = c.id WHERE mi.restaurant_id = ? AND mi.category_id = ? ORDER BY mi.display_order ASC, mi.name ASC");
        $stmt->execute([$restaurantId, $selectedCategoryId]);
    } else {
        $stmt = $pdo->prepare("SELECT mi.*, c.name as category_name FROM menu_items mi JOIN categories c ON mi.category_id = c.id WHERE mi.restaurant_id = ? ORDER BY mi.display_order ASC, mi.name ASC");
        $stmt->execute([$restaurantId]);
    }
    $menuItems = $stmt->fetchAll();
}

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
    }
}

$pageTitle = 'Menu Item Management';
include __DIR__ . '/../includes/manager-layout.php';
?>

        <div class="page-header">
    <h1 class="page-title">Menu Items</h1>
    <p class="page-subtitle">Manage your restaurant menu items, prices, and availability</p>
</div>

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

        <div class="settings-card">
            <div style="margin-bottom: 20px;">
                <form method="GET" style="display: flex; gap: 15px; align-items: end; flex-wrap: wrap;">
                    <?php if (isSuperAdmin() && $restaurantId): ?>
                        <input type="hidden" name="restaurant_id" value="<?php echo htmlspecialchars($restaurantId); ?>">
                    <?php endif; ?>
                    <div style="flex: 1; min-width: 220px;">
                        <label class="form-label" for="category_filter">Filter by Category</label>
                        <select id="category_filter" name="category_id" class="form-select">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo ($selectedCategoryId == $cat['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary" style="min-width: 110px;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        Filter
                    </button>
                    <?php if ($selectedCategoryId): ?>
                        <a href="menu-items.php<?php echo isSuperAdmin() && $restaurantId ? '?restaurant_id=' . urlencode($restaurantId) : ''; ?>" class="btn btn-secondary" style="min-width: 140px;">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Clear Filter
                        </a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
        
        <!-- Create/Edit Menu Item Modal -->
        <div class="modal" id="menuItemModal" style="display: <?php echo $editItem ? 'flex' : 'none'; ?>;">
            <div class="modal-overlay" onclick="closeMenuItemModal()"></div>
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title"><?php echo $editItem ? 'Edit Menu Item' : 'Create New Menu Item'; ?></h2>
                    <button class="modal-close" onclick="closeMenuItemModal()" aria-label="Close">&times;</button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="<?php 
                        $formAction = '';
                        if (isSuperAdmin() && $restaurantId) {
                            $formAction = '?restaurant_id=' . urlencode($restaurantId);
                            if ($returnTo === 'admin' && !empty($returnSlug)) {
                                $formAction .= '&return_to=' . urlencode($returnTo) . '&return_slug=' . urlencode($returnSlug);
                            }
                        }
                        echo $formAction;
                    ?>" enctype="multipart/form-data">
                <?php if (isSuperAdmin() && $restaurantId): ?>
                    <input type="hidden" name="restaurant_id" value="<?php echo htmlspecialchars($restaurantId); ?>">
                    <?php if ($returnTo === 'admin' && !empty($returnSlug)): ?>
                        <input type="hidden" name="return_to" value="<?php echo htmlspecialchars($returnTo); ?>">
                        <input type="hidden" name="return_slug" value="<?php echo htmlspecialchars($returnSlug); ?>">
                    <?php endif; ?>
                <?php endif; ?>
                <input type="hidden" name="action" value="<?php echo $editItem ? 'update' : 'create'; ?>">
                <?php if ($editItem): ?>
                    <input type="hidden" name="id" value="<?php echo $editItem['id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label class="form-label" for="category_id">Category *</label>
                    <select id="category_id" name="category_id" class="form-select" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo ($editItem && $editItem['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="name">Item Name *</label>
                    <input type="text" id="name" name="name" class="form-input" required value="<?php echo htmlspecialchars($editItem['name'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="slug">Slug *</label>
                    <input type="text" id="slug" name="slug" class="form-input" required value="<?php echo htmlspecialchars($editItem['slug'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="description">Description</label>
                    <textarea id="description" name="description" class="form-textarea" rows="3"><?php echo htmlspecialchars($editItem['description'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="image">Item Image</label>
                    <input type="file" id="image" name="image" class="form-input" accept="image/*">
                    <?php if ($editItem && $editItem['image']): ?>
                        <div style="margin-top: 10px;">
                            <p style="margin-bottom: 5px; color: var(--muted);">Current image:</p>
                            <img src="<?php echo UPLOAD_URL . '/menu-items/' . htmlspecialchars($editItem['image']); ?>" alt="Current image" style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 2px solid #e5e7eb;">
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="price">Price *</label>
                    <input type="number" id="price" name="price" class="form-input" step="0.01" min="0" required value="<?php echo $editItem['price'] ?? ''; ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="display_order">Display Order</label>
                    <input type="number" id="display_order" name="display_order" class="form-input" value="<?php echo $editItem['display_order'] ?? 0; ?>">
                </div>
                
                <div class="form-group">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <input type="checkbox" id="is_available" name="is_available" style="width: 20px; height: 20px;" <?php echo ($editItem['is_available'] ?? 1) ? 'checked' : ''; ?>>
                        <label class="form-label" for="is_available" style="margin: 0;">Available</label>
                    </div>
                </div>
                
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" onclick="closeMenuItemModal()">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <?php echo $editItem ? 'Update Menu Item' : 'Create Menu Item'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Delete Confirmation Modal -->
        <div class="modal" id="deleteModal" style="display: none;">
            <div class="modal-overlay" onclick="closeDeleteModal()"></div>
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title">Delete Menu Item</h2>
                    <button class="modal-close" onclick="closeDeleteModal()" aria-label="Close">&times;</button>
                </div>
                <div class="modal-body">
                    <p style="margin-bottom: 20px; font-size: 16px;">Are you sure you want to delete this menu item?</p>
                    <p style="margin-bottom: 20px; color: var(--danger); font-weight: 600;">This action cannot be undone. This will delete:</p>
                    <ul style="margin-left: 20px; margin-bottom: 20px; color: var(--muted);">
                        <li>The menu item and all its information</li>
                        <li>The menu item image</li>
                    </ul>
                    <form method="POST" action="" id="deleteForm">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="deleteMenuItemId" value="">
                        <?php if (isSuperAdmin() && $restaurantId): ?>
                            <input type="hidden" name="restaurant_id" value="<?php echo htmlspecialchars($restaurantId); ?>">
                        <?php endif; ?>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Cancel</button>
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
        
        <div class="settings-card">
            <div class="section-header">
                <h2 class="section-title">All Menu Items</h2>
                <?php if (!$editItem): ?>
                    <button class="btn btn-primary" onclick="openMenuItemModal()">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        New Item
                    </button>
                <?php endif; ?>
            </div>
            
            <div class="table-wrapper menu-items-table-desktop">
            <table class="table restaurants-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Order</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($menuItems)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px; color: var(--muted);">No menu items found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($menuItems as $item): ?>
                            <tr>
                                <td>
                                    <?php if ($item['image']): ?>
                                        <img src="<?php echo UPLOAD_URL . '/menu-items/' . htmlspecialchars($item['image']); ?>" alt="" class="menu-item-image">
                                    <?php else: ?>
                                        <div class="menu-item-image" style="background: #f0f0f0; display: flex; align-items: center; justify-content: center; color: #999;">No Image</div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                <td><?php echo htmlspecialchars($item['category_name']); ?></td>
                                <td><?php echo formatPrice($item['price']); ?></td>
                                <td><?php echo $item['display_order']; ?></td>
                                <td><span style="padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; background: <?php echo $item['is_available'] ? '#d1fae5' : '#fee2e2'; ?>; color: <?php echo $item['is_available'] ? '#065f46' : '#991b1b'; ?>"><?php echo $item['is_available'] ? 'Available' : 'Unavailable'; ?></span></td>
                                <td class="actions-cell">
                                    <button class="actions-btn" type="button" title="Actions">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                        </svg>
                                    </button>
                                    <div class="actions-dropdown">
                                        <a href="?action=edit&id=<?php echo $item['id']; ?><?php echo isSuperAdmin() && $restaurantId ? '&restaurant_id=' . urlencode($restaurantId) : ''; ?>" class="actions-dropdown-item">Edit</a>
                                        <div class="actions-dropdown-divider"></div>
                                        <button type="button" onclick="openDeleteModal(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars(addslashes($item['name'])); ?>')" class="actions-dropdown-item danger">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="menu-items-mobile" aria-label="Menu items (mobile)">
            <?php if (empty($menuItems)): ?>
                <p style="text-align:center; padding: 18px; color: var(--muted);">No menu items found.</p>
            <?php else: ?>
                <?php foreach ($menuItems as $item): ?>
                    <details class="mi-card">
                        <summary class="mi-summary">
                            <div class="mi-left">
                                <?php if ($item['image']): ?>
                                    <img src="<?php echo UPLOAD_URL . '/menu-items/' . htmlspecialchars($item['image']); ?>" alt="" class="mi-thumb">
                                <?php else: ?>
                                    <div class="mi-thumb mi-thumb-empty">No Image</div>
                                <?php endif; ?>
                                <div class="mi-main">
                                    <div class="mi-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                    <div class="mi-meta">
                                        <span><?php echo htmlspecialchars($item['category_name']); ?></span>
                                        <span class="mi-dot">•</span>
                                        <span><?php echo formatPrice($item['price']); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="mi-right">
                                <span class="mi-status" style="background: <?php echo $item['is_available'] ? '#d1fae5' : '#fee2e2'; ?>; color: <?php echo $item['is_available'] ? '#065f46' : '#991b1b'; ?>">
                                    <?php echo $item['is_available'] ? 'Available' : 'Unavailable'; ?>
                                </span>
                                <span class="mi-chevron" aria-hidden="true">▾</span>
                            </div>
                        </summary>
                        <div class="mi-body">
                            <div class="mi-grid">
                                <div class="mi-kv"><span class="mi-k">Order</span><span class="mi-v"><?php echo (int)$item['display_order']; ?></span></div>
                                <div class="mi-kv"><span class="mi-k">Slug</span><span class="mi-v"><?php echo htmlspecialchars($item['slug']); ?></span></div>
                            </div>
                            <div class="mi-actions">
                                <a class="btn btn-secondary" href="?action=edit&id=<?php echo (int)$item['id']; ?><?php echo isSuperAdmin() && $restaurantId ? '&restaurant_id=' . urlencode($restaurantId) : ''; ?>">Edit</a>
                                <button type="button" class="btn btn-danger" onclick="openDeleteModal(<?php echo (int)$item['id']; ?>, '<?php echo htmlspecialchars(addslashes($item['name'])); ?>')">Delete</button>
                            </div>
                        </div>
                    </details>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    
<style>
/* Clean Manager Menu Items Styles */
.alert {
    padding: 12px 16px;
    border-radius: 8px;
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
    max-width: 600px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    z-index: 3002;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
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
    margin: 0;
    color: #111827;
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

.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border-radius: 6px;
    font-weight: 500;
    font-size: 0.875rem;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
}

.btn svg {
    width: 16px;
    height: 16px;
    flex-shrink: 0;
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
    color: #111827;
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

.btn-small svg {
    width: 14px;
    height: 14px;
}

.menu-item-image {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
}

/* Table Styles */
.table {
    width: 100%;
    border-collapse: collapse;
}

.table thead {
    background: #f9fafb;
}

.table th {
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
    border-bottom: 1px solid #e5e7eb;
    color: #111827;
}

.table tbody tr:hover {
    background: #f9fafb;
}

/* Form Styles */
.form-group {
    margin-bottom: 20px;
}

.form-label {
    display: block;
    margin-bottom: 6px;
    font-size: 0.875rem;
    font-weight: 500;
    color: #111827;
}

.form-input,
.form-select,
.form-textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 0.875rem;
    color: #111827;
    transition: border-color 0.2s;
}

.form-input:focus,
.form-select:focus,
.form-textarea:focus {
    outline: none;
    border-color: #111827;
}

.form-textarea {
    resize: vertical;
    min-height: 80px;
}

/* Card Styles */
.card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    margin-bottom: 24px;
}

.card-header {
    padding: 20px 24px;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #111827;
    margin: 0;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .table {
        font-size: 0.875rem;
    }
    
    .table th,
    .table td {
        padding: 12px 8px;
    }
    
    .modal-content {
        width: 95%;
        max-height: 95vh;
    }
    
    .modal-footer {
        flex-direction: column-reverse;
    }
    
    .modal-footer .btn {
        width: 100%;
        justify-content: center;
    }

    /* Menu items: mobile cards instead of wide table */
    .menu-items-table-desktop { display: none; }
    .menu-items-mobile { display: block; }
    .mi-card { background:#fff; border:1px solid #e5e7eb; border-radius:12px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,0.06); }
    .mi-card + .mi-card { margin-top: 12px; }
    .mi-summary { list-style:none; cursor:pointer; padding: 14px; display:flex; align-items:center; justify-content:space-between; gap:12px; }
    .mi-summary::-webkit-details-marker { display:none; }
    .mi-left { display:flex; align-items:center; gap:12px; min-width:0; }
    .mi-thumb { width:48px; height:48px; border-radius:10px; object-fit:cover; border:1px solid #e5e7eb; flex-shrink:0; }
    .mi-thumb-empty { display:flex; align-items:center; justify-content:center; font-size:0.65rem; color:#6b7280; background:#f3f4f6; }
    .mi-main { min-width:0; }
    .mi-name { font-weight:700; color:#111827; font-size:0.95rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width: 220px; }
    .mi-meta { color:#6b7280; font-size:0.8rem; display:flex; gap:8px; align-items:center; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .mi-dot { color:#9ca3af; }
    .mi-right { display:flex; align-items:center; gap:10px; flex-shrink:0; }
    .mi-status { padding:4px 10px; border-radius:999px; font-size:0.7rem; font-weight:700; }
    .mi-chevron { color:#6b7280; transition: transform .15s ease; }
    .mi-card[open] .mi-chevron { transform: rotate(180deg); }
    .mi-body { border-top:1px solid #f3f4f6; padding: 12px 14px 14px; }
    .mi-grid { display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:12px; }
    .mi-kv { background:#f9fafb; border:1px solid #e5e7eb; border-radius:10px; padding:10px; min-width:0; }
    .mi-k { display:block; font-size:0.7rem; text-transform:uppercase; color:#6b7280; font-weight:700; letter-spacing:.04em; margin-bottom:4px; }
    .mi-v { display:block; font-size:0.85rem; font-weight:700; color:#111827; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .mi-actions { display:flex; gap:10px; }
    .mi-actions .btn { flex:1; justify-content:center; padding:10px 12px; }

    /* Filters: better wrap on mobile */
    .settings-card form { width: 100%; }
    .settings-card form > div { width: 100%; }
    .settings-card form button,
    .settings-card form a.btn { width: 100%; justify-content: center; }
}

@media (min-width: 769px) {
    .menu-items-mobile { display: none; }
}
</style>
    
    <script>
        // Auto-generate slug from name
        document.getElementById('name')?.addEventListener('input', function() {
            const slugInput = document.getElementById('slug');
            if (slugInput && !slugInput.value) {
                slugInput.value = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
            }
        });
        
        // Menu Item Modal Functions
        function openMenuItemModal() {
            document.getElementById('menuItemModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
        
        function closeMenuItemModal() {
            document.getElementById('menuItemModal').style.display = 'none';
            document.body.style.overflow = '';
            // Redirect to clear edit mode
            if (window.location.search.includes('action=edit')) {
                const baseUrl = 'menu-items.php';
                const params = new URLSearchParams(window.location.search);
                params.delete('action');
                params.delete('id');
                window.location.href = baseUrl + (params.toString() ? '?' + params.toString() : '');
            }
        }
        
        // Delete Modal Functions
        function openDeleteModal(menuItemId, menuItemName) {
            document.getElementById('deleteMenuItemId').value = menuItemId;
            const modalBody = document.querySelector('#deleteModal .modal-body');
            const nameParagraph = modalBody.querySelector('p:first-child');
            if (nameParagraph) {
                nameParagraph.innerHTML = 'Are you sure you want to delete <strong>"' + menuItemName + '"</strong>?';
            }
            document.getElementById('deleteModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
        
        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
            document.body.style.overflow = '';
        }
        
        // Open modal if editing
        <?php if ($editItem): ?>
        document.addEventListener('DOMContentLoaded', function() {
            openMenuItemModal();
        });
        <?php endif; ?>
    </script>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>

