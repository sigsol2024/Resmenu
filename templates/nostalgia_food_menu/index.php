<?php
/**
 * Nostalgia Food Menu - Full menu in nostalgia style
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
$nfmTemplateDir = __DIR__;
$nfmTemplateBaseUrl = rtrim($baseUrl, '/') . '/templates/nostalgia_food_menu';
$nfmPageBgFile = 'bg_white.png';
if (!file_exists($nfmTemplateDir . '/' . $nfmPageBgFile)) {
    $nfmPageBgFile = (file_exists($nfmTemplateDir . '/bg_white.jpg')) ? 'bg_white.jpg' : 'bg_white.png';
}
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
$tagline = !empty($restaurant['description']) ? trim($restaurant['description']) : 'Our Menu';
?>
<!DOCTYPE html>
<html lang="en" class="nfm-html"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title><?php echo htmlspecialchars($restaurant['name']); ?><?php if (!empty($singleSectionView) && !empty($sections[0]['name'])): ?> - <?php echo htmlspecialchars($sections[0]['name']); ?><?php else: ?> - Menu<?php endif; ?></title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700&amp;family=Montserrat:wght@300;400;600&amp;display=swap" rel="stylesheet"/>
<script>
    tailwind.config = { theme: { extend: { colors: { brandGold: '#f2b90d', darkEbony: '#0a0a0a' }, fontFamily: { serif: ['Cinzel', 'serif'], sans: ['Montserrat', 'sans-serif'] } } } }
  </script>
<style>
/* Same base on html + body avoids “peeling” / grey flash on mobile overscroll */
html.nfm-html {
  overflow-x: clip;
  overscroll-behavior-y: none;
  background-color: #050505;
  min-height: 100%;
}
body.nfm-body {
  overflow-x: clip;
  overscroll-behavior-y: none;
  background-color: #050505;
  color: #e5e5e5;
  min-height: 100vh;
  min-height: 100dvh;
}
/* Subtle texture (fixed layer — not background-attachment on body) */
.nfm-page-bg {
  position: fixed;
  inset: 0;
  z-index: 0;
  pointer-events: none;
  background-image: url('<?php echo htmlspecialchars($nfmTemplateBaseUrl . '/' . $nfmPageBgFile); ?>');
  background-repeat: repeat;
  background-size: 280px 280px;
  opacity: 0.06;
}
.nfm-vignette {
  position: fixed;
  inset: 0;
  z-index: 0;
  pointer-events: none;
  background: linear-gradient(180deg, rgba(26,26,26,0.92) 0%, rgba(0,0,0,0.97) 55%, #000000 100%);
}
.nfm-shell { position: relative; z-index: 1; }
.card-border { border: 2px solid #f2b90d; }
.divider-line { height: 1px; background: linear-gradient(90deg, transparent 0%, #f2b90d 50%, transparent 100%); margin: 1.5rem 0; }
</style>
</head>
<body class="nfm-body font-sans">
<div class="nfm-page-bg" aria-hidden="true"></div>
<div class="nfm-vignette" aria-hidden="true"></div>
<div class="nfm-shell flex min-h-screen min-w-0">
<!-- Sidebar: sections + categories (same pattern as neo_mex_cantina) -->
<nav class="fixed left-0 top-0 z-40 flex h-screen w-14 sm:w-16 md:w-20 flex-col items-center border-r border-brandGold/25 bg-black/80 py-6 backdrop-blur-md" aria-label="Menu navigation">
  <div class="mb-8 shrink-0">
    <?php if (!empty($restaurant['logo']) && empty($isTemplatePreview)): ?>
      <img src="<?php echo $uploadBaseUrl . '/logos/' . htmlspecialchars($restaurant['logo']); ?>" alt="" class="h-9 w-9 object-contain"/>
    <?php else: ?>
      <span class="flex h-9 w-9 items-center justify-center rounded border border-brandGold/50 text-xs font-serif font-bold text-brandGold"><?php echo strtoupper(substr($restaurant['name'] ?? 'N', 0, 1)); ?></span>
    <?php endif; ?>
  </div>
  <div class="flex flex-1 flex-col items-center justify-center gap-8 overflow-y-auto no-scrollbar py-4">
    <?php if (!empty($singleSectionView) && !empty($fullMenuUrl)): ?>
      <a class="flex max-h-28 flex-col items-center justify-center" href="<?php echo htmlspecialchars($fullMenuUrl); ?>"><span class="origin-center -rotate-90 text-[9px] font-semibold uppercase tracking-[0.2em] text-brandGold hover:text-white">Full menu</span></a>
    <?php endif; ?>
    <?php if (!empty($fullMenuUrl)): ?>
      <a class="flex max-h-28 flex-col items-center justify-center" href="<?php echo htmlspecialchars($fullMenuUrl); ?>#menu"><span class="origin-center -rotate-90 text-[9px] font-semibold uppercase tracking-[0.2em] text-brandGold/80 hover:text-brandGold">Menu</span></a>
    <?php endif; ?>
    <?php if (!empty($sectionsForNav) && is_array($sectionsForNav)): ?>
      <?php foreach ($sectionsForNav as $navSection): ?>
        <a class="flex max-h-36 flex-col items-center justify-center" href="<?php echo htmlspecialchars($fullMenuUrl); ?>#section-<?php echo htmlspecialchars($navSection['slug'] ?? ''); ?>"><span class="line-clamp-2 max-w-[10rem] origin-center -rotate-90 text-center text-[8px] font-semibold uppercase leading-tight tracking-[0.12em] text-gray-400 hover:text-brandGold"><?php echo htmlspecialchars($navSection['name'] ?? ''); ?></span></a>
      <?php endforeach; ?>
    <?php endif; ?>
    <span class="h-6 w-px shrink-0 bg-brandGold/30" aria-hidden="true"></span>
    <?php foreach ($activeCategories as $i => $cat): $s = isset($cat['slug']) ? $cat['slug'] : ('cat-' . $i); ?>
      <a class="group flex max-h-[140px] flex-col items-center justify-center" href="<?php echo htmlspecialchars(!empty($fullMenuUrl) ? ((!empty($singleSectionView) && !empty($sections) && is_array($sections) && !empty($sections[0]['slug'])) ? $fullMenuUrl . '/' . $sections[0]['slug'] . '#' . $s : $fullMenuUrl . '#' . $s) : '#' . $s); ?>">
        <span class="line-clamp-3 max-h-[120px] max-w-[9rem] origin-center -rotate-90 overflow-hidden text-center text-[8px] font-semibold uppercase leading-tight tracking-[0.12em] text-gray-500 group-hover:text-brandGold"><?php echo htmlspecialchars($cat['name']); ?></span>
      </a>
    <?php endforeach; ?>
    <?php if (!empty($supportsReservations)): ?>
      <span class="h-6 w-px shrink-0 bg-brandGold/30" aria-hidden="true"></span>
      <a class="flex max-h-28 flex-col items-center justify-center" href="<?php echo htmlspecialchars($reservationUrl); ?>"><span class="origin-center -rotate-90 text-[9px] font-semibold uppercase tracking-[0.2em] text-brandGold hover:text-white">Reserve</span></a>
    <?php endif; ?>
  </div>
</nav>

<main class="relative z-10 min-w-0 flex-1 pl-14 sm:pl-16 md:pl-20 p-6 md:p-12" id="menu">
<div class="mx-auto max-w-4xl">
<header class="mb-16 text-center">
<?php if (!empty($restaurant['logo']) && empty($isTemplatePreview)): ?><div class="mb-4"><img src="<?php echo $uploadBaseUrl . '/logos/' . htmlspecialchars($restaurant['logo']); ?>" alt="<?php echo htmlspecialchars($restaurant['name']); ?>" class="mx-auto h-20 w-auto object-contain"/></div><?php endif; ?>
<h1 class="mb-4 font-serif text-4xl uppercase tracking-widest text-brandGold md:text-6xl"><?php echo htmlspecialchars($restaurant['name']); ?></h1>
<div class="divider-line mx-auto max-w-xs"></div>
<p class="text-gray-400 italic"><?php echo htmlspecialchars($tagline); ?></p>
<?php if (!empty($singleSectionView) && !empty($fullMenuUrl)): ?><p class="mt-2"><a href="<?php echo htmlspecialchars($fullMenuUrl); ?>" class="text-brandGold hover:underline">Full menu</a></p><?php endif; ?>
<?php if (!empty($supportsReservations)): ?><p class="mt-2"><a href="<?php echo htmlspecialchars($reservationUrl); ?>" class="text-brandGold hover:underline">Reserve Table</a></p><?php endif; ?>
</header>
<?php foreach ($sections as $section): 
    if (empty($section['categories']) || !is_array($section['categories'])) continue;
?>
<div id="section-<?php echo htmlspecialchars($section['slug']); ?>" class="mb-14">
<h2 class="mb-8 text-center font-serif text-2xl font-bold uppercase tracking-widest text-brandGold md:text-3xl"><?php if (!empty($fullMenuUrl) && empty($singleSectionView)): ?><a href="<?php echo htmlspecialchars($fullMenuUrl . '/' . $section['slug']); ?>" class="text-brandGold hover:underline"><?php echo htmlspecialchars($section['name']); ?></a><?php else: ?><?php echo htmlspecialchars($section['name']); ?><?php endif; ?></h2>
<?php foreach ($section['categories'] as $catIndex => $category): 
    $slug = isset($category['slug']) ? $category['slug'] : ('cat-'.$catIndex);
    $items = isset($category['menu_items']) ? $category['menu_items'] : [];
    if (empty($items)) continue;
?>
<section class="card-border mb-16 bg-black/40 p-8 backdrop-blur-sm" id="<?php echo htmlspecialchars($slug); ?>">
<h3 class="mb-8 border-b border-brandGold/50 pb-4 font-serif text-2xl uppercase tracking-widest text-brandGold"><?php echo htmlspecialchars($category['name']); ?></h3>
<div class="space-y-6">
<?php foreach ($items as $item): ?>
<div class="flex items-start gap-4">
<?php if (!empty($item['image'])): ?><img src="<?php echo $uploadBaseUrl . '/menu-items/' . htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="h-20 w-20 flex-shrink-0 rounded object-cover"/><?php endif; ?>
<div class="flex min-w-0 flex-1 items-baseline justify-between gap-4">
<div>
<h3 class="text-xl font-semibold text-white"><?php echo htmlspecialchars($item['name']); ?></h3>
<p class="mt-1 text-sm text-gray-400"><?php echo htmlspecialchars($item['description'] ?? ''); ?></p>
<?php if (!empty($supportsOrdering) && !empty($item['is_available'])): ?><button type="button" class="add-to-bag-btn mt-2 rounded border border-brandGold px-3 py-1.5 text-brandGold hover:bg-brandGold hover:text-black" data-item-id="<?php echo (int)$item['id']; ?>" data-item-name="<?php echo htmlspecialchars($item['name']); ?>" data-item-price="<?php echo htmlspecialchars($item['price']); ?>" data-item-image="<?php echo !empty($item['image']) ? htmlspecialchars($item['image']) : ''; ?>">Add to bag</button><?php endif; ?>
</div>
<span class="whitespace-nowrap font-serif text-brandGold"><?php echo nfm_price($item['price']); ?></span>
</div>
</div>
<?php endforeach; ?>
</div>
</section>
<?php endforeach; ?>
</div>
<?php endforeach; ?>

<footer class="mt-4 border-t border-gray-700 py-10 text-center">
  <div class="font-serif text-2xl uppercase tracking-[0.25em] text-brandGold md:text-3xl"><?php echo htmlspecialchars($restaurant['name'] ?? ''); ?></div>
  <?php if (!empty($restaurant['address']) || !empty($restaurant['phone']) || !empty($restaurant['email'])): ?>
  <div class="mx-auto mt-6 flex max-w-2xl flex-wrap justify-center gap-x-4 gap-y-2 text-sm text-gray-400">
    <?php if (!empty($restaurant['address'])): ?><span class="max-w-full"><?php echo nl2br(htmlspecialchars($restaurant['address'])); ?></span><?php endif; ?>
    <?php if (!empty($restaurant['phone'])): ?>
      <span><?php if (!empty($restaurant['address'])): ?><span class="text-brandGold/40" aria-hidden="true"> · </span><?php endif; ?>
      <a href="tel:<?php echo htmlspecialchars(preg_replace('/\s+/', '', $restaurant['phone'])); ?>" class="text-brandGold hover:underline"><?php echo htmlspecialchars($restaurant['phone']); ?></a></span>
    <?php endif; ?>
    <?php if (!empty($restaurant['email'])): ?>
      <span><?php if (!empty($restaurant['address']) || !empty($restaurant['phone'])): ?><span class="text-brandGold/40" aria-hidden="true"> · </span><?php endif; ?>
      <a href="mailto:<?php echo htmlspecialchars($restaurant['email']); ?>" class="text-brandGold hover:underline"><?php echo htmlspecialchars($restaurant['email']); ?></a></span>
    <?php endif; ?>
  </div>
  <?php endif; ?>
  <?php if (!empty($restaurant['footer_content'])): ?>
    <div class="mx-auto mt-8 max-w-2xl border-t border-gray-800 pt-8 text-sm leading-relaxed text-gray-400"><?php echo nl2br(htmlspecialchars($restaurant['footer_content'])); ?></div>
  <?php elseif (empty($restaurant['address']) && empty($restaurant['phone']) && empty($restaurant['email'])): ?>
    <p class="mt-6 text-sm text-gray-500">Thank you for dining with us.</p>
  <?php endif; ?>
</footer>
</div>
</main>
</div>

<style>.no-scrollbar::-webkit-scrollbar{display:none}.no-scrollbar{-ms-overflow-style:none;scrollbar-width:none}</style>

<?php if (!empty($supportsOrdering)): ?>
<link rel="stylesheet" href="<?php echo rtrim(defined('SITE_URL') ? SITE_URL : $baseUrl, '/'); ?>/assets/css/cart-modal.css">
<div id="resmenu-cart-widget" class="fixed bottom-6 left-20 z-50 hidden sm:left-24"></div>
<script src="<?php echo rtrim(defined('SITE_URL') ? SITE_URL : $baseUrl, '/'); ?>/assets/js/cart.js"></script>
<script src="<?php echo rtrim(defined('SITE_URL') ? SITE_URL : $baseUrl, '/'); ?>/assets/js/cart-widget.js"></script>
<script src="<?php echo rtrim(defined('SITE_URL') ? SITE_URL : $baseUrl, '/'); ?>/assets/js/cart-modal.js"></script>
<script>
(function(){var baseUrl=<?php echo json_encode($baseUrl); ?>;var slug=<?php echo json_encode($restaurant['slug']??''); ?>;var config={restaurantSlug:slug,currencySymbol:<?php echo json_encode($currencySymbol); ?>,uploadBaseUrl:<?php echo json_encode($uploadBaseUrl??''); ?>,checkoutUrl:baseUrl+'/restaurant/'+slug+'/checkout',primaryColor:<?php echo json_encode($primaryColor); ?>,deliveryFee:0,taxRate:0};window.RESMENU_CART_CONFIG=config;if(window.RESMENU_CART_MODAL)window.RESMENU_CART_MODAL.init(config);if(window.RESMENU_CART_WIDGET)window.RESMENU_CART_WIDGET.init(config);document.querySelectorAll('.add-to-bag-btn').forEach(function(btn){btn.addEventListener('click',function(){var id=this.getAttribute('data-item-id'),name=this.getAttribute('data-item-name'),price=this.getAttribute('data-item-price'),image=this.getAttribute('data-item-image')||'';if(window.RESMENU_CART)window.RESMENU_CART.addItem(slug,{id:id,name:name,price:price,image:image},1);});});})();
</script>
<?php endif; ?>
<!-- Back to top -->
<a id="scrollToTop" href="#" aria-label="Scroll to top" class="fixed bottom-6 right-6 z-30 flex h-12 w-12 translate-y-2 items-center justify-center rounded-full bg-neutral-900 text-white opacity-0 shadow-lg transition-all hover:bg-black" style="visibility:hidden">
  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 15l-6-6-6 6"/></svg>
</a>
<script>
(function(){var btn=document.getElementById('scrollToTop');if(btn){window.addEventListener('scroll',function(){var st=window.pageYOffset||document.documentElement.scrollTop;var dh=document.documentElement.scrollHeight-window.innerHeight;if(dh>0&&st>=dh*0.3){btn.style.opacity='1';btn.style.visibility='visible';btn.style.transform='translateY(0)';}else{btn.style.opacity='0';btn.style.visibility='hidden';btn.style.transform='translateY(8px)';}});btn.addEventListener('click',function(e){e.preventDefault();window.scrollTo({top:0,behavior:'smooth'});});}})();
</script>
</body></html>
