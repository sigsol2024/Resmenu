-- Table Inventory Management Migration
-- Run this migration to add table inventory support for reservations

-- 1. Create table_inventory_daily (total tables per restaurant per date)
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

-- 2. Add is_walkin flag to table_reservations
-- Requires MySQL 8.0.12+ or MariaDB 10.5.2+ for IF NOT EXISTS.
-- For older MySQL, run instead: ALTER TABLE table_reservations ADD COLUMN is_walkin tinyint(1) NOT NULL DEFAULT 0 AFTER status;
ALTER TABLE `table_reservations` ADD COLUMN IF NOT EXISTS `is_walkin` tinyint(1) NOT NULL DEFAULT 0 AFTER `status`;
