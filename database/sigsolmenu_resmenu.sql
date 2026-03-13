-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Mar 12, 2026 at 11:12 PM
-- Server version: 10.6.25-MariaDB
-- PHP Version: 8.4.18

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
(15, 2, 'Appetizer', 'appetizers', '6945d9626bc44.jpg', 'Start your meal with our delicious appetizers', 1, 1, '2025-12-19 18:43:07', '2026-03-09 12:45:05'),
(16, 2, 'Side Orders', 'side-orders', '6945d97eb3a2f.webp', 'Perfect sides to complement your meal', 2, 1, '2025-12-19 18:43:07', '2025-12-19 23:02:22'),
(17, 2, 'Desserts', 'desserts', '6945d9f81c699.jpg', 'Sweet endings to your meal', 3, 1, '2025-12-19 18:43:07', '2025-12-19 23:04:24'),
(18, 2, 'Champagne', 'champagne', '6945da0b5f74f.jpg', 'Premium champagne selection', 4, 1, '2025-12-19 18:43:07', '2025-12-19 23:04:43'),
(19, 2, 'Tequila', 'tequila', '6945da1a42b4c.jpg', 'Premium tequila collection', 5, 1, '2025-12-19 18:43:07', '2025-12-19 23:04:58'),
(20, 2, 'Cognac', 'cognac', '6945da2ba75b9.jpg', 'Fine cognac selection', 6, 1, '2025-12-19 18:43:07', '2025-12-19 23:05:15'),
(21, 2, 'Whiskey', 'whiskey', '6945da3b4758d.jpg', 'Premium whiskey collection', 7, 1, '2025-12-19 18:43:07', '2025-12-19 23:05:31'),
(22, 2, 'Shisha', 'shisha', '6945da4e4f519.jpg', 'Flavored shisha selection', 8, 1, '2025-12-19 18:43:07', '2025-12-19 23:05:50'),
(23, 3, 'Soft Drinks & Non-Alcoholic', 'soft-drinks-non-alcoholic', NULL, 'Refreshing non-alcoholic beverages', 16, 1, '2026-02-13 09:52:41', '2026-02-13 13:54:16'),
(24, 3, 'Beer & Cider', 'beer-cider', NULL, 'Local and imported beers and ciders', 17, 1, '2026-02-13 09:52:41', '2026-02-13 13:54:16'),
(25, 3, 'Brandy & Cognac', 'brandy-cognac', NULL, 'Premium brandy and cognac selection', 18, 1, '2026-02-13 09:52:41', '2026-02-13 13:54:16'),
(26, 3, 'Whiskey', 'whiskey', NULL, 'Fine whiskey collection', 19, 1, '2026-02-13 09:52:41', '2026-02-13 13:54:16'),
(27, 3, 'Rum', 'rum', NULL, 'Rum selection', 20, 1, '2026-02-13 09:52:41', '2026-02-13 13:54:16'),
(28, 3, 'Vodka', 'vodka', NULL, 'Vodka selection', 21, 1, '2026-02-13 09:52:41', '2026-02-13 13:54:16'),
(29, 3, 'Gin', 'gin', NULL, 'Premium gin selection', 22, 1, '2026-02-13 09:52:41', '2026-02-13 13:54:16'),
(30, 3, 'Tequila', 'tequila', NULL, 'Tequila selection', 23, 1, '2026-02-13 09:52:41', '2026-02-13 13:54:16'),
(31, 3, 'Liqueurs', 'liqueurs', NULL, 'Sweet liqueurs and digestifs', 24, 1, '2026-02-13 09:52:41', '2026-02-13 13:54:16'),
(32, 3, 'Aperitifs & Bitters', 'aperitifs-bitters', NULL, 'Aperitifs and bitters', 25, 1, '2026-02-13 09:52:41', '2026-02-13 13:54:16'),
(33, 3, 'Champagne', 'champagne', NULL, 'Premium champagne selection', 26, 1, '2026-02-13 09:52:41', '2026-02-13 13:54:16'),
(34, 3, 'Mocktails', 'mocktails', NULL, 'Alcohol-free cocktails', 27, 1, '2026-02-13 09:52:41', '2026-02-13 13:54:16'),
(35, 3, 'Cocktails', 'cocktails', NULL, 'Classic and signature cocktails', 28, 1, '2026-02-13 09:52:41', '2026-02-13 13:54:16'),
(36, 3, 'White Wines', 'white-wines', NULL, 'White wine selection', 29, 1, '2026-02-13 09:52:41', '2026-02-13 13:54:16'),
(37, 3, 'Red Wines', 'red-wines', NULL, 'Red wine selection', 30, 1, '2026-02-13 09:52:41', '2026-02-13 13:54:16'),
(38, 3, 'Coffee', 'coffee', NULL, 'Hot coffee drinks', 31, 1, '2026-02-13 09:52:41', '2026-02-13 13:54:16'),
(39, 3, 'Smoothies', 'smoothies', NULL, 'Fresh fruit smoothies', 32, 1, '2026-02-13 09:52:41', '2026-02-13 13:54:16'),
(40, 3, 'Fresh Juices', 'fresh-juices', NULL, 'Freshly squeezed juices', 33, 1, '2026-02-13 09:52:41', '2026-02-13 13:54:16'),
(41, 3, 'Breakfast Trays (48-Hour Pre-Order)', 'breakfast-trays-48hr', NULL, 'Premium breakfast trays for pre-order', 1, 1, '2026-02-13 10:57:52', '2026-02-13 13:54:16'),
(42, 3, 'Breakfast', 'breakfast', NULL, 'Morning meals', 2, 1, '2026-02-13 10:57:52', '2026-02-13 13:54:16'),
(43, 3, 'Salads', 'salads', NULL, 'Fresh salads', 3, 1, '2026-02-13 10:57:52', '2026-02-13 13:54:16'),
(44, 3, 'Pepper Soups & Continental Soups', 'pepper-soups-continental-soups', NULL, 'Served with fresh bread rolls', 4, 1, '2026-02-13 10:57:52', '2026-02-13 16:22:45'),
(45, 3, 'Finger Foods & Small Chops', 'finger-foods-small-chops', NULL, 'Appetizers and small bites', 5, 1, '2026-02-13 10:57:52', '2026-02-13 16:22:45'),
(46, 3, 'Sandwiches & Burgers', 'sandwiches-burgers', NULL, 'Sandwiches and burgers', 6, 1, '2026-02-13 10:57:52', '2026-02-13 16:22:45'),
(47, 3, 'Chicken Entrées', 'chicken-entrees', NULL, 'Served with choice of fries, roast potatoes, sweet potato fries, or yam fries', 7, 1, '2026-02-13 10:57:52', '2026-02-13 13:54:16'),
(48, 3, 'Seafood', 'seafood', NULL, 'Fresh seafood dishes', 8, 1, '2026-02-13 10:57:52', '2026-02-13 13:54:16'),
(49, 3, 'Steaks, Ribs & Chops', 'steaks-ribs-chops', NULL, 'South African cuts — served with side of choice', 9, 1, '2026-02-13 10:57:52', '2026-02-13 16:22:45'),
(50, 3, 'Grills', 'grills', NULL, 'Grilled specialties', 10, 1, '2026-02-13 10:57:52', '2026-02-13 13:54:16'),
(51, 3, 'Platters', 'platters', NULL, 'Sharing platters', 11, 1, '2026-02-13 10:57:52', '2026-02-13 13:54:16'),
(52, 3, 'Pasta', 'pasta', NULL, 'Pasta dishes', 12, 1, '2026-02-13 10:57:52', '2026-02-13 13:54:16'),
(53, 3, 'Naija Soups', 'naija-soups', NULL, 'Served with semovita, eba, or pounded yam — protein choice included', 13, 1, '2026-02-13 10:57:52', '2026-02-13 13:54:16'),
(54, 3, 'Naija Specialties', 'naija-specialties', NULL, 'Nigerian specialties', 14, 1, '2026-02-13 10:57:52', '2026-02-13 13:54:16'),
(55, 3, 'Sides', 'sides', NULL, 'Side dishes', 15, 1, '2026-02-13 10:57:52', '2026-02-13 13:54:16'),
(57, 9, 'Nigeria Dish', 'n', NULL, 'Local Dish', 1, 1, '2026-03-11 17:05:12', '2026-03-11 17:05:12'),
(58, 10, 'Beverage', 'Quenches your thirst', NULL, 'Quenches your thirst', 2, 1, '2026-03-12 15:16:20', '2026-03-12 15:17:30'),
(59, 10, 'Food Menu', 'All out food tells a story that connect with every culture and taste', NULL, 'All out food tells a story that connect with every culture and taste', 1, 1, '2026-03-12 15:17:30', '2026-03-12 15:17:30'),
(60, 11, 'Davidskiltech Hub', 'davidskiltech-hub', '69b2d93dba374.jpg', 'hhhhhhhh', 10, 1, '2026-03-12 15:18:21', '2026-03-12 15:18:21');

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
(1, 2, 1, '#000000', 24, 'Inter', '#000000', 18, 'Inter', '#666666', 14, 'Inter', '#000000', 20, 'Inter', '#FFFFFF', '#FFFFFF', '#111111', '#FFFFFF', '2025-12-19 18:43:25', '2026-03-11 14:25:12'),
(4, 3, 1, '#121212', 24, 'Inter', '#1c1c1c', 18, 'Inter', '#666666', 14, 'Inter', '#121212', 20, 'Inter', '#121212', '#121212', '#84ab3e', '#ffffff', '2026-02-13 09:12:56', '2026-03-11 14:25:12'),
(5, 4, 1, '#000000', 24, 'Inter', '#000000', 18, 'Inter', '#666666', 14, 'Inter', '#000000', 20, 'Inter', '#FFFFFF', '#FFFFFF', '#111111', '#FFFFFF', '2026-03-03 23:30:50', '2026-03-03 23:30:50'),
(10, 9, 1, '#000000', 24, 'Inter', '#000000', 18, 'Inter', '#666666', 14, 'Inter', '#000000', 20, 'Inter', '#FFFFFF', '#FFFFFF', '#111111', '#FFFFFF', '2026-03-11 15:08:18', '2026-03-11 15:08:18'),
(11, 10, 1, '#000000', 24, 'Inter', '#000000', 18, 'Inter', '#666666', 14, 'Inter', '#000000', 20, 'Inter', '#FFFFFF', '#FFFFFF', '#111111', '#FFFFFF', '2026-03-12 15:07:10', '2026-03-12 15:07:10'),
(12, 11, 1, '#000000', 24, 'Inter', '#000000', 18, 'Inter', '#666666', 14, 'Inter', '#000000', 20, 'Inter', '#FFFFFF', '#FFFFFF', '#111111', '#FFFFFF', '2026-03-12 15:08:35', '2026-03-12 15:08:35'),
(13, 12, 1, '#000000', 24, 'Inter', '#000000', 18, 'Inter', '#666666', 14, 'Inter', '#000000', 20, 'Inter', '#FFFFFF', '#FFFFFF', '#111111', '#FFFFFF', '2026-03-12 15:10:13', '2026-03-12 15:10:13'),
(14, 13, 1, '#000000', 24, 'Inter', '#000000', 18, 'Inter', '#666666', 14, 'Inter', '#000000', 20, 'Inter', '#FFFFFF', '#FFFFFF', '#111111', '#FFFFFF', '2026-03-12 23:11:27', '2026-03-12 23:11:27');

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
(16, '102.88.113.224', 'skyhuz_manager', '2026-03-12 14:33:37');

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
(4, 'enu_manager', 'nostalgia@gmail.com', '$2y$10$JQmqOc8pGC3lUwqCwTIM4./tzGpQfGOlZ5gmcjxSeWOjXwRfZKSuq', 4, '2026-03-03 23:30:50', '2026-03-03 23:30:50'),
(9, 'estestaurantagos_manager', 'info@signature-solutions.com', '$2y$10$6fKqFrzcIKHcAfpGxpnPWuWhojmY3C7xeROz8jQ2Hh6TQPoQ505o2', 9, '2026-03-11 15:08:18', '2026-03-11 15:08:18'),
(10, 'admin', 'support@signature-solutions.com', '$2y$10$AvoPhjK2qoDsModbbGWTyecGgLBErqtb2rW.it3A0lBbu6VeUzbBm', 10, '2026-03-12 15:07:10', '2026-03-12 15:22:47'),
(11, 'avidskiltechub_manager', 'officialmfondavid@gmail.com', '$2y$10$JSYL4vR3Xa3VyqF/bXcc4uwQXLi13PFevRAvGrUAqHLjU6/9TiuHa', 11, '2026-03-12 15:08:35', '2026-03-12 15:08:35'),
(12, 'armrustastries_manager', 'abrobiz@gmail.com', '$2y$10$q0OsSQlUyu6KhWJ606QheuFFWTrQaKA/1paHHiI.dJaL/EXAcGT8u', 12, '2026-03-12 15:10:13', '2026-03-12 15:10:13'),
(13, 'heussoestaurant_manager', 'restaurant@lussohotelsabuja.com', '$2y$10$Fh0gC2vv/1u0mPAm9AAO6OAQ1xc3vrLNArRu1ZSbt7b376rcyCQby', 13, '2026-03-12 23:11:27', '2026-03-12 23:11:27');

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
(107, 3, 23, 'Juice Pack', 'juice-pack', '', 6000.00, NULL, 2, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(108, 3, 23, 'Malt Drink', 'malt-drink', '', 1500.00, NULL, 3, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(109, 3, 23, 'Energy Drink', 'energy-drink', '', 5000.00, NULL, 4, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(110, 3, 23, 'Water (Small)', 'water-small', '', 1000.00, NULL, 5, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(111, 3, 23, 'Soft Drinks (Coke, Fanta, Sprite, etc.)', 'soft-drinks', 'Coke, Fanta, Sprite and more', 1000.00, NULL, 6, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(112, 3, 23, 'Red Bull / Power Horse', 'red-bull-power-horse', '', 5000.00, NULL, 7, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(113, 3, 24, '33 Lager', '33-lager', '', 3000.00, NULL, 1, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(114, 3, 24, 'Smirnoff Ice', 'smirnoff-ice', '', 3000.00, NULL, 2, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
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
(162, 3, 27, 'Bacardi White (Shot)', 'bacardi-white-shot', 'Per shot', 3500.00, NULL, 2, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(163, 3, 27, 'Bacardi Gold (Bottle)', 'bacardi-gold-bottle', 'Bottle', 35000.00, NULL, 3, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(164, 3, 27, 'Bacardi Gold (Shot)', 'bacardi-gold-shot', 'Per shot', 2000.00, NULL, 4, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(165, 3, 27, 'Malibu (Bottle)', 'malibu-bottle', 'Bottle', 28000.00, NULL, 5, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(166, 3, 27, 'Malibu (Shot)', 'malibu-shot', 'Per shot', 4000.00, NULL, 6, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(167, 3, 28, 'Ciroc', 'ciroc', 'Bottle', 62000.00, NULL, 1, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(168, 3, 28, 'Absolut Vodka (Bottle)', 'absolut-vodka-bottle', 'Bottle', 35000.00, NULL, 2, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
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
(182, 3, 30, 'Olmeca White (Bottle)', 'olmeca-white-bottle', 'Bottle', 45000.00, NULL, 1, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(183, 3, 30, 'Olmeca White (Shot)', 'olmeca-white-shot', 'Per shot', 4000.00, NULL, 2, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(184, 3, 31, 'Baileys (Bottle)', 'baileys-bottle', 'Bottle', 30000.00, NULL, 1, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(185, 3, 31, 'Baileys (Shot)', 'baileys-shot', 'Per shot', 2000.00, NULL, 2, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(186, 3, 31, 'Kahlua (Bottle)', 'kahlua-bottle', 'Bottle', 23000.00, NULL, 3, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(187, 3, 31, 'Kahlua (Shot)', 'kahlua-shot', 'Per shot', 2000.00, NULL, 4, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(188, 3, 31, 'Cointreau', 'cointreau', 'Per shot', 2000.00, NULL, 5, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(189, 3, 31, 'Triple Sec', 'triple-sec', 'Per shot', 2000.00, NULL, 6, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(190, 3, 32, 'Campari', 'campari', 'Bottle', 20000.00, NULL, 1, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(191, 3, 32, 'Origin Bitters (Big)', 'origin-bitters-big', 'Bottle', 9000.00, NULL, 2, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(192, 3, 32, 'Origin Bitters (Mini)', 'origin-bitters-mini', '', 2500.00, NULL, 3, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(193, 3, 32, 'Palm Spirit (Aphro / Moor Rum)', 'palm-spirit', 'Bottle', 25000.00, NULL, 4, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(194, 3, 33, 'Moët Nectar Rosé', 'moet-nectar-rose', 'Bottle', 176000.00, NULL, 1, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(195, 3, 33, 'Veuve Clicquot Brut', 'veuve-clicquot-brut', 'Bottle', 170000.00, NULL, 2, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(196, 3, 33, 'Moët Imperial Brut', 'moet-imperial-brut', 'Bottle', 130000.00, NULL, 3, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(197, 3, 34, 'Virgin Colada', 'virgin-colada', '', 4500.00, NULL, 1, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(198, 3, 34, 'Virgin Margarita', 'virgin-margarita', '', 5500.00, NULL, 2, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(199, 3, 34, 'Chapman', 'chapman', '', 8000.00, NULL, 3, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(200, 3, 35, 'Long Island Iced Tea', 'long-island-iced-tea', '', 7500.00, NULL, 1, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(201, 3, 35, 'Daiquiri', 'daiquiri', '', 6500.00, NULL, 2, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
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
(214, 3, 36, 'Nederburg Sauvignon Blanc', 'nederburg-sauvignon-blanc', 'Bottle', 36000.00, NULL, 1, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(215, 3, 36, 'Nederburg Late Harvest', 'nederburg-late-harvest', 'Bottle', 36000.00, NULL, 2, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(216, 3, 36, 'Nederburg Chardonnay', 'nederburg-chardonnay', 'Bottle', 36000.00, NULL, 3, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(217, 3, 36, 'Mapu Sauvignon Blanc', 'mapu-sauvignon-blanc', 'Bottle', 19000.00, NULL, 4, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(218, 3, 36, 'Four Cousins', 'four-cousins-white', 'Bottle', 19000.00, NULL, 5, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(219, 3, 36, 'Frontera Moscato', 'frontera-moscato', 'Bottle', 16000.00, NULL, 6, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(220, 3, 36, 'Viala Moscato', 'viala-moscato', 'Bottle', 12000.00, NULL, 7, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(221, 3, 37, 'Nederburg Merlot', 'nederburg-merlot', 'Bottle', 36000.00, NULL, 1, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
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
(233, 3, 38, 'Turkish Coffee', 'turkish-coffee', '', 2000.00, NULL, 2, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(234, 3, 38, 'Double Espresso', 'double-espresso', '', 1500.00, NULL, 3, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(235, 3, 38, 'Single Espresso', 'single-espresso', '', 1000.00, NULL, 4, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(236, 3, 39, 'Fruit Medley Smoothie', 'fruit-medley-smoothie', 'Seasonal mixed fruits', 2500.00, NULL, 1, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(237, 3, 40, 'Fresh Orange Juice', 'fresh-orange-juice', '', 4000.00, NULL, 1, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(238, 3, 40, 'Fresh Pineapple Juice', 'fresh-pineapple-juice', '', 4000.00, NULL, 2, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(239, 3, 40, 'Fresh Watermelon Juice', 'fresh-watermelon-juice', '', 4000.00, NULL, 3, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(240, 3, 40, 'Sweet Zobo Drink', 'sweet-zobo-drink', '', 2000.00, NULL, 4, 1, '2026-02-13 09:52:41', '2026-02-13 09:52:41'),
(646, 3, 41, 'Premium Tray', 'premium-tray', 'Miniature wine bottle, juice pack, lemonade bottle, biscuits, wafers, coconut flakes, yoghurt cups, almonds, mug with assorted hot beverages, fresh bread rolls with butter, jam & cheese, club sandwich, cakes & croissants, plantain skewers, grapes & kiwi, English breakfast with lamb sausage, French toast, pancakes', 60000.00, NULL, 1, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(647, 3, 41, 'Deluxe Tray', 'deluxe-tray', 'Mug with assorted hot beverages, fresh bread rolls with butter, jam & cheese, club sandwich, biscuit pack, juice pack, yoghurt cups, grapes, apples, English breakfast with lamb sausage', 60000.00, NULL, 2, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(648, 3, 42, 'Breakfast Burger', 'breakfast-burger', 'With tea or coffee', 10000.00, NULL, 1, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(649, 3, 42, 'Hungry Jack Breakfast', 'hungry-jack-breakfast', 'Bacon, sausages, egg, milk mix', 7500.00, NULL, 2, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(650, 3, 42, 'Classic English Breakfast', 'classic-english-breakfast', 'Sausages, bread, eggs, baked beans, butter, toast', 10000.00, NULL, 3, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(651, 3, 42, 'African Breakfast', 'african-breakfast', 'Boiled or fried yam or plantain, egg sauce', 10000.00, NULL, 4, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(652, 3, 42, 'Naija Special', 'naija-special', 'Indomie noodles, egg, vegetables', 8000.00, NULL, 5, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(653, 3, 43, 'Chef\'s Salad', 'chefs-salad', 'Chicken breast, lettuce, cheese, croutons, bacon, tomatoes', 12000.00, NULL, 1, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(654, 3, 43, 'Chicken Caesar Salad', 'chicken-caesar-salad', 'Lettuce, chicken breast, cucumber, olives, tomatoes, egg', 15000.00, NULL, 2, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(655, 3, 43, 'Russian Salad', 'russian-salad', 'Chicken breast, carrot, Irish potatoes, sauce', 15000.00, NULL, 3, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(656, 3, 44, 'Fresh Croaker Fish (Whole)', 'fresh-croaker-fish-whole', 'Served with fresh bread rolls', 30000.00, NULL, 1, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(657, 3, 44, 'Catfish (Whole)', 'catfish-whole', 'Served with fresh bread rolls', 30000.00, NULL, 2, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
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
(668, 3, 45, 'Spicy Snails', 'spicy-snails', '', 15000.00, NULL, 2, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
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
(685, 3, 46, 'GM\'s Special Chicken Sandwich', 'gms-special-chicken-sandwich', '', 12500.00, NULL, 1, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(686, 3, 46, 'Classic Burger', 'classic-burger', '', 10000.00, NULL, 2, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(687, 3, 46, 'D\'View Club Sandwich', 'dview-club-sandwich', '', 10000.00, NULL, 3, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(688, 3, 46, 'Classic Ham & Cheese', 'classic-ham-cheese', '', 7500.00, NULL, 4, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(689, 3, 46, 'Chunky Tuna Sandwich', 'chunky-tuna-sandwich', '', 6500.00, NULL, 5, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(690, 3, 47, 'Southern Fried Chicken on Mash', 'southern-fried-chicken-on-mash', 'Served with choice of fries, roast potatoes, sweet potato fries, or yam fries', 16000.00, NULL, 1, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(691, 3, 47, 'Chicken Escalope', 'chicken-escalope', 'Served with choice of fries, roast potatoes, sweet potato fries, or yam fries', 15000.00, NULL, 2, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(692, 3, 47, 'D\'View Curry Chicken', 'dview-curry-chicken', 'Served with choice of fries, roast potatoes, sweet potato fries, or yam fries', 15000.00, NULL, 3, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(693, 3, 47, 'Chicken in Cream Sauce', 'chicken-in-cream-sauce', 'Served with choice of fries, roast potatoes, sweet potato fries, or yam fries', 15000.00, NULL, 4, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(694, 3, 47, 'Creamy Mustard Chicken', 'creamy-mustard-chicken', 'Served with choice of fries, roast potatoes, sweet potato fries, or yam fries', 15000.00, NULL, 5, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(695, 3, 47, 'Creamy Spinach Chicken Roll', 'creamy-spinach-chicken-roll', 'Served with choice of fries, roast potatoes, sweet potato fries, or yam fries', 9000.00, NULL, 6, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(696, 3, 47, 'Pepper Chicken', 'pepper-chicken-entree', 'Served with choice of fries, roast potatoes, sweet potato fries, or yam fries', 15000.00, NULL, 7, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(697, 3, 47, 'Oven Roast Chicken', 'oven-roast-chicken', 'Served with choice of fries, roast potatoes, sweet potato fries, or yam fries', 15500.00, NULL, 8, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(698, 3, 48, 'Grilled Salmon', 'grilled-salmon', '', 25000.00, NULL, 1, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(699, 3, 48, 'Grilled Croaker Fish', 'grilled-croaker-fish', '', 30000.00, NULL, 2, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(700, 3, 48, 'Grilled Catfish', 'grilled-catfish', '', 30000.00, NULL, 3, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(701, 3, 48, 'Grilled Jumbo Prawns', 'grilled-jumbo-prawns', '', 17000.00, NULL, 4, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(702, 3, 48, 'Butterfly Prawns', 'butterfly-prawns', '', 13000.00, NULL, 5, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(703, 3, 48, 'Lobster Thermidor', 'lobster-thermidor', '', 28500.00, NULL, 6, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(704, 3, 48, 'Golden Tilapia', 'golden-tilapia', '', 15000.00, NULL, 7, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(705, 3, 49, 'T-Bone', 't-bone', 'South African cuts — served with side of choice', 28000.00, NULL, 1, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(706, 3, 49, 'Rib-Eye', 'rib-eye', 'South African cuts — served with side of choice', 22000.00, NULL, 2, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(707, 3, 49, 'Lamb Chops', 'lamb-chops', 'South African cuts — served with side of choice', 30000.00, NULL, 3, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(708, 3, 49, 'Beef Ribs', 'beef-ribs', 'South African cuts — served with side of choice', 22000.00, NULL, 4, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(709, 3, 49, 'Oxtail', 'oxtail', 'South African cuts — served with side of choice', 6000.00, NULL, 5, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(710, 3, 50, 'Mixed Grill Special', 'mixed-grill-special', '', 13300.00, NULL, 1, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(711, 3, 50, 'Egyptian Mixed Grill', 'egyptian-mixed-grill', '', 13500.00, NULL, 2, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(712, 3, 51, 'MD\'s Prime Platter', 'mds-prime-platter', '', 56000.00, NULL, 1, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(713, 3, 51, 'Pacific Platter', 'pacific-platter', '', 38000.00, NULL, 2, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(714, 3, 51, 'D\'View Special Platter', 'dview-special-platter', '', 25000.00, NULL, 3, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(715, 3, 51, 'Ogazi Platter', 'ogazi-platter', '', 25000.00, NULL, 4, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(716, 3, 52, 'Spaghetti Prawn Marinara', 'spaghetti-prawn-marinara', '', 15000.00, NULL, 1, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(717, 3, 52, 'Creamy Prawn Tagliatelle', 'creamy-prawn-tagliatelle', '', 13000.00, NULL, 2, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(718, 3, 52, 'Seafood Pasta', 'seafood-pasta', '', 15000.00, NULL, 3, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(719, 3, 52, 'Spaghetti & Meatballs', 'spaghetti-meatballs', '', 8000.00, NULL, 4, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(720, 3, 52, 'Fettuccine Alfredo', 'fettuccine-alfredo', '', 16000.00, NULL, 5, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(721, 3, 52, 'Chicken Pesto Penne', 'chicken-pesto-penne', '', 13000.00, NULL, 6, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(722, 3, 52, 'Spaghetti Bolognese', 'spaghetti-bolognese', '', 15000.00, NULL, 7, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(723, 3, 52, 'Spaghetti Aglio Olio', 'spaghetti-aglio-olio', '', 6000.00, NULL, 8, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(724, 3, 52, 'Fettuccine Prawn Grill', 'fettuccine-prawn-grill', '', 7000.00, NULL, 9, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(725, 3, 53, 'Okro (Seafood)', 'okro-seafood', 'Served with semovita, eba, or pounded yam', 30000.00, NULL, 1, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(726, 3, 53, 'Eforiro (Seafood)', 'eforiro-seafood', 'Served with semovita, eba, or pounded yam', 30000.00, NULL, 2, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(727, 3, 53, 'Edikaikong (Seafood)', 'edikaikong-seafood', 'Served with semovita, eba, or pounded yam', 30000.00, NULL, 3, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(728, 3, 53, 'Egusi (Seafood)', 'egusi-seafood', 'Served with semovita, eba, or pounded yam', 30000.00, NULL, 4, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(729, 3, 53, 'Fisherman Soup (Croaker / Catfish)', 'fisherman-soup', 'Served with semovita, eba, or pounded yam', 30000.00, NULL, 5, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(730, 3, 53, 'Edikaikong (Regular)', 'edikaikong-regular', 'Served with semovita, eba, or pounded yam', 18000.00, NULL, 6, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(731, 3, 53, 'Eforiro (Regular)', 'eforiro-regular', 'Served with semovita, eba, or pounded yam', 18000.00, NULL, 7, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(732, 3, 53, 'Afang', 'afang', 'Served with semovita, eba, or pounded yam', 18000.00, NULL, 8, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(733, 3, 53, 'Ogbono', 'ogbono', 'Served with semovita, eba, or pounded yam', 18000.00, NULL, 9, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(734, 3, 54, 'Seafood Jollof Rice', 'seafood-jollof-rice', '', 25000.00, NULL, 1, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(735, 3, 54, 'D\'View Special Fried Rice', 'dview-special-fried-rice', '', 16000.00, NULL, 2, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(736, 3, 54, 'Jollof Rice Fiesta', 'jollof-rice-fiesta', '', 16000.00, NULL, 3, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(737, 3, 54, 'Isi Ewu', 'isi-ewu', '', 20000.00, NULL, 4, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(738, 3, 54, 'Yam Pottage', 'yam-pottage', '', 15000.00, NULL, 5, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(739, 3, 55, 'Jollof Rice', 'jollof-rice-side', '', 7000.00, NULL, 1, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(740, 3, 55, 'Fried Rice', 'fried-rice', '', 5000.00, NULL, 2, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(741, 3, 55, 'Fried Plantain', 'fried-plantain', '', 7000.00, NULL, 3, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(742, 3, 55, 'Yam Chips', 'yam-chips', '', 7000.00, NULL, 4, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(743, 3, 55, 'French Fries', 'french-fries', '', 5000.00, NULL, 5, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(744, 3, 55, 'Sweet Potato Fries', 'sweet-potato-fries', '', 5000.00, NULL, 6, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(745, 3, 55, 'Steamed Rice', 'steamed-rice', '', 5000.00, NULL, 7, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(746, 3, 55, 'Bread Rolls (2 pcs)', 'bread-rolls-2pcs', '', 1000.00, NULL, 8, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(747, 3, 55, 'Eggs (2)', 'eggs-2', '', 5000.00, NULL, 9, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(748, 3, 55, 'Ogbono Extra', 'ogbono-extra', '', 7000.00, NULL, 10, 1, '2026-02-13 10:57:53', '2026-02-13 10:57:53'),
(749, 9, 57, 'Amala with Eforiro', 'a', '', 10000.00, NULL, 0, 1, '2026-03-11 17:06:32', '2026-03-11 17:06:32'),
(750, 10, 58, 'Sprite', 'Sprite', 'Sprite', 3000.00, NULL, 0, 1, '2026-03-12 15:18:02', '2026-03-12 15:18:02'),
(751, 10, 59, 'Seafood Okro', 'Seafood Okro', 'Seafood Okro', 30000.00, NULL, 0, 1, '2026-03-12 15:18:34', '2026-03-12 15:18:34');

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
(7, 9, 8, 120000.00, 'NGN', 'paystack', 'PS_1773241756_51bfec42661670c3', NULL, 'pending', NULL, '2026-03-11 15:09:16'),
(8, 11, 10, 62400.00, 'NGN', 'paystack', 'PS_1773328449_b37967c753655342', NULL, 'pending', NULL, '2026-03-12 15:14:09');

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
(8, 9, '102.89.68.55', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 'Mobile', 'Chrome', 'Linux', 'Nigeria', 'Lagos', 6.44740000, 3.39030000, '2026-03-11 17:08:38'),
(9, 9, '102.89.68.55', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 'Mobile', 'Chrome', 'Linux', 'Nigeria', 'Lagos', 6.44740000, 3.39030000, '2026-03-11 17:09:44');

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
(17, 'SG QR WITH LOGO', 'gg', '17.svg', 1, '{\"pattern\":\"dots\",\"eyes\":\"rounded\",\"frame\":{\"type\":\"square\",\"text\":\"SCAN MEN\",\"color\":\"#000000\",\"text_color\":\"#ffffff\",\"text_size\":14,\"bg_enabled\":true,\"bg_color\":\"#000000\"},\"colors\":{\"foreground\":\"#000000\",\"background\":\"#ffffff\"},\"logo\":{\"enabled\":true,\"size\":0.1499999999999999944488848768742172978818416595458984375,\"center_only\":true}}', 1, '2026-03-10 14:15:20', '2026-03-10 15:21:48'),
(18, 'SG QR NO LOGO', '', '18.svg', 1, '{\"pattern\":\"square\",\"eyes\":\"rounded\",\"frame\":{\"type\":\"rounded\",\"text\":\"SCAN ME\",\"color\":\"#051357\",\"text_color\":\"#ffffff\",\"text_size\":14,\"bg_enabled\":true,\"bg_color\":\"#252154\"},\"colors\":{\"foreground\":\"#f7f7f7\",\"background\":\"#2f326f\"},\"logo\":{\"enabled\":false,\"size\":0.200000000000000011102230246251565404236316680908203125,\"center_only\":true}}', 1, '2026-03-10 14:44:24', '2026-03-10 15:20:59');

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
(2, 'LAVA', 'lava', '69459eb555362.jpg', '69459edb896e3.png', 'Premium dining experience with exquisite cuisine and fine beverages', '+234 800 000 0000', 'info@lava.com', 'LAVA., 5 Adetokunbu Ademola Street, Victoria Island', NULL, '', '', 'https://web.facebook.com/theviewhotellekki', '', 1, 1, NULL, NULL, '[\r\n  {\"label\": \"Menu\", \"url\": \"#menu\"},\r\n  {\"label\": \"Drinks\", \"url\": \"#drinks\"}\r\n]', 'af', 'jamesamaila07@gmail.com', 4.5, 'Google', 18, 1, 65, 0, 1, '2025-12-19 18:43:07', '2026-03-12 23:05:13'),
(3, 'Theview Hotel Lekki', 'theview-hotel', '698ee78360beb.jpg', '698ee783613af.jpg', 'Our restaurant offers the best platters like our very popular Ogazi Platter (Guinea fowl platter), D View Special Platter and Pacific Platter.', '+23490 9091 3608', 'reservations@theviewlekki.com', '1, Godwin Omene Street, Chief Collins Uchidiuno, Off Fola Osibo, Lekki Phase 1, Lagos, Nigeria', NULL, 'https://wa.link/g1n8bq', 'https://www.instagram.com/theviewlekki/', 'https://web.facebook.com/theviewhotellekki', '', 1, 1, NULL, NULL, NULL, 'Our restaurant offers the best platters like our very popular Ogazi Platter (Guinea fowl platter), D View Special Platter and Pacific Platter.', 'reservations@theviewlekki.com', 4.5, 'Google', 1, 1, 238, 0, 2, '2026-02-13 08:57:39', '2026-03-11 13:32:19'),
(4, 'NOSTALGIA Menu', 'nostalgia-menu', '69a76f2ad31b1.png', NULL, '', '08032336586', 'nostalgia@gmail.com', 'Suite B9 Ajah Shopping Mall', NULL, 'https://wa.me/2348134807718?text=Greetings%20TM%20Luxury%20Apartment', 'https://www.instagram.com/theviewlekki/', 'https://web.facebook.com/theviewhotellekki', '', 1, 1, NULL, NULL, NULL, NULL, 'nostalgia@gmail.com', 4.5, 'Google', 1, 1, 0, 0, 3, '2026-03-03 23:30:50', '2026-03-03 23:30:50'),
(9, 'Test Restaurant Lagos', 'test-restaurant-lagos', NULL, NULL, 'Special Dining Place', '80300000', 'info@signature-solutions.com', '292b Ajose Adeogun', NULL, '', '', '', '', 1, 1, NULL, NULL, NULL, NULL, 'info@signature-solutions.com', 4.5, 'Google', 1, 1, 1, 0, 8, '2026-03-11 15:08:18', '2026-03-11 17:06:32'),
(10, 'Johnson Samuel', 'johnson-samuel', NULL, NULL, 'Uncle jay', '08123982303', 'support@signature-solutions.com', 'ologunfe, Lagos', NULL, '', '', '', '', 1, 1, NULL, NULL, NULL, NULL, 'support@signature-solutions.com', 4.5, 'Google', 6, 1, 2, 0, 9, '2026-03-12 15:07:10', '2026-03-12 15:21:00'),
(11, 'Davidskiltech Hub', 'davidskiltech-hub', NULL, NULL, 'Deals with tech Service and hospitality', '09027037972', 'officialmfondavid@gmail.com', 'Block 315 jakande Housing Estate lagos state, victoria island', NULL, '', '', '', '', 1, 1, NULL, NULL, NULL, NULL, 'officialmfondavid@gmail.com', 4.5, 'Google', 1, 1, 0, 0, 10, '2026-03-12 15:08:35', '2026-03-12 15:40:38'),
(12, 'WarmCrust Pastries', 'warmcrust-pastries', NULL, NULL, 'WarmCrust Pastries serves rich, freshly baked pastries made with quality ingredients and crafted for flavor, warmth, and satisfaction. From flaky meatpies to golden doughnuts and perfectly wrapped sausage rolls, every item is prepared to deliver a comforting, premium taste experience. Whether you\'re grabbing a quick bite on the go or ordering for a group, WarmCrust Pastries brings you pastries that are always fresh, always warm, and always delicious.', '07038809086', 'abrobiz@gmail.com', '27 Aliu Street\r\nOff Debo', NULL, 'https://wa.me/2347038809086?text=Hello%20WarmCrust%20Pastries%2C%20I%20would%20like%20to%20place%20an%20order', '', '', '', 1, 1, NULL, NULL, NULL, NULL, 'abrobiz@gmail.com', 4.5, 'Google', 1, 1, 0, 0, 11, '2026-03-12 15:10:12', '2026-03-12 15:10:13'),
(13, 'The Lusso Restaurant', 'the-lusso-restaurant', NULL, NULL, 'The lusso hotel abuja', '', 'restaurant@lussohotelsabuja.com', '33 Usuma St, Maitama, Abuja 904101, Federal Capital Territory', NULL, '', '', '', '', 1, 1, NULL, NULL, NULL, NULL, 'restaurant@lussohotelsabuja.com', 4.5, 'Google', 1, 1, 0, 0, 12, '2026-03-12 23:11:27', '2026-03-12 23:11:27');

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
(3, 3, 'bank_transfer', 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, 'Monie point', '5834952438', 'Balcony regency suite', '2026-02-13 11:26:32', '2026-02-13 11:26:32'),
(4, 10, 'bank_transfer', 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, 'JGBank', '9876543210', 'Jay Restaurant', '2026-03-12 15:22:15', '2026-03-12 15:22:15');

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
(2, 2, 17, '{\"colors\":{\"foreground\":\"#000000\",\"background\":\"#ffffff\"},\"text_content\":\"\",\"text_color\":\"#000000\",\"text_size\":16}', '{\"pattern\":\"dots\",\"eyes\":\"rounded\",\"frame\":{\"type\":\"square\",\"text\":\"SCAN MEN\",\"color\":\"#000000\",\"text_color\":\"#ffffff\",\"text_size\":14,\"bg_enabled\":true,\"bg_color\":\"#000000\"},\"colors\":{\"foreground\":\"#000000\",\"background\":\"#ffffff\"},\"logo\":{\"enabled\":true,\"size\":0.1499999999999999944488848768742172978818416595458984375,\"center_only\":true}}', '#ffffff', '#000000', '', '#000000', 16, 'Arial', 300, 20, 1, '2025-12-22 18:18:47', '2026-03-10 21:11:30'),
(27, 3, 18, NULL, '{\"pattern\":\"square\",\"eyes\":\"rounded\",\"frame\":{\"type\":\"rounded\",\"text\":\"SCAN ME\",\"color\":\"#051357\",\"text_color\":\"#ffffff\",\"text_size\":14,\"bg_enabled\":true,\"bg_color\":\"#252154\"},\"colors\":{\"foreground\":\"#f7f7f7\",\"background\":\"#2f326f\"},\"logo\":{\"enabled\":false,\"size\":0.200000000000000011102230246251565404236316680908203125,\"center_only\":true}}', '#FFFFFF', '#000000', 'Scan to view menu', '#000000', 16, 'Arial', 300, 20, 1, '2026-03-10 14:39:22', '2026-03-10 16:03:43'),
(31, 9, 17, NULL, '{\"pattern\":\"dots\",\"eyes\":\"rounded\",\"frame\":{\"type\":\"square\",\"text\":\"SCAN MEN\",\"color\":\"#000000\",\"text_color\":\"#ffffff\",\"text_size\":14,\"bg_enabled\":true,\"bg_color\":\"#000000\"},\"colors\":{\"foreground\":\"#000000\",\"background\":\"#ffffff\"},\"logo\":{\"enabled\":true,\"size\":0.1499999999999999944488848768742172978818416595458984375,\"center_only\":true}}', '#FFFFFF', '#000000', 'Scan to view menu', '#000000', 16, 'Arial', 300, 20, 1, '2026-03-11 17:07:45', '2026-03-11 17:10:41'),
(34, 11, NULL, NULL, NULL, '#FFFFFF', '#000000', 'Scan to view menu', '#000000', 16, 'Arial', 300, 20, 1, '2026-03-12 15:17:28', '2026-03-12 15:17:28'),
(35, 12, NULL, NULL, NULL, '#FFFFFF', '#000000', 'Scan to view menu', '#000000', 16, 'Arial', 300, 20, 1, '2026-03-12 15:21:13', '2026-03-12 15:21:13'),
(36, 13, NULL, NULL, NULL, '#FFFFFF', '#000000', 'Scan to view menu', '#000000', 16, 'Arial', 300, 20, 1, '2026-03-12 23:11:55', '2026-03-12 23:11:55');

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
(4, 11, 1000.00, '2026-03-12 15:11:06', '2026-03-12 15:19:11'),
(5, 10, 20000.00, '2026-03-12 15:11:37', '2026-03-12 15:12:08');

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
(1, 2, 3, 'monthly', 'active', NULL, '2026-03-08 20:39:58', '2026-04-08 20:39:58', NULL, '2025-12-24 03:13:58', '2026-03-08 20:39:58'),
(2, 3, 3, 'monthly', 'active', '2026-02-20 08:57:39', '2026-03-07 20:46:55', '2026-04-07 20:46:55', NULL, '2026-02-13 08:57:39', '2026-03-12 14:35:08'),
(3, 4, 1, 'monthly', 'trial', '2026-03-10 23:30:50', NULL, NULL, NULL, '2026-03-03 23:30:50', '2026-03-03 23:30:50'),
(8, 9, 1, 'monthly', 'trial', '2026-03-18 15:08:18', NULL, NULL, NULL, '2026-03-11 15:08:18', '2026-03-11 17:10:32'),
(9, 10, 3, 'monthly', 'trial', '2026-03-19 15:07:10', NULL, NULL, NULL, '2026-03-12 15:07:10', '2026-03-12 15:07:10'),
(10, 11, 3, 'monthly', 'trial', '2026-03-19 15:08:35', NULL, NULL, NULL, '2026-03-12 15:08:35', '2026-03-12 15:08:35'),
(11, 12, 3, 'monthly', 'trial', '2026-03-19 15:10:13', NULL, NULL, NULL, '2026-03-12 15:10:13', '2026-03-12 15:10:13'),
(12, 13, 3, 'monthly', 'trial', '2026-03-19 23:11:27', NULL, NULL, NULL, '2026-03-12 23:11:27', '2026-03-12 23:11:27');

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
(1, 'Basic', 'basic', 'Perfect for small restaurants just getting started with digital menus.', 8000.00, 62400.00, 35.00, 5, 50, 3, 5, '{\"priority_support\":false,\"custom_domain\":false,\"analytics_advanced\":false,\"food_ordering\":false,\"table_reservations\":false}', 1, 1, '2025-12-24 02:38:31', '2026-03-11 17:16:12'),
(2, 'Professional', 'professional', 'Ideal for growing restaurants with multiple menu categories.', 15500.00, 120900.00, 35.00, 15, 300, 7, 7, '{\"priority_support\":true,\"custom_domain\":false,\"analytics_advanced\":true,\"food_ordering\":true,\"table_reservations\":true}', 1, 2, '2025-12-24 02:38:31', '2026-03-11 17:16:49'),
(3, 'Enterprise', 'enterprise', 'Full-featured solution for large restaurants and chains.', 25700.00, 200460.00, 35.00, -1, -1, -1, -1, '{\"priority_support\":true,\"custom_domain\":true,\"analytics_advanced\":true,\"food_ordering\":true,\"table_reservations\":true}', 1, 3, '2025-12-24 02:38:31', '2026-03-11 17:17:23');

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
(15, 'J6PI6UV2', 9, '2026-03-11', '18:00:00', 1, 'Test Sceneria', 'info@me.com', '0000000000', 'DATE_NIGHT', NULL, 0.00, 0, 'pending', 0, '2026-03-11 15:14:34', '2026-03-11 15:14:34'),
(16, 'OH548VNL', 11, '2026-03-12', '18:00:00', 1, 'Mfon Dige David', 'officialmfondavid@gmail.com', '09027037972', 'BIRTHDAY', NULL, 1000.00, 0, 'pending', 0, '2026-03-12 15:33:17', '2026-03-12 15:33:17'),
(17, 'Z6WK4VTJ', 10, '2026-03-12', '17:00:00', 2, 'Johnson Samuel', 'unclejaylive@gmail.com', '08123982303', 'BUSINESS', NULL, 20000.00, 0, 'pending', 0, '2026-03-12 15:38:58', '2026-03-12 15:38:58'),
(18, 'CS4JZZO9', 2, '2026-03-13', '18:30:00', 2, 'Abdulrahman Shittu', 'mr.carter.tech07@gmail.com', '08032336586', NULL, NULL, 25000.00, 0, 'pending', 0, '2026-03-12 19:47:50', '2026-03-12 19:47:50');

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
(4, 'The Gourmet Grill', 'template4', 'Premium dark-themed design with warm accents and rustic charm. Ideal for steakhouses, grills, and traditional pubs. Features reservation integration and a distinctive atmosphere.', '69a9ce4b7a8f5.jpg', NULL, 1, 0, '2026-02-09 12:24:42', '2026-03-11 14:25:12'),
(5, 'The Prime Cut', 'the_prime_cut', 'Premium steakhouse menu design with burgundy and gold.', NULL, NULL, 1, 0, '2026-03-08 18:19:35', '2026-03-11 14:25:12'),
(6, 'The Garden Bistro', 'the_garden_bistro', 'Garden bistro style menu template.', NULL, NULL, 1, 0, '2026-03-08 18:19:35', '2026-03-11 14:25:12'),
(7, 'The Art Fusion', 'the_art_fusion', 'Art fusion restaurant menu design.', NULL, NULL, 1, 0, '2026-03-08 18:19:35', '2026-03-11 14:25:12'),
(8, 'Sweet Delight', 'sweet_delight', 'Playful dessert parlour style menu.', NULL, NULL, 1, 0, '2026-03-08 18:19:35', '2026-03-11 14:25:12'),
(9, 'Street Food Hub', 'street_food_hub', 'Street food hub menu template.', NULL, NULL, 1, 0, '2026-03-08 18:19:35', '2026-03-11 14:25:12'),
(10, 'Salt N Socials White', 'salt_n_socials_white', 'Salt N Socials white variant.', NULL, NULL, 1, 0, '2026-03-08 18:19:35', '2026-03-11 14:25:12'),
(11, 'Salt N Socials Colored', 'salt_n_socials_colored', 'Salt N Socials colored variant.', NULL, NULL, 1, 0, '2026-03-08 18:19:35', '2026-03-11 14:25:12'),
(12, 'Mediterranean Fresh', 'mediterranean_fresh', 'Mediterranean fresh menu design.', NULL, NULL, 1, 0, '2026-03-08 18:19:35', '2026-03-11 14:25:12'),
(13, 'Forged In Spirit', 'forged_in_spirit', 'Forged In Spirit design.', NULL, NULL, 1, 0, '2026-03-08 18:19:35', '2026-03-11 14:25:12'),
(14, 'Eart Kitchen', 'eart_kitchen', 'Eart Kitchen menu template.', NULL, NULL, 1, 0, '2026-03-08 18:19:35', '2026-03-11 14:25:12'),
(15, 'Bold Flavours', 'bold_flavours', 'Bold flavours menu design.', NULL, NULL, 1, 0, '2026-03-08 18:19:35', '2026-03-11 14:25:12'),
(16, 'Neo Mex Cantina', 'neo_mex_cantina', 'Neo Mex Cantina style menu.', NULL, NULL, 1, 0, '2026-03-08 18:19:35', '2026-03-11 14:25:12'),
(17, 'Nostalgia Front Page', 'nostalgia_front_page', 'Nostalgia front page design.', NULL, NULL, 1, 0, '2026-03-08 18:19:35', '2026-03-11 14:25:12'),
(18, 'Nostalgia Food Menu', 'nostalgia_food_menu', 'Nostalgia food menu design.', NULL, NULL, 1, 0, '2026-03-08 18:19:35', '2026-03-11 14:25:12');

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
(1, 4, '#121212', 24, 'Epilogue', '#f20d0d', 18, 'Epilogue', '#666666', 14, 'Epilogue', '#121212', 20, 'Epilogue', '#f8f5f5', '#121212', '#f20d0d', '#FFFFFF', '2026-02-09 12:24:42', '2026-03-11 14:25:12'),
(20, 1, '#1A1A1A', 24, 'Inter', '#1A1A1A', 18, 'Inter', '#666666', 14, 'Inter', '#1A1A1A', 20, 'Inter', '#FFFFFF', '#FFFFFF', '#1A1A1A', '#FAF3E6', '2026-02-13 09:22:33', '2026-03-11 14:25:12'),
(21, 2, '#1A1A1A', 24, 'Inter', '#ea2a33', 18, 'Inter', '#666666', 14, 'Inter', '#1A1A1A', 20, 'Inter', '#f8f6f6', '#f8f6f6', '#ea2a33', '#FFFFFF', '2026-02-13 09:22:33', '2026-03-11 14:25:12'),
(22, 3, '#1A1A1A', 24, 'Inter', '#ea2a33', 18, 'Inter', '#666666', 14, 'Inter', '#1A1A1A', 20, 'Inter', '#f8f6f6', '#f8f6f6', '#ea2a33', '#FFFFFF', '2026-02-13 09:22:33', '2026-03-11 14:25:12');

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
  ADD KEY `idx_display_order` (`display_order`);

--
-- Indexes for table `customization_settings`
--
ALTER TABLE `customization_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `restaurant_template` (`restaurant_id`,`template_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `customization_settings`
--
ALTER TABLE `customization_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `managers`
--
ALTER TABLE `managers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=753;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
-- AUTO_INCREMENT for table `qr_code_scans`
--
ALTER TABLE `qr_code_scans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `qr_templates`
--
ALTER TABLE `qr_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `restaurants`
--
ALTER TABLE `restaurants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `restaurant_payment_settings`
--
ALTER TABLE `restaurant_payment_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `restaurant_qr_codes`
--
ALTER TABLE `restaurant_qr_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `restaurant_reservation_settings`
--
ALTER TABLE `restaurant_reservation_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `site_settings`
--
ALTER TABLE `site_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=313;

--
-- AUTO_INCREMENT for table `table_reservations`
--
ALTER TABLE `table_reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `templates`
--
ALTER TABLE `templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `template_customizations`
--
ALTER TABLE `template_customizations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

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
