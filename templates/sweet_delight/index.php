<?php
/**
 * Sweet Delight - Playful dessert parlour
 */
if (defined('UPLOAD_URL')) { $uploadBaseUrl = rtrim(UPLOAD_URL, '/'); } else {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $uploadBaseUrl = $protocol . ($_SERVER['HTTP_HOST'] ?? 'localhost') . (dirname(dirname(dirname($_SERVER['SCRIPT_NAME'] ?? ''))) ?: '') . '/uploads';
}
$baseUrl = defined('SITE_URL') ? rtrim(SITE_URL, '/') : '';
$reservationUrl = $baseUrl . '/restaurant/' . ($restaurant['slug'] ?? '') . '/reservation';
$currencySymbol = '₦';
$primaryColor = isset($customization['primary_color']) ? $customization['primary_color'] : '#FF85A2';
function sd_price($p, $s = '₦') { return $s . number_format((float)$p, 2); }
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
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script>
    tailwind.config = { theme: { extend: { colors: { 'pastel-pink': '#FFD1DC', 'pastel-mint': '#B2F2BB', 'cream': '#FFF9E5', 'soft-berry': '#FF85A2', 'mint-dark': '#7BC992' }, borderRadius: { xlarge: '2rem' } } } }
  </script>
<style>@import url('https://fonts.googleapis.com/css2?family=Fredoka+One&family=Quicksand:wght@400;600&display=swap'); body { font-family: 'Quicksand', sans-serif; background-color: #FFF9E5; } h1, h2, h3 { font-family: 'Fredoka One', cursive; } .blob { position: fixed; z-index: -1; filter: blur(40px); opacity: 0.4; } .blob-pink { top: 10%; left: 5%; width: 300px; height: 300px; background-color: #FFD1DC; border-radius: 40% 60% 70% 30% / 40% 50% 60% 50%; } .blob-mint { bottom: 10%; right: 5%; width: 400px; height: 400px; background-color: #B2F2BB; border-radius: 60% 40% 30% 70% / 60% 30% 70% 40%; }</style>
</head>
<body class="min-h-screen">
<div class="blob blob-pink"></div>
<div class="blob blob-mint"></div>
<header class="py-12 text-center" data-purpose="header-container">
<div class="inline-block p-4 bg-white rounded-full shadow-lg mb-4">
<?php if (!empty($restaurant['logo']) && empty($isTemplatePreview)): ?>
<img src="<?php echo $uploadBaseUrl . '/logos/' . htmlspecialchars($restaurant['logo']); ?>" alt="" style="max-height: 48px; width: auto;"/>
<?php else: ?>
<span class="text-4xl">🍦</span>
<?php endif; ?>
</div>
<h1 class="text-5xl md:text-6xl text-soft-berry mb-2"><?php echo htmlspecialchars($restaurant['name']); ?></h1>
<p class="text-lg text-gray-600 font-semibold italic"><?php echo htmlspecialchars($restaurant['description'] ?? 'Where every scoop is a dream!'); ?></p>
<nav class="mt-8 flex justify-center gap-4 flex-wrap" data-purpose="main-navigation">
<?php if (!empty($supportsReservations)): ?><a href="<?php echo htmlspecialchars($reservationUrl); ?>" class="px-6 py-2 bg-white border-2 border-soft-berry text-soft-berry rounded-full font-bold hover:shadow-md transition-all">Reserve Table</a><?php endif; ?>
<?php foreach ($activeCategories as $i => $cat): $s = isset($cat['slug']) ? $cat['slug'] : ('section-'.$i); ?>
<a class="px-6 py-2 <?php echo $i % 2 ? 'bg-pastel-mint text-mint-dark' : 'bg-pastel-pink text-soft-berry'; ?> rounded-full font-bold hover:shadow-md transition-all" href="#<?php echo htmlspecialchars($s); ?>"><?php echo htmlspecialchars($cat['name']); ?></a>
<?php endforeach; ?>
</nav>
</header>
<main class="container mx-auto px-4 pb-20">
<?php foreach ($activeCategories as $catIndex => $category): 
    $slug = isset($category['slug']) ? $category['slug'] : ('section-'.$catIndex);
    $items = $category['menu_items'];
    if (empty($items)) continue;
    $useMint = ($catIndex % 2);
?>
<section class="mb-16" id="<?php echo htmlspecialchars($slug); ?>">
<div class="flex items-center gap-4 mb-8">
<h2 class="text-3xl <?php echo $useMint ? 'text-mint-dark' : 'text-soft-berry'; ?>"><?php echo htmlspecialchars($category['name']); ?></h2>
<div class="h-1 flex-grow <?php echo $useMint ? 'bg-pastel-mint' : 'bg-pastel-pink'; ?> rounded-full"></div>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
<?php foreach ($items as $item): ?>
<div class="bg-white p-6 rounded-xlarge shadow-sm hover:shadow-xl transition-shadow border-4 <?php echo $useMint ? 'border-pastel-mint' : 'border-pastel-pink'; ?> relative overflow-hidden" data-purpose="menu-item-card">
<?php if (!empty($item['image'])): ?><img src="<?php echo $uploadBaseUrl . '/menu-items/' . htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="w-full h-40 object-cover rounded-t-xlarge -mx-6 -mt-6 mb-4"/><?php endif; ?>
<div class="flex justify-between items-start mb-4">
<h3 class="text-xl text-gray-800"><?php echo htmlspecialchars($item['name']); ?></h3>
<span class="px-3 py-1 rounded-full font-bold <?php echo $useMint ? 'bg-pastel-mint text-mint-dark' : 'bg-pastel-pink text-soft-berry'; ?>"><?php echo sd_price($item['price']); ?></span>
</div>
<p class="text-gray-600 mb-4"><?php echo htmlspecialchars($item['description'] ?? ''); ?></p>
<?php if (!empty($supportsOrdering) && !empty($item['is_available'])): ?><button type="button" class="add-to-bag-btn mt-2 px-4 py-2 rounded-full font-bold <?php echo $useMint ? 'bg-pastel-mint text-mint-dark' : 'bg-pastel-pink text-soft-berry'; ?> hover:opacity-90" data-item-id="<?php echo (int)$item['id']; ?>" data-item-name="<?php echo htmlspecialchars($item['name']); ?>" data-item-price="<?php echo htmlspecialchars($item['price']); ?>" data-item-image="<?php echo !empty($item['image']) ? htmlspecialchars($item['image']) : ''; ?>">Add to bag</button><?php endif; ?>
</div>
<?php endforeach; ?>
</div>
</section>
<?php endforeach; ?>
</main>
<footer class="bg-soft-berry text-white py-12 rounded-t-[3rem] text-center">
<div class="container mx-auto px-4">
<h2 class="text-3xl mb-4"><?php echo htmlspecialchars($restaurant['name']); ?></h2>
<p class="mb-6 opacity-90"><?php echo htmlspecialchars($restaurant['address'] ?? 'Visit us'); ?></p>
<div class="flex justify-center gap-6 text-2xl">
<?php if (!empty($restaurant['instagram_url'])): ?><a class="hover:scale-110 transition-transform" href="<?php echo htmlspecialchars($restaurant['instagram_url']); ?>">📸</a><?php endif; ?>
<?php if (!empty($restaurant['facebook_url'])): ?><a class="hover:scale-110 transition-transform" href="<?php echo htmlspecialchars($restaurant['facebook_url']); ?>">📘</a><?php endif; ?>
</div>
<div class="mt-10 pt-6 border-t border-white/20 text-sm"><?php echo htmlspecialchars($restaurant['footer_content'] ?? '© ' . date('Y') . ' ' . $restaurant['name']); ?></div>
</div>
</footer>
<script>document.querySelectorAll('a[href^="#"]').forEach(function(a){ a.addEventListener('click',function(e){ e.preventDefault(); var t=document.querySelector(this.getAttribute('href')); if(t) t.scrollIntoView({behavior:'smooth'}); }); });</script>
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
