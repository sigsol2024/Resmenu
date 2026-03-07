<?php
/**
 * Platform Registration (Multi-step)
 * Creates restaurant + manager + trial subscription.
 */

if (!defined('SITE_URL')) {
    require_once __DIR__ . '/config/config.php';
}
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

function getSafeNextPath($rawNext) {
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
    : '/manager/billing.php?welcome=1&trial=1';
$requestedNext = $_GET['next'] ?? ($_POST['next'] ?? '');
$requestedNext = $requestedNext === '' ? $defaultNext : $requestedNext;
$nextPath = getSafeNextPath($requestedNext);
if ($nextPath === '') {
    $nextPath = $defaultNext;
}
$loginQueryParams = [];
if ($selectedPlan !== '') {
    $loginQueryParams['plan'] = $selectedPlan;
    $loginQueryParams['cycle'] = $selectedCycle;
}
if ($nextPath !== '') {
    $loginQueryParams['next'] = $nextPath;
}
$loginLink = '/';
if (!empty($loginQueryParams)) {
    $loginLink .= '?' . http_build_query($loginQueryParams);
}

if (isLoggedIn()) {
    if (isManager()) {
        header('Location: ' . $nextPath);
        exit;
    }
    header('Location: /admin/dashboard.php');
    exit;
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
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please refresh and try again.';
    } else {
        $pdo = getDBConnection();
        if (!$pdo) {
            $error = 'Database connection failed. Please try again.';
        } else {
            $result = createRestaurantWithManager(
                $pdo,
                $_POST,
                [],
                [
                    'default_template_id' => 1,
                    'trial_plan_slug' => 'professional',
                    'trial_days' => 7,
                ]
            );

            if ($result['success']) {
                $stmt = $pdo->prepare("SELECT id, username, restaurant_id FROM managers WHERE id = ? LIMIT 1");
                $stmt->execute([$result['manager_id']]);
                $manager = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($manager) {
                    $_SESSION['user_id'] = (int)$manager['id'];
                    $_SESSION['user_role'] = 'manager';
                    $_SESSION['username'] = $manager['username'];
                    $_SESSION['restaurant_id'] = (int)$manager['restaurant_id'];
                    $_SESSION['created'] = time();
                    header('Location: ' . $nextPath);
                    exit;
                }

                $error = 'Account was created, but sign-in failed. Please sign in manually.';
            } else {
                $error = $result['message'];
            }
        }
    }
}

function oldValue($key, $default = '') {
    return htmlspecialchars($_POST[$key] ?? $default, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&amp;family=Poppins:wght@400;500;600;700&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#f97415",
                        "background-light": "#f8f7f5",
                        "background-dark": "#111827",
                    },
                    fontFamily: {
                        "display": ["Inter", "sans-serif"],
                        "poppins": ["Poppins", "sans-serif"]
                    },
                    borderRadius: {
                        "DEFAULT": "0.5rem",
                        "lg": "1rem",
                        "xl": "1.5rem",
                        "full": "9999px"
                    },
                },
            },
        }
    </script>
    <title>Register - <?php echo $siteName; ?></title>
</head>
<body class="bg-background-light dark:bg-background-dark font-display antialiased min-h-screen lg:h-screen overflow-x-hidden lg:overflow-hidden">
<div class="flex min-h-screen lg:h-screen flex-col lg:flex-row">
    <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden bg-primary/10">
        <div class="absolute inset-0 z-10 bg-gradient-to-t from-black/80 via-black/30 to-transparent"></div>
        <?php
