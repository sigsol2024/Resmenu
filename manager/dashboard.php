<?php
/**
 * Manager Dashboard
 */

require_once __DIR__ . '/../includes/auth.php';
requireManager();

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/subscription.php';
require_once __DIR__ . '/../includes/subscription-middleware.php';

$pdo = getDBConnection();

// Get restaurant slug from URL or from user's restaurant
$restaurantSlug = $_GET['slug'] ?? '';
$restaurantId = getCurrentUserRestaurantId();

// Get restaurant info
$restaurant = null;
if ($restaurantId && $pdo) {
    $stmt = $pdo->prepare("SELECT * FROM restaurants WHERE id = ?");
    $stmt->execute([$restaurantId]);
    $restaurant = $stmt->fetch();
    
    // Verify slug matches if provided
    if ($restaurantSlug && $restaurant['slug'] !== $restaurantSlug) {
        // Redirect to correct URL
        header('Location: /manager/' . urlencode($restaurant['slug']));
        exit;
    }
    
    // If no slug provided but restaurant exists, redirect to slug URL
    if (!$restaurantSlug && $restaurant) {
        header('Location: /manager/' . urlencode($restaurant['slug']));
        exit;
    }
}

if (!$restaurant) {
    die('Restaurant not found or access denied.');
}

// Get statistics
$stats = [
    'categories' => 0,
    'menu_items' => 0,
    'available_items' => 0,
    'unavailable_items' => 0,
    'total_orders' => 0,
    'total_orders_amount' => 0
];

if ($pdo && $restaurantId) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE restaurant_id = ?");
    $stmt->execute([$restaurantId]);
    $stats['total_orders'] = (int) $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(total), 0) FROM orders WHERE restaurant_id = ? AND status IN ('pending','confirmed','on_hold','completed')");
    $stmt->execute([$restaurantId]);
    $stats['total_orders_amount'] = (float) $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE restaurant_id = ?");
    $stmt->execute([$restaurantId]);
    $stats['categories'] = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM menu_items WHERE restaurant_id = ?");
    $stmt->execute([$restaurantId]);
    $stats['menu_items'] = $stmt->fetchColumn();
    
    // Get available items count
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM menu_items WHERE restaurant_id = ? AND is_available = 1");
    $stmt->execute([$restaurantId]);
    $stats['available_items'] = $stmt->fetchColumn();
    
    // Get unavailable items count
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM menu_items WHERE restaurant_id = ? AND is_available = 0");
    $stmt->execute([$restaurantId]);
    $stats['unavailable_items'] = $stmt->fetchColumn();
    
    // Update restaurant stats in database
    updateRestaurantStats($restaurantId);
    
    // Get subscription info
    $subscription = getRestaurantSubscription($restaurantId);
    $subscriptionStatus = getSubscriptionStatusInfo($subscription);
    $subscriptionAccess = checkSubscriptionAccess($restaurantId);
    $trialDaysRemaining = $subscription ? getTrialDaysRemaining($subscription) : 0;
    
    // Get QR analytics stats
    require_once __DIR__ . '/../includes/qr-analytics.php';
    $qrAnalytics = getQRCodeAnalytics($restaurantId);
    if (!$qrAnalytics) {
        $qrAnalytics = [
            'total_scans' => 0,
            'scans_by_device' => [],
            'scans_by_browser' => [],
            'scans_by_location' => [],
            'recent_scans' => []
        ];
    }
    
    // Count unique browsers, devices, and locations
    $uniqueBrowsers = count($qrAnalytics['scans_by_browser']);
    $uniqueDevices = count($qrAnalytics['scans_by_device']);
    $uniqueLocations = count($qrAnalytics['scans_by_location']);
}

$pageTitle = 'Dashboard - ' . htmlspecialchars($restaurant['name']);
include __DIR__ . '/../includes/manager-layout.php';
?>

      <div class="page-header">
        <h1 class="page-title"><?php echo htmlspecialchars($restaurant['name']); ?></h1>
        <p class="page-subtitle">Overview of your restaurant menu and orders</p>
      </div>

      <?php if (isset($subscription) && $subscription): ?>
          <?php if ($subscription['status'] === 'trial' && $trialDaysRemaining > 0): ?>
              <!-- Trial Banner -->
              <div class="subscription-banner trial-banner">
                  <div class="banner-icon">
                      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                  </div>
                  <div class="banner-content">
                      <strong><?php echo $trialDaysRemaining; ?> days left in your free trial</strong>
                      <span>Your trial ends on <?php echo date('F j, Y', strtotime($subscription['trial_ends_at'])); ?>. Subscribe to keep all features.</span>
                  </div>
                  <a href="billing.php" class="banner-btn">
                      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                      </svg>
                      Subscribe Now
                  </a>
              </div>
          <?php elseif ($subscription['status'] === 'trial' && $trialDaysRemaining <= 0): ?>
              <!-- Trial Expired Banner -->
              <div class="subscription-banner expired-banner">
                  <div class="banner-icon">
                      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                      </svg>
                  </div>
                  <div class="banner-content">
                      <strong>Your free trial has ended</strong>
                      <span>Subscribe now to continue using all features.</span>
                  </div>
                  <a href="billing.php" class="banner-btn">
                      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                      </svg>
                      Subscribe Now
                  </a>
              </div>
          <?php elseif ($subscription['status'] === 'expired'): ?>
              <!-- Subscription Expired Banner -->
              <div class="subscription-banner expired-banner">
                  <div class="banner-icon">
                      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                      </svg>
                  </div>
                  <div class="banner-content">
                      <strong>Your subscription has expired</strong>
                      <span>Renew now to continue managing your menu.</span>
                  </div>
                  <a href="billing.php" class="banner-btn">
                      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                      </svg>
                      Renew Now
                  </a>
              </div>
          <?php endif; ?>
      <?php endif; ?>

