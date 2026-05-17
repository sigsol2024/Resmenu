<?php
/**
 * Replace Material icon font markup with resmenu_icon() SVG helper.
 */
$root = dirname(__DIR__);
require_once $root . '/includes/resmenu-icons.php';

$replacements = [
    '/<span class="material-symbols-outlined text-3xl">menu<\/span>/' => '<?php echo resmenu_icon(\'menu\', [\'size\' => 28, \'class\' => \'text-3xl\']); ?>',
    '/<span class="material-symbols-outlined text-2xl">menu<\/span>/' => '<?php echo resmenu_icon(\'menu\', [\'size\' => 24, \'class\' => \'text-2xl\']); ?>',
    '/<span class="material-symbols-outlined cursor-pointer text-3xl" onclick="toggleMobileMenu\(\)">menu<\/span>/' => '<?php echo resmenu_icon(\'menu\', [\'size\' => 28, \'class\' => \'cursor-pointer text-3xl\']); ?>',
    '/<span class="material-symbols-outlined">menu<\/span>/' => '<?php echo resmenu_icon(\'menu\', [\'size\' => 24]); ?>',
    '/<span class="material-symbols-outlined text-3xl">close<\/span>/' => '<?php echo resmenu_icon(\'close\', [\'size\' => 28, \'class\' => \'text-3xl\']); ?>',
    '/<span class="material-symbols-outlined text-2xl">close<\/span>/' => '<?php echo resmenu_icon(\'close\', [\'size\' => 24, \'class\' => \'text-2xl\']); ?>',
    '/<span class="material-symbols-outlined">close<\/span>/' => '<?php echo resmenu_icon(\'close\', [\'size\' => 24]); ?>',
    '/<span class="material-symbols-outlined text-sm md:text-xs">shopping_bag<\/span>/' => '<?php echo resmenu_icon(\'shopping_bag\', [\'size\' => 16, \'class\' => \'text-sm md:text-xs\']); ?>',
    '/<span class="material-symbols-outlined text-2xl">arrow_upward<\/span>/' => '<?php echo resmenu_icon(\'arrow_upward\', [\'size\' => 24, \'class\' => \'text-2xl\']); ?>',
    '/<span class="material-symbols-outlined text-3xl">restaurant_menu<\/span>/' => '<?php echo resmenu_icon(\'restaurant_menu\', [\'size\' => 28, \'class\' => \'text-3xl\']); ?>',
    '/<span class="material-symbols-outlined text-white">restaurant_menu<\/span>/' => '<?php echo resmenu_icon(\'restaurant_menu\', [\'size\' => 24, \'class\' => \'text-white\']); ?>',
    '/<span class="material-symbols-outlined text-primary text-3xl">restaurant_menu<\/span>/' => '<?php echo resmenu_icon(\'restaurant_menu\', [\'size\' => 28, \'class\' => \'text-primary text-3xl\']); ?>',
    '/<span class="material-symbols-outlined text-base text-primary transition-transform group-hover\/btn:translate-x-1">add_circle<\/span>/' => '<?php echo resmenu_icon(\'add_circle\', [\'size\' => 18, \'class\' => \'text-base text-primary transition-transform group-hover/btn:translate-x-1\']); ?>',
    '/<span class="material-symbols-outlined text-white\/40 animate-bounce">expand_more<\/span>/' => '<?php echo resmenu_icon(\'expand_more\', [\'size\' => 24, \'class\' => \'text-white/40 animate-bounce\']); ?>',
    '/<span class="material-symbols-outlined text-primary text-lg">location_on<\/span>/' => '<?php echo resmenu_icon(\'location_on\', [\'size\' => 20, \'class\' => \'text-primary text-lg\']); ?>',
    '/<span class="material-symbols-outlined text-primary text-lg">call<\/span>/' => '<?php echo resmenu_icon(\'call\', [\'size\' => 20, \'class\' => \'text-primary text-lg\']); ?>',
    '/<span class="material-symbols-outlined text-primary text-lg">mail<\/span>/' => '<?php echo resmenu_icon(\'email\', [\'size\' => 20, \'class\' => \'text-primary text-lg\']); ?>',
    '/<span class="material-symbols-outlined text-base">arrow_back<\/span>/' => '<?php echo resmenu_icon(\'arrow_back\', [\'size\' => 16, \'class\' => \'text-base\']); ?>',
    '/<span class="material-symbols-outlined text-4xl">check_circle<\/span>/' => '<?php echo resmenu_icon(\'check_circle\', [\'size\' => 40, \'class\' => \'text-4xl\']); ?>',
    '/<span class="material-symbols-outlined text-4xl">cancel<\/span>/' => '<?php echo resmenu_icon(\'cancel\', [\'size\' => 40, \'class\' => \'text-4xl\']); ?>',
    '/<span class="material-symbols-outlined text-4xl">account_balance<\/span>/' => '<?php echo resmenu_icon(\'account_balance\', [\'size\' => 40, \'class\' => \'text-4xl\']); ?>',
    '/<span class="material-symbols-outlined">done<\/span>/' => '<?php echo resmenu_icon(\'done\', [\'size\' => 20]); ?>',
    '/<span class="material-symbols-outlined">schedule<\/span>/' => '<?php echo resmenu_icon(\'schedule\', [\'size\' => 20]); ?>',
    '/<span class="material-symbols-outlined">check_circle<\/span>/' => '<?php echo resmenu_icon(\'check_circle\', [\'size\' => 20]); ?>',
    '/<span class="material-symbols-outlined">restaurant_menu<\/span>/' => '<?php echo resmenu_icon(\'restaurant_menu\', [\'size\' => 20]); ?>',
    '/<span class="material-symbols-outlined">event_seat<\/span>/' => '<?php echo resmenu_icon(\'event_seat\', [\'size\' => 20]); ?>',
    '/<span class="material-symbols-outlined text-sm">lock<\/span>/' => '<?php echo resmenu_icon(\'lock\', [\'size\' => 16, \'class\' => \'text-sm\']); ?>',
    '/<span class="material-icons text-white">restaurant<\/span>/' => '<?php echo resmenu_icon(\'restaurant\', [\'size\' => 24, \'class\' => \'text-white\']); ?>',
    '/<span class="material-icons text-white text-sm">restaurant<\/span>/' => '<?php echo resmenu_icon(\'restaurant\', [\'size\' => 18, \'class\' => \'text-white text-sm\']); ?>',
    '/<span class="material-icons text-gray-500 text-lg">expand_more<\/span>/' => '<?php echo resmenu_icon(\'expand_more\', [\'size\' => 20, \'class\' => \'text-gray-500 text-lg\']); ?>',
    '/<span class="material-icons text-lg">chevron_left<\/span>/' => '<?php echo resmenu_icon(\'chevron_left\', [\'size\' => 20, \'class\' => \'text-lg\']); ?>',
    '/<span class="material-icons text-lg">chevron_right<\/span>/' => '<?php echo resmenu_icon(\'chevron_right\', [\'size\' => 20, \'class\' => \'text-lg\']); ?>',
    '/<span class="material-icons text-sm">remove<\/span>/' => '<?php echo resmenu_icon(\'remove\', [\'size\' => 16, \'class\' => \'text-sm\']); ?>',
    '/<span class="material-icons text-sm">add<\/span>/' => '<?php echo resmenu_icon(\'add\', [\'size\' => 16, \'class\' => \'text-sm\']); ?>',
    '/<span class="material-icons text-primary text-sm">place<\/span>/' => '<?php echo resmenu_icon(\'place\', [\'size\' => 16, \'class\' => \'text-primary text-sm\']); ?>',
    '/<span class="material-icons text-primary text-sm">phone<\/span>/' => '<?php echo resmenu_icon(\'phone\', [\'size\' => 16, \'class\' => \'text-primary text-sm\']); ?>',
    '/<span class="material-icons text-primary text-sm">email<\/span>/' => '<?php echo resmenu_icon(\'email\', [\'size\' => 16, \'class\' => \'text-primary text-sm\']); ?>',
];

