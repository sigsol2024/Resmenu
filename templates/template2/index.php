<?php
/**
 * Template 2: Salt and Social Design
 * Modern restaurant menu template with Tailwind CSS
 */

// Parse header menu items
$navLinks = [];
if (!empty($headerMenuItems)) {
    if (is_string($headerMenuItems)) {
        $decoded = json_decode($headerMenuItems, true);
        if (is_array($decoded)) {
            $navLinks = $decoded;
        }
    } elseif (is_array($headerMenuItems)) {
        $navLinks = $headerMenuItems;
    }
}

// Count active categories with menu items (for navigation logic)
$activeCategoryCount = 0;
if (!empty($sections) && is_array($sections)) {
    foreach ($sections as $sec) {
        if (empty($sec['categories']) || !is_array($sec['categories'])) continue;
        foreach ($sec['categories'] as $category) {
            if (!empty($category['menu_items']) && is_array($category['menu_items']) && $category['is_active']) {
                $activeCategoryCount++;
            }
        }
    }
}

// Use toggle menu if more than 1 category (like template1)
$useToggleMenu = $activeCategoryCount > 1;

// Get the correct base URL dynamically
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$currentDir = dirname($_SERVER['SCRIPT_NAME'] ?? '/');
$currentDir = ($currentDir === '/' || $currentDir === '\\') ? '' : rtrim($currentDir, '/');
$baseUrl = $protocol . $host . $currentDir;
$uploadBaseUrl = $baseUrl . '/uploads';
$reservationUrl = (defined('SITE_URL') ? rtrim(SITE_URL, '/') : $baseUrl) . '/restaurant/' . ($restaurant['slug'] ?? '') . '/reservation';

// Get hero image (section image for section pages if set; else restaurant hero; else fallback)
$heroImage = '';
if (!empty($singleSectionView) && !empty($sections[0]['image'])) {
    $heroImage = $uploadBaseUrl . '/sections/' . htmlspecialchars($sections[0]['image']);
} elseif (!empty($restaurant['hero_image_url'])) {
    $heroImage = $restaurant['hero_image_url'];
} elseif (!empty($restaurant['hero_image'])) {
    $heroImage = $uploadBaseUrl . '/heroes/' . htmlspecialchars($restaurant['hero_image']);
} else {
    $heroImage = 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=1200&h=800&fit=crop';
}

