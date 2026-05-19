<?php
/**
 * Bold Flavours - Neon bistro urban dining
 */
if (defined('UPLOAD_URL')) { $uploadBaseUrl = rtrim(UPLOAD_URL, '/'); } else {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $uploadBaseUrl = $protocol . ($_SERVER['HTTP_HOST'] ?? 'localhost') . (dirname(dirname(dirname($_SERVER['SCRIPT_NAME'] ?? ''))) ?: '') . '/uploads';
}
$baseUrl = defined('SITE_URL') ? rtrim(SITE_URL, '/') : '';
$reservationUrl = $baseUrl . '/restaurant/' . ($restaurant['slug'] ?? '') . '/reservation';
$currencySymbol = '₦';
$primaryColor = isset($customization['primary_color']) ? $customization['primary_color'] : '#ff007f';
function bf_price($p, $s = '₦') {
    return formatPrice($p, $s);
}
$activeCategories = [];
if (!empty($sections) && is_array($sections)) {
    foreach ($sections as $sec) {
        if (empty($sec['categories']) || !is_array($sec['categories'])) continue;
        foreach ($sec['categories'] as $c) {
            if (!empty($c['menu_items']) && is_array($c['menu_items']) && !empty($c['is_active'])) $activeCategories[] = $c;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title><?php echo htmlspecialchars($restaurant['name']); ?><?php if (!empty($singleSectionView) && !empty($sections[0]['name'])): ?> - <?php echo htmlspecialchars($sections[0]['name']); ?><?php endif; ?></title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script>
    tailwind.config = { theme: { extend: { colors: { obsidian: '#0a0a0a', neonPink: '#ff007f', neonBlue: '#00f2ff' }, fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] } } } }
  </script>
<style>.neon-border-pink { border: 1px solid rgba(255, 0, 127, 0.5); } .neon-border-pink:hover { border-color: #ff007f; } .hide-scrollbar::-webkit-scrollbar { display: none; } .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }</style>
</head>
<body class="bg-obsidian text-white font-sans antialiased selection:bg-neonPink selection:text-white">
<div class="flex min-h-screen">
<aside class="w-1/4 lg:w-1/5 h-screen sticky top-0 border-r border-white/10 bg-black flex flex-col justify-between p-8">
<div>
<?php if (!empty($restaurant['logo']) && empty($isTemplatePreview)): ?><div class="mb-6"><img src="<?php echo $uploadBaseUrl . '/logos/' . htmlspecialchars($restaurant['logo']); ?>" alt="<?php echo htmlspecialchars($restaurant['name']); ?>" class="h-14 w-auto object-contain"/></div><?php endif; ?>
<h1 class="text-3xl font-black tracking-tighter italic text-neonPink mb-12"><?php echo htmlspecialchars(mb_substr($restaurant['name'], 0, 12)); ?><br/><span class="text-neonBlue"><?php echo htmlspecialchars(mb_substr($restaurant['name'], 12, 20) ?: 'Menu'); ?></span></h1>
<nav class="space-y-6">
<?php if (!empty($singleSectionView) && !empty($fullMenuUrl)): ?>
<a href="<?php echo htmlspecialchars($fullMenuUrl); ?>" class="block text-xl font-bold hover:text-neonPink transition-colors duration-300 group">Full menu</a>
<?php endif; ?>
<?php if (!empty($fullMenuUrl)): ?>
<a href="<?php echo htmlspecialchars($fullMenuUrl); ?>#menu" class="block text-xl font-bold hover:text-neonPink transition-colors duration-300 group">View menu</a>
<?php endif; ?>
<?php if (!empty($sectionsForNav) && is_array($sectionsForNav)): ?>
<?php foreach ($sectionsForNav as $navSection): ?>
<a href="<?php echo htmlspecialchars($fullMenuUrl); ?>#section-<?php echo htmlspecialchars($navSection['slug'] ?? ''); ?>" class="block text-xl font-bold hover:text-neonPink transition-colors duration-300 group"><?php echo htmlspecialchars($navSection['name'] ?? ''); ?></a>
<?php endforeach; ?>
<?php endif; ?>
<hr class="border-white/20 my-4" aria-hidden="true" />
<?php foreach ($activeCategories as $i => $cat): $s = isset($cat['slug']) ? $cat['slug'] : ('section-'.$i); ?>
<a class="block text-xl font-bold hover:text-neonPink transition-colors duration-300 group" href="<?php echo htmlspecialchars(!empty($fullMenuUrl) ? ((!empty($singleSectionView) && !empty($sections) && is_array($sections) && !empty($sections[0]['slug'])) ? $fullMenuUrl . '/' . $sections[0]['slug'] . '#' . $s : $fullMenuUrl . '#' . $s) : '#' . $s); ?>"><span class="text-xs mr-2 opacity-50 group-hover:text-neonPink"><?php echo str_pad((string)($i+1), 2, '0', STR_PAD_LEFT); ?></span> <?php echo strtoupper(htmlspecialchars($cat['name'])); ?></a>
<?php endforeach; ?>
<hr class="border-white/20 my-4" aria-hidden="true" />
<?php if (!empty($supportsReservations)): ?>
<a href="<?php echo htmlspecialchars($reservationUrl); ?>" class="block text-xl font-bold hover:text-neonPink border border-neonPink/50 px-4 py-2 rounded hover:bg-neonPink/10 transition-colors text-center">Reserve Table</a>
<?php endif; ?>
</nav>
</div>
<div class="text-xs text-gray-500 uppercase tracking-widest"><?php echo htmlspecialchars(!empty($restaurant['footer_content']) ? $restaurant['footer_content'] : ($restaurant['address'] ?? '')); ?></div>
</aside>
<main class="flex-1 p-8 lg:p-16 overflow-y-auto" id="menu">
<header class="mb-24">
<span class="text-neonPink font-mono text-sm tracking-widest uppercase mb-4 block"><?php echo htmlspecialchars($restaurant['description'] ?? 'Urban'); ?></span>
<h2 class="text-7xl lg:text-8xl font-black italic uppercase leading-none tracking-tighter"><?php echo htmlspecialchars($restaurant['name']); ?></h2>
</header>
<?php foreach ($sections as $section): 
    if (empty($section['categories']) || !is_array($section['categories'])) continue;
?>
<div id="section-<?php echo htmlspecialchars($section['slug']); ?>" class="mb-14">
<h2 class="text-2xl md:text-3xl font-bold text-neonPink mb-8 border-b border-white/20 pb-4 text-center"><?php if (!empty($fullMenuUrl) && empty($singleSectionView)): ?><a href="<?php echo htmlspecialchars($fullMenuUrl . '/' . $section['slug']); ?>" class="hover:underline"><?php echo htmlspecialchars($section['name']); ?></a><?php else: ?><?php echo htmlspecialchars($section['name']); ?><?php endif; ?></h2>
<?php foreach ($section['categories'] as $catIndex => $category): 
    $slug = isset($category['slug']) ? $category['slug'] : ('cat-'.$catIndex);
    $items = isset($category['menu_items']) ? $category['menu_items'] : [];
    if (empty($items)) continue;
?>
<section class="mb-24" id="<?php echo htmlspecialchars($slug); ?>">
<h3 class="text-2xl font-bold text-neonPink mb-8 border-b border-white/20 pb-4"><?php echo htmlspecialchars($category['name']); ?></h3>
<div class="grid grid-cols-1 md:grid-cols-2 gap-12">
<?php foreach ($items as $item): ?>
<div class="neon-border-pink p-6 rounded-lg hover:bg-white/5 transition-colors">
<?php if (!empty($item['image'])): ?><img src="<?php echo $uploadBaseUrl . '/menu-items/' . htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="w-full h-40 object-cover rounded mb-4"/><?php endif; ?>
<div class="flex justify-between items-baseline mb-2">
<h4 class="text-xl font-bold"><?php echo htmlspecialchars($item['name']); ?></h4>
<span class="text-neonBlue font-mono"><?php echo bf_price($item['price']); ?></span>
</div>
<p class="text-sm text-gray-400"><?php echo htmlspecialchars($item['description'] ?? ''); ?></p>
</div>
<?php endforeach; ?>
</div>
</section>
<?php endforeach; ?>
</div>
<?php endforeach; ?>
</main>
</div>
<?php if (!empty($supportsOrdering)): ?>
<link rel="stylesheet" href="<?php echo rtrim(defined('SITE_URL') ? SITE_URL : '', '/'); ?>/assets/css/cart-modal.css">
<div id="resmenu-cart-widget" class="fixed bottom-6 left-6 z-50 hidden"></div>
<script src="<?php echo rtrim(defined('SITE_URL') ? SITE_URL : '', '/'); ?>/assets/js/cart.js"></script>
<script src="<?php echo rtrim(defined('SITE_URL') ? SITE_URL : '', '/'); ?>/assets/js/cart-widget.js"></script>
<script src="<?php echo rtrim(defined('SITE_URL') ? SITE_URL : '', '/'); ?>/assets/js/cart-modal.js"></script>
<script>
(function(){var baseUrl=<?php echo json_encode($baseUrl); ?>;var slug=<?php echo json_encode($restaurant['slug']??''); ?>;var config={restaurantSlug:slug,currencySymbol:<?php echo json_encode($currencySymbol); ?>,uploadBaseUrl:<?php echo json_encode($uploadBaseUrl??''); ?>,checkoutUrl:baseUrl+'/restaurant/'+slug+'/checkout',primaryColor:<?php echo json_encode($primaryColor); ?>,deliveryFee:0,taxRate:0};window.RESMENU_CART_CONFIG=config;if(window.RESMENU_CART_MODAL)window.RESMENU_CART_MODAL.init(config);if(window.RESMENU_CART_WIDGET)window.RESMENU_CART_WIDGET.init(config);document.querySelectorAll('.add-to-bag-btn').forEach(function(btn){btn.addEventListener('click',function(){var id=this.getAttribute('data-item-id'),name=this.getAttribute('data-item-name'),price=this.getAttribute('data-item-price'),image=this.getAttribute('data-item-image')||'';if(window.RESMENU_CART)window.RESMENU_CART.addItem(slug,{id:id,name:name,price:price,image:image},1);});});})();
</script>
<?php endif; ?>
<!-- Back to top -->
<a id="scrollToTop" href="#" aria-label="Scroll to top" style="position:fixed;bottom:24px;right:24px;z-index:30;width:48px;height:48px;display:flex;align-items:center;justify-content:center;border-radius:50%;background:#111;color:#fff;opacity:0;visibility:hidden;transform:translateY(10px);transition:opacity 0.3s,visibility 0.3s,transform 0.3s;box-shadow:0 4px 12px rgba(0,0,0,0.3);">
  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 15l-6-6-6 6"/></svg>
</a>
<script>
(function(){var btn=document.getElementById('scrollToTop');if(btn){window.addEventListener('scroll',function(){var st=window.pageYOffset||document.documentElement.scrollTop;var dh=document.documentElement.scrollHeight-window.innerHeight;if(dh>0&&st>=dh*0.3){btn.style.opacity='1';btn.style.visibility='visible';btn.style.transform='translateY(0)';}else{btn.style.opacity='0';btn.style.visibility='hidden';btn.style.transform='translateY(10px)';}});btn.addEventListener('click',function(e){e.preventDefault();window.scrollTo({top:0,behavior:'smooth'});});}})();
</script>
</body></html>
