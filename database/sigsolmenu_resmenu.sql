-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 13, 2026 at 09:27 AM
-- Server version: 10.6.25-MariaDB
-- PHP Version: 8.4.17

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sigsolmenu_resmenu`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `email`, `password_hash`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@restaurantmenu.com', 'admin123', '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(2, 'sigsol2024', 'sigsol2024@gmail.com', 'Secretpass0721//', '2025-12-19 18:43:07', '2025-12-19 18:43:07');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `restaurant_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `restaurant_id`, `name`, `slug`, `image`, `description`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'Appetizers/Starters', 'appetizers-starters', NULL, 'Start your meal with our delicious appetizers', 1, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(2, 1, 'Tacos', 'tacos', NULL, 'Fresh and flavorful tacos', 2, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(3, 1, 'Breakfasts & Brunches', 'breakfasts-brunches', NULL, 'Start your day right with our breakfast and brunch options', 3, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(4, 1, 'Rice Dishes', 'rice-dishes', NULL, 'Traditional and flavorful rice dishes', 4, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(5, 1, 'Sauces', 'sauces', NULL, 'Delicious sauces to complement your meal', 5, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(6, 1, 'Sides', 'sides', NULL, 'Perfect sides to accompany your main dish', 6, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(7, 1, 'Spinhive\'s Specials', 'spinhives-specials', NULL, 'Our signature dishes', 3, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(8, 1, 'Noodles', 'noodles', NULL, 'Satisfying noodle dishes', 7, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(9, 1, 'Pasta Dishes', 'pasta-dishes', NULL, 'Classic and creative pasta dishes', 8, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(10, 1, 'Burgers', 'burgers', NULL, 'Juicy and flavorful burgers', 9, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(11, 1, 'Prawns', 'prawns', NULL, 'Fresh prawn dishes', 10, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(12, 1, 'Seafood', 'seafood', NULL, 'Fresh seafood selections', 11, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(13, 1, 'Salads', 'salads', NULL, 'Fresh and healthy salads', 12, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(14, 1, 'Sandwiches', 'sandwiches', NULL, 'Delicious sandwiches', 13, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(15, 2, 'Appetizers', 'appetizers', '6945d9626bc44.jpg', 'Start your meal with our delicious appetizers', 1, 1, '2025-12-19 18:43:07', '2025-12-19 23:01:54'),
(16, 2, 'Side Orders', 'side-orders', '6945d97eb3a2f.webp', 'Perfect sides to complement your meal', 2, 1, '2025-12-19 18:43:07', '2025-12-19 23:02:22'),
(17, 2, 'Desserts', 'desserts', '6945d9f81c699.jpg', 'Sweet endings to your meal', 3, 1, '2025-12-19 18:43:07', '2025-12-19 23:04:24'),
(18, 2, 'Champagne', 'champagne', '6945da0b5f74f.jpg', 'Premium champagne selection', 4, 1, '2025-12-19 18:43:07', '2025-12-19 23:04:43'),
(19, 2, 'Tequila', 'tequila', '6945da1a42b4c.jpg', 'Premium tequila collection', 5, 1, '2025-12-19 18:43:07', '2025-12-19 23:04:58'),
(20, 2, 'Cognac', 'cognac', '6945da2ba75b9.jpg', 'Fine cognac selection', 6, 1, '2025-12-19 18:43:07', '2025-12-19 23:05:15'),
(21, 2, 'Whiskey', 'whiskey', '6945da3b4758d.jpg', 'Premium whiskey collection', 7, 1, '2025-12-19 18:43:07', '2025-12-19 23:05:31'),
(22, 2, 'Shisha', 'shisha', '6945da4e4f519.jpg', 'Flavored shisha selection', 8, 1, '2025-12-19 18:43:07', '2025-12-19 23:05:50');

-- --------------------------------------------------------

--
-- Table structure for table `customization_settings`
--

CREATE TABLE `customization_settings` (
  `id` int(11) NOT NULL,
  `restaurant_id` int(11) NOT NULL,
  `template_id` int(11) NOT NULL DEFAULT 1,
  `menu_title_color` varchar(7) DEFAULT '#000000',
  `menu_title_size` int(11) DEFAULT 24,
  `menu_title_font` varchar(100) DEFAULT 'Inter',
  `price_color` varchar(7) DEFAULT '#000000',
  `price_size` int(11) DEFAULT 18,
  `price_font` varchar(100) DEFAULT 'Inter',
  `description_color` varchar(7) DEFAULT '#666666',
  `description_size` int(11) DEFAULT 14,
  `description_font` varchar(100) DEFAULT 'Inter',
  `category_title_color` varchar(7) DEFAULT '#000000',
  `category_title_size` int(11) DEFAULT 20,
  `category_title_font` varchar(100) DEFAULT 'Inter',
  `background_color` varchar(7) DEFAULT '#FFFFFF',
  `header_background_color` varchar(7) DEFAULT '#FFFFFF',
  `primary_color` varchar(7) DEFAULT '#111111',
  `secondary_color` varchar(7) DEFAULT '#FFFFFF',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customization_settings`
--

INSERT INTO `customization_settings` (`id`, `restaurant_id`, `template_id`, `menu_title_color`, `menu_title_size`, `menu_title_font`, `price_color`, `price_size`, `price_font`, `description_color`, `description_size`, `description_font`, `category_title_color`, `category_title_size`, `category_title_font`, `background_color`, `header_background_color`, `primary_color`, `secondary_color`, `created_at`, `updated_at`) VALUES
(1, 2, 4, '#000000', 24, 'Inter', '#000000', 18, 'Inter', '#666666', 14, 'Inter', '#000000', 20, 'Inter', '#FFFFFF', '#FFFFFF', '#111111', '#FFFFFF', '2025-12-19 18:43:25', '2026-02-13 09:01:35'),
(2, 1, 4, '#000000', 24, 'Inter', '#000000', 18, 'Inter', '#666666', 14, 'Inter', '#000000', 20, 'Inter', '#FFFFFF', '#FFFFFF', '#111111', '#FFFFFF', '2025-12-19 18:45:58', '2026-02-13 09:01:35'),
(4, 3, 2, '#000000', 24, 'Inter', '#000000', 18, 'Inter', '#666666', 14, 'Inter', '#000000', 20, 'Inter', '#FFFFFF', '#FFFFFF', '#111111', '#FFFFFF', '2026-02-13 09:12:56', '2026-02-13 09:12:56');

-- --------------------------------------------------------

--
-- Table structure for table `managers`
--

CREATE TABLE `managers` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `restaurant_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `managers`
--

INSERT INTO `managers` (`id`, `username`, `email`, `password_hash`, `restaurant_id`, `created_at`, `updated_at`) VALUES
(1, 'skyhuz_manager', 'manager@skyhuz.com', '$2y$10$M6adKoSY2rV83qNJjMDb.e0fUo51hwTvoWau2NwDsPjEG904Zq0sa', 1, '2025-12-19 18:43:07', '2025-12-20 03:49:55'),
(2, 'lava_manager', 'jamesamaila07@gmail.com', '$2y$10$h0RdJU4tRyPL1Gi9vi6slOR6UT6G4pbO8JjCGP7z/11CeK6AzzdDK', 2, '2025-12-19 18:43:07', '2025-12-24 03:18:32'),
(3, 'heviewotelekki_manager', 'reservations@theviewlekki.com', '$2y$10$it3gLTDg5Xs66JtBM9XPs./c.WWAdxbEfbYOkKStFAe2RrdJEeCwa', 3, '2026-02-13 08:57:39', '2026-02-13 08:57:39');

-- --------------------------------------------------------

--
-- Table structure for table `menu_items`
--

CREATE TABLE `menu_items` (
  `id` int(11) NOT NULL,
  `restaurant_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_available` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `menu_items`
--

INSERT INTO `menu_items` (`id`, `restaurant_id`, `category_id`, `name`, `slug`, `description`, `price`, `image`, `display_order`, `is_available`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Meatballs', 'meatballs', 'Ground beef, rolled into a ball. Bread crumbs, minced onion, eggs, butter, and seasoning', 6000.00, NULL, 1, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(2, 1, 1, 'Prawn Cocktail', 'prawn-cocktail', 'Grilled chicken skewers served with prawns served on a bed of crisp lettuce in a cocktail glass and topped with our creamy cocktail sauce', 16000.00, NULL, 2, 0, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(3, 1, 1, 'Spring rolls / Samosas', 'spring-rolls-samosas', '', 10000.00, NULL, 3, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(4, 1, 1, 'Honey Garlic Glazed Wings', 'honey-garlic-glazed-wings', 'Chicken wings mixed in honey & garlic sauce', 15000.00, NULL, 4, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(5, 1, 1, 'Zesty Lime Shrimp Tacos', 'zesty-lime-shrimp-tacos', 'Grilled shrimp, avocado crema, and vegetables served on warm tortillas', 18000.00, NULL, 5, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(6, 1, 2, 'Chicken / Beef Tacos', 'chicken-beef-tacos', 'Beef or chicken in tortillas, topped with shredded lettuce and a guacamole sauce', 16000.00, NULL, 1, 0, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(7, 1, 3, 'Pancake Sundae', 'pancake-sundae', 'Pancakes topped with a scoop of ice cream, finished with rich syrup', 12000.00, NULL, 1, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(8, 1, 3, 'Pancake Stackwich', 'pancake-stackwich', 'Pancakes layered with bacon, melted cheddar', 16000.00, NULL, 2, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(9, 1, 4, 'Jollof Rice', 'jollof-rice', 'Rice slow-cooked in a rich blend of tomatoes, red pepper, garlic and spices', 8000.00, NULL, 1, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(10, 1, 4, 'Spin Hive\'s Special', 'spin-hives-special', 'Rice tossed with shrimp, sausage, chicken, veggies and mushrooms with seasonings', 20000.00, NULL, 2, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(11, 1, 4, 'Special Fried Rice', 'special-fried-rice', 'Rice, sausage, bacon, minced beef, vegetables, eggs, soy, garlic, and herbs', 10000.00, NULL, 3, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(12, 1, 4, 'Seafood Arancini Rice Balls', 'seafood-arancini-rice-balls', 'Fried balls of jollof rice stuffed with fillings and served with a spicy tomato dipping sauce', 25000.00, NULL, 4, 0, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(13, 1, 7, 'Pan-Seared Fish Fillet', 'pan-seared-fish-fillet', 'Delicately pan-seared fish fillet served with a lemon-butter sauce and seasonal sides', 22000.00, NULL, 1, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(14, 1, 7, 'Cottage Pie', 'cottage-pie', 'Minced beef, herbs, and vegetables under a mashed potato topping - baked golden', 20000.00, NULL, 2, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(15, 1, 7, 'Mashed Potato and Steak Bite', 'mashed-potato-and-steak-bite', 'Steak bites seared and served over a bed of creamy mashed potatoes', 30000.00, NULL, 3, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(16, 1, 7, 'Mashed Potato and Grilled Prawn', 'mashed-potato-and-grilled-prawn', 'Mashed potatoes blended with butter, cream, and seasoning finished with prawn toppings', 35000.00, NULL, 4, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(17, 1, 5, 'Egg Sauce', 'egg-sauce', 'Scrambled eggs simmered in a rich tomato and pepper sauce with a spicy tomato dipping sauce', 6500.00, NULL, 1, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(18, 1, 5, 'Chicken Butter Sauce', 'chicken-butter-sauce', 'Chicken marinated in garlic, then seared in butter, tossed in a creamy sauce, then finished with a squeeze of lemon juice', 14500.00, NULL, 2, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(19, 1, 5, 'Chicken Oyster Sauce', 'chicken-oyster-sauce', 'Chicken cooked in a silky oyster sauce glaze with bell peppers', 17500.00, NULL, 3, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(20, 1, 6, 'Yam Fries', 'yam-fries', 'Deep-fried yams, served hot with choice of sauce', 7500.00, NULL, 1, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(21, 1, 6, 'French Fries', 'french-fries', 'Crispy fries, perfectly salted and served hot', 7500.00, NULL, 2, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(22, 1, 8, 'Beef Chow Mein', 'beef-chow-mein', 'Noodles with vegetables, garlic, and ginger in rich soy and sugar-based sauce', 7000.00, NULL, 1, 0, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(23, 1, 8, 'Chicken Noodles', 'chicken-noodles', 'Grilled chicken with instant noodles, tossed in a nero-matuseosauce', 8500.00, NULL, 2, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(24, 1, 8, 'Protein Fused Noodles', 'protein-fused-noodles', 'Chicken breast strips, sausages, and mushrooms, fresh fried vegetables all tossed in the noodles', 10000.00, NULL, 3, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(25, 1, 8, 'Vegetable Noodles', 'vegetable-noodles', 'Vegetables stir-fried with noodles in a herb sauce', 6500.00, NULL, 4, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(26, 1, 9, 'Seafood Medley', 'seafood-medley', 'Pasta mixed in tomato sauce with prawns, calamari, shrimps, fish fillet, herbs, and chili flakes', 22500.00, NULL, 1, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(27, 1, 9, 'Bolognese Lasagna', 'bolognese-lasagna', 'Oven-baked layers of pasta, rich meat sauce, and creamy bechamel', 19500.00, NULL, 2, 0, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(28, 1, 9, 'Spaghetti Carbonara', 'spaghetti-carbonara', 'Pasta tossed in a sauce of eggs, Parmesan cheese, and a hint of garlic, finished with crispy bacon', 14500.00, NULL, 3, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(29, 1, 9, 'Spaghetti Bolognese', 'spaghetti-bolognese', 'Spaghetti served with meat sauce made from ground beef, tomatoes, garlic, and herbs', 17500.00, NULL, 4, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(30, 1, 9, 'Creamy Seafood Medley', 'creamy-seafood-medley', 'Linguine mixed with prawns, shrimps, fish fillet and calamari in a creamy sauce', 25000.00, NULL, 5, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(31, 1, 9, 'Chicken Carbonara Pasta', 'chicken-carbonara-pasta', 'Grilled chicken tossed with al dente pasta in a Parmesan and egg-based carbonara sauce and bacon', 16500.00, NULL, 6, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(32, 1, 10, 'Chicken Burger', 'chicken-burger', 'Grilled chicken breast layered with cheddar, cabbage, with fries', 20000.00, NULL, 1, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(33, 1, 10, 'Beef Burger', 'beef-burger', 'Grilled beef, layered with cheddar, onion and green pepper sauté and vegetables on a brioche bun', 18500.00, NULL, 2, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(34, 1, 10, 'Brunch Burger', 'brunch-burger', 'Beef layered with smoked bacon, cheddar, and egg', 25000.00, NULL, 3, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(35, 1, 11, 'Grilled Prawn', 'grilled-prawn', 'Prawns marinated in herbs, garlic, and lemon, then grilled', 20000.00, NULL, 1, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(36, 1, 11, 'Garlic Butter Prawn', 'garlic-butter-prawn', 'Prawns sautéed in rich garlic butter and fresh herbs', 20000.00, NULL, 2, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(37, 1, 12, 'Air-Fried Mackerel Fish', 'air-fried-mackerel-fish', 'Fresh Mackerel air-fried, crispy outside, tender inside', 25000.00, NULL, 1, 0, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(38, 1, 12, 'Chicken Wings or Drum stick', 'chicken-wings-or-drum-stick', 'Herb-marinated oven-roasted. Served tossed in chili sauce or sweet sauce', 10000.00, NULL, 2, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(39, 1, 13, 'Chicken Caesar Salad', 'chicken-caesar-salad', 'Grilled chicken layered on fresh lettuce and cabbage, Caesar dressing, croutons, and parmesan', 18500.00, NULL, 1, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(40, 1, 14, 'Club Sandwich', 'club-sandwich', 'Chicken, egg, lettuce, and tomatoes layered with artisan bread', 12500.00, NULL, 1, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(41, 2, 15, 'Chicken Spring Rolls', 'chicken-spring-rolls', 'Chicken stuffed rolls with mixed bell peppers and cabbage served with plum sauce', 100.00, '6945dee5937fd.jpg', 1, 1, '2025-12-19 18:43:07', '2026-02-11 13:10:35'),
(42, 2, 15, 'Grilled Chicken Wings', 'grilled-chicken-wings', 'Grilled Marinated Chicken Wings, Served With Homemade Chili Sauce.', 23000.00, '6945df24c6e77.jpg', 2, 1, '2025-12-19 18:43:07', '2025-12-19 23:26:28'),
(43, 2, 15, 'Lollipop Chicken', 'lollipop-chicken', 'Half boneless fried wings with your choice of spicy BBQ or honey mustard sauce.', 25000.00, '6945e61603f0b.jpg', 3, 1, '2025-12-19 18:43:07', '2025-12-19 23:56:06'),
(44, 2, 15, 'Caesar Chicken Sliders', 'caesar-chicken-sliders', 'marinated grilled chicken, Caesar sauce, lettuce, tomato, dill pickles, parmesan cheese.', 19000.00, '6945e63143e76.jpg', 4, 1, '2025-12-19 18:43:07', '2025-12-19 23:56:33'),
(45, 2, 15, 'Grilled Pettit Prawns', 'grilled-pettit-prawns', 'Grilled Medium Prawns Seasoned In Herb Sauce Served With Fresh Red Onions And Side Salad.', 35000.00, '6945e67a43210.jpg', 5, 1, '2025-12-19 18:43:07', '2025-12-19 23:57:46'),
(46, 2, 15, 'Dynamite Shrimp', 'dynamite-shrimp', 'Crispy, Fried Shrimps Coated In A Spicy Mayonnaise Dressing.', 25000.00, '6945e6919fc7f.jpg', 6, 1, '2025-12-19 18:43:07', '2025-12-19 23:58:09'),
(47, 2, 15, 'Dynamite Chicken', 'dynamite-chicken', 'Crispy, Golden-Brown Fried Chicken Served With Dynamite Sauce.', 25000.00, '6945e6daee3be.jpeg', 7, 1, '2025-12-19 18:43:07', '2025-12-19 23:59:22'),
(48, 2, 15, 'Opal Signature Snails', 'opal-signature-snails', 'Sauteed Snails With Mixed Bell Pepper In Nigerian Spice Served With Plantain Fingers.', 32000.00, '6945e735a03f8.jpg', 8, 1, '2025-12-19 18:43:07', '2025-12-20 00:00:53'),
(49, 2, 15, 'Goat Meat', 'goat-meat', 'Tender Goat Meat Sautéed With Nigerian Spices.', 28400.00, '6945e77099e77.jpg', 9, 1, '2025-12-19 18:43:07', '2025-12-20 00:01:52'),
(50, 2, 15, 'Peppered Shrimps', 'peppered-shrimps', 'Shrimps Toasted In Chili Peppered Sauce Green Pepper And Onions.', 38000.00, '6945e78c75af7.jpg', 10, 1, '2025-12-19 18:43:07', '2025-12-20 00:02:20'),
(51, 2, 15, 'Peppered Assorted Meat', 'peppered-assorted-meat', 'Peppered Nigerian Shaki, Gizzards, Tender Beef And Chicken Breast Served With Tomato And Red Onions.', 31700.00, '6945e7f2cac67.jpg', 11, 1, '2025-12-19 18:43:07', '2025-12-20 00:04:02'),
(52, 2, 15, 'Coconut Popcorn Shrimps', 'coconut-popcorn-shrimps', 'Breaded Shrimps, Deep Fried Served With Tartar And Cocktail Sauce', 35200.00, NULL, 12, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(53, 2, 15, 'Chicken Tender', 'chicken-tender', 'Deep-Fried Breaded Chicken Breast Served With Honey Mustard Sauce.', 30700.00, NULL, 13, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(54, 2, 16, 'Sweet Fried Potatoes', 'sweet-fried-potatoes', '', 8900.00, NULL, 1, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(55, 2, 16, 'French Fries', 'french-fries', '', 9600.00, NULL, 2, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(56, 2, 16, 'Chinese Fried Rice', 'chinese-fried-rice', '', 15200.00, NULL, 3, 1, '2025-12-19 18:43:07', '2025-12-20 13:19:16'),
(57, 2, 16, 'Steamed Rice', 'steamed-rice', '', 7000.00, NULL, 4, 1, '2025-12-19 18:43:07', '2025-12-20 13:21:01'),
(58, 2, 16, 'Plantain Fingers', 'plantain-fingers', '', 8500.00, NULL, 5, 1, '2025-12-19 18:43:07', '2025-12-20 13:22:04'),
(59, 2, 16, 'Yam Fingers', 'yam-fingers', '', 6500.00, NULL, 6, 1, '2025-12-19 18:43:07', '2025-12-20 13:22:52'),
(60, 2, 16, 'Ice Cream Cake', 'ice-cream-cake', '', 15200.00, NULL, 7, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(61, 2, 17, 'Fruit Salad & Chocolate Ice Cream', 'fruit-salad-chocolate-ice-cream', '', 14000.00, NULL, 1, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(62, 2, 17, 'Ice Cream Scoop', 'ice-cream-scoop', 'Chocolate Vanilla and Extra', 12000.00, NULL, 2, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(63, 2, 18, 'Don P Brut', 'don-p-brut', '', 1400000.00, NULL, 1, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(64, 2, 18, 'Don P Rose', 'don-p-rose', '', 1550000.00, NULL, 2, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(65, 2, 18, 'Ace of Spade', 'ace-of-spade', '', 1500000.00, NULL, 3, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(66, 2, 18, 'Cristal', 'cristal', '', 1300000.00, NULL, 4, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(67, 2, 19, 'Don Julio Magnum', 'don-julio-magnum', '', 2100000.00, NULL, 1, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(68, 2, 19, 'Don Julio 1942', 'don-julio-1942', '', 1200000.00, NULL, 2, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(69, 2, 19, 'Avion', 'avion', '', 900000.00, NULL, 3, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(70, 2, 19, 'Clase Azul', 'clase-azul', '', 1000000.00, NULL, 4, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(71, 2, 19, 'Don Julio 1942 Magnum', 'don-julio-1942-magnum', '', 2100000.00, NULL, 5, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(72, 2, 19, 'Casamigos 1L', 'casamigos-1l', '', 1200000.00, NULL, 6, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(73, 2, 19, 'Casamigos M', 'casamigos-m', '', 1250000.00, NULL, 7, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(74, 2, 19, 'Adiccion Reposado', 'adiccion-reposado', '', 1000000.00, NULL, 8, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(75, 2, 20, 'Hennessy XO', 'hennessy-xo', '', 1000000.00, NULL, 1, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(76, 2, 20, 'Martel XO', 'martel-xo', '', 950000.00, NULL, 2, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(77, 2, 21, 'Glen Fiddich 21', 'glen-fiddich-21', '', 1000000.00, NULL, 1, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(78, 2, 21, 'Glen Fiddich 23', 'glen-fiddich-23', '', 1300000.00, NULL, 2, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(79, 2, 21, 'Glen Fiddich 26', 'glen-fiddich-26', '', 2300000.00, NULL, 3, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(80, 2, 21, 'Glen Livet 21', 'glen-livet-21', '', 950000.00, NULL, 4, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(81, 2, 22, 'Iced Gum', 'iced-gum', '', 50000.00, NULL, 1, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(82, 2, 22, 'Magic Love', 'magic-love', '', 50000.00, NULL, 2, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(83, 2, 22, 'Love 66', 'love-66', '', 60000.00, NULL, 3, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(84, 2, 22, 'Strawberry', 'strawberry', '', 50000.00, NULL, 4, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(85, 2, 22, 'Strawberry and Mint', 'strawberry-and-mint', '', 50000.00, NULL, 5, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(86, 2, 22, 'Mixed Fruit', 'mixed-fruit', '', 50000.00, NULL, 6, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(87, 2, 22, 'Gum and Mint', 'gum-and-mint', '', 50000.00, NULL, 7, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(88, 2, 22, 'Gum', 'gum', '', 50000.00, NULL, 8, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(89, 2, 22, 'Lemon and Mint', 'lemon-and-mint', '', 50000.00, NULL, 9, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(90, 2, 22, 'Mint and Cream', 'mint-and-cream', '', 50000.00, NULL, 10, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(91, 2, 22, 'Grape and Mint', 'grape-and-mint', '', 50000.00, NULL, 11, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(92, 2, 22, 'Grape', 'grape', '', 50000.00, NULL, 12, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(93, 2, 22, 'Two Apple', 'two-apple', '', 50000.00, NULL, 13, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(94, 2, 22, 'Mint', 'mint', '', 50000.00, NULL, 14, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(95, 2, 22, 'Peach', 'peach', '', 50000.00, NULL, 15, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(96, 2, 22, 'Blueberry', 'blueberry', '', 50000.00, NULL, 16, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(97, 2, 22, 'Blueberry and Mint', 'blueberry-and-mint', '', 50000.00, NULL, 17, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(98, 2, 22, 'Mango', 'mango', '', 50000.00, NULL, 18, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(99, 2, 22, 'Watermelon', 'watermelon', '', 50000.00, NULL, 19, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(100, 2, 22, 'Watermelon and Mint', 'watermelon-and-mint', '', 50000.00, NULL, 20, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(101, 2, 22, 'Lady Killer', 'lady-killer', '', 50000.00, NULL, 21, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(102, 2, 22, 'Apple', 'apple', '', 50000.00, NULL, 22, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(103, 2, 22, 'Pineapple Fruit', 'pineapple-fruit', '', 50000.00, NULL, 23, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(104, 2, 22, 'Apple Fruit', 'apple-fruit', '', 50000.00, NULL, 24, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(105, 2, 22, 'Orange Fruit', 'orange-fruit', '', 50000.00, NULL, 25, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `restaurant_id`, `customer_name`, `customer_phone`, `customer_email`, `delivery_address`, `payment_method`, `status`, `subtotal`, `delivery_fee`, `tax`, `total`, `created_at`, `updated_at`) VALUES
(1, NULL, 2, 'carter tech', '91347593', 'mr.carter.tech07@gmail.com', 'Cf', NULL, 'pending', 42000.00, 0.00, 0.00, 42000.00, '2026-02-09 16:34:30', '2026-02-09 16:34:30'),
(2, NULL, 2, 'Abdulrahman Shittu', '08032336586', 'mr.carter.tech07@gmail.com', 'gdd', 'bank_transfer', 'pending', 42000.00, 0.00, 0.00, 42000.00, '2026-02-10 16:19:58', '2026-02-10 16:19:58'),
(3, NULL, 2, 'Abdulrahman Shittu', '08032336586', 'mr.carter.tech07@gmail.com', 'fsdg', 'bank_transfer', 'confirmed', 19000.00, 0.00, 0.00, 19000.00, '2026-02-10 16:36:59', '2026-02-10 17:03:22'),
(4, NULL, 2, 'Abdulrahman Shittu', '08032336586', 'mr.carter.tech07@gmail.com', 'jhh', 'bank_transfer', 'pending', 85000.00, 0.00, 0.00, 85000.00, '2026-02-10 16:51:05', '2026-02-10 16:51:05'),
(5, NULL, 2, 'Abdulrahman Shittu', '08032336586', 'mr.carter.tech07@gmail.com', 'dgddg', 'bank_transfer', 'pending', 230000.00, 0.00, 0.00, 230000.00, '2026-02-10 17:26:01', '2026-02-10 17:26:01'),
(6, NULL, 2, 'Abdulrahman Shittu', '08032336586', 'mr.carter.tech07@gmail.com', 'dgtggdg', 'paystack', 'pending', 44000.00, 0.00, 0.00, 44000.00, '2026-02-10 17:42:17', '2026-02-10 17:42:17'),
(7, NULL, 2, 'Abdulrahman Shittu', '08032336586', 'mr.carter.tech07@gmail.com', 'ffdfdgdgd', 'paystack', 'pending', 44000.00, 0.00, 0.00, 44000.00, '2026-02-10 18:09:27', '2026-02-10 18:09:27'),
(8, NULL, 2, 'Abdulrahman Shittu', '08032336586', 'mr.carter.tech07@gmail.com', 'ffdfdgdgd', 'bank_transfer', 'pending', 44000.00, 0.00, 0.00, 44000.00, '2026-02-10 18:10:09', '2026-02-10 18:10:09'),
(9, 'KMK9M2QM', 2, 'Abdulrahman Shittu', '08032336586', 'sigsol2024@gmail.com', 'aafs', 'bank_transfer', 'confirmed', 100.00, 0.00, 0.00, 100.00, '2026-02-11 13:13:02', '2026-02-11 13:13:15'),
(10, 'MYC6DN8Z', 2, 'Abdulrahman Shittu', '08032336586', 'sigsol2024@gmail.com', 'sfsf', 'paystack', 'pending', 100.00, 0.00, 0.00, 100.00, '2026-02-11 13:15:40', '2026-02-11 13:15:40'),
(11, 'BYRO5UG8', 2, 'Abdulrahman Shittu', '08032336586', 'sigsol2024@gmail.com', 'sfsfsf', 'paystack', 'confirmed', 100.00, 0.00, 0.00, 100.00, '2026-02-11 13:25:23', '2026-02-11 13:25:23'),
(12, '0NQ0334D', 2, 'Abdulrahman Shittu', '08032336586', 'mr.carter.tech07@gmail.com', 'fsfs', 'bank_transfer', 'confirmed', 100.00, 0.00, 0.00, 100.00, '2026-02-12 23:30:50', '2026-02-12 23:31:55');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `menu_item_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `menu_item_id`, `name`, `price`, `quantity`, `created_at`) VALUES
(1, 1, 41, 'Chicken Spring Rolls', 19000.00, 1, '2026-02-09 16:34:30'),
(2, 1, 42, 'Grilled Chicken Wings', 23000.00, 1, '2026-02-09 16:34:30'),
(3, 2, 41, 'Chicken Spring Rolls', 19000.00, 1, '2026-02-10 16:19:58'),
(4, 2, 42, 'Grilled Chicken Wings', 23000.00, 1, '2026-02-10 16:19:58'),
(5, 3, 41, 'Chicken Spring Rolls', 19000.00, 1, '2026-02-10 16:36:59'),
(6, 4, 45, 'Grilled Pettit Prawns', 35000.00, 1, '2026-02-10 16:51:05'),
(7, 4, 46, 'Dynamite Shrimp', 25000.00, 2, '2026-02-10 16:51:05'),
(8, 5, 42, 'Grilled Chicken Wings', 23000.00, 10, '2026-02-10 17:26:01'),
(9, 6, 43, 'Lollipop Chicken', 25000.00, 1, '2026-02-10 17:42:17'),
(10, 6, 44, 'Caesar Chicken Sliders', 19000.00, 1, '2026-02-10 17:42:17'),
(11, 7, 43, 'Lollipop Chicken', 25000.00, 1, '2026-02-10 18:09:27'),
(12, 7, 44, 'Caesar Chicken Sliders', 19000.00, 1, '2026-02-10 18:09:27'),
(13, 8, 43, 'Lollipop Chicken', 25000.00, 1, '2026-02-10 18:10:09'),
(14, 8, 44, 'Caesar Chicken Sliders', 19000.00, 1, '2026-02-10 18:10:09'),
(15, 9, 41, 'Chicken Spring Rolls', 100.00, 1, '2026-02-11 13:13:02'),
(16, 10, 41, 'Chicken Spring Rolls', 100.00, 1, '2026-02-11 13:15:40'),
(17, 11, 41, 'Chicken Spring Rolls', 100.00, 1, '2026-02-11 13:25:23'),
(18, 12, 41, 'Chicken Spring Rolls', 100.00, 1, '2026-02-12 23:30:50');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `restaurant_id` int(11) NOT NULL,
  `subscription_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) NOT NULL DEFAULT 'NGN',
  `payment_gateway` enum('paystack','flutterwave','manual') NOT NULL,
  `transaction_reference` varchar(100) DEFAULT NULL COMMENT 'Gateway reference',
  `gateway_response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Full gateway response',
  `status` enum('pending','success','failed','refunded') NOT NULL DEFAULT 'pending',
  `paid_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `restaurant_id`, `subscription_id`, `amount`, `currency`, `payment_gateway`, `transaction_reference`, `gateway_response`, `status`, `paid_at`, `created_at`) VALUES
(1, 2, 1, 50.00, 'NGN', 'paystack', NULL, NULL, 'pending', NULL, '2025-12-24 03:13:58'),
(2, 2, 1, 50.00, 'NGN', 'paystack', 'PS_1766546254_d79a5efb6231a96e', NULL, 'pending', NULL, '2025-12-24 03:17:34'),
(3, 2, 1, 100.00, 'NGN', 'paystack', 'PS_1766546331_90dc48f6b6afa69e', NULL, 'success', '2025-12-24 17:08:48', '2025-12-24 03:18:51'),
(4, 2, 1, 200.00, 'NGN', 'paystack', 'PS_1766624250_59406dae47c1dd0f', NULL, 'pending', NULL, '2025-12-25 00:57:30'),
(5, 2, 1, 220.00, 'NGN', 'paystack', 'PS_1766624771_b04c1caf52ed9745', NULL, 'pending', NULL, '2025-12-25 01:06:11');

-- --------------------------------------------------------

--
-- Table structure for table `payment_settings`
--

CREATE TABLE `payment_settings` (
  `id` int(11) NOT NULL,
  `gateway` varchar(50) NOT NULL COMMENT 'paystack or flutterwave',
  `is_active` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Enable/disable gateway',
  `test_mode` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Use test or live keys',
  `public_key_live` varchar(255) DEFAULT NULL COMMENT 'Live public key',
  `secret_key_live` text DEFAULT NULL COMMENT 'Live secret key (encrypted)',
  `webhook_secret_live` varchar(255) DEFAULT NULL COMMENT 'Live webhook secret',
  `public_key_test` varchar(255) DEFAULT NULL COMMENT 'Test public key',
  `secret_key_test` text DEFAULT NULL COMMENT 'Test secret key (encrypted)',
  `webhook_secret_test` varchar(255) DEFAULT NULL COMMENT 'Test webhook secret',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payment_settings`
--

INSERT INTO `payment_settings` (`id`, `gateway`, `is_active`, `test_mode`, `public_key_live`, `secret_key_live`, `webhook_secret_live`, `public_key_test`, `secret_key_test`, `webhook_secret_test`, `created_at`, `updated_at`) VALUES
(1, 'paystack', 1, 0, 'pk_live_acf5bb359c73ca5492ff2a65d28f3143edb25d49', 'fe9ffSaWR69MXqxx55sQ6jo6T2J4bi9INjU1Rm9yd0FqQU1Vc1BkWUYwLzB1MTY5NnBVNjErWitOM1R4RWpxZkNJa2xvTHRQVEVCMTVmbVRvRjUrKzZZUDhCZzRtV2FsOEdxOTUrNFE9PQ==', '', 'sigsol2024', '1aMJkPa6LL4sKIi8NwaMkjo6MEc3OHEycTR4L2ZrUFUwSFFlMXBkNnFFN2FFekNhWHhWd3UydWd1WGE5QT0=', '', '2025-12-24 02:38:31', '2025-12-24 03:13:14'),
(2, 'flutterwave', 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-24 02:38:31', '2025-12-24 02:38:31');

-- --------------------------------------------------------

--
-- Table structure for table `pending_bank_transfers`
--

CREATE TABLE `pending_bank_transfers` (
  `id` int(11) NOT NULL,
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pending_bank_transfers`
--

INSERT INTO `pending_bank_transfers` (`id`, `token`, `restaurant_id`, `payment_type`, `reservation_id`, `cart_json`, `customer_name`, `customer_phone`, `customer_email`, `delivery_address`, `subtotal`, `delivery_fee`, `tax`, `total`, `created_at`) VALUES
(2, '04a0e81ab38e7e08216b95d376fa6742ac0c5388c16cc6c4', 2, 'order', NULL, '[{\"id\":41,\"name\":\"Chicken Spring Rolls\",\"price\":100,\"image\":\"6945dee5937fd.jpg\",\"quantity\":1}]', 'Abdulrahman Shittu', '08032336586', 'sigsol2024@gmail.com', 'aaaf', 100.00, 0.00, 0.00, 100.00, '2026-02-11 13:13:49');

-- --------------------------------------------------------

--
-- Table structure for table `pending_online_payments`
--

CREATE TABLE `pending_online_payments` (
  `id` int(11) NOT NULL,
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pending_online_payments`
--

INSERT INTO `pending_online_payments` (`id`, `reference`, `restaurant_id`, `payment_type`, `reservation_id`, `gateway`, `cart_json`, `customer_name`, `customer_phone`, `customer_email`, `delivery_address`, `subtotal`, `delivery_fee`, `tax`, `total`, `created_at`) VALUES
(1, 'POP_1770815548_101794a814066c6c', 2, 'order', NULL, 'paystack', '[{\"id\":41,\"name\":\"Chicken Spring Rolls\",\"price\":100,\"image\":\"6945dee5937fd.jpg\",\"quantity\":1}]', 'Abdulrahman Shittu', '08032336586', 'sigsol2024@gmail.com', 'aafs', 100.00, 0.00, 0.00, 100.00, '2026-02-11 13:12:28');

-- --------------------------------------------------------

--
-- Table structure for table `qr_code_scans`
--

CREATE TABLE `qr_code_scans` (
  `id` int(11) NOT NULL,
  `restaurant_id` int(11) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `device_type` varchar(50) DEFAULT NULL,
  `browser` varchar(100) DEFAULT NULL,
  `os` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `scanned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `qr_code_scans`
--

INSERT INTO `qr_code_scans` (`id`, `restaurant_id`, `ip_address`, `user_agent`, `device_type`, `browser`, `os`, `country`, `city`, `latitude`, `longitude`, `scanned_at`) VALUES
(1, 2, '197.211.59.73', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', 'Mobile', 'Chrome', 'Linux', 'Nigeria', 'Lagos', 6.44740000, 3.39030000, '2025-12-24 00:07:30'),
(2, 2, '197.211.59.73', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', 'Mobile', 'Chrome', 'Linux', 'Nigeria', 'Lagos', 6.44740000, 3.39030000, '2025-12-24 00:11:57');

-- --------------------------------------------------------

--
-- Table structure for table `qr_templates`
--

CREATE TABLE `qr_templates` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `preview_image` varchar(255) DEFAULT NULL,
  `has_text` tinyint(1) DEFAULT 0,
  `config_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`config_json`)),
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `qr_templates`
--

INSERT INTO `qr_templates` (`id`, `name`, `description`, `preview_image`, `has_text`, `config_json`, `is_active`, `created_at`, `updated_at`) VALUES
(16, 'SG QR WITH LOGO', 'SG QR WITH LOGO', NULL, 1, '{\"pattern\":\"square\",\"eyes\":\"square\",\"frame\":{\"type\":\"rounded\",\"text\":\"SCAN ME\",\"color\":\"#000000\",\"text_color\":\"#ffffff\",\"text_size\":14,\"bg_enabled\":true,\"bg_color\":\"#000000\"},\"colors\":{\"foreground\":\"#000000\",\"background\":\"#ffffff\"},\"logo\":{\"enabled\":true,\"size\":0.200000000000000011102230246251565404236316680908203125,\"center_only\":true}}', 1, '2025-12-24 00:11:24', '2025-12-24 00:11:24');

-- --------------------------------------------------------

--
-- Table structure for table `restaurants`
--

CREATE TABLE `restaurants` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `hero_image` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `whatsapp_link` varchar(255) DEFAULT NULL,
  `instagram_url` varchar(255) DEFAULT NULL,
  `facebook_url` varchar(255) DEFAULT NULL,
  `twitter_url` varchar(255) DEFAULT NULL,
  `map_latitude` decimal(10,8) DEFAULT NULL,
  `map_longitude` decimal(11,8) DEFAULT NULL,
  `header_menu_items` text DEFAULT NULL,
  `footer_content` text DEFAULT NULL,
  `manager_email` varchar(255) DEFAULT NULL,
  `google_rating` decimal(3,1) DEFAULT 4.5,
  `rating_source` varchar(50) DEFAULT 'Google',
  `template_id` int(11) DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1,
  `available_items_count` int(11) DEFAULT 0,
  `unavailable_items_count` int(11) DEFAULT 0,
  `subscription_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `restaurants`
--

INSERT INTO `restaurants` (`id`, `name`, `slug`, `logo`, `hero_image`, `description`, `phone`, `email`, `address`, `website`, `whatsapp_link`, `instagram_url`, `facebook_url`, `twitter_url`, `map_latitude`, `map_longitude`, `header_menu_items`, `footer_content`, `manager_email`, `google_rating`, `rating_source`, `template_id`, `is_active`, `available_items_count`, `unavailable_items_count`, `subscription_id`, `created_at`, `updated_at`) VALUES
(1, 'Skyhuz', 'skyhuz', NULL, NULL, 'Wash and Chill all in one place', '0707 581 7419', NULL, 'G & K mall, Opposite Etiosa Maternal And Child Care Center, Ogombo Road, Ajah.', NULL, NULL, NULL, NULL, NULL, 6.46278000, 3.58594000, '[\n  {\"label\": \"Menu\", \"url\": \"#menu\"},\n  {\"label\": \"News\", \"url\": \"#news\"}\n]', 'At Skyhuz, our story began with a simple love for great service. Founded in 2025 by friends and food enthusiasts.\nOur mission is to bring fun and relaxation to the regular way of doing chores.\nJoin us at Skyhuz and taste the difference passion and quality make.', 'manager@skyhuz.com', 4.5, 'Google', 4, 1, 34, 6, NULL, '2025-12-19 18:43:07', '2026-02-09 12:38:13'),
(2, 'LAVA', 'lava', '69459eb555362.jpg', '69459edb896e3.png', 'Premium dining experience with exquisite cuisine and fine beverages', '+234 800 000 0000', 'info@lava.com', 'LAVA., 5 Adetokunbu Ademola Street, Victoria Island', NULL, '', NULL, NULL, NULL, NULL, NULL, '[\r\n  {\"label\": \"Menu\", \"url\": \"#menu\"},\r\n  {\"label\": \"Drinks\", \"url\": \"#drinks\"}\r\n]', NULL, 'jamesamaila07@gmail.com', 4.5, 'Google', 4, 1, 65, 0, 1, '2025-12-19 18:43:07', '2026-02-13 09:27:23'),
(3, 'Theview Hotel Lekki', 'theview-hotel', '698ee78360beb.jpg', '698ee783613af.jpg', '', '+23490 9091 3608', 'reservations@theviewlekki.com', '1, Godwin Omene Street, Chief Collins Uchidiuno, Off Fola Osibo, Lekki Phase 1, Lagos, Nigeria', NULL, 'https://wa.link/g1n8bq', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'reservations@theviewlekki.com', 4.5, 'Google', 2, 1, 0, 0, 2, '2026-02-13 08:57:39', '2026-02-13 09:12:56');

-- --------------------------------------------------------

--
-- Table structure for table `restaurant_payment_settings`
--

CREATE TABLE `restaurant_payment_settings` (
  `id` int(11) NOT NULL,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `restaurant_payment_settings`
--

INSERT INTO `restaurant_payment_settings` (`id`, `restaurant_id`, `gateway`, `is_active`, `test_mode`, `public_key_test`, `secret_key_test`, `webhook_secret_test`, `public_key_live`, `secret_key_live`, `webhook_secret_live`, `bank_name`, `account_number`, `account_name`, `created_at`, `updated_at`) VALUES
(1, 2, 'bank_transfer', 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, 'access bank', '8247865824847585', 'Lava Fly', '2026-02-09 17:46:32', '2026-02-09 17:46:32'),
(2, 2, 'paystack', 1, 0, '', NULL, '', 'pk_live_acf5bb359c73ca5492ff2a65d28f3143edb25d49', '2wFU5zuDj4i0Tppls2j1fjo6K2w2YUhLNXBvS2YyVzdIdFJlTHE3ZXRjMEV2R2RTZDhqVzk2eUtoNHl2aHRRZGRaZTVLR1JrbHJiTlVtVVE3ZXNSMkZzZ3lWdTh5aVppWUc3aGJmWUE9PQ==', '', NULL, NULL, NULL, '2026-02-10 17:30:59', '2026-02-10 17:31:43');

-- --------------------------------------------------------

--
-- Table structure for table `restaurant_qr_codes`
--

CREATE TABLE `restaurant_qr_codes` (
  `id` int(11) NOT NULL,
  `restaurant_id` int(11) NOT NULL,
  `qr_template_id` int(11) DEFAULT 1,
  `override_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`override_json`)),
  `final_config_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`final_config_json`)),
  `background_color` varchar(7) DEFAULT '#FFFFFF',
  `qr_color` varchar(7) DEFAULT '#000000',
  `text_content` text DEFAULT NULL,
  `text_color` varchar(7) DEFAULT '#000000',
  `text_size` int(11) DEFAULT 16,
  `text_font` varchar(100) DEFAULT 'Arial',
  `qr_size` int(11) DEFAULT 300,
  `margin` int(11) DEFAULT 20,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `restaurant_qr_codes`
--

INSERT INTO `restaurant_qr_codes` (`id`, `restaurant_id`, `qr_template_id`, `override_json`, `final_config_json`, `background_color`, `qr_color`, `text_content`, `text_color`, `text_size`, `text_font`, `qr_size`, `margin`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, NULL, NULL, '#FFFFFF', '#000000', 'Scan to view menu', '#000000', 16, 'Arial', 300, 20, 1, '2025-12-22 18:18:47', '2025-12-22 18:18:47'),
(2, 2, 16, '{\"colors\":{\"foreground\":\"#000000\",\"background\":\"#ffffff\"},\"text_content\":\"\",\"text_color\":\"#000000\",\"text_size\":16}', '{\"pattern\":\"square\",\"eyes\":\"square\",\"frame\":{\"type\":\"rounded\",\"text\":\"SCAN ME\",\"color\":\"#000000\",\"text_color\":\"#ffffff\",\"text_size\":14,\"bg_enabled\":true,\"bg_color\":\"#000000\"},\"colors\":{\"foreground\":\"#000000\",\"background\":\"#ffffff\"},\"logo\":{\"enabled\":true,\"size\":0.200000000000000011102230246251565404236316680908203125,\"center_only\":true}}', '#ffffff', '#000000', '', '#000000', 16, 'Arial', 300, 20, 1, '2025-12-22 18:18:47', '2025-12-24 00:11:36');

-- --------------------------------------------------------

--
-- Table structure for table `restaurant_reservation_settings`
--

CREATE TABLE `restaurant_reservation_settings` (
  `id` int(11) NOT NULL,
  `restaurant_id` int(11) NOT NULL,
  `deposit_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `restaurant_reservation_settings`
--

INSERT INTO `restaurant_reservation_settings` (`id`, `restaurant_id`, `deposit_amount`, `created_at`, `updated_at`) VALUES
(1, 2, 150.00, '2026-02-12 04:52:44', '2026-02-12 15:48:17');

-- --------------------------------------------------------

--
-- Table structure for table `site_settings`
--

CREATE TABLE `site_settings` (
  `id` int(11) NOT NULL,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `site_settings`
--

INSERT INTO `site_settings` (`id`, `site_name`, `site_logo`, `favicon`, `contact_sales_email`, `contact_sales_phone`, `contact_support_email`, `contact_support_phone`, `contact_partners_email`, `contact_form_recipient`, `contact_hq_title`, `contact_hq_address`, `contact_map_embed`, `contact_social_facebook`, `contact_social_twitter`, `contact_social_instagram`, `created_at`, `updated_at`) VALUES
(1, 'Resmenu', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-12 23:18:09', '2026-02-12 23:18:09');

-- --------------------------------------------------------

--
-- Table structure for table `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` int(11) NOT NULL,
  `restaurant_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `billing_cycle` enum('monthly','annual') NOT NULL DEFAULT 'monthly',
  `status` enum('trial','active','expired','cancelled','pending') NOT NULL DEFAULT 'trial',
  `trial_ends_at` datetime DEFAULT NULL COMMENT '7 days from creation',
  `current_period_start` datetime DEFAULT NULL,
  `current_period_end` datetime DEFAULT NULL,
  `cancelled_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subscriptions`
--

INSERT INTO `subscriptions` (`id`, `restaurant_id`, `plan_id`, `billing_cycle`, `status`, `trial_ends_at`, `current_period_start`, `current_period_end`, `cancelled_at`, `created_at`, `updated_at`) VALUES
(1, 2, 3, 'monthly', 'pending', NULL, '2025-12-24 17:08:48', '2026-01-24 17:08:48', NULL, '2025-12-24 03:13:58', '2025-12-25 01:06:11'),
(2, 3, 1, 'monthly', 'trial', '2026-02-20 08:57:39', NULL, NULL, NULL, '2026-02-13 08:57:39', '2026-02-13 08:57:39');

-- --------------------------------------------------------

--
-- Table structure for table `subscription_emails`
--

CREATE TABLE `subscription_emails` (
  `id` int(11) NOT NULL,
  `subscription_id` int(11) NOT NULL,
  `email_type` varchar(50) NOT NULL COMMENT 'trial_ending, payment_reminder, payment_success, expired',
  `days_before` int(11) DEFAULT NULL COMMENT '30, 15, 7, 3, 1 for reminders',
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subscription_change_requests`
--

CREATE TABLE `subscription_change_requests` (
  `id` int(11) NOT NULL,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `id` int(11) NOT NULL,
  `user_type` enum('admin','manager') NOT NULL,
  `user_id` int(11) NOT NULL,
  `identifier` varchar(191) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `request_ip` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subscription_plans`
--

CREATE TABLE `subscription_plans` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL COMMENT 'Basic, Professional, Enterprise',
  `slug` varchar(50) NOT NULL COMMENT 'basic, professional, enterprise',
  `description` text DEFAULT NULL,
  `monthly_price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Price in NGN',
  `annual_price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Price in NGN (20% discount)',
  `max_categories` int(11) NOT NULL DEFAULT 5 COMMENT '-1 for unlimited',
  `max_menu_items` int(11) NOT NULL DEFAULT 50 COMMENT '-1 for unlimited',
  `max_qr_styles` int(11) NOT NULL DEFAULT 3 COMMENT '-1 for unlimited',
  `max_templates` int(11) NOT NULL DEFAULT 3 COMMENT '-1 for unlimited',
  `features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Additional features as JSON',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subscription_plans`
--

INSERT INTO `subscription_plans` (`id`, `name`, `slug`, `description`, `monthly_price`, `annual_price`, `max_categories`, `max_menu_items`, `max_qr_styles`, `max_templates`, `features`, `is_active`, `display_order`, `created_at`, `updated_at`) VALUES
(1, 'Basic', 'basic', 'Perfect for small restaurants just getting started with digital menus.', 100.00, 96000.00, 5, 50, 3, 3, '{\"priority_support\":false,\"custom_domain\":false,\"analytics_advanced\":false}', 1, 1, '2025-12-24 02:38:31', '2025-12-24 03:18:12'),
(2, 'Professional', 'professional', 'Ideal for growing restaurants with multiple menu categories.', 200.00, 240000.00, 20, 300, 10, 7, '{\"priority_support\":true,\"custom_domain\":false,\"analytics_advanced\":true}', 1, 2, '2025-12-24 02:38:31', '2025-12-25 00:56:35'),
(3, 'Enterprise', 'enterprise', 'Full-featured solution for large restaurants and chains.', 220.00, 480000.00, -1, -1, -1, -1, '{\"priority_support\":true,\"custom_domain\":true,\"analytics_advanced\":true}', 1, 3, '2025-12-24 02:38:31', '2025-12-25 01:05:39');

-- --------------------------------------------------------

--
-- Table structure for table `table_inventory_daily`
--

CREATE TABLE `table_inventory_daily` (
  `id` int(11) NOT NULL,
  `restaurant_id` int(11) NOT NULL,
  `inventory_date` date NOT NULL,
  `total_tables` int(11) NOT NULL DEFAULT 10,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `table_inventory_daily`
--

INSERT INTO `table_inventory_daily` (`id`, `restaurant_id`, `inventory_date`, `total_tables`, `created_at`, `updated_at`) VALUES
(1, 2, '2026-02-25', 10, '2026-02-12 22:10:57', '2026-02-12 22:49:21'),
(3, 2, '2026-02-19', 59, '2026-02-12 22:55:24', '2026-02-12 22:55:24'),
(4, 2, '2026-02-20', 59, '2026-02-12 22:55:24', '2026-02-12 22:55:24'),
(5, 2, '2026-02-21', 59, '2026-02-12 22:55:24', '2026-02-12 22:55:24'),
(6, 2, '2026-02-22', 59, '2026-02-12 22:55:24', '2026-02-12 22:55:24');

-- --------------------------------------------------------

--
-- Table structure for table `table_reservations`
--

CREATE TABLE `table_reservations` (
  `id` int(11) NOT NULL,
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
  `deposit_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `deposit_paid` tinyint(1) NOT NULL DEFAULT 0,
  `status` varchar(50) NOT NULL DEFAULT 'pending' COMMENT 'pending, confirmed, cancelled, completed',
  `is_walkin` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `table_reservations`
--

INSERT INTO `table_reservations` (`id`, `reservation_number`, `restaurant_id`, `reservation_date`, `reservation_time`, `party_size`, `guest_name`, `guest_email`, `guest_phone`, `special_occasion`, `notes`, `deposit_amount`, `deposit_paid`, `status`, `is_walkin`, `created_at`, `updated_at`) VALUES
(1, NULL, 2, '2026-02-13', '19:00:00', 3, 'carter tech', 'mr.carter.tech07@gmail.com', '91347593', 'ANNIVERSARY', 'Vvghh', 0.00, 0, 'confirmed', 0, '2026-02-12 02:03:26', '2026-02-12 04:53:03'),
(2, NULL, 2, '2026-02-13', '18:30:00', 3, 'Abdulrahman Shittu', 'mr.carter.tech07@gmail.com', '08032336586', 'BIRTHDAY', 'sfsfs', 0.00, 0, 'pending', 0, '2026-02-12 04:20:31', '2026-02-12 04:20:31'),
(3, NULL, 2, '2026-02-13', '19:00:00', 2, 'Abdulrahman Shittu', 'mr.carter.tech07@gmail.com', '08032336586', 'ANNIVERSARY', 'xg', 599.00, 1, 'pending', 0, '2026-02-12 04:53:31', '2026-02-12 04:53:48'),
(4, NULL, 2, '2026-02-13', '18:00:00', 2, 'Abdulrahman Shittu', 'mr.carter.tech07@gmail.com', '08032336586', 'ANNIVERSARY', 'sfs', 100.00, 1, 'pending', 0, '2026-02-12 15:44:37', '2026-02-12 15:46:13'),
(5, NULL, 2, '2026-02-13', '21:30:00', 2, 'BOULEVARD INTEGRATED SERVICES LIMITED', 'mr.carter.tech07@gmail.com', '08032336586', NULL, 'zs', 150.00, 1, 'pending', 0, '2026-02-12 15:48:50', '2026-02-12 15:49:40'),
(6, NULL, 2, '2026-02-19', '18:30:00', 1, 'dada', 'ad@fj.com', '2224244255', 'ANNIVERSARY', 'sfsf', 150.00, 1, 'confirmed', 0, '2026-02-12 22:08:54', '2026-02-12 22:09:53'),
(7, '4GLULEF9', 2, '2026-02-13', '18:30:00', 1, 'Abdulrahman Shittu', 'mr.carter.tech07@gmail.com', '08032336586', NULL, 'z', 150.00, 1, 'confirmed', 0, '2026-02-12 23:32:46', '2026-02-12 23:32:54');

-- --------------------------------------------------------

--
-- Table structure for table `templates`
--

CREATE TABLE `templates` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `preview_image` varchar(255) DEFAULT NULL,
  `listing_image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `templates`
--

INSERT INTO `templates` (`id`, `name`, `description`, `preview_image`, `listing_image`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Framer Design', 'Clean, modern design with rounded corners and elegant typography', NULL, NULL, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(2, 'Salt and Social', 'Modern restaurant template with Tailwind CSS, featuring hero sections and featured items', NULL, NULL, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(3, 'Dark Navy Gradient', 'Dark navy blue gradient background template with red gradient category text and white cards', NULL, NULL, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(4, 'The Gourmet Grill', 'Premium dark-themed design with Epilogue font, herb pattern, and flame-grilled aesthetic', NULL, NULL, 1, '2026-02-09 12:24:42', '2026-02-13 09:24:58');

-- --------------------------------------------------------

--
-- Table structure for table `template_customizations`
--

CREATE TABLE `template_customizations` (
  `id` int(11) NOT NULL,
  `template_id` int(11) NOT NULL,
  `menu_title_color` varchar(7) DEFAULT '#000000',
  `menu_title_size` int(11) DEFAULT 24,
  `menu_title_font` varchar(50) DEFAULT 'Inter',
  `price_color` varchar(7) DEFAULT '#000000',
  `price_size` int(11) DEFAULT 18,
  `price_font` varchar(50) DEFAULT 'Inter',
  `description_color` varchar(7) DEFAULT '#666666',
  `description_size` int(11) DEFAULT 14,
  `description_font` varchar(50) DEFAULT 'Inter',
  `category_title_color` varchar(7) DEFAULT '#000000',
  `category_title_size` int(11) DEFAULT 20,
  `category_title_font` varchar(50) DEFAULT 'Inter',
  `background_color` varchar(7) DEFAULT '#fffffc',
  `header_background_color` varchar(7) DEFAULT '#fffffc',
  `primary_color` varchar(7) DEFAULT '#111111',
  `secondary_color` varchar(7) DEFAULT '#FFFFFF',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `template_customizations`
--

INSERT INTO `template_customizations` (`id`, `template_id`, `menu_title_color`, `menu_title_size`, `menu_title_font`, `price_color`, `price_size`, `price_font`, `description_color`, `description_size`, `description_font`, `category_title_color`, `category_title_size`, `category_title_font`, `background_color`, `header_background_color`, `primary_color`, `secondary_color`, `created_at`, `updated_at`) VALUES
(1, 4, '#121212', 24, 'Epilogue', '#f20d0d', 18, 'Epilogue', '#666666', 14, 'Epilogue', '#121212', 20, 'Epilogue', '#f8f5f5', '#121212', '#f20d0d', '#FFFFFF', '2026-02-09 12:24:42', '2026-02-13 09:24:58'),
(20, 1, '#1A1A1A', 24, 'Inter', '#1A1A1A', 18, 'Inter', '#666666', 14, 'Inter', '#1A1A1A', 20, 'Inter', '#FFFFFF', '#FFFFFF', '#1A1A1A', '#FAF3E6', '2026-02-13 09:22:33', '2026-02-13 09:24:58'),
(21, 2, '#1A1A1A', 24, 'Inter', '#ea2a33', 18, 'Inter', '#666666', 14, 'Inter', '#1A1A1A', 20, 'Inter', '#f8f6f6', '#f8f6f6', '#ea2a33', '#FFFFFF', '2026-02-13 09:22:33', '2026-02-13 09:24:58'),
(22, 3, '#1A1A1A', 24, 'Inter', '#ea2a33', 18, 'Inter', '#666666', 14, 'Inter', '#1A1A1A', 20, 'Inter', '#f8f6f6', '#f8f6f6', '#ea2a33', '#FFFFFF', '2026-02-13 09:22:33', '2026-02-13 09:24:58');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_restaurant_slug` (`restaurant_id`,`slug`),
  ADD KEY `idx_restaurant_id` (`restaurant_id`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_display_order` (`display_order`);

--
-- Indexes for table `customization_settings`
--
ALTER TABLE `customization_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `restaurant_template` (`restaurant_id`,`template_id`);

--
-- Indexes for table `managers`
--
ALTER TABLE `managers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_restaurant_id` (`restaurant_id`);

--
-- Indexes for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_restaurant_category_slug` (`restaurant_id`,`category_id`,`slug`),
  ADD KEY `idx_restaurant_id` (`restaurant_id`),
  ADD KEY `idx_category_id` (`category_id`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_display_order` (`display_order`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `restaurant_id` (`restaurant_id`),
  ADD KEY `status` (`status`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `menu_item_id` (`menu_item_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `restaurant_id` (`restaurant_id`),
  ADD KEY `subscription_id` (`subscription_id`),
  ADD KEY `status` (`status`),
  ADD KEY `transaction_reference` (`transaction_reference`);

--
-- Indexes for table `payment_settings`
--
ALTER TABLE `payment_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `gateway` (`gateway`);

--
-- Indexes for table `pending_bank_transfers`
--
ALTER TABLE `pending_bank_transfers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `restaurant_id` (`restaurant_id`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `pending_online_payments`
--
ALTER TABLE `pending_online_payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reference` (`reference`),
  ADD KEY `restaurant_id` (`restaurant_id`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `qr_code_scans`
--
ALTER TABLE `qr_code_scans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `restaurant_id` (`restaurant_id`),
  ADD KEY `scanned_at` (`scanned_at`);

--
-- Indexes for table `qr_templates`
--
ALTER TABLE `qr_templates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `restaurants`
--
ALTER TABLE `restaurants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `subscription_id` (`subscription_id`);

--
-- Indexes for table `restaurant_payment_settings`
--
ALTER TABLE `restaurant_payment_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `restaurant_id_gateway` (`restaurant_id`,`gateway`);

--
-- Indexes for table `restaurant_qr_codes`
--
ALTER TABLE `restaurant_qr_codes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `restaurant_id` (`restaurant_id`),
  ADD KEY `qr_template_id` (`qr_template_id`);

--
-- Indexes for table `restaurant_reservation_settings`
--
ALTER TABLE `restaurant_reservation_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `restaurant_id` (`restaurant_id`);

--
-- Indexes for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `restaurant_id` (`restaurant_id`),
  ADD KEY `plan_id` (`plan_id`),
  ADD KEY `status` (`status`),
  ADD KEY `current_period_end` (`current_period_end`);

--
-- Indexes for table `subscription_emails`
--
ALTER TABLE `subscription_emails`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_subscription_email_lookup` (`subscription_id`,`email_type`,`days_before`,`sent_at`),
  ADD KEY `subscription_id` (`subscription_id`);

--
-- Indexes for table `subscription_change_requests`
--
ALTER TABLE `subscription_change_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_subscription_pending` (`subscription_id`,`status`),
  ADD KEY `idx_effective_pending` (`effective_at`,`status`),
  ADD KEY `idx_restaurant_pending` (`restaurant_id`,`status`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_token_hash` (`token_hash`),
  ADD KEY `idx_user_active` (`user_type`,`user_id`,`used_at`,`expires_at`),
  ADD KEY `idx_identifier_created` (`identifier`,`created_at`),
  ADD KEY `idx_ip_created` (`request_ip`,`created_at`);

--
-- Indexes for table `subscription_plans`
--
ALTER TABLE `subscription_plans`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `table_inventory_daily`
--
ALTER TABLE `table_inventory_daily`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `restaurant_date` (`restaurant_id`,`inventory_date`),
  ADD KEY `restaurant_id` (`restaurant_id`),
  ADD KEY `inventory_date` (`inventory_date`);

--
-- Indexes for table `table_reservations`
--
ALTER TABLE `table_reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `restaurant_id` (`restaurant_id`),
  ADD KEY `reservation_date` (`reservation_date`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `templates`
--
ALTER TABLE `templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- Indexes for table `template_customizations`
--
ALTER TABLE `template_customizations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `template_id` (`template_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `customization_settings`
--
ALTER TABLE `customization_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `managers`
--
ALTER TABLE `managers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=106;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `payment_settings`
--
ALTER TABLE `payment_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `pending_bank_transfers`
--
ALTER TABLE `pending_bank_transfers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `pending_online_payments`
--
ALTER TABLE `pending_online_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `qr_code_scans`
--
ALTER TABLE `qr_code_scans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `qr_templates`
--
ALTER TABLE `qr_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `restaurants`
--
ALTER TABLE `restaurants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `restaurant_payment_settings`
--
ALTER TABLE `restaurant_payment_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `restaurant_qr_codes`
--
ALTER TABLE `restaurant_qr_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `restaurant_reservation_settings`
--
ALTER TABLE `restaurant_reservation_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `site_settings`
--
ALTER TABLE `site_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `subscription_emails`
--
ALTER TABLE `subscription_emails`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subscription_change_requests`
--
ALTER TABLE `subscription_change_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subscription_plans`
--
ALTER TABLE `subscription_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `table_inventory_daily`
--
ALTER TABLE `table_inventory_daily`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `table_reservations`
--
ALTER TABLE `table_reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `templates`
--
ALTER TABLE `templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `template_customizations`
--
ALTER TABLE `template_customizations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `customization_settings`
--
ALTER TABLE `customization_settings`
  ADD CONSTRAINT `customization_settings_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `managers`
--
ALTER TABLE `managers`
  ADD CONSTRAINT `managers_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD CONSTRAINT `menu_items_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `menu_items_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`);

--
-- Constraints for table `pending_bank_transfers`
--
ALTER TABLE `pending_bank_transfers`
  ADD CONSTRAINT `pending_bank_transfers_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pending_online_payments`
--
ALTER TABLE `pending_online_payments`
  ADD CONSTRAINT `pending_online_payments_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `qr_code_scans`
--
ALTER TABLE `qr_code_scans`
  ADD CONSTRAINT `qr_code_scans_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `restaurants`
--
ALTER TABLE `restaurants`
  ADD CONSTRAINT `restaurants_subscription_fk` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `restaurant_payment_settings`
--
ALTER TABLE `restaurant_payment_settings`
  ADD CONSTRAINT `restaurant_payment_settings_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `restaurant_qr_codes`
--
ALTER TABLE `restaurant_qr_codes`
  ADD CONSTRAINT `restaurant_qr_codes_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `restaurant_qr_codes_ibfk_2` FOREIGN KEY (`qr_template_id`) REFERENCES `qr_templates` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `restaurant_reservation_settings`
--
ALTER TABLE `restaurant_reservation_settings`
  ADD CONSTRAINT `restaurant_reservation_settings_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD CONSTRAINT `subscriptions_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `subscriptions_ibfk_2` FOREIGN KEY (`plan_id`) REFERENCES `subscription_plans` (`id`);

--
-- Constraints for table `subscription_emails`
--
ALTER TABLE `subscription_emails`
  ADD CONSTRAINT `subscription_emails_ibfk_1` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subscription_change_requests`
--
ALTER TABLE `subscription_change_requests`
  ADD CONSTRAINT `subscription_change_requests_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `subscription_change_requests_ibfk_2` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `subscription_change_requests_ibfk_3` FOREIGN KEY (`from_plan_id`) REFERENCES `subscription_plans` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `subscription_change_requests_ibfk_4` FOREIGN KEY (`to_plan_id`) REFERENCES `subscription_plans` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `table_inventory_daily`
--
ALTER TABLE `table_inventory_daily`
  ADD CONSTRAINT `table_inventory_daily_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `table_reservations`
--
ALTER TABLE `table_reservations`
  ADD CONSTRAINT `table_reservations_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `template_customizations`
--
ALTER TABLE `template_customizations`
  ADD CONSTRAINT `template_customizations_ibfk_1` FOREIGN KEY (`template_id`) REFERENCES `templates` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
