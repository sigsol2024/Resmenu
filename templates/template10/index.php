<?php
/**
 * Salt N Socials White - Clean menu style (design reference)
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
$snswTemplateBaseUrl = isset($templateAssetBaseUrl) ? $templateAssetBaseUrl : (rtrim($baseUrl, '/') . '/templates/template10');
$snswTemplateDir = __DIR__;
$snswPageBgFile = (file_exists($snswTemplateDir . '/Salt-Social-2-copy-1.png')) ? 'Salt-Social-2-copy-1.png' : 'Salt-Social-2-copy-1.jpg';
$snswSideBgFile = (file_exists($snswTemplateDir . '/side-bg-scaled.png')) ? 'side-bg-scaled.png' : 'side-bg-scaled.jpg';
$currencySymbol = '₦';
$primaryColor = isset($customization['primary_color']) ? $customization['primary_color'] : '#0D2633';
function snsw_price($p, $s = '₦') {
    return formatPrice($p, $s);
}
$activeCategories = [];
$snswCatSeen = [];
if (!empty($sections) && is_array($sections)) {
    foreach ($sections as $sec) {
        if (empty($sec['categories']) || !is_array($sec['categories'])) continue;
        foreach ($sec['categories'] as $ci => $c) {
            if (empty($c['menu_items']) || !is_array($c['menu_items']) || empty($c['is_active'])) continue;
            $anchor = (!empty($c['slug']) && (string) $c['slug'] !== '')
                ? (string) $c['slug']
                : (($sec['slug'] ?? 'section') . '-cat-' . (int) $ci);
            if (isset($snswCatSeen[$anchor])) continue;
            $snswCatSeen[$anchor] = true;
            $c['__snsw_anchor'] = $anchor;
            $activeCategories[] = $c;
        }
    }
}
$snswShowWelcomeModal = empty($singleSectionView) && !empty($sections) && is_array($sections);
$snswHeaderTitle = $restaurant['name'] ?? 'Menu';
$snswSectionHeroUrl = '';
if (!empty($singleSectionView) && !empty($sections) && is_array($sections) && !empty($sections[0])) {
    if (!empty($sections[0]['name'])) {
        $snswHeaderTitle = $sections[0]['name'];
    }
    if (!empty($sections[0]['image']) && empty($isTemplatePreview)) {
        $snswSectionHeroUrl = $uploadBaseUrl . '/sections/' . rawurlencode((string) $sections[0]['image']);
    }
}
$brandName = $restaurant['name'] ?? 'Menu';
$tagline = !empty($restaurant['description']) ? strtoupper(preg_replace('/\s+/', ' . ', trim(mb_substr($restaurant['description'], 0, 30)))) : 'SIP . SAVOR . SOCIALIZE';
function snsw_brand_markup($name) {
    if (strpos($name, ' & ') !== false) {
        $parts = explode(' & ', $name, 2);
        return htmlspecialchars($parts[0]) . ' <span class="text-accent-gold">&amp;</span> ' . htmlspecialchars($parts[1]);
    }
    return htmlspecialchars($name);
}
$snswSideBgUrl = htmlspecialchars($snswTemplateBaseUrl . '/' . $snswSideBgFile, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title><?php echo htmlspecialchars($restaurant['name']); ?><?php if (!empty($singleSectionView) && !empty($sections[0]['name'])): ?> - <?php echo htmlspecialchars($sections[0]['name']); ?><?php endif; ?></title>
<link href="https://fonts.googleapis.com" rel="preconnect"/><link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&amp;family=Cinzel+Decorative:wght@400;700&amp;family=Roboto:ital,wght@0,400;0,500;1,400&amp;display=swap" rel="stylesheet"/>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            'menu-bg': '#F3FAFD',
            'sidebar-bg': '#E4DABF',
            'accent-gold': '#C4A484',
            'menu-text': '#2C263F',
            'divider-dark': '#0D2633',
          },
          fontFamily: {
            cinzel: ['Cinzel', 'serif'],
            'cinzel-deco': ['"Cinzel Decorative"', 'serif'],
            roboto: ['Roboto', 'sans-serif'],
          }
        }
      }
    }
  </script>
<style>
html { overflow-x: clip; }
body.snsw-body { overflow-x: clip; background-color: #E4DABF; font-family: Roboto, sans-serif; color: #2C263F; min-width: 0; }
.snsw-shell {
  position: relative;
  display: flex;
  min-height: 100vh;
  min-width: 0;
  overflow-x: clip;
}
.snsw-page-bg {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  min-height: 100%;
  background-image: url('<?php echo htmlspecialchars($snswTemplateBaseUrl . '/' . $snswPageBgFile); ?>');
  background-repeat: repeat;
  background-size: 280px 280px;
  opacity: 0.018;
  z-index: 0;
  pointer-events: none;
}
.snsw-shell > .snsw-main-wrap { position: relative; z-index: 1; }
.snsw-main-wrap {
  flex: 1 1 auto;
  min-width: 0;
  margin-right: 3rem;
}
@media (min-width: 768px) {
  .snsw-main-wrap { margin-right: 5.5rem; }
}
@media (min-width: 1024px) {
  .snsw-main-wrap { margin-right: 7rem; }
}
.snsw-menu-items {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}
@media (min-width: 768px) {
  .snsw-menu-items { gap: 2rem; }
}
.menu-item {
  min-width: 0;
  padding-bottom: 0.15rem;
}
.menu-item-row { display: flex; align-items: baseline; width: 100%; min-width: 0; }
.item-dots { flex-grow: 1; flex-shrink: 0; min-width: 12px; border-bottom: 1px dotted #2C263F; margin: 0 6px; opacity: 0.3; }
.item-name {
  flex-shrink: 1; min-width: 0;
  font-family: Cinzel, serif;
  font-weight: 600;
  font-size: 0.95rem;
  text-transform: capitalize;
  word-break: break-word;
  overflow-wrap: break-word;
  color: #2C263F;
}
.item-price {
  flex-shrink: 0;
  font-family: Cinzel, serif;
  font-weight: 600;
  font-size: 0.95rem;
}
.item-desc {
  font-family: Roboto, sans-serif;
  font-size: 0.8125rem;
  line-height: 1.45;
  color: #4a5568;
  font-style: normal;
  font-weight: 400;
}
.section-header {
  font-family: "Cinzel Decorative", Cinzel, serif;
  font-weight: 700;
  font-size: 1.35rem;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  border-bottom: 12px solid #7A1519;
  display: inline-block;
  padding-bottom: 8px;
  margin-bottom: 28px;
  width: 100%;
  max-width: 16rem;
  color: #2C263F;
}
.section-header--red { border-bottom-color: #7A1519; }
.section-header--rail { border-bottom-color: #002F47; }
.snsw-section-title {
  font-family: "Cinzel Decorative", Cinzel, serif;
  font-weight: 700;
  font-size: clamp(1.65rem, 4vw, 2.25rem);
  letter-spacing: 0.18em;
  text-transform: uppercase;
  color: #2C263F;
}
.snsw-section-hero {
  max-width: 100%;
  max-height: 10rem;
  width: auto;
  height: auto;
  object-fit: contain;
}
@media (min-width: 768px) {
  .snsw-section-hero { max-height: 12rem; }
}
/* Right section rail: fixed, #002F47 + subtle white vertical pattern */
.snsw-rail {
  position: fixed;
  top: 0;
  right: 0;
  z-index: 50;
  display: flex;
  flex-direction: column;
  align-items: center;
  width: 3rem;
  height: 100vh;
  height: 100dvh;
  border-left: 3px solid #001f30;
  background-color: #002F47;
  padding: 0.75rem 0.25rem;
  overflow-x: visible;
}
.snsw-rail::before {
  content: '';
  position: absolute;
  inset: 0;
  background-image: url('<?php echo $snswSideBgUrl; ?>');
  background-repeat: repeat-y;
  background-position: top center;
  background-size: 100% auto;
  opacity: 0.09;
  filter: brightness(0) invert(1);
  pointer-events: none;
}
.snsw-rail-top,
.snsw-rail-slot {
  position: relative;
  z-index: 1;
}
@media (min-width: 768px) {
  .snsw-rail { width: 5.5rem; padding: 1rem 0.35rem; }
}
@media (min-width: 1024px) {
  .snsw-rail { width: 7rem; padding: 1.25rem 0.5rem; }
}
.snsw-rail-top {
  display: flex;
  width: 100%;
  flex-shrink: 0;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  padding: 0 0.15rem;
}
.snsw-rail-slot {
  display: flex;
  min-height: 0;
  flex: 1 1 auto;
  flex-direction: column;
  align-items: stretch;
  width: 100%;
  overflow: hidden;
}
.snsw-rail-menu-block {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.2rem;
  flex-shrink: 0;
  width: 2.25rem;
}
.snsw-rail-menu-label {
  display: block;
  width: 100%;
  font-family: Roboto, sans-serif;
  font-size: 0.5rem;
  font-weight: 500;
  letter-spacing: 0.08em;
  text-transform: lowercase;
  text-align: center;
  line-height: 1;
  color: rgba(243, 250, 253, 0.85);
}
.snsw-rail-scroll {
  overflow-x: visible !important;
  overflow-y: hidden;
  -webkit-overflow-scrolling: touch;
  min-height: 0;
  width: 100%;
  height: 100%;
  flex: 1 1 auto;
}
#snsw-rail-links {
  display: flex;
  flex-direction: column;
  align-items: center !important;
  justify-content: center !important;
  gap: 0.5rem;
  flex: 1 1 auto;
  min-height: 0;
  height: 100%;
  width: 100%;
  overflow: hidden;
  padding: 0.35rem 0;
}
.snsw-rail-item {
  display: flex;
  align-items: center;
  justify-content: center;
  flex: 0 0 auto;
  width: 100%;
}
.snsw-rail-link { opacity: 0; }
.snsw-rail-links--ready .snsw-rail-link {
  animation: snswRailFade 0.55s cubic-bezier(0.22, 1, 0.36, 1) forwards;
}
@keyframes snswRailFade { to { opacity: 1; } }
.snsw-rail-divider {
  flex: 0 0 auto;
  width: 56%;
  height: 2px;
  background: linear-gradient(90deg, transparent, rgba(196, 164, 132, 0.75) 50%, transparent);
  opacity: 0.65;
}
.snsw-vertical-link {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  writing-mode: vertical-rl;
  transform: rotate(180deg);
  text-orientation: mixed;
  white-space: nowrap;
  font-family: "Cinzel Decorative", Cinzel, serif;
  font-size: clamp(0.58rem, 1.75vh, 0.72rem);
  font-weight: 700;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  line-height: 1.1;
  padding: 0.35rem 0.28rem;
  border-radius: 0.35rem;
  border: 1px solid #5c1014;
  background: #7A1519;
  color: #fff;
  text-decoration: none;
  transition: background 0.2s, border-color 0.2s, color 0.2s;
}
.snsw-vertical-link:hover,
.snsw-vertical-link:focus-visible {
  background: #5c1014;
  border-color: #4a0d10;
  color: #fff;
}
.snsw-vertical-link--accent {
  background: #002F47;
  border-color: #001f30;
  color: #fff;
}
.snsw-vertical-link--accent:hover,
.snsw-vertical-link--accent:focus-visible {
  background: #001f30;
  border-color: #001522;
  color: #fff;
}
#snsw-category-toggle {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 2.25rem;
  height: 2.25rem;
  border-radius: 0.35rem;
  border: 1px solid rgba(255, 255, 255, 0.3);
  background: rgba(255, 255, 255, 0.12);
  color: #F3FAFD;
  cursor: pointer;
  transition: background 0.2s, border-color 0.2s;
}
#snsw-category-toggle:hover {
  background: rgba(255, 255, 255, 0.22);
  border-color: #C4A484;
}
#snsw-category-toggle:focus-visible {
  outline: 2px solid #C4A484;
  outline-offset: 2px;
}
/* Category drawer: plain cream — no vertical pattern (pattern stays on section rail only) */
#snsw-category-drawer {
  position: fixed;
  top: 0;
  right: 0;
  z-index: 60;
  display: flex;
  flex-direction: column;
  width: min(100vw - 3rem, 22rem);
  max-width: min(92vw, 22rem);
  height: 100%;
  transform: translateX(100%);
  transition: transform 0.3s ease-out;
  border-left: 3px solid #002F47;
  background-color: #E4DABF;
  background-image: none;
  box-shadow: -8px 0 24px rgba(13, 38, 51, 0.15);
}
#snsw-category-drawer.is-open { transform: translateX(0); }
#snsw-category-overlay {
  position: fixed;
  inset: 0;
  z-index: 55;
  background: rgba(13, 38, 51, 0.55);
  opacity: 0;
  visibility: hidden;
  pointer-events: none;
  transition: opacity 0.2s;
}
#snsw-category-overlay.is-open {
  opacity: 1;
  visibility: visible;
  pointer-events: auto;
}
.snsw-drawer-link {
  display: block;
  padding: 0.75rem 1rem;
  border-radius: 0.35rem;
  border: 1px solid rgba(13, 38, 51, 0.15);
  background: rgba(255, 255, 255, 0.5);
  font-family: "Cinzel Decorative", Cinzel, serif;
  font-size: 0.875rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: #0D2633;
  text-decoration: none;
  transition: background 0.2s, border-color 0.2s;
}
.snsw-drawer-link:hover {
  background: rgba(255, 255, 255, 0.85);
  border-color: #C4A484;
}
/* Welcome modal */
#snsw-welcome-modal {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
}
.snsw-welcome-panel {
  width: 100%;
  max-width: 22rem;
  max-height: min(94vh, 44rem);
  min-height: min(72vh, 28rem);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  border-radius: 0.5rem;
  border: 2px solid #0D2633;
  background: #F3FAFD;
  box-shadow: 0 12px 40px rgba(13, 38, 51, 0.2);
}
@media (min-width: 640px) {
  .snsw-welcome-panel { max-width: 26rem; max-height: min(94vh, 48rem); min-height: min(70vh, 32rem); }
}
.snsw-welcome-scroll {
  max-height: min(72vh, 36rem);
  overflow-y: auto;
  overscroll-behavior: contain;
  -webkit-overflow-scrolling: touch;
}
@media (min-width: 640px) {
  .snsw-welcome-scroll { max-height: min(74vh, 40rem); }
}
.snsw-welcome-scroll a.snsw-welcome-sep:not(:last-child) {
  border-bottom: 2px dotted #C4A484;
  margin-bottom: 0.65rem;
  padding-bottom: 0.65rem;
}
.snsw-welcome-section-img {
  max-width: 11rem;
  max-height: 5.5rem;
  object-fit: contain;
}
@media (min-width: 640px) {
  .snsw-welcome-section-img { max-width: 14rem; max-height: 7rem; }
}
.snsw-welcome-section-label {
  font-family: "Cinzel Decorative", Cinzel, serif;
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.16em;
  text-transform: uppercase;
  color: #002F47;
}
#snsw-welcome-close {
  width: 100%;
  padding: 0.5rem 1rem;
  border-radius: 0.35rem;
  border: 2px solid #0D2633;
  background: #0D2633;
  color: #fff;
  font-family: Cinzel, serif;
  font-size: 0.75rem;
  font-weight: 600;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  cursor: pointer;
  transition: background 0.2s, color 0.2s;
}
#snsw-welcome-close:hover {
  background: #002F47;
  border-color: #002F47;
}
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
body.snsw-body #resmenu-cart-widget {
  left: auto !important;
  right: 4.25rem !important;
}
@media (min-width: 768px) {
  body.snsw-body #resmenu-cart-widget { right: 6.5rem !important; }
}
@media (min-width: 1024px) {
  body.snsw-body #resmenu-cart-widget { right: 8rem !important; }
}
body.snsw-body #scrollToTop {
  right: 4.25rem;
}
@media (min-width: 768px) {
  body.snsw-body #scrollToTop { right: 6.5rem; }
}
@media (min-width: 1024px) {
  body.snsw-body #scrollToTop { bottom: 5.5rem; right: 8rem; }
}
@media (max-width: 767px) {
  .section-header { font-size: 1.1rem; letter-spacing: 0.05em; margin-bottom: 16px; border-bottom-width: 10px; padding-bottom: 6px; max-width: 13rem; }
  .item-name { font-size: 0.85rem; }
  .item-price { font-size: 0.85rem; }
  .snsw-section-title { font-size: 1.35rem; }
  .snsw-main { padding-left: 0.75rem; padding-right: 0.75rem; }
}
@media (min-width: 768px) and (max-width: 1023px) {
  .section-header { font-size: 1.2rem; }
  .item-name { font-size: 0.9rem; }
}
@media (prefers-reduced-motion: reduce) {
  .snsw-rail-link { opacity: 1; }
  .snsw-rail-links--ready .snsw-rail-link { animation: none; }
}
</style>
</head>
<body class="snsw-body flex min-h-screen min-w-0">
<?php if (!empty($snswShowWelcomeModal)): ?>
<div id="snsw-welcome-modal" class="fixed inset-0 z-[100] bg-[#0D2633]/75" role="dialog" aria-modal="true" aria-label="<?php echo htmlspecialchars($restaurant['name']); ?>">
<div class="snsw-welcome-panel">
<div class="shrink-0 border-b border-[#0D2633]/15 px-4 py-3 text-center sm:px-5 sm:py-4">
<?php if (!empty($restaurant['logo']) && empty($isTemplatePreview)): ?>
<div class="mb-2 flex justify-center"><img src="<?php echo $uploadBaseUrl . '/logos/' . htmlspecialchars($restaurant['logo']); ?>" alt="<?php echo htmlspecialchars($restaurant['name']); ?>" class="mx-auto h-12 w-auto max-w-[180px] object-contain sm:h-14"/></div>
<?php else: ?>
<h2 id="snsw-welcome-title" class="snsw-welcome-section-label text-base sm:text-lg"><?php echo htmlspecialchars($restaurant['name']); ?></h2>
<?php endif; ?>
<p class="mt-1 text-[10px] uppercase tracking-[0.25em] text-accent-gold sm:text-xs">Choose a section</p>
</div>
<div class="no-scrollbar snsw-welcome-scroll px-4 py-2.5 sm:px-5 sm:py-3">
<?php foreach ($sections as $snswWelSec):
    if (empty($snswWelSec['slug'])) continue;
    $snswWelSlug = (string) $snswWelSec['slug'];
    $snswWelName = htmlspecialchars($snswWelSec['name'] ?? '', ENT_QUOTES, 'UTF-8');
    $snswWelHref = !empty($fullMenuUrl)
        ? htmlspecialchars(rtrim($fullMenuUrl, '/') . '/' . $snswWelSlug, ENT_QUOTES, 'UTF-8')
        : '#section-' . htmlspecialchars($snswWelSlug, ENT_QUOTES, 'UTF-8');
