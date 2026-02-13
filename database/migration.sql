-- ============================================================
-- Resmenu Database Migration
-- ============================================================
-- Use for: Updating EXISTING database (run in phpMyAdmin or MySQL client)
-- Fresh installs: Use sigsolmenu_resmenu.sql (full schema)
--
-- Run this file against your database to add/update:
-- 1. orders table (food ordering: id, order_number, restaurant_id, customer_*, delivery_address, payment_method, status, totals, timestamps)
--    Status values: pending, confirmed, on_hold, cancelled, completed
-- 2. order_items table (order_id, menu_item_id, name, price, quantity)
-- 3. Template 4 (The Gourmet Grill) + template_customizations defaults
-- 4. restaurant_payment_settings (per-restaurant checkout options)
-- 5. payment_method, order_number columns on orders (if missing)
-- 6. pending_bank_transfers (draft orders before "I have made this payment")
-- 7. orders.status comment updated to include on_hold
-- 8. pending_online_payments (draft before Paystack/Flutterwave confirms)
-- 9. table_reservations (Template 4 table reservation system)
-- 10. restaurant_reservation_settings
-- 11-14. reservation payment support, deposit columns, payment_type/reservation_id on pending tables
-- 15. table_inventory_daily (daily table capacity) + is_walkin on table_reservations
-- 16. is_walkin on table_reservations
-- 17. reservation_number on table_reservations
-- 18. site_settings (site name, logo, favicon for super admin)
-- 19. customization_settings: add template_id for per-template color customization
--    (all color/size/font columns already exist; no schema change needed for full customization)
-- 20. restaurants: ensure social media link columns exist (WhatsApp, Instagram, Facebook, Twitter)
-- 21. Theview Hotel Lekki: optional menu data (18 categories, ~173 items for restaurant_id=3)
-- 22. Theview Hotel Lekki: food menu (15 categories, ~108 items for restaurant_id=3)
--     Skip or comment out section 21 if Theview menu is already loaded.
-- 23. Ensure Template 4 restaurants have reservation settings (default deposit 5000) for checkout redirect
--
-- Requires: MariaDB 10.0.2+ or MySQL 8.0.12+ for ADD COLUMN IF NOT EXISTS
-- Section 19 index statements: MariaDB 10.5.2+ (IF NOT EXISTS supported). For MySQL, use commented alternative.
-- ============================================================

