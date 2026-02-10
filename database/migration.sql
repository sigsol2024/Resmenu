-- ============================================================
-- Resmenu Database Migration
-- ============================================================
-- Use for: Updating EXISTING database (run in phpMyAdmin or MySQL client)
-- Fresh installs: Use sigsolmenu_resmenu.sql (full schema)
--
-- Run this file against your database to add:
-- - orders & order_items tables (food ordering)
-- - Template 4 (The Gourmet Grill) if missing
-- - restaurant_payment_settings table (per-restaurant checkout payment options)
-- - payment_method column on orders table
-- ============================================================

-- 1. Orders table (food ordering system)
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `restaurant_id` int(11) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_phone` varchar(50) NOT NULL,
  `customer_email` varchar(255) NOT NULL,
  `delivery_address` text NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'pending' COMMENT 'pending, confirmed, completed, cancelled',
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

-- 4. Add template customization defaults for Template 4
INSERT INTO `template_customizations` (`template_id`, `menu_title_color`, `menu_title_size`, `menu_title_font`, `price_color`, `price_size`, `price_font`, `description_color`, `description_size`, `description_font`, `category_title_color`, `category_title_size`, `category_title_font`, `background_color`, `header_background_color`, `primary_color`, `secondary_color`, `created_at`, `updated_at`)
VALUES (4, '#121212', 24, 'Epilogue', '#f20d0d', 18, 'Epilogue', '#666666', 14, 'Epilogue', '#121212', 20, 'Epilogue', '#f8f5f5', '#121212', '#f20d0d', '#FFFFFF', NOW(), NOW())
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
