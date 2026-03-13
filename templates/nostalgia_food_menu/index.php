<?php
/**
 * Nostalgia Food Menu - Full menu in nostalgia style
 */
if (defined('UPLOAD_URL')) { $uploadBaseUrl = rtrim(UPLOAD_URL, '/'); } else {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $uploadBaseUrl = $protocol . ($_SERVER['HTTP_HOST'] ?? 'localhost') . (dirname(dirname(dirname($_SERVER['SCRIPT_NAME'] ?? ''))) ?: '') . '/uploads';
}
$baseUrl = defined('SITE_URL') ? rtrim(SITE_URL, '/') : '';
$reservationUrl = $baseUrl . '/restaurant/' . ($restaurant['slug'] ?? '') . '/reservation';
$currencySymbol = '₦';
$primaryColor = isset($customization['primary_color']) ? $customization['primary_color'] : '#f2b90d';
function nfm_price($p, $s = '₦') { return $s . number_format((float)$p, 2); }
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
<title><?php echo htmlspecialchars($restaurant['name']); ?><?php if (!empty($singleSectionView) && !empty($sections[0]['name'])): ?> - <?php echo htmlspecialchars($sections[0]['name']); ?><?php else: ?> - Menu<?php endif; ?></title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700&amp;family=Montserrat:wght@300;400;600&amp;display=swap" rel="stylesheet"/>
<script>
    tailwind.config = { theme: { extend: { colors: { brandGold: '#f2b90d', darkEbony: '#0a0a0a' }, fontFamily: { serif: ['Cinzel', 'serif'], sans: ['Montserrat', 'sans-serif'] } } } }
  </script>
<style>body { background: linear-gradient(180deg, #1a1a1a 0%, #000000 100%); background-attachment: fixed; color: #e5e5e5; } .card-border { border: 2px solid #f2b90d; } .divider-line { height: 1px; background: linear-gradient(90deg, transparent 0%, #f2b90d 50%, transparent 100%); margin: 1.5rem 0; }</style>
</head>
<body class="min-h-screen p-6 md:p-12 font-sans">
<main class="max-w-4xl mx-auto">
<header class="text-center mb-16">
<?php if (!empty($restaurant['logo']) && empty($isTemplatePreview)): ?><div class="mb-4"><img src="<?php echo $uploadBaseUrl . '/logos/' . htmlspecialchars($restaurant['logo']); ?>" alt="<?php echo htmlspecialchars($restaurant['name']); ?>" class="h-20 w-auto object-contain mx-auto"/></div><?php endif; ?>
<h1 class="text-4xl md:text-6xl font-serif text-brandGold tracking-widest uppercase mb-4"><?php echo htmlspecialchars($restaurant['name']); ?></h1>
<div class="divider-line max-w-xs mx-auto"></div>
<p class="text-gray-400 italic"><?php echo htmlspecialchars($restaurant['description'] ?? 'Our Menu'); ?></p>
<?php if (!empty($singleSectionView) && !empty($fullMenuUrl)): ?><p class="mt-2"><a href="<?php echo htmlspecialchars($fullMenuUrl); ?>" class="text-brandGold hover:underline">Full menu</a></p><?php endif; ?>
<?php if (!empty($supportsReservations)): ?><p class="mt-2"><a href="<?php echo htmlspecialchars($reservationUrl); ?>" class="text-brandGold hover:underline">Reserve Table</a></p><?php endif; ?>
</header>
<?php foreach ($sections as $section): 
    if (empty($section['categories']) || !is_array($section['categories'])) continue;
?>
<div id="section-<?php echo htmlspecialchars($section['slug']); ?>" class="mb-14">
<h2 class="text-2xl md:text-3xl font-serif font-bold text-brandGold uppercase tracking-widest mb-8 text-center"><?php if (!empty($fullMenuUrl) && empty($singleSectionView)): ?><a href="<?php echo htmlspecialchars($fullMenuUrl . '/' . $section['slug']); ?>" class="hover:underline text-brandGold"><?php echo htmlspecialchars($section['name']); ?></a><?php else: ?><?php echo htmlspecialchars($section['name']); ?><?php endif; ?></h2>
<?php foreach ($section['categories'] as $catIndex => $category): 
    $slug = isset($category['slug']) ? $category['slug'] : ('cat-'.$catIndex);
    $items = isset($category['menu_items']) ? $category['menu_items'] : [];
    if (empty($items)) continue;
?>
<section class="mb-16 card-border p-8 bg-black/40 backdrop-blur-sm" id="<?php echo htmlspecialchars($slug); ?>">
<h3 class="text-2xl font-serif text-brandGold uppercase tracking-widest mb-8 border-b border-brandGold/50 pb-4"><?php echo htmlspecialchars($category['name']); ?></h3>
<div class="space-y-6">
<?php foreach ($items as $item): ?>
<div class="flex gap-4 items-start">
<?php if (!empty($item['image'])): ?><img src="<?php echo $uploadBaseUrl . '/menu-items/' . htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="w-20 h-20 flex-shrink-0 object-cover rounded"/><?php endif; ?>
<div class="flex-1 min-w-0 flex justify-between items-baseline gap-4">
<div>
<h3 class="text-xl font-semibold text-white"><?php echo htmlspecialchars($item['name']); ?></h3>
<p class="text-sm text-gray-400 mt-1"><?php echo htmlspecialchars($item['description'] ?? ''); ?></p>
<?php if (!empty($supportsOrdering) && !empty($item['is_available'])): ?><button type="button" class="add-to-bag-btn mt-2 text-brandGold border border-brandGold px-3 py-1.5 rounded hover:bg-brandGold hover:text-black" data-item-id="<?php echo (int)$item['id']; ?>" data-item-name="<?php echo htmlspecialchars($item['name']); ?>" data-item-price="<?php echo htmlspecialchars($item['price']); ?>" data-item-image="<?php echo !empty($item['image']) ? htmlspecialchars($item['image']) : ''; ?>">Add to bag</button><?php endif; ?>
</div>
<span class="font-serif text-brandGold whitespace-nowrap"><?php echo nfm_price($item['price']); ?></span>
</div>
</div>
<?php endforeach; ?>
</div>
</section>
<?php endforeach; ?>
</div>
<?php endforeach; ?>
<footer class="text-center py-12 text-gray-500 text-sm border-t border-gray-700"><?php echo htmlspecialchars($restaurant['footer_content'] ?? $restaurant['address'] ?? ''); ?></footer>
</main>
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
