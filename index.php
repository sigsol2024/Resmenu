<?php
/**
 * Root = Auth (Login) Page - our-menu.online
 * Same behavior as admin/login.php; form posts to /.
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

function getSafeNextPathForLogin($rawNext) {
    $next = trim((string)$rawNext);
    if ($next === '') {
        return '';
    }
    if ($next[0] !== '/') {
        return '';
    }
    if (strpos($next, '//') === 0) {
        return '';
    }
    if (preg_match('/[\r\n]/', $next)) {
        return '';
    }
    return $next;
}

$selectedPlan = trim((string)($_GET['plan'] ?? ($_POST['plan'] ?? '')));
$selectedCycle = strtolower(trim((string)($_GET['cycle'] ?? ($_POST['cycle'] ?? 'monthly'))));
if ($selectedCycle === 'yearly') {
    $selectedCycle = 'annual';
}
if (!in_array($selectedCycle, ['monthly', 'annual'], true)) {
    $selectedCycle = 'monthly';
}
$hasPlanSelection = $selectedPlan !== '';
$defaultNext = $hasPlanSelection
    ? '/manager/checkout.php?' . http_build_query([
        'plan' => $selectedPlan,
        'cycle' => $selectedCycle,
    ])
    : '';
$requestedNext = $_GET['next'] ?? ($_POST['next'] ?? $defaultNext);
$nextPath = getSafeNextPathForLogin($requestedNext);
if ($nextPath === '' && $defaultNext !== '') {
    $nextPath = $defaultNext;
}
$registerQueryParams = [];
if ($selectedPlan !== '') {
    $registerQueryParams['plan'] = $selectedPlan;
}
if ($selectedPlan !== '' || $nextPath !== '') {
    $registerQueryParams['cycle'] = $selectedCycle;
}
if ($nextPath !== '') {
    $registerQueryParams['next'] = $nextPath;
}
$registerLink = '/register.php';
if (!empty($registerQueryParams)) {
    $registerLink .= '?' . http_build_query($registerQueryParams);
}
$siteSettings = getSiteSettings();
$siteNameRaw = $siteSettings['site_name'] ?? 'SigSol Resmenu';
$siteName = htmlspecialchars($siteNameRaw, ENT_QUOTES, 'UTF-8');
$siteLogoUrl = !empty($siteSettings['site_logo']) ? rtrim(UPLOAD_URL, '/') . '/site/' . rawurlencode($siteSettings['site_logo']) : '';
$marketingHomeUrl = 'https://resmenu.net/';
$showcaseRestaurantLogos = [
    'https://our-menu.online/uploads/logos/698ee78360beb.jpg',
    'https://our-menu.online/uploads/logos/69459eb555362.jpg',
    'https://our-menu.online/uploads/logos/69a76f2ad31b1.png',
];

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    if (isSuperAdmin()) {
        header('Location: /admin/dashboard.php');
    } else {
        if ($nextPath !== '') {
            header('Location: ' . $nextPath);
        } else {
            $restaurantSlug = getRestaurantSlugByUserId($_SESSION['user_id']);
            if ($restaurantSlug) {
                header('Location: /manager/' . urlencode($restaurantSlug));
            } else {
                header('Location: /manager/dashboard.php');
            }
        }
    }
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? ($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter your username/email and password';
    } else {
        $result = loginUser($username, $password);
        if ($result['success']) {
            if ($result['user']['role'] === 'super_admin') {
                header('Location: /admin/dashboard.php');
            } else {
                if ($nextPath !== '') {
                    header('Location: ' . $nextPath);
                } else {
                    $restaurantSlug = getRestaurantSlugByUserId($result['user']['id']);
                    if ($restaurantSlug) {
                        header('Location: /manager/' . urlencode($restaurantSlug));
                    } else {
                        header('Location: /manager/dashboard.php');
                    }
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
<html class="light" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | <?php echo $siteName; ?></title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;family=Poppins:wght@600;700&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#f97415",
                        "background-light": "#f8f7f5",
                        "background-dark": "#23170f",
                    },
                    fontFamily: {
                        "display": ["Inter", "sans-serif"],
                        "heading": ["Poppins", "sans-serif"]
                    },
                    borderRadius: {
                        "DEFAULT": "0.5rem",
                        "lg": "0.75rem",
                        "xl": "1.5rem",
                        "full": "9999px"
                    },
                },
            },
        }
    </script>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-slate-900 dark:text-slate-100 min-h-screen lg:h-screen overflow-x-hidden lg:overflow-hidden p-4">
<div class="mx-auto max-w-[1000px] w-full bg-white dark:bg-slate-900 rounded-xl shadow-2xl overflow-hidden flex flex-col md:flex-row min-h-[600px] lg:h-[calc(100vh-2rem)]">
    <div class="hidden md:flex md:w-1/2 bg-primary relative items-center justify-center p-12 overflow-hidden">
        <div class="absolute inset-0 opacity-20">
            <div class="absolute top-0 left-0 w-64 h-64 bg-white rounded-full -translate-x-1/2 -translate-y-1/2"></div>
            <div class="absolute bottom-0 right-0 w-96 h-96 bg-black rounded-full translate-x-1/3 translate-y-1/3"></div>
        </div>
        <div class="relative z-10 text-white space-y-6">
            <a href="<?php echo htmlspecialchars($marketingHomeUrl); ?>" class="inline-flex items-center gap-3 hover:opacity-90 transition-opacity">
                <?php if ($siteLogoUrl !== ''): ?>
                    <img src="<?php echo htmlspecialchars($siteLogoUrl); ?>" alt="<?php echo $siteName; ?>" class="h-12 w-auto rounded-lg bg-white p-1.5">
                <?php else: ?>
                    <div class="bg-white p-2 rounded-lg">
                        <span class="material-symbols-outlined text-primary text-4xl">restaurant_menu</span>
                    </div>
                <?php endif; ?>
                <h1 class="font-heading text-2xl font-bold tracking-tight"><?php echo $siteName; ?></h1>
            </a>
            <h2 class="text-4xl font-heading font-bold leading-tight">Elevate Your Dining Experience</h2>
            <p class="text-lg opacity-90 leading-relaxed">Join thousands of restaurants managing their digital menus with ease and style.</p>
            <div class="pt-8">
                <div class="flex -space-x-3 overflow-hidden">
                    <?php foreach ($showcaseRestaurantLogos as $logo): ?>
                    <div class="inline-flex h-10 w-10 rounded-full ring-2 ring-primary bg-white/80 items-center justify-center overflow-hidden">
                        <img src="<?php echo htmlspecialchars($logo); ?>" alt="Restaurant logo" class="h-full w-full object-cover">
                    </div>
                    <?php endforeach; ?>
                </div>
                <p class="mt-3 text-sm font-medium">Trusted by 5,000+ businesses</p>
            </div>
        </div>
    </div>

    <div class="w-full md:w-1/2 p-8 lg:p-10 xl:p-12 flex flex-col justify-center">
        <div class="mb-10 text-center md:text-left">
            <div class="mb-6 flex items-center justify-between gap-4">
                <a href="<?php echo htmlspecialchars($marketingHomeUrl); ?>" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 hover:text-primary transition-colors">
                    <span class="material-symbols-outlined text-base">arrow_back</span>
                    Back to Home
                </a>
                <a href="<?php echo htmlspecialchars($marketingHomeUrl); ?>" class="inline-flex items-center gap-2 hover:opacity-90 transition-opacity">
                    <?php if ($siteLogoUrl !== ''): ?>
                        <img src="<?php echo htmlspecialchars($siteLogoUrl); ?>" alt="<?php echo $siteName; ?>" class="h-10 w-auto">
                    <?php else: ?>
                        <span class="material-symbols-outlined text-primary text-3xl">restaurant_menu</span>
                    <?php endif; ?>
                    <span class="font-heading text-lg font-bold text-slate-900 dark:text-white"><?php echo $siteName; ?></span>
                </a>
            </div>
            <h2 class="text-3xl font-heading font-bold text-slate-900 dark:text-white mb-2">Welcome Back</h2>
            <p class="text-slate-500 dark:text-slate-400">Log in to manage your digital menu.</p>
        </div>

        <form class="space-y-5" method="POST" action="">
            <input type="hidden" name="plan" value="<?php echo htmlspecialchars($selectedPlan); ?>">
            <input type="hidden" name="cycle" value="<?php echo htmlspecialchars($selectedCycle); ?>">
            <input type="hidden" name="next" value="<?php echo htmlspecialchars($nextPath); ?>">
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2" for="username">Email or Username</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <span class="material-symbols-outlined text-slate-400 text-xl">person</span>
                    </div>
                    <input class="block w-full pl-11 pr-4 py-3.5 bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-primary focus:border-primary transition-colors" id="username" name="username" placeholder="your email or username" type="text" value="<?php echo htmlspecialchars($_POST['username'] ?? ($_POST['email'] ?? '')); ?>" required autofocus/>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2" for="password">Password</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <span class="material-symbols-outlined text-slate-400 text-xl">lock</span>
                    </div>
                    <input class="block w-full pl-11 pr-12 py-3.5 bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-primary focus:border-primary transition-colors" id="password" name="password" placeholder="********" type="password" required/>
                    <button id="togglePassword" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-primary transition-colors" type="button" aria-label="Toggle password visibility">
                        <span id="togglePasswordIcon" class="material-symbols-outlined text-xl">visibility</span>
                    </button>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center cursor-pointer group">
                    <input class="w-4 h-4 rounded border-slate-300 text-primary focus:ring-primary dark:bg-slate-800 dark:border-slate-700" type="checkbox" name="remember_me"/>
                    <span class="ml-2 text-sm text-slate-600 dark:text-slate-400 group-hover:text-slate-900 dark:group-hover:text-slate-200 transition-colors">Remember me</span>
                </label>
                <a class="text-sm font-semibold text-primary hover:text-primary/80 transition-colors" href="/forgot-password.php">Forgot Password?</a>
            </div>

            <?php if ($error): ?>
                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <button class="w-full bg-primary hover:bg-primary/90 text-white font-bold py-4 rounded-lg shadow-lg shadow-primary/20 transition-all transform hover:-translate-y-0.5 active:translate-y-0" type="submit">
                Login
            </button>
        </form>

        <p class="mt-8 text-center text-sm text-slate-600 dark:text-slate-400">
            Don't have an account?
            <a class="font-bold text-primary hover:underline" href="<?php echo htmlspecialchars($registerLink); ?>">Sign Up</a>
        </p>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var password = document.getElementById('password');
        var toggle = document.getElementById('togglePassword');
        var icon = document.getElementById('togglePasswordIcon');

        if (password && toggle && icon) {
            toggle.addEventListener('click', function () {
                var isPassword = password.type === 'password';
                password.type = isPassword ? 'text' : 'password';
                icon.textContent = isPassword ? 'visibility_off' : 'visibility';
            });
        }
    });
</script>
</body>
</html>
