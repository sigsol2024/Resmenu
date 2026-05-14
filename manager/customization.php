<?php
/**
 * Template Selection & Customization (Manager)
 * Managers can select templates and customize all colors/styles per template
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
requireManager();

require_once __DIR__ . '/../includes/functions.php';

$restaurantId = getCurrentUserRestaurantId();
$pdo = getDBConnection();
$message = '';
$error = '';

// Get restaurant slug from URL (manager pages use /manager/{slug})
$restaurantSlug = $_GET['slug'] ?? '';

// Get restaurant data (make it available to sidebar)
$stmt = $pdo->prepare("SELECT * FROM restaurants WHERE id = ?");
$stmt->execute([$restaurantId]);
$restaurant = $stmt->fetch();

// Ensure restaurant slug is available for sidebar navigation
if (!$restaurant) {
    die('Restaurant not found or access denied.');
}

// Ensure slug is set - use URL slug if available, otherwise use database slug
if (empty($restaurant['slug']) || !isset($restaurant['slug'])) {
    if (!empty($restaurantSlug)) {
        $restaurant['slug'] = $restaurantSlug;
    } else {
        // Get slug from database
        $stmt = $pdo->prepare("SELECT slug FROM restaurants WHERE id = ?");
        $stmt->execute([$restaurantId]);
        $slugData = $stmt->fetch();
        if ($slugData && !empty($slugData['slug'])) {
            $restaurant['slug'] = $slugData['slug'];
        }
    }
}

// Handle form submission (POST-Redirect-GET to prevent form resubmission and ensure fresh data)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRFToken();
    $action = $_POST['action'] ?? '';
    
    // Handle template selection
    if ($action === 'save_template') {
        $templateId = intval($_POST['template_id'] ?? 0);
        
        if (!$restaurantId) {
            $error = 'Restaurant not found. Please log in again.';
        } elseif ($templateId < 1) {
            $error = 'Please select a valid template.';
        } else {
            require_once __DIR__ . '/../includes/template-loader.php';
            $availableForRestaurant = getTemplatesAvailableForRestaurant($restaurantId);
            $canUseIds = array_column(array_filter($availableForRestaurant, function ($t) { return !empty($t['can_use']); }), 'id');
            if (!in_array($templateId, $canUseIds)) {
                $error = 'This template is assigned to you but requires a higher plan to use. Please upgrade your subscription to use it.';
            } else {
                try {
                    $stmt = $pdo->prepare("UPDATE restaurants SET template_id = ? WHERE id = ?");
                    $stmt->execute([$templateId, $restaurantId]);
                    
                    $redirectUrl = '/manager/customization.php';
                    if (!empty($restaurant['slug'])) {
                        $redirectUrl .= '?slug=' . urlencode($restaurant['slug']);
                    }
                    $redirectUrl .= (strpos($redirectUrl, '?') !== false ? '&' : '?') . 'message=template_updated';
                    header('Location: ' . $redirectUrl);
                    exit;
                } catch (PDOException $e) {
                    $error = 'Error updating template: ' . $e->getMessage();
                }
            }
        }
    }
    
    // Handle manager feature toggles (ordering & reservations)
    if ($action === 'save_feature_toggles') {
        $enableOrdering = isset($_POST['enable_food_ordering']) ? 1 : 0;
        $enableReservations = isset($_POST['enable_table_reservations']) ? 1 : 0;
        
        try {
            $stmt = $pdo->prepare("UPDATE restaurants SET enable_food_ordering = ?, enable_table_reservations = ? WHERE id = ?");
            $stmt->execute([$enableOrdering, $enableReservations, $restaurantId]);
            
            $redirectUrl = '/manager/customization.php';
            if (!empty($restaurant['slug'])) {
                $redirectUrl .= '?slug=' . urlencode($restaurant['slug']);
            }
            $redirectUrl .= (strpos($redirectUrl, '?') !== false ? '&' : '?') . 'message=features_updated';
            header('Location: ' . $redirectUrl);
            exit;
        } catch (PDOException $e) {
            $error = 'Error saving feature toggles: ' . $e->getMessage();
        }
    }
    
    // Handle full customization save (per-template)
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
        if ($templateId >= 1) {
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
                $redirectUrl = '/manager/customization.php';
                if (!empty($restaurant['slug'])) $redirectUrl .= '?slug=' . urlencode($restaurant['slug']);
                $redirectUrl .= (strpos($redirectUrl, '?') !== false ? '&' : '?') . 'message=customization_updated';
                header('Location: ' . $redirectUrl);
                exit;
            } catch (PDOException $e) {
                $error = 'Error saving customization: ' . $e->getMessage();
            }
        } else {
            $error = 'Invalid template.';
        }
    }
}

// Show success message from redirect
if (isset($_GET['message'])) {
    if ($_GET['message'] === 'template_updated') $message = 'Template updated successfully';
    elseif ($_GET['message'] === 'customization_updated') $message = 'Template colors and styles saved. Each template keeps its own settings when you switch.';
    elseif ($_GET['message'] === 'features_updated') $message = 'Ordering & reservation settings updated for your menu.';
    elseif ($_GET['message'] === 'ordering_disabled') $message = 'Food ordering is turned off for your public menu. Turn it back on under Ordering & reservations below.';
    elseif ($_GET['message'] === 'reservations_disabled') $message = 'Table reservations are turned off for your public menu. Turn them back on under Ordering & reservations below.';
}

// Store template ID BEFORE layout include (sidebar overwrites $restaurant with only name/logo)
$currentTemplateId = isset($restaurant['template_id']) ? (int)$restaurant['template_id'] : 1;

$pageTitle = 'Template Selection';
include __DIR__ . '/../includes/manager-layout.php';

require_once __DIR__ . '/../includes/template-loader.php';
$availableTemplates = getTemplatesAvailableForRestaurant($restaurantId);
$templatesCanUse = array_filter($availableTemplates, function ($t) { return !empty($t['can_use']); });
$templatesUpgradeRequired = array_filter($availableTemplates, function ($t) { return !empty($t['can_see']) && empty($t['can_use']); });
$customization = getCustomizationSettings($restaurantId, $currentTemplateId);

// Current manager toggles (default to enabled if columns missing)
$enableFoodOrdering = array_key_exists('enable_food_ordering', (array)$restaurant)
    ? (int)$restaurant['enable_food_ordering']
    : 1;
$enableTableReservations = array_key_exists('enable_table_reservations', (array)$restaurant)
    ? (int)$restaurant['enable_table_reservations']
    : 1;

// Plan-level feature availability (used to disable toggles when plan does not include a feature)
require_once __DIR__ . '/../includes/subscription.php';
$planHasOrdering = hasFeatureAccess($restaurantId, 'food_ordering');
$planHasReservations = hasFeatureAccess($restaurantId, 'table_reservations');
?>

        <div class="page-header">
            <h1 class="page-title">Template Selection</h1>
            <p class="page-subtitle">Choose a design template for your restaurant's menu page</p>
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

        <!-- Template Selection -->
        <div class="settings-card">
            <div class="section-header">
                <h2 class="section-title">Select Template</h2>
            </div>
            <p style="margin-bottom: 20px; color: var(--muted); font-size: 0.875rem;">Choose a design template for your restaurant's menu page.</p>
            <form method="POST" action="/manager/customization.php<?php echo !empty($restaurant['slug']) ? '?slug=' . htmlspecialchars(urlencode($restaurant['slug'])) : ''; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(getCSRFToken()); ?>">
                <input type="hidden" name="action" value="save_template">
                <div class="form-group">
                    <label class="form-label">Select Template</label>
                    <select name="template_id" class="form-select">
                        <?php
                        $currentInCanUse = in_array($currentTemplateId, array_column($templatesCanUse, 'id'));
                        foreach ($templatesCanUse as $template): ?>
                            <option value="<?php echo $template['id']; ?>" <?php echo ($currentTemplateId == $template['id'] && $currentInCanUse) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($template['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (empty($templatesCanUse)): ?>
                        <p style="margin-top: 8px; color: var(--muted); font-size: 0.875rem;">No templates available for your plan. Contact support or upgrade to access more templates.</p>
                    <?php endif; ?>
                </div>
                <?php if (!empty($templatesUpgradeRequired)): ?>
                    <p style="margin-bottom: 16px; color: #6b7280; font-size: 0.875rem;">Templates assigned to you (upgrade required to use): <?php echo htmlspecialchars(implode(', ', array_column($templatesUpgradeRequired, 'name'))); ?> — <a href="/manager/billing.php<?php echo !empty($restaurant['slug']) ? '?slug=' . urlencode($restaurant['slug']) : ''; ?>">Upgrade plan</a></p>
                <?php endif; ?>
                <button type="submit" class="btn btn-primary" <?php echo empty($templatesCanUse) ? ' disabled' : ''; ?>>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Save Template
                </button>
            </form>
            <p style="margin-top: 15px; color: var(--muted); font-size: 14px;">
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
        
        <!-- Template Colors & Styles (per-template) - Accordion -->
        <div class="settings-card template-colors-card" style="margin-top: 24px;">
            <div class="section-header template-colors-toggle" id="template-colors-toggle" style="display:flex;align-items:center;justify-content:space-between;cursor:pointer;">
                <div>
                    <h2 class="section-title">Template Colors & Styles</h2>
                    <p style="margin: 4px 0 0; color: var(--muted); font-size: 0.8rem;">Click to expand and customize colors for this template.</p>
                </div>
                <span id="template-colors-chevron" style="font-size: 1rem; color: #6b7280; transition: transform 0.2s;">▼</span>
            </div>
            <div class="template-colors-body" id="template-colors-body" style="display: none; margin-top: 16px;">
                <p style="margin-bottom: 16px; color: var(--muted); font-size: 0.875rem;">Customize all colors and styles for the selected template. Each template remembers its own settings when you switch between them.</p>
                <form method="POST" action="/manager/customization.php<?php echo !empty($restaurant['slug']) ? '?slug=' . htmlspecialchars(urlencode($restaurant['slug'])) : ''; ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(getCSRFToken()); ?>">
                    <input type="hidden" name="action" value="save_customization">
                    <input type="hidden" name="template_id" value="<?php echo $currentTemplateId; ?>">
                
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
                        <h2 class="card-title">Background & Accent Colors</h2>
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
                        Save Colors & Styles
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Ordering & Reservations (manager-level toggles) -->
        <div class="settings-card" style="margin-top: 24px;">
            <div class="section-header">
                <h2 class="section-title">Ordering & Reservations</h2>
            </div>
            <p style="margin-bottom: 16px; color: var(--muted); font-size: 0.875rem;">
                Turn food ordering and table reservations on or off for your menu page. These settings apply to any template you select.
            </p>
            <form method="POST" action="/manager/customization.php<?php echo !empty($restaurant['slug']) ? '?slug=' . htmlspecialchars(urlencode($restaurant['slug'])) : ''; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(getCSRFToken()); ?>">
                <input type="hidden" name="action" value="save_feature_toggles">
                
                <div class="form-group">
                    <label class="form-label" style="display:flex;align-items:center;gap:8px;">
                        <input type="checkbox" name="enable_food_ordering" value="1"
                               <?php echo $enableFoodOrdering ? 'checked' : ''; ?>
                               <?php echo !$planHasOrdering ? 'disabled' : ''; ?>>
                        <span>Enable food ordering on my menu</span>
                    </label>
                    <p style="margin-top: 4px; color: var(--muted); font-size: 0.8rem;">
                        When turned off, all “Add to bag” buttons are hidden on your public menu, even if your plan includes ordering.
                    </p>
                    <?php if (!$planHasOrdering): ?>
                        <p style="margin-top: 4px; color: #b91c1c; font-size: 0.8rem;">
                            Your current plan does not include food ordering. <a href="/manager/billing.php<?php echo !empty($restaurant['slug']) ? '?slug=' . urlencode($restaurant['slug']) : ''; ?>">Upgrade your plan</a> to enable this feature.
                        </p>
                    <?php endif; ?>
                </div>
                
                <div class="form-group" style="margin-top: 12px;">
                    <label class="form-label" style="display:flex;align-items:center;gap:8px;">
                        <input type="checkbox" name="enable_table_reservations" value="1"
                               <?php echo $enableTableReservations ? 'checked' : ''; ?>
                               <?php echo !$planHasReservations ? 'disabled' : ''; ?>>
                        <span>Enable table reservations on my menu</span>
                    </label>
                    <p style="margin-top: 4px; color: var(--muted); font-size: 0.8rem;">
                        When turned off, all “Reserve Table” buttons and reservation entry points are hidden on your public menu.
                    </p>
                    <?php if (!$planHasReservations): ?>
                        <p style="margin-top: 4px; color: #b91c1c; font-size: 0.8rem;">
                            Your current plan does not include table reservations. <a href="/manager/billing.php<?php echo !empty($restaurant['slug']) ? '?slug=' . urlencode($restaurant['slug']) : ''; ?>">Upgrade your plan</a> to enable this feature.
                        </p>
                    <?php endif; ?>
                </div>
                
                <button type="submit" class="btn btn-primary" style="margin-top: 12px;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Save Settings
                </button>
            </form>
        </div>

<script>
// Accordion for Template Colors & Styles (starts closed on every page load)
document.addEventListener('DOMContentLoaded', function() {
    var toggle = document.getElementById('template-colors-toggle');
    var body = document.getElementById('template-colors-body');
    var chevron = document.getElementById('template-colors-chevron');
    if (!toggle || !body) return;
    
    body.style.display = 'none';
    
    toggle.addEventListener('click', function() {
        var isOpen = body.style.display === 'block';
        body.style.display = isOpen ? 'none' : 'block';
        if (chevron) {
            chevron.style.transform = isOpen ? 'rotate(0deg)' : 'rotate(180deg)';
        }
    });
});
</script>

<style>
/* Clean Button and Icon Styles */
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
    background: #111827;
    color: #fff;
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

.color-input-group {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 16px;
    margin-bottom: 20px;
    padding: 20px 24px;
}

.form-group {
    margin-bottom: 20px;
}

.form-input {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 0.875rem;
    color: #111827;
}

.form-label {
    display: block;
    margin-bottom: 6px;
    font-size: 0.875rem;
    font-weight: 500;
    color: #111827;
}

.form-select {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 0.875rem;
    color: #111827;
    transition: border-color 0.2s;
}

.form-select:focus {
    outline: none;
    border-color: #111827;
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
}

.card-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #111827;
    margin: 0;
}
</style>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>