$assetBase = defined('SITE_URL') ? rtrim(SITE_URL, '/') : '';
$heroImg = $assetBase . '/assets/images/woman_work.jpg';
?>
        <img class="absolute inset-0 h-full w-full object-cover object-center" alt="Professional chef at work" src="<?php echo htmlspecialchars($heroImg); ?>"/>
        <div class="relative z-20 flex h-full w-full flex-col justify-between p-12 text-white">
            <a href="<?php echo htmlspecialchars($marketingHomeUrl); ?>" class="inline-flex items-center gap-3 hover:opacity-90 transition-opacity">
                <?php if ($siteLogoUrl !== ''): ?>
                    <img src="<?php echo htmlspecialchars($siteLogoUrl); ?>" alt="<?php echo $siteName; ?>" class="h-12 w-auto rounded-lg bg-white p-1.5">
                <?php else: ?>
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary">
                        <span class="material-symbols-outlined text-white">restaurant_menu</span>
                    </div>
                    <span class="text-2xl font-bold tracking-tight font-poppins text-white"><?php echo $siteName; ?></span>
                <?php endif; ?>
            </a>
            <div class="max-w-md">
                <h1 class="text-5xl font-extrabold leading-tight mb-6 font-poppins">Join 1,000+ restaurants worldwide.</h1>
                <p class="text-xl text-slate-200">Create your restaurant account, start a 7-day Professional trial, and manage everything from one dashboard.</p>
                <div class="mt-8">
                    <div class="flex -space-x-2">
                        <?php foreach ($showcaseRestaurantLogos as $logo): ?>
                        <div class="h-10 w-10 rounded-full ring-2 ring-primary/70 bg-white/80 overflow-hidden">
                            <img src="<?php echo htmlspecialchars($logo); ?>" alt="Restaurant logo" class="h-full w-full object-cover">
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <p class="mt-3 text-sm font-medium">Trusted by industry leaders</p>
                </div>
            </div>
            <div class="text-sm text-slate-300">
                © <?php echo date('Y'); ?> <?php echo $siteName; ?>. All rights reserved.
            </div>
        </div>
    </div>

    <div class="flex flex-1 flex-col justify-center lg:justify-start px-6 py-8 lg:px-16 lg:py-8 xl:px-20 bg-background-light lg:overflow-y-auto">
        <div class="mb-6 flex items-center justify-between gap-4">
            <a href="<?php echo htmlspecialchars($marketingHomeUrl); ?>" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 hover:text-primary transition-colors">
                <span class="material-symbols-outlined text-base">arrow_back</span>
                Back to Home
            </a>
            <a href="<?php echo htmlspecialchars($marketingHomeUrl); ?>" class="inline-flex items-center gap-2 hover:opacity-90 transition-opacity lg:hidden">
                <?php if ($siteLogoUrl !== ''): ?>
                    <img src="<?php echo htmlspecialchars($siteLogoUrl); ?>" alt="<?php echo $siteName; ?>" class="h-10 w-auto">
                <?php else: ?>
                    <span class="material-symbols-outlined text-primary text-3xl">restaurant_menu</span>
                    <span class="text-lg font-bold font-poppins text-slate-900"><?php echo $siteName; ?></span>
                <?php endif; ?>
            </a>
        </div>

        <div class="mx-auto w-full max-w-md">
            <div class="mb-6">
                <h2 class="text-3xl font-extrabold tracking-tight text-slate-900 font-poppins">Create Your Account</h2>
                <p class="mt-2 text-slate-500">7-day Professional trial starts immediately after registration.</p>
            </div>

            <?php if ($error): ?>
                <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="mb-6">
                <div class="h-2 w-full rounded-full bg-slate-200 overflow-hidden">
                    <div id="progressFill" class="h-full bg-primary transition-all duration-300" style="width: 33%"></div>
                </div>
                <p id="progressText" class="mt-2 text-xs text-slate-500">Step 1 of 3 - Restaurant Profile</p>
            </div>

            <form id="registerForm" class="space-y-5" method="post">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(getCSRFToken()); ?>">
                <input type="hidden" name="plan" value="<?php echo htmlspecialchars($selectedPlan); ?>">
                <input type="hidden" name="cycle" value="<?php echo htmlspecialchars($selectedCycle); ?>">
                <input type="hidden" name="next" value="<?php echo htmlspecialchars($nextPath); ?>">

                <!-- Step 1: Restaurant details (same order as admin) -->
                <div class="space-y-5" data-step="1">
                    <h3 class="text-lg font-bold text-slate-900">Restaurant Details</h3>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5" for="name">Restaurant Name *</label>
                        <input class="block w-full rounded-lg border-slate-200 bg-white px-4 py-3 text-slate-900 placeholder:text-slate-400 focus:border-primary focus:ring-primary sm:text-sm shadow-sm" id="name" name="name" placeholder="The Tasty Bistro" type="text" value="<?php echo oldValue('name'); ?>" required/>
                    </div>
                    <input type="hidden" name="slug" id="slug" value="<?php echo oldValue('slug'); ?>">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5" for="description">Description</label>
                        <textarea class="block w-full rounded-lg border-slate-200 bg-white px-4 py-3 text-slate-900 placeholder:text-slate-400 focus:border-primary focus:ring-primary sm:text-sm shadow-sm" id="description" name="description" rows="3"><?php echo oldValue('description'); ?></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5" for="phone">Phone</label>
                        <input class="block w-full rounded-lg border-slate-200 bg-white px-4 py-3 text-slate-900 placeholder:text-slate-400 focus:border-primary focus:ring-primary sm:text-sm shadow-sm" id="phone" name="phone" placeholder="+234..." type="tel" value="<?php echo oldValue('phone'); ?>"/>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5" for="address">Address</label>
                        <textarea class="block w-full rounded-lg border-slate-200 bg-white px-4 py-3 text-slate-900 placeholder:text-slate-400 focus:border-primary focus:ring-primary sm:text-sm shadow-sm" id="address" name="address" rows="2"><?php echo oldValue('address'); ?></textarea>
                    </div>
                </div>

                <!-- Step 2: Manager account -->
                <div class="space-y-5 hidden" data-step="2">
                    <h3 class="text-lg font-bold text-slate-900">Manager Account</h3>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5" for="manager_email">Manager Email *</label>
                        <input class="block w-full rounded-lg border-slate-200 bg-white px-4 py-3 text-slate-900 placeholder:text-slate-400 focus:border-primary focus:ring-primary sm:text-sm shadow-sm" id="manager_email" name="manager_email" placeholder="manager@restaurant.com" type="email" value="<?php echo oldValue('manager_email'); ?>" required/>
                        <p class="mt-1 text-xs text-slate-500">This email will be used for manager login.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5" for="manager_password">Manager Password *</label>
                        <div class="relative">
                            <input class="block w-full rounded-lg border-slate-200 bg-white px-4 py-3 pr-12 text-slate-900 placeholder:text-slate-400 focus:border-primary focus:ring-primary sm:text-sm shadow-sm" id="manager_password" name="manager_password" placeholder="Enter password" type="password" minlength="8" required/>
                            <button type="button" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-primary transition-colors" aria-label="Toggle password visibility" data-password-toggle="manager_password">
                                <span class="material-symbols-outlined text-xl">visibility</span>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5" for="manager_password_confirm">Confirm Manager Password *</label>
                        <div class="relative">
                            <input class="block w-full rounded-lg border-slate-200 bg-white px-4 py-3 pr-12 text-slate-900 placeholder:text-slate-400 focus:border-primary focus:ring-primary sm:text-sm shadow-sm" id="manager_password_confirm" name="manager_password_confirm" placeholder="Confirm password" type="password" minlength="8" required/>
                            <button type="button" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-primary transition-colors" aria-label="Toggle password visibility" data-password-toggle="manager_password_confirm">
                                <span class="material-symbols-outlined text-xl">visibility</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="space-y-5 hidden" data-step="3">
                    <h3 class="text-lg font-bold text-slate-900">Social, Ratings, and Media</h3>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5" for="whatsapp_link">WhatsApp Link</label>
                        <input class="block w-full rounded-lg border-slate-200 bg-white px-4 py-3 text-slate-900 placeholder:text-slate-400 focus:border-primary focus:ring-primary sm:text-sm shadow-sm" id="whatsapp_link" name="whatsapp_link" placeholder="https://wa.me/..." type="url" value="<?php echo oldValue('whatsapp_link'); ?>"/>
                    </div>
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5" for="instagram_url">Instagram URL</label>
                            <input class="block w-full rounded-lg border-slate-200 bg-white px-4 py-3 text-slate-900 placeholder:text-slate-400 focus:border-primary focus:ring-primary sm:text-sm shadow-sm" id="instagram_url" name="instagram_url" placeholder="https://instagram.com/..." type="url" value="<?php echo oldValue('instagram_url'); ?>"/>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5" for="facebook_url">Facebook URL</label>
                            <input class="block w-full rounded-lg border-slate-200 bg-white px-4 py-3 text-slate-900 placeholder:text-slate-400 focus:border-primary focus:ring-primary sm:text-sm shadow-sm" id="facebook_url" name="facebook_url" placeholder="https://facebook.com/..." type="url" value="<?php echo oldValue('facebook_url'); ?>"/>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5" for="twitter_url">Twitter URL</label>
                        <input class="block w-full rounded-lg border-slate-200 bg-white px-4 py-3 text-slate-900 placeholder:text-slate-400 focus:border-primary focus:ring-primary sm:text-sm shadow-sm" id="twitter_url" name="twitter_url" placeholder="https://twitter.com/..." type="url" value="<?php echo oldValue('twitter_url'); ?>"/>
                    </div>
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5" for="rating_source">Rating Source</label>
                            <select class="block w-full rounded-lg border-slate-200 bg-white px-4 py-3 text-slate-900 focus:border-primary focus:ring-primary sm:text-sm shadow-sm" id="rating_source" name="rating_source">
                                <?php $selectedSource = oldValue('rating_source', 'Google'); ?>
                                <option value="Google" <?php echo $selectedSource === 'Google' ? 'selected' : ''; ?>>Google</option>
                                <option value="Yelp" <?php echo $selectedSource === 'Yelp' ? 'selected' : ''; ?>>Yelp</option>
                                <option value="TripAdvisor" <?php echo $selectedSource === 'TripAdvisor' ? 'selected' : ''; ?>>TripAdvisor</option>
                                <option value="Facebook" <?php echo $selectedSource === 'Facebook' ? 'selected' : ''; ?>>Facebook</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5" for="google_rating">Rating (0-5)</label>
                            <input class="block w-full rounded-lg border-slate-200 bg-white px-4 py-3 text-slate-900 focus:border-primary focus:ring-primary sm:text-sm shadow-sm" id="google_rating" name="google_rating" step="0.1" min="0" max="5" type="number" value="<?php echo oldValue('google_rating', '4.5'); ?>"/>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <?php $isActiveChecked = isset($_POST['is_active']) || $_SERVER['REQUEST_METHOD'] !== 'POST'; ?>
                        <input class="h-4 w-4 rounded border-slate-300 text-primary focus:ring-primary" id="is_active" name="is_active" type="checkbox" <?php echo $isActiveChecked ? 'checked' : ''; ?>/>
                        <label class="text-sm font-medium text-slate-700" for="is_active">Active</label>
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button id="prevBtn" class="hidden w-full sm:w-auto rounded-xl bg-slate-100 px-5 py-3 text-sm font-bold text-slate-900 hover:bg-slate-200 transition-colors" type="button">Back</button>
                    <button id="nextBtn" class="w-full sm:w-auto rounded-xl bg-primary px-5 py-3 text-sm font-bold text-white hover:bg-primary/90 transition-colors" type="button">Next Step</button>
                    <button id="submitBtn" class="hidden flex-1 rounded-xl bg-primary px-5 py-3 text-sm font-bold text-white shadow-lg shadow-primary/20 hover:bg-primary/90 transition-colors" type="submit">Create Account & Start Trial</button>
                </div>
            </form>

            <div class="mt-8 text-center">
                <p class="text-sm text-slate-600">
                    Already have an account?
                    <a class="font-semibold text-primary hover:text-primary/80 transition-colors" href="<?php echo htmlspecialchars($loginLink); ?>">Log In</a>
                </p>
            </div>
        </div>
    </div>
