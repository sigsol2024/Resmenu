<?php
/**
 * API Endpoint: Get Menu Data
 * Returns JSON data for a restaurant's menu
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

$restaurantSlug = $_GET['restaurant'] ?? null;

if (!$restaurantSlug) {
    jsonResponse(false, 'Restaurant slug required', null);
}

$restaurant = getRestaurantBySlug($restaurantSlug);

if (!$restaurant) {
    jsonResponse(false, 'Restaurant not found', null);
}

$categories = getCategories($restaurant['id']);
$menuData = [];

foreach ($categories as $category) {
    $items = getMenuItems($category['id']);
    $menuData[] = [
        'category' => [
            'id' => $category['id'],
            'name' => $category['name'],
            'slug' => $category['slug'],
            'description' => $category['description'],
            'image' => $category['image'] ? UPLOAD_URL . '/categories/' . $category['image'] : null,
        ],
        'items' => array_map(function($item) {
            return [
                'id' => $item['id'],
                'name' => $item['name'],
                'slug' => $item['slug'],
                'description' => $item['description'],
                'price' => floatval($item['price']),
                'image' => $item['image'] ? UPLOAD_URL . '/menu-items/' . $item['image'] : null,
            ];
        }, $items)
    ];
}

jsonResponse(true, 'Menu data retrieved successfully', [
    'restaurant' => [
        'id' => $restaurant['id'],
        'name' => $restaurant['name'],
        'slug' => $restaurant['slug'],
        'description' => $restaurant['description'],
        'logo' => $restaurant['logo'] ? UPLOAD_URL . '/logos/' . $restaurant['logo'] : null,
    ],
    'menu' => $menuData
]);

