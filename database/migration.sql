-- ============================================================
-- Resmenu Database Migration
-- ============================================================
-- This file is intentionally minimal.
--
-- Use it only for:
-- - Future schema changes (tables/columns/indexes).
-- - Global feature updates that apply to all restaurants.
--
-- Do NOT add any restaurant-specific seed data here:
-- - No menu_items/category inserts
-- - No per-restaurant account creation

-- ============================================================
-- 40. Lusso À La Carte Menu (seed, idempotent)
-- ============================================================

-- 40.1 Resolve Lusso restaurant by email
SET @lusso_rid = (SELECT id FROM `restaurants` WHERE `email` = 'restaurant@lussohotelsabuja.com' LIMIT 1);
SET @lusso_has_restaurant = IF(@lusso_rid IS NULL, 0, 1);

-- 40.2 Create À La Carte Menu section if missing
INSERT INTO `sections` (`restaurant_id`, `name`, `slug`, `display_order`, `is_active`, `created_at`, `updated_at`)
SELECT @lusso_rid, 'À La Carte Menu', 'a-la-carte-menu', 10, 1, NOW(), NOW()
WHERE @lusso_has_restaurant = 1
  AND NOT EXISTS (
    SELECT 1 FROM `sections`
    WHERE `restaurant_id` = @lusso_rid AND `slug` = 'a-la-carte-menu'
);

SET @ala_sid = (
  SELECT `id` FROM `sections`
  WHERE `restaurant_id` = @lusso_rid AND `slug` = 'a-la-carte-menu'
  LIMIT 1
);

-- 40.3 Create categories under À La Carte Menu

-- Organic Salads & Appetizers
SET @cat_organic_salads = NULL;
INSERT INTO `categories` (`restaurant_id`, `section_id`, `name`, `slug`, `description`, `display_order`, `is_active`, `created_at`, `updated_at`)
SELECT @lusso_rid, @ala_sid, 'Organic Salads & Appetizers', 'organic-salads-appetizers', NULL, 1, 1, NOW(), NOW()
WHERE @lusso_has_restaurant = 1
  AND @ala_sid IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM `categories`
    WHERE `restaurant_id` = @lusso_rid AND `slug` = 'organic-salads-appetizers'
);
SET @cat_organic_salads = (
  SELECT `id` FROM `categories`
  WHERE `restaurant_id` = @lusso_rid AND `slug` = 'organic-salads-appetizers'
  LIMIT 1
);

-- Vegetarian
SET @cat_vegetarian = NULL;
INSERT INTO `categories` (`restaurant_id`, `section_id`, `name`, `slug`, `description`, `display_order`, `is_active`, `created_at`, `updated_at`)
SELECT @lusso_rid, @ala_sid, 'Vegetarian', 'vegetarian', NULL, 2, 1, NOW(), NOW()
WHERE @lusso_has_restaurant = 1
  AND @ala_sid IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM `categories`
    WHERE `restaurant_id` = @lusso_rid AND `slug` = 'vegetarian'
);
SET @cat_vegetarian = (
  SELECT `id` FROM `categories`
  WHERE `restaurant_id` = @lusso_rid AND `slug` = 'vegetarian'
  LIMIT 1
);

-- Burgers
SET @cat_burgers = NULL;
INSERT INTO `categories` (`restaurant_id`, `section_id`, `name`, `slug`, `description`, `display_order`, `is_active`, `created_at`, `updated_at`)
SELECT @lusso_rid, @ala_sid, 'Burgers', 'burgers', NULL, 3, 1, NOW(), NOW()
WHERE @lusso_has_restaurant = 1
  AND @ala_sid IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM `categories`
    WHERE `restaurant_id` = @lusso_rid AND `slug` = 'burgers'
);
SET @cat_burgers = (
  SELECT `id` FROM `categories`
  WHERE `restaurant_id` = @lusso_rid AND `slug` = 'burgers'
  LIMIT 1
);

