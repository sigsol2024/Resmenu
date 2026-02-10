<?php
/**
 * QR Template Helper Functions
 * Handles template config parsing, merging, and caching
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/functions.php';

/**
 * Get template config by template ID
 * @param int $templateId
 * @return array|null
 */
function getTemplateConfig($templateId, $includeInactive = false) {
    $pdo = getDBConnection();
    if (!$pdo) {
        error_log("getTemplateConfig: No database connection for template_id: $templateId");
        return null;
    }
    
    // For admin operations or when explicitly requested, allow inactive templates
    $sql = $includeInactive 
        ? "SELECT id, is_active, config_json FROM qr_templates WHERE id = ?"
        : "SELECT id, is_active, config_json FROM qr_templates WHERE id = ? AND is_active = 1";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$templateId]);
    $result = $stmt->fetch();
    
    if (!$result) {
        error_log("getTemplateConfig: Template not found for template_id: $templateId, includeInactive: " . ($includeInactive ? 'true' : 'false'));
        return null;
    }
    
    if (empty($result['config_json'])) {
        error_log("getTemplateConfig: Template has empty config_json for template_id: $templateId, is_active: " . ($result['is_active'] ?? 'unknown'));
        return null;
    }
    
    // Handle both JSON column type and LONGTEXT string
    $config = is_string($result['config_json']) 
        ? json_decode($result['config_json'], true)
        : $result['config_json'];
    
    if (!$config || !is_array($config)) {
        error_log("getTemplateConfig: Failed to decode config_json for template_id: $templateId. Raw value: " . substr(is_string($result['config_json']) ? $result['config_json'] : json_encode($result['config_json']), 0, 100));
        
        // Try to decode manually if it's a string
        if (is_string($result['config_json'])) {
            $jsonError = json_last_error();
            if ($jsonError !== JSON_ERROR_NONE) {
                error_log("getTemplateConfig: JSON decode error: " . json_last_error_msg() . " for template_id: $templateId");
            }
        }
        
        return null;
    }
    
    return $config;
}

/**
 * Get manager override config for a restaurant
 * @param int $restaurantId
 * @return array|null
 */
function getManagerOverrideConfig($restaurantId) {
    $pdo = getDBConnection();
    if (!$pdo) return null;
    
    $stmt = $pdo->prepare("SELECT override_json FROM restaurant_qr_codes WHERE restaurant_id = ? AND is_active = 1");
    $stmt->execute([$restaurantId]);
    $result = $stmt->fetch();
    
    if (!$result || empty($result['override_json'])) {
        return null;
    }
    
    // Handle both JSON column type and LONGTEXT string
    $override = is_string($result['override_json']) 
        ? json_decode($result['override_json'], true)
        : $result['override_json'];
    
    return $override ?: null;
}

/**
 * Merge template config with manager overrides
 * Only allows overrides for fields permitted by template
 * @param array $templateConfig Template configuration
 * @param array|null $managerOverrides Manager override values
 * @return array Merged configuration
 */
function mergeTemplateWithOverrides($templateConfig, $managerOverrides = null) {
    // Always return template config, even if no overrides
    if (!$templateConfig || !is_array($templateConfig)) {
        error_log("mergeTemplateWithOverrides: Invalid template config provided");
        return null;
    }
    
    if (!$managerOverrides || !is_array($managerOverrides)) {
        return $templateConfig;
    }
    
    $permissions = $templateConfig['allow_manager_override'] ?? [];
    $merged = $templateConfig;
    
    // Merge colors if allowed
    if (!empty($permissions['colors']) && isset($managerOverrides['colors'])) {
        $merged['colors'] = array_merge(
            $templateConfig['colors'] ?? [],
            $managerOverrides['colors']
        );
    }
    
    // Merge text settings if allowed
    if (!empty($permissions['text']) && isset($managerOverrides['text'])) {
        // Text is stored separately in restaurant_qr_codes, but we can merge text_content here
        if (isset($managerOverrides['text_content'])) {
            $merged['text_content'] = $managerOverrides['text_content'];
        }
        if (isset($managerOverrides['text_color'])) {
            $merged['text_color'] = $managerOverrides['text_color'];
        }
        if (isset($managerOverrides['text_size'])) {
            $merged['text_size'] = $managerOverrides['text_size'];
        }
    }
    
    // Merge logo settings if allowed
    if (!empty($permissions['logo']) && isset($managerOverrides['logo'])) {
        $merged['logo'] = array_merge(
            $templateConfig['logo'] ?? [],
            $managerOverrides['logo']
        );
    }
    
    // Pattern, eyes, and frame are never overridable by managers
    // They remain from template config
    
    return $merged;
}