<style>
/* Clean Manager Dashboard Styles */
.subscription-banner {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px 20px;
    border-radius: 8px;
    margin-bottom: 24px;
}

.subscription-banner.trial-banner {
    background: #fef3c7;
    border: 1px solid #fbbf24;
}

.subscription-banner.expired-banner {
    background: #fee2e2;
    border: 1px solid #f87171;
}

.subscription-banner .banner-icon {
    width: 40px;
    height: 40px;
    background: rgba(255,255,255,0.6);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.subscription-banner .banner-icon svg {
    width: 20px;
    height: 20px;
}

.trial-banner .banner-icon { color: #1e3a5f; }
.expired-banner .banner-icon { color: #dc2626; }

.subscription-banner .banner-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.subscription-banner .banner-content strong {
    font-size: 0.875rem;
    font-weight: 600;
    color: #111827;
}

.subscription-banner .banner-content span {
    font-size: 0.813rem;
    color: #6b7280;
}

.subscription-banner .banner-btn {
    background: #111827;
    color: #fff;
    padding: 10px 20px;
    border-radius: 6px;
    font-weight: 500;
    font-size: 0.875rem;
    text-decoration: none;
    white-space: nowrap;
    transition: background 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.subscription-banner .banner-btn svg {
    width: 16px;
    height: 16px;
}

.subscription-banner .banner-btn:hover {
    background: #374151;
}

@media (max-width: 640px) {
    .subscription-banner {
        flex-direction: column;
        text-align: center;
    }
    
    .subscription-banner .banner-content {
        align-items: center;
    }
    
    .subscription-banner .banner-btn {
        width: 100%;
        justify-content: center;
    }
}
</style>

      <!-- STATS -->
      <section class="stats">
        <div class="stat-card">
          <div class="stat-label">Menu Items</div>
          <div class="stat-value"><?php echo $stats['menu_items']; ?></div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Categories</div>
          <div class="stat-value"><?php echo $stats['categories']; ?></div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Available Items</div>
          <div class="stat-value"><?php echo $stats['available_items']; ?></div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Unavailable Items</div>
          <div class="stat-value"><?php echo $stats['unavailable_items']; ?></div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Total Orders</div>
          <div class="stat-value"><?php echo $stats['total_orders']; ?></div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Orders Revenue</div>
          <div class="stat-value">₦<?php echo number_format($stats['total_orders_amount'], 0); ?></div>
        </div>
      </section>

      <!-- QR ANALYTICS OVERVIEW -->
      <?php
      // Prepare chart data for analytics
      $analyticsChartData = [
          ['label' => 'Total Scans', 'value' => $qrAnalytics['total_scans'], 'color' => '#5EB344'],
          ['label' => 'Browsers', 'value' => $uniqueBrowsers, 'color' => '#FCB72A'],
          ['label' => 'Device Types', 'value' => $uniqueDevices, 'color' => '#F8821A'],
          ['label' => 'Locations', 'value' => $uniqueLocations, 'color' => '#963D97'],
          ['label' => 'Total Orders', 'value' => $stats['total_orders'], 'color' => '#4f46e5'],
          ['label' => 'Orders Revenue (₦)', 'value' => (int) $stats['total_orders_amount'], 'color' => '#10b981']
      ];
      
      // Calculate max value for percentage
      $analyticsMax = max(array_column($analyticsChartData, 'value'));
      if ($analyticsMax == 0) $analyticsMax = 1;
      
      foreach ($analyticsChartData as &$item) {
          $item['percentage'] = ($item['value'] / $analyticsMax) * 100;
      }
      unset($item);
      ?>
      <section class="chart-card">
        <h2 class="chart-title">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            Analytics Overview
        </h2>
        <div class="simple-bar-chart gradient-bars">
          <?php foreach ($analyticsChartData as $item): ?>
            <div class="item" style="--clr: <?php echo htmlspecialchars($item['color']); ?>; --val: <?php echo round($item['percentage'], 1); ?>">
              <div class="label"><?php echo htmlspecialchars($item['label']); ?></div>
              <div class="value"><?php echo number_format($item['value']); ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      </section>

      <!-- QUICK ACTIONS -->
      <section class="quick-actions">
        <h2 class="section-title">Quick Actions</h2>
        <div class="actions-grid">
          <a href="orders.php<?php echo $restaurantSlug ? '?slug=' . urlencode($restaurantSlug) : ''; ?>" class="action-card">
            <div class="action-header">
              <div class="action-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:22px !important;height:22px !important;max-width:22px;max-height:22px;">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                </svg>
              </div>
              <div class="action-title">View Orders</div>
            </div>
            <p class="action-desc">Manage and track customer orders, update status, and view order details</p>
            <div class="action-arrow">
              Get started
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:16px !important;height:16px !important;max-width:16px;max-height:16px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
              </svg>
            </div>
          </a>
          
          <a href="menu-items.php" class="action-card">
            <div class="action-header">
              <div class="action-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:22px !important;height:22px !important;max-width:22px;max-height:22px;">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-3h6m-3-3h6" />
                </svg>
              </div>
              <div class="action-title">Manage Menu Items</div>
            </div>
            <p class="action-desc">Create and update menu items with descriptions, prices, and images</p>
            <div class="action-arrow">
              Get started
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:16px !important;height:16px !important;max-width:16px;max-height:16px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
              </svg>
            </div>
          </a>
          
          <a href="categories.php" class="action-card">
            <div class="action-header">
              <div class="action-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:22px !important;height:22px !important;max-width:22px;max-height:22px;">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                </svg>
              </div>
              <div class="action-title">Manage Categories</div>
            </div>
            <p class="action-desc">Add, edit, or delete menu categories to organize your menu items</p>
            <div class="action-arrow">
              Get started
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:16px !important;height:16px !important;max-width:16px;max-height:16px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
              </svg>
            </div>
          </a>
          
          <a href="customization.php" class="action-card">
            <div class="action-header">
              <div class="action-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:22px !important;height:22px !important;max-width:22px;max-height:22px;">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 00-5.78 1.128 2.25 2.25 0 01-2.4 2.245 4.5 4.5 0 008.4-2.245c0-.399-.078-.78-.22-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.043-.025a15.994 15.994 0 011.622-3.395m3.42 3.42a15.995 15.995 0 004.764-4.648l3.876-5.814a1.151 1.151 0 00-1.597-1.597L14.146 6.32a15.996 15.996 0 00-4.649 4.763m3.42 3.42a6.776 6.776 0 00-3.42-3.42" />
                </svg>
              </div>
              <div class="action-title">Customize Menu</div>
            </div>
            <p class="action-desc">Change colors, fonts, and styling to match your restaurant brand</p>
            <div class="action-arrow">
              Get started
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:16px !important;height:16px !important;max-width:16px;max-height:16px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
              </svg>
            </div>
          </a>
          
          <a href="qr-code.php<?php echo $restaurantSlug ? '?slug=' . urlencode($restaurantSlug) : ''; ?>" class="action-card">
            <div class="action-header">
              <div class="action-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:22px !important;height:22px !important;max-width:22px;max-height:22px;">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.5a.75.75 0 01.75-.75h4.5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0V6.31l-3.72 3.72a.75.75 0 01-1.06-1.06l3.72-3.72H4.5a.75.75 0 01-.75-.75zm9.75 0a.75.75 0 01.75-.75h4.5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0V6.31l-3.72 3.72a.75.75 0 11-1.06-1.06l3.72-3.72H14.25a.75.75 0 01-.75-.75zM3.75 15a.75.75 0 01.75.75H5.69l3.72-3.72a.75.75 0 111.06 1.06l-3.72 3.72v1.19a.75.75 0 01-1.5 0v-4.5zm9.75 0a.75.75 0 01.75.75h1.19l-3.72-3.72a.75.75 0 111.06-1.06l3.72 3.72V10.5a.75.75 0 011.5 0v4.5a.75.75 0 01-.75.75z" />
                </svg>
              </div>
              <div class="action-title">View QR Codes</div>
            </div>
            <p class="action-desc">Generate, customize, and download QR codes for your restaurant menu</p>
            <div class="action-arrow">
              Get started
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:16px !important;height:16px !important;max-width:16px;max-height:16px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
              </svg>
            </div>
          </a>
          
          <a href="customization.php" class="action-card">
            <div class="action-header">
              <div class="action-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:22px !important;height:22px !important;max-width:22px;max-height:22px;">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.216.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
              </div>
              <div class="action-title">Design Template</div>
            </div>
            <p class="action-desc">Select and customize menu templates to match your restaurant's style</p>
            <div class="action-arrow">
              Get started
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:16px !important;height:16px !important;max-width:16px;max-height:16px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
              </svg>
            </div>
          </a>
          
          <a href="profile.php" class="action-card">
            <div class="action-header">
              <div class="action-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:22px !important;height:22px !important;max-width:22px;max-height:22px;">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                </svg>
              </div>
              <div class="action-title">Manage Account</div>
            </div>
            <p class="action-desc">Update your profile information and account settings</p>
            <div class="action-arrow">
              Get started
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:16px !important;height:16px !important;max-width:16px;max-height:16px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
              </svg>
            </div>
          </a>
        </div>
      </section>

<style>
/* Clean Manager Dashboard Styles */
.page-header {
    margin-bottom: 24px;
}

.page-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--text);
    margin: 0;
}

/* ===== STATS ===== */
.stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: #fff;
    padding: 24px;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    transition: box-shadow 0.2s;
}

.stat-card:hover {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.stat-label {
    font-size: 0.75rem;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
    font-weight: 600;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: #111827;
}

/* ===== BAR CHART ===== */
.chart-card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    padding: 24px;
    margin-bottom: 30px;
}

.chart-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #111827;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.chart-title svg {
    width: 20px;
    height: 20px;
    color: #6b7280;
    flex-shrink: 0;
}

