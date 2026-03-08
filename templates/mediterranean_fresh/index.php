<?php
/**
 * Mediterranean Fresh - Sun-drenched coast style
 */
if (defined('UPLOAD_URL')) { $uploadBaseUrl = rtrim(UPLOAD_URL, '/'); } else {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $uploadBaseUrl = $protocol . ($_SERVER['HTTP_HOST'] ?? 'localhost') . (dirname(dirname(dirname($_SERVER['SCRIPT_NAME'] ?? ''))) ?: '') . '/uploads';
}
function mf_price($p, $s = '₦') { return $s . number_format((float)$p, 2); }
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
    tailwind.config = { theme: { extend: { colors: { medBlue: '#005696', lemonYellow: '#FFD700', offWhite: '#F8F9FA' }, fontFamily: { serif: ['Playfair Display', 'serif'], sans: ['Montserrat', 'sans-serif'] } } } }
  </script>
<style>@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600&family=Playfair+Display:ital,wght@0,700;1,400&display=swap'); body { font-family: 'Montserrat', sans-serif; background-color: #ffffff; } h1, h2, h3 { font-family: 'Playfair Display', serif; } .tile-pattern { background-color: #ffffff; background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M30 0l30 30-30 30L0 30 30 0z' fill='%23005696' fill-opacity='0.03' fill-rule='evenodd'/%3E%3C/svg%3E"); } .vertical-text { writing-mode: vertical-rl; text-transform: uppercase; letter-spacing: 0.2em; }</style>
</head>
<body class="tile-pattern text-slate-800 min-h-screen">
<header class="w-full py-12 px-6 text-center bg-white/80 backdrop-blur-sm border-b-4 border-medBlue">
<div class="max-w-4xl mx-auto">
<span class="text-lemonYellow text-4xl">☀</span>
<h1 class="text-5xl md:text-6xl text-medBlue font-bold mb-2"><?php echo htmlspecialchars($restaurant['name']); ?></h1>
<p class="italic text-lg text-slate-500 font-serif"><?php echo htmlspecialchars($restaurant['description'] ?? 'A Taste of the Sun-Drenched Coast'); ?></p>
</div>
</header>
<main class="max-w-6xl mx-auto px-4 py-16 grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-20">
<?php foreach ($activeCategories as $catIndex => $category): 
    $slug = isset($category['slug']) ? $category['slug'] : ('section-'.$catIndex);
    $items = $category['menu_items'];
    if (empty($items)) continue;
?>
<section class="flex gap-6 items-start" id="<?php echo htmlspecialchars($slug); ?>">
<div class="hidden md:block"><h2 class="vertical-text text-medBlue font-bold text-3xl border-l-2 border-lemonYellow pl-4 py-4"><?php echo htmlspecialchars($category['name']); ?></h2></div>
<div class="flex-1">
<h2 class="md:hidden text-3xl text-medBlue font-bold mb-6 border-b-2 border-lemonYellow inline-block"><?php echo htmlspecialchars($category['name']); ?></h2>
<div class="space-y-8">
<?php foreach ($items as $item): ?>
<div class="group">
<div class="flex justify-between items-baseline mb-1">
<h3 class="text-xl font-bold text-medBlue group-hover:text-lemonYellow transition-colors"><?php echo htmlspecialchars($item['name']); ?></h3>
<span class="font-bold text-medBlue"><?php echo mf_price($item['price']); ?></span>
</div>
<p class="text-sm text-slate-600 italic"><?php echo htmlspecialchars($item['description'] ?? ''); ?></p>
</div>
<?php endforeach; ?>
</div>
</div>
</section>
<?php endforeach; ?>
</main>
<footer class="max-w-6xl mx-auto py-12 text-center text-slate-500 border-t border-slate-200"><?php echo htmlspecialchars($restaurant['footer_content'] ?? $restaurant['address'] ?? ''); ?></footer>
</body></html>
