<?php
/**
 * Table Reservation Page
 * Available for any template; access gated by plan (table_reservations feature).
 */

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/subscription.php';
require_once __DIR__ . '/includes/subscription-middleware.php';

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

// Subscription + feature gating (public): reservation page must be blocked when subscription is invalid
// OR when the plan does not include table reservations.
$subscriptionAccess = checkSubscriptionAccess((int)$restaurant['id']);
if (!$subscriptionAccess['valid']) {
    renderPublicSubscriptionBlockedPage($restaurant, $subscriptionAccess, 'Table reservations');
    exit;
}
if (!hasFeatureAccess((int)$restaurant['id'], 'table_reservations')) {
    renderPublicSubscriptionBlockedPage(
        $restaurant,
        ['lockout_reason' => 'feature_not_in_plan', 'message' => 'Table reservations are not included on this plan.', 'subscription' => getRestaurantSubscription((int)$restaurant['id'])],
        'Table reservations',
        403
    );
    exit;
}

$pdo = getDBConnection();
if (!$pdo) {
    http_response_code(500);
    die('Service temporarily unavailable. Please try again later.');
}
$customization = getCustomizationSettings($restaurant['id']);
$primaryColor = $customization['primary_color'] ?? '#f20d0d';
$bgColor = $customization['background_color'] ?? '#f8f5f5';

$menuUrl = (defined('SITE_URL') ? rtrim(SITE_URL, '/') : '') . '/restaurant/' . $slug;
$reservationUrl = (defined('SITE_URL') ? rtrim(SITE_URL, '/') : '') . '/restaurant/' . $slug . '/reservation';
$baseUrl = (defined('SITE_URL') ? rtrim(SITE_URL, '/') : '');
$uploadBaseUrl = defined('UPLOAD_URL') ? rtrim(UPLOAD_URL, '/') : '';
ensureHotelReservationSettings($restaurant['id'], (int)($restaurant['template_id'] ?? 1));
$reservationSettings = getReservationSettings($restaurant['id']);
$depositAmount = (float) ($reservationSettings['deposit_amount'] ?? 0);

$heroBgImage = '';
if (!empty($restaurant['hero_image'])) {
    $heroBgImage = $uploadBaseUrl . '/heroes/' . htmlspecialchars($restaurant['hero_image']);
}
if (empty($heroBgImage) && !empty($restaurant['logo'])) {
    $heroBgImage = $uploadBaseUrl . '/logos/' . htmlspecialchars($restaurant['logo']);
}
if (empty($heroBgImage)) {
    $heroBgImage = 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=1600&h=900&fit=crop';
}

$success = false;
$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reservationDate = trim($_POST['reservation_date'] ?? '');
    $reservationTime = trim($_POST['reservation_time'] ?? '');
    $partySize = (int) ($_POST['party_size'] ?? 1);
    $guestName = trim($_POST['guest_name'] ?? '');
    $guestEmail = trim($_POST['guest_email'] ?? '');
    $guestPhone = trim($_POST['guest_phone'] ?? '');
    $specialOccasion = trim($_POST['special_occasion'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    if (empty($reservationDate)) $errors[] = 'Please select a date.';
    if (empty($reservationTime)) $errors[] = 'Please select a time slot.';
    if (!empty($reservationDate) && strtotime($reservationDate) < strtotime(date('Y-m-d'))) {
        $errors[] = 'Please select a future date.';
    }
    if (!empty($reservationDate) && !empty($reservationTime)) {
        $timeForParse = strlen($reservationTime) === 5 ? $reservationTime . ':00' : $reservationTime;
        $slotDateTime = strtotime($reservationDate . ' ' . $timeForParse);
        if ($slotDateTime !== false && $slotDateTime < time()) {
            $errors[] = 'Please select a future date and time.';
        }
    }
    if ($partySize < 1) $partySize = 1;
    if ($partySize > 10) $partySize = 10;
    if (empty($guestName)) $errors[] = 'Full name is required.';
    if (empty($guestEmail)) $errors[] = 'Email address is required.';
    if (!isValidEmail($guestEmail)) $errors[] = 'Please enter a valid email address.';
    if (empty($guestPhone)) $errors[] = 'Phone number is required.';
    $phoneDigits = preg_replace('/\D/', '', $guestPhone);
    if (strlen($phoneDigits) < 10 || strlen($phoneDigits) > 15) $errors[] = 'Please enter a valid phone number (digits only, 10-15 characters).';

    if (empty($errors)) {
        try {
            require_once __DIR__ . '/includes/order-functions.php';
            $reservationNumber = generateReservationNumber();
            $stmt = $pdo->prepare("
                INSERT INTO table_reservations (restaurant_id, reservation_number, reservation_date, reservation_time, party_size, guest_name, guest_email, guest_phone, special_occasion, notes, status, deposit_amount)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)
            ");
            $timeWithSeconds = strlen($reservationTime) === 5 ? $reservationTime . ':00' : $reservationTime;
            $stmt->execute([
                $restaurant['id'],
                $reservationNumber,
                $reservationDate,
                $timeWithSeconds,
                $partySize,
                $guestName,
                $guestEmail,
                $guestPhone,
                $specialOccasion ?: null,
                $notes ?: null,
                $depositAmount,
            ]);
            $reservationId = (int) $pdo->lastInsertId();
            try {
                sendReservationCreatedEmails($reservationId, $restaurant['id']);
            } catch (Exception $e) {
                error_log("Reservation email failed: " . $e->getMessage());
            }
            if ($depositAmount > 0 && $reservationId) {
                header('Location: ' . $baseUrl . '/restaurant/' . $slug . '/checkout?reservation_id=' . $reservationId);
                exit;
            }
            $success = true;
        } catch (PDOException $e) {
            error_log("Table reservation insert error: " . $e->getMessage());
            $errors[] = 'Sorry, we could not complete your reservation. Please try again or contact the restaurant.';
        }
    }
}

// Get time slots for selected date (or today)
$selectedDate = $_POST['reservation_date'] ?? $_GET['date'] ?? date('Y-m-d');
$timeSlots = getAvailableTimeSlots($restaurant['id'], $selectedDate);

$minDate = date('Y-m-d');
$restaurantName = htmlspecialchars($restaurant['name']);
?>
<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Table Reservation | <?php echo $restaurantName; ?></title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Epilogue:wght@300;400;500;600;700;800;900&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "<?php echo htmlspecialchars($primaryColor); ?>",
                        "background-light": "<?php echo htmlspecialchars($bgColor); ?>",
                        "background-dark": "#221010",
                    },
                    fontFamily: { "display": ["Epilogue", "sans-serif"] },
                    borderRadius: { "DEFAULT": "0.5rem", "lg": "1rem", "xl": "1.5rem", "full": "9999px" },
                },
            },
        }
    </script>
    <style>
        body { font-family: 'Epilogue', sans-serif; }
        .hero-overlay { background: linear-gradient(rgba(34, 16, 16, 0.7), rgba(34, 16, 16, 0.85)); }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-white min-h-screen">