?>
<a href="<?php echo $snswWelHref; ?>" class="snsw-welcome-section-link snsw-welcome-sep block py-1 text-center">
<?php if (!empty($snswWelSec['image']) && empty($isTemplatePreview)): ?>
<div class="mx-auto mb-1.5 flex justify-center"><img src="<?php echo $uploadBaseUrl . '/sections/' . htmlspecialchars($snswWelSec['image']); ?>" alt="" class="snsw-welcome-section-img w-auto rounded shadow-sm" loading="eager" decoding="async"/></div>
<?php endif; ?>
<span class="snsw-welcome-section-label"><?php echo $snswWelName; ?></span>
</a>
<?php endforeach; ?>
</div>
<div class="shrink-0 border-t border-[#0D2633]/15 px-4 py-2.5 sm:px-5 sm:py-3">
<button type="button" id="snsw-welcome-close">View full menu</button>
</div>
</div>
</div>
<?php endif; ?>

<div id="snsw-category-overlay" aria-hidden="true"></div>
<aside id="snsw-category-drawer" aria-label="Categories" aria-hidden="true" role="dialog">
<div class="flex shrink-0 items-center justify-between gap-3 border-b border-[#0D2633]/20 bg-white/40 px-4 py-4">
<span class="font-cinzel-deco text-sm font-bold uppercase tracking-[0.14em] text-[#002F47]">Categories</span>
<button type="button" id="snsw-category-close" class="flex h-10 w-10 shrink-0 items-center justify-center rounded border border-[#0D2633]/25 text-[#0D2633] hover:bg-white/60" aria-label="Close categories">
<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M18 6L6 18M6 6l12 12"/></svg>
</button>
</div>
<nav class="no-scrollbar flex flex-1 flex-col gap-2 overflow-y-auto px-3 py-4 pb-8">
<?php if (!empty($activeCategories)): ?>
<?php foreach ($activeCategories as $i => $cat):
    $catNavSlug = isset($cat['__snsw_anchor']) ? (string) $cat['__snsw_anchor'] : (isset($cat['slug']) ? (string) $cat['slug'] : ('cat-' . $i));
    if (!empty($fullMenuUrl)) {
        $catNavHref = !empty($singleSectionView) && !empty($sections[0]['slug'])
            ? $fullMenuUrl . '/' . $sections[0]['slug'] . '#' . $catNavSlug
            : $fullMenuUrl . '#' . $catNavSlug;
    } else {
        $catNavHref = '#' . $catNavSlug;
    }
