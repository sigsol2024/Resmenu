<?php
/**
 * Template Management (Super Admin)
 * Admin can view all templates, change their names, and customize template design
 */

require_once __DIR__ . '/../includes/auth.php';
requireSuperAdmin();

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/template-loader.php';

$pdo = getDBConnection();
$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Update template name
    if ($action === 'update_template_name') {
        $templateId = intval($_POST['template_id'] ?? 0);
        $newName = sanitize($_POST['template_name'] ?? '');
        
        if (empty($newName)) {
            $error = 'Template name is required';
        } elseif ($templateId < 1) {
            $error = 'Invalid template ID';
        } else {
            try {
                // Check if template exists in database
                $stmt = $pdo->prepare("SELECT id FROM templates WHERE id = ?");
                $stmt->execute([$templateId]);
                if ($stmt->fetch()) {
                    $stmt = $pdo->prepare("UPDATE templates SET name = ?, updated_at = NOW() WHERE id = ?");
                    $stmt->execute([$newName, $templateId]);
                } else {
                    // Insert new template record
                    $stmt = $pdo->prepare("INSERT INTO templates (id, name, description, is_active) VALUES (?, ?, ?, 1)");
                    $stmt->execute([$templateId, $newName, 'Template ' . $templateId]);
                }
                $message = 'Template name updated successfully';
            } catch (PDOException $e) {
                $error = 'Error updating template name: ' . $e->getMessage();
            }
        }
    }
    
    // Update template marketing (description + cover image for resmenu.net)
    if ($action === 'update_template_marketing') {
        $templateId = intval($_POST['template_id'] ?? 0);
        $description = sanitize($_POST['template_description'] ?? '');
        
        if ($templateId < 1) {
            $error = 'Invalid template ID';
        } else {
            try {
                $previewImage = null;
                $uploadDir = (defined('UPLOAD_PATH') ? UPLOAD_PATH : (dirname(__DIR__) . '/uploads')) . '/template-previews';
                if (!is_dir($uploadDir)) {
                    @mkdir($uploadDir, 0755, true);
                }
                if (!empty($_FILES['template_cover_image']['name']) && $_FILES['template_cover_image']['error'] === UPLOAD_ERR_OK && is_dir($uploadDir)) {
                    $result = uploadFile($_FILES['template_cover_image'], $uploadDir);
                    if ($result['success'] && $result['filename']) {
                        $previewImage = $result['filename'];
                    }
                }
                
                $stmt = $pdo->prepare("SELECT id, preview_image FROM templates WHERE id = ?");
                $stmt->execute([$templateId]);
                $row = $stmt->fetch();
                if ($row) {
                    $newPreview = $previewImage !== null ? $previewImage : $row['preview_image'];
                    $stmt = $pdo->prepare("UPDATE templates SET description = ?, preview_image = ?, updated_at = NOW() WHERE id = ?");
                    $stmt->execute([$description, $newPreview ?: null, $templateId]);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO templates (id, name, description, preview_image, is_active) VALUES (?, ?, ?, ?, 1)");
                    $stmt->execute([$templateId, 'Template ' . $templateId, $description, $previewImage]);
                }
                $message = 'Template description and cover image updated successfully';
            } catch (PDOException $e) {
                $error = 'Error updating template marketing: ' . $e->getMessage();
            }
        }
    }
    
    // Update template customization (default customization settings)
    if ($action === 'update_template_customization') {
        $templateId = intval($_POST['template_id'] ?? 0);
        
        if ($templateId < 1) {
            $error = 'Invalid template ID';
        } else {
            try {
                // Get or create template customization record
                $stmt = $pdo->prepare("SELECT id FROM template_customizations WHERE template_id = ?");
                $stmt->execute([$templateId]);
                $exists = $stmt->fetch();
                
                if ($exists) {
                    $stmt = $pdo->prepare("UPDATE template_customizations SET 
                        menu_title_color = ?, menu_title_size = ?, menu_title_font = ?,
                        price_color = ?, price_size = ?, price_font = ?,
                        description_color = ?, description_size = ?, description_font = ?,
                        category_title_color = ?, category_title_size = ?, category_title_font = ?,
                        background_color = ?, header_background_color = ?,
                        primary_color = ?, secondary_color = ?,
                        updated_at = NOW()
                        WHERE template_id = ?");
                } else {
                    $stmt = $pdo->prepare("INSERT INTO template_customizations (
                        template_id, menu_title_color, menu_title_size, menu_title_font,
                        price_color, price_size, price_font,
                        description_color, description_size, description_font,
                        category_title_color, category_title_size, category_title_font,
                        background_color, header_background_color,
                        primary_color, secondary_color
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                }
                
                $stmt->execute([
                    $templateId,
                    $_POST['menu_title_color'] ?? '#000000',
                    intval($_POST['menu_title_size'] ?? 24),
                    $_POST['menu_title_font'] ?? 'Inter',
                    $_POST['price_color'] ?? '#000000',
                    intval($_POST['price_size'] ?? 18),
                    $_POST['price_font'] ?? 'Inter',
                    $_POST['description_color'] ?? '#666666',
                    intval($_POST['description_size'] ?? 14),
                    $_POST['description_font'] ?? 'Inter',
                    $_POST['category_title_color'] ?? '#000000',
                    intval($_POST['category_title_size'] ?? 20),
                    $_POST['category_title_font'] ?? 'Inter',
                    $_POST['background_color'] ?? '#fffffc',
                    $_POST['header_background_color'] ?? '#fffffc',
                    $_POST['primary_color'] ?? '#111111',
                    $_POST['secondary_color'] ?? '#FFFFFF',
                ]);
                
                $message = 'Template customization updated successfully';
            } catch (PDOException $e) {
                // If table doesn't exist, create it
                if (strpos($e->getMessage(), "doesn't exist") !== false) {
                    try {
                        $pdo->exec("CREATE TABLE IF NOT EXISTS template_customizations (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            template_id INT NOT NULL UNIQUE,
                            menu_title_color VARCHAR(7) DEFAULT '#000000',
                            menu_title_size INT DEFAULT 24,
                            menu_title_font VARCHAR(50) DEFAULT 'Inter',
                            price_color VARCHAR(7) DEFAULT '#000000',
                            price_size INT DEFAULT 18,
                            price_font VARCHAR(50) DEFAULT 'Inter',
                            description_color VARCHAR(7) DEFAULT '#666666',
                            description_size INT DEFAULT 14,
                            description_font VARCHAR(50) DEFAULT 'Inter',
                            category_title_color VARCHAR(7) DEFAULT '#000000',
                            category_title_size INT DEFAULT 20,
                            category_title_font VARCHAR(50) DEFAULT 'Inter',
                            background_color VARCHAR(7) DEFAULT '#fffffc',
                            header_background_color VARCHAR(7) DEFAULT '#fffffc',
                            primary_color VARCHAR(7) DEFAULT '#111111',
                            secondary_color VARCHAR(7) DEFAULT '#FFFFFF',
                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                            FOREIGN KEY (template_id) REFERENCES templates(id) ON DELETE CASCADE
                        )");
                        // Retry insert
                        $stmt = $pdo->prepare("INSERT INTO template_customizations (
                            template_id, menu_title_color, menu_title_size, menu_title_font,
                            price_color, price_size, price_font,
                            description_color, description_size, description_font,
                            category_title_color, category_title_size, category_title_font,
                            background_color, header_background_color,
                            primary_color, secondary_color
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([
                            $templateId,
                            $_POST['menu_title_color'] ?? '#000000',
                            intval($_POST['menu_title_size'] ?? 24),
                            $_POST['menu_title_font'] ?? 'Inter',
                            $_POST['price_color'] ?? '#000000',
                            intval($_POST['price_size'] ?? 18),
                            $_POST['price_font'] ?? 'Inter',
                            $_POST['description_color'] ?? '#666666',
                            intval($_POST['description_size'] ?? 14),
                            $_POST['description_font'] ?? 'Inter',
                            $_POST['category_title_color'] ?? '#000000',
                            intval($_POST['category_title_size'] ?? 20),
                            $_POST['category_title_font'] ?? 'Inter',
                            $_POST['background_color'] ?? '#fffffc',
                            $_POST['header_background_color'] ?? '#fffffc',
                            $_POST['primary_color'] ?? '#111111',
                            $_POST['secondary_color'] ?? '#FFFFFF',
                        ]);
                        $message = 'Template customization created and saved successfully';
                    } catch (PDOException $e2) {
                        $error = 'Error creating template customization: ' . $e2->getMessage();
                    }
                } else {
                    $error = 'Error updating template customization: ' . $e->getMessage();
                }
            }
        }
    }
}

// Get all templates
$availableTemplates = getAvailableTemplates();

// Get template customizations
$templateCustomizations = [];
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM template_customizations");
        $customizations = $stmt->fetchAll();
        foreach ($customizations as $custom) {
            $templateCustomizations[$custom['template_id']] = $custom;
        }
    } catch (PDOException $e) {
        // Table might not exist yet, that's okay
    }
}

