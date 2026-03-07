<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$siteSettings = getSiteSettings();
$siteNameRaw = $siteSettings['site_name'] ?? 'Resmenu';
$siteName = htmlspecialchars($siteNameRaw, ENT_QUOTES, 'UTF-8');
$siteLogoUrl = !empty($siteSettings['site_logo']) ? rtrim(UPLOAD_URL, '/') . '/site/' . rawurlencode($siteSettings['site_logo']) : '';
$marketingHomeUrl = 'https://resmenu.net/';

$token = trim((string)($_GET['token'] ?? ($_POST['token'] ?? '')));
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please refresh and try again.';
    } else {
        $password = (string)($_POST['password'] ?? '');
        $confirmPassword = (string)($_POST['confirm_password'] ?? '');

        if ($password === '' || $confirmPassword === '') {
            $error = 'Please fill in both password fields.';
        } elseif ($password !== $confirmPassword) {
            $error = 'Passwords do not match.';
        } else {
            $resetError = '';
            if (resetPasswordWithToken($token, $password, $resetError)) {
                $success = 'Password updated successfully. You can now sign in.';
            } else {
                $error = $resetError ?: 'Invalid or expired reset link.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | <?php echo $siteName; ?></title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#f97415',
                        'background-light': '#f8f7f5'
                    }
                }
            }
        };
    </script>
</head>
<body class="bg-background-light min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md rounded-2xl bg-white shadow-xl border border-slate-200 p-6 sm:p-8">
        <div class="mb-6 flex items-center justify-between gap-4">
            <a href="<?php echo htmlspecialchars($marketingHomeUrl); ?>" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 hover:text-primary transition-colors">
                <span class="material-symbols-outlined text-base">arrow_back</span>
                Back to Home
            </a>
            <a href="<?php echo htmlspecialchars($marketingHomeUrl); ?>" class="inline-flex items-center gap-2">
                <?php if ($siteLogoUrl !== ''): ?>
                    <img src="<?php echo htmlspecialchars($siteLogoUrl); ?>" alt="<?php echo $siteName; ?>" class="h-9 w-auto">
                <?php else: ?>
                    <span class="material-symbols-outlined text-primary text-3xl">restaurant_menu</span>
                <?php endif; ?>
            </a>
        </div>

        <h1 class="text-2xl font-bold text-slate-900 mb-2">Reset Password</h1>
        <p class="text-sm text-slate-600 mb-6">Create a new password for your account.</p>

        <?php if ($error): ?>
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                <?php echo htmlspecialchars($success); ?>
            </div>
            <a href="/" class="inline-flex w-full items-center justify-center rounded-lg bg-primary px-4 py-3 font-semibold text-white hover:bg-primary/90 transition-colors">Go to Login</a>
        <?php elseif ($token === ''): ?>
            <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                Missing reset token. Please request a new password reset link.
            </div>
            <a href="/forgot-password.php" class="mt-4 inline-flex w-full items-center justify-center rounded-lg bg-primary px-4 py-3 font-semibold text-white hover:bg-primary/90 transition-colors">Request New Link</a>
        <?php else: ?>
            <form method="post" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(getCSRFToken()); ?>">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                <div>
                    <label for="password" class="block text-sm font-semibold text-slate-700 mb-2">New Password</label>
                    <div class="relative">
                        <input
                            id="password"
                            name="password"
                            type="password"
                            minlength="<?php echo (int)PASSWORD_MIN_LENGTH; ?>"
                            class="block w-full rounded-lg border-slate-200 bg-slate-50 pl-4 pr-12 py-3 text-slate-900 focus:border-primary focus:ring-primary"
                            placeholder="Enter new password"
                            required
                        />
                        <button type="button" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-primary transition-colors" aria-label="Toggle password visibility" id="togglePassword">
                            <span class="material-symbols-outlined text-xl" id="togglePasswordIcon">visibility</span>
                        </button>
                    </div>
                </div>
                <div>
                    <label for="confirm_password" class="block text-sm font-semibold text-slate-700 mb-2">Confirm New Password</label>
                    <div class="relative">
                        <input
                            id="confirm_password"
                            name="confirm_password"
                            type="password"
                            minlength="<?php echo (int)PASSWORD_MIN_LENGTH; ?>"
                            class="block w-full rounded-lg border-slate-200 bg-slate-50 pl-4 pr-12 py-3 text-slate-900 focus:border-primary focus:ring-primary"
                            placeholder="Confirm new password"
                            required
                        />
                        <button type="button" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-primary transition-colors" aria-label="Toggle password visibility" id="toggleConfirmPassword">
                            <span class="material-symbols-outlined text-xl" id="toggleConfirmPasswordIcon">visibility</span>
                        </button>
                    </div>
                </div>
                <button type="submit" class="w-full rounded-lg bg-primary px-4 py-3 font-semibold text-white hover:bg-primary/90 transition-colors">
                    Update Password
                </button>
            </form>
        <?php endif; ?>
    </div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    function setupToggle(inputId, btnId, iconId) {
        var input = document.getElementById(inputId);
        var btn = document.getElementById(btnId);
        var icon = document.getElementById(iconId);
        if (input && btn && icon) {
            btn.addEventListener('click', function() {
                var isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';
                icon.textContent = isPassword ? 'visibility_off' : 'visibility';
            });
        }
    }
    setupToggle('password', 'togglePassword', 'togglePasswordIcon');
    setupToggle('confirm_password', 'toggleConfirmPassword', 'toggleConfirmPasswordIcon');
});
</script>
</body>
</html>