?>
<a class="snsw-drawer-link" href="<?php echo htmlspecialchars($catNavHref, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($cat['name'] ?? ''); ?></a>
<?php endforeach; ?>
<?php else: ?>
<p class="px-2 text-sm text-menu-text/70">No categories to show.</p>
<?php endif; ?>
</nav>
</aside>

<div class="snsw-shell flex-1 w-full min-w-0">
<div class="snsw-page-bg" aria-hidden="true"></div>
<div class="snsw-main-wrap">
<main class="snsw-main mx-auto min-w-0 max-w-4xl flex-grow overflow-x-hidden px-4 py-6 sm:px-6 md:px-12 md:py-8">
  <header class="mb-10 pb-2 text-center md:mb-16">
    <?php if (empty($singleSectionView)): ?>
    <div class="mb-2 flex justify-center text-accent-gold">
      <svg class="h-3 w-3 sm:h-4 sm:w-4" fill="currentColor" viewBox="0 0 20 20"><path d="M10 12l-6-6h12l-6 6z"></path></svg>
    </div>
    <?php endif; ?>
    <?php if (!empty($restaurant['logo']) && empty($isTemplatePreview) && empty($singleSectionView)): ?>
    <div class="mb-3 md:mb-4"><img src="<?php echo $uploadBaseUrl . '/logos/' . htmlspecialchars($restaurant['logo']); ?>" alt="<?php echo htmlspecialchars($restaurant['name']); ?>" class="mx-auto h-16 w-auto max-w-full object-contain sm:h-20 md:h-24"/></div>
    <?php elseif (empty($singleSectionView) || $snswSectionHeroUrl === ''): ?>
    <h1 class="font-cinzel-deco break-words text-lg font-light tracking-[0.2em] text-menu-text sm:text-xl md:text-2xl sm:tracking-[0.3em]"><?php echo htmlspecialchars(strtoupper($snswHeaderTitle)); ?></h1>
    <?php endif; ?>
    <?php if ($snswSectionHeroUrl !== ''): ?>
    <div class="mb-4 flex justify-center md:mb-6">
      <img src="<?php echo htmlspecialchars($snswSectionHeroUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($snswHeaderTitle); ?>" class="snsw-section-hero rounded shadow-sm"/>
    </div>
    <h1 class="font-cinzel-deco snsw-section-title mb-2 text-center"><?php echo htmlspecialchars($snswHeaderTitle); ?></h1>
    <?php endif; ?>
    <?php if (!empty($restaurant['description']) && empty($restaurant['logo']) && empty($singleSectionView)): ?><p class="mt-1 break-words text-[10px] tracking-widest text-gray-700 sm:text-xs"><?php echo htmlspecialchars(mb_substr($restaurant['description'], 0, 60)); ?></p><?php endif; ?>
    <?php if (!empty($supportsReservations) && empty($singleSectionView)): ?><p class="mt-2 md:mt-3"><a href="<?php echo htmlspecialchars($reservationUrl); ?>" class="text-xs font-semibold text-[#002F47] hover:underline sm:text-sm">Reserve Table</a></p><?php endif; ?>
  </header>

  <?php $snswCatDividerIndex = 0; ?>
  <?php foreach ($sections as $section):
    if (empty($section['categories']) || !is_array($section['categories'])) continue;
  ?>
  <div class="mb-12 min-w-0 md:mb-16" id="section-<?php echo htmlspecialchars($section['slug']); ?>">
    <?php if (empty($singleSectionView)): ?>
    <h2 class="snsw-section-title mb-8 text-center"><?php echo htmlspecialchars($section['name']); ?></h2>
    <?php endif; ?>
  <?php foreach ($section['categories'] as $catIndex => $category):
    $slug = isset($category['slug']) ? $category['slug'] : ('cat-'.$catIndex);
    $items = isset($category['menu_items']) ? $category['menu_items'] : [];
    if (empty($items)) continue;
    $useBox = in_array(strtolower($category['name']), ['sides', 'side orders', 'desserts', 'dessert'], true);
    $snswDividerClass = ($snswCatDividerIndex % 4 === 3) ? 'section-header--rail' : 'section-header--red';
    $snswCatDividerIndex++;
  ?>
  <section class="mb-10 min-w-0 md:mb-16" id="<?php echo htmlspecialchars($slug); ?>">
    <h3 class="section-header <?php echo $snswDividerClass; ?>"><?php echo htmlspecialchars($category['name']); ?></h3>
    <?php if ($useBox): ?><div class="min-w-0 border border-divider-dark bg-white bg-opacity-40 p-4 md:p-6"><?php endif; ?>
    <div class="snsw-menu-items">
      <?php foreach ($items as $item):
        $itemAvailable = !isset($item['is_available']) || $item['is_available'];
      ?>
      <div class="menu-item">
        <?php if (!empty($item['image'])): ?><div class="mb-2"><img src="<?php echo $uploadBaseUrl . '/menu-items/' . htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="max-h-20 w-full max-w-full rounded object-cover md:max-h-24"/></div><?php endif; ?>
        <div class="menu-item-row">
          <span class="item-name"><?php echo htmlspecialchars($item['name']); ?></span>
          <span class="item-dots"></span>
          <span class="item-price"><?php echo snsw_price($item['price']); ?></span>
        </div>
        <?php if (!empty($item['description'])): ?><p class="item-desc mt-1 break-words"><?php echo htmlspecialchars($item['description']); ?></p><?php endif; ?>
        <?php if (!empty($supportsOrdering) && $itemAvailable): ?><button type="button" class="add-to-bag-btn mt-2 rounded border border-menu-text px-3 py-1.5 text-xs font-semibold text-menu-text transition-colors hover:bg-menu-text hover:text-white sm:px-4 sm:py-2 sm:text-sm" data-item-id="<?php echo (int)$item['id']; ?>" data-item-name="<?php echo htmlspecialchars($item['name']); ?>" data-item-price="<?php echo htmlspecialchars($item['price']); ?>" data-item-image="<?php echo !empty($item['image']) ? htmlspecialchars($item['image']) : ''; ?>">Add to bag</button><?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
    <?php if ($useBox): ?></div><?php endif; ?>
  </section>
  <?php endforeach; ?>
  </div>
  <?php endforeach; ?>

  <footer class="min-w-0 py-8 text-center md:py-12">
    <div class="mb-2 font-cinzel-deco text-3xl font-light tracking-widest text-menu-text"><?php echo snsw_brand_markup($restaurant['name']); ?></div>
    <?php if (!empty($restaurant['description'])): ?>
    <p class="snsw-footer-desc mx-auto mb-4 max-w-xl px-2 text-xs leading-relaxed text-gray-700 sm:text-sm"><?php echo nl2br(htmlspecialchars($restaurant['description'])); ?></p>
    <?php else: ?>
    <div class="mb-4 text-[10px] tracking-[0.4em] opacity-60"><?php echo htmlspecialchars($tagline); ?></div>
    <?php endif; ?>
    <?php if (!empty($restaurant['address']) || !empty($restaurant['phone']) || !empty($restaurant['email'])): ?>
    <div class="flex flex-wrap justify-center gap-x-4 gap-y-1 text-xs text-gray-700">
      <?php if (!empty($restaurant['address'])): ?><span><?php echo htmlspecialchars($restaurant['address']); ?></span><?php endif; ?>
      <?php if (!empty($restaurant['phone'])): ?><span><?php if (!empty($restaurant['address'])): ?> • <?php endif; ?><a href="tel:<?php echo htmlspecialchars(preg_replace('/\s+/', '', $restaurant['phone'])); ?>" class="text-menu-text hover:underline"><?php echo htmlspecialchars($restaurant['phone']); ?></a></span><?php endif; ?>
      <?php if (!empty($restaurant['email'])): ?><span><?php if (!empty($restaurant['address']) || !empty($restaurant['phone'])): ?> • <?php endif; ?><a href="mailto:<?php echo htmlspecialchars($restaurant['email']); ?>" class="text-menu-text hover:underline"><?php echo htmlspecialchars($restaurant['email']); ?></a></span><?php endif; ?>
    </div>
    <?php endif; ?>
    <?php if (!empty($restaurant['footer_content'])): ?><p class="mt-4 break-words text-xs text-gray-700 sm:text-sm"><?php echo nl2br(htmlspecialchars($restaurant['footer_content'])); ?></p><?php endif; ?>
  </footer>
