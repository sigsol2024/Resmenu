<?php
/**
 * Manager QR Code Analytics
 */

require_once __DIR__ . '/../includes/auth.php';
requireManager();

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/qr-analytics.php';
require_once __DIR__ . '/../includes/csrf.php';

$restaurantId = getCurrentUserRestaurantId();
$pdo = getDBConnection();

// Get restaurant slug from URL
$restaurantSlug = $_GET['slug'] ?? '';

// Get restaurant data
$stmt = $pdo->prepare("SELECT * FROM restaurants WHERE id = ?");
$stmt->execute([$restaurantId]);
$restaurant = $stmt->fetch();

if (!$restaurant) {
    die('Restaurant not found.');
}

// Ensure slug is set
if (empty($restaurant['slug']) || !isset($restaurant['slug'])) {
    if (!empty($restaurantSlug)) {
        $restaurant['slug'] = $restaurantSlug;
    } else {
        $stmt = $pdo->prepare("SELECT slug FROM restaurants WHERE id = ?");
        $stmt->execute([$restaurantId]);
        $slugData = $stmt->fetch();
        if ($slugData && !empty($slugData['slug'])) {
            $restaurant['slug'] = $slugData['slug'];
        }
    }
}

// Get date filters
$startDate = $_GET['start_date'] ?? null;
$endDate = $_GET['end_date'] ?? null;

// Get analytics
$analytics = getQRCodeAnalytics($restaurantId, $startDate, $endDate);
if (!$analytics) {
    $analytics = [
        'total_scans' => 0,
        'scans_by_device' => [],
        'scans_by_browser' => [],
        'scans_by_location' => [],
        'scans_by_date' => [],
        'recent_scans' => []
    ];
}

// Calculate device percentages for pie chart
$deviceTotal = array_sum(array_column($analytics['scans_by_device'], 'count'));
$devicePercentages = [];
$deviceColors = ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'];
foreach ($analytics['scans_by_device'] as $index => $device) {
    $percentage = $deviceTotal > 0 ? ($device['count'] / $deviceTotal) * 100 : 0;
    $devicePercentages[] = [
        'type' => $device['device_type'],
        'count' => $device['count'],
        'percentage' => $percentage,
        'color' => $deviceColors[$index % count($deviceColors)]
    ];
}

// Calculate browser percentages for bar chart
$browserMax = !empty($analytics['scans_by_browser']) ? max(array_column($analytics['scans_by_browser'], 'count')) : 1;
$browserColors = ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4'];

// Prepare time data (last 14 days for cleaner display)
$timeData = array_slice(array_reverse($analytics['scans_by_date'] ?? []), 0, 14);
$timeMax = !empty($timeData) ? max(array_column($timeData, 'count')) : 1;

$pageTitle = 'QR Code Analytics';
include __DIR__ . '/../includes/manager-layout.php';
?>

<link href="https://fonts.googleapis.com/css?family=Roboto:400,400i,700" rel="stylesheet">

<style>
/* Clean Button and Icon Styles */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border-radius: 6px;
    font-weight: 500;
    font-size: 0.875rem;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
    background: #111827;
    color: #fff;
}

.btn svg {
    width: 16px;
    height: 16px;
    flex-shrink: 0;
}

.btn-primary {
    background: #111827;
    color: #fff;
}

.btn-primary:hover {
    background: #374151;
}

.btn-secondary {
    background: #f3f4f6;
    color: #111827;
}

.btn-secondary:hover {
    background: #e5e7eb;
}

.btn-small {
    padding: 6px 12px;
    font-size: 0.813rem;
}

.btn-small svg {
    width: 14px;
    height: 14px;
}

/* Form Styles */
.form-group {
    margin-bottom: 20px;
}

.form-label {
    display: block;
    margin-bottom: 6px;
    font-size: 0.875rem;
    font-weight: 500;
    color: #111827;
}

.form-input {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 0.875rem;
    color: #111827;
    transition: border-color 0.2s;
}

.form-input:focus {
    outline: none;
    border-color: #111827;
}

/* ===== DEVICE PIE CHART STYLES ===== */
<?php
// Define pie chart colors (orange, yellow, blue, purple, teal)
$pieColors = ['#ff6b35', '#f9c846', '#3498db', '#8b5cf6', '#06b6d4'];

