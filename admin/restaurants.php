<?php
/**
 * Restaurant Management (Super Admin)
 */

require_once __DIR__ . '/../includes/auth.php';
requireSuperAdmin();

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/subscription.php';

$pdo = getDBConnection();
$message = '';
$error = '';

// Handle delete action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = intval($_POST['id'] ?? 0);
    if ($id > 0) {
        try {
            // Get restaurant data before deletion
            $stmt = $pdo->prepare("SELECT logo, hero_image, manager_email FROM restaurants WHERE id = ?");
            $stmt->execute([$id]);
            $restaurant = $stmt->fetch();
            
            if ($restaurant) {
                // Delete associated data
                // Delete menu items
                $pdo->prepare("DELETE FROM menu_items WHERE restaurant_id = ?")->execute([$id]);
                // Delete categories
                $pdo->prepare("DELETE FROM categories WHERE restaurant_id = ?")->execute([$id]);
                // Delete manager account
                if ($restaurant['manager_email']) {
                    $pdo->prepare("DELETE FROM managers WHERE restaurant_id = ?")->execute([$id]);
                }
                // Delete customization settings
                $pdo->prepare("DELETE FROM customization_settings WHERE restaurant_id = ?")->execute([$id]);
                // Delete restaurant
                $pdo->prepare("DELETE FROM restaurants WHERE id = ?")->execute([$id]);
                
                // Delete uploaded files
                if ($restaurant['logo']) {
                    deleteFile(UPLOAD_PATH . '/logos/' . $restaurant['logo']);
                }
                if ($restaurant['hero_image']) {
                    deleteFile(UPLOAD_PATH . '/heroes/' . $restaurant['hero_image']);
                }
                
                // Redirect to prevent form resubmission
                header('Location: restaurants.php?success=deleted');
                exit;
            } else {
                $error = 'Restaurant not found';
            }
        } catch (PDOException $e) {
            $error = 'Error deleting restaurant: ' . $e->getMessage();
        }
    }
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create' || $action === 'update') {
        $name = sanitize($_POST['name'] ?? '');
        $slug = sanitize($_POST['slug'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $address = sanitize($_POST['address'] ?? '');
        $whatsapp_link = sanitize($_POST['whatsapp_link'] ?? '');
        $google_rating = isset($_POST['google_rating']) && $_POST['google_rating'] !== '' ? floatval($_POST['google_rating']) : null;
        $rating_source = sanitize($_POST['rating_source'] ?? 'Google');
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        if (empty($name) || empty($slug)) {
            $error = 'Name and slug are required';
        } else {
            try {
                $logo = null;
                $heroImage = null;
                
                // Handle logo upload
                if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                    $uploadResult = uploadFile($_FILES['logo'], UPLOAD_PATH . '/logos');
                    if ($uploadResult['success']) {
                        $logo = $uploadResult['filename'];
                        
                        // Delete old logo if updating
                        if ($action === 'update' && isset($_POST['id'])) {
                            $stmt = $pdo->prepare("SELECT logo FROM restaurants WHERE id = ?");
                            $stmt->execute([$_POST['id']]);
                            $oldRestaurant = $stmt->fetch();
                            if ($oldRestaurant && $oldRestaurant['logo']) {
                                deleteFile(UPLOAD_PATH . '/logos/' . $oldRestaurant['logo']);
                            }
                        }
                    } else {
                        $error = $uploadResult['message'];
                    }
                } else {
                    // Keep existing logo if updating and no new file uploaded
                    if ($action === 'update' && isset($_POST['id'])) {
                        $stmt = $pdo->prepare("SELECT logo FROM restaurants WHERE id = ?");
                        $stmt->execute([$_POST['id']]);
                        $oldRestaurant = $stmt->fetch();
                        $logo = $oldRestaurant['logo'] ?? null;
                    }
                }
                
                // Handle hero image upload
                if (isset($_FILES['hero_image']) && $_FILES['hero_image']['error'] === UPLOAD_ERR_OK) {
                    $uploadResult = uploadFile($_FILES['hero_image'], UPLOAD_PATH . '/heroes');
                    if ($uploadResult['success']) {
                        $heroImage = $uploadResult['filename'];
                        
                        // Delete old hero image if updating
                        if ($action === 'update' && isset($_POST['id'])) {
                            $stmt = $pdo->prepare("SELECT hero_image FROM restaurants WHERE id = ?");
                            $stmt->execute([$_POST['id']]);
                            $oldRestaurant = $stmt->fetch();
                            if ($oldRestaurant && $oldRestaurant['hero_image']) {
                                deleteFile(UPLOAD_PATH . '/heroes/' . $oldRestaurant['hero_image']);
                            }
                        }
                    } else {
                        $error = $uploadResult['message'];
                    }
                } else {
                    // Keep existing hero image if updating and no new file uploaded
                    if ($action === 'update' && isset($_POST['id'])) {
                        $stmt = $pdo->prepare("SELECT hero_image FROM restaurants WHERE id = ?");
                        $stmt->execute([$_POST['id']]);
                        $oldRestaurant = $stmt->fetch();
                        $heroImage = $oldRestaurant['hero_image'] ?? null;
                    }
                }
                
                if (!$error) {
                    if ($action === 'create') {
                        // Get manager email and password
                        $managerEmail = sanitize($_POST['manager_email'] ?? '');
                        $managerPassword = $_POST['manager_password'] ?? '';
                        $managerPasswordConfirm = $_POST['manager_password_confirm'] ?? '';
                        
                        if (empty($managerEmail) || empty($managerPassword)) {
                            $error = 'Manager email and password are required';
                        } elseif ($managerPassword !== $managerPasswordConfirm) {
                            $error = 'Manager passwords do not match';
                        } elseif (!isValidEmail($managerEmail)) {
                            $error = 'Invalid manager email address';
                        } else {
                            try {
                                // Start transaction
                                $pdo->beginTransaction();
                                
                                // Create restaurant
                                $stmt = $pdo->prepare("INSERT INTO restaurants (name, slug, description, phone, email, address, whatsapp_link, logo, hero_image, manager_email, google_rating, rating_source, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                                $stmt->execute([$name, $slug, $description, $phone, $email, $address, $whatsapp_link, $logo, $heroImage, $managerEmail, $google_rating, $rating_source, $is_active]);
                                $restaurantId = $pdo->lastInsertId();
                                
                                // Create manager user account
                                require_once __DIR__ . '/../includes/auth.php';
                                $passwordHash = hashPassword($managerPassword);
                                $username = strtolower(preg_replace('/[^a-z0-9]/', '', $name)) . '_manager';
                                
                                // Ensure username is unique
                                $originalUsername = $username;
                                $counter = 1;
                                $maxIterations = 1000; // Safety limit
                                
                                // #region agent log
                                $logDir = __DIR__ . '/../.cursor';
                                if (!is_dir($logDir)) {
                                    @mkdir($logDir, 0755, true);
                                }
                                @file_put_contents($logDir . '/debug.log', json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'B','location'=>'admin/restaurants.php:94','message'=>'Username generation loop start','data'=>['originalUsername'=>$originalUsername,'counter'=>$counter],'timestamp'=>time()*1000]) . "\n", FILE_APPEND);
                                // #endregion
                                
                                while ($counter <= $maxIterations) {
                                    $checkStmt = $pdo->prepare("SELECT id FROM managers WHERE username = ?");
                                    $checkStmt->execute([$username]);
                                    if (!$checkStmt->fetch()) {
                                        break;
                                    }
                                    $username = $originalUsername . $counter;
                                    $counter++;
                                    
                                    // #region agent log
                                    $logDir = __DIR__ . '/../.cursor';
                                    if (!is_dir($logDir)) {
                                        @mkdir($logDir, 0755, true);
                                    }
                                    @file_put_contents($logDir . '/debug.log', json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'B','location'=>'admin/restaurants.php:103','message'=>'Username generation loop iteration','data'=>['username'=>$username,'counter'=>$counter,'maxIterations'=>$maxIterations],'timestamp'=>time()*1000]) . "\n", FILE_APPEND);
                                    // #endregion
                                }
                                
                                // #region agent log
                                $logDir = __DIR__ . '/../.cursor';
                                if (!is_dir($logDir)) {
                                    @mkdir($logDir, 0755, true);
                                }
                                @file_put_contents($logDir . '/debug.log', json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'B','location'=>'admin/restaurants.php:108','message'=>'Username generation loop end','data'=>['finalUsername'=>$username,'iterations'=>$counter,'maxReached'=>($counter > $maxIterations)],'timestamp'=>time()*1000]) . "\n", FILE_APPEND);
                                // #endregion
                                
                                if ($counter > $maxIterations) {
                                    throw new Exception('Unable to generate unique username after ' . $maxIterations . ' attempts');
                                }
                                
                                $stmt = $pdo->prepare("INSERT INTO managers (username, email, password_hash, restaurant_id) VALUES (?, ?, ?, ?)");
                                $stmt->execute([$username, $managerEmail, $passwordHash, $restaurantId]);
                                
                                // Create default customization settings
                                $stmt = $pdo->prepare("INSERT INTO customization_settings (restaurant_id) VALUES (?)");
                                $stmt->execute([$restaurantId]);
                                
                                // Create trial subscription for the new restaurant
                                // Get the first plan (Basic) for trial
                                $basicPlan = $pdo->query("SELECT id FROM subscription_plans WHERE is_active = 1 ORDER BY display_order ASC LIMIT 1")->fetch();
                                if ($basicPlan) {
                                    $trialPlanId = $basicPlan['id'];
                                    createSubscription($restaurantId, $trialPlanId, 'monthly', true);
                                }
                                
                                // Commit transaction
                                $pdo->commit();
                                
                                // #region agent log
                                $logDir = __DIR__ . '/../.cursor';
                                if (!is_dir($logDir)) {
                                    @mkdir($logDir, 0755, true);
                                }
                                @file_put_contents($logDir . '/debug.log', json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'D','location'=>'admin/restaurants.php:115','message'=>'Transaction committed successfully','data'=>['restaurantId'=>$restaurantId,'slug'=>$slug],'timestamp'=>time()*1000]) . "\n", FILE_APPEND);
                                // #endregion
                                
                                // Redirect to prevent form resubmission
                                header('Location: restaurants.php?success=created');
                                exit;
                                
                                // Redirect to restaurant view page
                                header('Location: restaurant-view.php?slug=' . urlencode($slug));
                                exit;
                            } catch (PDOException $e) {
                                $pdo->rollBack();
                                $error = 'Error creating restaurant: ' . $e->getMessage();
                            }
                        }
                    } else {
                        $id = $_POST['id'] ?? 0;
                        // Build update query based on what's being updated
                        $updateFields = ['name', 'slug', 'description', 'phone', 'email', 'address', 'whatsapp_link', 'google_rating', 'rating_source', 'is_active'];
                        $updateValues = [$name, $slug, $description, $phone, $email, $address, $whatsapp_link, $google_rating, $rating_source, $is_active];
                        
                        if ($logo) {
                            $updateFields[] = 'logo';
                            $updateValues[] = $logo;
                        }
                        if ($heroImage !== null) {
                            $updateFields[] = 'hero_image';
                            $updateValues[] = $heroImage;
                        }
                        
                        $updateValues[] = $id;
                        $placeholders = implode(', ', array_fill(0, count($updateFields), '?'));
                        $setClause = implode(' = ?, ', $updateFields) . ' = ?';
                        $stmt = $pdo->prepare("UPDATE restaurants SET $setClause WHERE id = ?");
                        $stmt->execute($updateValues);
                        
                        // Update manager password if provided
                        if (!empty($_POST['manager_password']) || !empty($_POST['manager_password_confirm'])) {
                            $managerPassword = $_POST['manager_password'] ?? '';
                            $managerPasswordConfirm = $_POST['manager_password_confirm'] ?? '';
                            
                            // Only validate if at least one field is filled
                            if (!empty($managerPassword) || !empty($managerPasswordConfirm)) {
                                if (empty($managerPassword) || empty($managerPasswordConfirm)) {
                                    $error = 'Both password fields must be filled to update password';
                                } elseif ($managerPassword !== $managerPasswordConfirm) {
                                    $error = 'Manager passwords do not match';
                                } else {
                                    require_once __DIR__ . '/../includes/auth.php';
                                    $passwordHash = hashPassword($managerPassword);
                                    
                                    // Get manager email from restaurant
                                    $stmt = $pdo->prepare("SELECT manager_email FROM restaurants WHERE id = ?");
                                    $stmt->execute([$id]);
                                    $restaurantData = $stmt->fetch();
                                    $managerEmail = $restaurantData['manager_email'] ?? '';
                                    
                                    if ($managerEmail) {
                                        // Update manager password
                                        $stmt = $pdo->prepare("UPDATE managers SET password_hash = ? WHERE email = ? AND restaurant_id = ?");
                                        $stmt->execute([$passwordHash, $managerEmail, $id]);
                                    }
                                }
                            }
                        }
                        
                        if (!$error) {
                            $message = 'Restaurant updated successfully';
                            // Redirect to prevent form resubmission
                            header('Location: restaurants.php?success=updated');
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

// Get restaurant for editing
$editRestaurant = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM restaurants WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $editRestaurant = $stmt->fetch();
}

// Get all restaurants
$restaurants = [];
if ($pdo) {
    $restaurants = $pdo->query("SELECT * FROM restaurants ORDER BY created_at DESC")->fetchAll();
}

// Handle success messages
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'created':
            $message = 'Restaurant created successfully';
            break;
        case 'updated':
            $message = 'Restaurant updated successfully';
            break;
        case 'deleted':
            $message = 'Restaurant and all associated data deleted successfully';
            break;
    }
}

