-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 30, 2026
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mj_chatbot`
--

-- --------------------------------------------------------

--
-- Table structure for table `firebase_users_tracking`
--

CREATE TABLE `firebase_users_tracking` (
  `uid` varchar(255) NOT NULL,
  `prompt_count` int(11) DEFAULT 0,
  `last_reset` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `firebase_users_tracking`
--

INSERT INTO `firebase_users_tracking` (`uid`, `prompt_count`, `last_reset`) VALUES
('user_bioysswhm', 0, '2026-04-20');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `version` varchar(255) NOT NULL,
  `class` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `namespace` varchar(255) NOT NULL,
  `time` int(11) NOT NULL,
  `batch` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES
(1, '2026-04-21-071900', 'App\\Database\\Migrations\\CreateOrdersTable', 'default', 'App', 1776758276, 1),
(2, '2026-04-21-072000', 'App\\Database\\Migrations\\CreateOrderItemsTable', 'default', 'App', 1776758276, 1),
(3, '2026-04-21-090000', 'App\\Database\\Migrations\\CreateAdvancedSeafoodModules', 'default', 'App', 1776758370, 2),
(4, '2026-04-21-100000', 'App\\Database\\Migrations\\RemoveAdvancedSeafoodModules', 'default', 'App', 1776764245, 3),
(5, '2026-04-21-110000', 'App\\Database\\Migrations\\CreateSalesHistoryTable', 'default', 'App', 1776936710, 4);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) UNSIGNED NOT NULL,
  `transaction_code` varchar(20) DEFAULT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT 0.00,
  `subtotal_amount` decimal(10,2) DEFAULT 0.00,
  `shipping_fee` decimal(10,2) DEFAULT 0.00,
  `voucher_discount` decimal(10,2) DEFAULT 0.00,
  `final_amount` decimal(10,2) DEFAULT 0.00,
  `status` varchar(50) DEFAULT 'Pending',
  `notes` text DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT 'COD',
  `payment_status` varchar(30) DEFAULT 'unpaid',
  `payment_ref` varchar(80) DEFAULT NULL,
  `payment_provider` varchar(40) DEFAULT NULL,
  `applied_vouchers` text DEFAULT NULL,
  `tracking_number` varchar(80) DEFAULT NULL,
  `courier_name` varchar(80) DEFAULT NULL,
  `shipped_at` datetime DEFAULT NULL,
  `delivered_at` datetime DEFAULT NULL,
  `cancel_reason` text DEFAULT NULL,
  `shipping_barangay` varchar(255) DEFAULT NULL,
  `shipping_phone` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `transaction_code`, `customer_name`, `total_amount`, `subtotal_amount`, `shipping_fee`, `voucher_discount`, `final_amount`, `status`, `notes`, `payment_method`, `payment_status`, `payment_ref`, `payment_provider`, `applied_vouchers`, `tracking_number`, `courier_name`, `shipped_at`, `delivered_at`, `cancel_reason`, `shipping_barangay`, `shipping_phone`, `created_at`, `updated_at`) VALUES
(1, 'ORD-69EA021EB5182', 'Mj Laurito', 700.00, 700.00, 0.00, 0.00, 700.00, 'Processing', 'Customer online order', 'COD', 'unpaid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-23 11:27:26', '2026-04-23 12:44:37'),
(2, 'ORD-69EA15A176419', 'Mj Laurito', 700.00, 700.00, 0.00, 0.00, 700.00, 'Cancelled', 'Customer online order', 'COD', 'unpaid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Cancelled by customer', NULL, NULL, '2026-04-23 12:50:41', '2026-04-23 12:51:39'),
(3, 'ORD-69EA1EAFCACCC', 'Mj Laurito', 500.00, 500.00, 0.00, 0.00, 500.00, 'Pending', 'Customer online order', 'COD', 'unpaid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'bocana', '09129238032', '2026-04-23 13:29:19', '2026-04-23 13:29:19');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `order_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `unit` varchar(20) DEFAULT 'piece',
  `quantity` decimal(10,2) NOT NULL DEFAULT 1.00,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `unit`, `quantity`, `unit_price`, `subtotal`) VALUES
