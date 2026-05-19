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
$nmcUploadsRoot = defined('UPLOAD_PATH') ? rtrim(UPLOAD_PATH, '/\\') : dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'uploads';
$nmcTemplateBaseUrl = isset($templateAssetBaseUrl) ? $templateAssetBaseUrl : (rtrim($baseUrl, '/') . '/templates/template16');
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
    return formatPrice($p, $s);
}
/**
 * PNG-like assets (incl. transparent WebP): no border/radius. JPEG-like: photo framing.
 * @return 'alpha'|'photo'
 */
function nmc_image_surface_kind($fileRef, $diskSubdir = null) {
    if ($fileRef === null || $fileRef === '') {
        return 'photo';
    }
    $fileRef = (string) $fileRef;
    $diskPath = null;
    if ($diskSubdir !== null && $fileRef !== '' && strpos($fileRef, '://') === false) {
        global $nmcUploadsRoot;
        $diskPath = $nmcUploadsRoot . DIRECTORY_SEPARATOR . trim(str_replace('/', DIRECTORY_SEPARATOR, $diskSubdir), DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR . ltrim($fileRef, '/\\');
    } elseif (is_file($fileRef)) {
        $diskPath = $fileRef;
    }
    if ($diskPath !== null && is_file($diskPath)) {
        return nmc_image_surface_kind_from_file($diskPath);
    }
    $path = parse_url($fileRef, PHP_URL_PATH);
    if (!is_string($path) || $path === '') {
        $path = $fileRef;
    }
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    if ($ext === 'png' || $ext === 'gif') {
        return 'alpha';
    }
    if (in_array($ext, ['jpg', 'jpeg'], true)) {
        return 'photo';
    }
    return 'photo';
}
function nmc_image_surface_kind_from_file($path) {
    $data = @file_get_contents($path, false, null, 0, 32);
    if ($data === false || strlen($data) < 12) {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return ($ext === 'png' || $ext === 'gif') ? 'alpha' : 'photo';
    }
    if (substr($data, 0, 8) === "\x89PNG\r\n\x1a\n") {
        return 'alpha';
    }
    if (substr($data, 0, 2) === "\xFF\xD8") {
        return 'photo';
    }
    if (substr($data, 0, 3) === 'GIF') {
        return 'alpha';
    }
    if (substr($data, 0, 4) === 'RIFF' && strlen($data) >= 16 && substr($data, 8, 4) === 'WEBP') {
        $chunk = substr($data, 12, 4);
        if ($chunk === 'VP8L' && strlen($data) >= 22) {
            return ((ord($data[21]) >> 3) & 1) ? 'alpha' : 'photo';
        }
        if ($chunk === 'VP8X' && strlen($data) >= 21) {
            return (ord($data[20]) & 2) ? 'alpha' : 'photo';
        }
        if ($chunk === 'VP8 ') {
            return 'photo';
        }
    }
    if (function_exists('finfo_open')) {
        $fi = finfo_open(FILEINFO_MIME_TYPE);
        $mime = ($fi && is_file($path)) ? finfo_file($fi, $path) : null;
        if ($fi) {
            finfo_close($fi);
        }
        if ($mime === 'image/png' || $mime === 'image/gif') {
            return 'alpha';
        }
    }
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    return ($ext === 'png' || $ext === 'gif') ? 'alpha' : 'photo';
}
function nmc_img_class($sizeClasses, $fileRef, $diskSubdir, $photoClasses = '') {
    $kind = nmc_image_surface_kind($fileRef, $diskSubdir);
    $sizeClasses = trim((string) $sizeClasses);
    if ($kind === 'alpha') {
        return $sizeClasses . ' nmc-img-alpha';
    }
    $photoClasses = trim((string) $photoClasses);
    return trim($sizeClasses . ' nmc-img-photo ' . $photoClasses);
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
$nmcShowWelcomeModal = empty($singleSectionView) && !empty($sections) && is_array($sections);
$nmcCoverUrl = '';
$nmcHeroImageFile = '';
$nmcHeroImageSubdir = null;
$nmcHeroSurfaceKind = 'photo';
if (!empty($restaurant['hero_image_url'])) {
    $nmcCoverUrl = (string) $restaurant['hero_image_url'];
    $nmcHeroSurfaceKind = nmc_image_surface_kind($nmcCoverUrl, null);
} elseif (!empty($restaurant['hero_image']) && empty($isTemplatePreview)) {
    $nmcHeroImageFile = (string) $restaurant['hero_image'];
    $nmcHeroImageSubdir = 'heroes';
    $nmcCoverUrl = $uploadBaseUrl . '/heroes/' . htmlspecialchars($nmcHeroImageFile, ENT_QUOTES, 'UTF-8');
    $nmcHeroSurfaceKind = nmc_image_surface_kind($nmcHeroImageFile, $nmcHeroImageSubdir);
} elseif (!empty($restaurant['logo']) && empty($isTemplatePreview)) {
    $nmcHeroImageFile = (string) $restaurant['logo'];
    $nmcHeroImageSubdir = 'logos';
    $nmcCoverUrl = $uploadBaseUrl . '/logos/' . htmlspecialchars($nmcHeroImageFile, ENT_QUOTES, 'UTF-8');
    $nmcHeroSurfaceKind = nmc_image_surface_kind($nmcHeroImageFile, $nmcHeroImageSubdir);
}
$nmcHeaderTitle = $restaurant['name'] ?? '';
$nmcHeroImageUrl = $nmcCoverUrl;
if (!empty($singleSectionView) && !empty($sections) && is_array($sections) && !empty($sections[0])) {
    $nmcSec0 = $sections[0];
    if (!empty($nmcSec0['name'])) {
        $nmcHeaderTitle = $nmcSec0['name'];
    }
    if (!empty($nmcSec0['image']) && empty($isTemplatePreview)) {
        $nmcHeroImageFile = (string) $nmcSec0['image'];
        $nmcHeroImageSubdir = 'sections';
        $nmcHeroImageUrl = $uploadBaseUrl . '/sections/' . htmlspecialchars($nmcHeroImageFile, ENT_QUOTES, 'UTF-8');
        $nmcHeroSurfaceKind = nmc_image_surface_kind($nmcHeroImageFile, $nmcHeroImageSubdir);
    } else {
        $nmcHeroImageUrl = '';
        $nmcHeroImageFile = '';
        $nmcHeroImageSubdir = null;
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
@media (min-width: 768px) {
  .nmc-page-bg { background-size: 192px 192px; }
}
#nmc-category-drawer:focus { outline: none; }
#nmc-category-toggle:focus-visible {
  outline: 2px solid rgba(249, 115, 22, 0.85);
  outline-offset: 3px;
}
.nmc-rail-menu-block {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.2rem;
  flex-shrink: 0;
  width: 2.5rem;
}
.nmc-rail-menu-label {
  display: block;
  width: 100%;
  font-family: ui-sans-serif, system-ui, sans-serif;
  font-size: 0.5rem;
  font-weight: 600;
  letter-spacing: 0.08em;
  text-transform: lowercase;
  text-align: center;
  line-height: 1;
  color: rgba(251, 146, 60, 0.9);
}
.nmc-welcome-panel {
  width: 100%;
  max-width: 20rem;
  max-height: min(92vh, 34rem);
}
@media (min-width: 640px) {
  .nmc-welcome-panel { max-width: 22rem; max-height: min(92vh, 38rem); }
}
@media (min-width: 768px) {
  .nmc-welcome-panel { max-width: 24rem; max-height: min(92vh, 40rem); }
}
.nmc-welcome-scroll {
  max-height: min(62vh, 24rem);
  overflow-y: auto;
  overscroll-behavior: contain;
  -webkit-overflow-scrolling: touch;
}
@media (min-width: 640px) {
  .nmc-welcome-scroll { max-height: min(66vh, 28rem); }
}
@media (min-width: 768px) {
  .nmc-welcome-scroll { max-height: min(68vh, 30rem); }
}
.nmc-welcome-scroll a.nmc-welcome-sep:not(:last-child) {
  border-bottom: 1px dotted #ef4444;
  margin-bottom: 0.65rem;
  padding-bottom: 0.65rem;
}
.nmc-welcome-section-img {
  max-width: 7.5rem;
  max-height: 3.25rem;
}
@media (min-width: 640px) {
  .nmc-welcome-section-img { max-width: 9rem; max-height: 4rem; }
}
#nmc-welcome-modal {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
}
#nmc-welcome-close:focus-visible {
  outline: 2px solid rgba(239, 68, 68, 0.95);
  outline-offset: 2px;
}
.nmc-rail-link {
  opacity: 0;
}
.nmc-rail-links--ready .nmc-rail-link {
  animation: nmcRailFadeIn 0.55s cubic-bezier(0.22, 1, 0.36, 1) forwards;
}
@keyframes nmcRailFadeIn {
  to { opacity: 1; }
}
.nmc-rail-scroll {
  overflow-x: visible !important;
  overflow-y: hidden;
  -webkit-overflow-scrolling: touch;
  padding-top: 0.15rem;
  padding-bottom: 0.2rem;
  height: 100%;
}
#nmc-rail-links {
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
.nmc-rail-divider {
  flex: 0 0 auto;
  width: 56%;
  height: 1px;
  margin: 0;
  border: 0;
  background: linear-gradient(
    90deg,
    transparent 0%,
    rgba(239, 68, 68, 0.45) 50%,
    transparent 100%
  );
  pointer-events: none;
}
.nmc-rail-item {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  flex: 0 0 auto;
  min-height: 0;
  width: 100%;
  box-sizing: border-box;
  padding: 0;
  overflow: visible;
}
.nmc-vertical-link {
  display: inline-flex;
  align-items: center;
  justify-content: center;

  position: relative;
  z-index: 1;
  flex-shrink: 0;

  writing-mode: vertical-rl;
  transform: rotate(180deg);

  text-orientation: mixed;

  white-space: nowrap;

  font-size: clamp(0.65rem, 1.85vh, 0.78rem);
  font-weight: 700;
  line-height: 1.1;

  letter-spacing: 0.12em;

  width: fit-content;
  height: auto;
  max-width: 100%;
  max-height: 100%;

  padding: 0.35rem 0.28rem;
  border-radius: 0.5rem;
  border: 1px solid rgba(255, 255, 255, 0.14);
  background: linear-gradient(145deg, rgba(255, 255, 255, 0.09) 0%, rgba(255, 255, 255, 0.03) 100%);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.08);
  color: #fff;
  text-decoration: none;
  text-align: center;

  overflow: visible;
  pointer-events: auto;
  transition: border-color 0.2s, background 0.2s;
}
.nmc-vertical-link:hover,
.nmc-vertical-link:focus-visible {
  border-color: rgba(249, 115, 22, 0.45);
  background: linear-gradient(145deg, rgba(249, 115, 22, 0.18) 0%, rgba(124, 58, 237, 0.12) 100%);
  color: #fff;
}
.nmc-vertical-link--accent {
  color: #fdba74;
}
.nmc-vertical-link--accent:hover,
.nmc-vertical-link--accent:focus-visible {
  color: #fff;
}
nav[aria-label="Section menu"] {
  overflow-x: visible;
}
nav[aria-label="Section menu"] .nmc-rail-slot {
  display: flex;
  min-height: 0;
  flex: 1 1 auto;
  flex-direction: column;
  align-items: stretch;
  justify-content: stretch;
  overflow: hidden;
}
.nmc-cat-head {
  overflow: visible;
}
.nmc-cat-head img {
  display: block;
}
img.nmc-img-alpha {
  border: none !important;
  border-radius: 0 !important;
  box-shadow: none !important;
  outline: none !important;
  --tw-ring-offset-shadow: 0 0 #0000 !important;
  --tw-ring-shadow: 0 0 #0000 !important;
  object-fit: contain;
  object-position: center;
}
img.nmc-img-photo {
  object-fit: cover;
  object-position: center;
}
.nmc-hero-frame--alpha {
  border: none !important;
  border-radius: 0 !important;
  box-shadow: none !important;
}
.nmc-section-title {
  font-size: 1.5rem;
  line-height: 1.2;
}
@media (min-width: 768px) {
  .nmc-section-title { font-size: 1.875rem; }
}
.nmc-cat-title {
  font-size: 1.05rem;
  line-height: 1.3;
  text-align: center;
}
@media (min-width: 768px) {
  .nmc-cat-title {
    font-size: 1.2rem;
    text-align: left;
  }
}
.nmc-item-title {
  font-size: 0.9rem;
  line-height: 1.35;
}
@media (min-width: 640px) {
  .nmc-item-title { font-size: 0.95rem; }
}
.nmc-menu-item--has-image {
  flex-direction: column;
  align-items: flex-start;
}
.nmc-menu-item--has-image .nmc-menu-item__text {
  width: 100%;
}
.nmc-menu-item__media {
  width: 100%;
  max-width: 100%;
  flex-shrink: 0;
  display: block;
  line-height: 0;
}
.nmc-menu-item__media img.nmc-menu-item__img {
  display: block;
  width: auto;
  height: auto;
  max-width: 100%;
  max-height: 3.5rem;
  object-fit: contain !important;
  object-position: top left;
}
@media (min-width: 640px) and (max-width: 1023px) {
  .nmc-menu-item__media img.nmc-menu-item__img {
    max-height: 4.25rem;
  }
}
@media (min-width: 1024px) {
  .nmc-menu-item__media img.nmc-menu-item__img {
    max-height: 8rem;
  }
}
.nmc-order-btn {
  margin-top: 0.375rem;
  padding: 0.25rem 0.625rem;
  font-size: 0.6875rem;
  font-weight: 600;
  line-height: 1.25;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  color: #fff;
  background-color: #ef4444;
  border: 1px solid rgba(239, 68, 68, 0.85);
  border-radius: 0.25rem;
  cursor: pointer;
  transition: background-color 0.15s ease, border-color 0.15s ease;
}
.nmc-order-btn:hover {
  background-color: #dc2626;
  border-color: #dc2626;
}
/* Template 16 only: floating cart — right side, price red, white label */
body.nmc-body #resmenu-cart-widget {
  left: auto !important;
  right: 1.5rem !important;
  bottom: 1.5rem !important;
  z-index: 9999 !important;
}
body.nmc-body #resmenu-cart-widget .resmenu-cart-widget-btn {
  background-color: #ef4444 !important;
  color: #fff !important;
  box-shadow: 0 10px 25px rgba(239, 68, 68, 0.35) !important;
}
body.nmc-body #resmenu-cart-widget .resmenu-cart-widget-btn:hover {
  background-color: #dc2626 !important;
}
body.nmc-body #resmenu-cart-widget .resmenu-cart-widget-btn .resmenu-icon,
body.nmc-body #resmenu-cart-widget .resmenu-cart-widget-btn span {
  color: #fff !important;
}
body.nmc-body #scrollToTop {
  bottom: 5.75rem;
}
@media (prefers-reduced-motion: reduce) {
  .nmc-reveal { opacity: 1; transform: none; transition: none; }
  .nmc-rail-link { opacity: 1; }
  .nmc-rail-links--ready .nmc-rail-link { animation: none; }
  .nmc-vertical-link { transform: none; }
}
</style>
</head>
<body class="nmc-body bg-transparent text-slate-100 font-sans selection:bg-orange-500/30">
<div class="nmc-page-bg" aria-hidden="true"></div>
<?php if (!empty($nmcShowWelcomeModal)): ?>
<div id="nmc-welcome-modal" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/80" role="dialog" aria-modal="true" aria-labelledby="nmc-welcome-title">
<div class="nmc-welcome-panel flex h-auto min-h-0 flex-col overflow-hidden rounded-xl border border-white/15 bg-black/50 shadow-[0_0_32px_rgba(124,58,237,0.18)] backdrop-blur-xl sm:rounded-2xl">
<div class="shrink-0 border-b border-white/10 px-4 py-3 text-center sm:px-5 sm:py-4">
<?php if (!empty($restaurant['logo']) && empty($isTemplatePreview)): ?>
<div class="mb-2 flex justify-center"><img src="<?php echo $uploadBaseUrl . '/logos/' . htmlspecialchars($restaurant['logo']); ?>" alt="<?php echo htmlspecialchars($restaurant['name']); ?>" class="mx-auto h-9 w-auto max-w-[140px] object-contain sm:h-10 sm:max-w-[160px]"/></div>
<?php else: ?>
<div class="mb-2 flex justify-center"><div class="flex h-9 w-9 rotate-12 items-center justify-center rounded-lg text-lg font-black brand-gradient sm:h-10 sm:w-10 sm:text-xl"><?php echo strtoupper(substr($restaurant['name'], 0, 1)); ?></div></div>
<?php endif; ?>
<h2 id="nmc-welcome-title" class="text-base font-black uppercase tracking-wide text-white sm:text-lg"><?php echo htmlspecialchars($restaurant['name']); ?></h2>
<p class="mt-0.5 text-[10px] uppercase tracking-widest text-orange-500/90 sm:text-xs">Choose a section</p>
</div>
<div class="no-scrollbar nmc-welcome-scroll px-4 py-2.5 sm:px-5 sm:py-3">
<?php foreach ($sections as $nmcWelSec):
    if (empty($nmcWelSec['slug'])) {
        continue;
    }
    $nmcWelSlugRaw = (string) $nmcWelSec['slug'];
    $nmcWelName = htmlspecialchars($nmcWelSec['name'] ?? '', ENT_QUOTES, 'UTF-8');
    $nmcWelHref = !empty($fullMenuUrl)
        ? htmlspecialchars(rtrim($fullMenuUrl, '/') . '/' . $nmcWelSlugRaw, ENT_QUOTES, 'UTF-8')
        : '#section-' . htmlspecialchars($nmcWelSlugRaw, ENT_QUOTES, 'UTF-8');
