<?php
/**
 * Payment Failed Page
 * Shown when Paystack/Flutterwave payment is cancelled or fails.
 * No order was created - nothing to switch or cancel.
 */

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/config.php';

$slug = trim($_GET['slug'] ?? '');
$reason = trim($_GET['reason'] ?? 'failed');

$baseUrl = defined('SITE_URL') ? rtrim(SITE_URL, '/') : '';
$menuUrl = $baseUrl . '/';

if (!empty($slug)) {
    $restaurant = getRestaurantBySlug($slug);
    if ($restaurant) {
        $menuUrl = $baseUrl . '/restaurant/' . $slug;
        $restaurantName = htmlspecialchars($restaurant['name']);
        $customization = getCustomizationSettings($restaurant['id']);
        $primaryColor = $customization['primary_color'] ?? '#f20d0d';
    } else {
        $restaurantName = 'Restaurant';
        $primaryColor = '#f20d0d';
    }
} else {
    $restaurantName = 'Restaurant';
    $primaryColor = '#f20d0d';
}

$message = match ($reason) {
    'cancelled' => 'Payment was cancelled. No order was placed.',
    'failed' => 'Payment failed. No order was placed. Please try again.',
    'init_failed' => 'Payment could not be initiated. Please try again.',
    default => 'Something went wrong. No order was placed. Please try again.'
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

    <div class="text-center">
        <a href="<?php echo htmlspecialchars($menuUrl); ?>" class="inline-flex items-center justify-center gap-2 w-full sm:w-auto h-14 px-8 rounded-lg text-white font-bold text-base shadow-lg transition-all hover:opacity-90" style="background-color:<?php echo htmlspecialchars($primaryColor); ?>">
            <span class="material-symbols-outlined">arrow_back</span> Back to Menu
        </a>
    </div>
</main>
</body>
</html>