</main>
</div>

<nav class="snsw-rail" aria-label="Section menu">
<div class="snsw-rail-top">
<div class="snsw-rail-menu-block">
<button type="button" id="snsw-category-toggle" aria-controls="snsw-category-drawer" aria-expanded="false" aria-label="Open categories menu">
<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
</button>
<span class="snsw-rail-menu-label" aria-hidden="true">menu</span>
</div>
<?php if (!empty($restaurant['logo']) && empty($isTemplatePreview)): ?>
<div class="shrink-0 px-0.5">
<img src="<?php echo $uploadBaseUrl . '/logos/' . htmlspecialchars($restaurant['logo']); ?>" alt="" class="mx-auto h-8 w-8 rounded object-contain md:h-10 md:w-10"/>
</div>
<?php endif; ?>
</div>
<div class="snsw-rail-slot">
<div id="snsw-rail-links" class="snsw-rail-scroll no-scrollbar">
<?php $snswRailDelay = 0; $snswNavSeen = []; $snswRailNeedSep = false; ?>
<?php if (!empty($singleSectionView) && !empty($fullMenuUrl)): ?>
<div class="snsw-rail-link" style="animation-delay: <?php echo ($snswRailDelay * 0.3); ?>s"><?php $snswRailDelay++; ?><a class="snsw-vertical-link" href="<?php echo htmlspecialchars($fullMenuUrl); ?>">View Full menu</a></div>
<?php $snswRailNeedSep = true; ?>
<?php endif; ?>
<?php
if (empty($singleSectionView) && !empty($sectionsForNav) && is_array($sectionsForNav) && !empty($fullMenuUrl)):
    foreach ($sectionsForNav as $navSection):
        $nsk = isset($navSection['slug']) ? (string)$navSection['slug'] : '';
        if ($nsk === '' || isset($snswNavSeen[$nsk])) continue;
        $snswNavSeen[$nsk] = true;
        if ($snswRailNeedSep): ?>
