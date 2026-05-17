<?php
/**
 * Inline SVG icons (Lucide-style paths). No icon fonts — no FOIT/FOUT.
 */

if (!function_exists('resmenu_icon_paths')) {

function resmenu_icon_paths() {
    static $paths = null;
    if ($paths !== null) {
        return $paths;
    }
    $paths = [
        'shopping-bag' => '<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/>',
        'plus' => '<path d="M5 12h14"/><path d="M12 5v14"/>',
        'minus' => '<path d="M5 12h14"/>',
        'x' => '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>',
        'menu' => '<path d="M4 6h16"/><path d="M4 12h16"/><path d="M4 18h16"/>',
        'arrow-left' => '<path d="m12 19-7-7 7-7"/><path d="M19 12H5"/>',
        'arrow-right' => '<path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>',
        'arrow-up' => '<path d="m18 15-6-6-6 6"/>',
        'chevron-down' => '<path d="m6 9 6 6 6-6"/>',
        'chevron-left' => '<path d="m15 18-6-6 6-6"/>',
        'chevron-right' => '<path d="m9 18 6-6-6-6"/>',
        'eye' => '<path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>',
        'eye-off' => '<path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/>',
        'user' => '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
        'lock' => '<rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
        'utensils' => '<path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2v0a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7"/>',
        'phone' => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>',
        'mail' => '<rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>',
        'map-pin' => '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>',
        'receipt' => '<path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z"/><path d="M14 8H8"/><path d="M16 12H8"/><path d="M13 16H8"/>',
        'armchair' => '<path d="M19 9V6a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v3"/><path d="M5 11V9a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v2"/><path d="M5 15v2a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-2"/><path d="M7 19v2"/><path d="M17 19v2"/>',
        'landmark' => '<line x1="3" x2="21" y1="22" y2="22"/><line x1="6" x2="6" y1="18" y2="11"/><line x1="10" x2="10" y1="18" y2="11"/><line x1="14" x2="14" y1="18" y2="11"/><line x1="18" x2="18" y1="18" y2="11"/><polygon points="12 2 20 7 4 7"/>',
        'circle-check' => '<circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/>',
        'clock' => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
        'circle-plus' => '<circle cx="12" cy="12" r="10"/><path d="M8 12h8"/><path d="M12 8v8"/>',
        'check' => '<path d="M20 6 9 17l-5-5"/>',
    ];
    return $paths;
}

function resmenu_resolve_icon_name($name) {
    static $aliases = [
        'shopping_bag' => 'shopping-bag',
        'restaurant_menu' => 'utensils',
        'restaurant' => 'utensils',
        'location_on' => 'map-pin',
        'arrow_back' => 'arrow-left',
        'arrow_forward' => 'arrow-right',
        'arrow_upward' => 'arrow-up',
        'expand_more' => 'chevron-down',
        'add_circle' => 'circle-plus',
        'event_seat' => 'armchair',
        'receipt_long' => 'receipt',
        'account_balance' => 'landmark',
        'check_circle' => 'circle-check',
        'call' => 'phone',
        'email' => 'mail',
        'person' => 'user',
        'done' => 'check',
        'cancel' => 'x',
        'close' => 'x',
        'remove' => 'minus',
        'add' => 'plus',
        'visibility' => 'eye',
        'visibility_off' => 'eye-off',
        'place' => 'map-pin',
        'chevron_left' => 'chevron-left',
        'chevron_right' => 'chevron-right',
    ];
    return $aliases[$name] ?? $name;
}

/**
 * @param string $name Icon name or Material alias
 * @param array{class?: string, size?: int, style?: string, label?: string} $options
 */
function resmenu_icon($name, $options = []) {
    static $stylesPrinted = false;
    $html = '';
    if (!$stylesPrinted) {
        $stylesPrinted = true;
        $html .= '<style>.resmenu-icon{display:inline-block;vertical-align:middle;line-height:0;flex-shrink:0}.resmenu-icon svg{display:block}</style>';
    }
    $resolved = resmenu_resolve_icon_name($name);
    $paths = resmenu_icon_paths();
    if (!isset($paths[$resolved])) {
        return $html;
    }
    $size = (int) ($options['size'] ?? 24);
    $class = trim((string) ($options['class'] ?? ''));
    $classAttr = $class !== '' ? ' class="resmenu-icon resmenu-icon--' . htmlspecialchars($resolved, ENT_QUOTES, 'UTF-8') . ' ' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '"' : ' class="resmenu-icon resmenu-icon--' . htmlspecialchars($resolved, ENT_QUOTES, 'UTF-8') . '"';
    $style = isset($options['style']) && $options['style'] !== ''
        ? ' style="' . htmlspecialchars((string) $options['style'], ENT_QUOTES, 'UTF-8') . '"'
        : '';
    $aria = isset($options['label']) && $options['label'] !== ''
        ? ' role="img" aria-label="' . htmlspecialchars((string) $options['label'], ENT_QUOTES, 'UTF-8') . '"'
        : ' aria-hidden="true"';
    return $html . '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"' . $classAttr . $style . $aria . '>' . $paths[$resolved] . '</svg>';
}

/** Password visibility toggle markup (pair of SVGs). */
function resmenu_password_toggle_icons($size = 20, $class = '') {
    $cls = trim('resmenu-password-toggle-icons ' . $class);
    return '<span class="' . htmlspecialchars($cls, ENT_QUOTES, 'UTF-8') . '" data-resmenu-password-icons>'
        . resmenu_icon('eye', ['size' => $size, 'class' => 'resmenu-icon-eye'])
        . resmenu_icon('eye-off', ['size' => $size, 'class' => 'resmenu-icon-eye-off hidden'])
        . '</span>';
}

}