-- Pasta Dishes
SET @cat_pasta = NULL;
INSERT INTO `categories` (`restaurant_id`, `section_id`, `name`, `slug`, `description`, `display_order`, `is_active`, `created_at`, `updated_at`)
SELECT @lusso_rid, @ala_sid, 'Pasta Dishes', 'pasta-dishes', NULL, 4, 1, NOW(), NOW()
WHERE @lusso_has_restaurant = 1
  AND @ala_sid IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM `categories`
    WHERE `restaurant_id` = @lusso_rid AND `slug` = 'pasta-dishes'
);
SET @cat_pasta = (
  SELECT `id` FROM `categories`
  WHERE `restaurant_id` = @lusso_rid AND `slug` = 'pasta-dishes'
  LIMIT 1
);

-- Medium Crust Pizzas
SET @cat_pizzas = NULL;
INSERT INTO `categories` (`restaurant_id`, `section_id`, `name`, `slug`, `description`, `display_order`, `is_active`, `created_at`, `updated_at`)
SELECT @lusso_rid, @ala_sid, 'Medium Crust Pizzas', 'medium-crust-pizzas', NULL, 5, 1, NOW(), NOW()
WHERE @lusso_has_restaurant = 1
  AND @ala_sid IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM `categories`
    WHERE `restaurant_id` = @lusso_rid AND `slug` = 'medium-crust-pizzas'
);
SET @cat_pizzas = (
  SELECT `id` FROM `categories`
  WHERE `restaurant_id` = @lusso_rid AND `slug` = 'medium-crust-pizzas'
  LIMIT 1
);

-- Main Courses
SET @cat_main_courses = NULL;
INSERT INTO `categories` (`restaurant_id`, `section_id`, `name`, `slug`, `description`, `display_order`, `is_active`, `created_at`, `updated_at`)
SELECT @lusso_rid, @ala_sid, 'Main Courses', 'main-courses', NULL, 6, 1, NOW(), NOW()
WHERE @lusso_has_restaurant = 1
  AND @ala_sid IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM `categories`
    WHERE `restaurant_id` = @lusso_rid AND `slug` = 'main-courses'
);
SET @cat_main_courses = (
  SELECT `id` FROM `categories`
  WHERE `restaurant_id` = @lusso_rid AND `slug` = 'main-courses'
  LIMIT 1
);

-- Poultry
SET @cat_poultry = NULL;
INSERT INTO `categories` (`restaurant_id`, `section_id`, `name`, `slug`, `description`, `display_order`, `is_active`, `created_at`, `updated_at`)
SELECT @lusso_rid, @ala_sid, 'Poultry', 'poultry', NULL, 7, 1, NOW(), NOW()
WHERE @lusso_has_restaurant = 1
  AND @ala_sid IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM `categories`
    WHERE `restaurant_id` = @lusso_rid AND `slug` = 'poultry'
);
SET @cat_poultry = (
  SELECT `id` FROM `categories`
  WHERE `restaurant_id` = @lusso_rid AND `slug` = 'poultry'
  LIMIT 1
);

-- Seafood & Fish
SET @cat_seafood = NULL;
INSERT INTO `categories` (`restaurant_id`, `section_id`, `name`, `slug`, `description`, `display_order`, `is_active`, `created_at`, `updated_at`)
SELECT @lusso_rid, @ala_sid, 'Seafood & Fish', 'seafood-fish', NULL, 8, 1, NOW(), NOW()
WHERE @lusso_has_restaurant = 1
  AND @ala_sid IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM `categories`
    WHERE `restaurant_id` = @lusso_rid AND `slug` = 'seafood-fish'
);
SET @cat_seafood = (
  SELECT `id` FROM `categories`
  WHERE `restaurant_id` = @lusso_rid AND `slug` = 'seafood-fish'
  LIMIT 1
);

