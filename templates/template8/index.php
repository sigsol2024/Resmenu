<?php
/**
 * Sweet Delight - Playful dessert parlour
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
$reservationUrl = $baseUrl . '/restaurant/' . ($restaurant['slug'] ?? '') . '/reservation';
$currencySymbol = '₦';
$primaryColor = isset($customization['primary_color']) ? $customization['primary_color'] : '#FF85A2';
function sd_price($p, $s = '₦') {
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
    tailwind.config = { theme: { extend: { colors: { 'pastel-pink': '#FFD1DC', 'pastel-mint': '#B2F2BB', 'cream': '#FFF9E5', 'soft-berry': '#FF85A2', 'mint-dark': '#7BC992' }, borderRadius: { xlarge: '2rem' } } } }
  </script>
<style>
@import url('https://fonts.googleapis.com/css2?family=Fredoka+One&family=Quicksand:wght@400;600&display=swap');
body { font-family: 'Quicksand', sans-serif; background-color: #FFF9E5; }
h1, h2, h3 { font-family: 'Fredoka One', cursive; }
.blob { position: fixed; z-index: -1; filter: blur(40px); opacity: 0.4; }
.blob-pink { top: 10%; left: 5%; width: 300px; height: 300px; background-color: #FFD1DC; border-radius: 40% 60% 70% 30% / 40% 50% 60% 50%; }
.blob-mint { bottom: 10%; right: 5%; width: 400px; height: 400px; background-color: #B2F2BB; border-radius: 60% 40% 30% 70% / 60% 30% 70% 40%; }
/* Cart widget: template green + pink */
#resmenu-cart-widget .resmenu-cart-widget-btn { background-color: #7BC992 !important; color: #FF85A2 !important; }
#resmenu-cart-widget .resmenu-cart-widget-btn .resmenu-icon { color: #FF85A2 !important; }
#resmenu-cart-widget .resmenu-cart-widget-btn:hover { background-color: #6ab87e !important; }
@media (max-width: 1024px) {
  .sd-desktop-nav { display: none; }
  .sd-mobile-toggle-wrap { display: flex !important; opacity: 0; visibility: hidden; pointer-events: none; transition: opacity 0.2s, visibility 0.2s; }
  .sd-mobile-toggle-wrap.sd-toggle-visible { opacity: 1; visibility: visible; pointer-events: auto; }
}
@media (min-width: 1025px) {
  .sd-mobile-toggle-wrap { display: none !important; }
}
.sd-mobile-toggle-wrap.sidebar-open { visibility: hidden; pointer-events: none; }
</style>
</head>
<body class="min-h-screen">
<div class="blob blob-pink"></div>
<div class="blob blob-mint"></div>
<div class="sd-mobile-toggle-wrap fixed top-4 right-4 z-[60] hidden items-center justify-center w-12 h-12 rounded-full bg-white border-2 border-soft-berry shadow-lg" id="sd-toggle-wrap">
<button type="button" id="sd-menu-toggle" class="flex items-center justify-center w-full h-full rounded-full text-soft-berry" aria-label="Open menu">
<?php echo resmenu_icon('menu', ['size' => 24, 'class' => 'text-2xl']); ?>
</button>
</div>
<div class="fixed inset-0 z-[45] bg-black/40 opacity-0 invisible pointer-events-none transition-opacity duration-200" id="sd-sidebar-overlay"></div>
<aside class="fixed top-0 right-0 z-[50] w-72 max-w-[85vw] h-full bg-white shadow-2xl transform translate-x-full transition-transform duration-300 overflow-y-auto rounded-l-3xl border-2 border-soft-berry/30" id="sd-sidebar">
<div class="p-6 sticky top-0 bg-white z-10 flex items-center justify-between border-b border-soft-berry/20 pb-4 mb-4 -mt-2">
<h3 class="text-xl font-bold text-soft-berry">Menu</h3>
<button type="button" id="sd-sidebar-close" class="flex items-center justify-center w-10 h-10 min-w-[2.5rem] min-h-[2.5rem] text-soft-berry hover:bg-pastel-pink rounded-full transition-colors shrink-0" aria-label="Close">
<?php echo resmenu_icon('close', ['size' => 24, 'class' => 'text-2xl']); ?>
</button>
</div>
<div class="px-6 pb-6">
<nav class="flex flex-col gap-2 sd-nav-links">
<?php if (!empty($singleSectionView) && !empty($fullMenuUrl)): ?><a href="<?php echo htmlspecialchars($fullMenuUrl); ?>" class="sd-nav-link block px-4 py-3 rounded-full font-bold text-center bg-white border-2 border-soft-berry text-soft-berry hover:shadow-md">Full menu</a><?php endif; ?>
<?php if (!empty($fullMenuUrl)): ?><a href="<?php echo htmlspecialchars($fullMenuUrl); ?>#menu" class="sd-nav-link block px-4 py-3 rounded-full font-bold text-center bg-white border-2 border-soft-berry text-soft-berry hover:shadow-md">View menu</a><?php endif; ?>
<?php if (!empty($sectionsForNav) && is_array($sectionsForNav)): ?>
<?php foreach ($sectionsForNav as $navSection): ?>
<a href="<?php echo htmlspecialchars($fullMenuUrl); ?>#section-<?php echo htmlspecialchars($navSection['slug'] ?? ''); ?>" class="sd-nav-link block px-4 py-3 rounded-full font-bold text-center bg-white border-2 border-soft-berry text-soft-berry hover:shadow-md"><?php echo htmlspecialchars($navSection['name'] ?? ''); ?></a>
<?php endforeach; ?>
<?php endif; ?>
<hr class="border-soft-berry/30 my-2" aria-hidden="true" />
<?php foreach ($activeCategories as $i => $cat): $s = isset($cat['slug']) ? $cat['slug'] : ('section-'.$i); ?>
<a href="<?php echo htmlspecialchars(!empty($fullMenuUrl) ? ((!empty($singleSectionView) && !empty($sections) && is_array($sections) && !empty($sections[0]['slug'])) ? $fullMenuUrl . '/' . $sections[0]['slug'] . '#' . $s : $fullMenuUrl . '#' . $s) : '#' . $s); ?>" class="sd-nav-link block px-4 py-3 rounded-full font-bold text-center <?php echo $i % 2 ? 'bg-pastel-mint text-mint-dark' : 'bg-pastel-pink text-soft-berry'; ?> hover:shadow-md"><?php echo htmlspecialchars($cat['name']); ?></a>
<?php endforeach; ?>
<hr class="border-soft-berry/30 my-2" aria-hidden="true" />
<?php if (!empty($supportsReservations)): ?><a href="<?php echo htmlspecialchars($reservationUrl); ?>" class="sd-nav-link block px-4 py-3 rounded-full font-bold text-center bg-soft-berry text-white hover:shadow-md">Reserve Table</a><?php endif; ?>
</nav>
</div>
</aside>
<header class="py-12 text-center relative z-10" data-purpose="header-container" id="sd-hero-header">
<?php if (!empty($restaurant['logo']) && empty($isTemplatePreview)): ?>
<div class="inline-block p-4 bg-white rounded-full shadow-lg mb-4"><img src="<?php echo $uploadBaseUrl . '/logos/' . htmlspecialchars($restaurant['logo']); ?>" alt="<?php echo htmlspecialchars($restaurant['name']); ?>" style="max-height: 48px; width: auto;"/></div>
<p class="text-lg text-gray-600 font-semibold italic mb-4"><?php echo htmlspecialchars($restaurant['description'] ?? 'Where every scoop is a dream!'); ?></p>
<?php else: ?>
<div class="inline-block p-4 bg-white rounded-full shadow-lg mb-4"><span class="text-4xl">🍦</span></div>
<h1 class="text-5xl md:text-6xl text-soft-berry mb-2"><?php echo htmlspecialchars($restaurant['name']); ?></h1>
<p class="text-lg text-gray-600 font-semibold italic mb-4"><?php echo htmlspecialchars($restaurant['description'] ?? 'Where every scoop is a dream!'); ?></p>
<?php endif; ?>
<?php if (!empty($singleSectionView) && !empty($fullMenuUrl)): ?><p class="mb-6"><a href="<?php echo htmlspecialchars($fullMenuUrl); ?>" class="inline-block px-6 py-2 bg-white border-2 border-soft-berry text-soft-berry rounded-full font-bold hover:shadow-md transition-all">Full menu</a></p><?php endif; ?>
<?php if (!empty($supportsReservations)): ?><p class="mb-6"><a href="<?php echo htmlspecialchars($reservationUrl); ?>" class="inline-block px-6 py-2 bg-white border-2 border-soft-berry text-soft-berry rounded-full font-bold hover:shadow-md transition-all">Reserve Table</a></p><?php endif; ?>
<nav class="mt-6 flex justify-center gap-4 flex-wrap sd-desktop-nav" data-purpose="main-navigation">
<?php if (!empty($fullMenuUrl)): ?><a class="px-6 py-2 bg-white border-2 border-soft-berry text-soft-berry rounded-full font-bold hover:shadow-md transition-all" href="<?php echo htmlspecialchars($fullMenuUrl); ?>#menu">View menu</a><?php endif; ?>
<?php if (!empty($sectionsForNav) && is_array($sectionsForNav)): ?>
<?php foreach ($sectionsForNav as $navSection): ?>
<a class="px-6 py-2 bg-pastel-pink text-soft-berry rounded-full font-bold hover:shadow-md transition-all" href="<?php echo htmlspecialchars($fullMenuUrl); ?>#section-<?php echo htmlspecialchars($navSection['slug'] ?? ''); ?>"><?php echo htmlspecialchars($navSection['name'] ?? ''); ?></a>
<?php endforeach; ?>
<?php endif; ?>
<span class="self-center w-px h-6 bg-soft-berry/40" aria-hidden="true"></span>
<?php foreach ($activeCategories as $i => $cat): $s = isset($cat['slug']) ? $cat['slug'] : ('section-'.$i); ?>
<a class="px-6 py-2 <?php echo $i % 2 ? 'bg-pastel-mint text-mint-dark' : 'bg-pastel-pink text-soft-berry'; ?> rounded-full font-bold hover:shadow-md transition-all" href="<?php echo htmlspecialchars(!empty($fullMenuUrl) ? ((!empty($singleSectionView) && !empty($sections) && is_array($sections) && !empty($sections[0]['slug'])) ? $fullMenuUrl . '/' . $sections[0]['slug'] . '#' . $s : $fullMenuUrl . '#' . $s) : '#' . $s); ?>"><?php echo htmlspecialchars($cat['name']); ?></a>
<?php endforeach; ?>
<span class="self-center w-px h-6 bg-soft-berry/40" aria-hidden="true"></span>
<?php if (!empty($supportsReservations)): ?><a class="px-6 py-2 bg-soft-berry text-white rounded-full font-bold hover:shadow-md transition-all" href="<?php echo htmlspecialchars($reservationUrl); ?>">Reserve Table</a><?php endif; ?>
</nav>
</header>
<main class="container mx-auto px-4 pb-20" id="menu">
<?php foreach ($sections as $section): 
    if (empty($section['categories']) || !is_array($section['categories'])) continue;
