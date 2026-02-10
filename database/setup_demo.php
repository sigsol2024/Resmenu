<?php
/**
 * Setup Demo Restaurant and Admin Account
 * Creates super admin, demo restaurant "skyhuz", and populates with menu data from index.html.backup
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Check if running from CLI or browser
$isCLI = php_sapi_name() === 'cli';

if (!$isCLI) {
    echo "<!DOCTYPE html><html><head><title>Setup Demo</title><style>body{font-family:monospace;padding:20px;background:#f5f5f5;} .success{color:green;} .error{color:red;} .info{color:blue;} .warning{color:orange;}</style></head><body>";
    echo "<h1>Demo Restaurant Setup</h1>";
}

function logMessage($message, $type = 'info') {
    global $isCLI;
    $prefix = $isCLI ? '' : "<div class='$type'>";
    $suffix = $isCLI ? "\n" : "</div>";
    echo $prefix . $message . $suffix;
    if ($isCLI) {
        flush();
    }
}

/**
 * Extract Menu Data from index.html.backup
 */
function extractMenuData($htmlFile) {
    if (!file_exists($htmlFile)) {
        return ['error' => 'HTML file not found'];
    }
    
    $htmlContent = file_get_contents($htmlFile);
    
    // Initialize result arrays
    $categories = [];
    $menuItems = [];
    
    // Find category sections by ID pattern
    preg_match_all('/id="([a-z-]+(?:\s+)?)"[^>]*data-framer-name="Category"/i', $htmlContent, $categoryIds);
    
    // Extract category images (first image in each category section)
    $categoryImagePattern = '/id="([^"]+)"[^>]*data-framer-name="Category"[^>]*>.*?src="assets\/images\/([^"?]+)/is';
    preg_match_all($categoryImagePattern, $htmlContent, $categoryImageMatches);
    
    // Build category map
    $categoryMap = [];
    $categoryOrder = 0;
    
    // Known categories from the HTML structure
    $knownCategories = [
        'starters' => 'Starters',
        'breakfast ' => 'Breakfast & Brunch',
        'rice-dishes' => 'Rice Dishes',
        'rice-dishes-1' => 'Rice Dishes',
        'rice-dishes-2' => 'Rice Dishes',
        'proteins-grills' => 'Proteins & Grills',
        'noodles' => 'Noodles',
        'pasta ' => 'Pasta',
        'sandwiches' => 'Sandwiches',
        'burgers' => 'Burgers',
        'sides' => 'Sides',
        'sides\'' => 'Sides',
        'prawns' => 'Prawns',
        'salads' => 'Salads',
        'coffee' => 'Coffee',
        'mocktails ' => 'Mocktails & Cocktails',
        'milkshakes' => 'Milkshakes & Smoothies',
        'beverages' => 'Beverages'
    ];
    
    // Extract unique categories
    foreach ($categoryIds[1] as $index => $categoryId) {
        $categoryId = trim($categoryId);
        if (isset($knownCategories[$categoryId]) && !isset($categoryMap[$categoryId])) {
            $categoryName = $knownCategories[$categoryId];
            $categorySlug = generateSlug($categoryName);
            
            // Find category image
            $categoryImage = null;
            if (isset($categoryImageMatches[2][$index])) {
                $imgPath = $categoryImageMatches[2][$index];
                // Remove query parameters
                $imgPath = preg_replace('/\?.*$/', '', $imgPath);
                $categoryImage = $imgPath;
            }
            
            $categoryMap[$categoryId] = [
                'id' => $categoryId,
                'name' => $categoryName,
                'slug' => $categorySlug,
                'image' => $categoryImage,
                'description' => null,
                'order' => $categoryOrder++
            ];
        }
    }
    
    // Extract menu items by processing each category section
    foreach ($categoryMap as $categoryId => $categoryData) {
        // Find the category section - look for the div with the category ID
        $categorySectionPattern = '/id="' . preg_quote($categoryId, '/') . '"[^>]*>(.*?)(?=id="[^"]+"[^>]*data-framer-name="Category"|<\/div>\s*<\/div>\s*<div[^>]*data-framer-name="Category"|$)/is';
        preg_match($categorySectionPattern, $htmlContent, $sectionMatch);
        
        if (isset($sectionMatch[1])) {
            $sectionContent = $sectionMatch[1];
            
            // Find all menu item containers - look for containers with images and prices
            // Pattern: container -> image -> post -> price + name + description
            $itemContainerPattern = '/<div[^>]*data-framer-name="With Image"[^>]*>.*?<img[^>]*src="assets\/images\/([^"?]+)[^"]*"[^>]*>.*?<div[^>]*data-framer-name="Post"[^>]*>(.*?)<\/div>/is';
            preg_match_all($itemContainerPattern, $sectionContent, $itemContainers, PREG_SET_OFFSET_CAPTURE);
            
            $sectionItemOrder = 0;
            foreach ($itemContainers[0] as $index => $containerMatch) {
                $itemImage = null;
                $itemPrice = null;
                $itemName = null;
                $itemDescription = null;
                
                // Extract image
                if (isset($itemContainers[1][$index][0])) {
                    $imgPath = $itemContainers[1][$index][0];
                    $imgPath = preg_replace('/\?.*$/', '', $imgPath);
                    $itemImage = $imgPath;
                }
                
                // Extract price, name, and description from the post content
                $postContent = $itemContainers[2][$index][0] ?? '';
                
                // Extract price: <strong>N12,000</strong> or <strong> N16,000</strong>
                if (preg_match('/<strong[^>]*>\s*(N|₦)\s*([0-9,]+)[^<]*<\/strong>/i', $postContent, $priceMatch)) {
                    $itemPrice = str_replace(',', '', $priceMatch[2]);
                    $itemPrice = floatval($itemPrice);
                }
                
                // Extract name: second <strong> tag after price
                if (preg_match_all('/<strong[^>]*>([^<]+)<\/strong>/i', $postContent, $nameMatches)) {
                    // Skip the first match (price), get the second one (name)
                    if (isset($nameMatches[1][1])) {
                        $itemName = trim($nameMatches[1][1]);
                    }
                }
                
                // Extract description: <p> tag with description class or any <p> after name
                if (preg_match('/<p[^>]*class="[^"]*Description[^>]*>([^<]+)<\/p>/i', $postContent, $descMatch)) {
                    $itemDescription = trim($descMatch[1]);
                } elseif (preg_match('/<p[^>]*>([^<]+)<\/p>/i', $postContent, $descMatch)) {
                    $itemDescription = trim($descMatch[1]);
                }
                
                // Clean up description (remove extra whitespace/newlines)
                if ($itemDescription) {
                    $itemDescription = preg_replace('/\s+/', ' ', $itemDescription);
                    $itemDescription = trim($itemDescription);
                }
                
                // Only add if we have at least name and price
                if (!empty($itemName) && $itemPrice > 0) {
                    $menuItems[] = [
                        'category_id' => $categoryId,
                        'category_slug' => $categoryData['slug'],
                        'name' => $itemName,
                        'description' => $itemDescription,
                        'price' => $itemPrice,
                        'image' => $itemImage,
                        'order' => $sectionItemOrder++
                    ];
                }
            }
        }
    }
    
    return [
        'categories' => array_values($categoryMap),
        'menu_items' => $menuItems
    ];
}