-- Desserts
SET @cat_desserts = NULL;
INSERT INTO `categories` (`restaurant_id`, `section_id`, `name`, `slug`, `description`, `display_order`, `is_active`, `created_at`, `updated_at`)
SELECT @lusso_rid, @ala_sid, 'Desserts', 'desserts-a-la-carte', NULL, 9, 1, NOW(), NOW()
WHERE @lusso_has_restaurant = 1
  AND @ala_sid IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM `categories`
    WHERE `restaurant_id` = @lusso_rid AND `slug` = 'desserts-a-la-carte'
);
SET @cat_desserts = (
  SELECT `id` FROM `categories`
  WHERE `restaurant_id` = @lusso_rid AND `slug` = 'desserts-a-la-carte'
  LIMIT 1
);

-- 40.4 Insert menu items (names, descriptions, prices)

-- Organic Salads & Appetizers
INSERT INTO `menu_items` (`restaurant_id`, `category_id`, `name`, `slug`, `description`, `price`, `image`, `display_order`, `is_available`, `created_at`, `updated_at`)
SELECT @lusso_rid, @cat_organic_salads, 'Greek Village Salad 🥛🌰', 'greek-village-salad',
       'Rocket leaves, grilled peppers, marinated olives and feta cheese finished with olive oil and balsamic reduction.',
       15000.00, NULL, 1, 1, NOW(), NOW()
WHERE @lusso_has_restaurant = 1
  AND @cat_organic_salads IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM `menu_items`
    WHERE `restaurant_id` = @lusso_rid AND `category_id` = @cat_organic_salads AND `slug` = 'greek-village-salad'
);

INSERT INTO `menu_items` (`restaurant_id`, `category_id`, `name`, `slug`, `description`, `price`, `image`, `display_order`, `is_available`, `created_at`, `updated_at`)
SELECT @lusso_rid, @cat_organic_salads, 'Asian Prawn Salad 🦐🌰🌶️', 'asian-prawn-salad',
       'Lemon and garlic marinated prawns served on crisp Asian slaw with green apple slices, toasted sesame seeds and sweet chili dressing.',
       25000.00, NULL, 2, 1, NOW(), NOW()
WHERE @lusso_has_restaurant = 1
  AND @cat_organic_salads IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM `menu_items`
    WHERE `restaurant_id` = @lusso_rid AND `category_id` = @cat_organic_salads AND `slug` = 'asian-prawn-salad'
);

INSERT INTO `menu_items` (`restaurant_id`, `category_id`, `name`, `slug`, `description`, `price`, `image`, `display_order`, `is_available`, `created_at`, `updated_at`)
SELECT @lusso_rid, @cat_organic_salads, 'Local Papaya Salad 🥛', 'local-papaya-salad',
       'Lettuce, pawpaw, tomato, watermelon, pineapple and feta cheese with lime ranch dressing.',
       17000.00, NULL, 3, 1, NOW(), NOW()
WHERE @lusso_has_restaurant = 1
  AND @cat_organic_salads IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM `menu_items`
    WHERE `restaurant_id` = @lusso_rid AND `category_id` = @cat_organic_salads AND `slug` = 'local-papaya-salad'
);

-- Vegetarian
INSERT INTO `menu_items` (`restaurant_id`, `category_id`, `name`, `slug`, `description`, `price`, `image`, `display_order`, `is_available`, `created_at`, `updated_at`)
SELECT @lusso_rid, @cat_vegetarian, 'Spiced Halloumi Wrap 🌾🥛', 'spiced-halloumi-wrap',
       'Grilled halloumi cheese wrapped with lettuce in tortilla bread.',
       15000.00, NULL, 1, 1, NOW(), NOW()
WHERE @lusso_has_restaurant = 1
  AND @cat_vegetarian IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM `menu_items`
    WHERE `restaurant_id` = @lusso_rid AND `category_id` = @cat_vegetarian AND `slug` = 'spiced-halloumi-wrap'
);