<!-- Hero Background -->
<div class="fixed inset-0 z-0">
    <img class="w-full h-full object-cover" alt="Restaurant interior" src="<?php echo htmlspecialchars($heroBgImage); ?>"/>
    <div class="absolute inset-0 hero-overlay"></div>
</div>

<!-- Navigation -->
<nav class="relative z-50 flex items-center justify-between px-6 md:px-8 py-6 max-w-7xl mx-auto">
    <a href="<?php echo htmlspecialchars($menuUrl); ?>" class="flex items-center space-x-2">
        <?php if (!empty($restaurant['logo'])): ?>
            <img src="<?php echo $uploadBaseUrl . '/logos/' . htmlspecialchars($restaurant['logo']); ?>" alt="<?php echo $restaurantName; ?>" class="h-10 w-auto object-contain">
        <?php else: ?>
            <div class="w-10 h-10 bg-primary flex items-center justify-center rounded-lg">
                <span class="material-icons text-white">restaurant</span>
            </div>
        <?php endif; ?>
        <span class="text-xl md:text-2xl font-extrabold tracking-tighter text-white uppercase"><?php echo $restaurantName; ?></span>
    </a>
    <div class="flex items-center space-x-4 md:space-x-8 text-sm font-medium text-white/80">
        <a class="hover:text-primary transition-colors" href="<?php echo htmlspecialchars($menuUrl); ?>">OUR MENU</a>
        <a class="text-primary font-bold" href="<?php echo htmlspecialchars($reservationUrl); ?>">RESERVE TABLE</a>
    </div>
</nav>

