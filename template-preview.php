<?php
/**
 * Template Preview - Renders a template with sample data for showcase.
 * URL: /template1-preview, /template2-preview, etc. (via .htaccess)
 * Allow embedding in iframe on resmenu.net (X-Frame-Options omitted for this URL in .htaccess).
 */
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/template-loader.php';
require_once __DIR__ . '/config/config.php';

// Allow this page to be embedded in iframes on resmenu.net (and same origin)
header("Content-Security-Policy: frame-ancestors 'self' https://resmenu.net https://www.resmenu.net http://resmenu.net http://www.resmenu.net");

// Allow template IDs 1–20 so new templates (5, 6, …) get the same preview behaviour and viewport widget
$templateId = isset($_GET['t']) ? max(1, min(20, (int)$_GET['t'])) : 1;

$siteSettings = getSiteSettings();
$siteName = $siteSettings['site_name'] ?? 'SigSol Resmenu';
$siteLogo = $siteSettings['site_logo'] ?? null;

$uploadBaseUrl = (defined('SITE_URL') ? rtrim(SITE_URL, '/') : '') . '/uploads';
$baseUrl = (defined('SITE_URL') ? rtrim(SITE_URL, '/') : '');

$pdo = getDBConnection();

// Template cover image from DB (used as hero/cover in preview)
$templateCoverUrl = null;
if ($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT preview_image FROM templates WHERE id = ? AND is_active = 1");
        $stmt->execute([$templateId]);
        $row = $stmt->fetch();
        if ($row && !empty($row['preview_image'])) {
            $templateCoverUrl = $baseUrl . '/uploads/template-previews/' . $row['preview_image'];
        }
    } catch (Exception $e) {
        // ignore
    }
}

// Menu item images from any restaurant (for menu items in preview)
$sampleImages = [];
// Category images: match by type so we don't use drink images for food sections (template 1)
$categoryImagesByType = ['starters' => [], 'mains' => [], 'desserts' => [], 'drinks' => []];
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT image FROM menu_items WHERE image IS NOT NULL AND image != '' LIMIT 12");
        while ($row = $stmt->fetch()) {
            $sampleImages[] = $row['image'];
        }
        $stmt = $pdo->query("SELECT id, name, slug, image FROM categories WHERE image IS NOT NULL AND image != ''");
        while ($row = $stmt->fetch()) {
            $name = strtolower($row['name'] . ' ' . $row['slug']);
            $img = $row['image'];
            if (preg_match('/starter|appetizer|salad|soup|small plate/i', $name)) {
                $categoryImagesByType['starters'][] = $img;
            } elseif (preg_match('/main|entree|grill|mains|rice|pasta|noodle|taco|burger|meat|fish|chicken|beef|seafood|special/i', $name)) {
                $categoryImagesByType['mains'][] = $img;
            } elseif (preg_match('/dessert|sweet|cake|ice|pastry|bakery/i', $name)) {
                $categoryImagesByType['desserts'][] = $img;
            } elseif (preg_match('/drink|beverage|wine|beer|cocktail|coffee|tea|juice|bar|soft/i', $name)) {
                $categoryImagesByType['drinks'][] = $img;
            }
        }
    } catch (Exception $e) {
        // ignore
    }
}
$typeOrder = ['starters', 'mains', 'desserts', 'drinks'];

// Fake restaurant for preview: no logo image so templates show text "Logo" instead
$restaurant = [
    'id' => 0,
    'name' => 'Your Restaurant',
    'slug' => 'template-preview',
    'logo' => null,
    'description' => 'Template preview – replace with your own name, logo, and menu.',
    'template_id' => $templateId,
    'header_menu_items' => null,
    'address' => '123 Sample Street, City Centre',
    'phone' => '+1 (555) 123-4567',
    'email' => 'hello@yourrestaurant.com',
    'hero_image' => null,
    'hero_image_url' => $templateCoverUrl,
    'footer_content' => "At Your Restaurant, we believe in great food and warm service. This is a template preview – your real content will replace this. Visit us for a memorable experience.",
    'google_rating' => 4.8,
    'rating_source' => 'Google',
    'map_latitude' => null,
    'map_longitude' => null,
    'opening_hours' => "Mon–Fri: 11:00 – 22:00\nSat–Sun: 10:00 – 23:00",
    'instagram_url' => 'https://instagram.com',
    'facebook_url' => 'https://facebook.com',
    'twitter_url' => 'https://twitter.com',
    'whatsapp_link' => 'https://wa.me/15551234567',
];

