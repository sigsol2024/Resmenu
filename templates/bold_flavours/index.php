<?php
/**
 * Bold Flavours - Neon bistro urban dining
 */
if (defined('UPLOAD_URL')) { $uploadBaseUrl = rtrim(UPLOAD_URL, '/'); } else {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $uploadBaseUrl = $protocol . ($_SERVER['HTTP_HOST'] ?? 'localhost') . (dirname(dirname(dirname($_SERVER['SCRIPT_NAME'] ?? ''))) ?: '') . '/uploads';
}
function bf_price($p, $s = '₦') { return $s . number_format((float)$p, 2); }
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
    tailwind.config = { theme: { extend: { colors: { obsidian: '#0a0a0a', neonPink: '#ff007f', neonBlue: '#00f2ff' }, fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] } } } }
  </script>
<style>.neon-border-pink { border: 1px solid rgba(255, 0, 127, 0.5); } .neon-border-pink:hover { border-color: #ff007f; } .hide-scrollbar::-webkit-scrollbar { display: none; } .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }</style>
</head>
<body class="bg-obsidian text-white font-sans antialiased selection:bg-neonPink selection:text-white">
<div class="flex min-h-screen">
<aside class="w-1/4 lg:w-1/5 h-screen sticky top-0 border-r border-white/10 bg-black flex flex-col justify-between p-8">
<div>
<h1 class="text-3xl font-black tracking-tighter italic text-neonPink mb-12"><?php echo htmlspecialchars(mb_substr($restaurant['name'], 0, 12)); ?><br/><span class="text-neonBlue"><?php echo htmlspecialchars(mb_substr($restaurant['name'], 12, 20) ?: 'Menu'); ?></span></h1>
<nav class="space-y-6">
<?php foreach ($activeCategories as $i => $cat): $s = isset($cat['slug']) ? $cat['slug'] : ('section-'.$i); ?>
<a class="block text-xl font-bold hover:text-neonPink transition-colors duration-300 group" href="#<?php echo htmlspecialchars($s); ?>"><span class="text-xs mr-2 opacity-50 group-hover:text-neonPink"><?php echo str_pad((string)($i+1), 2, '0', STR_PAD_LEFT); ?></span> <?php echo strtoupper(htmlspecialchars($cat['name'])); ?></a>
<?php endforeach; ?>
</nav>
</div>
<div class="text-xs text-gray-500 uppercase tracking-widest"><?php echo htmlspecialchars($restaurant['address'] ?? ''); ?></div>
</aside>
<main class="flex-1 p-8 lg:p-16 overflow-y-auto">
<header class="mb-24">
<span class="text-neonPink font-mono text-sm tracking-widest uppercase mb-4 block"><?php echo htmlspecialchars($restaurant['description'] ?? 'Urban'); ?></span>
<h2 class="text-7xl lg:text-8xl font-black italic uppercase leading-none tracking-tighter"><?php echo htmlspecialchars($restaurant['name']); ?></h2>
</header>
<?php foreach ($activeCategories as $catIndex => $category): 
    $slug = isset($category['slug']) ? $category['slug'] : ('section-'.$catIndex);
    $items = $category['menu_items'];
    if (empty($items)) continue;
?>
<section class="mb-24" id="<?php echo htmlspecialchars($slug); ?>">
<h3 class="text-2xl font-bold text-neonPink mb-8 border-b border-white/20 pb-4"><?php echo htmlspecialchars($category['name']); ?></h3>
<div class="grid grid-cols-1 md:grid-cols-2 gap-12">
<?php foreach ($items as $item): ?>
<div class="neon-border-pink p-6 rounded-lg hover:bg-white/5 transition-colors">
<div class="flex justify-between items-baseline mb-2">
<h4 class="text-xl font-bold"><?php echo htmlspecialchars($item['name']); ?></h4>
<span class="text-neonBlue font-mono"><?php echo bf_price($item['price']); ?></span>
</div>
<p class="text-sm text-gray-400"><?php echo htmlspecialchars($item['description'] ?? ''); ?></p>
</div>
<?php endforeach; ?>
</div>
</section>
<?php endforeach; ?>
</main>
</div>
</body></html>
