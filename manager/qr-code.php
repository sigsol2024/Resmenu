<?php
/**
 * Manager QR Code Dashboard
 * Simplified: Select template and download only
 */

require_once __DIR__ . '/../includes/auth.php';
requireManager();

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/qr-generator.php';
require_once __DIR__ . '/../includes/qr-analytics.php';
require_once __DIR__ . '/../includes/qr-template-helper.php';
require_once __DIR__ . '/../includes/csrf.php';

// Ensure SITE_URL is defined (used for previews and download links)
if (!defined('SITE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptPath = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
    $scriptDir = dirname(dirname($scriptPath));
    $basePath = ($scriptDir === '/' || $scriptDir === '\\' || $scriptDir === '.') ? '' : $scriptDir;
    define('SITE_URL', $protocol . $host . $basePath);
}

$restaurantId = getCurrentUserRestaurantId();
$pdo = getDBConnection();
$message = '';
$error = '';

// Get restaurant slug from URL
$restaurantSlug = $_GET['slug'] ?? '';

// Get restaurant data
$stmt = $pdo->prepare("SELECT * FROM restaurants WHERE id = ?");
$stmt->execute([$restaurantId]);
$restaurant = $stmt->fetch();

if (!$restaurant) {
    die('Restaurant not found.');
}

// Ensure slug is set
if (empty($restaurant['slug'])) {
    if (!empty($restaurantSlug)) {
        $restaurant['slug'] = $restaurantSlug;
    } else {
        $stmt = $pdo->prepare("SELECT slug FROM restaurants WHERE id = ?");
        $stmt->execute([$restaurantId]);
        $slugData = $stmt->fetch();
        if ($slugData && !empty($slugData['slug'])) {
            $restaurant['slug'] = $slugData['slug'];
        }
    }
}

// Get QR code settings (wrap in try/catch so missing table or query error does not 500 the page)
try {
    $qrSettings = getRestaurantQRCodeSettings($restaurantId);
    if (!$qrSettings) {
        createDefaultQRCodeSettings($restaurantId);
        $qrSettings = getRestaurantQRCodeSettings($restaurantId);
    }
} catch (Throwable $e) {
    error_log('QR settings load error (manager/qr-code.php): ' . $e->getMessage());
    $qrSettings = ['qr_template_id' => null, 'restaurant_id' => $restaurantId];
}

// Get available templates
try {
    $stmt = $pdo->prepare("SELECT * FROM qr_templates WHERE is_active = 1 ORDER BY name");
    $stmt->execute();
    $templates = $stmt->fetchAll();
} catch (Throwable $e) {
    error_log('QR templates load error (manager/qr-code.php): ' . $e->getMessage());
    $templates = [];
}

// Handle form submission - ONLY template selection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRFToken();
    
    $action = $_POST['action'] ?? '';
    
    if ($action === 'select_template') {
        $templateId = intval($_POST['qr_template_id'] ?? 0);
        
        if ($templateId < 1) {
            $error = 'Please select a template.';
        } else {
            // Verify template exists and is active
            $stmt = $pdo->prepare("SELECT id, config_json FROM qr_templates WHERE id = ? AND is_active = 1");
            $stmt->execute([$templateId]);
            $template = $stmt->fetch();
            
            if (!$template) {
                $error = 'Selected template is not available.';
            } else {
                // Ensure a row exists (INSERT or no-op if already exists); catch so FK/missing table doesn't 500
                try {
                    createDefaultQRCodeSettings($restaurantId);
                } catch (Throwable $e) {
                    error_log('QR createDefaultQRCodeSettings: ' . $e->getMessage());
                }

                // Save template selection and copy template config as final config
                $templateConfig = is_string($template['config_json'])
                    ? $template['config_json']
                    : json_encode($template['config_json']);

                $stmt = $pdo->prepare("
                    UPDATE restaurant_qr_codes SET
                        qr_template_id = ?,
                        final_config_json = ?,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE restaurant_id = ?
                ");

                if ($stmt->execute([$templateId, $templateConfig, $restaurantId]) && $stmt->rowCount() >= 0) {
                    $message = 'Template selected successfully! You can now download your QR code.';
                    try {
                        $qrSettings = getRestaurantQRCodeSettings($restaurantId);
                    } catch (Throwable $e) {
                        $qrSettings = ['qr_template_id' => $templateId, 'restaurant_id' => $restaurantId];
                    }
                    if (!$qrSettings) {
                        $qrSettings = ['qr_template_id' => $templateId, 'restaurant_id' => $restaurantId];
                    }
                } else {
                    $error = 'Failed to save template selection.';
                }
            }
        }
    }
}

// Get currently selected template info
$selectedTemplate = null;
if ($qrSettings && $qrSettings['qr_template_id']) {
    $stmt = $pdo->prepare("SELECT * FROM qr_templates WHERE id = ?");
    $stmt->execute([$qrSettings['qr_template_id']]);
    $selectedTemplate = $stmt->fetch();
}

// Get analytics summary (fail gracefully if analytics tables not present)
try {
    $analytics = getQRCodeAnalytics($restaurantId);
} catch (Throwable $e) {
    error_log('QR analytics error for restaurant ' . $restaurantId . ': ' . $e->getMessage());
    $analytics = null;
}
if (!$analytics || !is_array($analytics)) {
    $analytics = ['total_scans' => 0];
}
$qrCodeURL = getRestaurantQRCodeURL($restaurant['slug']);

$pageTitle = 'QR Code';
include __DIR__ . '/../includes/manager-layout.php';
?>

<div class="page-header">
    <h1 class="page-title">QR Code</h1>
    <p class="page-subtitle">Select a template and download your restaurant's QR code</p>
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

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;">
    <!-- QR Code Preview & Download -->
    <div class="settings-card">
        <div class="section-header">
            <h2 class="section-title">Your QR Code</h2>
        </div>
        <div style="text-align: center; padding: 20px;">
            <?php if (empty($templates)): ?>
                <div style="padding: 40px;">
                    <p style="color: var(--muted); font-size: 1rem; margin-bottom: 8px;">
                        <strong>No QR Code Templates Available</strong>
                    </p>
                    <p style="color: var(--muted); font-size: 0.875rem;">
                        Please contact your administrator to create QR code templates.
                    </p>
                </div>
            <?php elseif (!$selectedTemplate): ?>
                <div style="padding: 40px;">
                    <p style="color: var(--muted); font-size: 1rem; margin-bottom: 8px;">
                        <strong>No Template Selected</strong>
                    </p>
                    <p style="color: var(--muted); font-size: 0.875rem;">
                        Please select a template from the list to generate your QR code.
                    </p>
                </div>
            <?php else: ?>
                <div style="margin-bottom: 16px;">
                    <span style="display: inline-block; padding: 6px 16px; background: var(--primary); color: white; border-radius: 20px; font-size: 0.875rem; font-weight: 500;">
                        <?php echo htmlspecialchars($selectedTemplate['name']); ?>
                    </span>
                </div>
                
                <div style="background: #f9fafb; border-radius: 12px; padding: 20px; display: inline-block; margin-bottom: 20px;">
                    <img src="<?php echo SITE_URL; ?>/api/qr-generate.php?restaurant_id=<?php echo $restaurantId; ?>&format=png&size=250&t=<?php echo time(); ?>" 
                         alt="QR Code" 
                         style="max-width: 250px; height: auto; display: block;"
                         onerror="this.parentElement.innerHTML='<p style=\'color: var(--muted); padding: 40px;\'>Preview loading...</p>'">
                </div>
                
                <p style="color: var(--muted); margin-bottom: 20px; font-size: 0.875rem;">
                    <strong>URL:</strong> <code style="background: #f3f4f6; padding: 4px 8px; border-radius: 4px;"><?php echo htmlspecialchars($qrCodeURL); ?></code>
                </p>
                
                <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
                    <a href="<?php echo SITE_URL; ?>/api/qr-generate.php?restaurant_id=<?php echo $restaurantId; ?>&format=png&download=1" 
                       class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Download PNG
                    </a>
                    <a href="<?php echo SITE_URL; ?>/api/qr-generate.php?restaurant_id=<?php echo $restaurantId; ?>&format=jpeg&download=1" 
                       class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Download JPEG
                    </a>
                    <a href="<?php echo SITE_URL; ?>/api/qr-generate.php?restaurant_id=<?php echo $restaurantId; ?>&format=pdf&download=1" 
                       class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        Download PDF
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Template Selection -->
    <div class="settings-card">
        <div class="section-header">
            <h2 class="section-title">Select Template</h2>
        </div>
        
        <?php if (empty($templates)): ?>
            <div style="padding: 40px; text-align: center;">
                <p style="color: var(--muted); font-size: 1rem; margin-bottom: 8px;">
                    <strong>No Templates Available</strong>
                </p>
                <p style="color: var(--muted); font-size: 0.875rem;">
                    Please contact your administrator to create QR code templates.
                </p>
            </div>
        <?php else: ?>
            <form method="POST" id="template-form">
                <input type="hidden" name="action" value="select_template">
                <input type="hidden" name="csrf_token" value="<?php echo getCSRFToken(); ?>">
                <input type="hidden" name="qr_template_id" id="selected-template-id" value="<?php echo $qrSettings['qr_template_id'] ?? ''; ?>">
                
                <p style="color: var(--muted); font-size: 0.875rem; margin-bottom: 16px;">
                    Choose a QR code design created by your administrator:
                </p>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 16px; margin-bottom: 20px;">
                    <?php foreach ($templates as $template): 
                        $isSelected = ($qrSettings['qr_template_id'] ?? 0) == $template['id'];
                    ?>
                        <div class="template-card <?php echo $isSelected ? 'selected' : ''; ?>" 
                             data-template-id="<?php echo $template['id']; ?>"
                             onclick="selectTemplate(<?php echo $template['id']; ?>)"
                             style="cursor: pointer; border: 3px solid <?php echo $isSelected ? 'var(--primary)' : '#e5e7eb'; ?>; border-radius: 12px; padding: 12px; text-align: center; transition: all 0.2s; background: <?php echo $isSelected ? '#f0f9ff' : 'white'; ?>;">
                            
                            <div style="font-weight: 600; margin-bottom: 8px; font-size: 0.875rem; color: <?php echo $isSelected ? 'var(--primary)' : 'var(--text)'; ?>;">
                                <?php echo htmlspecialchars($template['name']); ?>
                            </div>
                            
                            <div style="width: 100%; height: 100px; background: #f9fafb; border-radius: 8px; display: flex; align-items: center; justify-content: center; overflow: hidden; margin-bottom: 8px;">
                                <?php
                                $previewSrc = !empty($template['preview_image'])
                                    ? (rtrim(SITE_URL, '/') . '/uploads/qr-templates/' . htmlspecialchars($template['preview_image']))
                                    : (SITE_URL . '/api/qr-template-preview.php?template_id=' . (int)$template['id'] . '&size=100');
                                ?>
                                <img src="<?php echo htmlspecialchars($previewSrc); ?>"
                                     alt="<?php echo htmlspecialchars($template['name']); ?>"
                                     style="max-width: 90%; max-height: 90%; object-fit: contain;"
                                     onerror="this.style.display='none'; this.parentElement.innerHTML='<span style=\'color: var(--muted); font-size: 0.75rem;\'>Preview</span>'">
                            </div>
                            
                            <?php if ($isSelected): ?>
                                <span style="display: inline-block; padding: 4px 10px; background: var(--primary); color: white; border-radius: 12px; font-size: 0.7rem; font-weight: 600;">
                                    ✓ SELECTED
                                </span>
                            <?php else: ?>
                                <span style="display: inline-block; padding: 4px 10px; background: #e5e7eb; color: var(--muted); border-radius: 12px; font-size: 0.7rem;">
                                    Click to select
                                </span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Save Template Selection
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<!-- Quick Stats -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Quick Stats</h2>
    </div>
    <div style="display: grid; grid-template-columns: auto 1fr auto; gap: 24px; align-items: center;">
        <div>
            <div style="font-size: 2rem; font-weight: 700; color: var(--text); margin-bottom: 8px;">
                <?php echo number_format($analytics['total_scans']); ?>
            </div>
            <div style="font-size: 0.875rem; color: var(--muted);">Total Scans</div>
        </div>
        <div></div>
        <div>
            <a href="<?php echo SITE_URL; ?>/manager/qr-analytics.php<?php echo !empty($restaurant['slug']) ? '?slug=' . urlencode($restaurant['slug']) : ''; ?>" class="btn btn-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                View Full Analytics
            </a>
        </div>
    </div>
</div>

<style>
/* Clean QR Code Page Styles */
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

/* Button Styles */
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
}

.card-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #111827;
    margin: 0;
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

.template-card:hover {
    border-color: #111827 !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.template-card.selected {
    border-color: #111827 !important;
    background: #f9fafb !important;
}

@media (max-width: 968px) {
    div[style*="grid-template-columns: 1fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
}
</style>

<script>
function selectTemplate(templateId) {
    // Update hidden input
    document.getElementById('selected-template-id').value = templateId;
    
    // Update visual state
    document.querySelectorAll('.template-card').forEach(card => {
        const cardId = parseInt(card.getAttribute('data-template-id'));
        if (cardId === templateId) {
            card.classList.add('selected');
            card.style.borderColor = 'var(--primary)';
            card.style.background = '#f0f9ff';
        } else {
            card.classList.remove('selected');
            card.style.borderColor = '#e5e7eb';
            card.style.background = 'white';
        }
    });
}
</script>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