// Sample categories and menu items (realistic Naira pricing: N1,000 – N50,000 range)
$sampleCategories = [
    ['name' => 'Starters', 'slug' => 'starters', 'items' => [
        ['name' => 'Bruschetta', 'price' => 2500, 'description' => 'Toasted bread with tomato, basil & olive oil'],
        ['name' => 'Caesar Salad', 'price' => 3200, 'description' => 'Crisp romaine, parmesan, croutons'],
        ['name' => 'Soup of the Day', 'price' => 1800, 'description' => 'Ask your server for today’s selection'],
    ]],
    ['name' => 'Mains', 'slug' => 'mains', 'items' => [
        ['name' => 'Grilled Salmon', 'price' => 18500, 'description' => 'With seasonal vegetables and herb butter'],
        ['name' => 'Beef Burger', 'price' => 9500, 'description' => 'Angus beef, lettuce, tomato, house sauce'],
        ['name' => 'Vegetable Pasta', 'price' => 7200, 'description' => 'Fresh pasta with garden vegetables'],
    ]],
    ['name' => 'Desserts', 'slug' => 'desserts', 'items' => [
        ['name' => 'Chocolate Cake', 'price' => 4500, 'description' => 'Rich chocolate with cream'],
        ['name' => 'Ice Cream', 'price' => 2800, 'description' => 'Vanilla, strawberry, or chocolate'],
    ]],
    ['name' => 'Drinks', 'slug' => 'drinks', 'items' => [
        ['name' => 'Fresh Lemonade', 'price' => 1500, 'description' => 'House-made lemonade'],
        ['name' => 'Coffee', 'price' => 1200, 'description' => 'Espresso, americano, or cappuccino'],
    ]],
];

$categories = [];
$catId = 1;
$itemId = 1;
$catIndex = 0;
foreach ($sampleCategories as $cat) {
    $menuItems = [];
    foreach ($cat['items'] as $item) {
        $img = null;
        if (!empty($sampleImages)) {
            $img = $sampleImages[($itemId - 1) % count($sampleImages)];
        }
        $menuItems[] = [
            'id' => $itemId++,
            'name' => $item['name'],
            'price' => $item['price'],
            'description' => $item['description'],
            'image' => $img,
            'display_order' => count($menuItems) + 1,
            'is_available' => 1,
        ];
    }
    $catImage = null;
    $key = $typeOrder[$catIndex] ?? 'starters';
    $bucket = $categoryImagesByType[$key] ?? [];
    if (!empty($bucket)) {
        $catImage = $bucket[array_rand($bucket)];
    } else {
        $fallback = array_merge(
            $categoryImagesByType['starters'],
            $categoryImagesByType['mains'],
            $categoryImagesByType['desserts'],
            ($key === 'drinks' ? $categoryImagesByType['drinks'] : [])
        );
        if (!empty($fallback)) {
            $catImage = $fallback[array_rand($fallback)];
        }
    }
    $categories[] = [
        'id' => $catId++,
        'name' => $cat['name'],
        'slug' => $cat['slug'],
        'image' => $catImage,
        'menu_items' => $menuItems,
        'is_active' => 1,
        'display_order' => count($categories) + 1,
    ];
    $catIndex++;
}

$customization = getTemplateDefaults($templateId);
$headerMenuItems = [];

ob_start();
$templateLoaded = loadTemplate($restaurant, $categories, $customization, $headerMenuItems, true);

if (!$templateLoaded) {
    ob_end_clean();
    http_response_code(500);
    die('Template preview unavailable.');
}

$previewHtml = ob_get_clean();