// Build conic-gradient from actual data
$conicGradient = '#e5e7eb 0% 100%'; // Default gray
if (!empty($devicePercentages)) {
    $total = array_sum(array_column($devicePercentages, 'percentage'));
    if ($total > 0) {
        $gradientParts = [];
        $cumulative = 0;
        foreach ($devicePercentages as $index => $device) {
            $percent = ($device['percentage'] / $total) * 100;
            $color = $pieColors[$index % count($pieColors)];
            $start = $cumulative;
            $end = $cumulative + $percent;
            $gradientParts[] = "{$color} {$start}% {$end}%";
            $cumulative = $end;
        }
        $conicGradient = implode(', ', $gradientParts);
    }
}
?>

.device-pie-container {
    width: 100%;
    max-width: 320px;
    margin: 2rem auto;
    text-align: center;
    padding: 20px;
}

.device-pie-chart {
    position: relative;
    width: 280px;
    height: 280px;
    border-radius: 50%;
    margin: 0 auto;
    background: conic-gradient(<?php echo $conicGradient; ?>);
    box-shadow: 0 8px 32px rgba(0,0,0,0.1);
}

/* Animation on scroll */
.device-pie-chart.animate {
    animation: pieReveal 1s ease-out forwards;
}

@keyframes pieReveal {
    0% { transform: scale(0) rotate(-90deg); opacity: 0; }
    100% { transform: scale(1) rotate(0deg); opacity: 1; }
}

.device-pie-text {
    position: absolute;
    font-weight: bold;
    font-family: 'Roboto', sans-serif;
    font-size: 12px;
    white-space: nowrap;
    text-align: center;
    line-height: 1.3;
    text-shadow: 0 1px 3px rgba(0,0,0,0.5);
    z-index: 10;
    color: #ffffff;
}

.device-pie-chart.animate .device-pie-text {
    animation: textFadeIn 0.5s ease forwards;
}

@keyframes textFadeIn {
    from { opacity: 0; transform: translate(-50%, -50%) scale(0.8); }
    to { opacity: 1; transform: translate(-50%, -50%) scale(1); }
}

<?php
// Dynamic text positioning based on segment angles
if (!empty($devicePercentages)) {
    $total = array_sum(array_column($devicePercentages, 'percentage'));
    $deviceCount = count($devicePercentages);
    
    $cumulative = 0;
    for ($i = 0; $i < $deviceCount; $i++) {
        $percent = ($devicePercentages[$i]['percentage'] / $total) * 100;
        $midPercent = $cumulative + ($percent / 2);
        $angleDeg = ($midPercent / 100) * 360;
        $angleRad = deg2rad($angleDeg - 90); // -90 to start from top
        
        // Position text at ~35% radius from center
        $radius = 35;
        $x = 50 + ($radius * cos($angleRad));
        $y = 50 + ($radius * sin($angleRad));
        
        // For yellow segment, use dark text
        $textColor = ($i == 1) ? '#333333' : '#ffffff';
        
        echo ".device-pie-text.text" . ($i + 1) . " {\n";
        echo "    color: {$textColor};\n";
        echo "    left: {$x}%;\n";
        echo "    top: {$y}%;\n";
        echo "    transform: translate(-50%, -50%);\n";
        echo "    animation-delay: " . (0.3 + ($i * 0.2)) . "s;\n";
        echo "}\n";
        
        $cumulative += $percent;
    }
}
?>

/* ===== BAR CHART STYLES ===== */
.simple-bar-chart {
    --line-count: 10;
    --line-color: currentcolor;
    --line-opacity: 0.25;
    --item-gap: 2%;
    --item-default-color: #3498db;
    
    height: 12rem;
    display: grid;
    grid-auto-flow: column;
    grid-auto-columns: 1fr;
    gap: var(--item-gap);
    align-items: end;
    padding-inline: var(--item-gap);
    --padding-block: 1.8rem;
    padding-block: var(--padding-block);
    position: relative;
    isolation: isolate;
}

.simple-bar-chart::after {
    content: "";
    position: absolute;
    inset: var(--padding-block) 0;
    z-index: -1;
    --line-width: 1px;
    --line-spacing: calc(100% / var(--line-count));
    background-image: repeating-linear-gradient(
        to top, 
        transparent 0 calc(var(--line-spacing) - var(--line-width)), 
        var(--line-color) 0 var(--line-spacing)
    );
    box-shadow: 0 var(--line-width) 0 var(--line-color);
    opacity: var(--line-opacity);
}

