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
// Category images from any restaurant (for template 1-style category sections)
$categoryImages = [];
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT image FROM menu_items WHERE image IS NOT NULL AND image != '' LIMIT 12");
        while ($row = $stmt->fetch()) {
            $sampleImages[] = $row['image'];
        }
        $stmt = $pdo->query("SELECT image FROM categories WHERE image IS NOT NULL AND image != '' ORDER BY RAND() LIMIT 8");
        while ($row = $stmt->fetch()) {
            $categoryImages[] = $row['image'];
        }
    } catch (Exception $e) {
        // ignore
    }
}

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
    if (!empty($categoryImages)) {
        $catImage = $categoryImages[$catIndex % count($categoryImages)];
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