$pageTitle = 'Restaurant Management';
include __DIR__ . '/../includes/admin-layout.php';
?>

<style>
/* Clean Restaurant Management Styles */
.page-header {
    margin-bottom: 24px;
}

.page-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--text);
    margin: 0;
}

.page-subtitle {
    font-size: 0.875rem;
    color: #6b7280;
    margin: 4px 0 0;
}

/* Stats cards (match manager dashboard) */
.stats-row {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 20px;
    margin-bottom: 24px;
}

.stat-card {
    background: #fff;
    padding: 16px;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    min-height: 80px;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
}

.stat-card:hover { box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); }

.stat-label {
    font-size: 0.7rem;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 6px;
    font-weight: 600;
}

.stat-value {
    font-size: 1.35rem;
    font-weight: 700;
    color: #111827;
}

/* Filters */
.filters-card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    padding: 20px 24px;
    margin-bottom: 24px;
}

.filters-card h3 {
    font-size: 0.875rem;
    font-weight: 600;
    margin: 0 0 12px;
    color: #111827;
}

.filters-form {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    align-items: flex-end;
}

.filters-form .form-group {
    margin-bottom: 0;
}

.filters-form label {
    display: block;
    font-size: 0.75rem;
    color: #6b7280;
    margin-bottom: 4px;
}

