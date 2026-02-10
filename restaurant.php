<?php
/**
 * Template Loader
 * Loads the appropriate template for a restaurant based on template_id
 */

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/template-loader.php';
require_once __DIR__ . '/config/config.php';

// Get restaurant slug from URL
$restaurantSlug = $_GET['slug'] ?? '';

// Allow template override for preview purposes (e.g., template showcase)
$templateOverride = isset($_GET['template']) ? intval($_GET['template']) : null;

// Get restaurant data
$restaurant = getRestaurantBySlug($restaurantSlug);

// Override template_id if template parameter is provided (for previews)
if ($restaurant && $templateOverride !== null && $templateOverride > 0) {
    $restaurant['template_id'] = $templateOverride;
}

if (!$restaurant) {
    http_response_code(404);
    die('Restaurant not found.');
}

// Parse header menu items from JSON
$headerMenuItems = [];
if (!empty($restaurant['header_menu_items'])) {
    $decoded = json_decode($restaurant['header_menu_items'], true);
    if (is_array($decoded)) {
        $headerMenuItems = $decoded;
    }
}

// Get customization settings
$customization = getCustomizationSettings($restaurant['id']);

// Get categories with menu items
$categories = getCategoriesWithMenuItems($restaurant['id']);

// Load template
$templateLoaded = loadTemplate($restaurant, $categories, $customization, $headerMenuItems);

if (!$templateLoaded) {
    http_response_code(500);
    die('Error loading template. Please contact the administrator.');
}

