<?php
/**
 * Admin Sidebar Component - Modern Design
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/config.php';

$currentPage = basename($_SERVER['PHP_SELF']);
$currentPath = $_SERVER['REQUEST_URI'];

// Get user info for profile section
$userInfo = null;
if (isLoggedIn() && isSuperAdmin()) {
    $pdo = getDBConnection();
    if ($pdo) {
        $userId = getCurrentUserId();
        $stmt = $pdo->prepare("SELECT username, email FROM admins WHERE id = ?");
        $stmt->execute([$userId]);
        $userInfo = $stmt->fetch();
    }
}

// Navigation items for admin
$navItems = [
    ['id' => 'dashboard', 'name' => 'Dashboard', 'href' => '/admin/dashboard.php', 'icon' => 'M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25'],
    ['id' => 'restaurants', 'name' => 'Restaurants', 'href' => '/admin/restaurants.php', 'icon' => 'M13.5 21v-7.5a6 6 0 0112 0V21M4.5 21h15M4.5 9h15m-15 0a3 3 0 01-3-3V6a3 3 0 013-3h15a3 3 0 013 3v6a3 3 0 01-3 3m-15 0V9'],
    ['id' => 'managers', 'name' => 'Managers', 'href' => '/admin/managers.php', 'icon' => 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z'],
    ['id' => 'subscription-plans', 'name' => 'Subscription Plans', 'href' => '/admin/subscription-plans.php', 'icon' => 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z'],
    ['id' => 'subscriptions', 'name' => 'Subscriptions', 'href' => '/admin/subscriptions.php', 'icon' => 'M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z'],
    ['id' => 'payments', 'name' => 'Payments', 'href' => '/admin/payments.php', 'icon' => 'M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
    ['id' => 'payment-settings', 'name' => 'Payment Settings', 'href' => '/admin/payment-settings.php', 'icon' => 'M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 011.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.56.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.893.149c-.425.07-.765.383-.93.78-.165.398-.143.854.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 01-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.397.165-.71.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 01-.12-1.45l.527-.737c.25-.35.273-.806.108-1.204-.165-.397-.505-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.107-1.204l-.527-.738a1.125 1.125 0 01.12-1.45l.773-.773a1.125 1.125 0 011.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894z'],
    ['id' => 'templates', 'name' => 'Templates', 'href' => '/admin/templates.php', 'icon' => 'M9.53 16.122a3 3 0 00-5.78 1.128 2.25 2.25 0 01-2.4 2.245 4.5 4.5 0 008.4-2.245c0-.399-.078-.78-.22-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.043-.025a15.994 15.994 0 011.622-3.395m3.42 3.42a15.995 15.995 0 004.764-4.648l3.876-5.814a1.151 1.151 0 00-1.597-1.597L14.146 6.32a15.996 15.996 0 00-4.649 4.763m3.42 3.42a6.776 6.776 0 00-3.42-3.42'],
    ['id' => 'qr-templates', 'name' => 'QR Templates', 'href' => '/admin/qr-templates.php', 'icon' => 'M3.75 4.5a.75.75 0 01.75-.75h4.5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0V6.31l-3.72 3.72a.75.75 0 01-1.06-1.06l3.72-3.72H4.5a.75.75 0 01-.75-.75zm9.75 0a.75.75 0 01.75-.75h4.5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0V6.31l-3.72 3.72a.75.75 0 11-1.06-1.06l3.72-3.72H14.25a.75.75 0 01-.75-.75zM3.75 15a.75.75 0 01.75.75H5.69l3.72-3.72a.75.75 0 111.06 1.06l-3.72 3.72v1.19a.75.75 0 01-1.5 0v-4.5zm9.75 0a.75.75 0 01.75.75h1.19l-3.72-3.72a.75.75 0 111.06-1.06l3.72 3.72V10.5a.75.75 0 011.5 0v4.5a.75.75 0 01-.75.75z'],
    ['id' => 'profile', 'name' => 'Profile', 'href' => '/admin/profile.php', 'icon' => 'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z'],
];

// Determine active item
$activeId = 'dashboard';
foreach ($navItems as $item) {
    if (strpos($currentPath, $item['href']) !== false) {
        $activeId = $item['id'];
        break;
    }
}
// Special case for dashboard
if ($currentPage === 'dashboard.php' || $currentPath === '/admin/' || $currentPath === '/admin') {
    $activeId = 'dashboard';
}
// Special case for restaurant-view.php - should show restaurants as active
if ($currentPage === 'restaurant-view.php') {
    $activeId = 'restaurants';
}
// Special case for qr-templates.php
if ($currentPage === 'qr-templates.php') {
    $activeId = 'qr-templates';
}

$username = $_SESSION['username'] ?? 'Admin';
$userEmail = $userInfo['email'] ?? '';
$userInitials = strtoupper(substr($username, 0, 2));
?>
<!-- Mobile hamburger button -->
<button
    onclick="toggleMobile()"
    class="mobile-hamburger"
    aria-label="Toggle sidebar"
>
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="hamburger-icon">
        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
    </svg>
</button>

<!-- Mobile overlay -->
<div class="sidebar-overlay" onclick="toggleMobile()"></div>

<!-- Sidebar -->
<aside class="sidebar-modern" id="sidebar">
    <!-- Header with logo and collapse button -->
    <div class="sidebar-header-modern">
        <div class="sidebar-logo-wrapper">
            <div class="sidebar-logo">
                <div class="logo-icon-modern">S</div>
                <div class="logo-text">
                    <span class="logo-title">Super Admin</span>
                    <span class="logo-subtitle">Dashboard</span>
                </div>
            </div>
            <div class="logo-icon-modern-centered" style="display: none;">S</div>
        </div>

        <!-- Desktop collapse button -->
        <button
            onclick="toggleCollapse()"
            class="collapse-btn-modern"
            aria-label="Collapse sidebar"
        >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="collapse-icon">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
            </svg>
        </button>
    </div>


    <!-- Navigation -->
    <nav class="sidebar-nav">
        <ul class="nav-list">
            <?php foreach ($navItems as $item): ?>
                <?php $isActive = $activeId === $item['id']; ?>
                <li>
                    <a
                        href="<?php echo htmlspecialchars($item['href']); ?>"
                        class="nav-item <?php echo $isActive ? 'active' : ''; ?>"
                        title="<?php echo htmlspecialchars($item['name']); ?>"
                    >
                        <div class="nav-icon-wrapper">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="nav-icon">
                                <path stroke-linecap="round" stroke-linejoin="round" d="<?php echo htmlspecialchars($item['icon']); ?>" />
                            </svg>
                        </div>
                        <span class="nav-text"><?php echo htmlspecialchars($item['name']); ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>

    <!-- Bottom section with profile and logout -->
    <div class="sidebar-footer-modern">
        <!-- Profile Section -->
        <div class="sidebar-profile">
            <?php if (!isset($_COOKIE['sidebar_collapsed']) || $_COOKIE['sidebar_collapsed'] !== 'true'): ?>
                <div class="profile-card">
                    <div class="profile-avatar"><?php echo htmlspecialchars($userInitials); ?></div>
                    <div class="profile-info">
                        <p class="profile-name"><?php echo htmlspecialchars($username); ?></p>
                        <p class="profile-role"><?php echo htmlspecialchars($userEmail ?: 'Administrator'); ?></p>
                    </div>
                    <div class="profile-status" title="Online"></div>
                </div>
            <?php else: ?>
                <div class="profile-avatar-centered">
                    <div class="profile-avatar-small"><?php echo htmlspecialchars($userInitials); ?></div>
                    <div class="profile-status-small"></div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Logout Button -->
        <div class="sidebar-logout">
            <a
                href="/admin/logout.php"
                class="logout-btn"
                title="Logout"
            >
                <div class="nav-icon-wrapper">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="nav-icon logout-icon">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9l5.5-5.5m0 0l-5.5-5.5m5.5 5.5H3.75" />
                    </svg>
                </div>
                <span class="nav-text">Logout</span>
            </a>
        </div>
    </div>
</aside>
