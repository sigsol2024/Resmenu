<?php
/**
 * Mediterranean Fresh - Sun-drenched coast style
 */
if (defined('UPLOAD_URL')) { $uploadBaseUrl = rtrim(UPLOAD_URL, '/'); } else {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $uploadBaseUrl = $protocol . ($_SERVER['HTTP_HOST'] ?? 'localhost') . (dirname(dirname(dirname($_SERVER['SCRIPT_NAME'] ?? ''))) ?: '') . '/uploads';
}
$baseUrl = defined('SITE_URL') ? rtrim(SITE_URL, '/') : '';
$reservationUrl = $baseUrl . '/restaurant/' . ($restaurant['slug'] ?? '') . '/reservation';
$currencySymbol = '₦';
$primaryColor = isset($customization['primary_color']) ? $customization['primary_color'] : '#005696';
function mf_price($p, $s = '₦') {
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
    tailwind.config = { theme: { extend: { colors: { medBlue: '#005696', lemonYellow: '#FFD700', offWhite: '#F8F9FA' }, fontFamily: { serif: ['Playfair Display', 'serif'], sans: ['Montserrat', 'sans-serif'] } } } }
  </script>
<style>@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600&family=Playfair+Display:ital,wght@0,700;1,400&display=swap'); body { font-family: 'Montserrat', sans-serif; background-color: #ffffff; } h1, h2, h3 { font-family: 'Playfair Display', serif; } .tile-pattern { background-color: #ffffff; background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M30 0l30 30-30 30L0 30 30 0z' fill='%23005696' fill-opacity='0.03' fill-rule='evenodd'/%3E%3C/svg%3E"); } .vertical-text { writing-mode: vertical-rl; text-transform: uppercase; letter-spacing: 0.2em; }</style>
</head>
<body class="tile-pattern text-slate-800 min-h-screen">
<header class="w-full py-12 px-6 text-center bg-white/80 backdrop-blur-sm border-b-4 border-medBlue">
<div class="max-w-4xl mx-auto">
<?php if (!empty($restaurant['logo']) && empty($isTemplatePreview)): ?><div class="mb-4"><img src="<?php echo $uploadBaseUrl . '/logos/' . htmlspecialchars($restaurant['logo']); ?>" alt="<?php echo htmlspecialchars($restaurant['name']); ?>" class="h-20 w-auto object-contain mx-auto"/></div><?php endif; ?>
<span class="text-lemonYellow text-4xl">☀</span>
<h1 class="text-5xl md:text-6xl text-medBlue font-bold mb-2"><?php echo htmlspecialchars($restaurant['name']); ?></h1>
<p class="italic text-lg text-slate-500 font-serif"><?php echo htmlspecialchars($restaurant['description'] ?? 'A Taste of the Sun-Drenched Coast'); ?></p>
<?php if (!empty($singleSectionView) && !empty($fullMenuUrl)): ?><p class="mt-2"><a href="<?php echo htmlspecialchars($fullMenuUrl); ?>" class="text-medBlue font-semibold hover:underline">Full menu</a></p><?php endif; ?>
<?php if (!empty($supportsReservations)): ?><p class="mt-2"><a href="<?php echo htmlspecialchars($reservationUrl); ?>" class="text-medBlue font-semibold hover:underline">Reserve Table</a></p><?php endif; ?>
</div>
</header>
<main class="max-w-6xl mx-auto px-4 py-16 grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-20">
<?php foreach ($sections as $section): 
    if (empty($section['categories']) || !is_array($section['categories'])) continue;
?>
<div id="section-<?php echo htmlspecialchars($section['slug']); ?>" class="mb-14">
<h2 class="text-3xl md:text-4xl font-bold text-medBlue text-center mb-8 border-b-2 border-lemonYellow pb-4"><?php if (!empty($fullMenuUrl) && empty($singleSectionView)): ?><a href="<?php echo htmlspecialchars($fullMenuUrl . '/' . $section['slug']); ?>" class="hover:underline text-medBlue"><?php echo htmlspecialchars($section['name']); ?></a><?php else: ?><?php echo htmlspecialchars($section['name']); ?><?php endif; ?></h2>
<?php foreach ($section['categories'] as $catIndex => $category): 
    $slug = isset($category['slug']) ? $category['slug'] : ('cat-'.$catIndex);
    $items = isset($category['menu_items']) ? $category['menu_items'] : [];
    if (empty($items)) continue;
?>
<section class="flex gap-6 items-start mb-12" id="<?php echo htmlspecialchars($slug); ?>">
<div class="hidden md:block"><h3 class="vertical-text text-medBlue font-bold text-3xl border-l-2 border-lemonYellow pl-4 py-4"><?php echo htmlspecialchars($category['name']); ?></h3></div>
<div class="flex-1">
<h3 class="md:hidden text-3xl text-medBlue font-bold mb-6 border-b-2 border-lemonYellow inline-block"><?php echo htmlspecialchars($category['name']); ?></h3>
<div class="space-y-8">
<?php foreach ($items as $item): ?>
<div class="group">
<?php if (!empty($item['image'])): ?><img src="<?php echo $uploadBaseUrl . '/menu-items/' . htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="w-full max-h-36 object-cover rounded border border-lemonYellow/30 mb-2"/><?php endif; ?>
<div class="flex justify-between items-baseline mb-1">
<h3 class="text-xl font-bold text-medBlue group-hover:text-lemonYellow transition-colors"><?php echo htmlspecialchars($item['name']); ?></h3>
<span class="font-bold text-medBlue"><?php echo mf_price($item['price']); ?></span>
</div>
<p class="text-sm text-slate-600 italic"><?php echo htmlspecialchars($item['description'] ?? ''); ?></p>
<?php if (!empty($supportsOrdering) && !empty($item['is_available'])): ?><button type="button" class="add-to-bag-btn mt-2 text-medBlue border border-medBlue px-4 py-2 rounded hover:bg-medBlue hover:text-white" data-item-id="<?php echo (int)$item['id']; ?>" data-item-name="<?php echo htmlspecialchars($item['name']); ?>" data-item-price="<?php echo htmlspecialchars($item['price']); ?>" data-item-image="<?php echo !empty($item['image']) ? htmlspecialchars($item['image']) : ''; ?>">Add to bag</button><?php endif; ?>
</div>
<?php endforeach; ?>
</div>
</div>
</section>
<?php endforeach; ?>
</div>
<?php endforeach; ?>
</main>
<footer class="max-w-6xl mx-auto py-12 text-center text-slate-500 border-t border-slate-200"><?php echo htmlspecialchars($restaurant['footer_content'] ?? $restaurant['address'] ?? ''); ?></footer>
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
