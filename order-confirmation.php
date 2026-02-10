<?php
/**
 * Order Confirmation / Thank You Page
 * Shown after checkout. For bank transfer: shows Order Details with bank info, countdown, and "I have made this payment".
 * For other payment methods: shows thank you message immediately.
 */

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/order-functions.php';
require_once __DIR__ . '/includes/restaurant-payment-functions.php';
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

$paymentMethod = $order['payment_method'] ?? '';
$isBankTransfer = ($paymentMethod === 'bank_transfer');

$bankTransferMethod = null;
if ($isBankTransfer) {
    $bankTransferMethod = getRestaurantPaymentSettings($restaurant['id'], 'bank_transfer');
}

// Unix timestamp for countdown (avoids timezone/parsing issues in JS)
$orderCreatedAtUnix = strtotime($order['created_at'] ?? 'now');
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title><?php echo $isBankTransfer ? 'Order Details' : 'Thank You'; ?> - Order #<?php echo htmlspecialchars(getOrderDisplayNumber($order)); ?> - <?php echo $restaurantName; ?></title>
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
    <?php if ($isBankTransfer && $bankTransferMethod): ?>
    <!-- Bank Transfer: Order Details with bank info, countdown, "I have made this payment" -->
    <div id="order-details-view">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-100 text-blue-600 mb-4">
                <span class="material-symbols-outlined text-4xl">account_balance</span>
            </div>
            <h1 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-2">Order Details</h1>
            <p class="text-gray-600">Complete your bank transfer using the details below. You have 15 minutes.</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-gray-600">Order Number</span>
                    <span class="text-lg font-bold" style="color:<?php echo htmlspecialchars($primaryColor); ?>">#<?php echo htmlspecialchars(getOrderDisplayNumber($order)); ?></span>
                </div>
            </div>
            <div class="p-6">
                <div class="mb-6 p-5 rounded-lg bg-amber-50 border border-amber-200">
                    <p class="text-sm font-bold text-gray-900 mb-3">Bank Transfer Details</p>
                    <p class="text-base font-bold text-gray-900 mb-1">Bank: <?php echo htmlspecialchars($bankTransferMethod['bank_name'] ?? '-'); ?></p>
                    <p class="text-base font-bold text-gray-900 mb-1">Account Number: <?php echo htmlspecialchars($bankTransferMethod['account_number'] ?? '-'); ?></p>
                    <p class="text-base font-bold text-gray-900">Account Name: <?php echo htmlspecialchars($bankTransferMethod['account_name'] ?? '-'); ?></p>
                    <p class="text-sm text-amber-800 mt-3">Transfer exactly <strong><?php echo $currencySymbol . number_format((float)$order['total'], 2); ?></strong> and use your order number <strong>#<?php echo htmlspecialchars(getOrderDisplayNumber($order)); ?></strong> as the reference.</p>
                </div>
                <div id="countdown-box" class="mb-6 flex items-center justify-center gap-2 py-3 px-4 rounded-lg bg-gray-100 text-gray-800">
                    <span class="material-symbols-outlined">schedule</span>
                    <span id="countdown-text" class="font-mono font-bold text-lg">15:00</span>
                </div>
                <div class="mb-4">
                    <p class="text-xs font-medium text-gray-500 uppercase mb-1">Delivery to</p>
                    <p class="font-medium text-gray-900"><?php echo htmlspecialchars($order['customer_name']); ?></p>
                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($order['delivery_address']); ?></p>
                </div>
                <div class="border-t border-gray-200 pt-4">
                    <p class="text-xs font-medium text-gray-500 uppercase mb-3">Order summary</p>
                    <div class="space-y-3">
                        <?php foreach ($orderItems as $item): ?>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-900"><?php echo htmlspecialchars($item['name']); ?> × <?php echo (int)$item['quantity']; ?></span>
                            <span class="font-medium text-gray-900"><?php echo $currencySymbol . number_format((float)$item['price'] * (int)$item['quantity'], 2); ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="flex justify-between font-bold text-base mt-4 pt-4 border-t border-gray-200">
                        <span class="text-gray-900">Total</span>
                        <span style="color:<?php echo htmlspecialchars($primaryColor); ?>"><?php echo $currencySymbol . number_format((float)$order['total'], 2); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center">
            <button type="button" id="payment-confirmed-btn" class="inline-flex items-center justify-center gap-2 w-full sm:w-auto h-12 px-8 rounded-lg text-white font-bold text-base shadow-lg transition-all hover:opacity-90" style="background-color:<?php echo htmlspecialchars($primaryColor); ?>">
                <span class="material-symbols-outlined">check_circle</span> I have made this payment
            </button>
        </div>
    </div>

    <div id="thank-you-view" class="hidden">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-100 text-green-600 mb-4">
                <span class="material-symbols-outlined text-4xl">check_circle</span>
            </div>
            <h1 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-2">Thank you for your order!</h1>
            <p class="text-gray-600">Your payment confirmation has been received. We'll prepare your order shortly.</p>
        </div>
        <div class="text-center">
            <a href="<?php echo htmlspecialchars($menuUrl); ?>" class="inline-flex items-center justify-center gap-2 w-full sm:w-auto h-14 px-8 rounded-lg text-white font-bold text-base shadow-lg transition-all hover:opacity-90" style="background-color:<?php echo htmlspecialchars($primaryColor); ?>">
                <span class="material-symbols-outlined">done</span> Done
            </a>
        </div>
    </div>

    <script>
    (function() {
        var orderCreatedAtUnix = <?php echo (int)$orderCreatedAtUnix; ?>;
        var endTime = new Date(orderCreatedAtUnix * 1000 + 15 * 60 * 1000);

        function updateCountdown() {
            var now = new Date();
            var diff = Math.max(0, Math.floor((endTime - now) / 1000));
            var mins = Math.floor(diff / 60);
            var secs = diff % 60;
            var el = document.getElementById('countdown-text');
            if (el) el.textContent = String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
            if (diff <= 0) return;
            setTimeout(updateCountdown, 1000);
        }
        updateCountdown();

        document.getElementById('payment-confirmed-btn').addEventListener('click', function() {
            document.getElementById('order-details-view').classList.add('hidden');
            document.getElementById('thank-you-view').classList.remove('hidden');
        });
    })();
    </script>
    <?php else: ?>
    <!-- Non-bank-transfer: Thank you immediately -->
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-100 text-green-600 mb-4">
            <span class="material-symbols-outlined text-4xl">check_circle</span>
        </div>
        <h1 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-2">Thank you for your order!</h1>
        <p class="text-gray-600">Your order has been received and is being processed.</p>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-600">Order Number</span>
                <span class="text-lg font-bold" style="color:<?php echo htmlspecialchars($primaryColor); ?>">#<?php echo htmlspecialchars(getOrderDisplayNumber($order)); ?></span>
            </div>
        </div>
        <div class="p-6">
            <div class="mb-4">
                <p class="text-xs font-medium text-gray-500 uppercase mb-1">Delivery to</p>
                <p class="font-medium text-gray-900"><?php echo htmlspecialchars($order['customer_name']); ?></p>
                <p class="text-sm text-gray-600"><?php echo htmlspecialchars($order['delivery_address']); ?></p>
            </div>
            <div class="border-t border-gray-200 pt-4">
                <p class="text-xs font-medium text-gray-500 uppercase mb-3">Order summary</p>
                <div class="space-y-3">
                    <?php foreach ($orderItems as $item): ?>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-900"><?php echo htmlspecialchars($item['name']); ?> × <?php echo (int)$item['quantity']; ?></span>
                        <span class="font-medium text-gray-900"><?php echo $currencySymbol . number_format((float)$item['price'] * (int)$item['quantity'], 2); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="flex justify-between font-bold text-base mt-4 pt-4 border-t border-gray-200">
                    <span class="text-gray-900">Total</span>
                    <span style="color:<?php echo htmlspecialchars($primaryColor); ?>"><?php echo $currencySymbol . number_format((float)$order['total'], 2); ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="text-center">
        <a href="<?php echo htmlspecialchars($menuUrl); ?>" class="inline-flex items-center justify-center gap-2 w-full sm:w-auto h-14 px-8 rounded-lg text-white font-bold text-base shadow-lg transition-all hover:opacity-90" style="background-color:<?php echo htmlspecialchars($primaryColor); ?>">
            <span class="material-symbols-outlined">done</span> Done
        </a>
    </div>
    <?php endif; ?>
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
