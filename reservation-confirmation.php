<?php
/**
 * Reservation Confirmation Page
 * Shown after successful reservation deposit payment (Paystack/Flutterwave or bank transfer).
 */

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/config.php';

$slug = trim($_GET['slug'] ?? '');
$reservationId = (int)($_GET['reservation_id'] ?? 0);

if (empty($slug) || !$reservationId) {
    header('Location: ' . (defined('SITE_URL') ? rtrim(SITE_URL, '/') : '') . '/');
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
    die('Unable to load reservation.');
}

$stmt = $pdo->prepare("SELECT * FROM table_reservations WHERE id = ? AND restaurant_id = ?");
$stmt->execute([$reservationId, $restaurant['id']]);
$reservation = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reservation) {
    http_response_code(404);
    die('Reservation not found.');
}

$customization = getCustomizationSettings($restaurant['id']);
$primaryColor = $customization['primary_color'] ?? '#f20d0d';
$restaurantName = htmlspecialchars($restaurant['name']);
$baseUrl = defined('SITE_URL') ? rtrim(SITE_URL, '/') : '';
$menuUrl = $baseUrl . '/restaurant/' . $slug;
$reservationUrl = $baseUrl . '/restaurant/' . $slug . '/reservation';
$currencySymbol = '₦';
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Reservation Confirmed - <?php echo $restaurantName; ?></title>
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
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-100 text-green-600 mb-4">
            <span class="material-symbols-outlined text-4xl">check_circle</span>
        </div>
        <h1 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-2">Reservation Confirmed!</h1>
        <p class="text-gray-600">Your deposit has been paid. We look forward to seeing you.</p>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-600">Reservation</span>
                <span class="text-lg font-bold" style="color:<?php echo htmlspecialchars($primaryColor); ?>">#<?php echo (int)$reservationId; ?></span>
            </div>
        </div>
        <div class="p-6">
            <div class="space-y-2 text-sm text-gray-700">
                <p><strong>Date:</strong> <?php echo htmlspecialchars(date('l, F j, Y', strtotime($reservation['reservation_date']))); ?></p>
                <p><strong>Time:</strong> <?php echo htmlspecialchars(date('g:i A', strtotime($reservation['reservation_time']))); ?></p>
                <p><strong>Guests:</strong> <?php echo (int)$reservation['party_size']; ?></p>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($reservation['guest_name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($reservation['guest_email']); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($reservation['guest_phone']); ?></p>
                <?php if (!empty($reservation['deposit_amount']) && (float)$reservation['deposit_amount'] > 0): ?>
                <p><strong>Deposit paid:</strong> <?php echo $currencySymbol . number_format((float)$reservation['deposit_amount'], 2); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="flex flex-col sm:flex-row gap-3 justify-center">
        <a href="<?php echo htmlspecialchars($menuUrl); ?>" class="inline-flex items-center justify-center gap-2 w-full sm:w-auto h-12 px-8 rounded-lg text-white font-bold text-base shadow-lg transition-all hover:opacity-90" style="background-color:<?php echo htmlspecialchars($primaryColor); ?>">
            <span class="material-symbols-outlined">restaurant_menu</span> View Menu
        </a>
        <a href="<?php echo htmlspecialchars($reservationUrl); ?>" class="inline-flex items-center justify-center gap-2 w-full sm:w-auto h-12 px-8 rounded-lg border-2 border-gray-200 text-gray-700 font-bold text-base transition-all hover:bg-gray-50">
            <span class="material-symbols-outlined">event_seat</span> Make Another Reservation
        </a>
    </div>
</main>

</body>
</html>
