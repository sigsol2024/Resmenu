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
if (!empty($categories) && is_array($categories)) {
    foreach ($categories as $category) {
        if (!empty($category['menu_items']) && is_array($category['menu_items']) && $category['is_active']) {
            $activeCategoryCount++;
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

// Get hero image
$heroImage = '';
if (!empty($restaurant['hero_image'])) {
    $heroImage = $uploadBaseUrl . '/logos/' . htmlspecialchars($restaurant['hero_image']);
} else {
    $heroImage = 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=1200&h=800&fit=crop';
}

// Format price helper function
function formatPriceTemplate2($price, $currency = '$') {
    return $currency . number_format($price, 2);
}
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title><?php echo htmlspecialchars($restaurant['name']); ?> - <?php echo htmlspecialchars($restaurant['description'] ?? 'Restaurant Menu'); ?></title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
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
<?php if (!empty($restaurant['logo'])): ?>
    <img src="<?php echo $uploadBaseUrl . '/logos/' . htmlspecialchars($restaurant['logo']); ?>" alt="<?php echo htmlspecialchars($restaurant['name']); ?>" class="h-8 w-auto object-contain max-w-[200px]">
<?php else: ?>
    <div class="size-8 flex items-center justify-center text-primary">
        <span class="material-symbols-outlined text-3xl">restaurant_menu</span>
    </div>
<?php endif; ?>
<h2 class="text-lg font-bold leading-tight tracking-[-0.015em]"><?php echo htmlspecialchars($restaurant['name'] ?? 'Restaurant'); ?></h2>
</div>
<div class="flex items-center gap-8 hidden md:flex">
<?php if ($useToggleMenu): ?>
    <button class="flex items-center gap-2 text-[#1b0e0e] dark:text-gray-200 text-sm font-medium hover:text-primary transition-colors" onclick="toggleCategoryMenu()">
        <span class="material-symbols-outlined">menu</span>
        <span>Categories</span>
    </button>
<?php else: ?>
    <div class="flex items-center gap-9">
        <?php 
        // Show active categories as navigation links
        if (!empty($categories) && is_array($categories)):
            foreach ($categories as $category): 
                if (!empty($category['menu_items']) && is_array($category['menu_items']) && $category['is_active']):
        ?>
            <a class="text-[#1b0e0e] dark:text-gray-200 text-sm font-medium hover:text-primary transition-colors" href="#<?php echo htmlspecialchars($category['slug']); ?>"><?php echo htmlspecialchars($category['name']); ?></a>
        <?php 
                endif;
            endforeach;
        endif;
        ?>
    </div>
<?php endif; ?>
<?php if (!empty($restaurant['whatsapp_link'])): ?>
<button onclick="window.open('<?php echo htmlspecialchars($restaurant['whatsapp_link']); ?>', '_blank')" class="flex min-w-[84px] cursor-pointer items-center justify-center overflow-hidden rounded-full h-10 px-6 bg-primary text-white text-sm font-bold shadow-lg shadow-primary/30 transition-transform hover:scale-105">
<span class="truncate">Order Online</span>
</button>
<?php endif; ?>
</div>
<div class="md:hidden text-[#1b0e0e] dark:text-white">
<span class="material-symbols-outlined cursor-pointer text-3xl" onclick="toggleMobileMenu()">menu</span>
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
<span class="material-symbols-outlined">close</span>
</button>
</div>
<nav class="flex flex-col gap-2">
<?php 
if (!empty($categories) && is_array($categories)):
    foreach ($categories as $category): 
        if (!empty($category['menu_items']) && is_array($category['menu_items']) && $category['is_active']):
?>
<a href="#<?php echo htmlspecialchars($category['slug']); ?>" onclick="toggleCategoryMenu()" class="text-[#1b0e0e] dark:text-white py-2 px-4 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"><?php echo htmlspecialchars($category['name']); ?></a>
<?php 
        endif;
    endforeach;
endif;
?>
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
<a href="#menu" class="flex min-w-[140px] cursor-pointer items-center justify-center overflow-hidden rounded-full h-12 px-8 bg-primary text-white text-base font-bold shadow-lg shadow-primary/40 transition-all hover:bg-red-600 hover:scale-105">
<span class="truncate">View Menu</span>
</a>
<?php if (!empty($restaurant['whatsapp_link'])): ?>
<a href="<?php echo htmlspecialchars($restaurant['whatsapp_link']); ?>" target="_blank" class="flex min-w-[140px] cursor-pointer items-center justify-center overflow-hidden rounded-full h-12 px-8 bg-white/90 backdrop-blur-sm text-[#1b0e0e] text-base font-bold hover:bg-white transition-all hover:scale-105">
<span class="truncate">Order Online</span>
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
<?php if (!empty($categories) && is_array($categories)): ?>
<?php $categoryIndex = 0; ?>
<?php foreach ($categories as $category): ?>
<?php if (!empty($category['menu_items']) && is_array($category['menu_items']) && $category['is_active']): ?>
<?php $categoryIndex++; ?>
<div id="<?php echo htmlspecialchars($category['slug']); ?>" class="px-4 md:px-40 flex justify-center <?php echo $categoryIndex === 1 ? 'pt-16' : 'pt-20'; ?> pb-5">
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
<?php if ($item['is_available']): ?>
<?php if (!empty($supportsOrdering)): ?>
<button type="button" class="add-to-bag-btn mt-3 text-sm font-bold text-[#1b0e0e] dark:text-white flex items-center gap-2 group/btn cursor-pointer border-0 bg-transparent p-0"
    data-item-id="<?php echo (int)$item['id']; ?>"
    data-item-name="<?php echo htmlspecialchars($item['name']); ?>"
    data-item-price="<?php echo htmlspecialchars($item['price']); ?>"
    data-item-image="<?php echo !empty($item['image']) ? htmlspecialchars($item['image']) : ''; ?>">
    Add to Order <span class="material-symbols-outlined text-base text-primary transition-transform group-hover/btn:translate-x-1">add_circle</span>
</button>
<?php elseif (!empty($restaurant['whatsapp_link'])): ?>
<a href="<?php echo htmlspecialchars($restaurant['whatsapp_link']); ?>" target="_blank" class="mt-3 text-sm font-bold text-[#1b0e0e] dark:text-white flex items-center gap-2 group/btn">
    Add to Order <span class="material-symbols-outlined text-base text-primary transition-transform group-hover/btn:translate-x-1">add_circle</span>
</a>
<?php endif; ?>
<?php endif; ?>
</div>
</div>
</div>
<?php endforeach; ?>
</div>
</div>
</div>
<?php if ($categoryIndex < count(array_filter($categories, function($cat) { return !empty($cat['menu_items']) && $cat['is_active']; }))): ?>
<!-- Category Divider -->
<div class="px-4 md:px-40 flex justify-center py-8">
<div class="w-full max-w-[960px]">
<div class="border-t-2 border-[#f3e7e8] dark:border-[#332222]"></div>
</div>
</div>
<?php endif; ?>
<?php endif; ?>
<?php endforeach; ?>
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
<?php if (!empty($restaurant['instagram_url']) || !empty($restaurant['facebook_url']) || !empty($restaurant['twitter_url'])): ?>
<div class="flex gap-4 mt-4">
<?php if (!empty($restaurant['instagram_url'])): ?>
<a class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center hover:bg-primary transition-colors" href="<?php echo htmlspecialchars($restaurant['instagram_url']); ?>" target="_blank" title="Instagram">
<span class="text-sm font-bold">IG</span>
</a>
<?php endif; ?>
<?php if (!empty($restaurant['facebook_url'])): ?>
<a class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center hover:bg-primary transition-colors" href="<?php echo htmlspecialchars($restaurant['facebook_url']); ?>" target="_blank" title="Facebook">
<span class="text-sm font-bold">FB</span>
</a>
<?php endif; ?>
<?php if (!empty($restaurant['twitter_url'])): ?>
<a class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center hover:bg-primary transition-colors" href="<?php echo htmlspecialchars($restaurant['twitter_url']); ?>" target="_blank" title="Twitter">
<span class="text-sm font-bold">TT</span>
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
</script>

</body>
</html>
