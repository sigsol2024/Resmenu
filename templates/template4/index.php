<?php
/**
 * Template 4: The Gourmet Grill Design
 * Premium dark-themed restaurant menu with Tailwind CSS
 * Flame-grilled aesthetic with Epilogue font and herb pattern
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

// Get active categories for navigation (from sections)
$activeCategories = [];
if (!empty($sections) && is_array($sections)) {
    foreach ($sections as $sec) {
        if (empty($sec['categories']) || !is_array($sec['categories'])) continue;
        foreach ($sec['categories'] as $category) {
            if (!empty($category['menu_items']) && is_array($category['menu_items'])) {
                $activeCategories[] = $category;
            }
        }
    }
}

// Base URLs (restored from working backup)
if (defined('UPLOAD_URL')) {
    $uploadBaseUrl = rtrim(UPLOAD_URL, '/');
} else {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptPath = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
    $baseDir = dirname($scriptPath);
    $baseDir = ($baseDir === '/' || $baseDir === '\\') ? '' : rtrim($baseDir, '/');
    $uploadBaseUrl = $protocol . $host . $baseDir . '/uploads';
}

$template4BaseUrl = (defined('SITE_URL') ? rtrim(SITE_URL, '/') : ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? 'localhost'))) . '/templates/template4';
$reservationUrl = (defined('SITE_URL') ? rtrim(SITE_URL, '/') : '') . '/restaurant/' . ($restaurant['slug'] ?? '') . '/reservation';

// Hero image (section image for section pages if set; else restaurant hero, fallback)
$heroBgImage = '';
if (!empty($singleSectionView) && !empty($sections[0]['image'])) {
    $heroBgImage = $uploadBaseUrl . '/sections/' . htmlspecialchars($sections[0]['image']);
} elseif (!empty($restaurant['hero_image_url'])) {
    $heroBgImage = $restaurant['hero_image_url'];
} elseif (!empty($restaurant['hero_image'])) {
    $heroBgImage = $uploadBaseUrl . '/heroes/' . htmlspecialchars($restaurant['hero_image']);
} else {
    $heroBgImage = 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=1600&h=900&fit=crop';
}

// Customization colors
$primaryColor = $customization['primary_color'] ?? '#f20d0d';
$menuTitleColor = $customization['menu_title_color'] ?? '#121212';
$priceColor = $customization['price_color'] ?? '#f20d0d';
$priceSize = (int) ($customization['price_size'] ?? 18);
$priceFont = $customization['price_font'] ?? 'Epilogue';
$descColor = $customization['description_color'] ?? '#666666';
$categoryTitleColor = $customization['category_title_color'] ?? '#ffffff';
$bgColor = $customization['background_color'] ?? '#f8f5f5';

$currencySymbol = '₦';

function t4_formatPrice($price, $symbol = '₦') {
    return formatPrice($price, $symbol);
}
?>
<!DOCTYPE html>
<html class="dark" lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title><?php echo htmlspecialchars($restaurant['name']); ?><?php if (!empty($singleSectionView) && !empty($sections[0]['name'])): ?> - <?php echo htmlspecialchars($sections[0]['name']); ?><?php else: ?> - Menu<?php endif; ?></title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Epilogue:wght@300;400;500;600;700;800;900&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
    tailwind.config = {
        darkMode: "class",
        theme: {
            extend: {
                colors: {
                    "primary": "<?php echo htmlspecialchars($primaryColor); ?>",
                    "background-light": "<?php echo htmlspecialchars($bgColor); ?>",
                    "background-dark": "#221010",
                    "charcoal": "#121212",
                },
                fontFamily: {
                    "display": ["Epilogue", "sans-serif"],
                    "serif": ["serif"],
                },
                borderRadius: {
                    "DEFAULT": "0.5rem",
                    "lg": "1rem",
                    "xl": "1.5rem",
                    "full": "9999px"
                },
            },
        },
    }
</script>
<style>
.food-pattern {
    background-color: #f0f0f0;
    position: relative;
}
.food-pattern::before {
    content: '';
    position: fixed;
    inset: 0;
    background-image: url('<?php echo htmlspecialchars($template4BaseUrl); ?>/bg_black.png');
    background-repeat: repeat;
    background-size: 280px 280px;
    opacity: 0.1;
    pointer-events: none;
    z-index: 0;
}
.menu-card-animate {
    opacity: 1;
    transform: translateY(24px);
    transition: opacity 0.6s ease-out, transform 0.6s ease-out;
}
.menu-card-animate.visible {
    opacity: 1;
    transform: translateY(0);
}
.menu-card-no-image {
    position: relative;
}
.menu-card-no-image::before {
    content: '';
    position: absolute;
    top: 50%;
    right: 0;
    width: 1px;
    height: 50%;
    background: linear-gradient(to bottom, rgba(18, 18, 18, 0.08), rgba(18, 18, 18, 0.12));
    z-index: 0;
    pointer-events: none;
}
.category-title-animate {
    opacity: 1;
    transform: translateX(0);
    transition: opacity 0.5s ease-out, transform 0.5s ease-out;
}
.category-title-animate.visible {
    opacity: 1;
    transform: translateX(0);
}
.herb-pattern {
    background-image: url(https://lh3.googleusercontent.com/aida-public/AB6AXuBhjbTaui5mvpKAiWnqupMqh3VMykhTJ7PT2Nn8T5GT0DuSsBI22jvAcrApfT7RBvkAkhoNa9-vRb9_KYfij-Ywo0CusLWwpoOfaLtkKzR7wWbAtmkw5GDvWZedr8Duq4j8nrBzgMwU3gSY6AsydtVIsB0XPuCL0RYJgRApnafbE-LIAtMvo2yFBHMB-YLwDqpYk_MaJZoGlbv54esr3mgbssRXc2dNvOTppNdTDUTh-vqsT1hBC15tA6kC0Zklb9FnpI3lq9Om07Y);
    opacity: 0.1;
}
.sidebar-pattern::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image: url('<?php echo htmlspecialchars($template4BaseUrl); ?>/bg_black.png');
    background-repeat: repeat;
    background-size: 280px 280px;
    opacity: 0.08;
    pointer-events: none;
    z-index: 0;
}
.scroll-to-top {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 30;
    width: 48px;
    height: 48px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(10px);
    transition: opacity 0.3s, visibility 0.3s, transform 0.3s;
}
.scroll-to-top.visible {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}
#menuPanel {
    right: -288px;
    transition: right 0.3s ease;
}
#menuPanel.open {
    right: 0;
}
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
</head>
<body class="font-display bg-background-light dark:bg-background-dark text-charcoal dark:text-white">
<header class="fixed top-0 w-full z-50 bg-gray-800/50 backdrop-blur-xl border-b border-white/10 px-6 py-4">
    <div class="max-w-7xl mx-auto flex items-center justify-between">
        <div class="flex items-center gap-2">
            <?php if (!empty($restaurant['logo']) && empty($isTemplatePreview)): ?>
                <img src="<?php echo $uploadBaseUrl . '/logos/' . htmlspecialchars($restaurant['logo']); ?>" alt="<?php echo htmlspecialchars($restaurant['name']); ?>" class="h-10 w-auto object-contain">
            <?php elseif (!empty($isTemplatePreview)): ?>
                <span class="text-white text-xl font-bold tracking-tight">Logo</span>
            <?php else: ?>
                <h1 class="text-white text-xl font-bold tracking-tight"><?php echo htmlspecialchars($restaurant['name']); ?></h1>
            <?php endif; ?>
        </div>
        <div class="flex items-center gap-4 ml-auto">
            <button id="menuToggle" class="p-2 text-white/80 hover:text-white" aria-label="Open menu">
                <?php echo resmenu_icon('menu', ['size' => 28, 'class' => 'text-3xl']); ?>
            </button>
        </div>
    </div>
</header>

<!-- Sidebar menu (mobile + desktop) -->
<div id="menuOverlay" class="fixed inset-0 bg-black/60 z-40 hidden" onclick="toggleMenu()"></div>
<div id="menuPanel" class="fixed top-0 w-72 h-full bg-gray-900/85 backdrop-blur-xl border-l border-white/10 z-50 sidebar-pattern overflow-hidden flex flex-col">
    <div class="relative z-10 flex-1 min-h-0 overflow-y-auto p-6 pt-20">
        <button id="menuClose" class="absolute top-4 right-4 text-white/80 hover:text-white" aria-label="Close menu">
            <?php echo resmenu_icon('close', ['size' => 28, 'class' => 'text-3xl']); ?>
        </button>
        <nav class="flex flex-col gap-4">
            <?php if (!empty($singleSectionView) && !empty($fullMenuUrl)): ?>
                <a class="text-white font-medium py-2" href="<?php echo htmlspecialchars($fullMenuUrl); ?>" onclick="toggleMenu()">Full menu</a>
            <?php endif; ?>
            <?php if (!empty($fullMenuUrl)): ?>
                <a class="text-white/80 hover:text-white font-medium py-2" href="<?php echo htmlspecialchars($fullMenuUrl); ?>#menu" onclick="toggleMenu()">View menu</a>
            <?php endif; ?>
            <?php if (!empty($sectionsForNav) && is_array($sectionsForNav)): ?>
                <?php foreach ($sectionsForNav as $navSection): ?>
                <a class="text-white/80 hover:text-white font-medium py-2" href="<?php echo htmlspecialchars($fullMenuUrl); ?>#section-<?php echo htmlspecialchars($navSection['slug'] ?? ''); ?>" onclick="toggleMenu()"><?php echo htmlspecialchars($navSection['name'] ?? ''); ?></a>
                <?php endforeach; ?>
            <?php endif; ?>
            <hr class="border-white/20 my-2" aria-hidden="true" />
            <?php foreach ($activeCategories as $cat): ?>
                <a class="text-white/80 hover:text-white font-medium py-2" href="<?php echo htmlspecialchars(!empty($fullMenuUrl) ? ((!empty($singleSectionView) && !empty($sections) && is_array($sections) && !empty($sections[0]['slug'])) ? $fullMenuUrl . '/' . $sections[0]['slug'] . '#' . $cat['slug'] . '-section' : $fullMenuUrl . '#' . $cat['slug'] . '-section') : '#' . $cat['slug'] . '-section'); ?>" onclick="toggleMenu()"><?php echo htmlspecialchars($cat['name']); ?></a>
            <?php endforeach; ?>
            <hr class="border-white/20 my-2" aria-hidden="true" />
            <?php if (!empty($supportsReservations)): ?>
                <a class="text-white font-medium py-2 bg-primary px-4 py-2 rounded-lg text-center hover:opacity-90" href="<?php echo htmlspecialchars($reservationUrl); ?>" onclick="toggleMenu()">Reserve Table</a>
            <?php endif; ?>
            <?php foreach ($navLinks as $link): ?>
                <a class="text-white/80 hover:text-white font-medium py-2" href="<?php echo htmlspecialchars($link['url'] ?? '#'); ?>" onclick="toggleMenu()"><?php echo htmlspecialchars($link['label'] ?? ''); ?></a>
            <?php endforeach; ?>
        </nav>
    </div>
</div>

<!-- Hero Section -->
<section class="relative min-h-[85vh] flex items-center justify-center bg-charcoal overflow-hidden pt-20">
    <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('<?php echo htmlspecialchars($heroBgImage); ?>');"></div>
    <div class="absolute inset-0 herb-pattern pointer-events-none"></div>
    <div class="absolute inset-0 bg-gradient-to-t from-charcoal via-black/70 to-transparent opacity-90"></div>
    <div class="relative z-10 text-center max-w-4xl px-6">
        <h2 class="text-white text-6xl md:text-8xl font-serif font-black mb-6 tracking-tight">
            <?php echo htmlspecialchars($restaurant['name']); ?>
        </h2>
        <?php if (!empty($restaurant['description'])): ?>
            <p class="text-lg md:text-2xl mb-10 text-white/90 font-light tracking-wide">
                <?php echo htmlspecialchars($restaurant['description']); ?>
            </p>
        <?php endif; ?>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <?php if (!empty($fullMenuUrl)): ?><a href="<?php echo htmlspecialchars($fullMenuUrl); ?>#menu" class="w-full sm:w-auto bg-primary hover:bg-charcoal text-white text-lg font-bold px-10 py-4 rounded-xl transition-all transform hover:scale-105">VIEW MENU</a><?php endif; ?>
            <?php if (!empty($supportsReservations)): ?>
                <a href="<?php echo htmlspecialchars($reservationUrl); ?>" class="w-full sm:w-auto bg-transparent border-2 border-white/20 hover:border-white text-white text-lg font-bold px-10 py-4 rounded-xl transition-all">
                    RESERVE TABLE
                </a>
            <?php endif; ?>
        </div>
    </div>
    <div class="absolute bottom-10 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2">
        <span class="text-white/40 text-xs font-bold tracking-[0.3em] uppercase">Scroll</span>
        <?php echo resmenu_icon('expand_more', ['size' => 24, 'class' => 'text-white/40 animate-bounce']); ?>
    </div>
</section>


<!-- Menu Section (grey bg + bg_black pattern overlay) -->
<main class="food-pattern relative min-h-screen" id="menu">
    <div class="relative z-10 max-w-7xl mx-auto px-6 py-20">
        <?php if (empty($sections) || !is_array($sections)): ?>
            <p class="text-center text-charcoal/60 py-20">No menu items available at the moment.</p>
        <?php else:
            $categoryIndex = 0;
            $totalCategoryCount = 0;
            foreach ($sections as $sec) {
                if (!empty($sec['categories']) && is_array($sec['categories'])) {
                    foreach ($sec['categories'] as $c) {
                        if (!empty($c['menu_items']) && is_array($c['menu_items'])) $totalCategoryCount++;
                    }
                }
            }
            foreach ($sections as $section): ?>
            <?php if (empty($section['categories']) || !is_array($section['categories'])) continue; ?>
            <div class="mb-16" id="section-<?php echo htmlspecialchars($section['slug']); ?>">
                <div class="flex items-center justify-center mb-10">
                    <div class="h-px flex-1 max-w-[160px] bg-charcoal/15"></div>
                    <h2 class="px-6 text-center text-2xl md:text-3xl font-serif font-black text-charcoal tracking-tight">
                        <?php if (!empty($fullMenuUrl) && empty($singleSectionView)): ?>
                            <a href="<?php echo htmlspecialchars($fullMenuUrl . '/' . $section['slug']); ?>" class="hover:underline focus:underline"><?php echo htmlspecialchars($section['name']); ?></a>
                        <?php else: ?>
                            <?php echo htmlspecialchars($section['name']); ?>
                        <?php endif; ?>
                    </h2>
                    <div class="h-px flex-1 max-w-[160px] bg-charcoal/15"></div>
                </div>
                <?php if (empty($singleSectionView) && !empty($section['image']) && empty($isTemplatePreview)): ?>
                <div class="flex justify-center mb-10 px-4">
                    <img src="<?php echo $uploadBaseUrl . '/sections/' . htmlspecialchars($section['image']); ?>" alt="" class="max-h-32 md:max-h-40 w-auto max-w-full rounded-xl object-contain shadow-md" loading="lazy" decoding="async"/>
                </div>
                <?php endif; ?>
            </div>
            <?php foreach ($section['categories'] as $category): ?>
            <?php if (empty($category['menu_items']) || !is_array($category['menu_items'])) continue; ?>
            <?php $categoryIndex++; ?>
                <div class="mb-24" id="<?php echo htmlspecialchars($category['slug']); ?>-section">
                    <div class="flex items-center gap-4 md:gap-6 mb-8 category-title-animate">
                        <?php if (!empty($category['image']) && empty($isTemplatePreview)): ?>
                        <div class="h-20 w-20 md:h-24 md:w-24 lg:h-28 lg:w-28 shrink-0 rounded-xl border-2 border-charcoal/15 bg-white shadow-sm flex items-center justify-center overflow-hidden p-2 md:p-2.5 box-border" aria-hidden="true">
                            <img src="<?php echo $uploadBaseUrl . '/categories/' . htmlspecialchars($category['image']); ?>" alt="" class="max-h-full max-w-full w-auto h-auto object-contain object-center" loading="lazy" decoding="async"/>
                        </div>
                        <?php endif; ?>
                        <h3 class="text-base md:text-lg font-serif font-black tracking-tight bg-charcoal rounded-xl px-4 py-3 shrink-0" style="color: <?php echo htmlspecialchars($categoryTitleColor); ?>"><?php echo htmlspecialchars($category['name']); ?></h3>
                        <div class="h-px flex-1 bg-charcoal/20"></div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6 md:gap-4">
                        <?php foreach (($category['menu_items'] ?? []) as $item): ?>
                            <?php
                            $itemImage = '';
                            if (!empty($item['image'])) {
                                $itemImage = $uploadBaseUrl . '/menu-items/' . htmlspecialchars($item['image']);
                            }
                            $hasImage = !empty($itemImage);
                            ?>
                            <div class="menu-card-animate bg-white border border-charcoal/5 rounded-2xl overflow-hidden flex flex-col <?php echo $hasImage ? '' : 'menu-card-no-image'; ?> hover:shadow-xl hover:border-primary/10 transition-shadow duration-300 group relative">
                                <?php if ($hasImage): ?>
                                <div class="w-full h-44 md:h-36 lg:h-32 shrink-0 bg-cover bg-center rounded-t-2xl" style="background-image: url('<?php echo htmlspecialchars($itemImage); ?>');"></div>
                                <?php endif; ?>
                                <div class="flex-1 flex flex-col justify-center p-5 md:p-3 lg:p-3 relative z-10 min-w-0">
                                    <h4 class="text-lg md:text-base lg:text-sm font-bold group-hover:text-primary transition-colors mb-0.5 md:mb-0 line-clamp-2" style="color: <?php echo htmlspecialchars($menuTitleColor); ?>"><?php echo htmlspecialchars($item['name']); ?></h4>
                                    <span class="font-black text-xl md:text-lg lg:text-base mb-2 md:mb-1" style="color: <?php echo htmlspecialchars($priceColor); ?>; font-size: <?php echo $priceSize; ?>px; font-family: <?php echo htmlspecialchars($priceFont, ENT_QUOTES, 'UTF-8'); ?>, sans-serif;"><?php echo t4_formatPrice($item['price'], $currencySymbol); ?></span>
                                    <?php if (!empty($item['description'])): ?>
                                        <p class="text-sm md:text-xs leading-relaxed mb-3 md:mb-2 line-clamp-2" style="color: <?php echo htmlspecialchars($descColor); ?>"><?php echo nl2br(htmlspecialchars($item['description'])); ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($supportsOrdering)): ?>
                                    <div class="flex justify-start mt-auto">
                                        <button type="button" class="add-to-bag-btn inline-flex items-center gap-1.5 md:gap-1 bg-primary text-white hover:bg-charcoal px-4 py-2 md:px-3 md:py-1.5 lg:px-3 lg:py-1.5 rounded-full text-xs md:text-[10px] lg:text-[10px] font-bold uppercase tracking-wider transition-all transform active:scale-95 shadow-lg shadow-primary/20 cursor-pointer border-0"
                                            data-item-id="<?php echo (int)$item['id']; ?>"
                                            data-item-name="<?php echo htmlspecialchars($item['name']); ?>"
                                            data-item-price="<?php echo htmlspecialchars($item['price']); ?>"
                                            data-item-image="<?php echo !empty($item['image']) ? htmlspecialchars($item['image']) : ''; ?>">
                                            <?php echo resmenu_icon('shopping_bag', ['size' => 16, 'class' => 'text-sm md:text-xs']); ?>
                                            Add to bag
                                        </button>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php if ($categoryIndex < $totalCategoryCount): ?>
                <div class="flex items-center gap-4 w-full my-20 py-4 px-6 rounded-xl" style="background-color: rgba(245, 240, 230, 0.9);">
                    <div class="h-px flex-1 bg-charcoal/20"></div>
                    <div class="shrink-0 flex items-center justify-center px-4">
                        <?php if (!empty($restaurant['logo']) && empty($isTemplatePreview)): ?>
                            <img src="<?php echo $uploadBaseUrl . '/logos/' . htmlspecialchars($restaurant['logo']); ?>" alt="<?php echo htmlspecialchars($restaurant['name']); ?>" class="h-8 w-auto object-contain max-w-[120px]">
                        <?php elseif (!empty($isTemplatePreview)): ?>
                            <span class="text-primary font-bold text-sm md:text-base tracking-wide whitespace-nowrap">Logo</span>
                        <?php else: ?>
                            <span class="text-primary font-bold text-sm md:text-base tracking-wide whitespace-nowrap"><?php echo htmlspecialchars($restaurant['name']); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="h-px flex-1 bg-charcoal/20"></div>
                </div>
                <?php endif; ?>
            <?php endforeach; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<!-- Footer -->
<footer class="bg-charcoal text-white pt-20 pb-10 border-t border-white/5">
    <div class="max-w-7xl mx-auto px-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-20">
            <div class="space-y-6">
                <div class="flex items-center gap-2">
                    <?php if (!empty($restaurant['logo']) && empty($isTemplatePreview)): ?>
                        <img src="<?php echo $uploadBaseUrl . '/logos/' . htmlspecialchars($restaurant['logo']); ?>" alt="<?php echo htmlspecialchars($restaurant['name']); ?>" class="h-10 w-auto object-contain">
                    <?php elseif (!empty($isTemplatePreview)): ?>
                        <span class="text-white text-xl font-bold tracking-tight">Logo</span>
                    <?php else: ?>
                        <span class="text-white text-xl font-bold tracking-tight"><?php echo htmlspecialchars($restaurant['name']); ?></span>
                    <?php endif; ?>
                </div>
                <?php if (!empty($restaurant['footer_content'])): ?>
                    <p class="text-white/40 text-sm leading-relaxed"><?php echo nl2br(htmlspecialchars($restaurant['footer_content'])); ?></p>
                <?php elseif (!empty($restaurant['description'])): ?>
                    <p class="text-white/40 text-sm leading-relaxed"><?php echo htmlspecialchars($restaurant['description']); ?></p>
                <?php endif; ?>
                <div class="flex gap-4">
                    <?php if (!empty($restaurant['instagram_url'])): ?>
                        <a class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center hover:bg-primary transition-colors text-white" href="<?php echo htmlspecialchars($restaurant['instagram_url']); ?>" target="_blank" rel="noopener">
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073z"/></svg>
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($restaurant['facebook_url'])): ?>
                        <a class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center hover:bg-primary transition-colors text-white" href="<?php echo htmlspecialchars($restaurant['facebook_url']); ?>" target="_blank" rel="noopener">
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M9 8h-3v4h3v12h5v-12h3.642l.358-4h-4v-1.667c0-.955.192-1.333 1.115-1.333h2.885v-5h-3.808c-3.596 0-5.192 1.583-5.192 4.615v3.385z"/></svg>
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($restaurant['twitter_url'])): ?>
                        <a class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center hover:bg-primary transition-colors text-white" href="<?php echo htmlspecialchars($restaurant['twitter_url']); ?>" target="_blank" rel="noopener">
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg>
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($restaurant['whatsapp_link'])): ?>
                        <a class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center hover:bg-primary transition-colors text-white" href="<?php echo htmlspecialchars($restaurant['whatsapp_link']); ?>" target="_blank" rel="noopener" title="WhatsApp">
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php if (!empty($restaurant['address']) || !empty($restaurant['phone']) || !empty($restaurant['email'])): ?>
            <div>
                <h5 class="text-white font-bold mb-6 tracking-widest uppercase text-xs">Contact Us</h5>
                <ul class="space-y-4 text-sm text-white/60">
                    <?php if (!empty($restaurant['address'])): ?>
                        <li class="flex items-start gap-3">
                            <?php echo resmenu_icon('location_on', ['size' => 20, 'class' => 'text-primary text-lg']); ?>
                            <span><?php echo nl2br(htmlspecialchars($restaurant['address'])); ?></span>
                        </li>
                    <?php endif; ?>
                    <?php if (!empty($restaurant['phone'])): ?>
                        <li class="flex items-center gap-3">
                            <?php echo resmenu_icon('call', ['size' => 20, 'class' => 'text-primary text-lg']); ?>
                            <a href="tel:<?php echo htmlspecialchars(preg_replace('/\s+/', '', $restaurant['phone'])); ?>" class="hover:text-white transition-colors"><?php echo htmlspecialchars($restaurant['phone']); ?></a>
                        </li>
                    <?php endif; ?>
                    <?php if (!empty($restaurant['email'])): ?>
                        <li class="flex items-center gap-3">
                            <?php echo resmenu_icon('email', ['size' => 20, 'class' => 'text-primary text-lg']); ?>
                            <a href="mailto:<?php echo htmlspecialchars($restaurant['email']); ?>" class="hover:text-white transition-colors"><?php echo htmlspecialchars($restaurant['email']); ?></a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
            <?php endif; ?>
        </div>
        <div class="pt-8 border-t border-white/5 flex flex-col md:flex-row justify-between items-center gap-4 text-white/20 text-xs font-medium">
            <p>© <?php echo date('Y'); ?> <?php echo htmlspecialchars($restaurant['name']); ?>. All Rights Reserved.</p>
        </div>
    </div>
</footer>

<?php if (!empty($supportsOrdering)): ?>
<link rel="stylesheet" href="<?php echo rtrim(defined('SITE_URL') ? SITE_URL : '', '/'); ?>/assets/css/cart-modal.css">
<div id="resmenu-cart-widget" class="fixed bottom-6 left-6 z-50 hidden"></div>
<script src="<?php echo rtrim(defined('SITE_URL') ? SITE_URL : '', '/'); ?>/assets/js/cart.js"></script>
<script src="<?php echo rtrim(defined('SITE_URL') ? SITE_URL : '', '/'); ?>/assets/js/cart-widget.js"></script>
<script src="<?php echo rtrim(defined('SITE_URL') ? SITE_URL : '', '/'); ?>/assets/js/cart-modal.js"></script>
<script>
(function() {
    var baseUrl = <?php echo json_encode(defined('SITE_URL') ? rtrim(SITE_URL, '/') : ''); ?>;
    var slug = <?php echo json_encode($restaurant['slug'] ?? ''); ?>;
    var config = {
        restaurantSlug: slug,
        currencySymbol: <?php echo json_encode($currencySymbol); ?>,
        uploadBaseUrl: <?php echo json_encode($uploadBaseUrl ?? ''); ?>,
        checkoutUrl: baseUrl + '/restaurant/' + slug + '/checkout',
        primaryColor: <?php echo json_encode($primaryColor ?? '#f20d0d'); ?>,
        deliveryFee: 0,
        taxRate: 0
    };
    window.RESMENU_CART_CONFIG = config;
    if (window.RESMENU_CART_MODAL) window.RESMENU_CART_MODAL.init(config);
    if (window.RESMENU_CART_WIDGET) window.RESMENU_CART_WIDGET.init(config);


    document.querySelectorAll('.add-to-bag-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id = this.getAttribute('data-item-id');
            var name = this.getAttribute('data-item-name');
            var price = this.getAttribute('data-item-price');
            var image = this.getAttribute('data-item-image') || '';
            if (window.RESMENU_CART) {
                window.RESMENU_CART.addItem(slug, { id: id, name: name, price: price, image: image }, 1);
            }
        });
    });
})();
</script>
<?php endif; ?>

<script>
function toggleMenu() {
    const panel = document.getElementById('menuPanel');
    const overlay = document.getElementById('menuOverlay');
    if (panel && overlay) {
        panel.classList.toggle('open');
        overlay.classList.toggle('hidden', !panel.classList.contains('open'));
        document.body.style.overflow = panel.classList.contains('open') ? 'hidden' : '';
    }
}
document.addEventListener('DOMContentLoaded', function() {
    const menuBtn = document.getElementById('menuToggle');
    const menuClose = document.getElementById('menuClose');
    if (menuBtn) menuBtn.addEventListener('click', toggleMenu);
    if (menuClose) menuClose.addEventListener('click', toggleMenu);

    document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href === '#') return;
            const target = document.querySelector(href);
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    function t4RevealAnimated(selector) {
        document.querySelectorAll(selector).forEach(function(el) {
            el.classList.add('visible');
        });
    }
    if (typeof IntersectionObserver !== 'undefined') {
        var menuCards = document.querySelectorAll('.menu-card-animate');
        var cardObserver = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, { threshold: 0.05, rootMargin: '0px 0px 120px 0px' });
        menuCards.forEach(function(card) {
            cardObserver.observe(card);
            var rect = card.getBoundingClientRect();
            if (rect.top < window.innerHeight + 120) {
                card.classList.add('visible');
            }
        });

        var categoryTitles = document.querySelectorAll('.category-title-animate');
        var titleObserver = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, { threshold: 0.05, rootMargin: '0px 0px 80px 0px' });
        categoryTitles.forEach(function(el) {
            titleObserver.observe(el);
            el.classList.add('visible');
        });
    } else {
        t4RevealAnimated('.menu-card-animate');
        t4RevealAnimated('.category-title-animate');
    }

    var scrollBtn = document.getElementById('scrollToTop');
    if (scrollBtn) {
        window.addEventListener('scroll', function() {
            var scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            var docHeight = document.documentElement.scrollHeight - window.innerHeight;
            if (docHeight > 0 && scrollTop >= docHeight * 0.3) {
                scrollBtn.classList.add('visible');
            } else {
                scrollBtn.classList.remove('visible');
            }
        });
        scrollBtn.addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }
});
</script>
<a id="scrollToTop" href="#" class="scroll-to-top flex items-center justify-center rounded-full bg-charcoal text-white hover:bg-primary transition-colors shadow-lg w-12 h-12" aria-label="Scroll to top">
    <?php echo resmenu_icon('arrow_upward', ['size' => 24, 'class' => 'text-2xl']); ?>
</a>
</body>
</html>
