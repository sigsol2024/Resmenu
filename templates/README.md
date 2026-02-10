# Template System Documentation

## Overview

The restaurant menu platform supports multiple design templates. Each restaurant can select a template that determines the visual appearance and layout of their public menu page.

## Template Structure

Each template must be located in its own directory under `templates/`:

```
templates/
├── template1/
│   ├── index.php       # Main template file (required)
│   ├── style.css       # Template-specific styles (optional)
│   └── README.md       # Template documentation (optional)
├── template2/
│   └── ...
```

## Creating a New Template

### Step 1: Create Template Directory

Create a new directory: `templates/template{N}/` where `{N}` is the next available template number.

### Step 2: Create Template Files

#### `index.php` (Required)

This is the main template file that will be loaded. It receives the following variables:

**Available Variables:**

```php
// Restaurant data (array)
$restaurant = [
    'id' => int,
    'name' => string,
    'slug' => string,
    'logo' => string|null,
    'description' => string|null,
    'phone' => string|null,
    'email' => string|null,
    'address' => string|null,
    'whatsapp_link' => string|null,
    'instagram_url' => string|null,
    'facebook_url' => string|null,
    'twitter_url' => string|null,
    'map_latitude' => float|null,
    'map_longitude' => float|null,
    'header_menu_items' => string|null,  // JSON string
    'footer_content' => string|null,     // HTML content
    'template_id' => int
];

// Categories with menu items (array of arrays)
$categories = [
    [
        'id' => int,
        'name' => string,
        'slug' => string,
        'image' => string|null,
        'description' => string|null,
        'display_order' => int,
        'is_active' => bool,
        'menu_items' => [
            [
                'id' => int,
                'name' => string,
                'slug' => string,
                'description' => string|null,
                'price' => float,
                'image' => string|null,
                'display_order' => int,
                'is_available' => bool
            ],
            // ... more menu items
        ]
    ],
    // ... more categories
];

// Customization settings (array)
$customization = [
    'menu_title_color' => string,      // Hex color
    'menu_title_size' => int,          // Pixels
    'menu_title_font' => string,       // Font family
    'price_color' => string,
    'price_size' => int,
    'price_font' => string,
    'description_color' => string,
    'description_size' => int,
    'description_font' => string,
    'category_title_color' => string,
    'category_title_size' => int,
    'category_title_font' => string,
    'background_color' => string,
    'header_background_color' => string,
    'primary_color' => string,
    'secondary_color' => string
];

// Header menu items (array)
$headerMenuItems = [
    ['label' => string, 'url' => string],
    // ... more items
];
```

**Helper Functions Available:**

- `formatPrice($price)` - Formats price with currency symbol
- `UPLOAD_URL` - Base URL for uploaded files (e.g., `/uploads`)
- `getMenuItems($categoryId)` - Get menu items for a category
- `getCategories($restaurantId)` - Get categories for a restaurant

**Example Template Structure:**

```php
<?php
/**
 * Template {N}: {Template Name}
 */

// Apply customization CSS variables
$menuTitleColor = $customization['menu_title_color'] ?? '#000000';
// ... more variables

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($restaurant['name']); ?> - Menu</title>
    <link rel="stylesheet" href="/templates/template{N}/style.css">
    <style>
        :root {
            --menu-title-color: <?php echo htmlspecialchars($menuTitleColor); ?>;
            /* ... more CSS variables */
        }
    </style>
</head>
<body>
    <!-- Your template HTML here -->
    <!-- Use $restaurant, $categories, $customization, $headerMenuItems -->
</body>
</html>
```

#### `style.css` (Optional)

Template-specific CSS styles. Use CSS variables for customization:

```css
.menu-title {
    color: var(--menu-title-color, #000000);
    font-size: var(--menu-title-size, 24px);
}
```

### Step 3: Register Template in Database

Add the template to the `templates` table:

```sql
INSERT INTO templates (id, name, description) VALUES 
(N, 'Template Name', 'Template description');
```

Or run the migration script if you have one.

## CSS Variable Naming Convention

Use these CSS variable names for consistency:

- `--menu-title-color`, `--menu-title-size`, `--menu-title-font`
- `--price-color`, `--price-size`, `--price-font`
- `--description-color`, `--description-size`, `--description-font`
- `--category-title-color`, `--category-title-size`, `--category-title-fon`
- `--background-color`, `--header-bg-color`
- `--primary-color`, `--secondary-color`

## Template Requirements

1. **Responsive Design**: Templates must be mobile-friendly
2. **Accessibility**: Use semantic HTML and proper alt tags
3. **Performance**: Optimize images and CSS
4. **Security**: Always use `htmlspecialchars()` for output
5. **Search/Filter Support**: Include search and filter functionality (see Template 1 for reference)

## Testing Checklist

- [ ] Template loads correctly with sample data
- [ ] All customization settings apply correctly
- [ ] Responsive on mobile, tablet, and desktop
- [ ] Search and filter functionality works
- [ ] Images display correctly
- [ ] Links work properly
- [ ] Footer content renders correctly
- [ ] Header menu items display correctly
- [ ] Map displays if coordinates are provided
- [ ] Social media links work

## Best Practices

1. **Keep it Simple**: Don't overcomplicate the template
2. **Reuse Components**: Create reusable CSS classes
3. **Document Your Code**: Add comments explaining complex sections
4. **Test Thoroughly**: Test with various data combinations
5. **Follow Existing Patterns**: Look at Template 1 for reference

## Template Loading Flow

```
User visits /restaurant/{slug}
    ↓
restaurant.php loads
    ↓
Gets restaurant data (including template_id)
    ↓
loadTemplate() function called
    ↓
Template file loaded: templates/template{N}/index.php
    ↓
Variables extracted and made available
    ↓
Template renders HTML
```

## Troubleshooting

**Template not loading:**
- Check that `templates/template{N}/index.php` exists
- Verify template_id in database matches directory name
- Check PHP error logs

**Customization not applying:**
- Ensure CSS variables are defined in `<style>` tag
- Check that variable names match customization keys
- Verify customization settings in database

**Images not displaying:**
- Use `UPLOAD_URL` constant for image paths
- Check file permissions on uploads directory
- Verify image filenames match database values

## Support

For questions or issues with template creation, refer to Template 1 (`templates/template1/`) as a reference implementation.

