<?php
/**
 * Salt N Socials White - Clean white menu style
 */
if (defined('UPLOAD_URL')) { $uploadBaseUrl = rtrim(UPLOAD_URL, '/'); } else {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $uploadBaseUrl = $protocol . ($_SERVER['HTTP_HOST'] ?? 'localhost') . (dirname(dirname(dirname($_SERVER['SCRIPT_NAME'] ?? ''))) ?: '') . '/uploads';
}
function snsw_price($p, $s = '₦') { return $s . number_format((float)$p, 2); }
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
<link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&amp;family=Raleway:wght@300;400;600;700&amp;display=swap" rel="stylesheet"/>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script>
    tailwind.config = { theme: { extend: { colors: { 'menu-bg': '#F3FAFD', 'sidebar-bg': '#0D2633', 'accent-gold': '#C4A484', 'menu-text': '#2C263F' }, fontFamily: { raleway: ['Raleway', 'sans-serif'], opensans: ['Open Sans', 'sans-serif'] } } } }
  </script>
<style>body { background-color: #F3FAFD; font-family: "Open Sans", sans-serif; color: #2C263F; } .menu-item-row { display: flex; align-items: baseline; width: 100%; } .item-name { flex-shrink: 0; font-weight: 700; font-family: "Raleway", sans-serif; text-transform: uppercase; } .item-dots { flex-grow: 1; border-bottom: 1px dotted #2C263F; margin: 0 8px; opacity: 0.3; } .item-price { flex-shrink: 0; font-weight: 600; font-family: "Raleway", sans-serif; } .section-header { font-family: "Raleway", sans-serif; letter-spacing: 0.2em; border-bottom: 4px solid #0D2633; display: inline-block; padding-bottom: 2px; margin-bottom: 24px; }</style>
</head>
<body class="min-h-screen p-6 md:p-12">
<main class="max-w-4xl mx-auto bg-white shadow-lg p-8 md:p-16">
<header class="text-center mb-16">
<h1 class="text-4xl md:text-6xl font-raleway font-light text-menu-text mb-2"><?php echo htmlspecialchars($restaurant['name']); ?></h1>
<p class="text-sm uppercase tracking-widest text-gray-500"><?php echo htmlspecialchars($restaurant['description'] ?? 'Food &amp; Drinks'); ?></p>
</header>
<?php foreach ($activeCategories as $catIndex => $category): 
    $slug = isset($category['slug']) ? $category['slug'] : ('section-'.$catIndex);
    $items = $category['menu_items'];
    if (empty($items)) continue;
?>
<section class="mb-12" id="<?php echo htmlspecialchars($slug); ?>">
<h2 class="section-header text-2xl text-menu-text"><?php echo htmlspecialchars($category['name']); ?></h2>
<div class="space-y-4">
<?php foreach ($items as $item): ?>
<div class="menu-item-row">
<span class="item-name"><?php echo htmlspecialchars($item['name']); ?></span>
<span class="item-dots"></span>
<span class="item-price"><?php echo snsw_price($item['price']); ?></span>
</div>
<p class="text-sm text-gray-600 pl-0 mb-6"><?php echo htmlspecialchars($item['description'] ?? ''); ?></p>
<?php endforeach; ?>
</div>
</section>
<?php endforeach; ?>
<footer class="mt-16 pt-8 border-t border-gray-200 text-center text-sm text-gray-500">
<?php echo htmlspecialchars($restaurant['footer_content'] ?? $restaurant['address'] ?? ''); ?>
</footer>
</main>
</body></html>
