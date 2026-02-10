<?php
/**
 * Checkout Page
 * Secure checkout for restaurant orders
 */

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/restaurant-payment-functions.php';
require_once __DIR__ . '/config/config.php';

$slug = trim($_GET['slug'] ?? $_POST['slug'] ?? '');
if (empty($slug)) {
    header('Location: /');
    exit;
}

$restaurant = getRestaurantBySlug($slug);
if (!$restaurant) {
    http_response_code(404);
    die('Restaurant not found.');
}

$pdo = getDBConnection();
$customization = getCustomizationSettings($restaurant['id']);
$primaryColor = $customization['primary_color'] ?? '#f20d0d';
$paymentMethods = getRestaurantActivePaymentMethods($restaurant['id']);
$restaurantName = htmlspecialchars($restaurant['name']);
$menuUrl = (defined('SITE_URL') ? rtrim(SITE_URL, '/') : '') . '/restaurant/' . $slug;
$uploadBaseUrl = defined('UPLOAD_URL') ? rtrim(UPLOAD_URL, '/') : '';

$currencySymbol = '₦';
$deliveryFee = 0;
$taxRate = 0;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cartJson = $_POST['cart_json'] ?? '';
    $customerName = trim($_POST['customer_name'] ?? '');
    $customerPhone = trim($_POST['customer_phone'] ?? '');
    $customerEmail = trim($_POST['customer_email'] ?? '');
    $deliveryAddress = trim($_POST['delivery_address'] ?? '');
    $paymentMethod = trim($_POST['payment_method'] ?? '');

    $errors = [];
    if (empty($customerName)) $errors[] = 'Full name is required.';
    if (empty($customerPhone)) $errors[] = 'Phone number is required.';
    if (empty($customerEmail)) $errors[] = 'Email address is required.';
    if (!isValidEmail($customerEmail)) $errors[] = 'Please enter a valid email address.';
    if (empty($deliveryAddress)) $errors[] = 'House address is required.';

    $cart = [];
    if (!empty($cartJson)) {
        $decoded = json_decode($cartJson, true);
        if (is_array($decoded)) $cart = $decoded;
    }
    if (empty($cart)) $errors[] = 'Your cart is empty. Please add items before checkout.';
    if (empty($paymentMethods)) $errors[] = 'No payment methods configured. Please contact the restaurant.';

    $activeGateways = array_column($paymentMethods, 'gateway');
    if (!empty($paymentMethods) && empty($paymentMethod)) $errors[] = 'Please select a payment method.';
    if (!empty($paymentMethods) && $paymentMethod && !in_array($paymentMethod, $activeGateways)) $errors[] = 'Invalid payment method selected.';

    if (empty($errors)) {
        require_once __DIR__ . '/includes/order-functions.php';
        $result = createOrder($restaurant['id'], $cart, [
            'customer_name' => $customerName,
            'customer_phone' => $customerPhone,
            'customer_email' => $customerEmail,
            'delivery_address' => $deliveryAddress,
            'payment_method' => $paymentMethod,
        ], $deliveryFee, $taxRate);

        if ($result['success']) {
            $thankYouUrl = (defined('SITE_URL') ? rtrim(SITE_URL, '/') : '') . '/order-confirmation.php?slug=' . urlencode($slug) . '&order_id=' . (int)$result['order_id'];
            header('Location: ' . $thankYouUrl);
            exit;
        }
        $errors = array_merge($errors, $result['errors'] ?? ['Failed to create order.']);
    }
}

?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Secure Checkout - <?php echo $restaurantName; ?></title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@300;400;500;600;700&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "<?php echo htmlspecialchars($primaryColor); ?>",
                        "background-light": "#f2f4f7",
                    },
                    fontFamily: { "display": ["Work Sans", "sans-serif"] },
                    borderRadius: {"DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "full": "9999px"},
                },
            },
        }
    </script>
</head>
<body class="bg-[#f2f4f7] font-display min-h-screen flex flex-col">
<header class="sticky top-0 z-50 flex items-center justify-between whitespace-nowrap border-b border-solid border-gray-200 px-6 lg:px-10 py-3 bg-white">
    <a href="<?php echo htmlspecialchars($menuUrl); ?>" class="flex items-center gap-4 text-gray-900">
        <span class="material-symbols-outlined text-2xl" style="color:<?php echo htmlspecialchars($primaryColor); ?>">restaurant_menu</span>
        <h2 class="text-xl font-bold"><?php echo $restaurantName; ?></h2>
    </a>
    <a href="<?php echo htmlspecialchars($menuUrl); ?>" class="text-sm font-medium text-gray-700 hover:opacity-80">Menu</a>
