<?php
/**
 * Template Loader Utility
 * Handles loading and managing restaurant menu templates
 */

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/subscription.php';
require_once __DIR__ . '/category-icons.php';

/**
 * Check if template supports ordering (Add to bag / cart)
 * @param int $templateId
 * @return bool
 */
function templateSupportsOrdering($templateId) {
    return true;
}

/**
 * Resolve template directory name from id (and optional slug from DB)
 * @param int $templateId
 * @return string Directory name under templates/ (e.g. template1 or the_prime_cut)
 */
function getTemplateDirName($templateId) {
    $templateId = intval($templateId);
    $pdo = getDBConnection();
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("SELECT slug FROM templates WHERE id = ?");
            $stmt->execute([$templateId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row && !empty($row['slug'])) {
                return $row['slug'];
            }
        } catch (PDOException $e) {
            // ignore
        }
    }
    return 'template' . $templateId;
}

/**
 * Get template path for a given template ID
 * @param int $templateId
 * @return string|null
 */
function getTemplatePath($templateId) {
    $templateId = intval($templateId);
    if ($templateId < 1) {
        $templateId = 1; // Default to template 1
    }
    
    $dirName = getTemplateDirName($templateId);
    $templatePath = __DIR__ . "/../templates/{$dirName}/index.php";
    
    if (file_exists($templatePath)) {
        return $templatePath;
    }
    
    // Fallback to template 1 if requested template doesn't exist
    if ($templateId !== 1) {
        $fallbackPath = __DIR__ . "/../templates/template1/index.php";
        if (file_exists($fallbackPath)) {
            error_log("Template {$templateId} not found, falling back to template 1");
            return $fallbackPath;
        }
    }
    
    return null;
}

/**
 * Get available templates
 * @return array
 */
function getAvailableTemplates() {
    $templates = [];
    $templatesDir = __DIR__ . '/../templates';
    
    if (!is_dir($templatesDir)) {
        return $templates;
    }
    
    $pdo = getDBConnection();
    if ($pdo) {
        try {
            $stmt = $pdo->query("SELECT * FROM templates WHERE is_active = 1 ORDER BY id ASC");
            $dbTemplates = $stmt->fetchAll();
            
            foreach ($dbTemplates as $dbTemplate) {
                $templateId = $dbTemplate['id'];
                $dirName = !empty($dbTemplate['slug']) ? $dbTemplate['slug'] : ('template' . $templateId);
                $templatePath = __DIR__ . "/../templates/{$dirName}/index.php";
                
                if (file_exists($templatePath)) {
                    $templates[] = [
                        'id' => $templateId,
                        'name' => $dbTemplate['name'],
                        'description' => $dbTemplate['description'],
                        'preview_image' => $dbTemplate['preview_image'],
                        'listing_image' => $dbTemplate['listing_image'] ?? null,
                        'path' => $templatePath
                    ];
                }
            }
        } catch (PDOException $e) {
            error_log("Error fetching templates: " . $e->getMessage());
        }
    }
    
    // Fallback: scan templates directory if database table doesn't exist
    if (empty($templates)) {
        $dirs = glob($templatesDir . '/template*', GLOB_ONLYDIR);
        foreach ($dirs as $dir) {
            $templateId = basename($dir);
            if (preg_match('/template(\d+)/', $templateId, $matches)) {
                $id = intval($matches[1]);
                $templatePath = $dir . '/index.php';
                if (file_exists($templatePath)) {
                    $templates[] = [
                        'id' => $id,
                        'name' => 'Template ' . $id,
                        'description' => 'Template ' . $id,
                        'preview_image' => null,
                        'path' => $templatePath
                    ];
                }
            }
        }
    }
    
    return $templates;
}

/**
 * Get templates visible and usable for a restaurant (plan + private assignment).
 * Returns templates the restaurant can see; can_use indicates if they can select/use it (plan in template_plans).
 * If they see only via private assignment and can_use is false, they must upgrade to use.
 *
 * @param int $restaurantId
 * @return array List of template records with id, name, description, preview_image, listing_image, path, can_use, can_see
 */
