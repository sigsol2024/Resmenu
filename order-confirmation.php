<?php
/**
 * Order Confirmation / Thank You Page
 * Shown after successful checkout - displays order preview and order number
 */

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/config.php';

$slug = trim($_GET['slug'] ?? '');
$orderId = (int)($_GET['order_id'] ?? 0);

if (empty($slug) || !$orderId) {
    header('Location: /');
    exit;
}

$restaurant = getRestaurantBySlug($slug);
if (!$restaurant) {
    http_response_code(404);
    die('Restaurant not found.');
}

$pdo = getDBConnection();
if (!$pdo) {
    http_response_code(500);
    die('Unable to load order.');
}

// Verify order belongs to this restaurant
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND restaurant_id = ?");
$stmt->execute([$orderId, $restaurant['id']]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    http_response_code(404);
    die('Order not found.');
}

// Get order items
$stmt = $pdo->prepare("SELECT name, price, quantity FROM order_items WHERE order_id = ?");
$stmt->execute([$orderId]);
$orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

$customization = getCustomizationSettings($restaurant['id']);
$primaryColor = $customization['primary_color'] ?? '#f20d0d';
$restaurantName = htmlspecialchars($restaurant['name']);
$menuUrl = (defined('SITE_URL') ? rtrim(SITE_URL, '/') : '') . '/restaurant/' . $slug;
$currencySymbol = '₦';
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Thank You - Order #<?php echo (int)$order['id']; ?> - <?php echo $restaurantName; ?></title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@300;400;500;600;700&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { "primary": "<?php echo htmlspecialchars($primaryColor); ?>" },
                    fontFamily: { "display": ["Work Sans", "sans-serif"] },
                },
            },
        }
    </script>
</head>
<body class="bg-[#f8f5f5] dark:bg-[#221010] font-display min-h-screen flex flex-col">
<header class="sticky top-0 z-50 flex items-center justify-between border-b border-[#e5e5e5] dark:border-[#3a2020] px-6 lg:px-10 py-3 bg-white dark:bg-[#1a0b0b]">
    <a href="<?php echo htmlspecialchars($menuUrl); ?>" class="flex items-center gap-4 text-[#181111] dark:text-white">
        <span class="material-symbols-outlined text-2xl" style="color:<?php echo htmlspecialchars($primaryColor); ?>">restaurant_menu</span>
        <h2 class="text-xl font-bold"><?php echo $restaurantName; ?></h2>
    </a>
</header>

<main class="flex-grow w-full max-w-[640px] mx-auto px-4 lg:px-10 py-12">
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 mb-4">
            <span class="material-symbols-outlined text-4xl">check_circle</span>
        </div>
        <h1 class="text-2xl lg:text-3xl font-bold text-[#181111] dark:text-white mb-2">Thank you for your order!</h1>
        <p class="text-[#8a6060] dark:text-[#bcaaaa]">Your order has been received and is being processed.</p>
    </div>

    <div class="bg-white dark:bg-[#1a0b0b] rounded-xl border border-[#e6dbdb] dark:border-[#3a2020] shadow-sm overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-[#f5f0f0] dark:border-[#3a2020] bg-[#f9fafb] dark:bg-[#2a1a1a]">
            <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-[#8a6060] dark:text-[#bcaaaa]">Order Number</span>
                <span class="text-lg font-bold" style="color:<?php echo htmlspecialchars($primaryColor); ?>">#<?php echo (int)$order['id']; ?></span>
            </div>
        </div>
        <div class="p-6">
            <div class="mb-4">
                <p class="text-xs font-medium text-[#8a6060] dark:text-[#bcaaaa] uppercase mb-1">Delivery to</p>
                <p class="font-medium text-[#181111] dark:text-white"><?php echo htmlspecialchars($order['customer_name']); ?></p>
                <p class="text-sm text-[#8a6060] dark:text-[#bcaaaa]"><?php echo htmlspecialchars($order['delivery_address']); ?></p>
            </div>
            <div class="border-t border-[#f5f0f0] dark:border-[#3a2020] pt-4">
                <p class="text-xs font-medium text-[#8a6060] dark:text-[#bcaaaa] uppercase mb-3">Order summary</p>
                <div class="space-y-3">
                    <?php foreach ($orderItems as $item): ?>
                    <div class="flex justify-between text-sm">
                        <span class="text-[#181111] dark:text-white"><?php echo htmlspecialchars($item['name']); ?> × <?php echo (int)$item['quantity']; ?></span>
                        <span class="font-medium text-[#181111] dark:text-white"><?php echo $currencySymbol . number_format((float)$item['price'] * (int)$item['quantity'], 2); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="flex justify-between font-bold text-base mt-4 pt-4 border-t border-[#f5f0f0] dark:border-[#3a2020]">
                    <span class="text-[#181111] dark:text-white">Total</span>
                    <span style="color:<?php echo htmlspecialchars($primaryColor); ?>"><?php echo $currencySymbol . number_format((float)$order['total'], 2); ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="text-center">
        <a href="<?php echo htmlspecialchars($menuUrl); ?>" class="inline-flex items-center justify-center gap-2 w-full sm:w-auto h-14 px-8 rounded-lg text-white font-bold text-base shadow-lg transition-all hover:opacity-90" style="background-color:<?php echo htmlspecialchars($primaryColor); ?>">
            <span class="material-symbols-outlined">done</span>
            Done
        </a>
    </div>
</main>

<script src="<?php echo rtrim(SITE_URL ?? '', '/'); ?>/assets/js/cart.js"></script>
<script>
(function() {
    var slug = <?php echo json_encode($slug); ?>;
    if (window.RESMENU_CART && slug) {
        window.RESMENU_CART.clearCart(slug);
    }
})();
</script>
</body>
</html>
