-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 17, 2026 at 09:06 PM
-- Server version: 10.6.26-MariaDB
-- PHP Version: 8.4.21

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
(3, 'sigsol2024', 'sigsol2024@gmail.com', '$2y$10$rGSkGNyikjhRyBx5ASECrO8zDSU4/fv7HqgIS5kXWTF9kx.zolyHe', '2026-03-07 23:33:20', '2026-03-07 23:33:20');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `restaurant_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
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

INSERT INTO `categories` (`id`, `restaurant_id`, `section_id`, `name`, `slug`, `image`, `description`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES
(15, 2, 1, 'Appetizer', 'appetizers', '6945d9626bc44.jpg', 'Start your meal with our delicious appetizers', 1, 1, '2025-12-19 18:43:07', '2026-03-13 01:58:22'),
(16, 2, 1, 'Side Orders', 'side-orders', '6945d97eb3a2f.webp', 'Perfect sides to complement your meal', 2, 1, '2025-12-19 18:43:07', '2026-03-13 01:58:22'),
(17, 2, 1, 'Desserts', 'desserts', '6945d9f81c699.jpg', 'Sweet endings to your meal', 3, 1, '2025-12-19 18:43:07', '2026-03-13 01:58:22'),
(18, 2, 1, 'Champagne', 'champagne', '6945da0b5f74f.jpg', 'Premium champagne selection', 4, 1, '2025-12-19 18:43:07', '2026-03-13 01:58:22'),
(19, 2, 1, 'Tequila', 'tequila', '6945da1a42b4c.jpg', 'Premium tequila collection', 5, 1, '2025-12-19 18:43:07', '2026-03-13 01:58:22'),
(20, 2, 1, 'Cognac', 'cognac', '6945da2ba75b9.jpg', 'Fine cognac selection', 6, 1, '2025-12-19 18:43:07', '2026-03-13 01:58:22'),
(21, 2, 1, 'Whiskey', 'whiskey', '6945da3b4758d.jpg', 'Premium whiskey collection', 7, 1, '2025-12-19 18:43:07', '2026-03-13 01:58:22'),
(22, 2, 1, 'Shisha', 'shisha', '6945da4e4f519.jpg', 'Flavored shisha selection', 8, 1, '2025-12-19 18:43:07', '2026-03-13 01:58:22'),
(23, 3, 11, 'Soft Drinks & Non-Alcoholic', 'soft-drinks-non-alcoholic', NULL, 'Refreshing non-alcoholic beverages', 16, 1, '2026-02-13 09:52:41', '2026-03-13 02:33:07'),
(24, 3, 11, 'Beer & Cider', 'beer-cider', NULL, 'Local and imported beers and ciders', 17, 1, '2026-02-13 09:52:41', '2026-03-13 02:32:57'),
(25, 3, 11, 'Brandy & Cognac', 'brandy-cognac', NULL, 'Premium brandy and cognac selection', 18, 1, '2026-02-13 09:52:41', '2026-03-13 02:32:48'),
(26, 3, 11, 'Whiskey', 'whiskey', NULL, 'Fine whiskey collection', 19, 1, '2026-02-13 09:52:41', '2026-03-13 02:32:38'),
(27, 3, 11, 'Rum', 'rum', NULL, 'Rum selection', 20, 1, '2026-02-13 09:52:41', '2026-03-13 02:32:29'),
(28, 3, 11, 'Vodka', 'vodka', NULL, 'Vodka selection', 21, 1, '2026-02-13 09:52:41', '2026-03-13 02:32:20'),
(29, 3, 11, 'Gin', 'gin', NULL, 'Premium gin selection', 22, 1, '2026-02-13 09:52:41', '2026-03-13 02:32:09'),
(30, 3, 11, 'Tequila', 'tequila', NULL, 'Tequila selection', 23, 1, '2026-02-13 09:52:41', '2026-03-13 02:31:57'),
(31, 3, 11, 'Liqueurs', 'liqueurs', NULL, 'Sweet liqueurs and digestifs', 24, 1, '2026-02-13 09:52:41', '2026-03-13 02:31:46'),
(32, 3, 11, 'Aperitifs & Bitters', 'aperitifs-bitters', NULL, 'Aperitifs and bitters', 25, 1, '2026-02-13 09:52:41', '2026-03-13 02:31:36'),
(33, 3, 11, 'Champagne', 'champagne', NULL, 'Premium champagne selection', 26, 1, '2026-02-13 09:52:41', '2026-03-13 02:31:26'),
(34, 3, 11, 'Mocktails', 'mocktails', NULL, 'Alcohol-free cocktails', 27, 1, '2026-02-13 09:52:41', '2026-03-13 02:31:16'),
(35, 3, 11, 'Cocktails', 'cocktails', NULL, 'Classic and signature cocktails', 28, 1, '2026-02-13 09:52:41', '2026-03-13 02:31:06'),
(36, 3, 11, 'White Wines', 'white-wines', NULL, 'White wine selection', 29, 1, '2026-02-13 09:52:41', '2026-03-13 02:30:54'),
(37, 3, 11, 'Red Wines', 'red-wines', NULL, 'Red wine selection', 30, 1, '2026-02-13 09:52:41', '2026-03-13 02:30:31'),
(38, 3, 11, 'Coffee', 'coffee', NULL, 'Hot coffee drinks', 31, 1, '2026-02-13 09:52:41', '2026-03-13 02:30:42'),
(39, 3, 11, 'Smoothies', 'smoothies', NULL, 'Fresh fruit smoothies', 32, 1, '2026-02-13 09:52:41', '2026-03-13 02:29:56'),
(40, 3, 11, 'Fresh Juices', 'fresh-juices', NULL, 'Freshly squeezed juices', 33, 1, '2026-02-13 09:52:41', '2026-03-13 02:29:40'),
(41, 3, 10, 'Breakfast Trays (48-Hour Pre-Order)', 'breakfast-trays-48hr', NULL, 'Premium breakfast trays for pre-order', 1, 1, '2026-02-13 10:57:52', '2026-03-13 02:27:49'),
(42, 3, 10, 'Breakfast', 'breakfast', NULL, 'Morning meals', 2, 1, '2026-02-13 10:57:52', '2026-03-13 02:28:00'),
(43, 3, 10, 'Salads', 'salads', NULL, 'Fresh salads', 3, 1, '2026-02-13 10:57:52', '2026-03-13 02:28:09'),
(44, 3, 10, 'Pepper Soups & Continental Soups', 'pepper-soups-continental-soups', NULL, 'Served with fresh bread rolls', 4, 1, '2026-02-13 10:57:52', '2026-03-13 02:28:18'),
(45, 3, 10, 'Finger Foods & Small Chops', 'finger-foods-small-chops', NULL, 'Appetizers and small bites', 5, 1, '2026-02-13 10:57:52', '2026-03-13 02:28:33'),
(46, 3, 10, 'Sandwiches & Burgers', 'sandwiches-burgers', NULL, 'Sandwiches and burgers', 6, 1, '2026-02-13 10:57:52', '2026-03-13 02:28:43'),
(47, 3, 10, 'Chicken Entrées', 'chicken-entrees', NULL, 'Served with choice of fries, roast potatoes, sweet potato fries, or yam fries', 7, 1, '2026-02-13 10:57:52', '2026-03-13 02:28:52'),
(48, 3, 10, 'Seafood', 'seafood', NULL, 'Fresh seafood dishes', 8, 1, '2026-02-13 10:57:52', '2026-03-13 02:29:01'),
(49, 3, 10, 'Steaks, Ribs & Chops', 'steaks-ribs-chops', NULL, 'South African cuts — served with side of choice', 9, 1, '2026-02-13 10:57:52', '2026-03-13 02:29:11'),
(50, 3, 10, 'Grills', 'grills', NULL, 'Grilled specialties', 10, 1, '2026-02-13 10:57:52', '2026-03-13 02:29:22'),
(51, 3, 10, 'Platters', 'platters', NULL, 'Sharing platters', 11, 1, '2026-02-13 10:57:52', '2026-03-13 02:33:58'),
(52, 3, 10, 'Pasta', 'pasta', NULL, 'Pasta dishes', 12, 1, '2026-02-13 10:57:52', '2026-03-13 02:33:49'),
(53, 3, 10, 'Naija Soups', 'naija-soups', NULL, 'Served with semovita, eba, or pounded yam — protein choice included', 13, 1, '2026-02-13 10:57:52', '2026-03-13 02:33:36'),
(54, 3, 10, 'Naija Specialties', 'naija-specialties', NULL, 'Nigerian specialties', 14, 1, '2026-02-13 10:57:52', '2026-03-13 02:33:26'),
(55, 3, 10, 'Sides', 'sides', NULL, 'Side dishes', 15, 1, '2026-02-13 10:57:52', '2026-03-13 02:33:18'),
(61, 13, 12, 'BREAKFAST', 'breakfast', NULL, 'Breakfast is served Monday – Sunday | 6:00 AM – 11:00 AM. To place your order, dial Ext: 000.', 24, 1, '2026-03-13 09:42:21', '2026-05-02 18:46:27'),
(62, 13, 12, 'Breakfast Sides', 'breakfast-sides', NULL, NULL, 27, 1, '2026-03-13 09:42:21', '2026-05-02 18:46:27'),
(63, 13, 12, 'Breakfast Combos', 'breakfast-combos', NULL, NULL, 31, 1, '2026-03-13 09:42:21', '2026-05-02 18:46:27'),
(64, 13, 13, 'AUTHENTIC NIGERIAN CUISINE', 'authentic-nigerian-cuisine', NULL, '', 17, 1, '2026-03-13 09:42:21', '2026-05-02 18:46:27'),
(65, 13, 12, 'Extra Side Dishes', 'extra-side-dishes', NULL, NULL, 40, 1, '2026-03-13 09:42:21', '2026-05-02 18:46:27'),
(66, 13, 12, 'PIZZA & PASTA', 'pizza-pasta', NULL, NULL, 44, 1, '2026-03-13 09:42:21', '2026-05-02 18:46:27'),
(67, 13, 12, 'Appetizers & Salads', 'appetizers-salads', NULL, NULL, 48, 1, '2026-03-13 09:42:21', '2026-05-02 18:46:27'),
(68, 13, 13, 'Soups', 'soups', NULL, '', 13, 1, '2026-03-13 09:42:21', '2026-05-02 18:46:27'),
(69, 13, 12, 'Vegetarian Cuisine', 'vegetarian-cuisine', NULL, NULL, 55, 1, '2026-03-13 09:42:21', '2026-05-02 18:46:27'),
(70, 13, 12, 'In A Bun (Burgers)', 'in-a-bun-burgers', NULL, NULL, 59, 1, '2026-03-13 09:42:21', '2026-05-02 18:46:27'),
(71, 13, 13, 'FROM THE GRILL', 'from-the-grill', NULL, '', 18, 1, '2026-03-13 09:42:21', '2026-05-02 18:46:27'),
(72, 13, 12, 'Triple Stack Sandwiches', 'triple-stack-sandwiches', NULL, NULL, 66, 1, '2026-03-13 09:42:21', '2026-05-02 18:46:27'),
(73, 13, 12, 'Kids Menu', 'kids-menu', NULL, NULL, 67, 1, '2026-03-13 09:42:21', '2026-05-02 18:46:27'),
(74, 13, 12, 'Desserts', 'desserts', NULL, NULL, 68, 1, '2026-03-13 09:42:21', '2026-05-02 18:46:27'),
(75, 13, 13, 'Organic Salads & Appetizers', 'organic-salads-appetizers', '69b42285b1bc4.jpg', '', 14, 1, '2026-03-13 10:13:02', '2026-05-02 18:46:27'),
(76, 13, 13, 'Vegetarian', 'vegetarian', NULL, NULL, 29, 1, '2026-03-13 10:13:02', '2026-05-02 18:46:27'),
(78, 13, 13, 'Pasta Dishes', 'pasta-dishes', NULL, '', 15, 1, '2026-03-13 10:13:02', '2026-05-02 18:46:27'),
(79, 13, 13, 'Medium Crust Pizzas', 'medium-crust-pizzas', NULL, NULL, 42, 1, '2026-03-13 10:13:02', '2026-05-02 18:46:27'),
(80, 13, 13, 'Main Courses', 'main-courses', NULL, NULL, 46, 1, '2026-03-13 10:13:02', '2026-05-02 18:46:27'),
(81, 13, 13, 'Poultry', 'poultry', NULL, NULL, 50, 1, '2026-03-13 10:13:02', '2026-05-02 18:46:27'),
(82, 13, 13, 'Seafood & Fish', 'seafood-fish', NULL, '', 21, 1, '2026-03-13 10:13:02', '2026-05-02 18:46:27'),
(83, 13, 13, 'Desserts', 'desserts-a-la-carte', NULL, '', 22, 1, '2026-03-13 10:13:02', '2026-05-02 18:46:27'),
(84, 13, 14, 'Soft Drinks / Water', 'soft-drinks-water', NULL, '', 25, 1, '2026-03-14 17:42:54', '2026-05-02 18:46:27'),
(85, 13, 14, 'Juices', 'juices', NULL, '', 26, 1, '2026-03-14 17:42:54', '2026-05-02 18:46:27'),
(86, 13, 14, 'Energy Drinks', 'energy-drinks', NULL, '', 28, 1, '2026-03-14 17:42:54', '2026-05-02 18:46:27'),
(87, 13, 14, 'Beers', 'beers', NULL, '', 30, 1, '2026-03-14 17:42:54', '2026-05-02 18:46:27'),
(88, 13, 14, 'Aperitif', 'aperitif', NULL, '', 32, 1, '2026-03-14 17:42:54', '2026-05-02 18:46:27'),
(89, 13, 14, 'Gin', 'gin', NULL, '', 34, 1, '2026-03-14 17:42:54', '2026-05-02 18:46:27'),
(91, 13, 14, 'Whisky Single Malt', 'whisky-single-malt', NULL, '', 39, 1, '2026-03-14 17:42:54', '2026-05-02 18:46:27'),
(92, 13, 14, 'Whisky Premium Blend', 'whisky-premium-blend', NULL, '', 41, 1, '2026-03-14 17:42:54', '2026-05-02 18:46:27'),
(93, 13, 14, 'Whisky American Irish', 'whisky-american-irish', NULL, '', 43, 1, '2026-03-14 17:42:54', '2026-05-02 18:46:27'),
(94, 13, 14, 'Vodka', 'vodka', NULL, '', 45, 1, '2026-03-14 17:42:54', '2026-05-02 18:46:27'),
(95, 13, 14, 'Rum', 'rum', NULL, '', 47, 1, '2026-03-14 17:42:54', '2026-05-02 18:46:27'),
(96, 13, 14, 'Cognac', 'cognac', NULL, '', 49, 1, '2026-03-14 17:42:54', '2026-05-02 18:46:27'),
(97, 13, 14, 'Tequila', 'tequila', NULL, '', 51, 1, '2026-03-14 17:42:54', '2026-05-02 18:46:27'),
(98, 13, 14, 'Liquor', 'liquor', NULL, '', 52, 1, '2026-03-14 17:42:54', '2026-05-02 18:46:27'),
(99, 13, 14, 'Hot Beverages', 'hot-beverages', NULL, '', 54, 1, '2026-03-14 17:42:54', '2026-05-02 18:46:27'),
(100, 13, 14, 'White Wine', 'white-wine', NULL, '', 56, 1, '2026-03-14 17:42:54', '2026-05-02 18:46:27'),
(101, 13, 14, 'Red Wine', 'red-wine', NULL, '', 58, 1, '2026-03-14 17:42:54', '2026-05-02 18:46:27'),
(102, 13, 14, 'Rosé Wine', 'rose-wine', NULL, '', 62, 1, '2026-03-14 17:42:54', '2026-05-02 18:46:27'),
(103, 13, 14, 'Champagne', 'champagne', NULL, '', 63, 1, '2026-03-14 17:42:54', '2026-05-02 18:46:27'),
(104, 13, 13, 'Pizza', 'p', NULL, '', 16, 1, '2026-03-23 13:54:19', '2026-05-02 18:46:27'),
(105, 13, 13, 'SANDWICH', 's', NULL, '', 19, 1, '2026-03-26 09:39:18', '2026-05-02 18:46:27'),
(106, 13, 13, 'BURGER', 'b', NULL, '', 20, 1, '2026-03-26 09:41:28', '2026-05-02 18:46:27'),
(112, 13, 14, 'HERBAL TEA', 'h', NULL, '', 53, 1, '2026-04-02 13:51:30', '2026-05-02 18:46:27'),
(115, 13, 14, 'Classic Cocktail', 'c', NULL, '', 64, 1, '2026-04-22 14:35:50', '2026-05-02 18:46:27'),
(117, 13, 14, 'Classic Mocktail', 'x', NULL, '', 65, 1, '2026-04-22 16:01:06', '2026-05-02 18:46:27'),
(126, 13, 14, 'Non Alcoholic Wine', 'n', NULL, '', 57, 1, '2026-04-25 12:47:45', '2026-05-02 18:46:27'),
(129, 19, 15, 'BREAKFAST', 'b', NULL, 'Breakfast is served with a choice of tea, Americano coffee or fresh juice', 15, 1, '2026-05-06 00:55:32', '2026-05-06 01:51:11'),
(130, 19, 15, 'PANCAKES', 'pancakes', NULL, '', 14, 1, '2026-05-06 01:02:51', '2026-05-06 01:51:11'),
(131, 19, 15, 'WAFFLES', 'waffles', NULL, '', 13, 1, '2026-05-06 01:41:17', '2026-05-06 01:51:11'),
(132, 19, 15, 'EGGS', 'eggs', NULL, 'Eggs are served with fresh vegetables, brioche toast and butter', 12, 1, '2026-05-06 01:42:19', '2026-05-06 01:51:11'),
(133, 19, 15, 'APPETIZERS', 'appetizers', NULL, '', 11, 1, '2026-05-06 01:42:53', '2026-05-06 01:51:11'),
(134, 19, 15, 'SALAD', 'salad', NULL, '', 10, 1, '2026-05-06 01:43:20', '2026-05-06 01:51:11'),
(135, 19, 15, 'COLD SANDWICHES', 'cold-sandwiches', NULL, 'All cold sandwiches are served with coleslaw salad', 9, 1, '2026-05-06 01:44:16', '2026-05-06 01:51:11'),
(136, 19, 15, 'PANINI', 'panini', NULL, 'All sandwiches are toasted and served with fresh side salad.', 8, 1, '2026-05-06 01:44:54', '2026-05-06 01:51:11'),
(137, 19, 15, 'BURGERS', 'burgers', NULL, 'All burgers are served with French fries and coleslaw salad.', 7, 1, '2026-05-06 01:45:36', '2026-05-06 01:51:11'),
(138, 19, 15, 'TOP YOUR BURGER WITH', 'top-your-burger-with', NULL, '', 6, 1, '2026-05-06 01:46:43', '2026-05-06 01:51:11'),
(139, 19, 15, 'PIZZA', 'pizza', NULL, '', 5, 1, '2026-05-06 01:47:20', '2026-05-06 01:51:11'),
(140, 19, 15, 'SEAFOOD DISHES', 'seafood-dishes', NULL, '', 4, 1, '2026-05-06 01:48:13', '2026-05-06 01:51:11'),
(141, 19, 15, 'CHICKEN DISHES', 'chicken-dishes', NULL, '', 3, 1, '2026-05-06 01:48:48', '2026-05-06 01:51:11'),
(142, 19, 15, 'BEEF DISHES', 'beef-dishes', NULL, '', 2, 1, '2026-05-06 01:49:29', '2026-05-06 01:51:11'),
(143, 19, 15, 'NIGERIAN DELIGHT', 'nigerian-delight', NULL, 'ALL SOUPS ARE SERVED WITH A CHOICE OF SWALLOW', 1, 1, '2026-05-06 01:51:11', '2026-05-06 01:51:11'),
(144, 4, 18, 'Starters', 'food-starters', '6a045de08b74d.webp', '', 1, 1, '2026-05-13 01:27:51', '2026-05-13 11:17:52'),
(145, 4, 18, 'Main Course', 'food-main-course', '6a046fe792f15.webp', '', 5, 1, '2026-05-13 01:27:51', '2026-05-13 12:34:47'),
(146, 4, 18, 'Platters', 'food-platters', NULL, NULL, 9, 1, '2026-05-13 01:27:51', '2026-05-13 01:28:09'),
(147, 4, 18, 'Salads', 'food-salads', NULL, NULL, 12, 1, '2026-05-13 01:27:51', '2026-05-13 01:28:09'),
(148, 4, 18, 'Sides', 'food-sides', '6a045e4fe0a49.webp', '', 14, 1, '2026-05-13 01:27:51', '2026-05-13 11:19:43'),
(149, 4, 18, 'Desserts', 'food-desserts', NULL, NULL, 16, 1, '2026-05-13 01:27:51', '2026-05-13 01:28:09'),
(150, 4, 19, 'Champagne', 'drink-champagne', '6a046f7a33272.webp', '', 2, 1, '2026-05-13 01:27:51', '2026-05-13 12:32:58'),
(151, 4, 19, 'Cognac', 'drink-cognac', NULL, NULL, 6, 1, '2026-05-13 01:27:51', '2026-05-13 01:28:09'),
(152, 4, 19, 'Whisky', 'drink-whisky', NULL, NULL, 10, 1, '2026-05-13 01:27:51', '2026-05-13 01:28:09'),
(153, 4, 19, 'Tequila', 'drink-tequila', NULL, NULL, 13, 1, '2026-05-13 01:27:51', '2026-05-13 01:28:09'),
(154, 4, 19, 'Gin', 'drink-gin', NULL, NULL, 15, 1, '2026-05-13 01:27:51', '2026-05-13 01:28:09'),
(155, 4, 19, 'Creams', 'drink-creams', NULL, NULL, 17, 1, '2026-05-13 01:27:51', '2026-05-13 01:28:09'),
(156, 4, 19, 'Bitters', 'drink-bitters', NULL, NULL, 18, 1, '2026-05-13 01:27:51', '2026-05-13 01:28:09'),
(157, 4, 19, 'Rum', 'drink-rum', '6a045fbc01a78.webp', '', 19, 1, '2026-05-13 01:27:51', '2026-05-13 11:25:48'),
(158, 4, 19, 'Red Wine', 'drink-red-wine', '6a046fb6c2a83.webp', '', 20, 1, '2026-05-13 01:27:51', '2026-05-13 12:33:58'),
(159, 4, 19, 'White Wine', 'drink-white-wine', NULL, NULL, 21, 1, '2026-05-13 01:27:51', '2026-05-13 01:28:09'),
(160, 4, 19, 'Cocktails', 'drink-cocktails', NULL, NULL, 22, 1, '2026-05-13 01:27:51', '2026-05-13 01:28:09'),
(161, 4, 19, 'Virgin Cocktails', 'drink-virgin-cocktails', NULL, NULL, 23, 1, '2026-05-13 01:27:51', '2026-05-13 01:28:09'),
(162, 4, 19, 'Beers', 'drink-beers', '6a045ebf2fc29.webp', '', 24, 1, '2026-05-13 01:27:51', '2026-05-13 11:21:35'),
(163, 4, 19, 'Energy Drinks', 'drink-energy-drinks', NULL, NULL, 25, 1, '2026-05-13 01:27:51', '2026-05-13 01:28:09'),
(164, 4, 19, 'Soft Drinks', 'drink-soft-drinks', NULL, NULL, 26, 1, '2026-05-13 01:27:51', '2026-05-13 01:28:09'),
(165, 4, 19, 'Milkshakes', 'drink-milkshakes', NULL, NULL, 27, 1, '2026-05-13 01:27:51', '2026-05-13 01:28:09'),
(166, 4, 20, 'Breakfast Specials', 'brunch-breakfast-specials', NULL, NULL, 3, 1, '2026-05-13 01:27:51', '2026-05-13 01:28:09'),
(167, 4, 20, 'Brunch Sides', 'brunch-sides', NULL, NULL, 7, 1, '2026-05-13 01:27:51', '2026-05-13 01:28:09'),
(168, 4, 20, 'Brunch Desserts', 'brunch-desserts', NULL, NULL, 11, 1, '2026-05-13 01:27:51', '2026-05-13 01:28:09'),
(169, 4, 21, 'Shisha Flavours', 'shisha-flavours', NULL, NULL, 4, 1, '2026-05-13 01:27:51', '2026-05-13 01:28:09'),
(170, 4, 21, 'Extras', 'shisha-extras', NULL, NULL, 8, 1, '2026-05-13 01:27:51', '2026-05-13 01:28:09'),
(177, 25, 26, 'Lord of the Wings', 'wm-lord', '6a09d433c5bf6.webp', '', 1, 1, '2026-05-14 22:48:10', '2026-05-17 14:44:03'),
(178, 25, 26, 'Waffle combos', 'wm-waffle', '6a09dbf7ccb81.webp', '', 4, 1, '2026-05-14 22:48:10', '2026-05-17 15:17:11'),
(179, 25, 26, 'Wings on fire challenge (poppers)', 'wm-poppers', NULL, NULL, 7, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:34'),
(180, 25, 26, 'Choose your flavor', 'wm-flavors', NULL, NULL, 10, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:34'),
(181, 25, 26, 'Choose your dip', 'wm-dips', NULL, NULL, 13, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:34'),
(182, 25, 26, 'Sides', 'wm-sides', NULL, NULL, 17, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:34'),
(183, 25, 26, 'Combo deals', 'wm-combo', NULL, NULL, 20, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:34'),
(184, 25, 26, 'Kids zone meals', 'wm-kids', NULL, NULL, 23, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:34'),
(185, 25, 26, 'Sweet treats', 'wm-sweets', NULL, NULL, 26, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:34'),
(186, 25, 26, 'Wings on fire challenge', 'wm-challenge', NULL, NULL, 28, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:34'),
(187, 25, 27, 'Breakfast', 'mb-breakfast', NULL, NULL, 2, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:34'),
(188, 25, 27, 'Starter', 'mb-starters', NULL, NULL, 5, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:34'),
(189, 25, 27, 'Soups', 'mb-soups', NULL, NULL, 8, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:34'),
(190, 25, 27, 'Main dish', 'mb-mains', NULL, NULL, 11, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:34'),
(191, 25, 27, 'Champagne & wines', 'mb-wines', NULL, NULL, 14, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:34'),
(192, 25, 28, 'Munchies & plates', 'hm-munch', NULL, NULL, 3, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:34'),
(193, 25, 28, 'Fish & rice mains', 'hm-fish', NULL, NULL, 6, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:34'),
(194, 25, 28, 'Sides', 'hm-sides', NULL, NULL, 9, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:34'),
(195, 25, 28, 'Sweets', 'hm-sweets', NULL, NULL, 12, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:34'),
(196, 25, 28, 'Soft drinks (shared)', 'shared-soft-drinks', NULL, NULL, 15, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:34'),
(197, 25, 28, 'Juices (shared)', 'shared-juices', NULL, NULL, 18, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:34'),
(198, 25, 28, 'Milkshakes (shared)', 'shared-milkshakes', NULL, NULL, 21, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:34'),
(199, 25, 28, 'Smoothies (shared)', 'shared-smoothies', NULL, NULL, 24, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:34'),
(200, 25, 28, 'Champagne', 'hm-champagne', NULL, NULL, 16, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:34'),
(201, 25, 28, 'Whiskey', 'hm-whiskey', NULL, NULL, 19, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:34'),
(202, 25, 28, 'Cognac', 'hm-cognac', NULL, NULL, 22, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:34'),
(203, 25, 28, 'Gin', 'hm-gin', NULL, NULL, 25, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:34'),
(204, 25, 28, 'Vodka', 'hm-vodka', NULL, NULL, 27, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:34'),
(205, 25, 28, 'Tequila', 'hm-tequila', NULL, NULL, 29, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:34'),
(206, 25, 28, 'Beer', 'hm-beer', NULL, NULL, 30, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:34'),
(207, 25, 28, 'White wine', 'hm-white', NULL, NULL, 31, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:34'),
(208, 25, 28, 'Rosé wine', 'hm-rose', NULL, NULL, 32, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:34'),
(209, 25, 28, 'Red wine', 'hm-red', NULL, NULL, 33, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:34'),
(210, 25, 28, 'Cocktails', 'hm-cocktails', NULL, NULL, 34, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:34'),
(211, 25, 28, 'Mocktails', 'hm-mocktails', NULL, NULL, 35, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:34'),
(212, 25, 28, 'Smoothies', 'hm-smooth', NULL, NULL, 36, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:34');

-- --------------------------------------------------------

--
-- Table structure for table `category_secondary_sections`
--

CREATE TABLE `category_secondary_sections` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `category_secondary_sections`
--

INSERT INTO `category_secondary_sections` (`id`, `category_id`, `section_id`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 84, 13, 1, '2026-03-20 13:07:23', '2026-03-20 13:07:23'),
(2, 84, 12, 1, '2026-03-20 13:07:23', '2026-03-20 13:07:23'),
(3, 85, 13, 1, '2026-03-20 13:20:34', '2026-03-20 13:20:34'),
(4, 85, 12, 1, '2026-03-20 13:20:34', '2026-03-20 13:20:34'),
(5, 86, 13, 1, '2026-03-20 13:20:47', '2026-03-20 13:20:47'),
(6, 86, 12, 1, '2026-03-20 13:20:47', '2026-03-20 13:20:47'),
(7, 87, 13, 1, '2026-03-20 13:20:58', '2026-03-20 13:20:58'),
(8, 87, 12, 1, '2026-03-20 13:20:58', '2026-03-20 13:20:58'),
(9, 88, 13, 1, '2026-03-20 13:21:10', '2026-03-20 13:21:10'),
(10, 88, 12, 1, '2026-03-20 13:21:10', '2026-03-20 13:21:10'),
(11, 89, 13, 1, '2026-03-20 13:21:22', '2026-03-20 13:21:22'),
(12, 89, 12, 1, '2026-03-20 13:21:22', '2026-03-20 13:21:22'),
(15, 91, 13, 1, '2026-03-20 13:22:00', '2026-03-20 13:22:00'),
(16, 91, 12, 1, '2026-03-20 13:22:00', '2026-03-20 13:22:00'),
(17, 92, 13, 1, '2026-03-20 13:22:12', '2026-03-20 13:22:12'),
(18, 92, 12, 1, '2026-03-20 13:22:12', '2026-03-20 13:22:12'),
(19, 93, 13, 1, '2026-03-20 13:22:59', '2026-03-20 13:22:59'),
(20, 93, 12, 1, '2026-03-20 13:22:59', '2026-03-20 13:22:59'),
(21, 94, 13, 1, '2026-03-20 13:23:12', '2026-03-20 13:23:12'),
(22, 94, 12, 1, '2026-03-20 13:23:12', '2026-03-20 13:23:12'),
(23, 95, 13, 1, '2026-03-20 13:23:25', '2026-03-20 13:23:25'),
(24, 95, 12, 1, '2026-03-20 13:23:25', '2026-03-20 13:23:25'),
(25, 96, 13, 1, '2026-03-20 13:23:35', '2026-03-20 13:23:35'),
(26, 96, 12, 1, '2026-03-20 13:23:35', '2026-03-20 13:23:35'),
(27, 97, 13, 1, '2026-03-20 13:23:49', '2026-03-20 13:23:49'),
(28, 97, 12, 1, '2026-03-20 13:23:49', '2026-03-20 13:23:49'),
(29, 98, 13, 1, '2026-03-20 13:24:01', '2026-03-20 13:24:01'),
(30, 98, 12, 1, '2026-03-20 13:24:01', '2026-03-20 13:24:01'),
(31, 99, 13, 1, '2026-03-20 13:24:13', '2026-03-20 13:24:13'),
(32, 99, 12, 1, '2026-03-20 13:24:13', '2026-03-20 13:24:13'),
(33, 100, 13, 1, '2026-03-20 13:24:24', '2026-03-20 13:24:24'),
(34, 100, 12, 1, '2026-03-20 13:24:24', '2026-03-20 13:24:24'),
(35, 101, 13, 1, '2026-03-20 13:24:40', '2026-03-20 13:24:40'),
(36, 101, 12, 1, '2026-03-20 13:24:40', '2026-03-20 13:24:40'),
(37, 103, 13, 1, '2026-03-20 13:24:55', '2026-03-20 13:24:55'),
(38, 103, 12, 1, '2026-03-20 13:24:55', '2026-03-20 13:24:55'),
(39, 102, 13, 1, '2026-03-20 13:25:06', '2026-03-20 13:25:06'),
(40, 102, 12, 1, '2026-03-20 13:25:06', '2026-03-20 13:25:06'),
(42, 64, 12, 1, '2026-03-27 13:25:06', '2026-03-27 13:25:06'),
(43, 71, 12, 1, '2026-03-27 13:25:39', '2026-03-27 13:25:39'),
(44, 196, 26, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(45, 197, 26, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(46, 198, 26, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(47, 199, 26, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10');

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
(1, 2, 18, '#000000', 24, 'Inter', '#000000', 18, 'Inter', '#666666', 14, 'Inter', '#000000', 20, 'Inter', '#FFFFFF', '#FFFFFF', '#111111', '#FFFFFF', '2025-12-19 18:43:25', '2026-03-13 01:58:22'),
(4, 3, 6, '#121212', 24, 'Inter', '#1c1c1c', 18, 'Inter', '#666666', 14, 'Inter', '#121212', 20, 'Inter', '#121212', '#121212', '#84ab3e', '#ffffff', '2026-02-13 09:12:56', '2026-03-13 09:42:21'),
(5, 4, 1, '#000000', 24, 'Inter', '#000000', 18, 'Inter', '#666666', 14, 'Inter', '#000000', 20, 'Inter', '#FFFFFF', '#FFFFFF', '#111111', '#FFFFFF', '2026-03-03 23:30:50', '2026-03-03 23:30:50'),
(14, 13, 1, '#000000', 24, 'Inter', '#000000', 18, 'Inter', '#666666', 14, 'Inter', '#000000', 20, 'Inter', '#FFFFFF', '#FFFFFF', '#111111', '#FFFFFF', '2026-03-12 23:11:27', '2026-03-12 23:11:27'),
(20, 19, 1, '#000000', 24, 'Inter', '#000000', 18, 'Inter', '#666666', 14, 'Inter', '#000000', 20, 'Inter', '#FFFFFF', '#FFFFFF', '#111111', '#FFFFFF', '2026-04-06 17:01:14', '2026-04-06 17:01:14'),
(21, 20, 1, '#000000', 24, 'Inter', '#000000', 18, 'Inter', '#666666', 14, 'Inter', '#000000', 20, 'Inter', '#FFFFFF', '#FFFFFF', '#111111', '#FFFFFF', '2026-04-28 20:07:22', '2026-04-28 20:07:22'),
(22, 21, 1, '#000000', 24, 'Inter', '#000000', 18, 'Inter', '#666666', 14, 'Inter', '#000000', 20, 'Inter', '#FFFFFF', '#FFFFFF', '#111111', '#FFFFFF', '2026-05-09 17:25:57', '2026-05-09 17:25:57'),
(26, 25, 1, '#000000', 24, 'Inter', '#000000', 18, 'Inter', '#666666', 14, 'Inter', '#000000', 20, 'Inter', '#FFFFFF', '#FFFFFF', '#111111', '#FFFFFF', '2026-05-14 14:20:15', '2026-05-14 14:20:15'),
(27, 26, 1, '#000000', 24, 'Inter', '#000000', 18, 'Inter', '#666666', 14, 'Inter', '#000000', 20, 'Inter', '#FFFFFF', '#FFFFFF', '#111111', '#FFFFFF', '2026-05-17 21:04:00', '2026-05-17 21:04:00');

-- --------------------------------------------------------

--
-- Table structure for table `email_delivery_suppressions`
--

CREATE TABLE `email_delivery_suppressions` (
  `id` int(11) NOT NULL,
  `email_sha256` char(64) NOT NULL,
  `reason` varchar(64) NOT NULL DEFAULT 'hard_bounce',
  `source` varchar(64) NOT NULL DEFAULT 'manual',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `identifier` varchar(255) NOT NULL DEFAULT '',
  `attempted_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `login_attempts`
