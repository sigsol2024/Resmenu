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
require_once __DIR__ . '/includes/mail.php';
require_once __DIR__ . '/includes/recaptcha.php';
require_once __DIR__ . '/includes/rate-limit.php';

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
$info = '';

$recaptchaEnabled = recaptchaIsEnabled();
$recaptchaSiteKey = $recaptchaEnabled ? (string)RECAPTCHA_SITE_KEY : '';
$recaptchaSessionKey = 'registration_recaptcha_ok';

function registrationRecaptchaOkForEmail($email, $ttlSeconds = 300) {
    global $recaptchaSessionKey;
    $email = trim((string)$email);
    $sess = $_SESSION[$recaptchaSessionKey] ?? null;
    if (!is_array($sess)) return false;
    $at = (int)($sess['at'] ?? 0);
    $okEmail = (string)($sess['email'] ?? '');
    $okIp = (string)($sess['ip'] ?? '');
    $ip = (string)($_SERVER['REMOTE_ADDR'] ?? '');
    if ($at <= 0 || (time() - $at) > (int)$ttlSeconds) return false;
    if ($okEmail === '' || $okEmail !== $email) return false;
    // Bind cached CAPTCHA to same IP to reduce reuse abuse.
    if ($okIp !== '' && $ip !== '' && $okIp !== $ip) return false;
    return true;
}

function markRegistrationRecaptchaOk($email) {
    global $recaptchaSessionKey;
    $_SESSION[$recaptchaSessionKey] = [
        'at' => time(),
        'email' => trim((string)$email),
        'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
    ];
}

function maskEmailForDisplay($email) {
    $email = trim((string)$email);
    if ($email === '' || strpos($email, '@') === false) return $email;
    [$local, $domain] = explode('@', $email, 2);
    $local = (string)$local;
    $domain = (string)$domain;
    if (strlen($local) <= 2) {
        $maskedLocal = substr($local, 0, 1) . '*';
    } else {
        $maskedLocal = substr($local, 0, 1) . str_repeat('*', max(1, strlen($local) - 2)) . substr($local, -1);
    }
    return $maskedLocal . '@' . $domain;
}

function sendRegistrationOtpEmail($toEmail, $siteName, $otpCode, $validMinutes = 10) {
    $subject = 'Your ' . $siteName . ' verification code';
    $html = '<div style="font-family:Inter,Arial,sans-serif;line-height:1.5;color:#111827;">'
        . '<h2 style="margin:0 0 12px;">Verify your email</h2>'
        . '<p style="margin:0 0 16px;">Use this code to complete your registration. It expires in ' . (int)$validMinutes . ' minutes.</p>'
        . '<div style="font-size:28px;letter-spacing:6px;font-weight:800;background:#f3f4f6;padding:14px 16px;border-radius:12px;display:inline-block;">'
        . htmlspecialchars($otpCode, ENT_QUOTES, 'UTF-8')
        . '</div>'
        . '<p style="margin:16px 0 0;color:#6b7280;font-size:12px;">If you didn’t request this, you can ignore this email.</p>'
        . '</div>';
    return sendEmail($toEmail, '', $subject, $html);
}

