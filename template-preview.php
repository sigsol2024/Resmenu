<?php
/**
 * Template Preview - Renders a template with sample data for showcase.
 * URL: /template1-preview, /template2-preview, etc. (via .htaccess)
 */
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/template-loader.php';
require_once __DIR__ . '/config/config.php';

$templateId = isset($_GET['t']) ? max(1, min(4, (int)$_GET['t'])) : 1;

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

// Fake restaurant for preview: full footer/contact/social so all sections and icons render
$restaurant = [
    'id' => 0,
    'name' => 'Your Restaurant',
    'slug' => 'template-preview',
    'logo' => $siteLogo,
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

$templateLoaded = loadTemplate($restaurant, $categories, $customization, $headerMenuItems);

if (!$templateLoaded) {
    http_response_code(500);
    die('Template preview unavailable.');
}