?>
<a href="<?php echo $nmcWelHref; ?>" class="nmc-welcome-section-link nmc-welcome-sep block py-1 text-center">
<?php if (!empty($nmcWelSec['image']) && empty($isTemplatePreview)): ?>
<div class="mx-auto mb-1.5 flex justify-center"><img src="<?php echo $uploadBaseUrl . '/sections/' . htmlspecialchars($nmcWelSec['image']); ?>" alt="" class="<?php echo htmlspecialchars(nmc_img_class('nmc-welcome-section-img w-auto', $nmcWelSec['image'], 'sections', 'rounded-md shadow-sm'), ENT_QUOTES, 'UTF-8'); ?>" loading="eager" decoding="async"/></div>
<?php endif; ?>
<span class="text-xs font-bold uppercase tracking-[0.18em] text-red-500 sm:text-sm"><?php echo $nmcWelName; ?></span>
</a>
<?php endforeach; ?>
</div>
<div class="shrink-0 border-t border-white/10 px-4 py-2.5 sm:px-5 sm:py-3">
<button type="button" id="nmc-welcome-close" class="w-full rounded-lg border border-red-600/40 bg-white py-2 text-center text-xs font-bold uppercase tracking-widest text-red-600 shadow-sm transition-colors hover:bg-red-50 hover:text-red-700 sm:py-2.5 sm:text-sm">View full menu</button>
</div>
</div>
</div>
<?php endif; ?>
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
<nav class="fixed left-0 top-0 z-50 flex h-screen w-24 shrink-0 flex-col items-center overflow-x-visible border-r border-white/10 bg-black/40 py-3 backdrop-blur-md sm:w-24 md:w-28 lg:w-32 md:py-4" aria-label="Section menu">
<div class="flex w-full shrink-0 flex-col items-center gap-3 px-1.5 md:gap-4">
<div class="shrink-0">
<?php if (!empty($restaurant['logo']) && empty($isTemplatePreview)): ?><img src="<?php echo $uploadBaseUrl . '/logos/' . htmlspecialchars($restaurant['logo']); ?>" alt="<?php echo htmlspecialchars($restaurant['name']); ?>" class="<?php echo htmlspecialchars(nmc_img_class('h-11 w-11 md:h-12 md:w-12', $restaurant['logo'], 'logos', 'rounded-xl'), ENT_QUOTES, 'UTF-8'); ?>"/><?php else: ?><div class="flex h-11 w-11 rotate-12 items-center justify-center rounded-xl font-black text-xl brand-gradient md:h-12 md:w-12 md:text-2xl"><?php echo strtoupper(substr($restaurant['name'], 0, 1)); ?></div><?php endif; ?>
</div>
<div class="nmc-rail-menu-block lg:hidden">
<button type="button" id="nmc-category-toggle" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-orange-500/40 bg-gradient-to-br from-orange-500/15 to-purple-600/15 text-orange-400 shadow-inner shadow-orange-500/10 transition-colors hover:border-orange-400 hover:text-orange-300" aria-controls="nmc-category-drawer" aria-expanded="false" aria-label="Open categories menu">
<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
</button>
<span class="nmc-rail-menu-label" aria-hidden="true">menu</span>
</div>
</div>
<div class="nmc-rail-slot flex w-full min-h-0 flex-1 flex-col px-0.5 pb-2 pt-0">
<?php $nmcRailDelay = 0; $nmcNavSeen = []; $nmcRailNeedSep = false; ?>
<div id="nmc-rail-links" class="nmc-rail-scroll no-scrollbar min-h-0 w-full flex-1">
<?php if (!empty($singleSectionView) && !empty($fullMenuUrl)): ?><div class="nmc-rail-link nmc-rail-item" style="animation-delay: <?php echo ($nmcRailDelay * 0.3); ?>s"><?php $nmcRailDelay++; ?><a class="nmc-vertical-link uppercase" href="<?php echo htmlspecialchars($fullMenuUrl); ?>">View Full menu</a></div><?php $nmcRailNeedSep = true; ?><?php endif; ?>
<?php
if (empty($singleSectionView) && !empty($sectionsForNav) && is_array($sectionsForNav) && !empty($fullMenuUrl)):
    foreach ($sectionsForNav as $navSection):
        $nsk = isset($navSection['slug']) ? (string)$navSection['slug'] : '';
        if ($nsk === '' || isset($nmcNavSeen[$nsk])) continue;
        $nmcNavSeen[$nsk] = true;
        if ($nmcRailNeedSep): ?>
