<?php
/**
 * Bank Transfer Pending Page
 * Shows bank details and countdown. Order is NOT created until user clicks "I have made this payment".
 * If countdown expires, show "Order invoice expired" - no order is recorded.
 */

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/restaurant-payment-functions.php';
require_once __DIR__ . '/config/config.php';

$token = trim($_GET['token'] ?? '');
$baseUrl = defined('SITE_URL') ? rtrim(SITE_URL, '/') : '';

if (empty($token)) {
    header('Location: ' . $baseUrl . '/');
    exit;
}

$pdo = getDBConnection();
if (!$pdo) {
    http_response_code(500);
    die('Unable to load page.');
}

$stmt = $pdo->prepare("SELECT * FROM pending_bank_transfers WHERE token = ?");
$stmt->execute([$token]);
$draft = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$draft) {
    header('Location: ' . $baseUrl . '/');
    exit;
}

$restaurant = getRestaurant($draft['restaurant_id']);
if (!$restaurant) {
    header('Location: ' . $baseUrl . '/');
    exit;
}

$slug = $restaurant['slug'] ?? '';
$bankTransferMethod = getRestaurantPaymentSettings($restaurant['id'], 'bank_transfer');
if (empty($bankTransferMethod['is_active']) || empty($bankTransferMethod['account_number'])) {
    header('Location: ' . $baseUrl . '/restaurant/' . $slug);
    exit;
}

$cart = json_decode($draft['cart_json'] ?? '[]', true);
if (!is_array($cart)) $cart = [];

$isReservation = (($draft['payment_type'] ?? 'order') === 'reservation') && !empty($draft['reservation_id']);
$reservation = null;
if ($isReservation) {
    $stmtRes = $pdo->prepare("SELECT * FROM table_reservations WHERE id = ? AND restaurant_id = ?");
    $stmtRes->execute([$draft['reservation_id'], $draft['restaurant_id']]);
    $reservation = $stmtRes->fetch(PDO::FETCH_ASSOC);
}

$customization = getCustomizationSettings($restaurant['id']);
$primaryColor = $customization['primary_color'] ?? '#f20d0d';
$restaurantName = htmlspecialchars($restaurant['name']);
$menuUrl = $baseUrl . '/restaurant/' . $slug;
$currencySymbol = '₦';

// Display reference for customer to use in transfer (no order yet)
$displayRef = 'BT-' . strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $token), 0, 8));

