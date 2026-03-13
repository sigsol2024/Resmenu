<?php
/**
 * Section Management (Manager)
 * Sections contain categories. Access from Categories page via "Manage Sections" button.
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();
if (!isSuperAdmin() && !isManager()) {
    header('Location: /admin/login.php');
    exit;
}

$restaurantId = null;
if (isSuperAdmin()) {
    $restaurantId = isset($_GET['restaurant_id']) ? intval($_GET['restaurant_id']) : null;
    if (!$restaurantId && isset($_POST['restaurant_id'])) {
        $restaurantId = intval($_POST['restaurant_id']);
    }
    if (!$restaurantId && isset($_SESSION['admin_restaurant_id'])) {
        $restaurantId = intval($_SESSION['admin_restaurant_id']);
    }
    if (!$restaurantId) {
        die('Restaurant ID required for admin access. Please provide restaurant_id parameter.');
    }
    $_SESSION['admin_restaurant_id'] = $restaurantId;
} else {
    $restaurantId = getCurrentUserRestaurantId();
    if (!$restaurantId) {
        die('No restaurant associated with your account. Please contact administrator.');
    }
}

$pdo = getDBConnection();
$message = '';
$error = '';

$restaurantSlug = null;
if ($pdo && $restaurantId) {
    try {
        $stmt = $pdo->prepare("SELECT slug FROM restaurants WHERE id = ?");
        $stmt->execute([$restaurantId]);
        $restaurant = $stmt->fetch();
        if ($restaurant && !empty($restaurant['slug'])) {
            $restaurantSlug = $restaurant['slug'];
        }
    } catch (PDOException $e) {
        // If slug lookup fails, we simply won't show the public section view link.
    }
}

$slugParam = (isSuperAdmin() && $restaurantId) ? '?restaurant_id=' . urlencode($restaurantId) : '';
// Build redirect URL so it always has ? (never sections.php&...)
$redirectQuery = (isSuperAdmin() && $restaurantId) ? 'restaurant_id=' . urlencode($restaurantId) . '&' : '';
$redirectUrl = function ($success) use ($redirectQuery) { return 'sections.php?' . $redirectQuery . $success; };

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    requireCSRFToken();
    $id = intval($_POST['id'] ?? 0);
    if ($id > 0 && $restaurantId && $pdo) {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE section_id = ?");
            $stmt->execute([$id]);
            $count = (int) $stmt->fetchColumn();
            if ($count > 0) {
                $error = 'Cannot delete section: it has ' . $count . ' categor' . ($count === 1 ? 'y' : 'ies') . '. Move or delete them first.';
            } else {
                $stmt = $pdo->prepare("DELETE FROM sections WHERE id = ? AND restaurant_id = ?");
                $stmt->execute([$id, $restaurantId]);
                if ($stmt->rowCount()) {
                    header('Location: ' . $redirectUrl('success=deleted'));
                    exit;
                }
                $error = 'Section not found';
            }
        } catch (PDOException $e) {
            $error = 'Error deleting section: ' . $e->getMessage();
        }
    }
}

// Handle create/update
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $restaurantId && $pdo) {
    requireCSRFToken();
    $action = $_POST['action'] ?? '';
    if ($action === 'create' || $action === 'update') {
        $name = sanitize($_POST['name'] ?? '');
        $slug = sanitize($_POST['slug'] ?? '');
        if (empty($slug) && !empty($name)) {
            $slug = generateSlug($name);
        }
        $display_order = max(1, intval($_POST['display_order'] ?? 0));
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        // Handle section image upload (optional)
        $uploadedImageFilename = null;
        if (!empty($_FILES['image']['name'] ?? '')) {
            $uploadResult = uploadFile($_FILES['image'], UPLOAD_PATH . '/sections');
            if (!$uploadResult['success']) {
                $error = $uploadResult['message'];
            } else {
                $uploadedImageFilename = $uploadResult['filename'];
            }
        }

        if (empty($name) || empty($slug)) {
            $error = 'Name is required';
        } else {
            try {
                if ($action === 'create') {
                    reorderSectionsForInsert($restaurantId, $display_order);
                    $stmt = $pdo->prepare("INSERT INTO sections (restaurant_id, name, slug, image, display_order, is_active) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$restaurantId, $name, $slug, $uploadedImageFilename, $display_order, $is_active]);
                    header('Location: ' . $redirectUrl('success=created'));
                    exit;
                }
                $id = intval($_POST['id'] ?? 0);
                if ($id > 0) {
                    $stmt = $pdo->prepare("SELECT display_order FROM sections WHERE id = ? AND restaurant_id = ?");
                    $stmt->execute([$id, $restaurantId]);
                    $old = $stmt->fetch();
                    $oldOrder = $old ? (int)$old['display_order'] : 0;
                    reorderSectionsForUpdate($restaurantId, $id, $oldOrder, $display_order);

                    if ($uploadedImageFilename !== null) {
                        $stmt = $pdo->prepare("UPDATE sections SET name = ?, slug = ?, image = ?, display_order = ?, is_active = ? WHERE id = ? AND restaurant_id = ?");
                        $stmt->execute([$name, $slug, $uploadedImageFilename, $display_order, $is_active, $id, $restaurantId]);
                    } else {
                        $stmt = $pdo->prepare("UPDATE sections SET name = ?, slug = ?, display_order = ?, is_active = ? WHERE id = ? AND restaurant_id = ?");
                        $stmt->execute([$name, $slug, $display_order, $is_active, $id, $restaurantId]);
                    }
                    header('Location: ' . $redirectUrl('success=updated'));
                    exit;
                }
            } catch (PDOException $e) {
                $error = 'Error: ' . $e->getMessage();
            }
        }
    }
}

$editSection = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id']) && $pdo && $restaurantId) {
    $stmt = $pdo->prepare("SELECT * FROM sections WHERE id = ? AND restaurant_id = ?");
    $stmt->execute([$_GET['id'], $restaurantId]);
    $editSection = $stmt->fetch();
}

$sections = [];
if ($pdo && $restaurantId) {
    normalizeSectionDisplayOrder($restaurantId);
    $stmt = $pdo->prepare("SELECT * FROM sections WHERE restaurant_id = ? ORDER BY display_order ASC, name ASC");
    $stmt->execute([$restaurantId]);
    $sections = $stmt->fetchAll();
}

if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'created':
            $message = 'Section created successfully';
            break;
        case 'updated':
            $message = 'Section updated successfully';
            break;
        case 'deleted':
            $message = 'Section deleted successfully';
            break;
    }
}

$pageTitle = 'Manage Sections';
include __DIR__ . '/../includes/manager-layout.php';
?>

        <div class="page-header" style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px;">
            <div>
                <a href="categories.php<?php echo $slugParam; ?>" class="btn btn-secondary" style="margin-bottom: 8px; display: inline-flex; align-items: center; gap: 6px; text-decoration: none;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                    Back to Categories
                </a>
                <h1 class="page-title">Manage Sections</h1>
                <p class="page-subtitle">Sections group categories on your menu (e.g. Food, Drinks, À la Carte). Reorder sections to change how they appear on the menu.</p>
            </div>
            <?php if (!$editSection): ?>
            <button class="btn btn-primary" onclick="openSectionModal()">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                New Section
            </button>
            <?php endif; ?>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- Create/Edit Section Modal -->
        <div class="modal" id="sectionModal" style="display: <?php echo $editSection ? 'flex' : 'none'; ?>;">
            <div class="modal-overlay" onclick="closeSectionModal()"></div>
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title"><?php echo $editSection ? 'Edit Section' : 'Create New Section'; ?></h2>
                    <button class="modal-close" onclick="closeSectionModal()" aria-label="Close">&times;</button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(getCSRFToken()); ?>">
                        <input type="hidden" name="action" value="<?php echo $editSection ? 'update' : 'create'; ?>">
                        <?php if ($editSection): ?>
                            <input type="hidden" name="id" value="<?php echo (int)$editSection['id']; ?>">
                        <?php endif; ?>

                        <div class="form-group">
                            <label class="form-label" for="section_name">Section Name *</label>
                            <input type="text" id="section_name" name="name" class="form-input" required value="<?php echo htmlspecialchars($editSection['name'] ?? ''); ?>">
                        </div>
                        <?php if (isSuperAdmin()): ?>
                        <div class="form-group">
                            <label class="form-label" for="section_slug">Slug *</label>
                            <input type="text" id="section_slug" name="slug" class="form-input" required value="<?php echo htmlspecialchars($editSection['slug'] ?? ''); ?>">
                        </div>
                        <?php else: ?>
                            <input type="hidden" name="slug" id="section_slug" value="<?php echo htmlspecialchars($editSection['slug'] ?? ''); ?>">
                        <?php endif; ?>

                        <div class="form-group">
                            <label class="form-label" for="section_display_order">Display Order</label>
                            <input type="number" id="section_display_order" name="display_order" class="form-input" min="1" value="<?php echo (int)($editSection['display_order'] ?? 1); ?>">
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="section_image">Section Image (optional)</label>
                            <input type="file" id="section_image" name="image" class="form-input" accept="image/*">
                            <?php if (!empty($editSection['image'])): ?>
                                <p style="margin-top: 6px; font-size: 0.8rem;">
                                    Current image:
                                    <code><?php echo htmlspecialchars($editSection['image']); ?></code>
                                </p>
                            <?php endif; ?>
                            <p style="margin-top: 4px; font-size: 0.8rem; color: #6b7280;">
                                Recommended: clear photo that represents this section. Max 1 MB; large images will be auto-optimized.
                            </p>
                        </div>

                        <div class="form-group">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <input type="checkbox" id="section_is_active" name="is_active" style="width: 20px; height: 20px;" <?php echo ($editSection['is_active'] ?? 1) ? 'checked' : ''; ?>>
                                <label class="form-label" for="section_is_active" style="margin: 0;">Active</label>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" onclick="closeSectionModal()">Cancel</button>
                            <button type="submit" class="btn btn-primary"><?php echo $editSection ? 'Update Section' : 'Create Section'; ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div class="modal" id="deleteSectionModal" style="display: none;">
            <div class="modal-overlay" onclick="closeDeleteSectionModal()"></div>
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title">Delete Section</h2>
                    <button class="modal-close" onclick="closeDeleteSectionModal()" aria-label="Close">&times;</button>
                </div>
                <div class="modal-body">
                    <p style="margin-bottom: 20px;">Are you sure you want to delete the section &ldquo;<strong id="deleteSectionName"></strong>&rdquo;?</p>
                    <p style="margin-bottom: 20px; color: var(--muted);">You can only delete a section if it has no categories. Move categories to another section first if needed.</p>
                    <form method="POST" action="" id="deleteSectionForm">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(getCSRFToken()); ?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="deleteSectionId" value="">
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" onclick="closeDeleteSectionModal()">Cancel</button>
                            <button type="submit" class="btn btn-danger">Yes, Delete Section</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="settings-card">
            <h2 class="section-title">All Sections</h2>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Order</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($sections)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 40px; color: var(--muted);">No sections yet. Create one to group your categories (e.g. Food, Drinks).</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($sections as $sec): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($sec['name']); ?></td>
                                    <td><code style="font-size: 12px;"><?php echo htmlspecialchars($sec['slug']); ?></code></td>
                                    <td><?php echo (int)$sec['display_order']; ?></td>
                                    <td>
                                        <span style="padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; background: <?php echo $sec['is_active'] ? '#d1fae5' : '#fee2e2'; ?>; color: <?php echo $sec['is_active'] ? '#065f46' : '#991b1b'; ?>">
                                            <?php echo $sec['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td class="actions-cell">
                                        <a href="?action=edit&id=<?php echo (int)$sec['id']; ?><?php echo $slugParam ? '&' . ltrim($slugParam, '?') : ''; ?>" class="btn btn-small btn-secondary">Edit</a>
                                        <?php
                                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE section_id = ?");
                                        $stmt->execute([$sec['id']]);
                                        $catCount = (int) $stmt->fetchColumn();
                                        $sectionViewUrl = null;
                                        if (!empty($restaurantSlug) && !empty($sec['slug'])) {
                                            $sectionViewUrl = '/restaurant/' . $restaurantSlug . '/' . $sec['slug'];
                                        }
                                        ?>
                                        <?php if ($sectionViewUrl): ?>
                                            <a href="<?php echo htmlspecialchars($sectionViewUrl); ?>" target="_blank" class="btn btn-small btn-primary">View section menu</a>
                                        <?php endif; ?>
                                        <button type="button" class="btn btn-small btn-danger" onclick="openDeleteSectionModal(<?php echo (int)$sec['id']; ?>, '<?php echo htmlspecialchars(addslashes($sec['name'])); ?>')" <?php echo $catCount > 0 ? 'disabled title="Section has categories"' : ''; ?>>Delete</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <style>
        .modal { position: fixed; inset: 0; z-index: 3000; display: flex; align-items: center; justify-content: center; }
        .modal-overlay { position: absolute; inset: 0; background: rgba(0,0,0,0.5); z-index: 3001; }
        .modal-content { position: relative; background: white; border-radius: 8px; max-width: 500px; width: 90%; max-height: 90vh; overflow-y: auto; z-index: 3002; box-shadow: 0 10px 25px rgba(0,0,0,0.2); }
        .modal-header { display: flex; justify-content: space-between; align-items: center; padding: 20px 24px; border-bottom: 1px solid #e5e7eb; }
        .modal-title { font-size: 1.25rem; font-weight: 600; margin: 0; }
        .modal-close { background: none; border: none; font-size: 24px; color: #6b7280; cursor: pointer; padding: 0; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; line-height: 1; }
        .modal-body { padding: 24px; }
        .modal-footer { display: flex; gap: 12px; justify-content: flex-end; padding: 20px 24px; border-top: 1px solid #e5e7eb; }
        .form-group { margin-bottom: 16px; }
        .form-label { display: block; margin-bottom: 6px; font-weight: 500; color: #374151; }
        .form-input { width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.875rem; }
        .btn { display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; border-radius: 6px; font-weight: 500; font-size: 0.875rem; border: none; cursor: pointer; text-decoration: none; }
        .btn-small { padding: 6px 12px; font-size: 0.813rem; }
        .btn-primary { background: #111827; color: #fff; }
        .btn-secondary { background: #f3f4f6; color: #111827; }
        .btn-danger { background: #dc2626; color: white; }
        .btn-danger:disabled { opacity: 0.5; cursor: not-allowed; }
        .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; }
        .alert-success { background: #d1fae5; color: #065f46; }
        .alert-error { background: #fee2e2; color: #991b1b; }
        </style>

        <script>
        function openSectionModal() {
            document.getElementById('sectionModal').style.display = 'flex';
        }
        function closeSectionModal() {
            document.getElementById('sectionModal').style.display = 'none';
        }
        function openDeleteSectionModal(id, name) {
            document.getElementById('deleteSectionId').value = id;
            document.getElementById('deleteSectionName').textContent = name || '';
            document.getElementById('deleteSectionModal').style.display = 'flex';
        }
        function closeDeleteSectionModal() {
            document.getElementById('deleteSectionModal').style.display = 'none';
        }
        <?php if (!isSuperAdmin()): ?>
        document.addEventListener('DOMContentLoaded', function() {
            var nameEl = document.getElementById('section_name');
            var slugEl = document.getElementById('section_slug');
            if (nameEl && slugEl) {
                nameEl.addEventListener('input', function() {
                    if (!slugEl.dataset.manual) slugEl.value = nameEl.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
                });
            }
        });
        <?php endif; ?>
        </script>
