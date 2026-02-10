<?php
/**
 * API Endpoint: Get All Active Restaurants
 * Returns JSON array of active restaurants for public listing
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $restaurants = getAllActiveRestaurants();
    
    // Format restaurants data for API response
    $formattedRestaurants = array_map(function($restaurant) {
        return [
            'id' => intval($restaurant['id']),
            'name' => $restaurant['name'],
            'slug' => $restaurant['slug'],
            'logo' => $restaurant['logo'] ? UPLOAD_URL . '/logos/' . $restaurant['logo'] : null,
            'description' => $restaurant['description'],
            'phone' => $restaurant['phone'],
            'address' => $restaurant['address'],
            'email' => $restaurant['email'],
        ];
    }, $restaurants);
    
    jsonResponse(true, 'Restaurants retrieved successfully', $formattedRestaurants);
} catch (Exception $e) {
    error_log("API Error (restaurants.php): " . $e->getMessage());
    jsonResponse(false, 'Failed to retrieve restaurants', null);
}