.simple-bar-chart{
  --line-count: 10;
  --line-color: currentcolor;
  --line-opacity: 0.25;
  --item-gap: 2%;
  --item-default-color: #060606;
  
  height: 10rem;
  display: grid;
  grid-auto-flow: column;
  gap: var(--item-gap);
  align-items: end;
  padding-inline: var(--item-gap);
  --padding-block: 1.5rem;
  padding-block: var(--padding-block);
  position: relative;
  isolation: isolate;
}

.simple-bar-chart::after{
  content: "";
  position: absolute;
  inset: var(--padding-block) 0;
  z-index: -1;
  --line-width: 1px;
  --line-spacing: calc(100% / var(--line-count));
  background-image: repeating-linear-gradient(to top, transparent 0 calc(var(--line-spacing) - var(--line-width)), var(--line-color) 0 var(--line-spacing));
  box-shadow: 0 var(--line-width) 0 var(--line-color);
  opacity: var(--line-opacity);
}

.simple-bar-chart > .item{
  height: calc(1% * var(--val));
  background-color: var(--clr, var(--item-default-color));
  position: relative;
  animation: item-height 1s ease forwards;
  border-radius: 4px 4px 0 0;
}

@keyframes item-height { 
  from { height: 0 } 
}

.simple-bar-chart > .item > * { 
  position: absolute; 
  text-align: center;
  width: 100%;
}

