<?php
/**
 * The Art Fusion - Zen Japanese fusion minimalist
 */
if (defined('UPLOAD_URL')) { $uploadBaseUrl = rtrim(UPLOAD_URL, '/'); } else {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $uploadBaseUrl = $protocol . ($_SERVER['HTTP_HOST'] ?? 'localhost') . (dirname(dirname(dirname($_SERVER['SCRIPT_NAME'] ?? ''))) ?: '') . '/uploads';
}
$baseUrl = defined('SITE_URL') ? rtrim(SITE_URL, '/') : '';
if ($baseUrl === '') {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $baseUrl = $protocol . ($_SERVER['HTTP_HOST'] ?? 'localhost') . (dirname(dirname(dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php'))));
}
$siteAssetsBase = rtrim($baseUrl, '/') . '/uploads/site';
$reservationUrl = $baseUrl . '/restaurant/' . ($restaurant['slug'] ?? '') . '/reservation';
$currencySymbol = '₦';
$primaryColor = isset($customization['primary_color']) ? $customization['primary_color'] : '#bc002d';
function taf_price($p, $s = '₦') {
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
<style>
@import url('https://fonts.googleapis.com/css2?family=Bodoni+Moda:ital,opsz,wght@0,6..96,400;0,6..96,700;1,6..96,400&family=Noto+Sans+JP:wght@300;400;500&display=swap');
body { font-family: 'Noto Sans JP', sans-serif; color: #1a1a1a; }
body.art-fusion-body { background-color: #fafafa; position: relative; overflow-x: hidden; }
body.art-fusion-body .art-fusion-bg { position: absolute; inset: 0; pointer-events: none; background-image: url('<?php echo htmlspecialchars($siteAssetsBase . '/bh_pattern-orange.png'); ?>'); background-repeat: repeat; background-size: 280px 280px; opacity: 0.08; }
h1, h2, h3, .serif-font { font-family: 'Bodoni Moda', serif; }
.zen-divider { height: 2px; background-color: #1a1a1a; width: 100%; margin: 2rem 0; border-radius: 0; max-width: 42rem; margin-left: auto; margin-right: auto; }
@media (max-width: 768px) {
  .zen-divider { height: 1px; max-width: calc(100% - 2rem); margin-left: 1rem; margin-right: 1rem; }
}
.accent-red { color: #bc002d; }
.hide-scrollbar::-webkit-scrollbar { display: none; }
.hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
.menu-section { scroll-margin-top: 100px; }
.art-fusion-toggle-wrap.sidebar-open { visibility: hidden; pointer-events: none; }
</style>
</head>
<body class="antialiased art-fusion-body">
<div class="art-fusion-bg" aria-hidden="true"></div>
<header class="fixed top-0 left-0 right-0 z-50 bg-white/90 backdrop-blur-sm border-b border-gray-100">
<div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
<div class="flex-shrink-0 flex items-center gap-3">
<?php if (!empty($restaurant['logo']) && empty($isTemplatePreview)): ?>
<img src="<?php echo $uploadBaseUrl . '/logos/' . htmlspecialchars($restaurant['logo']); ?>" alt="<?php echo htmlspecialchars($restaurant['name']); ?>" class="h-10 w-auto object-contain"/>
<?php else: ?>
<h1 class="text-2xl font-bold tracking-[0.3em] uppercase"><?php echo htmlspecialchars($restaurant['name']); ?></h1>
<?php endif; ?>
</div>
<div class="art-fusion-toggle-wrap flex items-center justify-center w-12 h-12 flex-shrink-0" id="art-fusion-toggle-wrap">
<button type="button" id="art-fusion-menu-toggle" class="flex items-center justify-center w-full h-full rounded border border-gray-300 hover:border-[#bc002d] hover:text-[#bc002d] transition-colors" aria-label="Open menu">
<?php echo resmenu_icon('menu', ['size' => 24, 'class' => 'text-2xl']); ?>
</button>
</div>
</div>
</header>
<div class="fixed inset-0 z-[45] bg-black/50 opacity-0 invisible pointer-events-none transition-opacity duration-200" id="art-fusion-sidebar-overlay"></div>
<aside class="fixed top-0 right-0 z-[50] w-72 max-w-[85vw] h-full bg-white border-l border-gray-200 shadow-2xl transform translate-x-full transition-transform duration-300 overflow-y-auto" id="art-fusion-sidebar">
<div class="p-6 relative">
<div class="flex items-center justify-between mb-6">
<h3 class="text-lg font-bold tracking-[0.2em] uppercase text-gray-800">Menu</h3>
<button type="button" id="art-fusion-sidebar-close" class="relative z-10 flex items-center justify-center w-10 h-10 min-w-[2.5rem] min-h-[2.5rem] text-gray-600 hover:text-[#bc002d] hover:bg-gray-100 rounded-full transition-colors" aria-label="Close">
<?php echo resmenu_icon('close', ['size' => 24, 'class' => 'text-2xl']); ?>
</button>
</div>
<nav class="flex flex-col gap-1">
<?php if (!empty($singleSectionView) && !empty($fullMenuUrl)): ?><a href="<?php echo htmlspecialchars($fullMenuUrl); ?>" class="art-fusion-nav-link block py-3 px-4 text-sm uppercase tracking-widest font-medium text-gray-700 hover:text-[#bc002d] rounded">Full menu</a><?php endif; ?>
<?php if (!empty($fullMenuUrl)): ?><a href="<?php echo htmlspecialchars($fullMenuUrl); ?>#menu" class="art-fusion-nav-link block py-3 px-4 text-sm uppercase tracking-widest font-medium text-gray-700 hover:text-[#bc002d] rounded">View menu</a><?php endif; ?>
<?php if (!empty($sectionsForNav) && is_array($sectionsForNav)): ?>
<?php foreach ($sectionsForNav as $navSection): ?>
<a href="<?php echo htmlspecialchars($fullMenuUrl); ?>#section-<?php echo htmlspecialchars($navSection['slug'] ?? ''); ?>" class="art-fusion-nav-link block py-3 px-4 text-sm uppercase tracking-widest font-medium text-gray-700 hover:bg-gray-50 hover:text-[#bc002d] rounded"><?php echo htmlspecialchars($navSection['name'] ?? ''); ?></a>
<?php endforeach; ?>
<?php endif; ?>
<hr class="border-gray-200 my-2" aria-hidden="true" />
<?php foreach ($activeCategories as $i => $cat): $s = isset($cat['slug']) ? $cat['slug'] : ('section-'.$i); ?>
<a href="<?php echo htmlspecialchars(!empty($fullMenuUrl) ? ((!empty($singleSectionView) && !empty($sections) && is_array($sections) && !empty($sections[0]['slug'])) ? $fullMenuUrl . '/' . $sections[0]['slug'] . '#' . $s : $fullMenuUrl . '#' . $s) : '#' . $s); ?>" class="art-fusion-nav-link block py-3 px-4 text-sm uppercase tracking-widest font-medium text-gray-700 hover:bg-gray-50 hover:text-[#bc002d] rounded"><?php echo htmlspecialchars($cat['name']); ?></a>
<?php endforeach; ?>
<hr class="border-gray-200 my-2" aria-hidden="true" />
<?php if (!empty($supportsReservations)): ?><a href="<?php echo htmlspecialchars($reservationUrl); ?>" class="art-fusion-nav-link block py-3 px-4 text-sm uppercase tracking-widest font-medium text-gray-700 hover:text-[#bc002d] bg-white/50 hover:bg-white/70 rounded border border-gray-200 hover:border-[#bc002d] transition-colors">Reserve Table</a><?php endif; ?>
</nav>
</div>
</aside>
<main class="pt-20 relative z-10" id="menu">
<section class="relative h-[70vh] flex items-center justify-center overflow-hidden bg-gray-50">
<div class="z-10 text-center px-4">
<span class="text-xs uppercase tracking-[0.5em] mb-4 block"><?php echo htmlspecialchars($restaurant['description'] ?? 'Crafting Balance'); ?></span>
<div class="w-12 h-px bg-black mx-auto mb-6"></div>
<?php if (!empty($supportsReservations)): ?><a href="<?php echo htmlspecialchars($reservationUrl); ?>" class="inline-block text-sm uppercase tracking-widest font-medium text-gray-700 hover:text-[#bc002d] border border-gray-300 hover:border-[#bc002d] bg-white/60 hover:bg-white/80 px-6 py-2.5 transition-colors">Reserve Table</a><?php endif; ?>
</div>
<?php
$tafHeroSrc = '';
if (!empty($singleSectionView) && !empty($sections[0]['image'])) {
    $tafHeroSrc = $uploadBaseUrl . '/sections/' . htmlspecialchars($sections[0]['image']);
} elseif (!empty($restaurant['hero_image']) && empty($isTemplatePreview)) {
    $tafHeroSrc = $uploadBaseUrl . '/heroes/' . htmlspecialchars($restaurant['hero_image']);
}
if ($tafHeroSrc === ''): ?>
<img alt="Zen Interior" class="absolute inset-0 w-full h-full object-cover opacity-80" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAk14in8WcP48BdHZuySC634eIGIRiALxRkUfbVkrJcIRS3dXUqmY2gKlDeEvji5Alw7DN3zJQmePUjDq6fu-6HNbAFYq0gLIHW3l6-LQiq1StCU2j0zTOvrvs4Jf_dN1fFwK8cbERicdHftKKuNYrWX3eBL1w_SVxbfdaWLGcLg_DY3OufFYGa7LCU-NUc2L8-HJmr6ipY9uZKklqSWQzWsJ8UPcrEWvMVTaehUU9diPCOOodb8eo_RRDAK3m569RIPKeEN_QJaa83"/>
<?php else: ?>
<img alt="" class="absolute inset-0 w-full h-full object-cover opacity-80" src="<?php echo $tafHeroSrc; ?>"/>
<?php endif; ?>
</section>
<?php 
$tafCatIndex = 0;
$tafTotalCats = count($activeCategories);
foreach ($sections as $section): 
    if (empty($section['categories']) || !is_array($section['categories'])) continue;
?>
<div id="section-<?php echo htmlspecialchars($section['slug']); ?>" class="py-12">
<h2 class="text-2xl md:text-3xl font-bold text-center tracking-[0.2em] uppercase accent-red mb-12 pb-4 border-b-2 border-[#bc002d]"><?php if (!empty($fullMenuUrl) && empty($singleSectionView)): ?><a href="<?php echo htmlspecialchars($fullMenuUrl . '/' . $section['slug']); ?>" class="hover:underline accent-red"><?php echo htmlspecialchars($section['name']); ?></a><?php else: ?><?php echo htmlspecialchars($section['name']); ?><?php endif; ?></h2>
<?php foreach ($section['categories'] as $category): 
    $slug = isset($category['slug']) ? $category['slug'] : ('cat-'.$tafCatIndex);
    $items = isset($category['menu_items']) ? $category['menu_items'] : [];
    if (empty($items)) continue;
    $tafCatIndex++;
?>
<section class="menu-section py-24 max-w-7xl mx-auto px-6" id="<?php echo htmlspecialchars($slug); ?>">
<h3 class="text-2xl md:text-3xl font-bold tracking-[0.2em] uppercase accent-red mb-12 pb-4 border-b-2 border-[#bc002d]"><?php echo htmlspecialchars($category['name']); ?></h3>
<div class="grid grid-cols-1 md:grid-cols-2 gap-x-20 gap-y-12">
<?php foreach ($items as $item): $itemAvailable = !isset($item['is_available']) || $item['is_available']; ?>
<div class="flex gap-4 border-b border-gray-100 pb-4 items-start">
<?php if (!empty($item['image'])): ?><img src="<?php echo $uploadBaseUrl . '/menu-items/' . htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="w-20 h-20 flex-shrink-0 object-cover rounded"/><?php endif; ?>
<div class="flex-1 min-w-0 w-full">
<div class="flex justify-between items-baseline gap-4 mb-1">
<h4 class="text-lg uppercase tracking-wider"><?php echo htmlspecialchars($item['name']); ?></h4>
<span class="font-medium flex-shrink-0"><?php echo taf_price($item['price']); ?></span>
</div>
<p class="text-sm text-gray-400 italic w-full"><?php echo htmlspecialchars($item['description'] ?? ''); ?></p>
<?php if (!empty($supportsOrdering) && $itemAvailable): ?><button type="button" class="add-to-bag-btn mt-2 text-xs uppercase tracking-wider text-[#bc002d] border border-[#bc002d] px-3 py-1.5 hover:bg-[#bc002d] hover:text-white" data-item-id="<?php echo (int)$item['id']; ?>" data-item-name="<?php echo htmlspecialchars($item['name']); ?>" data-item-price="<?php echo htmlspecialchars($item['price']); ?>" data-item-image="<?php echo !empty($item['image']) ? htmlspecialchars($item['image']) : ''; ?>">Add to bag</button><?php endif; ?>
</div>
</div>
<?php endforeach; ?>
</div>
</section>
<?php if ($tafCatIndex < $tafTotalCats): ?><div class="zen-divider max-w-2xl mx-auto"></div><?php endif; ?>
<?php endforeach; ?>
</div>
<?php endforeach; ?>
</main>
<footer class="bg-white py-20 border-t border-gray-100 relative z-10" data-purpose="menu-footer">
<div class="max-w-7xl mx-auto px-6 flex flex-col items-center text-center">
<?php if (!empty($restaurant['footer_content'])): ?><p class="text-sm text-gray-500 max-w-lg mb-6"><?php echo nl2br(htmlspecialchars($restaurant['footer_content'])); ?></p><?php endif; ?>
<h2 class="text-2xl font-bold tracking-[0.5em] uppercase mb-4"><?php echo htmlspecialchars($restaurant['name']); ?></h2>
<div class="flex flex-wrap justify-center gap-x-4 gap-y-1 text-sm text-gray-500 mb-4">
<?php if (!empty($restaurant['address'])): ?><span><?php echo htmlspecialchars($restaurant['address']); ?></span><?php endif; ?>
<?php if (!empty($restaurant['phone'])): ?><span><?php if (!empty($restaurant['address'])): ?> • <?php endif; ?><a href="tel:<?php echo htmlspecialchars(preg_replace('/\s+/', '', $restaurant['phone'])); ?>" class="text-gray-700 hover:text-[#bc002d]"><?php echo htmlspecialchars($restaurant['phone']); ?></a></span><?php endif; ?>
<?php if (!empty($restaurant['email'])): ?><span><?php if (!empty($restaurant['address']) || !empty($restaurant['phone'])): ?> • <?php endif; ?><a href="mailto:<?php echo htmlspecialchars($restaurant['email']); ?>" class="text-gray-700 hover:text-[#bc002d]"><?php echo htmlspecialchars($restaurant['email']); ?></a></span><?php endif; ?>
</div>
<?php if (!empty($restaurant['opening_hours'])): ?><p class="text-sm text-gray-400 max-w-sm mb-6"><?php echo htmlspecialchars($restaurant['opening_hours']); ?></p><?php endif; ?>
<p class="text-xs text-gray-400 uppercase tracking-widest mb-8">Please inform your server of any allergies.</p>
<div class="flex flex-wrap justify-center gap-x-8 text-xs uppercase tracking-widest border-t border-gray-100 pt-8 w-full">
<?php if (!empty($restaurant['instagram_url'])): ?><a class="hover:text-[#bc002d]" href="<?php echo htmlspecialchars($restaurant['instagram_url']); ?>" target="_blank" rel="noopener">Instagram</a><?php endif; ?>
<?php if (!empty($restaurant['facebook_url'])): ?><a class="hover:text-[#bc002d]" href="<?php echo htmlspecialchars($restaurant['facebook_url']); ?>" target="_blank" rel="noopener">Facebook</a><?php endif; ?>
<?php if (!empty($restaurant['website'])): ?><a class="hover:text-[#bc002d]" href="<?php echo htmlspecialchars($restaurant['website']); ?>" target="_blank" rel="noopener"><?php echo htmlspecialchars(parse_url($restaurant['website'], PHP_URL_HOST) ?: $restaurant['website']); ?></a><?php endif; ?>
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
<script>
(function() {
    var toggle = document.getElementById('art-fusion-menu-toggle');
    var sidebar = document.getElementById('art-fusion-sidebar');
    var overlay = document.getElementById('art-fusion-sidebar-overlay');
    var closeBtn = document.getElementById('art-fusion-sidebar-close');
    var toggleWrap = document.getElementById('art-fusion-toggle-wrap');
    function openSidebar() {
        if (sidebar) sidebar.classList.remove('translate-x-full');
        if (overlay) { overlay.classList.remove('opacity-0', 'invisible', 'pointer-events-none'); overlay.classList.add('opacity-100'); overlay.style.pointerEvents = 'auto'; }
        if (toggleWrap) toggleWrap.classList.add('sidebar-open');
        document.body.style.overflow = 'hidden';
    }
    function closeSidebar() {
        if (sidebar) sidebar.classList.add('translate-x-full');
        if (overlay) { overlay.classList.add('opacity-0', 'invisible', 'pointer-events-none'); overlay.classList.remove('opacity-100'); overlay.style.pointerEvents = 'none'; }
        if (toggleWrap) toggleWrap.classList.remove('sidebar-open');
        document.body.style.overflow = '';
    }
    if (toggle) toggle.addEventListener('click', function(e) { e.stopPropagation(); openSidebar(); });
    if (closeBtn) closeBtn.addEventListener('click', function(e) { e.preventDefault(); e.stopPropagation(); closeSidebar(); });
    if (overlay) overlay.addEventListener('click', closeSidebar);
    document.querySelectorAll('.art-fusion-nav-link').forEach(function(link) { link.addEventListener('click', closeSidebar); });
    document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeSidebar(); });
})();
</script>
<!-- Back to top -->
<a id="scrollToTop" href="#" aria-label="Scroll to top" style="position:fixed;bottom:24px;right:24px;z-index:30;width:48px;height:48px;display:flex;align-items:center;justify-content:center;border-radius:50%;background:#111;color:#fff;opacity:0;visibility:hidden;transform:translateY(10px);transition:opacity 0.3s,visibility 0.3s,transform 0.3s;box-shadow:0 4px 12px rgba(0,0,0,0.3);">
  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 15l-6-6-6 6"/></svg>
</a>
<script>
(function(){var btn=document.getElementById('scrollToTop');if(btn){window.addEventListener('scroll',function(){var st=window.pageYOffset||document.documentElement.scrollTop;var dh=document.documentElement.scrollHeight-window.innerHeight;if(dh>0&&st>=dh*0.3){btn.style.opacity='1';btn.style.visibility='visible';btn.style.transform='translateY(0)';}else{btn.style.opacity='0';btn.style.visibility='hidden';btn.style.transform='translateY(10px)';}});btn.addEventListener('click',function(e){e.preventDefault();window.scrollTo({top:0,behavior:'smooth'});});}})();
</script>
</body></html>