/**
 * Get final QR config for a restaurant (cached or generated)
 * @param int $restaurantId
 * @return array|null
 */
function getFinalQRConfig($restaurantId) {
    // Check if config is already set globally (from api/qr-generate.php)
    if (isset($GLOBALS['qr_final_config']) && is_array($GLOBALS['qr_final_config'])) {
        return $GLOBALS['qr_final_config'];
    }
    
    $pdo = getDBConnection();
    if (!$pdo) return null;
    
    // Check for preview template override (for preview mode)
    $previewTemplateId = $GLOBALS['qr_preview_template_id'] ?? null;
    
    // If preview template is set, use it instead of saved template (even if no restaurant_qr_codes record exists)
    if ($previewTemplateId) {
        // For preview, allow inactive templates
        $templateConfig = getTemplateConfig($previewTemplateId, true);
        if ($templateConfig) {
            // Get manager overrides (if any) - may return null if no restaurant_qr_codes record exists
            $overrideConfig = getManagerOverrideConfig($restaurantId);
            // Merge preview template with overrides
            $mergedConfig = mergeTemplateWithOverrides($templateConfig, $overrideConfig);
            if ($mergedConfig) {
                return $mergedConfig;
            } else {
                error_log("getFinalQRConfig: Failed to merge preview template config for template_id: $previewTemplateId");
            }
        } else {
            error_log("getFinalQRConfig: Preview template not found for template_id: $previewTemplateId, restaurant_id: $restaurantId");
        }
        // If preview mode fails, return null (don't fall through to saved config)
        return null;
    }
    
    // Try to get cached final_config_json
    $stmt = $pdo->prepare("
        SELECT final_config_json, qr_template_id, override_json 
        FROM restaurant_qr_codes 
        WHERE restaurant_id = ? AND is_active = 1
    ");
    $stmt->execute([$restaurantId]);
    $result = $stmt->fetch();
    
    if (!$result) {
        return null;
    }
    
    // If final_config_json exists and is not empty, return it
    if (!empty($result['final_config_json'])) {
        $finalConfig = is_string($result['final_config_json']) 
            ? json_decode($result['final_config_json'], true)
            : $result['final_config_json'];
        
        if ($finalConfig && is_array($finalConfig)) {
            return $finalConfig;
        } else {
            error_log("getFinalQRConfig: final_config_json exists but is invalid for restaurant_id: $restaurantId. Value: " . substr($result['final_config_json'], 0, 100));
            // Continue to regenerate it
        }
    }
    
    // Otherwise, generate it from template + overrides
    $templateId = $result['qr_template_id'] ?? null;
    
    // If no template is selected, return null (no QR code available)
    if (!$templateId) {
        error_log("getFinalQRConfig: No template_id for restaurant_id: $restaurantId");
        return null;
    }
    
    // Try to get template config - allow inactive templates if they were previously selected
    $templateConfig = getTemplateConfig($templateId, true); // Include inactive templates
    
    // If template doesn't exist at all, return null
    if (!$templateConfig) {
        // Check if template exists (even if inactive)
        $stmt = $pdo->prepare("SELECT id, name, is_active, config_json FROM qr_templates WHERE id = ?");
        $stmt->execute([$templateId]);
        $templateInfo = $stmt->fetch();
        
        if (!$templateInfo) {
            error_log("getFinalQRConfig: Template ID $templateId does not exist for restaurant_id: $restaurantId");
            // Template was deleted - clear template_id
            $stmt = $pdo->prepare("UPDATE restaurant_qr_codes SET qr_template_id = NULL, final_config_json = NULL WHERE restaurant_id = ?");
            $stmt->execute([$restaurantId]);
            return null;
        } elseif (empty($templateInfo['config_json'])) {
            error_log("getFinalQRConfig: Template ID $templateId has empty config_json for restaurant_id: $restaurantId");
            return null;
        } else {
            // Template exists and has config_json, but decode failed - try to decode manually
            $config = is_string($templateInfo['config_json']) 
                ? json_decode($templateInfo['config_json'], true)
                : $templateInfo['config_json'];
            
            if ($config && is_array($config)) {
                $templateConfig = $config;
                error_log("getFinalQRConfig: Successfully decoded config_json manually for template_id: $templateId");
            } else {
                error_log("getFinalQRConfig: Template ID $templateId config_json decode failed for restaurant_id: $restaurantId");
                return null;
            }
        }
    }
    
    $overrideJson = $result['override_json'] ?? null;
    $managerOverrides = null;
    if ($overrideJson) {
        $managerOverrides = is_string($overrideJson) 
            ? json_decode($overrideJson, true)
            : $overrideJson;
    }
    
    $finalConfig = mergeTemplateWithOverrides($templateConfig, $managerOverrides);
    
    if (!$finalConfig || !is_array($finalConfig)) {
        error_log("getFinalQRConfig: Failed to merge template config for restaurant_id: $restaurantId, template_id: $templateId");
        return null;
    }
    
    // Cache the final config
    $saveSuccess = saveFinalQRConfig($restaurantId, $finalConfig);
    
    if (!$saveSuccess) {
        error_log("getFinalQRConfig: Failed to cache final_config_json for restaurant_id: $restaurantId, but returning config anyway");
    }
    
    return $finalConfig;
}

/**
 * Save final QR config to database (cache)
 * @param int $restaurantId
 * @param array $finalConfig
 * @return bool
 */
function saveFinalQRConfig($restaurantId, $finalConfig) {
    $pdo = getDBConnection();
    if (!$pdo) return false;
    
    $configJson = json_encode($finalConfig);
    
    $stmt = $pdo->prepare("
        UPDATE restaurant_qr_codes 
        SET final_config_json = ? 
        WHERE restaurant_id = ?
    ");
    
    return $stmt->execute([$configJson, $restaurantId]);
}

/**
 * Save manager override config
 * Also regenerates and caches final_config_json
 * @param int $restaurantId
 * @param array $overrideConfig
 * @return bool
 */
function saveManagerOverrideConfig($restaurantId, $overrideConfig) {
    $pdo = getDBConnection();
    if (!$pdo) return false;
    
    $overrideJson = json_encode($overrideConfig);
    
    // Get template config to merge (read fresh from database)
    $stmt = $pdo->prepare("SELECT qr_template_id FROM restaurant_qr_codes WHERE restaurant_id = ?");
    $stmt->execute([$restaurantId]);
    $result = $stmt->fetch();
    
    if (!$result) {
        error_log("saveManagerOverrideConfig: No restaurant_qr_codes record found for restaurant_id: $restaurantId");
        return false;
    }
    
    $templateId = $result['qr_template_id'] ?? null;
    
    // If no template is selected, clear final_config_json
    if (!$templateId) {
        error_log("saveManagerOverrideConfig: No template_id for restaurant_id: $restaurantId");
        $stmt = $pdo->prepare("
            UPDATE restaurant_qr_codes 
            SET override_json = ?, final_config_json = NULL, updated_at = CURRENT_TIMESTAMP
            WHERE restaurant_id = ?
        ");
        return $stmt->execute([$overrideJson, $restaurantId]);
    }
    
    // Try to get template config - allow inactive templates if they were previously selected
    // This handles the case where a template becomes inactive but was already selected
    $templateConfig = getTemplateConfig($templateId, true); // Include inactive templates
    
    // If template doesn't exist at all, clear everything
    if (!$templateConfig) {
        // Check if template exists (even if inactive)
        $stmt = $pdo->prepare("SELECT id, name, is_active, config_json FROM qr_templates WHERE id = ?");
        $stmt->execute([$templateId]);
        $templateInfo = $stmt->fetch();
        
        if (!$templateInfo) {
            error_log("saveManagerOverrideConfig: Template ID $templateId does not exist for restaurant_id: $restaurantId - clearing template_id");
            // Template was deleted - clear template_id and config
            $stmt = $pdo->prepare("
                UPDATE restaurant_qr_codes 
                SET qr_template_id = NULL, override_json = ?, final_config_json = NULL, updated_at = CURRENT_TIMESTAMP
                WHERE restaurant_id = ?
            ");
            return $stmt->execute([$overrideJson, $restaurantId]);
        } elseif (empty($templateInfo['config_json'])) {
            error_log("saveManagerOverrideConfig: Template ID $templateId has empty config_json for restaurant_id: $restaurantId");
            // Template exists but has no config - can't generate QR
            $stmt = $pdo->prepare("
                UPDATE restaurant_qr_codes 
                SET override_json = ?, final_config_json = NULL, updated_at = CURRENT_TIMESTAMP
                WHERE restaurant_id = ?
            ");
            return $stmt->execute([$overrideJson, $restaurantId]);
        } else {
            // Template exists and has config_json, but decode failed - try to decode manually
            $config = is_string($templateInfo['config_json']) 
                ? json_decode($templateInfo['config_json'], true)
                : $templateInfo['config_json'];
            
            if ($config && is_array($config)) {
                $templateConfig = $config;
                error_log("saveManagerOverrideConfig: Successfully decoded config_json manually for template_id: $templateId");
            } else {
                error_log("saveManagerOverrideConfig: Template ID $templateId config_json decode failed for restaurant_id: $restaurantId");
                $stmt = $pdo->prepare("
                    UPDATE restaurant_qr_codes 
                    SET override_json = ?, final_config_json = NULL, updated_at = CURRENT_TIMESTAMP
                    WHERE restaurant_id = ?
                ");
                return $stmt->execute([$overrideJson, $restaurantId]);
            }
        }
    }
    
    // Merge and cache final config
    $finalConfig = mergeTemplateWithOverrides($templateConfig, $overrideConfig);
    
    if (!$finalConfig || empty($finalConfig)) {
        error_log("saveManagerOverrideConfig: Failed to merge template config for restaurant_id: $restaurantId, template_id: $templateId");
        return false;
    }
    
    $finalConfigJson = json_encode($finalConfig);
    
    if (!$finalConfigJson || $finalConfigJson === 'null') {
        error_log("saveManagerOverrideConfig: Failed to encode final_config_json for restaurant_id: $restaurantId, template_id: $templateId");
        return false;
    }
    
    // Update both override_json and final_config_json
    $stmt = $pdo->prepare("
        UPDATE restaurant_qr_codes 
        SET override_json = ?, final_config_json = ?, updated_at = CURRENT_TIMESTAMP
        WHERE restaurant_id = ?
    ");
    
    $success = $stmt->execute([$overrideJson, $finalConfigJson, $restaurantId]);
    
    if (!$success) {
        $errorInfo = $stmt->errorInfo();
        error_log("saveManagerOverrideConfig: Failed to update final_config_json for restaurant_id: $restaurantId. Error: " . print_r($errorInfo, true));
    } else {
        error_log("saveManagerOverrideConfig: Successfully saved final_config_json for restaurant_id: $restaurantId, template_id: $templateId");
    }
    
    return $success;
}

/**
 * Get default template config (fallback)
 * @return array
 */
function getDefaultTemplateConfig() {
    return [
        'pattern' => 'square',
        'eyes' => 'square',
        'frame' => [
            'type' => 'none',
            'text' => '',
            'color' => '#000000',
            'text_color' => '#000000',
            'text_size' => 14,
            'bg_enabled' => false,
            'bg_color' => '#FFFFFF'
        ],
        'colors' => [
            'foreground' => '#000000',
            'background' => '#FFFFFF'
        ],
        'logo' => [
            'enabled' => false,
            'size' => 0.2,
            'center_only' => true
        ]
    ];
}

/**
 * Validate template config structure
 * @param array $config
 * @return bool
 */
function validateTemplateConfig($config) {
    if (!is_array($config)) {
        return false;
    }
    
    // Required fields for a valid template config
    $required = ['pattern', 'eyes', 'frame', 'colors', 'logo'];
    
    foreach ($required as $key) {
        if (!isset($config[$key])) {
            return false;
        }
    }
    
    // Validate pattern
    $validPatterns = ['square', 'dots', 'rounded', 'extra-rounded'];
    if (!in_array($config['pattern'], $validPatterns)) {
        return false;
    }
    
    // Validate eyes
    $validEyes = ['square', 'rounded', 'leaf', 'circle'];
    if (!in_array($config['eyes'], $validEyes)) {
        return false;
    }
    
    // Validate frame
    if (!is_array($config['frame']) || !isset($config['frame']['type'])) {
        return false;
    }
    
    return true;
}

