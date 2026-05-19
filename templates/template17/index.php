<?php
/**
 * Nostalgia Front Page - Landing with menu category cards
 */
if (defined('UPLOAD_URL')) { $uploadBaseUrl = rtrim(UPLOAD_URL, '/'); } else {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $uploadBaseUrl = $protocol . ($_SERVER['HTTP_HOST'] ?? 'localhost') . (dirname(dirname(dirname($_SERVER['SCRIPT_NAME'] ?? ''))) ?: '') . '/uploads';
}
$baseUrl = defined('SITE_URL') ? rtrim(SITE_URL, '/') : '';
$reservationUrl = $baseUrl . '/restaurant/' . ($restaurant['slug'] ?? '') . '/reservation';
$currencySymbol = '₦';
$primaryColor = isset($customization['primary_color']) ? $customization['primary_color'] : '#f2b90d';
$activeCategories = [];
if (!empty($sections) && is_array($sections)) {
    foreach ($sections as $sec) {
        if (empty($sec['categories']) || !is_array($sec['categories'])) continue;
        foreach ($sec['categories'] as $c) {
            if (!empty($c['menu_items']) && is_array($c['menu_items']) && !empty($c['is_active'])) $activeCategories[] = $c;
        }
    }
}
$cardImages = ['https://lh3.googleusercontent.com/aida-public/AB6AXuD-NUGPkPCxpJ_nDAQV6DBrnTSRFar12tbgws-JbaaVlTnoTilN2HiC7cms5yqd8-ZB2sTXpUvWlhJuNI7khLoGvkr8_SEc7lIrA_MEFGd-x-bwnYc88B3jIM9XQwVEmzYU06fXyn3SMdujgrsHjSF2L4hJk4enNQ4OUgdVyoX9aWp6V4cr_QvVftOVDZjx0RX5e0hRgwSVYyOyDVrwfx08Vd-SsTUMgTb21kqi_DXLUC065r8SP0mr5gWUI-uXSgrjRy5oC9ymUp4F'];
?>
<!DOCTYPE html>
<html lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title><?php echo htmlspecialchars($restaurant['name']); ?><?php if (!empty($singleSectionView) && !empty($sections[0]['name'])): ?> - <?php echo htmlspecialchars($sections[0]['name']); ?><?php endif; ?></title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700&amp;family=Montserrat:wght@300;400;600&amp;display=swap" rel="stylesheet"/>
<script>
    tailwind.config = { theme: { extend: { colors: { brandGold: '#f2b90d', darkEbony: '#0a0a0a' }, fontFamily: { serif: ['Cinzel', 'serif'], sans: ['Montserrat', 'sans-serif'] } } } }
  </script>
