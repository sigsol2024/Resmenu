<?php
/**
 * TEMPORARY: Register a new admin user.
 * Delete this file after creating your admin account.
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = getDBConnection();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please refresh and try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';

        if ($username === '' || $email === '' || $password === '' || $password_confirm === '') {
            $error = 'All fields are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } elseif (!preg_match('/^[a-zA-Z0-9_]{3,100}$/', $username)) {
            $error = 'Username must be 3–100 characters (letters, numbers, underscore only).';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters.';
        } elseif ($password !== $password_confirm) {
            $error = 'Passwords do not match.';
        } elseif (!$pdo) {
            $error = 'Database connection failed.';
        } else {
            try {
                $stmt = $pdo->prepare("SELECT id FROM admins WHERE username = ? OR email = ?");
                $stmt->execute([$username, $email]);
                if ($stmt->fetch()) {
                    $error = 'That username or email is already in use.';
                } else {
                    $hash = hashPassword($password);
                    $stmt = $pdo->prepare("INSERT INTO admins (username, email, password_hash) VALUES (?, ?, ?)");
                    $stmt->execute([$username, $email, $hash]);
                    $success = 'Admin account created. You can now log in at the main login page. Delete this file (register-admin.php) when done.';
                }
            } catch (PDOException $e) {
                error_log("Admin registration error: " . $e->getMessage());
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}

$siteName = 'Resmenu';
if ($pdo) {
    $settings = getSiteSettings();
    $siteName = $settings['site_name'] ?? 'Resmenu';
}
$siteName = htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Admin (Temp) | <?php echo $siteName; ?></title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet"/>
    <script>
        tailwind.config = { theme: { extend: { colors: { primary: "#f97415" }, fontFamily: { sans: ["Inter", "sans-serif"] } } } };
    </script>
</head>
<body class="bg-slate-100 min-h-screen font-sans antialiased flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6 text-amber-800 text-sm">
            <strong>Temp page.</strong> Delete <code class="bg-amber-100 px-1 rounded">register-admin.php</code> after creating your admin.
        </div>

        <div class="bg-white rounded-2xl shadow-lg border border-slate-200 p-8">
            <h1 class="text-2xl font-bold text-slate-900 mb-2">Register new admin</h1>
            <p class="text-slate-500 text-sm mb-6">Creates a super-admin account. Log in at the main site after.</p>

            <?php if ($error): ?>
                <div class="mb-4 p-3 rounded-lg bg-red-50 text-red-700 text-sm"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="mb-4 p-3 rounded-lg bg-green-50 text-green-800 text-sm"><?php echo htmlspecialchars($success); ?></div>
                <p class="text-slate-600 text-sm"><a href="/" class="text-primary font-medium hover:underline">Go to login</a></p>
            <?php else: ?>
                <form method="post" action="" class="space-y-4">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(getCSRFToken()); ?>">
                    <div>
                        <label for="username" class="block text-sm font-medium text-slate-700 mb-1">Username</label>
                        <input type="text" id="username" name="username" required
                               value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                               class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-primary focus:border-primary">
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                        <input type="email" id="email" name="email" required
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                               class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-primary focus:border-primary">
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                        <input type="password" id="password" name="password" required minlength="8"
                               class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-primary focus:border-primary">
                        <p class="text-xs text-slate-500 mt-1">At least 8 characters</p>
                    </div>
                    <div>
                        <label for="password_confirm" class="block text-sm font-medium text-slate-700 mb-1">Confirm password</label>
                        <input type="password" id="password_confirm" name="password_confirm" required minlength="8"
                               class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-primary focus:border-primary">
                    </div>
                    <button type="submit" class="w-full py-3 rounded-lg bg-primary text-white font-semibold hover:bg-primary/90 transition-colors">
                        Create admin
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <p class="mt-4 text-center text-slate-500 text-sm">
            <a href="/" class="text-primary hover:underline">Back to login</a>
        </p>
    </div>
</body>
</html>
