-- Resmenu schema migration
-- Adds `sections` table and `categories.section_id` required by the application.
-- Run this once on a database created from sigsolmenu_resmenu.sql (or equivalent).
-- For a new server: import the full dump (sigsolmenu_resmenu.sql) then run this file.
-- If `section_id` already exists on categories, skip the ALTER TABLE statements at the end.

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
/*!40101 SET NAMES utf8mb4 */;

-- ---------------------------------------------------------------------------
-- 1. Create sections table (application expects sections for menu grouping)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `restaurant_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `display_order` int(11) DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_sections_restaurant` (`restaurant_id`),
  KEY `idx_sections_display_order` (`display_order`),
  CONSTRAINT `sections_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- 2. Add section_id to categories (run once; omit if column already exists)
-- ---------------------------------------------------------------------------
-- This block is idempotent: it checks for the column, index and FK before adding them,
-- so it can be safely re-run without "duplicate" errors.

DELIMITER //

DROP PROCEDURE IF EXISTS resmenu_add_section_id_to_categories//
CREATE PROCEDURE resmenu_add_section_id_to_categories()
BEGIN
  DECLARE col_exists INT DEFAULT 0;
  DECLARE idx_exists INT DEFAULT 0;
  DECLARE fk_exists INT DEFAULT 0;

  -- Check if the section_id column already exists
  SELECT COUNT(*) INTO col_exists
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'categories'
    AND COLUMN_NAME = 'section_id';

  IF col_exists = 0 THEN
    ALTER TABLE `categories` ADD COLUMN `section_id` int(11) DEFAULT NULL AFTER `restaurant_id`;
  END IF;

  -- Check if the index on section_id already exists
  SELECT COUNT(*) INTO idx_exists
  FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'categories'
    AND INDEX_NAME = 'idx_section_id';

  IF idx_exists = 0 THEN
    ALTER TABLE `categories` ADD KEY `idx_section_id` (`section_id`);
  END IF;

  -- Check if the foreign key already exists
  SELECT COUNT(*) INTO fk_exists
  FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'categories'
    AND CONSTRAINT_TYPE = 'FOREIGN KEY'
    AND CONSTRAINT_NAME = 'categories_section_fk';

  IF fk_exists = 0 THEN
    ALTER TABLE `categories`
      ADD CONSTRAINT `categories_section_fk`
      FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE SET NULL;
  END IF;
END//

CALL resmenu_add_section_id_to_categories()//
DROP PROCEDURE IF EXISTS resmenu_add_section_id_to_categories//

DELIMITER ;

-- ---------------------------------------------------------------------------
-- 3. Add image column to sections (idempotent)
-- ---------------------------------------------------------------------------

DELIMITER //

DROP PROCEDURE IF EXISTS resmenu_add_image_to_sections//
CREATE PROCEDURE resmenu_add_image_to_sections()
BEGIN
  DECLARE col_exists INT DEFAULT 0;

  SELECT COUNT(*) INTO col_exists
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'sections'
    AND COLUMN_NAME = 'image';

  IF col_exists = 0 THEN
    ALTER TABLE `sections` ADD COLUMN `image` varchar(255) DEFAULT NULL AFTER `slug`;
  END IF;
END//

CALL resmenu_add_image_to_sections()//
DROP PROCEDURE IF EXISTS resmenu_add_image_to_sections//

DELIMITER ;

-- ---------------------------------------------------------------------------
-- 4. Seed Drinks section for The Lusso Restaurant (optional)
-- ---------------------------------------------------------------------------
-- To populate the Drinks section with categories and menu items for the
-- restaurant with email restaurant@lussohotelsabuja.com, run the seed file
-- after this migration:
--
--   mysql -u user -p database_name < database/seed_lusso_drinks.sql
--
-- Or in MySQL client: SOURCE /path/to/Resmenu/database/seed_lusso_drinks.sql;
--
-- The seed creates section "Drinks" and 20 categories (Soft Drinks/Water,
-- Juices, Energy Drinks, Beers, Aperitif, Gin, Whisky variants, Vodka, Rum,
-- Cognac, Tequila, Liquor, Hot Beverages, White/Red/Rosé Wine, Champagne)
-- with all menu items and prices in ₦. Run once per restaurant.
--
-- Mania House (admin@maniahouse.our-menu.online): optional full menu seed
-- (Wing Mania, Mania Brunch, Hookah Mania sections). Regenerate SQL from
-- database/build_mania_seed.mjs if you edit menu data, then run:
--   mysql -u user -p database_name < database/seed_maniahouse_menu.sql

-- ---------------------------------------------------------------------------
-- 5. Category -> multiple secondary sections (category_secondary_sections)
-- ---------------------------------------------------------------------------
-- This enables a category to appear on multiple secondary section pages,
-- while full-menu pages still show only primary categories (categories.section_id).

CREATE TABLE IF NOT EXISTS `category_secondary_sections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_category_secondary` (`category_id`, `section_id`),
  KEY `idx_secondary_section` (`section_id`),
  CONSTRAINT `fk_css_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_css_section` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

