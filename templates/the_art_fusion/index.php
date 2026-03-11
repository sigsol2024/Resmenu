<?php
/**
 * The Art Fusion - Zen Japanese fusion minimalist
 */
if (defined('UPLOAD_URL')) { $uploadBaseUrl = rtrim(UPLOAD_URL, '/'); } else {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $uploadBaseUrl = $protocol . ($_SERVER['HTTP_HOST'] ?? 'localhost') . (dirname(dirname(dirname($_SERVER['SCRIPT_NAME'] ?? ''))) ?: '') . '/uploads';
}
$baseUrl = defined('SITE_URL') ? rtrim(SITE_URL, '/') : '';
$reservationUrl = $baseUrl . '/restaurant/' . ($restaurant['slug'] ?? '') . '/reservation';
$currencySymbol = '₦';
$primaryColor = isset($customization['primary_color']) ? $customization['primary_color'] : '#bc002d';
function taf_price($p, $s = '₦') { return $s . number_format((float)$p, 2); }
$activeCategories = [];
if (!empty($categories) && is_array($categories)) {
    foreach ($categories as $c) {
        if (!empty($c['menu_items']) && is_array($c['menu_items']) && !empty($c['is_active'])) $activeCategories[] = $c;
    }
}
?>
<!DOCTYPE html>
<html lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title><?php echo htmlspecialchars($restaurant['name']); ?></title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<style>@import url('https://fonts.googleapis.com/css2?family=Bodoni+Moda:ital,opsz,wght@0,6..96,400;0,6..96,700;1,6..96,400&family=Noto+Sans+JP:wght@300;400;500&display=swap'); body { font-family: 'Noto Sans JP', sans-serif; background-color: #ffffff; color: #1a1a1a; } h1, h2, h3, .serif-font { font-family: 'Bodoni Moda', serif; } .zen-divider { height: 1px; background-color: #e5e5e5; width: 100%; margin: 2rem 0; } .accent-red { color: #bc002d; } .hide-scrollbar::-webkit-scrollbar { display: none; } .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; } .menu-section { scroll-margin-top: 100px; }</style>
</head>
<body class="antialiased">
<header class="fixed top-0 left-0 right-0 z-50 bg-white/90 backdrop-blur-sm border-b border-gray-100">
<div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
<div class="flex-shrink-0 flex items-center gap-3">
<?php if (!empty($restaurant['logo']) && empty($isTemplatePreview)): ?><img src="<?php echo $uploadBaseUrl . '/logos/' . htmlspecialchars($restaurant['logo']); ?>" alt="<?php echo htmlspecialchars($restaurant['name']); ?>" class="h-10 w-auto object-contain"/><?php endif; ?>
<h1 class="text-2xl font-bold tracking-[0.3em] uppercase"><?php echo htmlspecialchars(mb_substr($restaurant['name'], 0, 20)); ?></h1>
</div>
<nav class="flex overflow-x-auto hide-scrollbar space-x-8 text-sm uppercase tracking-widest font-medium">
<?php if (!empty($supportsReservations)): ?><a class="hover:text-[#bc002d] transition-colors whitespace-nowrap py-2 border-b-2 border-transparent hover:border-[#bc002d]" href="<?php echo htmlspecialchars($reservationUrl); ?>">Reserve Table</a><?php endif; ?>
<?php foreach ($activeCategories as $i => $cat): $s = isset($cat['slug']) ? $cat['slug'] : ('section-'.$i); ?>
<a class="hover:text-[#bc002d] transition-colors whitespace-nowrap py-2 border-b-2 border-transparent hover:border-[#bc002d]" href="#<?php echo htmlspecialchars($s); ?>"><?php echo htmlspecialchars($cat['name']); ?></a>
<?php endforeach; ?>
</nav>
</div>
</header>
<main class="pt-20">
<section class="relative h-[70vh] flex items-center justify-center overflow-hidden bg-gray-50">
<div class="z-10 text-center px-4">
<span class="text-xs uppercase tracking-[0.5em] mb-4 block"><?php echo htmlspecialchars($restaurant['description'] ?? 'Crafting Balance'); ?></span>
<h2 class="text-5xl md:text-7xl font-light mb-6 italic"><?php echo htmlspecialchars($restaurant['name']); ?></h2>
<div class="w-12 h-px bg-black mx-auto"></div>
</div>
<?php if (!empty($restaurant['hero_image']) && empty($isTemplatePreview)): ?>
<img alt="" class="absolute inset-0 w-full h-full object-cover opacity-80" src="<?php echo $uploadBaseUrl . '/heroes/' . htmlspecialchars($restaurant['hero_image']); ?>"/>
<?php else: ?>
<img alt="Zen Interior" class="absolute inset-0 w-full h-full object-cover opacity-80" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAk14in8WcP48BdHZuySC634eIGIRiALxRkUfbVkrJcIRS3dXUqmY2gKlDeEvji5Alw7DN3zJQmePUjDq6fu-6HNbAFYq0gLIHW3l6-LQiq1StCU2j0zTOvrvs4Jf_dN1fFwK8cbERicdHftKKuNYrWX3eBL1w_SVxbfdaWLGcLg_DY3OufFYGa7LCU-NUc2L8-HJmr6ipY9uZKklqSWQzWsJ8UPcrEWvMVTaehUU9diPCOOodb8eo_RRDAK3m569RIPKeEN_QJaa83"/>
<?php endif; ?>
</section>
<?php foreach ($activeCategories as $catIndex => $category): 
    $slug = isset($category['slug']) ? $category['slug'] : ('section-'.$catIndex);
    $items = $category['menu_items'];
    if (empty($items)) continue;
?>
<section class="menu-section py-24 max-w-7xl mx-auto px-6" id="<?php echo htmlspecialchars($slug); ?>">
<div class="grid grid-cols-1 md:grid-cols-2 gap-x-20 gap-y-12">
<?php foreach ($items as $item): ?>
<div class="flex gap-4 justify-between border-b border-gray-100 pb-4 items-start">
<?php if (!empty($item['image'])): ?><img src="<?php echo $uploadBaseUrl . '/menu-items/' . htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="w-20 h-20 flex-shrink-0 object-cover rounded"/><?php endif; ?>
<div class="flex-1 min-w-0">
<h4 class="text-lg uppercase tracking-wider mb-1"><?php echo htmlspecialchars($item['name']); ?></h4>
<p class="text-sm text-gray-400 italic"><?php echo htmlspecialchars($item['description'] ?? ''); ?></p>
<?php if (!empty($supportsOrdering) && !empty($item['is_available'])): ?><button type="button" class="add-to-bag-btn mt-2 text-xs uppercase tracking-wider text-[#bc002d] border border-[#bc002d] px-3 py-1.5 hover:bg-[#bc002d] hover:text-white" data-item-id="<?php echo (int)$item['id']; ?>" data-item-name="<?php echo htmlspecialchars($item['name']); ?>" data-item-price="<?php echo htmlspecialchars($item['price']); ?>" data-item-image="<?php echo !empty($item['image']) ? htmlspecialchars($item['image']) : ''; ?>">Add to bag</button><?php endif; ?>
</div>
<span class="font-medium flex-shrink-0"><?php echo taf_price($item['price']); ?></span>
</div>
<?php endforeach; ?>
</div>
</section>
<?php if ($catIndex < count($activeCategories) - 1): ?><div class="zen-divider max-w-2xl mx-auto"></div><?php endif; ?>
<?php endforeach; ?>
</main>
<footer class="bg-white py-20 border-t border-gray-100">
<div class="max-w-7xl mx-auto px-6 flex flex-col items-center text-center">
<h2 class="text-2xl font-bold tracking-[0.5em] uppercase mb-8"><?php echo htmlspecialchars($restaurant['name']); ?></h2>
<?php if (!empty($restaurant['footer_content'])): ?><p class="text-sm text-gray-500 max-w-sm mb-4"><?php echo nl2br(htmlspecialchars($restaurant['footer_content'])); ?></p><?php endif; ?>
<p class="text-sm text-gray-400 max-w-sm mb-12"><?php echo nl2br(htmlspecialchars($restaurant['address'] ?? '123 Minimalist Way')); ?><br/><?php echo htmlspecialchars($restaurant['opening_hours'] ?? 'Mon – Sun'); ?></p>
<div class="flex space-x-12 text-xs uppercase tracking-widest border-t border-gray-100 pt-12 w-full justify-center">
<?php if (!empty($restaurant['instagram_url'])): ?><a class="hover:text-[#bc002d]" href="<?php echo htmlspecialchars($restaurant['instagram_url']); ?>">Instagram</a><?php endif; ?>
<?php if (!empty($restaurant['facebook_url'])): ?><a class="hover:text-[#bc002d]" href="<?php echo htmlspecialchars($restaurant['facebook_url']); ?>">Facebook</a><?php endif; ?>
</div>
</div>
</footer>
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
