-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: May 31, 2025 at 04:36 AM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `e-php`
--

-- --------------------------------------------------------

--
-- Table structure for table `banners`
--

DROP TABLE IF EXISTS `banners`;
CREATE TABLE IF NOT EXISTS `banners` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text,
  `button_text` varchar(50) DEFAULT NULL,
  `button_link` varchar(255) DEFAULT NULL,
  `image_path` varchar(255) NOT NULL,
  `position` enum('left','right') NOT NULL DEFAULT 'left',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `banners`
--

INSERT INTO `banners` (`id`, `title`, `description`, `button_text`, `button_link`, `image_path`, `position`, `status`, `created_at`) VALUES
(1, 'New Arrivals!', 'Discover the latest in tech and gadgets', 'Shop Now', 'Shop Now', 'uploads/banners/banner_6815d8e0bfd78.jpg', 'left', 'active', '2025-05-03 08:50:40'),
(2, 'Summer Sale!', 'Get up to 50% off on selected items. Limited time only!', 'Shop Now', 'Shop Now', 'uploads/banners/banner_6815d923db6ae.jpg', 'right', 'active', '2025-05-03 08:51:47');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `image`, `created_at`) VALUES
(1, 'Apple', 'Explore the world of cutting-edge technology with Apple\'s latest innovations.', 'uploads/categories/680d527916e05.webp', '2025-04-26 21:39:05'),
(4, 'Samsung', 'Discover Samsung\'s most powerful and sleek smartphones yet.', 'uploads/categories/6815cdf7cd34a.jpg', '2025-05-03 08:04:07'),
(5, 'Vivo', 'Explore the latest features of Vivo\'s innovative smartphones.', 'uploads/categories/6815ce17c2c3b.jpg', '2025-05-03 08:04:39');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
CREATE TABLE IF NOT EXISTS `customers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(191) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `address` text,
  `city` varchar(100) DEFAULT NULL,
  `zip_code` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `first_name`, `last_name`, `email`, `phone`, `password`, `status`, `address`, `city`, `zip_code`, `created_at`, `updated_at`) VALUES
(2, 'ASU', 'RA', 'asura@gmail.com', '0977288288', '$2y$10$H3iFpPv/6S.sLXeKO.GsBOU6DFhOIDjpiX/6cXcBEWRe1TVMImCpa', 'active', 'Phnom Penh', NULL, NULL, '2025-05-14 14:05:48', '2025-05-14 14:05:48');

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

DROP TABLE IF EXISTS `invoices`;
CREATE TABLE IF NOT EXISTS `invoices` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `invoice_date` date NOT NULL,
  `due_date` date NOT NULL,
  `total_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `tax_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `shipping_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `discount_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `status` varchar(20) NOT NULL DEFAULT 'draft',
  `notes` text,
  `billing_address` text,
  `shipping_address` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`id`, `order_id`, `invoice_number`, `invoice_date`, `due_date`, `total_amount`, `tax_amount`, `shipping_amount`, `discount_amount`, `status`, `notes`, `billing_address`, `shipping_address`, `created_at`, `updated_at`) VALUES
(10, 16, 'INV202505-0002', '2025-05-15', '2025-06-14', 2588.76, 191.76, 0.00, 0.00, 'draft', '', 'Phnom Penh', 'Phnom Penh', '2025-05-15 18:44:10', '2025-05-15 18:44:10');

-- --------------------------------------------------------

--
-- Table structure for table `invoice_settings`
--

DROP TABLE IF EXISTS `invoice_settings`;
CREATE TABLE IF NOT EXISTS `invoice_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `company_name` varchar(100) NOT NULL,
  `address` text,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `tax_id` varchar(50) DEFAULT NULL,
  `prefix` varchar(10) DEFAULT 'INV',
  `terms` text,
  `notes` text,
  `logo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `invoice_settings`
--

INSERT INTO `invoice_settings` (`id`, `company_name`, `address`, `phone`, `email`, `tax_id`, `prefix`, `terms`, `notes`, `logo`, `created_at`, `updated_at`) VALUES
(1, 'PHONE SHOP', 'St 123, Phnom Penh, Cambodia', '+855 977626855', 'phoneshop@gmail.com', NULL, 'INV', NULL, NULL, NULL, '2025-05-15 17:19:50', '2025-05-15 18:46:35');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_reference` varchar(50) DEFAULT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_email` varchar(255) NOT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `shipping_address` text NOT NULL,
  `city` varchar(100) NOT NULL,
  `zip_code` varchar(20) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `shipping` decimal(10,2) NOT NULL,
  `tax` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `payment_verified_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_reference` (`order_reference`)
) ENGINE=MyISAM AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_reference`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `city`, `zip_code`, `subtotal`, `shipping`, `tax`, `total`, `payment_method`, `status`, `created_at`, `payment_verified_at`, `updated_at`) VALUES
(16, 'ORD-20250515-6825ffe735bd3', 'ASU RA', 'asura@gmail.com', '0977288288', 'Phnom Penh', 'phnom penh', '111', 2397.00, 0.00, 191.76, 2588.76, 'paypal', 'paid', '2025-05-15 14:53:27', '2025-05-15 14:53:42', '2025-05-15 14:53:42');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int NOT NULL,
  `total` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=MyISAM AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `price`, `quantity`, `total`) VALUES
