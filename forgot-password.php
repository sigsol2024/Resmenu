<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$siteSettings = getSiteSettings();
$siteNameRaw = $siteSettings['site_name'] ?? 'Resmenu';
$siteName = htmlspecialchars($siteNameRaw, ENT_QUOTES, 'UTF-8');
$siteLogoUrl = !empty($siteSettings['site_logo']) ? rtrim(UPLOAD_URL, '/') . '/site/' . rawurlencode($siteSettings['site_logo']) : '';
$marketingHomeUrl = 'https://resmenu.net/';

if (isLoggedIn()) {
    if (isSuperAdmin()) {
        header('Location: /admin/dashboard.php');
    } else {
        header('Location: /manager/dashboard.php');
    }
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please refresh and try again.';
    } else {
        $identifier = trim((string)($_POST['identifier'] ?? ''));
        if ($identifier === '') {
            $error = 'Please enter your email or username.';
        } else {
            $requestAccepted = requestPasswordReset($identifier);
            if ($requestAccepted) {
                $success = 'If an account exists for that email or username, a reset link has been sent.';
            } else {
                $error = 'We could not process your request right now. Please try again shortly.';
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
    <title>Forgot Password | <?php echo $siteName; ?></title>
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

        <h1 class="text-2xl font-bold text-slate-900 mb-2">Forgot Password</h1>
        <p class="text-sm text-slate-600 mb-6">Enter your email or username and we will send a password reset link.</p>

        <?php if ($error): ?>
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <form method="post" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(getCSRFToken()); ?>">
            <div>
                <label for="identifier" class="block text-sm font-semibold text-slate-700 mb-2">Email or Username</label>
                <input
                    id="identifier"
                    name="identifier"
                    type="text"
                    class="block w-full rounded-lg border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 focus:border-primary focus:ring-primary"
                    placeholder="your email or username"
                    value="<?php echo htmlspecialchars($_POST['identifier'] ?? ''); ?>"
                    required
                />
            </div>
            <button type="submit" class="w-full rounded-lg bg-primary px-4 py-3 font-semibold text-white hover:bg-primary/90 transition-colors">
                Send Reset Link
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-slate-600">
            Remember your password?
            <a href="/" class="font-semibold text-primary hover:underline">Back to Login</a>
        </p>
    </div>
</body>
</html>
