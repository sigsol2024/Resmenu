<?php
/**
 * API: Subscription plans for marketing (resmenu.net)
 * Returns active plans from subscription_plans (same data as admin/subscription-plans.php).
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/subscription.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $pdo = getDBConnection();
    if (!$pdo) {
        jsonResponse(false, 'Database unavailable', null);
        exit;
    }

    $activeOnly = !isset($_GET['active_only']) || (string) $_GET['active_only'] !== '0';
    $plans = getSubscriptionPlans($activeOnly);

    if (empty($plans)) {
        jsonResponse(false, 'No subscription plans found', []);
    }

    jsonResponse(true, 'Subscription plans retrieved successfully', $plans);
} catch (Exception $e) {
    error_log('API subscription-plans.php: ' . $e->getMessage());
    jsonResponse(false, 'Failed to retrieve subscription plans', null);
}