<div class="nmc-rail-divider" aria-hidden="true"></div>
<?php endif; $nmcRailNeedSep = true; ?>
<div class="nmc-rail-link nmc-rail-item" style="animation-delay: <?php echo ($nmcRailDelay * 0.3); ?>s"><?php $nmcRailDelay++; ?><a class="nmc-vertical-link uppercase" href="<?php echo htmlspecialchars(rtrim($fullMenuUrl, '/') . '/' . $nsk); ?>"><?php echo htmlspecialchars($navSection['name'] ?? ''); ?></a></div>
<?php endforeach; endif; ?>
<?php if (!empty($supportsReservations)): ?><?php if ($nmcRailNeedSep): ?><div class="nmc-rail-divider" aria-hidden="true"></div><?php endif; ?><div class="nmc-rail-link nmc-rail-item" style="animation-delay: <?php echo ($nmcRailDelay * 0.3); ?>s"><?php $nmcRailDelay++; ?><a class="nmc-vertical-link nmc-vertical-link--accent uppercase" href="<?php echo htmlspecialchars($reservationUrl); ?>">Reserve Table</a></div><?php endif; ?>
</div>
</div>
</nav>
<main class="ml-24 box-border min-w-0 max-w-full flex-1 overflow-x-hidden p-5 sm:ml-24 sm:p-8 md:ml-28 lg:ml-32 lg:p-16" id="menu">
<header class="mb-10 text-center md:mb-16">
<h1 class="w-full text-4xl font-black leading-tight text-white sm:text-5xl md:text-6xl"><?php echo htmlspecialchars($nmcHeaderTitle); ?></h1>
<?php if (!empty($restaurant['description'])): ?>
<p class="mx-auto mt-4 max-w-2xl text-slate-300"><?php echo htmlspecialchars($restaurant['description']); ?></p>
<?php endif; ?>
</header>
<?php if (!empty($nmcHeroImageUrl)): ?>
<div class="mx-auto mb-10 max-w-4xl overflow-hidden md:mb-14<?php echo $nmcHeroSurfaceKind === 'alpha' ? ' nmc-hero-frame--alpha' : ' rounded-2xl border border-white/10 shadow-xl'; ?>">
<img src="<?php echo htmlspecialchars($nmcHeroImageUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="" class="<?php echo htmlspecialchars(nmc_img_class('h-auto w-full max-h-52 sm:max-h-64 md:max-h-80', $nmcHeroImageFile !== '' ? $nmcHeroImageFile : $nmcHeroImageUrl, $nmcHeroImageSubdir, 'rounded-2xl'), ENT_QUOTES, 'UTF-8'); ?>" loading="lazy" decoding="async"/>
</div>
<?php endif; ?>
<?php foreach ($sections as $section):
    if (empty($section['categories']) || !is_array($section['categories'])) continue;