-- Burgers
INSERT INTO `menu_items` (`restaurant_id`, `category_id`, `name`, `slug`, `description`, `price`, `image`, `display_order`, `is_available`, `created_at`, `updated_at`)
SELECT @lusso_rid, @cat_burgers, 'The Giant Burger 🌾🥛🥚', 'the-giant-burger',
       'Signature beef patty with back bacon, lettuce, tomato, coated onions, gherkins, mustard mayo and gratinated mozzarella in a sesame bun.',
       25000.00, NULL, 1, 1, NOW(), NOW()
WHERE @lusso_has_restaurant = 1
  AND @cat_burgers IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM `menu_items`
    WHERE `restaurant_id` = @lusso_rid AND `category_id` = @cat_burgers AND `slug` = 'the-giant-burger'
);

-- Pasta Dishes
INSERT INTO `menu_items` (`restaurant_id`, `category_id`, `name`, `slug`, `description`, `price`, `image`, `display_order`, `is_available`, `created_at`, `updated_at`)
SELECT @lusso_rid, @cat_pasta, 'Aglio Olio Prawn Pasta 🦐🌾🍷🥛', 'aglio-olio-prawn-pasta',
       'Pasta tossed in garlic and herb infusion with capsicum and white wine, finished with prawns and seafood, served with gratinated capsicum and cheese bruschetta.',
       22000.00, NULL, 1, 1, NOW(), NOW()
WHERE @lusso_has_restaurant = 1
  AND @cat_pasta IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM `menu_items`
    WHERE `restaurant_id` = @lusso_rid AND `category_id` = @cat_pasta AND `slug` = 'aglio-olio-prawn-pasta'
);

INSERT INTO `menu_items` (`restaurant_id`, `category_id`, `name`, `slug`, `description`, `price`, `image`, `display_order`, `is_available`, `created_at`, `updated_at`)
SELECT @lusso_rid, @cat_pasta, 'Turkey Ham Carbonara 🌾🥛🥚', 'turkey-ham-carbonara',
       'Turkey ham in rich creamy carbonara sauce with egg yolk and freshly grated parmesan, served with gratinated capsicum and cheese bruschetta.',
       25000.00, NULL, 2, 1, NOW(), NOW()
WHERE @lusso_has_restaurant = 1
  AND @cat_pasta IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM `menu_items`
    WHERE `restaurant_id` = @lusso_rid AND `category_id` = @cat_pasta AND `slug` = 'turkey-ham-carbonara'
);

-- Medium Crust Pizzas
INSERT INTO `menu_items` (`restaurant_id`, `category_id`, `name`, `slug`, `description`, `price`, `image`, `display_order`, `is_available`, `created_at`, `updated_at`)
SELECT @lusso_rid, @cat_pizzas, 'Caprese Margherita (V) 🌾🥛', 'caprese-margherita',
       'Tomato basil sauce, mozzarella, plum tomatoes and olive oil.',
       16000.00, NULL, 1, 1, NOW(), NOW()
WHERE @lusso_has_restaurant = 1
  AND @cat_pizzas IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM `menu_items`
    WHERE `restaurant_id` = @lusso_rid AND `category_id` = @cat_pizzas AND `slug` = 'caprese-margherita'
);

INSERT INTO `menu_items` (`restaurant_id`, `category_id`, `name`, `slug`, `description`, `price`, `image`, `display_order`, `is_available`, `created_at`, `updated_at`)
SELECT @lusso_rid, @cat_pizzas, 'Seafood Alforno 🦐🐟🌾🥛', 'seafood-alforno',
       'Shrimps, calamari, octopus, basil, peppers and mozzarella.',
       25000.00, NULL, 2, 1, NOW(), NOW()
WHERE @lusso_has_restaurant = 1
  AND @cat_pizzas IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM `menu_items`
    WHERE `restaurant_id` = @lusso_rid AND `category_id` = @cat_pizzas AND `slug` = 'seafood-alforno'
);

