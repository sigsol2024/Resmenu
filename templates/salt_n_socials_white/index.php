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
$snswTemplateBaseUrl = rtrim($baseUrl, '/') . '/templates/salt_n_socials_white';
$snswTemplateDir = __DIR__;
$snswPageBgFile = (file_exists($snswTemplateDir . '/Salt-Social-2-copy-1.png')) ? 'Salt-Social-2-copy-1.png' : 'Salt-Social-2-copy-1.jpg';
$snswSideBgFile = (file_exists($snswTemplateDir . '/side-bg-scaled.png')) ? 'side-bg-scaled.png' : 'side-bg-scaled.jpg';
$currencySymbol = '₦';
$primaryColor = isset($customization['primary_color']) ? $customization['primary_color'] : '#0D2633';
function snsw_price($p, $s = '₦') { return $s . number_format((float)$p, 2); }
$activeCategories = [];
if (!empty($categories) && is_array($categories)) {
    foreach ($categories as $c) {
        if (!empty($c['menu_items']) && is_array($c['menu_items']) && !empty($c['is_active'])) $activeCategories[] = $c;
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
?>
<!DOCTYPE html>
<html lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title><?php echo htmlspecialchars($restaurant['name']); ?></title>
<link href="https://fonts.googleapis.com" rel="preconnect"/><link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&amp;family=Raleway:wght@300;400;600;700&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
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
            'raleway': ['Raleway', 'sans-serif'],
            'opensans': ['Open Sans', 'sans-serif'],
          }
        }
      }
    }
  </script>