</header>

<main class="flex-grow w-full max-w-[1280px] mx-auto px-4 lg:px-10 py-8 lg:py-12">
    <div class="mb-10 text-center lg:text-left">
        <h1 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-2">Secure Checkout</h1>
        <p class="text-gray-600">Complete your details below to finalize your order.</p>
    </div>

    <?php if (!empty($errors)): ?>
    <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200 text-red-700">
        <ul class="list-disc list-inside space-y-1 text-sm">
            <?php foreach ($errors as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <form method="post" id="checkout-form" class="flex flex-col lg:flex-row gap-8 lg:gap-16">
        <input type="hidden" name="slug" value="<?php echo htmlspecialchars($slug); ?>"/>
        <input type="hidden" name="cart_json" id="cart-json-input" value=""/>

        <div class="flex-1 min-w-0">
            <div class="mb-10">
                <div class="flex items-center justify-between w-full relative">
                    <div class="absolute left-0 top-1/2 -translate-y-1/2 w-full h-0.5 bg-gray-200 -z-10"></div>
                    <div class="flex flex-col items-center gap-2 bg-[#f2f4f7] px-2">
                        <div class="flex items-center justify-center w-10 h-10 rounded-full text-white font-bold shadow-lg ring-4 ring-[#f2f4f7]" style="background-color:<?php echo htmlspecialchars($primaryColor); ?>">1</div>
                        <span class="text-sm font-bold whitespace-nowrap" style="color:<?php echo htmlspecialchars($primaryColor); ?>">Delivery</span>
                    </div>
                    <div class="flex flex-col items-center gap-2 bg-[#f2f4f7] px-2">
                        <div class="flex items-center justify-center w-10 h-10 rounded-full bg-white border-2 border-gray-200 text-gray-500 font-medium ring-4 ring-[#f2f4f7]">2</div>
                        <span class="text-sm font-medium text-gray-500 whitespace-nowrap hidden sm:block">Payment</span>
                    </div>
                    <div class="flex flex-col items-center gap-2 bg-[#f2f4f7] px-2">
                        <div class="flex items-center justify-center w-10 h-10 rounded-full bg-white border-2 border-gray-200 text-gray-500 font-medium ring-4 ring-[#f2f4f7]">3</div>
                        <span class="text-sm font-medium text-gray-500 whitespace-nowrap hidden sm:block">Review</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 lg:p-8">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold text-gray-900">Contact Information</h3>
                    <span class="text-xs font-medium px-2 py-1 rounded" style="color:<?php echo htmlspecialchars($primaryColor); ?>;background-color:rgba(242,13,13,0.1)">Step 1 of 3</span>
                </div>
                <div class="grid grid-cols-1 gap-6 mb-8">
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium text-gray-700">Full Name</label>
                        <input name="customer_name" type="text" placeholder="e.g. Jonathan Doe" required
                            class="w-full h-12 px-4 rounded-lg border border-gray-200 bg-white text-gray-900 focus:border-primary focus:ring-1 focus:ring-primary placeholder-gray-400"
                            value="<?php echo htmlspecialchars($_POST['customer_name'] ?? ''); ?>"/>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium text-gray-700">Phone Number</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-gray-500">call</span>
                            <input name="customer_phone" type="tel" placeholder="(555) 000-0000" required
                                class="w-full h-12 pl-12 pr-4 rounded-lg border border-gray-200 bg-white text-gray-900 focus:border-primary focus:ring-1 focus:ring-primary placeholder-gray-400"
                                value="<?php echo htmlspecialchars($_POST['customer_phone'] ?? ''); ?>"/>
                        </div>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium text-gray-700">Email Address</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-gray-500">email</span>
                            <input name="customer_email" type="email" placeholder="you@example.com" required
                                class="w-full h-12 pl-12 pr-4 rounded-lg border border-gray-200 bg-white text-gray-900 focus:border-primary focus:ring-1 focus:ring-primary placeholder-gray-400"
                                value="<?php echo htmlspecialchars($_POST['customer_email'] ?? ''); ?>"/>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between mb-6 pt-6 border-t border-gray-200">
                    <h3 class="text-xl font-bold text-gray-900">Delivery Address</h3>
                </div>
                <div class="gap-6 mb-8">
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium text-gray-700">House Address</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-4 top-4 text-gray-500">location_on</span>
                            <textarea name="delivery_address" rows="3" placeholder="Street address, apartment/suite, city, state, zip" required
                                class="w-full min-h-[100px] pl-12 pr-4 py-3 rounded-lg border border-gray-200 bg-white text-gray-900 focus:border-primary focus:ring-1 focus:ring-primary placeholder-gray-400 resize-y"><?php echo htmlspecialchars($_POST['delivery_address'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between mb-6 pt-6 border-t border-gray-200">
                    <h3 class="text-xl font-bold text-gray-900">Payment Method</h3>
                </div>
                <?php if (empty($paymentMethods)): ?>
                <div class="mb-8 p-4 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-sm">
                    No payment methods configured. Please contact the restaurant.
                </div>
                <?php else: ?>
                <div class="flex flex-wrap gap-4 mb-8">
                    <?php
                    $icons = ['paystack' => 'credit_card', 'flutterwave' => 'account_balance_wallet', 'bank_transfer' => 'account_balance'];
                    $selectedPayment = $_POST['payment_method'] ?? '';
                    foreach ($paymentMethods as $pm): ?>
                    <label class="flex-1 min-w-[140px] cursor-pointer">
                        <input type="radio" name="payment_method" value="<?php echo htmlspecialchars($pm['gateway']); ?>" class="peer sr-only" data-gateway="<?php echo htmlspecialchars($pm['gateway']); ?>"
                            <?php echo $selectedPayment === $pm['gateway'] ? 'checked' : ''; ?>/>
                        <div class="border-2 border-gray-200 rounded-lg p-4 flex flex-col items-center justify-center gap-2 peer-checked:border-primary peer-checked:ring-2 peer-checked:ring-primary transition-all">
                            <span class="material-symbols-outlined text-gray-500"><?php echo $icons[$pm['gateway']] ?? 'payments'; ?></span>
                            <span class="font-medium text-gray-900 text-center text-sm"><?php echo htmlspecialchars($pm['label']); ?></span>
                        </div>
                    </label>
                    <?php endforeach; ?>
                </div>
                <?php
                $hasBankTransfer = false;
                foreach ($paymentMethods as $pm) { if ($pm['gateway'] === 'bank_transfer') { $hasBankTransfer = true; break; } }
                if ($hasBankTransfer): ?>
                <div id="bank-transfer-note" class="mb-8 p-4 rounded-lg bg-blue-50 border border-blue-200" style="display:none">
                    <p class="text-sm text-gray-700">After placing your order, you'll be redirected to the Order Details page where you will find our bank account information. Please complete the transfer within 15 minutes to confirm your order.</p>
                </div>
                <script>
                document.querySelectorAll('input[name="payment_method"]').forEach(function(r) {
                    r.addEventListener('change', function() {
                        var note = document.getElementById('bank-transfer-note');
                        if (note) note.style.display = this.value === 'bank_transfer' ? 'block' : 'none';
                    });
                });
                </script>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="w-full lg:w-[380px] flex-shrink-0">
            <div class="sticky top-24 bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <span class="material-symbols-outlined" style="color:<?php echo htmlspecialchars($primaryColor); ?>">receipt_long</span>
                        Order Summary
                    </h3>
                </div>
                <div class="p-6 flex flex-col gap-6">
                    <div id="checkout-order-items" class="flex flex-col gap-4 max-h-[300px] overflow-y-auto pr-2"></div>
                    <div class="h-px bg-gray-200 w-full"></div>
                    <div class="flex flex-col gap-2 pt-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Subtotal</span>
                            <span id="checkout-subtotal" class="font-medium text-gray-900">₦0.00</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Delivery Fee</span>
                            <span id="checkout-delivery" class="font-medium text-gray-900">₦0.00</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Tax</span>
                            <span id="checkout-tax" class="font-medium text-gray-900">₦0.00</span>
                        </div>
                    </div>
                    <div class="flex justify-between items-end pt-4 border-t border-dashed border-gray-200">
                        <span class="text-base font-bold text-gray-900">Total</span>
                        <span id="checkout-total" class="text-2xl font-bold" style="color:<?php echo htmlspecialchars($primaryColor); ?>">₦0.00</span>
                    </div>
                    <button type="submit" class="w-full mt-4 h-14 px-6 rounded-lg text-white font-bold text-base shadow-lg transition-all flex items-center justify-center gap-2 group" style="background-color:<?php echo htmlspecialchars($primaryColor); ?>">
                        Proceed to Payment <span class="material-symbols-outlined group-hover:translate-x-1 transition-transform">arrow_forward</span>
                    </button>
                    <a href="<?php echo htmlspecialchars($menuUrl); ?>" class="inline-flex items-center justify-center gap-1.5 py-2 text-sm text-gray-500 hover:text-gray-700 transition-colors">
                        <span class="material-symbols-outlined text-base">arrow_back</span> Back to Menu
                    </a>
                </div>
                <div class="bg-gray-50 p-4 text-center">
                    <p class="text-xs text-gray-600 flex items-center justify-center gap-1">
                        <span class="material-symbols-outlined text-sm">lock</span> Secure 256-bit SSL Encrypted Payment
                    </p>
                </div>
            </div>
        </div>
    </form>
</main>

<footer class="mt-auto border-t border-gray-200 bg-white py-10 px-6 lg:px-10">
    <div class="flex flex-col md:flex-row justify-between items-center gap-6 max-w-[1280px] mx-auto">
        <span class="text-sm font-bold text-gray-900"><?php echo $restaurantName; ?></span>
        <p class="text-xs text-gray-600">© <?php echo date('Y'); ?> <?php echo $restaurantName; ?>. All rights reserved.</p>
    </div>
</footer>

<script src="<?php echo rtrim(SITE_URL ?? '', '/'); ?>/assets/js/cart.js"></script>
<script>
(function() {
    const CART = window.RESMENU_CART;
    const slug = <?php echo json_encode($slug); ?>;
    const symbol = <?php echo json_encode($currencySymbol); ?>;
    const uploadBaseUrl = <?php echo json_encode($uploadBaseUrl); ?>;
    const deliveryFee = <?php echo (float)$deliveryFee; ?>;
    const taxRate = <?php echo (float)$taxRate; ?>;

    const items = CART.getCart(slug);
    const subtotal = CART.getTotalAmount(slug);
    const tax = subtotal * taxRate;
    const total = subtotal + deliveryFee + tax;

    document.getElementById('cart-json-input').value = JSON.stringify(items);

    const itemsEl = document.getElementById('checkout-order-items');
    const itemsHtml = items.map(function(item) {
        const imgUrl = item.image ? (uploadBaseUrl + '/menu-items/' + item.image) : '';
        const imgStyle = imgUrl ? 'background-image:url(\'' + imgUrl.replace(/'/g, "\\'") + '\')' : 'background:#e5e5e5';
        const lineTotal = (parseFloat(item.price) || 0) * (item.quantity || 1);
        return '<div class="flex gap-4"><div class="w-16 h-16 rounded-lg bg-gray-100 overflow-hidden flex-shrink-0 bg-cover bg-center" style="' + imgStyle + '"></div><div class="flex-1 flex flex-col justify-between"><div class="flex justify-between items-start"><p class="text-sm font-bold text-gray-900">' + (item.name || '').replace(/</g, '&lt;') + '</p><p class="text-sm font-bold text-gray-900">' + CART.formatPrice(lineTotal, symbol) + '</p></div><p class="text-xs text-gray-600">Qty: ' + (item.quantity || 1) + '</p></div></div>';
    }).join('');
    itemsEl.innerHTML = itemsHtml || '<p class="text-gray-600 py-4">No items in cart.</p>';

    document.getElementById('checkout-subtotal').textContent = CART.formatPrice(subtotal, symbol);
    document.getElementById('checkout-delivery').textContent = CART.formatPrice(deliveryFee, symbol);
    document.getElementById('checkout-tax').textContent = CART.formatPrice(tax, symbol);
    document.getElementById('checkout-total').textContent = CART.formatPrice(total, symbol);

    document.getElementById('checkout-form').addEventListener('submit', function() {
        if (items.length === 0) {
            alert('Your cart is empty. Please add items before checkout.');
            return false;
        }
    });
})();
</script>
</body>
</html>
