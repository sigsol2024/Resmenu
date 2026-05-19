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
$siteAssetsBase = (defined('SITE_URL') ? rtrim(SITE_URL, '/') : $baseUrl) . '/uploads/site';
function the_prime_cut_price($price, $symbol = '₦') {
    return formatPrice($price, $symbol);
}
$activeCategories = [];
if (!empty($sections) && is_array($sections)) {
    foreach ($sections as $sec) {
        if (empty($sec['categories']) || !is_array($sec['categories'])) continue;
        foreach ($sec['categories'] as $cat) {
            if (!empty($cat['menu_items']) && is_array($cat['menu_items']) && !empty($cat['is_active'])) {
                $activeCategories[] = $cat;
            }
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
<style data-purpose="custom-textures">
body.prime-cut-outer { background-color: #0f172a; position: relative; overflow-x: hidden; }
body.prime-cut-outer .prime-cut-outer-bg { position: absolute; inset: 0; pointer-events: none; background-image: url('<?php echo htmlspecialchars($siteAssetsBase . '/bh_pattern-orange.png'); ?>'); background-repeat: repeat; background-size: 280px 280px; opacity: 0.12; }
.menu-page {
    background-color: #000;
    position: relative;
    overflow: hidden;
    box-shadow: 0 0 50px rgba(0, 0, 0, 0.8);
    }
.menu-page .menu-page-pattern { position: absolute; inset: 0; pointer-events: none; background-image: url('<?php echo htmlspecialchars($siteAssetsBase . '/bg_white.png'); ?>'); background-repeat: repeat; background-size: 280px 280px; opacity: 0.08; }
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
    }
/* Mobile: more outer bg visible on sides, extra top spacing, toggle, logo size */
@media (max-width: 768px) {
    body.prime-cut-outer { padding-left: 1.5rem; padding-right: 1.5rem; }
    .menu-page.prime-cut-main { max-width: calc(100vw - 3rem); width: 100%; margin-top: 2.5rem; }
    .prime-cut-toggle-wrap { top: 0.75rem !important; right: 1.25rem !important; width: 3.25rem !important; height: 3.25rem !important; min-width: 3.25rem !important; min-height: 3.25rem !important; }
    .prime-cut-toggle-wrap .resmenu-icon { width: 1.75rem; height: 1.75rem; }
    .prime-cut-toggle-wrap.sidebar-open { visibility: hidden; pointer-events: none; }
    .prime-cut-logo { max-height: 4rem !important; height: auto !important; width: auto !important; }
}
</style>
</head>
<body class="prime-cut-outer min-h-screen py-12 px-4 flex justify-center items-start">
<div class="prime-cut-outer-bg" aria-hidden="true"></div>
<!-- Categories sidebar (desktop + mobile) -->
<div class="prime-cut-toggle-wrap fixed top-4 right-4 z-[60] flex items-center justify-center w-12 h-12 min-w-[3rem] min-h-[3rem] rounded-full border-2 border-gold/60 text-gold hover:bg-gold/20 transition-colors bg-black/40" id="prime-cut-toggle-wrap">
    <button type="button" id="prime-cut-menu-toggle" class="flex items-center justify-center w-full h-full rounded-full" aria-label="Open menu categories">
        <?php echo resmenu_icon('menu', ['size' => 24, 'class' => 'text-2xl']); ?>
    </button>
</div>
<div class="fixed inset-0 z-[45] bg-black/50 opacity-0 invisible pointer-events-none transition-opacity duration-200" id="prime-cut-sidebar-overlay"></div>
<aside class="fixed top-0 right-0 z-[50] w-72 max-w-[85vw] h-full bg-darkwood border-l-2 border-gold/40 shadow-2xl transform translate-x-full transition-transform duration-300 overflow-y-auto" id="prime-cut-category-sidebar">
    <div class="p-6 relative">
        <div class="flex items-center justify-between mb-6">
            <h3 class="font-serif text-xl text-gold uppercase tracking-widest">Categories</h3>
            <button type="button" id="prime-cut-sidebar-close" class="relative z-10 flex items-center justify-center w-10 h-10 min-w-[2.5rem] min-h-[2.5rem] text-gold hover:bg-gold/10 rounded-full transition-colors" aria-label="Close">
                <?php echo resmenu_icon('close', ['size' => 24, 'class' => 'text-2xl']); ?>
            </button>
        </div>
        <nav class="flex flex-col gap-2">
            <?php if (!empty($singleSectionView) && !empty($fullMenuUrl)): ?>
            <a href="<?php echo htmlspecialchars($fullMenuUrl); ?>" class="prime-cut-nav-link block py-3 px-4 font-sans text-cream hover:bg-gold/10 hover:text-gold rounded-lg transition-colors">Full menu</a>
            <?php endif; ?>
            <?php if (!empty($fullMenuUrl)): ?>
            <a href="<?php echo htmlspecialchars($fullMenuUrl); ?>#menu" class="prime-cut-nav-link block py-3 px-4 font-sans text-cream hover:bg-gold/10 hover:text-gold rounded-lg transition-colors">View menu</a>
            <?php endif; ?>
            <?php if (!empty($sectionsForNav) && is_array($sectionsForNav)): ?>
            <?php foreach ($sectionsForNav as $navSection): ?>
            <a href="<?php echo htmlspecialchars($fullMenuUrl); ?>#section-<?php echo htmlspecialchars($navSection['slug'] ?? ''); ?>" class="prime-cut-nav-link block py-3 px-4 font-sans text-cream hover:bg-gold/10 hover:text-gold rounded-lg transition-colors"><?php echo htmlspecialchars($navSection['name'] ?? ''); ?></a>
            <?php endforeach; ?>
            <?php endif; ?>
            <hr class="border-gold/30 my-2" aria-hidden="true" />
            <?php foreach ($activeCategories as $cat): $cslug = isset($cat['slug']) ? $cat['slug'] : ('section-' . array_search($cat, $activeCategories)); ?>
            <a href="<?php echo htmlspecialchars(!empty($fullMenuUrl) ? ((!empty($singleSectionView) && !empty($sections) && is_array($sections) && !empty($sections[0]['slug'])) ? $fullMenuUrl . '/' . $sections[0]['slug'] . '#' . $cslug : $fullMenuUrl . '#' . $cslug) : '#' . $cslug); ?>" class="prime-cut-nav-link block py-3 px-4 font-sans text-cream hover:bg-gold/10 hover:text-gold rounded-lg transition-colors"><?php echo htmlspecialchars($cat['name']); ?></a>
            <?php endforeach; ?>
            <hr class="border-gold/30 my-2" aria-hidden="true" />
            <?php if (!empty($supportsReservations)): ?>
            <a href="<?php echo htmlspecialchars($reservationUrl); ?>" class="prime-cut-nav-link block py-3 px-4 font-sans text-gold font-bold border border-gold/60 hover:bg-gold/20 rounded-lg transition-colors text-center">Reserve Table</a>
            <?php endif; ?>
        </nav>
    </div>
</aside>
<main class="menu-page prime-cut-main max-w-4xl w-full text-cream p-8 md:p-16 gold-border relative z-10" id="menu">
<div class="menu-page-pattern" aria-hidden="true"></div>
<header class="text-center mb-16 relative z-10" data-purpose="main-header">
<?php if (!empty($restaurant['logo']) && empty($isTemplatePreview)): ?>
<div class="mb-4"><img src="<?php echo $uploadBaseUrl . '/logos/' . htmlspecialchars($restaurant['logo']); ?>" alt="<?php echo htmlspecialchars($restaurant['name']); ?>" class="prime-cut-logo h-24 w-auto object-contain mx-auto"/></div>
<div class="mb-4"><span class="text-gold tracking-[0.3em] uppercase text-sm font-sans"><?php echo !empty($restaurant['description']) ? htmlspecialchars(mb_substr($restaurant['description'], 0, 60)) : 'Established'; ?></span></div>
<?php else: ?>
<h1 class="font-serif text-4xl md:text-6xl text-gold italic mb-2"><?php echo htmlspecialchars($restaurant['name']); ?></h1>
<p class="font-sans text-lg tracking-widest uppercase opacity-80"><?php echo !empty($restaurant['description']) ? htmlspecialchars(mb_substr($restaurant['description'], 0, 80)) : 'Premium Artisanal'; ?></p>
<?php endif; ?>
<?php if (!empty($supportsReservations)): ?><div class="mt-4"><a href="<?php echo htmlspecialchars($reservationUrl); ?>" class="font-sans text-sm tracking-widest uppercase text-gold border border-gold/60 px-6 py-2 hover:bg-gold/20 transition-colors">Reserve Table</a></div><?php endif; ?>
<div class="ornate-divider">
<span class="ornate-symbol">❦</span>
</div>
</header>
<?php 
$primeCutCatIndex = 0;
foreach ($sections as $section): 
    if (empty($section['categories']) || !is_array($section['categories'])) continue;
?>
<div id="section-<?php echo htmlspecialchars($section['slug']); ?>" class="mb-14">
<h2 class="text-center font-serif text-3xl md:text-5xl font-bold text-gold mb-10 uppercase tracking-widest"><?php if (!empty($fullMenuUrl) && empty($singleSectionView)): ?><a href="<?php echo htmlspecialchars($fullMenuUrl . '/' . $section['slug']); ?>" class="hover:underline text-gold"><?php echo htmlspecialchars($section['name']); ?></a><?php else: ?><?php echo htmlspecialchars($section['name']); ?><?php endif; ?></h2>
<?php foreach ($section['categories'] as $category): 
    $slug = isset($category['slug']) ? $category['slug'] : ('cat-' . $primeCutCatIndex);
    $items = isset($category['menu_items']) ? $category['menu_items'] : [];
    if (empty($items)) continue;
    $categoryIcon = resmenu_get_category_icon($category);
    $primeCutCatIndex++;
?>
<section class="mb-16" data-purpose="menu-section" id="<?php echo htmlspecialchars($slug); ?>">
<?php if ($primeCutCatIndex > 1): ?>
<div class="ornate-divider">
<span class="ornate-symbol"><?php echo $categoryIcon; ?></span>
</div>
<?php endif; ?>
<h3 class="text-left font-serif text-2xl md:text-4xl text-gold mb-10 uppercase tracking-widest"><?php echo htmlspecialchars($category['name']); ?></h3>
<?php if ($primeCutCatIndex === 1 && count($items) > 0): ?>
<div class="space-y-12">
<div class="relative p-8 border border-gold/30 bg-black/20 flex flex-col items-start text-left" data-purpose="featured-item">
<div class="absolute -top-4 left-0 bg-gold text-burgundy px-4 py-1 text-xs font-bold uppercase tracking-widest">Chef's Recommendation</div>
<?php $feat = $items[0]; $featAvailable = !isset($feat['is_available']) || $feat['is_available']; ?>
<h3 class="font-serif text-3xl font-bold mb-2"><?php echo htmlspecialchars($feat['name']); ?></h3>
<p class="max-w-xl text-sm font-sans italic opacity-80 mb-4 text-left"><?php echo htmlspecialchars($feat['description'] ?? ''); ?></p>
<span class="text-gold font-serif text-2xl"><?php echo the_prime_cut_price($feat['price']); ?></span>
<?php if (!empty($supportsOrdering) && $featAvailable): ?><button type="button" class="add-to-bag-btn mt-3 font-sans text-sm uppercase text-gold border border-gold/60 px-3 py-1.5 hover:bg-gold/20 transition-colors inline-block w-auto" data-item-id="<?php echo (int)$feat['id']; ?>" data-item-name="<?php echo htmlspecialchars($feat['name']); ?>" data-item-price="<?php echo htmlspecialchars($feat['price']); ?>" data-item-image="<?php echo !empty($feat['image']) ? htmlspecialchars($feat['image']) : ''; ?>">Add to bag</button><?php endif; ?>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-10">
<?php for ($i = 1; $i < count($items); $i++): $item = $items[$i]; $itemAvailable = !isset($item['is_available']) || $item['is_available']; ?>
<div class="flex flex-col text-left">
<div class="flex justify-between items-baseline mb-1">
<h3 class="font-serif text-xl font-bold"><?php echo htmlspecialchars($item['name']); ?></h3>
<span class="text-gold font-serif"><?php echo the_prime_cut_price($item['price']); ?></span>
</div>
<p class="text-sm font-sans italic opacity-75 text-left"><?php echo htmlspecialchars($item['description'] ?? ''); ?></p>
<?php if (!empty($supportsOrdering) && $itemAvailable): ?><button type="button" class="add-to-bag-btn mt-2 font-sans text-xs uppercase text-gold border border-gold/60 px-3 py-1.5 hover:bg-gold/20 transition-colors inline-block w-auto" data-item-id="<?php echo (int)$item['id']; ?>" data-item-name="<?php echo htmlspecialchars($item['name']); ?>" data-item-price="<?php echo htmlspecialchars($item['price']); ?>" data-item-image="<?php echo !empty($item['image']) ? htmlspecialchars($item['image']) : ''; ?>">Add to bag</button><?php endif; ?>
</div>
<?php endfor; ?>
</div>
</div>
<?php else: ?>
<div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8">
<?php foreach ($items as $item): $itemAvailable = !isset($item['is_available']) || $item['is_available']; ?>
<div class="flex flex-col text-left">
<?php if (!empty($item['image'])): ?><div class="mb-3"><img src="<?php echo $uploadBaseUrl . '/menu-items/' . htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="w-full max-h-40 object-cover rounded border border-gold/30"/></div><?php endif; ?>
<div class="flex justify-between items-baseline mb-1">
<h3 class="font-serif text-xl font-bold"><?php echo htmlspecialchars($item['name']); ?></h3>
<span class="text-gold font-serif"><?php echo the_prime_cut_price($item['price']); ?></span>
</div>
<p class="text-sm font-sans italic opacity-75 text-left"><?php echo htmlspecialchars($item['description'] ?? ''); ?></p>
<?php if (!empty($supportsOrdering) && $itemAvailable): ?><button type="button" class="add-to-bag-btn mt-2 font-sans text-xs uppercase text-gold border border-gold/60 px-3 py-1.5 hover:bg-gold/20 transition-colors inline-block w-auto" data-item-id="<?php echo (int)$item['id']; ?>" data-item-name="<?php echo htmlspecialchars($item['name']); ?>" data-item-price="<?php echo htmlspecialchars($item['price']); ?>" data-item-image="<?php echo !empty($item['image']) ? htmlspecialchars($item['image']) : ''; ?>">Add to bag</button><?php endif; ?>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
</section>
<?php endforeach; ?>
</div>
<?php endforeach; ?>
<footer class="text-center border-t border-gold/20 pt-10 relative z-10" data-purpose="menu-footer">
<?php if (!empty($restaurant['footer_content'])): ?><p class="font-serif italic text-gold text-lg mb-4"><?php echo htmlspecialchars($restaurant['footer_content']); ?></p><?php endif; ?>
<h3 class="font-serif text-gold text-xl mb-3"><?php echo htmlspecialchars($restaurant['name']); ?></h3>
<div class="flex flex-wrap justify-center gap-x-4 gap-y-1 text-sm font-sans uppercase tracking-[0.15em] opacity-80 text-cream">
<?php if (!empty($restaurant['address'])): ?><span><?php echo htmlspecialchars($restaurant['address']); ?></span><?php endif; ?>
<?php if (!empty($restaurant['phone'])): ?><span><?php if (!empty($restaurant['address'])): ?> • <?php endif; ?><a href="tel:<?php echo htmlspecialchars(preg_replace('/\s+/', '', $restaurant['phone'])); ?>" class="text-gold hover:underline"><?php echo htmlspecialchars($restaurant['phone']); ?></a></span><?php endif; ?>
<?php if (!empty($restaurant['email'])): ?><span><?php if (!empty($restaurant['address']) || !empty($restaurant['phone'])): ?> • <?php endif; ?><a href="mailto:<?php echo htmlspecialchars($restaurant['email']); ?>" class="text-gold hover:underline"><?php echo htmlspecialchars($restaurant['email']); ?></a></span><?php endif; ?>
</div>
</footer>
</main>
<?php if (!empty($supportsOrdering)): ?>
<link rel="stylesheet" href="<?php echo rtrim(defined('SITE_URL') ? SITE_URL : $baseUrl, '/'); ?>/assets/css/cart-modal.css">
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
<script>
(function() {
    var toggle = document.getElementById('prime-cut-menu-toggle');
    var sidebar = document.getElementById('prime-cut-category-sidebar');
    var overlay = document.getElementById('prime-cut-sidebar-overlay');
    var closeBtn = document.getElementById('prime-cut-sidebar-close');
    var toggleWrap = document.getElementById('prime-cut-toggle-wrap');
    function openSidebar() {
        if (sidebar) { sidebar.classList.remove('translate-x-full'); }
        if (overlay) { overlay.classList.remove('opacity-0', 'invisible', 'pointer-events-none'); overlay.classList.add('opacity-100'); overlay.style.pointerEvents = 'auto'; }
        if (toggleWrap) { toggleWrap.classList.add('sidebar-open'); }
        document.body.style.overflow = 'hidden';
    }
    function closeSidebar() {
        if (sidebar) { sidebar.classList.add('translate-x-full'); }
        if (overlay) { overlay.classList.add('opacity-0', 'invisible', 'pointer-events-none'); overlay.classList.remove('opacity-100'); overlay.style.pointerEvents = 'none'; }
        if (toggleWrap) { toggleWrap.classList.remove('sidebar-open'); }
        document.body.style.overflow = '';
    }
    if (toggle) { toggle.addEventListener('click', function(e) { e.stopPropagation(); openSidebar(); }); }
    if (closeBtn) { closeBtn.addEventListener('click', function(e) { e.preventDefault(); e.stopPropagation(); closeSidebar(); }); }
    if (overlay) overlay.addEventListener('click', closeSidebar);
    document.querySelectorAll('.prime-cut-nav-link').forEach(function(link) {
        link.addEventListener('click', closeSidebar);
    });
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
