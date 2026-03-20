<?php
/**
 * Category Management (Manager)
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
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

// Secondary section selections (used only when editing)
$editCategorySecondarySectionIds = [];

// Get restaurant slug for return URL (if coming from admin dashboard)
$restaurantSlug = null;
if (isSuperAdmin() && $restaurantId && $pdo) {
    $stmt = $pdo->prepare("SELECT slug FROM restaurants WHERE id = ?");
    $stmt->execute([$restaurantId]);
    $restaurant = $stmt->fetch();
    if ($restaurant) {
        $restaurantSlug = $restaurant['slug'];
    }
}

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
    requireCSRFToken();
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
                // Delete secondary section mappings for this category
                try {
                    $pdo->prepare("DELETE FROM category_secondary_sections WHERE category_id = ?")->execute([$id]);
                } catch (PDOException $e) {
                    // Optional feature may not have been migrated yet.
                }
                // Delete category
                $pdo->prepare("DELETE FROM categories WHERE id = ? AND restaurant_id = ?")->execute([$id, $restaurantId]);
                
                // Delete uploaded file
                if ($category['image']) {
                    deleteFile(UPLOAD_PATH . '/categories/' . $category['image']);
                }
                
                // Redirect to prevent form resubmission
                $redirectUrl = 'categories.php';
                if (isSuperAdmin() && $restaurantId) {
                    $redirectUrl .= '?restaurant_id=' . urlencode($restaurantId) . '&success=deleted';
                } else {
                    $redirectUrl .= '?success=deleted';
                }
                header('Location: ' . $redirectUrl);
                exit;
            } else {
                $error = 'Category not found';
            }
        } catch (PDOException $e) {
            $error = 'Error deleting category: ' . $e->getMessage();
        }
    }
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRFToken();
    // Validate restaurant_id before processing
    if (!$restaurantId) {
        $error = 'No restaurant associated with your account. Please contact administrator.';
    } else {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create' || $action === 'update') {
        $name = sanitize($_POST['name'] ?? '');
        $slug = sanitize($_POST['slug'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $display_order = intval($_POST['display_order'] ?? 0);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $section_id = intval($_POST['section_id'] ?? 0);
        
        // Check subscription limits for new categories (skip for admins)
        if ($action === 'create' && !isSuperAdmin()) {
            $canAdd = canAddCategory($restaurantId);
            if (!$canAdd['allowed']) {
                $error = $canAdd['message'];
            }
        }
        
        if (empty($name) || empty($slug)) {
            $error = 'Name and slug are required';
        } elseif ($section_id < 1) {
            $error = 'Please select a section for this category';
        } elseif (!$error) {
            try {
                $image = null;
                
                // Handle image upload
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $uploadResult = uploadFile($_FILES['image'], UPLOAD_PATH . '/categories');
                    if ($uploadResult['success']) {
                        $image = $uploadResult['filename'];
                        
                        // Delete old image if updating
                        if ($action === 'update' && isset($_POST['id'])) {
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
                    if ($action === 'update' && isset($_POST['id'])) {
                        $stmt = $pdo->prepare("SELECT image FROM categories WHERE id = ? AND restaurant_id = ?");
                        $stmt->execute([$_POST['id'], $restaurantId]);
                        $oldCategory = $stmt->fetch();
                        $image = $oldCategory['image'] ?? null;
                    }
                }
                
                if (!$error) {
                    if ($action === 'create') {
                        reorderCategoriesForInsert($restaurantId, max(1, $display_order));
                        $stmt = $pdo->prepare("INSERT INTO categories (restaurant_id, section_id, name, slug, description, image, display_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$restaurantId, $section_id, $name, $slug, $description, $image, max(1, $display_order), $is_active]);

                        // Persist secondary section mappings for this category.
                        // Category can appear in multiple secondary sections (not on the main full menu).
                        $newCategoryId = (int)$pdo->lastInsertId();
                        if ($newCategoryId > 0) {
                            $secondarySectionIds = $_POST['secondary_section_ids'] ?? [];
                            if (!is_array($secondarySectionIds)) $secondarySectionIds = [];
                            $secondarySectionIds = array_values(array_filter(array_map('intval', $secondarySectionIds)));
                            // Prevent duplicates by filtering out the primary section.
                            $secondarySectionIds = array_values(array_filter($secondarySectionIds, function($sid) use ($section_id) {
                                return (int)$sid !== (int)$section_id;
                            }));

                            // Refresh mappings (ignore if migration table doesn't exist yet)
                            try {
                                $pdo->prepare("DELETE FROM category_secondary_sections WHERE category_id = ?")->execute([$newCategoryId]);
                                $stmtIns = $pdo->prepare("INSERT INTO category_secondary_sections (category_id, section_id, is_active) VALUES (?, ?, ?)");
                                foreach ($secondarySectionIds as $sid) {
                                    $stmtIns->execute([$newCategoryId, $sid, 1]);
                                }
                            } catch (PDOException $e) {
                                // Optional feature may not have been migrated yet.
                            }
                        }

                        // Redirect to prevent form resubmission
                        header('Location: categories.php?' . (isSuperAdmin() && $restaurantId ? 'restaurant_id=' . urlencode($restaurantId) . '&' : '') . 'success=created');
                        exit;
                    } else {
                        $id = intval($_POST['id'] ?? 0);
                        if ($id > 0) {
                            $stmt = $pdo->prepare("SELECT display_order FROM categories WHERE id = ? AND restaurant_id = ?");
                            $stmt->execute([$id, $restaurantId]);
                            $old = $stmt->fetch();
                            $oldOrder = $old ? (int)$old['display_order'] : 0;
                            reorderCategoriesForUpdate($restaurantId, $id, $oldOrder, max(1, $display_order));
                            if ($image) {
                                $stmt = $pdo->prepare("UPDATE categories SET section_id = ?, name = ?, slug = ?, description = ?, image = ?, display_order = ?, is_active = ? WHERE id = ? AND restaurant_id = ?");
                                $stmt->execute([$section_id, $name, $slug, $description, $image, max(1, $display_order), $is_active, $id, $restaurantId]);
                            } else {
                                $stmt = $pdo->prepare("UPDATE categories SET section_id = ?, name = ?, slug = ?, description = ?, display_order = ?, is_active = ? WHERE id = ? AND restaurant_id = ?");
                                $stmt->execute([$section_id, $name, $slug, $description, max(1, $display_order), $is_active, $id, $restaurantId]);
                            }

                            // Refresh secondary section mappings
                            $secondarySectionIds = $_POST['secondary_section_ids'] ?? [];
                            if (!is_array($secondarySectionIds)) $secondarySectionIds = [];
                            $secondarySectionIds = array_values(array_filter(array_map('intval', $secondarySectionIds)));
                            $secondarySectionIds = array_values(array_filter($secondarySectionIds, function($sid) use ($section_id) {
                                return (int)$sid !== (int)$section_id;
                            }));

                            // Refresh mappings (ignore if migration table doesn't exist yet)
                            try {
                                $pdo->prepare("DELETE FROM category_secondary_sections WHERE category_id = ?")->execute([$id]);
                                $stmtIns = $pdo->prepare("INSERT INTO category_secondary_sections (category_id, section_id, is_active) VALUES (?, ?, ?)");
                                foreach ($secondarySectionIds as $sid) {
                                    $stmtIns->execute([$id, $sid, 1]);
                                }
                            } catch (PDOException $e) {
                                // Optional feature may not have been migrated yet.
                            }

                            // Redirect to prevent form resubmission
                            header('Location: categories.php?' . (isSuperAdmin() && $restaurantId ? 'restaurant_id=' . urlencode($restaurantId) . '&' : '') . 'success=updated');
                            exit;
                        }
                    }
                }
            } catch (PDOException $e) {
                $error = 'Error: ' . $e->getMessage();
            }
        }
    }
    }
}

// Get category for editing
$editCategory = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ? AND restaurant_id = ?");
    $stmt->execute([$_GET['id'], $restaurantId]);
    $editCategory = $stmt->fetch();

    if ($editCategory) {
        try {
            $stmt2 = $pdo->prepare("SELECT section_id FROM category_secondary_sections WHERE category_id = ? AND is_active = 1");
            $stmt2->execute([(int)$editCategory['id']]);
            $editCategorySecondarySectionIds = array_map('intval', $stmt2->fetchAll(PDO::FETCH_COLUMN));
        } catch (Throwable $e) {
            // If migration isn't run yet, keep empty selection.
            $editCategorySecondarySectionIds = [];
        }
    }
}

// Get all sections (for dropdown and list grouping)
$sectionsList = [];
if ($pdo && $restaurantId) {
    try {
        $sectionsList = getSections($restaurantId, false);
    } catch (Throwable $e) {
        $sectionsList = [];
    }
}

// Get all categories (ordered by section then category)
$categories = [];
if ($pdo && $restaurantId) {
    normalizeCategoryDisplayOrder($restaurantId);
    try {
        $stmt = $pdo->prepare("SELECT c.*, s.name AS section_name FROM categories c LEFT JOIN sections s ON s.id = c.section_id WHERE c.restaurant_id = ? ORDER BY COALESCE(s.display_order, 0) ASC, c.display_order ASC, c.name ASC");
        $stmt->execute([$restaurantId]);
        $categories = $stmt->fetchAll();
    } catch (Throwable $e) {
        $stmt = $pdo->prepare("SELECT * FROM categories WHERE restaurant_id = ? ORDER BY display_order ASC, name ASC");
        $stmt->execute([$restaurantId]);
        $categories = $stmt->fetchAll();
        foreach ($categories as &$c) { $c['section_name'] = null; }
    }
}

// Handle success messages
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'created':
            $message = 'Category created successfully';
            break;
        case 'updated':
            $message = 'Category updated successfully';
            break;
        case 'deleted':
            $message = 'Category and all associated menu items deleted successfully';
            break;
    }
}

$pageTitle = 'Category Management';
include __DIR__ . '/../includes/manager-layout.php';
?>

        <div class="page-header">
            <h1 class="page-title">Category Management</h1>
            <p class="page-subtitle">Create and manage menu categories for your restaurant</p>
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

        <!-- Create/Edit Category Modal -->
        <div class="modal" id="categoryModal" style="display: <?php echo $editCategory ? 'flex' : 'none'; ?>;">
            <div class="modal-overlay" onclick="closeCategoryModal()"></div>
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title"><?php echo $editCategory ? 'Edit Category' : 'Create New Category'; ?></h2>
                    <button class="modal-close" onclick="closeCategoryModal()" aria-label="Close">&times;</button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(getCSRFToken()); ?>">
                <input type="hidden" name="action" value="<?php echo $editCategory ? 'update' : 'create'; ?>">
                <?php if ($editCategory): ?>
                    <input type="hidden" name="id" value="<?php echo $editCategory['id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label class="form-label" for="name">Category Name *</label>
                    <input type="text" id="name" name="name" class="form-input" required value="<?php echo htmlspecialchars($editCategory['name'] ?? ''); ?>">
                </div>
                
                <?php if (isSuperAdmin()): ?>
                <div class="form-group">
                    <label class="form-label" for="slug">Slug *</label>
                    <input type="text" id="slug" name="slug" class="form-input" required value="<?php echo htmlspecialchars($editCategory['slug'] ?? ''); ?>">
                </div>
                <?php else: ?>
                    <input type="hidden" id="slug" name="slug" value="<?php echo htmlspecialchars($editCategory['slug'] ?? ''); ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label class="form-label" for="section_id">Section *</label>
                    <select id="section_id" name="section_id" class="form-input" required>
                        <option value="">— Select section —</option>
                        <?php foreach ($sectionsList as $sec): ?>
                            <option value="<?php echo (int)$sec['id']; ?>" <?php echo (isset($editCategory['section_id']) && (int)$editCategory['section_id'] === (int)$sec['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($sec['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p style="margin-top: 6px; font-size: 12px; color: var(--muted);">Categories are grouped under sections on your menu (e.g. Food, Drinks). <a href="sections.php<?php echo isSuperAdmin() && $restaurantId ? '?restaurant_id=' . urlencode($restaurantId) : ''; ?>">Manage sections</a></p>
                </div>

                <div class="form-group">
                    <label class="form-label">Secondary Sections (optional)</label>
                    <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                        <?php
                        $primarySectionId = (int)($editCategory['section_id'] ?? 0);
                        $selectedSecondary = $editCategorySecondarySectionIds ?? [];
                        ?>
                        <?php foreach ($sectionsList as $sec): ?>
                            <?php $sid = (int)$sec['id']; ?>
                            <?php
                                $isSecondary = in_array($sid, $selectedSecondary, true);
                                $isPrimary = ($sid === $primarySectionId && $primarySectionId > 0);
                            ?>
                            <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--muted);<?php echo $isPrimary ? 'display:none;' : ''; ?>">
                                <input type="checkbox"
                                       name="secondary_section_ids[]"
                                       value="<?php echo $sid; ?>"
                                       data-secondary="<?php echo $isSecondary ? '1' : '0'; ?>"
                                       <?php echo ($isSecondary && !$isPrimary) ? 'checked' : ''; ?>
                                       <?php echo $isPrimary ? 'disabled' : ''; ?>>
                                <?php echo htmlspecialchars($sec['name']); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <p style="margin-top: 6px; font-size: 12px; color: var(--muted);">
                        This category will also appear on those section pages only (not on the main full menu page).
                    </p>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="description">Description</label>
                    <textarea id="description" name="description" class="form-textarea" rows="3"><?php echo htmlspecialchars($editCategory['description'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="image">Category Image</label>
                    <input type="file" id="image" name="image" class="form-input" accept="image/*">
                    <?php if ($editCategory && $editCategory['image']): ?>
                        <div style="margin-top: 10px;">
                            <p style="margin-bottom: 5px; color: var(--muted);">Current image:</p>
                            <img src="<?php echo UPLOAD_URL . '/categories/' . htmlspecialchars($editCategory['image']); ?>" alt="Current image" style="max-width: 300px; max-height: 200px; border-radius: 8px; border: 2px solid #e5e7eb;">
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="display_order">Display Order</label>
                    <input type="number" id="display_order" name="display_order" class="form-input" value="<?php echo $editCategory['display_order'] ?? 0; ?>">
                </div>
                
                <div class="form-group">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <input type="checkbox" id="is_active" name="is_active" style="width: 20px; height: 20px;" <?php echo ($editCategory['is_active'] ?? 1) ? 'checked' : ''; ?>>
                        <label class="form-label" for="is_active" style="margin: 0;">Active</label>
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
        
        <!-- Delete Confirmation Modal -->
        <div class="modal" id="deleteModal" style="display: none;">
            <div class="modal-overlay" onclick="closeDeleteModal()"></div>
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title">Delete Category</h2>
                    <button class="modal-close" onclick="closeDeleteModal()" aria-label="Close">&times;</button>
                </div>
                <div class="modal-body">
                    <p style="margin-bottom: 20px; font-size: 16px;">Are you sure you want to delete this category?</p>
                    <p style="margin-bottom: 20px; color: var(--danger); font-weight: 600;">This action cannot be undone. This will delete:</p>
                    <ul style="margin-left: 20px; margin-bottom: 20px; color: var(--muted);">
                        <li>The category and all its information</li>
                        <li>All menu items in this category</li>
                        <li>The category image</li>
                    </ul>
                    <form method="POST" action="" id="deleteForm">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(getCSRFToken()); ?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="deleteCategoryId" value="">
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Cancel</button>
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
        
        <div class="settings-card">
            <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                <h2 class="section-title">All Categories</h2>
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <a href="sections.php<?php echo isSuperAdmin() && $restaurantId ? '?restaurant_id=' . urlencode($restaurantId) : ''; ?>" class="btn btn-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>
                        Manage Sections
                    </a>
                    <?php if (!$editCategory): ?>
                    <button class="btn btn-primary" onclick="openCategoryModal()">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        New Category
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            <div class="table-wrapper categories-table-desktop">
            <table class="table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Section</th>
                        <th>Order</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($categories)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px; color: var(--muted);">No categories found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <td>
                                    <?php if ($category['image']): ?>
                                        <img src="<?php echo UPLOAD_URL . '/categories/' . htmlspecialchars($category['image']); ?>" alt="" class="menu-item-image">
                                    <?php else: ?>
                                        <div class="menu-item-image" style="background: #f0f0f0; display: flex; align-items: center; justify-content: center; color: #999;">No Image</div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($category['name']); ?></td>
                                <td><?php echo htmlspecialchars($category['section_name'] ?? '—'); ?></td>
                                <td><?php echo $category['display_order']; ?></td>
                                <td><span style="padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; background: <?php echo $category['is_active'] ? '#d1fae5' : '#fee2e2'; ?>; color: <?php echo $category['is_active'] ? '#065f46' : '#991b1b'; ?>"><?php echo $category['is_active'] ? 'Active' : 'Inactive'; ?></span></td>
                                <td class="actions-cell">
                                    <button class="actions-btn" type="button" title="Actions">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                        </svg>
                                    </button>
                                    <div class="actions-dropdown">
                                        <a href="?action=edit&id=<?php echo $category['id']; ?><?php echo isSuperAdmin() && isset($restaurantId) && $restaurantId ? '&restaurant_id=' . urlencode($restaurantId) : ''; ?>" class="actions-dropdown-item">Edit</a>
                                        <a href="menu-items.php?category_id=<?php echo $category['id']; ?><?php echo isSuperAdmin() && isset($restaurantId) && $restaurantId ? '&restaurant_id=' . urlencode($restaurantId) : ''; ?>" class="actions-dropdown-item">View Items</a>
                                        <div class="actions-dropdown-divider"></div>
                                        <button type="button" onclick="openDeleteModal(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars(addslashes($category['name'])); ?>')" class="actions-dropdown-item danger">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            </div>

            <div class="categories-mobile" aria-label="Categories (mobile)">
                <?php if (empty($categories)): ?>
                    <p style="text-align:center; padding: 18px; color: var(--muted);">No categories found.</p>
                <?php else: ?>
                    <?php foreach ($categories as $category): ?>
                        <details class="cat-card">
                            <summary class="cat-summary">
                                <div class="cat-left">
                                    <?php if ($category['image']): ?>
                                        <img src="<?php echo UPLOAD_URL . '/categories/' . htmlspecialchars($category['image']); ?>" alt="" class="cat-thumb">
                                    <?php else: ?>
                                        <div class="cat-thumb cat-thumb-empty">No Image</div>
                                    <?php endif; ?>
                                    <div class="cat-main">
                                        <div class="cat-name"><?php echo htmlspecialchars($category['name']); ?></div>
                                        <div class="cat-meta">
                                            <span>Order: <?php echo (int)$category['display_order']; ?></span>
                                            <span class="cat-dot">•</span>
                                            <span><?php echo htmlspecialchars($category['slug']); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="cat-right">
                                    <span class="cat-status" style="background: <?php echo $category['is_active'] ? '#d1fae5' : '#fee2e2'; ?>; color: <?php echo $category['is_active'] ? '#065f46' : '#991b1b'; ?>">
                                        <?php echo $category['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                    <span class="cat-chevron" aria-hidden="true">▾</span>
                                </div>
                            </summary>
                            <div class="cat-body">
                                <?php if (!empty($category['description'])): ?>
                                    <div class="cat-desc"><?php echo nl2br(htmlspecialchars($category['description'])); ?></div>
                                <?php endif; ?>
                                <div class="cat-actions">
                                    <a class="btn btn-secondary" href="?action=edit&id=<?php echo (int)$category['id']; ?><?php echo isSuperAdmin() && isset($restaurantId) && $restaurantId ? '&restaurant_id=' . urlencode($restaurantId) : ''; ?>">Edit</a>
                                    <a class="btn btn-secondary" href="menu-items.php?category_id=<?php echo (int)$category['id']; ?><?php echo isSuperAdmin() && isset($restaurantId) && $restaurantId ? '&restaurant_id=' . urlencode($restaurantId) : ''; ?>">Items</a>
                                    <button type="button" class="btn btn-danger" onclick="openDeleteModal(<?php echo (int)$category['id']; ?>, '<?php echo htmlspecialchars(addslashes($category['name'])); ?>')">Delete</button>
                                </div>
                            </div>
                        </details>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    
<style>
/* Clean Manager Categories Styles */
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

    /* Categories: mobile cards instead of wide table */
    .categories-table-desktop { display: none; }
    .categories-mobile { display: block; }
    .cat-card { background:#fff; border:1px solid #e5e7eb; border-radius:12px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,0.06); }
    .cat-card + .cat-card { margin-top: 12px; }
    .cat-summary { list-style:none; cursor:pointer; padding: 14px; display:flex; align-items:center; justify-content:space-between; gap:12px; }
    .cat-summary::-webkit-details-marker { display:none; }
    .cat-left { display:flex; align-items:center; gap:12px; min-width:0; }
    .cat-thumb { width:48px; height:48px; border-radius:10px; object-fit:cover; border:1px solid #e5e7eb; flex-shrink:0; }
    .cat-thumb-empty { display:flex; align-items:center; justify-content:center; font-size:0.65rem; color:#6b7280; background:#f3f4f6; }
    .cat-main { min-width:0; }
    .cat-name { font-weight:700; color:#111827; font-size:0.95rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width: 220px; }
    .cat-meta { color:#6b7280; font-size:0.8rem; display:flex; gap:8px; align-items:center; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .cat-dot { color:#9ca3af; }
    .cat-right { display:flex; align-items:center; gap:10px; flex-shrink:0; }
    .cat-status { padding:4px 10px; border-radius:999px; font-size:0.7rem; font-weight:700; }
    .cat-chevron { color:#6b7280; transition: transform .15s ease; }
    .cat-card[open] .cat-chevron { transform: rotate(180deg); }
    .cat-body { border-top:1px solid #f3f4f6; padding: 12px 14px 14px; }
    .cat-desc { color:#374151; font-size:0.85rem; line-height:1.5; margin-bottom: 12px; }
    .cat-actions { display:flex; gap:10px; flex-wrap:wrap; }
    .cat-actions .btn { flex:1; justify-content:center; padding:10px 12px; }
    .table-wrapper { overflow: visible; }
}

@media (min-width: 769px) {
    .categories-mobile { display: none; }
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

        // Keep Secondary Sections UI in sync with the Primary Section dropdown (real-time).
        function updateSecondarySectionsUI() {
            const categoryModal = document.getElementById('categoryModal');
            const primarySelect = document.getElementById('section_id');
            if (!categoryModal || !primarySelect) return;

            const primaryId = parseInt(primarySelect.value || '0', 10) || 0;
            const checkboxes = categoryModal.querySelectorAll('input[name="secondary_section_ids[]"]');

            checkboxes.forEach(function(cb) {
                const sid = parseInt(cb.value || '0', 10) || 0;
                const shouldBeSecondary = (cb.dataset.secondary || '0') === '1';
                const label = cb.closest('label');

                if (primaryId && sid === primaryId) {
                    cb.checked = false;
                    cb.disabled = true;
                    if (label) label.style.display = 'none';
                } else {
                    cb.disabled = false;
                    cb.checked = shouldBeSecondary;
                    if (label) label.style.display = 'flex';
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            updateSecondarySectionsUI();
            document.getElementById('section_id')?.addEventListener('change', updateSecondarySectionsUI);
        });
        
        // Category Modal Functions
        function openCategoryModal() {
            document.getElementById('categoryModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
        
        function closeCategoryModal() {
            document.getElementById('categoryModal').style.display = 'none';
            document.body.style.overflow = '';
            // Redirect to clear edit mode
            if (window.location.search.includes('action=edit')) {
                window.location.href = 'categories.php<?php echo isSuperAdmin() && $restaurantId ? '?restaurant_id=' . urlencode($restaurantId) : ''; ?>';
            }
        }
        
        // Delete Modal Functions
        function openDeleteModal(categoryId, categoryName) {
            document.getElementById('deleteCategoryId').value = categoryId;
            const modalBody = document.querySelector('#deleteModal .modal-body');
            const nameParagraph = modalBody.querySelector('p:first-child');
            if (nameParagraph) {
                nameParagraph.innerHTML = 'Are you sure you want to delete <strong>"' + categoryName + '"</strong>?';
            }
            document.getElementById('deleteModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
        
        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
            document.body.style.overflow = '';
        }
        
        // Open modal if editing
        <?php if ($editCategory): ?>
        document.addEventListener('DOMContentLoaded', function() {
            openCategoryModal();
        });
        <?php endif; ?>
    </script>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>

