<?php
/**
 * Super Admin Settings
 * Site settings, email test, and admin profile
 */

require_once __DIR__ . '/../includes/auth.php';
requireSuperAdmin();

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/config.php';

$pdo = getDBConnection();
$message = '';
$error = '';

$adminId = getCurrentUserId();
$stmt = $pdo->prepare("SELECT id, username, email, created_at FROM admins WHERE id = ?");
$stmt->execute([$adminId]);
$admin = $stmt->fetch();

if (!$admin) {
    header('Location: /admin/logout.php');
    exit;
}

$siteSettings = getSiteSettings();
$siteLogoUrl = !empty($siteSettings['site_logo']) ? (UPLOAD_URL . '/site/' . $siteSettings['site_logo']) : null;
$faviconUrl = !empty($siteSettings['favicon']) ? (UPLOAD_URL . '/site/' . $siteSettings['favicon']) : null;

$siteUploadPath = UPLOAD_PATH . '/site';
if (!is_dir($siteUploadPath)) {
    @mkdir($siteUploadPath, 0755, true);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'test_email') {
        $testEmail = trim($_POST['test_email'] ?? '');
        if (empty($testEmail) || !isValidEmail($testEmail)) {
            $error = 'Please enter a valid email address';
        } else {
            require_once __DIR__ . '/../includes/mail.php';
            $siteName = $siteSettings['site_name'] ?? 'Resmenu';
            $body = '<h2 style="margin:0 0 16px;font-size:22px;font-weight:700;color:#111827;">Email Configuration Test</h2>
                <p style="margin:0 0 12px;">Hello,</p>
                <p style="margin:0 0 16px;">This is a test email from <strong>' . htmlspecialchars($siteName) . '</strong>.</p>
                <p style="margin:0 0 16px;">If you received this, your SMTP configuration is working correctly and emails will be delivered in the branded template.</p>
                <p style="margin:0;padding:16px;background:#f9fafb;border-radius:8px;font-size:13px;color:#6b7280;">Sent at: ' . date('F j, Y \a\t g:i A') . '</p>';
            $html = getSiteEmailTemplate('Email Test', $body, $siteSettings);
            if (sendEmail($testEmail, '', 'Test Email - ' . $siteName, $html)) {
                $message = 'Test email sent successfully to ' . htmlspecialchars($testEmail);
            } else {
                $error = 'Failed to send test email. Check your SMTP configuration and error logs.';
            }
        }
    }

    if ($action === 'update_site') {
        $siteName = sanitize($_POST['site_name'] ?? 'Resmenu');
        if (empty($siteName)) $siteName = 'Resmenu';

        $siteLogo = $siteSettings['site_logo'] ?? null;
        $favicon = $siteSettings['favicon'] ?? null;

        if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadFile($_FILES['site_logo'], $siteUploadPath);
            if ($uploadResult['success']) {
                if ($siteLogo) deleteFile($siteUploadPath . '/' . $siteLogo);
                $siteLogo = $uploadResult['filename'];
            } else {
                $error = $uploadResult['message'];
            }
        }
        if (isset($_FILES['favicon']) && $_FILES['favicon']['error'] === UPLOAD_ERR_OK) {
            $faviconTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/x-icon'];
            $uploadResult = uploadFile($_FILES['favicon'], $siteUploadPath, $faviconTypes, ['ico']);
            if ($uploadResult['success']) {
                if ($favicon) deleteFile($siteUploadPath . '/' . $favicon);
                $favicon = $uploadResult['filename'];
            } else {
                $error = $error ?: $uploadResult['message'];
            }
        }

        if (empty($error) && updateSiteSettings(['site_name' => $siteName, 'site_logo' => $siteLogo, 'favicon' => $favicon])) {
            $message = $message ?: 'Site settings updated successfully';
            $siteSettings = getSiteSettings();
            $siteLogoUrl = !empty($siteSettings['site_logo']) ? (UPLOAD_URL . '/site/' . $siteSettings['site_logo']) : null;
            $faviconUrl = !empty($siteSettings['favicon']) ? (UPLOAD_URL . '/site/' . $siteSettings['favicon']) : null;
        } elseif (empty($error)) {
            $error = 'Failed to update site settings';
        }
    }

    if ($action === 'update_profile') {
        $newEmail = sanitize($_POST['email'] ?? '');
        $newUsername = sanitize($_POST['username'] ?? '');

        if (empty($newEmail) || empty($newUsername)) {
            $error = $error ?: 'Email and username are required';
        } elseif (!isValidEmail($newEmail)) {
            $error = $error ?: 'Invalid email address';
        } else {
            $stmt = $pdo->prepare("SELECT id FROM admins WHERE email = ? AND id != ?");
            $stmt->execute([$newEmail, $adminId]);
            if ($stmt->fetch()) {
                $error = $error ?: 'Email is already taken by another admin';
            } else {
                $stmt = $pdo->prepare("SELECT id FROM admins WHERE username = ? AND id != ?");
                $stmt->execute([$newUsername, $adminId]);
                if ($stmt->fetch()) {
                    $error = $error ?: 'Username is already taken by another admin';
                } else {
                    try {
                        $stmt = $pdo->prepare("UPDATE admins SET email = ?, username = ?, updated_at = NOW() WHERE id = ?");
                        $stmt->execute([$newEmail, $newUsername, $adminId]);
                        $message = $message ?: 'Profile updated successfully';
                        $_SESSION['username'] = $newUsername;
                        $stmt = $pdo->prepare("SELECT id, username, email, created_at FROM admins WHERE id = ?");
                        $stmt->execute([$adminId]);
                        $admin = $stmt->fetch();
                    } catch (PDOException $e) {
                        $error = $error ?: ('Error updating profile: ' . $e->getMessage());
                    }
                }
            }
        }
    }

    if ($action === 'update_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $error = $error ?: 'All password fields are required';
        } elseif ($newPassword !== $confirmPassword) {
            $error = $error ?: 'New passwords do not match';
        } elseif (strlen($newPassword) < 6) {
            $error = $error ?: 'Password must be at least 6 characters';
        } else {
            $stmt = $pdo->prepare("SELECT password_hash FROM admins WHERE id = ?");
            $stmt->execute([$adminId]);
            $adminData = $stmt->fetch();

            if (!$adminData || !password_verify($currentPassword, $adminData['password_hash'])) {
                $error = $error ?: 'Current password is incorrect';
            } else {
                try {
                    $passwordHash = hashPassword($newPassword);
                    $stmt = $pdo->prepare("UPDATE admins SET password_hash = ?, updated_at = NOW() WHERE id = ?");
                    $stmt->execute([$passwordHash, $adminId]);
                    $message = $message ?: 'Password updated successfully';
                } catch (PDOException $e) {
                    $error = $error ?: ('Error updating password: ' . $e->getMessage());
                }
            }
        }
    }
}