</div>

<script>
(() => {
    const steps = Array.from(document.querySelectorAll('[data-step]'));
    const progressFill = document.getElementById('progressFill');
    const progressText = document.getElementById('progressText');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const submitBtn = document.getElementById('submitBtn');
    const nameInput = document.getElementById('name');
    const slugInput = document.getElementById('slug');
    let currentStep = 1;

    const labels = {
        1: 'Step 1 of 3 - Restaurant Details',
        2: 'Step 2 of 3 - Manager Account',
        3: 'Step 3 of 3 - Social, Ratings, and Media'
    };

    function updateStep() {
        steps.forEach((el, idx) => {
            const step = idx + 1;
            el.classList.toggle('hidden', step !== currentStep);
        });
        progressFill.style.width = (currentStep / steps.length * 100) + '%';
        progressText.textContent = labels[currentStep] || '';
        prevBtn.classList.toggle('hidden', currentStep === 1);
        nextBtn.classList.toggle('hidden', currentStep === steps.length);
        submitBtn.classList.toggle('hidden', currentStep !== steps.length);
    }

    function validateCurrentStep() {
        const current = document.querySelector('[data-step="' + currentStep + '"]');
        if (!current) return true;
        const fields = Array.from(current.querySelectorAll('input,textarea,select')).filter((field) => field.hasAttribute('required'));
        for (const field of fields) {
            if (!field.reportValidity()) {
                return false;
            }
        }
        if (currentStep === 2) {
            const pw = document.getElementById('manager_password');
            const cpw = document.getElementById('manager_password_confirm');
            if (pw && cpw && pw.value !== cpw.value) {
                cpw.setCustomValidity('Passwords do not match.');
                cpw.reportValidity();
                return false;
            }
            if (cpw) {
                cpw.setCustomValidity('');
            }
        }
        return true;
    }

    nextBtn.addEventListener('click', () => {
        if (!validateCurrentStep()) return;
        currentStep = Math.min(steps.length, currentStep + 1);
        updateStep();
    });

    prevBtn.addEventListener('click', () => {
        currentStep = Math.max(1, currentStep - 1);
        updateStep();
    });

    if (nameInput && slugInput) {
        function syncSlug() {
            slugInput.value = nameInput.value
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');
        }
        nameInput.addEventListener('input', syncSlug);
        syncSlug();
    }

    document.querySelectorAll('[data-password-toggle]').forEach(function(btn) {
        var id = btn.getAttribute('data-password-toggle');
        var input = document.getElementById(id);
        var icon = btn.querySelector('.material-symbols-outlined');
        if (input && icon) {
            btn.addEventListener('click', function() {
                var isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';
                icon.textContent = isPassword ? 'visibility_off' : 'visibility';
            });
        }
    });

    updateStep();
})();
</script>
</body>
</html>