/* Bar chart */
.chart-card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    padding: 24px;
    margin-bottom: 24px;
}

.chart-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #111827;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.simple-bar-chart {
    height: 10rem;
    display: grid;
    grid-auto-flow: column;
    gap: 2%;
    align-items: end;
    padding: 1.5rem 2% 1.5rem;
    position: relative;
}

.simple-bar-chart > .item {
    height: calc(1% * var(--val));
    background-color: var(--clr, #111827);
    border-radius: 4px 4px 0 0;
    position: relative;
}

.simple-bar-chart > .item > .label {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    font-size: 0.75rem;
    color: #6b7280;
    margin-top: 4px;
    text-align: center;
}

.simple-bar-chart > .item > .value {
    position: absolute;
    bottom: 100%;
    left: 0;
    right: 0;
    font-size: 0.875rem;
    font-weight: 600;
    margin-bottom: 4px;
    text-align: center;
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
    color: #fff;
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

.status-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 500;
}

.status-badge.active {
    background: #d1fae5;
    color: #065f46;
}

.status-badge.inactive {
    background: #fee2e2;
    color: #991b1b;
}

.form-group {
    margin-bottom: 16px;
}

.form-group:last-child {
    margin-bottom: 0;
}

.form-group-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
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
    display: flex;
    align-items: center;
    gap: 8px;
}

