<?php
/**
 * Admin Profile Page
 */

require_once __DIR__ . '/../includes/auth.php';
requireSuperAdmin();

require_once __DIR__ . '/../includes/functions.php';

$pdo = getDBConnection();
$message = '';
$error = '';

// Get current admin info
$adminId = getCurrentUserId();
$stmt = $pdo->prepare("SELECT id, username, email, created_at FROM admins WHERE id = ?");
$stmt->execute([$adminId]);
$admin = $stmt->fetch();

if (!$admin) {
    header('Location: /admin/logout.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_profile') {
        $newEmail = sanitize($_POST['email'] ?? '');
        $newUsername = sanitize($_POST['username'] ?? '');
        
        if (empty($newEmail) || empty($newUsername)) {
            $error = 'Email and username are required';
        } elseif (!isValidEmail($newEmail)) {
            $error = 'Invalid email address';
        } else {
            // Check if email is already taken by another admin
            $stmt = $pdo->prepare("SELECT id FROM admins WHERE email = ? AND id != ?");
            $stmt->execute([$newEmail, $adminId]);
            if ($stmt->fetch()) {
                $error = 'Email is already taken by another admin';
            } else {
                // Check if username is already taken by another admin
                $stmt = $pdo->prepare("SELECT id FROM admins WHERE username = ? AND id != ?");
                $stmt->execute([$newUsername, $adminId]);
                if ($stmt->fetch()) {
                    $error = 'Username is already taken by another admin';
                } else {
                    try {
                        $stmt = $pdo->prepare("UPDATE admins SET email = ?, username = ?, updated_at = NOW() WHERE id = ?");
                        $stmt->execute([$newEmail, $newUsername, $adminId]);
                        $message = 'Profile updated successfully';
                        $_SESSION['username'] = $newUsername;
                        
                        // Refresh admin data
                        $stmt = $pdo->prepare("SELECT id, username, email, created_at FROM admins WHERE id = ?");
                        $stmt->execute([$adminId]);
                        $admin = $stmt->fetch();
                    } catch (PDOException $e) {
                        $error = 'Error updating profile: ' . $e->getMessage();
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
            $error = 'All password fields are required';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'New passwords do not match';
        } elseif (strlen($newPassword) < 6) {
            $error = 'Password must be at least 6 characters';
        } else {
            // Verify current password
            $stmt = $pdo->prepare("SELECT password_hash FROM admins WHERE id = ?");
            $stmt->execute([$adminId]);
            $adminData = $stmt->fetch();
            
            if (!$adminData || !password_verify($currentPassword, $adminData['password_hash'])) {
                $error = 'Current password is incorrect';
            } else {
                try {
                    require_once __DIR__ . '/../includes/auth.php';
                    $passwordHash = hashPassword($newPassword);
                    $stmt = $pdo->prepare("UPDATE admins SET password_hash = ?, updated_at = NOW() WHERE id = ?");
                    $stmt->execute([$passwordHash, $adminId]);
                    $message = 'Password updated successfully';
                } catch (PDOException $e) {
                    $error = 'Error updating password: ' . $e->getMessage();
                }
            }
        }
    }
}

$pageTitle = 'My Profile';
include __DIR__ . '/../includes/admin-layout.php';
?>

<style>
/* Clean Profile Styles */
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

.form-input {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 0.875rem;
    transition: border-color 0.2s;
    background: #fff;
    font-family: inherit;
}

.form-input:focus {
    outline: none;
    border-color: #111827;
}

.info-display {
    padding: 12px;
    background: #f9fafb;
    border-radius: 6px;
    font-weight: 500;
    color: #111827;
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
    <h1 class="page-title">My Profile</h1>
    <p class="page-subtitle">Manage your account information</p>
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
                <h2 class="card-title">My Profile</h2>
            </div>
            
            <!-- Profile Information -->
            <div class="card" style="margin-bottom: 24px;">
                <div class="card-header">
                    <h2 class="card-title">Account Information</h2>
                </div>
                <div style="display: grid; gap: 20px;">
                    <div>
                        <label class="form-label">Username</label>
                        <div class="info-display">
                            <?php echo htmlspecialchars($admin['username']); ?>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Email</label>
                        <div class="info-display">
                            <?php echo htmlspecialchars($admin['email']); ?>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Account Created</label>
                        <div class="info-display">
                            <?php echo $admin['created_at'] ? date('F j, Y g:i A', strtotime($admin['created_at'])) : 'N/A'; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Update Profile Form -->
            <div class="card" style="margin-bottom: 24px;">
                <div class="card-header">
                    <h2 class="card-title">Update Profile</h2>
                </div>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="form-group">
                        <label class="form-label" for="username">Username *</label>
                        <input type="text" id="username" name="username" class="form-input" required value="<?php echo htmlspecialchars($admin['username']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="email">Email *</label>
                        <input type="email" id="email" name="email" class="form-input" required value="<?php echo htmlspecialchars($admin['email']); ?>">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Update Profile
                    </button>
                </form>
            </div>
            
            <!-- Update Password Form -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Change Password</h2>
                </div>
                <form method="POST" action="">
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
                    
                    <button type="submit" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Change Password
                    </button>
                </form>
            </div>
        </div>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>

