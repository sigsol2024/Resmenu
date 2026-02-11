<?php
/**
 * Manager Profile Page
 */

require_once __DIR__ . '/../includes/auth.php';
requireManager();

require_once __DIR__ . '/../includes/functions.php';

$pdo = getDBConnection();
$message = '';
$error = '';

// Get current manager info
$managerId = getCurrentUserId();
$restaurantId = getCurrentUserRestaurantId();

$stmt = $pdo->prepare("SELECT id, username, email, created_at FROM managers WHERE id = ?");
$stmt->execute([$managerId]);
$manager = $stmt->fetch();

// Get restaurant info
$restaurant = null;
if ($restaurantId) {
    $stmt = $pdo->prepare("SELECT name, slug FROM restaurants WHERE id = ?");
    $stmt->execute([$restaurantId]);
    $restaurant = $stmt->fetch();
}

if (!$manager) {
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
            // Check if email is already taken by another manager
            $stmt = $pdo->prepare("SELECT id FROM managers WHERE email = ? AND id != ?");
            $stmt->execute([$newEmail, $managerId]);
            if ($stmt->fetch()) {
                $error = 'Email is already taken by another manager';
            } else {
                // Check if username is already taken by another manager
                $stmt = $pdo->prepare("SELECT id FROM managers WHERE username = ? AND id != ?");
                $stmt->execute([$newUsername, $managerId]);
                if ($stmt->fetch()) {
                    $error = 'Username is already taken by another manager';
                } else {
                    try {
                        $stmt = $pdo->prepare("UPDATE managers SET email = ?, username = ?, updated_at = NOW() WHERE id = ?");
                        $stmt->execute([$newEmail, $newUsername, $managerId]);
                        
                        // Also update restaurant manager_email if it matches
                        if ($restaurantId) {
                            $stmt = $pdo->prepare("UPDATE restaurants SET manager_email = ? WHERE id = ? AND manager_email = ?");
                            $stmt->execute([$newEmail, $restaurantId, $manager['email']]);
                        }
                        
                        $message = 'Profile updated successfully';
                        $_SESSION['username'] = $newUsername;
                        
                        // Refresh manager data
                        $stmt = $pdo->prepare("SELECT id, username, email, created_at FROM managers WHERE id = ?");
                        $stmt->execute([$managerId]);
                        $manager = $stmt->fetch();
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
            $stmt = $pdo->prepare("SELECT password_hash FROM managers WHERE id = ?");
            $stmt->execute([$managerId]);
            $managerData = $stmt->fetch();
            
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

$pageTitle = 'My Profile';
include __DIR__ . '/../includes/manager-layout.php';
?>

        <div class="page-header">
            <h1 class="page-title">My Profile</h1>
            <p class="page-subtitle">Manage your account information and password</p>
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

        <!-- Profile Information -->
        <div class="settings-card">
            <div class="section-header">
                <h2 class="section-title">Account Information</h2>
            </div>
            <div style="display: grid; gap: 20px;">
                    <div>
                        <label class="form-label">Username</label>
                        <div style="padding: 12px; background: #f9fafb; border-radius: 8px; font-weight: 500;">
                            <?php echo htmlspecialchars($manager['username']); ?>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Email</label>
                        <div style="padding: 12px; background: #f9fafb; border-radius: 8px; font-weight: 500;">
                            <?php echo htmlspecialchars($manager['email']); ?>
                        </div>
                    </div>
                    <?php if ($restaurant): ?>
                    <div>
                        <label class="form-label">Restaurant</label>
                        <div style="padding: 12px; background: #f9fafb; border-radius: 8px; font-weight: 500;">
                            <?php echo htmlspecialchars($restaurant['name']); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div>
                        <label class="form-label">Account Created</label>
                        <div style="padding: 12px; background: #f9fafb; border-radius: 8px; font-weight: 500;">
                            <?php echo $manager['created_at'] ? date('F j, Y g:i A', strtotime($manager['created_at'])) : 'N/A'; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Update Profile Form -->
        <div class="settings-card">
            <div class="section-header">
                <h2 class="section-title">Update Profile</h2>
            </div>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="form-group">
                        <label class="form-label" for="username">Username *</label>
                        <input type="text" id="username" name="username" class="form-input" required value="<?php echo htmlspecialchars($manager['username']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="email">Email *</label>
                        <input type="email" id="email" name="email" class="form-input" required value="<?php echo htmlspecialchars($manager['email']); ?>">
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
        <div class="settings-card">
            <div class="section-header">
                <h2 class="section-title">Change Password</h2>
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
                        <small style="color: var(--muted); display: block; margin-top: 5px;">Password must be at least 6 characters</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="confirm_password">Confirm New Password *</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-input" required minlength="6">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                        </svg>
                        Change Password
                    </button>
                </form>
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

.form-input {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 0.875rem;
    color: #111827;
    transition: border-color 0.2s;
}

.form-input:focus {
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