.modal-title svg {
    width: 18px;
    height: 18px;
    color: #6b7280;
    flex-shrink: 0;
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
    .stats-row {
        grid-template-columns: repeat(2, 1fr);
    }

    .card-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }
    
    .table {
        overflow-x: auto;
        display: block;
    }
    
    .form-group-row {
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

<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">Restaurant Management</h1>
    <p class="page-subtitle">Create and manage restaurants. View stats, revenue, and orders per restaurant.</p>
</div>

<!-- Stats Cards -->
<section class="stats-row" id="stats-section">
    <div class="stat-card">
        <div class="stat-label">Total Revenue</div>
        <div class="stat-value" id="stat-revenue">₦0</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Total Menu Items</div>
        <div class="stat-value" id="stat-menu-items">0</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Total Categories</div>
        <div class="stat-value" id="stat-categories">0</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Total Orders</div>
        <div class="stat-value" id="stat-orders">0</div>
    </div>
</section>

<!-- Filters -->
<div class="filters-card">
    <h3>Filters</h3>
    <form id="overview-filter-form" class="filters-form">
        <div>
            <label for="start_date">Start Date</label>
            <input type="date" id="start_date" name="start_date" class="form-input" style="padding:8px 12px;">
        </div>
        <div>
            <label for="end_date">End Date</label>
            <input type="date" id="end_date" name="end_date" class="form-input" style="padding:8px 12px;">
        </div>
        <div>
            <label for="restaurant_filter">Restaurant</label>
            <select id="restaurant_filter" name="restaurant_id" class="form-select" style="padding:8px 12px;min-width:200px;">
                <option value="">All Restaurants</option>
                <?php foreach ($restaurants as $r): ?>
                <option value="<?php echo (int)$r['id']; ?>"><?php echo htmlspecialchars($r['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary" style="padding:8px 16px;">Apply</button>
    </form>
</div>

<!-- Bar Chart -->
<section class="chart-card" id="chart-section">
    <h2 class="chart-title">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
        </svg>
        Statistics Overview
    </h2>
    <div class="simple-bar-chart" id="bar-chart">
        <!-- Populated by JS -->
    </div>
</section>

<!-- Create/Edit Restaurant Modal -->
        <div class="modal" id="restaurantModal" style="display: <?php echo $editRestaurant ? 'flex' : 'none'; ?>;">
            <div class="modal-overlay" onclick="closeRestaurantModal()"></div>
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title">
                        <?php echo $editRestaurant ? 'Edit Restaurant' : 'Create New Restaurant'; ?>
                    </h2>
                    <button class="modal-close" onclick="closeRestaurantModal()" aria-label="Close">&times;</button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" name="action" value="<?php echo $editRestaurant ? 'update' : 'create'; ?>">
                <?php if ($editRestaurant): ?>
                    <input type="hidden" name="id" value="<?php echo $editRestaurant['id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label class="form-label" for="name">Restaurant Name *</label>
                    <input type="text" id="name" name="name" class="form-input" required value="<?php echo htmlspecialchars($editRestaurant['name'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="slug">Slug * (URL-friendly name)</label>
                    <input type="text" id="slug" name="slug" class="form-input" required value="<?php echo htmlspecialchars($editRestaurant['slug'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="description">Description</label>
                    <textarea id="description" name="description" class="form-textarea" rows="3"><?php echo htmlspecialchars($editRestaurant['description'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="phone">Phone</label>
                    <input type="text" id="phone" name="phone" class="form-input" value="<?php echo htmlspecialchars($editRestaurant['phone'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-input" value="<?php echo htmlspecialchars($editRestaurant['email'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="address">Address</label>
                    <textarea id="address" name="address" class="form-textarea" rows="2"><?php echo htmlspecialchars($editRestaurant['address'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="whatsapp_link">WhatsApp Link</label>
                    <input type="text" id="whatsapp_link" name="whatsapp_link" class="form-input" value="<?php echo htmlspecialchars($editRestaurant['whatsapp_link'] ?? ''); ?>">
                </div>
                
                <div class="form-group-row">
                    <div class="form-group">
                        <label class="form-label" for="rating_source">Rating Source</label>
                        <select id="rating_source" name="rating_source" class="form-select">
                            <option value="Google" <?php echo ($editRestaurant['rating_source'] ?? 'Google') === 'Google' ? 'selected' : ''; ?>>Google</option>
                            <option value="Yelp" <?php echo ($editRestaurant['rating_source'] ?? 'Google') === 'Yelp' ? 'selected' : ''; ?>>Yelp</option>
                            <option value="TripAdvisor" <?php echo ($editRestaurant['rating_source'] ?? 'Google') === 'TripAdvisor' ? 'selected' : ''; ?>>TripAdvisor</option>
                            <option value="Facebook" <?php echo ($editRestaurant['rating_source'] ?? 'Google') === 'Facebook' ? 'selected' : ''; ?>>Facebook</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="google_rating">Rating (0-5)</label>
                        <input type="number" id="google_rating" name="google_rating" class="form-input" step="0.1" min="0" max="5" value="<?php echo htmlspecialchars($editRestaurant['google_rating'] ?? '4.5'); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="logo">Logo</label>
                    <input type="file" id="logo" name="logo" class="form-input" accept="image/*">
                    <?php if ($editRestaurant && $editRestaurant['logo']): ?>
                        <div style="margin-top: 10px;">
                            <p style="margin-bottom: 5px; color: var(--muted);">Current logo:</p>
                            <img src="<?php echo UPLOAD_URL . '/logos/' . htmlspecialchars($editRestaurant['logo']); ?>" alt="Current logo" style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 2px solid #e5e7eb;">
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="hero_image">Hero Image</label>
                    <input type="file" id="hero_image" name="hero_image" class="form-input" accept="image/*">
                    <small style="color: var(--muted); display: block; margin-top: 5px;">Large image displayed on the right side of the hero section</small>
                    <?php if ($editRestaurant && $editRestaurant['hero_image']): ?>
                        <div style="margin-top: 10px;">
                            <p style="margin-bottom: 5px; color: var(--muted);">Current hero image:</p>
                            <img src="<?php echo UPLOAD_URL . '/heroes/' . htmlspecialchars($editRestaurant['hero_image']); ?>" alt="Current hero image" style="max-width: 300px; max-height: 200px; border-radius: 8px; border: 2px solid #e5e7eb;">
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <input type="checkbox" id="is_active" name="is_active" style="width: 20px; height: 20px;" <?php echo ($editRestaurant['is_active'] ?? 1) ? 'checked' : ''; ?>>
                        <label class="form-label" for="is_active" style="margin: 0;">Active</label>
                    </div>
                </div>
                
                <?php if (!$editRestaurant): ?>
                    <hr style="margin: 30px 0; border: none; border-top: 2px solid #e5e7eb;">
                    <h3 style="margin-bottom: 20px; font-weight: 600;">Manager Account</h3>
                    
                    <div class="form-group">
                        <label class="form-label" for="manager_email">Manager Email *</label>
                        <input type="email" id="manager_email" name="manager_email" class="form-input" required placeholder="manager@restaurant.com">
                        <small style="color: var(--muted); display: block; margin-top: 5px;">This email will be used for the manager login</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="manager_password">Manager Password *</label>
                        <input type="password" id="manager_password" name="manager_password" class="form-input" required minlength="6" placeholder="Enter password">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="manager_password_confirm">Confirm Manager Password *</label>
                        <input type="password" id="manager_password_confirm" name="manager_password_confirm" class="form-input" required minlength="6" placeholder="Confirm password">
                    </div>
                <?php else: ?>
                    <?php
                    // Get manager info for this restaurant
                    $managerInfo = null;
                    if ($editRestaurant) {
                        $stmt = $pdo->prepare("SELECT id, username, email FROM managers WHERE restaurant_id = ? LIMIT 1");
                        $stmt->execute([$editRestaurant['id']]);
                        $managerInfo = $stmt->fetch();
                    }
                    ?>
                    <?php if ($managerInfo): ?>
                        <hr style="margin: 30px 0; border: none; border-top: 2px solid #e5e7eb;">
                        <h3 style="margin-bottom: 20px; font-weight: 600;">Manager Account</h3>
                        
                        <div class="form-group">
                            <label class="form-label">Manager Email</label>
                            <input type="text" class="form-input" value="<?php echo htmlspecialchars($managerInfo['email']); ?>" readonly style="background-color: #f9fafb;">
                            <small style="color: var(--muted); display: block; margin-top: 5px;">Manager email cannot be changed. Use this email to login.</small>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="manager_password">Update Manager Password</label>
                            <input type="password" id="manager_password" name="manager_password" class="form-input" minlength="6" placeholder="Leave blank to keep current password">
                            <small style="color: var(--muted); display: block; margin-top: 5px;">Only fill this if you want to change the manager's password</small>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="manager_password_confirm">Confirm New Password</label>
                            <input type="password" id="manager_password_confirm" name="manager_password_confirm" class="form-input" minlength="6" placeholder="Confirm new password">
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
                
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" onclick="closeRestaurantModal()">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <?php echo $editRestaurant ? 'Update Restaurant' : 'Create Restaurant'; ?>
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
                    <h2 class="modal-title">
                        Delete Restaurant
                    </h2>
                    <button class="modal-close" onclick="closeDeleteModal()" aria-label="Close">&times;</button>
                </div>
                <div class="modal-body">
                    <p style="margin-bottom: 20px; font-size: 16px;">Are you sure you want to delete this restaurant?</p>
                    <p style="margin-bottom: 20px; color: var(--danger); font-weight: 600;">This action cannot be undone. This will delete:</p>
                    <ul style="margin-left: 20px; margin-bottom: 20px; color: var(--muted);">
                        <li>The restaurant and all its information</li>
                        <li>All categories</li>
                        <li>All menu items</li>
                        <li>The manager account</li>
                        <li>All uploaded images (logo, hero image, category images, menu item images)</li>
                    </ul>
                    <form method="POST" action="" id="deleteForm">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="deleteRestaurantId" value="">
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Cancel</button>
                            <button type="submit" class="btn btn-danger">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Yes, Delete Restaurant
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">All Restaurants</h2>
                <?php if (!$editRestaurant): ?>
                    <button class="btn btn-primary" onclick="openRestaurantModal()">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Create New Restaurant
                    </button>
                <?php endif; ?>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Revenue</th>
                        <th>Menu Items</th>
                        <th>Categories</th>
                        <th>Orders</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="restaurants-tbody">
                    <tr><td colspan="9" style="text-align: center; padding: 24px; color: #6b7280;">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    
    <script>
        // Auto-generate slug from name
        document.getElementById('name')?.addEventListener('input', function() {
            const slugInput = document.getElementById('slug');
            if (slugInput && !slugInput.value) {
                slugInput.value = this.value.toLowerCase()
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, '');
            }
        });
        
        // Restaurant Modal Functions
        function openRestaurantModal() {
            document.getElementById('restaurantModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
        
        function closeRestaurantModal() {
            document.getElementById('restaurantModal').style.display = 'none';
            document.body.style.overflow = '';
            // Redirect to clear edit mode
            if (window.location.search.includes('action=edit')) {
                window.location.href = 'restaurants.php';
            }
        }
        
        // Delete Modal Functions
        function openDeleteModal(restaurantId, restaurantName) {
            document.getElementById('deleteRestaurantId').value = restaurantId;
            const modalBody = document.querySelector('#deleteModal .modal-body');
            const nameParagraph = modalBody.querySelector('p:first-child');
            if (nameParagraph) {
                var esc = document.createElement('div');
                esc.textContent = restaurantName;
                nameParagraph.innerHTML = 'Are you sure you want to delete <strong>"' + esc.innerHTML + '"</strong>?';
            }
            document.getElementById('deleteModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
        
        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
            document.body.style.overflow = '';
        }
        
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
        
        // Open modal if editing
        <?php if ($editRestaurant): ?>
        document.addEventListener('DOMContentLoaded', function() {
            openRestaurantModal();
        });
        <?php endif; ?>

        // Restaurants overview: stats, bar chart, table
        (function() {
            function esc(s) {
                if (s == null || s === '') return '';
                const t = String(s);
                return t.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
            }

            function loadOverview() {
                const start = document.getElementById('start_date').value || '';
                const end = document.getElementById('end_date').value || '';
                const restaurantId = document.getElementById('restaurant_filter').value || '';
                let url = '../api/orders-analytics.php?action=restaurants_overview&';
                if (start) url += 'start_date=' + encodeURIComponent(start) + '&';
                if (end) url += 'end_date=' + encodeURIComponent(end) + '&';
                if (restaurantId) url += 'restaurant_id=' + encodeURIComponent(restaurantId) + '&';

                const tbody = document.getElementById('restaurants-tbody');
                tbody.innerHTML = '<tr><td colspan="9" style="text-align: center; padding: 24px; color: #6b7280;">Loading...</td></tr>';

                fetch(url).then(r => r.json()).then(function(data) {
                    if (!data.success) {
                        tbody.innerHTML = '<tr><td colspan="9" style="padding: 24px; text-align: center; color: #ef4444;">' + esc(data.message || 'Failed to load data.') + '</td></tr>';
                        return;
                    }
                    const rows = data.restaurants || [];
                    const summary = data.summary || { total_revenue: 0, total_orders: 0, total_menu_items: 0, total_categories: 0 };

                    // Update stats cards
                    document.getElementById('stat-revenue').textContent = '\u20A6' + parseFloat(summary.total_revenue || 0).toLocaleString('en-NG', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
                    document.getElementById('stat-menu-items').textContent = parseInt(summary.total_menu_items || 0, 10).toLocaleString();
                    document.getElementById('stat-categories').textContent = parseInt(summary.total_categories || 0, 10).toLocaleString();
                    document.getElementById('stat-orders').textContent = parseInt(summary.total_orders || 0, 10).toLocaleString();

                    // Update bar chart
                    const chartData = [
                        { label: 'Revenue (\u20A6)', value: parseFloat(summary.total_revenue || 0), color: '#10b981' },
                        { label: 'Orders', value: parseInt(summary.total_orders || 0, 10), color: '#5EB344' },
                        { label: 'Menu Items', value: parseInt(summary.total_menu_items || 0, 10), color: '#F8821A' },
                        { label: 'Categories', value: parseInt(summary.total_categories || 0, 10), color: '#069CDB' }
                    ];
                    const maxVal = Math.max(1, ...chartData.map(d => d.value));
                    const chartEl = document.getElementById('bar-chart');
                    chartEl.innerHTML = chartData.map(function(d) {
                        const pct = maxVal > 0 ? (d.value / maxVal) * 100 : 0;
                        return '<div class="item" style="--clr: ' + d.color + '; --val: ' + pct.toFixed(1) + '"><span class="value">' + (typeof d.value === 'number' && d.value >= 1000 ? d.value.toLocaleString() : d.value) + '</span><span class="label">' + esc(d.label) + '</span></div>';
                    }).join('');

                    // Update table
                    if (rows.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="9" style="padding: 24px; text-align: center; color: #6b7280;">No data found.</td></tr>';
                        return;
                    }
                    tbody.innerHTML = rows.map(function(r) {
                        const slug = encodeURIComponent(r.slug || '');
                        const nameJs = JSON.stringify(String(r.name || '')).replace(/"/g, '&quot;');
                        const isActive = (r.is_active !== undefined && r.is_active != 0 && r.is_active !== '0');
                        return '<tr>' +
                            '<td>' + parseInt(r.id || 0, 10) + '</td>' +
                            '<td>' + esc(r.name) + '</td>' +
                            '<td><code style="background:#f9fafb;padding:4px 8px;border-radius:4px;font-size:12px;">' + esc(r.slug) + '</code></td>' +
                            '<td>\u20A6' + parseFloat(r.total_revenue || 0).toLocaleString('en-NG', { minimumFractionDigits: 0, maximumFractionDigits: 0 }) + '</td>' +
                            '<td>' + parseInt(r.menu_items || 0, 10) + '</td>' +
                            '<td>' + parseInt(r.categories || 0, 10) + '</td>' +
                            '<td>' + parseInt(r.total_orders || 0, 10) + '</td>' +
                            '<td><span class="status-badge ' + (isActive ? 'active' : 'inactive') + '">' + (isActive ? 'Active' : 'Inactive') + '</span></td>' +
                            '<td class="actions-cell">' +
                            '<button class="actions-btn" onclick="toggleDropdown(this)" title="Actions"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/></svg></button>' +
                            '<div class="actions-dropdown">' +
                            '<a href="restaurant-view.php?slug=' + slug + '" class="actions-dropdown-item">Manage</a>' +
                            '<a href="?action=edit&id=' + parseInt(r.id, 10) + '" class="actions-dropdown-item">Edit</a>' +
                            '<a href="/restaurant/' + slug + '" target="_blank" class="actions-dropdown-item">View Menu</a>' +
                            '<div class="actions-dropdown-divider"></div>' +
                            '<button type="button" onclick="openDeleteModal(' + parseInt(r.id, 10) + ', ' + nameJs + ')" class="actions-dropdown-item danger">Delete</button>' +
                            '</div></td></tr>';
                    }).join('');
                }).catch(function() {
                    tbody.innerHTML = '<tr><td colspan="9" style="padding: 24px; text-align: center; color: #ef4444;">Failed to load data.</td></tr>';
                });
            }

            document.getElementById('overview-filter-form').addEventListener('submit', function(e) {
                e.preventDefault();
                loadOverview();
            });

            loadOverview();
        })();
    </script>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>