<!-- Main Content -->
<main class="relative z-10 max-w-4xl mx-auto px-4 py-12">
    <div class="bg-white rounded-xl shadow-2xl overflow-hidden border border-gray-200">
        <div class="p-8 md:p-12">
            <?php if ($success): ?>
                <header class="text-center mb-10">
                    <h1 class="text-3xl md:text-4xl font-bold mb-2 dark:text-white">Reservation Confirmed</h1>
                    <p class="text-slate-500 dark:text-slate-400">Thank you! We look forward to seeing you.</p>
                </header>
                <div class="text-center space-y-4">
                    <p class="text-slate-600 dark:text-slate-300">You will receive a confirmation shortly. If you need to modify your reservation, please contact us.</p>
                    <a href="<?php echo htmlspecialchars($menuUrl); ?>" class="inline-block py-3 px-8 bg-primary hover:bg-red-700 text-white font-bold rounded-lg transition-colors">Back to Menu</a>
                </div>
            <?php else: ?>
                <header class="text-center mb-10">
                    <h1 class="text-3xl md:text-4xl font-bold mb-2 dark:text-white">Book Your Table</h1>
                    <p class="text-slate-500 dark:text-slate-400">Join us for an unforgettable culinary experience.</p>
                </header>

                <?php if (!empty($errors)): ?>
                <div class="mb-6 p-4 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300">
                    <ul class="list-disc list-inside space-y-1 text-sm">
                        <?php foreach ($errors as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <!-- Progress steps -->
                <div class="mb-10">
                    <div class="flex items-center justify-between w-full relative">
                        <div class="absolute left-0 top-1/2 -translate-y-1/2 w-full h-0.5 bg-gray-200 -z-10"></div>
                        <div class="flex flex-col items-center gap-2 px-2" style="background-color:<?php echo htmlspecialchars($bgColor); ?>">
                            <div class="res-step-indicator w-10 h-10 rounded-full font-bold shadow-lg ring-4 flex items-center justify-center" id="step-ind-1" data-step="1" style="background-color:<?php echo htmlspecialchars($primaryColor); ?>;color:white;ring-color:<?php echo htmlspecialchars($bgColor); ?>">1</div>
                            <span class="text-xs font-semibold text-gray-600">Date & Time</span>
                        </div>
                        <div class="flex flex-col items-center gap-2 px-2" style="background-color:<?php echo htmlspecialchars($bgColor); ?>">
                            <div class="res-step-indicator w-10 h-10 rounded-full bg-white border-2 border-gray-200 text-gray-500 font-medium ring-4 flex items-center justify-center" id="step-ind-2" data-step="2" style="ring-color:<?php echo htmlspecialchars($bgColor); ?>">2</div>
                            <span class="text-xs font-medium text-gray-500">Guest Info</span>
                        </div>
                        <div class="flex flex-col items-center gap-2 px-2" style="background-color:<?php echo htmlspecialchars($bgColor); ?>">
                            <div class="res-step-indicator w-10 h-10 rounded-full bg-white border-2 border-gray-200 text-gray-500 font-medium ring-4 flex items-center justify-center" id="step-ind-3" data-step="3" style="ring-color:<?php echo htmlspecialchars($bgColor); ?>">3</div>
                            <span class="text-xs font-medium text-gray-500">Requests</span>
                        </div>
                        <div class="flex flex-col items-center gap-2 px-2" style="background-color:<?php echo htmlspecialchars($bgColor); ?>">
                            <div class="res-step-indicator w-10 h-10 rounded-full bg-white border-2 border-gray-200 text-gray-500 font-medium ring-4 flex items-center justify-center" id="step-ind-4" data-step="4" style="ring-color:<?php echo htmlspecialchars($bgColor); ?>">4</div>
                            <span class="text-xs font-medium text-gray-500">Confirm</span>
                        </div>
                    </div>
                </div>

                <form method="post" id="reservation-form">
                    <input type="hidden" name="slug" value="<?php echo htmlspecialchars($slug); ?>"/>
                    <input type="hidden" name="party_size" id="party-size-input" value="<?php echo (int) ($_POST['party_size'] ?? 1); ?>"/>

                    <!-- Step 1: Date, Number of Guests, Time -->
                    <div class="res-step" data-step="1">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="block text-sm font-semibold uppercase tracking-wider mb-3 text-gray-700">Select Date</label>
                                <input type="hidden" name="reservation_date" id="reservation-date-input" value="<?php echo htmlspecialchars($_POST['reservation_date'] ?? $selectedDate); ?>" required/>
                                <div id="reservation-date-trigger" class="border border-gray-200 rounded-lg p-4 bg-gray-50 cursor-pointer hover:border-gray-300 transition-colors flex items-center justify-between" role="button" tabindex="0">
                                    <span id="res-date-display" class="text-gray-600 font-medium">Click to select date</span>
                                    <span class="material-icons text-gray-500 text-lg">expand_more</span>
                                </div>
                                <div id="reservation-calendar-wrap" class="border border-gray-200 rounded-lg p-4 bg-gray-50 mt-3 hidden">
                                    <div class="flex justify-between items-center mb-4">
                                        <button type="button" id="res-cal-prev" class="p-2 rounded hover:bg-gray-200 text-gray-600"><span class="material-icons text-lg">chevron_left</span></button>
                                        <span id="res-cal-month" class="font-bold text-gray-800 text-sm"></span>
                                        <button type="button" id="res-cal-next" class="p-2 rounded hover:bg-gray-200 text-gray-600"><span class="material-icons text-lg">chevron_right</span></button>
                                    </div>
                                    <div id="reservation-calendar" class="grid grid-cols-7 gap-1 text-center text-xs"></div>
                                    <p id="res-cal-legend" class="mt-3 text-xs text-gray-500 flex flex-wrap gap-4"><span><span class="inline-block w-3 h-3 rounded bg-green-500 mr-1"></span>Available</span><span><span class="inline-block w-3 h-3 rounded bg-amber-400 mr-1"></span>Limited</span><span><span class="inline-block w-3 h-3 rounded bg-gray-300 mr-1"></span>Full</span></p>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold uppercase tracking-wider mb-3 text-gray-700">Number of Guests</label>
                                <div class="flex items-center justify-between px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg">
                                    <button type="button" id="party-minus" class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-100 border border-gray-300 text-gray-700 hover:bg-primary hover:text-white hover:border-primary transition-colors shadow-sm">
                                        <span class="material-icons text-sm">remove</span>
                                    </button>
                                    <span id="party-display" class="font-bold text-lg px-4 text-gray-900"><?php echo (int) ($_POST['party_size'] ?? 1); ?> Guest<?php echo ($_POST['party_size'] ?? 1) != 1 ? 's' : ''; ?></span>
                                    <button type="button" id="party-plus" class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-100 border border-gray-300 text-gray-700 hover:bg-primary hover:text-white hover:border-primary transition-colors shadow-sm">
                                        <span class="material-icons text-sm">add</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold uppercase tracking-wider mb-4 text-gray-700 text-center">Available Time Slots</label>
                            <div id="time-slots-container" class="grid grid-cols-3 md:grid-cols-6 gap-3">
                                <?php foreach ($timeSlots as $slot): ?>
                                <button type="button" data-time="<?php echo htmlspecialchars($slot['time']); ?>"
                                    class="time-slot py-3 px-2 text-sm font-bold rounded-lg transition-all border
                                    <?php echo $slot['available'] ? 'border-gray-200 hover:border-primary text-gray-700' : 'opacity-50 cursor-not-allowed line-through border-gray-200 text-gray-500'; ?>"
                                    <?php echo $slot['available'] ? '' : 'disabled'; ?>>
                                    <?php echo htmlspecialchars($slot['time']); ?>
                                </button>
                                <?php endforeach; ?>
                            </div>
                            <input type="hidden" name="reservation_time" id="reservation-time-input" value="<?php echo htmlspecialchars($_POST['reservation_time'] ?? ''); ?>" required/>
                        </div>
                        <div class="flex justify-end mt-8">
                            <button type="button" class="res-next-btn px-8 py-3 font-bold rounded-lg text-white" style="background-color:<?php echo htmlspecialchars($primaryColor); ?>">Next</button>
                        </div>
                    </div>

                    <!-- Step 2: Guest Info -->
                    <div class="res-step hidden" data-step="2">
                        <label class="block text-sm font-semibold uppercase tracking-wider mb-4 text-gray-700">Guest Information</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <input name="guest_name" type="text" placeholder="Full Name" required
                                value="<?php echo htmlspecialchars($_POST['guest_name'] ?? ''); ?>"
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-gray-900"/>
                            <input name="guest_email" type="email" placeholder="Email Address" required
                                value="<?php echo htmlspecialchars($_POST['guest_email'] ?? ''); ?>"
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-gray-900"/>
                        </div>
                        <input name="guest_phone" type="tel" placeholder="Phone Number (numbers only)" required inputmode="numeric"
                            value="<?php echo htmlspecialchars($_POST['guest_phone'] ?? ''); ?>"
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-gray-900 mb-8"/>
                        <div class="flex justify-between">
                            <button type="button" class="res-back-btn px-8 py-3 font-bold rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Back</button>
                            <button type="button" class="res-next-btn px-8 py-3 font-bold rounded-lg text-white" style="background-color:<?php echo htmlspecialchars($primaryColor); ?>">Next</button>
                        </div>
                    </div>

                    <!-- Step 3: Special Requests -->
                    <div class="res-step hidden" data-step="3">
                        <label class="block text-sm font-semibold uppercase tracking-wider mb-2 text-gray-700">Special Requests</label>
                        <div class="flex flex-wrap gap-2 mb-4">
                            <?php
                            $occasions = ['BIRTHDAY', 'ANNIVERSARY', 'BUSINESS', 'DATE_NIGHT'];
                            $selectedOccasion = $_POST['special_occasion'] ?? '';
                            foreach ($occasions as $occ): ?>
                                <button type="button" data-occasion="<?php echo htmlspecialchars($occ); ?>"
                                    class="occasion-btn px-4 py-2 text-xs font-bold rounded-full transition-colors border
                                    <?php echo $selectedOccasion === $occ ? 'border-primary text-white' : 'border-gray-200 bg-gray-50 text-gray-600 hover:bg-gray-100'; ?>"
                                    style="<?php echo $selectedOccasion === $occ ? 'background-color:' . htmlspecialchars($primaryColor) : ''; ?>">
                                    <?php echo htmlspecialchars($occ); ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" name="special_occasion" id="special-occasion-input" value="<?php echo htmlspecialchars($selectedOccasion); ?>"/>
                        <textarea name="notes" rows="3" placeholder="Dietary requirements or additional notes..."
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-gray-900 resize-none mb-8"><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
                        <div class="flex justify-between">
                            <button type="button" class="res-back-btn px-8 py-3 font-bold rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Back</button>
                            <button type="button" class="res-next-btn px-8 py-3 font-bold rounded-lg text-white" style="background-color:<?php echo htmlspecialchars($primaryColor); ?>">Next</button>
                        </div>
                    </div>

                    <!-- Step 4: Review & Confirm -->
                    <div class="res-step hidden" data-step="4">
                        <div id="res-review-summary" class="mb-6 p-4 bg-gray-50 border border-gray-200 rounded-lg text-gray-700 space-y-2 text-sm"></div>
                        <?php if ($depositAmount > 0): ?>
                        <p class="mb-4 text-gray-600">A deposit of <strong>₦<?php echo number_format($depositAmount, 2); ?></strong> will be required at checkout.</p>
                        <?php endif; ?>
                        <div class="flex justify-between">
                            <button type="button" class="res-back-btn px-8 py-3 font-bold rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Back</button>
                            <button type="submit" class="px-8 py-3 font-bold rounded-lg text-white" style="background-color:<?php echo htmlspecialchars($primaryColor); ?>">Confirm Reservation</button>
                        </div>
                        <p class="text-center text-xs text-gray-500 mt-4">By booking, you agree to our terms and cancellation policy.</p>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</main>

<!-- Footer -->
<footer class="relative z-10 mt-20 bg-zinc-900 text-white py-16">
    <div class="max-w-7xl mx-auto px-6 md:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12">
            <div class="space-y-4">
                <div class="flex items-center space-x-2 mb-6">
                    <?php if (!empty($restaurant['logo'])): ?>
                        <img src="<?php echo $uploadBaseUrl . '/logos/' . htmlspecialchars($restaurant['logo']); ?>" alt="<?php echo $restaurantName; ?>" class="h-10 w-auto object-contain">
                    <?php else: ?>
                        <div class="w-8 h-8 bg-primary flex items-center justify-center rounded-lg">
                            <span class="material-icons text-white text-sm">restaurant</span>
                        </div>
                    <?php endif; ?>
                    <span class="text-xl font-extrabold tracking-tighter uppercase"><?php echo $restaurantName; ?></span>
                </div>
                <?php if (!empty($restaurant['footer_content'])): ?>
                    <p class="text-zinc-400 text-sm leading-relaxed"><?php echo nl2br(htmlspecialchars($restaurant['footer_content'])); ?></p>
                <?php elseif (!empty($restaurant['description'])): ?>
                    <p class="text-zinc-400 text-sm leading-relaxed"><?php echo htmlspecialchars($restaurant['description']); ?></p>
                <?php endif; ?>
                <div class="flex gap-4">
                    <?php if (!empty($restaurant['instagram_url'])): ?>
                        <a class="w-10 h-10 rounded-full border border-zinc-700 flex items-center justify-center hover:bg-primary hover:border-primary transition-colors" href="<?php echo htmlspecialchars($restaurant['instagram_url']); ?>" target="_blank" rel="noopener">
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073z"/></svg>
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($restaurant['facebook_url'])): ?>
                        <a class="w-10 h-10 rounded-full border border-zinc-700 flex items-center justify-center hover:bg-primary hover:border-primary transition-colors" href="<?php echo htmlspecialchars($restaurant['facebook_url']); ?>" target="_blank" rel="noopener">
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M9 8h-3v4h3v12h5v-12h3.642l.358-4h-4v-1.667c0-.955.192-1.333 1.115-1.333h2.885v-5h-3.808c-3.596 0-5.192 1.583-5.192 4.615v3.385z"/></svg>
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($restaurant['twitter_url'])): ?>
                        <a class="w-10 h-10 rounded-full border border-zinc-700 flex items-center justify-center hover:bg-primary hover:border-primary transition-colors" href="<?php echo htmlspecialchars($restaurant['twitter_url']); ?>" target="_blank" rel="noopener">
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php if (!empty($restaurant['address']) || !empty($restaurant['phone']) || !empty($restaurant['email'])): ?>
            <div>
                <h4 class="font-bold text-sm uppercase tracking-widest mb-6">Contact</h4>
                <ul class="space-y-3 text-zinc-400 text-sm">
                    <?php if (!empty($restaurant['address'])): ?>
                        <li class="flex items-center gap-3"><span class="material-icons text-primary text-sm">place</span> <?php echo nl2br(htmlspecialchars($restaurant['address'])); ?></li>
                    <?php endif; ?>
                    <?php if (!empty($restaurant['phone'])): ?>
                        <li class="flex items-center gap-3"><span class="material-icons text-primary text-sm">phone</span> <a href="tel:<?php echo htmlspecialchars(preg_replace('/\s+/', '', $restaurant['phone'])); ?>" class="hover:text-white"><?php echo htmlspecialchars($restaurant['phone']); ?></a></li>
                    <?php endif; ?>
                    <?php if (!empty($restaurant['email'])): ?>
                        <li class="flex items-center gap-3"><span class="material-icons text-primary text-sm">email</span> <a href="mailto:<?php echo htmlspecialchars($restaurant['email']); ?>" class="hover:text-white"><?php echo htmlspecialchars($restaurant['email']); ?></a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <?php endif; ?>
        </div>
        <div class="border-t border-zinc-800 mt-16 pt-8 text-center text-xs text-zinc-500 uppercase tracking-widest">
            &copy; <?php echo date('Y'); ?> <?php echo $restaurantName; ?>. All Rights Reserved.
        </div>
    </div>
