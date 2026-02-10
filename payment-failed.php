<?php
/**
 * Payment Failed Page
 * Shown when Paystack/Flutterwave payment is cancelled or fails.
 * Offers option to switch to Bank Transfer (no duplicate order) or cancel.
 */

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/restaurant-payment-functions.php';
require_once __DIR__ . '/config/config.php';

$slug = trim($_GET['slug'] ?? '');
$orderId = (int)($_GET['order_id'] ?? 0);
$reason = trim($_GET['reason'] ?? 'failed');

$baseUrl = defined('SITE_URL') ? rtrim(SITE_URL, '/') : '';
$menuUrl = $baseUrl . '/';
$canSwitchToBankTransfer = false;
$switchUrl = '';

if (!empty($slug) && $orderId) {
    $restaurant = getRestaurantBySlug($slug);
    if ($restaurant) {
        $menuUrl = $baseUrl . '/restaurant/' . $slug;
        $restaurantName = htmlspecialchars($restaurant['name']);
        $customization = getCustomizationSettings($restaurant['id']);
        $primaryColor = $customization['primary_color'] ?? '#f20d0d';
        $bankTransferSettings = getRestaurantPaymentSettings($restaurant['id'], 'bank_transfer');
        if (!empty($bankTransferSettings['is_active']) && !empty($bankTransferSettings['account_number'])) {
            $canSwitchToBankTransfer = true;
            $switchUrl = $baseUrl . '/switch-payment-to-bank-transfer.php?slug=' . urlencode($slug) . '&order_id=' . $orderId;
        }
    } else {
        $restaurantName = 'Restaurant';
        $primaryColor = '#f20d0d';
    }
} else {
    $restaurantName = 'Restaurant';
    $primaryColor = '#f20d0d';
}

$message = match ($reason) {
    'cancelled' => 'Payment was cancelled. You can switch to Bank Transfer or cancel your order.',
    'failed' => 'Payment failed. You can switch to Bank Transfer or cancel your order.',
    'init_failed' => 'Payment could not be initiated. You can switch to Bank Transfer or cancel your order.',
    default => 'Something went wrong. You can switch to Bank Transfer or cancel your order.'
};
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Payment Failed - <?php echo $restaurantName; ?></title>
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
<body class="bg-[#f2f4f7] font-display min-h-screen flex flex-col">
<header class="sticky top-0 z-50 flex items-center justify-between border-b border-gray-200 px-6 lg:px-10 py-3 bg-white">
    <a href="<?php echo htmlspecialchars($menuUrl); ?>" class="flex items-center gap-4 text-gray-900">
        <span class="material-symbols-outlined text-2xl" style="color:<?php echo htmlspecialchars($primaryColor); ?>">restaurant_menu</span>
        <h2 class="text-xl font-bold"><?php echo $restaurantName; ?></h2>
    </a>
</header>

<main class="flex-grow w-full max-w-[640px] mx-auto px-4 lg:px-10 py-12">
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-red-100 text-red-600 mb-4">
            <span class="material-symbols-outlined text-4xl">cancel</span>
        </div>
        <h1 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-2">Payment Failed</h1>
        <p class="text-gray-600"><?php echo htmlspecialchars($message); ?></p>
    </div>

    <div class="flex flex-col sm:flex-row gap-3 justify-center items-center">
        <?php if ($canSwitchToBankTransfer): ?>
        <a href="<?php echo htmlspecialchars($switchUrl); ?>" class="inline-flex items-center justify-center gap-2 w-full sm:w-auto h-14 px-8 rounded-lg text-white font-bold text-base shadow-lg transition-all hover:opacity-90" style="background-color:<?php echo htmlspecialchars($primaryColor); ?>">
            <span class="material-symbols-outlined">account_balance</span> Pay via Bank Transfer
        </a>
        <?php endif; ?>
        <a href="<?php echo htmlspecialchars($baseUrl); ?>/cancel-pending-order.php?slug=<?php echo urlencode($slug); ?>&order_id=<?php echo (int)$orderId; ?>&return=menu" class="inline-flex items-center justify-center gap-2 w-full sm:w-auto h-14 px-8 rounded-lg border-2 border-gray-300 text-gray-700 font-bold text-base transition-all hover:bg-gray-50">
            <span class="material-symbols-outlined">cancel</span> Cancel order and return to menu
        </a>
    </div>
</main>
</body>
</html>