$pageTitle = 'Template Management';
include __DIR__ . '/../includes/admin-layout.php';
?>

<style>
/* Clean Template Management Styles */
.page-header {
    margin-bottom: 24px;
}

.page-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--text);
    margin: 0;
}

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
}

.card-header {
    padding: 20px 24px;
    border-bottom: 1px solid #e5e7eb;
    cursor: pointer;
}

.card-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #111827;
    margin: 0;
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

.btn-primary {
    background: #111827;
    color: #fff;
}

.btn-primary:hover {
    background: #374151;
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
.form-select:focus {
    outline: none;
    border-color: #111827;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .card-header {
        padding: 16px;
    }
}
</style>

<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">Template Management</h1>
    <p class="page-subtitle">Manage menu page design templates</p>
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

<div class="card">
            <div class="card-header">
                <h2 class="card-title">Template Management</h2>
            </div>
            <div style="padding: 20px 24px; border-bottom: 1px solid #e5e7eb;">
                <p style="margin: 0; color: #6b7280; font-size: 0.875rem;">Manage templates, change their names, and customize default design settings. These settings will be used as defaults for restaurants using each template.</p>
            </div>
            
            <?php foreach ($availableTemplates as $template): ?>
                <?php 
                $customization = $templateCustomizations[$template['id']] ?? null;
                $isExpanded = isset($_GET['expand']) && $_GET['expand'] == $template['id'];
                ?>
                <div class="card" style="margin-bottom: 24px;">
                    <div class="card-header" style="cursor: pointer;" onclick="toggleTemplate(<?php echo $template['id']; ?>)">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <h2 class="card-title" style="margin: 0;">Template <?php echo $template['id']; ?>: <?php echo htmlspecialchars($template['name']); ?></h2>
                            <span id="toggle-<?php echo $template['id']; ?>" style="font-size: 20px;"><?php echo $isExpanded ? '▼' : '▶'; ?></span>
                        </div>
                    </div>
                    
                    <div id="content-<?php echo $template['id']; ?>" style="display: <?php echo $isExpanded ? 'block' : 'none'; ?>;">
                        <!-- Update Template Name -->
                        <div class="card" style="margin-bottom: 20px;">
                            <div class="card-header">
                                <h3 class="card-title">Template Name</h3>
                            </div>
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="update_template_name">
                                <input type="hidden" name="template_id" value="<?php echo $template['id']; ?>">
                                <div class="form-group">
                                    <label class="form-label">Template Name</label>
                                    <input type="text" name="template_name" class="form-input" value="<?php echo htmlspecialchars($template['name']); ?>" required>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Update Name
                                </button>
                            </form>
                        </div>
                        
                        <!-- Marketing: Description & Cover Image (for resmenu.net templates page) -->
                        <div class="card" style="margin-bottom: 20px;">
                            <div class="card-header">
                                <h3 class="card-title">Marketing (Description &amp; Cover Image)</h3>
                            </div>
                            <p style="margin-bottom: 16px; color: #6b7280; font-size: 0.875rem;">Used on the resmenu.net templates page. Description is shown under the template name; cover image is used as the card preview image.</p>
                            <form method="POST" action="" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="update_template_marketing">
                                <input type="hidden" name="template_id" value="<?php echo $template['id']; ?>">
                                <div class="form-group">
                                    <label class="form-label">Description</label>
                                    <textarea name="template_description" class="form-input" rows="4" placeholder="e.g. Elegant and sophisticated fine dining style..."><?php echo htmlspecialchars($template['description'] ?? ''); ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Cover image (preview card on resmenu.net)</label>
                                    <?php
                                    $previewImg = $template['preview_image'] ?? null;
                                    $previewUrl = $previewImg ? (defined('SITE_URL') ? rtrim(SITE_URL, '/') : '') . '/uploads/template-previews/' . $previewImg : null;
                                    ?>
                                    <?php if ($previewUrl): ?>
                                    <p style="margin-bottom: 8px;"><img src="<?php echo htmlspecialchars($previewUrl); ?>" alt="Current cover" style="max-width: 200px; max-height: 120px; object-fit: contain; border: 1px solid #e5e7eb; border-radius: 6px;"></p>
                                    <?php endif; ?>
                                    <input type="file" name="template_cover_image" class="form-input" accept="image/jpeg,image/png,image/gif,image/webp">
                                    <p style="margin-top: 6px; color: #6b7280; font-size: 0.8rem;">Leave empty to keep current image. JPG, PNG, GIF or WebP.</p>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Save Description &amp; Cover Image
                                </button>
                            </form>
                        </div>
                        
                        <!-- Template Customization -->
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Default Design Customization</h3>
                            </div>
                            <p style="margin-bottom: 20px; color: var(--muted);">These are the default design settings for this template. Restaurants using this template will start with these settings.</p>
                            
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="update_template_customization">
                                <input type="hidden" name="template_id" value="<?php echo $template['id']; ?>">
                                
                                <!-- Menu Title Styling -->
                                <div class="card" style="margin-bottom: 20px;">
                                    <div class="card-header">
                                        <h4 class="card-title">Menu Title Styling</h4>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Color</label>
                                        <div style="display: flex; gap: 15px; align-items: center;">
                                            <input type="color" name="menu_title_color" value="<?php echo htmlspecialchars($customization['menu_title_color'] ?? '#000000'); ?>" style="width: 60px; height: 40px; border: 2px solid #e5e7eb; border-radius: 8px; cursor: pointer;">
                                            <input type="text" name="menu_title_color" class="form-input" value="<?php echo htmlspecialchars($customization['menu_title_color'] ?? '#000000'); ?>" style="flex: 1;">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Font Size (px)</label>
                                        <input type="number" name="menu_title_size" class="form-input" value="<?php echo $customization['menu_title_size'] ?? 24; ?>" min="12" max="72">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Font Family</label>
                                        <select name="menu_title_font" class="form-select">
                                            <option value="Inter" <?php echo ($customization['menu_title_font'] ?? 'Inter') === 'Inter' ? 'selected' : ''; ?>>Inter</option>
                                            <option value="Poppins" <?php echo ($customization['menu_title_font'] ?? 'Inter') === 'Poppins' ? 'selected' : ''; ?>>Poppins</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <!-- Price Styling -->
                                <div class="card" style="margin-bottom: 20px;">
                                    <div class="card-header">
                                        <h4 class="card-title">Price Styling</h4>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Color</label>
                                        <div style="display: flex; gap: 15px; align-items: center;">
                                            <input type="color" name="price_color" value="<?php echo htmlspecialchars($customization['price_color'] ?? '#000000'); ?>" style="width: 60px; height: 40px; border: 2px solid #e5e7eb; border-radius: 8px; cursor: pointer;">
                                            <input type="text" name="price_color" class="form-input" value="<?php echo htmlspecialchars($customization['price_color'] ?? '#000000'); ?>" style="flex: 1;">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Font Size (px)</label>
                                        <input type="number" name="price_size" class="form-input" value="<?php echo $customization['price_size'] ?? 18; ?>" min="12" max="48">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Font Family</label>
                                        <select name="price_font" class="form-select">
                                            <option value="Inter" <?php echo ($customization['price_font'] ?? 'Inter') === 'Inter' ? 'selected' : ''; ?>>Inter</option>
                                            <option value="Poppins" <?php echo ($customization['price_font'] ?? 'Inter') === 'Poppins' ? 'selected' : ''; ?>>Poppins</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <!-- Description Styling -->
                                <div class="card" style="margin-bottom: 20px;">
                                    <div class="card-header">
                                        <h4 class="card-title">Description Styling</h4>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Color</label>
                                        <div style="display: flex; gap: 15px; align-items: center;">
                                            <input type="color" name="description_color" value="<?php echo htmlspecialchars($customization['description_color'] ?? '#666666'); ?>" style="width: 60px; height: 40px; border: 2px solid #e5e7eb; border-radius: 8px; cursor: pointer;">
                                            <input type="text" name="description_color" class="form-input" value="<?php echo htmlspecialchars($customization['description_color'] ?? '#666666'); ?>" style="flex: 1;">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Font Size (px)</label>
                                        <input type="number" name="description_size" class="form-input" value="<?php echo $customization['description_size'] ?? 14; ?>" min="10" max="24">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Font Family</label>
                                        <select name="description_font" class="form-select">
                                            <option value="Inter" <?php echo ($customization['description_font'] ?? 'Inter') === 'Inter' ? 'selected' : ''; ?>>Inter</option>
                                            <option value="Poppins" <?php echo ($customization['description_font'] ?? 'Inter') === 'Poppins' ? 'selected' : ''; ?>>Poppins</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <!-- Category Title Styling -->
                                <div class="card" style="margin-bottom: 20px;">
                                    <div class="card-header">
                                        <h4 class="card-title">Category Title Styling</h4>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Color</label>
                                        <div style="display: flex; gap: 15px; align-items: center;">
                                            <input type="color" name="category_title_color" value="<?php echo htmlspecialchars($customization['category_title_color'] ?? '#000000'); ?>" style="width: 60px; height: 40px; border: 2px solid #e5e7eb; border-radius: 8px; cursor: pointer;">
                                            <input type="text" name="category_title_color" class="form-input" value="<?php echo htmlspecialchars($customization['category_title_color'] ?? '#000000'); ?>" style="flex: 1;">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Font Size (px)</label>
                                        <input type="number" name="category_title_size" class="form-input" value="<?php echo $customization['category_title_size'] ?? 20; ?>" min="12" max="48">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Font Family</label>
                                        <select name="category_title_font" class="form-select">
                                            <option value="Inter" <?php echo ($customization['category_title_font'] ?? 'Inter') === 'Inter' ? 'selected' : ''; ?>>Inter</option>
                                            <option value="Poppins" <?php echo ($customization['category_title_font'] ?? 'Inter') === 'Poppins' ? 'selected' : ''; ?>>Poppins</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <!-- Background Colors -->
                                <div class="card" style="margin-bottom: 20px;">
                                    <div class="card-header">
                                        <h4 class="card-title">Background Colors</h4>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Page Background Color</label>
                                        <div style="display: flex; gap: 15px; align-items: center;">
                                            <input type="color" name="background_color" value="<?php echo htmlspecialchars($customization['background_color'] ?? '#fffffc'); ?>" style="width: 60px; height: 40px; border: 2px solid #e5e7eb; border-radius: 8px; cursor: pointer;">
                                            <input type="text" name="background_color" class="form-input" value="<?php echo htmlspecialchars($customization['background_color'] ?? '#fffffc'); ?>" style="flex: 1;">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Header Background Color</label>
                                        <div style="display: flex; gap: 15px; align-items: center;">
                                            <input type="color" name="header_background_color" value="<?php echo htmlspecialchars($customization['header_background_color'] ?? '#fffffc'); ?>" style="width: 60px; height: 40px; border: 2px solid #e5e7eb; border-radius: 8px; cursor: pointer;">
                                            <input type="text" name="header_background_color" class="form-input" value="<?php echo htmlspecialchars($customization['header_background_color'] ?? '#fffffc'); ?>" style="flex: 1;">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Primary Colors -->
                                <div class="card" style="margin-bottom: 20px;">
                                    <div class="card-header">
                                        <h4 class="card-title">Primary Colors</h4>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Primary Color (Buttons, etc.)</label>
                                        <div style="display: flex; gap: 15px; align-items: center;">
                                            <input type="color" name="primary_color" value="<?php echo htmlspecialchars($customization['primary_color'] ?? '#111111'); ?>" style="width: 60px; height: 40px; border: 2px solid #e5e7eb; border-radius: 8px; cursor: pointer;">
                                            <input type="text" name="primary_color" class="form-input" value="<?php echo htmlspecialchars($customization['primary_color'] ?? '#111111'); ?>" style="flex: 1;">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Secondary Color (Text on buttons)</label>
                                        <div style="display: flex; gap: 15px; align-items: center;">
                                            <input type="color" name="secondary_color" value="<?php echo htmlspecialchars($customization['secondary_color'] ?? '#FFFFFF'); ?>" style="width: 60px; height: 40px; border: 2px solid #e5e7eb; border-radius: 8px; cursor: pointer;">
                                            <input type="text" name="secondary_color" class="form-input" value="<?php echo htmlspecialchars($customization['secondary_color'] ?? '#FFFFFF'); ?>" style="flex: 1;">
                                        </div>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Save Template Customization
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    
    <script>
        function toggleTemplate(templateId) {
            const content = document.getElementById('content-' + templateId);
            const toggle = document.getElementById('toggle-' + templateId);
            
            if (content.style.display === 'none') {
                content.style.display = 'block';
                toggle.textContent = '▼';
            } else {
                content.style.display = 'none';
                toggle.textContent = '▶';
            }
        }
        
        // Sync color inputs
        document.querySelectorAll('input[type="color"]').forEach(colorInput => {
            const textInput = colorInput.nextElementSibling;
            if (textInput && textInput.type === 'text') {
                colorInput.addEventListener('input', function() {
                    textInput.value = this.value;
                });
                textInput.addEventListener('input', function() {
                    colorInput.value = this.value;
                });
            }
        });
    </script>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>

