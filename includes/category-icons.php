<?php
/**
 * Category icons for menu templates
 * Shared map of category name/slug keywords → emoji icons (food & drinks).
 * Use resmenu_get_category_icon($category) in any template.
 */

if (!function_exists('resmenu_get_category_icon')) {

/**
 * Return a category-appropriate icon (emoji) for section dividers or labels.
 * @param array $category Category array with at least 'name' and optionally 'slug'
 * @param string $fallback Fallback icon when no keyword matches (default ✧)
 * @return string Single emoji or fallback
 */
function resmenu_get_category_icon($category, $fallback = '✧') {
    $name = isset($category['name']) ? mb_strtolower(trim($category['name']), 'UTF-8') : '';
    $slug = isset($category['slug']) ? mb_strtolower(trim($category['slug']), 'UTF-8') : '';
    $text = $name . ' ' . $slug;

    $map = resmenu_get_category_icon_map();
    foreach ($map as $keyword => $icon) {
        if ($keyword !== '' && (mb_strpos($text, $keyword) !== false || mb_strpos($name, $keyword) !== false || mb_strpos($slug, $keyword) !== false)) {
            return $icon;
        }
    }
    return $fallback;
}

/**
 * Get the full keyword → icon map (so templates can extend or reuse).
 * @return array<string, string>
 */
function resmenu_get_category_icon_map() {
    static $map = null;
    if ($map !== null) {
        return $map;
    }

    $map = [
        // Ice cream & frozen
        'ice cream' => '🍦', 'icecream' => '🍦', 'gelato' => '🍦', 'sorbet' => '🍦', 'frozen' => '🍦',
        // Desserts & sweets
        'dessert' => '🍰', 'desserts' => '🍰', 'cake' => '🍰', 'cakes' => '🍰', 'sweet' => '🍰', 'sweets' => '🍰',
        'pastry' => '🍰', 'pastries' => '🍰', 'cookie' => '🍪', 'cookies' => '🍪', 'brownie' => '🍫', 'brownies' => '🍫',
        'pie' => '🥧', 'pies' => '🥧', 'tart' => '🥧', 'tarts' => '🥧', 'donut' => '🍩', 'donuts' => '🍩',
        'cupcake' => '🧁', 'cupcakes' => '🧁', 'chocolate' => '🍫', 'candy' => '🍬', 'pudding' => '🍮',
        // Shisha / hookah
        'shisha' => '🌿', 'hookah' => '🌿',
        // Wine & champagne
        'champagne' => '🍾', 'wine' => '🍷', 'wines' => '🍷', 'vineyard' => '🍷',
        // Spirits
        'cognac' => '🥃', 'whiskey' => '🥃', 'whisky' => '🥃', 'bourbon' => '🥃', 'tequila' => '🥃',
        'rum' => '🥃', 'vodka' => '🥃', 'gin' => '🥃', 'liqueur' => '🥃', 'spirits' => '🥃', 'liquor' => '🥃',
        // Cocktails & bar
        'cocktail' => '🍸', 'cocktails' => '🍸', 'bar' => '🍸', 'drink' => '🍷', 'drinks' => '🍷', 'beverage' => '🍷', 'beverages' => '🍷',
        'mocktail' => '🍹', 'mocktails' => '🍹', 'happy hour' => '🍸', 'late night' => '🌙',
        // Beer
        'beer' => '🍺', 'beers' => '🍺', 'draft' => '🍺', 'craft' => '🍺',
        // Coffee & tea
        'coffee' => '☕', 'espresso' => '☕', 'cappuccino' => '☕', 'latte' => '☕', 'tea' => '🍵', 'teas' => '🍵',
        'hot chocolate' => '☕', 'cocoa' => '☕',
        // Juices & soft
        'juice' => '🧃', 'juices' => '🧃', 'smoothie' => '🥤', 'smoothies' => '🥤', 'milkshake' => '🥤',
        'soda' => '🥤', 'soft drink' => '🥤', 'water' => '💧',
        // Sake
        'sake' => '🍶',
        // Starters & salads
        'appetizer' => '🥗', 'appetizers' => '🥗', 'starter' => '🥗', 'starters' => '🥗',
        'salad' => '🥗', 'salads' => '🥗', 'tapas' => '🥗', 'small plate' => '🥗', 'sharing' => '🥗', 'share' => '🥗',
        // Soup
        'soup' => '🍲', 'soups' => '🍲', 'chowder' => '🍲', 'bisque' => '🍲',
        // Main & grill
        'main' => '🥩', 'mains' => '🥩', 'entree' => '🥩', 'entrees' => '🥩', 'grill' => '🥩', 'grilled' => '🥩',
        'steak' => '🥩', 'steaks' => '🥩', 'meat' => '🥩', 'bbq' => '🍖', 'barbecue' => '🍖', 'smoked' => '🍖',
        // Seafood
        'seafood' => '🦐', 'fish' => '🐟', 'shrimp' => '🦐', 'prawn' => '🦐', 'crab' => '🦀', 'lobster' => '🦞',
        'oyster' => '🦪', 'oysters' => '🦪', 'sushi' => '🍣', 'sashimi' => '🍣',
        // Sides
        'side' => '🥔', 'sides' => '🥔', 'fry' => '🍟', 'fries' => '🍟', 'wing' => '🍗', 'wings' => '🍗',
        // Meals & cuisines
        'breakfast' => '🍳', 'brunch' => '🍳', 'lunch' => '🍽️', 'dinner' => '🍽️', 'supper' => '🍽️',
        'pizza' => '🍕', 'pizzas' => '🍕', 'burger' => '🍔', 'burgers' => '🍔', 'pasta' => '🍝', 'pastas' => '🍝',
        'taco' => '🌮', 'tacos' => '🌮', 'mexican' => '🌮', 'noodle' => '🍜', 'noodles' => '🍜', 'ramen' => '🍜',
        'rice' => '🍚', 'curry' => '🍛', 'indian' => '🍛', 'thai' => '🍜', 'chinese' => '🥡', 'japanese' => '🍣', 'korean' => '🍱',
        'asian' => '🍜',
        // Sandwiches & wraps
        'sandwich' => '🥪', 'sandwiches' => '🥪', 'wrap' => '🌯', 'wraps' => '🌯',
        // Snacks & dips
        'snack' => '🥜', 'snacks' => '🥜', 'dip' => '🥑', 'dips' => '🥑', 'bread' => '🥖', 'cheese' => '🧀',
        'charcuterie' => '🧀', 'board' => '🧀',
        // Special diet & labels
        'vegan' => '🌱', 'plant' => '🌱', 'vegetarian' => '🥬', 'healthy' => '🥗', 'organic' => '🌿', 'local' => '📍',
        'kids' => '👶', 'child' => '👶', 'children' => '👶', 'family' => '👨‍👩‍👧‍👦', 'senior' => '👴',
        // Chef / specials
        'chef' => '👨‍🍳', 'special' => '⭐', 'specials' => '⭐', 'today' => '⭐', 'catch' => '🐟', 'fresh' => '🐟',
    ];

    return $map;
}

}
