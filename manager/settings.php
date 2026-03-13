<?php
/**
 * Manager Settings Page
 * Account, Restaurant Details (synced with admin), Password
 * Same restaurants table as admin/restaurants.php - no conflicts
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
requireManager();

require_once __DIR__ . '/../includes/functions.php';

$pdo = getDBConnection();
$message = '';
$error = '';

$managerId = getCurrentUserId();
$restaurantId = getCurrentUserRestaurantId();

// Fallback: if session lacks restaurant_id, fetch from managers table and sync
if (!$restaurantId && $pdo && $managerId) {
    $stmt = $pdo->prepare("SELECT restaurant_id FROM managers WHERE id = ?");
    $stmt->execute([$managerId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row && !empty($row['restaurant_id'])) {
        $restaurantId = (int)$row['restaurant_id'];
        $_SESSION['restaurant_id'] = $restaurantId;
    }
}

$stmt = $pdo->prepare("SELECT id, username, email, created_at FROM managers WHERE id = ?");
$stmt->execute([$managerId]);
$manager = $stmt->fetch(PDO::FETCH_ASSOC);

// Get restaurant info - same table admin uses (restaurants)
$restaurant = null;
if ($restaurantId && $pdo) {
    $stmt = $pdo->prepare("SELECT * FROM restaurants WHERE id = ?");
    $stmt->execute([$restaurantId]);
    $restaurant = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$manager) {
    header('Location: /admin/logout.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRFToken();
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_profile') {
        $newEmail = sanitize($_POST['email'] ?? '');
        $newUsername = sanitize($_POST['username'] ?? '');
        
        if (empty($newEmail) || empty($newUsername)) {
            $error = 'Email and username are required';
        } elseif (!isValidEmail($newEmail)) {
            $error = 'Invalid email address';
        } else {
            $stmt = $pdo->prepare("SELECT id FROM managers WHERE email = ? AND id != ?");
            $stmt->execute([$newEmail, $managerId]);
            if ($stmt->fetch()) {
                $error = 'Email is already taken by another manager';
            } else {
                $stmt = $pdo->prepare("SELECT id FROM managers WHERE username = ? AND id != ?");
                $stmt->execute([$newUsername, $managerId]);
                if ($stmt->fetch()) {
                    $error = 'Username is already taken by another manager';
                } else {
                    try {
                        $stmt = $pdo->prepare("UPDATE managers SET email = ?, username = ?, updated_at = NOW() WHERE id = ?");
                        $stmt->execute([$newEmail, $newUsername, $managerId]);
                        if ($restaurantId) {
                            $stmt = $pdo->prepare("UPDATE restaurants SET manager_email = ? WHERE id = ? AND manager_email = ?");
                            $stmt->execute([$newEmail, $restaurantId, $manager['email']]);
                        }
                        $message = 'Account updated successfully';
                        $_SESSION['username'] = $newUsername;
                        $stmt = $pdo->prepare("SELECT id, username, email, created_at FROM managers WHERE id = ?");
                        $stmt->execute([$managerId]);
                        $manager = $stmt->fetch(PDO::FETCH_ASSOC);
                    } catch (PDOException $e) {
                        $error = 'Error updating account: ' . $e->getMessage();
                    }
                }
            }
        }
    }
    
    if ($action === 'update_restaurant' && $restaurantId) {
        $name = sanitize($_POST['name'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $address = sanitize($_POST['address'] ?? '');
        $whatsapp_link = sanitize($_POST['whatsapp_link'] ?? '');
        $instagram_url = sanitize($_POST['instagram_url'] ?? '');
        $facebook_url = sanitize($_POST['facebook_url'] ?? '');
        $twitter_url = sanitize($_POST['twitter_url'] ?? '');
        $footer_content = sanitize($_POST['footer_content'] ?? '');
        $logo = null;

        if (empty($name)) {
            $error = 'Restaurant name is required';
        } else {
            try {
                // Handle logo upload similar to admin side
                if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                    $uploadResult = uploadFile($_FILES['logo'], UPLOAD_PATH . '/logos');
                    if ($uploadResult['success']) {
                        $logo = $uploadResult['filename'];

                        // Delete old logo if updating
                        $stmt = $pdo->prepare("SELECT logo FROM restaurants WHERE id = ?");
                        $stmt->execute([$restaurantId]);
                        $oldRestaurant = $stmt->fetch();
                        if ($oldRestaurant && $oldRestaurant['logo']) {
                            deleteFile(UPLOAD_PATH . '/logos/' . $oldRestaurant['logo']);
                        }
                    } else {
                        $error = $uploadResult['message'];
                    }
                } else {
                    // Keep existing logo if updating and no new file uploaded
                    $stmt = $pdo->prepare("SELECT logo FROM restaurants WHERE id = ?");
                    $stmt->execute([$restaurantId]);
                    $oldRestaurant = $stmt->fetch();
                    $logo = $oldRestaurant['logo'] ?? null;
                }

                if (!$error) {
                    $stmt = $pdo->prepare("UPDATE restaurants SET name = ?, description = ?, phone = ?, email = ?, address = ?, whatsapp_link = ?, instagram_url = ?, facebook_url = ?, twitter_url = ?, footer_content = ?, logo = ?, updated_at = NOW() WHERE id = ?");
                    $stmt->execute([$name, $description, $phone, $email, $address, $whatsapp_link, $instagram_url, $facebook_url, $twitter_url, $footer_content, $logo, $restaurantId]);
                $message = 'Restaurant details updated successfully';
                $stmt = $pdo->prepare("SELECT * FROM restaurants WHERE id = ?");
                $stmt->execute([$restaurantId]);
                $restaurant = $stmt->fetch(PDO::FETCH_ASSOC);
                }
            } catch (PDOException $e) {
                $error = 'Error updating restaurant: ' . $e->getMessage();
            }
        }
    }
    
    if ($action === 'update_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $error = 'All password fields are required';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'New passwords do not match';
        } elseif (strlen($newPassword) < 6) {
            $error = 'Password must be at least 6 characters';
        } else {
            $stmt = $pdo->prepare("SELECT password_hash FROM managers WHERE id = ?");
            $stmt->execute([$managerId]);
            $managerData = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$managerData || !password_verify($currentPassword, $managerData['password_hash'])) {
                $error = 'Current password is incorrect';
            } else {
                try {
                    require_once __DIR__ . '/../includes/auth.php';
                    $passwordHash = hashPassword($newPassword);
                    $stmt = $pdo->prepare("UPDATE managers SET password_hash = ?, updated_at = NOW() WHERE id = ?");
                    $stmt->execute([$passwordHash, $managerId]);
                    $message = 'Password updated successfully';
                } catch (PDOException $e) {
                    $error = 'Error updating password: ' . $e->getMessage();
                }
            }
        }
    }
}

$pageTitle = 'Settings';
$activeTab = $_POST['tab'] ?? $_GET['tab'] ?? 'account';
if (!in_array($activeTab, ['account', 'restaurant', 'password'])) $activeTab = 'account';
include __DIR__ . '/../includes/manager-layout.php';
?>

<style>
.page-header { margin-bottom: 24px; }
.page-title { font-size: 1.5rem; font-weight: 600; color: var(--text); margin: 0; }
.page-subtitle { color: var(--muted); font-size: 0.875rem; margin-top: 4px; }
.alert { padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; font-size: 0.875rem; }
.alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
.alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
.tabs-container { background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 24px; overflow: hidden; }
.tabs-nav { display: flex; border-bottom: 1px solid #e5e7eb; background: #f9fafb; flex-wrap: wrap; }
.tab-button { padding: 14px 20px; background: transparent; border: none; border-bottom: 2px solid transparent; cursor: pointer; font-size: 0.875rem; font-weight: 500; color: #6b7280; transition: all 0.2s; display: flex; align-items: center; gap: 8px; }
.tab-button:hover { background: #f3f4f6; color: #374151; }
.tab-button.active { color: #111827; border-bottom-color: #111827; background: #fff; }
.tab-button svg { width: 18px; height: 18px; }
.tab-content { display: none; padding: 24px; }
.tab-content.active { display: block; }
.settings-card { background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 24px; margin-bottom: 24px; }
.settings-card:last-child { margin-bottom: 0; }
.section-header { margin-bottom: 20px; padding-bottom: 12px; border-bottom: 1px solid #e5e7eb; }
.section-title { font-size: 1rem; font-weight: 600; color: #111827; }
.form-group { margin-bottom: 16px; }
.form-group label, .form-label { display: block; font-weight: 500; color: #374151; margin-bottom: 6px; font-size: 0.875rem; }
.form-input, .form-textarea { width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.875rem; font-family: inherit; }
.form-input:focus, .form-textarea:focus { outline: none; border-color: var(--primary); }
.form-textarea { resize: vertical; min-height: 80px; }
.btn { display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; border-radius: 6px; font-weight: 500; font-size: 0.875rem; border: none; cursor: pointer; background: var(--primary); color: #fff; }
.btn:hover { background: var(--primary-dark); }
.btn svg { width: 16px; height: 16px; }
.info-row { display: grid; gap: 12px; margin-bottom: 20px; }
.info-row .form-label { margin-bottom: 4px; }
.info-value { padding: 10px 12px; background: #f9fafb; border-radius: 6px; font-weight: 500; color: #374151; }
.form-group-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
@media (max-width: 768px) { .tabs-nav { flex-direction: column; } .tab-button.active { border-left: 2px solid #111827; border-bottom-color: transparent; } .form-group-row { grid-template-columns: 1fr; } }
</style>

<?php if ($message): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>
<?php if ($error): ?>
<div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="page-header">
    <h1 class="page-title">Settings</h1>
    <p class="page-subtitle">Manage your account, restaurant details, and password</p>
</div>

<div class="tabs-container">
    <div class="tabs-nav">
        <button type="button" class="tab-button <?php echo $activeTab === 'account' ? 'active' : ''; ?>" data-tab="account" onclick="switchTab('account')">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            Account
        </button>
        <button type="button" class="tab-button <?php echo $activeTab === 'restaurant' ? 'active' : ''; ?>" data-tab="restaurant" onclick="switchTab('restaurant')">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            Restaurant Details
        </button>
        <button type="button" class="tab-button <?php echo $activeTab === 'password' ? 'active' : ''; ?>" data-tab="password" onclick="switchTab('password')">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
            Password
        </button>
    </div>

    <!-- Account Tab -->
    <div id="tab-account" class="tab-content <?php echo $activeTab === 'account' ? 'active' : ''; ?>">
        <div class="settings-card">
            <div class="section-header">
                <h2 class="section-title">Account Information</h2>
            </div>
            <div class="info-row">
                <div><label class="form-label">Username</label><div class="info-value"><?php echo htmlspecialchars($manager['username'] ?? ''); ?></div></div>
                <div><label class="form-label">Email</label><div class="info-value"><?php echo htmlspecialchars($manager['email'] ?? ''); ?></div></div>
                <?php if ($restaurant): ?>
                <div><label class="form-label">Restaurant</label><div class="info-value"><?php echo htmlspecialchars($restaurant['name'] ?? ''); ?></div></div>
                <?php endif; ?>
                <div><label class="form-label">Account Created</label><div class="info-value"><?php echo !empty($manager['created_at']) ? date('F j, Y g:i A', strtotime($manager['created_at'])) : 'N/A'; ?></div></div>
            </div>
            <form method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(getCSRFToken()); ?>">
                <input type="hidden" name="tab" value="account">
                <input type="hidden" name="action" value="update_profile">
                <div class="form-group">
                    <label class="form-label" for="username">Username *</label>
                    <input type="text" id="username" name="username" class="form-input" required value="<?php echo htmlspecialchars($manager['username'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label" for="email">Email *</label>
                    <input type="email" id="email" name="email" class="form-input" required value="<?php echo htmlspecialchars($manager['email'] ?? ''); ?>">
                </div>
                <button type="submit" class="btn">Save Account</button>
            </form>
        </div>
    </div>

    <!-- Restaurant Details Tab (synced with admin - same restaurants table) -->
    <div id="tab-restaurant" class="tab-content <?php echo $activeTab === 'restaurant' ? 'active' : ''; ?>">
        <?php if ($restaurant): ?>
        <div class="settings-card">
            <div class="section-header">
                <h2 class="section-title">Restaurant Details</h2>
            </div>
            <p style="color: var(--muted); font-size: 0.875rem; margin-bottom: 20px;">Edit your restaurant information. Synced with admin—changes here appear in admin and vice versa. All data is saved to the same database.</p>
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(getCSRFToken()); ?>">
                <input type="hidden" name="tab" value="restaurant">
                <input type="hidden" name="action" value="update_restaurant">
                <div class="form-group">
                    <label class="form-label" for="rest_name">Restaurant Name *</label>
                    <input type="text" id="rest_name" name="name" class="form-input" required value="<?php echo htmlspecialchars($restaurant['name'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label" for="rest_description">Description</label>
                    <textarea id="rest_description" name="description" class="form-input form-textarea" rows="3"><?php echo htmlspecialchars($restaurant['description'] ?? ''); ?></textarea>
                </div>
                <div class="form-group-row">
                    <div class="form-group">
                        <label class="form-label" for="rest_phone">Phone</label>
                        <input type="text" id="rest_phone" name="phone" class="form-input" value="<?php echo htmlspecialchars($restaurant['phone'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="rest_email">Email</label>
                        <input type="email" id="rest_email" name="email" class="form-input" value="<?php echo htmlspecialchars($restaurant['email'] ?? ''); ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="rest_address">Address</label>
                    <textarea id="rest_address" name="address" class="form-input form-textarea" rows="2"><?php echo htmlspecialchars($restaurant['address'] ?? ''); ?></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label" for="rest_logo">Logo</label>
                    <input type="file" id="rest_logo" name="logo" class="form-input" accept="image/*">
                    <?php if (!empty($restaurant['logo'])): ?>
                        <div style="margin-top: 10px;">
                            <p style="margin-bottom: 5px; color: var(--muted);">Current logo:</p>
                            <img src="<?php echo UPLOAD_URL . '/logos/' . htmlspecialchars($restaurant['logo']); ?>" alt="Current logo" style="max-width: 160px; max-height: 160px; border-radius: 8px; border: 2px solid #e5e7eb;">
                        </div>
                    <?php endif; ?>
                    <small style="color: var(--muted); display: block; margin-top: 5px;">Recommended: square or horizontal logo (PNG/JPEG), max ~1MB.</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Social Media Links</label>
                    <p style="color: var(--muted); font-size: 0.8rem; margin-bottom: 12px;">Only links with values will appear as icons in the menu footer.</p>
                    <div class="form-group-row">
                        <div class="form-group">
                            <label class="form-label" for="rest_whatsapp" style="font-size: 0.8rem;">WhatsApp</label>
                            <input type="url" id="rest_whatsapp" name="whatsapp_link" class="form-input" value="<?php echo htmlspecialchars($restaurant['whatsapp_link'] ?? ''); ?>" placeholder="https://wa.me/...">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="rest_instagram" style="font-size: 0.8rem;">Instagram</label>
                            <input type="url" id="rest_instagram" name="instagram_url" class="form-input" value="<?php echo htmlspecialchars($restaurant['instagram_url'] ?? ''); ?>" placeholder="https://instagram.com/...">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="rest_facebook" style="font-size: 0.8rem;">Facebook</label>
                            <input type="url" id="rest_facebook" name="facebook_url" class="form-input" value="<?php echo htmlspecialchars($restaurant['facebook_url'] ?? ''); ?>" placeholder="https://facebook.com/...">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="rest_twitter" style="font-size: 0.8rem;">Twitter</label>
                            <input type="url" id="rest_twitter" name="twitter_url" class="form-input" value="<?php echo htmlspecialchars($restaurant['twitter_url'] ?? ''); ?>" placeholder="https://twitter.com/...">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="rest_footer">Footer Content</label>
                    <textarea id="rest_footer" name="footer_content" class="form-input form-textarea" rows="3"><?php echo htmlspecialchars($restaurant['footer_content'] ?? ''); ?></textarea>
                    <small style="color: var(--muted); display: block; margin-top: 5px;">Optional text displayed in the footer of your menu.</small>
                </div>
                <button type="submit" class="btn">Save Restaurant Details</button>
            </form>
        </div>
        <?php else: ?>
        <div class="settings-card">
            <p style="color: var(--muted);">No restaurant is associated with your account. Please contact your administrator.</p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Password Tab -->
    <div id="tab-password" class="tab-content <?php echo $activeTab === 'password' ? 'active' : ''; ?>">
        <div class="settings-card">
            <div class="section-header">
                <h2 class="section-title">Change Password</h2>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(getCSRFToken()); ?>">
                <input type="hidden" name="tab" value="password">
                <input type="hidden" name="action" value="update_password">
                <div class="form-group">
                    <label class="form-label" for="current_password">Current Password *</label>
                    <input type="password" id="current_password" name="current_password" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="new_password">New Password *</label>
                    <input type="password" id="new_password" name="new_password" class="form-input" required minlength="6">
                    <small style="color: var(--muted); display: block; margin-top: 5px;">Password must be at least 6 characters</small>
                </div>
                <div class="form-group">
                    <label class="form-label" for="confirm_password">Confirm New Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-input" required minlength="6">
                </div>
                <button type="submit" class="btn">Change Password</button>
            </form>
        </div>
    </div>
</div>

<script>
function switchTab(tab) {
    document.querySelectorAll('.tab-button').forEach(b => { b.classList.remove('active'); if (b.dataset.tab === tab) b.classList.add('active'); });
    document.querySelectorAll('.tab-content').forEach(c => { c.classList.remove('active'); if (c.id === 'tab-' + tab) c.classList.add('active'); });
    history.replaceState(null, '', '?tab=' + tab);
}
</script>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
