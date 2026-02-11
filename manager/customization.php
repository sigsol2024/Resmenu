<?php
/**
 * Template Selection (Manager)
 * Managers can only select templates, not customize design settings
 */

require_once __DIR__ . '/../includes/auth.php';
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
    $action = $_POST['action'] ?? '';
    
    // Handle template selection
    if ($action === 'save_template') {
        $templateId = intval($_POST['template_id'] ?? 0);
        
        if (!$restaurantId) {
            $error = 'Restaurant not found. Please log in again.';
        } elseif ($templateId < 1) {
            $error = 'Please select a valid template.';
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE restaurants SET template_id = ? WHERE id = ?");
                $stmt->execute([$templateId, $restaurantId]);
                
                // Redirect to avoid form resubmission and ensure page shows fresh data (rowCount can be 0 if value unchanged)
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

// Show success message from redirect
if (isset($_GET['message']) && $_GET['message'] === 'template_updated') {
    $message = 'Template updated successfully';
}

// Store template ID BEFORE layout include (sidebar overwrites $restaurant with only name/logo)
$currentTemplateId = isset($restaurant['template_id']) ? (int)$restaurant['template_id'] : 1;

$pageTitle = 'Template Selection';
include __DIR__ . '/../includes/manager-layout.php';

require_once __DIR__ . '/../includes/template-loader.php';
$availableTemplates = getAvailableTemplates();
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
                <input type="hidden" name="action" value="save_template">
                <div class="form-group">
                    <label class="form-label">Select Template</label>
                    <select name="template_id" class="form-select">
                        <?php foreach ($availableTemplates as $template): ?>
                            <option value="<?php echo $template['id']; ?>" <?php echo $currentTemplateId == $template['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($template['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">
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
        
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Template Preview</h2>
            </div>
            <p style="margin-bottom: 20px; color: var(--muted);">Your restaurant menu page will use the selected template design. You can preview it by visiting your restaurant's public menu page.</p>
            <div style="text-align: center; padding: 20px;">
                <?php 
                // Ensure slug is available - try multiple methods
                $viewSlug = $restaurant['slug'] ?? '';
                if (empty($viewSlug)) {
                    // Try URL slug first
                    $viewSlug = $restaurantSlug ?? '';
                    if (empty($viewSlug) && $restaurantId && $pdo) {
                        // Fallback to database
                        $stmt = $pdo->prepare("SELECT slug FROM restaurants WHERE id = ?");
                        $stmt->execute([$restaurantId]);
                        $slugData = $stmt->fetch();
                        if ($slugData && !empty($slugData['slug'])) {
                            $viewSlug = $slugData['slug'];
                            $restaurant['slug'] = $viewSlug; // Update restaurant array
                        }
                    }
                }
                
                if (!empty($viewSlug)):
                ?>
                    <a href="/restaurant/<?php echo htmlspecialchars($viewSlug); ?>" target="_blank" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                        View Menu Page
                    </a>
                <?php else: ?>
                    <p style="color: var(--danger);">Error: Restaurant slug is missing. Please contact administrator.</p>
                <?php endif; ?>
            </div>
        </div>

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

