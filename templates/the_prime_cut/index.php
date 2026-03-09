<?php
/**
 * The Prime Cut - Premium steakhouse menu (burgundy & gold)
 * Variables from loader: $restaurant, $categories, $customization, $headerMenuItems, $supportsOrdering, $supportsReservations, $isTemplatePreview
 */
$currencySymbol = '₦';
if (defined('UPLOAD_URL')) {
    $uploadBaseUrl = rtrim(UPLOAD_URL, '/');
    $baseUrl = defined('SITE_URL') ? rtrim(SITE_URL, '/') : '';
    if ($baseUrl === '') {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $baseDir = dirname(dirname(dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php')));
        $baseUrl = $protocol . $host . $baseDir;
    }
} else {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptPath = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
    $baseDir = dirname(dirname(dirname($scriptPath)));
    $uploadBaseUrl = $protocol . $host . $baseDir . '/uploads';
    $baseUrl = defined('SITE_URL') ? rtrim(SITE_URL, '/') : ($protocol . $host . $baseDir);
}
$reservationUrl = $baseUrl . '/restaurant/' . ($restaurant['slug'] ?? '') . '/reservation';
$primaryColor = isset($customization['primary_color']) ? $customization['primary_color'] : '#D4AF37';
function the_prime_cut_price($price, $symbol = '₦') {
    return $symbol . number_format((float)$price, 2);
}
$activeCategories = [];
if (!empty($categories) && is_array($categories)) {
    foreach ($categories as $cat) {
        if (!empty($cat['menu_items']) && is_array($cat['menu_items']) && !empty($cat['is_active'])) {
            $activeCategories[] = $cat;
        }
    }
}
$ornateSymbols = ['❦', '✧', '⚜', '🍷'];
?>
<!DOCTYPE html>
<html lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title><?php echo htmlspecialchars($restaurant['name']); ?></title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Bodoni+Moda:ital,opsz,wght@0,6..96,400..900;1,6..96,400..900&amp;family=Montserrat:wght@300;400;600&amp;display=swap" rel="stylesheet"/>
<script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            burgundy: '#4A0404',
            gold: '#D4AF37',
            darkwood: '#1A0F0A',
            cream: '#FDFCF0',
          },
          fontFamily: {
            serif: ['"Bodoni Moda"', 'serif'],
            sans: ['Montserrat', 'sans-serif'],
          },
        },
      },
    }
  </script>