<div class="snsw-rail-divider" aria-hidden="true"></div>
<?php endif; $snswRailNeedSep = true; ?>
<div class="snsw-rail-link" style="animation-delay: <?php echo ($snswRailDelay * 0.3); ?>s"><?php $snswRailDelay++; ?><a class="snsw-vertical-link" href="<?php echo htmlspecialchars(rtrim($fullMenuUrl, '/') . '/' . $nsk); ?>"><?php echo htmlspecialchars($navSection['name'] ?? ''); ?></a></div>
<?php endforeach; endif; ?>
<?php if (!empty($supportsReservations)): ?>
<?php if ($snswRailNeedSep): ?><div class="snsw-rail-divider" aria-hidden="true"></div><?php endif; ?>
<div class="snsw-rail-link" style="animation-delay: <?php echo ($snswRailDelay * 0.3); ?>s"><?php $snswRailDelay++; ?><a class="snsw-vertical-link snsw-vertical-link--accent" href="<?php echo htmlspecialchars($reservationUrl); ?>">Reserve Table</a></div>
<?php endif; ?>
</div>
</div>
</nav>
</div>

<?php if (!empty($supportsOrdering)): ?>
<link rel="stylesheet" href="<?php echo rtrim(defined('SITE_URL') ? SITE_URL : $baseUrl, '/'); ?>/assets/css/cart-modal.css">
<div id="resmenu-cart-widget" class="fixed bottom-6 z-50 hidden"></div>
<script src="<?php echo rtrim(defined('SITE_URL') ? SITE_URL : $baseUrl, '/'); ?>/assets/js/cart.js"></script>
<script src="<?php echo rtrim(defined('SITE_URL') ? SITE_URL : $baseUrl, '/'); ?>/assets/js/cart-widget.js"></script>
<script src="<?php echo rtrim(defined('SITE_URL') ? SITE_URL : $baseUrl, '/'); ?>/assets/js/cart-modal.js"></script>
<script>
(function(){var baseUrl=<?php echo json_encode($baseUrl); ?>;var slug=<?php echo json_encode($restaurant['slug']??''); ?>;var config={restaurantSlug:slug,currencySymbol:<?php echo json_encode($currencySymbol); ?>,uploadBaseUrl:<?php echo json_encode($uploadBaseUrl??''); ?>,checkoutUrl:baseUrl+'/restaurant/'+slug+'/checkout',primaryColor:<?php echo json_encode($primaryColor); ?>,deliveryFee:0,taxRate:0};window.RESMENU_CART_CONFIG=config;if(window.RESMENU_CART_MODAL)window.RESMENU_CART_MODAL.init(config);if(window.RESMENU_CART_WIDGET)window.RESMENU_CART_WIDGET.init(config);document.querySelectorAll('.add-to-bag-btn').forEach(function(btn){btn.addEventListener('click',function(){var id=this.getAttribute('data-item-id'),name=this.getAttribute('data-item-name'),price=this.getAttribute('data-item-price'),image=this.getAttribute('data-item-image')||'';if(window.RESMENU_CART)window.RESMENU_CART.addItem(slug,{id:id,name:name,price:price,image:image},1);});});})();
</script>
<?php endif; ?>
<a id="scrollToTop" href="#" aria-label="Scroll to top" style="position:fixed;bottom:24px;z-index:30;width:48px;height:48px;display:flex;align-items:center;justify-content:center;border-radius:50%;background:#0D2633;color:#fff;opacity:0;visibility:hidden;transform:translateY(10px);transition:opacity 0.3s,visibility 0.3s,transform 0.3s;box-shadow:0 4px 12px rgba(0,0,0,0.3);">
  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 15l-6-6-6 6"/></svg>