.simple-bar-chart > .item {
    height: calc(1% * var(--val));
    background: linear-gradient(180deg, var(--clr, var(--item-default-color)) 0%, color-mix(in srgb, var(--clr, var(--item-default-color)) 70%, #000) 100%);
    border-radius: 4px 4px 0 0;
    position: relative;
    min-width: 30px;
}

.simple-bar-chart.animate > .item {
    animation: item-height 1s ease forwards;
}

@keyframes item-height { 
    from { transform: scaleY(0); transform-origin: bottom; } 
    to { transform: scaleY(1); transform-origin: bottom; }
}

.simple-bar-chart > .item > * { 
    position: absolute; 
    text-align: center;
    left: 50%;
    transform: translateX(-50%);
    white-space: nowrap;
}

.simple-bar-chart > .item > .label { 
    top: 100%;
    padding-top: 8px;
    font-size: 11px;
    color: var(--muted);
}

.simple-bar-chart > .item > .value { 
    bottom: 100%;
    padding-bottom: 6px;
    font-size: 12px;
    font-weight: 600;
    color: var(--text);
}

.simple-bar-chart.animate > .item > .value {
    animation: valueFadeIn 0.3s ease forwards;
    animation-delay: 0.8s;
}

@keyframes valueFadeIn {
    from { opacity: 0; transform: translateX(-50%) translateY(5px); }
    to { opacity: 1; transform: translateX(-50%) translateY(0); }
}

/* ===== LINE CHART STYLES ===== */
.time-chart-wrapper {
    padding: 20px;
    position: relative;
}

.time-chart {
    --chart-height: 200px;
    --y-label-width: 50px;
    --line-count: 5;
    --line-color: #e5e7eb;
    
    display: grid;
    grid-template-columns: var(--y-label-width) 1fr;
    gap: 10px;
    position: relative;
}

.time-chart-y-axis {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    height: var(--chart-height);
    text-align: right;
    padding-right: 10px;
    font-size: 11px;
    color: var(--muted);
}

.time-chart-area {
    position: relative;
    height: var(--chart-height);
}

/* Grid lines */
.time-chart-area::before {
    content: '';
    position: absolute;
    inset: 0;
    background: repeating-linear-gradient(
        to top,
        transparent 0 calc(100% / var(--line-count) - 1px),
        var(--line-color) 0 calc(100% / var(--line-count))
    );
    z-index: 0;
}

/* Bottom axis line */
.time-chart-area::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: var(--line-color);
}

.time-chart-bars {
    display: flex;
    align-items: flex-end;
    height: 100%;
    gap: 3px;
    position: relative;
    z-index: 1;
}

.time-chart-bar {
    flex: 1;
    position: relative;
    height: calc(1% * var(--val));
    min-width: 8px;
}

.time-chart-bar-fill {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 100%;
    background: linear-gradient(180deg, #3b82f6 0%, rgba(59, 130, 246, 0.2) 100%);
    border-radius: 3px 3px 0 0;
    transition: background 0.2s ease;
}

.time-chart.animate .time-chart-bar-fill {
    animation: chartBarGrow 1s ease-out forwards;
    animation-delay: calc(var(--index) * 0.03s);
}

@keyframes chartBarGrow {
    from { transform: scaleY(0); transform-origin: bottom; }
    to { transform: scaleY(1); transform-origin: bottom; }
}

.time-chart-bar:hover .time-chart-bar-fill {
    background: linear-gradient(180deg, #2563eb 0%, rgba(37, 99, 235, 0.3) 100%);
}

/* Tooltip on hover */
.time-chart-bar::after {
    content: attr(data-tooltip);
    position: absolute;
    bottom: calc(1% * var(--val) + 8px);
    left: 50%;
    transform: translateX(-50%) translateY(-5px);
    background: #1f2937;
    color: white;
    padding: 6px 10px;
    border-radius: 6px;
    font-size: 11px;
    white-space: nowrap;
    z-index: 20;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.2s ease, transform 0.2s ease;
}

.time-chart-bar:hover::after {
    opacity: 1;
    transform: translateX(-50%) translateY(0);
}

/* X-axis labels */
.time-chart-x-axis {
    grid-column: 2;
    display: flex;
    justify-content: space-between;
    padding-top: 10px;
    font-size: 10px;
    color: var(--muted);
}

.time-chart-x-axis span {
    text-align: center;
    flex: 1;
    max-width: 60px;
}

.time-chart-x-axis span:first-child {
    text-align: left;
}

.time-chart-x-axis span:last-child {
    text-align: right;
}

/* Connecting line overlay */
.time-chart-line {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 2;
    pointer-events: none;
}

.time-chart-line svg {
    width: 100%;
    height: 100%;
}

.time-chart-line path {
    fill: none;
    stroke: #3b82f6;
    stroke-width: 2.5;
    stroke-linecap: round;
    stroke-linejoin: round;
}

.time-chart.animate .time-chart-line path {
    stroke-dasharray: 1000;
    stroke-dashoffset: 1000;
    animation: drawLine 1.5s ease forwards;
    animation-delay: 0.3s;
}

@keyframes drawLine {
    to { stroke-dashoffset: 0; }
}

/* Data points */
.time-chart-line circle {
    fill: #3b82f6;
    stroke: white;
    stroke-width: 2;
}

.time-chart.animate .time-chart-line circle {
    animation: dotAppear 0.4s ease forwards;
}

@keyframes dotAppear {
    from { transform: scale(0); }
    to { transform: scale(1); }
}

/* ===== NO DATA STATE ===== */
.no-data {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 180px;
    color: var(--muted);
    font-size: 14px;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 968px) {
    .charts-grid {
        grid-template-columns: 1fr !important;
    }
    
    .pie-chart-container {
        flex-direction: column;
        gap: 24px;
    }
    
    .simple-bar-chart {
        overflow-x: auto;
        min-height: 14rem;
    }
    
    .simple-bar-chart > .item {
        min-width: 45px;
    }
}

@media (max-width: 768px) {
    .filter-grid {
        grid-template-columns: 1fr !important;
    }
    
    .simple-bar-chart > .item > .label {
        font-size: 10px;
    }
    
    .time-chart {
        --y-label-width: 40px;
        --chart-height: 150px;
    }
    
    .time-chart-y-axis {
        font-size: 9px;
    }
    
    .time-chart-x-axis {
        font-size: 9px;
    }
    
    .time-chart-bar::after {
        font-size: 10px;
        padding: 4px 6px;
    }
}
</style>

<div class="page-header">
    <h1 class="page-title">QR Code Analytics</h1>
    <p class="page-subtitle">View scan statistics, device types, and trends for your QR code</p>
</div>

<!-- Date Range Filter -->
<div class="settings-card">
    <div class="section-header">
        <h2 class="section-title">Date Range Filter</h2>
    </div>
    <form method="GET">
        <input type="hidden" name="slug" value="<?php echo htmlspecialchars($restaurant['slug']); ?>">
        <div class="filter-grid" style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 20px; align-items: end; padding: 0 20px 20px;">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Start Date</label>
                <input type="date" name="start_date" class="form-input" value="<?php echo htmlspecialchars($startDate ?? ''); ?>">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">End Date</label>
                <input type="date" name="end_date" class="form-input" value="<?php echo htmlspecialchars($endDate ?? ''); ?>">
            </div>
            <div style="display: flex; gap: 12px;">
                <button type="submit" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Filter
                </button>
                <a href="<?php echo SITE_URL; ?>/manager/qr-analytics.php<?php echo !empty($restaurant['slug']) ? '?slug=' . urlencode($restaurant['slug']) : ''; ?>" class="btn btn-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Clear
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Total Scans Stat -->
<div class="settings-card">
    <div style="text-align: center; padding: 30px 20px;">
        <div style="font-size: 3.5rem; font-weight: 800; background: linear-gradient(135deg, #6366f1, #8b5cf6); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; margin-bottom: 8px;">
            <?php echo number_format($analytics['total_scans']); ?>
        </div>
        <div style="font-size: 1rem; color: var(--muted); font-weight: 500;">Total Scans</div>
    </div>
</div>

<!-- Charts Grid -->
<div class="charts-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;">
    <!-- Scans by Device Type - Pie Chart -->
    <div class="settings-card">
        <div class="section-header">
            <h2 class="section-title">Scans by Device Type</h2>
        </div>
        <?php if (empty($devicePercentages)): ?>
            <div class="no-data">No device data available</div>
        <?php else: ?>
            <?php 
            // Colors matching the CSS pie chart colors
            $chartColors = ['#ff6b35', '#f9c846', '#3498db', '#8b5cf6', '#06b6d4'];
            $totalPct = array_sum(array_column($devicePercentages, 'percentage'));
            ?>
            <div class="device-pie-container">
                <div class="device-pie-chart">
                    <?php 
                    // Display text labels for ALL device types on the pie
                    foreach ($devicePercentages as $i => $device): 
                        $displayPct = $totalPct > 0 ? round(($device['percentage'] / $totalPct) * 100) : 0;
                    ?>
                        <div class="device-pie-text text<?php echo $i + 1; ?>">
                            <strong><?php echo htmlspecialchars($device['type']); ?></strong><br>
                            <?php echo number_format($device['count']); ?><br>
                            <?php echo $displayPct; ?>%
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Legend below chart -->
            <div style="display: flex; justify-content: center; gap: 16px; flex-wrap: wrap; padding: 15px 20px; border-top: 1px solid #e5e7eb;">
                <?php foreach ($devicePercentages as $i => $device): 
                    $displayPct = $totalPct > 0 ? round(($device['percentage'] / $totalPct) * 100) : 0;
                    $color = $chartColors[$i % count($chartColors)];
                ?>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 14px; height: 14px; border-radius: 3px; background: <?php echo $color; ?>;"></div>
                        <span style="font-size: 13px; color: var(--text);">
                            <?php echo htmlspecialchars($device['type']); ?>: <?php echo number_format($device['count']); ?> (<?php echo $displayPct; ?>%)
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Scans by Browser - Bar Chart -->
    <div class="settings-card">
        <div class="section-header">
            <h2 class="section-title">Scans by Browser</h2>
        </div>
        <?php if (empty($analytics['scans_by_browser'])): ?>
            <div class="no-data">No browser data available</div>
        <?php else: ?>
            <div class="simple-bar-chart">
                <?php foreach ($analytics['scans_by_browser'] as $index => $browser): 
                    $percentage = ($browser['count'] / $browserMax) * 100;
                    $color = $browserColors[$index % count($browserColors)];
                ?>
                    <div class="item" style="--clr: <?php echo $color; ?>; --val: <?php echo $percentage; ?>;">
                        <div class="value"><?php echo $browser['count']; ?></div>
                        <div class="label"><?php echo htmlspecialchars($browser['browser']); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Scans Over Time -->
<div class="settings-card">
    <div class="section-header">
        <h2 class="section-title">Scans Over Time</h2>
    </div>
    <?php if (empty($timeData)): ?>
        <div class="no-data">No scan history available</div>
    <?php else: ?>
        <?php
        // Calculate Y-axis labels
        $yLabels = [];
        $steps = 5;
        for ($i = $steps; $i >= 0; $i--) {
            $yLabels[] = round(($timeMax / $steps) * $i);
        }
        
        // Prepare data points for SVG line
        $dataCount = count($timeData);
        $svgPoints = [];
        foreach ($timeData as $index => $day) {
            $xPercent = $dataCount > 1 ? ($index / ($dataCount - 1)) * 100 : 50;
            $yPercent = $timeMax > 0 ? 100 - (($day['count'] / $timeMax) * 100) : 100;
            $svgPoints[] = ['x' => $xPercent, 'y' => $yPercent, 'count' => $day['count']];
        }
        ?>
        <div class="time-chart-wrapper">
            <div class="time-chart">
                <!-- Y-Axis Labels -->
                <div class="time-chart-y-axis">
                    <?php foreach ($yLabels as $label): ?>
                        <span><?php echo number_format($label); ?></span>
                    <?php endforeach; ?>
                </div>
                
                <!-- Chart Area -->
                <div class="time-chart-area">
                    <div class="time-chart-bars">
                        <?php foreach ($timeData as $index => $day): 
                            $heightPercent = $timeMax > 0 ? ($day['count'] / $timeMax) * 100 : 0;
                            $formattedDate = date('M j', strtotime($day['date']));
                        ?>
                            <div class="time-chart-bar" 
                                 style="--val: <?php echo max($heightPercent, 2); ?>; --index: <?php echo $index; ?>;"
                                 data-tooltip="<?php echo $formattedDate; ?>: <?php echo number_format($day['count']); ?> scans">
                                <div class="time-chart-bar-fill"></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- SVG Line overlay -->
                    <div class="time-chart-line">
                        <svg viewBox="0 0 100 100" preserveAspectRatio="none">
                            <?php if (count($svgPoints) > 1): ?>
                                <path d="M <?php 
                                    $pathParts = [];
                                    foreach ($svgPoints as $i => $pt) {
                                        $pathParts[] = $pt['x'] . ' ' . $pt['y'];
                                    }
                                    echo implode(' L ', $pathParts);
                                ?>" />
                                <?php foreach ($svgPoints as $i => $pt): ?>
                                    <circle cx="<?php echo $pt['x']; ?>" cy="<?php echo $pt['y']; ?>" r="1.5" style="animation-delay: <?php echo 0.5 + ($i * 0.05); ?>s;" />
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </svg>
                    </div>
                </div>
                
                <!-- X-Axis Labels -->
                <div class="time-chart-x-axis">
                    <?php 
                    // Show first, middle, and last dates
                    $showIndices = [0];
                    if ($dataCount > 2) {
                        $showIndices[] = floor($dataCount / 2);
                    }
                    if ($dataCount > 1) {
                        $showIndices[] = $dataCount - 1;
                    }
                    foreach ($showIndices as $idx): 
                        if (isset($timeData[$idx])):
                    ?>
                        <span><?php echo date('M j', strtotime($timeData[$idx]['date'])); ?></span>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Scans by Location -->
<div class="settings-card">
    <div class="section-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h2 class="section-title">Scans by Location</h2>
        <a href="<?php echo SITE_URL; ?>/api/qr-export.php?restaurant_id=<?php echo $restaurantId; ?>&format=csv" class="btn btn-secondary btn-small">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            Export CSV
        </a>
    </div>
    <div style="overflow-x: auto;">
        <table class="table">
            <thead>
                <tr>
                    <th>Country</th>
                    <th>City</th>
                    <th>Scans</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($analytics['scans_by_location'])): ?>
                    <tr>
                        <td colspan="3" style="text-align: center; color: var(--muted); padding: 20px;">No location data available</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($analytics['scans_by_location'] as $location): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($location['country'] ?? 'Unknown'); ?></td>
                            <td><?php echo htmlspecialchars($location['city'] ?? 'Unknown'); ?></td>
                            <td><?php echo number_format($location['count']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Recent Scans -->
<div class="settings-card">
    <div class="section-header">
        <h2 class="section-title">Recent Scans</h2>
    </div>
    <div style="overflow-x: auto;">
        <table class="table">
            <thead>
                <tr>
                    <th>Date & Time</th>
                    <th>Device</th>
                    <th>Browser</th>
                    <th>OS</th>
                    <th>Location</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($analytics['recent_scans'])): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; color: var(--muted); padding: 20px;">No scans yet</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($analytics['recent_scans'] as $scan): ?>
                        <tr>
                            <td><?php echo date('Y-m-d H:i:s', strtotime($scan['scanned_at'])); ?></td>
                            <td>
                                <span style="display: inline-flex; align-items: center; gap: 6px;">
                                    <?php 
                                    $deviceIcon = match($scan['device_type'] ?? '') {
                                        'Mobile' => '📱',
                                        'Tablet' => '📱',
                                        'Desktop' => '💻',
                                        default => '🖥️'
                                    };
                                    echo $deviceIcon . ' ' . htmlspecialchars($scan['device_type'] ?? 'Unknown');
                                    ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($scan['browser'] ?? 'Unknown'); ?></td>
                            <td><?php echo htmlspecialchars($scan['os'] ?? 'Unknown'); ?></td>
                            <td>
                                <?php 
                                $location = [];
                                if (!empty($scan['city'])) $location[] = $scan['city'];
                                if (!empty($scan['country'])) $location[] = $scan['country'];
                                echo htmlspecialchars(implode(', ', $location) ?: 'Unknown');
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Intersection Observer for chart animations on scroll
document.addEventListener('DOMContentLoaded', function() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                // Add animate class when chart comes into view
                entry.target.classList.add('animate');
                // Stop observing once animated
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.3 // Trigger when 30% of chart is visible
    });
    
    // Observe pie chart
    const pieChart = document.querySelector('.device-pie-chart');
    if (pieChart) {
        observer.observe(pieChart);
    }
    
    // Observe bar chart
    const barChart = document.querySelector('.simple-bar-chart');
    if (barChart) {
        observer.observe(barChart);
    }
    
    // Observe time/line chart
    const timeChart = document.querySelector('.time-chart');
    if (timeChart) {
        observer.observe(timeChart);
    }
});
</script>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