INSERT INTO `menu_items` (`restaurant_id`, `category_id`, `name`, `slug`, `description`, `price`, `image`, `display_order`, `is_available`, `created_at`, `updated_at`)
SELECT @lusso_rid, @cat_pizzas, 'Dodo & Chicken Pizza 🌾🥛', 'dodo-chicken-pizza',
       'Plantain with grilled chicken, peppers, basil and mozzarella.',
       22000.00, NULL, 3, 1, NOW(), NOW()
WHERE @lusso_has_restaurant = 1
  AND @cat_pizzas IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM `menu_items`
    WHERE `restaurant_id` = @lusso_rid AND `category_id` = @cat_pizzas AND `slug` = 'dodo-chicken-pizza'
);

-- Main Courses
INSERT INTO `menu_items` (`restaurant_id`, `category_id`, `name`, `slug`, `description`, `price`, `image`, `display_order`, `is_available`, `created_at`, `updated_at`)
SELECT @lusso_rid, @cat_main_courses, 'Herb-Crusted Rack of Lamb 🥛', 'herb-crusted-rack-of-lamb',
       'Oven roasted rack of lamb with mint jelly crust, char-grilled ratatouille, creamy cheddar mashed potatoes and garlic herb jus.',
       49000.00, NULL, 1, 1, NOW(), NOW()
WHERE @lusso_has_restaurant = 1
  AND @cat_main_courses IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM `menu_items`
    WHERE `restaurant_id` = @lusso_rid AND `category_id` = @cat_main_courses AND `slug` = 'herb-crusted-rack-of-lamb'
);

INSERT INTO `menu_items` (`restaurant_id`, `category_id`, `name`, `slug`, `description`, `price`, `image`, `display_order`, `is_available`, `created_at`, `updated_at`)
SELECT @lusso_rid, @cat_main_courses, 'Crown of Beef 🌾🥛🍷', 'crown-of-beef',
       'Flame grilled beef fillet medallion wrapped in puff pastry, served with baby vegetables, crispy pommes allumettes, gratinated fondant potatoes and cream onion truffle wine sauce.',
       35000.00, NULL, 2, 1, NOW(), NOW()
WHERE @lusso_has_restaurant = 1
  AND @cat_main_courses IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM `menu_items`
    WHERE `restaurant_id` = @lusso_rid AND `category_id` = @cat_main_courses AND `slug` = 'crown-of-beef'
);

-- Poultry
INSERT INTO `menu_items` (`restaurant_id`, `category_id`, `name`, `slug`, `description`, `price`, `image`, `display_order`, `is_available`, `created_at`, `updated_at`)
SELECT @lusso_rid, @cat_poultry, 'Semi-Bone-Out Half Chicken 🌾🌶️', 'semi-bone-out-half-chicken',
       'Grilled and oven finished half chicken seasoned with Peri-Peri or Lemon & Herb, served with crispy potato wedges, micro leaf salad, lentil onion bread stuffing and sauce of choice.',
       20000.00, NULL, 1, 1, NOW(), NOW()
WHERE @lusso_has_restaurant = 1
  AND @cat_poultry IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM `menu_items`
    WHERE `restaurant_id` = @lusso_rid AND `category_id` = @cat_poultry AND `slug` = 'semi-bone-out-half-chicken'
);

INSERT INTO `menu_items` (`restaurant_id`, `category_id`, `name`, `slug`, `description`, `price`, `image`, `display_order`, `is_available`, `created_at`, `updated_at`)
SELECT @lusso_rid, @cat_poultry, 'Chicken Supreme 🥛', 'chicken-supreme',
       'Chicken fillet stuffed with spinach and feta cheese, served with seasonal vegetables and chef’s signature rice finished with creamy mushroom thyme sauce.',
       20000.00, NULL, 2, 1, NOW(), NOW()
WHERE @lusso_has_restaurant = 1
  AND @cat_poultry IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM `menu_items`
    WHERE `restaurant_id` = @lusso_rid AND `category_id` = @cat_poultry AND `slug` = 'chicken-supreme'
);