(1, 1, 3, 'pasayan', '', 1.00, 500.00, 500.00),
(2, 1, 1, 'bangros', '', 1.00, 200.00, 200.00),
(3, 2, 3, 'pasayan', '', 1.00, 500.00, 500.00),
(4, 2, 1, 'bangros', '', 1.00, 200.00, 200.00),
(5, 3, 3, 'pasayan', '', 1.00, 500.00, 500.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `cost_price` decimal(10,2) DEFAULT 0.00,
  `selling_price` decimal(10,2) DEFAULT 0.00,
  `initial_stock` decimal(10,2) DEFAULT 0.00,
  `current_stock` decimal(10,2) DEFAULT 0.00,
  `wastage_qty` decimal(10,2) DEFAULT 0.00,
  `unit` enum('kg','piece','batch') DEFAULT 'kg',
  `image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `cost_price`, `selling_price`, `initial_stock`, `current_stock`, `wastage_qty`, `unit`, `image`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'bangros', 212.00, 200.00, 2.00, 23.00, 0.00, '', '1776942474_a709a6b323bc76e7d97e.jpeg', 'active', '2026-04-21 07:37:33', '2026-04-23 11:07:54', NULL),
(3, 'pasayan', 200.00, 500.00, 20.00, 20.00, 0.00, '', '1776941975_58dd3f54ca0bdbf59570.jpg', 'active', '2026-04-23 10:59:35', '2026-04-23 10:59:35', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sales_history`
--

CREATE TABLE `sales_history` (
  `id` int(11) UNSIGNED NOT NULL,
  `transaction_code` varchar(50) NOT NULL,
  `items_summary` text DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales_history`
--

INSERT INTO `sales_history` (`id`, `transaction_code`, `items_summary`, `total_amount`, `created_at`) VALUES
(1, 'ORD-69EA021EB5182', 'pasayan, bangros', 700.00, '2026-04-23 11:27:26'),
(2, 'ORD-69EA15A176419', 'pasayan, bangros', 700.00, '2026-04-23 12:50:41'),
(3, 'ORD-69EA1EAFCACCC', 'pasayan', 500.00, '2026-04-23 13:29:20');

-- --------------------------------------------------------

--
-- Table structure for table `shipping_locations`
--

CREATE TABLE `shipping_locations` (
  `id` int(11) NOT NULL,
  `barangay_name` varchar(255) NOT NULL,
  `city_municipality` varchar(255) DEFAULT 'Bacolod City',
  `shipping_fee` decimal(10,2) DEFAULT 49.00,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shipping_locations`
--

INSERT INTO `shipping_locations` (`id`, `barangay_name`, `city_municipality`, `shipping_fee`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'dancalan', 'Ilog', 49.00, 1, '2026-04-23 13:10:32', '2026-04-23 13:10:32'),
(2, 'bocana', 'ilog', 49.00, 1, '2026-04-23 13:10:50', '2026-04-23 13:10:50');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` varchar(20) DEFAULT 'staff',
  `password` varchar(255) NOT NULL,
  `prompt_count` int(11) DEFAULT 0,
  `last_reset` date DEFAULT curdate()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `role`, `password`, `prompt_count`, `last_reset`) VALUES
(2, 'admin_user', 'admin12345@gmail.com', 'admin', '$2y$10$I3pGQiDVIKDfV.KixpkdTuhr6JhaH/s7mPZ2PFRtWi6iSDSLI3.HC', 0, '2026-04-08'),
(3, 'staff_member', 'staff12345@gmail.com', 'staff', '$2y$10$xYNpeBlyRK/8/E5/I8nbsupnzPUN40OkbZf145UmiVEu2L/6gSgti', 0, '2026-04-08'),
(13, 'laurito12345@gmail.com', 'laurito12345@gmail.com', 'customer', '$2y$10$XAuY70ZMSzxLcUXqTtARNuMSz5UuqK13GKX/WY6bbTelTRWXJIG7O', 0, '2026-04-20'),
(14, 'Mj Laurito', 'laurmj4@gmail.com', 'customer', '', 0, '2026-04-20');

-- --------------------------------------------------------

--
-- Table structure for table `vouchers`
--

CREATE TABLE `vouchers` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` varchar(40) NOT NULL,
  `name` varchar(100) NOT NULL,
  `scope` varchar(20) DEFAULT 'platform',
  `discount_type` varchar(20) DEFAULT 'fixed',
  `discount_value` decimal(10,2) DEFAULT 0.00,
  `max_discount` decimal(10,2) DEFAULT NULL,
  `min_order_amount` decimal(10,2) DEFAULT 0.00,
  `payment_method_limit` varchar(30) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `starts_at` datetime DEFAULT NULL,
  `ends_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vouchers`
--

INSERT INTO `vouchers` (`code`, `name`, `scope`, `discount_type`, `discount_value`, `max_discount`, `min_order_amount`, `is_active`, `created_at`, `updated_at`) VALUES
('PLAT40', 'Platform 40 Off', 'platform', 'fixed', 40.00, 40.00, 500.00, 1, NOW(), NOW()),
('SHOP8', 'Shop 8 Percent', 'shop', 'percent', 8.00, 120.00, 1000.00, 1, NOW(), NOW());

-- --------------------------------------------------------

--
-- Table structure for table `voucher_redemptions`
--

CREATE TABLE `voucher_redemptions` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `voucher_id` int(11) UNSIGNED NOT NULL,
  `order_id` int(11) UNSIGNED NOT NULL,
  `customer_name` varchar(120) DEFAULT NULL,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `voucher_id` (`voucher_id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_attempts`
--

CREATE TABLE `payment_attempts` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` int(11) UNSIGNED NOT NULL,
  `payment_method` varchar(30) NOT NULL,
  `provider` varchar(30) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT 0.00,
  `status` varchar(20) DEFAULT 'pending',
  `reference` varchar(80) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cod_compliance`
--

CREATE TABLE `cod_compliance` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_name` varchar(120) NOT NULL,
  `failed_cod_count` int(11) DEFAULT 0,
  `window_start_at` datetime DEFAULT NULL,
  `cod_disabled_until` datetime DEFAULT NULL,
  `last_failed_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customer_name` (`customer_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_payment_constraints`
--

CREATE TABLE `product_payment_constraints` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` int(11) UNSIGNED NOT NULL,
  `payment_method` varchar(30) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_status_history`
--

CREATE TABLE `order_status_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `status_from` varchar(50) NOT NULL,
  `status_to` varchar(50) NOT NULL,
  `changed_by` varchar(100) NOT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_reviews`
--

CREATE TABLE `order_reviews` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` int(11) UNSIGNED NOT NULL,
  `customer_name` varchar(120) NOT NULL,
  `rating` tinyint(1) NOT NULL,
  `comment` text DEFAULT NULL,
  `media_paths` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `refund_requests`
--

CREATE TABLE `refund_requests` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` int(11) UNSIGNED NOT NULL,
  `customer_name` varchar(120) NOT NULL,
  `reason` text DEFAULT NULL,
  `status` varchar(30) DEFAULT 'Pending',
  `evidence_paths` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `firebase_users_tracking`
--
ALTER TABLE `firebase_users_tracking`
  ADD PRIMARY KEY (`uid`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sales_history`
--
ALTER TABLE `sales_history`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transaction_code` (`transaction_code`);

--
-- Indexes for table `shipping_locations`
--
ALTER TABLE `shipping_locations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sales_history`
--
ALTER TABLE `sales_history`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `shipping_locations`
--
ALTER TABLE `shipping_locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
