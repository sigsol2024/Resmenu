<?php
/**
 * Template Loader Utility
 * Handles loading and managing restaurant menu templates
 */

require_once __DIR__ . '/functions.php';

/**
 * Check if template supports ordering (Add to bag / cart)
 * @param int $templateId
 * @return bool
 */
function templateSupportsOrdering($templateId) {
    return in_array(intval($templateId), [2, 3, 4]);
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
    
    $templatePath = __DIR__ . "/../templates/template{$templateId}/index.php";
    
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
                $templatePath = __DIR__ . "/../templates/template{$templateId}/index.php";
                
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
 * Load template with restaurant data
 * @param array $restaurant Restaurant data
 * @param array $categories Categories array
 * @param array $customization Customization settings
 * @param array $headerMenuItems Header menu items
 * @return bool Success
 */
function loadTemplate($restaurant, $categories, $customization, $headerMenuItems = []) {
    $templateId = $restaurant['template_id'] ?? 1;
    $templatePath = getTemplatePath($templateId);
    
    if (!$templatePath) {
        error_log("No template found for template_id: {$templateId}");
        return false;
    }
    
    // Make variables available to template
    $supportsOrdering = templateSupportsOrdering($templateId);
    extract([
        'restaurant' => $restaurant,
        'categories' => $categories,
        'customization' => $customization,
        'headerMenuItems' => $headerMenuItems,
        'supportsOrdering' => $supportsOrdering
    ], EXTR_SKIP);
    
    // Include the template
    include $templatePath;
    
    return true;
}