function getTemplatesAvailableForRestaurant($restaurantId) {
    $restaurantId = (int) $restaurantId;
    $templatesDir = __DIR__ . '/../templates';
    if (!is_dir($templatesDir)) {
        return [];
    }

    $subscription = getRestaurantSubscription($restaurantId);
    $planId = $subscription ? (int) ($subscription['plan_id'] ?? 0) : null;

    $pdo = getDBConnection();
    if (!$pdo) {
        return [];
    }

    try {
        $stmt = $pdo->prepare("
            SELECT t.id, t.name, t.slug, t.description, t.preview_image, t.listing_image,
                EXISTS (SELECT 1 FROM template_plans tp WHERE tp.template_id = t.id AND tp.plan_id = ?) AS can_use
            FROM templates t
            WHERE t.is_active = 1
            AND (
                EXISTS (SELECT 1 FROM template_plans tp WHERE tp.template_id = t.id)
                OR (t.is_private = 1 AND EXISTS (SELECT 1 FROM template_restaurants tr WHERE tr.template_id = t.id AND tr.restaurant_id = ?))
            )
            ORDER BY t.id ASC
        ");
        $stmt->execute([$planId ?: 0, $restaurantId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("getTemplatesAvailableForRestaurant: " . $e->getMessage());
        // Fallback when template_plans/template_restaurants don't exist yet
        $all = getAvailableTemplates();
        foreach ($all as &$t) {
            $t['can_use'] = true;
            $t['can_see'] = true;
        }
        return $all;
    }

    $templates = [];
    foreach ($rows as $row) {
        $templateId = (int) $row['id'];
        $dirName = !empty($row['slug']) ? $row['slug'] : ('template' . $templateId);
        $templatePath = __DIR__ . "/../templates/{$dirName}/index.php";
        if (!file_exists($templatePath)) {
            continue;
        }
        $templates[] = [
            'id' => $templateId,
            'name' => $row['name'],
            'description' => $row['description'] ?? null,
            'preview_image' => $row['preview_image'] ?? null,
            'listing_image' => $row['listing_image'] ?? null,
            'path' => $templatePath,
            'can_use' => (bool) ($row['can_use'] ?? false),
            'can_see' => true,
        ];
    }
    return $templates;
}

/**
 * Load template with restaurant data
 * @param array $restaurant Restaurant data
 * @param array $sections Sections array (each with 'categories' and each category with 'menu_items')
 * @param array $customization Customization settings
 * @param array $headerMenuItems Header menu items
 * @param bool $isTemplatePreview If true, templates show text "Logo" instead of logo image
 * @param bool $singleSectionView If true, only one section is shown (section sub-page); templates may show "Full menu" link
 * @param string $fullMenuUrl URL to full menu (e.g. /restaurant/slug); used when $singleSectionView is true
 * @return bool Success
 */
function loadTemplate($restaurant, $sections, $customization, $headerMenuItems = [], $isTemplatePreview = false, $singleSectionView = false, $fullMenuUrl = '') {
    $templateId = $restaurant['template_id'] ?? 1;
    $templatePath = getTemplatePath($templateId);
    
    if (!$templatePath) {
        error_log("No template found for template_id: {$templateId}");
        return false;
    }
    
    // Make variables available to template
    // Note: Preview mode should show full template capabilities (no subscription/manager gating).
    $supportsOrdering = templateSupportsOrdering($templateId);
    $supportsReservations = true;
    
    if (!$isTemplatePreview) {
        $restaurantId = (int)($restaurant['id'] ?? 0);
        // Manager-level toggles (per restaurant) - default to enabled if column missing/null
        $orderingToggle = array_key_exists('enable_food_ordering', (array)$restaurant)
            ? (int)$restaurant['enable_food_ordering']
            : 1;
        $reservationsToggle = array_key_exists('enable_table_reservations', (array)$restaurant)
            ? (int)$restaurant['enable_table_reservations']
            : 1;
        
        // Feature gating: templates may support ordering/reservations, but only enabled plans AND manager toggles should expose them.
        $supportsOrdering = $supportsOrdering
            && hasFeatureAccess($restaurantId, 'food_ordering')
            && ($orderingToggle === 1);
        $supportsReservations = $supportsReservations
            && hasFeatureAccess($restaurantId, 'table_reservations')
            && ($reservationsToggle === 1);
    }
    // Flat list of categories (by section order) for nav/sidebar links
    $categories = [];
    foreach ($sections as $sec) {
        foreach (isset($sec['categories']) ? $sec['categories'] : [] as $c) {
            $categories[] = $c;
        }
    }
    extract([
        'restaurant' => $restaurant,
        'sections' => $sections,
        'categories' => $categories,
        'customization' => $customization,
        'headerMenuItems' => $headerMenuItems,
        'supportsOrdering' => $supportsOrdering,
        'supportsReservations' => $supportsReservations,
        'isTemplatePreview' => $isTemplatePreview,
        'singleSectionView' => $singleSectionView,
        'fullMenuUrl' => $fullMenuUrl
    ], EXTR_SKIP);
    
    // Include the template
    include $templatePath;
    
    return true;
}