$pdo = getDBConnection();

if (!$pdo) {
    logMessage("Database connection failed. Please check your database configuration.", 'error');
    exit(1);
}

try {
    $pdo->beginTransaction();
    
    logMessage("Starting setup process...", 'info');
    
    // Step 1: Create Super Admin
    logMessage("Step 1: Creating super admin account...", 'info');
    
    $adminEmail = 'sigsol2024@gmail.com';
    $adminPassword = 'Secretpass0721//';
    $adminUsername = 'sigsol2024';
    
    // Check if admin already exists
    $stmt = $pdo->prepare("SELECT id FROM admins WHERE email = ? OR username = ?");
    $stmt->execute([$adminEmail, $adminUsername]);
    $existingAdmin = $stmt->fetch();
    
    if ($existingAdmin) {
        logMessage("Admin account already exists. Skipping creation.", 'warning');
        $adminId = $existingAdmin['id'];
    } else {
        $passwordHash = password_hash($adminPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO admins (username, email, password_hash) VALUES (?, ?, ?)");
        $stmt->execute([$adminUsername, $adminEmail, $passwordHash]);
        $adminId = $pdo->lastInsertId();
        logMessage("Super admin created successfully. ID: $adminId", 'success');
    }
    
    // Step 2: Create Demo Restaurant "skyhuz"
    logMessage("Step 2: Creating demo restaurant 'skyhuz'...", 'info');
    
    $restaurantName = 'Skyhuz';
    $restaurantSlug = generateSlug($restaurantName);
    
    // Demo restaurant data with complete information
    $managerEmail = 'manager@skyhuz.com';
    $managerPassword = 'Skyhuz2024!';
    $managerUsername = 'skyhuz_manager';
    
    // Header menu items (JSON format)
    $headerMenuItems = json_encode([
        ['label' => 'Home', 'url' => '/'],
        ['label' => 'Menu', 'url' => '#menu'],
        ['label' => 'About', 'url' => '#about'],
        ['label' => 'Contact', 'url' => '#contact']
    ]);
    
    // Footer content (HTML)
    $footerContent = '<p>Welcome to Skyhuz Restaurant, where culinary excellence meets warm hospitality. We serve fresh, locally-sourced ingredients prepared with passion and creativity.</p><p>Open daily from 9:00 AM to 10:00 PM. Reservations recommended for weekends.</p>';
    
    // Check if restaurant already exists
    $stmt = $pdo->prepare("SELECT id FROM restaurants WHERE slug = ?");
    $stmt->execute([$restaurantSlug]);
    $existingRestaurant = $stmt->fetch();
    
    if ($existingRestaurant) {
        logMessage("Restaurant 'skyhuz' already exists. Updating with complete information...", 'warning');
        $restaurantId = $existingRestaurant['id'];
        
        // Update restaurant with all demo information
        $stmt = $pdo->prepare("UPDATE restaurants SET 
            name = ?, 
            description = ?, 
            phone = ?, 
            email = ?, 
            address = ?, 
            website = ?,
            whatsapp_link = ?, 
            instagram_url = ?, 
            facebook_url = ?, 
            twitter_url = ?,
            map_latitude = ?,
            map_longitude = ?,
            header_menu_items = ?,
            footer_content = ?,
            manager_email = ?,
            template_id = 1, 
            is_active = 1 
            WHERE id = ?");
        $stmt->execute([
            $restaurantName,
            'A modern restaurant offering delicious meals and great service. Experience fine dining with a touch of local flavor.',
            '+234 123 456 7890',
            'info@skyhuz.com',
            '123 Main Street, Victoria Island, Lagos, Nigeria',
            'https://www.skyhuz.com',
            'https://wa.me/2341234567890',
            'https://instagram.com/skyhuz',
            'https://facebook.com/skyhuz',
            'https://twitter.com/skyhuz',
            6.4281, // Lagos latitude
            3.4219, // Lagos longitude
            $headerMenuItems,
            $footerContent,
            $managerEmail,
            $restaurantId
        ]);
        logMessage("Restaurant 'skyhuz' updated successfully.", 'success');
    } else {
        $restaurantData = [
            'name' => $restaurantName,
            'slug' => $restaurantSlug,
            'description' => 'A modern restaurant offering delicious meals and great service. Experience fine dining with a touch of local flavor.',
            'phone' => '+234 123 456 7890',
            'email' => 'info@skyhuz.com',
            'address' => '123 Main Street, Victoria Island, Lagos, Nigeria',
            'website' => 'https://www.skyhuz.com',
            'whatsapp_link' => 'https://wa.me/2341234567890',
            'instagram_url' => 'https://instagram.com/skyhuz',
            'facebook_url' => 'https://facebook.com/skyhuz',
            'twitter_url' => 'https://twitter.com/skyhuz',
            'map_latitude' => 6.4281, // Lagos latitude
            'map_longitude' => 3.4219, // Lagos longitude
            'header_menu_items' => $headerMenuItems,
            'footer_content' => $footerContent,
            'manager_email' => $managerEmail,
            'template_id' => 1,
            'is_active' => 1
        ];
        
        $stmt = $pdo->prepare("INSERT INTO restaurants (name, slug, description, phone, email, address, website, whatsapp_link, instagram_url, facebook_url, twitter_url, map_latitude, map_longitude, header_menu_items, footer_content, manager_email, template_id, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $restaurantData['name'],
            $restaurantData['slug'],
            $restaurantData['description'],
            $restaurantData['phone'],
            $restaurantData['email'],
            $restaurantData['address'],
            $restaurantData['website'],
            $restaurantData['whatsapp_link'],
            $restaurantData['instagram_url'],
            $restaurantData['facebook_url'],
            $restaurantData['twitter_url'],
            $restaurantData['map_latitude'],
            $restaurantData['map_longitude'],
            $restaurantData['header_menu_items'],
            $restaurantData['footer_content'],
            $restaurantData['manager_email'],
            $restaurantData['template_id'],
            $restaurantData['is_active']
        ]);
        $restaurantId = $pdo->lastInsertId();
        logMessage("Restaurant 'skyhuz' created successfully. ID: $restaurantId", 'success');
    }
    
    // Step 2.5: Create Demo Manager Account
    logMessage("Step 2.5: Creating demo manager account...", 'info');
    
    // Check if manager already exists
    $stmt = $pdo->prepare("SELECT id FROM managers WHERE email = ? OR username = ?");
    $stmt->execute([$managerEmail, $managerUsername]);
    $existingManager = $stmt->fetch();
    
    if ($existingManager) {
        logMessage("Manager account already exists. Updating...", 'warning');
        $managerId = $existingManager['id'];
        // Update manager to ensure it's linked to the restaurant
        $passwordHash = password_hash($managerPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE managers SET restaurant_id = ?, password_hash = ? WHERE id = ?");
        $stmt->execute([$restaurantId, $passwordHash, $managerId]);
        logMessage("Manager account updated successfully.", 'success');
    } else {
        $passwordHash = password_hash($managerPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO managers (username, email, password_hash, restaurant_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$managerUsername, $managerEmail, $passwordHash, $restaurantId]);
        $managerId = $pdo->lastInsertId();
        logMessage("Manager account created successfully. ID: $managerId", 'success');
        logMessage("Manager Login: Email: $managerEmail, Username: $managerUsername, Password: $managerPassword", 'info');
    }
    
    // Step 3: Extract menu data from HTML backup
    logMessage("Step 3: Extracting menu data from index.html.backup...", 'info');
    
    $htmlFile = __DIR__ . '/../index.html.backup';
    $menuData = extractMenuData($htmlFile);
    
    if (isset($menuData['error'])) {
        throw new Exception("Failed to extract menu data: " . $menuData['error']);
    }
    
    $categories = $menuData['categories'] ?? [];
    $menuItems = $menuData['menu_items'] ?? [];
    
    logMessage("Found " . count($categories) . " categories and " . count($menuItems) . " menu items.", 'info');
    
    // Step 4: Create categories
    logMessage("Step 4: Creating categories...", 'info');
    
    $categoryMap = []; // Maps category slug to category ID
    
    // First, delete existing categories for this restaurant (if re-running)
    $stmt = $pdo->prepare("DELETE FROM categories WHERE restaurant_id = ?");
    $stmt->execute([$restaurantId]);
    
    foreach ($categories as $category) {
        $categorySlug = $category['slug'];
        
        // Check if category already exists
        $stmt = $pdo->prepare("SELECT id FROM categories WHERE restaurant_id = ? AND slug = ?");
        $stmt->execute([$restaurantId, $categorySlug]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            $categoryMap[$categorySlug] = $existing['id'];
            logMessage("Category '{$category['name']}' already exists.", 'info');
        } else {
            $stmt = $pdo->prepare("INSERT INTO categories (restaurant_id, name, slug, image, description, display_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $restaurantId,
                $category['name'],
                $categorySlug,
                $category['image'],
                $category['description'],
                $category['order'],
                1
            ]);
            $categoryMap[$categorySlug] = $pdo->lastInsertId();
            logMessage("Created category: {$category['name']}", 'success');
        }
    }
    
    logMessage("Categories created/verified. Total: " . count($categoryMap), 'success');
    
    // Step 5: Copy images
    logMessage("Step 5: Copying images...", 'info');
    
    $assetsDir = __DIR__ . '/../assets/images';
    $uploadsDir = __DIR__ . '/../uploads';
    $categoriesDir = $uploadsDir . '/categories';
    $menuItemsDir = $uploadsDir . '/menu-items';
    $logosDir = $uploadsDir . '/logos';
    
    // Create upload directories if they don't exist
    if (!is_dir($categoriesDir)) {
        mkdir($categoriesDir, 0755, true);
    }
    if (!is_dir($menuItemsDir)) {
        mkdir($menuItemsDir, 0755, true);
    }
    if (!is_dir($logosDir)) {
        mkdir($logosDir, 0755, true);
    }
    
    $imagesCopied = 0;
    
    // Copy category images
    foreach ($categories as $category) {
        if (!empty($category['image'])) {
            $sourceFile = $assetsDir . '/' . $category['image'];
            $destFile = $categoriesDir . '/' . basename($category['image']);
            
            if (file_exists($sourceFile)) {
                if (copy($sourceFile, $destFile)) {
                    $imagesCopied++;
                    // Update category with just the filename
                    $stmt = $pdo->prepare("UPDATE categories SET image = ? WHERE id = ?");
                    $stmt->execute([basename($category['image']), $categoryMap[$category['slug']]]);
                }
            }
        }
    }
    
    // Copy menu item images
    foreach ($menuItems as $item) {
        if (!empty($item['image'])) {
            $sourceFile = $assetsDir . '/' . $item['image'];
            $destFile = $menuItemsDir . '/' . basename($item['image']);
            
            if (file_exists($sourceFile)) {
                if (copy($sourceFile, $destFile)) {
                    $imagesCopied++;
                }
            }
        }
    }
    
    logMessage("Copied $imagesCopied images.", 'success');
    
    // Step 6: Create menu items
    logMessage("Step 6: Creating menu items...", 'info');
    
    // Delete existing menu items for this restaurant (if re-running)
    $stmt = $pdo->prepare("DELETE FROM menu_items WHERE restaurant_id = ?");
    $stmt->execute([$restaurantId]);
    
    $itemsCreated = 0;
    $itemOrderMap = []; // Track order within each category
    
    foreach ($menuItems as $item) {
        $categorySlug = $item['category_slug'];
        
        if (!isset($categoryMap[$categorySlug])) {
            logMessage("Skipping item '{$item['name']}' - category '$categorySlug' not found", 'warning');
            continue;
        }
        
        $categoryId = $categoryMap[$categorySlug];
        
        // Track display order within category
        if (!isset($itemOrderMap[$categoryId])) {
            $itemOrderMap[$categoryId] = 0;
        }
        
        $itemSlug = generateSlug($item['name']);
        $itemImage = !empty($item['image']) ? basename($item['image']) : null;
        
        // Check if item already exists
        $stmt = $pdo->prepare("SELECT id FROM menu_items WHERE restaurant_id = ? AND category_id = ? AND slug = ?");
        $stmt->execute([$restaurantId, $categoryId, $itemSlug]);
        $existing = $stmt->fetch();
        
        if (!$existing) {
            $stmt = $pdo->prepare("INSERT INTO menu_items (restaurant_id, category_id, name, slug, description, price, image, display_order, is_available) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $restaurantId,
                $categoryId,
                $item['name'],
                $itemSlug,
                $item['description'],
                $item['price'],
                $itemImage,
                $itemOrderMap[$categoryId]++,
                1
            ]);
            $itemsCreated++;
        }
    }
    
    logMessage("Created $itemsCreated menu items.", 'success');
    
    // Step 7: Create default customization settings
    logMessage("Step 7: Creating default customization settings...", 'info');
    
    $stmt = $pdo->prepare("SELECT id FROM customization_settings WHERE restaurant_id = ?");
    $stmt->execute([$restaurantId]);
    $existingSettings = $stmt->fetch();
    
    if (!$existingSettings) {
        $stmt = $pdo->prepare("INSERT INTO customization_settings (restaurant_id) VALUES (?)");
        $stmt->execute([$restaurantId]);
        logMessage("Default customization settings created.", 'success');
    } else {
        logMessage("Customization settings already exist.", 'info');
    }
    
    // Commit transaction
    $pdo->commit();
    
    logMessage("Setup completed successfully!", 'success');
    logMessage("=== Login Credentials ===", 'info');
    logMessage("Super Admin - Email: $adminEmail, Username: $adminUsername, Password: $adminPassword", 'info');
    logMessage("Manager - Email: $managerEmail, Username: $managerUsername, Password: $managerPassword", 'info');
    logMessage("=== Restaurant Information ===", 'info');
    logMessage("Restaurant Name: $restaurantName", 'info');
    logMessage("Restaurant URL: /restaurant/$restaurantSlug", 'info');
    logMessage("Restaurant Email: info@skyhuz.com", 'info');
    logMessage("Restaurant Phone: +234 123 456 7890", 'info');
    
    if (!$isCLI) {
        echo "<p><a href='/admin/login.php'>Go to Admin Login</a></p>";
        echo "<p><a href='/restaurant/$restaurantSlug'>View Restaurant Menu</a></p>";
        echo "</body></html>";
    }
    
} catch (Exception $e) {
    $pdo->rollBack();
    logMessage("Error during setup: " . $e->getMessage(), 'error');
    if (!$isCLI) {
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
    exit(1);
}