$fontLink = '/<link[^>]*Material\+Symbols\+Outlined[^>]*>\s*/i';
$fontLinkIcons = '/<link[^>]*family=Material\+Icons[^>]*>\s*/i';
$materialCss = '/\s*\.material-symbols-outlined\s*\{[^}]+\}\s*/';
$sweetCart = '/#resmenu-cart-widget \.resmenu-cart-widget-btn \.material-symbols-outlined \{ color: #[^;]+ !important; \}\s*/';

$dirs = [$root . '/templates', $root];
$skip = [DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR, 'migrate-icons', DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR];
$count = 0;

foreach ($dirs as $dir) {
    if (!is_dir($dir)) continue;
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($it as $file) {
        if (!$file->isFile() || $file->getExtension() !== 'php') continue;
        $path = $file->getPathname();
        $skipFile = false;
        foreach ($skip as $s) {
            if (strpos($path, $s) !== false) { $skipFile = true; break; }
        }
        if ($skipFile) continue;
        $content = file_get_contents($path);
        $orig = $content;
        foreach ($replacements as $pattern => $replacement) {
            $content = preg_replace($pattern, $replacement, $content);
        }
        $content = preg_replace($fontLink, '', $content);
        $content = preg_replace($fontLinkIcons, '', $content);
        $content = preg_replace($materialCss, "\n", $content);
        $content = preg_replace($sweetCart, '', $content);
        if ($content !== $orig) {
            file_put_contents($path, $content);
            $count++;
            echo "Updated: $path\n";
        }
    }
}
echo "Done. Files updated: $count\n";
