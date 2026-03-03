<?php
/**
 * Root = Auth (Login) Page - our-menu.online
 * Same behavior as admin/login.php; form posts to /.
 */

require_once __DIR__ . '/includes/auth.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    if (isSuperAdmin()) {
        header('Location: /admin/dashboard.php');
    } else {
        $restaurantSlug = getRestaurantSlugByUserId($_SESSION['user_id']);
        if ($restaurantSlug) {
            header('Location: /manager/' . urlencode($restaurantSlug));
        } else {
            header('Location: /manager/dashboard.php');
        }
    }
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        $result = loginUser($username, $password);
        if ($result['success']) {
            if ($result['user']['role'] === 'super_admin') {
                header('Location: /admin/dashboard.php');
            } else {
                $restaurantSlug = getRestaurantSlugByUserId($result['user']['id']);
                if ($restaurantSlug) {
                    header('Location: /manager/' . urlencode($restaurantSlug));
                } else {
                    header('Location: /manager/dashboard.php');
                }
            }
            exit;
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in - Resmenu</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; min-height: 100vh; width: 100%; display: flex; align-items: center; justify-content: center; background: white; padding: 20px; z-index: 1; }
        .login-wrapper { width: 100%; max-width: 384px; background: linear-gradient(to bottom, rgba(240, 249, 255, 0.5), white); border-radius: 24px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); padding: 32px; border: 1px solid rgba(219, 234, 254, 1); text-align: center; }
        .login-icon-wrapper { display: flex; align-items: center; justify-content: center; width: 56px; height: 56px; border-radius: 16px; background: white; margin: 0 auto 24px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); }
        .login-icon-wrapper svg { width: 28px; height: 28px; color: black; }
        h2 { font-size: 24px; font-weight: 600; margin-bottom: 8px; color: black; }
        .subtitle { color: #6b7280; font-size: 14px; margin-bottom: 24px; }
        .form-container { width: 100%; display: flex; flex-direction: column; gap: 12px; margin-bottom: 8px; }
        .input-group { position: relative; }
        .input-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #9ca3af; width: 16px; height: 16px; pointer-events: none; }
        .input-icon svg { width: 16px; height: 16px; }
        input[type="text"], input[type="password"] { width: 100%; padding: 8px 40px 8px 40px; border-radius: 12px; border: 1px solid #e5e7eb; background: #f9fafb; color: black; font-size: 14px; transition: all 0.2s; }
        input[type="text"]:focus, input[type="password"]:focus { outline: none; border-color: #93c5fd; background: white; box-shadow: 0 0 0 2px rgba(147, 197, 253, 0.2); }
        .password-input-wrapper { position: relative; width: 100%; }
        .password-toggle { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; padding: 4px; display: flex; align-items: center; justify-content: center; color: #6b7280; z-index: 10; }
        .password-toggle.hidden .eye-open { display: none; }
        .password-toggle.hidden .eye-closed { display: block; }
        .password-toggle .eye-closed { display: none; }
        .password-toggle .eye-open { display: block; }
        .forgot-password { width: 100%; display: flex; justify-content: space-between; align-items: flex-start; margin-top: 4px; }
        .error-text { font-size: 14px; color: #ef4444; text-align: left; }
        .submit-btn { width: 100%; background: linear-gradient(to bottom, #374151, #111827); color: white; font-weight: 500; padding: 8px; border-radius: 12px; border: none; cursor: pointer; margin-top: 8px; margin-bottom: 16px; }
        .divider { display: flex; align-items: center; width: 100%; margin: 8px 0; }
        .divider-line { flex-grow: 1; border-top: 1px dashed #e5e7eb; }
        .divider-text { margin: 0 8px; font-size: 12px; color: #9ca3af; }
        .social-buttons { display: flex; gap: 12px; width: 100%; justify-content: center; margin-top: 8px; }
        .social-btn { display: flex; align-items: center; justify-content: center; width: 48px; height: 48px; border-radius: 12px; border: 1px solid #e5e7eb; background: white; cursor: pointer; flex-grow: 1; }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-icon-wrapper">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9l5.5-5.5m0 0l-5.5-5.5m5.5 5.5H3.75" />
            </svg>
        </div>
        <h2>Sign in</h2>
        <p class="subtitle">Sign in to manage your restaurant menu and dashboard.</p>
        <form method="POST" action="">
            <div class="form-container">
                <div class="input-group">
                    <span class="input-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                        </svg>
                    </span>
                    <input type="text" id="username" name="username" placeholder="Email or username" required autofocus>
                </div>
                <div class="input-group">
                    <span class="input-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                        </svg>
                    </span>
                    <div class="password-input-wrapper">
                        <input type="password" id="password" name="password" placeholder="Password" required>
                        <button type="button" class="password-toggle" aria-label="Toggle password visibility">
                            <svg class="eye-open" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            <svg class="eye-closed" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228L3 3m3.228 3.228L3.98 8.223m13.793 5.772L21 21m-2.227-2.227L17.022 15.78M15.78 17.022l-2.227-2.227m0 0a3 3 0 01-4.243-4.243M13.553 13.553a3 3 0 01-4.243-4.243" /></svg>
                        </button>
                    </div>
                </div>
                <div class="forgot-password">
                    <?php if ($error): ?><div class="error-text"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
                </div>
            </div>
            <button type="submit" class="submit-btn">Sign in</button>
        </form>
        <div class="divider">
            <div class="divider-line"></div>
            <span class="divider-text">Or sign in with</span>
            <div class="divider-line"></div>
        </div>
        <div class="social-buttons">
            <button type="button" class="social-btn"><img src="https://www.svgrepo.com/show/475656/google-color.svg" alt="Google" width="24" height="24"></button>
            <button type="button" class="social-btn"><img src="https://www.svgrepo.com/show/448224/facebook.svg" alt="Facebook" width="24" height="24"></button>
            <button type="button" class="social-btn"><img src="https://www.svgrepo.com/show/511330/apple-173.svg" alt="Apple" width="24" height="24"></button>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var p = document.getElementById('password'), t = document.querySelector('.password-toggle');
        if (t && p) t.addEventListener('click', function() { p.type = p.type === 'password' ? 'text' : 'password'; t.classList.toggle('hidden', p.type === 'text'); });
    });
    </script>
</body>
</html>