</footer>

<?php if (!$success): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var primaryColor = '<?php echo addslashes($primaryColor); ?>';
    var baseUrl = '<?php echo addslashes(rtrim($baseUrl ?? '', '/')); ?>';
    var slug = '<?php echo addslashes($slug); ?>';
    var partySize = <?php echo (int) ($_POST['party_size'] ?? 1); ?>;
    var partyInput = document.getElementById('party-size-input');
    var partyDisplay = document.getElementById('party-display');
    var timeInput = document.getElementById('reservation-time-input');
    var occasionInput = document.getElementById('special-occasion-input');
    var dateInput = document.getElementById('reservation-date-input');
    var currentStep = 1;

    function updateParty() {
        partySize = Math.max(1, Math.min(10, partySize));
        partyInput.value = partySize;
        partyDisplay.textContent = partySize + ' Guest' + (partySize !== 1 ? 's' : '');
    }

    function showStep(step) {
        currentStep = step;
        document.querySelectorAll('.res-step').forEach(function(el) { el.classList.add('hidden'); });
        var s = document.querySelector('.res-step[data-step="' + step + '"]');
        if (s) s.classList.remove('hidden');
        document.querySelectorAll('.res-step-indicator').forEach(function(el) {
            var n = parseInt(el.getAttribute('data-step'), 10);
            el.classList.remove('ring-4');
            if (n < step) {
                el.style.backgroundColor = primaryColor;
                el.style.color = 'white';
                el.className = 'res-step-indicator w-10 h-10 rounded-full font-bold shadow-lg ring-4 flex items-center justify-center';
            } else if (n === step) {
                el.style.backgroundColor = primaryColor;
                el.style.color = 'white';
                el.className = 'res-step-indicator w-10 h-10 rounded-full font-bold shadow-lg ring-4 flex items-center justify-center';
            } else {
                el.style.backgroundColor = '';
                el.style.color = '';
                el.className = 'res-step-indicator w-10 h-10 rounded-full bg-white border-2 border-gray-200 text-gray-500 font-medium ring-4 flex items-center justify-center';
            }
        });
        if (step === 4) updateReviewSummary();
    }

    function updateReviewSummary() {
        var date = dateInput ? dateInput.value : '';
        var time = timeInput ? timeInput.value : '';
        var name = document.querySelector('input[name="guest_name"]') ? document.querySelector('input[name="guest_name"]').value : '';
        var email = document.querySelector('input[name="guest_email"]') ? document.querySelector('input[name="guest_email"]').value : '';
        var phone = document.querySelector('input[name="guest_phone"]') ? document.querySelector('input[name="guest_phone"]').value : '';
        var occ = occasionInput ? occasionInput.value : '';
        var notes = document.querySelector('textarea[name="notes"]') ? document.querySelector('textarea[name="notes"]').value : '';
        var html = '<p><strong>Date:</strong> ' + (date || '-') + ' | <strong>Time:</strong> ' + (time || '-') + '</p>';
        html += '<p><strong>Guests:</strong> ' + partySize + '</p>';
        html += '<p><strong>Name:</strong> ' + (name || '-') + '</p>';
        html += '<p><strong>Email:</strong> ' + (email || '-') + '</p>';
        html += '<p><strong>Phone:</strong> ' + (phone || '-') + '</p>';
        if (occ) html += '<p><strong>Occasion:</strong> ' + occ + '</p>';
        if (notes) html += '<p><strong>Notes:</strong> ' + notes + '</p>';
        var el = document.getElementById('res-review-summary');
        if (el) el.innerHTML = html;
    }

    function loadTimeSlots(date) {
        var container = document.getElementById('time-slots-container');
        if (!container) return;
        container.innerHTML = '<p class="col-span-full text-center text-gray-500 py-4">Loading...</p>';
        fetch((baseUrl || '') + '/api/get-reservation-slots.php?slug=' + encodeURIComponent(slug) + '&date=' + encodeURIComponent(date))
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (!data.success || !data.slots) {
                    container.innerHTML = '<p class="col-span-full text-center text-red-500 py-4">Failed to load slots.</p>';
                    return;
                }
                var html = '';
                data.slots.forEach(function(slot) {
                    var cls = 'time-slot py-3 px-2 text-sm font-bold rounded-lg transition-all border ';
                    cls += slot.available ? 'border-gray-200 hover:border-primary text-gray-700' : 'opacity-50 cursor-not-allowed line-through border-gray-200 text-gray-500';
                    html += '<button type="button" data-time="' + slot.time + '" class="' + cls + '"' + (slot.available ? '' : ' disabled') + '>' + slot.time + '</button>';
                });
                container.innerHTML = html;
                timeInput.value = '';
                container.querySelectorAll('.time-slot').forEach(function(btn) {
                    if (btn.disabled) return;
                    btn.addEventListener('click', function() {
                        container.querySelectorAll('.time-slot').forEach(function(b) {
                            b.style.backgroundColor = '';
                            b.style.color = '';
                            b.style.borderColor = '';
                        });
                        btn.style.backgroundColor = primaryColor;
                        btn.style.color = 'white';
                        btn.style.borderColor = primaryColor;
                        timeInput.value = btn.getAttribute('data-time');
                    });
                });
            })
            .catch(function() {
                container.innerHTML = '<p class="col-span-full text-center text-red-500 py-4">Failed to load slots.</p>';
            });
    }

    document.getElementById('party-minus').addEventListener('click', function() { partySize--; updateParty(); });
    document.getElementById('party-plus').addEventListener('click', function() { partySize++; updateParty(); });

    document.getElementById('time-slots-container').addEventListener('click', function(e) {
        var btn = e.target.closest('.time-slot');
        if (!btn || btn.disabled) return;
        document.querySelectorAll('#time-slots-container .time-slot').forEach(function(b) {
            b.style.backgroundColor = '';
            b.style.color = '';
            b.style.borderColor = '';
        });
        btn.style.backgroundColor = primaryColor;
        btn.style.color = 'white';
        btn.style.borderColor = primaryColor;
        timeInput.value = btn.getAttribute('data-time');
    });

    var preSelected = timeInput.value;
    if (preSelected) {
        var sel = document.querySelector('#time-slots-container .time-slot[data-time="' + preSelected + '"]');
        if (sel && !sel.disabled) { sel.style.backgroundColor = primaryColor; sel.style.color = 'white'; sel.style.borderColor = primaryColor; }
    }

    var calYear = new Date().getFullYear();
    var calMonth = new Date().getMonth();
    var minDateStr = '<?php echo $minDate; ?>';

    function getCalMonthRange() {
        var start = new Date(calYear, calMonth, 1);
        var end = new Date(calYear, calMonth + 1, 0);
        return {
            start: start.getFullYear() + '-' + String(start.getMonth() + 1).padStart(2, '0') + '-' + String(start.getDate()).padStart(2, '0'),
            end: end.getFullYear() + '-' + String(end.getMonth() + 1).padStart(2, '0') + '-' + String(end.getDate()).padStart(2, '0')
        };
    }

    function loadReservationCalendar() {
        var r = getCalMonthRange();
        var monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];
        document.getElementById('res-cal-month').textContent = monthNames[calMonth] + ' ' + calYear;
        fetch((baseUrl || '') + '/api/reservation-date-availability.php?slug=' + encodeURIComponent(slug) + '&start=' + encodeURIComponent(r.start) + '&end=' + encodeURIComponent(r.end))
            .then(function(res) { return res.json(); })
            .then(function(data) {
                var dates = (data.success && data.dates) ? data.dates : {};
                renderResCalendar(dates);
            })
            .catch(function() { renderResCalendar({}); });
    }

    function renderResCalendar(dates) {
        var first = new Date(calYear, calMonth, 1);
        var last = new Date(calYear, calMonth + 1, 0);
        var startPad = first.getDay();
        var daysInMonth = last.getDate();
        var prevMonth = calMonth === 0 ? 11 : calMonth - 1;
        var prevYear = calMonth === 0 ? calYear - 1 : calYear;
        var prevLast = new Date(prevYear, prevMonth + 1, 0).getDate();
        var today = new Date().toISOString().slice(0, 10);
        var selectedVal = dateInput.value || '';

        var html = '';
        ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'].forEach(function(d) { html += '<div class="font-semibold text-gray-500 py-1">' + d + '</div>'; });
        for (var i = 0; i < startPad; i++) {
            var d = prevLast - startPad + i + 1;
            html += '<div class="py-2 text-gray-300">' + d + '</div>';
        }
        for (var d = 1; d <= daysInMonth; d++) {
            var dateStr = calYear + '-' + String(calMonth + 1).padStart(2, '0') + '-' + String(d).padStart(2, '0');
            var info = dates[dateStr] || { available: 0, total: 10, status: 'full' };
            var status = info.status;
            if (dateStr < today) status = 'past';
            var cls = 'py-2 rounded cursor-pointer transition-colors ';
            var clickable = false;
            if (status === 'past') {
                cls += 'text-gray-300 cursor-default';
            } else if (status === 'full') {
                cls += 'text-gray-400 bg-gray-100 cursor-not-allowed';
            } else if (status === 'limited') {
                cls += 'bg-amber-100 text-amber-900 hover:bg-amber-200';
                clickable = true;
            } else {
                cls += 'bg-green-100 text-green-800 hover:bg-green-200';
                clickable = true;
            }
            if (dateStr === selectedVal) cls += ' ring-2 ring-offset-1 font-bold';
            var label = status === 'past' ? '' : (status === 'limited' ? info.available + ' left' : '');
            var style = (dateStr === selectedVal) ? ' box-shadow: 0 0 0 2px ' + primaryColor + ';' : '';
            html += '<div class="' + cls + '" data-date="' + dateStr + '" data-clickable="' + (clickable ? '1' : '0') + '" style="' + style + '">' + d + (label ? '<br><span class="text-[10px]">' + label + '</span>' : '') + '</div>';
        }
        var totalCells = startPad + daysInMonth;
        var remainder = totalCells % 7;
        var nextDays = remainder === 0 ? 0 : 7 - remainder;
        for (var i = 1; i <= nextDays; i++) {
            html += '<div class="py-2 text-gray-300">' + i + '</div>';
        }
        document.getElementById('reservation-calendar').innerHTML = html;

        document.querySelectorAll('#reservation-calendar [data-clickable="1"]').forEach(function(el) {
            el.addEventListener('click', function() {
                var dt = this.getAttribute('data-date');
                dateInput.value = dt;
                loadTimeSlots(dt);
                timeInput.value = '';
                document.querySelectorAll('#reservation-calendar [data-date]').forEach(function(c) { c.classList.remove('ring-2','ring-offset-1','font-bold'); c.style.boxShadow = ''; });
                this.classList.add('ring-2','ring-offset-1','font-bold');
                this.style.boxShadow = '0 0 0 2px ' + primaryColor;
                var disp = document.getElementById('res-date-display');
                var wrap = document.getElementById('reservation-calendar-wrap');
                if (disp) disp.textContent = new Date(dt + 'T12:00:00').toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });
                if (wrap) wrap.classList.add('hidden');
            });
        });
    }

    var initDate = dateInput.value || minDateStr;
    if (initDate) {
        var p = initDate.split('-');
        if (p.length === 3) {
            calYear = parseInt(p[0], 10);
            calMonth = parseInt(p[1], 10) - 1;
        }
    }
    loadReservationCalendar();
    var dateTrigger = document.getElementById('reservation-date-trigger');
    var dateDisplay = document.getElementById('res-date-display');
    var calendarWrap = document.getElementById('reservation-calendar-wrap');
    function updateDateDisplay() {
        var d = dateInput.value;
        if (d) {
            var dt = new Date(d + 'T12:00:00');
            dateDisplay.textContent = dt.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });
        } else {
            dateDisplay.textContent = 'Click to select date';
        }
    }
    updateDateDisplay();
    dateTrigger.addEventListener('click', function() {
        calendarWrap.classList.toggle('hidden');
    });
    dateTrigger.addEventListener('keydown', function(e) { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); calendarWrap.classList.toggle('hidden'); } });
    document.getElementById('res-cal-prev').onclick = function() {
        if (calMonth === 0) { calMonth = 11; calYear--; } else calMonth--;
        loadReservationCalendar();
    };
    document.getElementById('res-cal-next').onclick = function() {
        if (calMonth === 11) { calMonth = 0; calYear++; } else calMonth++;
        loadReservationCalendar();
    };

    dateInput.addEventListener('change', function() {
        loadTimeSlots(this.value);
        timeInput.value = '';
    });

    document.querySelectorAll('.occasion-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var occ = btn.getAttribute('data-occasion');
            document.querySelectorAll('.occasion-btn').forEach(function(b) {
                b.style.backgroundColor = '';
                b.classList.remove('border-primary', 'text-white');
                b.classList.add('border-gray-200', 'bg-gray-50', 'text-gray-600');
            });
            if (occasionInput.value === occ) {
                occasionInput.value = '';
            } else {
                occasionInput.value = occ;
                btn.style.backgroundColor = primaryColor;
                btn.classList.remove('border-gray-200', 'bg-gray-50', 'text-gray-600');
                btn.classList.add('border-primary', 'text-white');
            }
        });
    });
    var preOcc = occasionInput.value;
    if (preOcc) {
        var occBtn = document.querySelector('.occasion-btn[data-occasion="' + preOcc + '"]');
        if (occBtn) { occBtn.style.backgroundColor = primaryColor; occBtn.classList.add('border-primary', 'text-white'); occBtn.classList.remove('bg-gray-50', 'text-gray-600'); }
    }

    function isValidEmailClient(val) {
        return /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test((val || '').trim());
    }
    function isValidPhoneClient(val) {
        var digits = (val || '').replace(/\D/g, '');
        return digits.length >= 10 && digits.length <= 15;
    }
    document.querySelectorAll('.res-next-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            if (currentStep === 2) {
                var emailEl = document.querySelector('input[name="guest_email"]');
                var phoneEl = document.querySelector('input[name="guest_phone"]');
                var ok = true;
                if (emailEl) {
                    if (!isValidEmailClient(emailEl.value)) {
                        emailEl.setCustomValidity('Please enter a valid email address (e.g. name@example.com)');
                        emailEl.reportValidity();
                        ok = false;
                    } else { emailEl.setCustomValidity(''); }
                }
                if (phoneEl && ok) {
                    if (!isValidPhoneClient(phoneEl.value)) {
                        phoneEl.setCustomValidity('Please enter a valid phone number (digits only, 10-15 characters)');
                        phoneEl.reportValidity();
                        ok = false;
                    } else { phoneEl.setCustomValidity(''); }
                }
                if (!ok) return;
            }
            if (currentStep < 4) showStep(currentStep + 1);
        });
    });
    document.querySelectorAll('.res-back-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            if (currentStep > 1) showStep(currentStep - 1);
        });
    });
    var phoneInput = document.querySelector('input[name="guest_phone"]');
    if (phoneInput) phoneInput.addEventListener('input', function() { this.value = this.value.replace(/[^\d+\s\-]/g, ''); });
    var formEl = document.getElementById('reservation-form');
    if (formEl) formEl.addEventListener('submit', function(e) {
        var emailEl = document.querySelector('input[name="guest_email"]');
        var phoneEl = document.querySelector('input[name="guest_phone"]');
        if (emailEl && !isValidEmailClient(emailEl.value)) { e.preventDefault(); emailEl.setCustomValidity('Please enter a valid email address'); emailEl.reportValidity(); return false; }
        if (phoneEl && !isValidPhoneClient(phoneEl.value)) { e.preventDefault(); phoneEl.setCustomValidity('Please enter a valid phone number (digits only)'); phoneEl.reportValidity(); return false; }
    });
    showStep(1);
});
</script>
<?php endif; ?>
</body>
</html>