$otpSessionKey = 'registration_email_otp';
$pendingSessionKey = 'registration_pending_payload';
$otpActive = is_array($_SESSION[$otpSessionKey] ?? null) && is_array($_SESSION[$pendingSessionKey] ?? null);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please refresh and try again.';
    } else {
        // Honeypot: bots often fill hidden fields.
        if (!empty($_POST['company'] ?? '')) {
            error_log('register: honeypot_triggered ip=' . (string)($_SERVER['REMOTE_ADDR'] ?? ''));
            $error = 'Invalid request.';
        }

        $action = (string)($_POST['registration_action'] ?? 'start');

        // Verify OTP step
        if ($action === 'verify_otp') {
            if (!$otpActive) {
                $error = 'Verification session expired. Please start registration again.';
            } else {
                $otp = $_SESSION[$otpSessionKey];
                $expiresAt = (int)($otp['expires_at'] ?? 0);
                if ($expiresAt > 0 && time() > $expiresAt) {
                    unset($_SESSION[$otpSessionKey], $_SESSION[$pendingSessionKey]);
                    $error = 'Verification code expired. Please request a new code.';
                } else {
                    $code = preg_replace('/\D+/', '', (string)($_POST['otp_code'] ?? ''));
                    if (strlen($code) !== 6) {
                        $error = 'Please enter the 6-digit code.';
                    } elseif (!hash_equals((string)($otp['hash'] ?? ''), hash('sha256', $code))) {
                        $error = 'Invalid verification code.';
                    } else {
                        $pdo = getDBConnection();
                        if (!$pdo) {
                            $error = 'Database connection failed. Please try again.';
                        } else {
                            $payload = $_SESSION[$pendingSessionKey];
                            $result = createRestaurantWithManager(
                                $pdo,
                                $payload,
                                [],
                                [
                                    'default_template_id' => 1,
                                    'trial_plan_slug' => 'enterprise',
                                    'trial_days' => 7,
                                ]
                            );

                            if ($result['success']) {
                                unset($_SESSION[$otpSessionKey], $_SESSION[$pendingSessionKey]);
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
            }
        } else {
            // Start or resend OTP step
            $payload = $_POST;
            $managerEmail = trim((string)($payload['manager_email'] ?? ''));
            if ($managerEmail === '' || !filter_var($managerEmail, FILTER_VALIDATE_EMAIL)) {
                $error = 'Please enter a valid manager email address.';
            } else {
                // Abuse prevention: cooldown + max attempts per window (per email + per IP)
                // This is enforced before sending any OTP email.
                $emailKey = 'reg-otp-email:' . strtolower($managerEmail);
                $ipKey = 'reg-otp-ip:' . (string)($_SERVER['REMOTE_ADDR'] ?? '');
                $globalKey = 'reg-otp-global';

                // If rate limit storage isn't available, fail closed to protect deliverability.
                if ($error === '' && !getRateLimitDir()) {
                    error_log('register: otp_rate_limit_storage_unavailable ip=' . ($ipKey ?: ''));
                    $error = 'Email verification is temporarily unavailable. Please try again later.';
                }

                // Disposable email blocking (basic)
                if ($error === '') {
                    $domain = strtolower(trim((string)substr(strrchr($managerEmail, "@") ?: '', 1)));
                    $blockedDomains = [
                        '10minutemail.com',
                        'tempmail.com',
                        'yopmail.com',
                        'mailinator.com',
                        'guerrillamail.com',
                        'guerrillamail.net',
                        'sharklasers.com',
                        'getnada.com',
                        'dispostable.com',
                    ];
                    foreach ($blockedDomains as $bd) {
                        $bd = strtolower(trim((string)$bd));
                        if ($bd === '' || $domain === '') continue;
                        if ($domain === $bd || (strlen($domain) > strlen($bd) && substr($domain, -strlen('.' . $bd)) === ('.' . $bd))) {
                            $error = 'Please use a real business email address (disposable emails are not allowed).';
                            break;
                        }
                    }
                }

                // Tightened cooldowns (seconds)
                $cooldownEmail = 60;
                $cooldownIp = 60;

                // Tightened windows + limits (active attack posture)
                $emailWindowSeconds = 15 * 60; // 15 minutes
                $ipWindowSeconds = 10 * 60;    // 10 minutes
                $globalWindowSeconds = 60;     // 1 minute
                $limitPerEmail = 2;            // 2 / 15 min
                $limitPerIp = 3;               // 3 / 10 min
                $limitGlobal = 30;             // 30 / minute (system-wide)

                // Cooldown checks
                if ($error === '') {
                    $cdEmail = rateLimitCooldownCheck($emailKey, $cooldownEmail, $emailWindowSeconds);
                    if (!$cdEmail['allowed']) {
                        $error = "Please wait {$cdEmail['retry_after']} seconds before requesting another verification code.";
                    }
                }
                if ($error === '') {
                    $cdIp = rateLimitCooldownCheck($ipKey, $cooldownIp, $ipWindowSeconds);
                    if (!$cdIp['allowed']) {
                        $error = "Too many requests. Please wait {$cdIp['retry_after']} seconds and try again.";
                    }
                }

                // Windowed max-attempt checks
                if ($error === '') {
                    $chkEmail = rateLimitCheck($emailKey, $limitPerEmail, $emailWindowSeconds);
                    if (!$chkEmail['allowed']) {
                        $mins = (int)ceil($chkEmail['retry_after'] / 60);
                        $error = "You've requested too many verification codes for this email. Please try again in {$mins} minute(s).";
                    }
                }
                if ($error === '') {
                    $chkIp = rateLimitCheck($ipKey, $limitPerIp, $ipWindowSeconds);
                    if (!$chkIp['allowed']) {
                        $mins = (int)ceil($chkIp['retry_after'] / 60);
                        $error = "Too many verification requests from your network. Please try again in {$mins} minute(s).";
                    }
                }
                if ($error === '') {
                    $chkGlobal = rateLimitCheck($globalKey, $limitGlobal, $globalWindowSeconds);
                    if (!$chkGlobal['allowed']) {
                        $error = 'Too many verification requests right now. Please try again in a minute.';
                    }
                }

                // reCAPTCHA gate (prevents bot OTP spam + protects email reputation)
                if ($error === '' && recaptchaIsEnabled() && !registrationRecaptchaOkForEmail($managerEmail)) {
                    $token = (string)($_POST['g-recaptcha-response'] ?? '');
                    $verify = verifyRecaptchaToken($token, $_SERVER['REMOTE_ADDR'] ?? '');
                    if (empty($verify['success'])) {
                        $error = 'Please complete the CAPTCHA to continue.';
                    } else {
                        markRegistrationRecaptchaOk($managerEmail);
                    }
                }

                $lastSentAt = (int)(($_SESSION[$otpSessionKey]['sent_at'] ?? 0));
                if ($action === 'resend_otp' && $lastSentAt && (time() - $lastSentAt) < 30) {
                    $error = 'Please wait a few seconds before requesting a new code.';
                } else {
                    // If the email already belongs to an existing manager, don't send OTPs (prevents targeted spam).
                    if ($error === '') {
                        $pdo = getDBConnection();
                        if ($pdo) {
                            $stmt = $pdo->prepare('SELECT id FROM managers WHERE email = ? LIMIT 1');
                            $stmt->execute([$managerEmail]);
                            if ($stmt->fetch(PDO::FETCH_ASSOC)) {
                                $error = 'An account already exists for this email. Please sign in instead.';
                            }
                        }
                    }

                    $otpCode = (string)random_int(100000, 999999);
                    $_SESSION[$otpSessionKey] = [
                        'hash' => hash('sha256', $otpCode),
                        'sent_at' => time(),
                        'expires_at' => time() + (10 * 60),
                        'email' => $managerEmail,
                    ];
                    $_SESSION[$pendingSessionKey] = $payload;
                    $otpActive = true;

                    // Count this as an OTP send attempt (even if mail fails).
                    rateLimitHit($emailKey, $emailWindowSeconds);
                    rateLimitHit($ipKey, $ipWindowSeconds);
                    rateLimitHit($globalKey, $globalWindowSeconds);

                    if (sendRegistrationOtpEmail($managerEmail, $siteNameRaw, $otpCode, 10)) {
                        $info = 'We sent a 6-digit verification code to ' . maskEmailForDisplay($managerEmail) . '.';
                    } else {
                        $error = 'We could not send the verification code. Please try again.';
                    }
                }
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
    <?php if ($recaptchaEnabled && $recaptchaSiteKey !== ''): ?>
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <?php endif; ?>
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

            <?php if ($info): ?>
                <div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    <?php echo htmlspecialchars($info); ?>
                </div>
            <?php endif; ?>

            <?php if ($otpActive): ?>
                <?php $otpEmail = (string)(($_SESSION[$otpSessionKey]['email'] ?? '') ?: ($_SESSION[$pendingSessionKey]['manager_email'] ?? '')); ?>
                <div class="mb-6">
                    <h3 class="text-lg font-bold text-slate-900">Verify your email</h3>
                    <p class="mt-2 text-sm text-slate-600">Enter the 6-digit code sent to <span class="font-semibold"><?php echo htmlspecialchars(maskEmailForDisplay($otpEmail)); ?></span>.</p>
                </div>

                <form class="space-y-5" method="post" autocomplete="off">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(getCSRFToken()); ?>">
                    <input type="hidden" name="registration_action" value="verify_otp">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5" for="otp_code">6-digit code</label>
                        <input class="block w-full rounded-lg border-slate-200 bg-white px-4 py-3 text-center tracking-[0.5em] font-extrabold text-slate-900 placeholder:text-slate-400 focus:border-primary focus:ring-primary sm:text-lg shadow-sm" id="otp_code" name="otp_code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" placeholder="••••••" required>
                        <p class="mt-2 text-xs text-slate-500">Code expires in 10 minutes.</p>
                    </div>

                    <button class="w-full rounded-xl bg-primary px-5 py-3 text-sm font-bold text-white hover:bg-primary/90 transition-colors" type="submit">Verify & Create Account</button>
                </form>

                <form class="mt-4" method="post">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(getCSRFToken()); ?>">
                    <input type="hidden" name="registration_action" value="resend_otp">
                    <input type="text" name="company" value="" style="display:none" tabindex="-1" autocomplete="off">
                    <?php
                    // Keep pending payload when resending.
                    foreach (($_SESSION[$pendingSessionKey] ?? []) as $k => $v) {
                        if ($k === 'csrf_token' || $k === 'registration_action' || $k === 'otp_code') continue;
                        if (is_array($v)) continue;
                        echo '<input type="hidden" name="' . htmlspecialchars((string)$k, ENT_QUOTES, 'UTF-8') . '" value="' . htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8') . '">' . "\n";
                    }
                    ?>
                    <?php if ($recaptchaEnabled && !registrationRecaptchaOkForEmail($otpEmail ?? '')): ?>
                        <div class="mt-4 flex justify-center">
                            <div class="g-recaptcha" data-sitekey="<?php echo htmlspecialchars($recaptchaSiteKey, ENT_QUOTES, 'UTF-8'); ?>"></div>
                        </div>
                    <?php endif; ?>
                    <button class="w-full rounded-xl bg-slate-100 px-5 py-3 text-sm font-bold text-slate-900 hover:bg-slate-200 transition-colors" type="submit">Resend Code</button>
                </form>
            <?php else: ?>
                <div class="mb-6">
                    <div class="h-2 w-full rounded-full bg-slate-200 overflow-hidden">
                        <div id="progressFill" class="h-full bg-primary transition-all duration-300" style="width: 33%"></div>
                    </div>
                    <p id="progressText" class="mt-2 text-xs text-slate-500">Step 1 of 3 - Restaurant Details</p>
                </div>

                <form id="registerForm" class="space-y-5" method="post">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(getCSRFToken()); ?>">
                    <input type="hidden" name="registration_action" value="start">
                    <input type="hidden" name="plan" value="<?php echo htmlspecialchars($selectedPlan); ?>">
                    <input type="hidden" name="cycle" value="<?php echo htmlspecialchars($selectedCycle); ?>">
                    <input type="hidden" name="next" value="<?php echo htmlspecialchars($nextPath); ?>">
                    <input type="text" name="company" value="" style="display:none" tabindex="-1" autocomplete="off">

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
                        <input class="block w-full rounded-lg border-slate-200 bg-white px-4 py-3 text-slate-900 placeholder:text-slate-400 focus:border-primary focus:ring-primary sm:text-sm shadow-sm" id="phone" name="phone" placeholder="2348012345678" type="tel" inputmode="numeric" pattern="[0-9]*" maxlength="15" value="<?php echo oldValue('phone'); ?>"/>
                        <p class="mt-1 text-xs text-slate-500">Numbers only.</p>
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

                    <?php if ($recaptchaEnabled && $recaptchaSiteKey !== ''): ?>
                        <div class="pt-2 flex justify-center">
                            <div class="g-recaptcha" data-sitekey="<?php echo htmlspecialchars($recaptchaSiteKey, ENT_QUOTES, 'UTF-8'); ?>"></div>
                        </div>
                        <p class="text-xs text-slate-500 text-center">Please complete the CAPTCHA before creating your account.</p>
                    <?php endif; ?>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button id="prevBtn" class="hidden w-full sm:w-auto rounded-xl bg-slate-100 px-5 py-3 text-sm font-bold text-slate-900 hover:bg-slate-200 transition-colors" type="button">Back</button>
                    <button id="nextBtn" class="w-full sm:w-auto rounded-xl bg-primary px-5 py-3 text-sm font-bold text-white hover:bg-primary/90 transition-colors" type="button">Next Step</button>
                    <button id="submitBtn" class="hidden flex-1 rounded-xl bg-primary px-5 py-3 text-sm font-bold text-white shadow-lg shadow-primary/20 hover:bg-primary/90 transition-colors" type="submit">Create Account & Start Trial</button>
                </div>
            </form>
            <?php endif; ?>

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
    const phoneInput = document.getElementById('phone');
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

    if (phoneInput) {
        phoneInput.addEventListener('input', function () {
            const digitsOnly = (phoneInput.value || '').replace(/\D+/g, '').slice(0, 15);
            if (phoneInput.value !== digitsOnly) {
                phoneInput.value = digitsOnly;
            }
        });
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

    // Client-side guard: ensure reCAPTCHA is completed before submitting registration.
    <?php if ($recaptchaEnabled && $recaptchaSiteKey !== ''): ?>
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            if (typeof grecaptcha !== 'undefined' && grecaptcha.getResponse) {
                const token = grecaptcha.getResponse();
                if (!token) {
                    e.preventDefault();
                    alert('Please complete the CAPTCHA to continue.');
                }
            }
        });
    }
    <?php endif; ?>

    updateStep();
})();
</script>
</body>
</html>