?>
<div id="section-<?php echo htmlspecialchars($section['slug']); ?>" class="mb-14">
<h2 class="text-3xl md:text-4xl font-bold text-center text-gray-800 mb-8"><?php if (!empty($fullMenuUrl) && empty($singleSectionView)): ?><a href="<?php echo htmlspecialchars($fullMenuUrl . '/' . $section['slug']); ?>" class="hover:underline text-gray-800"><?php echo htmlspecialchars($section['name']); ?></a><?php else: ?><?php echo htmlspecialchars($section['name']); ?><?php endif; ?></h2>
<?php foreach ($section['categories'] as $catIndex => $category): 
    $slug = isset($category['slug']) ? $category['slug'] : ('cat-'.$catIndex);
    $items = isset($category['menu_items']) ? $category['menu_items'] : [];
    if (empty($items)) continue;
    $useMint = ($catIndex % 2);
?>
<section class="mb-16" id="<?php echo htmlspecialchars($slug); ?>">
<div class="flex items-center gap-4 mb-8">
<h3 class="text-3xl <?php echo $useMint ? 'text-mint-dark' : 'text-soft-berry'; ?>"><?php echo htmlspecialchars($category['name']); ?></h3>
<div class="h-1 flex-grow <?php echo $useMint ? 'bg-pastel-mint' : 'bg-pastel-pink'; ?> rounded-full"></div>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
<?php foreach ($items as $item): $itemAvailable = !isset($item['is_available']) || $item['is_available']; ?>
<div class="bg-white p-6 rounded-xlarge shadow-sm hover:shadow-xl transition-shadow border-4 <?php echo $useMint ? 'border-pastel-mint' : 'border-pastel-pink'; ?> relative overflow-hidden" data-purpose="menu-item-card">
<?php if (!empty($item['image'])): ?><img src="<?php echo $uploadBaseUrl . '/menu-items/' . htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="w-full h-40 object-cover rounded-t-xlarge -mx-6 -mt-6 mb-4"/><?php endif; ?>
<div class="flex justify-between items-start mb-4">
<h3 class="text-xl text-gray-800"><?php echo htmlspecialchars($item['name']); ?></h3>
<span class="px-3 py-1 rounded-full font-bold <?php echo $useMint ? 'bg-pastel-mint text-mint-dark' : 'bg-pastel-pink text-soft-berry'; ?>"><?php echo sd_price($item['price']); ?></span>
</div>
<p class="text-gray-600 mb-4"><?php echo htmlspecialchars($item['description'] ?? ''); ?></p>
<?php if (!empty($supportsOrdering) && $itemAvailable): ?><button type="button" class="add-to-bag-btn mt-2 px-4 py-2 rounded-full font-bold bg-black text-white hover:opacity-90" data-item-id="<?php echo (int)$item['id']; ?>" data-item-name="<?php echo htmlspecialchars($item['name']); ?>" data-item-price="<?php echo htmlspecialchars($item['price']); ?>" data-item-image="<?php echo !empty($item['image']) ? htmlspecialchars($item['image']) : ''; ?>">Add to bag</button><?php endif; ?>
</div>
<?php endforeach; ?>
</div>
</section>
<?php endforeach; ?>
</div>
<?php endforeach; ?>
</main>
<footer class="bg-soft-berry text-white py-12 rounded-t-[3rem] text-center" data-purpose="menu-footer">
<div class="container mx-auto px-4">
<?php if (!empty($restaurant['footer_content'])): ?><p class="mb-6 opacity-90 text-sm"><?php echo nl2br(htmlspecialchars($restaurant['footer_content'])); ?></p><?php endif; ?>
<h2 class="text-3xl mb-4 font-bold"><?php echo htmlspecialchars($restaurant['name']); ?></h2>
<div class="flex flex-wrap justify-center gap-x-4 gap-y-1 text-sm opacity-90 mb-4">
<?php if (!empty($restaurant['address'])): ?><span><?php echo htmlspecialchars($restaurant['address']); ?></span><?php endif; ?>
<?php if (!empty($restaurant['phone'])): ?><span><?php if (!empty($restaurant['address'])): ?> • <?php endif; ?><a href="tel:<?php echo htmlspecialchars(preg_replace('/\s+/', '', $restaurant['phone'])); ?>" class="hover:underline"><?php echo htmlspecialchars($restaurant['phone']); ?></a></span><?php endif; ?>
<?php if (!empty($restaurant['email'])): ?><span><?php if (!empty($restaurant['address']) || !empty($restaurant['phone'])): ?> • <?php endif; ?><a href="mailto:<?php echo htmlspecialchars($restaurant['email']); ?>" class="hover:underline"><?php echo htmlspecialchars($restaurant['email']); ?></a></span><?php endif; ?>
</div>
<?php if (!empty($restaurant['opening_hours'])): ?><p class="text-sm opacity-80 mb-4"><?php echo htmlspecialchars($restaurant['opening_hours']); ?></p><?php endif; ?>
<p class="text-xs uppercase tracking-widest opacity-80 mb-6">Please inform your server of any allergies.</p>
<div class="flex justify-center gap-6 text-2xl">
<?php if (!empty($restaurant['instagram_url'])): ?><a class="hover:scale-110 transition-transform" href="<?php echo htmlspecialchars($restaurant['instagram_url']); ?>" target="_blank" rel="noopener">📸</a><?php endif; ?>
<?php if (!empty($restaurant['facebook_url'])): ?><a class="hover:scale-110 transition-transform" href="<?php echo htmlspecialchars($restaurant['facebook_url']); ?>" target="_blank" rel="noopener">📘</a><?php endif; ?>
<?php if (!empty($restaurant['website'])): ?><a class="hover:scale-110 transition-transform" href="<?php echo htmlspecialchars($restaurant['website']); ?>" target="_blank" rel="noopener">🌐</a><?php endif; ?>
</div>
<div class="mt-10 pt-6 border-t border-white/20 text-sm">© <?php echo date('Y'); ?> <?php echo htmlspecialchars($restaurant['name']); ?></div>
</div>
</footer>
<script>document.querySelectorAll('a[href^="#"]').forEach(function(a){ a.addEventListener('click',function(e){ e.preventDefault(); var t=document.querySelector(this.getAttribute('href')); if(t) t.scrollIntoView({behavior:'smooth'}); }); });</script>
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
    var toggle=document.getElementById('sd-menu-toggle');
    var sidebar=document.getElementById('sd-sidebar');
    var overlay=document.getElementById('sd-sidebar-overlay');
    var closeBtn=document.getElementById('sd-sidebar-close');
    var toggleWrap=document.getElementById('sd-toggle-wrap');
    var heroHeader=document.getElementById('sd-hero-header');
    function openSidebar(){ if(sidebar)sidebar.classList.remove('translate-x-full'); if(overlay){ overlay.classList.remove('opacity-0','invisible','pointer-events-none'); overlay.classList.add('opacity-100'); overlay.style.pointerEvents='auto'; } if(toggleWrap)toggleWrap.classList.add('sidebar-open'); document.body.style.overflow='hidden'; }
    function closeSidebar(){ if(sidebar)sidebar.classList.add('translate-x-full'); if(overlay){ overlay.classList.add('opacity-0','invisible','pointer-events-none'); overlay.classList.remove('opacity-100'); overlay.style.pointerEvents='none'; } if(toggleWrap)toggleWrap.classList.remove('sidebar-open'); document.body.style.overflow=''; }
    if(toggle)toggle.addEventListener('click',function(e){e.stopPropagation();openSidebar();});
    if(closeBtn)closeBtn.addEventListener('click',function(e){e.preventDefault();e.stopPropagation();closeSidebar();});
    if(overlay)overlay.addEventListener('click',closeSidebar);
    document.querySelectorAll('.sd-nav-link').forEach(function(l){l.addEventListener('click',closeSidebar);});
    document.addEventListener('keydown',function(e){if(e.key==='Escape')closeSidebar();});
    if(heroHeader&&toggleWrap){
        function checkScroll(){
            if(window.innerWidth>1024)return;
            var rect=heroHeader.getBoundingClientRect();
            if(rect.bottom<0)toggleWrap.classList.add('sd-toggle-visible');
            else toggleWrap.classList.remove('sd-toggle-visible');
        }
        window.addEventListener('scroll',checkScroll,{passive:true});
        window.addEventListener('resize',checkScroll);
        checkScroll();
    }
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