<style>body { background: linear-gradient(180deg, #1a1a1a 0%, #000000 100%); background-attachment: fixed; } .card-border { border: 2px solid #f2b90d; transition: all 0.3s ease-in-out; } .card-border:hover { box-shadow: 0 0 20px rgba(242, 185, 13, 0.4); transform: translatey(-5px); border-color: #fff; } .divider-line { height: 1px; background: linear-gradient(90deg, transparent 0%, #fff 50%, transparent 100%); width: 100%; max-width: 400px; }</style>
</head>
<body class="text-white min-h-screen flex flex-col justify-between overflow-x-hidden">
<header class="pt-12 pb-8 flex flex-col items-center px-4">
<div class="mb-8">
<?php if (!empty($restaurant['logo']) && empty($isTemplatePreview)): ?>
<img alt="Logo" class="h-24 w-auto object-contain" src="<?php echo $uploadBaseUrl . '/logos/' . htmlspecialchars($restaurant['logo']); ?>"/>
<?php else: ?>
<img alt="Logo" class="h-24 w-auto object-contain" src="https://lh3.googleusercontent.com/aida-public/AB6AXuD3Cg2JO4hgMJxCrFb7fDZxWxS6ktqXPXZn8efe4mOW-MRu9zPxbXANC1NRGivKk0OPK7YROdVnueD5Jb5ut8rtJ1HBwr3f85kemYDAdgnuTtbH1xj8SpVhd2iv3dL-le1py0nk0_qR6BaLEj3075REO7lYhAAkIyVr__xEKUsCCQstBFytqy3fC2sKQA0BeT-ZxgHKJI-S68dkwF1QSX3HmnMhimbVw6XmXkIK0DYTEO3Ay2fHJ4nKS4PgEErcb9uhQoLzzDXbYT-o"/>
<?php endif; ?>
<div class="mt-4 tracking-[0.5em] text-xs font-light text-center"><?php echo strtoupper(htmlspecialchars($restaurant['name'])); ?></div>
</div>
<h1 class="text-4xl md:text-6xl font-serif tracking-widest text-center mb-6 uppercase"><?php echo htmlspecialchars($restaurant['name']); ?> Menu</h1>
<div class="flex items-center gap-4 w-full justify-center opacity-80">
<div class="divider-line"></div>
<div class="text-brandGold"><svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path d="M22 19H2v-2h1c0-4.97 4.03-9 9-9s9 4.03 9 9h1v2zm-10-15c-1.1 0-2 .9-2 2h4c0-1.1-.9-2-2-2z"/></svg></div>
<div class="divider-line"></div>
</div>
<?php if (!empty($singleSectionView) && !empty($fullMenuUrl)): ?><p class="mt-4"><a href="<?php echo htmlspecialchars($fullMenuUrl); ?>" class="text-brandGold hover:underline">Full menu</a></p><?php endif; ?>
<?php if (!empty($supportsReservations)): ?><p class="mt-4"><a href="<?php echo htmlspecialchars($reservationUrl); ?>" class="text-brandGold hover:underline">Reserve Table</a></p><?php endif; ?>
</header>
<main class="container mx-auto px-4 py-12 max-w-6xl flex-1">
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
<?php foreach ($activeCategories as $i => $cat): 
    $slug = isset($cat['slug']) ? $cat['slug'] : ('section-'.$i);
    $img = !empty($cat['image']) ? $uploadBaseUrl . '/categories/' . htmlspecialchars($cat['image']) : ($cardImages[$i % count($cardImages)] ?? $cardImages[0]);
?>
<a class="card-border bg-black/40 backdrop-blur-sm p-2 flex flex-col items-center group cursor-pointer" href="#<?php echo htmlspecialchars($slug); ?>">
<div class="overflow-hidden w-full h-48 mb-6">
<img alt="<?php echo htmlspecialchars($cat['name']); ?>" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" src="<?php echo $img; ?>"/>
</div>
<h2 class="text-xl font-sans font-semibold tracking-wide mb-4 group-hover:text-brandGold transition-colors"><?php echo htmlspecialchars($cat['name']); ?></h2>
</a>
<?php endforeach; ?>
</div>
<div class="mt-16 space-y-16">
<?php 
function nfp_price($p, $s = '₦') {
    return formatPrice($p, $s);
}
foreach ($sections as $section): 
    if (empty($section['categories']) || !is_array($section['categories'])) continue;
?>
<div id="section-<?php echo htmlspecialchars($section['slug']); ?>" class="mb-14">
<h2 class="text-2xl md:text-3xl font-serif font-bold text-brandGold uppercase tracking-widest mb-8 text-center"><?php if (!empty($fullMenuUrl) && empty($singleSectionView)): ?><a href="<?php echo htmlspecialchars($fullMenuUrl . '/' . $section['slug']); ?>" class="hover:underline text-brandGold"><?php echo htmlspecialchars($section['name']); ?></a><?php else: ?><?php echo htmlspecialchars($section['name']); ?><?php endif; ?></h2>
<?php foreach ($section['categories'] as $catIndex => $category): 
    $slug = isset($category['slug']) ? $category['slug'] : ('cat-'.$catIndex);
    $items = isset($category['menu_items']) ? $category['menu_items'] : [];
    if (empty($items)) continue;
?>
<section class="card-border p-8 bg-black/40" id="<?php echo htmlspecialchars($slug); ?>">
<h3 class="text-2xl font-serif text-brandGold uppercase tracking-widest mb-6"><?php echo htmlspecialchars($category['name']); ?></h3>
<div class="space-y-4">
<?php foreach ($items as $item): ?>
<div class="flex gap-4 items-start border-b border-gray-700 pb-3">
<?php if (!empty($item['image'])): ?><img src="<?php echo $uploadBaseUrl . '/menu-items/' . htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="w-16 h-16 flex-shrink-0 object-cover rounded"/><?php endif; ?>
<div class="flex-1 min-w-0 flex justify-between items-baseline">
<div><h3 class="text-lg font-semibold"><?php echo htmlspecialchars($item['name']); ?></h3><p class="text-sm text-gray-400"><?php echo htmlspecialchars($item['description'] ?? ''); ?></p><?php if (!empty($supportsOrdering) && !empty($item['is_available'])): ?><button type="button" class="add-to-bag-btn mt-2 text-brandGold border border-brandGold px-3 py-1.5 rounded hover:bg-brandGold hover:text-black" data-item-id="<?php echo (int)$item['id']; ?>" data-item-name="<?php echo htmlspecialchars($item['name']); ?>" data-item-price="<?php echo htmlspecialchars($item['price']); ?>" data-item-image="<?php echo !empty($item['image']) ? htmlspecialchars($item['image']) : ''; ?>">Add to bag</button><?php endif; ?></div>
<span class="text-brandGold font-serif"><?php echo nfp_price($item['price']); ?></span>
</div>
</div>
<?php endforeach; ?>
</div>
</section>
<?php endforeach; ?>
</div>
<?php endforeach; ?>
</div>
</main>
<footer class="py-8 text-center text-gray-500 text-sm"><?php echo htmlspecialchars($restaurant['footer_content'] ?? $restaurant['address'] ?? ''); ?></footer>
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
