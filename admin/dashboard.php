<?php
/**
 * Super Admin Dashboard
 */

require_once __DIR__ . '/../includes/auth.php';
requireSuperAdmin();

require_once __DIR__ . '/../includes/functions.php';

$pdo = getDBConnection();

// Get statistics
$stats = [
    'restaurants' => 0,
    'categories' => 0,
    'menu_items' => 0,
    'managers' => 0,
    'total_revenue' => 0
];

if ($pdo) {
    // Get ALL statistics from database - no date filters, includes all historical data
    try {
        // Count ALL restaurants (including inactive and old ones)
        $stats['restaurants'] = (int)$pdo->query("SELECT COUNT(*) FROM restaurants")->fetchColumn();
        
        // Count ALL categories (including inactive and old ones)
        $stats['categories'] = (int)$pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
        
        // Count ALL menu items (including unavailable and old ones)
        $stats['menu_items'] = (int)$pdo->query("SELECT COUNT(*) FROM menu_items")->fetchColumn();
        
        // Count ALL managers (including old accounts)
        $stats['managers'] = (int)$pdo->query("SELECT COUNT(*) FROM managers")->fetchColumn();
        
        // Total revenue (all restaurants)
        $stats['total_revenue'] = (float)$pdo->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE status IN ('pending','confirmed','on_hold','completed')")->fetchColumn();
        
        // Count active restaurants only
        $stats['active_restaurants'] = (int)$pdo->query("SELECT COUNT(*) FROM restaurants WHERE is_active = 1")->fetchColumn();
        
        // Count active categories only
        $stats['active_categories'] = (int)$pdo->query("SELECT COUNT(*) FROM categories WHERE is_active = 1")->fetchColumn();
        
        // Get most recent 7 restaurants (for the list below)
        $stmt = $pdo->prepare("SELECT * FROM restaurants ORDER BY created_at DESC LIMIT 7");
        $stmt->execute();
        $restaurants = $stmt->fetchAll();
        
        // Prepare chart data with ALL historical data (including Total Revenue)
        $chartData = [
            ['label' => 'Restaurants', 'value' => $stats['restaurants'], 'color' => '#5EB344'],
            ['label' => 'Categories', 'value' => $stats['categories'], 'color' => '#FCB72A'],
            ['label' => 'Menu Items', 'value' => $stats['menu_items'], 'color' => '#F8821A'],
            ['label' => 'Managers', 'value' => $stats['managers'], 'color' => '#E0393E'],
            ['label' => 'Active Restaurants', 'value' => $stats['active_restaurants'], 'color' => '#963D97'],
            ['label' => 'Active Categories', 'value' => $stats['active_categories'], 'color' => '#069CDB'],
            ['label' => 'Total Revenue (₦)', 'value' => (int) $stats['total_revenue'], 'color' => '#10b981']
        ];
        
        // Calculate max value for percentage calculation
        $maxValue = max(array_column($chartData, 'value'));
        if ($maxValue == 0) {
            $maxValue = 1; // Prevent division by zero
        }
        
        // Calculate percentages based on max value
        foreach ($chartData as &$item) {
            $item['percentage'] = ($item['value'] / $maxValue) * 100;
        }
        unset($item);
        
    } catch (PDOException $e) {
        // Log error but don't break the page
        error_log("Dashboard statistics error: " . $e->getMessage());
        $chartData = [];
    }
}

$pageTitle = 'Super Admin Dashboard';
include __DIR__ . '/../includes/admin-layout.php';
?>

<style>
/* Clean Dashboard Styles */
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
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: #fff;
    padding: 16px;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    transition: box-shadow 0.2s;
    min-height: 80px;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
}

.stat-card:hover {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.stat-label {
    font-size: 0.7rem;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 6px;
    font-weight: 600;
    line-height: 1.2;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.stat-value {
    font-size: 1.35rem;
    font-weight: 700;
    color: #111827;
    line-height: 1.3;
    overflow: hidden;
    text-overflow: ellipsis;
    min-height: 1.5em;
}

/* ===== RESTAURANT LIST (ACCORDION) ===== */
.list-card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    margin-bottom: 24px;
}

.list-card-header {
    padding: 20px 24px;
    border-bottom: 1px solid #e5e7eb;
}

.list-card-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #111827;
    margin: 0;
}

.restaurant {
    border-top: 1px solid #e5e7eb;
}

.restaurant:first-child {
    border-top: none;
}

.restaurant-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 24px;
    cursor: pointer;
    transition: background 0.2s;
}

.restaurant-header:hover {
    background: #f9fafb;
}

.restaurant-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
    flex: 1;
}

.restaurant-name {
    font-weight: 600;
    color: #111827;
    font-size: 0.875rem;
}

.restaurant-slug {
    font-size: 0.75rem;
    color: #6b7280;
    font-family: monospace;
}

.restaurant-toggle {
    color: #6b7280;
    transition: transform 0.2s;
    font-size: 0.875rem;
    flex-shrink: 0;
    margin-left: 16px;
}

.restaurant.open .restaurant-toggle {
    transform: rotate(180deg);
}

.restaurant-body {
    display: none;
    padding: 16px 24px;
    background: #f9fafb;
    border-top: 1px solid #e5e7eb;
}

.restaurant.open .restaurant-body {
    display: block;
}

