-- ============================================================
-- Resmenu Database Migration
-- ============================================================
-- Use for: Updating EXISTING database (run in phpMyAdmin or MySQL client)
-- Fresh installs: Use sigsolmenu_resmenu.sql (full schema)
--
-- This migration includes:
-- 1. Schema updates (orders, order_items, payments, reservations, etc.)
-- 2. Fix &amp; in category/menu names (section 23a)
-- 3. Ensure Template 4 restaurants have reservation settings (section 23b)
-- 34. Menu sections (sections table, categories.section_id, backfill) — idempotent
--
-- Requires: MariaDB 10.0.2+ or MySQL 8.0.12+ for ADD COLUMN IF NOT EXISTS
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

-- 3. Template 4 + customization defaults
INSERT INTO `templates` (`id`, `name`, `description`, `preview_image`, `is_active`, `created_at`, `updated_at`)
VALUES (4, 'The Gourmet Grill', 'Premium dark-themed design with Epilogue font, herb pattern, and flame-grilled aesthetic', NULL, 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `description` = VALUES(`description`), `is_active` = 1, `updated_at` = NOW();

INSERT INTO `template_customizations` (`template_id`, `menu_title_color`, `menu_title_size`, `menu_title_font`, `price_color`, `price_size`, `price_font`, `description_color`, `description_size`, `description_font`, `category_title_color`, `category_title_size`, `category_title_font`, `background_color`, `header_background_color`, `primary_color`, `secondary_color`, `created_at`, `updated_at`)
VALUES (1, '#1A1A1A', 24, 'Inter', '#1A1A1A', 18, 'Inter', '#666666', 14, 'Inter', '#1A1A1A', 20, 'Inter', '#FFFFFF', '#FFFFFF', '#1A1A1A', '#FAF3E6', NOW(), NOW()),
       (2, '#1A1A1A', 24, 'Inter', '#ea2a33', 18, 'Inter', '#666666', 14, 'Inter', '#1A1A1A', 20, 'Inter', '#f8f6f6', '#f8f6f6', '#ea2a33', '#FFFFFF', NOW(), NOW()),
       (3, '#1A1A1A', 24, 'Inter', '#ea2a33', 18, 'Inter', '#666666', 14, 'Inter', '#1A1A1A', 20, 'Inter', '#f8f6f6', '#f8f6f6', '#ea2a33', '#FFFFFF', NOW(), NOW()),
       (4, '#121212', 24, 'Epilogue', '#f20d0d', 18, 'Epilogue', '#666666', 14, 'Epilogue', '#121212', 20, 'Epilogue', '#f8f5f5', '#121212', '#f20d0d', '#FFFFFF', NOW(), NOW())
ON DUPLICATE KEY UPDATE `menu_title_color` = VALUES(`menu_title_color`), `price_color` = VALUES(`price_color`), `primary_color` = VALUES(`primary_color`), `background_color` = VALUES(`background_color`), `updated_at` = NOW();

-- 4. Restaurant payment settings
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

-- 5-7. Orders columns
ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `payment_method` varchar(50) DEFAULT NULL AFTER `delivery_address`;
ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `order_number` varchar(10) DEFAULT NULL AFTER `id`;
ALTER TABLE `orders` MODIFY COLUMN `status` varchar(50) NOT NULL DEFAULT 'pending' COMMENT 'pending, confirmed, on_hold, cancelled, completed';

-- 8. Pending bank transfers
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

-- 9. Pending online payments
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

-- 10. Table reservations
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

-- 11. Restaurant reservation settings
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

-- 12. Reservation payment support on pending tables
ALTER TABLE `pending_bank_transfers` ADD COLUMN IF NOT EXISTS `payment_type` varchar(20) NOT NULL DEFAULT 'order' AFTER `restaurant_id`;
ALTER TABLE `pending_bank_transfers` ADD COLUMN IF NOT EXISTS `reservation_id` int(11) DEFAULT NULL AFTER `payment_type`;
ALTER TABLE `pending_online_payments` ADD COLUMN IF NOT EXISTS `payment_type` varchar(20) NOT NULL DEFAULT 'order' AFTER `restaurant_id`;
ALTER TABLE `pending_online_payments` ADD COLUMN IF NOT EXISTS `reservation_id` int(11) DEFAULT NULL AFTER `payment_type`;

-- 13. Deposit columns on table_reservations
ALTER TABLE `table_reservations` ADD COLUMN IF NOT EXISTS `deposit_amount` decimal(10,2) NOT NULL DEFAULT 0.00 AFTER `notes`;
ALTER TABLE `table_reservations` ADD COLUMN IF NOT EXISTS `deposit_paid` tinyint(1) NOT NULL DEFAULT 0 AFTER `deposit_amount`;

-- 14. Table inventory daily
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

-- 15-17. Table reservations columns
ALTER TABLE `table_reservations` ADD COLUMN IF NOT EXISTS `is_walkin` tinyint(1) NOT NULL DEFAULT 0 AFTER `status`;
ALTER TABLE `table_reservations` ADD COLUMN IF NOT EXISTS `reservation_number` varchar(10) DEFAULT NULL AFTER `id`;

-- 18. Site settings
CREATE TABLE IF NOT EXISTS `site_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `site_name` varchar(255) NOT NULL DEFAULT 'Resmenu',
  `site_logo` varchar(255) DEFAULT NULL,
  `favicon` varchar(255) DEFAULT NULL,
  `contact_sales_email` varchar(255) DEFAULT NULL,
  `contact_sales_phone` varchar(50) DEFAULT NULL,
  `contact_support_email` varchar(255) DEFAULT NULL,
  `contact_support_phone` varchar(50) DEFAULT NULL,
  `contact_partners_email` varchar(255) DEFAULT NULL,
  `contact_form_recipient` varchar(255) DEFAULT NULL,
  `contact_hq_title` varchar(255) DEFAULT NULL,
  `contact_hq_address` text DEFAULT NULL,
  `contact_map_embed` text DEFAULT NULL,
  `contact_social_facebook` varchar(255) DEFAULT NULL,
  `contact_social_twitter` varchar(255) DEFAULT NULL,
  `contact_social_instagram` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT IGNORE INTO `site_settings` (`id`, `site_name`) VALUES (1, 'Resmenu');

-- Ensure new contact/social columns exist on existing installs
ALTER TABLE `site_settings` ADD COLUMN IF NOT EXISTS `contact_sales_email` varchar(255) DEFAULT NULL AFTER `favicon`;
ALTER TABLE `site_settings` ADD COLUMN IF NOT EXISTS `contact_sales_phone` varchar(50) DEFAULT NULL AFTER `contact_sales_email`;
ALTER TABLE `site_settings` ADD COLUMN IF NOT EXISTS `contact_support_email` varchar(255) DEFAULT NULL AFTER `contact_sales_phone`;
ALTER TABLE `site_settings` ADD COLUMN IF NOT EXISTS `contact_support_phone` varchar(50) DEFAULT NULL AFTER `contact_support_email`;
ALTER TABLE `site_settings` ADD COLUMN IF NOT EXISTS `contact_partners_email` varchar(255) DEFAULT NULL AFTER `contact_support_phone`;
ALTER TABLE `site_settings` ADD COLUMN IF NOT EXISTS `contact_form_recipient` varchar(255) DEFAULT NULL AFTER `contact_partners_email`;
ALTER TABLE `site_settings` ADD COLUMN IF NOT EXISTS `contact_hq_title` varchar(255) DEFAULT NULL AFTER `contact_form_recipient`;
ALTER TABLE `site_settings` ADD COLUMN IF NOT EXISTS `contact_hq_address` text DEFAULT NULL AFTER `contact_hq_title`;
ALTER TABLE `site_settings` ADD COLUMN IF NOT EXISTS `contact_map_embed` text DEFAULT NULL AFTER `contact_hq_address`;
ALTER TABLE `site_settings` ADD COLUMN IF NOT EXISTS `contact_social_facebook` varchar(255) DEFAULT NULL AFTER `contact_map_embed`;
ALTER TABLE `site_settings` ADD COLUMN IF NOT EXISTS `contact_social_twitter` varchar(255) DEFAULT NULL AFTER `contact_social_facebook`;
ALTER TABLE `site_settings` ADD COLUMN IF NOT EXISTS `contact_social_instagram` varchar(255) DEFAULT NULL AFTER `contact_social_twitter`;

-- 19. Per-template customization
ALTER TABLE `customization_settings` ADD COLUMN IF NOT EXISTS `template_id` int(11) NOT NULL DEFAULT 1 AFTER `restaurant_id`;
DELETE FROM `customization_settings` WHERE id IN (
  SELECT id FROM (
    SELECT cs1.id FROM `customization_settings` cs1
    INNER JOIN `restaurants` r ON r.id = cs1.restaurant_id
    INNER JOIN `customization_settings` cs2 ON cs2.restaurant_id = cs1.restaurant_id AND cs2.template_id = COALESCE(r.template_id, 1) AND cs2.id != cs1.id
    WHERE cs1.template_id != COALESCE(r.template_id, 1)
  ) AS t
);
DELETE FROM `customization_settings` WHERE id IN (
  SELECT id FROM (
    SELECT cs1.id FROM `customization_settings` cs1
    INNER JOIN `customization_settings` cs2 ON cs1.restaurant_id = cs2.restaurant_id AND cs1.template_id = cs2.template_id AND cs1.id > cs2.id
  ) AS t
);
UPDATE `customization_settings` cs JOIN `restaurants` r ON r.id = cs.restaurant_id SET cs.template_id = COALESCE(r.template_id, 1);
CREATE UNIQUE INDEX IF NOT EXISTS `restaurant_template` ON `customization_settings` (`restaurant_id`, `template_id`);
ALTER TABLE `customization_settings` DROP INDEX IF EXISTS `restaurant_id`;

-- 20. Restaurants social media columns
ALTER TABLE `restaurants` ADD COLUMN IF NOT EXISTS `whatsapp_link` varchar(255) DEFAULT NULL AFTER `website`;
ALTER TABLE `restaurants` ADD COLUMN IF NOT EXISTS `instagram_url` varchar(255) DEFAULT NULL AFTER `whatsapp_link`;
ALTER TABLE `restaurants` ADD COLUMN IF NOT EXISTS `facebook_url` varchar(255) DEFAULT NULL AFTER `instagram_url`;
ALTER TABLE `restaurants` ADD COLUMN IF NOT EXISTS `twitter_url` varchar(255) DEFAULT NULL AFTER `facebook_url`;

-- 21. Restaurant-level toggles for ordering & reservations (manager can turn off even if plan allows)
ALTER TABLE `restaurants` ADD COLUMN IF NOT EXISTS `enable_food_ordering` tinyint(1) NOT NULL DEFAULT 1 AFTER `twitter_url`;
ALTER TABLE `restaurants` ADD COLUMN IF NOT EXISTS `enable_table_reservations` tinyint(1) NOT NULL DEFAULT 1 AFTER `enable_food_ordering`;

-- ============================================================
-- 23. Most recent migrations
-- ============================================================

-- 23a. Fix double-encoded &amp; in category and menu item names/descriptions
UPDATE `categories` SET `name` = REPLACE(`name`, '&amp;', '&'), `description` = REPLACE(`description`, '&amp;', '&') WHERE `name` LIKE '%&amp;%' OR `description` LIKE '%&amp;%';
UPDATE `menu_items` SET `name` = REPLACE(`name`, '&amp;', '&'), `description` = REPLACE(`description`, '&amp;', '&') WHERE `name` LIKE '%&amp;%' OR `description` LIKE '%&amp;%';

-- 23b. Ensure Template 4 (hotel) restaurants have reservation settings for checkout redirect
INSERT INTO `restaurant_reservation_settings` (`restaurant_id`, `deposit_amount`)
SELECT r.id, 5000 FROM `restaurants` r
LEFT JOIN `restaurant_reservation_settings` rrs ON r.id = rrs.restaurant_id
WHERE r.template_id = 4 AND rrs.restaurant_id IS NULL;

-- 23c. Template listing image (resmenu.net timeline card image; cover stays for template preview page)
ALTER TABLE `templates` ADD COLUMN IF NOT EXISTS `listing_image` varchar(255) DEFAULT NULL AFTER `preview_image`;

-- 24. Scheduled subscription change requests (for downgrade/cycle change at period end)
CREATE TABLE IF NOT EXISTS `subscription_change_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `restaurant_id` int(11) NOT NULL,
  `subscription_id` int(11) NOT NULL,
  `from_plan_id` int(11) NOT NULL,
  `to_plan_id` int(11) NOT NULL,
  `from_billing_cycle` varchar(20) NOT NULL,
  `to_billing_cycle` varchar(20) NOT NULL,
  `change_type` varchar(50) NOT NULL,
  `effective_at` datetime NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `requested_by` varchar(20) DEFAULT 'manager',
  `applied_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_subscription_pending` (`subscription_id`, `status`),
  KEY `idx_effective_pending` (`effective_at`, `status`),
  KEY `idx_restaurant_pending` (`restaurant_id`, `status`),
  CONSTRAINT `subscription_change_requests_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `subscription_change_requests_ibfk_2` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `subscription_change_requests_ibfk_3` FOREIGN KEY (`from_plan_id`) REFERENCES `subscription_plans` (`id`) ON DELETE CASCADE,
  CONSTRAINT `subscription_change_requests_ibfk_4` FOREIGN KEY (`to_plan_id`) REFERENCES `subscription_plans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 25. Password reset tokens (email reset-link flow)
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_type` enum('admin','manager') NOT NULL,
  `user_id` int(11) NOT NULL,
  `identifier` varchar(191) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `request_ip` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_token_hash` (`token_hash`),
  KEY `idx_user_active` (`user_type`, `user_id`, `used_at`, `expires_at`),
  KEY `idx_identifier_created` (`identifier`, `created_at`),
  KEY `idx_ip_created` (`request_ip`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 26. Subscription email history index (allow recurring reminders across billing cycles)
ALTER TABLE `subscription_emails` DROP INDEX IF EXISTS `unique_email`;
CREATE INDEX IF NOT EXISTS `idx_subscription_email_lookup` ON `subscription_emails` (`subscription_id`, `email_type`, `days_before`, `sent_at`);

-- 27. Ensure plan feature flags include ordering/reservations (default mapping)
-- Adds new JSON keys if missing:
-- - Basic: food_ordering=false, table_reservations=false
-- - Professional: food_ordering=true, table_reservations=false
-- - Enterprise: food_ordering=true, table_reservations=true
-- Uses JSON_EXTRACT from literal JSON to avoid CAST(... AS JSON) syntax issues on older MariaDB/MySQL.
UPDATE `subscription_plans`
SET `features` =
  CASE
    WHEN `features` IS NULL OR `features` = '' OR JSON_VALID(`features`) = 0 THEN
      JSON_OBJECT(
        'priority_support', 0,
        'custom_domain', 0,
        'analytics_advanced', 0,
        'food_ordering', IF(`slug` IN ('professional','enterprise'), true, false),
        'table_reservations', IF(`slug` = 'enterprise', true, false)
      )
    ELSE
      JSON_SET(
        JSON_SET(
          `features`,
          '$.food_ordering',
          COALESCE(JSON_EXTRACT(`features`, '$.food_ordering'), JSON_EXTRACT(CASE WHEN `slug` IN ('professional','enterprise') THEN '{"f":true}' ELSE '{"f":false}' END, '$.f'))
        ),
        '$.table_reservations',
        COALESCE(JSON_EXTRACT(`features`, '$.table_reservations'), JSON_EXTRACT(CASE WHEN `slug` = 'enterprise' THEN '{"f":true}' ELSE '{"f":false}' END, '$.f'))
      )
  END
WHERE `slug` IN ('basic','professional','enterprise');

-- 28. Default template descriptions for marketing (resmenu.net) and template preview
-- Run once to set or update descriptions. Admin can change them later in /admin/templates.php
UPDATE `templates` SET `description` = 'Elegant and sophisticated fine dining style with clean typography and alternating layout. Perfect for bistros, full-service restaurants, and venues that want a classic yet modern menu presentation.' WHERE `id` = 1;
UPDATE `templates` SET `description` = 'Modern restaurant template with hero sections and featured items. Ideal for casual dining, cafes, and bars. Tailwind-based design with a fresh, approachable look.' WHERE `id` = 2;
UPDATE `templates` SET `description` = 'Dark navy gradient background with bold typography and white cards. Great for lounges, cocktail bars, and upscale venues that want a striking, premium feel.' WHERE `id` = 3;
UPDATE `templates` SET `description` = 'Premium dark-themed design with warm accents and rustic charm. Ideal for steakhouses, grills, and traditional pubs. Features reservation integration and a distinctive atmosphere.' WHERE `id` = 4;

-- 29. Login rate limiting (security audit)
CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `identifier` varchar(255) NOT NULL DEFAULT '',
  `attempted_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_login_attempts_ip_time` (`ip_address`, `attempted_at`),
  KEY `idx_login_attempts_identifier_time` (`identifier`(191), `attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 30. Yearly discount % for subscription plans (annual = monthly*12*(1 - discount/100))
ALTER TABLE `subscription_plans` ADD COLUMN IF NOT EXISTS `yearly_discount_percent` decimal(5,2) NOT NULL DEFAULT 20.00 COMMENT 'Discount % applied to yearly plan (annual price = monthly*12*(1 - this/100))' AFTER `annual_price`;
UPDATE `subscription_plans` SET `annual_price` = `monthly_price` * 12 * (1 - COALESCE(`yearly_discount_percent`, 20) / 100) WHERE `monthly_price` > 0;

-- 31. Admins table for admin registration (create if not exists; used by temporary register-admin.php)
CREATE TABLE IF NOT EXISTS `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `admins_username` (`username`),
  UNIQUE KEY `admins_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 32. Template plan assignment and private restaurant assignment
ALTER TABLE `templates` ADD COLUMN IF NOT EXISTS `is_private` tinyint(1) NOT NULL DEFAULT 0 AFTER `is_active`;

CREATE TABLE IF NOT EXISTS `template_plans` (
  `template_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  PRIMARY KEY (`template_id`, `plan_id`),
  KEY `plan_id` (`plan_id`),
  CONSTRAINT `template_plans_ibfk_1` FOREIGN KEY (`template_id`) REFERENCES `templates` (`id`) ON DELETE CASCADE,
  CONSTRAINT `template_plans_ibfk_2` FOREIGN KEY (`plan_id`) REFERENCES `subscription_plans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `template_restaurants` (
  `template_id` int(11) NOT NULL,
  `restaurant_id` int(11) NOT NULL,
  PRIMARY KEY (`template_id`, `restaurant_id`),
  KEY `restaurant_id` (`restaurant_id`),
  CONSTRAINT `template_restaurants_ibfk_1` FOREIGN KEY (`template_id`) REFERENCES `templates` (`id`) ON DELETE CASCADE,
  CONSTRAINT `template_restaurants_ibfk_2` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 33. Template slug and 14 new named templates (real names, slug = folder name)
ALTER TABLE `templates` ADD COLUMN IF NOT EXISTS `slug` varchar(100) DEFAULT NULL AFTER `name`;

UPDATE `templates` SET `slug` = 'template1' WHERE `id` = 1;
UPDATE `templates` SET `slug` = 'template2' WHERE `id` = 2;
UPDATE `templates` SET `slug` = 'template3' WHERE `id` = 3;
UPDATE `templates` SET `slug` = 'template4' WHERE `id` = 4;

INSERT INTO `templates` (`id`, `name`, `slug`, `description`, `preview_image`, `listing_image`, `is_active`, `created_at`, `updated_at`) VALUES
(5, 'The Prime Cut', 'the_prime_cut', 'Premium steakhouse menu design with burgundy and gold.', NULL, NULL, 1, NOW(), NOW()),
(6, 'The Garden Bistro', 'the_garden_bistro', 'Garden bistro style menu template.', NULL, NULL, 1, NOW(), NOW()),
(7, 'The Art Fusion', 'the_art_fusion', 'Art fusion restaurant menu design.', NULL, NULL, 1, NOW(), NOW()),
(8, 'Sweet Delight', 'sweet_delight', 'Playful dessert parlour style menu.', NULL, NULL, 1, NOW(), NOW()),
(9, 'Street Food Hub', 'street_food_hub', 'Street food hub menu template.', NULL, NULL, 1, NOW(), NOW()),
(10, 'Salt N Socials White', 'salt_n_socials_white', 'Salt N Socials white variant.', NULL, NULL, 1, NOW(), NOW()),
(11, 'Salt N Socials Colored', 'salt_n_socials_colored', 'Salt N Socials colored variant.', NULL, NULL, 1, NOW(), NOW()),
(12, 'Mediterranean Fresh', 'mediterranean_fresh', 'Mediterranean fresh menu design.', NULL, NULL, 1, NOW(), NOW()),
(13, 'Forged In Spirit', 'forged_in_spirit', 'Forged In Spirit design.', NULL, NULL, 1, NOW(), NOW()),
(14, 'Eart Kitchen', 'eart_kitchen', 'Eart Kitchen menu template.', NULL, NULL, 1, NOW(), NOW()),
(15, 'Bold Flavours', 'bold_flavours', 'Bold flavours menu design.', NULL, NULL, 1, NOW(), NOW()),
(16, 'Neo Mex Cantina', 'neo_mex_cantina', 'Neo Mex Cantina style menu.', NULL, NULL, 1, NOW(), NOW()),
(17, 'Nostalgia Front Page', 'nostalgia_front_page', 'Nostalgia front page design.', NULL, NULL, 1, NOW(), NOW()),
(18, 'Nostalgia Food Menu', 'nostalgia_food_menu', 'Nostalgia food menu design.', NULL, NULL, 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `slug` = VALUES(`slug`), `description` = VALUES(`description`), `is_active` = 1, `updated_at` = NOW();

-- ============================================================
-- 34. Menu sections (Section → Categories → Menu items)
-- Idempotent: safe to re-run (skips ADD KEY / MODIFY / FK if already applied).
-- ============================================================
CREATE TABLE IF NOT EXISTS `sections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `restaurant_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_restaurant_section_slug` (`restaurant_id`,`slug`),
  KEY `idx_sections_restaurant` (`restaurant_id`),
  KEY `idx_sections_display_order` (`display_order`),
  CONSTRAINT `sections_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `categories` ADD COLUMN IF NOT EXISTS `section_id` int(11) DEFAULT NULL AFTER `restaurant_id`;

-- Add index only if it does not exist (idempotent re-run)
SET @idx_exists = (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'categories' AND INDEX_NAME = 'idx_section_id');
SET @sql = IF(@idx_exists = 0, 'ALTER TABLE `categories` ADD KEY `idx_section_id` (`section_id`)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Backfill: create one default section per restaurant that has categories, then assign categories to it
INSERT INTO `sections` (`restaurant_id`, `name`, `slug`, `display_order`, `is_active`, `created_at`, `updated_at`)
SELECT DISTINCT c.`restaurant_id`, 'General', 'general', 1, 1, NOW(), NOW()
FROM `categories` c
LEFT JOIN `sections` s ON s.`restaurant_id` = c.`restaurant_id` AND s.`slug` = 'general'
WHERE s.`id` IS NULL;

UPDATE `categories` c
INNER JOIN `sections` s ON s.`restaurant_id` = c.`restaurant_id` AND s.`slug` = 'general'
SET c.`section_id` = s.`id`
WHERE c.`section_id` IS NULL;

-- Ensure section_id is set for any remaining categories (restaurants that had no General section yet)
INSERT INTO `sections` (`restaurant_id`, `name`, `slug`, `display_order`, `is_active`, `created_at`, `updated_at`)
SELECT DISTINCT c.`restaurant_id`, 'General', 'general', 1, 1, NOW(), NOW()
FROM `categories` c
WHERE c.`section_id` IS NULL;

UPDATE `categories` c
INNER JOIN `sections` s ON s.`restaurant_id` = c.`restaurant_id` AND s.`slug` = 'general'
SET c.`section_id` = s.`id`
WHERE c.`section_id` IS NULL;

-- Make section_id NOT NULL and add FK only if not already applied (idempotent)
SET @col_not_null = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'categories' AND COLUMN_NAME = 'section_id' AND IS_NULLABLE = 'NO');
SET @sql = IF(@col_not_null = 0, 'ALTER TABLE `categories` MODIFY COLUMN `section_id` int(11) NOT NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @fk_exists = (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'categories' AND CONSTRAINT_NAME = 'categories_section_fk');
SET @sql = IF(@fk_exists = 0, 'ALTER TABLE `categories` ADD CONSTRAINT `categories_section_fk` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE RESTRICT', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
