<?php
/**
 * Salt N Socials Colored - Colored variant
 */
if (defined('UPLOAD_URL')) { $uploadBaseUrl = rtrim(UPLOAD_URL, '/'); } else {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $uploadBaseUrl = $protocol . ($_SERVER['HTTP_HOST'] ?? 'localhost') . (dirname(dirname(dirname($_SERVER['SCRIPT_NAME'] ?? ''))) ?: '') . '/uploads';
}
function snsc_price($p, $s = '₦') { return $s . number_format((float)$p, 2); }
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
<link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&amp;family=Raleway:wght@300;400;600;700&amp;display=swap" rel="stylesheet"/>
<script>
    tailwind.config = { theme: { extend: { colors: { 'menu-bg': '#F3FAFD', 'accent': '#0D2633', 'gold': '#C4A484' }, fontFamily: { raleway: ['Raleway', 'sans-serif'], opensans: ['Open Sans', 'sans-serif'] } } } }
  </script>
<style>body { font-family: "Open Sans", sans-serif; background: linear-gradient(135deg, #F3FAFD 0%, #E8F4F8 100%); } .item-row { display: flex; align-items: baseline; } .item-dots { flex-grow: 1; border-bottom: 1px dotted #0D2633; margin: 0 8px; }</style>
</head>
<body class="min-h-screen p-6 md:p-12 text-gray-800">
<main class="max-w-4xl mx-auto bg-white/90 backdrop-blur shadow-xl p-8 md:p-16 rounded-lg border border-gold/30">
<header class="text-center mb-16">
<h1 class="text-4xl md:text-6xl font-raleway font-semibold text-accent mb-2"><?php echo htmlspecialchars($restaurant['name']); ?></h1>
<p class="text-sm uppercase tracking-widest text-gold"><?php echo htmlspecialchars($restaurant['description'] ?? 'Menu'); ?></p>
</header>
<?php foreach ($activeCategories as $catIndex => $category): 
    $slug = isset($category['slug']) ? $category['slug'] : ('section-'.$catIndex);
    $items = $category['menu_items'];
    if (empty($items)) continue;
?>
<section class="mb-12" id="<?php echo htmlspecialchars($slug); ?>">
<h2 class="text-2xl font-raleway border-b-4 border-accent pb-2 mb-6 inline-block"><?php echo htmlspecialchars($category['name']); ?></h2>
<div class="space-y-2">
<?php foreach ($items as $item): ?>
<div class="item-row">
<span class="font-bold"><?php echo htmlspecialchars($item['name']); ?></span>
<span class="item-dots"></span>
<span class="font-semibold text-accent"><?php echo snsc_price($item['price']); ?></span>
</div>
<p class="text-sm text-gray-600 mb-4"><?php echo htmlspecialchars($item['description'] ?? ''); ?></p>
<?php endforeach; ?>
</div>
</section>
<?php endforeach; ?>
<footer class="mt-16 pt-8 border-t border-gray-200 text-center text-sm text-gray-500"><?php echo htmlspecialchars($restaurant['footer_content'] ?? ''); ?></footer>
</main>
</body></html>