$pageTitle = 'Settings';
include __DIR__ . '/../includes/admin-layout.php';
?>

<style>
.page-header { margin-bottom: 24px; }
.page-title { font-size: 1.5rem; font-weight: 600; color: var(--text); margin: 0; }
.alert { padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; font-size: 0.875rem; }
.alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
.alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
.card { background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 24px; }
.card-header { padding: 20px 24px; border-bottom: 1px solid #e5e7eb; }
.card-title { font-size: 1.125rem; font-weight: 600; color: #111827; margin: 0; }
.card-body { padding: 20px 24px; }
.btn { padding: 10px 20px; border-radius: 6px; font-weight: 500; font-size: 0.875rem; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 8px; }
.btn-primary { background: #111827; color: #fff; }
.btn-primary:hover { background: #374151; }
.btn-secondary { background: #e5e7eb; color: #374151; }
.btn-secondary:hover { background: #d1d5db; }
.form-group { margin-bottom: 16px; }
.form-label { display: block; font-weight: 500; color: #374151; margin-bottom: 6px; font-size: 0.875rem; }
.form-input { width: 100%; max-width: 400px; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.875rem; }
.form-input:focus { outline: none; border-color: #111827; }
.info-display { padding: 12px; background: #f9fafb; border-radius: 6px; font-weight: 500; color: #111827; }
.image-preview { max-width: 120px; max-height: 60px; margin-top: 8px; border-radius: 6px; border: 1px solid #e5e7eb; }
</style>

<div class="page-header">
    <h1 class="page-title">Settings</h1>
    <p class="page-subtitle">Site configuration, email test, and your profile</p>
</div>

<?php if ($message): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>
<?php if ($error): ?>
<div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<!-- Email Test -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Email Configuration Test</h2>
    </div>
    <div class="card-body">
        <p style="margin: 0 0 16px; color: #6b7280; font-size: 0.875rem;">Send a test email to verify your SMTP configuration. Current: <?php echo defined('SMTP_HOST') && SMTP_HOST && SMTP_HOST !== 'smtp.example.com' ? 'SMTP (' . htmlspecialchars(SMTP_HOST) . ')' : 'PHP mail()'; ?></p>
        <form method="POST">
            <input type="hidden" name="action" value="test_email">
            <div class="form-group">
                <label class="form-label" for="test_email">Email address to send test to</label>
                <input type="email" id="test_email" name="test_email" class="form-input" required placeholder="admin@example.com">
            </div>
            <button type="submit" class="btn btn-primary">Send Test Email</button>
        </form>
    </div>
</div>

<!-- Site Settings -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Site Settings</h2>
    </div>
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="update_site">
            <div class="form-group">
                <label class="form-label" for="site_name">Site Name</label>
                <input type="text" id="site_name" name="site_name" class="form-input" value="<?php echo htmlspecialchars($siteSettings['site_name'] ?? 'Resmenu'); ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Site Logo</label>
                <?php if ($siteLogoUrl): ?>
                <div><img src="<?php echo htmlspecialchars($siteLogoUrl); ?>" alt="Logo" class="image-preview"></div>
                <?php endif; ?>
                <input type="file" name="site_logo" accept="image/jpeg,image/png,image/gif,image/webp" style="margin-top: 8px;">
                <small style="color: #6b7280; display: block; margin-top: 4px;">Leave empty to keep current. JPG, PNG, GIF, WebP. Max 5MB.</small>
            </div>
            <div class="form-group">
                <label class="form-label">Favicon</label>
                <?php if ($faviconUrl): ?>
                <div><img src="<?php echo htmlspecialchars($faviconUrl); ?>" alt="Favicon" class="image-preview"></div>
                <?php endif; ?>
                <input type="file" name="favicon" accept="image/jpeg,image/png,image/gif,image/webp,image/x-icon,.ico" style="margin-top: 8px;">
                <small style="color: #6b7280; display: block; margin-top: 4px;">Leave empty to keep current. PNG, ICO recommended.</small>
            </div>
            <button type="submit" class="btn btn-primary">Update Site Settings</button>
        </form>
    </div>
</div>

<!-- Profile (merged from profile.php) -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Account Information</h2>
    </div>
    <div class="card-body">
        <div class="form-group">
            <label class="form-label">Username</label>
            <div class="info-display"><?php echo htmlspecialchars($admin['username']); ?></div>
        </div>
        <div class="form-group">
            <label class="form-label">Email</label>
            <div class="info-display"><?php echo htmlspecialchars($admin['email']); ?></div>
        </div>
        <div class="form-group">
            <label class="form-label">Account Created</label>
            <div class="info-display"><?php echo $admin['created_at'] ? date('F j, Y g:i A', strtotime($admin['created_at'])) : 'N/A'; ?></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Update Profile</h2>
    </div>
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="action" value="update_profile">
            <div class="form-group">
                <label class="form-label" for="username">Username *</label>
                <input type="text" id="username" name="username" class="form-input" required value="<?php echo htmlspecialchars($admin['username']); ?>">
            </div>
            <div class="form-group">
                <label class="form-label" for="email">Email *</label>
                <input type="email" id="email" name="email" class="form-input" required value="<?php echo htmlspecialchars($admin['email']); ?>">
            </div>
            <button type="submit" class="btn btn-primary">Update Profile</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Change Password</h2>
    </div>
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="action" value="update_password">
            <div class="form-group">
                <label class="form-label" for="current_password">Current Password *</label>
                <input type="password" id="current_password" name="current_password" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="new_password">New Password *</label>
                <input type="password" id="new_password" name="new_password" class="form-input" required minlength="6">
                <small style="color: #6b7280; display: block; margin-top: 5px; font-size: 0.75rem;">Password must be at least 6 characters</small>
            </div>
            <div class="form-group">
                <label class="form-label" for="confirm_password">Confirm New Password *</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-input" required minlength="6">
            </div>
            <button type="submit" class="btn btn-primary">Change Password</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