// Floating viewport toggle: changes viewport META so the template's real responsive CSS applies.
// Widget visibility is based on INITIAL window size only (JS), so it stays clickable when simulating mobile.
$viewportWidget = <<<'WIDGET'
<style id="preview-viewport-widget-styles">
.preview-viewport-widget { position: fixed; bottom: 20px; right: 20px; z-index: 99999; display: flex; flex-direction: row; gap: 6px; align-items: center; background: #fff; padding: 8px 10px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,.15); border: 1px solid #e5e7eb; }
.preview-viewport-widget button { display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 40px; padding: 0; border: none; border-radius: 8px; background: #f3f4f6; color: #374151; cursor: pointer; transition: background .2s, color .2s; }
.preview-viewport-widget button:hover { background: #e5e7eb; color: #111; }
.preview-viewport-widget button.active { background: #1e3a5f; color: #fff; }
.preview-viewport-widget button svg { width: 22px; height: 22px; pointer-events: none; }
</style>
<div class="preview-viewport-widget" id="previewViewportWidget" aria-label="Toggle preview viewport size">
  <button type="button" id="previewViewportDesktop" title="Desktop view" aria-pressed="true"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg></button>
  <button type="button" id="previewViewportTablet" title="Tablet view"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg></button>
  <button type="button" id="previewViewportMobile" title="Mobile view"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg></button>
</div>
<script id="preview-viewport-widget-script">
(function() {
  var widget = document.getElementById('previewViewportWidget');
  if (!widget) return;
  var realWidth = window.innerWidth;
  if (realWidth <= 767) {
    widget.style.display = 'none';
    return;
  }
  widget.style.display = 'flex';
  var key = 'previewViewport';
  var desktopBtn = document.getElementById('previewViewportDesktop');
  var tabletBtn = document.getElementById('previewViewportTablet');
  var mobileBtn = document.getElementById('previewViewportMobile');
  function getViewportMeta() {
    var meta = document.querySelector('meta[name="viewport"]');
    if (!meta) {
      meta = document.createElement('meta');
      meta.name = 'viewport';
      document.head.appendChild(meta);
    }
    return meta;
  }
  function setViewport(mode) {
    var meta = getViewportMeta();
    if (mode === 'desktop') {
      meta.content = 'width=device-width, initial-scale=1';
    } else if (mode === 'tablet') {
      meta.content = 'width=768';
    } else {
      meta.content = 'width=375';
    }
    if (desktopBtn) { desktopBtn.setAttribute('aria-pressed', mode === 'desktop' ? 'true' : 'false'); desktopBtn.classList.toggle('active', mode === 'desktop'); }
    if (tabletBtn) { tabletBtn.setAttribute('aria-pressed', mode === 'tablet' ? 'true' : 'false'); tabletBtn.classList.toggle('active', mode === 'tablet'); }
    if (mobileBtn) { mobileBtn.setAttribute('aria-pressed', mode === 'mobile' ? 'true' : 'false'); mobileBtn.classList.toggle('active', mode === 'mobile'); }
    try { localStorage.setItem(key, mode); } catch (e) {}
  }
  var saved = null;
  try { saved = localStorage.getItem(key); } catch (e) {}
  if (realWidth >= 1024) {
    setViewport(saved === 'tablet' || saved === 'mobile' ? saved : 'desktop');
  } else {
    setViewport(saved === 'mobile' ? 'mobile' : 'tablet');
  }
  if (desktopBtn) desktopBtn.addEventListener('click', function() { setViewport('desktop'); });
  if (tabletBtn) tabletBtn.addEventListener('click', function() { setViewport('tablet'); });
  if (mobileBtn) mobileBtn.addEventListener('click', function() { setViewport('mobile'); });
})();
</script>
WIDGET;

// Inject widget before </body> so it runs on every template preview
if (stripos($previewHtml, '</body>') !== false) {
    $previewHtml = str_ireplace('</body>', $viewportWidget . "\n</body>", $previewHtml);
} else {
    $previewHtml .= $viewportWidget;
}

echo $previewHtml;
