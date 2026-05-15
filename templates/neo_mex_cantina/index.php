<?php
/**
 * Neo Mex Cantina - Tech-forward cantina
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
$nmcTemplateDir = __DIR__;
$nmcTemplateBaseUrl = rtrim($baseUrl, '/') . '/templates/neo_mex_cantina';
$nmcBgCandidates = [
    'binding_dark.webp',
    'binding_dark.jpg',
    'binding_dark.png',
    '( binding_dark ).png',
    '(binding_dark).png',
];
$nmcBgFile = '';
foreach ($nmcBgCandidates as $f) {
    if (file_exists($nmcTemplateDir . DIRECTORY_SEPARATOR . $f)) {
        $nmcBgFile = $f;
        break;
    }
}
if ($nmcBgFile === '' && is_dir($nmcTemplateDir)) {
    $nmcDirList = @scandir($nmcTemplateDir);
    if (is_array($nmcDirList)) {
        natcasesort($nmcDirList);
        foreach ($nmcDirList as $f) {
            if ($f === '.' || $f === '..') {
                continue;
            }
            if (!preg_match('/\.(webp|jpe?g|png)$/i', $f)) {
                continue;
            }
            if (stripos($f, 'binding_dark') === false) {
                continue;
            }
            $nmcBgFile = $f;
            break;
        }
    }
}
$nmcBgCssUrl = '';
if ($nmcBgFile !== '') {
    $nmcBgCssUrl = htmlspecialchars($nmcTemplateBaseUrl . '/' . rawurlencode($nmcBgFile), ENT_QUOTES, 'UTF-8');
}
$reservationUrl = $baseUrl . '/restaurant/' . ($restaurant['slug'] ?? '') . '/reservation';
$currencySymbol = '₦';
$primaryColor = isset($customization['primary_color']) ? $customization['primary_color'] : '#f97316';
function nmc_price($p, $s = '₦') {
    $n = (float)$p;
    if ($n == 0.0) return '';
    return $s . number_format($n, 2);
}
$activeCategories = [];
$nmcCatSeen = [];
if (!empty($sections) && is_array($sections)) {
    foreach ($sections as $sec) {
        if (empty($sec['categories']) || !is_array($sec['categories'])) {
            continue;
        }
        foreach ($sec['categories'] as $ci => $c) {
            if (empty($c['menu_items']) || !is_array($c['menu_items']) || empty($c['is_active'])) {
                continue;
            }
            $anchor = (!empty($c['slug']) && (string) $c['slug'] !== '')
                ? (string) $c['slug']
                : (($sec['slug'] ?? 'section') . '-cat-' . (int) $ci);
            if (isset($nmcCatSeen[$anchor])) {
                continue;
            }
            $nmcCatSeen[$anchor] = true;
            $c['__nmc_anchor'] = $anchor;
            $activeCategories[] = $c;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="nmc-html"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title><?php echo htmlspecialchars($restaurant['name']); ?><?php if (!empty($singleSectionView) && !empty($sections[0]['name'])): ?> - <?php echo htmlspecialchars($sections[0]['name']); ?><?php endif; ?></title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script>
    tailwind.config = { theme: { extend: { colors: { cantina: { dark: '#0a0a0c', purple: '#7c3aed', orange: '#f97316' } }, backgroundImage: { 'gradient-radial': 'radial-gradient(var(--tw-gradient-stops))' } } } }
  </script>
<style>
html.nmc-html { overflow-x: clip; min-height: 100%; background-color: #0a0a0c; }
body.nmc-body { overflow-x: clip; min-height: 100vh; min-height: 100dvh; }
.nmc-page-bg {
  position: fixed;
  inset: 0;
  z-index: 0;
  pointer-events: none;
  <?php if ($nmcBgCssUrl !== ''): ?>
  background-image: url('<?php echo $nmcBgCssUrl; ?>');
  background-repeat: repeat;
  background-size: 168px 168px;
  background-position: 0 0;
  <?php else: ?>
  background: linear-gradient(160deg, #0a0a0c 0%, #1a1025 50%, #0a0a0c 100%);
  <?php endif; ?>
}
.nmc-page-bg::after {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(180deg, rgba(10,10,12,0.55) 0%, rgba(10,10,12,0.72) 100%);
  pointer-events: none;
}
.nmc-shell { position: relative; z-index: 1; }
.brand-gradient { background: linear-gradient(135deg, #f97316 0%, #7c3aed 100%); }
.glass-card { background: rgba(255,255,255,0.03); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.1); }
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
.nmc-reveal {
  opacity: 0;
  transform: translateY(24px);
  will-change: opacity, transform;
  transition: opacity 0.85s cubic-bezier(0.22, 1, 0.36, 1), transform 0.85s cubic-bezier(0.22, 1, 0.36, 1);
}
.nmc-reveal.nmc-reveal--in {
  opacity: 1;
  transform: translateY(0);
  will-change: auto;
}
@media (prefers-reduced-motion: reduce) {
  .nmc-reveal { opacity: 1; transform: none; transition: none; }
}
@media (min-width: 768px) {
  .nmc-page-bg { background-size: 192px 192px; }
}
#nmc-category-drawer:focus { outline: none; }
#nmc-category-toggle:focus-visible {
  outline: 2px solid rgba(249, 115, 22, 0.85);
  outline-offset: 3px;
}
</style>
</head>
<body class="nmc-body bg-transparent text-slate-100 font-sans selection:bg-orange-500/30">
<div class="nmc-page-bg" aria-hidden="true"></div>
<div class="nmc-shell flex min-h-screen min-w-0 overflow-x-hidden">
<div id="nmc-category-overlay" class="pointer-events-none fixed inset-0 z-[55] invisible bg-black/75 opacity-0 transition-opacity duration-200 lg:hidden" aria-hidden="true"></div>
<aside id="nmc-category-drawer" class="fixed top-0 right-0 z-[60] flex h-full w-[min(100vw-3rem,22rem)] max-w-[min(92vw,22rem)] translate-x-full flex-col border-l border-orange-500/35 bg-[#0a0a0c]/96 shadow-[0_0_40px_rgba(124,58,237,0.12)] backdrop-blur-xl transition-transform duration-300 ease-out lg:hidden" aria-label="Categories" aria-hidden="true" role="dialog">
<div class="flex shrink-0 items-center justify-between gap-3 border-b border-white/10 px-4 py-4">
<span class="bg-gradient-to-r from-orange-500 to-purple-600 bg-clip-text text-base font-black uppercase tracking-[0.2em] text-transparent">Categories</span>
<button type="button" id="nmc-category-close" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-white/20 text-white transition-colors hover:border-orange-500/60 hover:bg-orange-500/10" aria-label="Close categories">
<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M18 6L6 18M6 6l12 12"/></svg>
</button>
</div>
<nav class="no-scrollbar flex flex-1 flex-col gap-1 overflow-y-auto px-3 py-4 pb-8">
<?php if (!empty($activeCategories) && !empty($fullMenuUrl)): ?>
<?php foreach ($activeCategories as $i => $cat):
    $catNavSlug = isset($cat['__nmc_anchor']) ? (string) $cat['__nmc_anchor'] : (isset($cat['slug']) ? (string) $cat['slug'] : ('cat-' . $i));
    $catNavHref = htmlspecialchars(!empty($fullMenuUrl) ? ((!empty($singleSectionView) && !empty($sections) && is_array($sections) && !empty($sections[0]['slug'])) ? $fullMenuUrl . '/' . $sections[0]['slug'] . '#' . $catNavSlug : $fullMenuUrl . '#' . $catNavSlug) : '#' . $catNavSlug, ENT_QUOTES, 'UTF-8');
?>
<a class="nmc-drawer-link rounded-lg border border-white/5 bg-white/[0.04] px-3 py-3 text-sm font-semibold text-slate-200 transition-colors hover:border-orange-500/40 hover:bg-orange-500/10 hover:text-orange-400" href="<?php echo $catNavHref; ?>"><?php echo htmlspecialchars($cat['name'] ?? ''); ?></a>
<?php endforeach; ?>
<?php elseif (!empty($activeCategories)): ?>
<?php foreach ($activeCategories as $i => $cat):
    $catNavSlug = isset($cat['__nmc_anchor']) ? (string) $cat['__nmc_anchor'] : (isset($cat['slug']) ? (string) $cat['slug'] : ('cat-' . $i));
?>
<a class="nmc-drawer-link rounded-lg border border-white/5 bg-white/[0.04] px-3 py-3 text-sm font-semibold text-slate-200" href="#<?php echo htmlspecialchars($catNavSlug); ?>"><?php echo htmlspecialchars($cat['name'] ?? ''); ?></a>
<?php endforeach; ?>
<?php else: ?>
<p class="px-2 text-sm text-slate-500">No categories to show.</p>
<?php endif; ?>
</nav>
</aside>
<nav class="fixed left-0 top-0 z-50 flex h-screen w-14 shrink-0 flex-col items-center border-r border-white/10 bg-black/40 py-4 backdrop-blur-md md:w-32 md:py-8" aria-label="Section menu">
<div class="flex w-full shrink-0 flex-col items-center gap-3 px-1 md:gap-4">
<div class="shrink-0">
<?php if (!empty($restaurant['logo']) && empty($isTemplatePreview)): ?><img src="<?php echo $uploadBaseUrl . '/logos/' . htmlspecialchars($restaurant['logo']); ?>" alt="<?php echo htmlspecialchars($restaurant['name']); ?>" class="h-10 w-10 rounded-xl object-contain md:h-12 md:w-12"/><?php else: ?><div class="flex h-10 w-10 rotate-12 items-center justify-center rounded-xl font-black text-xl brand-gradient md:h-12 md:w-12 md:text-2xl"><?php echo strtoupper(substr($restaurant['name'], 0, 1)); ?></div><?php endif; ?>
</div>
<button type="button" id="nmc-category-toggle" class="lg:hidden flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-orange-500/40 bg-gradient-to-br from-orange-500/15 to-purple-600/15 text-orange-400 shadow-inner shadow-orange-500/10 transition-colors hover:border-orange-400 hover:text-orange-300" aria-controls="nmc-category-drawer" aria-expanded="false" aria-label="Open categories menu">
<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
</button>
</div>
<div class="flex min-h-0 w-full flex-1 flex-col items-center overflow-hidden px-0.5 pb-2">
<div class="no-scrollbar flex w-full flex-col items-center gap-y-10 overflow-y-auto overflow-x-hidden py-5 md:gap-y-12 md:py-6">
<?php if (!empty($singleSectionView) && !empty($fullMenuUrl)): ?><div class="flex w-full min-h-[2.75rem] items-center justify-center py-2"><a class="inline-flex max-w-full origin-center -rotate-90 whitespace-nowrap px-1 text-[9px] font-bold uppercase leading-none tracking-widest text-orange-500 hover:opacity-100 md:text-[10px]" href="<?php echo htmlspecialchars($fullMenuUrl); ?>">Full menu</a></div><?php endif; ?>
<?php if (!empty($fullMenuUrl)): ?><div class="flex w-full min-h-[2.75rem] items-center justify-center py-2"><a class="inline-flex max-w-full origin-center -rotate-90 whitespace-nowrap px-1 text-[9px] font-bold uppercase leading-none tracking-widest text-orange-500 hover:opacity-100 md:text-[10px]" href="<?php echo htmlspecialchars($fullMenuUrl); ?>#menu">View menu</a></div><?php endif; ?>
<?php
if (!empty($sectionsForNav) && is_array($sectionsForNav) && !empty($fullMenuUrl)):
    $nmcNavSeen = [];
    foreach ($sectionsForNav as $navSection):
        $nsk = isset($navSection['slug']) ? (string)$navSection['slug'] : '';
        if ($nsk === '' || isset($nmcNavSeen[$nsk])) continue;
        $nmcNavSeen[$nsk] = true;
?>
<div class="flex w-full min-h-[3.25rem] items-center justify-center py-2 md:min-h-[3.5rem]"><a class="inline-flex max-w-full origin-center -rotate-90 whitespace-nowrap px-1 text-[9px] font-bold uppercase leading-none tracking-widest text-orange-500 opacity-90 hover:opacity-100 md:text-[10px]" href="<?php echo htmlspecialchars($fullMenuUrl); ?>#section-<?php echo htmlspecialchars($nsk); ?>"><?php echo htmlspecialchars($navSection['name'] ?? ''); ?></a></div>
<?php endforeach; endif; ?>
<?php if (!empty($supportsReservations)): ?><div class="flex w-full min-h-[2.75rem] items-center justify-center py-2"><a class="inline-flex max-w-full origin-center -rotate-90 whitespace-nowrap px-1 text-[9px] font-bold uppercase leading-none tracking-widest text-orange-500 md:text-[10px]" href="<?php echo htmlspecialchars($reservationUrl); ?>">Reserve Table</a></div><?php endif; ?>
</div>
</div>
</nav>
<main class="ml-14 box-border min-w-0 max-w-full flex-1 overflow-x-hidden p-5 sm:p-8 lg:p-16 md:ml-32" id="menu">
<header class="mb-16 text-center md:mb-24">
<h1 class="w-full text-5xl font-black text-white md:text-7xl"><?php echo htmlspecialchars($restaurant['name']); ?></h1>
<p class="mx-auto mt-4 max-w-2xl text-slate-300"><?php echo htmlspecialchars($restaurant['description'] ?? 'Modern Tech-Forward Cantina'); ?></p>
</header>
<?php foreach ($sections as $section):
    if (empty($section['categories']) || !is_array($section['categories'])) continue;
?>
<div id="section-<?php echo htmlspecialchars($section['slug']); ?>" class="mb-14 min-w-0">
<h2 class="mb-6 text-center text-2xl font-bold text-orange-500 md:mb-8 md:text-3xl"><?php if (!empty($fullMenuUrl) && empty($singleSectionView)): ?><a href="<?php echo htmlspecialchars($fullMenuUrl . '/' . $section['slug']); ?>" class="text-orange-500 hover:underline"><?php echo htmlspecialchars($section['name']); ?></a><?php else: ?><?php echo htmlspecialchars($section['name']); ?><?php endif; ?></h2>
<?php if (empty($singleSectionView) && !empty($section['image']) && empty($isTemplatePreview)): ?>
<div class="mx-auto mb-8 max-w-md px-2 sm:max-w-lg md:max-w-2xl">
<img src="<?php echo $uploadBaseUrl . '/sections/' . htmlspecialchars($section['image']); ?>" alt="" class="mx-auto max-h-40 w-full rounded-lg object-cover shadow-lg sm:max-h-48 md:max-h-56" loading="lazy" decoding="async"/>
</div>
<?php endif; ?>
<?php foreach ($section['categories'] as $catIndex => $category):
    $slug = (!empty($category['slug']) && (string) $category['slug'] !== '')
        ? (string) $category['slug']
        : (($section['slug'] ?? 'section') . '-cat-' . (int) $catIndex);
    $items = isset($category['menu_items']) ? $category['menu_items'] : [];
    if (empty($items)) continue;
?>
<section class="nmc-reveal mb-20 min-w-0 rounded-2xl glass-card p-6 sm:p-8" id="<?php echo htmlspecialchars($slug); ?>">
<div class="mb-8 min-w-0">
<h3 class="inline-block w-full max-w-full border-b-2 border-orange-500 pb-2 text-2xl font-bold text-white"><?php echo htmlspecialchars($category['name']); ?></h3>
<?php if (!empty($category['image']) && empty($isTemplatePreview)): ?>
<div class="mt-4 max-w-md">
<img src="<?php echo $uploadBaseUrl . '/categories/' . htmlspecialchars($category['image']); ?>" alt="" class="h-auto max-h-48 w-full rounded-lg object-cover object-center ring-1 ring-white/15" loading="lazy" decoding="async"/>
</div>
<?php endif; ?>
</div>
<div class="space-y-6">
<?php foreach ($items as $item): ?>
<div class="flex min-w-0 gap-3 border-b border-white/10 pb-4 sm:gap-4">
<?php if (!empty($item['image'])): ?><img src="<?php echo $uploadBaseUrl . '/menu-items/' . htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="h-16 w-16 shrink-0 rounded object-cover sm:h-20 sm:w-20"/><?php endif; ?>
<div class="min-w-0 flex-1">
<div class="grid grid-cols-[minmax(0,1fr)_auto] gap-x-3 gap-y-1 items-start">
<h4 class="col-start-1 row-start-1 min-w-0 text-left text-lg font-semibold leading-snug text-slate-100"><?php echo htmlspecialchars($item['name']); ?></h4>
<span class="col-start-2 row-start-1 <?php echo !empty($item['description']) ? 'row-span-2' : 'row-span-1'; ?> self-start justify-self-end font-mono text-sm tabular-nums leading-snug text-red-500 sm:text-base"><?php echo nmc_price($item['price']); ?></span>
<?php if (!empty($item['description'])): ?><p class="col-start-1 row-start-2 min-w-0 text-left text-sm leading-relaxed text-slate-400"><?php echo htmlspecialchars($item['description']); ?></p><?php endif; ?>
</div>
<?php if (!empty($supportsOrdering) && !empty($item['is_available'])): ?><button type="button" class="add-to-bag-btn mt-2 rounded border border-orange-500/60 px-3 py-1.5 text-orange-500 hover:bg-orange-500/20" data-item-id="<?php echo (int)$item['id']; ?>" data-item-name="<?php echo htmlspecialchars($item['name']); ?>" data-item-price="<?php echo htmlspecialchars($item['price']); ?>" data-item-image="<?php echo !empty($item['image']) ? htmlspecialchars($item['image']) : ''; ?>">Add to bag</button><?php endif; ?>
</div>
</div>
<?php endforeach; ?>
</div>
</section>
<?php endforeach; ?>
</div>
<?php endforeach; ?>
<footer class="mt-16 border-t border-white/10 pt-8 text-center text-sm text-slate-500">
<?php if (!empty($restaurant['footer_content'])): ?><p class="mb-4"><?php echo nl2br(htmlspecialchars($restaurant['footer_content'])); ?></p><?php endif; ?>
<?php if (!empty($restaurant['address'])): ?><p><?php echo htmlspecialchars($restaurant['address']); ?></p><?php endif; ?>
</footer>
</main>
</div>
<?php if (!empty($supportsOrdering)): ?>
<?php $nmcAssetBase = rtrim((defined('SITE_URL') && (string)SITE_URL !== '') ? SITE_URL : $baseUrl, '/'); ?>
<link rel="stylesheet" href="<?php echo $nmcAssetBase; ?>/assets/css/cart-modal.css">
<div id="resmenu-cart-widget" class="fixed bottom-6 left-[4.25rem] z-50 hidden md:left-40"></div>
<script src="<?php echo $nmcAssetBase; ?>/assets/js/cart.js"></script>
<script src="<?php echo $nmcAssetBase; ?>/assets/js/cart-widget.js"></script>
<script src="<?php echo $nmcAssetBase; ?>/assets/js/cart-modal.js"></script>
<script>
(function(){var baseUrl=<?php echo json_encode($baseUrl); ?>;var slug=<?php echo json_encode($restaurant['slug']??''); ?>;var config={restaurantSlug:slug,currencySymbol:<?php echo json_encode($currencySymbol); ?>,uploadBaseUrl:<?php echo json_encode($uploadBaseUrl??''); ?>,checkoutUrl:baseUrl+'/restaurant/'+slug+'/checkout',primaryColor:<?php echo json_encode($primaryColor); ?>,deliveryFee:0,taxRate:0};window.RESMENU_CART_CONFIG=config;if(window.RESMENU_CART_MODAL)window.RESMENU_CART_MODAL.init(config);if(window.RESMENU_CART_WIDGET)window.RESMENU_CART_WIDGET.init(config);document.querySelectorAll('.add-to-bag-btn').forEach(function(btn){btn.addEventListener('click',function(){var id=this.getAttribute('data-item-id'),name=this.getAttribute('data-item-name'),price=this.getAttribute('data-item-price'),image=this.getAttribute('data-item-image')||'';if(window.RESMENU_CART)window.RESMENU_CART.addItem(slug,{id:id,name:name,price:price,image:image},1);});});})();
</script>
<?php endif; ?>
<script>
(function(){
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    document.querySelectorAll('.nmc-reveal').forEach(function (el) { el.classList.add('nmc-reveal--in'); });
    return;
  }
  var nodes = document.querySelectorAll('.nmc-reveal');
  if (!nodes.length) return;
  if (!('IntersectionObserver' in window)) {
    nodes.forEach(function (el) { el.classList.add('nmc-reveal--in'); });
    return;
  }
  function armObserver() {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        var el = entry.target;
        io.unobserve(el);
        window.setTimeout(function () { el.classList.add('nmc-reveal--in'); }, 60);
      });
    }, { root: null, rootMargin: '0px 0px 8% 0px', threshold: 0.01 });
    nodes.forEach(function (el) { io.observe(el); });
  }
  function deferArm() {
    window.requestAnimationFrame(function () {
      window.requestAnimationFrame(function () {
        window.setTimeout(armObserver, 140);
      });
    });
  }
  if (document.readyState === 'complete') deferArm();
  else window.addEventListener('load', deferArm);
})();
(function () {
  var openBtn = document.getElementById('nmc-category-toggle');
  var closeBtn = document.getElementById('nmc-category-close');
  var drawer = document.getElementById('nmc-category-drawer');
  var overlay = document.getElementById('nmc-category-overlay');
  if (!drawer || !overlay) return;
  function openDrawer() {
    drawer.classList.remove('translate-x-full');
    drawer.setAttribute('aria-hidden', 'false');
    overlay.classList.remove('opacity-0', 'invisible', 'pointer-events-none');
    overlay.classList.add('opacity-100');
    overlay.setAttribute('aria-hidden', 'false');
    if (openBtn) openBtn.setAttribute('aria-expanded', 'true');
    document.body.style.overflow = 'hidden';
  }
  function closeDrawer() {
    drawer.classList.add('translate-x-full');
    drawer.setAttribute('aria-hidden', 'true');
    overlay.classList.add('opacity-0', 'invisible', 'pointer-events-none');
    overlay.classList.remove('opacity-100');
    overlay.setAttribute('aria-hidden', 'true');
    if (openBtn) openBtn.setAttribute('aria-expanded', 'false');
    document.body.style.overflow = '';
  }
  if (openBtn) openBtn.addEventListener('click', function (e) { e.stopPropagation(); openDrawer(); });
  if (closeBtn) closeBtn.addEventListener('click', function (e) { e.preventDefault(); closeDrawer(); });
  overlay.addEventListener('click', closeDrawer);
  document.querySelectorAll('.nmc-drawer-link').forEach(function (l) { l.addEventListener('click', closeDrawer); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeDrawer(); });
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