-- 1. Orders table (food ordering system)
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_number` varchar(10) DEFAULT NULL,
  `restaurant_id` int(11) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_phone` varchar(50) NOT NULL,
  `customer_email` varchar(255) NOT NULL,
  `delivery_address` text NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'pending' COMMENT 'pending, confirmed, on_hold, cancelled, completed',
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `delivery_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tax` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `restaurant_id` (`restaurant_id`),
  KEY `status` (`status`),
  KEY `created_at` (`created_at`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Order items table
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `menu_item_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `menu_item_id` (`menu_item_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Add Template 4 (The Gourmet Grill) to templates table
INSERT INTO `templates` (`id`, `name`, `description`, `preview_image`, `is_active`, `created_at`, `updated_at`)
VALUES (4, 'The Gourmet Grill', 'Premium dark-themed design with Epilogue font, herb pattern, and flame-grilled aesthetic', NULL, 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `description` = VALUES(`description`), `is_active` = 1, `updated_at` = NOW();

-- 4. Add template customization defaults for all templates (so manager shows template colors, not generic black/white)
INSERT INTO `template_customizations` (`template_id`, `menu_title_color`, `menu_title_size`, `menu_title_font`, `price_color`, `price_size`, `price_font`, `description_color`, `description_size`, `description_font`, `category_title_color`, `category_title_size`, `category_title_font`, `background_color`, `header_background_color`, `primary_color`, `secondary_color`, `created_at`, `updated_at`)
VALUES (1, '#1A1A1A', 24, 'Inter', '#1A1A1A', 18, 'Inter', '#666666', 14, 'Inter', '#1A1A1A', 20, 'Inter', '#FFFFFF', '#FFFFFF', '#1A1A1A', '#FAF3E6', NOW(), NOW()),
       (2, '#1A1A1A', 24, 'Inter', '#ea2a33', 18, 'Inter', '#666666', 14, 'Inter', '#1A1A1A', 20, 'Inter', '#f8f6f6', '#f8f6f6', '#ea2a33', '#FFFFFF', NOW(), NOW()),
       (3, '#1A1A1A', 24, 'Inter', '#ea2a33', 18, 'Inter', '#666666', 14, 'Inter', '#1A1A1A', 20, 'Inter', '#f8f6f6', '#f8f6f6', '#ea2a33', '#FFFFFF', NOW(), NOW()),
       (4, '#121212', 24, 'Epilogue', '#f20d0d', 18, 'Epilogue', '#666666', 14, 'Epilogue', '#121212', 20, 'Epilogue', '#f8f5f5', '#121212', '#f20d0d', '#FFFFFF', NOW(), NOW())
ON DUPLICATE KEY UPDATE `menu_title_color` = VALUES(`menu_title_color`), `price_color` = VALUES(`price_color`), `primary_color` = VALUES(`primary_color`), `background_color` = VALUES(`background_color`), `updated_at` = NOW();

-- 5. Restaurant payment settings (per-restaurant checkout payment options)
CREATE TABLE IF NOT EXISTS `restaurant_payment_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `restaurant_id` int(11) NOT NULL,
  `gateway` varchar(50) NOT NULL,
  `is_active` tinyint(1) DEFAULT 0,
  `test_mode` tinyint(1) DEFAULT 1,
  `public_key_test` varchar(255) DEFAULT NULL,
  `secret_key_test` text DEFAULT NULL,
  `webhook_secret_test` varchar(255) DEFAULT NULL,
  `public_key_live` varchar(255) DEFAULT NULL,
  `secret_key_live` text DEFAULT NULL,
  `webhook_secret_live` varchar(255) DEFAULT NULL,
  `bank_name` varchar(255) DEFAULT NULL,
  `account_number` varchar(100) DEFAULT NULL,
  `account_name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `restaurant_id_gateway` (`restaurant_id`, `gateway`),
  CONSTRAINT `restaurant_payment_settings_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Add payment_method column to orders (IF NOT EXISTS skips if column already present - MariaDB 10.0.2+)
ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `payment_method` varchar(50) DEFAULT NULL AFTER `delivery_address`;

-- 7. Add order_number column (8-char alphanumeric unique display number)
ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `order_number` varchar(10) DEFAULT NULL AFTER `id`;

-- 8. Pending bank transfers (draft before user clicks "I have made this payment")
CREATE TABLE IF NOT EXISTS `pending_bank_transfers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(64) NOT NULL,
  `restaurant_id` int(11) NOT NULL,
  `payment_type` varchar(20) NOT NULL DEFAULT 'order',
  `reservation_id` int(11) DEFAULT NULL,
  `cart_json` text NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_phone` varchar(50) NOT NULL,
  `customer_email` varchar(255) NOT NULL,
  `delivery_address` text NOT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `delivery_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tax` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `restaurant_id` (`restaurant_id`),
  KEY `created_at` (`created_at`),
  CONSTRAINT `pending_bank_transfers_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Ensure orders.status comment includes on_hold (app uses: pending, confirmed, on_hold, cancelled, completed)
ALTER TABLE `orders` MODIFY COLUMN `status` varchar(50) NOT NULL DEFAULT 'pending' COMMENT 'pending, confirmed, on_hold, cancelled, completed';

-- 10. Pending online payments (draft before Paystack/Flutterwave confirms)
CREATE TABLE IF NOT EXISTS `pending_online_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reference` varchar(80) NOT NULL,
  `restaurant_id` int(11) NOT NULL,
  `payment_type` varchar(20) NOT NULL DEFAULT 'order',
  `reservation_id` int(11) DEFAULT NULL,
  `gateway` varchar(50) NOT NULL,
  `cart_json` text NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_phone` varchar(50) NOT NULL,
  `customer_email` varchar(255) NOT NULL,
  `delivery_address` text NOT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `delivery_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tax` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `reference` (`reference`),
  KEY `restaurant_id` (`restaurant_id`),
  KEY `created_at` (`created_at`),
  CONSTRAINT `pending_online_payments_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Table reservations (Template 4 only)
CREATE TABLE IF NOT EXISTS `table_reservations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reservation_number` varchar(10) DEFAULT NULL,
  `restaurant_id` int(11) NOT NULL,
  `reservation_date` date NOT NULL,
  `reservation_time` time NOT NULL,
  `party_size` int(11) NOT NULL DEFAULT 1,
  `guest_name` varchar(255) NOT NULL,
  `guest_email` varchar(255) NOT NULL,
  `guest_phone` varchar(50) NOT NULL,
  `special_occasion` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'pending' COMMENT 'pending, confirmed, rejected, cancelled, completed',
  `is_walkin` tinyint(1) NOT NULL DEFAULT 0,
  `deposit_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `deposit_paid` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `restaurant_id` (`restaurant_id`),
  KEY `reservation_date` (`reservation_date`),
  KEY `status` (`status`),
  CONSTRAINT `table_reservations_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Restaurant reservation settings (deposit amount per restaurant)
CREATE TABLE IF NOT EXISTS `restaurant_reservation_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `restaurant_id` int(11) NOT NULL,
  `deposit_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `restaurant_id` (`restaurant_id`),
  CONSTRAINT `restaurant_reservation_settings_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. Add reservation payment support to pending tables
ALTER TABLE `pending_bank_transfers` ADD COLUMN IF NOT EXISTS `payment_type` varchar(20) NOT NULL DEFAULT 'order' AFTER `restaurant_id`;
ALTER TABLE `pending_bank_transfers` ADD COLUMN IF NOT EXISTS `reservation_id` int(11) DEFAULT NULL AFTER `payment_type`;
ALTER TABLE `pending_online_payments` ADD COLUMN IF NOT EXISTS `payment_type` varchar(20) NOT NULL DEFAULT 'order' AFTER `restaurant_id`;
ALTER TABLE `pending_online_payments` ADD COLUMN IF NOT EXISTS `reservation_id` int(11) DEFAULT NULL AFTER `payment_type`;

-- 14. Add deposit columns to existing table_reservations (if table already exists)
ALTER TABLE `table_reservations` ADD COLUMN IF NOT EXISTS `deposit_amount` decimal(10,2) NOT NULL DEFAULT 0.00 AFTER `notes`;
ALTER TABLE `table_reservations` ADD COLUMN IF NOT EXISTS `deposit_paid` tinyint(1) NOT NULL DEFAULT 0 AFTER `deposit_amount`;

-- 15. Table inventory (daily table capacity for reservations, Template 4)
CREATE TABLE IF NOT EXISTS `table_inventory_daily` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `restaurant_id` int(11) NOT NULL,
  `inventory_date` date NOT NULL,
  `total_tables` int(11) NOT NULL DEFAULT 10,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `restaurant_date` (`restaurant_id`, `inventory_date`),
  KEY `restaurant_id` (`restaurant_id`),
  KEY `inventory_date` (`inventory_date`),
  CONSTRAINT `table_inventory_daily_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16. Add is_walkin flag to table_reservations (for walk-in tracking)
ALTER TABLE `table_reservations` ADD COLUMN IF NOT EXISTS `is_walkin` tinyint(1) NOT NULL DEFAULT 0 AFTER `status`;

-- 17. Add reservation_number (8-char alphanumeric, same pattern as orders)
ALTER TABLE `table_reservations` ADD COLUMN IF NOT EXISTS `reservation_number` varchar(10) DEFAULT NULL AFTER `id`;

-- 18. Site settings (site name, logo, favicon for super admin)
CREATE TABLE IF NOT EXISTS `site_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `site_name` varchar(255) NOT NULL DEFAULT 'Resmenu',
  `site_logo` varchar(255) DEFAULT NULL,
  `favicon` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT IGNORE INTO `site_settings` (`id`, `site_name`) VALUES (1, 'Resmenu');

-- 19. Per-template customization (each restaurant can have different colors per template)
-- Adds template_id, all color/size/font columns already exist in customization_settings.
ALTER TABLE `customization_settings` ADD COLUMN IF NOT EXISTS `template_id` int(11) NOT NULL DEFAULT 1 AFTER `restaurant_id`;
-- Remove rows that would create duplicates: if restaurant has multiple rows and one already matches restaurant.template_id, delete the others (MySQL #1093 workaround: use derived table)
DELETE FROM `customization_settings` WHERE id IN (
  SELECT id FROM (
    SELECT cs1.id FROM `customization_settings` cs1
    INNER JOIN `restaurants` r ON r.id = cs1.restaurant_id
    INNER JOIN `customization_settings` cs2 ON cs2.restaurant_id = cs1.restaurant_id AND cs2.template_id = COALESCE(r.template_id, 1) AND cs2.id != cs1.id
    WHERE cs1.template_id != COALESCE(r.template_id, 1)
  ) AS t
);
-- Remove duplicate (restaurant_id, template_id) pairs, keeping the row with the lowest id
DELETE FROM `customization_settings` WHERE id IN (
  SELECT id FROM (
    SELECT cs1.id FROM `customization_settings` cs1
    INNER JOIN `customization_settings` cs2 ON cs1.restaurant_id = cs2.restaurant_id AND cs1.template_id = cs2.template_id AND cs1.id > cs2.id
  ) AS t
);
-- Now safe to update remaining rows to match restaurant's template_id
UPDATE `customization_settings` cs JOIN `restaurants` r ON r.id = cs.restaurant_id SET cs.template_id = COALESCE(r.template_id, 1);
-- Index changes: MariaDB supports IF NOT EXISTS; MySQL does not. Use one of the two blocks below.
-- MariaDB 10.5.2+ (recommended):
CREATE UNIQUE INDEX IF NOT EXISTS `restaurant_template` ON `customization_settings` (`restaurant_id`, `template_id`);
ALTER TABLE `customization_settings` DROP INDEX IF EXISTS `restaurant_id`;
-- MySQL 8.0 (if above fails): run these instead, skip if index/column already exists:
-- ALTER TABLE `customization_settings` ADD UNIQUE KEY `restaurant_template` (`restaurant_id`, `template_id`);
-- ALTER TABLE `customization_settings` DROP INDEX `restaurant_id`;

-- 20. Restaurants: ensure social media link columns exist (for manager profile + footer icons)
-- Run these if your restaurants table was created before these columns were added.
ALTER TABLE `restaurants` ADD COLUMN IF NOT EXISTS `whatsapp_link` varchar(255) DEFAULT NULL AFTER `website`;
ALTER TABLE `restaurants` ADD COLUMN IF NOT EXISTS `instagram_url` varchar(255) DEFAULT NULL AFTER `whatsapp_link`;
ALTER TABLE `restaurants` ADD COLUMN IF NOT EXISTS `facebook_url` varchar(255) DEFAULT NULL AFTER `instagram_url`;
ALTER TABLE `restaurants` ADD COLUMN IF NOT EXISTS `twitter_url` varchar(255) DEFAULT NULL AFTER `facebook_url`;

-- ============================================================
-- 21. Theview Hotel Lekki - Optional Menu Import (restaurant_id=3)
-- ============================================================
-- 18 categories, ~173 menu items. Run ONCE.
-- If categories already exist for restaurant 3, delete first:
--   DELETE FROM menu_items WHERE restaurant_id = 3;
--   DELETE FROM categories WHERE restaurant_id = 3;
-- Then run this section. Or comment out this entire section if not needed.
-- ============================================================

-- 21a. Insert categories (IDs 23-40 for restaurant_id=3)
INSERT IGNORE INTO `categories` (`id`, `restaurant_id`, `name`, `slug`, `image`, `description`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES
(23, 3, 'Soft Drinks & Non-Alcoholic', 'soft-drinks-non-alcoholic', NULL, 'Refreshing non-alcoholic beverages', 1, 1, NOW(), NOW()),
(24, 3, 'Beer & Cider', 'beer-cider', NULL, 'Local and imported beers and ciders', 2, 1, NOW(), NOW()),
(25, 3, 'Brandy & Cognac', 'brandy-cognac', NULL, 'Premium brandy and cognac selection', 3, 1, NOW(), NOW()),
(26, 3, 'Whiskey', 'whiskey', NULL, 'Fine whiskey collection', 4, 1, NOW(), NOW()),
(27, 3, 'Rum', 'rum', NULL, 'Rum selection', 5, 1, NOW(), NOW()),
(28, 3, 'Vodka', 'vodka', NULL, 'Vodka selection', 6, 1, NOW(), NOW()),
(29, 3, 'Gin', 'gin', NULL, 'Premium gin selection', 7, 1, NOW(), NOW()),
(30, 3, 'Tequila', 'tequila', NULL, 'Tequila selection', 8, 1, NOW(), NOW()),
(31, 3, 'Liqueurs', 'liqueurs', NULL, 'Sweet liqueurs and digestifs', 9, 1, NOW(), NOW()),
(32, 3, 'Aperitifs & Bitters', 'aperitifs-bitters', NULL, 'Aperitifs and bitters', 10, 1, NOW(), NOW()),
(33, 3, 'Champagne', 'champagne', NULL, 'Premium champagne selection', 11, 1, NOW(), NOW()),
(34, 3, 'Mocktails', 'mocktails', NULL, 'Alcohol-free cocktails', 12, 1, NOW(), NOW()),
(35, 3, 'Cocktails', 'cocktails', NULL, 'Classic and signature cocktails', 13, 1, NOW(), NOW()),
(36, 3, 'White Wines', 'white-wines', NULL, 'White wine selection', 14, 1, NOW(), NOW()),
(37, 3, 'Red Wines', 'red-wines', NULL, 'Red wine selection', 15, 1, NOW(), NOW()),
(38, 3, 'Coffee', 'coffee', NULL, 'Hot coffee drinks', 16, 1, NOW(), NOW()),
(39, 3, 'Smoothies', 'smoothies', NULL, 'Fresh fruit smoothies', 17, 1, NOW(), NOW()),
(40, 3, 'Fresh Juices', 'fresh-juices', NULL, 'Freshly squeezed juices', 18, 1, NOW(), NOW());

-- 21b. Insert menu items (INSERT IGNORE skips duplicates if already loaded)
INSERT IGNORE INTO `menu_items` (`restaurant_id`, `category_id`, `name`, `slug`, `description`, `price`, `image`, `display_order`, `is_available`, `created_at`, `updated_at`) VALUES
(3, 23, 'Cranberry Juice', 'cranberry-juice', '', 6000.00, NULL, 1, 1, NOW(), NOW()),
(3, 23, 'Juice Pack', 'juice-pack', '', 6000.00, NULL, 2, 1, NOW(), NOW()),
(3, 23, 'Malt Drink', 'malt-drink', '', 1500.00, NULL, 3, 1, NOW(), NOW()),
(3, 23, 'Energy Drink', 'energy-drink', '', 5000.00, NULL, 4, 1, NOW(), NOW()),
(3, 23, 'Water (Small)', 'water-small', '', 1000.00, NULL, 5, 1, NOW(), NOW()),
(3, 23, 'Soft Drinks (Coke, Fanta, Sprite, etc.)', 'soft-drinks', 'Coke, Fanta, Sprite and more', 1000.00, NULL, 6, 1, NOW(), NOW()),
(3, 23, 'Red Bull / Power Horse', 'red-bull-power-horse', '', 5000.00, NULL, 7, 1, NOW(), NOW()),
(3, 24, '33 Lager', '33-lager', '', 3000.00, NULL, 1, 1, NOW(), NOW()),
(3, 24, 'Smirnoff Ice', 'smirnoff-ice', '', 3000.00, NULL, 2, 1, NOW(), NOW()),
(3, 24, 'Star Draft (Big)', 'star-draft-big', '', 2000.00, NULL, 3, 1, NOW(), NOW()),
(3, 24, 'Star Draft (Small)', 'star-draft-small', '', 1000.00, NULL, 4, 1, NOW(), NOW()),
(3, 24, 'Star Radler', 'star-radler', '', 3000.00, NULL, 5, 1, NOW(), NOW()),
(3, 24, 'Budweiser (Big)', 'budweiser-big', '', 3500.00, NULL, 6, 1, NOW(), NOW()),
(3, 24, 'Heineken', 'heineken', '', 3500.00, NULL, 7, 1, NOW(), NOW()),
(3, 24, 'Heineken Draft (Big)', 'heineken-draft-big', '', 3500.00, NULL, 8, 1, NOW(), NOW()),
(3, 24, 'Heineken Draft (Small)', 'heineken-draft-small', '', 1500.00, NULL, 9, 1, NOW(), NOW()),
(3, 24, 'Flying Fish', 'flying-fish', '', 3000.00, NULL, 10, 1, NOW(), NOW()),
(3, 24, 'Desperados', 'desperados', '', 3000.00, NULL, 11, 1, NOW(), NOW()),
(3, 24, 'Guinness Stout (Big)', 'guinness-stout-big', '', 3500.00, NULL, 12, 1, NOW(), NOW()),
(3, 24, 'Guinness Stout (Small)', 'guinness-stout-small', '', 3000.00, NULL, 13, 1, NOW(), NOW()),
(3, 24, 'Guinness Extra Smooth', 'guinness-extra-smooth', '', 3000.00, NULL, 14, 1, NOW(), NOW()),
(3, 24, 'Gulder', 'gulder', '', 3000.00, NULL, 15, 1, NOW(), NOW()),
(3, 24, 'Star', 'star', '', 3000.00, NULL, 16, 1, NOW(), NOW()),
(3, 24, 'Trophy', 'trophy', '', 3000.00, NULL, 17, 1, NOW(), NOW()),
(3, 24, 'Goldberg', 'goldberg', '', 3000.00, NULL, 18, 1, NOW(), NOW()),
(3, 24, 'Harp', 'harp', '', 3000.00, NULL, 19, 1, NOW(), NOW()),
(3, 25, 'Rémy Martin XO', 'remy-martin-xo', 'Bottle', 230000.00, NULL, 1, 1, NOW(), NOW()),
(3, 25, 'Hennessy XO', 'hennessy-xo', 'Bottle', 575000.00, NULL, 2, 1, NOW(), NOW()),
(3, 25, 'Hennessy VSOP (Bottle)', 'hennessy-vsop-bottle', 'Bottle', 130000.00, NULL, 3, 1, NOW(), NOW()),
(3, 25, 'Hennessy VSOP (Shot)', 'hennessy-vsop-shot', 'Per shot', 9000.00, NULL, 4, 1, NOW(), NOW()),
(3, 25, 'Rémy Martin VSOP (Bottle)', 'remy-martin-vsop-bottle', 'Bottle', 90000.00, NULL, 5, 1, NOW(), NOW()),
(3, 25, 'Rémy Martin VSOP (Shot)', 'remy-martin-vsop-shot', 'Per shot', 6500.00, NULL, 6, 1, NOW(), NOW()),
(3, 25, 'Martell Blue Swift (Bottle)', 'martell-blue-swift-bottle', 'Bottle', 80000.00, NULL, 7, 1, NOW(), NOW()),
(3, 25, 'Martell Blue Swift (Shot)', 'martell-blue-swift-shot', 'Per shot', 6000.00, NULL, 8, 1, NOW(), NOW()),
(3, 25, 'Hennessy VS (Bottle)', 'hennessy-vs-bottle', 'Bottle', 80000.00, NULL, 9, 1, NOW(), NOW()),
(3, 25, 'Hennessy VS (Shot)', 'hennessy-vs-shot', 'Per shot', 7000.00, NULL, 10, 1, NOW(), NOW()),
(3, 26, 'Glenfiddich 18 Years', 'glenfiddich-18-years', 'Bottle', 180000.00, NULL, 1, 1, NOW(), NOW()),
(3, 26, 'Glenfiddich 15 Years (Bottle)', 'glenfiddich-15-years-bottle', 'Bottle', 120000.00, NULL, 2, 1, NOW(), NOW()),
(3, 26, 'Glenfiddich 15 Years (Shot)', 'glenfiddich-15-years-shot', 'Per shot', 8000.00, NULL, 3, 1, NOW(), NOW()),
(3, 26, 'Glenfiddich 12 Years (Bottle)', 'glenfiddich-12-years-bottle', 'Bottle', 90000.00, NULL, 4, 1, NOW(), NOW()),
(3, 26, 'Glenfiddich 12 Years (Shot)', 'glenfiddich-12-years-shot', 'Per shot', 5000.00, NULL, 5, 1, NOW(), NOW()),
(3, 26, 'Jameson Black Barrel (Bottle)', 'jameson-black-barrel-bottle', 'Bottle', 55000.00, NULL, 6, 1, NOW(), NOW()),
(3, 26, 'Jameson Black Barrel (Shot)', 'jameson-black-barrel-shot', 'Per shot', 3000.00, NULL, 7, 1, NOW(), NOW()),
(3, 26, 'Jameson (Big Bottle)', 'jameson-big-bottle', 'Bottle', 47000.00, NULL, 8, 1, NOW(), NOW()),
(3, 26, 'Jameson (Shot)', 'jameson-shot', 'Per shot', 5000.00, NULL, 9, 1, NOW(), NOW()),
(3, 26, 'Jameson Miniature', 'jameson-miniature', '', 18500.00, NULL, 10, 1, NOW(), NOW()),
(3, 26, 'Johnnie Walker Black Label (Bottle)', 'johnnie-walker-black-label-bottle', 'Bottle', 45000.00, NULL, 11, 1, NOW(), NOW()),
(3, 26, 'Johnnie Walker Black Label (Shot)', 'johnnie-walker-black-label-shot', 'Per shot', 5000.00, NULL, 12, 1, NOW(), NOW()),
(3, 26, 'Johnnie Walker Red Label (Bottle)', 'johnnie-walker-red-label-bottle', 'Bottle', 27000.00, NULL, 13, 1, NOW(), NOW()),
(3, 26, 'Johnnie Walker Red Label (Shot)', 'johnnie-walker-red-label-shot', 'Per shot', 3000.00, NULL, 14, 1, NOW(), NOW()),
(3, 26, 'Johnnie Walker Blue Label', 'johnnie-walker-blue-label', 'Bottle', 90000.00, NULL, 15, 1, NOW(), NOW()),
(3, 26, 'Jack Daniel''s (Bottle)', 'jack-daniels-bottle', 'Bottle', 48000.00, NULL, 16, 1, NOW(), NOW()),
(3, 26, 'Jack Daniel''s (Shot)', 'jack-daniels-shot', 'Per shot', 4000.00, NULL, 17, 1, NOW(), NOW()),
(3, 26, 'Chivas Regal (Bottle)', 'chivas-regal-bottle', 'Bottle', 25000.00, NULL, 18, 1, NOW(), NOW()),
(3, 26, 'Chivas Regal (Shot)', 'chivas-regal-shot', 'Per shot', 2500.00, NULL, 19, 1, NOW(), NOW()),
(3, 27, 'Bacardi White (Bottle)', 'bacardi-white-bottle', 'Bottle', 35000.00, NULL, 1, 1, NOW(), NOW()),
(3, 27, 'Bacardi White (Shot)', 'bacardi-white-shot', 'Per shot', 3500.00, NULL, 2, 1, NOW(), NOW()),
(3, 27, 'Bacardi Gold (Bottle)', 'bacardi-gold-bottle', 'Bottle', 35000.00, NULL, 3, 1, NOW(), NOW()),
(3, 27, 'Bacardi Gold (Shot)', 'bacardi-gold-shot', 'Per shot', 2000.00, NULL, 4, 1, NOW(), NOW()),
(3, 27, 'Malibu (Bottle)', 'malibu-bottle', 'Bottle', 28000.00, NULL, 5, 1, NOW(), NOW()),
(3, 27, 'Malibu (Shot)', 'malibu-shot', 'Per shot', 4000.00, NULL, 6, 1, NOW(), NOW()),
(3, 28, 'Ciroc', 'ciroc', 'Bottle', 62000.00, NULL, 1, 1, NOW(), NOW()),
(3, 28, 'Absolut Vodka (Bottle)', 'absolut-vodka-bottle', 'Bottle', 35000.00, NULL, 2, 1, NOW(), NOW()),
(3, 28, 'Absolut Vodka (Shot)', 'absolut-vodka-shot', 'Per shot', 3000.00, NULL, 3, 1, NOW(), NOW()),
(3, 28, 'Smirnoff (Bottle)', 'smirnoff-bottle', 'Bottle', 22000.00, NULL, 4, 1, NOW(), NOW()),
(3, 28, 'Smirnoff (Shot)', 'smirnoff-shot', 'Per shot', 2500.00, NULL, 5, 1, NOW(), NOW()),
(3, 28, 'Grey Goose (Bottle)', 'grey-goose-bottle', 'Bottle', 45000.00, NULL, 6, 1, NOW(), NOW()),
(3, 28, 'Grey Goose (Shot)', 'grey-goose-shot', 'Per shot', 2500.00, NULL, 7, 1, NOW(), NOW()),
(3, 29, 'Gin Mare (Bottle)', 'gin-mare-bottle', 'Bottle', 40000.00, NULL, 1, 1, NOW(), NOW()),
(3, 29, 'Gin Mare (Shot)', 'gin-mare-shot', 'Per shot', 3000.00, NULL, 2, 1, NOW(), NOW()),
(3, 29, 'Hendrick''s (Bottle)', 'hendricks-bottle', 'Bottle', 73000.00, NULL, 3, 1, NOW(), NOW()),
(3, 29, 'Hendrick''s (Shot)', 'hendricks-shot', 'Per shot', 4000.00, NULL, 4, 1, NOW(), NOW()),
(3, 29, 'Hendrick''s Alt Bottle', 'hendricks-alt-bottle', 'Alternative bottle', 50000.00, NULL, 5, 1, NOW(), NOW()),
(3, 29, 'Hendrick''s Alt Bottle (Shot)', 'hendricks-alt-bottle-shot', 'Per shot', 2500.00, NULL, 6, 1, NOW(), NOW()),
(3, 29, 'Bombay Sapphire (Bottle)', 'bombay-sapphire-bottle', 'Bottle', 50000.00, NULL, 7, 1, NOW(), NOW()),
(3, 29, 'Bombay Sapphire (Shot)', 'bombay-sapphire-shot', 'Per shot', 4000.00, NULL, 8, 1, NOW(), NOW()),
(3, 30, 'Olmeca White (Bottle)', 'olmeca-white-bottle', 'Bottle', 45000.00, NULL, 1, 1, NOW(), NOW()),
(3, 30, 'Olmeca White (Shot)', 'olmeca-white-shot', 'Per shot', 4000.00, NULL, 2, 1, NOW(), NOW()),
(3, 31, 'Baileys (Bottle)', 'baileys-bottle', 'Bottle', 30000.00, NULL, 1, 1, NOW(), NOW()),
(3, 31, 'Baileys (Shot)', 'baileys-shot', 'Per shot', 2000.00, NULL, 2, 1, NOW(), NOW()),
(3, 31, 'Kahlua (Bottle)', 'kahlua-bottle', 'Bottle', 23000.00, NULL, 3, 1, NOW(), NOW()),
(3, 31, 'Kahlua (Shot)', 'kahlua-shot', 'Per shot', 2000.00, NULL, 4, 1, NOW(), NOW()),
(3, 31, 'Cointreau', 'cointreau', 'Per shot', 2000.00, NULL, 5, 1, NOW(), NOW()),
(3, 31, 'Triple Sec', 'triple-sec', 'Per shot', 2000.00, NULL, 6, 1, NOW(), NOW()),
(3, 32, 'Campari', 'campari', 'Bottle', 20000.00, NULL, 1, 1, NOW(), NOW()),
(3, 32, 'Origin Bitters (Big)', 'origin-bitters-big', 'Bottle', 9000.00, NULL, 2, 1, NOW(), NOW()),
(3, 32, 'Origin Bitters (Mini)', 'origin-bitters-mini', '', 2500.00, NULL, 3, 1, NOW(), NOW()),
(3, 32, 'Palm Spirit (Aphro / Moor Rum)', 'palm-spirit', 'Bottle', 25000.00, NULL, 4, 1, NOW(), NOW()),
(3, 33, 'Moët Nectar Rosé', 'moet-nectar-rose', 'Bottle', 176000.00, NULL, 1, 1, NOW(), NOW()),
(3, 33, 'Veuve Clicquot Brut', 'veuve-clicquot-brut', 'Bottle', 170000.00, NULL, 2, 1, NOW(), NOW()),
(3, 33, 'Moët Imperial Brut', 'moet-imperial-brut', 'Bottle', 130000.00, NULL, 3, 1, NOW(), NOW()),
(3, 34, 'Virgin Colada', 'virgin-colada', '', 4500.00, NULL, 1, 1, NOW(), NOW()),
(3, 34, 'Virgin Margarita', 'virgin-margarita', '', 5500.00, NULL, 2, 1, NOW(), NOW()),
(3, 34, 'Chapman', 'chapman', '', 8000.00, NULL, 3, 1, NOW(), NOW()),
(3, 35, 'Long Island Iced Tea', 'long-island-iced-tea', '', 7500.00, NULL, 1, 1, NOW(), NOW()),
(3, 35, 'Daiquiri', 'daiquiri', '', 6500.00, NULL, 2, 1, NOW(), NOW()),
(3, 35, 'Moscow Mule', 'moscow-mule', '', 6000.00, NULL, 3, 1, NOW(), NOW()),
(3, 35, 'Cosmopolitan', 'cosmopolitan', '', 6000.00, NULL, 4, 1, NOW(), NOW()),
(3, 35, 'Margarita', 'margarita', '', 5000.00, NULL, 5, 1, NOW(), NOW()),
(3, 35, 'Mojito', 'mojito', '', 7500.00, NULL, 6, 1, NOW(), NOW()),
(3, 35, 'Sex on the Beach', 'sex-on-the-beach', '', 5000.00, NULL, 7, 1, NOW(), NOW()),
(3, 35, 'Piña Colada', 'pina-colada', '', 6000.00, NULL, 8, 1, NOW(), NOW()),
(3, 35, 'Tequila Sunrise', 'tequila-sunrise', '', 4000.00, NULL, 9, 1, NOW(), NOW()),
(3, 35, 'Mai Tai', 'mai-tai', '', 6000.00, NULL, 10, 1, NOW(), NOW()),
(3, 35, 'Whiskey Sour', 'whiskey-sour', '', 6000.00, NULL, 11, 1, NOW(), NOW()),
(3, 35, 'Screaming Orgasm', 'screaming-orgasm', '', 8500.00, NULL, 12, 1, NOW(), NOW()),
(3, 35, 'The Boss', 'the-boss', '', 5000.00, NULL, 13, 1, NOW(), NOW()),
(3, 35, 'D''View Cocktail', 'dview-cocktail', 'Signature cocktail', 5000.00, NULL, 14, 1, NOW(), NOW()),
(3, 36, 'Nederburg Sauvignon Blanc', 'nederburg-sauvignon-blanc', 'Bottle', 36000.00, NULL, 1, 1, NOW(), NOW()),
(3, 36, 'Nederburg Late Harvest', 'nederburg-late-harvest', 'Bottle', 36000.00, NULL, 2, 1, NOW(), NOW()),
(3, 36, 'Nederburg Chardonnay', 'nederburg-chardonnay', 'Bottle', 36000.00, NULL, 3, 1, NOW(), NOW()),
(3, 36, 'Mapu Sauvignon Blanc', 'mapu-sauvignon-blanc', 'Bottle', 19000.00, NULL, 4, 1, NOW(), NOW()),
(3, 36, 'Four Cousins', 'four-cousins-white', 'Bottle', 19000.00, NULL, 5, 1, NOW(), NOW()),
(3, 36, 'Frontera Moscato', 'frontera-moscato', 'Bottle', 16000.00, NULL, 6, 1, NOW(), NOW()),
(3, 36, 'Viala Moscato', 'viala-moscato', 'Bottle', 12000.00, NULL, 7, 1, NOW(), NOW()),
(3, 37, 'Nederburg Merlot', 'nederburg-merlot', 'Bottle', 36000.00, NULL, 1, 1, NOW(), NOW()),
(3, 37, 'Nederburg Cabernet Sauvignon', 'nederburg-cabernet-sauvignon', 'Bottle', 36000.00, NULL, 2, 1, NOW(), NOW()),
(3, 37, 'Escudo Rojo', 'escudo-rojo', 'Bottle', 32000.00, NULL, 3, 1, NOW(), NOW()),
(3, 37, 'Mapu Cabernet Sauvignon', 'mapu-cabernet-sauvignon', 'Bottle', 19000.00, NULL, 4, 1, NOW(), NOW()),
(3, 37, 'Four Cousins', 'four-cousins-red', 'Bottle', 18000.00, NULL, 5, 1, NOW(), NOW()),
(3, 37, 'Carlo Rossi', 'carlo-rossi', 'Bottle', 12000.00, NULL, 6, 1, NOW(), NOW()),
(3, 37, 'Drostdy-Hof', 'drostdy-hof', 'Bottle', 12000.00, NULL, 7, 1, NOW(), NOW()),
(3, 37, '4th Street Red', '4th-street-red', 'Bottle', 12000.00, NULL, 8, 1, NOW(), NOW()),
(3, 37, 'Asara', 'asara', 'Bottle', 12000.00, NULL, 9, 1, NOW(), NOW()),
(3, 37, 'Bolzano', 'bolzano', 'Bottle', 12000.00, NULL, 10, 1, NOW(), NOW()),
(3, 37, 'Châteauneuf-du-Pape', 'chateauneuf-du-pape', 'Bottle', 20000.00, NULL, 11, 1, NOW(), NOW()),
(3, 38, 'Cappuccino', 'cappuccino', '', 2000.00, NULL, 1, 1, NOW(), NOW()),
(3, 38, 'Turkish Coffee', 'turkish-coffee', '', 2000.00, NULL, 2, 1, NOW(), NOW()),
(3, 38, 'Double Espresso', 'double-espresso', '', 1500.00, NULL, 3, 1, NOW(), NOW()),
(3, 38, 'Single Espresso', 'single-espresso', '', 1000.00, NULL, 4, 1, NOW(), NOW()),
(3, 39, 'Fruit Medley Smoothie', 'fruit-medley-smoothie', 'Seasonal mixed fruits', 2500.00, NULL, 1, 1, NOW(), NOW()),
(3, 40, 'Fresh Orange Juice', 'fresh-orange-juice', '', 4000.00, NULL, 1, 1, NOW(), NOW()),
(3, 40, 'Fresh Pineapple Juice', 'fresh-pineapple-juice', '', 4000.00, NULL, 2, 1, NOW(), NOW()),
(3, 40, 'Fresh Watermelon Juice', 'fresh-watermelon-juice', '', 4000.00, NULL, 3, 1, NOW(), NOW()),
(3, 40, 'Sweet Zobo Drink', 'sweet-zobo-drink', '', 2000.00, NULL, 4, 1, NOW(), NOW());

-- 21c. Update restaurant item counts
UPDATE `restaurants` SET `available_items_count` = (SELECT COUNT(*) FROM `menu_items` WHERE `restaurant_id` = 3 AND `is_available` = 1), `unavailable_items_count` = (SELECT COUNT(*) FROM `menu_items` WHERE `restaurant_id` = 3 AND `is_available` = 0) WHERE `id` = 3;

-- ============================================================
-- 22. Theview Hotel Lekki - Food Menu (restaurant_id=3)
-- ============================================================
-- 15 food categories, ~108 menu items. Run after section 21.
-- INSERT IGNORE skips duplicates if already loaded.
-- ============================================================

-- 22a. Insert food categories (IDs 41-55 for restaurant_id=3)
INSERT IGNORE INTO `categories` (`id`, `restaurant_id`, `name`, `slug`, `image`, `description`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES
(41, 3, 'Breakfast Trays (48-Hour Pre-Order)', 'breakfast-trays-48hr', NULL, 'Premium breakfast trays for pre-order', 19, 1, NOW(), NOW()),
(42, 3, 'Breakfast', 'breakfast', NULL, 'Morning meals', 20, 1, NOW(), NOW()),
(43, 3, 'Salads', 'salads', NULL, 'Fresh salads', 21, 1, NOW(), NOW()),
(44, 3, 'Pepper Soups & Continental Soups', 'pepper-soups-continental-soups', NULL, 'Served with fresh bread rolls', 22, 1, NOW(), NOW()),
(45, 3, 'Finger Foods & Small Chops', 'finger-foods-small-chops', NULL, 'Appetizers and small bites', 23, 1, NOW(), NOW()),
(46, 3, 'Sandwiches & Burgers', 'sandwiches-burgers', NULL, 'Sandwiches and burgers', 24, 1, NOW(), NOW()),
(47, 3, 'Chicken Entrées', 'chicken-entrees', NULL, 'Served with choice of fries, roast potatoes, sweet potato fries, or yam fries', 25, 1, NOW(), NOW()),
(48, 3, 'Seafood', 'seafood', NULL, 'Fresh seafood dishes', 26, 1, NOW(), NOW()),
(49, 3, 'Steaks, Ribs & Chops', 'steaks-ribs-chops', NULL, 'South African cuts — served with side of choice', 27, 1, NOW(), NOW()),
(50, 3, 'Grills', 'grills', NULL, 'Grilled specialties', 28, 1, NOW(), NOW()),
(51, 3, 'Platters', 'platters', NULL, 'Sharing platters', 29, 1, NOW(), NOW()),
(52, 3, 'Pasta', 'pasta', NULL, 'Pasta dishes', 30, 1, NOW(), NOW()),
(53, 3, 'Naija Soups', 'naija-soups', NULL, 'Served with semovita, eba, or pounded yam — protein choice included', 31, 1, NOW(), NOW()),
(54, 3, 'Naija Specialties', 'naija-specialties', NULL, 'Nigerian specialties', 32, 1, NOW(), NOW()),
(55, 3, 'Sides', 'sides', NULL, 'Side dishes', 33, 1, NOW(), NOW());

-- 22b. Insert food menu items
INSERT IGNORE INTO `menu_items` (`restaurant_id`, `category_id`, `name`, `slug`, `description`, `price`, `image`, `display_order`, `is_available`, `created_at`, `updated_at`) VALUES
(3, 41, 'Premium Tray', 'premium-tray', 'Miniature wine bottle, juice pack, lemonade bottle, biscuits, wafers, coconut flakes, yoghurt cups, almonds, mug with assorted hot beverages, fresh bread rolls with butter, jam & cheese, club sandwich, cakes & croissants, plantain skewers, grapes & kiwi, English breakfast with lamb sausage, French toast, pancakes', 60000.00, NULL, 1, 1, NOW(), NOW()),
(3, 41, 'Deluxe Tray', 'deluxe-tray', 'Mug with assorted hot beverages, fresh bread rolls with butter, jam & cheese, club sandwich, biscuit pack, juice pack, yoghurt cups, grapes, apples, English breakfast with lamb sausage', 60000.00, NULL, 2, 1, NOW(), NOW()),
(3, 42, 'Breakfast Burger', 'breakfast-burger', 'With tea or coffee', 10000.00, NULL, 1, 1, NOW(), NOW()),
(3, 42, 'Hungry Jack Breakfast', 'hungry-jack-breakfast', 'Bacon, sausages, egg, milk mix', 7500.00, NULL, 2, 1, NOW(), NOW()),
(3, 42, 'Classic English Breakfast', 'classic-english-breakfast', 'Sausages, bread, eggs, baked beans, butter, toast', 10000.00, NULL, 3, 1, NOW(), NOW()),
(3, 42, 'African Breakfast', 'african-breakfast', 'Boiled or fried yam or plantain, egg sauce', 10000.00, NULL, 4, 1, NOW(), NOW()),
(3, 42, 'Naija Special', 'naija-special', 'Indomie noodles, egg, vegetables', 8000.00, NULL, 5, 1, NOW(), NOW()),
(3, 43, 'Chef''s Salad', 'chefs-salad', 'Chicken breast, lettuce, cheese, croutons, bacon, tomatoes', 12000.00, NULL, 1, 1, NOW(), NOW()),
(3, 43, 'Chicken Caesar Salad', 'chicken-caesar-salad', 'Lettuce, chicken breast, cucumber, olives, tomatoes, egg', 15000.00, NULL, 2, 1, NOW(), NOW()),
(3, 43, 'Russian Salad', 'russian-salad', 'Chicken breast, carrot, Irish potatoes, sauce', 15000.00, NULL, 3, 1, NOW(), NOW()),
(3, 44, 'Fresh Croaker Fish (Whole)', 'fresh-croaker-fish-whole', 'Served with fresh bread rolls', 30000.00, NULL, 1, 1, NOW(), NOW()),
(3, 44, 'Catfish (Whole)', 'catfish-whole', 'Served with fresh bread rolls', 30000.00, NULL, 2, 1, NOW(), NOW()),
(3, 44, 'Fresh Croaker Fish (Portion)', 'fresh-croaker-fish-portion', 'Served with fresh bread rolls', 15000.00, NULL, 3, 1, NOW(), NOW()),
(3, 44, 'Catfish (Portion)', 'catfish-portion', 'Served with fresh bread rolls', 15000.00, NULL, 4, 1, NOW(), NOW()),
(3, 44, 'Goat Meat Pepper Soup', 'goat-meat-pepper-soup', 'Served with fresh bread rolls', 15000.00, NULL, 5, 1, NOW(), NOW()),
(3, 44, 'Chicken Pepper Soup', 'chicken-pepper-soup', 'Served with fresh bread rolls', 15000.00, NULL, 6, 1, NOW(), NOW()),
(3, 44, 'Chinese Noodle Soup (Shrimp & Chicken)', 'chinese-noodle-soup-shrimp-chicken', 'Served with fresh bread rolls', 15000.00, NULL, 7, 1, NOW(), NOW()),
(3, 44, 'Creamy Italian Seafood Soup', 'creamy-italian-seafood-soup', 'Served with fresh bread rolls', 15000.00, NULL, 8, 1, NOW(), NOW()),
(3, 44, 'Cream of Chicken Soup', 'cream-of-chicken-soup', 'Served with fresh bread rolls', 15000.00, NULL, 9, 1, NOW(), NOW()),
(3, 44, 'French Onion Soup', 'french-onion-soup', 'Served with fresh bread rolls', 10000.00, NULL, 10, 1, NOW(), NOW()),
(3, 44, 'Oxtail Soup', 'oxtail-soup', 'Served with fresh bread rolls', 10000.00, NULL, 11, 1, NOW(), NOW()),
(3, 45, 'Nick Nack Combo Board', 'nick-nack-combo-board', '', 10500.00, NULL, 1, 1, NOW(), NOW()),
(3, 45, 'Spicy Snails', 'spicy-snails', '', 15000.00, NULL, 2, 1, NOW(), NOW()),
(3, 45, 'Spicy Goat Dodo', 'spicy-goat-dodo', '', 15500.00, NULL, 3, 1, NOW(), NOW()),
(3, 45, 'Peppered Goat Meat', 'peppered-goat-meat', '', 15000.00, NULL, 4, 1, NOW(), NOW()),
(3, 45, 'Crusted Calamari', 'crusted-calamari', '', 15000.00, NULL, 5, 1, NOW(), NOW()),
(3, 45, 'Gizzdodo', 'gizzdodo', '', 15500.00, NULL, 6, 1, NOW(), NOW()),
(3, 45, 'Smokey Chicken Wings', 'smokey-chicken-wings', '', 15000.00, NULL, 7, 1, NOW(), NOW()),
(3, 45, 'Hot Chicken Wings', 'hot-chicken-wings', '', 15000.00, NULL, 8, 1, NOW(), NOW()),
(3, 45, 'Yaji Wings', 'yaji-wings', '', 15000.00, NULL, 9, 1, NOW(), NOW()),
(3, 45, 'Buffalo Wings', 'buffalo-wings', '', 15000.00, NULL, 10, 1, NOW(), NOW()),
(3, 45, 'Nkwobi', 'nkwobi', '', 15000.00, NULL, 11, 1, NOW(), NOW()),
(3, 45, 'Peppered Gizzard', 'peppered-gizzard', '', 15000.00, NULL, 12, 1, NOW(), NOW()),
(3, 45, 'Pepper Fish', 'pepper-fish', '', 14000.00, NULL, 13, 1, NOW(), NOW()),
(3, 45, 'Shrimp Rolls (4 pcs)', 'shrimp-rolls-4pcs', '', 15000.00, NULL, 14, 1, NOW(), NOW()),
(3, 45, 'Pepper Beef', 'pepper-beef', '', 15000.00, NULL, 15, 1, NOW(), NOW()),
(3, 45, 'Pepper Chicken', 'pepper-chicken', '', 14000.00, NULL, 16, 1, NOW(), NOW()),
(3, 45, 'Pepper Turkey', 'pepper-turkey', '', 15000.00, NULL, 17, 1, NOW(), NOW()),
(3, 45, 'Coleslaw', 'coleslaw', '', 4000.00, NULL, 18, 1, NOW(), NOW()),
(3, 46, 'GM''s Special Chicken Sandwich', 'gms-special-chicken-sandwich', '', 12500.00, NULL, 1, 1, NOW(), NOW()),
(3, 46, 'Classic Burger', 'classic-burger', '', 10000.00, NULL, 2, 1, NOW(), NOW()),
(3, 46, 'D''View Club Sandwich', 'dview-club-sandwich', '', 10000.00, NULL, 3, 1, NOW(), NOW()),
(3, 46, 'Classic Ham & Cheese', 'classic-ham-cheese', '', 7500.00, NULL, 4, 1, NOW(), NOW()),
(3, 46, 'Chunky Tuna Sandwich', 'chunky-tuna-sandwich', '', 6500.00, NULL, 5, 1, NOW(), NOW()),
(3, 47, 'Southern Fried Chicken on Mash', 'southern-fried-chicken-on-mash', 'Served with choice of fries, roast potatoes, sweet potato fries, or yam fries', 16000.00, NULL, 1, 1, NOW(), NOW()),
(3, 47, 'Chicken Escalope', 'chicken-escalope', 'Served with choice of fries, roast potatoes, sweet potato fries, or yam fries', 15000.00, NULL, 2, 1, NOW(), NOW()),
(3, 47, 'D''View Curry Chicken', 'dview-curry-chicken', 'Served with choice of fries, roast potatoes, sweet potato fries, or yam fries', 15000.00, NULL, 3, 1, NOW(), NOW()),
(3, 47, 'Chicken in Cream Sauce', 'chicken-in-cream-sauce', 'Served with choice of fries, roast potatoes, sweet potato fries, or yam fries', 15000.00, NULL, 4, 1, NOW(), NOW()),
(3, 47, 'Creamy Mustard Chicken', 'creamy-mustard-chicken', 'Served with choice of fries, roast potatoes, sweet potato fries, or yam fries', 15000.00, NULL, 5, 1, NOW(), NOW()),
(3, 47, 'Creamy Spinach Chicken Roll', 'creamy-spinach-chicken-roll', 'Served with choice of fries, roast potatoes, sweet potato fries, or yam fries', 9000.00, NULL, 6, 1, NOW(), NOW()),
(3, 47, 'Pepper Chicken', 'pepper-chicken-entree', 'Served with choice of fries, roast potatoes, sweet potato fries, or yam fries', 15000.00, NULL, 7, 1, NOW(), NOW()),
(3, 47, 'Oven Roast Chicken', 'oven-roast-chicken', 'Served with choice of fries, roast potatoes, sweet potato fries, or yam fries', 15500.00, NULL, 8, 1, NOW(), NOW()),
(3, 48, 'Grilled Salmon', 'grilled-salmon', '', 25000.00, NULL, 1, 1, NOW(), NOW()),
(3, 48, 'Grilled Croaker Fish', 'grilled-croaker-fish', '', 30000.00, NULL, 2, 1, NOW(), NOW()),
(3, 48, 'Grilled Catfish', 'grilled-catfish', '', 30000.00, NULL, 3, 1, NOW(), NOW()),
(3, 48, 'Grilled Jumbo Prawns', 'grilled-jumbo-prawns', '', 17000.00, NULL, 4, 1, NOW(), NOW()),
(3, 48, 'Butterfly Prawns', 'butterfly-prawns', '', 13000.00, NULL, 5, 1, NOW(), NOW()),
(3, 48, 'Lobster Thermidor', 'lobster-thermidor', '', 28500.00, NULL, 6, 1, NOW(), NOW()),
(3, 48, 'Golden Tilapia', 'golden-tilapia', '', 15000.00, NULL, 7, 1, NOW(), NOW()),
(3, 49, 'T-Bone', 't-bone', 'South African cuts — served with side of choice', 28000.00, NULL, 1, 1, NOW(), NOW()),
(3, 49, 'Rib-Eye', 'rib-eye', 'South African cuts — served with side of choice', 22000.00, NULL, 2, 1, NOW(), NOW()),
(3, 49, 'Lamb Chops', 'lamb-chops', 'South African cuts — served with side of choice', 30000.00, NULL, 3, 1, NOW(), NOW()),
(3, 49, 'Beef Ribs', 'beef-ribs', 'South African cuts — served with side of choice', 22000.00, NULL, 4, 1, NOW(), NOW()),
(3, 49, 'Oxtail', 'oxtail', 'South African cuts — served with side of choice', 6000.00, NULL, 5, 1, NOW(), NOW()),
(3, 50, 'Mixed Grill Special', 'mixed-grill-special', '', 13300.00, NULL, 1, 1, NOW(), NOW()),
(3, 50, 'Egyptian Mixed Grill', 'egyptian-mixed-grill', '', 13500.00, NULL, 2, 1, NOW(), NOW()),
(3, 51, 'MD''s Prime Platter', 'mds-prime-platter', '', 56000.00, NULL, 1, 1, NOW(), NOW()),
(3, 51, 'Pacific Platter', 'pacific-platter', '', 38000.00, NULL, 2, 1, NOW(), NOW()),
(3, 51, 'D''View Special Platter', 'dview-special-platter', '', 25000.00, NULL, 3, 1, NOW(), NOW()),
(3, 51, 'Ogazi Platter', 'ogazi-platter', '', 25000.00, NULL, 4, 1, NOW(), NOW()),
(3, 52, 'Spaghetti Prawn Marinara', 'spaghetti-prawn-marinara', '', 15000.00, NULL, 1, 1, NOW(), NOW()),
(3, 52, 'Creamy Prawn Tagliatelle', 'creamy-prawn-tagliatelle', '', 13000.00, NULL, 2, 1, NOW(), NOW()),
(3, 52, 'Seafood Pasta', 'seafood-pasta', '', 15000.00, NULL, 3, 1, NOW(), NOW()),
(3, 52, 'Spaghetti & Meatballs', 'spaghetti-meatballs', '', 8000.00, NULL, 4, 1, NOW(), NOW()),
(3, 52, 'Fettuccine Alfredo', 'fettuccine-alfredo', '', 16000.00, NULL, 5, 1, NOW(), NOW()),
(3, 52, 'Chicken Pesto Penne', 'chicken-pesto-penne', '', 13000.00, NULL, 6, 1, NOW(), NOW()),
(3, 52, 'Spaghetti Bolognese', 'spaghetti-bolognese', '', 15000.00, NULL, 7, 1, NOW(), NOW()),
(3, 52, 'Spaghetti Aglio Olio', 'spaghetti-aglio-olio', '', 6000.00, NULL, 8, 1, NOW(), NOW()),
(3, 52, 'Fettuccine Prawn Grill', 'fettuccine-prawn-grill', '', 7000.00, NULL, 9, 1, NOW(), NOW()),
(3, 53, 'Okro (Seafood)', 'okro-seafood', 'Served with semovita, eba, or pounded yam', 30000.00, NULL, 1, 1, NOW(), NOW()),
(3, 53, 'Eforiro (Seafood)', 'eforiro-seafood', 'Served with semovita, eba, or pounded yam', 30000.00, NULL, 2, 1, NOW(), NOW()),
(3, 53, 'Edikaikong (Seafood)', 'edikaikong-seafood', 'Served with semovita, eba, or pounded yam', 30000.00, NULL, 3, 1, NOW(), NOW()),
(3, 53, 'Egusi (Seafood)', 'egusi-seafood', 'Served with semovita, eba, or pounded yam', 30000.00, NULL, 4, 1, NOW(), NOW()),
(3, 53, 'Fisherman Soup (Croaker / Catfish)', 'fisherman-soup', 'Served with semovita, eba, or pounded yam', 30000.00, NULL, 5, 1, NOW(), NOW()),
(3, 53, 'Edikaikong (Regular)', 'edikaikong-regular', 'Served with semovita, eba, or pounded yam', 18000.00, NULL, 6, 1, NOW(), NOW()),
(3, 53, 'Eforiro (Regular)', 'eforiro-regular', 'Served with semovita, eba, or pounded yam', 18000.00, NULL, 7, 1, NOW(), NOW()),
(3, 53, 'Afang', 'afang', 'Served with semovita, eba, or pounded yam', 18000.00, NULL, 8, 1, NOW(), NOW()),
(3, 53, 'Ogbono', 'ogbono', 'Served with semovita, eba, or pounded yam', 18000.00, NULL, 9, 1, NOW(), NOW()),
(3, 54, 'Seafood Jollof Rice', 'seafood-jollof-rice', '', 25000.00, NULL, 1, 1, NOW(), NOW()),
(3, 54, 'D''View Special Fried Rice', 'dview-special-fried-rice', '', 16000.00, NULL, 2, 1, NOW(), NOW()),
(3, 54, 'Jollof Rice Fiesta', 'jollof-rice-fiesta', '', 16000.00, NULL, 3, 1, NOW(), NOW()),
(3, 54, 'Isi Ewu', 'isi-ewu', '', 20000.00, NULL, 4, 1, NOW(), NOW()),
(3, 54, 'Yam Pottage', 'yam-pottage', '', 15000.00, NULL, 5, 1, NOW(), NOW()),
(3, 55, 'Jollof Rice', 'jollof-rice-side', '', 7000.00, NULL, 1, 1, NOW(), NOW()),
(3, 55, 'Fried Rice', 'fried-rice', '', 5000.00, NULL, 2, 1, NOW(), NOW()),
(3, 55, 'Fried Plantain', 'fried-plantain', '', 7000.00, NULL, 3, 1, NOW(), NOW()),
(3, 55, 'Yam Chips', 'yam-chips', '', 7000.00, NULL, 4, 1, NOW(), NOW()),
(3, 55, 'French Fries', 'french-fries', '', 5000.00, NULL, 5, 1, NOW(), NOW()),
(3, 55, 'Sweet Potato Fries', 'sweet-potato-fries', '', 5000.00, NULL, 6, 1, NOW(), NOW()),
(3, 55, 'Steamed Rice', 'steamed-rice', '', 5000.00, NULL, 7, 1, NOW(), NOW()),
(3, 55, 'Bread Rolls (2 pcs)', 'bread-rolls-2pcs', '', 1000.00, NULL, 8, 1, NOW(), NOW()),
(3, 55, 'Eggs (2)', 'eggs-2', '', 5000.00, NULL, 9, 1, NOW(), NOW()),
(3, 55, 'Ogbono Extra', 'ogbono-extra', '', 7000.00, NULL, 10, 1, NOW(), NOW());

-- 22c. Update restaurant item counts
UPDATE `restaurants` SET `available_items_count` = (SELECT COUNT(*) FROM `menu_items` WHERE `restaurant_id` = 3 AND `is_available` = 1), `unavailable_items_count` = (SELECT COUNT(*) FROM `menu_items` WHERE `restaurant_id` = 3 AND `is_available` = 0) WHERE `id` = 3;

-- 23. Ensure Template 4 (hotel) restaurants have reservation settings for checkout redirect
--     Inserts default deposit (5000) for any Template 4 restaurant missing settings
INSERT INTO `restaurant_reservation_settings` (`restaurant_id`, `deposit_amount`)
SELECT r.id, 5000 FROM `restaurants` r
LEFT JOIN `restaurant_reservation_settings` rrs ON r.id = rrs.restaurant_id
WHERE r.template_id = 4 AND rrs.restaurant_id IS NULL;