</a>
<script>
(function () {
  var openBtn = document.getElementById('snsw-category-toggle');
  var closeBtn = document.getElementById('snsw-category-close');
  var drawer = document.getElementById('snsw-category-drawer');
  var overlay = document.getElementById('snsw-category-overlay');
  if (!drawer || !overlay) return;
  function openDrawer() {
    drawer.classList.add('is-open');
    drawer.setAttribute('aria-hidden', 'false');
    overlay.classList.add('is-open');
    overlay.setAttribute('aria-hidden', 'false');
    if (openBtn) openBtn.setAttribute('aria-expanded', 'true');
    document.body.style.overflow = 'hidden';
  }
  function closeDrawer() {
    drawer.classList.remove('is-open');
    drawer.setAttribute('aria-hidden', 'true');
    overlay.classList.remove('is-open');
    overlay.setAttribute('aria-hidden', 'true');
    if (openBtn) openBtn.setAttribute('aria-expanded', 'false');
    document.body.style.overflow = '';
  }
  if (openBtn) openBtn.addEventListener('click', function (e) { e.stopPropagation(); openDrawer(); });
  if (closeBtn) closeBtn.addEventListener('click', function (e) { e.preventDefault(); closeDrawer(); });
  overlay.addEventListener('click', closeDrawer);
  document.querySelectorAll('.snsw-drawer-link').forEach(function (l) { l.addEventListener('click', closeDrawer); });
  document.addEventListener('keydown', function (e) {
    if (e.key !== 'Escape') return;
    var wel = document.getElementById('snsw-welcome-modal');
    if (wel && wel.style.display !== 'none') return;
    closeDrawer();
  });
})();
(function () {
  var root = document.getElementById('snsw-welcome-modal');
  if (!root) return;
  var closeBtn = document.getElementById('snsw-welcome-close');
  function closeWelcome() {
    root.style.display = 'none';
    root.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
  }
  document.body.style.overflow = 'hidden';
  if (closeBtn) closeBtn.addEventListener('click', function (e) { e.preventDefault(); closeWelcome(); });
  root.addEventListener('click', function (e) { if (e.target === root) closeWelcome(); });
  document.querySelectorAll('.snsw-welcome-section-link').forEach(function (a) {
    a.addEventListener('click', function (e) {
      var href = a.getAttribute('href') || '';
      if (href.charAt(0) === '#') {
        e.preventDefault();
        closeWelcome();
        var id = href.slice(1);
        var el = id ? document.getElementById(id) : null;
        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        return;
      }
      closeWelcome();
    });
  });
  document.addEventListener('keydown', function (e) {
    if (e.key !== 'Escape') return;
    if (root.style.display === 'none') return;
    closeWelcome();
  });
})();
(function () {
  var rail = document.getElementById('snsw-rail-links');
  if (!rail) return;
  function arm() { rail.classList.add('snsw-rail-links--ready'); }
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) { arm(); return; }
  var done = false;
  var io = null;
  function armOnce() {
    if (done) return;
    done = true;
    arm();
    if (io) io.disconnect();
  }
  if ('IntersectionObserver' in window) {
    io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) { if (en.isIntersecting) armOnce(); });
    }, { root: null, rootMargin: '0px', threshold: 0.06 });
    io.observe(rail);
  }
  window.requestAnimationFrame(function () {
    window.requestAnimationFrame(function () { window.setTimeout(armOnce, 72); });
  });
})();
(function(){var btn=document.getElementById('scrollToTop');if(btn){window.addEventListener('scroll',function(){var st=window.pageYOffset||document.documentElement.scrollTop;var dh=document.documentElement.scrollHeight-window.innerHeight;if(dh>0&&st>=dh*0.3){btn.style.opacity='1';btn.style.visibility='visible';btn.style.transform='translateY(0)';}else{btn.style.opacity='0';btn.style.visibility='hidden';btn.style.transform='translateY(10px)';}});btn.addEventListener('click',function(e){e.preventDefault();window.scrollTo({top:0,behavior:'smooth'});});}})();
</script>
</body></html>