(23, 16, 0, 'iPhone 13 mini', 899.00, 1, 899.00),
(24, 16, 0, 'Samsung Galaxy S23 Ultra', 1199.00, 1, 1199.00),
(25, 16, 0, 'Vivo Y56', 299.00, 1, 299.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(10,2) NOT NULL,
  `stock` int NOT NULL DEFAULT '0',
  `category_id` int DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `stock`, `category_id`, `image`, `status`, `created_at`, `updated_at`) VALUES
(1, 'iPhone 16 Pro', '', 1699.00, 100, 1, 'uploads/products/681516e7d38f1.jpg', 'active', '2025-05-02 19:03:03', '2025-05-02 19:03:03'),
(2, 'iPhone 13 mini', '', 899.00, 100, 1, 'uploads/products/68158c1d92d4c.jpg', 'active', '2025-05-03 03:23:09', '2025-05-03 03:23:09'),
(3, 'Samsung Galaxy Z Fold 5', '', 1299.00, 0, 4, 'uploads/products/681620f2cfa9a.jpg', 'active', '2025-05-03 13:58:10', '2025-05-03 13:58:10'),
(4, 'Vivo X90 Pro', '', 899.00, 100, 5, 'uploads/products/681621c11cc2b.png', 'active', '2025-05-03 14:01:37', '2025-05-03 14:01:37'),
(5, 'iPhone 15 Pro', '', 1499.00, 100, 1, 'uploads/products/68256da63cbbc.jpg', 'active', '2025-05-15 04:29:26', '2025-05-15 04:29:26'),
(6, 'iPhone 14 Pro', '', 1299.00, 100, 1, 'uploads/products/68256de68c32b.jpg', 'active', '2025-05-15 04:30:30', '2025-05-15 04:30:30'),
(7, 'Samsung Galaxy S23 Ultra', '', 1199.00, 100, 4, 'uploads/products/68256e5f6bce3.jpg', 'active', '2025-05-15 04:32:31', '2025-05-15 04:32:31'),
(8, 'Samsung Galaxy A54', '', 449.00, 100, 4, 'uploads/products/68256e8645c90.jpg', 'active', '2025-05-15 04:33:10', '2025-05-15 04:33:10'),
(9, 'Samsung Galaxy S21 FE', '', 699.00, 100, 4, 'uploads/products/68256eb11edc6.jpg', 'active', '2025-05-15 04:33:53', '2025-05-15 04:33:53'),
(10, 'Vivo V29', '', 499.00, 100, 5, 'uploads/products/68256fc338c4b.jpg', 'active', '2025-05-15 04:38:27', '2025-05-15 04:38:27'),
(11, 'Vivo Y56', '', 299.00, 100, 5, 'uploads/products/68256ffb9ebbc.jpg', 'active', '2025-05-15 04:39:23', '2025-05-15 04:39:23'),
(12, 'Vivo X70 Pro', '', 799.00, 100, 5, 'uploads/products/68257020e6d18.jpg', 'active', '2025-05-15 04:40:00', '2025-05-15 04:40:00');

-- --------------------------------------------------------

--
-- Table structure for table `sliders`
--

DROP TABLE IF EXISTS `sliders`;
CREATE TABLE IF NOT EXISTS `sliders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `description1` text COLLATE utf8mb4_general_ci,
  `description2` text COLLATE utf8mb4_general_ci,
  `button_text` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `button_link` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sliders`
--

INSERT INTO `sliders` (`id`, `title`, `description1`, `description2`, `button_text`, `button_link`, `image_path`, `status`, `created_at`, `updated_at`) VALUES
(2, 'Apple', 'Get up to 50% off on selected items. Limited time only!', 'Built for Apple Intelligence.', 'Shop Now', 'Shop Now', 'uploads/sliders/slider_6815d4b30617a.jpg', 'active', '2025-05-03 15:32:51', NULL),
(3, 'Samsung', 'Discover the latest in tech and gadgets.', 'Galaxy AI is here', 'Shop Now', 'Shop Now', 'uploads/sliders/slider_6815d52c39687.jpg', 'active', '2025-05-03 15:34:52', NULL),
(4, 'Vivo', 'Don\'t miss out on our exclusive offers.', 'Delight in Every Portrait', 'Shop Now', 'Shop Now', 'uploads/sliders/slider_6815d5697b8d7.jpg', 'active', '2025-05-03 15:35:53', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `created_at`) VALUES
(6, 'Admin', 'admin@gmail.com', '$2y$10$uBU7xq61d/mZiAynUQ..POKCGJWeZNFzRvXY9q6VdW835K.46R85W', '2025-05-28 15:39:59');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