/* ===== ACTIONS ===== */
.actions {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.actions a,
.actions button {
    padding: 8px 16px;
    border-radius: 6px;
    border: none;
    font-size: 0.813rem;
    cursor: pointer;
    text-decoration: none;
    font-weight: 500;
    transition: background 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.actions svg {
    width: 14px;
    height: 14px;
    flex-shrink: 0;
}

.btn-manage {
    background: #111827;
    color: #fff;
}

.btn-manage:hover {
    background: #374151;
}

.btn-edit {
    background: #f3f4f6;
    color: #374151;
}

.btn-edit:hover {
    background: #e5e7eb;
}

.btn-view {
    background: #f3f4f6;
    color: #374151;
}

.btn-view:hover {
    background: #e5e7eb;
}

.btn-delete {
    background: #dc2626;
    color: #fff;
}

.btn-delete:hover {
    background: #b91c1c;
}

.empty-state {
    padding: 60px 20px;
    text-align: center;
    color: #6b7280;
}

.empty-state p {
    margin-bottom: 12px;
    font-size: 0.875rem;
}

.empty-state a {
    color: #111827;
    text-decoration: none;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.empty-state a:hover {
    text-decoration: underline;
}

.empty-state svg {
    width: 16px;
    height: 16px;
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

/* Mobile Responsive */
@media (max-width: 768px) {
    .stats {
        grid-template-columns: 1fr;
        gap: 16px;
    }
    
    .stat-card {
        padding: 20px;
    }
    
    .chart-card {
        padding: 20px;
    }
    
    .restaurant-header {
        padding: 12px 16px;
    }
    
    .restaurant-body {
        padding: 12px 16px;
    }
    
    .actions {
        flex-direction: column;
    }
    
    .actions a,
    .actions button {
        width: 100%;
        justify-content: center;
    }
}
</style>

<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">Dashboard</h1>
    <p class="page-subtitle">Overview of your restaurant platform</p>
</div>

<!-- STATS -->
<section class="stats">
  <div class="stat-card">
    <div class="stat-label">Restaurants</div>
    <div class="stat-value"><?php echo $stats['restaurants']; ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Categories</div>
    <div class="stat-value"><?php echo $stats['categories']; ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Menu Items</div>
    <div class="stat-value"><?php echo $stats['menu_items']; ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Managers</div>
    <div class="stat-value"><?php echo $stats['managers']; ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Total Revenue (All Restaurants)</div>
    <div class="stat-value">₦<?php echo number_format($stats['total_revenue'], 0); ?></div>
  </div>
</section>

<!-- BAR CHART -->
<?php if ($pdo && isset($chartData)): ?>
<section class="chart-card">
  <h2 class="chart-title">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
    </svg>
    Statistics Overview
  </h2>
  <div class="simple-bar-chart gradient-bars">
    <?php foreach ($chartData as $item): ?>
      <div class="item" style="--clr: <?php echo htmlspecialchars($item['color']); ?>; --val: <?php echo round($item['percentage'], 1); ?>">
        <div class="label"><?php echo htmlspecialchars($item['label']); ?></div>
        <div class="value"><?php echo number_format($item['value']); ?></div>
      </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<!-- RESTAURANT LIST -->
<section class="list-card">
  <div class="list-card-header">
    <h2 class="list-card-title">Recent Restaurants</h2>
  </div>
  <?php if (empty($restaurants)): ?>
    <div class="empty-state">
      <p>No restaurants found.</p>
      <p>
        <a href="restaurants.php">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Create your first restaurant
        </a>
      </p>
    </div>
  <?php else: ?>
    <?php foreach ($restaurants as $restaurant): ?>
      <div class="restaurant" onclick="toggleRestaurant(this)">
        <div class="restaurant-header">
          <div class="restaurant-info">
            <span class="restaurant-name"><?php echo htmlspecialchars($restaurant['name']); ?></span>
            <span class="restaurant-slug"><?php echo htmlspecialchars($restaurant['slug']); ?></span>
            <?php if ($restaurant['created_at']): ?>
              <span class="restaurant-date" style="font-size: 0.75rem; color: #6b7280; margin-top: 4px;">
                Created: <?php echo date('M d, Y g:i A', strtotime($restaurant['created_at'])); ?>
              </span>
            <?php endif; ?>
          </div>
          <span class="restaurant-toggle">▼</span>
        </div>
        <div class="restaurant-body">
          <div class="actions-cell">
            <button class="actions-btn" onclick="event.stopPropagation();toggleDropdown(this)" title="Actions">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
              </svg>
            </button>
            <div class="actions-dropdown">
              <a href="restaurant-view.php?slug=<?php echo htmlspecialchars($restaurant['slug']); ?>" class="actions-dropdown-item">Manage</a>
              <a href="restaurants.php?action=edit&id=<?php echo $restaurant['id']; ?>" class="actions-dropdown-item">Edit</a>
              <a href="/restaurant/<?php echo htmlspecialchars($restaurant['slug']); ?>" target="_blank" class="actions-dropdown-item">View Menu</a>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</section>

<script>
function toggleRestaurant(el){
  el.classList.toggle('open');
}
function toggleDropdown(btn) {
  document.querySelectorAll('.actions-dropdown.show').forEach(d => d.classList.remove('show'));
  const dropdown = btn.nextElementSibling;
  dropdown.classList.toggle('show');
  document.addEventListener('click', function closeDropdown(e) {
    if (!btn.contains(e.target) && !dropdown.contains(e.target)) {
      dropdown.classList.remove('show');
      document.removeEventListener('click', closeDropdown);
    }
  });
}
</script>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
