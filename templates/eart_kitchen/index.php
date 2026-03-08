<?php
/**
 * Eart Kitchen - Organic farm-to-table
 */
if (defined('UPLOAD_URL')) { $uploadBaseUrl = rtrim(UPLOAD_URL, '/'); } else {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $uploadBaseUrl = $protocol . ($_SERVER['HTTP_HOST'] ?? 'localhost') . (dirname(dirname(dirname($_SERVER['SCRIPT_NAME'] ?? ''))) ?: '') . '/uploads';
}
function ek_price($p, $s = '₦') { return $s . number_format((float)$p, 2); }
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
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&amp;family=Montserrat:wght@300;400;600&amp;family=Caveat:wght@600&amp;display=swap" rel="stylesheet"/>
<script>
    tailwind.config = { theme: { extend: { colors: { sage: '#87947d', terracotta: '#c27d63', cream: '#fdfaf5', earth: '#4a443f' }, fontFamily: { serif: ['Playfair Display', 'serif'], sans: ['Montserrat', 'sans-serif'], handwritten: ['Caveat', 'cursive'] } } } }
  </script>
<style>.paper-texture { background-color: #fdfaf5; } .hand-drawn-line { height: 2px; background: linear-gradient(to right, transparent, #c27d63, transparent); margin: 1.5rem 0; } .price-tag { font-style: italic; color: #c27d63; }</style>
</head>
<body class="paper-texture text-earth font-sans min-h-screen p-4 md:p-12">
<div class="max-w-5xl mx-auto border-[12px] border-sage/20 p-6 md:p-16 relative overflow-hidden bg-white/40 shadow-xl">
<header class="text-center mb-16 relative z-10">
<?php if (!empty($restaurant['logo']) && empty($isTemplatePreview)): ?><div class="mb-4"><img src="<?php echo $uploadBaseUrl . '/logos/' . htmlspecialchars($restaurant['logo']); ?>" alt="<?php echo htmlspecialchars($restaurant['name']); ?>" class="h-20 w-auto object-contain mx-auto"/></div><?php endif; ?>
<h1 class="font-serif text-5xl md:text-7xl font-bold text-earth mb-2"><?php echo htmlspecialchars($restaurant['name']); ?></h1>
<p class="font-serif italic text-sage text-xl tracking-widest uppercase mb-4"><?php echo htmlspecialchars($restaurant['description'] ?? 'Sustainable &amp; Sourced'); ?></p>
<div class="hand-drawn-line w-1/3 mx-auto"></div>
</header>
<?php foreach ($activeCategories as $catIndex => $category): 
    $slug = isset($category['slug']) ? $category['slug'] : ('section-'.$catIndex);
    $items = $category['menu_items'];
    if (empty($items)) continue;
?>
<section class="mb-16" id="<?php echo htmlspecialchars($slug); ?>">
<h2 class="text-3xl font-serif font-bold text-earth border-b-2 border-terracotta pb-2 mb-8"><?php echo htmlspecialchars($category['name']); ?></h2>
<div class="space-y-8">
<?php foreach ($items as $item): ?>
<div>
<?php if (!empty($item['image'])): ?><img src="<?php echo $uploadBaseUrl . '/menu-items/' . htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="w-full max-h-36 object-cover rounded border border-terracotta/30 mb-2"/><?php endif; ?>
<div class="flex justify-between items-baseline mb-1">
<h3 class="text-xl font-semibold text-earth menu-item-title"><?php echo htmlspecialchars($item['name']); ?></h3>
<span class="price-tag font-serif"><?php echo ek_price($item['price']); ?></span>
</div>
<p class="text-sm text-earth/80"><?php echo htmlspecialchars($item['description'] ?? ''); ?></p>
</div>
<?php endforeach; ?>
</div>
</section>
<?php endforeach; ?>
<footer class="mt-16 pt-8 border-t border-sage/30 text-center text-earth/60 text-sm"><?php if (!empty($restaurant['footer_content'])): ?><p class="mb-4"><?php echo nl2br(htmlspecialchars($restaurant['footer_content'])); ?></p><?php endif; ?><?php echo htmlspecialchars($restaurant['address'] ?? ''); ?></footer>
</div>
</body></html>
