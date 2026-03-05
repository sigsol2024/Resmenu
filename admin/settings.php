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

    if ($action === 'update_contact') {
        // Merge existing site settings with updated contact fields so we don't wipe other columns
        $current = getSiteSettings();
        $data = [
            'site_name' => $current['site_name'] ?? 'Resmenu',
            'site_logo' => $current['site_logo'] ?? null,
            'favicon' => $current['favicon'] ?? null,
            'contact_sales_email' => trim($_POST['contact_sales_email'] ?? ($current['contact_sales_email'] ?? '')),
            'contact_sales_phone' => trim($_POST['contact_sales_phone'] ?? ($current['contact_sales_phone'] ?? '')),
            'contact_support_email' => trim($_POST['contact_support_email'] ?? ($current['contact_support_email'] ?? '')),
            'contact_support_phone' => trim($_POST['contact_support_phone'] ?? ($current['contact_support_phone'] ?? '')),
            'contact_partners_email' => trim($_POST['contact_partners_email'] ?? ($current['contact_partners_email'] ?? '')),
            'contact_form_recipient' => trim($_POST['contact_form_recipient'] ?? ($current['contact_form_recipient'] ?? '')),
            'contact_hq_title' => trim($_POST['contact_hq_title'] ?? ($current['contact_hq_title'] ?? '')),
            'contact_hq_address' => trim($_POST['contact_hq_address'] ?? ($current['contact_hq_address'] ?? '')),
            'contact_map_embed' => trim($_POST['contact_map_embed'] ?? ($current['contact_map_embed'] ?? '')),
            'contact_social_facebook' => trim($_POST['contact_social_facebook'] ?? ($current['contact_social_facebook'] ?? '')),
            'contact_social_twitter' => trim($_POST['contact_social_twitter'] ?? ($current['contact_social_twitter'] ?? '')),
            'contact_social_instagram' => trim($_POST['contact_social_instagram'] ?? ($current['contact_social_instagram'] ?? '')),
        ];

        // Basic validation for recipient email
        if (!empty($data['contact_form_recipient']) && !isValidEmail($data['contact_form_recipient'])) {
            $error = $error ?: 'Contact form recipient email is invalid';
        }

        if (empty($error) && updateSiteSettings($data)) {
            $message = $message ?: 'Contact settings updated successfully';
            $siteSettings = getSiteSettings();
        } elseif (empty($error)) {
            $error = 'Failed to update contact settings';
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
/* Tabs (similar to payment settings) */
.tabs-container { background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 24px; overflow: hidden; }
.tabs-nav { display: flex; border-bottom: 1px solid #e5e7eb; background: #f9fafb; }
.tab-button { flex: 1; padding: 12px 16px; background: transparent; border: none; border-bottom: 2px solid transparent; cursor: pointer; font-size: 0.875rem; font-weight: 500; color: #6b7280; transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 6px; }
.tab-button:hover { background: #f3f4f6; color: #374151; }
.tab-button.active { color: #111827; border-bottom-color: #111827; background: #fff; }
.tab-content { display: none; padding: 20px 24px; }
.tab-content.active { display: block; }
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

<div class="tabs-container">
    <div class="tabs-nav">
        <button type="button" class="tab-button active" data-tab="site">Site</button>
        <button type="button" class="tab-button" data-tab="contact">Contact Page</button>
        <button type="button" class="tab-button" data-tab="account">Admin Account</button>
    </div>
    <div class="tab-content active" id="tab-site">
        <!-- Email Test -->
        <div class="card" style="margin-bottom:16px;">
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
    </div>

    <div class="tab-content" id="tab-contact">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Contact Page Settings</h2>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="update_contact">
                    <div class="form-group">
                        <label class="form-label" for="contact_sales_email">Sales email</label>
                        <input type="email" id="contact_sales_email" name="contact_sales_email" class="form-input" value="<?php echo htmlspecialchars($siteSettings['contact_sales_email'] ?? ''); ?>" placeholder="sales@yourdomain.com">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="contact_sales_phone">Sales phone</label>
                        <input type="text" id="contact_sales_phone" name="contact_sales_phone" class="form-input" value="<?php echo htmlspecialchars($siteSettings['contact_sales_phone'] ?? ''); ?>" placeholder="+234 ...">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="contact_support_email">Support email</label>
                        <input type="email" id="contact_support_email" name="contact_support_email" class="form-input" value="<?php echo htmlspecialchars($siteSettings['contact_support_email'] ?? ''); ?>" placeholder="support@yourdomain.com">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="contact_support_phone">Support phone</label>
                        <input type="text" id="contact_support_phone" name="contact_support_phone" class="form-input" value="<?php echo htmlspecialchars($siteSettings['contact_support_phone'] ?? ''); ?>" placeholder="+234 ...">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="contact_partners_email">Partnerships email</label>
                        <input type="email" id="contact_partners_email" name="contact_partners_email" class="form-input" value="<?php echo htmlspecialchars($siteSettings['contact_partners_email'] ?? ''); ?>" placeholder="partners@yourdomain.com">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="contact_form_recipient">Contact form recipient email</label>
                        <input type="email" id="contact_form_recipient" name="contact_form_recipient" class="form-input" value="<?php echo htmlspecialchars($siteSettings['contact_form_recipient'] ?? ''); ?>" placeholder="where contact form emails go">
                    </div>
                    <hr style="margin: 20px 0;">
                    <div class="form-group">
                        <label class="form-label" for="contact_hq_title">HQ label</label>
                        <input type="text" id="contact_hq_title" name="contact_hq_title" class="form-input" value="<?php echo htmlspecialchars($siteSettings['contact_hq_title'] ?? ''); ?>" placeholder="Lagos HQ">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="contact_hq_address">HQ address</label>
                        <textarea id="contact_hq_address" name="contact_hq_address" class="form-input" rows="3" placeholder="Street, city, country"><?php echo htmlspecialchars($siteSettings['contact_hq_address'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="contact_map_embed">Map embed code (iframe)</label>
                        <textarea id="contact_map_embed" name="contact_map_embed" class="form-input" rows="4" placeholder="Paste Google Maps embed iframe here"><?php echo htmlspecialchars($siteSettings['contact_map_embed'] ?? ''); ?></textarea>
                        <small style="color:#6b7280;display:block;margin-top:4px;font-size:0.75rem;">Use the Google Maps \"Share\" → \"Embed a map\" iframe code.</small>
                    </div>
                    <hr style="margin: 20px 0;">
                    <div class="form-group">
                        <label class="form-label" for="contact_social_facebook">Facebook URL</label>
                        <input type="url" id="contact_social_facebook" name="contact_social_facebook" class="form-input" value="<?php echo htmlspecialchars($siteSettings['contact_social_facebook'] ?? ''); ?>" placeholder="https://facebook.com/yourpage">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="contact_social_twitter">Twitter/X URL</label>
                        <input type="url" id="contact_social_twitter" name="contact_social_twitter" class="form-input" value="<?php echo htmlspecialchars($siteSettings['contact_social_twitter'] ?? ''); ?>" placeholder="https://twitter.com/yourhandle">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="contact_social_instagram">Instagram URL</label>
                        <input type="url" id="contact_social_instagram" name="contact_social_instagram" class="form-input" value="<?php echo htmlspecialchars($siteSettings['contact_social_instagram'] ?? ''); ?>" placeholder="https://instagram.com/yourhandle">
                    </div>
                    <button type="submit" class="btn btn-primary">Update Contact Settings</button>
                </form>
            </div>
        </div>
    </div>

    <div class="tab-content" id="tab-account">
        <!-- Profile (merged from profile.php) -->
        <div class="card" style="margin-bottom:16px;">
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

        <div class="card" style="margin-bottom:16px;">
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
    </div>
</div>

<script>
(function() {
    var buttons = document.querySelectorAll('.tab-button');
    var contents = {
        site: document.getElementById('tab-site'),
        contact: document.getElementById('tab-contact'),
        account: document.getElementById('tab-account')
    };
    buttons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            var tab = this.getAttribute('data-tab');
            buttons.forEach(function(b) { b.classList.remove('active'); });
            this.classList.add('active');
            Object.keys(contents).forEach(function(key) {
                if (contents[key]) {
                    contents[key].classList.toggle('active', key === tab);
                }
            });
        });
    });
})();
</script>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