$orderCreatedAtUnix = strtotime($draft['created_at'] ?? 'now');
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Complete Bank Transfer - <?php echo $restaurantName; ?></title>
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
    <div id="order-details-view">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-100 text-blue-600 mb-4">
                <span class="material-symbols-outlined text-4xl">account_balance</span>
            </div>
            <h1 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-2"><?php echo $isReservation ? 'Complete Your Reservation Deposit' : 'Complete Your Payment'; ?></h1>
            <p class="text-gray-600">Complete your bank transfer using the details below. You have 15 minutes.</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-gray-600">Reference</span>
                    <span class="text-lg font-bold" style="color:<?php echo htmlspecialchars($primaryColor); ?>">#<?php echo htmlspecialchars($displayRef); ?></span>
                </div>
            </div>
            <div class="p-6">
                <div class="mb-6 p-5 rounded-lg bg-amber-50 border border-amber-200">
                    <p class="text-sm font-bold text-gray-900 mb-3">Bank Transfer Details</p>
                    <p class="text-base font-bold text-gray-900 mb-1">Bank: <?php echo htmlspecialchars($bankTransferMethod['bank_name'] ?? '-'); ?></p>
                    <p class="text-base font-bold text-gray-900 mb-1">Account Number: <?php echo htmlspecialchars($bankTransferMethod['account_number'] ?? '-'); ?></p>
                    <p class="text-base font-bold text-gray-900">Account Name: <?php echo htmlspecialchars($bankTransferMethod['account_name'] ?? '-'); ?></p>
                    <p class="text-sm text-amber-800 mt-3">Transfer exactly <strong><?php echo $currencySymbol . number_format((float)$draft['total'], 2); ?></strong> and use <strong>#<?php echo htmlspecialchars($displayRef); ?></strong> as the reference.</p>
                </div>
                <div id="countdown-box" class="mb-6 flex items-center justify-center gap-2 py-3 px-4 rounded-lg bg-gray-100 text-gray-800">
                    <span class="material-symbols-outlined">schedule</span>
                    <span id="countdown-text" class="font-mono font-bold text-lg">15:00</span>
                </div>
                <div class="mb-4">
                    <p class="text-xs font-medium text-gray-500 uppercase mb-1"><?php echo $isReservation ? 'Reservation for' : 'Delivery to'; ?></p>
                    <p class="font-medium text-gray-900"><?php echo htmlspecialchars($draft['customer_name']); ?></p>
                    <?php if (!$isReservation): ?><p class="text-sm text-gray-600"><?php echo htmlspecialchars($draft['delivery_address']); ?></p><?php endif; ?>
                </div>
                <div class="border-t border-gray-200 pt-4">
                    <p class="text-xs font-medium text-gray-500 uppercase mb-3"><?php echo $isReservation ? 'Reservation deposit' : 'Order summary'; ?></p>
                    <?php if ($isReservation && $reservation): ?>
                    <div class="space-y-2 text-sm">
                        <p><strong>Date:</strong> <?php echo htmlspecialchars(date('M j, Y', strtotime($reservation['reservation_date']))); ?></p>
                        <p><strong>Time:</strong> <?php echo htmlspecialchars(date('g:i A', strtotime($reservation['reservation_time']))); ?></p>
                        <p><strong>Guests:</strong> <?php echo (int)$reservation['party_size']; ?></p>
                    </div>
                    <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($cart as $item): ?>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-900"><?php echo htmlspecialchars($item['name'] ?? ''); ?> × <?php echo max(1, (int)($item['quantity'] ?? 1)); ?></span>
                            <span class="font-medium text-gray-900"><?php echo $currencySymbol . number_format((float)($item['price'] ?? 0) * max(1, (int)($item['quantity'] ?? 1)), 2); ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    <div class="flex justify-between font-bold text-base mt-4 pt-4 border-t border-gray-200">
                        <span class="text-gray-900">Total</span>
                        <span style="color:<?php echo htmlspecialchars($primaryColor); ?>"><?php echo $currencySymbol . number_format((float)$draft['total'], 2); ?></span>
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
            <h1 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-2">Thank you!</h1>
            <p class="text-gray-600"><?php echo $isReservation ? 'Your reservation deposit has been recorded. We look forward to seeing you!' : 'Your order has been recorded. It will be approved once payment is confirmed.'; ?></p>
        </div>
        <div class="text-center">
            <a href="<?php echo $isReservation ? htmlspecialchars($baseUrl . '/restaurant/' . $slug . '/reservation') : htmlspecialchars($menuUrl); ?>" class="inline-flex items-center justify-center gap-2 w-full sm:w-auto h-14 px-8 rounded-lg text-white font-bold text-base shadow-lg transition-all hover:opacity-90" style="background-color:<?php echo htmlspecialchars($primaryColor); ?>">
                <span class="material-symbols-outlined">done</span> <?php echo $isReservation ? 'Back to Reservation' : 'Done'; ?>
            </a>
        </div>
    </div>

    <div id="expired-view" class="hidden">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-red-100 text-red-600 mb-4">
                <span class="material-symbols-outlined text-4xl">cancel</span>
            </div>
            <h1 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-2"><?php echo $isReservation ? 'Payment window expired' : 'Order invoice expired'; ?></h1>
            <p class="text-gray-600"><?php echo $isReservation ? 'The payment window has expired. Please make a new reservation if you still wish to book.' : 'The payment window has expired. This order was not recorded. Please place a new order if you still wish to order.'; ?></p>
        </div>
        <div class="text-center">
            <a href="<?php echo $isReservation ? htmlspecialchars($baseUrl . '/restaurant/' . $slug . '/reservation') : htmlspecialchars($menuUrl); ?>" class="inline-flex items-center justify-center gap-2 w-full sm:w-auto h-14 px-8 rounded-lg text-white font-bold text-base shadow-lg transition-all hover:opacity-90" style="background-color:<?php echo htmlspecialchars($primaryColor); ?>">
                <span class="material-symbols-outlined">arrow_back</span> <?php echo $isReservation ? 'Back to Reservation' : 'Back to Menu'; ?>
            </a>
        </div>
    </div>

    <script>
    (function() {
        var token = <?php echo json_encode($token); ?>;
        var orderCreatedAtUnix = <?php echo (int)$orderCreatedAtUnix; ?>;
        var endTime = new Date(orderCreatedAtUnix * 1000 + 15 * 60 * 1000);

        function updateCountdown() {
            var now = new Date();
            var diff = Math.max(0, Math.floor((endTime - now) / 1000));
            var mins = Math.floor(diff / 60);
            var secs = diff % 60;
            var el = document.getElementById('countdown-text');
            if (el) el.textContent = String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
            if (diff <= 0) {
                var orderDetails = document.getElementById('order-details-view');
                var expiredView = document.getElementById('expired-view');
                if (orderDetails && expiredView) {
                    orderDetails.classList.add('hidden');
                    expiredView.classList.remove('hidden');
                    fetch('<?php echo htmlspecialchars($baseUrl); ?>/api/expire-bank-transfer-draft.php?token=' + encodeURIComponent(token), { method: 'POST' }).catch(function() {});
                }
                return;
            }
            setTimeout(updateCountdown, 1000);
        }
        updateCountdown();

        document.getElementById('payment-confirmed-btn').addEventListener('click', function() {
            var btn = this;
            btn.disabled = true;
            fetch('<?php echo htmlspecialchars($baseUrl); ?>/api/confirm-bank-transfer.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'token=' + encodeURIComponent(token)
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success && data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    btn.disabled = false;
                    alert(data.message || 'Something went wrong. Please try again.');
                }
            })
            .catch(function() {
                btn.disabled = false;
                alert('Something went wrong. Please try again.');
            });
        });
    })();
    </script>
</main>

<script src="<?php echo htmlspecialchars($baseUrl); ?>/assets/js/cart.js"></script>
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