?>
<div id="section-<?php echo htmlspecialchars($section['slug']); ?>" class="mb-14 min-w-0">
<?php if (empty($singleSectionView)): ?>
<h2 class="nmc-section-title mb-6 text-center font-bold text-orange-500 md:mb-8"><?php if (!empty($fullMenuUrl)): ?><a href="<?php echo htmlspecialchars($fullMenuUrl . '/' . $section['slug']); ?>" class="text-orange-500 hover:underline"><?php echo htmlspecialchars($section['name']); ?></a><?php else: ?><?php echo htmlspecialchars($section['name']); ?><?php endif; ?></h2>
<?php endif; ?>
<?php foreach ($section['categories'] as $catIndex => $category):
    $slug = (!empty($category['slug']) && (string) $category['slug'] !== '')
        ? (string) $category['slug']
        : (($section['slug'] ?? 'section') . '-cat-' . (int) $catIndex);
    $items = isset($category['menu_items']) ? $category['menu_items'] : [];
    if (empty($items)) continue;
?>
<section class="nmc-reveal mb-20 min-w-0 overflow-visible rounded-2xl glass-card p-6 sm:p-8" id="<?php echo htmlspecialchars($slug); ?>">
<div class="nmc-cat-head mb-8 min-w-0">
<h3 class="nmc-cat-title block w-full border-b-2 border-orange-500 pb-2 font-bold text-white"><?php echo htmlspecialchars($category['name']); ?></h3>
<?php if (!empty($category['image']) && empty($isTemplatePreview)): ?>
<div class="mt-3 w-full max-w-md overflow-visible">
<img src="<?php echo $uploadBaseUrl . '/categories/' . htmlspecialchars($category['image']); ?>" alt="" class="<?php echo htmlspecialchars(nmc_img_class('h-auto max-h-48 w-full sm:max-h-52', $category['image'], 'categories', 'rounded-lg ring-1 ring-white/15'), ENT_QUOTES, 'UTF-8'); ?>" loading="lazy" decoding="async"/>
</div>
<?php endif; ?>
</div>
<div class="space-y-6">
<?php foreach ($items as $item):
    $nmcItemPrice = nmc_price($item['price']);
    $nmcItemHasImage = !empty($item['image']);