.simple-bar-chart > .item > .label { 
    inset: 100% 0 auto 0;
    font-size: 0.75rem;
    color: #6b7280;
    font-weight: 500;
    margin-top: 4px;
}

.simple-bar-chart > .item > .value { 
    inset: auto 0 100% 0;
    font-size: 0.875rem;
    font-weight: 600;
    color: #111827;
    margin-bottom: 4px;
}

/* ===== QUICK ACTIONS ===== */
.quick-actions {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    padding: 24px;
    margin-bottom: 24px;
}

.section-title {
    font-size: 1.125rem;
    font-weight: 600;
    margin-bottom: 20px;
    color: #111827;
}

.actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 16px;
}

.action-card {
    padding: 20px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    text-decoration: none;
    color: #111827;
    transition: all 0.2s;
    display: flex;
    flex-direction: column;
    gap: 12px;
    background: #fff;
}

.action-card:hover {
    border-color: #111827;
    background: #f9fafb;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.action-header {
    display: flex;
    align-items: center;
    gap: 12px;
}

.action-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    background: #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #111827;
    flex-shrink: 0;
}

.action-icon svg {
    width: 20px;
    height: 20px;
}

.action-title {
    font-weight: 600;
    font-size: 1rem;
    color: #111827;
}

.action-desc {
    color: #6b7280;
    font-size: 0.875rem;
    line-height: 1.5;
}

.action-arrow {
    margin-top: auto;
    color: #111827;
    font-weight: 500;
    font-size: 0.875rem;
    display: flex;
    align-items: center;
    gap: 6px;
}

.action-arrow svg {
    width: 16px;
    height: 16px;
    transition: transform 0.2s;
}

.action-card:hover .action-arrow {
    transform: translateX(4px);
}

/* Mobile Responsive */
@media (max-width: 900px) {
    .actions-grid {
        grid-template-columns: 1fr;
    }
    
    .stats {
        grid-template-columns: 1fr;
        gap: 16px;
    }
    
    .stat-card {
        padding: 20px;
    }
}
</style>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