// Format price helper function
function formatPriceTemplate2($price, $currency = '$') {
    return formatPrice($price, $currency);
}
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title><?php echo htmlspecialchars($restaurant['name']); ?><?php if (!empty($singleSectionView) && !empty($sections[0]['name'])): ?> - <?php echo htmlspecialchars($sections[0]['name']); ?><?php else: ?> - <?php echo htmlspecialchars($restaurant['description'] ?? 'Restaurant Menu'); ?><?php endif; ?></title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;700;900&family=Noto+Sans:wght@400;500;700;900&display=swap" rel="stylesheet"/>
<script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#ea2a33",
                        "background-light": "#f8f6f6",
                        "background-dark": "#211111",
                    },
                    fontFamily: {
                        "display": ["Be Vietnam Pro", "Noto Sans", "sans-serif"]
                    },
                    borderRadius: {"DEFAULT": "1rem", "lg": "2rem", "xl": "3rem", "full": "9999px"},
                },
            },
        }
    </script>
    <style>
    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-[#1b0e0e] dark:text-white antialiased overflow-x-hidden">
<div class="relative flex h-auto min-h-screen w-full flex-col group/design-root">
<!-- Navigation -->
<nav class="w-full bg-[#fcf8f8] dark:bg-[#1b0e0e] border-b border-[#f3e7e8] dark:border-[#332222] sticky top-0 z-50">
<div class="px-4 md:px-10 py-3 flex items-center justify-between max-w-[1440px] mx-auto">
<div class="flex items-center gap-4 text-[#1b0e0e] dark:text-white">
<?php if (!empty($restaurant['logo']) && empty($isTemplatePreview)): ?>
    <img src="<?php echo $uploadBaseUrl . '/logos/' . htmlspecialchars($restaurant['logo']); ?>" alt="<?php echo htmlspecialchars($restaurant['name']); ?>" class="h-8 w-auto object-contain max-w-[200px]">
<?php elseif (!empty($isTemplatePreview)): ?>
    <span class="text-lg font-bold">Logo</span>
<?php else: ?>
    <div class="size-8 flex items-center justify-center text-primary">
        <?php echo resmenu_icon('restaurant_menu', ['size' => 28, 'class' => 'text-3xl']); ?>
    </div>
<?php endif; ?>
<h2 class="text-lg font-bold leading-tight tracking-[-0.015em]"><?php echo htmlspecialchars($restaurant['name'] ?? 'Restaurant'); ?></h2>
</div>
<div class="flex items-center gap-6 hidden md:flex flex-wrap">
<?php if (!empty($singleSectionView) && !empty($fullMenuUrl)): ?>
    <a href="<?php echo htmlspecialchars($fullMenuUrl); ?>" class="text-[#1b0e0e] dark:text-gray-200 text-sm font-medium hover:text-primary transition-colors">Full menu</a>
<?php endif; ?>
<?php if (!empty($fullMenuUrl)): ?>
    <a href="<?php echo htmlspecialchars($fullMenuUrl); ?>#menu" class="text-[#1b0e0e] dark:text-gray-200 text-sm font-medium hover:text-primary transition-colors">View menu</a>
<?php endif; ?>
<?php if (!empty($sectionsForNav) && is_array($sectionsForNav)): ?>
    <?php foreach ($sectionsForNav as $navSection): ?>
    <a href="<?php echo htmlspecialchars($fullMenuUrl); ?>#section-<?php echo htmlspecialchars($navSection['slug'] ?? ''); ?>" class="text-[#1b0e0e] dark:text-gray-200 text-sm font-medium hover:text-primary transition-colors"><?php echo htmlspecialchars($navSection['name'] ?? ''); ?></a>
    <?php endforeach; ?>
<?php endif; ?>
<span class="border-l border-[#1b0e0e]/30 dark:border-gray-200/30 h-4" aria-hidden="true"></span>
<?php if ($useToggleMenu): ?>
    <button class="flex items-center gap-2 text-[#1b0e0e] dark:text-gray-200 text-sm font-medium hover:text-primary transition-colors" onclick="toggleCategoryMenu()">
        <?php echo resmenu_icon('menu', ['size' => 24]); ?>
        <span>Categories</span>
    </button>
<?php else: ?>
    <?php
    if (!empty($categories) && is_array($categories)):
        foreach ($categories as $category):
            if (!empty($category['menu_items']) && is_array($category['menu_items']) && $category['is_active']):
    ?>
    <a class="text-[#1b0e0e] dark:text-gray-200 text-sm font-medium hover:text-primary transition-colors" href="<?php echo htmlspecialchars(!empty($fullMenuUrl) ? ((!empty($singleSectionView) && !empty($sections) && is_array($sections) && !empty($sections[0]['slug'])) ? $fullMenuUrl . '/' . $sections[0]['slug'] . '#' . $category['slug'] : $fullMenuUrl . '#' . $category['slug']) : '#' . $category['slug']); ?>"><?php echo htmlspecialchars($category['name']); ?></a>
    <?php
            endif;
        endforeach;
    endif;
    ?>
<?php endif; ?>
<span class="border-l border-[#1b0e0e]/30 dark:border-gray-200/30 h-4" aria-hidden="true"></span>
<?php if (!empty($supportsReservations)): ?>
    <a href="<?php echo htmlspecialchars($reservationUrl); ?>" class="text-[#1b0e0e] dark:text-gray-200 text-sm font-medium hover:text-primary transition-colors">Reserve Table</a>
<?php endif; ?>
</div>
<div class="md:hidden text-[#1b0e0e] dark:text-white">
<?php echo resmenu_icon('menu', ['size' => 28, 'class' => 'cursor-pointer text-3xl']); ?>
</div>
</div>
</nav>

<!-- Mobile Category Sidebar -->
<?php if ($useToggleMenu): ?>
<div id="categorySidebar" class="fixed inset-y-0 right-0 w-80 bg-white dark:bg-[#1b0e0e] shadow-xl z-50 transform translate-x-full transition-transform duration-300">
<div class="p-6">
<div class="flex items-center justify-between mb-6">
<h3 class="text-xl font-bold">Menu Categories</h3>
<button onclick="toggleCategoryMenu()" class="text-gray-500 hover:text-gray-700">
<?php echo resmenu_icon('close', ['size' => 24]); ?>
</button>
</div>
<nav class="flex flex-col gap-2">
<?php if (!empty($singleSectionView) && !empty($fullMenuUrl)): ?>
<a href="<?php echo htmlspecialchars($fullMenuUrl); ?>" onclick="toggleCategoryMenu()" class="text-[#1b0e0e] dark:text-white py-2 px-4 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">Full menu</a>
<?php endif; ?>
<?php if (!empty($fullMenuUrl)): ?>
<a href="<?php echo htmlspecialchars($fullMenuUrl); ?>#menu" onclick="toggleCategoryMenu()" class="text-[#1b0e0e] dark:text-white py-2 px-4 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">View menu</a>
<?php endif; ?>
<?php if (!empty($sectionsForNav) && is_array($sectionsForNav)): ?>
<?php foreach ($sectionsForNav as $navSection): ?>
<a href="<?php echo htmlspecialchars($fullMenuUrl); ?>#section-<?php echo htmlspecialchars($navSection['slug'] ?? ''); ?>" onclick="toggleCategoryMenu()" class="text-[#1b0e0e] dark:text-white py-2 px-4 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"><?php echo htmlspecialchars($navSection['name'] ?? ''); ?></a>
<?php endforeach; ?>
<?php endif; ?>
<hr class="border-[#f3e7e8] dark:border-[#332222] my-2" aria-hidden="true" />
<?php
if (!empty($categories) && is_array($categories)):
    foreach ($categories as $category):
        if (!empty($category['menu_items']) && is_array($category['menu_items']) && $category['is_active']):
?>
<a href="<?php echo htmlspecialchars(!empty($fullMenuUrl) ? ((!empty($singleSectionView) && !empty($sections) && is_array($sections) && !empty($sections[0]['slug'])) ? $fullMenuUrl . '/' . $sections[0]['slug'] . '#' . $category['slug'] : $fullMenuUrl . '#' . $category['slug']) : '#' . $category['slug']); ?>" onclick="toggleCategoryMenu()" class="text-[#1b0e0e] dark:text-white py-2 px-4 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"><?php echo htmlspecialchars($category['name']); ?></a>
<?php
        endif;
    endforeach;
endif;
?>
<hr class="border-[#f3e7e8] dark:border-[#332222] my-2" aria-hidden="true" />
<?php if (!empty($supportsReservations)): ?>
<a href="<?php echo htmlspecialchars($reservationUrl); ?>" onclick="toggleCategoryMenu()" class="text-[#1b0e0e] dark:text-white py-2 px-4 rounded-lg bg-primary text-white font-bold hover:opacity-90 transition-colors">Reserve Table</a>
<?php endif; ?>
</nav>
</div>
</div>
<div id="categoryOverlay" class="fixed inset-0 bg-black/50 z-40 hidden" onclick="toggleCategoryMenu()"></div>
<?php endif; ?>

<div class="layout-container flex h-full grow flex-col max-w-[1440px] mx-auto w-full">
<!-- Hero Section -->
<div class="px-4 md:px-40 flex flex-1 justify-center py-5">
<div class="layout-content-container flex flex-col w-full flex-1">
<div class="@container">
<div class="@[480px]:p-4">
<div class="flex min-h-[560px] flex-col gap-6 bg-cover bg-center bg-no-repeat @[480px]:gap-8 rounded-xl md:rounded-xl items-center justify-center p-8 relative overflow-hidden group shadow-xl" style='background-image: linear-gradient(rgba(0, 0, 0, 0.3) 0%, rgba(0, 0, 0, 0.6) 100%), url("<?php echo htmlspecialchars($heroImage); ?>");'>
<div class="flex flex-col gap-4 text-center z-10 max-w-[800px]">
<h1 class="text-white text-5xl font-black leading-tight tracking-[-0.033em] md:text-7xl drop-shadow-md">
                                        <?php echo htmlspecialchars($restaurant['name']); ?>
                                    </h1>
<?php if (!empty($restaurant['description'])): ?>
<h2 class="text-white text-lg font-medium leading-normal md:text-2xl opacity-90">
                                        <?php echo htmlspecialchars($restaurant['description']); ?>
                                    </h2>
<?php endif; ?>
</div>
<div class="flex flex-wrap gap-4 justify-center z-10 mt-4">
<?php if (!empty($fullMenuUrl)): ?><a href="<?php echo htmlspecialchars($fullMenuUrl); ?>#menu" class="flex min-w-[140px] cursor-pointer items-center justify-center overflow-hidden rounded-full h-12 px-8 bg-primary text-white text-base font-bold shadow-lg shadow-primary/40 transition-all hover:bg-red-600 hover:scale-105"><span class="truncate">View Menu</span></a><?php endif; ?>
<?php if (!empty($supportsReservations)): ?>
<a href="<?php echo htmlspecialchars($reservationUrl); ?>" class="flex min-w-[140px] items-center justify-center overflow-hidden rounded-full h-12 px-8 border-2 border-white/80 text-white text-base font-bold transition-all hover:bg-white/20 hover:scale-105">
<span class="truncate">Reserve Table</span>
</a>
<?php endif; ?>
</div>
</div>
</div>
</div>
</div>
</div>
</div>

<!-- Full Menu Categories -->
<?php if (!empty($sections) && is_array($sections)): ?>
<?php $categoryIndex = 0; ?>
<div id="menu">
<?php foreach ($sections as $section): ?>
<?php if (empty($section['categories']) || !is_array($section['categories'])) continue; ?>
<div class="px-4 md:px-40 flex justify-center pt-16 pb-2" id="section-<?php echo htmlspecialchars($section['slug']); ?>">
<div class="w-full max-w-[960px] text-center">
<h2 class="text-primary text-xl md:text-2xl font-black uppercase tracking-widest mb-8"><?php if (!empty($fullMenuUrl) && empty($singleSectionView)): ?><a href="<?php echo htmlspecialchars($fullMenuUrl . '/' . $section['slug']); ?>" class="hover:underline"><?php echo htmlspecialchars($section['name']); ?></a><?php else: ?><?php echo htmlspecialchars($section['name']); ?><?php endif; ?></h2>
</div>
</div>
<?php foreach ($section['categories'] as $category): ?>
<?php if (!empty($category['menu_items']) && is_array($category['menu_items']) && $category['is_active']): ?>
<?php $categoryIndex++; ?>
<div id="<?php echo htmlspecialchars($category['slug']); ?>" class="px-4 md:px-40 flex justify-center <?php echo $categoryIndex === 1 ? 'pt-4' : 'pt-20'; ?> pb-5">
<div class="w-full max-w-[960px]">
<div class="flex items-center gap-3 mb-6">
<span class="h-px w-8 bg-primary"></span>
<span class="text-primary text-base md:text-lg font-black uppercase tracking-widest"><?php echo htmlspecialchars($category['name']); ?></span>
</div>
<?php if (!empty($category['description'])): ?>
<p class="text-[#1b0e0e] dark:text-gray-300 text-lg mb-8"><?php echo htmlspecialchars($category['description']); ?></p>
<?php endif; ?>
</div>
</div>
<div class="px-4 md:px-40 flex flex-1 justify-center pb-10">
<div class="layout-content-container flex flex-col max-w-[960px] flex-1">
<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8">
<?php $itemIndex = 0; ?>
<?php foreach ($category['menu_items'] as $item): ?>
<?php $itemIndex++; ?>
<div class="relative flex flex-col group cursor-pointer" style="--index: <?php echo $itemIndex - 1; ?>;">
<?php if (!empty($item['image'])): ?>
<div class="w-full aspect-[4/3] overflow-hidden rounded-xl bg-gray-100 relative mb-0">
<div class="absolute inset-0 bg-cover bg-center transition-transform duration-500 group-hover:scale-110" style='background-image: url("<?php echo $uploadBaseUrl . '/menu-items/' . htmlspecialchars($item['image']); ?>");'></div>
<div class="absolute top-3 right-3 bg-white/90 dark:bg-black/80 backdrop-blur rounded-full px-3 py-1 text-xs font-bold shadow-sm"><?php echo formatPriceTemplate2($item['price']); ?></div>
<?php if (!$item['is_available']): ?>
<div class="absolute inset-0 bg-black/60 flex items-center justify-center">
<span class="text-white font-bold text-lg">Unavailable</span>
</div>
<?php endif; ?>
</div>
<?php endif; ?>
<div class="bg-white dark:bg-[#2a1a1a] rounded-lg p-6 shadow-md hover:shadow-lg transition-all duration-300 ease-in-out <?php echo !empty($item['image']) ? '-mt-8 relative z-10' : ''; ?>" style="animation: slideUp 0.6s ease-in-out forwards; animation-delay: calc(var(--index, 0) * 0.1s); opacity: 0; transform: translateY(30px);">
<div class="flex flex-col">
<div class="flex items-center justify-between gap-2 mb-1">
<h3 class="text-[#1b0e0e] dark:text-white text-xl font-bold leading-tight group-hover:text-primary transition-colors"><?php echo htmlspecialchars($item['name']); ?></h3>
<?php if (empty($item['image'])): ?>
<span class="text-primary text-lg font-bold whitespace-nowrap"><?php echo formatPriceTemplate2($item['price']); ?></span>
<?php endif; ?>
</div>
<?php if (!empty($item['description'])): ?>
<p class="text-gray-500 dark:text-gray-400 text-sm mt-1 line-clamp-2"><?php echo htmlspecialchars($item['description']); ?></p>
<?php endif; ?>
<?php if (!$item['is_available'] && empty($item['image'])): ?>
<span class="mt-2 text-sm font-bold text-red-500">Unavailable</span>
<?php endif; ?>
<?php if ($item['is_available'] && !empty($supportsOrdering)): ?>
<button type="button" class="add-to-bag-btn mt-3 text-sm font-bold text-[#1b0e0e] dark:text-white flex items-center gap-2 group/btn cursor-pointer border-0 bg-transparent p-0"
    data-item-id="<?php echo (int)$item['id']; ?>"
    data-item-name="<?php echo htmlspecialchars($item['name']); ?>"
    data-item-price="<?php echo htmlspecialchars($item['price']); ?>"
    data-item-image="<?php echo !empty($item['image']) ? htmlspecialchars($item['image']) : ''; ?>">
    Add to Order <?php echo resmenu_icon('add_circle', ['size' => 18, 'class' => 'text-base text-primary transition-transform group-hover/btn:translate-x-1']); ?>
</button>
<?php endif; ?>
</div>
</div>
</div>
<?php endforeach; ?>
</div>
</div>
</div>
<?php if ($categoryIndex < $activeCategoryCount): ?>
<!-- Category Divider -->
<div class="px-4 md:px-40 flex justify-center py-8">
<div class="w-full max-w-[960px]">
<div class="border-t-2 border-[#f3e7e8] dark:border-[#332222]"></div>
</div>
</div>
<?php endif; ?>
<?php endif; ?>
<?php endforeach; ?>
<?php endforeach; ?>
</div>
<?php endif; ?>

<!-- CTA Footer Section -->
<div class="px-4 md:px-40 flex flex-1 justify-center py-20 bg-[#1b0e0e] text-white mt-10 rounded-t-[3rem]">
<div class="layout-content-container flex flex-col max-w-[960px] flex-1">
<div class="flex flex-col md:flex-row justify-between gap-10">
<div class="flex flex-col gap-4 max-w-sm">
<h2 class="text-3xl font-bold">Join the Social</h2>
<p class="text-gray-400">Sign up for our newsletter to get the latest updates on events, new menu items, and exclusive offers.</p>
<?php if (!empty($restaurant['email'])): ?>
<div class="flex gap-2 mt-2">
<a href="mailto:<?php echo htmlspecialchars($restaurant['email']); ?>" class="bg-primary text-white rounded-full px-6 py-3 font-bold hover:bg-red-600 transition-colors text-center">Contact Us</a>
</div>
<?php endif; ?>
</div>
<div class="flex flex-col gap-6">
<div class="grid grid-cols-2 gap-x-12 gap-y-4">
<?php if (!empty($restaurant['address'])): ?>
<div>
<h4 class="font-bold mb-2 text-primary">Location</h4>
<p class="text-sm text-gray-300"><?php echo nl2br(htmlspecialchars($restaurant['address'])); ?></p>
</div>
<?php endif; ?>
<?php if (!empty($restaurant['phone'])): ?>
<div>
<h4 class="font-bold mb-2 text-primary">Contact</h4>
<p class="text-sm text-gray-300">
<?php if (!empty($restaurant['phone'])): ?>
<a href="tel:<?php echo htmlspecialchars($restaurant['phone']); ?>" class="hover:text-white"><?php echo htmlspecialchars($restaurant['phone']); ?></a><br/>
<?php endif; ?>
<?php if (!empty($restaurant['email'])): ?>
<a href="mailto:<?php echo htmlspecialchars($restaurant['email']); ?>" class="hover:text-white"><?php echo htmlspecialchars($restaurant['email']); ?></a>
<?php endif; ?>
</p>
</div>
<?php endif; ?>
</div>
<?php if (!empty($restaurant['instagram_url']) || !empty($restaurant['facebook_url']) || !empty($restaurant['twitter_url']) || !empty($restaurant['whatsapp_link'])): ?>
<div class="flex gap-4 mt-4">
<?php if (!empty($restaurant['instagram_url'])): ?>
<a class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center hover:bg-primary transition-colors text-white" href="<?php echo htmlspecialchars($restaurant['instagram_url']); ?>" target="_blank" rel="noopener" title="Instagram">
<svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073z"/></svg>
</a>
<?php endif; ?>
<?php if (!empty($restaurant['facebook_url'])): ?>
<a class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center hover:bg-primary transition-colors text-white" href="<?php echo htmlspecialchars($restaurant['facebook_url']); ?>" target="_blank" rel="noopener" title="Facebook">
<svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M9 8h-3v4h3v12h5v-12h3.642l.358-4h-4v-1.667c0-.955.192-1.333 1.115-1.333h2.885v-5h-3.808c-3.596 0-5.192 1.583-5.192 4.615v3.385z"/></svg>
</a>
<?php endif; ?>
<?php if (!empty($restaurant['twitter_url'])): ?>
<a class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center hover:bg-primary transition-colors text-white" href="<?php echo htmlspecialchars($restaurant['twitter_url']); ?>" target="_blank" rel="noopener" title="Twitter">
<svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg>
</a>
<?php endif; ?>
<?php if (!empty($restaurant['whatsapp_link'])): ?>
<a class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center hover:bg-primary transition-colors text-white" href="<?php echo htmlspecialchars($restaurant['whatsapp_link']); ?>" target="_blank" rel="noopener" title="WhatsApp">
<svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
</a>
<?php endif; ?>
</div>
<?php endif; ?>
</div>
</div>
<div class="border-t border-white/10 mt-16 pt-8 flex flex-col md:flex-row justify-between items-center text-xs text-gray-500 gap-4">
<p>© <?php echo date('Y'); ?> <?php echo htmlspecialchars($restaurant['name']); ?>. All rights reserved.</p>
</div>
</div>
</div>
</div>
</div>
</div>

<?php if (!empty($supportsOrdering)): ?>
<?php $primaryColor = $customization['primary_color'] ?? '#ea2a33'; $currencySymbol = '₦'; ?>
<link rel="stylesheet" href="<?php echo rtrim(defined('SITE_URL') ? SITE_URL : $baseUrl, '/'); ?>/assets/css/cart-modal.css">
<div id="resmenu-cart-widget" class="fixed bottom-6 left-6 z-50 hidden"></div>
<script src="<?php echo rtrim(defined('SITE_URL') ? SITE_URL : $baseUrl, '/'); ?>/assets/js/cart.js"></script>
<script src="<?php echo rtrim(defined('SITE_URL') ? SITE_URL : $baseUrl, '/'); ?>/assets/js/cart-widget.js"></script>
<script src="<?php echo rtrim(defined('SITE_URL') ? SITE_URL : $baseUrl, '/'); ?>/assets/js/cart-modal.js"></script>
<script>
(function() {
    var baseUrl = <?php echo json_encode(rtrim(defined('SITE_URL') ? SITE_URL : $baseUrl, '/')); ?>;
    var slug = <?php echo json_encode($restaurant['slug'] ?? ''); ?>;
    var config = { restaurantSlug: slug, currencySymbol: <?php echo json_encode($currencySymbol); ?>, uploadBaseUrl: <?php echo json_encode($uploadBaseUrl ?? ''); ?>, checkoutUrl: baseUrl + '/restaurant/' + slug + '/checkout', primaryColor: <?php echo json_encode($primaryColor); ?>, deliveryFee: 0, taxRate: 0 };
    window.RESMENU_CART_CONFIG = config;
    if (window.RESMENU_CART_MODAL) window.RESMENU_CART_MODAL.init(config);
    if (window.RESMENU_CART_WIDGET) window.RESMENU_CART_WIDGET.init(config);
    document.querySelectorAll('.add-to-bag-btn').forEach(function(btn) { btn.addEventListener('click', function() { var id=this.getAttribute('data-item-id'); var name=this.getAttribute('data-item-name'); var price=this.getAttribute('data-item-price'); var image=this.getAttribute('data-item-image')||''; if(window.RESMENU_CART) window.RESMENU_CART.addItem(slug,{id:id,name:name,price:price,image:image},1); }); });
})();
</script>
<?php endif; ?>

<script>
function toggleMobileMenu() {
    // Mobile menu toggle functionality
    alert('Mobile menu coming soon');
}

function toggleCategoryMenu() {
    const sidebar = document.getElementById('categorySidebar');
    const overlay = document.getElementById('categoryOverlay');
    if (sidebar && overlay) {
        sidebar.classList.toggle('translate-x-full');
        overlay.classList.toggle('hidden');
    }
}

// Back to top (show at 30% scroll)
(function(){
    var btn = document.getElementById('scrollToTop');
    if (btn) {
        window.addEventListener('scroll', function() {
            var st = window.pageYOffset || document.documentElement.scrollTop;
            var dh = document.documentElement.scrollHeight - window.innerHeight;
            if (dh > 0 && st >= dh * 0.3) btn.classList.add('visible');
            else btn.classList.remove('visible');
        });
        btn.addEventListener('click', function(e) { e.preventDefault(); window.scrollTo({ top: 0, behavior: 'smooth' }); });
    }
})();
</script>
<a id="scrollToTop" href="#" class="fixed bottom-6 right-6 z-30 w-12 h-12 flex items-center justify-center rounded-full bg-primary text-white shadow-lg opacity-0 invisible translate-y-2 transition-all duration-300 hover:bg-primary/90" aria-label="Scroll to top" style="pointer-events:none;"><?php echo resmenu_icon('arrow_upward', ['size' => 24, 'class' => 'text-2xl']); ?></a>
<style>#scrollToTop.visible{opacity:1!important;visibility:visible!important;transform:translateY(0)!important;pointer-events:auto!important;}</style>

</body>
</html>
