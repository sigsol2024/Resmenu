<?php
/**
 * Table Reservation Page
 * Template 4 only - unique per restaurant
 */

require_once __DIR__ . '/includes/functions.php';
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

// Template 4 only
$templateId = (int) ($restaurant['template_id'] ?? 1);
if ($templateId !== 4) {
    http_response_code(404);
    die('Reservations are not available for this restaurant.');
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
$uploadBaseUrl = defined('UPLOAD_URL') ? rtrim(UPLOAD_URL, '/') : '';

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

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO table_reservations (restaurant_id, reservation_date, reservation_time, party_size, guest_name, guest_email, guest_phone, special_occasion, notes, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
            ");
            $timeWithSeconds = strlen($reservationTime) === 5 ? $reservationTime . ':00' : $reservationTime;
            $stmt->execute([
                $restaurant['id'],
                $reservationDate,
                $timeWithSeconds,
                $partySize,
                $guestName,
                $guestEmail,
                $guestPhone,
                $specialOccasion ?: null,
                $notes ?: null,
            ]);
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
    <div class="bg-white dark:bg-zinc-900 rounded-xl shadow-2xl overflow-hidden border border-black/5 dark:border-white/10">
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

                <form method="post" class="space-y-8">
                    <input type="hidden" name="slug" value="<?php echo htmlspecialchars($slug); ?>"/>
                    <input type="hidden" name="party_size" id="party-size-input" value="<?php echo (int) ($_POST['party_size'] ?? 4); ?>"/>

                    <!-- Step 1: Date & Party Size -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold uppercase tracking-wider mb-3 text-slate-700 dark:text-slate-300">Select Date</label>
                            <div class="relative">
                                <span class="material-icons absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">calendar_today</span>
                                <input name="reservation_date" type="date" min="<?php echo $minDate; ?>" required
                                    value="<?php echo htmlspecialchars($_POST['reservation_date'] ?? $selectedDate); ?>"
                                    class="w-full pl-12 pr-4 py-3 bg-background-light dark:bg-zinc-800 border-none rounded-lg focus:ring-2 focus:ring-primary text-slate-900 dark:text-white"/>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold uppercase tracking-wider mb-3 text-slate-700 dark:text-slate-300">Party Size</label>
                            <div class="flex items-center justify-between px-4 py-3 bg-background-light dark:bg-zinc-800 rounded-lg">
                                <button type="button" id="party-minus" class="w-8 h-8 flex items-center justify-center rounded-full bg-white dark:bg-zinc-700 shadow hover:bg-primary hover:text-white transition-colors">
                                    <span class="material-icons text-sm">remove</span>
                                </button>
                                <span id="party-display" class="font-bold text-lg px-4"><?php echo (int) ($_POST['party_size'] ?? 4); ?> Guests</span>
                                <button type="button" id="party-plus" class="w-8 h-8 flex items-center justify-center rounded-full bg-white dark:bg-zinc-700 shadow hover:bg-primary hover:text-white transition-colors">
                                    <span class="material-icons text-sm">add</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Time Selection -->
                    <div>
                        <label class="block text-sm font-semibold uppercase tracking-wider mb-4 text-slate-700 dark:text-slate-300 text-center">Available Time Slots</label>
                        <div class="grid grid-cols-3 md:grid-cols-6 gap-3">
                            <?php foreach ($timeSlots as $slot): ?>
                                <button type="button" data-time="<?php echo htmlspecialchars($slot['time']); ?>"
                                    class="time-slot py-3 px-2 text-sm font-bold rounded-lg transition-all
                                    <?php echo $slot['available'] ? 'border border-slate-200 dark:border-zinc-700 hover:border-primary dark:hover:border-primary' : 'opacity-50 cursor-not-allowed line-through border border-slate-200 dark:border-zinc-700'; ?>"
                                    <?php echo $slot['available'] ? '' : 'disabled'; ?>>
                                    <?php echo htmlspecialchars($slot['time']); ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" name="reservation_time" id="reservation-time-input" value="<?php echo htmlspecialchars($_POST['reservation_time'] ?? ''); ?>" required/>
                    </div>

                    <!-- Step 3: Contact Info -->
                    <div class="space-y-4 border-t border-slate-100 dark:border-zinc-800 pt-8">
                        <label class="block text-sm font-semibold uppercase tracking-wider mb-2 text-slate-700 dark:text-slate-300">Guest Information</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <input name="guest_name" type="text" placeholder="Full Name" required
                                value="<?php echo htmlspecialchars($_POST['guest_name'] ?? ''); ?>"
                                class="w-full px-4 py-3 bg-background-light dark:bg-zinc-800 border-none rounded-lg focus:ring-2 focus:ring-primary text-slate-900 dark:text-white"/>
                            <input name="guest_email" type="email" placeholder="Email Address" required
                                value="<?php echo htmlspecialchars($_POST['guest_email'] ?? ''); ?>"
                                class="w-full px-4 py-3 bg-background-light dark:bg-zinc-800 border-none rounded-lg focus:ring-2 focus:ring-primary text-slate-900 dark:text-white"/>
                        </div>
                        <input name="guest_phone" type="tel" placeholder="Phone Number" required
                            value="<?php echo htmlspecialchars($_POST['guest_phone'] ?? ''); ?>"
                            class="w-full px-4 py-3 bg-background-light dark:bg-zinc-800 border-none rounded-lg focus:ring-2 focus:ring-primary text-slate-900 dark:text-white"/>
                    </div>

                    <!-- Step 4: Special Occasion -->
                    <div class="space-y-4">
                        <label class="block text-sm font-semibold uppercase tracking-wider mb-2 text-slate-700 dark:text-slate-300">Special Requests</label>
                        <div class="flex flex-wrap gap-2 mb-4">
                            <?php
                            $occasions = ['BIRTHDAY', 'ANNIVERSARY', 'BUSINESS', 'DATE_NIGHT'];
                            $selectedOccasion = $_POST['special_occasion'] ?? '';
                            foreach ($occasions as $occ): ?>
                                <button type="button" data-occasion="<?php echo htmlspecialchars($occ); ?>"
                                    class="occasion-btn px-4 py-2 text-xs font-bold rounded-full transition-colors
                                    <?php echo $selectedOccasion === $occ ? 'bg-primary/10 text-primary border border-primary/20' : 'bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-slate-400 hover:bg-primary/10 hover:text-primary'; ?>">
                                    <?php echo htmlspecialchars($occ); ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" name="special_occasion" id="special-occasion-input" value="<?php echo htmlspecialchars($selectedOccasion); ?>"/>
                        <textarea name="notes" rows="3" placeholder="Dietary requirements or additional notes..."
                            class="w-full px-4 py-3 bg-background-light dark:bg-zinc-800 border-none rounded-lg focus:ring-2 focus:ring-primary text-slate-900 dark:text-white resize-none"><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
                    </div>

                    <!-- CTA -->
                    <div class="pt-4">
                        <button type="submit" class="w-full py-5 bg-primary hover:bg-red-700 text-white font-extrabold text-lg uppercase tracking-widest rounded-lg shadow-xl shadow-primary/20 transition-all transform hover:-translate-y-1">
                            Confirm Reservation
                        </button>
                        <p class="text-center text-xs text-slate-400 mt-4">By booking, you agree to our terms and cancellation policy.</p>
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
    var partySize = <?php echo (int) ($_POST['party_size'] ?? 4); ?>;
    var partyInput = document.getElementById('party-size-input');
    var partyDisplay = document.getElementById('party-display');
    var timeInput = document.getElementById('reservation-time-input');
    var occasionInput = document.getElementById('special-occasion-input');

    function updateParty() {
        partySize = Math.max(1, Math.min(10, partySize));
        partyInput.value = partySize;
        partyDisplay.textContent = partySize + ' Guest' + (partySize !== 1 ? 's' : '');
    }

    document.getElementById('party-minus').addEventListener('click', function() { partySize--; updateParty(); });
    document.getElementById('party-plus').addEventListener('click', function() { partySize++; updateParty(); });

    document.querySelectorAll('.time-slot').forEach(function(btn) {
        if (btn.disabled) return;
        btn.addEventListener('click', function() {
            document.querySelectorAll('.time-slot').forEach(function(b) { b.classList.remove('bg-primary', 'text-white', 'ring-2', 'ring-primary', 'shadow-lg', 'shadow-primary/30'); });
            btn.classList.add('bg-primary', 'text-white', 'ring-2', 'ring-primary', 'shadow-lg', 'shadow-primary/30');
            timeInput.value = btn.getAttribute('data-time');
        });
    });
    var preSelected = timeInput.value;
    if (preSelected) {
        var sel = document.querySelector('.time-slot[data-time="' + preSelected + '"]');
        if (sel && !sel.disabled) sel.classList.add('bg-primary', 'text-white', 'ring-2', 'ring-primary', 'shadow-lg', 'shadow-primary/30');
    }

    document.querySelectorAll('.occasion-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var occ = btn.getAttribute('data-occasion');
            document.querySelectorAll('.occasion-btn').forEach(function(b) {
                b.classList.remove('bg-primary/10', 'text-primary', 'border', 'border-primary/20');
                b.classList.add('bg-slate-100', 'dark:bg-zinc-800', 'text-slate-600', 'dark:text-slate-400');
            });
            if (occasionInput.value === occ) {
                occasionInput.value = '';
            } else {
                occasionInput.value = occ;
                btn.classList.remove('bg-slate-100', 'dark:bg-zinc-800', 'text-slate-600', 'dark:text-slate-400');
                btn.classList.add('bg-primary/10', 'text-primary', 'border', 'border-primary/20');
            }
        });
    });
    var preOcc = occasionInput.value;
    if (preOcc) {
        var occBtn = document.querySelector('.occasion-btn[data-occasion="' + preOcc + '"]');
        if (occBtn) occBtn.classList.remove('bg-slate-100', 'dark:bg-zinc-800', 'text-slate-600', 'dark:text-slate-400'), occBtn.classList.add('bg-primary/10', 'text-primary', 'border', 'border-primary/20');
    }

    document.querySelector('input[name="reservation_date"]').addEventListener('change', function() {
        window.location.href = '<?php echo htmlspecialchars($reservationUrl); ?>?date=' + encodeURIComponent(this.value);
    });
});
</script>
<?php endif; ?>
</body>
</html>
