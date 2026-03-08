<?php
/**
 * Nostalgia Front Page - Landing with menu category cards
 */
if (defined('UPLOAD_URL')) { $uploadBaseUrl = rtrim(UPLOAD_URL, '/'); } else {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $uploadBaseUrl = $protocol . ($_SERVER['HTTP_HOST'] ?? 'localhost') . (dirname(dirname(dirname($_SERVER['SCRIPT_NAME'] ?? ''))) ?: '') . '/uploads';
}
$activeCategories = [];
if (!empty($categories) && is_array($categories)) {
    foreach ($categories as $c) {
        if (!empty($c['menu_items']) && is_array($c['menu_items']) && !empty($c['is_active'])) $activeCategories[] = $c;
    }
}
$cardImages = ['https://lh3.googleusercontent.com/aida-public/AB6AXuD-NUGPkPCxpJ_nDAQV6DBrnTSRFar12tbgws-JbaaVlTnoTilN2HiC7cms5yqd8-ZB2sTXpUvWlhJuNI7khLoGvkr8_SEc7lIrA_MEFGd-x-bwnYc88B3jIM9XQwVEmzYU06fXyn3SMdujgrsHjSF2L4hJk4enNQ4OUgdVyoX9aWp6V4cr_QvVftOVDZjx0RX5e0hRgwSVYyOyDVrwfx08Vd-SsTUMgTb21kqi_DXLUC065r8SP0mr5gWUI-uXSgrjRy5oC9ymUp4F'];
?>
<!DOCTYPE html>
<html lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title><?php echo htmlspecialchars($restaurant['name']); ?></title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700&amp;family=Montserrat:wght@300;400;600&amp;display=swap" rel="stylesheet"/>
<script>
    tailwind.config = { theme: { extend: { colors: { brandGold: '#f2b90d', darkEbony: '#0a0a0a' }, fontFamily: { serif: ['Cinzel', 'serif'], sans: ['Montserrat', 'sans-serif'] } } } }
  </script>
<style>body { background: linear-gradient(180deg, #1a1a1a 0%, #000000 100%); background-attachment: fixed; } .card-border { border: 2px solid #f2b90d; transition: all 0.3s ease-in-out; } .card-border:hover { box-shadow: 0 0 20px rgba(242, 185, 13, 0.4); transform: translatey(-5px); border-color: #fff; } .divider-line { height: 1px; background: linear-gradient(90deg, transparent 0%, #fff 50%, transparent 100%); width: 100%; max-width: 400px; }</style>
</head>
<body class="text-white min-h-screen flex flex-col justify-between overflow-x-hidden">
<header class="pt-12 pb-8 flex flex-col items-center px-4">
<div class="mb-8">
<?php if (!empty($restaurant['logo']) && empty($isTemplatePreview)): ?>
<img alt="Logo" class="h-24 w-auto object-contain" src="<?php echo $uploadBaseUrl . '/logos/' . htmlspecialchars($restaurant['logo']); ?>"/>
<?php else: ?>
<img alt="Logo" class="h-24 w-auto object-contain" src="https://lh3.googleusercontent.com/aida-public/AB6AXuD3Cg2JO4hgMJxCrFb7fDZxWxS6ktqXPXZn8efe4mOW-MRu9zPxbXANC1NRGivKk0OPK7YROdVnueD5Jb5ut8rtJ1HBwr3f85kemYDAdgnuTtbH1xj8SpVhd2iv3dL-le1py0nk0_qR6BaLEj3075REO7lYhAAkIyVr__xEKUsCCQstBFytqy3fC2sKQA0BeT-ZxgHKJI-S68dkwF1QSX3HmnMhimbVw6XmXkIK0DYTEO3Ay2fHJ4nKS4PgEErcb9uhQoLzzDXbYT-o"/>
<?php endif; ?>
<div class="mt-4 tracking-[0.5em] text-xs font-light text-center"><?php echo strtoupper(htmlspecialchars($restaurant['name'])); ?></div>
</div>
<h1 class="text-4xl md:text-6xl font-serif tracking-widest text-center mb-6 uppercase"><?php echo htmlspecialchars($restaurant['name']); ?> Menu</h1>
<div class="flex items-center gap-4 w-full justify-center opacity-80">
<div class="divider-line"></div>
<div class="text-brandGold"><svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path d="M22 19H2v-2h1c0-4.97 4.03-9 9-9s9 4.03 9 9h1v2zm-10-15c-1.1 0-2 .9-2 2h4c0-1.1-.9-2-2-2z"/></svg></div>
<div class="divider-line"></div>
</div>
</header>
<main class="container mx-auto px-4 py-12 max-w-6xl flex-1">
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
<?php foreach ($activeCategories as $i => $cat): 
    $slug = isset($cat['slug']) ? $cat['slug'] : ('section-'.$i);
    $img = !empty($cat['image']) ? $uploadBaseUrl . '/categories/' . htmlspecialchars($cat['image']) : ($cardImages[$i % count($cardImages)] ?? $cardImages[0]);
?>
<a class="card-border bg-black/40 backdrop-blur-sm p-2 flex flex-col items-center group cursor-pointer" href="#<?php echo htmlspecialchars($slug); ?>">
<div class="overflow-hidden w-full h-48 mb-6">
<img alt="<?php echo htmlspecialchars($cat['name']); ?>" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" src="<?php echo $img; ?>"/>
</div>
<h2 class="text-xl font-sans font-semibold tracking-wide mb-4 group-hover:text-brandGold transition-colors"><?php echo htmlspecialchars($cat['name']); ?></h2>
</a>
<?php endforeach; ?>
</div>
<div class="mt-16 space-y-16">
<?php 
function nfp_price($p, $s = '₦') { return $s . number_format((float)$p, 2); }
foreach ($activeCategories as $catIndex => $category): 
    $slug = isset($category['slug']) ? $category['slug'] : ('section-'.$catIndex);
    $items = $category['menu_items'];
    if (empty($items)) continue;
?>
<section class="card-border p-8 bg-black/40" id="<?php echo htmlspecialchars($slug); ?>">
<h2 class="text-2xl font-serif text-brandGold uppercase tracking-widest mb-6"><?php echo htmlspecialchars($category['name']); ?></h2>
<div class="space-y-4">
<?php foreach ($items as $item): ?>
<div class="flex gap-4 items-start border-b border-gray-700 pb-3">
<?php if (!empty($item['image'])): ?><img src="<?php echo $uploadBaseUrl . '/menu-items/' . htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="w-16 h-16 flex-shrink-0 object-cover rounded"/><?php endif; ?>
<div class="flex-1 min-w-0 flex justify-between items-baseline">
<div><h3 class="text-lg font-semibold"><?php echo htmlspecialchars($item['name']); ?></h3><p class="text-sm text-gray-400"><?php echo htmlspecialchars($item['description'] ?? ''); ?></p></div>
<span class="text-brandGold font-serif"><?php echo nfp_price($item['price']); ?></span>
</div>
</div>
<?php endforeach; ?>
</div>
</section>
<?php endforeach; ?>
</div>
</main>
<footer class="py-8 text-center text-gray-500 text-sm"><?php echo htmlspecialchars($restaurant['footer_content'] ?? $restaurant['address'] ?? ''); ?></footer>
</body></html>
