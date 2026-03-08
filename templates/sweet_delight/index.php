<?php
/**
 * Sweet Delight - Playful dessert parlour
 */
if (defined('UPLOAD_URL')) { $uploadBaseUrl = rtrim(UPLOAD_URL, '/'); } else {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $uploadBaseUrl = $protocol . ($_SERVER['HTTP_HOST'] ?? 'localhost') . (dirname(dirname(dirname($_SERVER['SCRIPT_NAME'] ?? ''))) ?: '') . '/uploads';
}
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
<div class="flex justify-between items-start mb-4">
<h3 class="text-xl text-gray-800"><?php echo htmlspecialchars($item['name']); ?></h3>
<span class="px-3 py-1 rounded-full font-bold <?php echo $useMint ? 'bg-pastel-mint text-mint-dark' : 'bg-pastel-pink text-soft-berry'; ?>"><?php echo sd_price($item['price']); ?></span>
</div>
<p class="text-gray-600 mb-4"><?php echo htmlspecialchars($item['description'] ?? ''); ?></p>
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
</body></html>
