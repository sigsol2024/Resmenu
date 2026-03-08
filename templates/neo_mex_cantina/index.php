<?php
/**
 * Neo Mex Cantina - Tech-forward cantina
 */
if (defined('UPLOAD_URL')) { $uploadBaseUrl = rtrim(UPLOAD_URL, '/'); } else {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $uploadBaseUrl = $protocol . ($_SERVER['HTTP_HOST'] ?? 'localhost') . (dirname(dirname(dirname($_SERVER['SCRIPT_NAME'] ?? ''))) ?: '') . '/uploads';
}
function nmc_price($p, $s = '₦') { return $s . number_format((float)$p, 2); }
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
    tailwind.config = { theme: { extend: { colors: { cantina: { dark: '#0a0a0c', purple: '#7c3aed', orange: '#f97316' } }, backgroundImage: { 'gradient-radial': 'radial-gradient(var(--tw-gradient-stops))' } } } }
  </script>
<style>.brand-gradient { background: linear-gradient(135deg, #f97316 0%, #7c3aed 100%); } .glass-card { background: rgba(255,255,255,0.03); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.1); } .no-scrollbar::-webkit-scrollbar { display: none; } .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }</style>
</head>
<body class="bg-cantina-dark text-slate-100 font-sans selection:bg-orange-500/30">
<div class="flex min-h-screen">
<nav class="fixed h-screen w-20 md:w-32 flex flex-col items-center py-8 border-r border-white/10 bg-black/40 backdrop-blur-md z-50">
<div class="mb-12">
<?php if (!empty($restaurant['logo']) && empty($isTemplatePreview)): ?><img src="<?php echo $uploadBaseUrl . '/logos/' . htmlspecialchars($restaurant['logo']); ?>" alt="<?php echo htmlspecialchars($restaurant['name']); ?>" class="w-12 h-12 object-contain rounded-xl"/><?php else: ?><div class="w-12 h-12 brand-gradient rounded-xl flex items-center justify-center font-black text-2xl transform rotate-12"><?php echo strtoupper(substr($restaurant['name'], 0, 1)); ?></div><?php endif; ?>
</div>
<div class="flex flex-col space-y-12 flex-grow justify-center">
<?php foreach ($activeCategories as $i => $cat): $s = isset($cat['slug']) ? $cat['slug'] : ('section-'.$i); ?>
<a class="group flex flex-col items-center" href="#<?php echo htmlspecialchars($s); ?>"><span class="text-[10px] uppercase tracking-widest font-bold opacity-40 group-hover:opacity-100 group-hover:text-orange-500 transition-all origin-center -rotate-90"><?php echo htmlspecialchars($cat['name']); ?></span></a>
<?php endforeach; ?>
</div>
</nav>
<main class="flex-1 ml-20 md:ml-32 p-8 lg:p-16">
<header class="mb-24">
<h1 class="text-5xl md:text-7xl font-black bg-gradient-to-r from-orange-500 to-purple-600 bg-clip-text text-transparent"><?php echo htmlspecialchars($restaurant['name']); ?></h1>
<p class="text-slate-400 mt-4"><?php echo htmlspecialchars($restaurant['description'] ?? 'Modern Tech-Forward Cantina'); ?></p>
</header>
<?php foreach ($activeCategories as $catIndex => $category): 
    $slug = isset($category['slug']) ? $category['slug'] : ('section-'.$catIndex);
    $items = $category['menu_items'];
    if (empty($items)) continue;
?>
<section class="mb-20 glass-card rounded-2xl p-8" id="<?php echo htmlspecialchars($slug); ?>">
<h2 class="text-2xl font-bold text-orange-500 mb-8"><?php echo htmlspecialchars($category['name']); ?></h2>
<div class="space-y-6">
<?php foreach ($items as $item): ?>
<div class="flex gap-4 items-start border-b border-white/10 pb-4">
<?php if (!empty($item['image'])): ?><img src="<?php echo $uploadBaseUrl . '/menu-items/' . htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="w-20 h-20 flex-shrink-0 object-cover rounded"/><?php endif; ?>
<div class="flex-1 min-w-0">
<h3 class="text-lg font-semibold"><?php echo htmlspecialchars($item['name']); ?></h3>
<p class="text-sm text-slate-400"><?php echo htmlspecialchars($item['description'] ?? ''); ?></p>
</div>
<span class="font-mono text-purple-400 flex-shrink-0"><?php echo nmc_price($item['price']); ?></span>
</div>
<?php endforeach; ?>
</div>
</section>
<?php endforeach; ?>
<footer class="mt-16 pt-8 border-t border-white/10 text-center text-slate-500 text-sm">
<?php if (!empty($restaurant['footer_content'])): ?><p class="mb-4"><?php echo nl2br(htmlspecialchars($restaurant['footer_content'])); ?></p><?php endif; ?>
<?php if (!empty($restaurant['address'])): ?><p><?php echo htmlspecialchars($restaurant['address']); ?></p><?php endif; ?>
</footer>
</main>
</div>
</body></html>
