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
--
-- Requires: MariaDB 10.0.2+ or MySQL 8.0.12+ for ADD COLUMN IF NOT EXISTS
-- Section 19 also needs: MariaDB 10.5.2+ or MySQL 8.0.13+ for DROP/CREATE INDEX IF NOT EXISTS
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
ALTER TABLE `customization_settings` ADD COLUMN IF NOT EXISTS `template_id` int(11) NOT NULL DEFAULT 1 AFTER `restaurant_id`;
UPDATE `customization_settings` cs JOIN `restaurants` r ON r.id = cs.restaurant_id SET cs.template_id = r.template_id;
-- Drop old unique; create new composite unique (requires DROP INDEX IF EXISTS, CREATE INDEX IF NOT EXISTS)
ALTER TABLE `customization_settings` DROP INDEX IF EXISTS `restaurant_id`;
CREATE UNIQUE INDEX IF NOT EXISTS `restaurant_template` ON `customization_settings` (`restaurant_id`, `template_id`);
