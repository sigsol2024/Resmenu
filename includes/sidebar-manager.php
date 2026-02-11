<?php
/**
 * Manager Sidebar Component - Modern Design
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/config.php';

$currentPage = basename($_SERVER['PHP_SELF']);
$currentPath = $_SERVER['REQUEST_URI'];
$restaurantSlug = $_GET['slug'] ?? '';

// If slug is not in URL, get it from the restaurant data
if (empty($restaurantSlug)) {
    if (isset($restaurant) && isset($restaurant['slug'])) {
        $restaurantSlug = $restaurant['slug'];
    } else {
        // Try to get from database
        $pdo = getDBConnection();
        if ($pdo && isLoggedIn() && isManager()) {
            $restaurantId = getCurrentUserRestaurantId();
            if ($restaurantId) {
                $stmt = $pdo->prepare("SELECT slug FROM restaurants WHERE id = ?");
                $stmt->execute([$restaurantId]);
                $restaurantData = $stmt->fetch();
                if ($restaurantData) {
                    $restaurantSlug = $restaurantData['slug'];
                }
            }
        }
    }
}

// Use /manager/{slug} format for dashboard (matches .htaccess rewrite rule)
$dashboardHref = $restaurantSlug ? '/manager/' . urlencode($restaurantSlug) : '/manager/dashboard.php';
$slugParam = $restaurantSlug ? '?slug=' . urlencode($restaurantSlug) : '';

// Get user info for profile section
$userInfo = null;
$restaurantName = '';
$restaurantLogo = '';
if (isLoggedIn() && isManager()) {
    $pdo = getDBConnection();
    if ($pdo) {
        $userId = getCurrentUserId();
        $stmt = $pdo->prepare("SELECT username, email FROM managers WHERE id = ?");
        $stmt->execute([$userId]);
        $userInfo = $stmt->fetch();
        
        $restaurantId = getCurrentUserRestaurantId();
        if ($restaurantId) {
            $stmt = $pdo->prepare("SELECT name, logo FROM restaurants WHERE id = ?");
            $stmt->execute([$restaurantId]);
            $restaurant = $stmt->fetch();
            $restaurantName = $restaurant['name'] ?? '';
            $restaurantLogo = $restaurant['logo'] ?? '';
        }
    }
}

// Navigation items for manager
$navItems = [
    ['id' => 'dashboard', 'name' => 'Dashboard', 'href' => $dashboardHref, 'icon' => 'M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25'],
    ['id' => 'orders', 'name' => 'Orders', 'href' => '/manager/orders.php' . $slugParam, 'icon' => 'M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z'],
    ['id' => 'menu-items', 'name' => 'Menu Items', 'href' => '/manager/menu-items.php', 'icon' => 'M12 6v12m-3-3h6m-3-3h6'],
    ['id' => 'categories', 'name' => 'Categories', 'href' => '/manager/categories.php', 'icon' => 'M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z'],
    ['id' => 'qr-code', 'name' => 'QR Code', 'href' => '/manager/qr-code.php' . $slugParam, 'icon' => 'M3.75 4.5a.75.75 0 01.75-.75h4.5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0V6.31l-3.72 3.72a.75.75 0 01-1.06-1.06l3.72-3.72H4.5a.75.75 0 01-.75-.75zm9.75 0a.75.75 0 01.75-.75h4.5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0V6.31l-3.72 3.72a.75.75 0 11-1.06-1.06l3.72-3.72H14.25a.75.75 0 01-.75-.75zM3.75 15a.75.75 0 01.75.75H5.69l3.72-3.72a.75.75 0 111.06 1.06l-3.72 3.72v1.19a.75.75 0 01-1.5 0v-4.5zm9.75 0a.75.75 0 01.75.75h1.19l-3.72-3.72a.75.75 0 111.06-1.06l3.72 3.72V10.5a.75.75 0 011.5 0v4.5a.75.75 0 01-.75.75z'],
    ['id' => 'customization', 'name' => 'Templates', 'href' => '/manager/customization.php', 'icon' => 'M9.53 16.122a3 3 0 00-5.78 1.128 2.25 2.25 0 01-2.4 2.245 4.5 4.5 0 008.4-2.245c0-.399-.078-.78-.22-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.043-.025a15.994 15.994 0 011.622-3.395m3.42 3.42a15.995 15.995 0 004.764-4.648l3.876-5.814a1.151 1.151 0 00-1.597-1.597L14.146 6.32a15.996 15.996 0 00-4.649 4.763m3.42 3.42a6.776 6.776 0 00-3.42-3.42'],
    ['id' => 'billing', 'name' => 'Billing', 'href' => '/manager/billing.php', 'icon' => 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z'],
    ['id' => 'payment-settings', 'name' => 'Payment Settings', 'href' => '/manager/payment-settings.php', 'icon' => 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z'],
    ['id' => 'profile', 'name' => 'Profile', 'href' => '/manager/profile.php', 'icon' => 'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z'],
];

// Determine active item
$activeId = 'dashboard';
foreach ($navItems as $item) {
    if (strpos($currentPath, $item['href']) !== false || ($item['id'] === 'dashboard' && $currentPage === 'dashboard.php')) {
        $activeId = $item['id'];
        break;
    }
}

// Handle QR Code pages (qr-code.php and qr-analytics.php)
if ($currentPage === 'qr-code.php' || $currentPage === 'qr-analytics.php') {
    $activeId = 'qr-code';
}
// Handle Orders page (including restaurant-orders subpage)
if ($currentPage === 'orders.php' || $currentPage === 'restaurant-orders.php') {
    $activeId = 'orders';
}
// Handle Payment Settings page
if ($currentPage === 'payment-settings.php') {
    $activeId = 'payment-settings';
}

$username = $_SESSION['username'] ?? 'Manager';
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
                <?php if (!empty($restaurantLogo)): ?>
                    <img src="<?php echo UPLOAD_URL . '/logos/' . htmlspecialchars($restaurantLogo); ?>" alt="<?php echo htmlspecialchars($restaurantName ?: 'Restaurant'); ?>" class="logo-image-modern">
                <?php else: ?>
                    <div class="logo-icon-modern"><?php echo strtoupper(substr($restaurantName ?: 'M', 0, 1)); ?></div>
                <?php endif; ?>
                <div class="logo-text">
                    <span class="logo-title"><?php echo htmlspecialchars($restaurantName ?: 'Manager'); ?></span>
                    <span class="logo-subtitle">Restaurant Dashboard</span>
                </div>
            </div>
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
                        <p class="profile-role"><?php echo htmlspecialchars($restaurantName ?: ($userEmail ?: 'Manager')); ?></p>
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
