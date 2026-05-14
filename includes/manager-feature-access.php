<?php
/**
 * Manager Orders/Reservations URL guards.
 * - Plan missing a feature: main pages (orders.php, reservations.php) show blurred content + manager-upgrade-overlay.php (not redirected).
 * - Plan includes feature but Templates toggles are off: redirect to customization.php (same as public menu / sidebar).
 */

/**
 * @param string $restaurantSlug May be empty before sync from DB
 * @param string $messageKey     GET message= for customization.php
 */
function redirectManagerToCustomization(string $restaurantSlug, string $messageKey): void {
    $q = ['message' => $messageKey];
    if ($restaurantSlug !== '') {
        $q['slug'] = $restaurantSlug;
    }
    header('Location: /manager/customization.php?' . http_build_query($q));
    exit;
}

/**
 * When the plan includes food ordering but the manager turned it off on Templates, send them to Templates.
 * When the plan does not include food ordering, do nothing (caller shows upgrade overlay on orders.php).
 *
 * @param array $restaurant restaurants row (needs slug, enable_food_ordering when present)
 */
function assertManagerFoodOrderingToggleOnOrRedirect(int $restaurantId, array $restaurant): void {
    require_once __DIR__ . '/subscription.php';
    if (!hasFeatureAccess($restaurantId, 'food_ordering')) {
        return;
    }
    $enabled = array_key_exists('enable_food_ordering', $restaurant) ? (int) $restaurant['enable_food_ordering'] : 1;
    if ($enabled !== 1) {
        $slug = isset($restaurant['slug']) ? (string) $restaurant['slug'] : '';
        redirectManagerToCustomization($slug, 'ordering_disabled');
    }
}

/**
 * Same as assertManagerFoodOrderingToggleOnOrRedirect for table reservations.
 *
 * @param array $restaurant restaurants row (needs slug, enable_table_reservations when present)
 */
function assertManagerTableReservationsToggleOnOrRedirect(int $restaurantId, array $restaurant): void {
    require_once __DIR__ . '/subscription.php';
    if (!hasFeatureAccess($restaurantId, 'table_reservations')) {
        return;
    }
    $enabled = array_key_exists('enable_table_reservations', $restaurant) ? (int) $restaurant['enable_table_reservations'] : 1;
    if ($enabled !== 1) {
        $slug = isset($restaurant['slug']) ? (string) $restaurant['slug'] : '';
        redirectManagerToCustomization($slug, 'reservations_disabled');
    }
}

/**
 * Subpages: no plan → redirect to overview (upgrade overlay). Plan + toggle off → customization.
 *
 * @param string $slugParam e.g. "?slug=foo" or ""
 */
function assertManagerFoodOrderingSubpageOrRedirect(int $restaurantId, array $restaurant, string $slugParam): void {
    require_once __DIR__ . '/subscription.php';
    if (!hasFeatureAccess($restaurantId, 'food_ordering')) {
        header('Location: /manager/orders.php' . $slugParam);
        exit;
    }
    assertManagerFoodOrderingToggleOnOrRedirect($restaurantId, $restaurant);
}

/**
 * @param string $slugParam e.g. "?slug=foo" or ""
 */
function assertManagerTableReservationsSubpageOrRedirect(int $restaurantId, array $restaurant, string $slugParam): void {
    require_once __DIR__ . '/subscription.php';
    if (!hasFeatureAccess($restaurantId, 'table_reservations')) {
        header('Location: /manager/reservations.php' . $slugParam);
        exit;
    }
    assertManagerTableReservationsToggleOnOrRedirect($restaurantId, $restaurant);
}

/**
 * Plan + Templates toggle (for APIs and POST handlers). Super-admin bypass is not used here.
 */
function managerRestaurantFoodOrderingUsable(int $restaurantId): bool {
    if ($restaurantId < 1) {
        return false;
    }
    require_once __DIR__ . '/subscription.php';
    if (!hasFeatureAccess($restaurantId, 'food_ordering')) {
        return false;
    }
    require_once __DIR__ . '/functions.php';
    $pdo = getDBConnection();
    if (!$pdo) {
        return false;
    }
    $stmt = $pdo->prepare('SELECT enable_food_ordering FROM restaurants WHERE id = ? LIMIT 1');
    $stmt->execute([$restaurantId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        return false;
    }
    if (!array_key_exists('enable_food_ordering', $row)) {
        return true;
    }

    return (int) $row['enable_food_ordering'] === 1;
}

/**
 * Plan + Templates toggle for table reservations.
 */
function managerRestaurantTableReservationsUsable(int $restaurantId): bool {
    if ($restaurantId < 1) {
        return false;
    }
    require_once __DIR__ . '/subscription.php';
    if (!hasFeatureAccess($restaurantId, 'table_reservations')) {
        return false;
    }
    require_once __DIR__ . '/functions.php';
    $pdo = getDBConnection();
    if (!$pdo) {
        return false;
    }
    $stmt = $pdo->prepare('SELECT enable_table_reservations FROM restaurants WHERE id = ? LIMIT 1');
    $stmt->execute([$restaurantId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        return false;
    }
    if (!array_key_exists('enable_table_reservations', $row)) {
        return true;
    }

    return (int) $row['enable_table_reservations'] === 1;
}