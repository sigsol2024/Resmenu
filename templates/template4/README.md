# Template 4: The Gourmet Grill

## Description

Premium dark-themed restaurant menu inspired by The Gourmet Grill design. Features Tailwind CSS, Epilogue font, herb pattern overlay, and a flame-grilled aesthetic with charcoal and primary red accents.

## Features

- Fixed top navigation with logo and header menu
- Full-height hero section with restaurant name and description
- Sticky category navigation for quick scroll
- Card-based menu layout (3-col grid for most categories, 2-col for featured)
- Image cards with hover effects
- Dark footer with contact info and social links
- WhatsApp integration for ordering
- Full customization support (colors via manager customization)

## Design Elements

- **Colors**: Primary #f20d0d, charcoal #121212, background-light #f8f5f5
- **Font**: Epilogue (display), serif for headings
- **Patterns**: Linen texture background, herb pattern overlay on hero
- **Icons**: Material Symbols Outlined

## Responsive

- Desktop: 1024px+ (3-col grid)
- Tablet: 768px - 1023px (2-col grid)
- Mobile: < 768px (1-col, stacked)

## Customization Variables

Uses restaurant customization_settings:
- primary_color
- menu_title_color
- price_color
- description_color
- category_title_color
- background_color

## File Structure

- `index.php` - Main template file (Tailwind CDN, no separate CSS)
- `README.md` - This file

## Usage Notes

- Hero image from restaurant hero_image (uploads/heroes/) or logo fallback
- Menu item images from uploads/menu-items/
- Category images from uploads/categories/
- WhatsApp link enables "Order" and "Book a Table" buttons
