<?php
/**
 * Salt N Socials White - Clean white menu style
 */
if (defined('UPLOAD_URL')) { $uploadBaseUrl = rtrim(UPLOAD_URL, '/'); } else {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $uploadBaseUrl = $protocol . ($_SERVER['HTTP_HOST'] ?? 'localhost') . (dirname(dirname(dirname($_SERVER['SCRIPT_NAME'] ?? ''))) ?: '') . '/uploads';
}
$baseUrl = defined('SITE_URL') ? rtrim(SITE_URL, '/') : '';
$reservationUrl = $baseUrl . '/restaurant/' . ($restaurant['slug'] ?? '') . '/reservation';
$currencySymbol = '₦';
$primaryColor = isset($customization['primary_color']) ? $customization['primary_color'] : '#0D2633';
function snsw_price($p, $s = '₦') { return $s . number_format((float)$p, 2); }
$activeCategories = [];
if (!empty($categories) && is_array($categories)) {
    foreach ($categories as $c) {
        if (!empty($c['menu_items']) && is_array($c['menu_items']) && !empty($c['is_active'])) $activeCategories[] = $c;
    }
}
?>
<!DOCTYPE html>
<html lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title><?php echo htmlspecialchars($restaurant['name']); ?></title>
<link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&amp;family=Raleway:wght@300;400;600;700&amp;display=swap" rel="stylesheet"/>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script>
    tailwind.config = { theme: { extend: { colors: { 'menu-bg': '#F3FAFD', 'sidebar-bg': '#0D2633', 'accent-gold': '#C4A484', 'menu-text': '#2C263F' }, fontFamily: { raleway: ['Raleway', 'sans-serif'], opensans: ['Open Sans', 'sans-serif'] } } } }
  </script>
<style>body { background-color: #F3FAFD; font-family: "Open Sans", sans-serif; color: #2C263F; } .menu-item-row { display: flex; align-items: baseline; width: 100%; } .item-name { flex-shrink: 0; font-weight: 700; font-family: "Raleway", sans-serif; text-transform: uppercase; } .item-dots { flex-grow: 1; border-bottom: 1px dotted #2C263F; margin: 0 8px; opacity: 0.3; } .item-price { flex-shrink: 0; font-weight: 600; font-family: "Raleway", sans-serif; } .section-header { font-family: "Raleway", sans-serif; letter-spacing: 0.2em; border-bottom: 4px solid #0D2633; display: inline-block; padding-bottom: 2px; margin-bottom: 24px; }</style>
</head>
<body class="min-h-screen p-6 md:p-12">
<main class="max-w-4xl mx-auto bg-white shadow-lg p-8 md:p-16">
<header class="text-center mb-16">
<?php if (!empty($restaurant['logo']) && empty($isTemplatePreview)): ?><div class="mb-4"><img src="<?php echo $uploadBaseUrl . '/logos/' . htmlspecialchars($restaurant['logo']); ?>" alt="<?php echo htmlspecialchars($restaurant['name']); ?>" class="h-20 w-auto object-contain mx-auto"/></div><?php endif; ?>
<h1 class="text-4xl md:text-6xl font-raleway font-light text-menu-text mb-2"><?php echo htmlspecialchars($restaurant['name']); ?></h1>
<p class="text-sm uppercase tracking-widest text-gray-500"><?php echo htmlspecialchars($restaurant['description'] ?? 'Food &amp; Drinks'); ?></p>
<?php if (!empty($supportsReservations)): ?><p class="mt-2"><a href="<?php echo htmlspecialchars($reservationUrl); ?>" class="text-sidebar-bg font-semibold hover:underline">Reserve Table</a></p><?php endif; ?>
</header>
<?php foreach ($activeCategories as $catIndex => $category): 
    $slug = isset($category['slug']) ? $category['slug'] : ('section-'.$catIndex);
    $items = $category['menu_items'];
    if (empty($items)) continue;
?>
<section class="mb-12" id="<?php echo htmlspecialchars($slug); ?>">
<h2 class="section-header text-2xl text-menu-text"><?php echo htmlspecialchars($category['name']); ?></h2>
<div class="space-y-4">
<?php foreach ($items as $item): ?>
<?php if (!empty($item['image'])): ?><div class="mb-2"><img src="<?php echo $uploadBaseUrl . '/menu-items/' . htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="max-h-24 w-auto object-cover rounded"/></div><?php endif; ?>
<div class="menu-item-row">
<span class="item-name"><?php echo htmlspecialchars($item['name']); ?></span>
<span class="item-dots"></span>
<span class="item-price"><?php echo snsw_price($item['price']); ?></span>
</div>
<p class="text-sm text-gray-600 pl-0 mb-6"><?php echo htmlspecialchars($item['description'] ?? ''); ?></p>
<?php if (!empty($supportsOrdering) && !empty($item['is_available'])): ?><button type="button" class="add-to-bag-btn mb-4 text-sidebar-bg border border-sidebar-bg px-4 py-2 rounded hover:bg-sidebar-bg hover:text-white" data-item-id="<?php echo (int)$item['id']; ?>" data-item-name="<?php echo htmlspecialchars($item['name']); ?>" data-item-price="<?php echo htmlspecialchars($item['price']); ?>" data-item-image="<?php echo !empty($item['image']) ? htmlspecialchars($item['image']) : ''; ?>">Add to bag</button><?php endif; ?>
<?php endforeach; ?>
</div>
</section>
<?php endforeach; ?>
<footer class="mt-16 pt-8 border-t border-gray-200 text-center text-sm text-gray-500">
<?php echo htmlspecialchars($restaurant['footer_content'] ?? $restaurant['address'] ?? ''); ?>
</footer>
</main>
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
</body></html>
