<?php
/**
 * The Garden Bistro - Modern minimalist cafe menu
 */
if (defined('UPLOAD_URL')) { $uploadBaseUrl = rtrim(UPLOAD_URL, '/'); } else {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $uploadBaseUrl = $protocol . ($_SERVER['HTTP_HOST'] ?? 'localhost') . (dirname(dirname(dirname($_SERVER['SCRIPT_NAME'] ?? ''))) ?: '') . '/uploads';
}
function tgb_price($p, $s = '₦') { return $s . number_format((float)$p, 2); }
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
<link href="https://fonts.googleapis.com" rel="preconnect"/><link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&amp;family=Inter:wght@300;400;500&amp;display=swap" rel="stylesheet"/>
<script>
    tailwind.config = { theme: { extend: { colors: { pastel: { sage: '#E2E8E4', rose: '#F7E7E6', cream: '#F9F7F2', blue: '#E3EBF0' }, charcoal: '#333333' }, fontFamily: { serif: ['Playfair Display', 'serif'], sans: ['Inter', 'sans-serif'] } } } }
  </script>
<style>body { font-family: 'Inter', sans-serif; color: #333333; background-color: #F9F7F2; } h1, h2, h3 { font-family: 'Playfair Display', serif; } .menu-item-price::before { content: '₦'; } .divider { height: 1px; background-color: #D1D1D1; width: 100%; margin: 1.5rem 0; } .circular-image { aspect-ratio: 1/1; border-radius: 50%; object-fit: cover; border: 4px solid white; box-shadow: 0 4px 6px -1px rgb(0 0 0/0.1); }</style>
</head>
<body class="min-h-screen p-4 md:p-12 lg:p-24">
<main class="max-w-6xl mx-auto bg-white shadow-sm border border-stone-100 p-8 md:p-16" data-purpose="menu-wrapper">
<header class="text-center mb-20" data-purpose="main-header">
<?php if (!empty($restaurant['logo']) && empty($isTemplatePreview)): ?><div class="mb-6"><img src="<?php echo $uploadBaseUrl . '/logos/' . htmlspecialchars($restaurant['logo']); ?>" alt="<?php echo htmlspecialchars($restaurant['name']); ?>" class="h-24 w-auto object-contain mx-auto"/></div><?php endif; ?>
<h1 class="text-5xl md:text-7xl font-light mb-4 tracking-tight"><?php echo htmlspecialchars($restaurant['name']); ?></h1>
<p class="uppercase tracking-[0.2em] text-sm text-stone-500 font-medium"><?php echo htmlspecialchars($restaurant['description'] ?? 'Elevated Brunch &amp; Artisan Coffee'); ?></p>
<div class="divider max-w-xs mx-auto mt-8 bg-stone-200"></div>
</header>
<?php foreach ($activeCategories as $catIndex => $category): 
    $slug = isset($category['slug']) ? $category['slug'] : ('section-'.$catIndex);
    $items = $category['menu_items'];
    if (empty($items)) continue;
    $isMains = ($catIndex === 1 && count($activeCategories) > 1);
?>
<section class="mb-20" data-purpose="<?php echo $slug; ?>">
<div class="flex items-center justify-between mb-10">
<h2 class="text-3xl italic"><?php echo htmlspecialchars($category['name']); ?></h2>
<div class="h-[1px] flex-grow mx-8 bg-stone-200"></div>
<span class="text-stone-400 font-light italic">To Share</span>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-12">
<?php foreach ($items as $item): 
    $imgSrc = !empty($item['image']) ? $uploadBaseUrl . '/menu-items/' . htmlspecialchars($item['image']) : '';
?>
<div class="flex gap-6 items-center" data-purpose="menu-item">
<?php if ($imgSrc): ?><img alt="<?php echo htmlspecialchars($item['name']); ?>" class="circular-image w-24 h-24 flex-shrink-0 object-cover" src="<?php echo $imgSrc; ?>"/><?php endif; ?>
<div>
<div class="flex justify-between items-baseline mb-1">
<h3 class="text-xl font-semibold"><?php echo htmlspecialchars($item['name']); ?></h3>
<span class="menu-item-price font-medium"><?php echo number_format((float)$item['price'], 2); ?></span>
</div>
<p class="text-stone-500 text-sm leading-relaxed"><?php echo htmlspecialchars($item['description'] ?? ''); ?></p>
</div>
</div>
<?php endforeach; ?>
</div>
</section>
<?php endforeach; ?>
<footer class="mt-24 pt-12 border-t border-stone-100 text-center" data-purpose="menu-footer">
<?php if (!empty($restaurant['footer_content'])): ?><p class="text-stone-600 text-sm mb-4"><?php echo nl2br(htmlspecialchars($restaurant['footer_content'])); ?></p><?php endif; ?>
<?php if (!empty($restaurant['address'])): ?><p class="text-stone-500 text-sm mb-4"><?php echo htmlspecialchars($restaurant['address']); ?></p><?php endif; ?>
<p class="text-stone-400 text-xs tracking-widest uppercase mb-4">Please inform your server of any allergies.</p>
<div class="flex justify-center space-x-6 text-stone-300">
<?php if (!empty($restaurant['instagram_url'])): ?><a href="<?php echo htmlspecialchars($restaurant['instagram_url']); ?>" class="hover:text-stone-500">Instagram</a><span>•</span><?php endif; ?>
<?php if (!empty($restaurant['website'])): ?><a href="<?php echo htmlspecialchars($restaurant['website']); ?>" class="hover:text-stone-500"><?php echo htmlspecialchars(parse_url($restaurant['website'], PHP_URL_HOST) ?: $restaurant['website']); ?></a><?php endif; ?>
</div>
</footer>
</main>
</body></html>
