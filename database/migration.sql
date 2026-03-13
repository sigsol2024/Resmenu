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
-- If you get "Duplicate column name 'section_id'", the migration was already applied.
ALTER TABLE `categories` ADD COLUMN `section_id` int(11) DEFAULT NULL AFTER `restaurant_id`;
ALTER TABLE `categories` ADD KEY `idx_section_id` (`section_id`);
ALTER TABLE `categories` ADD CONSTRAINT `categories_section_fk` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE SET NULL;
