<?php
/**
 * Street Food Hub - Vibrant street food brutalism
 */
if (defined('UPLOAD_URL')) { $uploadBaseUrl = rtrim(UPLOAD_URL, '/'); } else {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $uploadBaseUrl = $protocol . ($_SERVER['HTTP_HOST'] ?? 'localhost') . (dirname(dirname(dirname($_SERVER['SCRIPT_NAME'] ?? ''))) ?: '') . '/uploads';
}
$baseUrl = defined('SITE_URL') ? rtrim(SITE_URL, '/') : '';
$reservationUrl = $baseUrl . '/restaurant/' . ($restaurant['slug'] ?? '') . '/reservation';
$currencySymbol = '₦';
$primaryColor = isset($customization['primary_color']) ? $customization['primary_color'] : '#FFD700';
function sfh_price($p, $s = '₦') { return $s . number_format((float)$p, 2); }
$activeCategories = [];
if (!empty($categories) && is_array($categories)) {
    foreach ($categories as $c) {
        if (!empty($c['menu_items']) && is_array($c['menu_items']) && !empty($c['is_active'])) $activeCategories[] = $c;
    }
}
$masonryClasses = ['masonry-item-sm', 'masonry-item-md', 'masonry-item-lg'];
?>
<!DOCTYPE html>
<html lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title><?php echo htmlspecialchars($restaurant['name']); ?></title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Bungee&amp;family=Inter:wght@400;700;900&amp;display=swap" rel="stylesheet"/>
<script>
    tailwind.config = { theme: { extend: { colors: { brandYellow: '#FFD700', brandBlack: '#1A1A1A' }, fontFamily: { chunky: ['Bungee', 'cursive'], sans: ['Inter', 'sans-serif'] }, boxShadow: { brutal: '8px 8px 0px 0px #1A1A1A', 'brutal-sm': '4px 4px 0px 0px #1A1A1A' } } } }
  </script>
