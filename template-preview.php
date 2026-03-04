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

// Optional: grab existing menu item images from DB for preview
$sampleImages = [];
$pdo = getDBConnection();
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT image FROM menu_items WHERE image IS NOT NULL AND image != '' LIMIT 12");
        while ($row = $stmt->fetch()) {
            $sampleImages[] = $row['image'];
        }
    } catch (Exception $e) {
        // ignore
    }
}

$uploadBaseUrl = (defined('SITE_URL') ? rtrim(SITE_URL, '/') : '') . '/uploads';

// Fake restaurant for preview
$restaurant = [
    'id' => 0,
    'name' => 'Your Restaurant',
    'slug' => 'template-preview',
    'logo' => $siteLogo,
    'description' => 'Template preview – replace with your own name, logo, and menu.',
    'template_id' => $templateId,
    'header_menu_items' => null,
    'address' => null,
    'phone' => null,
    'email' => null,
    'hero_image' => null,
    'footer_content' => null,
    'google_rating' => null,
    'rating_source' => null,
    'map_latitude' => null,
    'map_longitude' => null,
    'whatsapp_link' => null,
];

// Sample categories and menu items
$sampleCategories = [
    ['name' => 'Starters', 'slug' => 'starters', 'items' => [
        ['name' => 'Bruschetta', 'price' => 8.99, 'description' => 'Toasted bread with tomato, basil & olive oil'],
        ['name' => 'Caesar Salad', 'price' => 9.50, 'description' => 'Crisp romaine, parmesan, croutons'],
        ['name' => 'Soup of the Day', 'price' => 6.99, 'description' => 'Ask your server for today’s selection'],
    ]],
    ['name' => 'Mains', 'slug' => 'mains', 'items' => [
        ['name' => 'Grilled Salmon', 'price' => 18.99, 'description' => 'With seasonal vegetables and herb butter'],
        ['name' => 'Beef Burger', 'price' => 14.99, 'description' => 'Angus beef, lettuce, tomato, house sauce'],
        ['name' => 'Vegetable Pasta', 'price' => 13.99, 'description' => 'Fresh pasta with garden vegetables'],
    ]],
    ['name' => 'Desserts', 'slug' => 'desserts', 'items' => [
        ['name' => 'Chocolate Cake', 'price' => 7.99, 'description' => 'Rich chocolate with cream'],
        ['name' => 'Ice Cream', 'price' => 5.50, 'description' => 'Vanilla, strawberry, or chocolate'],
    ]],
    ['name' => 'Drinks', 'slug' => 'drinks', 'items' => [
        ['name' => 'Fresh Lemonade', 'price' => 4.99, 'description' => 'House-made lemonade'],
        ['name' => 'Coffee', 'price' => 3.50, 'description' => 'Espresso, americano, or cappuccino'],
    ]],
];

$categories = [];
$catId = 1;
$itemId = 1;
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
        ];
    }
    $categories[] = [
        'id' => $catId++,
        'name' => $cat['name'],
        'slug' => $cat['slug'],
        'menu_items' => $menuItems,
        'is_active' => 1,
        'display_order' => count($categories) + 1,
    ];
}

$customization = getTemplateDefaults($templateId);
$headerMenuItems = [];

$templateLoaded = loadTemplate($restaurant, $categories, $customization, $headerMenuItems);

if (!$templateLoaded) {
    http_response_code(500);
    die('Template preview unavailable.');
}