<style data-purpose="custom-textures">body {
    background-color: #1A0F0A;
    background-image: url(https://lh3.googleusercontent.com/aida-public/AB6AXuBfDohbLB1S59o5A-LDPhD-EWKz6bpJpxo29Aky59qsM2XRmlwqkKi-oIbUg7lvUTBDyYkLAzEL0pmU0nSYnPOe_rkZAH7xQfFd8WVvf6PRa4TgsG01aVlfEAcE_A2lgprCmWm0bUOLWL2m5M7PTbaCC04yFbmqIdSgjHOhxR2UV-G9FEqcqkpdcSQeOJcUKTTiRxVkh3NPXSQnK30MHfnEkO_p9H2hK176s1Py7YCWeMB7DmNQq0ac04D-nI3TsJQzO6F3kboMwglv)
    }
.menu-page {
    background-color: #4A0404;
    background-image: url(https://lh3.googleusercontent.com/aida-public/AB6AXuBsIkU6Av4uOchJ6KqHumh9oquAiJsS-PwQnUzbjpR-u-kHo-UVLotmGg1_dKwrbgpMZ868DmTYkWZnlHKGT_BHqFUAKi-3FxzTh004YhT2X-qbN5NBH0Yl4PB5pXOWmezW2kv9csTmWN2sNUYYMf5IGVbfyYKZZR52CRVdVkVSWQ9eNHA1Z5T2KQKeaULDDZ9ciUdFC-mC8PzaZCyM9jBXwnHJR-7i-O-O8ynSK-90ajsQxakWiJGhXELz7S0YXTNeI0ADquwrYZq7);
    box-shadow: 0 0 50px rgba(0, 0, 0, 0.8)
    }
.gold-border {
    border: 2px solid #D4AF37;
    outline: 1px solid #D4AF37;
    outline-offset: 4px
    }
.ornate-divider {
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 2rem 0
    }
.ornate-divider::before, .ornate-divider::after {
    content: "";
    flex: 1;
    height: 1px;
    background: linear-gradient(to var(--direction, right), transparent, #D4AF37)
    }
.ornate-divider::after {
    --direction: left
    }
.ornate-symbol {
    margin: 0 1rem;
    color: #D4AF37;
    font-size: 1.5rem
    }</style>
</head>
<body class="min-h-screen py-12 px-4 flex justify-center items-start">
<main class="menu-page max-w-4xl w-full text-cream p-8 md:p-16 gold-border relative">
<header class="text-center mb-16" data-purpose="main-header">
<?php if (!empty($restaurant['logo']) && empty($isTemplatePreview)): ?><div class="mb-4"><img src="<?php echo $uploadBaseUrl . '/logos/' . htmlspecialchars($restaurant['logo']); ?>" alt="<?php echo htmlspecialchars($restaurant['name']); ?>" class="h-20 w-auto object-contain mx-auto"/></div><?php endif; ?>
<div class="mb-4">
<span class="text-gold tracking-[0.3em] uppercase text-sm font-sans"><?php echo !empty($restaurant['description']) ? htmlspecialchars(mb_substr($restaurant['description'], 0, 60)) : 'Established'; ?></span>
</div>
<h1 class="font-serif text-6xl md:text-7xl text-gold italic mb-2"><?php echo htmlspecialchars($restaurant['name']); ?></h1>
<p class="font-sans text-lg tracking-widest uppercase opacity-80"><?php echo !empty($restaurant['description']) ? htmlspecialchars(mb_substr($restaurant['description'], 0, 80)) : 'Premium Artisanal'; ?></p>
<?php if (!empty($supportsReservations)): ?><div class="mt-4"><a href="<?php echo htmlspecialchars($reservationUrl); ?>" class="font-sans text-sm tracking-widest uppercase text-gold border border-gold/60 px-6 py-2 hover:bg-gold/20 transition-colors">Reserve Table</a></div><?php endif; ?>
<div class="ornate-divider">
<span class="ornate-symbol">❦</span>
</div>
</header>
<?php foreach ($activeCategories as $catIndex => $category): 
    $slug = isset($category['slug']) ? $category['slug'] : ('section-' . $catIndex);
    $items = $category['menu_items'];
    if (empty($items)) continue;
    $symbol = $ornateSymbols[$catIndex % count($ornateSymbols)];
?>
<section class="mb-16" data-purpose="menu-section" id="<?php echo htmlspecialchars($slug); ?>">
<?php if ($catIndex > 0): ?>
<div class="ornate-divider">
<span class="ornate-symbol"><?php echo $symbol; ?></span>
</div>
<?php endif; ?>
<h2 class="text-center font-serif text-4xl text-gold mb-10 uppercase tracking-widest"><?php echo htmlspecialchars($category['name']); ?></h2>
<?php if ($catIndex === 1 && count($items) > 0): ?>
<div class="space-y-12">
<div class="relative p-8 border border-gold/30 bg-black/20 flex flex-col items-center text-center" data-purpose="featured-item">
<div class="absolute -top-4 left-1/2 -translate-x-1/2 bg-gold text-burgundy px-4 py-1 text-xs font-bold uppercase tracking-widest">Chef's Recommendation</div>
<?php $feat = $items[0]; ?>
<h3 class="font-serif text-3xl font-bold mb-2"><?php echo htmlspecialchars($feat['name']); ?></h3>
<p class="max-w-xl text-sm font-sans italic opacity-80 mb-4"><?php echo htmlspecialchars($feat['description'] ?? ''); ?></p>
<span class="text-gold font-serif text-2xl"><?php echo the_prime_cut_price($feat['price']); ?></span>
<?php if (!empty($supportsOrdering) && !empty($feat['is_available'])): ?><button type="button" class="add-to-bag-btn mt-3 font-sans text-sm tracking-widest uppercase text-gold border border-gold/60 px-4 py-2 hover:bg-gold/20 transition-colors" data-item-id="<?php echo (int)$feat['id']; ?>" data-item-name="<?php echo htmlspecialchars($feat['name']); ?>" data-item-price="<?php echo htmlspecialchars($feat['price']); ?>" data-item-image="<?php echo !empty($feat['image']) ? htmlspecialchars($feat['image']) : ''; ?>">Add to bag</button><?php endif; ?>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-10">
<?php for ($i = 1; $i < count($items); $i++): $item = $items[$i]; ?>
<div class="flex flex-col text-center md:text-left">
<div class="flex justify-between items-baseline mb-1">
<h3 class="font-serif text-xl font-bold"><?php echo htmlspecialchars($item['name']); ?></h3>
<span class="text-gold font-serif"><?php echo the_prime_cut_price($item['price']); ?></span>
</div>
<p class="text-sm font-sans italic opacity-75"><?php echo htmlspecialchars($item['description'] ?? ''); ?></p>
<?php if (!empty($supportsOrdering) && !empty($item['is_available'])): ?><button type="button" class="add-to-bag-btn mt-2 font-sans text-xs tracking-widest uppercase text-gold border border-gold/60 px-3 py-1.5 hover:bg-gold/20 transition-colors" data-item-id="<?php echo (int)$item['id']; ?>" data-item-name="<?php echo htmlspecialchars($item['name']); ?>" data-item-price="<?php echo htmlspecialchars($item['price']); ?>" data-item-image="<?php echo !empty($item['image']) ? htmlspecialchars($item['image']) : ''; ?>">Add to bag</button><?php endif; ?>
</div>
<?php endfor; ?>
</div>
</div>
<?php else: ?>
<div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8">
<?php foreach ($items as $item): ?>
<div class="flex flex-col text-center md:text-left">
<?php if (!empty($item['image'])): ?><div class="mb-3"><img src="<?php echo $uploadBaseUrl . '/menu-items/' . htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="w-full max-h-40 object-cover rounded border border-gold/30"/></div><?php endif; ?>
<div class="flex justify-between items-baseline mb-1">
<h3 class="font-serif text-xl font-bold"><?php echo htmlspecialchars($item['name']); ?></h3>
<span class="text-gold font-serif"><?php echo the_prime_cut_price($item['price']); ?></span>
</div>
<p class="text-sm font-sans italic opacity-75"><?php echo htmlspecialchars($item['description'] ?? ''); ?></p>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
</section>
<?php endforeach; ?>
<footer class="text-center border-t border-gold/20 pt-10" data-purpose="menu-footer">
<p class="font-serif italic text-gold text-lg mb-2"><?php echo !empty($restaurant['footer_content']) ? htmlspecialchars($restaurant['footer_content']) : '"Quality is never an accident."'; ?></p>
<div class="flex justify-center gap-6 mt-4 text-xs font-sans uppercase tracking-[0.2em] opacity-60">
<?php if (!empty($restaurant['address'])): ?><span><?php echo htmlspecialchars($restaurant['address']); ?></span><span>•</span><?php endif; ?>
<?php if (!empty($restaurant['phone'])): ?><span><?php echo htmlspecialchars($restaurant['phone']); ?></span><?php endif; ?>
</div>
</footer>
</main>
<?php if (!empty($supportsOrdering)): ?>
<div id="resmenu-cart-widget" class="fixed bottom-6 left-6 z-50 hidden"></div>
<script src="<?php echo rtrim(defined('SITE_URL') ? SITE_URL : $baseUrl, '/'); ?>/assets/js/cart.js"></script>
<script src="<?php echo rtrim(defined('SITE_URL') ? SITE_URL : $baseUrl, '/'); ?>/assets/js/cart-widget.js"></script>
<script src="<?php echo rtrim(defined('SITE_URL') ? SITE_URL : $baseUrl, '/'); ?>/assets/js/cart-modal.js"></script>
<script>
(function() {
    var baseUrl = <?php echo json_encode(defined('SITE_URL') ? rtrim(SITE_URL, '/') : $baseUrl); ?>;
    var slug = <?php echo json_encode($restaurant['slug'] ?? ''); ?>;
    var config = { restaurantSlug: slug, currencySymbol: <?php echo json_encode($currencySymbol); ?>, uploadBaseUrl: <?php echo json_encode($uploadBaseUrl ?? ''); ?>, checkoutUrl: baseUrl + '/restaurant/' + slug + '/checkout', primaryColor: <?php echo json_encode($primaryColor ?? '#D4AF37'); ?>, deliveryFee: 0, taxRate: 0 };
    window.RESMENU_CART_CONFIG = config;
    if (window.RESMENU_CART_MODAL) window.RESMENU_CART_MODAL.init(config);
    if (window.RESMENU_CART_WIDGET) window.RESMENU_CART_WIDGET.init(config);
    document.querySelectorAll('.add-to-bag-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id = this.getAttribute('data-item-id'), name = this.getAttribute('data-item-name'), price = this.getAttribute('data-item-price'), image = this.getAttribute('data-item-image') || '';
            if (window.RESMENU_CART) window.RESMENU_CART.addItem(slug, { id: id, name: name, price: price, image: image }, 1);
        });
    });
})();
</script>
<?php endif; ?>
</body></html>
