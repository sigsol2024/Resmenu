<?php
/**
 * The Art Fusion - Zen Japanese fusion minimalist
 */
if (defined('UPLOAD_URL')) { $uploadBaseUrl = rtrim(UPLOAD_URL, '/'); } else {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $uploadBaseUrl = $protocol . ($_SERVER['HTTP_HOST'] ?? 'localhost') . (dirname(dirname(dirname($_SERVER['SCRIPT_NAME'] ?? ''))) ?: '') . '/uploads';
}
function taf_price($p, $s = '₦') { return $s . number_format((float)$p, 2); }
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
<style>@import url('https://fonts.googleapis.com/css2?family=Bodoni+Moda:ital,opsz,wght@0,6..96,400;0,6..96,700;1,6..96,400&family=Noto+Sans+JP:wght@300;400;500&display=swap'); body { font-family: 'Noto Sans JP', sans-serif; background-color: #ffffff; color: #1a1a1a; } h1, h2, h3, .serif-font { font-family: 'Bodoni Moda', serif; } .zen-divider { height: 1px; background-color: #e5e5e5; width: 100%; margin: 2rem 0; } .accent-red { color: #bc002d; } .hide-scrollbar::-webkit-scrollbar { display: none; } .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; } .menu-section { scroll-margin-top: 100px; }</style>
</head>
<body class="antialiased">
<header class="fixed top-0 left-0 right-0 z-50 bg-white/90 backdrop-blur-sm border-b border-gray-100">
<div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
<div class="flex-shrink-0"><h1 class="text-2xl font-bold tracking-[0.3em] uppercase"><?php echo htmlspecialchars(mb_substr($restaurant['name'], 0, 20)); ?></h1></div>
<nav class="flex overflow-x-auto hide-scrollbar space-x-8 text-sm uppercase tracking-widest font-medium">
<?php foreach ($activeCategories as $i => $cat): $s = isset($cat['slug']) ? $cat['slug'] : ('section-'.$i); ?>
<a class="hover:text-[#bc002d] transition-colors whitespace-nowrap py-2 border-b-2 border-transparent hover:border-[#bc002d]" href="#<?php echo htmlspecialchars($s); ?>"><?php echo htmlspecialchars($cat['name']); ?></a>
<?php endforeach; ?>
</nav>
</div>
</header>
<main class="pt-20">
<section class="relative h-[70vh] flex items-center justify-center overflow-hidden bg-gray-50">
<div class="z-10 text-center px-4">
<span class="text-xs uppercase tracking-[0.5em] mb-4 block"><?php echo htmlspecialchars($restaurant['description'] ?? 'Crafting Balance'); ?></span>
<h2 class="text-5xl md:text-7xl font-light mb-6 italic"><?php echo htmlspecialchars($restaurant['name']); ?></h2>
<div class="w-12 h-px bg-black mx-auto"></div>
</div>
<?php if (!empty($restaurant['hero_image']) && empty($isTemplatePreview)): ?>
<img alt="" class="absolute inset-0 w-full h-full object-cover opacity-80" src="<?php echo $uploadBaseUrl . '/heroes/' . htmlspecialchars($restaurant['hero_image']); ?>"/>
<?php else: ?>
<img alt="Zen Interior" class="absolute inset-0 w-full h-full object-cover opacity-80" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAk14in8WcP48BdHZuySC634eIGIRiALxRkUfbVkrJcIRS3dXUqmY2gKlDeEvji5Alw7DN3zJQmePUjDq6fu-6HNbAFYq0gLIHW3l6-LQiq1StCU2j0zTOvrvs4Jf_dN1fFwK8cbERicdHftKKuNYrWX3eBL1w_SVxbfdaWLGcLg_DY3OufFYGa7LCU-NUc2L8-HJmr6ipY9uZKklqSWQzWsJ8UPcrEWvMVTaehUU9diPCOOodb8eo_RRDAK3m569RIPKeEN_QJaa83"/>
<?php endif; ?>
</section>
<?php foreach ($activeCategories as $catIndex => $category): 
    $slug = isset($category['slug']) ? $category['slug'] : ('section-'.$catIndex);
    $items = $category['menu_items'];
    if (empty($items)) continue;
?>
<section class="menu-section py-24 max-w-7xl mx-auto px-6" id="<?php echo htmlspecialchars($slug); ?>">
<div class="grid grid-cols-1 md:grid-cols-2 gap-x-20 gap-y-12">
<?php foreach ($items as $item): ?>
<div class="flex justify-between border-b border-gray-100 pb-4">
<div>
<h4 class="text-lg uppercase tracking-wider mb-1"><?php echo htmlspecialchars($item['name']); ?></h4>
<p class="text-sm text-gray-400 italic"><?php echo htmlspecialchars($item['description'] ?? ''); ?></p>
</div>
<span class="font-medium"><?php echo taf_price($item['price']); ?></span>
</div>
<?php endforeach; ?>
</div>
</section>
<?php if ($catIndex < count($activeCategories) - 1): ?><div class="zen-divider max-w-2xl mx-auto"></div><?php endif; ?>
<?php endforeach; ?>
</main>
<footer class="bg-white py-20 border-t border-gray-100">
<div class="max-w-7xl mx-auto px-6 flex flex-col items-center text-center">
<h2 class="text-2xl font-bold tracking-[0.5em] uppercase mb-8"><?php echo htmlspecialchars($restaurant['name']); ?></h2>
<p class="text-sm text-gray-400 max-w-sm mb-12"><?php echo nl2br(htmlspecialchars($restaurant['address'] ?? '123 Minimalist Way')); ?><br/><?php echo htmlspecialchars($restaurant['opening_hours'] ?? 'Mon – Sun'); ?></p>
<div class="flex space-x-12 text-xs uppercase tracking-widest border-t border-gray-100 pt-12 w-full justify-center">
<?php if (!empty($restaurant['instagram_url'])): ?><a class="hover:text-[#bc002d]" href="<?php echo htmlspecialchars($restaurant['instagram_url']); ?>">Instagram</a><?php endif; ?>
<?php if (!empty($restaurant['facebook_url'])): ?><a class="hover:text-[#bc002d]" href="<?php echo htmlspecialchars($restaurant['facebook_url']); ?>">Facebook</a><?php endif; ?>
</div>
</div>
</footer>
</body></html>