<style>.comic-border { border: 4px solid #1A1A1A; } .masonry-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; } .masonry-item-sm { grid-row-end: span 35; } .masonry-item-md { grid-row-end: span 45; } .masonry-item-lg { grid-row-end: span 55; } @keyframes wiggle { 0%, 100% { transform: rotate(-1deg); } 50% { transform: rotate(1deg); } } .animate-wiggle { animation: wiggle 2s infinite ease-in-out; }</style>
</head>
<body class="bg-brandYellow text-brandBlack font-sans antialiased p-4 md:p-8">
<header class="max-w-7xl mx-auto mb-12 text-center" data-purpose="page-header">
<?php if (!empty($restaurant['logo']) && empty($isTemplatePreview)): ?><div class="mb-4"><img src="<?php echo $uploadBaseUrl . '/logos/' . htmlspecialchars($restaurant['logo']); ?>" alt="<?php echo htmlspecialchars($restaurant['name']); ?>" class="h-20 w-auto object-contain mx-auto"/></div><?php endif; ?>
<div class="inline-block bg-brandBlack text-white p-6 comic-border shadow-brutal -rotate-2 mb-6">
<h1 class="font-chunky text-5xl md:text-7xl uppercase tracking-tighter"><?php echo htmlspecialchars($restaurant['name']); ?></h1>
</div>
<p class="font-bold text-xl md:text-2xl uppercase italic"><?php echo htmlspecialchars($restaurant['description'] ?? 'Vibrant. Loud. Delicious.'); ?></p>
</header>
<nav class="max-w-7xl mx-auto mb-16 flex flex-wrap justify-center gap-4" data-purpose="category-navigation">
<?php if (!empty($supportsReservations)): ?><a class="bg-white comic-border px-6 py-2 font-chunky hover:bg-brandBlack hover:text-white transition-colors shadow-brutal-sm" href="<?php echo htmlspecialchars($reservationUrl); ?>">Reserve Table</a><?php endif; ?>
<?php foreach ($activeCategories as $i => $cat): $s = isset($cat['slug']) ? $cat['slug'] : ('section-'.$i); ?>
<a class="bg-white comic-border px-6 py-2 font-chunky hover:bg-brandBlack hover:text-white transition-colors shadow-brutal-sm" href="#<?php echo htmlspecialchars($s); ?>"><?php echo htmlspecialchars($cat['name']); ?></a>
<?php endforeach; ?>
</nav>
<main class="max-w-7xl mx-auto">
<div class="masonry-grid">
<?php foreach ($activeCategories as $catIndex => $category): 
    $slug = isset($category['slug']) ? $category['slug'] : ('section-'.$catIndex);
    $items = $category['menu_items'];
    if (empty($items)) continue;
    foreach ($items as $itemIndex => $item): 
        $masonry = $masonryClasses[$itemIndex % 3];
        $imgUrl = !empty($item['image']) ? $uploadBaseUrl . '/menu-items/' . htmlspecialchars($item['image']) : '';
?>
<article class="<?php echo $masonry; ?> bg-white comic-border p-6 shadow-brutal flex flex-col relative overflow-hidden group" data-purpose="menu-item" id="<?php echo $itemIndex === 0 ? $slug : ''; ?>">
<div class="absolute -top-2 -right-2 bg-brandBlack text-white px-4 py-2 font-chunky text-xl comic-border z-10 <?php echo $itemIndex === 0 ? 'animate-wiggle' : ''; ?>"><?php echo sfh_price($item['price']); ?></div>
<?php if ($imgUrl): ?><img alt="<?php echo htmlspecialchars($item['name']); ?>" class="w-full h-48 object-cover comic-border mb-4 grayscale group-hover:grayscale-0 transition-all duration-300" src="<?php echo $imgUrl; ?>"/><?php endif; ?>
<h3 class="font-chunky text-2xl mb-2"><?php echo htmlspecialchars($item['name']); ?></h3>
<p class="text-sm font-bold flex-grow"><?php echo htmlspecialchars($item['description'] ?? ''); ?></p>
<?php if (!empty($supportsOrdering) && !empty($item['is_available'])): ?><button type="button" class="add-to-bag-btn mt-3 comic-border px-4 py-2 font-chunky bg-brandBlack text-white hover:bg-white hover:text-brandBlack transition-colors" data-item-id="<?php echo (int)$item['id']; ?>" data-item-name="<?php echo htmlspecialchars($item['name']); ?>" data-item-price="<?php echo htmlspecialchars($item['price']); ?>" data-item-image="<?php echo !empty($item['image']) ? htmlspecialchars($item['image']) : ''; ?>">Add to bag</button><?php endif; ?>
<div class="mt-4 border-t-2 border-brandBlack pt-2 italic text-xs uppercase font-black"><?php echo htmlspecialchars($category['name']); ?></div>
</article>
<?php endforeach; endforeach; ?>
</div>
</main>
<footer class="max-w-7xl mx-auto mt-20 mb-10 text-center" data-purpose="footer">
<div class="bg-brandBlack text-white p-8 comic-border shadow-brutal">
<h2 class="font-chunky text-3xl mb-4"><?php echo htmlspecialchars($restaurant['name']); ?></h2>
<?php if (!empty($restaurant['footer_content'])): ?><p class="font-bold mb-4"><?php echo nl2br(htmlspecialchars($restaurant['footer_content'])); ?></p><?php endif; ?>
<p class="font-bold mb-6"><?php echo htmlspecialchars($restaurant['address'] ?? 'Find us'); ?></p>
<div class="flex justify-center gap-6">
<?php if (!empty($restaurant['instagram_url'])): ?><a class="comic-border p-2 bg-brandYellow text-brandBlack font-black cursor-pointer hover:bg-white transition-colors" href="<?php echo htmlspecialchars($restaurant['instagram_url']); ?>">INSTA</a><?php endif; ?>
<?php if (!empty($restaurant['website'])): ?><a class="comic-border p-2 bg-brandYellow text-brandBlack font-black cursor-pointer hover:bg-white transition-colors" href="<?php echo htmlspecialchars($restaurant['website']); ?>">WEB</a><?php endif; ?>
</div>
</div>
</footer>
<?php if (!empty($supportsOrdering)): ?>
<div id="resmenu-cart-widget" class="fixed bottom-6 left-6 z-50 hidden"></div>
<script src="<?php echo rtrim(defined('SITE_URL') ? SITE_URL : '', '/'); ?>/assets/js/cart.js"></script>
<script src="<?php echo rtrim(defined('SITE_URL') ? SITE_URL : '', '/'); ?>/assets/js/cart-widget.js"></script>
<script src="<?php echo rtrim(defined('SITE_URL') ? SITE_URL : '', '/'); ?>/assets/js/cart-modal.js"></script>
<script>
(function(){var baseUrl=<?php echo json_encode($baseUrl); ?>;var slug=<?php echo json_encode($restaurant['slug']??''); ?>;var config={restaurantSlug:slug,currencySymbol:<?php echo json_encode($currencySymbol); ?>,uploadBaseUrl:<?php echo json_encode($uploadBaseUrl??''); ?>,checkoutUrl:baseUrl+'/restaurant/'+slug+'/checkout',primaryColor:<?php echo json_encode($primaryColor); ?>,deliveryFee:0,taxRate:0};window.RESMENU_CART_CONFIG=config;if(window.RESMENU_CART_MODAL)window.RESMENU_CART_MODAL.init(config);if(window.RESMENU_CART_WIDGET)window.RESMENU_CART_WIDGET.init(config);document.querySelectorAll('.add-to-bag-btn').forEach(function(btn){btn.addEventListener('click',function(){var id=this.getAttribute('data-item-id'),name=this.getAttribute('data-item-name'),price=this.getAttribute('data-item-price'),image=this.getAttribute('data-item-image')||'';if(window.RESMENU_CART)window.RESMENU_CART.addItem(slug,{id:id,name:name,price:price,image:image},1);});});})();
</script>
<?php endif; ?>
</body></html>
