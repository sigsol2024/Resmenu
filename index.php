<?php
/**
 * Root = Auth (Login) Page - our-menu.online
 * Same behavior as admin/login.php; form posts to /.
 */

require_once __DIR__ . '/includes/auth.php';

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
    $username = trim($_POST['email'] ?? ($_POST['username'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter both email and password';
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
    <title>Login | SigSol Resmenu</title>
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
<body class="bg-background-light dark:bg-background-dark font-display text-slate-900 dark:text-slate-100 min-h-screen flex items-center justify-center p-4">
<div class="max-w-[1000px] w-full bg-white dark:bg-slate-900 rounded-xl shadow-2xl overflow-hidden flex flex-col md:flex-row min-h-[600px]">
    <div class="hidden md:flex md:w-1/2 bg-primary relative items-center justify-center p-12 overflow-hidden">
        <div class="absolute inset-0 opacity-20">
            <div class="absolute top-0 left-0 w-64 h-64 bg-white rounded-full -translate-x-1/2 -translate-y-1/2"></div>
            <div class="absolute bottom-0 right-0 w-96 h-96 bg-black rounded-full translate-x-1/3 translate-y-1/3"></div>
        </div>
        <div class="relative z-10 text-white space-y-6">
            <div class="flex items-center gap-3">
                <div class="bg-white p-2 rounded-lg">
                    <span class="material-symbols-outlined text-primary text-4xl">restaurant_menu</span>
                </div>
                <h1 class="font-heading text-2xl font-bold tracking-tight">SigSol Resmenu</h1>
            </div>
            <h2 class="text-4xl font-heading font-bold leading-tight">Elevate Your Dining Experience</h2>
            <p class="text-lg opacity-90 leading-relaxed">Join thousands of restaurants managing their digital menus with ease and style.</p>
            <div class="pt-8">
                <div class="flex -space-x-3 overflow-hidden">
                    <div class="inline-flex h-10 w-10 rounded-full ring-2 ring-primary bg-white/80 items-center justify-center text-primary text-xs font-bold">RS</div>
                    <div class="inline-flex h-10 w-10 rounded-full ring-2 ring-primary bg-white/70 items-center justify-center text-primary text-xs font-bold">MN</div>
                    <div class="inline-flex h-10 w-10 rounded-full ring-2 ring-primary bg-white/60 items-center justify-center text-primary text-xs font-bold">HQ</div>
                </div>
                <p class="mt-3 text-sm font-medium">Trusted by 5,000+ businesses</p>
            </div>
        </div>
    </div>

    <div class="w-full md:w-1/2 p-8 lg:p-16 flex flex-col justify-center">
        <div class="mb-10 text-center md:text-left">
            <div class="md:hidden flex justify-center mb-6">
                <span class="material-symbols-outlined text-primary text-5xl">restaurant_menu</span>
            </div>
            <h2 class="text-3xl font-heading font-bold text-slate-900 dark:text-white mb-2">Welcome Back</h2>
            <p class="text-slate-500 dark:text-slate-400">Log in to manage your digital menu.</p>
        </div>

        <form class="space-y-5" method="POST" action="">
            <input type="hidden" name="plan" value="<?php echo htmlspecialchars($selectedPlan); ?>">
            <input type="hidden" name="cycle" value="<?php echo htmlspecialchars($selectedCycle); ?>">
            <input type="hidden" name="next" value="<?php echo htmlspecialchars($nextPath); ?>">
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2" for="email">Email Address</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <span class="material-symbols-outlined text-slate-400 text-xl">mail</span>
                    </div>
                    <input class="block w-full pl-11 pr-4 py-3.5 bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-primary focus:border-primary transition-colors" id="email" name="email" placeholder="name@company.com" type="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required autofocus/>
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
                <a class="text-sm font-semibold text-primary hover:text-primary/80 transition-colors" href="/contact.php">Forgot Password?</a>
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

        <div class="mt-8 relative">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-slate-200 dark:border-slate-700"></div>
            </div>
            <div class="relative flex justify-center text-sm">
                <span class="px-2 bg-white dark:bg-slate-900 text-slate-500">Or continue with</span>
            </div>
        </div>
        <div class="mt-6 grid grid-cols-2 gap-4">
            <button class="flex items-center justify-center px-4 py-2.5 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors" type="button">
                <span class="material-symbols-outlined text-base mr-2">public</span>
                Google
            </button>
            <button class="flex items-center justify-center px-4 py-2.5 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors" type="button">
                <span class="material-symbols-outlined text-base mr-2">laptop_mac</span>
                Apple
            </button>
        </div>

        <p class="mt-10 text-center text-sm text-slate-600 dark:text-slate-400">
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