?>
<div class="nmc-menu-item flex min-w-0 gap-3 border-b border-white/10 pb-4 sm:gap-4<?php echo $nmcItemHasImage ? ' nmc-menu-item--has-image' : ''; ?>">
<?php if ($nmcItemHasImage): ?>
<div class="nmc-menu-item__media">
<img src="<?php echo $uploadBaseUrl . '/menu-items/' . htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="<?php echo htmlspecialchars(nmc_img_class('nmc-menu-item__img', $item['image'], 'menu-items', ''), ENT_QUOTES, 'UTF-8'); ?>"/>
</div>
<?php endif; ?>
<div class="nmc-menu-item__text min-w-0<?php echo $nmcItemHasImage ? ' w-full' : ' flex-1'; ?>">
<div class="flex min-w-0 items-baseline justify-between gap-3">
<h4 class="nmc-item-title min-w-0 flex-1 text-left font-semibold text-slate-100"><?php echo htmlspecialchars($item['name']); ?></h4>
<?php if ($nmcItemPrice !== ''): ?><span class="shrink-0 font-mono text-sm tabular-nums leading-snug text-red-500 sm:text-base"><?php echo $nmcItemPrice; ?></span><?php endif; ?>
</div>
<?php if (!empty($item['description'])): ?><p class="mt-1.5 w-full text-left text-sm leading-relaxed text-slate-400"><?php echo htmlspecialchars($item['description']); ?></p><?php endif; ?>
<?php if (!empty($supportsOrdering) && !empty($item['is_available'])): ?><button type="button" class="add-to-bag-btn nmc-order-btn" data-item-id="<?php echo (int)$item['id']; ?>" data-item-name="<?php echo htmlspecialchars($item['name']); ?>" data-item-price="<?php echo htmlspecialchars($item['price']); ?>" data-item-image="<?php echo !empty($item['image']) ? htmlspecialchars($item['image']) : ''; ?>">Order</button><?php endif; ?>
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
<div id="resmenu-cart-widget" class="nmc-cart-widget fixed bottom-6 right-6 z-50 hidden"></div>
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
  document.addEventListener('keydown', function (e) {
    if (e.key !== 'Escape') return;
    var wel = document.getElementById('nmc-welcome-modal');
    if (wel && wel.style.display !== 'none') return;
    closeDrawer();
  });
})();
(function () {
  var root = document.getElementById('nmc-welcome-modal');
  if (!root) return;
  var closeBtn = document.getElementById('nmc-welcome-close');
  function closeWelcome() {
    root.style.display = 'none';
    root.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
  }
  document.body.style.overflow = 'hidden';
  if (closeBtn) closeBtn.addEventListener('click', function (e) { e.preventDefault(); closeWelcome(); });
  root.addEventListener('click', function (e) { if (e.target === root) closeWelcome(); });
  document.querySelectorAll('.nmc-welcome-section-link').forEach(function (a) {
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
  var rail = document.getElementById('nmc-rail-links');
  if (!rail) return;
  function arm() {
    rail.classList.add('nmc-rail-links--ready');
  }
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    arm();
    return;
  }
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
      entries.forEach(function (en) {
        if (en.isIntersecting) armOnce();
      });
    }, { root: null, rootMargin: '0px', threshold: 0.06 });
    io.observe(rail);
  }
  window.requestAnimationFrame(function () {
    window.requestAnimationFrame(function () {
      window.setTimeout(armOnce, 72);
    });
  });
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
