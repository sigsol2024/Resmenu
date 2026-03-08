<?php
/**
 * Nostalgia Food Menu - Full menu in nostalgia style
 */
if (defined('UPLOAD_URL')) { $uploadBaseUrl = rtrim(UPLOAD_URL, '/'); } else {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $uploadBaseUrl = $protocol . ($_SERVER['HTTP_HOST'] ?? 'localhost') . (dirname(dirname(dirname($_SERVER['SCRIPT_NAME'] ?? ''))) ?: '') . '/uploads';
}
function nfm_price($p, $s = '₦') { return $s . number_format((float)$p, 2); }
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
<title><?php echo htmlspecialchars($restaurant['name']); ?> - Menu</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700&amp;family=Montserrat:wght@300;400;600&amp;display=swap" rel="stylesheet"/>
<script>
    tailwind.config = { theme: { extend: { colors: { brandGold: '#f2b90d', darkEbony: '#0a0a0a' }, fontFamily: { serif: ['Cinzel', 'serif'], sans: ['Montserrat', 'sans-serif'] } } } }
  </script>
<style>body { background: linear-gradient(180deg, #1a1a1a 0%, #000000 100%); background-attachment: fixed; color: #e5e5e5; } .card-border { border: 2px solid #f2b90d; } .divider-line { height: 1px; background: linear-gradient(90deg, transparent 0%, #f2b90d 50%, transparent 100%); margin: 1.5rem 0; }</style>
</head>
<body class="min-h-screen p-6 md:p-12 font-sans">
<main class="max-w-4xl mx-auto">
<header class="text-center mb-16">
<h1 class="text-4xl md:text-6xl font-serif text-brandGold tracking-widest uppercase mb-4"><?php echo htmlspecialchars($restaurant['name']); ?></h1>
<div class="divider-line max-w-xs mx-auto"></div>
<p class="text-gray-400 italic"><?php echo htmlspecialchars($restaurant['description'] ?? 'Our Menu'); ?></p>
</header>
<?php foreach ($activeCategories as $catIndex => $category): 
    $slug = isset($category['slug']) ? $category['slug'] : ('section-'.$catIndex);
    $items = $category['menu_items'];
    if (empty($items)) continue;
?>
<section class="mb-16 card-border p-8 bg-black/40 backdrop-blur-sm" id="<?php echo htmlspecialchars($slug); ?>">
<h2 class="text-2xl font-serif text-brandGold uppercase tracking-widest mb-8 border-b border-brandGold/50 pb-4"><?php echo htmlspecialchars($category['name']); ?></h2>
<div class="space-y-6">
<?php foreach ($items as $item): ?>
<div class="flex justify-between items-baseline gap-4">
<div class="flex-1">
<h3 class="text-xl font-semibold text-white"><?php echo htmlspecialchars($item['name']); ?></h3>
<p class="text-sm text-gray-400 mt-1"><?php echo htmlspecialchars($item['description'] ?? ''); ?></p>
</div>
<span class="font-serif text-brandGold whitespace-nowrap"><?php echo nfm_price($item['price']); ?></span>
</div>
<?php endforeach; ?>
</div>
</section>
<?php endforeach; ?>
<footer class="text-center py-12 text-gray-500 text-sm border-t border-gray-700"><?php echo htmlspecialchars($restaurant['footer_content'] ?? $restaurant['address'] ?? ''); ?></footer>
</main>
</body></html>