<style>
html { overflow-x: clip; }
body { overflow-x: clip; background-color: #E4DABF; font-family: "Open Sans", sans-serif; color: #2C263F; min-width: 0; }
/* Template body background image: Salt-Social-2-copy-1, repeated like other templates */
.snsw-page-bg {
  position: fixed;
  top: 0; left: 0; width: 100%; height: 100%;
  background-image: url('<?php echo htmlspecialchars($snswTemplateBaseUrl . '/' . $snswPageBgFile); ?>');
  background-repeat: repeat;
  background-size: 280px 280px;
  opacity: 0.08;
  z-index: -1;
  pointer-events: none;
}
/* Left sidebar (desktop): side-bg-scaled image repeated vertically */
.snsw-left-aside-bg {
  background-repeat: repeat-y;
  background-position: top center;
  background-size: 100% auto;
}
.vertical-text { writing-mode: vertical-rl; text-transform: uppercase; letter-spacing: 0.05em; }
.menu-item-row { display: flex; align-items: baseline; width: 100%; min-width: 0; }
.item-name { flex-shrink: 1; min-width: 0; font-weight: 700; font-family: "Raleway", sans-serif; font-size: 1rem; text-transform: uppercase; word-break: break-word; overflow-wrap: break-word; }
.item-dots { flex-grow: 1; flex-shrink: 0; min-width: 12px; border-bottom: 1px dotted #2C263F; margin: 0 6px; opacity: 0.3; }
.item-price { flex-shrink: 0; font-weight: 600; font-family: "Raleway", sans-serif; }
.section-header {
  font-family: "Raleway", sans-serif;
  font-weight: 400;
  letter-spacing: 0.2em;
  border-bottom: 4px solid #0D2633;
  display: inline-block;
  padding-bottom: 2px;
  margin-bottom: 24px;
  width: 100%;
  max-width: 300px;
}
.material-symbols-outlined { font-family: 'Material Symbols Outlined', sans-serif; font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
@media (max-width: 767px) {
  .vertical-text { font-size: 0.6rem; letter-spacing: 0.02em; }
  .section-header { font-size: 0.95rem; letter-spacing: 0.1em; margin-bottom: 16px; }
  .item-name { font-size: 0.8rem; }
  .item-price { font-size: 0.85rem; }
  .snsw-main { padding-left: 0.75rem; padding-right: 0.75rem; }
}
@media (min-width: 768px) and (max-width: 1023px) {
  .vertical-text { font-size: 0.75rem; letter-spacing: 0.04em; }
  .section-header { font-size: 1rem; }
  .item-name { font-size: 0.9rem; }
}
</style>
</head>
<body class="flex min-h-screen min-w-0">
<div class="snsw-page-bg"></div>
<!-- Layout: mobile = [sidebar][main]; desktop = [sidebar left][main][sidebar right] -->
<div class="flex flex-1 w-full min-w-0 flex-row" style="overflow-x: clip;">
<!-- Sidebar: #002F47, no text; mobile/tablet left, desktop right; same size -->
<aside class="flex flex-col w-12 sm:w-16 md:w-20 bg-[#002F47] shrink-0 z-10 order-1 md:order-3 sticky top-0 self-start py-6 md:py-12" style="height: 100vh;"></aside>
<!-- Main Content -->
<main class="flex-grow min-w-0 max-w-4xl mx-auto px-4 sm:px-6 md:px-12 py-6 md:py-8 snsw-main overflow-x-hidden order-2">
  <header class="text-center mb-10 md:mb-16 border-b border-gray-200 pb-4">
    <div class="mb-2 text-accent-gold flex justify-center">
      <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M10 12l-6-6h12l-6 6z"></path></svg>
    </div>
    <?php if (!empty($restaurant['logo']) && empty($isTemplatePreview)): ?>
    <div class="mb-3 md:mb-4"><img src="<?php echo $uploadBaseUrl . '/logos/' . htmlspecialchars($restaurant['logo']); ?>" alt="<?php echo htmlspecialchars($restaurant['name']); ?>" class="h-12 sm:h-16 w-auto object-contain mx-auto max-w-full"/></div>
    <?php else: ?>
    <h1 class="text-lg sm:text-xl md:text-2xl font-raleway tracking-[0.2em] sm:tracking-[0.3em] font-light text-menu-text break-words"><?php echo htmlspecialchars(strtoupper($restaurant['name'])); ?></h1>
    <?php endif; ?>
    <?php if (!empty($restaurant['description']) && empty($restaurant['logo'])): ?><p class="text-[10px] sm:text-xs tracking-widest text-gray-500 mt-1 break-words"><?php echo htmlspecialchars(mb_substr($restaurant['description'], 0, 60)); ?></p><?php endif; ?>
    <?php if (!empty($supportsReservations)): ?><p class="mt-2 md:mt-3"><a href="<?php echo htmlspecialchars($reservationUrl); ?>" class="text-sidebar-bg font-semibold hover:underline text-xs sm:text-sm">Reserve Table</a></p><?php endif; ?>
  </header>

  <?php foreach ($activeCategories as $catIndex => $category):
    $slug = isset($category['slug']) ? $category['slug'] : ('section-'.$catIndex);
    $items = $category['menu_items'];
    if (empty($items)) continue;
    $useBox = in_array(strtolower($category['name']), ['sides', 'side orders', 'desserts', 'dessert'], true);
  ?>
  <section class="mb-10 md:mb-16 min-w-0" id="<?php echo htmlspecialchars($slug); ?>">
    <h2 class="section-header text-xl uppercase text-menu-text"><?php echo htmlspecialchars($category['name']); ?></h2>
    <?php if ($useBox): ?><div class="border border-divider-dark p-4 md:p-6 bg-white bg-opacity-40 min-w-0"><?php endif; ?>
    <div class="space-y-5 md:space-y-8">
      <?php foreach ($items as $item):
        $itemAvailable = !isset($item['is_available']) || $item['is_available'];
      ?>
      <div class="menu-item min-w-0">
        <?php if (!empty($item['image'])): ?><div class="mb-2"><img src="<?php echo $uploadBaseUrl . '/menu-items/' . htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="max-h-20 md:max-h-24 w-full max-w-full object-cover rounded"/></div><?php endif; ?>
        <div class="menu-item-row">
          <span class="item-name"><?php echo htmlspecialchars($item['name']); ?></span>
          <span class="item-dots"></span>
          <span class="item-price"><?php echo snsw_price($item['price']); ?></span>
        </div>
        <?php if (!empty($item['description'])): ?><p class="text-xs sm:text-sm text-gray-500 mt-1 italic break-words"><?php echo htmlspecialchars($item['description']); ?></p><?php endif; ?>
        <?php if (!empty($supportsOrdering) && $itemAvailable): ?><button type="button" class="add-to-bag-btn mt-2 text-sidebar-bg border border-sidebar-bg px-3 py-1.5 sm:px-4 sm:py-2 rounded text-xs sm:text-sm font-semibold hover:bg-sidebar-bg hover:text-white transition-colors" data-item-id="<?php echo (int)$item['id']; ?>" data-item-name="<?php echo htmlspecialchars($item['name']); ?>" data-item-price="<?php echo htmlspecialchars($item['price']); ?>" data-item-image="<?php echo !empty($item['image']) ? htmlspecialchars($item['image']) : ''; ?>">Add to bag</button><?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
    <?php if ($useBox): ?></div><?php endif; ?>
  </section>
  <?php endforeach; ?>

  <footer class="text-center py-8 md:py-12 border-t border-gray-200 min-w-0">
    <div class="font-raleway text-3xl font-light tracking-widest text-menu-text mb-2"><?php echo snsw_brand_markup($restaurant['name']); ?></div>
    <div class="text-[10px] tracking-[0.4em] opacity-60 mb-4"><?php echo htmlspecialchars($tagline); ?></div>
    <?php if (!empty($restaurant['address']) || !empty($restaurant['phone']) || !empty($restaurant['email'])): ?>
    <div class="flex flex-wrap justify-center gap-x-4 gap-y-1 text-xs text-gray-500">
      <?php if (!empty($restaurant['address'])): ?><span><?php echo htmlspecialchars($restaurant['address']); ?></span><?php endif; ?>
      <?php if (!empty($restaurant['phone'])): ?><span><?php if (!empty($restaurant['address'])): ?> • <?php endif; ?><a href="tel:<?php echo htmlspecialchars(preg_replace('/\s+/', '', $restaurant['phone'])); ?>" class="text-menu-text hover:underline"><?php echo htmlspecialchars($restaurant['phone']); ?></a></span><?php endif; ?>
      <?php if (!empty($restaurant['email'])): ?><span><?php if (!empty($restaurant['address']) || !empty($restaurant['phone'])): ?> • <?php endif; ?><a href="mailto:<?php echo htmlspecialchars($restaurant['email']); ?>" class="text-menu-text hover:underline"><?php echo htmlspecialchars($restaurant['email']); ?></a></span><?php endif; ?>
    </div>
    <?php endif; ?>
    <?php if (!empty($restaurant['footer_content'])): ?><p class="mt-4 text-xs sm:text-sm text-gray-500 break-words"><?php echo nl2br(htmlspecialchars($restaurant['footer_content'])); ?></p><?php endif; ?>
  </footer>
</main>
<!-- Sidebar: #E4DABF, side-bg-scaled repeated vertically; desktop only, left; slightly wider -->
<aside class="snsw-left-aside-bg hidden md:flex flex-col w-20 md:w-24 shrink-0 z-10 order-3 md:order-1 sticky top-0 self-start py-6 md:py-12" style="height: 100vh; background-image: url('<?php echo htmlspecialchars($snswTemplateBaseUrl . '/' . $snswSideBgFile); ?>');"></aside>
</div>

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
<a id="scrollToTop" href="#" aria-label="Scroll to top" style="position:fixed;bottom:24px;right:24px;z-index:30;width:48px;height:48px;display:flex;align-items:center;justify-content:center;border-radius:50%;background:#0D2633;color:#fff;opacity:0;visibility:hidden;transform:translateY(10px);transition:opacity 0.3s,visibility 0.3s,transform 0.3s;box-shadow:0 4px 12px rgba(0,0,0,0.3);">
  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 15l-6-6-6 6"/></svg>
</a>
<script>
(function(){var btn=document.getElementById('scrollToTop');if(btn){window.addEventListener('scroll',function(){var st=window.pageYOffset||document.documentElement.scrollTop;var dh=document.documentElement.scrollHeight-window.innerHeight;if(dh>0&&st>=dh*0.3){btn.style.opacity='1';btn.style.visibility='visible';btn.style.transform='translateY(0)';}else{btn.style.opacity='0';btn.style.visibility='hidden';btn.style.transform='translateY(10px)';}});btn.addEventListener('click',function(e){e.preventDefault();window.scrollTo({top:0,behavior:'smooth'});});}})();
</script>
</body></html>
