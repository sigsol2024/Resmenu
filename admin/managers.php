<?php
/**
 * Managers Management (Super Admin)
 */

require_once __DIR__ . '/../includes/auth.php';
requireSuperAdmin();

require_once __DIR__ . '/../includes/functions.php';

$pdo = getDBConnection();
$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_password') {
        $managerId = intval($_POST['manager_id'] ?? 0);
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (empty($newPassword) || empty($confirmPassword)) {
            $error = 'Password fields are required';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'Passwords do not match';
        } elseif (strlen($newPassword) < 6) {
            $error = 'Password must be at least 6 characters';
        } else {
            try {
                require_once __DIR__ . '/../includes/auth.php';
                $passwordHash = hashPassword($newPassword);
                $stmt = $pdo->prepare("UPDATE managers SET password_hash = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$passwordHash, $managerId]);
                $message = 'Manager password updated successfully';
            } catch (PDOException $e) {
                $error = 'Error updating password: ' . $e->getMessage();
            }
        }
    }
    
    if ($action === 'delete') {
        $managerId = intval($_POST['manager_id'] ?? 0);
        
        if ($managerId > 0) {
            try {
                $stmt = $pdo->prepare("DELETE FROM managers WHERE id = ?");
                $stmt->execute([$managerId]);
                $message = 'Manager deleted successfully';
            } catch (PDOException $e) {
                $error = 'Error deleting manager: ' . $e->getMessage();
            }
        }
    }
}

// Get all managers with restaurant info
$managers = [];
if ($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT 
                m.id,
                m.username,
                m.email,
                m.created_at,
                m.updated_at,
                r.id as restaurant_id,
                r.name as restaurant_name,
                r.slug as restaurant_slug
            FROM managers m
            LEFT JOIN restaurants r ON m.restaurant_id = r.id
            ORDER BY m.created_at DESC
        ");
        $managers = $stmt->fetchAll();
    } catch (PDOException $e) {
        $error = 'Error fetching managers: ' . $e->getMessage();
    }
}

$pageTitle = 'Managers Management';
include __DIR__ . '/../includes/admin-layout.php';
?>

<style>
/* Clean Managers Management Styles */
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
    max-width: 500px;
    width: 90%;
    z-index: 3002;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.modal-header {
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
    .table {
        overflow-x: auto;
        display: block;
    }
    
    .modal-content {
        width: 95%;
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

<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">Managers Management</h1>
    <p class="page-subtitle">Manage restaurant manager accounts</p>
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
                <h2 class="card-title">All Managers</h2>
            </div>
            
            <?php if (empty($managers)): ?>
                <div style="text-align: center; padding: 60px 20px; color: var(--muted);">
                    <p style="margin-bottom: 12px;">No managers found.</p>
                    <p><a href="restaurants.php" style="color: var(--primary); text-decoration: none; font-weight: 500;">Create a restaurant to add managers</a></p>
                </div>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Restaurant</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($managers as $manager): ?>
                            <tr>
                                <td><?php echo $manager['id']; ?></td>
                                <td><?php echo htmlspecialchars($manager['username']); ?></td>
                                <td><?php echo htmlspecialchars($manager['email']); ?></td>
                                <td>
                                    <?php if ($manager['restaurant_name']): ?>
                                        <a href="restaurant-view.php?slug=<?php echo htmlspecialchars($manager['restaurant_slug']); ?>" style="color: var(--primary); text-decoration: none;">
                                            <?php echo htmlspecialchars($manager['restaurant_name']); ?>
                                        </a>
                                    <?php else: ?>
                                        <span style="color: var(--muted);">No restaurant</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $manager['created_at'] ? date('M j, Y', strtotime($manager['created_at'])) : 'N/A'; ?></td>
                                <td class="actions-cell">
                                    <button class="actions-btn" onclick="toggleDropdown(this)" title="Actions">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                        </svg>
                                    </button>
                                    <div class="actions-dropdown">
                                        <button type="button" onclick="openPasswordModal(<?php echo $manager['id']; ?>, '<?php echo htmlspecialchars($manager['username']); ?>')" class="actions-dropdown-item">Reset Password</button>
                                        <?php if ($manager['restaurant_id']): ?>
                                        <a href="restaurant-view.php?slug=<?php echo htmlspecialchars($manager['restaurant_slug']); ?>" class="actions-dropdown-item">View Restaurant</a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

<!-- Password Reset Modal -->
<div class="modal" id="passwordModal" style="display: none;">
    <div class="modal-overlay" onclick="closePasswordModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">
                Reset Password for <span id="modalManagerName"></span>
            </h3>
        </div>
        <div class="modal-body">
            <form method="POST" action="">
                <input type="hidden" name="action" value="update_password">
                <input type="hidden" name="manager_id" id="modalManagerId">
                
                <div class="form-group">
                    <label class="form-label" for="new_password">New Password *</label>
                    <input type="password" id="new_password" name="new_password" class="form-input" required minlength="6">
                    <small style="color: #6b7280; display: block; margin-top: 5px; font-size: 0.75rem;">Password must be at least 6 characters</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="confirm_password">Confirm New Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-input" required minlength="6">
                </div>
                
                <div class="modal-footer">
                    <button type="button" onclick="closePasswordModal()" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openPasswordModal(managerId, managerName) {
    document.getElementById('modalManagerId').value = managerId;
    document.getElementById('modalManagerName').textContent = managerName;
    document.getElementById('passwordModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closePasswordModal() {
    document.getElementById('passwordModal').style.display = 'none';
    document.body.style.overflow = '';
    document.getElementById('new_password').value = '';
    document.getElementById('confirm_password').value = '';
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
</script>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>