--

INSERT INTO `login_attempts` (`id`, `ip_address`, `identifier`, `attempted_at`) VALUES
(54, '98.97.79.11', 'ellipsehotelslagos@gmail.com', '2026-05-15 09:30:02');

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
(2, 'lava_manager', 'jamesamaila07@gmail.com', '$2y$10$h0RdJU4tRyPL1Gi9vi6slOR6UT6G4pbO8JjCGP7z/11CeK6AzzdDK', 2, '2025-12-19 18:43:07', '2025-12-24 03:18:32'),
(3, 'heviewotelekki_manager', 'reservations@theviewlekki.com', '$2y$10$it3gLTDg5Xs66JtBM9XPs./c.WWAdxbEfbYOkKStFAe2RrdJEeCwa', 3, '2026-02-13 08:57:39', '2026-02-13 08:57:39'),
(4, 'Nostalgia', 'admin@nostalgia.our-menu.online', '$2y$10$6RzEqDr3dsF//RAixtQfTu.pwixF38Miqt/bf1FNp9db8YnSPKkRy', 4, '2026-03-03 23:30:50', '2026-05-12 23:35:50'),
(13, 'heussoestaurant_manager', 'restaurant@lussohotelsabuja.com', '$2y$10$Fh0gC2vv/1u0mPAm9AAO6OAQ1xc3vrLNArRu1ZSbt7b376rcyCQby', 13, '2026-03-12 23:11:27', '2026-03-12 23:11:27'),
(19, 'restaurant_manager', 'opallagos1@gmail.com', '$2y$10$IG9TuMWMmmp9o6pWrFEGZOpo1N3cDDUJxAQdcYPNVirvtiYUvrewC', 19, '2026-04-06 17:01:14', '2026-04-06 17:01:14'),
(20, 'wissheistana_manager', 'it.vistana@swissinternationalhotels.com', '$2y$10$4SZDGdX8G.bM0Sp3teev7OPOzbqurIcN7Ex8f4IPSIRKJ8ZEBIRym', 20, '2026-04-28 20:07:22', '2026-04-28 20:07:22'),
(21, 'Ellipse_Hotels', 'ellipsehotelslagos@gmail.com', '$2y$10$qf44.BDvK7sAzPEg2tdeUegT3H/4xcDCCBgLw96z1AGtK6bxftR3i', 21, '2026-05-09 17:25:57', '2026-05-15 09:34:18'),
(25, 'heaniaouse_manager', 'admin@maniahouse.our-menu.online', '$2y$10$vJ/8ehXrESneVUwPZ8gFwOdp1J/pJCSnQRzdxlUlUkykm8WDXGfuC', 25, '2026-05-14 14:20:15', '2026-05-14 14:20:15'),
(26, 'altndocial_manager', 'admin@saltandsocial.our-menu.online', '$2y$10$vWJ4NB1SF5adtaMK9ZAjy.2YAA49kB2UN2GPB1W2FKsnRsY/KxPym', 26, '2026-05-17 21:04:00', '2026-05-17 21:04:00');

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
(41, 2, 15, 'Chicken Spring Rolls', 'chicken-spring-rolls', 'Chicken stuffed rolls with mixed bell peppers and cabbage served with plum sauce', 200.00, '6945dee5937fd.jpg', 1, 1, '2025-12-19 18:43:07', '2026-03-09 12:51:35'),
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
(105, 2, 22, 'Orange Fruit', 'orange-fruit', '', 50000.00, NULL, 25, 1, '2025-12-19 18:43:07', '2025-12-19 18:43:07'),
(106, 3, 23, 'Cranberry Juice', 'cranberry-juice', '', 6000.00, NULL, 1, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(107, 3, 23, 'Juice Pack', 'juice-pack', '', 9840.00, NULL, 2, 1, '2026-02-13 09:52:41', '2026-05-07 12:39:33'),
(108, 3, 23, 'Malt Drink', 'malt-drink', '', 1500.00, NULL, 3, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(109, 3, 23, 'Energy Drink', 'energy-drink', '', 5000.00, NULL, 4, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(110, 3, 23, 'Water (Small)', 'water-small', '', 1000.00, NULL, 5, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(111, 3, 23, 'Soft Drinks (Coke, Fanta, Sprite, etc.)', 'soft-drinks', 'Coke, Fanta, Sprite and more', 1000.00, NULL, 6, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(112, 3, 23, 'Red Bull / Power Horse', 'red-bull-power-horse', '', 5000.00, NULL, 7, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(113, 3, 24, '33 Lager', '33-lager', '', 3820.39, NULL, 1, 1, '2026-02-13 09:52:41', '2026-05-07 11:58:42'),
(114, 3, 24, 'Smirnoff Ice', 'smirnoff-ice', '', 4305.00, NULL, 2, 1, '2026-02-13 09:52:41', '2026-05-07 12:42:24'),
(115, 3, 24, 'Star Draft (Big)', 'star-draft-big', '', 2000.00, NULL, 3, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(116, 3, 24, 'Star Draft (Small)', 'star-draft-small', '', 1000.00, NULL, 4, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(117, 3, 24, 'Star Radler', 'star-radler', '', 3000.00, NULL, 5, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(118, 3, 24, 'Budweiser (Big)', 'budweiser-big', '', 3500.00, NULL, 6, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(119, 3, 24, 'Heineken', 'heineken', '', 3500.00, NULL, 7, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(120, 3, 24, 'Heineken Draft (Big)', 'heineken-draft-big', '', 3500.00, NULL, 8, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(121, 3, 24, 'Heineken Draft (Small)', 'heineken-draft-small', '', 1500.00, NULL, 9, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(122, 3, 24, 'Flying Fish', 'flying-fish', '', 3000.00, NULL, 10, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(123, 3, 24, 'Desperados', 'desperados', '', 3000.00, NULL, 11, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(124, 3, 24, 'Guinness Stout (Big)', 'guinness-stout-big', '', 3500.00, NULL, 12, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(125, 3, 24, 'Guinness Stout (Small)', 'guinness-stout-small', '', 3000.00, NULL, 13, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(126, 3, 24, 'Guinness Extra Smooth', 'guinness-extra-smooth', '', 3000.00, NULL, 14, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(127, 3, 24, 'Gulder', 'gulder', '', 3000.00, NULL, 15, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(128, 3, 24, 'Star', 'star', '', 3000.00, NULL, 16, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(129, 3, 24, 'Trophy', 'trophy', '', 3000.00, NULL, 17, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(130, 3, 24, 'Goldberg', 'goldberg', '', 3000.00, NULL, 18, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(131, 3, 24, 'Harp', 'harp', '', 3000.00, NULL, 19, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(132, 3, 25, 'Rémy Martin XO', 'remy-martin-xo', 'Bottle', 230000.00, NULL, 1, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(133, 3, 25, 'Hennessy XO', 'hennessy-xo', 'Bottle', 575000.00, NULL, 2, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(134, 3, 25, 'Hennessy VSOP (Bottle)', 'hennessy-vsop-bottle', 'Bottle', 130000.00, NULL, 3, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(135, 3, 25, 'Hennessy VSOP (Shot)', 'hennessy-vsop-shot', 'Per shot', 9000.00, NULL, 4, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(136, 3, 25, 'Rémy Martin VSOP (Bottle)', 'remy-martin-vsop-bottle', 'Bottle', 90000.00, NULL, 5, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(137, 3, 25, 'Rémy Martin VSOP (Shot)', 'remy-martin-vsop-shot', 'Per shot', 6500.00, NULL, 6, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(138, 3, 25, 'Martell Blue Swift (Bottle)', 'martell-blue-swift-bottle', 'Bottle', 80000.00, NULL, 7, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(139, 3, 25, 'Martell Blue Swift (Shot)', 'martell-blue-swift-shot', 'Per shot', 6000.00, NULL, 8, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(140, 3, 25, 'Hennessy VS (Bottle)', 'hennessy-vs-bottle', 'Bottle', 80000.00, NULL, 9, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(141, 3, 25, 'Hennessy VS (Shot)', 'hennessy-vs-shot', 'Per shot', 7000.00, NULL, 10, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(142, 3, 26, 'Glenfiddich 18 Years', 'glenfiddich-18-years', 'Bottle', 180000.00, NULL, 1, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(143, 3, 26, 'Glenfiddich 15 Years (Bottle)', 'glenfiddich-15-years-bottle', 'Bottle', 120000.00, NULL, 2, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(144, 3, 26, 'Glenfiddich 15 Years (Shot)', 'glenfiddich-15-years-shot', 'Per shot', 8000.00, NULL, 3, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(145, 3, 26, 'Glenfiddich 12 Years (Bottle)', 'glenfiddich-12-years-bottle', 'Bottle', 90000.00, NULL, 4, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(146, 3, 26, 'Glenfiddich 12 Years (Shot)', 'glenfiddich-12-years-shot', 'Per shot', 5000.00, NULL, 5, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(147, 3, 26, 'Jameson Black Barrel (Bottle)', 'jameson-black-barrel-bottle', 'Bottle', 55000.00, NULL, 6, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(148, 3, 26, 'Jameson Black Barrel (Shot)', 'jameson-black-barrel-shot', 'Per shot', 3000.00, NULL, 7, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(149, 3, 26, 'Jameson (Big Bottle)', 'jameson-big-bottle', 'Bottle', 47000.00, NULL, 8, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(150, 3, 26, 'Jameson (Shot)', 'jameson-shot', 'Per shot', 5000.00, NULL, 9, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(151, 3, 26, 'Jameson Miniature', 'jameson-miniature', '', 18500.00, NULL, 10, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(152, 3, 26, 'Johnnie Walker Black Label (Bottle)', 'johnnie-walker-black-label-bottle', 'Bottle', 45000.00, NULL, 11, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(153, 3, 26, 'Johnnie Walker Black Label (Shot)', 'johnnie-walker-black-label-shot', 'Per shot', 5000.00, NULL, 12, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(154, 3, 26, 'Johnnie Walker Red Label (Bottle)', 'johnnie-walker-red-label-bottle', 'Bottle', 27000.00, NULL, 13, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(155, 3, 26, 'Johnnie Walker Red Label (Shot)', 'johnnie-walker-red-label-shot', 'Per shot', 3000.00, NULL, 14, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(156, 3, 26, 'Johnnie Walker Blue Label', 'johnnie-walker-blue-label', 'Bottle', 90000.00, NULL, 15, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(157, 3, 26, 'Jack Daniel\'s (Bottle)', 'jack-daniels-bottle', 'Bottle', 48000.00, NULL, 16, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(158, 3, 26, 'Jack Daniel\'s (Shot)', 'jack-daniels-shot', 'Per shot', 4000.00, NULL, 17, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(159, 3, 26, 'Chivas Regal (Bottle)', 'chivas-regal-bottle', 'Bottle', 25000.00, NULL, 18, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(160, 3, 26, 'Chivas Regal (Shot)', 'chivas-regal-shot', 'Per shot', 2500.00, NULL, 19, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(161, 3, 27, 'Bacardi White (Bottle)', 'bacardi-white-bottle', 'Bottle', 35000.00, NULL, 1, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(162, 3, 27, 'Bacardi White (Shot)', 'bacardi-white-shot', 'Per shot', 3690.00, NULL, 2, 1, '2026-02-13 09:52:41', '2026-05-07 12:18:20'),
(163, 3, 27, 'Bacardi Gold (Bottle)', 'bacardi-gold-bottle', 'Bottle', 35000.00, NULL, 3, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(164, 3, 27, 'Bacardi Gold (Shot)', 'bacardi-gold-shot', 'Per shot', 2000.00, NULL, 4, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(165, 3, 27, 'Malibu (Bottle)', 'malibu-bottle', 'Bottle', 28000.00, NULL, 5, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(166, 3, 27, 'Malibu (Shot)', 'malibu-shot', 'Per shot', 4000.00, NULL, 6, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(167, 3, 28, 'Ciroc', 'ciroc', 'Bottle', 62000.00, NULL, 1, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(168, 3, 28, 'Absolut Vodka (Bottle)', 'absolut-vodka-bottle', 'Bottle', 73800.00, NULL, 2, 1, '2026-02-13 09:52:41', '2026-05-07 12:17:11'),
(169, 3, 28, 'Absolut Vodka (Shot)', 'absolut-vodka-shot', 'Per shot', 3000.00, NULL, 3, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(170, 3, 28, 'Smirnoff (Bottle)', 'smirnoff-bottle', 'Bottle', 22000.00, NULL, 4, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(171, 3, 28, 'Smirnoff (Shot)', 'smirnoff-shot', 'Per shot', 2500.00, NULL, 5, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(172, 3, 28, 'Grey Goose (Bottle)', 'grey-goose-bottle', 'Bottle', 45000.00, NULL, 6, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(173, 3, 28, 'Grey Goose (Shot)', 'grey-goose-shot', 'Per shot', 2500.00, NULL, 7, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(174, 3, 29, 'Gin Mare (Bottle)', 'gin-mare-bottle', 'Bottle', 40000.00, NULL, 1, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(175, 3, 29, 'Gin Mare (Shot)', 'gin-mare-shot', 'Per shot', 3000.00, NULL, 2, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(176, 3, 29, 'Hendrick\'s (Bottle)', 'hendricks-bottle', 'Bottle', 73000.00, NULL, 3, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(177, 3, 29, 'Hendrick\'s (Shot)', 'hendricks-shot', 'Per shot', 4000.00, NULL, 4, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(178, 3, 29, 'Hendrick\'s Alt Bottle', 'hendricks-alt-bottle', 'Alternative bottle', 50000.00, NULL, 5, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(179, 3, 29, 'Hendrick\'s Alt Bottle (Shot)', 'hendricks-alt-bottle-shot', 'Per shot', 2500.00, NULL, 6, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(180, 3, 29, 'Bombay Sapphire (Bottle)', 'bombay-sapphire-bottle', 'Bottle', 50000.00, NULL, 7, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(181, 3, 29, 'Bombay Sapphire (Shot)', 'bombay-sapphire-shot', 'Per shot', 4000.00, NULL, 8, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(182, 3, 30, 'Olmeca White (Bottle)', 'olmeca-white-bottle', 'Bottle', 67650.00, NULL, 1, 1, '2026-02-13 09:52:41', '2026-05-07 12:12:07'),
(184, 3, 31, 'Baileys (Bottle)', 'baileys-bottle', 'Bottle', 30000.00, NULL, 1, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(185, 3, 31, 'Baileys (Shot)', 'baileys-shot', 'Per shot', 3690.00, NULL, 2, 1, '2026-02-13 09:52:41', '2026-05-07 12:19:22'),
(186, 3, 31, 'Kahlua (Bottle)', 'kahlua-bottle', 'Bottle', 23000.00, NULL, 3, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(187, 3, 31, 'Kahlua (Shot)', 'kahlua-shot', 'Per shot', 2000.00, NULL, 4, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(188, 3, 31, 'Cointreau', 'cointreau', 'Per shot', 2000.00, NULL, 5, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(189, 3, 31, 'Triple Sec', 'triple-sec', 'Per shot', 2000.00, NULL, 6, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(190, 3, 32, 'Campari', 'campari', 'Bottle', 30750.00, NULL, 1, 1, '2026-02-13 09:52:41', '2026-05-07 11:59:42'),
(191, 3, 32, 'Origin Bitters (Big)', 'origin-bitters-big', 'Bottle', 6237.00, NULL, 2, 1, '2026-02-13 09:52:41', '2026-05-07 12:40:31'),
(192, 3, 32, 'Origin Bitters (Mini)', 'origin-bitters-mini', '', 2500.00, NULL, 3, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(193, 3, 32, 'Palm Spirit (Aphro / Moor Rum)', 'palm-spirit', 'Bottle', 25000.00, NULL, 4, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(194, 3, 33, 'Moët Nectar Rosé', 'moet-nectar-rose', 'Bottle', 176000.00, NULL, 1, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(195, 3, 33, 'Veuve Clicquot Brut', 'veuve-clicquot-brut', 'Bottle', 170000.00, NULL, 2, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(196, 3, 33, 'Moët Imperial Brut', 'moet-imperial-brut', 'Bottle', 130000.00, NULL, 3, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(197, 3, 34, 'Virgin Colada', 'virgin-colada', '', 12474.00, NULL, 1, 1, '2026-02-13 09:52:41', '2026-05-07 12:16:14'),
(198, 3, 34, 'Virgin Margarita', 'virgin-margarita', '', 12447.00, NULL, 2, 1, '2026-02-13 09:52:41', '2026-05-07 12:44:46'),
(199, 3, 34, 'Chapman', 'chapman', '', 9840.00, NULL, 3, 1, '2026-02-13 09:52:41', '2026-05-07 12:46:04'),
(200, 3, 35, 'Long Island Iced Tea', 'long-island-iced-tea', '', 18711.00, NULL, 1, 1, '2026-02-13 09:52:41', '2026-05-07 12:07:28'),
(201, 3, 35, 'Daiquiri', 'daiquiri', '', 14968.00, NULL, 2, 1, '2026-02-13 09:52:41', '2026-05-07 12:31:30'),
(202, 3, 35, 'Moscow Mule', 'moscow-mule', '', 6000.00, NULL, 3, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(203, 3, 35, 'Cosmopolitan', 'cosmopolitan', '', 6000.00, NULL, 4, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(204, 3, 35, 'Margarita', 'margarita', '', 5000.00, NULL, 5, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(205, 3, 35, 'Mojito', 'mojito', '', 7500.00, NULL, 6, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(206, 3, 35, 'Sex on the Beach', 'sex-on-the-beach', '', 5000.00, NULL, 7, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(207, 3, 35, 'Piña Colada', 'pina-colada', '', 6000.00, NULL, 8, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(208, 3, 35, 'Tequila Sunrise', 'tequila-sunrise', '', 4000.00, NULL, 9, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(209, 3, 35, 'Mai Tai', 'mai-tai', '', 6000.00, NULL, 10, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(210, 3, 35, 'Whiskey Sour', 'whiskey-sour', '', 6000.00, NULL, 11, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(211, 3, 35, 'Screaming Orgasm', 'screaming-orgasm', '', 8500.00, NULL, 12, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(212, 3, 35, 'The Boss', 'the-boss', '', 5000.00, NULL, 13, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(213, 3, 35, 'D\'View Cocktail', 'dview-cocktail', 'Signature cocktail', 5000.00, NULL, 14, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(214, 3, 36, 'Nederburg Sauvignon Blanc', 'nederburg-sauvignon-blanc', 'Bottle', 51143.40, NULL, 1, 1, '2026-02-13 09:52:41', '2026-05-07 12:09:10'),
(215, 3, 36, 'Nederburg Late Harvest', 'nederburg-late-harvest', 'Bottle', 36000.00, NULL, 2, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(216, 3, 36, 'Nederburg Chardonnay', 'nederburg-chardonnay', 'Bottle', 36000.00, NULL, 3, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(217, 3, 36, 'Mapu Sauvignon Blanc', 'mapu-sauvignon-blanc', 'Bottle', 19000.00, NULL, 4, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(218, 3, 36, 'Four Cousins', 'four-cousins-white', 'Bottle', 19000.00, NULL, 5, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(219, 3, 36, 'Frontera Moscato', 'frontera-moscato', 'Bottle', 16000.00, NULL, 6, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(220, 3, 36, 'Viala Moscato', 'viala-moscato', 'Bottle', 12000.00, NULL, 7, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(221, 3, 37, 'Nederburg Merlot', 'nederburg-merlot', 'Bottle', 48648.60, NULL, 1, 1, '2026-02-13 09:52:41', '2026-05-07 12:08:32'),
(222, 3, 37, 'Nederburg Cabernet Sauvignon', 'nederburg-cabernet-sauvignon', 'Bottle', 36000.00, NULL, 2, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(223, 3, 37, 'Escudo Rojo', 'escudo-rojo', 'Bottle', 32000.00, NULL, 3, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(224, 3, 37, 'Mapu Cabernet Sauvignon', 'mapu-cabernet-sauvignon', 'Bottle', 19000.00, NULL, 4, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(225, 3, 37, 'Four Cousins', 'four-cousins-red', 'Bottle', 18000.00, NULL, 5, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(226, 3, 37, 'Carlo Rossi', 'carlo-rossi', 'Bottle', 12000.00, NULL, 6, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(227, 3, 37, 'Drostdy-Hof', 'drostdy-hof', 'Bottle', 12000.00, NULL, 7, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(228, 3, 37, '4th Street Red', '4th-street-red', 'Bottle', 12000.00, NULL, 8, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(229, 3, 37, 'Asara', 'asara', 'Bottle', 12000.00, NULL, 9, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(230, 3, 37, 'Bolzano', 'bolzano', 'Bottle', 12000.00, NULL, 10, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(231, 3, 37, 'Châteauneuf-du-Pape', 'chateauneuf-du-pape', 'Bottle', 20000.00, NULL, 11, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(232, 3, 38, 'Cappuccino', 'cappuccino', '', 2000.00, NULL, 1, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(234, 3, 38, 'Double Espresso', 'double-espresso', '', 1500.00, NULL, 3, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(235, 3, 38, 'Single Espresso', 'single-espresso', '', 1000.00, NULL, 4, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(237, 3, 40, 'Fresh Orange Juice', 'fresh-orange-juice', '', 9979.20, NULL, 1, 1, '2026-02-13 09:52:41', '2026-05-07 12:01:00'),
(238, 3, 40, 'Fresh Pineapple Juice', 'fresh-pineapple-juice', '', 9840.00, NULL, 2, 1, '2026-02-13 09:52:41', '2026-05-07 12:35:02'),
(239, 3, 40, 'Fresh Watermelon Juice', 'fresh-watermelon-juice', '', 4000.00, NULL, 3, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(240, 3, 40, 'Sweet Zobo Drink', 'sweet-zobo-drink', '', 2000.00, NULL, 4, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(646, 3, 41, 'Premium Tray', 'premium-tray', 'Miniature wine bottle, juice pack, lemonade bottle, biscuits, wafers, coconut flakes, yoghurt cups, almonds, mug with assorted hot beverages, fresh bread rolls with butter, jam & cheese, club sandwich, cakes & croissants, plantain skewers, grapes & kiwi, English breakfast with lamb sausage, French toast, pancakes', 60000.00, NULL, 1, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(647, 3, 41, 'Deluxe Tray', 'deluxe-tray', 'Mug with assorted hot beverages, fresh bread rolls with butter, jam & cheese, club sandwich, biscuit pack, juice pack, yoghurt cups, grapes, apples, English breakfast with lamb sausage', 60000.00, NULL, 2, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(648, 3, 42, 'Breakfast Burger', 'breakfast-burger', 'With tea or coffee', 10000.00, NULL, 1, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(650, 3, 42, 'Classic English Breakfast', 'classic-english-breakfast', 'Sausages, bread, eggs, baked beans, butter, toast', 48968.00, NULL, 3, 1, '2026-02-13 10:57:53', '2026-05-07 12:46:44'),
(651, 3, 42, 'African Breakfast', 'african-breakfast', 'Boiled or fried yam or plantain, egg sauce', 10000.00, NULL, 4, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(652, 3, 42, 'Naija Special', 'naija-special', 'Indomie noodles, egg, vegetables', 8000.00, NULL, 5, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(653, 3, 43, 'Chef\'s Salad', 'chefs-salad', 'Chicken breast, lettuce, cheese, croutons, bacon, tomatoes', 12000.00, NULL, 1, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(654, 3, 43, 'Chicken Caesar Salad', 'chicken-caesar-salad', 'Lettuce, chicken breast, cucumber, olives, tomatoes, egg', 18450.00, NULL, 2, 1, '2026-02-13 10:57:53', '2026-05-07 12:26:03'),
(655, 3, 43, 'Russian Salad', 'russian-salad', 'Chicken breast, carrot, Irish potatoes, sauce', 15000.00, NULL, 3, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(656, 3, 44, 'Fresh Croaker Fish (Whole)', 'fresh-croaker-fish-whole', 'Served with fresh bread rolls', 37422.00, NULL, 1, 1, '2026-02-13 10:57:53', '2026-05-07 12:00:23'),
(657, 3, 44, 'Catfish (Whole)', 'catfish-whole', 'A spicy delicacy made with catfish, native spices, herbs. its warming and Aromatic', 36899.95, NULL, 10, 1, '2026-02-13 10:57:53', '2026-05-07 12:24:52'),
(658, 3, 44, 'Fresh Croaker Fish (Portion)', 'fresh-croaker-fish-portion', 'Served with fresh bread rolls', 15000.00, NULL, 3, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(659, 3, 44, 'Catfish (Portion)', 'catfish-portion', 'Served with fresh bread rolls', 15000.00, NULL, 4, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(660, 3, 44, 'Goat Meat Pepper Soup', 'goat-meat-pepper-soup', 'Served with fresh bread rolls', 15000.00, NULL, 5, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(661, 3, 44, 'Chicken Pepper Soup', 'chicken-pepper-soup', 'Served with fresh bread rolls', 15000.00, NULL, 6, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(662, 3, 44, 'Chinese Noodle Soup (Shrimp & Chicken)', 'chinese-noodle-soup-shrimp-chicken', 'Served with fresh bread rolls', 15000.00, NULL, 7, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(663, 3, 44, 'Creamy Italian Seafood Soup', 'creamy-italian-seafood-soup', 'Served with fresh bread rolls', 15000.00, NULL, 8, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(664, 3, 44, 'Cream of Chicken Soup', 'cream-of-chicken-soup', 'Served with fresh bread rolls', 15000.00, NULL, 9, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(665, 3, 44, 'French Onion Soup', 'french-onion-soup', 'Served with fresh bread rolls', 10000.00, NULL, 10, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(666, 3, 44, 'Oxtail Soup', 'oxtail-soup', 'Served with fresh bread rolls', 10000.00, NULL, 11, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(667, 3, 45, 'Nick Nack Combo Board', 'nick-nack-combo-board', '', 10500.00, NULL, 1, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(668, 3, 45, 'Spicy Snails', 'spicy-snails', '', 24860.00, NULL, 2, 1, '2026-02-13 10:57:53', '2026-05-07 12:43:01'),
(669, 3, 45, 'Spicy Goat Dodo', 'spicy-goat-dodo', '', 15500.00, NULL, 3, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(670, 3, 45, 'Peppered Goat Meat', 'peppered-goat-meat', '', 15000.00, NULL, 4, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(671, 3, 45, 'Crusted Calamari', 'crusted-calamari', '', 15000.00, NULL, 5, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(672, 3, 45, 'Gizzdodo', 'gizzdodo', '', 15500.00, NULL, 6, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(673, 3, 45, 'Smokey Chicken Wings', 'smokey-chicken-wings', '', 15000.00, NULL, 7, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(674, 3, 45, 'Hot Chicken Wings', 'hot-chicken-wings', '', 15000.00, NULL, 8, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(675, 3, 45, 'Yaji Wings', 'yaji-wings', '', 15000.00, NULL, 9, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(676, 3, 45, 'Buffalo Wings', 'buffalo-wings', '', 15000.00, NULL, 10, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(677, 3, 45, 'Nkwobi', 'nkwobi', '', 15000.00, NULL, 11, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(678, 3, 45, 'Peppered Gizzard', 'peppered-gizzard', '', 15000.00, NULL, 12, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(679, 3, 45, 'Pepper Fish', 'pepper-fish', '', 14000.00, NULL, 13, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(680, 3, 45, 'Shrimp Rolls (4 pcs)', 'shrimp-rolls-4pcs', '', 15000.00, NULL, 14, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(681, 3, 45, 'Pepper Beef', 'pepper-beef', '', 15000.00, NULL, 15, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(682, 3, 45, 'Pepper Chicken', 'pepper-chicken', '', 14000.00, NULL, 16, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(683, 3, 45, 'Pepper Turkey', 'pepper-turkey', '', 15000.00, NULL, 17, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(684, 3, 45, 'Coleslaw', 'coleslaw', '', 4000.00, NULL, 18, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(687, 3, 46, 'D\'View Club Sandwich', 'dview-club-sandwich', '', 12474.00, NULL, 3, 1, '2026-02-13 10:57:53', '2026-05-07 12:48:21'),
(688, 3, 46, 'Classic Ham & Cheese', 'classic-ham-cheese', '', 7500.00, NULL, 4, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(689, 3, 46, 'Chunky Tuna Sandwich', 'chunky-tuna-sandwich', '', 6500.00, NULL, 5, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(690, 3, 47, 'Southern Fried Chicken on Mash', 'southern-fried-chicken-on-mash', 'Served with choice of fries, roast potatoes, sweet potato fries, or yam fries', 22140.00, NULL, 1, 1, '2026-02-13 10:57:53', '2026-05-07 12:13:51'),
(691, 3, 47, 'Chicken Escalope', 'chicken-escalope', 'Served with choice of fries, roast potatoes, sweet potato fries, or yam fries', 15000.00, NULL, 2, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(692, 3, 47, 'D\'View Curry Chicken', 'dview-curry-chicken', 'Served with choice of fries, roast potatoes, sweet potato fries, or yam fries', 18711.00, NULL, 3, 1, '2026-02-13 10:57:53', '2026-05-07 12:49:42'),
(693, 3, 47, 'Chicken in Cream Sauce', 'chicken-in-cream-sauce', 'Served with choice of fries, roast potatoes, sweet potato fries, or yam fries', 15000.00, NULL, 4, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(694, 3, 47, 'Creamy Mustard Chicken', 'creamy-mustard-chicken', 'Served with choice of fries, roast potatoes, sweet potato fries, or yam fries', 15000.00, NULL, 5, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(695, 3, 47, 'Creamy Spinach Chicken Roll', 'creamy-spinach-chicken-roll', 'Served with choice of fries, roast potatoes, sweet potato fries, or yam fries', 9000.00, NULL, 6, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(696, 3, 47, 'Pepper Chicken', 'pepper-chicken-entree', 'Served with choice of fries, roast potatoes, sweet potato fries, or yam fries', 15000.00, NULL, 7, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(697, 3, 47, 'Oven Roast Chicken', 'oven-roast-chicken', 'Served with choice of fries, roast potatoes, sweet potato fries, or yam fries', 15500.00, NULL, 8, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(698, 3, 48, 'Grilled Salmon', 'grilled-salmon', '', 43659.00, NULL, 1, 1, '2026-02-13 10:57:53', '2026-05-07 12:01:51'),
(699, 3, 48, 'Grilled Croaker Fish', 'grilled-croaker-fish', '', 37422.00, NULL, 2, 1, '2026-02-13 10:57:53', '2026-05-07 12:38:23'),
(700, 3, 48, 'Grilled Catfish', 'grilled-catfish', '', 30000.00, NULL, 3, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(701, 3, 48, 'Grilled Jumbo Prawns', 'grilled-jumbo-prawns', '', 17000.00, NULL, 4, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(702, 3, 48, 'Butterfly Prawns', 'butterfly-prawns', '', 13000.00, NULL, 5, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(703, 3, 48, 'Lobster Thermidor', 'lobster-thermidor', '', 28500.00, NULL, 6, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(704, 3, 48, 'Golden Tilapia', 'golden-tilapia', '', 15000.00, NULL, 7, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(705, 3, 49, 'T-Bone', 't-bone', 'South African cuts — served with side of choice', 46153.00, NULL, 1, 1, '2026-02-13 10:57:53', '2026-05-07 12:15:21'),
(706, 3, 49, 'Rib-Eye', 'rib-eye', 'South African cuts — served with side of choice', 46153.00, NULL, 2, 1, '2026-02-13 10:57:53', '2026-05-07 12:41:50'),
(707, 3, 49, 'Lamb Chops', 'lamb-chops', 'South African cuts — served with side of choice', 30000.00, NULL, 3, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(708, 3, 49, 'Beef Ribs', 'beef-ribs', 'South African cuts — served with side of choice', 22000.00, NULL, 4, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(709, 3, 49, 'Oxtail', 'oxtail', 'South African cuts — served with side of choice', 6000.00, NULL, 5, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(710, 3, 50, 'Mixed Grill Special', 'mixed-grill-special', '', 13300.00, NULL, 1, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(713, 3, 51, 'Pacific Platter', 'pacific-platter', '', 38000.00, NULL, 2, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(714, 3, 51, 'D\'View Special Platter', 'dview-special-platter', '', 25000.00, NULL, 3, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(715, 3, 51, 'Ogazi Platter', 'ogazi-platter', '', 25000.00, NULL, 4, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(716, 3, 52, 'Spaghetti Prawn Marinara', 'spaghetti-prawn-marinara', '', 30750.00, NULL, 1, 1, '2026-02-13 10:57:53', '2026-05-07 12:28:36'),
(718, 3, 52, 'Seafood Pasta', 'seafood-pasta', '', 15000.00, NULL, 3, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(719, 3, 52, 'Spaghetti & Meatballs', 'spaghetti-meatballs', '', 8000.00, NULL, 4, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(720, 3, 52, 'Fettuccine Alfredo', 'fettuccine-alfredo', '', 16000.00, NULL, 5, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(721, 3, 52, 'Chicken Pesto Penne', 'chicken-pesto-penne', '', 13000.00, NULL, 6, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(722, 3, 52, 'Spaghetti Bolognese', 'spaghetti-bolognese', '', 15000.00, NULL, 7, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(723, 3, 52, 'Spaghetti Aglio Olio', 'spaghetti-aglio-olio', '', 6000.00, NULL, 8, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(724, 3, 52, 'Fettuccine Prawn Grill', 'fettuccine-prawn-grill', '', 7000.00, NULL, 9, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(725, 3, 53, 'Okro (Seafood)', 'okro-seafood', 'Served with semovita, eba, or pounded yam', 43659.00, NULL, 1, 1, '2026-02-13 10:57:53', '2026-05-07 12:09:58'),
(726, 3, 53, 'Eforiro (Seafood)', 'eforiro-seafood', 'Served with semovita, eba, or pounded yam', 43659.00, NULL, 2, 1, '2026-02-13 10:57:53', '2026-05-07 12:33:10'),
(727, 3, 53, 'Edikaikong (Seafood)', 'edikaikong-seafood', 'Served with semovita, eba, or pounded yam', 30000.00, NULL, 3, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(728, 3, 53, 'Egusi (Seafood)', 'egusi-seafood', 'Served with semovita, eba, or pounded yam', 30000.00, NULL, 4, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(729, 3, 53, 'Fisherman Soup (Croaker / Catfish)', 'fisherman-soup', 'Served with semovita, eba, or pounded yam', 30000.00, NULL, 5, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(730, 3, 53, 'Edikaikong (Regular)', 'edikaikong-regular', 'Served with semovita, eba, or pounded yam', 18000.00, NULL, 6, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(731, 3, 53, 'Eforiro (Regular)', 'eforiro-regular', 'Served with semovita, eba, or pounded yam', 18000.00, NULL, 7, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(732, 3, 53, 'Afang', 'afang', 'Served with semovita, eba, or pounded yam', 18000.00, NULL, 8, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(733, 3, 53, 'Ogbono', 'ogbono', 'Served with semovita, eba, or pounded yam', 18000.00, NULL, 9, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(734, 3, 54, 'Seafood Jollof Rice', 'seafood-jollof-rice', '', 43650.00, NULL, 1, 1, '2026-02-13 10:57:53', '2026-05-07 12:13:05'),
(735, 3, 54, 'D\'View Special Fried Rice', 'dview-special-fried-rice', '', 22457.00, NULL, 2, 1, '2026-02-13 10:57:53', '2026-05-07 12:30:24'),
(736, 3, 54, 'Jollof Rice Fiesta', 'jollof-rice-fiesta', '', 16000.00, NULL, 3, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(737, 3, 54, 'Isi Ewu', 'isi-ewu', '', 20000.00, NULL, 4, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(738, 3, 54, 'Yam Pottage', 'yam-pottage', '', 15000.00, NULL, 5, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(739, 3, 55, 'Jollof Rice', 'jollof-rice-side', 'freshly cooked smokey Nigerian jollof rice', 8610.00, NULL, 1, 1, '2026-02-13 10:57:53', '2026-05-07 12:04:10'),
(740, 3, 55, 'Fried Rice', 'fried-rice', '', 8610.00, NULL, 2, 1, '2026-02-13 10:57:53', '2026-05-07 12:37:06'),
(741, 3, 55, 'Fried Plantain', 'fried-plantain', '', 7000.00, NULL, 3, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(742, 3, 55, 'Yam Chips', 'yam-chips', '', 7000.00, NULL, 4, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(743, 3, 55, 'French Fries', 'french-fries', '', 5000.00, NULL, 5, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(744, 3, 55, 'Sweet Potato Fries', 'sweet-potato-fries', '', 5000.00, NULL, 6, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(745, 3, 55, 'Steamed Rice', 'steamed-rice', '', 5000.00, NULL, 7, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(746, 3, 55, 'Bread Rolls (2 pcs)', 'bread-rolls-2pcs', '', 1000.00, NULL, 8, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(747, 3, 55, 'Eggs (2)', 'eggs-2', '', 5000.00, NULL, 9, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(748, 3, 55, 'Ogbono Extra', 'ogbono-extra', '', 7000.00, NULL, 10, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(753, 13, 61, 'Veggie Omelet (D)(N)(V)', 'veggie-omelet', 'Three egg omelet cooked with diced \r\ntomatoes, onion\r\ngreen pepper, side vegetables and \r\noptional cheese served with toast bread \r\nand butter', 18000.00, NULL, 1, 1, '2026-03-13 09:42:21', '2026-03-21 09:45:54'),
(754, 13, 61, 'Eggs Your Way (D)(N)(V)', 'eggs-your-way', 'Three eggs your way – plain or cheese \r\nomelet, fried or scrambled, portion of \r\nwhole pan-fried button mushrooms\r\ngrilled tomatoes, accompanied by toast \r\nbread and butter', 15000.00, NULL, 2, 1, '2026-03-13 09:42:21', '2026-03-21 09:47:12'),
(755, 13, 61, 'Lusso Style Pancakes (D)(V)', 'lusso-style-pancakes', 'Triple stack pancakes with quenelle \r\ncream cheese, chantelle cream, jam, \r\nnuts, fried plantain or banana drizzled \r\nwith pure maple syrup or honey', 20000.00, NULL, 3, 1, '2026-03-13 09:42:21', '2026-03-21 09:48:32'),
(756, 13, 62, 'Smoked Salmon', 'smoked-salmon', NULL, 15000.00, NULL, 1, 1, '2026-03-13 09:42:21', '2026-03-13 09:42:21'),
(757, 13, 62, 'Chicken Sausage', 'chicken-sausage', NULL, 10000.00, NULL, 2, 1, '2026-03-13 09:42:21', '2026-03-13 09:42:21'),
(758, 13, 62, 'Bacon', 'bacon', NULL, 10000.00, NULL, 3, 1, '2026-03-13 09:42:21', '2026-03-13 09:42:21'),
(759, 13, 62, 'Hash Brown Potatoes (V)', 'hash-brown-potatoes', NULL, 8000.00, NULL, 4, 1, '2026-03-13 09:42:21', '2026-03-13 09:42:21'),
(760, 13, 62, 'Baked Beans (V)', 'baked-beans', NULL, 8000.00, NULL, 5, 1, '2026-03-13 09:42:21', '2026-03-13 09:42:21'),
(761, 13, 62, 'Beans Pottage (V)', 'nigerian-stewed-beans', '', 8000.00, NULL, 6, 1, '2026-03-13 09:42:21', '2026-04-02 13:45:52'),
(762, 13, 62, 'Dodo – Fried Plantain (V)', 'dodo-fried-plantain', NULL, 8000.00, NULL, 7, 1, '2026-03-13 09:42:21', '2026-03-13 09:42:21'),
(763, 13, 62, 'Moi-Moi (V)', 'moi-moi', NULL, 10000.00, NULL, 8, 1, '2026-03-13 09:42:21', '2026-03-13 09:42:21'),
(764, 13, 62, 'Akara (V)', 'akara', NULL, 10000.00, NULL, 9, 1, '2026-03-13 09:42:21', '2026-03-13 09:42:21'),
(765, 13, 62, 'Baker’s Basket', 'bakers-basket', NULL, 15000.00, NULL, 10, 1, '2026-03-13 09:42:21', '2026-03-13 09:42:21'),
(766, 13, 62, 'Grilled Mushrooms', 'grilled-mushrooms', NULL, 8000.00, NULL, 11, 1, '2026-03-13 09:42:21', '2026-03-13 09:42:21'),
(767, 13, 62, 'Fruit Salad (V)', 'fruit-salad', NULL, 10000.00, NULL, 12, 1, '2026-03-13 09:42:21', '2026-03-13 09:42:21'),
(769, 13, 63, 'Mini Continental Breakfast (D)(N)', 'mini-continental-breakfast', 'Glass of freshly squeezed juice, coffee or tea with \r\nbaker’s basket, butter and jams', 20000.00, NULL, 1, 1, '2026-03-13 09:42:21', '2026-03-21 09:50:46'),
(770, 13, 63, 'English Breakfast (D)(N)', 'english-breakfast', 'Baker\'s basket\r\nMorning rolls, croissant, pain au chocolate raisin whirl \r\nand toast, served with butter, selection of jams and honey\r\nYour style of eggs \r\nOmelette, scrambled, or fried served with chicken sausages, sauté potatoes, bacon, \r\nbutton mushrooms and grilled tomato\r\nRefreshing fresh pineapple or orange or watermelon \r\njuice, seasonal fruits slices\r\nFreshly brewed coffee, regular or decaffeinated tea \r\nor hot chocolate', 38000.00, NULL, 2, 1, '2026-03-13 09:42:21', '2026-03-21 09:54:53'),
(771, 13, 63, 'Rising Sun Continental Breakfast (D)(N)', 'rising-sun-continental-breakfast', 'Baker\'s basket\r\nMorning rolls, croissant, pain au chocolate raisin whirl \r\nand toast, served with butter, selection of jams and honey\r\nRefreshing fresh juice - pineapple or orange or watermelon\r\nSeasonal fruit slices, plain or flavoured yoghurt\r\nCereals\r\nChoice of muesli, cornflakes, rice crispy or fruit n fiber\r\nFreshly brewed coffee, regular or decaffeinated tea \r\nor hot chocolate', 35000.00, NULL, 3, 1, '2026-03-13 09:42:21', '2026-03-21 09:56:28'),
(772, 13, 63, 'Good Morning Lusso – Nigerian Breakfast (D)(N)', 'good-morning-lusso-nigerian-breakfast', 'Nigerian Breakfast\r\nRefreshing fresh juice - pineapple or orange or watermelon\r\nSeasonal fruit slices\r\nYour choice of ogi, oatmeal porridge or cereal of your choice \r\nServed with skimmed, full cream or soya milk\r\nTraditional Nigerian egg sauce garnished with fried plantain, \r\nMoi-moi or yam served with stew of the day\r\nFreshly brewed coffee, regular or decaffeinated tea or hot chocolate', 35000.00, NULL, 4, 1, '2026-03-13 09:42:21', '2026-03-21 09:57:41'),
(773, 13, 64, 'Nigerian Stew of the Day', 'nigerian-stew-of-the-day', 'Please enquire from your service attendant about our \r\nlovingly prepared stew of the day, served with your \r\npreferred starch- yam chips, white or traditional rice of \r\nthe day, dodo and coleslaw', 25000.00, NULL, 1, 1, '2026-03-13 09:42:21', '2026-03-23 13:23:35'),
(774, 13, 64, 'Nigerian Soup of the Day (N)', 'nigerian-soup-of-the-day', 'Please enquire from your service attendant about our \r\nauthentic traditional soups of the day, served with your \r\nchoice of freshly preferred swallow', 25000.00, NULL, 2, 1, '2026-03-13 09:42:21', '2026-03-21 10:16:10'),
(775, 13, 64, 'Pepper Snails (D)(N)(S)', 'pepper-snails', 'Stewed giant African snails, braised in African chili \r\nsauce, onion and local hot pepper, accompanied by \r\nyour choice of fried plantain or rice of the day \r\nor French fries', 40000.00, NULL, 3, 1, '2026-03-13 09:42:21', '2026-03-21 10:17:33'),
(776, 13, 64, 'Spicy Half BBQ Chicken (D)(N)', 'spicy-half-bbq-chicken', 'Half BBQ chicken, roasted to perfection, served with \r\nside of Fried plantain or rice of the day of French fries', 22000.00, NULL, 4, 1, '2026-03-13 09:42:21', '2026-03-21 10:18:50'),
(777, 13, 65, 'Swallow of the Day (V)', 'swallow-of-the-day', NULL, 7000.00, NULL, 1, 1, '2026-03-13 09:42:21', '2026-03-13 09:42:21'),
(778, 13, 65, 'Rice of the Day (V)', 'rice-of-the-day', NULL, 7000.00, NULL, 2, 1, '2026-03-13 09:42:21', '2026-03-13 09:42:21'),
(779, 13, 65, 'Basmati Rice (V)', 'basmati-rice', NULL, 8000.00, NULL, 3, 1, '2026-03-13 09:42:21', '2026-03-13 09:42:21'),
(781, 13, 65, 'Fried Plantain', 'fried-plantain', NULL, 7000.00, NULL, 5, 1, '2026-03-13 09:42:21', '2026-03-13 09:42:21'),
(787, 13, 67, 'Smoked Salmon Rosette', 'smoked-salmon-rosette', 'Smoked salmon rosette with coddled egg, garden salad, \r\nsmooth cream cheese, French dressing capers and \r\nred onion with a hint of lemon', 28000.00, NULL, 3, 1, '2026-03-13 09:42:21', '2026-03-27 13:17:15'),
(788, 13, 67, 'Greek Village Salad (D)(N)(V)', 'greek-village-salad', 'Greek delicacy of feta cheese combined with fresh organic lettuce, \r\nTomato, cucumber, olives, and red onion, dressed with French vinaigrette', 17000.00, NULL, 2, 1, '2026-03-13 09:42:21', '2026-03-21 10:00:21'),
(789, 13, 67, 'Chicken Caesar Salad (D)(N)(S)', 'chicken-caesar-salad', '220g BBQ chicken fillet grilled to perfection, on a bed of iceberg \r\nlocal lettuce with flavorsome homemade garlic croutons, \r\nfreshly grated Italian parmesan cheese, accompanied \r\nby our Chef’s Caesar dressing and a hint of anchovies', 22000.00, NULL, 3, 1, '2026-03-13 09:42:21', '2026-03-21 10:01:34'),
(792, 13, 69, 'Curried Veggie Delight (D)(N)(V)', 'curried-veggie-delight', 'Chickpeas, potato & lentil dahl masala with basmati rice \r\naccompaniment of plain yoghurt, served with chapatti & hot chili pepper \r\nsauce', 18000.00, NULL, 1, 1, '2026-03-13 09:42:21', '2026-03-21 10:08:46'),
(793, 13, 69, 'Spicy Penne Arrabiata (D)(N)', 'spicy-penne-arrabiata', 'Penne pasta with black olive, mixed bell peppers, onion & garlic tossed \r\nin chili pepper sauce, served with gratinated French bread & parmesan \r\ncheese', 20000.00, NULL, 2, 1, '2026-03-13 09:42:21', '2026-03-21 10:10:11'),
(794, 13, 70, 'Succulent Beef Burger (D)(N)', 'succulent-beef-burger', '250g pure ground beef BBQ patty, grilled to perfection \r\nwith gratinated mozzarella cheese', 25000.00, NULL, 1, 1, '2026-03-13 09:42:21', '2026-03-21 10:12:51'),
(795, 13, 70, 'Chicken Burger (D)(N)', 'chicken-burger', '220g Chef’s secret breaded butterflied chicken, \r\nfilled with gratinated mozzarella cheese', 24000.00, NULL, 2, 1, '2026-03-13 09:42:21', '2026-03-21 10:14:04'),
(798, 13, 71, 'Fillet Mignon (D)', 'fillet-mignon', '300g cut of the finest grass-fed cattle, fillet of beef, \r\ntenderly grilled, topped with whole garlic butter \r\nmushrooms & aromatic black peppers, sea salt', 55000.00, NULL, 3, 1, '2026-03-13 09:42:21', '2026-04-25 13:11:52'),
(800, 13, 71, 'Suya Style (D)(N)', 'suya-style', 'Nigerian most popular delicacy, a combination of two \r\nsticks of chicken and two sticks of beef suya, specially \r\nspiced, char grilled served with fresh onion, tomatoes, \r\nfries or fried plantain', 25000.00, NULL, 5, 1, '2026-03-13 09:42:21', '2026-03-23 13:31:11'),
(801, 13, 72, 'Lusso Club Sandwich (D)(N)', 'lusso-club-sandwich', 'Double decker with chicken & turkey ham, fried eggs, lettuce \r\ntomato, pickles, mayonnaise spread & mozzarella cheese \r\naccompanied with fries and homemade coleslaw', 18000.00, NULL, 1, 1, '2026-03-13 09:42:21', '2026-03-21 10:28:59'),
(802, 13, 72, 'Perinaise Chicken Wrap (D)(N)', 'perinaise-chicken-wrap', 'Delicious, coated chicken strips in a flour tortilla filled with \r\nsmooth cream cheese, chiffonade of lettuce & diced onion with our \r\nsecret Nigerian perinaise sauce accompanied \r\nby fries and homemade coleslaw', 20000.00, NULL, 2, 1, '2026-03-13 09:42:21', '2026-03-21 10:29:53');
INSERT INTO `menu_items` (`id`, `restaurant_id`, `category_id`, `name`, `slug`, `description`, `price`, `image`, `display_order`, `is_available`, `created_at`, `updated_at`) VALUES
(803, 13, 73, 'Kids Omelet (D)(V)', 'kids-omelet', 'Served with fresh seasonal steamed vegetables', 8000.00, NULL, 1, 1, '2026-03-13 09:42:21', '2026-03-21 10:41:18'),
(804, 13, 73, 'Spaghetti (D)(V)', 'spaghetti-with-tomato-sauce', 'Served with tomato sauce', 10000.00, NULL, 2, 1, '2026-03-13 09:42:21', '2026-03-21 10:42:24'),
(805, 13, 73, 'Cocktail Beef & Cheese Slider (D)(N)', 'cocktail-beef-cheese-slider', 'Served with French fries', 15000.00, NULL, 3, 1, '2026-03-13 09:42:21', '2026-03-21 10:43:07'),
(806, 13, 73, 'Corn Fried Chicken Nuggets (D)(N)', 'corn-fried-chicken-nuggets', 'Accompanied with French fries', 15000.00, NULL, 4, 1, '2026-03-13 09:42:21', '2026-03-21 10:43:53'),
(807, 13, 73, 'Mini Margherita Pizza (D)(V)', 'mini-margherita-pizza', NULL, 10000.00, NULL, 5, 1, '2026-03-13 09:42:21', '2026-03-13 09:42:21'),
(808, 13, 74, 'Crème Brûlée (D)', 'creme-brulee', NULL, 12000.00, NULL, 1, 1, '2026-03-13 09:42:21', '2026-03-13 09:42:21'),
(809, 13, 74, 'Chocolate Brownie with Vanilla Ice Cream (D)(N)', 'chocolate-brownie-with-vanilla-ice-cream', NULL, 18000.00, NULL, 2, 1, '2026-03-13 09:42:21', '2026-03-13 09:42:21'),
(810, 13, 74, 'Exotic Fruit Platter (V)', 'exotic-fruit-platter', NULL, 10000.00, NULL, 3, 1, '2026-03-13 09:42:21', '2026-03-13 09:42:21'),
(811, 13, 74, 'Vanilla Ice Cream & Cookies (D)(N)', 'vanilla-ice-cream-cookies', NULL, 16000.00, NULL, 4, 1, '2026-03-13 09:42:21', '2026-03-13 09:42:21'),
(812, 13, 75, 'Greek Village Salad 🥛🌰', 'greek-village-salad', 'Rocket leaves, grilled peppers, marinated olives and feta cheese finished with olive oil and balsamic reduction.', 18000.00, NULL, 1, 1, '2026-03-13 10:13:02', '2026-03-23 14:23:25'),
(813, 13, 75, 'Asian Prawn Salad 🦐🌰🌶️', 'asian-prawn-salad', 'Lemon and garlic marinated prawns served on crisp Asian slaw with green apple slices, toasted sesame seeds and sweet chili dressing.', 25000.00, NULL, 2, 1, '2026-03-13 10:13:02', '2026-03-13 10:13:02'),
(814, 13, 75, 'Local Papaya Salad 🥛', 'local-papaya-salad', 'Lettuce, pawpaw, tomato, watermelon, pineapple and feta cheese with lime ranch dressing.', 17000.00, NULL, 4, 1, '2026-03-13 10:13:02', '2026-03-27 13:20:08'),
(815, 13, 76, 'Spiced Halloumi Wrap 🌾🥛', 'spiced-halloumi-wrap', 'Grilled halloumi cheese wrapped with lettuce in tortilla bread.', 15000.00, NULL, 1, 1, '2026-03-13 10:13:02', '2026-03-13 10:13:02'),
(817, 13, 78, 'Aglio Olio Prawn Pasta 🦐🌾🍷🥛', 'aglio-olio-prawn-pasta', 'Pasta tossed in garlic and herb infusion with capsicum and white wine, finished with prawns and seafood, served with gratinated capsicum and cheese bruschetta.', 30000.00, NULL, 1, 1, '2026-03-13 10:13:02', '2026-03-23 14:13:07'),
(818, 13, 78, 'Turkey Ham Carbonara 🌾🥛🥚', 'turkey-ham-carbonara', 'Turkey ham in rich creamy carbonara sauce with egg yolk and freshly grated parmesan, served with gratinated capsicum and cheese bruschetta.', 30000.00, NULL, 2, 1, '2026-03-13 10:13:02', '2026-03-23 14:13:42'),
(819, 13, 79, 'Caprese Margherita (V) 🌾🥛', 'caprese-margherita', 'Tomato basil sauce, mozzarella, plum tomatoes and olive oil.', 16000.00, NULL, 1, 1, '2026-03-13 10:13:02', '2026-03-13 10:13:02'),
(820, 13, 79, 'Seafood Alforno 🦐🐟🌾🥛', 'seafood-alforno', 'Shrimps, calamari, octopus, basil, peppers and mozzarella.', 25000.00, NULL, 2, 1, '2026-03-13 10:13:02', '2026-03-13 10:13:02'),
(821, 13, 79, 'Dodo & Chicken Pizza 🌾🥛', 'dodo-chicken-pizza', 'Plantain with grilled chicken, peppers, basil and mozzarella.', 22000.00, NULL, 3, 1, '2026-03-13 10:13:02', '2026-03-13 10:13:02'),
(822, 13, 80, 'Herb-Crusted Rack of Lamb 🥛', 'herb-crusted-rack-of-lamb', 'Oven roasted rack of lamb with mint jelly crust, char-grilled ratatouille, creamy cheddar mashed potatoes and garlic herb jus.', 49000.00, NULL, 1, 1, '2026-03-13 10:13:02', '2026-03-13 10:13:02'),
(823, 13, 80, 'Crown of Beef 🌾🥛🍷', 'crown-of-beef', 'Flame grilled beef fillet medallion wrapped in puff pastry, served with baby vegetables, crispy pommes allumettes, gratinated fondant potatoes and cream onion truffle wine sauce.', 35000.00, NULL, 2, 1, '2026-03-13 10:13:02', '2026-03-13 10:13:02'),
(824, 13, 81, 'Semi-Bone-Out Half Chicken 🌾🌶️', 'semi-bone-out-half-chicken', 'Grilled and oven finished half chicken seasoned with Peri-Peri or Lemon & Herb, served with crispy potato wedges, micro leaf salad, lentil onion bread stuffing and sauce of choice.', 32000.00, NULL, 1, 1, '2026-03-13 10:13:02', '2026-03-26 09:44:19'),
(826, 13, 82, 'Parmesan Mussel & Herb–Encrusted Croaker 🐟🦐🥛', 'parmesan-mussel-herb-encrusted-croaker', 'Grilled croaker encrusted with parmesan, herbs and mussels, served with buttered baby vegetables and rustic potatoes finished with fish velouté.', 35000.00, NULL, 1, 1, '2026-03-13 10:13:02', '2026-03-13 10:13:02'),
(827, 13, 82, 'Teriyaki Salmon 🐟🌰🍷', 'teriyaki-salmon', 'Grilled salmon glazed with soy, honey, chili, sesame oil, garlic and pickled ginger, served with julienne vegetables and wok egg noodles.', 49000.00, NULL, 2, 1, '2026-03-13 10:13:02', '2026-03-13 10:13:02'),
(828, 13, 83, 'Crème Brûlée with Chocolate Chip Biscuit 🥛🥚🌾', 'creme-brulee-chocolate-chip-biscuit', 'Light baked custard with caramelized crust served with berry coulis and chocolate chip biscuit.', 12000.00, NULL, 1, 1, '2026-03-13 10:13:02', '2026-03-26 09:49:30'),
(829, 13, 83, 'Ice Cream 🥛🥚', 'ice-cream', 'Ask your waiter for today’s flavor, topped with chocolate sprinkles and butter biscuit.', 15000.00, NULL, 2, 1, '2026-03-13 10:13:02', '2026-03-13 10:13:02'),
(830, 13, 83, 'Freshly Cut Fruit Salad', 'freshly-cut-fruit-salad', 'Seasonal fresh fruit medley.', 9000.00, NULL, 3, 1, '2026-03-13 10:13:02', '2026-03-26 09:48:37'),
(831, 13, 84, 'Still Water Large', 'still-water-large', NULL, 4000.00, NULL, 1, 1, '2026-03-14 17:42:54', '2026-03-14 17:42:54'),
(832, 13, 84, 'Still Water Small', 'still-water-small', NULL, 3000.00, NULL, 2, 1, '2026-03-14 17:42:54', '2026-03-14 17:42:54'),
(833, 13, 84, 'Perrier Sparkling Water Large', 'perrier-sparkling-water-large', '', 20000.00, NULL, 3, 1, '2026-03-14 17:42:54', '2026-03-26 10:09:15'),
(834, 13, 84, 'Perrier Sparkling Water Small', 'perrier-sparkling-water-small', '', 8000.00, NULL, 4, 1, '2026-03-14 17:42:54', '2026-03-26 10:09:50'),
(835, 13, 84, 'Soft Drinks (Coca Cola, Sprite, Tonic, Bitter Lemon, Soda Water, Fanta, Pepsi, Mirinda)', 'soft-drinks-mix', NULL, 3500.00, NULL, 5, 1, '2026-03-14 17:42:54', '2026-03-14 17:42:54'),
(836, 13, 84, 'Diet Coke', 'diet-coke', NULL, 0.00, NULL, 6, 1, '2026-03-14 17:42:54', '2026-03-14 17:42:54'),
(837, 13, 84, 'Maltina', 'maltina', NULL, 4500.00, NULL, 7, 1, '2026-03-14 17:42:54', '2026-03-14 17:42:54'),
(838, 13, 84, 'Amstel Malta', 'amstel-malta', NULL, 4500.00, NULL, 8, 1, '2026-03-14 17:42:54', '2026-03-14 17:42:54'),
(839, 13, 84, 'Malta Guinness', 'malta-guinness', NULL, 4500.00, NULL, 9, 1, '2026-03-14 17:42:54', '2026-03-14 17:42:54'),
(840, 13, 84, 'Fayrous', 'fayrous', NULL, 4500.00, NULL, 10, 1, '2026-03-14 17:42:54', '2026-03-14 17:42:54'),
(841, 13, 85, 'Fresh Juice Large', 'fresh-juice-large', NULL, 7000.00, NULL, 1, 1, '2026-03-14 17:42:54', '2026-03-14 17:42:54'),
(842, 13, 85, 'Fresh Juice Small', 'fresh-juice-small', NULL, 5000.00, NULL, 2, 1, '2026-03-14 17:42:54', '2026-03-14 17:42:54'),
(843, 13, 85, 'Fresh Fruit Punch Large', 'fresh-fruit-punch-large', NULL, 7000.00, NULL, 3, 1, '2026-03-14 17:42:54', '2026-03-14 17:42:54'),
(844, 13, 85, 'Fresh Fruit Punch Small', 'fresh-fruit-punch-small', NULL, 5500.00, NULL, 4, 1, '2026-03-14 17:42:54', '2026-03-14 17:42:54'),
(845, 13, 85, 'Packet Juice Large', 'packet-juice-large', NULL, 4500.00, NULL, 5, 1, '2026-03-14 17:42:54', '2026-03-14 17:42:54'),
(846, 13, 85, 'Packet Juice Small', 'packet-juice-small', NULL, 4000.00, NULL, 6, 1, '2026-03-14 17:42:54', '2026-03-14 17:42:54'),
(847, 13, 85, 'Juice Packet', 'juice-packet', NULL, 12000.00, NULL, 7, 1, '2026-03-14 17:42:54', '2026-03-14 17:42:54'),
(848, 13, 85, 'Cranberry Packet', 'cranberry-packet', NULL, 25000.00, NULL, 8, 1, '2026-03-14 17:42:54', '2026-03-14 17:42:54'),
(849, 13, 85, 'Cranberry Glass', 'cranberry-glass', NULL, 9000.00, NULL, 9, 1, '2026-03-14 17:42:54', '2026-03-14 17:42:54'),
(850, 13, 86, 'Power Horse', 'power-horse', NULL, 6000.00, NULL, 1, 1, '2026-03-14 17:42:54', '2026-03-14 17:42:54'),
(851, 13, 86, 'Red Bull', 'red-bull', NULL, 6500.00, NULL, 2, 1, '2026-03-14 17:42:54', '2026-03-14 17:42:54'),
(852, 13, 86, 'Climax', 'climax', NULL, 6000.00, NULL, 3, 1, '2026-03-14 17:42:54', '2026-03-14 17:42:54'),
(853, 13, 87, 'Star', 'star', NULL, 5000.00, NULL, 1, 1, '2026-03-14 17:42:54', '2026-03-14 17:42:54'),
(854, 13, 87, 'Heineken 60CL', 'heineken', '', 6500.00, NULL, 2, 1, '2026-03-14 17:42:54', '2026-04-22 11:57:32'),
(855, 13, 87, 'Heineken Draught Large', 'heineken-draught-large', '', 6500.00, NULL, 3, 1, '2026-03-14 17:42:54', '2026-04-22 11:58:25'),
(856, 13, 87, 'Heineken Draught Small', 'heineken-draught-small', '', 5500.00, NULL, 4, 1, '2026-03-14 17:42:54', '2026-04-22 11:59:06'),
(857, 13, 87, 'Budweiser', 'budweiser', '', 6500.00, NULL, 5, 1, '2026-03-14 17:42:54', '2026-04-22 12:03:12'),
(858, 13, 87, 'Guinness Extra Smooth', 'guinness-extra-smooth', NULL, 5000.00, NULL, 6, 1, '2026-03-14 17:42:54', '2026-03-14 17:42:54'),
(859, 13, 87, 'Guinness Stout 60cl', 'guinness-stout-60cl', '', 6500.00, NULL, 7, 1, '2026-03-14 17:42:54', '2026-04-22 12:04:00'),
(860, 13, 87, 'Guinness Stout Medium', 'guinness-stout-medium', '', 5500.00, NULL, 8, 1, '2026-03-14 17:42:54', '2026-04-22 12:06:07'),
(861, 13, 87, 'Star Radler Citrus', 'star-radler-citrus', NULL, 4500.00, NULL, 9, 1, '2026-03-14 17:42:54', '2026-03-14 17:42:54'),
(862, 13, 87, 'Trophy', 'trophy', '', 5000.00, NULL, 10, 1, '2026-03-14 17:42:54', '2026-04-22 12:01:47'),
(863, 13, 87, '33 Export', '33-export', '', 5000.00, NULL, 11, 1, '2026-03-14 17:42:54', '2026-04-22 12:07:23'),
(864, 13, 87, 'Life Beer', 'life-beer', '', 5000.00, NULL, 12, 1, '2026-03-14 17:42:54', '2026-04-22 12:08:06'),
(865, 13, 87, 'Hero Beer', 'hero-beer', '', 5000.00, NULL, 13, 1, '2026-03-14 17:42:54', '2026-04-22 12:08:56'),
(866, 13, 87, 'Gulder', 'gulder', NULL, 5000.00, NULL, 14, 1, '2026-03-14 17:42:54', '2026-03-14 17:42:54'),
(867, 13, 87, 'Goldberg', 'goldberg', '', 5000.00, NULL, 15, 1, '2026-03-14 17:42:54', '2026-04-22 12:09:38'),
(868, 13, 87, 'Legend', 'legend', '', 5500.00, NULL, 16, 1, '2026-03-14 17:42:54', '2026-04-22 12:10:27'),
(869, 13, 87, 'Tiger Beer', 'tiger-beer', '', 5000.00, NULL, 17, 1, '2026-03-14 17:42:54', '2026-04-22 12:13:10'),
(870, 13, 87, 'Origin Beer', 'origin-beer', NULL, 5000.00, NULL, 18, 1, '2026-03-14 17:42:54', '2026-03-14 17:42:54'),
(871, 13, 87, 'Smirnoff Double Black', 'smirnoff-double-black', '', 5000.00, NULL, 19, 1, '2026-03-14 17:42:54', '2026-04-22 12:00:23'),
(872, 13, 87, 'Smirnoff Ice 60cl', 'smirnoff-ice-60cl', NULL, 6000.00, NULL, 20, 1, '2026-03-14 17:42:54', '2026-03-14 17:42:54'),
(873, 13, 87, 'Smirnoff Ice 35cl', 'smirnoff-ice-35cl', NULL, 4500.00, NULL, 21, 1, '2026-03-14 17:42:54', '2026-03-14 17:42:54'),
(874, 13, 87, 'Desperado', 'desperado', NULL, 5000.00, NULL, 22, 1, '2026-03-14 17:42:54', '2026-03-14 17:42:54'),
(875, 13, 87, 'Flying Fish', 'flying-fish', NULL, 4500.00, NULL, 23, 1, '2026-03-14 17:42:54', '2026-03-14 17:42:54'),
(876, 13, 87, 'Castle Lite', 'castle-lite', NULL, 5000.00, NULL, 24, 1, '2026-03-14 17:42:54', '2026-03-14 17:42:54'),
(877, 13, 88, 'Martini Bianco', 'martini-bianco', 'Tot  N5,000', 50000.00, NULL, 1, 1, '2026-03-14 17:42:54', '2026-03-19 11:36:07'),
(878, 13, 88, 'Martini Rosso', 'martini-rorro', 'Tot  N6,000', 60000.00, NULL, 2, 1, '2026-03-14 17:42:54', '2026-03-19 11:37:23'),
(879, 13, 88, 'Martini Extra Dry', 'martini-extra-dry', 'Tot  5,000', 60000.00, NULL, 3, 1, '2026-03-14 17:42:54', '2026-03-19 11:43:28'),
(880, 13, 88, 'Aperol Aperitivo', 'aperol-aperitivo', 'Tot  8,000', 80000.00, NULL, 4, 1, '2026-03-14 17:42:54', '2026-03-19 11:44:00'),
(881, 13, 88, 'Campari', 'campari', 'Tot  9,000', 80000.00, NULL, 5, 1, '2026-03-14 17:42:54', '2026-03-26 10:12:52'),
(882, 13, 89, 'Gordon', 'gordon', 'Tot  5,000', 40000.00, NULL, 1, 1, '2026-03-14 17:42:54', '2026-03-26 10:15:02'),
(883, 13, 89, 'Beefeater', 'beefeater', 'Tot  5,500', 45000.00, NULL, 2, 1, '2026-03-14 17:42:54', '2026-03-26 10:15:43'),
(884, 13, 89, 'Bombay Sapphire', 'bombay-sapphire', 'Tot  8,000', 80000.00, NULL, 3, 1, '2026-03-14 17:42:54', '2026-03-19 11:46:54'),
(885, 13, 89, 'Hendrick', 'hendrick', 'Tot  10,000', 140000.00, NULL, 4, 1, '2026-03-14 17:42:54', '2026-03-19 15:24:59'),
(886, 13, 89, 'Tanqueray 10', 'tanqueray-10', 'Tot   15,000', 140000.00, NULL, 5, 1, '2026-03-14 17:42:54', '2026-03-26 10:16:29'),
(887, 13, 89, 'Monkey 47', 'monkey-47', 'Tot  10,000', 120000.00, NULL, 6, 1, '2026-03-14 17:42:54', '2026-03-26 10:17:01'),
(891, 13, 91, 'Macallan 12 years', 'macallan-12-years', 'Tot  15,000', 250000.00, NULL, 1, 1, '2026-03-14 17:42:54', '2026-03-19 12:05:38'),
(892, 13, 91, 'Macallan 15 years', 'macallan-15-years', 'Tot  28,000', 600000.00, NULL, 2, 1, '2026-03-14 17:42:54', '2026-03-19 12:06:15'),
(893, 13, 91, 'Macallan 18 years', 'macallan-18-years', 'Tot 100,000', 1600000.00, NULL, 3, 1, '2026-03-14 17:42:54', '2026-03-26 10:29:07'),
(894, 13, 91, 'Glenfiddich 12 years', 'glenfiddich-12-years', 'Tot  14,000', 190000.00, NULL, 4, 1, '2026-03-14 17:42:54', '2026-03-26 10:30:13'),
(895, 13, 91, 'Glenfiddich 15 years', 'glenfiddich-15-years', 'Tot  25,000', 300000.00, NULL, 5, 1, '2026-03-14 17:42:54', '2026-04-28 11:39:19'),
(896, 13, 91, 'Glenfiddich 18 years', 'glenfiddich-18-years', 'Tot  30,000', 380000.00, NULL, 6, 1, '2026-03-14 17:42:54', '2026-03-26 10:32:03'),
(897, 13, 91, 'Singleton 12 years', 'singleton-12-years', 'Tot  14,000', 200000.00, NULL, 7, 1, '2026-03-14 17:42:54', '2026-03-19 12:10:12'),
(898, 13, 91, 'Singleton 15 years', 'singleton-15-years', 'Tot   25,000', 260000.00, NULL, 8, 1, '2026-03-14 17:42:54', '2026-03-26 10:33:29'),
(899, 13, 91, 'Singleton 18 years', 'singleton-18-years', 'Tot  45,000', 650000.00, NULL, 9, 1, '2026-03-14 17:42:54', '2026-03-19 12:11:58'),
(900, 13, 92, 'Chivas Regal 12 years', 'chivas-regal-12-years', 'Tot  7,500', 120000.00, NULL, 1, 1, '2026-03-14 17:42:54', '2026-03-19 11:52:07'),
(901, 13, 92, 'Chivas Regal 18 years', 'chivas-regal-18-years', 'Tot 12,500', 310000.00, NULL, 2, 1, '2026-03-14 17:42:54', '2026-03-19 11:53:01'),
(902, 13, 92, 'Smokey Monkey', 'smokey-monkey', 'Tot  12,000', 200000.00, NULL, 3, 1, '2026-03-14 17:42:54', '2026-03-19 11:54:54'),
(903, 13, 92, 'Chivas Regal 25 years', 'chivas-regal-25-years', 'Tot  45,000', 800000.00, NULL, 4, 1, '2026-03-14 17:42:54', '2026-03-19 11:55:49'),
(905, 13, 92, 'Johnnie Walker Black Label', 'johnnie-walker-black-label', 'Tot  9,000', 120000.00, NULL, 6, 1, '2026-03-14 17:42:54', '2026-03-19 11:57:48'),
(906, 13, 92, 'Johnnie Walker Gold Label', 'johnnie-walker-gold-label', 'Tot  20,000', 240000.00, NULL, 7, 1, '2026-03-14 17:42:54', '2026-03-19 11:58:44'),
(907, 13, 92, 'Johnnie Walker Platinum Label', 'johnnie-walker-platinum-label', 'Tot  28,000', 450000.00, NULL, 8, 1, '2026-03-14 17:42:54', '2026-03-19 12:00:44'),
(908, 13, 92, 'Johnnie Walker Blue Label', 'johnnie-walker-blue-label', 'Tot  90,000', 1100000.00, NULL, 9, 1, '2026-03-14 17:42:54', '2026-04-28 11:35:15'),
(909, 13, 93, 'Jameson', 'jameson', 'Tot  8,000', 60000.00, NULL, 1, 1, '2026-03-14 17:42:54', '2026-03-26 10:35:44'),
(910, 13, 93, 'Jameson Black Barrel', 'jameson-black-barrel', 'Tot  12,,000', 130000.00, NULL, 2, 1, '2026-03-14 17:42:54', '2026-03-26 10:36:24'),
(911, 13, 93, 'Jack Daniel', 'jack-daniel', 'Tot  8,000', 90000.00, NULL, 3, 1, '2026-03-14 17:42:54', '2026-04-28 11:32:15'),
(912, 13, 93, 'Jack Daniel Gentleman Jack', 'jack-daniel-gentleman-jack', 'Tot  10,000', 140000.00, NULL, 4, 1, '2026-03-14 17:42:54', '2026-03-26 10:38:01'),
(913, 13, 93, 'Jack Daniel Honey', 'jack-daniel-honey', 'Tot   9,000', 80000.00, NULL, 5, 1, '2026-03-14 17:42:54', '2026-03-26 10:39:16'),
(914, 13, 93, 'Jack Daniel Single Barrel Select', 'jack-daniel-single-barrel-select', 'Tot  15,000', 140000.00, NULL, 6, 1, '2026-03-14 17:42:54', '2026-03-26 10:40:14'),
(915, 13, 93, 'Jack Daniel Apple', 'jack-daniel-apple', 'Tot  9,000', 90000.00, NULL, 7, 1, '2026-03-14 17:42:54', '2026-04-28 11:36:56'),
(916, 13, 93, 'Woodford Reserve', 'woodford-reserve', 'Tot 13,000', 130000.00, NULL, 8, 1, '2026-03-14 17:42:54', '2026-03-26 10:41:27'),
(917, 13, 93, 'Wild Turkey', 'wild-turkey', 'Tot  8,000', 80000.00, NULL, 9, 1, '2026-03-14 17:42:54', '2026-04-28 12:16:28'),
(918, 13, 94, 'Smirnoff Red', 'smirnoff-red', 'Tot  6,000', 70000.00, NULL, 1, 1, '2026-03-14 17:42:54', '2026-03-19 13:27:36'),
(919, 13, 94, 'Smirnoff Blue', 'smirnoff-blue', 'Tot  6,000', 75000.00, NULL, 2, 1, '2026-03-14 17:42:54', '2026-03-19 13:28:19'),
(920, 13, 94, 'Ciroc', 'ciroc', 'Tot  10,000', 120000.00, NULL, 3, 1, '2026-03-14 17:42:54', '2026-03-26 10:44:10'),
(921, 13, 94, 'Neft Vodka', 'neft-vodka', 'Tot  10,000', 130000.00, NULL, 4, 1, '2026-03-14 17:42:54', '2026-03-19 13:31:23'),
(922, 13, 94, 'Absolut Blue', 'absolut-blue', 'Tot  5,500', 65000.00, NULL, 5, 1, '2026-03-14 17:42:54', '2026-03-19 13:31:58'),
(923, 13, 94, 'Grey Goose', 'grey-goose', 'Tot  12,000', 150000.00, NULL, 6, 1, '2026-03-14 17:42:54', '2026-03-26 10:44:57'),
(924, 13, 95, 'Bacardi', 'bacardi', 'Tot  5,000', 60000.00, NULL, 1, 1, '2026-03-14 17:42:54', '2026-03-19 13:33:28'),
(925, 13, 95, 'Captain Morgan', 'captain-morgan', 'Tot  5,000', 50000.00, NULL, 2, 1, '2026-03-14 17:42:54', '2026-03-19 13:34:11'),
(926, 13, 95, 'St James', 'st-james', 'Tot  8,000', 99000.00, NULL, 3, 1, '2026-03-14 17:42:54', '2026-03-19 13:34:56'),
(927, 13, 95, 'Malibu', 'malibu', 'Tot  5,000', 60000.00, NULL, 4, 1, '2026-03-14 17:42:54', '2026-03-19 13:35:31'),
(928, 13, 96, 'Remy Martin XO', 'remy-martin-xo', 'Tot  75,000', 1100000.00, NULL, 1, 1, '2026-03-14 17:42:54', '2026-03-26 10:53:20'),
(929, 13, 96, 'Remy Martin VSOP', 'remy-martin-vsop', 'Tot 25,000', 300000.00, NULL, 2, 1, '2026-03-14 17:42:54', '2026-03-26 10:54:01'),
(930, 13, 96, 'Hennessy XO', 'hennessy-xo', 'Tot  75,000', 1200000.00, NULL, 3, 1, '2026-03-14 17:42:54', '2026-03-19 13:48:11'),
(931, 13, 96, 'Hennessy VSOP', 'hennessy-vsop', 'Tot  25,000', 320000.00, NULL, 4, 1, '2026-03-14 17:42:54', '2026-04-28 11:45:46'),
(932, 13, 96, 'Hennessy VS', 'hennessy-vs', 'Tot 20,000', 160000.00, NULL, 5, 1, '2026-03-14 17:42:54', '2026-03-26 10:55:53'),
(933, 13, 96, 'Martel VS', 'martel-vs', 'Tot 14,000', 150000.00, NULL, 6, 1, '2026-03-14 17:42:54', '2026-03-19 13:52:13'),
(934, 13, 96, 'Martel Blue Swift', 'martel-blue-swift', 'Tot  20,000', 260000.00, NULL, 7, 1, '2026-03-14 17:42:54', '2026-04-28 11:59:28'),
(935, 13, 96, 'Martel XO', 'martel-xo', 'Tot  65,000', 1000000.00, NULL, 8, 1, '2026-03-14 17:42:54', '2026-03-26 11:02:10'),
(936, 13, 96, 'Remy Martin 1738', 'remy-martin-1738', 'Tot  30,000', 350000.00, NULL, 9, 1, '2026-03-14 17:42:54', '2026-03-26 11:02:57'),
(937, 13, 97, 'Olmeca Gold', 'olmeca-gold', 'Tot  8,000', 80000.00, NULL, 1, 1, '2026-03-14 17:42:54', '2026-03-19 13:57:01'),
(938, 13, 97, 'El Padrino', 'el-padrino', 'Tot  14,000', 230000.00, NULL, 2, 1, '2026-03-14 17:42:54', '2026-04-22 12:22:19'),
(939, 13, 97, 'Sierra Gold', 'sierra-gold', 'Tot  7,000', 70000.00, NULL, 3, 1, '2026-03-14 17:42:54', '2026-03-19 14:01:52'),
(940, 13, 97, 'Sierra White', 'sierra-white', 'Tot  7,000', 70000.00, NULL, 4, 1, '2026-03-14 17:42:54', '2026-03-19 14:03:14'),
(941, 13, 97, 'Cazcabel Reposado', 'cazcabel-reposado', 'Tot  12,000', 160000.00, NULL, 5, 1, '2026-03-14 17:42:54', '2026-03-19 14:04:48'),
(944, 13, 98, 'Cointreau', 'cointreau', 'Tot  8,000', 90000.00, NULL, 3, 1, '2026-03-14 17:42:54', '2026-03-19 14:37:29'),
(945, 13, 98, 'Baileys Irish Cream', 'baileys-irish-cream', 'Tot  8,000', 80000.00, NULL, 4, 1, '2026-03-14 17:42:54', '2026-03-19 14:42:18'),
(946, 13, 98, 'Amarula', 'amarula', 'Tot  8,000', 80000.00, NULL, 5, 1, '2026-03-14 17:42:54', '2026-03-19 14:43:24'),
(947, 13, 98, 'Amaretto', 'amaretto', 'Tot  8,000', 80000.00, NULL, 6, 1, '2026-03-14 17:42:54', '2026-03-19 14:44:23'),
(948, 13, 98, 'Tia Maria', 'tia-maria', 'Tot  8,000', 80000.00, NULL, 7, 1, '2026-03-14 17:42:54', '2026-03-19 14:45:22'),
(949, 13, 98, 'Sambuca', 'sambuca', 'Tot  7,000', 75000.00, NULL, 8, 1, '2026-03-14 17:42:54', '2026-03-19 14:46:51'),
(950, 13, 98, 'Drambuie', 'drambuie', 'Tot  7,000', 75000.00, NULL, 9, 1, '2026-03-14 17:42:54', '2026-03-19 14:48:07'),
(951, 13, 98, 'Kahlua', 'kahlua', 'Tot  9,000', 90000.00, NULL, 10, 1, '2026-03-14 17:42:54', '2026-03-19 14:49:35'),
(952, 13, 98, 'Grappa Nonino', 'grappa-nonino', 'Tot  9,000', 95000.00, NULL, 11, 1, '2026-03-14 17:42:54', '2026-03-19 14:51:16'),
(953, 13, 99, 'Americano', 'americano', NULL, 6000.00, NULL, 1, 1, '2026-03-14 17:42:54', '2026-03-14 17:42:54'),
(954, 13, 99, 'Cappuccino', 'cappuccino', NULL, 6500.00, NULL, 2, 1, '2026-03-14 17:42:54', '2026-03-14 17:42:54'),
(955, 13, 99, 'Espresso', 'espresso', NULL, 6000.00, NULL, 3, 1, '2026-03-14 17:42:54', '2026-03-14 17:42:54'),
(956, 13, 99, 'Double Espresso', 'double-espresso', NULL, 6500.00, NULL, 4, 1, '2026-03-14 17:42:54', '2026-03-14 17:42:54'),
(957, 13, 99, 'Café Latte', 'cafe-latte', NULL, 6500.00, NULL, 5, 1, '2026-03-14 17:42:54', '2026-03-14 17:42:54'),
(958, 13, 99, 'Macchiato', 'macchiato', NULL, 6000.00, NULL, 6, 1, '2026-03-14 17:42:54', '2026-03-14 17:42:54'),
(959, 13, 99, 'Hot Chocolate', 'hot-chocolate', NULL, 6500.00, NULL, 7, 1, '2026-03-14 17:42:54', '2026-03-14 17:42:54'),
(960, 13, 99, 'Assorted Tea', 'assorted-tea', '', 5500.00, NULL, 8, 1, '2026-03-14 17:42:54', '2026-03-27 08:16:52'),
(961, 13, 99, 'Caramel Frappe', 'caramel-frappe', NULL, 6500.00, NULL, 9, 1, '2026-03-14 17:42:54', '2026-03-14 17:42:54'),
(962, 13, 99, 'Strawberry Frappe', 'strawberry-frappe', NULL, 6500.00, NULL, 10, 1, '2026-03-14 17:42:54', '2026-03-14 17:42:54'),
(963, 13, 99, 'Banana Frappe', 'banana-frappe', NULL, 6500.00, NULL, 11, 1, '2026-03-14 17:42:54', '2026-03-14 17:42:54'),
(964, 13, 100, 'Man Sauvignon Blanc South Africa', 'man-sauvignon-blanc-south-africa', NULL, 80000.00, NULL, 1, 1, '2026-03-14 17:42:54', '2026-03-14 17:42:54'),
(966, 13, 100, 'Maison Castel', 'maison-castel', '', 70000.00, NULL, 3, 1, '2026-03-14 17:42:54', '2026-05-02 20:15:47'),
(967, 13, 100, 'Riunite Moscato', 'riunite-moscato', '', 60000.00, NULL, 4, 1, '2026-03-14 17:42:54', '2026-03-26 15:50:54'),
(968, 13, 100, 'Protea Pinot Grigio', 'protea-pinot-grigio', NULL, 80000.00, NULL, 5, 1, '2026-03-14 17:42:54', '2026-03-14 17:42:54'),
(969, 13, 100, 'Bosio Moscato Vino Spumante Dolce', 'bosio-moscato-vino-spumante-dolce', '', 70000.00, NULL, 6, 1, '2026-03-14 17:42:54', '2026-03-26 15:52:14'),
(970, 13, 100, 'Klein Constantia Estate Sauvignon', 'klein-constantia-estate-sauvignon', NULL, 80000.00, NULL, 7, 1, '2026-03-14 17:42:54', '2026-03-14 17:42:54'),
(972, 13, 100, 'Protea Sauvignon Blanc', 'protea-sauvignon-blanc', NULL, 70000.00, NULL, 9, 1, '2026-03-14 17:42:54', '2026-03-14 17:42:54'),
(973, 13, 100, 'Protea Chardonnay', 'protea-chardonnay', NULL, 70000.00, NULL, 10, 1, '2026-03-14 17:42:54', '2026-03-14 17:42:54'),
(974, 13, 100, 'Clarington Unwood Chardonnay South Africa', 'clarington-unwood-chardonnay-south-africa', NULL, 90000.00, NULL, 11, 1, '2026-03-14 17:42:54', '2026-03-14 17:42:54'),
(976, 13, 100, 'Paul Cluver Riesling South Africa', 'paul-cluver-riesling-south-africa', NULL, 90000.00, NULL, 13, 1, '2026-03-14 17:42:54', '2026-03-14 17:42:54'),
(977, 13, 100, 'Vodeling Sweet Carolyn', 'vodeling-sweet-carolyn', '', 120000.00, NULL, 14, 1, '2026-03-14 17:42:54', '2026-05-02 20:21:30'),
(978, 13, 101, 'Man Cabernet Sauvignon South Africa', 'man-cabernet-sauvignon-south-africa', NULL, 70000.00, NULL, 1, 1, '2026-03-14 17:42:54', '2026-03-14 17:42:54'),
(979, 13, 101, 'Escudo Rojo', 'escudo-rojo', '', 80000.00, NULL, 2, 1, '2026-03-14 17:42:54', '2026-03-27 07:17:35'),
(980, 13, 101, 'Cooper & Thief', 'cooper-thief', '', 145000.00, NULL, 3, 1, '2026-03-14 17:42:54', '2026-03-27 07:15:18'),
(981, 13, 101, 'Penfolds Father Grand Tawny 10', 'penfolds-father-grand-tawny-10', '', 80000.00, NULL, 4, 1, '2026-03-14 17:42:54', '2026-03-27 07:21:12'),
(985, 13, 101, 'Protea Cabernet Sauvignon', 'protea-cabernet-sauvignon', NULL, 80000.00, NULL, 8, 1, '2026-03-14 17:42:54', '2026-03-14 17:42:54'),
(986, 13, 101, 'Jordan The Prospector South Africa', 'jordan-the-prospector-south-africa', NULL, 150000.00, NULL, 9, 1, '2026-03-14 17:42:54', '2026-03-14 17:42:54'),
(987, 13, 101, 'Chateau Pouyanne France', 'chateau-pouyanne-france', NULL, 10000.00, NULL, 10, 1, '2026-03-14 17:42:54', '2026-03-14 17:42:54'),
(989, 13, 101, 'Saumur Champigny Cabernet Franc France', 'saumur-champigny-cabernet-franc-france', NULL, 70000.00, NULL, 12, 1, '2026-03-14 17:42:54', '2026-03-14 17:42:54'),
(991, 13, 102, 'Painted Wolf The Den Dry Rosé', 'painted-wolf-the-den-dry-rose', NULL, 130000.00, NULL, 2, 1, '2026-03-14 17:42:54', '2026-03-14 17:42:54'),
(992, 13, 103, 'Moet et Chandon Brut Imperial', 'moet-et-chandon-brut-imperial', '', 330000.00, NULL, 1, 1, '2026-03-14 17:42:54', '2026-04-25 12:36:43'),
(993, 13, 103, 'Moet et Chandon Nectar Imperial Rosé', 'moet-et-chandon-nectar-imperial-rose', '', 350000.00, NULL, 2, 1, '2026-03-14 17:42:54', '2026-04-25 12:37:41'),
(994, 13, 103, 'Moet Chandon Ice Imperial', 'moet-chandon-ice-imperial', '', 550000.00, NULL, 3, 1, '2026-03-14 17:42:54', '2026-03-19 15:19:00'),
(995, 13, 103, 'Dom Perignon Vintage Brut', 'dom-perignon-vintage-brut', '', 1400000.00, NULL, 4, 1, '2026-03-14 17:42:54', '2026-04-25 12:39:28'),
(996, 13, 103, 'Dom Perignon Vintage Rosé', 'dom-perignon-vintage-rose', '', 1500000.00, NULL, 5, 1, '2026-03-14 17:42:54', '2026-04-25 12:40:14'),
(997, 13, 103, 'Veuve Clicquot Brut', 'veuve-clicquot-brut', '', 310000.00, NULL, 6, 1, '2026-03-14 17:42:54', '2026-04-28 10:42:29'),
(998, 13, 103, 'Veuve Clicquot Rich', 'veuve-clicquot-rich', '', 445000.00, NULL, 7, 1, '2026-03-14 17:42:54', '2026-04-28 10:43:36'),
(999, 13, 87, 'Heineken 45CL', 'h', '', 5000.00, NULL, 2, 1, '2026-03-18 12:57:01', '2026-03-18 12:57:01'),
(1000, 13, 91, 'Glenfiddich 21 year', 'g', 'Tot 90,000', 1000000.00, NULL, 0, 1, '2026-03-18 13:06:58', '2026-03-26 10:18:37'),
(1001, 13, 91, 'Glenfiddich grand cortes  XXII', 'glenfiddich-grand-cortes-xxii', 'Tot 95,000', 1300000.00, NULL, 0, 1, '2026-03-18 13:08:30', '2026-03-30 11:32:07'),
(1002, 13, 95, 'Embargo  Anejo Bianco', 'e', 'Tot 6,000', 70000.00, NULL, 0, 1, '2026-03-19 13:37:35', '2026-03-26 10:48:46'),
(1005, 13, 95, 'Embargo Anejo Extra', 'embargo-anejo-extra', 'Tot 12,000', 100000.00, NULL, 0, 1, '2026-03-19 13:42:48', '2026-03-26 10:49:33'),
(1006, 13, 97, 'Olmeca Bianco', 'o', 'Tot 7500', 75000.00, NULL, 0, 1, '2026-03-19 13:59:21', '2026-03-30 11:43:06'),
(1007, 13, 97, 'Casamigo Reposado', 'c', 'Tot  25,000', 500000.00, NULL, 0, 1, '2026-03-19 14:14:25', '2026-04-25 12:27:30'),
(1014, 13, 97, 'Aman Rosa Blanco', 'a', 'Tot  28,000', 420000.00, NULL, 0, 1, '2026-03-19 14:24:28', '2026-04-25 12:29:20'),
(1015, 13, 97, 'Don Julio 1942', 'd', 'Tot  70,000', 1000000.00, NULL, 0, 1, '2026-03-19 14:26:31', '2026-03-26 11:35:08'),
(1019, 13, 99, 'Coffee', 'c', '', 6000.00, NULL, 0, 1, '2026-03-19 14:53:12', '2026-03-19 14:53:12'),
(1020, 13, 100, 'Santa Cristina pinot grigio', 's', '', 70000.00, NULL, 0, 1, '2026-03-19 14:56:19', '2026-03-26 15:56:48'),
(1021, 13, 100, 'Diemersdal Cape sauvignon', 'd', '', 70000.00, NULL, 0, 1, '2026-03-19 14:57:50', '2026-03-26 15:59:06'),
(1022, 13, 100, 'Pamille Perrin La Vielle Ferme Blanc', 'p', '', 70000.00, NULL, 0, 1, '2026-03-19 14:59:13', '2026-03-26 15:58:18'),
(1023, 13, 101, 'Santa Cristina Fattoria Le Maestrelle Toscana', 's', '', 80000.00, NULL, 0, 1, '2026-03-19 15:02:28', '2026-03-27 07:14:03'),
(1024, 13, 101, 'Darling Cellar Sweet Red', 'd', '', 70000.00, NULL, 0, 1, '2026-03-19 15:03:17', '2026-03-19 15:03:17'),
(1029, 13, 101, 'Chateau Beausejour Hostens', 'c', '', 80000.00, NULL, 0, 1, '2026-03-19 15:13:10', '2026-03-27 07:10:27'),
(1037, 13, 101, 'Dona Paula Blue Edition', 'dona-paula-blue-edition', 'Dona Paula Blue Edition', 120000.00, NULL, 0, 1, '2026-03-19 16:18:50', '2026-03-19 16:19:59'),
(1038, 13, 101, 'Dona Paula Malbec', 'dona-paula-malbec', '', 120000.00, NULL, 0, 1, '2026-03-19 16:22:04', '2026-03-19 16:22:04'),
(1039, 13, 97, 'Casamigo Anejo', 'casamigo-anejo', 'Tot 30,000', 440000.00, NULL, 0, 1, '2026-03-19 16:23:25', '2026-04-25 12:25:54'),
(1040, 13, 101, 'Swartland Serengeti Sweet Red', 's-2', '', 70000.00, NULL, 0, 1, '2026-03-19 17:04:07', '2026-03-19 17:04:07'),
(1041, 13, 83, 'Chocolate brownie with vanilla ice cream (D) (N)', 'chocolate-brownie-with-vanilla-ice-cream-d-n', 'Tender homemade brownie, made with premium Belgian chocolate, \r\naccompanied with vanilla ice cream drizzled and chocolate sauce', 18000.00, NULL, 0, 1, '2026-03-21 10:40:28', '2026-03-21 10:40:28'),
(1042, 13, 64, 'Smokey Jollof Rice', 's', 'Authentic smoky Jollof rice cooked in a rich tomato and pepper base, served with your choice of protein, crisp coleslaw, and \r\ngolden fried plantain.', 26000.00, NULL, 0, 1, '2026-03-23 13:29:32', '2026-03-23 13:29:32'),
(1043, 13, 71, 'Full Roasted Cat Fish', 'f', 'Whole fresh catfish, expertly marinated and charcoal-roasted to perfection.\r\nServed with fragrant steamed rice, rich atarodo tomato pepper sauce, and your choice of suya-spiced sweet potato or classic \r\nIrish potatoes.', 32000.00, NULL, 0, 1, '2026-03-23 13:33:57', '2026-03-23 13:33:57'),
(1044, 13, 71, 'Mixed Platter', 'm', 'A bold selection of flame - grilled favorites —succulent turkey cuts, whole roasted catfish, grilled tilapia, jumbo prawns, and \r\nspiced beef suya , served with suya - dusted sweet potato wedges or crispy yam chips, accompanied by vibrant atarodo pepper sauce.', 190000.00, NULL, 0, 1, '2026-03-23 13:38:52', '2026-03-23 13:38:52'),
(1045, 13, 82, 'Fish Fillet', 'f', 'Grilled  croaker, served with mashed potatoes and side salad, finished with a lemon butter cream sauce.', 35000.00, NULL, 0, 1, '2026-03-23 13:42:08', '2026-03-23 13:42:08'),
(1046, 13, 82, 'Newburg Croaker with Prawn', 'newburg-croaker-with-prawn-atlantic-seared-croaker-fillet-3-00g-topped-wi', 'Atlantic seared croaker fillet - 3 00g topped with jumbo prawn & matched with cream parmesan mustard sauce mashed potato and seasonal vegetables', 46000.00, NULL, 0, 1, '2026-03-23 13:47:58', '2026-03-23 13:47:58'),
(1047, 13, 82, 'Coriander & Black Pepper Salmon', 'coriander-black-pepper-salmon', '300g  freshwater coriander and black pepper coated grilled Salmon, accompanied by light fish veloute sauce,  mashed potato and seasonal vegetables', 55000.00, NULL, 0, 1, '2026-03-23 13:49:56', '2026-03-23 13:49:56'),
(1048, 13, 82, 'Grilled Prawns', 'g', 'Grilled tiger prawns served with sauté potato and spicy vegetable sauce', 45000.00, NULL, 0, 1, '2026-03-23 13:51:38', '2026-03-23 13:51:38'),
(1049, 13, 104, 'Magarita Pizza', 'magarita-pizza', 'Medium base pizza, topped with delicious pizziola sauce, gratinated mozzarella cheese and oregano', 21000.00, NULL, 0, 1, '2026-03-23 13:55:53', '2026-03-23 13:55:53'),
(1050, 13, 104, 'Quatro Chicken Supreme Pizza', 'quatro-chicken-supreme-pizza', 'BBQ chicken, mixed bell peppers, mushrooms, gratinated mozzarella & feta cheese with oregano', 23500.00, NULL, 0, 1, '2026-03-23 13:57:19', '2026-03-23 13:57:19'),
(1051, 13, 104, 'Cheesy Regina Melt', 'ch-eesy-regina-melt', 'Smoked turkey ham, mushrooms, gratinated mozzarella & gouda cheese with a hint of oregano', 24500.00, NULL, 0, 1, '2026-03-23 13:58:52', '2026-03-23 13:58:52'),
(1052, 13, 104, 'Seafood Alforno Pizza', 'seafood-alforn-o-pizza', 'Shrimps, calamari, octopus, basil, peppers & gratinated mozzarella & gouda cheese with a hint of oregano', 24500.00, NULL, 0, 1, '2026-03-23 14:00:18', '2026-03-23 14:00:18'),
(1053, 13, 78, 'Spicy Penne Arabiatta', 'spicy-penne-arabiatta', 'Penne, mixed bell peppers, onion & garlic tossed \r\nin chili pepper sauce, served with gratinated French bread & parmesan cheese', 28500.00, NULL, 0, 1, '2026-03-23 14:09:28', '2026-03-23 14:09:28'),
(1054, 13, 78, 'Spaghetti Con Ragout', 'spaghetti-con-ragout', 'Bolognaise sauce infused spaghetti with gratinated French bread & parmesan cheese', 28500.00, NULL, 0, 1, '2026-03-23 14:11:02', '2026-03-23 14:11:02'),
(1055, 13, 78, 'Creamy Cheesy Chicken Alforno', 'creamy-cheesy-chicken-alforno', 'Your choice of penne, spaghetti or farfalle, tender sautéed chicken, cooked in creamy cheesy garlic sauce, gratinated with French bread & parmesan cheese', 30000.00, NULL, 0, 1, '2026-03-23 14:12:14', '2026-03-23 14:12:14'),
(1056, 13, 68, 'Pepper Soup', 'pepper-soup', 'Goat meat,  Mixed meat, chicken or croaker ,  pepper soup of the day, served with cocktail roll with butter', 15000.00, NULL, 0, 1, '2026-03-23 14:19:00', '2026-03-23 14:19:00'),
(1057, 13, 68, 'Continental Soup of the Day', 'continental-soup-of-the-day-2', 'Pumpkin soup, served with cocktail roll with butter', 15000.00, NULL, 1, 1, '2026-03-23 14:22:24', '2026-03-26 09:27:44'),
(1058, 13, 75, 'Smoked Salmon Rosette', 'sm-oked-salmon-rosette', 'Smoked salmon rosette with coddled egg, garden salad, smooth cream cheese, French dressing capers and red onion with a hint of lemon', 28000.00, NULL, 3, 1, '2026-03-23 14:25:05', '2026-03-27 13:19:48'),
(1059, 13, 75, 'Spicy Chicken Wings', 'spicy-chicken-wings', 'Coated fried spicy chicken wings served with sweet potato.', 20000.00, NULL, 5, 1, '2026-03-23 14:26:14', '2026-03-27 13:19:11'),
(1060, 13, 68, 'Whole Cat Fish Pepper Soup', 'w', 'Whole fresh catfish cooked to perfection served with fragrant steamed rice, \r\nrich atarodo tomato pepper sauce, and your choice of suya-spiced sweet potato or classic Irish potatoes', 32000.00, NULL, 2, 1, '2026-03-26 09:22:54', '2026-03-26 09:28:31'),
(1061, 13, 105, 'Lusso Club Sandwich', 'lusso-club-sandwich', 'Double decker with chicken & turkey ham, fried eggs, lettuce tomato, pickles, mayonnaise spread & mozzarella cheese \r\naccompanied with fries and homemade coleslaw', 28000.00, NULL, 0, 1, '2026-03-26 09:40:56', '2026-03-26 09:40:56'),
(1062, 13, 106, 'The Giant Burger', 'the-giant-burger', 'Signature beef patty with back bacon, lettuce, tomato, coated onions, gherkins, mustard mayo, gratinated mozzarella &\r\nsesame bun.', 32000.00, '69e27433b76cc.webp', 0, 1, '2026-03-26 09:43:02', '2026-04-17 17:56:03'),
(1063, 13, 84, 'Voss', 'v', '', 10000.00, NULL, 1, 1, '2026-03-26 10:08:34', '2026-03-26 10:08:34'),
(1064, 13, 94, 'Sky Vodka Infusion Raspberry', 'sky-vodka-infusion-raspberry', 'Tot  6000', 70000.00, NULL, 0, 1, '2026-03-26 10:47:07', '2026-03-26 10:47:07'),
(1065, 13, 97, 'Don Julio Reposado', 'don-julio-reposado', 'Tot  30000', 550000.00, NULL, 0, 1, '2026-03-26 11:22:58', '2026-03-26 11:22:58'),
(1066, 13, 101, 'La Vielle Ferme', 'l', '', 80000.00, NULL, 0, 1, '2026-03-27 07:29:14', '2026-03-27 07:29:14'),
(1067, 13, 101, 'Darling Cellar Sweet Red', 'd-2', '', 70000.00, NULL, 0, 1, '2026-03-27 12:48:38', '2026-03-27 12:48:38'),
(1068, 13, 97, 'El Padrino De Ni Tierra Anejo', 'e', 'Tot  28000', 400000.00, NULL, 0, 1, '2026-03-30 11:59:04', '2026-03-30 11:59:04'),
(1069, 13, 112, 'Arabian Tea Large', 'a', 'Small = 16,500', 23500.00, NULL, 0, 1, '2026-04-02 13:52:25', '2026-05-02 19:59:04'),
(1070, 13, 112, 'Moringa Tea', 'm', '', 8000.00, NULL, 0, 1, '2026-04-02 13:53:13', '2026-04-02 13:53:13'),
(1071, 13, 112, 'Hibiscus Tea', 'h', '', 15000.00, NULL, 0, 1, '2026-04-02 13:53:38', '2026-05-02 20:02:45'),
(1072, 13, 112, 'Hibiscus & Moringa Tea', 'h-2', '', 15000.00, NULL, 0, 1, '2026-04-02 13:54:28', '2026-05-02 20:01:54'),
(1073, 13, 112, 'Herbal Infused Tea', 'h-3', '', 15000.00, NULL, 0, 1, '2026-04-02 13:56:12', '2026-05-02 20:01:09'),
(1074, 13, 82, 'Jumbo Prawns', 'j', '', 55000.00, NULL, 0, 1, '2026-04-22 11:19:09', '2026-04-22 11:19:09'),
(1075, 13, 101, 'Nederburg Merlot', 'n', '', 90000.00, NULL, 0, 1, '2026-04-22 11:24:50', '2026-04-22 11:24:50'),
(1076, 13, 101, 'Nederburg Cab Sauvignon', 'n-2', '', 90000.00, NULL, 0, 1, '2026-04-22 11:43:40', '2026-04-22 11:43:40'),
(1077, 13, 101, 'La Fiole Chateanuf Du Pape', 'l-2', '', 160000.00, NULL, 0, 1, '2026-04-22 11:45:45', '2026-04-22 11:45:45'),
(1078, 13, 101, 'Sand Stone', 's-3', '', 90000.00, NULL, 0, 1, '2026-04-22 11:52:20', '2026-04-22 11:52:20'),
(1079, 13, 115, 'Cosmopolitan', 'c', 'vodka,, tripple sec, fresh squeeze lime juice, cranberry juice', 22500.00, NULL, 0, 1, '2026-04-22 14:39:25', '2026-05-02 20:04:13'),
(1080, 13, 115, 'Planter\'s Island', 'p', 'dark rum, orange juice, pineapple juice, lemon, grenadine syrup', 18500.00, NULL, 0, 1, '2026-04-22 14:41:25', '2026-05-02 20:07:46'),
(1081, 13, 115, 'Pina colada', 'p-2', 'white rum, coconut cream, fresh pineapple juice, coconut rum', 22500.00, NULL, 0, 1, '2026-04-22 15:17:52', '2026-05-02 20:06:33'),
(1082, 13, 115, 'Mojito', 'm', 'white rum,  mint leaves, fresh squeeze lime juice, simple syrup, soda water', 22500.00, NULL, 0, 1, '2026-04-22 15:20:01', '2026-05-02 20:06:04'),
(1083, 13, 115, 'Long Island Ice Tea', 'l', 'tequila, gin, bacardi , vodka, tripple sec, coke', 23000.00, NULL, 0, 1, '2026-04-22 15:23:01', '2026-05-02 20:05:28'),
(1084, 13, 115, 'Whiskey Sour', 'w', 'bourbon whiskey, egg white( optional),sugar syrup, lemon juice', 18500.00, NULL, 0, 1, '2026-04-22 15:37:48', '2026-05-02 20:07:11'),
(1085, 13, 115, 'White Russian', 'w-2', 'vodka, kahlua, cream', 18500.00, NULL, 0, 1, '2026-04-22 15:58:44', '2026-05-02 20:08:51'),
(1086, 13, 117, 'Chapman', 'c', 'fanta, sprite, orange juice, bitter lemon, grenadine , angostura', 6500.00, NULL, 0, 1, '2026-04-22 16:03:58', '2026-04-22 16:03:58'),
(1087, 13, 117, 'Virgin Colada', 'v', 'fresh pineapple juice, coconut cream, whip cream', 6500.00, NULL, 0, 1, '2026-04-22 17:03:54', '2026-04-22 17:03:54'),
(1088, 13, 117, 'Virgin Mojito', 'v-2', 'mint leaves, soda water, simple syrup, sprite , lime', 6500.00, NULL, 0, 1, '2026-04-22 17:05:26', '2026-04-22 17:05:26'),
(1089, 13, 117, 'Blue Sky', 'b', 'vanilla ice cream, sprite, blue curacao, egg white optional', 6500.00, NULL, 0, 1, '2026-04-22 17:06:43', '2026-04-22 17:06:43'),
(1090, 13, 117, 'couples Delight', 'c-2', 'pineapple juice, apple juice, orange juice , passion fruit', 6500.00, NULL, 0, 1, '2026-04-22 17:09:38', '2026-04-22 17:09:38'),
(1091, 13, 117, 'Cranberry Cooler', 'c-3', 'cranberry juice, grenadine , cream', 6500.00, NULL, 0, 1, '2026-04-22 17:11:24', '2026-04-22 17:11:24'),
(1092, 13, 97, 'Casamigo Reposado 100cl', 'c-2', '', 600000.00, NULL, 0, 1, '2026-04-25 12:24:39', '2026-04-25 12:24:39'),
(1093, 13, 97, 'Aman Anejo', 'a-2', '', 750000.00, NULL, 0, 1, '2026-04-25 12:30:29', '2026-04-25 12:30:29'),
(1094, 13, 103, 'Don Perignon Luminous', 'd', '', 1500000.00, NULL, 0, 1, '2026-04-25 12:43:22', '2026-04-25 12:43:22'),
(1095, 13, 103, 'Louis  Roederer Cristal', 'l', '', 1500000.00, NULL, 0, 1, '2026-04-25 12:45:58', '2026-04-25 12:45:58'),
(1096, 13, 126, 'Martinellis', 'm', '', 50000.00, NULL, 0, 1, '2026-04-25 13:05:23', '2026-04-25 13:05:23'),
(1097, 13, 91, 'Glenfiddich 16 Years', 'glenfiddich-grand-cortes-xxii-2', '', 320000.00, NULL, 0, 1, '2026-04-25 13:08:27', '2026-04-25 13:08:27'),
(1098, 13, 71, 'Ribeye Steak', 'r', '', 62000.00, NULL, 0, 1, '2026-04-25 13:15:18', '2026-04-25 13:15:18'),
(1099, 13, 71, 'T bone Steak', 't', '', 65000.00, NULL, 0, 1, '2026-04-25 13:16:23', '2026-04-25 13:16:23'),
(1100, 13, 103, 'Veuve Clicquot Rich Rose', 'v', '', 450000.00, NULL, 8, 1, '2026-04-28 10:45:52', '2026-04-28 10:46:39'),
(1101, 13, 96, 'Remy Martin VS', 'r', 'Tot  10,000', 160000.00, NULL, 0, 1, '2026-04-28 11:34:16', '2026-04-28 11:34:16'),
(1102, 13, 91, 'Glenfiddich 18 year Limited Edition', 'glenfiddich-21-year', 'Tot 31,000', 390000.00, NULL, 0, 1, '2026-04-28 11:56:53', '2026-04-28 11:56:53'),
(1103, 13, 93, 'Wild Turkey 101', 'w', 'Tot 9,000', 90000.00, NULL, 0, 1, '2026-04-28 12:17:38', '2026-04-28 12:17:38'),
(1104, 13, 115, 'Margarita', 'm-2', '', 18500.00, NULL, 0, 1, '2026-05-02 20:14:11', '2026-05-02 20:14:11'),
(1105, 13, 100, 'Friends and Family white wine', 'f', '', 35000.00, NULL, 0, 1, '2026-05-02 20:20:05', '2026-05-02 20:20:05'),
(1106, 13, 100, 'Chateau Vartely muscat', 'c', '', 70000.00, NULL, 0, 1, '2026-05-02 20:22:59', '2026-05-02 20:22:59'),
(1107, 13, 115, 'Pornstar Martini', 'o', '', 18500.00, NULL, 0, 1, '2026-05-03 17:58:31', '2026-05-03 17:58:31'),
(1108, 13, 102, 'Friends and Family Rose wine', 'f', 'Glass = 10,000', 35000.00, NULL, 0, 1, '2026-05-04 11:39:46', '2026-05-04 11:39:46'),
(1109, 13, 102, 'Vartely Chateau Rose', 'v', 'Glass = 13,000', 55000.00, NULL, 0, 1, '2026-05-04 11:42:14', '2026-05-04 11:42:14'),
(1110, 19, 129, 'ENGLISH BREAKFAST', 'english-breakfast', 'Eggs (scrambled, fried, boiled or omelet), chicken sausage, Pork bacon, hash brown potato, grilled tomato, baked beans, mushrooms, tomatoes, & brioche toast', 26000.00, NULL, 0, 1, '2026-05-06 00:57:05', '2026-05-06 00:57:35'),
(1111, 19, 129, 'NIGERIAN BREAKFAST', 'nigerian-breakfast', 'Egg sauce cooked your way, Boiled yam or Plantain basket with Nigerian style beans cooked in a tomato sauce and served with a side of chicken sausage', 19400.00, NULL, 0, 1, '2026-05-06 00:58:54', '2026-05-06 00:58:54'),
(1112, 19, 129, 'ENERGY BREAKFAST', 'energy-breakfast', 'Fresh yogurt, an assortment of berries, a banana, and dry almonds served with honey', 13000.00, NULL, 0, 1, '2026-05-06 01:00:38', '2026-05-06 01:00:38'),
(1113, 19, 130, 'MAPLE SYRUP', 'maple-syrup', '3 buttermilk pancakes, maple syrup, fruits, and caramel sauce.', 8600.00, NULL, 0, 1, '2026-05-06 01:04:08', '2026-05-06 01:04:08'),
(1114, 19, 130, 'CHOCOLATE BANANA', 'chocolate-banana', '3 Buttermilk pancake, Nutella chocolate spread, banana, roasted hazelnuts.', 13500.00, NULL, 0, 1, '2026-05-06 01:06:41', '2026-05-06 01:06:41'),
(1115, 19, 130, 'BREAKFAST SANDWISH', 'breakfast-sandwish', '3 buttermilk pancake, Chicken sausage, cheddar cheese, bacon, scrambled egg.', 16000.00, NULL, 0, 1, '2026-05-06 01:38:39', '2026-05-06 01:38:39'),
(1116, 19, 130, 'LITE BUTTERMILK', 'lite-buttermilk', '1 buttermilk pancake, chicken sausage, bacon, scrambled egg, mixed cheese, side salad served with balsamic vinegar sauce & maple syrup', 17000.00, NULL, 0, 1, '2026-05-06 01:40:31', '2026-05-06 01:40:31'),
(1117, 19, 131, 'PLAIN', 'plain', 'Crispy waffle dusted with icing sugar, served with caramel', 5900.00, NULL, 0, 1, '2026-05-06 01:56:04', '2026-05-06 01:56:04'),
(1118, 19, 131, 'BERRIES AND VANILLA ICE CREAM', 'berries-and-vanilla-ice-cream', 'Homemade crispy waffle, assorted berries served with a scoop of vanilla ice cream and chocolate sauce,', 15000.00, NULL, 0, 1, '2026-05-06 01:56:41', '2026-05-06 01:56:41'),
(1119, 19, 131, 'CHOCOLATE BANANA', 'chocolate-banana', 'Homemade crispy waffle, banana, Nutella chocolate, whipped cream, and caramel', 13500.00, NULL, 0, 1, '2026-05-06 01:57:40', '2026-05-06 01:57:40'),
(1120, 19, 131, 'CHICKEN AND WAFFLE SANDWISH', 'chicken-and-waffle-sandwish', 'Homemade crispy waffle topped with crispy fried chicken breast served with secret sauce', 12000.00, NULL, 0, 1, '2026-05-06 01:58:26', '2026-05-06 01:58:26'),
(1121, 19, 131, 'Waffle and egg', 'waffle-and-egg', 'Homemade crispy waffle topped with scrambled eggs', 14900.00, NULL, 0, 1, '2026-05-06 01:59:24', '2026-05-06 01:59:24'),
(1122, 19, 132, 'CLASSIC BENEDICT', 'classic-benedict', 'Served with hollandaise sauce', 10000.00, NULL, 0, 1, '2026-05-06 02:00:32', '2026-05-06 02:00:32'),
(1123, 19, 132, 'SPANISH OMELET', 'spanish-omelet', 'Onions, tomato, green pepper, spring onions, brioche toast, and butter', 13500.00, NULL, 0, 1, '2026-05-06 02:02:16', '2026-05-06 02:02:16'),
(1124, 19, 132, 'OMELET NATURE', 'omelet-nature', '', 9000.00, NULL, 0, 1, '2026-05-06 02:02:51', '2026-05-06 02:02:51'),
(1125, 19, 132, 'SUNNY SIDE UP', 'sunny-side-up', '', 9000.00, NULL, 0, 1, '2026-05-06 02:03:11', '2026-05-06 02:03:11'),
(1126, 19, 132, 'SCRAMBLED', 'scrambled', '', 9000.00, NULL, 0, 1, '2026-05-06 02:04:33', '2026-05-06 02:04:33'),
(1127, 19, 133, 'CRISPY CHICKEN WINGS', 'crispy-chicken-wings', 'Seasoned deep-fried chicken wings, fried and served with your choice of BBQ or chili sauce.', 17000.00, NULL, 0, 1, '2026-05-06 02:06:18', '2026-05-06 02:06:18'),
(1128, 4, 144, 'Hunter\'s Soup', 'hunters-soup', 'Goat Meat Chunks, Herbs, Yam Balls', 12500.00, NULL, 1, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1129, 4, 144, 'Seafood Skillet', 'seafood-skillet', 'Mixed Seafood, Eggs, Bell Peppers, Mozzarella Cheese, Marinara Sauce, Agege French Toast', 13500.00, NULL, 2, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1130, 4, 144, 'Beef Sliders', 'beef-sliders', 'Bun, Beef Patties, Lettuce, Tomatoes, Caramelized Onions, Mozzarella Cheese', 15000.00, NULL, 3, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1131, 4, 144, 'Spicy Glazed Lamb Ribs', 'spicy-glazed-lamb-ribs', 'Braised Lamb Ribs, Pickled Shombo, Hoisin Sauce, Sesame Seeds, Petite Salad', 19900.00, NULL, 4, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1132, 4, 144, 'Damgonama Rolls', 'damgonama-rolls', 'Dried Pulled Beef, Cabbage, Bell Peppers, Chili Flakes, Peppered Jam', 10000.00, NULL, 5, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1133, 4, 144, 'Peanut Crusted Suya', 'peanut-crusted-suya', 'Torzo, Chicken Breast, Crushed Peanuts, Yaji, Tomatoes, Onions', 12900.00, NULL, 6, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1134, 4, 144, 'Stuffed Chicken Caesar Salad', 'stuffed-chicken-caesar-salad', 'Chicken Breast, Parmesan Croutons, Iceberg Lettuce, Cherry Tomatoes, Salad Dressing', 19900.00, NULL, 7, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1135, 4, 144, 'Spicy Snails Basket', 'spicy-snails-basket', 'Peppered Snails, Paprika Boli, Iyamase Sauce', 25000.00, NULL, 8, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1136, 4, 144, 'Mixed Meat Taco', 'mixed-meat-taco', 'Brisket, Tortilla, Tomato Salsa, Cucumber Yoghurt Drizzle', 12900.00, NULL, 9, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1137, 4, 144, 'Let\'s Wings It... Chicken Lollipops', 'lets-wings-it-chicken-lollipops', 'Tomato Base, Mushrooms, Caramelized Onions, Cheese. Choice of Sauce: Orange Cumin • Honey Mustard • BBQ Peanut • Lemon Garlic Parmesan • Chilli Sauce • Plain', 14900.00, NULL, 10, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1138, 4, 145, 'Brown Butter Salmon', 'brown-butter-salmon', 'Grilled Salmon, Sweet Potato Fingers, Seasonal Vegetables, Beurre Blanc', 49000.00, NULL, 1, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1139, 4, 145, 'Seafood Pasta', 'seafood-pasta', 'Creamy Seafood Mix, Linguine, Tomatoes, Herbs, Pesto Baguette', 43750.00, NULL, 2, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1140, 4, 145, 'Beef Burger', 'beef-burger', 'Beef Pattie, Lettuce, Fried Egg, Caramelized Onions, Spicy Burger Sauce', 22500.00, NULL, 3, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1141, 4, 145, 'Smoked Short Rib Pasta', 'smoked-short-rib-pasta', 'Short Ribs, Rigatoni, Ragu, Herbs, Mushrooms', 34990.00, NULL, 4, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1142, 4, 145, 'Smoked Chicken Tagliatelle', 'smoked-chicken-tagliatelle', 'Chicken, Macon, Tagliatelle Pasta, Cream, Kale, Peas, Sundried Tomatoes', 29900.00, NULL, 5, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1143, 4, 145, 'Grilled Baby Chicken', 'grilled-baby-chicken', 'Baby Chicken, Oxtail Fried Rice, Potato Crisps, Caramelized Onions, Chili Sauce', 39000.00, NULL, 6, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1144, 4, 145, '700G Grilled Tomahawk Steak (Feeds 2)', '700g-grilled-tomahawk-steak', 'Tomahawk Steak, Oxtail Fried Rice, Seasonal Vegetables, Carrot Puree, Peppercorn Sauce', 90000.00, NULL, 7, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1145, 4, 145, 'Sirloin with Marrow Butter', 'sirloin-with-marrow-butter', '250g Sirloin, Mash Potatoes, Seasonal Vegetables, Carrot Puree, Marrow Butter', 48500.00, NULL, 8, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1146, 4, 145, 'Herb Crusted Lamb Chops', 'herb-crusted-lamb-chops', '450g French Trimmed Cutlets, Oxtail Rice, Seasonal Vegetables. Options: Grilled / Herb-Crusted', 58500.00, NULL, 9, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1147, 4, 145, 'Pan Seared Chicken', 'pan-seared-chicken', 'Grilled Chicken, Sauteed Potatoes, Creamed Corn, Chicken Gravy', 29900.00, NULL, 10, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1148, 4, 145, 'Peri-Peri Jumbo Prawns', 'peri-peri-jumbo-prawns', 'Chargrilled Jumbo Prawns, Shredded Beef, Salsa, Kelewele, Jollof Rice', 45750.00, NULL, 11, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1149, 4, 146, 'Meat Nostalgia', 'meat-nostalgia', 'Braised Short Ribs, Pineapple Chicken Drumsticks, Pulled Brisket Taquitos, Whisky Beef Sliders, Mixed Tubers, Oxtail Fried Rice', 69000.00, NULL, 1, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1150, 4, 146, 'Nostalgia Seafood Sail', 'nostalgia-seafood-sail', 'XXL Croaker, Jumbo Prawns, Baby Calamari, Shrimp Rolls, Curry Snails, Kelewele, Fries', 79500.00, NULL, 2, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1151, 4, 146, 'Smoked Guinea Fowl', 'smoked-guinea-fowl', 'Smoked Guinea Fowl, Sweet Potato Wedges, Corn Ribs, Green Salad', 42000.00, NULL, 3, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1152, 4, 147, 'Chicken & Prawns Caesar Salad', 'chicken-prawns-caesar-salad', 'Jumbo Prawns, Chicken Breast, Iceberg Lettuce, Croutons, Egg, Parmesan Cheese, Caesar Dressing', 33900.00, NULL, 1, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1153, 4, 147, 'Italian Chopped Salad', 'italian-chopped-salad', 'Turkey Ham, Iceberg Lettuce, Feta Cheese, Cherry Tomatoes, Sweet Corn, Olives', 15000.00, NULL, 2, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1154, 4, 147, 'Prawns Salad', 'prawns-salad', 'Jumbo Prawns, Iceberg Lettuce, Croutons, Egg, Parmesan Cheese, Caesar Dressing', 29900.00, NULL, 3, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51');
INSERT INTO `menu_items` (`id`, `restaurant_id`, `category_id`, `name`, `slug`, `description`, `price`, `image`, `display_order`, `is_available`, `created_at`, `updated_at`) VALUES
(1155, 4, 147, 'Chicken Caesar Salad', 'chicken-caesar-salad', 'Chicken Breast, Iceberg Lettuce, Croutons, Egg, Parmesan Cheese, Caesar Dressing', 24900.00, NULL, 4, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1156, 4, 148, 'Jollof Rice', 'jollof-rice', NULL, 6000.00, NULL, 1, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1157, 4, 148, 'Oxtail Fried Rice', 'oxtail-fried-rice', NULL, 6900.00, NULL, 2, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1158, 4, 148, 'Kelewele', 'kelewele', NULL, 5000.00, NULL, 3, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1159, 4, 148, 'Mixed Tubers', 'mixed-tubers', NULL, 3900.00, NULL, 4, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1160, 4, 148, 'Mash Potatoes', 'mash-potatoes', NULL, 4500.00, NULL, 5, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1161, 4, 148, 'French Fries', 'french-fries', NULL, 4500.00, NULL, 6, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1162, 4, 148, 'Seasonal Vegetables', 'seasonal-vegetables', NULL, 3500.00, NULL, 7, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1163, 4, 148, 'Prawns', 'prawns-side', NULL, 26800.00, NULL, 8, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1164, 4, 149, 'Bailey Brownie', 'bailey-brownie', 'Baileys Irish Liqueur, Espresso Powder, Truffle Chocolate Cake', 9500.00, NULL, 1, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1165, 4, 149, 'Apple Pie', 'apple-pie', 'Baked Apples, Lemon Butter, Short Crust Pie', 9500.00, NULL, 2, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1166, 4, 149, 'Jamaican Rum Cake', 'jamaican-rum-cake', 'Candied Fruit, Rum, Moist Brown Sponge', 9500.00, NULL, 3, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1167, 4, 150, 'Cristal Louis Roederer', 'cristal-louis-roederer', NULL, 1159200.00, NULL, 1, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1168, 4, 150, 'Cristal Magnum', 'cristal-magnum', NULL, 2600000.00, NULL, 2, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1169, 4, 150, 'Dom Pérignon Brut', 'dom-perignon-brut', NULL, 1034000.00, NULL, 3, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1170, 4, 150, 'Ace of Spades Brut', 'ace-of-spades-brut', NULL, 1140000.00, NULL, 4, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1171, 4, 150, 'Moët Brut Imperial', 'moet-brut-imperial', NULL, 290000.00, NULL, 5, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1172, 4, 150, 'Moët Nectar Imperial Rosé', 'moet-nectar-imperial-rose', NULL, 350000.00, NULL, 6, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1173, 4, 150, 'Veuve Clicquot Brut', 'veuve-clicquot-brut', NULL, 299000.00, NULL, 7, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1174, 4, 150, 'Veuve Clicquot Rich', 'veuve-clicquot-rich', NULL, 450000.00, NULL, 8, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1175, 4, 151, 'Hennessy Paradis', 'hennessy-paradis', NULL, 2875000.00, NULL, 1, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1176, 4, 151, 'Hennessy XO', 'hennessy-xo', NULL, 990000.00, NULL, 2, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1177, 4, 151, 'Hennessy VSOP', 'hennessy-vsop', NULL, 296000.00, NULL, 3, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1178, 4, 151, 'Martell Blue Swift', 'martell-blue-swift', NULL, 270000.00, NULL, 4, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1179, 4, 151, 'Martell XO', 'martell-xo', NULL, 790000.00, NULL, 5, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1180, 4, 151, 'Cognac Shot', 'cognac-shot', NULL, 7000.00, NULL, 6, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1181, 4, 152, 'Glenfiddich 18', 'glenfiddich-18', NULL, 350000.00, NULL, 1, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1182, 4, 152, 'Glenfiddich 21', 'glenfiddich-21', NULL, 850500.00, NULL, 2, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1183, 4, 152, 'Glenfiddich 23', 'glenfiddich-23', NULL, 1250000.00, NULL, 3, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1184, 4, 152, 'Glenfiddich 26', 'glenfiddich-26', NULL, 1825000.00, NULL, 4, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1185, 4, 152, 'Glenfiddich 30', 'glenfiddich-30', NULL, 7000000.00, NULL, 5, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1186, 4, 152, 'Macallan Rare Cask', 'macallan-rare-cask', NULL, 389500.00, NULL, 6, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1187, 4, 152, 'Jameson Black Barrel', 'jameson-black-barrel', NULL, 195000.00, NULL, 7, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1188, 4, 152, 'Whiskey Shot', 'whiskey-shot', NULL, 7000.00, NULL, 8, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1189, 4, 153, 'Don Julio 1942', 'don-julio-1942', NULL, 999000.00, NULL, 1, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1190, 4, 153, 'Clase Azul Reposado', 'clase-azul-reposado', NULL, 950000.00, NULL, 2, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1191, 4, 153, 'Patron Silver', 'patron-silver', NULL, 199000.00, NULL, 3, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1192, 4, 153, 'Adictivo Tequila Extra Añejo', 'adictivo-tequila-extra-anejo', NULL, 750000.00, NULL, 4, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1193, 4, 153, 'Casamigos Reposado', 'casamigos-reposado', NULL, 485000.00, NULL, 5, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1194, 4, 153, 'Tequila Shot', 'tequila-shot', NULL, 6500.00, NULL, 6, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1195, 4, 154, 'Hendrick\'s', 'hendricks', NULL, 169000.00, NULL, 1, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1196, 4, 154, 'Bombay Sapphire', 'bombay-sapphire', NULL, 100000.00, NULL, 2, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1197, 4, 154, 'Tonino Lamborghini Gin', 'tonino-lamborghini-gin', NULL, 189000.00, NULL, 3, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1198, 4, 154, 'Gin Shot', 'gin-shot', NULL, 7000.00, NULL, 4, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1199, 4, 155, 'Baileys', 'baileys', NULL, 90000.00, NULL, 1, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1200, 4, 156, 'Campari', 'campari', NULL, 95000.00, NULL, 1, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1201, 4, 157, 'Bumbu', 'bumbu', NULL, 85500.00, NULL, 1, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1202, 4, 158, 'Château Vartely Sweet Red Wine', 'chateau-vartely-sweet-red-wine', NULL, 90000.00, NULL, 1, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1203, 4, 158, 'Amabile Di Rosa Sweet White', 'amabile-di-rosa-sweet-white-red', NULL, 85000.00, NULL, 2, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1204, 4, 158, 'Shannon Mount Bullet Merlot', 'shannon-mount-bullet-merlot', NULL, 230900.00, NULL, 3, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1205, 4, 158, 'Santa Rita 3 Medallas', 'santa-rita-3-medallas-red', NULL, 51000.00, NULL, 4, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1206, 4, 158, 'Darling Cellar Sweet Rosé', 'darling-cellar-sweet-rose-red', NULL, 85000.00, NULL, 5, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1207, 4, 158, 'Darling Cellars Sweet Red', 'darling-cellars-sweet-red', NULL, 85000.00, NULL, 6, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1208, 4, 158, 'Dona Paula Estate Malbec', 'dona-paula-estate-malbec', NULL, 85000.00, NULL, 7, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1209, 4, 158, 'Ermelinda Vinho Tinto Apostle', 'ermelinda-vinho-tinto-apostle', NULL, 75000.00, NULL, 8, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1210, 4, 158, 'BLABLA Cabernet Sauvignon', 'blabla-cabernet-sauvignon', NULL, 75000.00, NULL, 9, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1211, 4, 158, 'Leopard Leaps', 'leopard-leaps', NULL, 75000.00, NULL, 10, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1212, 4, 159, 'Santa Rita 3 Medallas', 'santa-rita-3-medallas-white', NULL, 65000.00, NULL, 1, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1213, 4, 159, 'Cloudy Bay Sauvignon Blanc', 'cloudy-bay-sauvignon-blanc', NULL, 85500.00, NULL, 2, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1214, 4, 159, 'Fantinel Sun Goddess Pinot', 'fantinel-sun-goddess-pinot', NULL, 94999.00, NULL, 3, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1215, 4, 159, 'Santa Rita 120 Reserva Especial Chardonnay', 'santa-rita-120-reserva-especial-chardonnay', NULL, 75000.00, NULL, 4, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1216, 4, 159, 'Amabile Di Rosa Sweet White', 'amabile-di-rosa-sweet-white-white', NULL, 85000.00, NULL, 5, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1217, 4, 159, 'Château d\'Esclans Whispering Angel Rosé', 'chateau-desclans-whispering-angel-rose', NULL, 75000.00, NULL, 6, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1218, 4, 159, 'Amabile Di Rosa Sweet Rosé', 'amabile-di-rosa-sweet-rose-white', NULL, 75000.00, NULL, 7, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1219, 4, 160, 'Manhattan', 'manhattan', NULL, 13600.00, NULL, 1, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1220, 4, 160, 'Old Fashioned', 'old-fashioned', NULL, 13600.00, NULL, 2, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1221, 4, 160, 'Whiskey Sour', 'whiskey-sour', NULL, 13600.00, NULL, 3, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1222, 4, 160, 'Negroni', 'negroni', NULL, 13600.00, NULL, 4, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1223, 4, 160, 'Gin Basil', 'gin-basil', NULL, 13600.00, NULL, 5, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1224, 4, 160, 'Gin Fizz', 'gin-fizz', NULL, 13600.00, NULL, 6, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1225, 4, 160, 'Margarita', 'margarita', NULL, 13600.00, NULL, 7, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1226, 4, 160, 'Cosmopolitan', 'cosmopolitan', NULL, 13600.00, NULL, 8, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1227, 4, 160, 'Screw Driver', 'screw-driver', NULL, 13600.00, NULL, 9, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1228, 4, 160, 'Mojito', 'mojito', NULL, 13600.00, NULL, 10, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1229, 4, 160, 'Rum Sour', 'rum-sour', NULL, 13600.00, NULL, 11, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1230, 4, 160, 'Daiquiri', 'daiquiri', NULL, 13600.00, NULL, 12, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1231, 4, 160, 'Long Island', 'long-island', NULL, 13600.00, NULL, 13, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1232, 4, 160, 'Pornstar Martini', 'pornstar-martini', NULL, 13600.00, NULL, 14, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1233, 4, 160, 'Mind Twister', 'mind-twister', '(Tequila Based)', 17900.00, NULL, 15, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1234, 4, 160, 'Cody Funk', 'cody-funk', '(Vodka Based)', 14500.00, NULL, 16, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1235, 4, 160, 'Standing Nipple', 'standing-nipple', NULL, 14500.00, NULL, 17, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1236, 4, 161, 'Chapman', 'chapman', NULL, 10000.00, NULL, 1, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1237, 4, 161, 'Virgin Margarita', 'virgin-margarita', NULL, 10000.00, NULL, 2, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1238, 4, 161, 'Virgin Daiquiri', 'virgin-daiquiri', NULL, 10000.00, NULL, 3, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1239, 4, 161, 'Virgin Colada', 'virgin-colada', NULL, 10000.00, NULL, 4, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1240, 4, 161, 'Virgin Mojito', 'virgin-mojito', NULL, 10000.00, NULL, 5, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1241, 4, 162, 'Stout', 'stout', NULL, 4500.00, NULL, 1, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1242, 4, 162, 'Orijin Can', 'orijin-can', NULL, 4500.00, NULL, 2, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1243, 4, 162, 'Gulder', 'gulder', NULL, 4500.00, NULL, 3, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1244, 4, 162, 'Budweiser', 'budweiser', NULL, 4500.00, NULL, 4, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1245, 4, 162, 'Heineken', 'heineken', NULL, 4500.00, NULL, 5, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1246, 4, 163, 'Power Horse', 'power-horse', NULL, 4500.00, NULL, 1, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1247, 4, 164, 'Malta Can', 'malta-can', NULL, 3000.00, NULL, 1, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1248, 4, 164, 'Pet Coke', 'pet-coke', NULL, 3000.00, NULL, 2, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1249, 4, 164, 'Pet Sprite', 'pet-sprite', NULL, 3000.00, NULL, 3, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1250, 4, 164, 'Pet Fanta', 'pet-fanta', NULL, 3000.00, NULL, 4, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1251, 4, 164, 'Can Mojito', 'can-mojito', NULL, 3000.00, NULL, 5, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1252, 4, 164, 'Water', 'water', NULL, 2000.00, NULL, 6, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1253, 4, 164, 'Tonic Water', 'tonic-water', NULL, 3000.00, NULL, 7, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1254, 4, 164, 'Cranberry Juice', 'cranberry-juice', NULL, 10000.00, NULL, 8, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1255, 4, 165, 'Vanilla Milkshake', 'vanilla-milkshake', NULL, 7500.00, NULL, 1, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1256, 4, 165, 'Strawberry Milkshake', 'strawberry-milkshake', NULL, 7500.00, NULL, 2, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1257, 4, 165, 'Chocolate Milkshake', 'chocolate-milkshake', NULL, 7500.00, NULL, 3, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1258, 4, 165, 'Chocy Teaser', 'chocy-teaser', NULL, 9900.00, NULL, 4, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1259, 4, 165, 'Oreo and Waffle', 'oreo-and-waffle', NULL, 9900.00, NULL, 5, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1260, 4, 165, 'Strawberry Gummy', 'strawberry-gummy', NULL, 9900.00, NULL, 6, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1261, 4, 166, 'English Breakfast', 'english-breakfast', 'Eggs, Bacon, Sausages, Baked Beans, Grilled Tomatoes, Toast', 14900.00, NULL, 1, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1262, 4, 166, 'Waffles Delight', 'waffles-delight', 'Crispy Chicken, Waffles, Fried Egg, Green Salad, Syrup', 13900.00, NULL, 2, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1263, 4, 166, 'Buttermilk Blueberry Pancakes', 'buttermilk-blueberry-pancakes', 'Fresh Blueberries, Flour, Eggs, Vanilla Extract. Choice of: Sausages • Bacon • Plain with Syrup', 17500.00, NULL, 3, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1264, 4, 166, 'Popcorn Chicken with French Toast', 'popcorn-chicken-with-french-toast', 'Diced Chicken, French Toast, Fruit Bowl, Whipped Cream', 14900.00, NULL, 4, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1265, 4, 166, 'Turfed Chicken Caesar Salad', 'turfed-chicken-caesar-salad', 'Chicken Breast, Parmesan Croutons, Iceberg Lettuce, Cherry Tomatoes, Salad Dressing', 30500.00, NULL, 5, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1266, 4, 166, 'Philly Omelet', 'philly-omelet', 'Eggs, Pulled Beef, Caramelized Onions, Bell Peppers, Cheese, Chili Sauce. Choice of: Fried Yam • Fried Plantain', 10000.00, NULL, 6, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1267, 4, 166, 'Salmon Skillet', 'salmon-skillet', 'Salmon, Eggs, Bell Peppers, Mozzarella Cheese, Marinara Sauce, Agege French Toast', 25000.00, NULL, 7, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1268, 4, 166, 'Nostalgia Breakfast Burrito', 'nostalgia-breakfast-burrito', 'Tortilla, Scrambled Eggs, Sausages, Bacon, Pulled Beef, Breakfast Potatoes, Lettuce, Caramelized Onions, Cheese, Peppercorn Aioli', 11000.00, NULL, 8, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1269, 4, 166, 'Beef Burger', 'beef-burger-brunch', 'Beef Pattie, Lettuce, Cheese, Tomato Chutney, Caramelized Onions', 15000.00, NULL, 9, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1270, 4, 166, 'Balsamic Braised Lamb Shanks', 'balsamic-braised-lamb-shanks', 'Lamb Shank, Mash Potatoes, Seasonal Vegetables, Balsamic Reduction', 22900.00, NULL, 10, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1271, 4, 166, 'Peri-Peri Jumbo Prawns', 'peri-peri-jumbo-prawns-brunch', 'Chargrilled Jumbo Prawns, Shredded Beef, Salsa, Kelewele, Jollof Rice', 29900.00, NULL, 11, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1272, 4, 166, 'Seafood Pasta', 'seafood-pasta-brunch', 'Creamy Seafood Mix, Linguine, Tomatoes, Herbs, Pesto Baguette', 27900.00, NULL, 12, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1273, 4, 167, 'French Toast', 'french-toast-brunch', NULL, 4000.00, NULL, 1, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1274, 4, 167, 'Waffles', 'waffles-brunch', NULL, 4000.00, NULL, 2, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1275, 4, 167, 'Blueberry Pancakes', 'blueberry-pancakes-brunch', NULL, 4500.00, NULL, 3, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1276, 4, 167, 'Mash Potatoes', 'mash-potatoes-brunch', NULL, 3500.00, NULL, 4, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1277, 4, 167, 'Jollof Rice', 'jollof-rice-brunch', NULL, 3000.00, NULL, 5, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1278, 4, 167, 'French Fries', 'french-fries-brunch', NULL, 3500.00, NULL, 6, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1279, 4, 167, 'Seasonal Vegetables', 'seasonal-vegetables-brunch', NULL, 3500.00, NULL, 7, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1280, 4, 167, 'Kelewele', 'kelewele-brunch', NULL, 3500.00, NULL, 8, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1281, 4, 167, 'Breakfast Extras', 'breakfast-extras', NULL, 2500.00, NULL, 9, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1282, 4, 168, 'Bailey Brownie', 'bailey-brownie-brunch', 'Baileys Irish Liqueur, Espresso Powder, Truffle Chocolate Cake', 7000.00, NULL, 1, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1283, 4, 168, 'Apple Pie', 'apple-pie-brunch', 'Baked Apples, Lemon Butter, Short Crust Pie', 6500.00, NULL, 2, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1284, 4, 168, 'Sticky Toffee Pudding', 'sticky-toffee-pudding', NULL, 6500.00, NULL, 3, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1285, 4, 169, 'Blueberry', 'shisha-blueberry', NULL, 35000.00, NULL, 1, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1286, 4, 169, 'Chocolate', 'shisha-chocolate', NULL, 35000.00, NULL, 2, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1287, 4, 169, 'Cream & Mint', 'shisha-cream-mint', NULL, 35000.00, NULL, 3, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1288, 4, 169, 'Double Apple', 'shisha-double-apple', NULL, 35000.00, NULL, 4, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1289, 4, 169, 'Grape Flavour', 'shisha-grape-flavour', NULL, 35000.00, NULL, 5, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1290, 4, 169, 'Lemon & Mint', 'shisha-lemon-mint', NULL, 35000.00, NULL, 6, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1291, 4, 169, 'Strawberry', 'shisha-strawberry', NULL, 35000.00, NULL, 7, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1292, 4, 169, 'Love 66', 'shisha-love-66', NULL, 35000.00, NULL, 8, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1293, 4, 169, 'Watermelon', 'shisha-watermelon', NULL, 35000.00, NULL, 9, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1294, 4, 170, 'Extra Flavour', 'extra-flavour', NULL, 12500.00, NULL, 1, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(1296, 25, 177, '6pcs wings', 'wm-6pcs', 'LORD OF THE WINGS! Choose your flavor & dip.', 12000.00, NULL, 1, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1297, 25, 177, '8pcs wings', 'wm-8pcs', 'LORD OF THE WINGS! Choose your flavor & dip.', 13500.00, NULL, 2, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1298, 25, 177, '10pcs wings', 'wm-10pcs', 'LORD OF THE WINGS! Choose your 1 flavor & 1 dip.', 15000.00, NULL, 3, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1299, 25, 177, '15pcs wings', 'wm-15pcs', 'LORD OF THE WINGS! Choose up to 2 flavors & 1 dip.', 20500.00, NULL, 4, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1300, 25, 177, '20pcs wings', 'wm-20pcs', 'LORD OF THE WINGS! Choose up to 2 flavors & 1 dip.', 22000.00, NULL, 5, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1301, 25, 177, '30pcs wings', 'wm-30pcs', 'LORD OF THE WINGS! Choose up to 3 flavors & 1 dip.', 30000.00, NULL, 6, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1302, 25, 178, 'WAFFLE UP POWER UP!', 'wm-waffle-power', 'Waffles, chicken tenders in flavor of choice & cheesy Mac.', 12000.00, NULL, 1, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1303, 25, 178, 'DUNKED', 'wm-dunked', 'Waffles, chicken tenders in flavor of choice.', 10000.00, NULL, 2, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1304, 25, 178, 'HULK', 'wm-hulk', 'Waffles, chicken tenders in flavor of choice, classic French fries & ketchup.', 12000.00, NULL, 3, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1305, 25, 178, 'BIGGIE', 'wm-biggie', 'Waffles, chicken tenders in flavor of choice, classic French fries, chicken poppers, 6pcs wings in flavor of choice & ketchup.', 20000.00, NULL, 4, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1306, 25, 178, 'CHAIRMAN', 'wm-chairman', 'Waffles, chicken tenders in flavor of choice, seasoned wedges, chicken poppers & ketchup.', 30000.00, NULL, 5, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1307, 25, 178, 'CHICKUTERIE', 'wm-chickuterie', 'Waffles, chicken tenders in flavor of choice, seasoned wedges, chicken poppers, 8 wings in flavor of choice, coleslaw & ketchup.', 30000.00, NULL, 6, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1308, 25, 179, 'POP IT LIKE ITS HOT OG', 'wm-pop-og', '10pcs chicken poppers (choose your flavor & dip).', 8000.00, NULL, 1, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1309, 25, 179, 'RANGER', 'wm-ranger', 'Chicken poppers loaded fries with peri peri spice mix, cheese & ranch sauce.', 15000.00, NULL, 2, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1310, 25, 179, 'BOSSMAN', 'wm-bossman', '20pcs chicken poppers (choose your flavor & dip).', 12000.00, NULL, 3, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1311, 25, 179, 'THE SHAKER', 'wm-shaker', '25pcs chicken poppers with suya spice.', 9000.00, NULL, 4, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1312, 25, 179, 'POP STARS', 'wm-pop-stars', '30pcs smothered hot chicken poppers, seasoned wedges & ketchup.', 13000.00, NULL, 5, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1313, 25, 182, 'Coleslaw', 'wm-side-coleslaw', NULL, 5000.00, NULL, 1, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1314, 25, 182, 'Classic French fries', 'wm-side-fries', NULL, 6500.00, NULL, 2, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1315, 25, 182, 'Seasoned Potato wedges', 'wm-side-wedges', NULL, 7500.00, NULL, 3, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1316, 25, 182, 'Spicy suya fries', 'wm-side-suya', NULL, 8000.00, NULL, 4, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1317, 25, 182, 'Cheesy mac', 'wm-side-mac', NULL, 7000.00, NULL, 5, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1318, 25, 182, 'Cajun fried corn', 'wm-side-corn', NULL, 6000.00, NULL, 6, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1319, 25, 183, 'TORNADO', 'wm-combo-tornado', 'Mango habanero chicken tender sandwich, classic French fries & ketchup.', 12500.00, NULL, 1, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1320, 25, 183, 'THE BIG BANG', 'wm-combo-big-bang', 'Cajun chicken tender double cheese burger, coleslaw, seasoned wedges & ketchup.', 16000.00, NULL, 2, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1321, 25, 183, 'TRAFFIC', 'wm-combo-traffic', '8pcs wings in flavor of choice, Cajun fried corn, French fries & ketchup.', 15000.00, NULL, 3, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1322, 25, 183, 'CITIZEN', 'wm-combo-citizen', 'Smothered hot chicken poppers, 8pcs wings in flavor of choice, spicy suya fries & ketchup.', 15000.00, NULL, 4, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1323, 25, 183, 'SUPERBOWL', 'wm-combo-superbowl', 'Superbowl salad: lettuce, sweet corn, purple cabbage, tomatoes, cheese shavings, croutons, chopped sweet chili tenders & lemon honey vinaigrette.', 12500.00, NULL, 5, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1324, 25, 183, 'EMPIRE', 'wm-combo-empire', 'Boneless jerk wing cheese burger, French fries & ketchup.', 15000.00, NULL, 6, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1325, 25, 183, 'SUB', 'wm-combo-sub', 'Boneless teriyaki wings wrap: tortilla, lettuce, cheese shavings, avocado lime sauce, boneless teriyaki wings, classic French fries & ketchup.', 12500.00, NULL, 7, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1326, 25, 184, 'SONIC', 'wm-kids-sonic', '4pcs hickory BBQ wings, kid fries, ketchup & chi smart malt drink.', 7000.00, '6a09dc98e4391.webp', 1, 1, '2026-05-14 22:48:10', '2026-05-17 15:19:52'),
(1327, 25, 184, 'PANDA', 'wm-kids-panda', 'Kid fries, 2pcs sweet chili tenders, ketchup, a pack of reel fruits & chi smart malt drink.', 8500.00, '6a09e173df801.webp', 2, 1, '2026-05-14 22:48:10', '2026-05-17 15:40:35'),
(1328, 25, 184, 'RUGRATS', 'wm-kids-rugrats', '8pcs sweet chili chicken poppers, cheesy mac & chi smart malt drink.', 8000.00, '6a09e195ddca0.webp', 3, 1, '2026-05-14 22:48:10', '2026-05-17 15:41:09'),
(1329, 25, 184, 'BUZZ', 'wm-kids-buzz', 'Waffles, 2pcs BBQ tenders, kid fries, ketchup, a pack of reel fruits & chi smart malt drink.', 12000.00, '6a09e1e402dd9.webp', 4, 1, '2026-05-14 22:48:10', '2026-05-17 15:42:28'),
(1330, 25, 185, 'Chocolate sundae', 'wm-sweet-sundae', NULL, 7500.00, NULL, 1, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1331, 25, 185, 'Mini churros & chocolate dip', 'wm-sweet-churros', NULL, 5500.00, NULL, 2, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1332, 25, 185, 'Apple pies', 'wm-sweet-apple-pies', NULL, 6000.00, NULL, 3, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1333, 25, 185, 'BLIZZARD', 'wm-sweet-blizzard', NULL, 8500.00, NULL, 4, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1334, 25, 185, 'Soft ice-cream (₦1,500)', 'wm-sweet-ice-single', NULL, 1500.00, NULL, 5, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1335, 25, 185, 'Soft ice-cream (₦5,000)', 'wm-sweet-ice-regular', NULL, 5000.00, NULL, 6, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1336, 25, 186, 'Wings on Fire challenge (rules)', 'wm-wof-concept', 'Customers order 20pcs of incredibly hot wings, to be consumed in 60 seconds without any liquid (monitored). If they finish in time: a special meal for free and signature on the illustrious winner wall.', 0.00, NULL, 1, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1337, 25, 186, 'Wings on Fire challenge (order)', 'wm-wof-order', 'Challenge entry / pricing at venue.', 0.00, NULL, 2, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1338, 25, 180, 'Mango habanero', 'wm-fl-1', 'Flavor option (no separate charge).', 0.00, NULL, 1, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1339, 25, 180, 'Classic mild buffalo', 'wm-fl-2', 'Flavor option (no separate charge).', 0.00, NULL, 2, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1340, 25, 180, 'Sweet chili', 'wm-fl-3', 'Flavor option (no separate charge).', 0.00, NULL, 3, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1341, 25, 180, 'Cajun', 'wm-fl-4', 'Flavor option (no separate charge).', 0.00, NULL, 4, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1342, 25, 180, 'Lemon pepper', 'wm-fl-5', 'Flavor option (no separate charge).', 0.00, NULL, 5, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1343, 25, 180, 'Fire power', 'wm-fl-6', 'Flavor option (no separate charge).', 0.00, NULL, 6, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1344, 25, 180, 'Hickory BBQ', 'wm-fl-7', 'Flavor option (no separate charge).', 0.00, NULL, 7, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1345, 25, 180, 'Teriyaki', 'wm-fl-8', 'Flavor option (no separate charge).', 0.00, NULL, 8, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1346, 25, 180, 'Jerk', 'wm-fl-9', 'Flavor option (no separate charge).', 0.00, NULL, 9, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1347, 25, 180, 'Lemon garlic', 'wm-fl-10', 'Flavor option (no separate charge).', 0.00, NULL, 10, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1348, 25, 181, 'Spicy honey mustard', 'wm-dip-1', 'Dip option.', 0.00, NULL, 1, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1349, 25, 181, 'Bangbang', 'wm-dip-2', 'Dip option.', 0.00, NULL, 2, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1350, 25, 181, 'Randy\'s ranch', 'wm-dip-3', 'Dip option.', 0.00, NULL, 3, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1351, 25, 187, 'English Breakfast', 'mb-eng', 'A classic full English plate featuring: golden toast with fluffy scrambled eggs, grilled cherry tomatoes & sautéed mushrooms, juicy sausages & warm baked beans. A hearty, traditional breakfast to start your day right.', 25000.00, NULL, 1, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1352, 25, 187, 'American Breakfast', 'mb-usa', 'A rich, indulgent spread of: fluffy pancakes drizzled with maple syrup, tender beef steak with broccoli & potato sides, fresh farm eggs cooked to your style. A bold and satisfying all-American morning treat.', 30000.00, NULL, 2, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1353, 25, 188, 'Chicken Caesar Salad', 'mb-caesar', 'Classic Caesar with grilled chicken, parmesan, crunchy croutons, and creamy Greek yogurt dressing.', 20000.00, NULL, 1, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1354, 25, 188, 'Conch Salad', 'mb-conch', 'A refreshing mix of calamari, shrimps, bell peppers, pineapple, and Dijon mustard with a spicy habanero kick.', 25000.00, NULL, 2, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1355, 25, 188, 'Caprese Salad with flank steak', 'mb-caprese', 'Mozzarella, avocado, sweet basil and pickles, drizzled with olive oil and Dijon mustard, topped with juicy flank steak.', 25000.00, NULL, 3, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1356, 25, 189, 'Assorted Pepper Soup', 'mb-pepper', 'Traditional spiced broth with assorted meats, scent leaves, and peppers.', 17000.00, NULL, 1, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1357, 25, 189, 'Ginger & Carrot Soup', 'mb-ginger', 'A velvety blend of carrots, ginger, Irish potatoes, and cream.', 12000.00, NULL, 2, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1358, 25, 190, 'Creamy Jackpasta with Stuffed Chicken Breast', 'mb-jackpasta', 'Velvety jack-cheese pasta served with tender, herb-stuffed chicken breast.', 30000.00, NULL, 1, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1359, 25, 190, 'Butter Saffron Rice with Seafood Sauce & Asparagus', 'mb-saffron', 'Fragrant saffron basmati rice topped with juicy prawns and buttery seafood sauce, finished with crisp asparagus.', 40000.00, NULL, 2, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1360, 25, 190, 'Jamaican Oxtail Stew with Rice & Peas', 'mb-oxtail', 'Slow-braised oxtail in rich Caribbean spices, served with coconut rice and kidney beans.', 30000.00, NULL, 3, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1361, 25, 191, 'Veuve Clicquot Brut', 'mb-vcb', 'Bold and crisp, with notes of apple and brioche.', 350000.00, NULL, 1, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1362, 25, 191, 'Veuve Clicquot Rosé', 'mb-vcr', 'Vibrant and fruity, with red berry aromas.', 410000.00, NULL, 2, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1363, 25, 191, 'Moët & Chandon Brut', 'mb-mb', 'Classic champagne, fresh citrus and floral hints.', 386000.00, NULL, 3, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1364, 25, 191, 'Moët & Chandon Rosé', 'mb-mr', 'Elegant rosé with wild strawberry and raspberry notes.', 446000.00, NULL, 4, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1365, 25, 191, 'Moët & Chandon Imperial Brut', 'mb-mi', 'Signature style, balanced with apple and citrus zest.', 398000.00, NULL, 5, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1366, 25, 191, 'Amabile Red', 'mb-ar', 'Medium-bodied, soft berry flavour.', 45000.00, NULL, 6, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1367, 25, 191, 'Amabile Rosé', 'mb-aro', 'Semi-sweet, smooth, fruity finish.', 45000.00, NULL, 7, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1368, 25, 191, 'Carlo Rossi Red', 'mb-crr', 'Smooth, medium-bodied with ripe berry flavours and a soft finish.', 45000.00, NULL, 8, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1369, 25, 191, 'Carlo Rossi White', 'mb-crw', 'Light, crisp, and refreshing with fruity notes.', 45000.00, NULL, 9, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1370, 25, 192, 'MUNCHIEZ DIPERZ', 'hm-m1', 'Tortilla nachos & bang bang dip.', 10000.00, NULL, 1, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1371, 25, 192, 'AFRICAN GIANT PLATTER', 'hm-m2', 'Consisting of wings, chili beef chunks, peppered snails, puff puff, kelewele, prawn skewers, mosa, fried yam, goat chunks and pepper sauce.', 70000.00, NULL, 2, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1372, 25, 192, 'YING YANG', 'hm-m3', '2 flavor calamari. Pan chili calamari & deep fried calamari with garlic mayo.', 15000.00, NULL, 3, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1373, 25, 192, 'CAPRI', 'hm-m4', 'Deep fried goat chunks tossed in green chili with fried yam.', 17000.00, NULL, 4, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1374, 25, 192, 'MEX', 'hm-m5', 'Mince and cheese taquitos with simple salsa.', 10500.00, NULL, 5, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1375, 25, 192, 'DYNAMITEZ', 'hm-m6', 'Prawn dynamites.', 25000.00, NULL, 6, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1376, 25, 192, 'RELOAD ALOHA', 'hm-m7', 'Chicken Caesar salad and dressing.', 15000.00, NULL, 7, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1377, 25, 192, 'THAI TANIC', 'hm-m8', 'Thai fisherman soup with garlic bread.', 15000.00, NULL, 8, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1378, 25, 192, 'DUTCH', 'hm-m9', 'One skillet beef and broccoli with steamed rice.', 12500.00, NULL, 9, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1379, 25, 192, 'TUSCANY (chicken/beef)', 'hm-m10', 'An option of chicken or beef pasta.', 20500.00, NULL, 10, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1380, 25, 192, 'STIR IT UP', 'hm-m11', 'One spicy stir fried rice consisting of shredded beef, shredded chicken, broccoli, assorted bell peppers, spring onion, chili flakes and spices.', 20500.00, NULL, 11, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1381, 25, 192, 'WAIKIKI', 'hm-m12', 'Surf n turf steak, crushed herbed sweet potatoes, glazed marrow & creamy mushroom sauce.', 60000.00, NULL, 12, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1382, 25, 192, 'THE G.O.A.T', 'hm-m13', 'Spicy goat rice mix.', 25000.00, NULL, 13, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1383, 25, 192, 'CHOPPED', 'hm-m14', 'Succulent lamb chops with creamy mushroom sauce, seasoned wedges or Smokey Jollof and coleslaw.', 70000.00, NULL, 14, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1384, 25, 192, 'TUSCANY (Seafood)', 'hm-m15', 'Seafood pasta.', 22500.00, NULL, 15, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1385, 25, 193, 'FISH MONAY (Standard)', 'hm-f1', 'Grilled medium tilapia, expertly seasoned and served with rich, spicy pepper sauce.', 40000.00, NULL, 1, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1386, 25, 193, 'CATFISH SUPREME', 'hm-f2', 'Tender catfish in a flavorful pepper soup broth, served with white rice or grilled garlic bread. A perfect blend of spices and fresh herbs.', 25000.00, NULL, 2, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1387, 25, 193, 'THE POT', 'hm-f3', 'A hearty blend of sweet potato or yam, catfish, smoked fish and aromatic herbs, cooked in rich red oil for a satisfying flavorful meal. Pure comfort in every bite.', 25000.00, NULL, 3, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1388, 25, 193, 'Shepherd\'s Pie', 'hm-f4', 'A comforting dish made with creamy mashed Irish potatoes, savoury minced meat, and a blend of spices, topped with melted parmesan cheese for a perfect finish. A delicious, hearty meal.', 20000.00, NULL, 4, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1389, 25, 193, 'FISH MONAY (Deluxe)', 'hm-f5', 'Full size tilapia, flamed grilled to perfection with bold spice and signature sauce.', 50000.00, NULL, 5, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1390, 25, 193, 'THE KINGS CATCH', 'hm-f6', 'Whole grilled catfish: a majestic serving of whole catfish, marinated in bold spices and grilled to tender, smokey perfection. Packed with flavor and served with your preferred side. A true showstopper.', 60000.00, NULL, 6, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1391, 25, 193, 'Coconut Rice', 'hm-f7', 'Flavourful coconut-infused rice, served with well-seasoned turkey.', 35000.00, NULL, 7, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1392, 25, 193, 'Special Jollof Rice', 'hm-f8', 'Rich smoky jollof rice served with any protein of your choice (Chicken/Turkey/Fish). Additional charges may apply for premium proteins.', 35000.00, NULL, 8, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1393, 25, 193, 'Special Fried Rice', 'hm-f9', 'Savory fried rice with mixed veggies and spices, served with turkey.', 35000.00, NULL, 9, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1394, 25, 194, 'Smokey Jollof rice', 'hm-s1', NULL, 8000.00, NULL, 1, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1395, 25, 194, 'Fried yam', 'hm-s2', NULL, 7500.00, NULL, 2, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1396, 25, 194, 'Steamed rice', 'hm-s3', NULL, 7500.00, NULL, 3, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1397, 25, 194, 'Crushed sweet potatoes', 'hm-s4', NULL, 7500.00, NULL, 4, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1398, 25, 195, 'PICCASSO', 'hm-sw1', 'French toast pudding, ice cream, syrup & berries.', 10000.00, NULL, 1, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1399, 25, 195, 'PIE-RATES', 'hm-sw2', 'Apple crumble & vanilla ice cream.', 12000.00, NULL, 2, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1400, 25, 195, 'SUNDAE', 'hm-sw3', 'Ice cream sundae (as listed on drink menu).', 10000.00, NULL, 3, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1401, 25, 196, 'Red Bull', 'shared-redbull', NULL, 5000.00, NULL, 1, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1402, 25, 196, 'Coke', 'shared-coke', NULL, 2000.00, NULL, 2, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1403, 25, 196, 'Pepsi', 'shared-pepsi', NULL, 2000.00, NULL, 3, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1404, 25, 196, 'Sprite', 'shared-sprite', NULL, 2000.00, NULL, 4, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1405, 25, 196, 'Fanta', 'shared-fanta', NULL, 2000.00, NULL, 5, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1406, 25, 196, '7up', 'shared-7up', NULL, 2000.00, NULL, 6, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1407, 25, 196, 'Pepsi Diet', 'shared-pepsi-diet', NULL, 2000.00, NULL, 7, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1408, 25, 196, 'Pepsi wingman', 'shared-pepsi-wingman', NULL, 2000.00, NULL, 8, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1409, 25, 196, 'Pepsi diet wingman', 'shared-pepsi-diet-wingman', NULL, 2000.00, NULL, 9, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1410, 25, 196, '7up Diet', 'shared-7up-diet', NULL, 2000.00, NULL, 10, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1411, 25, 196, 'Miranda', 'shared-miranda', NULL, 2000.00, NULL, 11, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1412, 25, 196, 'Soda water', 'shared-soda-water', NULL, 2000.00, NULL, 12, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1413, 25, 196, 'Tonic', 'shared-tonic', NULL, 2000.00, NULL, 13, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1414, 25, 196, 'Bitter lemon', 'shared-bitter-lemon', NULL, 2000.00, NULL, 14, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1415, 25, 197, 'Cranberry Juice (glass)', 'shared-cranberry-glass', NULL, 6000.00, NULL, 1, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1416, 25, 197, 'Cranberry Juice (pitcher)', 'shared-cranberry-pitcher', NULL, 15000.00, NULL, 2, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1417, 25, 197, 'Orange Juice (glass)', 'shared-orange-glass', NULL, 5000.00, NULL, 3, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1418, 25, 197, 'Orange Juice (pitcher)', 'shared-orange-pitcher', NULL, 12000.00, NULL, 4, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1419, 25, 197, 'Pineapple Juice (glass)', 'shared-pineapple-glass', NULL, 5000.00, NULL, 5, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1420, 25, 197, 'Pineapple Juice (pitcher)', 'shared-pineapple-pitcher', NULL, 12000.00, NULL, 6, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1421, 25, 197, 'Apple Juice (glass)', 'shared-apple-glass', NULL, 5000.00, NULL, 7, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1422, 25, 197, 'Apple Juice (pitcher)', 'shared-apple-pitcher', NULL, 12000.00, NULL, 8, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1423, 25, 197, 'Chivita Orange Juice', 'shared-chivita-orange', NULL, 12000.00, NULL, 9, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1424, 25, 197, 'Chivita Pineapple Juice', 'shared-chivita-pineapple', NULL, 12000.00, NULL, 10, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1425, 25, 197, 'Chivita Apple Juice', 'shared-chivita-apple', 'As listed on menu (Chivita Apple Juice…).', 12000.00, NULL, 11, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1426, 25, 197, 'Perrier', 'shared-perrier', NULL, 5000.00, NULL, 12, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1427, 25, 198, 'Strawberry milkshake', 'shared-milkshake-strawberry', NULL, 12500.00, NULL, 1, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1428, 25, 198, 'Salted caramel milk shake', 'shared-milkshake-salted-caramel', NULL, 12500.00, NULL, 2, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1429, 25, 198, 'S’mores chocolate milkshake', 'shared-milkshake-smores', NULL, 12500.00, NULL, 3, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1430, 25, 198, 'Oreo cheesecake milkshake', 'shared-milkshake-oreo-cheesecake', NULL, 12500.00, NULL, 4, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1431, 25, 199, 'Banana & Mango', 'shared-smoothie-banana-mango', NULL, 12500.00, NULL, 1, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1432, 25, 199, 'Watermelon & Strawberry', 'shared-smoothie-watermelon-strawberry', NULL, 12500.00, NULL, 2, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1433, 25, 199, 'BANANA&STRAWBERRY', 'shared-smoothie-banana-strawberry', NULL, 12500.00, NULL, 3, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1434, 25, 199, 'KALE Green', 'shared-smoothie-kale-green', NULL, 12500.00, NULL, 4, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1435, 25, 200, 'Veuve Clicquot Brut', 'hm-ch1', NULL, 350000.00, NULL, 1, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1436, 25, 200, 'Veuve Clicquot Rosé', 'hm-ch2', NULL, 410000.00, NULL, 2, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1437, 25, 200, 'Moët et Chandon Brut', 'hm-ch3', NULL, 386000.00, NULL, 3, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1438, 25, 200, 'Moët et Chandon Rosé', 'hm-ch4', NULL, 446000.00, NULL, 4, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1439, 25, 200, 'Moët et Chandon Imperial Brut', 'hm-ch5', NULL, 398000.00, NULL, 5, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1440, 25, 200, 'Dom Pérignon Brut', 'hm-ch6', NULL, 1250000.00, NULL, 6, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1441, 25, 200, 'Ace Of Spades', 'hm-ch7', NULL, 1250000.00, NULL, 7, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1442, 25, 200, 'Ace Of Spades Rosé', 'hm-ch8', NULL, 1850000.00, NULL, 8, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1443, 25, 200, 'LP Rosé', 'hm-ch9', NULL, 380000.00, NULL, 9, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1444, 25, 200, 'LP Brut', 'hm-ch10', NULL, 290000.00, NULL, 10, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1445, 25, 201, 'Johnnie Walker Black Label', 'hm-w1', 'Bottle ₦150,000. Glass/5cl ₦15,000.', 150000.00, NULL, 1, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1446, 25, 201, 'Johnnie Walker Blue Label', 'hm-w2', NULL, 600000.00, NULL, 2, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1447, 25, 201, 'Johnnie Walker Green Label', 'hm-w3', NULL, 300000.00, NULL, 4, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1448, 25, 201, 'Johnnie Walker Gold Label', 'hm-w4', NULL, 250000.00, NULL, 5, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1449, 25, 201, 'Jameson Irish Original', 'hm-w5', NULL, 195000.00, NULL, 6, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1450, 25, 201, 'Glenfiddich 12', 'hm-w6', 'Bottle ₦200,000. Glass/5cl ₦20,000.', 200000.00, NULL, 7, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1451, 25, 201, 'Glenfiddich 15', 'hm-w7', NULL, 370000.00, NULL, 8, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1452, 25, 201, 'Glenfiddich 18', 'hm-w8', NULL, 450000.00, NULL, 9, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1453, 25, 201, 'Glenfiddich 21', 'hm-w9', NULL, 750000.00, NULL, 10, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1454, 25, 201, 'Monkey Shoulder', 'hm-w10', 'Bottle ₦130,000. Glass/5cl ₦12,000.', 130000.00, NULL, 11, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1455, 25, 201, 'Macallan 12', 'hm-w11', 'Bottle ₦150,000. Glass/5cl ₦15,000.', 150000.00, NULL, 12, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1456, 25, 201, 'Macallan 15', 'hm-w12', NULL, 280000.00, NULL, 13, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1457, 25, 201, 'Macallan 18', 'hm-w13', NULL, 550000.00, NULL, 14, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1458, 25, 201, 'Jameson Black Barrel', 'hm-w14', NULL, 250000.00, NULL, 15, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1459, 25, 201, 'Balvenie 12', 'hm-w15', NULL, 221000.00, NULL, 16, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1460, 25, 201, 'Balvenie 14', 'hm-w16', NULL, 300000.00, NULL, 17, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1461, 25, 201, 'Jack Daniel\'s', 'hm-w17', NULL, 220000.00, NULL, 18, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1462, 25, 201, 'The Singleton', 'hm-w18', NULL, 225000.00, NULL, 19, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1463, 25, 201, 'The Pogues', 'hm-w19', NULL, 100000.00, NULL, 20, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1464, 25, 202, 'Hennessy VSOP', 'hm-cg1', NULL, 400000.00, NULL, 1, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1465, 25, 202, 'Hennessy VS', 'hm-cg2', 'Glass 5cl ₦20,000', 300000.00, NULL, 2, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1466, 25, 202, 'Martell Blue Swift', 'hm-cg3', NULL, 300000.00, NULL, 3, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1467, 25, 202, 'Martell XO', 'hm-cg4', NULL, 780000.00, NULL, 4, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1468, 25, 202, 'Hennessy XO', 'hm-cg5', NULL, 800000.00, NULL, 5, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1469, 25, 203, 'Hendrick\'s', 'hm-g1', 'Glass 5cl ₦18,000', 235000.00, NULL, 1, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1470, 25, 203, 'Gin Mare', 'hm-g2', 'Glass 5cl ₦9,000', 160000.00, NULL, 2, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1471, 25, 203, 'Tanqueray No. Ten', 'hm-g3', NULL, 195000.00, NULL, 3, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1472, 25, 203, 'Bombay Sapphire', 'hm-g4', 'Glass 5cl ₦13,500', 167000.00, NULL, 4, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1473, 25, 203, 'Monkey 47', 'hm-g5', NULL, 150000.00, NULL, 5, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1474, 25, 203, 'Cape Town', 'hm-g6', 'Glass 5cl ₦11,500', 180000.00, NULL, 6, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1475, 25, 204, 'Belvedere', 'hm-v1', NULL, 200000.00, NULL, 1, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1476, 25, 204, 'Grey Goose', 'hm-v2', NULL, 150000.00, NULL, 2, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1477, 25, 204, 'Absolut', 'hm-v3', 'Glass 5cl ₦10,000', 155000.00, NULL, 3, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1478, 25, 204, 'Cîroc', 'hm-v4', NULL, 150000.00, NULL, 4, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1479, 25, 205, 'Jose Cuervo (premium)', 'hm-t1', 'Shot ₦12,000', 160000.00, NULL, 1, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1480, 25, 205, 'Jose Cuervo (standard)', 'hm-t2', 'Shot ₦7,000', 110000.00, NULL, 2, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1481, 25, 205, 'Casamigos Añejo', 'hm-t3', NULL, 550000.00, NULL, 3, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1482, 25, 205, 'Casamigos Reposado', 'hm-t4', NULL, 520000.00, NULL, 4, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1483, 25, 205, 'Don Julio 1942', 'hm-t5', NULL, 900000.00, NULL, 5, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1484, 25, 205, 'Don Julio Reposado', 'hm-t6', NULL, 550000.00, NULL, 6, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1485, 25, 205, 'Patrón Blanco', 'hm-t7', 'Glass 5cl ₦15,000', 200000.00, NULL, 7, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1486, 25, 205, 'Patrón Reposado', 'hm-t8', NULL, 200000.00, NULL, 8, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1487, 25, 205, 'Patrón Añejo', 'hm-t9', NULL, 350000.00, NULL, 9, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1488, 25, 205, 'Clase Azul Reposado', 'hm-t10', NULL, 950000.00, NULL, 10, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1489, 25, 205, 'Clase Azul Añejo', 'hm-t11', NULL, 2500000.00, NULL, 11, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1490, 25, 205, 'Vivir Blanco', 'hm-t12', NULL, 270000.00, NULL, 12, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1491, 25, 205, 'Vivir Reposado', 'hm-t13', NULL, 350000.00, NULL, 13, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1492, 25, 205, 'Teremana Reposado', 'hm-t14', NULL, 500000.00, NULL, 14, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1493, 25, 206, 'Heineken Draft', 'hm-b1', NULL, 5000.00, NULL, 1, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10');
INSERT INTO `menu_items` (`id`, `restaurant_id`, `category_id`, `name`, `slug`, `description`, `price`, `image`, `display_order`, `is_available`, `created_at`, `updated_at`) VALUES
(1494, 25, 206, 'Guinness / Legend', 'hm-b2', NULL, 5000.00, NULL, 2, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1495, 25, 206, 'Tiger', 'hm-b3', NULL, 5000.00, NULL, 3, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1496, 25, 206, 'Star Radler Can', 'hm-b4', NULL, 4000.00, NULL, 4, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1497, 25, 207, 'DC Sweet (white, bottle)', 'hm-ww1', 'Bottle ₦45,000; glass ₦20,000 (per menu).', 45000.00, NULL, 1, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1498, 25, 207, 'DC Dry Chenin (white, bottle)', 'hm-ww2', 'Bottle ₦45,000; glass ₦20,000.', 45000.00, NULL, 2, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1499, 25, 207, 'DC Dry Sweet (white, bottle)', 'hm-ww-drysweet', 'Menu listing (Dc SDc Dryweet). Bottle ₦45,000; glass ₦20,000.', 45000.00, NULL, 3, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1500, 25, 207, 'Sungoddess (white)', 'hm-ww3', NULL, 100000.00, NULL, 4, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1501, 25, 207, 'Santa Rita 120 Chardonnay', 'hm-ww4', NULL, 100000.00, NULL, 5, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1502, 25, 207, 'Amabile Sweet (white)', 'hm-ww5', 'Juicy Italian white wine.', 45000.00, NULL, 6, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1503, 25, 208, 'Sungoddess Pinot Grigio', 'hm-rw1', NULL, 100000.00, NULL, 1, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1504, 25, 208, 'Ermelinda Tulipa Rosé', 'hm-rw2', NULL, 50000.00, NULL, 2, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1505, 25, 208, 'Amabili Di Rosa', 'hm-rw3', 'Juicy Italian wine.', 40000.00, NULL, 3, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1506, 25, 209, 'DC Sweet Red (bottle)', 'hm-rd1', 'Glass ₦16,000.', 40000.00, NULL, 1, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1507, 25, 209, 'Amabile Di Rosa (red)', 'hm-rd2', 'Glass ₦16,000.', 40000.00, NULL, 2, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1508, 25, 209, 'DC Sweet Red (sweet & smooth)', 'hm-rd-sweet', 'Sweet & smooth with fruits & flowers. Glass ₦16,000.', 40000.00, NULL, 3, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1509, 25, 209, 'DC Dry Red', 'hm-rd3', 'Glass ₦16,000.', 40000.00, NULL, 4, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1510, 25, 209, 'Bla Bla', 'hm-rd4', NULL, 60000.00, NULL, 5, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1511, 25, 209, 'Escudo Rojo', 'hm-rd5', 'Glass ₦18,000.', 60000.00, NULL, 6, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1512, 25, 209, 'Carlo Rossi (red)', 'hm-rd6', NULL, 40000.00, NULL, 7, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1513, 25, 209, '4 Cousins', 'hm-rd7', NULL, 40000.00, NULL, 8, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1514, 25, 209, 'Prosecco Rosario', 'hm-rd8', 'Glass 5cl ₦9,000.', 42000.00, NULL, 9, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1515, 25, 209, 'Sungoddess (red)', 'hm-rd9', NULL, 65000.00, NULL, 10, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1516, 25, 210, 'Mr Flinstone', 'hm-ck1', 'Gin, pineapple, amaro, yellow chartreuse, honey/ginger syrup, lemon juice & aromatic bitters.', 22500.00, NULL, 1, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1517, 25, 210, 'Roller coaster', 'hm-ck2', 'Vodka, Midori, Cointreau, lemon juice, egg white & aquafaba.', 22500.00, NULL, 2, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1518, 25, 210, 'Orange & Basil', 'hm-ck3', 'Gin, orange & basil cordial, lime cordial.', 22500.00, NULL, 3, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1519, 25, 210, 'Peer pressure', 'hm-ck4', 'Apple cider vinegar, celery, honey syrup, bitters, lemon juice, pear juice, soda, aged rum & Grand Marnier.', 22500.00, NULL, 4, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1520, 25, 210, 'Colder club', 'hm-ck5', 'Fresh fig, fresh raspberries, almond syrup, gin, lemon juice (optional), egg white, aquafaba.', 22500.00, NULL, 5, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1521, 25, 210, 'The bullshort', 'hm-ck6', 'Vodka, grapefruit juice, red vermouth, elderflower liqueur.', 22500.00, NULL, 6, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1522, 25, 210, 'Borrowed Time', 'hm-ck7', 'Whiskey, triple sec, grapefruit juice, thyme syrup.', 22500.00, NULL, 7, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1523, 25, 210, 'Sunny Spritzer', 'hm-ck8', 'Limoncello, lemon soda, Prosecco.', 22500.00, NULL, 8, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1524, 25, 210, 'Berry Whipped', 'hm-ck9', 'Lemon juice, cranberry juice, raspberry liqueur, white rum, aquafaba.', 22500.00, NULL, 9, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1525, 25, 210, 'Frame Up', 'hm-ck10', 'Pineapple juice, Midori, coconut rum, vodka.', 22500.00, NULL, 10, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1526, 25, 210, 'Bloody Mary', 'hm-ck11', 'Classic cocktail.', 22500.00, NULL, 11, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1527, 25, 210, 'Long Island Iced Tea', 'hm-ck12', 'Vodka, tequila, gin, triple sec, lemon juice, cola & rum.', 22500.00, NULL, 12, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1528, 25, 210, 'Wild Sex', 'hm-ck13', 'Rum, juice, triple sec, vodka, coconut & rum.', 20000.00, NULL, 13, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1529, 25, 210, 'Cosmopolitan', 'hm-ck14', 'Vodka, triple sec, juice & lime juice.', 20000.00, NULL, 14, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1530, 25, 210, 'Chapman', 'hm-ck15', NULL, 20000.00, NULL, 15, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1531, 25, 210, 'Mai Tai', 'hm-ck16', 'Rum, triple sec, gold rum, lime juice & almond syrup.', 20000.00, NULL, 16, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1532, 25, 210, 'Sex On The Beach', 'hm-ck17', 'Vodka, peach liqueur, juice & grenadine.', 20000.00, NULL, 17, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1533, 25, 210, 'Gin Basil', 'hm-ck18', 'Gin, simple syrup, basil leaf & lemon juice.', 20000.00, NULL, 18, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1534, 25, 210, 'Porn Star Martini', 'hm-ck19', 'Vanilla syrup, vodka, passion fruit liqueur, lime juice & sparkling wine.', 20000.00, NULL, 19, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1535, 25, 210, 'Strawberry Daiquiri', 'hm-ck20', 'Rum, lime juice & syrup.', 20000.00, NULL, 20, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1536, 25, 210, 'Whiskey Sour', 'hm-ck21', 'Lemon juice, simple syrup, bitters & egg.', 20000.00, NULL, 21, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1537, 25, 210, 'Margarita', 'hm-ck22', 'Classic.', 22500.00, NULL, 22, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1538, 25, 211, 'Mango Favez', 'hm-mk1', 'Mango puree, fresh mint leaf, lime juice, mango soda.', 18000.00, NULL, 1, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1539, 25, 211, 'Sunny Breeze', 'hm-mk2', 'Coconut cordial, grapefruit juice, 7up.', 18000.00, NULL, 2, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1540, 25, 211, 'Passion Rise', 'hm-mk3', 'Fresh passion fruit, grenadine syrup, orange juice, lime juice.', 18000.00, NULL, 3, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1541, 25, 211, 'Goodluck Charm', 'hm-mk4', 'Grapefruit juice, guava juice, strawberry puree, cranberry juice.', 18000.00, NULL, 4, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1542, 25, 211, 'Peach & Thyme Fizz', 'hm-mk5', 'Peach syrup, lemon juice, soda water.', 18000.00, NULL, 5, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1543, 25, 211, 'Strawberry margarita', 'hm-mk6', NULL, 22500.00, NULL, 6, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1544, 25, 212, 'Banana & Mango smoothie', 'hm-sm1', NULL, 18000.00, NULL, 1, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1545, 25, 212, 'Watermelon & Strawberry smoothie', 'hm-sm2', NULL, 18000.00, NULL, 2, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1546, 25, 212, 'Banana & Strawberry smoothie', 'hm-sm-bs', NULL, 18000.00, NULL, 3, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10'),
(1547, 25, 212, 'KALE Green smoothie', 'hm-sm3', NULL, 18000.00, NULL, 4, 1, '2026-05-14 22:48:10', '2026-05-14 22:48:10');

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
(12, '0NQ0334D', 2, 'Abdulrahman Shittu', '08032336586', 'mr.carter.tech07@gmail.com', 'fsfs', 'bank_transfer', 'confirmed', 100.00, 0.00, 0.00, 100.00, '2026-02-12 23:30:50', '2026-02-12 23:31:55'),
(13, 'VNW31EG9', 3, 'Abdulrahman Shittu', '08032336586', 'mr.carter.tech07@gmail.com', 'wrrr', 'bank_transfer', 'confirmed', 60000.00, 0.00, 0.00, 60000.00, '2026-02-17 14:48:59', '2026-02-17 14:50:08');

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
(18, 12, 41, 'Chicken Spring Rolls', 100.00, 1, '2026-02-12 23:30:50'),
(19, 13, 646, 'Premium Tray', 60000.00, 1, '2026-02-17 14:48:59');

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

--
-- Dumping data for table `password_reset_tokens`
--

INSERT INTO `password_reset_tokens` (`id`, `user_type`, `user_id`, `identifier`, `email`, `token_hash`, `expires_at`, `used_at`, `request_ip`, `user_agent`, `created_at`) VALUES
(1, 'manager', 12, 'abrobiz@gmail.com', 'abrobiz@gmail.com', '6d181ecb6e1d7415f25b2ce33d0b68222db7a53efef84c6c49479571a143f022', '2026-03-13 09:00:13', '2026-03-13 08:00:51', '102.207.247.18', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-13 08:00:13');

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
(5, 2, 1, 220.00, 'NGN', 'paystack', 'PS_1766624771_b04c1caf52ed9745', NULL, 'pending', NULL, '2025-12-25 01:06:11'),
(9, 19, 18, 120900.00, 'NGN', 'paystack', 'PS_1775494894_361b2945d74f30c2', '{\"id\":6010814859,\"domain\":\"live\",\"status\":\"success\",\"reference\":\"PS_1775494894_361b2945d74f30c2\",\"receipt_number\":null,\"amount\":12090000,\"message\":null,\"gateway_response\":\"Approved\",\"paid_at\":\"2026-04-06T17:17:46.000Z\",\"created_at\":\"2026-04-06T17:01:35.000Z\",\"channel\":\"bank_transfer\",\"currency\":\"NGN\",\"ip_address\":\"102.88.55.173\",\"metadata\":{\"payment_id\":\"9\",\"subscription_id\":\"18\",\"restaurant_id\":\"19\",\"plan_id\":\"2\",\"billing_cycle\":\"annual\",\"referrer\":\"https:\\/\\/our-menu.online\\/\"},\"log\":{\"start_time\":1775494899,\"time_spent\":967,\"attempts\":0,\"errors\":0,\"success\":true,\"mobile\":false,\"input\":[],\"history\":[{\"type\":\"pending\",\"message\":\"Payment in progress with bank\",\"time\":1},{\"type\":\"action\",\"message\":\"Set payment method to: bank\",\"time\":8},{\"type\":\"action\",\"message\":\"Set payment method to: bank_transfer\",\"time\":18},{\"type\":\"success\",\"message\":\"Successfully paid with bank_transfer\",\"time\":967}]},\"fees\":191350,\"fees_split\":null,\"authorization\":{\"authorization_code\":\"AUTH_jk11d3w884\",\"bin\":\"540XXX\",\"last4\":\"X718\",\"exp_month\":\"04\",\"exp_year\":\"2026\",\"channel\":\"bank_transfer\",\"card_type\":\"transfer\",\"bank\":\"Providus Bank\",\"country_code\":\"NG\",\"brand\":\"Managed Account\",\"reusable\":false,\"signature\":null,\"account_name\":null,\"sender_bank\":\"Providus Bank\",\"sender_country\":\"NG\",\"sender_bank_account_number\":\"XXXXXXX718\",\"sender_name\":\"VENDOME ENT. (PETTY CASH A\\/C)\",\"narration\":\"To TITAN-PAYSTACK | PAYSTACK CHECKOUT Opal qr code\",\"receiver_bank_account_number\":null,\"receiver_bank\":null},\"customer\":{\"id\":353433914,\"first_name\":null,\"last_name\":null,\"email\":\"opallagos1@gmail.com\",\"customer_code\":\"CUS_5k609h7ceyoi1oe\",\"phone\":null,\"metadata\":null,\"risk_action\":\"default\",\"international_format_phone\":null},\"plan\":null,\"split\":[],\"order_id\":null,\"paidAt\":\"2026-04-06T17:17:46.000Z\",\"createdAt\":\"2026-04-06T17:01:35.000Z\",\"requested_amount\":12090000,\"pos_transaction_data\":null,\"source\":null,\"fees_breakdown\":[{\"amount\":191350,\"formula\":null,\"type\":\"paystack\"}],\"connect\":null,\"transaction_date\":\"2026-04-06T17:01:35.000Z\",\"plan_object\":[],\"subaccount\":[]}', 'success', '2026-04-06 17:18:09', '2026-04-06 17:01:34');

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
(1, 'paystack', 1, 0, 'pk_live_a7cb9e736edaa1f9d08cbaac4202c7d4a3b3d84c', 'OWXN9U8jP/nRIKUbX772IDo6SkpZQ2s2eElxSWdOajlTZUVZUUQxeWxrN0ZqSndwU2s0cGFURGgrd21PMmpLb1JvbnpOUS9pWWUvbWdncy9HOG0wbEltSGxtT0xyR3ROMFdVOGRvRlE9PQ==', '', 'sigsol2024', '1aMJkPa6LL4sKIi8NwaMkjo6MEc3OHEycTR4L2ZrUFUwSFFlMXBkNnFFN2FFekNhWHhWd3UydWd1WGE5QT0=', '', '2025-12-24 02:38:31', '2026-03-07 21:27:42'),
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
(2, '04a0e81ab38e7e08216b95d376fa6742ac0c5388c16cc6c4', 2, 'order', NULL, '[{\"id\":41,\"name\":\"Chicken Spring Rolls\",\"price\":100,\"image\":\"6945dee5937fd.jpg\",\"quantity\":1}]', 'Abdulrahman Shittu', '08032336586', 'sigsol2024@gmail.com', 'aaaf', 100.00, 0.00, 0.00, 100.00, '2026-02-11 13:13:49'),
(8, '233fd6ed11ddc38ea0d9885cf83e5b7259c9d7b8a928d7ad', 3, 'order', NULL, '[{\"id\":650,\"name\":\"Classic English Breakfast\",\"price\":10000,\"image\":\"\",\"quantity\":1},{\"id\":651,\"name\":\"African Breakfast\",\"price\":10000,\"image\":\"\",\"quantity\":1},{\"id\":653,\"name\":\"Chef\'s Salad\",\"price\":12000,\"image\":\"\",\"quantity\":1},{\"id\":654,\"name\":\"Chicken Caesar Salad\",\"price\":15000,\"image\":\"\",\"quantity\":1},{\"id\":655,\"name\":\"Russian Salad\",\"price\":15000,\"image\":\"\",\"quantity\":1}]', 'Abdulrahman Shittu', '08032336586', 'mr.carter.tech07@gmail.com', 'fsdgddgdd', 62000.00, 0.00, 0.00, 62000.00, '2026-02-17 14:46:07'),
(10, 'c2749fdd2874e357e505f60c1a9c5cfb40ed0ef48009699f', 2, 'order', NULL, '[{\"id\":41,\"name\":\"Chicken Spring Rolls\",\"price\":100,\"image\":\"6945dee5937fd.jpg\",\"quantity\":3},{\"id\":42,\"name\":\"Grilled Chicken Wings\",\"price\":23000,\"image\":\"6945df24c6e77.jpg\",\"quantity\":1},{\"id\":43,\"name\":\"Lollipop Chicken\",\"price\":25000,\"image\":\"6945e61603f0b.jpg\",\"quantity\":1},{\"id\":44,\"name\":\"Caesar Chicken Sliders\",\"price\":19000,\"image\":\"6945e63143e76.jpg\",\"quantity\":1}]', 'Abdulrahman Shittu', '08032336586', 'sigsol2024@gmail.com', 'srfsffsffssfsf', 67300.00, 0.00, 0.00, 67300.00, '2026-02-23 11:08:24');

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
-- Table structure for table `public_api_rate_events`
--

CREATE TABLE `public_api_rate_events` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `action` varchar(64) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(2, 2, '197.211.59.73', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', 'Mobile', 'Chrome', 'Linux', 'Nigeria', 'Lagos', 6.44740000, 3.39030000, '2025-12-24 00:11:57'),
(3, 3, '102.89.83.48', 'QR Scanner Android', 'Mobile', 'Unknown', 'Android', 'Nigeria', 'Lagos', 6.44740000, 3.39030000, '2026-03-10 14:41:01'),
(4, 3, '102.89.83.48', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 'Mobile', 'Chrome', 'Linux', 'Nigeria', 'Lagos', 6.44740000, 3.39030000, '2026-03-10 14:41:07'),
(5, 3, '102.89.83.48', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 'Mobile', 'Chrome', 'Linux', 'Nigeria', 'Lagos', 6.44740000, 3.39030000, '2026-03-10 16:04:10'),
(6, 2, '102.89.82.149', 'QR Scanner Android', 'Mobile', 'Unknown', 'Android', 'Nigeria', 'Lagos', 6.44740000, 3.39030000, '2026-03-11 14:09:27'),
(7, 2, '102.89.82.149', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 'Mobile', 'Chrome', 'Linux', 'Nigeria', 'Lagos', 6.44740000, 3.39030000, '2026-03-11 14:09:29'),
(10, 2, '102.88.112.82', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 'Mobile', 'Chrome', 'Linux', 'Nigeria', 'Lagos', 6.44740000, 3.39030000, '2026-03-20 16:33:40'),
(11, 2, '102.88.112.82', 'QR Scanner Android', 'Mobile', 'Unknown', 'Android', 'Nigeria', 'Lagos', 6.44740000, 3.39030000, '2026-03-20 16:33:43'),
(12, 13, '190.2.149.91', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'Desktop', 'Chrome', 'Windows', 'Netherlands', 'Naaldwijk', 51.99680000, 4.20570000, '2026-03-20 17:03:54'),
(13, 13, '190.2.149.91', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'Desktop', 'Chrome', 'Windows', 'Netherlands', 'Naaldwijk', 51.99680000, 4.20570000, '2026-03-20 17:04:12'),
(14, 13, '102.88.112.82', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 'Mobile', 'Chrome', 'Linux', 'Nigeria', 'Lagos', 6.44740000, 3.39030000, '2026-03-20 17:09:35'),
(15, 13, '102.91.93.192', 'Mozilla/5.0 (Linux; U; Android 12; TECNO BF6 Build/SP1A.210812.001; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/145.0.7632.162 Mobile Safari/537.36 OPR/96.0.2254.79777', 'Mobile', 'Chrome', 'Linux', 'Nigeria', 'Funtua', 11.52350000, 7.31174000, '2026-03-21 13:17:09');

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
(18, 'SG QR NO LOGO', '', '18.svg', 1, '{\"pattern\":\"square\",\"eyes\":\"rounded\",\"frame\":{\"type\":\"rounded\",\"text\":\"SCAN ME\",\"color\":\"#051357\",\"text_color\":\"#ffffff\",\"text_size\":14,\"bg_enabled\":true,\"bg_color\":\"#252154\"},\"colors\":{\"foreground\":\"#f7f7f7\",\"background\":\"#2f326f\"},\"logo\":{\"enabled\":false,\"size\":0.200000000000000011102230246251565404236316680908203125,\"center_only\":true}}', 1, '2026-03-10 14:44:24', '2026-03-10 15:20:59'),
(19, 'SG QR WITH LOGO', 'SG QR WITH LOGO', '19.svg', 1, '{\"pattern\":\"square\",\"eyes\":\"square\",\"frame\":{\"type\":\"none\",\"text\":\"SCAN ME\",\"color\":\"#000000\",\"text_color\":\"#ffffff\",\"text_size\":14,\"bg_enabled\":true,\"bg_color\":\"#000000\"},\"colors\":{\"foreground\":\"#000000\",\"background\":\"#ffffff\"},\"logo\":{\"enabled\":false,\"size\":0.200000000000000011102230246251565404236316680908203125,\"center_only\":true}}', 1, '2026-03-20 17:08:41', '2026-03-20 17:08:41');

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
  `enable_food_ordering` tinyint(1) NOT NULL DEFAULT 1,
  `enable_table_reservations` tinyint(1) NOT NULL DEFAULT 1,
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

INSERT INTO `restaurants` (`id`, `name`, `slug`, `logo`, `hero_image`, `description`, `phone`, `email`, `address`, `website`, `whatsapp_link`, `instagram_url`, `facebook_url`, `twitter_url`, `enable_food_ordering`, `enable_table_reservations`, `map_latitude`, `map_longitude`, `header_menu_items`, `footer_content`, `manager_email`, `google_rating`, `rating_source`, `template_id`, `is_active`, `available_items_count`, `unavailable_items_count`, `subscription_id`, `created_at`, `updated_at`) VALUES
(2, 'LAVA', 'lava', '69459eb555362.jpg', '69459edb896e3.png', 'Premium dining experience with exquisite cuisine and fine beverages', '+234 800 000 0000', 'info@lava.com', 'LAVA., 5 Adetokunbu Ademola Street, Victoria Island', NULL, '', '', 'https://web.facebook.com/theviewhotellekki', '', 1, 1, NULL, NULL, '[\r\n  {\"label\": \"Menu\", \"url\": \"#menu\"},\r\n  {\"label\": \"Drinks\", \"url\": \"#drinks\"}\r\n]', 'af', 'jamesamaila07@gmail.com', 4.5, 'Google', 3, 1, 65, 0, 1, '2025-12-19 18:43:07', '2026-03-20 14:37:20'),
(3, 'Theview Hotel Lekki', 'theview-hotel', '698ee78360beb.jpg', '698ee783613af.jpg', 'Our restaurant offers the best platters like our very popular Ogazi Platter (Guinea fowl platter), D View Special Platter and Pacific Platter.', '+23490 9091 3608', 'reservations@theviewlekki.com', '1, Godwin Omene Street, Chief Collins Uchidiuno, Off Fola Osibo, Lekki Phase 1, Lagos, Nigeria', NULL, 'https://wa.link/g1n8bq', 'https://www.instagram.com/theviewlekki/', 'https://web.facebook.com/theviewhotellekki', '', 1, 1, NULL, NULL, NULL, 'Our restaurant offers the best platters like our very popular Ogazi Platter (Guinea fowl platter), D View Special Platter and Pacific Platter.', 'reservations@theviewlekki.com', 4.5, 'Google', 6, 1, 229, 0, 2, '2026-02-13 08:57:39', '2026-05-07 12:44:03'),
(4, 'NOSTALGIA', 'nostalgia-menu', '6a03d7489f311.png', '6a045d414dc91.jpg', '', '+234 911 311 9337', 'info@nostalgialagos.com', '88 Hakeem Dickson Road, Lekki Phase 1', NULL, '', 'https://www.instagram.com/vcphotels/?hl=en', '', '', 0, 0, NULL, NULL, NULL, 'A Fusion of Culture, Style & Vibrant Nights.  \r\n  Offers both Dine-in and Take-out options.  \r\n    \r\n  ## Operating Hours  \r\n     Open: Tuesdays through Sundays, from 12:00 PM (noon) to 3:00 AM.  \r\n     Closed: Mondays.  \r\n     Sunday Brunch: Available from 12:00 PM (noon) to 4:30 PM.  \r\n\r\n    \r\n  ## Contact & Location   \r\n     Address: 88 Hakeem Dickson Road, Lekki Phase 1, Lagos, Nigeria 105102.', 'admin@nostalgia.our-menu.online', 4.5, 'Google', 18, 1, 167, 0, 3, '2026-03-03 23:30:50', '2026-05-14 12:13:21'),
(13, 'Café De Bourgeois', 'the-lusso-restaurant', '69b4163236007.jpg', '69b41a5c8e5f6.jpg', 'The lusso hotel abuja', '', 'restaurant@lussohotelsabuja.com', '33 Usuma St, Maitama, Abuja 904101, Federal Capital Territory', NULL, '', '', '', '', 1, 1, NULL, NULL, NULL, '', 'restaurant@lussohotelsabuja.com', 4.5, 'Google', 6, 1, 307, 0, 12, '2026-03-12 23:11:27', '2026-05-04 11:42:14'),
(19, 'OPAL CAFE MENU', 'opal-cafe-menu', NULL, NULL, 'Cafe', '2349024262089', 'opallagos1@gmail.com', 'No 5 Adetukunbo Ademola Victoria Island, Lagos', NULL, '', '', '', '', 1, 1, NULL, NULL, NULL, NULL, 'opallagos1@gmail.com', 4.5, 'Google', 1, 1, 18, 0, 18, '2026-04-06 17:01:14', '2026-05-06 02:06:18'),
(20, 'Swiss The Vistana', 'swiss-the-vistana', NULL, NULL, '', '09168340156', 'it.vistana@swissinternationalhotels.com', '', NULL, '', 'https://l.instagram.com/', '', '', 1, 1, NULL, NULL, NULL, NULL, 'it.vistana@swissinternationalhotels.com', 5.0, 'Google', 1, 1, 0, 0, 19, '2026-04-28 20:07:22', '2026-04-28 20:07:22'),
(21, 'Ellipse Hotels', 'ellipse-hotels', '6a06e916909bc.jpg', NULL, 'Luxury and Comfort Redefined', '08109453960', 'ellipsehotelslagos@gmail.com', 'N0 31 Shola Adewumi Street, Bucknor Ejigbo Lagos State', NULL, '', '', '', '', 0, 1, NULL, NULL, NULL, '', 'ellipsehotelslagos@gmail.com', 4.5, 'Google', 1, 1, 0, 0, 20, '2026-05-09 17:25:57', '2026-05-15 09:36:22'),
(25, 'The Mania House', 'the-mania-house', '6a0a2610c2af8.png', '6a06635db76a9.jpg', 'Victoria Island Lagos', '08144258984', 'admin@maniahouse.our-menu.online', '25a Gafari Animashaun St, Victoria Island, Lagos 101241, Lagos', NULL, '', 'https://www.instagram.com/themaniahouse/?hl=en', '', '', 1, 0, NULL, NULL, NULL, '', 'admin@maniahouse.our-menu.online', 4.5, 'Google', 16, 1, 252, 0, 24, '2026-05-14 14:20:15', '2026-05-17 20:33:20'),
(26, 'Salt And Social', 'salt-and-social', NULL, NULL, 'Our menu is a celebration of classic favorites, from juicy burgers and sizzling steaks to mouthwatering pizzas', '09083338888', 'admin@saltandsocial.our-menu.online', '2a Admiralty Wy, Lekki Phase 1, Lagos 105102, Lagos', NULL, '', 'https://www.instagram.com/s.a.l.t.s.o.c.i.a.l/', '', '', 1, 1, NULL, NULL, NULL, NULL, 'admin@saltandsocial.our-menu.online', 4.5, 'Google', 1, 1, 0, 0, 25, '2026-05-17 21:04:00', '2026-05-17 21:04:00');

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
(1, 2, 'bank_transfer', 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, 'access bank', '82478658248475', 'Lava Fly', '2026-02-09 17:46:32', '2026-03-12 19:59:01'),
(2, 2, 'paystack', 1, 0, '', NULL, '', '', '2wFU5zuDj4i0Tppls2j1fjo6K2w2YUhLNXBvS2YyVzdIdFJlTHE3ZXRjMEV2R2RTZDhqVzk2eUtoNHl2aHRRZGRaZTVLR1JrbHJiTlVtVVE3ZXNSMkZzZ3lWdTh5aVppWUc3aGJmWUE9PQ==', '', NULL, NULL, NULL, '2026-02-10 17:30:59', '2026-02-14 10:23:24'),
(3, 3, 'bank_transfer', 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, 'Monie point', '5834952438', 'Balcony regency suite', '2026-02-13 11:26:32', '2026-02-13 11:26:32');

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
(2, 2, NULL, '{\"colors\":{\"foreground\":\"#000000\",\"background\":\"#ffffff\"},\"text_content\":\"\",\"text_color\":\"#000000\",\"text_size\":16}', '{\"pattern\":\"dots\",\"eyes\":\"rounded\",\"frame\":{\"type\":\"square\",\"text\":\"SCAN ME\",\"color\":\"#000000\",\"text_color\":\"#ffffff\",\"text_size\":14,\"bg_enabled\":true,\"bg_color\":\"#000000\"},\"colors\":{\"foreground\":\"#000000\",\"background\":\"#ffffff\"},\"logo\":{\"enabled\":true,\"size\":0.1499999999999999944488848768742172978818416595458984375,\"center_only\":true}}', '#ffffff', '#000000', '', '#000000', 16, 'Arial', 300, 20, 1, '2025-12-22 18:18:47', '2026-03-16 09:40:42'),
(27, 3, 18, NULL, '{\"pattern\":\"square\",\"eyes\":\"rounded\",\"frame\":{\"type\":\"rounded\",\"text\":\"SCAN ME\",\"color\":\"#051357\",\"text_color\":\"#ffffff\",\"text_size\":14,\"bg_enabled\":true,\"bg_color\":\"#252154\"},\"colors\":{\"foreground\":\"#f7f7f7\",\"background\":\"#2f326f\"},\"logo\":{\"enabled\":false,\"size\":0.200000000000000011102230246251565404236316680908203125,\"center_only\":true}}', '#FFFFFF', '#000000', 'Scan to view menu', '#000000', 16, 'Arial', 300, 20, 1, '2026-03-10 14:39:22', '2026-03-10 16:03:43'),
(36, 13, 19, NULL, '{\"pattern\":\"square\",\"eyes\":\"square\",\"frame\":{\"type\":\"none\",\"text\":\"SCAN ME\",\"color\":\"#000000\",\"text_color\":\"#ffffff\",\"text_size\":14,\"bg_enabled\":true,\"bg_color\":\"#000000\"},\"colors\":{\"foreground\":\"#000000\",\"background\":\"#ffffff\"},\"logo\":{\"enabled\":false,\"size\":0.200000000000000011102230246251565404236316680908203125,\"center_only\":true}}', '#FFFFFF', '#000000', 'Scan to view menu', '#000000', 16, 'Arial', 300, 20, 1, '2026-03-12 23:11:55', '2026-03-20 17:08:59'),
(44, 19, NULL, NULL, NULL, '#FFFFFF', '#000000', 'Scan to view menu', '#000000', 16, 'Arial', 300, 20, 1, '2026-04-06 17:20:24', '2026-04-06 17:20:24'),
(45, 20, NULL, NULL, NULL, '#FFFFFF', '#000000', 'Scan to view menu', '#000000', 16, 'Arial', 300, 20, 1, '2026-04-28 20:08:30', '2026-04-28 20:08:30'),
(46, 21, 19, NULL, '{\"pattern\":\"square\",\"eyes\":\"square\",\"frame\":{\"type\":\"none\",\"text\":\"SCAN ME\",\"color\":\"#000000\",\"text_color\":\"#ffffff\",\"text_size\":14,\"bg_enabled\":true,\"bg_color\":\"#000000\"},\"colors\":{\"foreground\":\"#000000\",\"background\":\"#ffffff\"},\"logo\":{\"enabled\":false,\"size\":0.200000000000000011102230246251565404236316680908203125,\"center_only\":true}}', '#FFFFFF', '#000000', 'Scan to view menu', '#000000', 16, 'Arial', 300, 20, 1, '2026-05-10 15:07:51', '2026-05-10 15:09:59');

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
(1, 2, 25000.00, '2026-02-12 04:52:44', '2026-03-12 17:26:28'),
(2, 3, 2000.00, '2026-02-13 11:34:30', '2026-03-12 14:44:23'),
(6, 13, 5000.00, '2026-03-14 18:25:58', '2026-03-14 18:25:58');

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

CREATE TABLE `sections` (
  `id` int(11) NOT NULL,
  `restaurant_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sections`
--

INSERT INTO `sections` (`id`, `restaurant_id`, `name`, `slug`, `image`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 2, 'General', 'general', NULL, 1, 1, '2026-03-13 01:58:22', '2026-03-13 01:58:22'),
(2, 3, 'General', 'general', NULL, 5, 1, '2026-03-13 01:58:22', '2026-03-13 02:20:56'),
(10, 3, 'FOOD', 'food', NULL, 1, 1, '2026-03-13 02:20:39', '2026-03-13 02:34:20'),
(11, 3, 'DRINKS', 'drinks', NULL, 2, 1, '2026-03-13 02:20:56', '2026-03-13 02:34:20'),
(12, 13, 'In-Room Dinning', 'in-room-dinning', '69b47d5582598.jpg', 2, 1, '2026-03-13 09:42:21', '2026-03-20 13:26:36'),
(13, 13, 'À La Carte Menu', 'a-la-carte-menu', NULL, 1, 1, '2026-03-13 10:13:02', '2026-03-13 10:14:12'),
(14, 13, 'Drinks', 'drinks', NULL, 3, 1, '2026-03-14 17:42:54', '2026-03-14 17:43:53'),
(15, 19, 'FOOD', 'food', NULL, 3, 1, '2026-05-06 00:53:28', '2026-05-06 01:53:00'),
(16, 19, 'DRINKS', 'drinks', NULL, 2, 1, '2026-05-06 00:53:48', '2026-05-06 01:53:00'),
(18, 4, 'Food Menu', 'food-menu', '6a03d58aa458b.webp', 1, 1, '2026-05-13 01:27:51', '2026-05-13 01:36:10'),
(19, 4, 'Drink Menu', 'drink-menu', '6a045f3fc2b51.webp', 2, 1, '2026-05-13 01:27:51', '2026-05-13 11:23:43'),
(20, 4, 'Brunch Menu', 'brunch-menu', NULL, 3, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(21, 4, 'Shisha Menu', 'shisha-menu', NULL, 4, 1, '2026-05-13 01:27:51', '2026-05-13 01:27:51'),
(22, 21, 'Food Menu', 'food-menu', NULL, 2, 1, '2026-05-13 18:58:05', '2026-05-13 18:59:00'),
(23, 21, 'Drink Menu', 'drink-menu', NULL, 1, 1, '2026-05-13 18:59:00', '2026-05-13 18:59:00'),
(26, 25, 'WING MANIA', 'wing-mania', '6a0661d54bc83.webp', 1, 1, '2026-05-14 22:48:10', '2026-05-14 23:59:17'),
(27, 25, 'MANIA BRUNCH', 'mania-brunch', '6a066233d7d61.webp', 2, 1, '2026-05-14 22:48:10', '2026-05-15 00:00:51'),
(28, 25, 'HOOKAH MANIA', 'hookah-mania', '6a0666ea82b91.webp', 3, 1, '2026-05-14 22:48:10', '2026-05-15 00:20:58');

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
(1, 'Resmenu', '69a96b49c1b02.png', '69a96b49c1e53.jpg', 'sales@resmenu.net', '+234 RES MENU', 'support@resmenu.net', '', 'partners@resmenu.net', 'info@resmenu.net', 'Laagos HQ', 'Ogombo Road\r\nCitadel view Estate along Ogumbo Road Off Abraham Adesayan', '', 'https://our-menu.online/admin/settings.php', 'https://our-menu.online/admin/settings.php', 'https://our-menu.online/admin/settings.php', '2026-02-12 23:18:09', '2026-03-05 12:31:05');

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
(1, 2, 3, 'monthly', 'active', NULL, '2026-03-08 20:39:58', '2026-05-08 20:39:58', NULL, '2025-12-24 03:13:58', '2026-03-15 13:30:08'),
(2, 3, 3, 'monthly', 'active', '2026-02-20 08:57:39', '2026-03-07 20:46:55', '2026-05-07 20:46:55', NULL, '2026-02-13 08:57:39', '2026-03-15 13:30:04'),
(3, 4, 3, 'monthly', 'trial', '2026-06-12 00:14:50', NULL, NULL, NULL, '2026-03-03 23:30:50', '2026-05-13 00:14:50'),
(12, 13, 3, 'monthly', 'trial', '2026-05-18 23:11:27', NULL, '2026-07-13 13:29:58', NULL, '2026-03-12 23:11:27', '2026-03-19 16:52:40'),
(18, 19, 2, 'annual', 'active', '2026-04-13 17:01:14', '2026-04-06 17:18:09', '2027-04-06 17:18:09', NULL, '2026-04-06 17:01:14', '2026-04-06 17:18:09'),
(19, 20, 3, 'monthly', 'trial', '2026-05-05 20:07:22', NULL, NULL, NULL, '2026-04-28 20:07:22', '2026-04-28 20:07:22'),
(20, 21, 3, 'monthly', 'trial', '2026-05-16 17:25:57', NULL, NULL, NULL, '2026-05-09 17:25:57', '2026-05-09 17:25:57'),
(24, 25, 3, 'monthly', 'trial', '2026-05-21 14:20:15', NULL, NULL, NULL, '2026-05-14 14:20:15', '2026-05-14 14:20:15'),
(25, 26, 3, 'monthly', 'trial', '2026-05-24 21:04:00', NULL, NULL, NULL, '2026-05-17 21:04:00', '2026-05-17 21:04:00');

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
-- Table structure for table `subscription_plans`
--

CREATE TABLE `subscription_plans` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL COMMENT 'Basic, Professional, Enterprise',
  `slug` varchar(50) NOT NULL COMMENT 'basic, professional, enterprise',
  `description` text DEFAULT NULL,
  `monthly_price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Price in NGN',
  `annual_price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Price in NGN (20% discount)',
  `yearly_discount_percent` decimal(5,2) NOT NULL DEFAULT 20.00 COMMENT 'Discount % applied to yearly plan (annual price = monthly*12*(1 - this/100))',
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

INSERT INTO `subscription_plans` (`id`, `name`, `slug`, `description`, `monthly_price`, `annual_price`, `yearly_discount_percent`, `max_categories`, `max_menu_items`, `max_qr_styles`, `max_templates`, `features`, `is_active`, `display_order`, `created_at`, `updated_at`) VALUES
(1, 'Basic', 'basic', 'Perfect for small restaurants just getting started with digital menus.', 8000.00, 62400.00, 35.00, 5, 50, 3, 5, '{\"priority_support\": false, \"custom_domain\": false, \"analytics_advanced\": false, \"food_ordering\": false, \"table_reservations\": false}', 1, 1, '2025-12-24 02:38:31', '2026-03-13 01:58:22'),
(2, 'Professional', 'professional', 'Ideal for growing restaurants with multiple menu categories.', 15500.00, 120900.00, 35.00, 15, 300, 7, 7, '{\"priority_support\": true, \"custom_domain\": false, \"analytics_advanced\": true, \"food_ordering\": true, \"table_reservations\": true}', 1, 2, '2025-12-24 02:38:31', '2026-03-13 01:58:22'),
(3, 'Enterprise', 'enterprise', 'Full-featured solution for large restaurants and chains.', 25700.00, 200460.00, 35.00, -1, -1, -1, -1, '{\"priority_support\": true, \"custom_domain\": true, \"analytics_advanced\": true, \"food_ordering\": true, \"table_reservations\": true}', 1, 3, '2025-12-24 02:38:31', '2026-03-13 01:58:22');

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
(6, 2, '2026-02-22', 59, '2026-02-12 22:55:24', '2026-02-12 22:55:24'),
(7, 2, '2026-03-01', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(8, 2, '2026-03-02', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(9, 2, '2026-03-03', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(10, 2, '2026-03-04', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(11, 2, '2026-03-05', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(12, 2, '2026-03-06', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(13, 2, '2026-03-07', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(14, 2, '2026-03-08', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(15, 2, '2026-03-09', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(16, 2, '2026-03-10', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(17, 2, '2026-03-11', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(18, 2, '2026-03-12', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(19, 2, '2026-03-13', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(20, 2, '2026-03-14', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(21, 2, '2026-03-15', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(22, 2, '2026-03-16', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(23, 2, '2026-03-17', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(24, 2, '2026-03-18', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(25, 2, '2026-03-19', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(26, 2, '2026-03-20', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(27, 2, '2026-03-21', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(28, 2, '2026-03-22', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(29, 2, '2026-03-23', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(30, 2, '2026-03-24', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(31, 2, '2026-03-25', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(32, 2, '2026-03-26', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(33, 2, '2026-03-27', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(34, 2, '2026-03-28', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(35, 2, '2026-03-29', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(36, 2, '2026-03-30', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(37, 2, '2026-03-31', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(38, 2, '2026-04-01', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(39, 2, '2026-04-02', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(40, 2, '2026-04-03', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(41, 2, '2026-04-04', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(42, 2, '2026-04-05', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(43, 2, '2026-04-06', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(44, 2, '2026-04-07', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(45, 2, '2026-04-08', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(46, 2, '2026-04-09', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(47, 2, '2026-04-10', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(48, 2, '2026-04-11', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(49, 2, '2026-04-12', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(50, 2, '2026-04-13', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(51, 2, '2026-04-14', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(52, 2, '2026-04-15', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(53, 2, '2026-04-16', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(54, 2, '2026-04-17', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(55, 2, '2026-04-18', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(56, 2, '2026-04-19', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(57, 2, '2026-04-20', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(58, 2, '2026-04-21', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(59, 2, '2026-04-22', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(60, 2, '2026-04-23', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(61, 2, '2026-04-24', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(62, 2, '2026-04-25', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(63, 2, '2026-04-26', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(64, 2, '2026-04-27', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(65, 2, '2026-04-28', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(66, 2, '2026-04-29', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(67, 2, '2026-04-30', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(68, 2, '2026-05-01', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(69, 2, '2026-05-02', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(70, 2, '2026-05-03', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(71, 2, '2026-05-04', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(72, 2, '2026-05-05', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(73, 2, '2026-05-06', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(74, 2, '2026-05-07', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(75, 2, '2026-05-08', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(76, 2, '2026-05-09', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(77, 2, '2026-05-10', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(78, 2, '2026-05-11', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(79, 2, '2026-05-12', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(80, 2, '2026-05-13', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(81, 2, '2026-05-14', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(82, 2, '2026-05-15', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(83, 2, '2026-05-16', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(84, 2, '2026-05-17', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(85, 2, '2026-05-18', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(86, 2, '2026-05-19', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(87, 2, '2026-05-20', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(88, 2, '2026-05-21', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(89, 2, '2026-05-22', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(90, 2, '2026-05-23', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(91, 2, '2026-05-24', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(92, 2, '2026-05-25', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(93, 2, '2026-05-26', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(94, 2, '2026-05-27', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(95, 2, '2026-05-28', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(96, 2, '2026-05-29', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(97, 2, '2026-05-30', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(98, 2, '2026-05-31', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(99, 2, '2026-06-01', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(100, 2, '2026-06-02', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(101, 2, '2026-06-03', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(102, 2, '2026-06-04', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(103, 2, '2026-06-05', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(104, 2, '2026-06-06', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(105, 2, '2026-06-07', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(106, 2, '2026-06-08', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(107, 2, '2026-06-09', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(108, 2, '2026-06-10', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(109, 2, '2026-06-11', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(110, 2, '2026-06-12', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(111, 2, '2026-06-13', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(112, 2, '2026-06-14', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(113, 2, '2026-06-15', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(114, 2, '2026-06-16', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(115, 2, '2026-06-17', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(116, 2, '2026-06-18', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(117, 2, '2026-06-19', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(118, 2, '2026-06-20', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(119, 2, '2026-06-21', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(120, 2, '2026-06-22', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(121, 2, '2026-06-23', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(122, 2, '2026-06-24', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(123, 2, '2026-06-25', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(124, 2, '2026-06-26', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(125, 2, '2026-06-27', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(126, 2, '2026-06-28', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(127, 2, '2026-06-29', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(128, 2, '2026-06-30', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(129, 2, '2026-07-01', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(130, 2, '2026-07-02', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(131, 2, '2026-07-03', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(132, 2, '2026-07-04', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(133, 2, '2026-07-05', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(134, 2, '2026-07-06', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(135, 2, '2026-07-07', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(136, 2, '2026-07-08', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(137, 2, '2026-07-09', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(138, 2, '2026-07-10', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(139, 2, '2026-07-11', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(140, 2, '2026-07-12', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(141, 2, '2026-07-13', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(142, 2, '2026-07-14', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(143, 2, '2026-07-15', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(144, 2, '2026-07-16', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(145, 2, '2026-07-17', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(146, 2, '2026-07-18', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(147, 2, '2026-07-19', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(148, 2, '2026-07-20', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(149, 2, '2026-07-21', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(150, 2, '2026-07-22', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(151, 2, '2026-07-23', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(152, 2, '2026-07-24', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(153, 2, '2026-07-25', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(154, 2, '2026-07-26', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(155, 2, '2026-07-27', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(156, 2, '2026-07-28', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(157, 2, '2026-07-29', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(158, 2, '2026-07-30', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(159, 2, '2026-07-31', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(160, 2, '2026-08-01', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(161, 2, '2026-08-02', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(162, 2, '2026-08-03', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(163, 2, '2026-08-04', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(164, 2, '2026-08-05', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(165, 2, '2026-08-06', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(166, 2, '2026-08-07', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(167, 2, '2026-08-08', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(168, 2, '2026-08-09', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(169, 2, '2026-08-10', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(170, 2, '2026-08-11', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(171, 2, '2026-08-12', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(172, 2, '2026-08-13', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(173, 2, '2026-08-14', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(174, 2, '2026-08-15', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(175, 2, '2026-08-16', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(176, 2, '2026-08-17', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(177, 2, '2026-08-18', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(178, 2, '2026-08-19', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(179, 2, '2026-08-20', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(180, 2, '2026-08-21', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(181, 2, '2026-08-22', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(182, 2, '2026-08-23', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(183, 2, '2026-08-24', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(184, 2, '2026-08-25', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(185, 2, '2026-08-26', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(186, 2, '2026-08-27', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(187, 2, '2026-08-28', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(188, 2, '2026-08-29', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(189, 2, '2026-08-30', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(190, 2, '2026-08-31', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(191, 2, '2026-09-01', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(192, 2, '2026-09-02', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(193, 2, '2026-09-03', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(194, 2, '2026-09-04', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(195, 2, '2026-09-05', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(196, 2, '2026-09-06', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(197, 2, '2026-09-07', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(198, 2, '2026-09-08', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(199, 2, '2026-09-09', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(200, 2, '2026-09-10', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(201, 2, '2026-09-11', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(202, 2, '2026-09-12', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(203, 2, '2026-09-13', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(204, 2, '2026-09-14', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(205, 2, '2026-09-15', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(206, 2, '2026-09-16', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(207, 2, '2026-09-17', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(208, 2, '2026-09-18', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(209, 2, '2026-09-19', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(210, 2, '2026-09-20', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(211, 2, '2026-09-21', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(212, 2, '2026-09-22', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(213, 2, '2026-09-23', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(214, 2, '2026-09-24', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(215, 2, '2026-09-25', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(216, 2, '2026-09-26', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(217, 2, '2026-09-27', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(218, 2, '2026-09-28', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(219, 2, '2026-09-29', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(220, 2, '2026-09-30', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(221, 2, '2026-10-01', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(222, 2, '2026-10-02', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(223, 2, '2026-10-03', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(224, 2, '2026-10-04', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(225, 2, '2026-10-05', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(226, 2, '2026-10-06', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(227, 2, '2026-10-07', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(228, 2, '2026-10-08', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(229, 2, '2026-10-09', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(230, 2, '2026-10-10', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(231, 2, '2026-10-11', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(232, 2, '2026-10-12', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(233, 2, '2026-10-13', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(234, 2, '2026-10-14', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(235, 2, '2026-10-15', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(236, 2, '2026-10-16', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(237, 2, '2026-10-17', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(238, 2, '2026-10-18', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(239, 2, '2026-10-19', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(240, 2, '2026-10-20', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(241, 2, '2026-10-21', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(242, 2, '2026-10-22', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(243, 2, '2026-10-23', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(244, 2, '2026-10-24', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(245, 2, '2026-10-25', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(246, 2, '2026-10-26', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(247, 2, '2026-10-27', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(248, 2, '2026-10-28', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(249, 2, '2026-10-29', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(250, 2, '2026-10-30', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(251, 2, '2026-10-31', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(252, 2, '2026-11-01', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(253, 2, '2026-11-02', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(254, 2, '2026-11-03', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(255, 2, '2026-11-04', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(256, 2, '2026-11-05', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(257, 2, '2026-11-06', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(258, 2, '2026-11-07', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(259, 2, '2026-11-08', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(260, 2, '2026-11-09', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(261, 2, '2026-11-10', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(262, 2, '2026-11-11', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(263, 2, '2026-11-12', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(264, 2, '2026-11-13', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(265, 2, '2026-11-14', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(266, 2, '2026-11-15', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(267, 2, '2026-11-16', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(268, 2, '2026-11-17', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(269, 2, '2026-11-18', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(270, 2, '2026-11-19', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(271, 2, '2026-11-20', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(272, 2, '2026-11-21', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(273, 2, '2026-11-22', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(274, 2, '2026-11-23', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(275, 2, '2026-11-24', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(276, 2, '2026-11-25', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(277, 2, '2026-11-26', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(278, 2, '2026-11-27', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(279, 2, '2026-11-28', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(280, 2, '2026-11-29', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(281, 2, '2026-11-30', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(282, 2, '2026-12-01', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(283, 2, '2026-12-02', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(284, 2, '2026-12-03', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(285, 2, '2026-12-04', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(286, 2, '2026-12-05', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(287, 2, '2026-12-06', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(288, 2, '2026-12-07', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(289, 2, '2026-12-08', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(290, 2, '2026-12-09', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(291, 2, '2026-12-10', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(292, 2, '2026-12-11', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(293, 2, '2026-12-12', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(294, 2, '2026-12-13', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(295, 2, '2026-12-14', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(296, 2, '2026-12-15', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(297, 2, '2026-12-16', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(298, 2, '2026-12-17', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(299, 2, '2026-12-18', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(300, 2, '2026-12-19', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(301, 2, '2026-12-20', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(302, 2, '2026-12-21', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(303, 2, '2026-12-22', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(304, 2, '2026-12-23', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(305, 2, '2026-12-24', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(306, 2, '2026-12-25', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(307, 2, '2026-12-26', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(308, 2, '2026-12-27', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(309, 2, '2026-12-28', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(310, 2, '2026-12-29', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(311, 2, '2026-12-30', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20'),
(312, 2, '2026-12-31', 10, '2026-03-12 18:32:20', '2026-03-12 18:32:20');

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
(7, '4GLULEF9', 2, '2026-02-13', '18:30:00', 1, 'Abdulrahman Shittu', 'mr.carter.tech07@gmail.com', '08032336586', NULL, 'z', 150.00, 1, 'confirmed', 0, '2026-02-12 23:32:46', '2026-02-12 23:32:54'),
(8, 'NU5R6NXR', 3, '2026-02-18', '17:30:00', 2, 'carter tech', 'billyfredrickgibbons@gmail.com', '913475935555', 'BIRTHDAY', 'Xx', 0.00, 0, 'pending', 0, '2026-02-13 11:25:27', '2026-02-13 11:25:27'),
(9, 'V6R0W5A2', 3, '2026-02-21', '19:00:00', 3, 'carter tech', 'mr.carter.tech07@gmail.com', '913475938888', NULL, NULL, 0.00, 0, 'pending', 0, '2026-02-13 11:26:55', '2026-02-13 11:26:55'),
(10, 'FE29QJVJ', 3, '2026-02-20', '19:00:00', 1, 'Abdulrahman Shittu', 'mr.carter.tech07@gmail.com', '08032336586', NULL, NULL, 0.00, 0, 'pending', 0, '2026-02-13 11:27:21', '2026-02-13 11:27:21'),
(11, '2HIMIZ0Z', 3, '2026-02-19', '19:00:00', 2, 'Carter', 'mr.carter.tech07@gmail.com', '8855566556625', NULL, NULL, 25000.00, 1, 'confirmed', 0, '2026-02-13 11:35:23', '2026-02-13 11:35:35'),
(12, '63GJP5G6', 3, '2026-02-18', '18:00:00', 2, 'Abdulrahman Shittu', 'mr.carter.tech07@gmail.com', '08032336586', NULL, 'ddd', 25000.00, 0, 'pending', 0, '2026-02-17 14:42:38', '2026-02-17 14:42:38'),
(13, '1FC2W3SY', 2, '2026-03-20', '17:30:00', 5, 'Abdulrahman Shittu', 'sigsol2024@gmail.com', '08032336586', 'BUSINESS', 'fssfssfs', 150.00, 0, 'pending', 0, '2026-02-23 11:10:42', '2026-02-23 11:10:42'),
(14, 'QYLCXOT6', 2, '2026-03-09', '19:30:00', 2, 'carter tech', 'mr.carter.tech07@gmail.com', '0946434664', 'BIRTHDAY', NULL, 150.00, 0, 'pending', 0, '2026-03-09 16:38:16', '2026-03-09 16:38:16'),
(18, 'CS4JZZO9', 2, '2026-03-13', '18:30:00', 2, 'Abdulrahman Shittu', 'mr.carter.tech07@gmail.com', '08032336586', NULL, NULL, 25000.00, 0, 'pending', 0, '2026-03-12 19:47:50', '2026-03-12 19:47:50'),
(20, 'BC43GX2E', 13, '2026-04-16', '18:00:00', 4, 'Peculiar', 'pecu15@yahoo.com', '08697029779', 'DATE_NIGHT', NULL, 5000.00, 0, 'pending', 0, '2026-04-15 04:42:36', '2026-04-15 04:42:36'),
(21, 'QMC9VC7M', 13, '2026-04-18', '18:00:00', 2, 'Ayo', 'aayinde@domeoresources.org', '08072785537', NULL, NULL, 5000.00, 0, 'pending', 0, '2026-04-17 08:03:04', '2026-04-17 08:03:04'),
(22, 'BQFTAX7H', 13, '2026-06-30', '20:00:00', 2, 'Aishatu mesh', 'aishatumesh@gmail.com', '07032536086', NULL, 'Bauchi Bauchi sister', 5000.00, 0, 'pending', 0, '2026-05-05 02:58:01', '2026-05-05 02:58:01');

-- --------------------------------------------------------

--
-- Table structure for table `templates`
--

CREATE TABLE `templates` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `preview_image` varchar(255) DEFAULT NULL,
  `listing_image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `is_private` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `templates`
--

INSERT INTO `templates` (`id`, `name`, `slug`, `description`, `preview_image`, `listing_image`, `is_active`, `is_private`, `created_at`, `updated_at`) VALUES
(1, 'Framer Design', 'template1', 'Elegant and sophisticated fine dining style with clean typography and alternating layout. Perfect for bistros, full-service restaurants, and venues that want a classic yet modern menu presentation.', '69a9cd81e4873.jpg', '69ab40d07e069.png', 1, 0, '2025-12-19 18:43:07', '2026-03-08 18:19:35'),
(2, 'Salt and Social', 'template2', 'Modern restaurant template with hero sections and featured items. Ideal for casual dining, cafes, and bars. Tailwind-based design with a fresh, approachable look.', '69a9cd5c97088.jpg', NULL, 1, 0, '2025-12-19 18:43:07', '2026-03-08 20:29:54'),
(3, 'Dark Navy Gradient', 'template3', 'Dark navy gradient background with bold typography and white cards. Great for lounges, cocktail bars, and upscale venues that want a striking, premium feel.', '69a9ce38d1547.jpg', NULL, 1, 0, '2025-12-19 18:43:07', '2026-03-08 18:19:35'),
(4, 'The Gourmet Grill', 'template4', 'Premium dark-themed design with warm accents and rustic charm. Ideal for steakhouses, grills, and traditional pubs. Features reservation integration and a distinctive atmosphere.', '69a9ce4b7a8f5.jpg', NULL, 1, 0, '2026-02-09 12:24:42', '2026-03-13 09:42:21'),
(5, 'The Prime Cut', 'the_prime_cut', 'Premium steakhouse menu design with burgundy and gold.', NULL, NULL, 1, 0, '2026-03-08 18:19:35', '2026-03-13 09:42:21'),
(6, 'The Garden Bistro', 'the_garden_bistro', 'Garden bistro style menu template.', NULL, NULL, 1, 0, '2026-03-08 18:19:35', '2026-03-13 09:42:21'),
(7, 'The Art Fusion', 'the_art_fusion', 'Art fusion restaurant menu design.', NULL, NULL, 1, 0, '2026-03-08 18:19:35', '2026-03-13 09:42:21'),
(8, 'Sweet Delight', 'sweet_delight', 'Playful dessert parlour style menu.', NULL, NULL, 1, 0, '2026-03-08 18:19:35', '2026-03-13 09:42:21'),
(9, 'Street Food Hub', 'street_food_hub', 'Street food hub menu template.', NULL, NULL, 1, 0, '2026-03-08 18:19:35', '2026-03-13 09:42:21'),
(10, 'Salt N Socials White', 'salt_n_socials_white', 'Salt N Socials white variant.', NULL, NULL, 1, 0, '2026-03-08 18:19:35', '2026-03-13 09:42:21'),
(11, 'Salt N Socials Colored', 'salt_n_socials_colored', 'Salt N Socials colored variant.', NULL, NULL, 1, 0, '2026-03-08 18:19:35', '2026-03-13 09:42:21'),
(12, 'Mediterranean Fresh', 'mediterranean_fresh', 'Mediterranean fresh menu design.', NULL, NULL, 1, 0, '2026-03-08 18:19:35', '2026-03-13 09:42:21'),
(13, 'Forged In Spirit', 'forged_in_spirit', 'Forged In Spirit design.', NULL, NULL, 1, 0, '2026-03-08 18:19:35', '2026-03-13 09:42:21'),
(14, 'Eart Kitchen', 'eart_kitchen', 'Eart Kitchen menu template.', NULL, NULL, 1, 0, '2026-03-08 18:19:35', '2026-03-13 09:42:21'),
(15, 'Bold Flavours', 'bold_flavours', 'Bold flavours menu design.', NULL, NULL, 1, 0, '2026-03-08 18:19:35', '2026-03-13 09:42:21'),
(16, 'Neo Mex Cantina', 'neo_mex_cantina', 'Neo Mex Cantina style menu.', NULL, NULL, 1, 0, '2026-03-08 18:19:35', '2026-03-13 09:42:21'),
(17, 'Nostalgia Front Page', 'nostalgia_front_page', 'Nostalgia front page design.', NULL, NULL, 1, 0, '2026-03-08 18:19:35', '2026-03-13 09:42:21'),
(18, 'Nostalgia Food Menu', 'nostalgia_food_menu', 'Nostalgia food menu design.', NULL, NULL, 1, 0, '2026-03-08 18:19:35', '2026-03-13 09:42:21');

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
(1, 4, '#121212', 24, 'Epilogue', '#f20d0d', 18, 'Epilogue', '#666666', 14, 'Epilogue', '#121212', 20, 'Epilogue', '#f8f5f5', '#121212', '#f20d0d', '#FFFFFF', '2026-02-09 12:24:42', '2026-03-13 09:42:21'),
(20, 1, '#1A1A1A', 24, 'Inter', '#1A1A1A', 18, 'Inter', '#666666', 14, 'Inter', '#1A1A1A', 20, 'Inter', '#FFFFFF', '#FFFFFF', '#1A1A1A', '#FAF3E6', '2026-02-13 09:22:33', '2026-03-13 09:42:21'),
(21, 2, '#1A1A1A', 24, 'Inter', '#ea2a33', 18, 'Inter', '#666666', 14, 'Inter', '#1A1A1A', 20, 'Inter', '#f8f6f6', '#f8f6f6', '#ea2a33', '#FFFFFF', '2026-02-13 09:22:33', '2026-03-13 09:42:21'),
(22, 3, '#1A1A1A', 24, 'Inter', '#ea2a33', 18, 'Inter', '#666666', 14, 'Inter', '#1A1A1A', 20, 'Inter', '#f8f6f6', '#f8f6f6', '#ea2a33', '#FFFFFF', '2026-02-13 09:22:33', '2026-03-13 09:42:21');

-- --------------------------------------------------------

--
-- Table structure for table `template_plans`
--

CREATE TABLE `template_plans` (
  `template_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `template_plans`
--

INSERT INTO `template_plans` (`template_id`, `plan_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(3, 1),
(3, 2),
(3, 3),
(4, 1),
(4, 2),
(4, 3),
(5, 1),
(5, 2),
(5, 3),
(6, 1),
(6, 2),
(6, 3),
(7, 1),
(7, 2),
(7, 3),
(8, 1),
(8, 2),
(8, 3),
(9, 1),
(9, 2),
(9, 3),
(10, 1),
(10, 2),
(10, 3),
(11, 1),
(11, 2),
(11, 3),
(12, 1),
(12, 2),
(12, 3),
(13, 1),
(13, 2),
(13, 3),
(14, 1),
(14, 2),
(14, 3),
(15, 1),
(15, 2),
(15, 3),
(16, 1),
(16, 2),
(16, 3),
(17, 1),
(17, 2),
(17, 3),
(18, 1),
(18, 2),
(18, 3);

-- --------------------------------------------------------

--
-- Table structure for table `template_restaurants`
--

CREATE TABLE `template_restaurants` (
  `template_id` int(11) NOT NULL,
  `restaurant_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  ADD KEY `idx_display_order` (`display_order`),
  ADD KEY `idx_section_id` (`section_id`);

--
-- Indexes for table `category_secondary_sections`
--
ALTER TABLE `category_secondary_sections`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_category_secondary` (`category_id`,`section_id`),
  ADD KEY `idx_secondary_section` (`section_id`);

--
-- Indexes for table `customization_settings`
--
ALTER TABLE `customization_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `restaurant_template` (`restaurant_id`,`template_id`);

--
-- Indexes for table `email_delivery_suppressions`
--
ALTER TABLE `email_delivery_suppressions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_email_sha256` (`email_sha256`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_login_attempts_ip_time` (`ip_address`,`attempted_at`),
  ADD KEY `idx_login_attempts_identifier_time` (`identifier`(191),`attempted_at`);

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
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_token_hash` (`token_hash`),
  ADD KEY `idx_user_active` (`user_type`,`user_id`,`used_at`,`expires_at`),
  ADD KEY `idx_identifier_created` (`identifier`,`created_at`),
  ADD KEY `idx_ip_created` (`request_ip`,`created_at`);

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
-- Indexes for table `public_api_rate_events`
--
ALTER TABLE `public_api_rate_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_action_ip_time` (`action`,`ip_address`,`created_at`);

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
-- Indexes for table `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_restaurant_section_slug` (`restaurant_id`,`slug`),
  ADD KEY `idx_sections_restaurant` (`restaurant_id`),
  ADD KEY `idx_sections_display_order` (`display_order`);

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
-- Indexes for table `subscription_change_requests`
--
ALTER TABLE `subscription_change_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_subscription_pending` (`subscription_id`,`status`),
  ADD KEY `idx_effective_pending` (`effective_at`,`status`),
  ADD KEY `idx_restaurant_pending` (`restaurant_id`,`status`),
  ADD KEY `subscription_change_requests_ibfk_3` (`from_plan_id`),
  ADD KEY `subscription_change_requests_ibfk_4` (`to_plan_id`);

--
-- Indexes for table `subscription_emails`
--
ALTER TABLE `subscription_emails`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subscription_id` (`subscription_id`),
  ADD KEY `idx_subscription_email_lookup` (`subscription_id`,`email_type`,`days_before`,`sent_at`);

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
-- Indexes for table `template_plans`
--
ALTER TABLE `template_plans`
  ADD PRIMARY KEY (`template_id`,`plan_id`),
  ADD KEY `plan_id` (`plan_id`);

--
-- Indexes for table `template_restaurants`
--
ALTER TABLE `template_restaurants`
  ADD PRIMARY KEY (`template_id`,`restaurant_id`),
  ADD KEY `restaurant_id` (`restaurant_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=213;

--
-- AUTO_INCREMENT for table `category_secondary_sections`
--
ALTER TABLE `category_secondary_sections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `customization_settings`
--
ALTER TABLE `customization_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `email_delivery_suppressions`
--
ALTER TABLE `email_delivery_suppressions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `managers`
--
ALTER TABLE `managers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1548;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `payment_settings`
--
ALTER TABLE `payment_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `pending_bank_transfers`
--
ALTER TABLE `pending_bank_transfers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `pending_online_payments`
--
ALTER TABLE `pending_online_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `public_api_rate_events`
--
ALTER TABLE `public_api_rate_events`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `qr_code_scans`
--
ALTER TABLE `qr_code_scans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `qr_templates`
--
ALTER TABLE `qr_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `restaurants`
--
ALTER TABLE `restaurants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `restaurant_payment_settings`
--
ALTER TABLE `restaurant_payment_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `restaurant_qr_codes`
--
ALTER TABLE `restaurant_qr_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `restaurant_reservation_settings`
--
ALTER TABLE `restaurant_reservation_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `site_settings`
--
ALTER TABLE `site_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `subscription_change_requests`
--
ALTER TABLE `subscription_change_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subscription_emails`
--
ALTER TABLE `subscription_emails`
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=375;

--
-- AUTO_INCREMENT for table `table_reservations`
--
ALTER TABLE `table_reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `templates`
--
ALTER TABLE `templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `template_customizations`
--
ALTER TABLE `template_customizations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `categories_section_fk` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`);

--
-- Constraints for table `category_secondary_sections`
--
ALTER TABLE `category_secondary_sections`
  ADD CONSTRAINT `fk_css_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_css_section` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE CASCADE;

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
-- Constraints for table `sections`
--
ALTER TABLE `sections`
  ADD CONSTRAINT `sections_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD CONSTRAINT `subscriptions_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `subscriptions_ibfk_2` FOREIGN KEY (`plan_id`) REFERENCES `subscription_plans` (`id`);

--
-- Constraints for table `subscription_change_requests`
--
ALTER TABLE `subscription_change_requests`
  ADD CONSTRAINT `subscription_change_requests_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `subscription_change_requests_ibfk_2` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `subscription_change_requests_ibfk_3` FOREIGN KEY (`from_plan_id`) REFERENCES `subscription_plans` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `subscription_change_requests_ibfk_4` FOREIGN KEY (`to_plan_id`) REFERENCES `subscription_plans` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subscription_emails`
--
ALTER TABLE `subscription_emails`
  ADD CONSTRAINT `subscription_emails_ibfk_1` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE CASCADE;

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

--
-- Constraints for table `template_plans`
--
ALTER TABLE `template_plans`
  ADD CONSTRAINT `template_plans_ibfk_1` FOREIGN KEY (`template_id`) REFERENCES `templates` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `template_plans_ibfk_2` FOREIGN KEY (`plan_id`) REFERENCES `subscription_plans` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `template_restaurants`
--
ALTER TABLE `template_restaurants`
  ADD CONSTRAINT `template_restaurants_ibfk_1` FOREIGN KEY (`template_id`) REFERENCES `templates` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `template_restaurants_ibfk_2` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
