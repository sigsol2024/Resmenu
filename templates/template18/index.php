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
$nfmTemplateBaseUrl = isset($templateAssetBaseUrl) ? $templateAssetBaseUrl : (rtrim($baseUrl, '/') . '/templates/template18');
$nfmPageBgFile = 'bg_white.png';
if (!file_exists($nfmTemplateDir . '/' . $nfmPageBgFile)) {
    $nfmPageBgFile = (file_exists($nfmTemplateDir . '/bg_white.jpg')) ? 'bg_white.jpg' : 'bg_white.png';
}
$reservationUrl = $baseUrl . '/restaurant/' . ($restaurant['slug'] ?? '') . '/reservation';
$currencySymbol = '₦';
$primaryColor = isset($customization['primary_color']) ? $customization['primary_color'] : '#f2b90d';
function nfm_price($p, $s = '₦') {
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
/* Cover / hero: section-specific page uses section banner; else restaurant cover URL or file or logo */
$nfmHeroBgUrl = '';
if (!empty($singleSectionView) && !empty($sections) && is_array($sections) && !empty($sections[0]['image']) && empty($isTemplatePreview)) {
    $nfmHeroBgUrl = $uploadBaseUrl . '/sections/' . htmlspecialchars($sections[0]['image']);
} elseif (!empty($restaurant['hero_image_url'])) {
    $nfmHeroBgUrl = $restaurant['hero_image_url'];
} elseif (!empty($restaurant['hero_image']) && empty($isTemplatePreview)) {
    $nfmHeroBgUrl = $uploadBaseUrl . '/heroes/' . htmlspecialchars($restaurant['hero_image']);
} elseif (!empty($restaurant['logo']) && empty($isTemplatePreview)) {
    $nfmHeroBgUrl = $uploadBaseUrl . '/logos/' . htmlspecialchars($restaurant['logo']);
}
?>
<!DOCTYPE html>
<html lang="en" class="nfm-html"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title><?php echo htmlspecialchars($restaurant['name']); ?><?php if (!empty($singleSectionView) && !empty($sections) && is_array($sections) && !empty($sections[0]['name'])): ?> - <?php echo htmlspecialchars($sections[0]['name']); ?><?php else: ?> - Menu<?php endif; ?></title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Carattere&amp;family=Cinzel:wght@400;700&amp;family=Montserrat:wght@300;400;600&amp;display=swap" rel="stylesheet"/>
<script>
    tailwind.config = { theme: { extend: { colors: { brandGold: '#f2b90d', darkEbony: '#0a0a0a' }, fontFamily: { serif: ['Cinzel', 'serif'], sans: ['Montserrat', 'sans-serif'], carattere: ['Carattere', 'cursive'] } } } }
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
  background-size: 260px 260px;
  opacity: 0.17;
}
@media (min-width: 768px) {
  .nfm-page-bg { background-size: 240px 240px; opacity: 0.14; }
}
.nfm-vignette {
  position: fixed;
  inset: 0;
  z-index: 0;
  pointer-events: none;
  background: linear-gradient(180deg, rgba(26,26,26,0.32) 0%, rgba(0,0,0,0.48) 50%, rgba(0,0,0,0.65) 100%);
}
.nfm-shell { position: relative; z-index: 1; }
.card-border { border: 2px solid #f2b90d; }
/* Per-item row + dotted leader */
.nfm-menu-item {
  background: rgba(10, 10, 12, 0.62);
  border: 1px solid rgba(255, 255, 255, 0.1);
}
.nfm-dot-leader {
  flex: 1 1 0%;
  min-width: 0.5rem;
  border-bottom: 1px dotted rgba(255, 255, 255, 0.4);
  transform: translateY(-0.1em);
}
/* Section + category headings (Carattere) */
.nfm-section-title,
.nfm-category-title {
  font-family: Carattere, cursive;
  font-weight: 400;
  letter-spacing: 0.02em;
  text-transform: none;
}
/* Scroll reveal — slower so motion reads after paint */
.nfm-reveal {
  opacity: 0;
  transform: translateY(28px);
  will-change: opacity, transform;
  transition: opacity 0.95s cubic-bezier(0.22, 1, 0.36, 1), transform 0.95s cubic-bezier(0.22, 1, 0.36, 1);
}
.nfm-reveal.nfm-reveal--in {
  opacity: 1;
  transform: translateY(0);
  will-change: auto;
}
@media (prefers-reduced-motion: reduce) {
  .nfm-reveal { opacity: 1; transform: none; transition: none; }
}
/* Category card: light glass so page texture reads through */
.nfm-cat-panel {
  background: rgba(39, 39, 42, 0.22);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.18);
}
.divider-line { height: 1px; background: linear-gradient(90deg, transparent 0%, #f2b90d 50%, transparent 100%); margin: 1.5rem 0; }
#nfm-menu-toggle:focus-visible { outline: 2px solid #f2b90d; outline-offset: 2px; }
.nfm-hero { min-height: 32vh; min-height: 32dvh; }
@media (min-width: 640px) {
  .nfm-hero { min-height: 36vh; min-height: 36dvh; }
}
@media (min-width: 768px) {
  .nfm-hero { min-height: 38vh; min-height: 38dvh; }
}
@media (min-width: 1024px) {
  .nfm-hero { min-height: 44vh; min-height: 44dvh; }
}
/* Hero text fallbacks if Tailwind CDN is blocked or slow */
.nfm-hero h1 { color: #f2b90d; }
/* Bottom smoke (canvas) — below content, above texture */
#nfm-smoke-wrap {
  position: fixed;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 3;
  pointer-events: none;
  overflow: hidden;
  /* Match JS bandBasePx(): min(52vh, 580px) before footer overlap */
  height: 52vh;
  max-height: 580px;
  transition: height 0.35s ease-out, max-height 0.35s ease-out;
}
#nfm-smoke-canvas {
  display: block;
  width: 100%;
  height: 100%;
  opacity: 1;
}
@media (prefers-reduced-motion: reduce) {
  #nfm-smoke-wrap { display: none !important; }
}
</style>
</head>
<body class="nfm-body font-sans">
<div class="nfm-page-bg" aria-hidden="true"></div>
<div class="nfm-vignette" aria-hidden="true"></div>
<div class="nfm-shell min-h-screen min-w-0">
<div id="nfm-smoke-wrap" aria-hidden="true"><canvas id="nfm-smoke-canvas" width="300" height="200"></canvas></div>
<!-- Slide-out menu (toggle) — not a permanent sidebar rail -->
<div id="nfm-sidebar-overlay" class="pointer-events-none fixed inset-0 z-[55] bg-black/70 opacity-0 invisible transition-opacity duration-200" aria-hidden="true"></div>
<aside id="nfm-sidebar" class="fixed top-0 right-0 z-[60] flex h-full w-[min(100vw-3rem,22rem)] max-w-[90vw] translate-x-full flex-col border-l border-brandGold/40 bg-[#0a0a0a]/95 shadow-2xl backdrop-blur-md transition-transform duration-300 ease-out" aria-label="Menu" aria-hidden="true">
  <div class="flex shrink-0 items-center justify-between border-b border-brandGold/30 px-4 py-4">
    <span class="text-sm font-semibold uppercase tracking-[0.35em] text-white/90">Navigate</span>
    <button type="button" id="nfm-sidebar-close" class="flex h-10 w-10 items-center justify-center rounded border border-white/35 text-white transition-colors hover:bg-white/10" aria-label="Close menu">
      <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M18 6L6 18M6 6l12 12"/></svg>
    </button>
  </div>
  <nav class="nfm-drawer-nav no-scrollbar flex flex-1 flex-col gap-1 overflow-y-auto px-3 py-4 pb-8 font-sans text-sm">
    <?php if (!empty($singleSectionView) && !empty($fullMenuUrl)): ?>
      <a href="<?php echo htmlspecialchars($fullMenuUrl); ?>" class="nfm-drawer-link rounded px-3 py-3 text-center font-semibold uppercase tracking-widest text-white ring-1 ring-white/25 hover:bg-white/10">Full menu</a>
    <?php endif; ?>
    <?php if (!empty($fullMenuUrl)): ?>
      <a href="<?php echo htmlspecialchars($fullMenuUrl); ?>#menu" class="nfm-drawer-link rounded px-3 py-3 text-center font-semibold uppercase tracking-widest text-white/90 ring-1 ring-white/15 hover:bg-white/5">View menu</a>
    <?php endif; ?>
    <?php if (!empty($sectionsForNav) && is_array($sectionsForNav) && !empty($fullMenuUrl)): ?>
      <?php foreach ($sectionsForNav as $navSection): ?>
        <a href="<?php echo htmlspecialchars($fullMenuUrl); ?>#section-<?php echo htmlspecialchars($navSection['slug'] ?? ''); ?>" class="nfm-drawer-link rounded px-3 py-2.5 text-center text-xs font-semibold uppercase tracking-widest text-gray-300 ring-1 ring-white/10 hover:border-brandGold/40 hover:text-brandGold"><?php echo htmlspecialchars($navSection['name'] ?? ''); ?></a>
      <?php endforeach; ?>
    <?php endif; ?>
    <hr class="my-2 border-brandGold/20" />
    <?php foreach ($activeCategories as $i => $cat): $s = isset($cat['slug']) ? $cat['slug'] : ('cat-' . $i); ?>
      <a href="<?php echo htmlspecialchars(!empty($fullMenuUrl) ? ((!empty($singleSectionView) && !empty($sections) && is_array($sections) && !empty($sections[0]['slug'])) ? $fullMenuUrl . '/' . $sections[0]['slug'] . '#' . $s : $fullMenuUrl . '#' . $s) : '#' . $s); ?>" class="nfm-drawer-link rounded px-3 py-2.5 text-center text-xs uppercase tracking-wide text-gray-400 ring-1 ring-white/10 hover:text-brandGold"><?php echo htmlspecialchars($cat['name']); ?></a>
    <?php endforeach; ?>
    <hr class="my-2 border-brandGold/20" />
    <?php if (!empty($supportsReservations)): ?>
      <a href="<?php echo htmlspecialchars($reservationUrl); ?>" class="nfm-drawer-link rounded px-3 py-3 text-center font-semibold uppercase tracking-widest text-white ring-1 ring-white/30 hover:bg-white/10">Reserve table</a>
    <?php endif; ?>
  </nav>
</aside>
<button type="button" id="nfm-menu-toggle" class="fixed right-4 top-4 z-40 flex h-12 w-12 items-center justify-center rounded border-2 border-white/70 bg-black/90 text-white shadow-lg backdrop-blur-sm transition-colors hover:bg-white/10" aria-label="Open menu" aria-expanded="false" aria-controls="nfm-sidebar">
  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
</button>

<section class="nfm-hero relative z-10 w-full overflow-hidden border-b border-brandGold/20" aria-label="Restaurant cover">
  <?php if (!empty($nfmHeroBgUrl)): ?>
  <div class="absolute inset-0 bg-cover bg-center bg-no-repeat" style="background-image:url('<?php echo htmlspecialchars($nfmHeroBgUrl, ENT_QUOTES, 'UTF-8'); ?>')"></div>
  <?php else: ?>
  <div class="absolute inset-0 bg-gradient-to-br from-zinc-800 via-zinc-900 to-black"></div>
  <?php endif; ?>
  <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/35 to-black/15"></div>
  <div class="relative mx-auto flex max-w-4xl flex-col items-center justify-center px-6 pb-14 pt-16 text-center sm:pb-16 sm:pt-20 md:max-w-5xl md:pb-20 md:pt-24 lg:max-w-6xl lg:pb-20 lg:pt-24">
    <?php if (!empty($restaurant['logo']) && empty($isTemplatePreview)): ?>
      <div class="mb-4"><img src="<?php echo $uploadBaseUrl . '/logos/' . htmlspecialchars($restaurant['logo']); ?>" alt="<?php echo htmlspecialchars($restaurant['name']); ?>" class="mx-auto h-16 w-auto max-w-[220px] object-contain md:h-20"/></div>
    <?php endif; ?>
    <?php if (empty($restaurant['logo']) || !empty($isTemplatePreview)): ?>
    <h1 class="font-serif text-3xl uppercase tracking-[0.2em] text-brandGold sm:text-4xl md:text-5xl"><?php echo htmlspecialchars($restaurant['name']); ?></h1>
    <?php endif; ?>
    <?php if (!empty($singleSectionView) && !empty($sections) && is_array($sections) && !empty($sections[0]['name'])): ?>
      <p class="nfm-section-title mt-2 text-2xl text-white md:text-3xl"><?php echo htmlspecialchars($sections[0]['name']); ?></p>
    <?php endif; ?>
    <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
      <?php if (!empty($supportsReservations)): ?>
        <a href="<?php echo htmlspecialchars($reservationUrl); ?>" class="inline-flex items-center justify-center rounded border-2 border-white bg-white px-4 py-1.5 text-[10px] font-semibold uppercase tracking-widest text-black transition-colors hover:bg-white/90 sm:px-5 sm:py-2 sm:text-[11px] md:px-5 md:py-2 md:text-xs">Reserve table</a>
      <?php endif; ?>
      <?php if (!empty($singleSectionView) && !empty($fullMenuUrl)): ?>
        <a href="<?php echo htmlspecialchars($fullMenuUrl); ?>" class="inline-flex items-center justify-center rounded border-2 border-white/80 bg-transparent px-4 py-1.5 text-[10px] font-semibold uppercase tracking-widest text-white transition-colors hover:bg-white/10 sm:px-5 sm:py-2 sm:text-[11px] md:text-xs">Full menu</a>
      <?php endif; ?>
    </div>
  </div>
</section>

<main class="relative z-10 mx-auto w-full max-w-4xl px-4 pb-6 pt-8 sm:px-6 md:max-w-5xl md:px-8 md:pb-10 md:pt-10 lg:max-w-6xl lg:px-10" id="menu">
<?php if (!empty($sections) && is_array($sections)): ?>
<?php foreach ($sections as $section): 
    if (empty($section['categories']) || !is_array($section['categories'])) continue;
?>
<div id="section-<?php echo htmlspecialchars($section['slug']); ?>" class="mb-12 md:mb-16">
<h2 class="nfm-section-title mb-4 text-center <?php echo !empty($singleSectionView) ? 'text-3xl text-gray-400 sm:text-4xl md:mb-4 md:text-4xl lg:text-5xl' : 'text-4xl text-brandGold sm:text-5xl md:mb-4 md:text-5xl lg:text-6xl xl:text-7xl'; ?>"><?php if (!empty($fullMenuUrl) && empty($singleSectionView)): ?><a href="<?php echo htmlspecialchars($fullMenuUrl . '/' . $section['slug']); ?>" class="text-brandGold hover:underline"><?php echo htmlspecialchars($section['name']); ?></a><?php else: ?><?php echo htmlspecialchars($section['name']); ?><?php endif; ?></h2>
<?php if (empty($singleSectionView) && !empty($section['image']) && empty($isTemplatePreview)): ?>
<div class="mx-auto mb-4 max-w-[11.5rem] px-1 sm:mb-5 sm:max-w-xs md:mb-6 md:max-w-md">
  <img src="<?php echo $uploadBaseUrl . '/sections/' . htmlspecialchars($section['image']); ?>" alt="" class="mx-auto max-h-[4.25rem] w-full rounded-md object-contain shadow-md sm:max-h-28 md:max-h-36" loading="lazy" decoding="async"/>
</div>
<?php endif; ?>
<div class="grid grid-cols-1 gap-y-12 gap-x-0 sm:gap-y-14 md:grid-cols-2 md:gap-x-8 md:gap-y-12 lg:gap-x-10 lg:gap-y-14">
<?php foreach ($section['categories'] as $catIndex => $category): 
    $slug = isset($category['slug']) ? $category['slug'] : ('cat-'.$catIndex);
    $items = isset($category['menu_items']) ? $category['menu_items'] : [];
    if (empty($items)) continue;
?>
<section class="card-border flex h-full min-w-0 flex-col rounded-sm border-brandGold/30 bg-transparent p-4 sm:p-5 md:p-5 lg:p-6" id="<?php echo htmlspecialchars($slug); ?>">
<h3 class="nfm-category-title mb-4 flex flex-wrap items-center justify-center gap-3 border-b border-brandGold/25 pb-3 text-center text-5xl leading-tight text-white sm:text-5xl md:mb-5 md:justify-start md:text-left md:text-4xl lg:text-5xl xl:text-6xl">
  <?php if (!empty($category['image']) && empty($isTemplatePreview)): ?>
    <img src="<?php echo $uploadBaseUrl . '/categories/' . htmlspecialchars($category['image']); ?>" alt="" class="h-11 w-11 shrink-0 rounded-full object-cover ring-2 ring-brandGold/45 md:h-10 md:w-10" width="44" height="44" loading="lazy" decoding="async"/>
  <?php endif; ?>
  <span class="min-w-0"><?php echo htmlspecialchars($category['name']); ?></span>
</h3>
<div class="flex flex-col gap-3 md:gap-3.5">
<?php foreach ($items as $item): ?>
<article class="nfm-menu-item nfm-reveal flex min-w-0 items-start gap-3 rounded-md px-3 py-3 sm:px-4 sm:py-3.5">
<?php if (!empty($item['image'])): ?><img src="<?php echo $uploadBaseUrl . '/menu-items/' . htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="h-14 w-14 shrink-0 rounded-md object-cover ring-1 ring-white/15 sm:h-16 sm:w-16 md:h-14 md:w-14" loading="lazy" decoding="async"/><?php endif; ?>
<div class="flex min-w-0 flex-1 flex-col gap-1.5">
<div class="flex w-full min-w-0 items-start gap-3">
<div class="flex min-w-0 flex-1 items-start gap-2">
<span class="line-clamp-2 min-w-0 shrink text-left text-base font-semibold leading-snug text-white"><?php echo htmlspecialchars($item['name']); ?></span>
<span class="nfm-dot-leader mt-[0.55em] min-h-0 min-w-[8px]" aria-hidden="true"></span>
</div>
<span class="shrink-0 rounded-sm bg-white px-2 py-0.5 text-right font-sans text-xs font-semibold tabular-nums leading-none text-black"><?php echo nfm_price($item['price']); ?></span>
</div>
<?php if (!empty($item['description'])): ?><p class="text-xs leading-relaxed text-gray-300 sm:text-sm md:text-[11px] md:leading-snug"><?php echo htmlspecialchars($item['description']); ?></p><?php endif; ?>
<?php if (!empty($supportsOrdering) && !empty($item['is_available'])): ?><button type="button" class="add-to-bag-btn self-start rounded border border-white/50 px-2.5 py-1 text-xs text-white hover:bg-white/10 md:px-2 md:py-0.5 md:text-[10px]" data-item-id="<?php echo (int)$item['id']; ?>" data-item-name="<?php echo htmlspecialchars($item['name']); ?>" data-item-price="<?php echo htmlspecialchars($item['price']); ?>" data-item-image="<?php echo !empty($item['image']) ? htmlspecialchars($item['image']) : ''; ?>">Add to bag</button><?php endif; ?>
</div>
</article>
<?php endforeach; ?>
</div>
</section>
<?php endforeach; ?>
</div>
</div>
<?php endforeach; ?>
<?php endif; ?>

<footer id="nfm-footer" class="relative z-20 mt-4 border-t border-gray-700 bg-[#050505]/95 py-10 text-center backdrop-blur-sm">
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
</main>
</div>

<style>.no-scrollbar::-webkit-scrollbar{display:none}.no-scrollbar{-ms-overflow-style:none;scrollbar-width:none}</style>
<script>
(function(){
  var openBtn = document.getElementById('nfm-menu-toggle');
  var closeBtn = document.getElementById('nfm-sidebar-close');
  var sidebar = document.getElementById('nfm-sidebar');
  var overlay = document.getElementById('nfm-sidebar-overlay');
  function openDrawer() {
    if (sidebar) { sidebar.classList.remove('translate-x-full'); sidebar.setAttribute('aria-hidden', 'false'); }
    if (overlay) {
      overlay.classList.remove('opacity-0', 'invisible', 'pointer-events-none');
      overlay.classList.add('opacity-100');
      overlay.setAttribute('aria-hidden', 'false');
    }
    if (openBtn) openBtn.setAttribute('aria-expanded', 'true');
    document.body.style.overflow = 'hidden';
  }
  function closeDrawer() {
    if (sidebar) { sidebar.classList.add('translate-x-full'); sidebar.setAttribute('aria-hidden', 'true'); }
    if (overlay) {
      overlay.classList.add('opacity-0', 'invisible', 'pointer-events-none');
      overlay.classList.remove('opacity-100');
      overlay.setAttribute('aria-hidden', 'true');
    }
    if (openBtn) openBtn.setAttribute('aria-expanded', 'false');
    document.body.style.overflow = '';
  }
  if (openBtn) openBtn.addEventListener('click', function (e) { e.stopPropagation(); openDrawer(); });
  if (closeBtn) closeBtn.addEventListener('click', function (e) { e.preventDefault(); closeDrawer(); });
  if (overlay) overlay.addEventListener('click', closeDrawer);
  document.querySelectorAll('.nfm-drawer-link').forEach(function (l) { l.addEventListener('click', closeDrawer); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeDrawer(); });
})();
(function(){
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    document.querySelectorAll('.nfm-reveal').forEach(function (el) { el.classList.add('nfm-reveal--in'); });
    return;
  }
  var nodes = document.querySelectorAll('.nfm-reveal');
  if (!nodes.length) return;
  if (!('IntersectionObserver' in window)) {
    nodes.forEach(function (el) { el.classList.add('nfm-reveal--in'); });
    return;
  }
  function armObserver() {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        var el = entry.target;
        io.unobserve(el);
        window.setTimeout(function () { el.classList.add('nfm-reveal--in'); }, 60);
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
</script>

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
<!-- Back to top -->
<a id="scrollToTop" href="#" aria-label="Scroll to top" class="fixed bottom-6 right-6 z-30 flex h-12 w-12 translate-y-2 items-center justify-center rounded-full bg-neutral-900 text-white opacity-0 shadow-lg transition-all hover:bg-black" style="visibility:hidden">
  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 15l-6-6-6 6"/></svg>
</a>
<script>
(function(){var btn=document.getElementById('scrollToTop');if(btn){window.addEventListener('scroll',function(){var st=window.pageYOffset||document.documentElement.scrollTop;var dh=document.documentElement.scrollHeight-window.innerHeight;if(dh>0&&st>=dh*0.3){btn.style.opacity='1';btn.style.visibility='visible';btn.style.transform='translateY(0)';}else{btn.style.opacity='0';btn.style.visibility='hidden';btn.style.transform='translateY(8px)';}});btn.addEventListener('click',function(e){e.preventDefault();window.scrollTo({top:0,behavior:'smooth'});});}})();
</script>
<script>
(function () {
  var wrap = document.getElementById('nfm-smoke-wrap');
  var canvas = document.getElementById('nfm-smoke-canvas');
  var footer = document.getElementById('nfm-footer');
  if (!wrap || !canvas) return;
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

  var ctx = canvas.getContext('2d');
  if (!ctx) return;

  var parts = [];
  var BASE_VH = 0.52;
  var MAX_PX_CAP = 580;
  var MIN_BAND = 56;
  var dpr = 1;

  function bandBasePx() {
    return Math.min(window.innerHeight * BASE_VH, MAX_PX_CAP);
  }

  function updateBandFromFooter() {
    var base = bandBasePx();
    if (!footer || !footer.getBoundingClientRect) {
      wrap.style.height = base + 'px';
      return;
    }
    var rect = footer.getBoundingClientRect();
    var vh = window.innerHeight;
    var overlap = Math.max(0, vh - rect.top);
    var h = Math.max(MIN_BAND, base - overlap * 0.7);
    wrap.style.height = h + 'px';
  }

  function resizeCanvas() {
    dpr = Math.min(window.devicePixelRatio || 1, 2);
    var w = wrap.clientWidth;
    var hh = Math.max(1, wrap.clientHeight);
    canvas.width = Math.floor(w * dpr);
    canvas.height = Math.floor(hh * dpr);
    canvas.style.width = w + 'px';
    canvas.style.height = hh + 'px';
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
  }

  function spawn() {
    var w = wrap.clientWidth;
    var h = wrap.clientHeight;
    if (h < 28 || w < 80) return;
    var scale = Math.max(0.35, Math.min(1, (h - MIN_BAND) / (bandBasePx() - MIN_BAND + 1)));
    var cap = Math.floor((w < 520 ? 58 : 88) * scale);
    if (parts.length >= cap) return;
    if (Math.random() > 0.22) return;
    parts.push({
      x: w * (0.1 + Math.random() * 0.8),
      y: h + 6 + Math.random() * 18,
      vx: (Math.random() - 0.5) * 0.55,
      vy: -(0.38 + Math.random() * 0.72),
      r: 10 + Math.random() * 18,
      a: 0.15 + Math.random() * 0.22,
      grow: 0.16 + Math.random() * 0.28,
      warm: Math.random() > 0.35
    });
  }

  function tick() {
    if (document.hidden) return;
    var w = wrap.clientWidth;
    var h = wrap.clientHeight;
    ctx.clearRect(0, 0, w, h);
    var ceiling = h * 0.07;
    for (var i = parts.length - 1; i >= 0; i--) {
      var p = parts[i];
      p.x += p.vx;
      p.y += p.vy;
      p.r += p.grow;
      p.a *= 0.988;
      p.vy *= 0.997;
      if (p.y < ceiling || p.a < 0.012 || p.r > Math.max(w, h) * 0.52) {
        parts.splice(i, 1);
        continue;
      }
      var g = ctx.createRadialGradient(p.x, p.y, 0, p.x, p.y, p.r);
      if (p.warm) {
        g.addColorStop(0, 'rgba(255,215,170,' + (p.a * 0.82) + ')');
        g.addColorStop(0.28, 'rgba(220,205,190,' + (p.a * 0.55) + ')');
        g.addColorStop(1, 'rgba(45,42,40,0)');
      } else {
        g.addColorStop(0, 'rgba(235,230,225,' + (p.a * 0.65) + ')');
        g.addColorStop(0.35, 'rgba(175,170,165,' + (p.a * 0.42) + ')');
        g.addColorStop(1, 'rgba(32,30,30,0)');
      }
      ctx.fillStyle = g;
      ctx.beginPath();
      ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
      ctx.fill();
    }
    spawn();
    requestAnimationFrame(tick);
  }

  function onLayout() {
    updateBandFromFooter();
    resizeCanvas();
  }

  function init() {
    onLayout();
    window.addEventListener('resize', onLayout, { passive: true });
    window.addEventListener('scroll', updateBandFromFooter, { passive: true });
    document.addEventListener('visibilitychange', function () {
      if (!document.hidden) requestAnimationFrame(tick);
    });
    if (footer && 'IntersectionObserver' in window) {
      new IntersectionObserver(updateBandFromFooter, { threshold: [0, 0.05, 0.1, 0.2, 0.35, 0.5, 0.75, 1] }).observe(footer);
    }
    requestAnimationFrame(tick);
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();
</script>
</body></html>