-- Seafood & Fish
INSERT INTO `menu_items` (`restaurant_id`, `category_id`, `name`, `slug`, `description`, `price`, `image`, `display_order`, `is_available`, `created_at`, `updated_at`)
SELECT @lusso_rid, @cat_seafood, 'Parmesan Mussel & Herb–Encrusted Croaker 🐟🦐🥛', 'parmesan-mussel-herb-encrusted-croaker',
       'Grilled croaker encrusted with parmesan, herbs and mussels, served with buttered baby vegetables and rustic potatoes finished with fish velouté.',
       35000.00, NULL, 1, 1, NOW(), NOW()
WHERE @lusso_has_restaurant = 1
  AND @cat_seafood IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM `menu_items`
    WHERE `restaurant_id` = @lusso_rid AND `category_id` = @cat_seafood AND `slug` = 'parmesan-mussel-herb-encrusted-croaker'
);

INSERT INTO `menu_items` (`restaurant_id`, `category_id`, `name`, `slug`, `description`, `price`, `image`, `display_order`, `is_available`, `created_at`, `updated_at`)
SELECT @lusso_rid, @cat_seafood, 'Teriyaki Salmon 🐟🌰🍷', 'teriyaki-salmon',
       'Grilled salmon glazed with soy, honey, chili, sesame oil, garlic and pickled ginger, served with julienne vegetables and wok egg noodles.',
       49000.00, NULL, 2, 1, NOW(), NOW()
WHERE @lusso_has_restaurant = 1
  AND @cat_seafood IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM `menu_items`
    WHERE `restaurant_id` = @lusso_rid AND `category_id` = @cat_seafood AND `slug` = 'teriyaki-salmon'
);

-- Desserts
INSERT INTO `menu_items` (`restaurant_id`, `category_id`, `name`, `slug`, `description`, `price`, `image`, `display_order`, `is_available`, `created_at`, `updated_at`)
SELECT @lusso_rid, @cat_desserts, 'Crème Brûlée with Chocolate Chip Biscuit 🥛🥚🌾', 'creme-brulee-chocolate-chip-biscuit',
       'Light baked custard with caramelized crust served with berry coulis and chocolate chip biscuit.',
       10000.00, NULL, 1, 1, NOW(), NOW()
WHERE @lusso_has_restaurant = 1
  AND @cat_desserts IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM `menu_items`
    WHERE `restaurant_id` = @lusso_rid AND `category_id` = @cat_desserts AND `slug` = 'creme-brulee-chocolate-chip-biscuit'
);

INSERT INTO `menu_items` (`restaurant_id`, `category_id`, `name`, `slug`, `description`, `price`, `image`, `display_order`, `is_available`, `created_at`, `updated_at`)
SELECT @lusso_rid, @cat_desserts, 'Ice Cream 🥛🥚', 'ice-cream',
       'Ask your waiter for today’s flavor, topped with chocolate sprinkles and butter biscuit.',
       15000.00, NULL, 2, 1, NOW(), NOW()
WHERE @lusso_has_restaurant = 1
  AND @cat_desserts IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM `menu_items`
    WHERE `restaurant_id` = @lusso_rid AND `category_id` = @cat_desserts AND `slug` = 'ice-cream'
);

INSERT INTO `menu_items` (`restaurant_id`, `category_id`, `name`, `slug`, `description`, `price`, `image`, `display_order`, `is_available`, `created_at`, `updated_at`)
SELECT @lusso_rid, @cat_desserts, 'Freshly Cut Fruit Salad', 'freshly-cut-fruit-salad',
       'Seasonal fresh fruit medley.',
       7000.00, NULL, 3, 1, NOW(), NOW()
WHERE @lusso_has_restaurant = 1
  AND @cat_desserts IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM `menu_items`
    WHERE `restaurant_id` = @lusso_rid AND `category_id` = @cat_desserts AND `slug` = 'freshly-cut-fruit-salad'
);
