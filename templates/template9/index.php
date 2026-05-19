<?php
/**
 * Street Food Hub - Vibrant street food brutalism
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
$primaryColor = isset($customization['primary_color']) ? $customization['primary_color'] : '#FFD700';
function sfh_price($p, $s = '₦') {
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
$masonryClasses = ['masonry-item-sm', 'masonry-item-md', 'masonry-item-lg'];
?>
<!DOCTYPE html>
<html lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title><?php echo htmlspecialchars($restaurant['name']); ?><?php if (!empty($singleSectionView) && !empty($sections[0]['name'])): ?> - <?php echo htmlspecialchars($sections[0]['name']); ?><?php endif; ?></title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Bungee&amp;family=Inter:wght@400;700;900&amp;display=swap" rel="stylesheet"/>
<script>
    tailwind.config = { theme: { extend: { colors: { brandYellow: '#FFD700', brandBlack: '#1A1A1A' }, fontFamily: { chunky: ['Bungee', 'cursive'], sans: ['Inter', 'sans-serif'] }, boxShadow: { brutal: '8px 8px 0px 0px #1A1A1A', 'brutal-sm': '4px 4px 0px 0px #1A1A1A' } } } }
  </script>
<style>
.comic-border { border: 4px solid #1A1A1A; }
.masonry-grid { display: grid; gap: 10px; align-items: start; grid-template-columns: repeat(2, 1fr); }
@media (min-width: 768px) {
  .masonry-grid { grid-template-columns: repeat(3, 1fr); gap: 14px; }
}
@media (min-width: 1024px) {
  .masonry-grid { grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
}
.masonry-item-sm { grid-row-end: span 14; }
.masonry-item-md { grid-row-end: span 16; }
.masonry-item-lg { grid-row-end: span 18; }
@media (min-width: 768px) {
  .masonry-item-sm { grid-row-end: span 16; }
  .masonry-item-md { grid-row-end: span 20; }
  .masonry-item-lg { grid-row-end: span 22; }
}
@media (min-width: 1024px) {
  .masonry-item-sm { grid-row-end: span 18; }
  .masonry-item-md { grid-row-end: span 22; }
  .masonry-item-lg { grid-row-end: span 26; }
}
.sfh-no-img h3 { margin-top: 2.75rem; }
@media (max-width: 767px) {
  .sfh-no-img h3 { margin-top: 2rem; }
}
.sfh-card { padding: 0.75rem; }
@media (min-width: 768px) { .sfh-card { padding: 1rem; } }
@media (min-width: 1024px) { .sfh-card { padding: 1.5rem; } }
.sfh-card .sfh-price { font-size: 0.7rem; padding: 0.35rem 0.5rem; }
@media (min-width: 768px) { .sfh-card .sfh-price { font-size: 0.8rem; padding: 0.4rem 0.6rem; } }
@media (min-width: 1024px) { .sfh-card .sfh-price { font-size: 1.25rem; padding: 0.5rem 1rem; } }
.sfh-card .sfh-title { font-size: 0.95rem; margin-bottom: 0.25rem; }
@media (min-width: 768px) { .sfh-card .sfh-title { font-size: 1.1rem; margin-bottom: 0.35rem; } }
@media (min-width: 1024px) { .sfh-card .sfh-title { font-size: 1.5rem; margin-bottom: 0.5rem; } }
.sfh-card .sfh-desc { font-size: 0.7rem; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
@media (min-width: 768px) { .sfh-card .sfh-desc { font-size: 0.75rem; -webkit-line-clamp: 4; } }
@media (min-width: 1024px) { .sfh-card .sfh-desc { font-size: 0.875rem; -webkit-line-clamp: unset; display: block; overflow: visible; } }
.sfh-card .sfh-btn { font-size: 0.65rem; padding: 0.35rem 0.6rem; margin-top: 0.5rem; }
@media (min-width: 768px) { .sfh-card .sfh-btn { font-size: 0.75rem; padding: 0.5rem 0.75rem; margin-top: 0.6rem; } }
@media (min-width: 1024px) { .sfh-card .sfh-btn { font-size: 1rem; padding: 0.5rem 1rem; margin-top: 0.75rem; } }
.sfh-card .sfh-img { height: 5rem; margin-bottom: 0.5rem; }
@media (min-width: 768px) { .sfh-card .sfh-img { height: 6rem; margin-bottom: 0.5rem; } }
@media (min-width: 1024px) { .sfh-card .sfh-img { height: 12rem; margin-bottom: 1rem; } }
@keyframes wiggle { 0%, 100% { transform: rotate(-1deg); } 50% { transform: rotate(1deg); } }
.animate-wiggle { animation: wiggle 2s infinite ease-in-out; }
body.sfh-body { position: relative; min-height: 100vh; }
body.sfh-body .sfh-bg { position: absolute; inset: 0; pointer-events: none; background-image: url('<?php echo htmlspecialchars($siteAssetsBase . '/bg_black.png'); ?>'); background-repeat: repeat; background-size: 280px 280px; opacity: 0.06; z-index: 0; }
</style>
</head>
<body class="bg-brandYellow text-brandBlack font-sans antialiased p-4 md:p-8 relative sfh-body">
<div class="sfh-bg" aria-hidden="true"></div>
<div class="sfh-toggle-wrap fixed top-4 right-4 z-[60] flex items-center justify-center w-12 h-12 bg-white comic-border shadow-brutal-sm" id="sfh-toggle-wrap">
<button type="button" id="sfh-menu-toggle" class="flex items-center justify-center w-full h-full text-brandBlack hover:bg-brandBlack hover:text-white transition-colors" aria-label="Open menu">
<?php echo resmenu_icon('menu', ['size' => 24, 'class' => 'text-2xl']); ?>
</button>
</div>
<div class="fixed inset-0 z-[45] bg-black/40 opacity-0 invisible pointer-events-none transition-opacity duration-200" id="sfh-sidebar-overlay"></div>
<aside class="fixed top-0 right-0 z-[50] w-72 max-w-[85vw] h-full bg-brandYellow shadow-brutal border-l-4 border-brandBlack overflow-y-auto transform translate-x-full transition-transform duration-300" id="sfh-sidebar">
<div class="p-6 sticky top-0 bg-brandYellow z-10 flex items-center justify-between border-b-4 border-brandBlack pb-4 mb-4">
<h3 class="font-chunky text-xl">Menu</h3>
<button type="button" id="sfh-sidebar-close" class="flex items-center justify-center w-10 h-10 min-w-[2.5rem] min-h-[2.5rem] text-brandBlack hover:bg-brandBlack hover:text-white comic-border transition-colors" aria-label="Close">
<?php echo resmenu_icon('close', ['size' => 24, 'class' => 'text-2xl']); ?>
</button>
</div>
<nav class="flex flex-col gap-2 sfh-nav-links px-6 pb-6">
<?php if (!empty($singleSectionView) && !empty($fullMenuUrl)): ?><a href="<?php echo htmlspecialchars($fullMenuUrl); ?>" class="sfh-nav-link block text-center comic-border px-4 py-3 font-chunky bg-white hover:bg-brandBlack hover:text-white transition-colors shadow-brutal-sm">Full menu</a><?php endif; ?>
<?php if (!empty($fullMenuUrl)): ?><a href="<?php echo htmlspecialchars($fullMenuUrl); ?>#menu" class="sfh-nav-link block text-center comic-border px-4 py-3 font-chunky bg-white hover:bg-brandBlack hover:text-white transition-colors shadow-brutal-sm">View menu</a><?php endif; ?>
<?php if (!empty($sectionsForNav) && is_array($sectionsForNav)): ?>
<?php foreach ($sectionsForNav as $navSection): ?>
<a href="<?php echo htmlspecialchars($fullMenuUrl); ?>#section-<?php echo htmlspecialchars($navSection['slug'] ?? ''); ?>" class="sfh-nav-link block text-center comic-border px-4 py-3 font-chunky bg-white hover:bg-brandBlack hover:text-white transition-colors shadow-brutal-sm"><?php echo htmlspecialchars($navSection['name'] ?? ''); ?></a>
<?php endforeach; ?>
<?php endif; ?>
<hr class="border-brandBlack/30 my-2" aria-hidden="true" />
<?php foreach ($activeCategories as $i => $cat): $s = isset($cat['slug']) ? $cat['slug'] : ('section-'.$i); ?>
<a href="<?php echo htmlspecialchars(!empty($fullMenuUrl) ? ((!empty($singleSectionView) && !empty($sections) && is_array($sections) && !empty($sections[0]['slug'])) ? $fullMenuUrl . '/' . $sections[0]['slug'] . '#' . $s : $fullMenuUrl . '#' . $s) : '#' . $s); ?>" class="sfh-nav-link block text-center comic-border px-4 py-3 font-chunky bg-white hover:bg-brandBlack hover:text-white transition-colors shadow-brutal-sm"><?php echo htmlspecialchars($cat['name']); ?></a>
<?php endforeach; ?>
<hr class="border-brandBlack/30 my-2" aria-hidden="true" />
<?php if (!empty($supportsReservations)): ?><a href="<?php echo htmlspecialchars($reservationUrl); ?>" class="sfh-nav-link block text-center comic-border px-4 py-3 font-chunky bg-brandBlack text-white hover:bg-white hover:text-brandBlack transition-colors shadow-brutal-sm">Reserve Table</a><?php endif; ?>
</nav>
</aside>
<header class="max-w-7xl mx-auto mb-12 text-center relative z-10" data-purpose="page-header">
<div class="inline-block bg-brandBlack text-white p-6 comic-border shadow-brutal -rotate-2 mb-6">
<?php if (!empty($restaurant['logo']) && empty($isTemplatePreview)): ?>
<img src="<?php echo $uploadBaseUrl . '/logos/' . htmlspecialchars($restaurant['logo']); ?>" alt="<?php echo htmlspecialchars($restaurant['name']); ?>" class="h-20 w-auto object-contain mx-auto"/>
<?php else: ?>
<h1 class="font-chunky text-5xl md:text-7xl uppercase tracking-tighter"><?php echo htmlspecialchars($restaurant['name']); ?></h1>
<?php endif; ?>
</div>
<p class="font-bold text-xl md:text-2xl uppercase italic"><?php echo htmlspecialchars($restaurant['description'] ?? 'Vibrant. Loud. Delicious.'); ?></p>
<?php if (!empty($supportsReservations)): ?><p class="mt-4"><a class="inline-block bg-white comic-border px-6 py-2 font-chunky hover:bg-brandBlack hover:text-white transition-colors shadow-brutal-sm" href="<?php echo htmlspecialchars($reservationUrl); ?>">Reserve Table</a></p><?php endif; ?>
</header>
<main class="max-w-7xl mx-auto relative z-10" id="menu">
<?php foreach ($sections as $section): 
    if (empty($section['categories']) || !is_array($section['categories'])) continue;
?>
<div id="section-<?php echo htmlspecialchars($section['slug']); ?>" class="mb-14">
<h2 class="font-chunky text-3xl md:text-4xl uppercase mb-8 text-center font-bold text-brandBlack"><?php if (!empty($fullMenuUrl) && empty($singleSectionView)): ?><a href="<?php echo htmlspecialchars($fullMenuUrl . '/' . $section['slug']); ?>" class="hover:underline text-brandBlack"><?php echo htmlspecialchars($section['name']); ?></a><?php else: ?><?php echo htmlspecialchars($section['name']); ?><?php endif; ?></h2>
<?php foreach ($section['categories'] as $catIndex => $category): 
    $slug = isset($category['slug']) ? $category['slug'] : ('cat-'.$catIndex);
    $items = isset($category['menu_items']) ? $category['menu_items'] : [];
    if (empty($items)) continue;
?>
<section class="mb-16" id="<?php echo htmlspecialchars($slug); ?>">
<h3 class="font-chunky text-3xl md:text-4xl uppercase mb-6 comic-border inline-block bg-brandYellow text-brandBlack px-6 py-3 shadow-brutal-sm -rotate-1"><?php echo htmlspecialchars($category['name']); ?></h3>
<div class="masonry-grid">
<?php foreach ($items as $itemIndex => $item): 
        $masonry = $masonryClasses[$itemIndex % 3];
        $imgUrl = !empty($item['image']) ? $uploadBaseUrl . '/menu-items/' . htmlspecialchars($item['image']) : '';
        $itemAvailable = !isset($item['is_available']) || $item['is_available'];
?>
<article class="<?php echo $masonry; ?> sfh-card bg-white comic-border shadow-brutal flex flex-col relative overflow-hidden group <?php echo $imgUrl ? '' : 'sfh-no-img'; ?>" data-purpose="menu-item">
<div class="absolute -top-1 -right-1 sfh-price bg-brandBlack text-white font-chunky comic-border z-10 <?php echo $itemIndex === 0 ? 'animate-wiggle' : ''; ?>"><?php echo sfh_price($item['price']); ?></div>
<?php if ($imgUrl): ?><img alt="<?php echo htmlspecialchars($item['name']); ?>" class="w-full sfh-img object-cover comic-border group-hover:grayscale transition-all duration-300" src="<?php echo $imgUrl; ?>"/><?php endif; ?>
<h3 class="font-chunky sfh-title"><?php echo htmlspecialchars($item['name']); ?></h3>
<p class="sfh-desc font-bold flex-grow min-h-0"><?php echo htmlspecialchars($item['description'] ?? ''); ?></p>
<?php if (!empty($supportsOrdering) && $itemAvailable): ?><button type="button" class="add-to-bag-btn sfh-btn comic-border font-chunky bg-brandBlack text-white hover:bg-white hover:text-brandBlack transition-colors" data-item-id="<?php echo (int)$item['id']; ?>" data-item-name="<?php echo htmlspecialchars($item['name']); ?>" data-item-price="<?php echo htmlspecialchars($item['price']); ?>" data-item-image="<?php echo !empty($item['image']) ? htmlspecialchars($item['image']) : ''; ?>">Add to bag</button><?php endif; ?>
</article>
<?php endforeach; ?>
</div>
</section>
<?php endforeach; ?>
</div>
<?php endforeach; ?>
</main>
<footer class="max-w-7xl mx-auto mt-20 mb-10 text-center relative z-10" data-purpose="footer">
<div class="bg-brandBlack text-white p-8 comic-border shadow-brutal">
<?php if (!empty($restaurant['footer_content'])): ?><p class="font-bold mb-4"><?php echo nl2br(htmlspecialchars($restaurant['footer_content'])); ?></p><?php endif; ?>
<h2 class="font-chunky text-3xl mb-4"><?php echo htmlspecialchars($restaurant['name']); ?></h2>
<div class="flex flex-wrap justify-center gap-x-4 gap-y-1 text-sm font-bold mb-4">
<?php if (!empty($restaurant['address'])): ?><span><?php echo htmlspecialchars($restaurant['address']); ?></span><?php endif; ?>
<?php if (!empty($restaurant['phone'])): ?><span><?php if (!empty($restaurant['address'])): ?> • <?php endif; ?><a href="tel:<?php echo htmlspecialchars(preg_replace('/\s+/', '', $restaurant['phone'])); ?>" class="text-brandYellow hover:underline"><?php echo htmlspecialchars($restaurant['phone']); ?></a></span><?php endif; ?>
<?php if (!empty($restaurant['email'])): ?><span><?php if (!empty($restaurant['address']) || !empty($restaurant['phone'])): ?> • <?php endif; ?><a href="mailto:<?php echo htmlspecialchars($restaurant['email']); ?>" class="text-brandYellow hover:underline"><?php echo htmlspecialchars($restaurant['email']); ?></a></span><?php endif; ?>
</div>
<?php if (!empty($restaurant['opening_hours'])): ?><p class="text-sm mb-4"><?php echo htmlspecialchars($restaurant['opening_hours']); ?></p><?php endif; ?>
<p class="text-xs uppercase tracking-widest opacity-80 mb-6">Please inform your server of any allergies.</p>
<div class="flex justify-center gap-4 flex-wrap">
<?php if (!empty($restaurant['instagram_url'])): ?><a class="comic-border p-2 bg-brandYellow text-brandBlack font-black hover:bg-white transition-colors" href="<?php echo htmlspecialchars($restaurant['instagram_url']); ?>" target="_blank" rel="noopener">INSTA</a><?php endif; ?>
<?php if (!empty($restaurant['facebook_url'])): ?><a class="comic-border p-2 bg-brandYellow text-brandBlack font-black hover:bg-white transition-colors" href="<?php echo htmlspecialchars($restaurant['facebook_url']); ?>" target="_blank" rel="noopener">FB</a><?php endif; ?>
<?php if (!empty($restaurant['website'])): ?><a class="comic-border p-2 bg-brandYellow text-brandBlack font-black hover:bg-white transition-colors" href="<?php echo htmlspecialchars($restaurant['website']); ?>" target="_blank" rel="noopener">WEB</a><?php endif; ?>
</div>
<p class="mt-6 pt-4 border-t-2 border-white/20 text-sm">© <?php echo date('Y'); ?> <?php echo htmlspecialchars($restaurant['name']); ?></p>
</div>
</footer>
<?php if (!empty($supportsOrdering)): ?>
<link rel="stylesheet" href="<?php echo rtrim(defined('SITE_URL') ? SITE_URL : $baseUrl, '/'); ?>/assets/css/cart-modal.css">
<div id="resmenu-cart-widget" class="fixed bottom-6 left-6 z-50 hidden"></div>
<script src="<?php echo rtrim(defined('SITE_URL') ? SITE_URL : $baseUrl, '/'); ?>/assets/js/cart.js"></script>
<script src="<?php echo rtrim(defined('SITE_URL') ? SITE_URL : $baseUrl, '/'); ?>/assets/js/cart-widget.js"></script>
<script src="<?php echo rtrim(defined('SITE_URL') ? SITE_URL : $baseUrl, '/'); ?>/assets/js/cart-modal.js"></script>
<script>
(function(){var baseUrl=<?php echo json_encode($baseUrl); ?>;var slug=<?php echo json_encode($restaurant['slug']??''); ?>;var config={restaurantSlug:slug,currencySymbol:<?php echo json_encode($currencySymbol); ?>,uploadBaseUrl:<?php echo json_encode($uploadBaseUrl??''); ?>,checkoutUrl:baseUrl+'/restaurant/'+slug+'/checkout',primaryColor:<?php echo json_encode($primaryColor); ?>,deliveryFee:0,taxRate:0};window.RESMENU_CART_CONFIG=config;if(window.RESMENU_CART_MODAL)window.RESMENU_CART_MODAL.init(config);if(window.RESMENU_CART_WIDGET)window.RESMENU_CART_WIDGET.init(config);document.querySelectorAll('.add-to-bag-btn').forEach(function(btn){btn.addEventListener('click',function(){var id=this.getAttribute('data-item-id'),name=this.getAttribute('data-item-name'),price=this.getAttribute('data-item-price'),image=this.getAttribute('data-item-image')||'';if(window.RESMENU_CART)window.RESMENU_CART.addItem(slug,{id:id,name:name,price:price,image:image},1);});});})();
</script>
<?php endif; ?>
<script>
(function(){
    var toggle=document.getElementById('sfh-menu-toggle');
    var sidebar=document.getElementById('sfh-sidebar');
    var overlay=document.getElementById('sfh-sidebar-overlay');
    var closeBtn=document.getElementById('sfh-sidebar-close');
    var toggleWrap=document.getElementById('sfh-toggle-wrap');
    function openSidebar(){ if(sidebar)sidebar.classList.remove('translate-x-full'); if(overlay){ overlay.classList.remove('opacity-0','invisible','pointer-events-none'); overlay.classList.add('opacity-100'); overlay.style.pointerEvents='auto'; } if(toggleWrap)toggleWrap.classList.add('sidebar-open'); document.body.style.overflow='hidden'; }
    function closeSidebar(){ if(sidebar)sidebar.classList.add('translate-x-full'); if(overlay){ overlay.classList.add('opacity-0','invisible','pointer-events-none'); overlay.classList.remove('opacity-100'); overlay.style.pointerEvents='none'; } if(toggleWrap)toggleWrap.classList.remove('sidebar-open'); document.body.style.overflow=''; }
    if(toggle)toggle.addEventListener('click',function(e){e.stopPropagation();openSidebar();});
    if(closeBtn)closeBtn.addEventListener('click',function(e){e.preventDefault();e.stopPropagation();closeSidebar();});
    if(overlay)overlay.addEventListener('click',closeSidebar);
    document.querySelectorAll('.sfh-nav-link').forEach(function(l){l.addEventListener('click',closeSidebar);});
    document.addEventListener('keydown',function(e){if(e.key==='Escape')closeSidebar();});
})();
</script>
<script>document.querySelectorAll('a[href^="#"]').forEach(function(a){a.addEventListener('click',function(e){var h=this.getAttribute('href');if(h==='#')return;e.preventDefault();var t=document.querySelector(h);if(t)t.scrollIntoView({behavior:'smooth'});});});</script>
<style>.sfh-toggle-wrap.sidebar-open{visibility:hidden;pointer-events:none;}</style>
<!-- Back to top -->
<a id="scrollToTop" href="#" aria-label="Scroll to top" style="position:fixed;bottom:24px;right:24px;z-index:30;width:48px;height:48px;display:flex;align-items:center;justify-content:center;border-radius:50%;background:#111;color:#fff;opacity:0;visibility:hidden;transform:translateY(10px);transition:opacity 0.3s,visibility 0.3s,transform 0.3s;box-shadow:0 4px 12px rgba(0,0,0,0.3);">
  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 15l-6-6-6 6"/></svg>
</a>
<script>
(function(){var btn=document.getElementById('scrollToTop');if(btn){window.addEventListener('scroll',function(){var st=window.pageYOffset||document.documentElement.scrollTop;var dh=document.documentElement.scrollHeight-window.innerHeight;if(dh>0&&st>=dh*0.3){btn.style.opacity='1';btn.style.visibility='visible';btn.style.transform='translateY(0)';}else{btn.style.opacity='0';btn.style.visibility='hidden';btn.style.transform='translateY(10px)';}});btn.addEventListener('click',function(e){e.preventDefault();window.scrollTo({top:0,behavior:'smooth'});});}})();
</script>
</body></html>
