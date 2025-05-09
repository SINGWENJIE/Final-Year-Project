-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 03, 2025 at 12:34 PM
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
-- Database: `gogo_supermarket`
--

-- --------------------------------------------------------

--
-- Table structure for table `address`
--

CREATE TABLE `address` (
  `address_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `recipient_name` varchar(100) NOT NULL,
  `street_address` varchar(255) NOT NULL,
  `city` enum('MELAKA TENGAH','ALOR GAJAH','JASIN') NOT NULL,
  `state` enum('MALACCA') NOT NULL,
  `postal_code` varchar(20) NOT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `phone_number` varchar(15) NOT NULL,
  `note` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `address`
--

INSERT INTO `address` (`address_id`, `user_id`, `recipient_name`, `street_address`, `city`, `state`, `postal_code`, `is_default`, `phone_number`, `note`) VALUES
(1, 3, 'qiaoxuan', 'No. 5, Jalan Bukit', '', '', '50000', 1, '0123456789', NULL),
(2, 18, 'AVEE', 'NO7, JALAN CS 8, TAMAN CHENG SETIA, 75250', 'MELAKA TENGAH', 'MALACCA', '75250', 0, '011-205-65146', NULL),
(3, 19, 'LAU SHEREEN', 'NO7, JALAN CS 8, TAMAN CHENG SETIA, 75250', 'ALOR GAJAH', 'MALACCA', '75250', 1, '011-205-65146', NULL),
(4, 14, 'WEIFU', 'NO7, JALAN CS 8, TAMAN CHENG SETIA, 75250', '', 'MALACCA', '75250', 0, '0126456099', 'PUT ON MY DOOR INFRONT');

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `admin_name` varchar(100) NOT NULL,
  `admin_email` varchar(255) NOT NULL,
  `admin_password` varchar(255) NOT NULL,
  `admin_phone_num` varchar(20) DEFAULT NULL,
  `admin_role` enum('superadmin','admin') NOT NULL DEFAULT 'admin',
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `admin_name`, `admin_email`, `admin_password`, `admin_phone_num`, `admin_role`, `status`) VALUES
(7, 'qiaoxuan', 'qiaoxuanp@gmail.com', 'password', NULL, 'admin', 'active'),
(8, 'changhao', 'changhao@gmail.com', 'password', NULL, 'admin', 'active'),
(9, 'weixin', 'weixin@gmail.com', 'password', NULL, 'admin', 'active'),
(10, 'CHEW', 'CHEW@gmail.com', 'password', NULL, 'admin', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `CART_ID` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `SESSION_ID` varchar(100) DEFAULT NULL,
  `CREATED_AT` timestamp NOT NULL DEFAULT current_timestamp(),
  `UPDATED_AT` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`CART_ID`, `user_id`, `SESSION_ID`, `CREATED_AT`, `UPDATED_AT`) VALUES
(2, 14, NULL, '2025-04-27 10:39:49', '2025-04-27 10:39:49'),
(3, 17, NULL, '2025-04-28 04:20:00', '2025-04-28 04:20:00'),
(4, 18, NULL, '2025-04-28 13:50:33', '2025-04-28 13:50:33'),
(5, 19, NULL, '2025-04-28 14:03:37', '2025-04-28 14:03:37');

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `CART_ITEM_ID` int(11) NOT NULL,
  `CART_ID` int(11) NOT NULL,
  `prod_id` int(11) NOT NULL,
  `QUANTITY` int(11) NOT NULL DEFAULT 1 CHECK (`QUANTITY` > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart_items`
--

INSERT INTO `cart_items` (`CART_ITEM_ID`, `CART_ID`, `prod_id`, `QUANTITY`) VALUES
(7, 3, 5, 4);

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`category_id`, `category_name`) VALUES
(7, 'Bakery&Breakfast'),
(5, 'Beauty&Personal Care'),
(4, 'Cleaning&Laundry'),
(12, 'Drinks'),
(10, 'FOOD'),
(1, 'Fruits'),
(6, 'Health&Wellness'),
(3, 'Ice Cream'),
(2, 'Snacks'),
(9, 'Vegetables');

-- --------------------------------------------------------

--
-- Table structure for table `delivery`
--

CREATE TABLE `delivery` (
  `delivery_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `tracking_number` varchar(100) DEFAULT NULL,
  `delivery_status` enum('processing','out_for_delivery','delivered') DEFAULT 'processing',
  `carrier` enum('STANDARD DELIVERY','EXPRESS DELIVERY') DEFAULT 'STANDARD DELIVERY',
  `estimated_delivery_date` date DEFAULT NULL,
  `actual_delivery_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `delivery`
--

INSERT INTO `delivery` (`delivery_id`, `order_id`, `tracking_number`, `delivery_status`, `carrier`, `estimated_delivery_date`, `actual_delivery_date`, `notes`, `admin_id`) VALUES
(1, 1005, NULL, 'processing', 'STANDARD DELIVERY', '2025-05-06', NULL, NULL, NULL),
(2, 1006, NULL, 'processing', 'EXPRESS DELIVERY', '2025-05-06', NULL, NULL, NULL),
(3, 1007, NULL, 'processing', 'EXPRESS DELIVERY', '2025-05-05', NULL, NULL, NULL),
(4, 1008, NULL, 'processing', 'STANDARD DELIVERY', '2025-05-07', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `feedback_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `prod_id` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `feedback_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_approved` tinyint(1) DEFAULT 0,
  `admin_id` int(11) DEFAULT NULL,
  `admin_response` text DEFAULT NULL,
  `response_date` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `shipping_address_id` int(11) NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `subtotal` decimal(10,2) NOT NULL,
  `total_amount` decimal(12,2) NOT NULL,
  `DISCOUNT_AMOUNT` decimal(10,2) DEFAULT 0.00,
  `delivery_fee` decimal(10,2) DEFAULT 5.00,
  `order_status` enum('pending','processing','shipped','delivered') DEFAULT 'pending',
  `admin_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `shipping_address_id`, `order_date`, `subtotal`, `total_amount`, `DISCOUNT_AMOUNT`, `delivery_fee`, `order_status`, `admin_id`) VALUES
(1005, 19, 3, '2025-05-03 08:50:11', 55.96, 60.96, 0.00, 5.00, 'pending', NULL),
(1006, 14, 4, '2025-05-03 08:55:01', 27.96, 17.96, 20.00, 10.00, 'pending', NULL),
(1007, 18, 2, '2025-05-03 09:23:52', 3.20, 8.20, 5.00, 10.00, 'processing', NULL),
(1008, 18, 2, '2025-05-03 09:24:59', 14.00, 19.00, 0.00, 5.00, 'processing', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_item`
--

CREATE TABLE `order_item` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `prod_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `order_item_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_item`
--

INSERT INTO `order_item` (`order_item_id`, `order_id`, `prod_id`, `quantity`, `order_item_price`) VALUES
(8, 1005, 5, 2, 13.98),
(9, 1005, 14, 2, 14.00),
(10, 1006, 5, 2, 13.98),
(11, 1007, 13, 1, 3.20),
(12, 1008, 14, 1, 14.00);

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `payment_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `payment_method` enum('credit_card','debit_card','tng_ewallet','cash_on_delivery') NOT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `payment_status` enum('completed') DEFAULT 'completed',
  `payment_date` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`payment_id`, `order_id`, `amount`, `payment_method`, `transaction_id`, `payment_status`, `payment_date`) VALUES
(1, 1005, 60.96, 'cash_on_delivery', NULL, 'completed', NULL),
(2, 1006, 17.96, 'tng_ewallet', NULL, 'completed', NULL),
(3, 1007, 8.20, 'cash_on_delivery', 'TRANS-20250503-1007-3572', 'completed', '2025-05-03 09:23:52'),
(4, 1008, 19.00, 'cash_on_delivery', 'TRANS-20250503-1008-4418', 'completed', '2025-05-03 09:24:59');

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `prod_id` int(11) NOT NULL,
  `prod_name` varchar(255) NOT NULL,
  `category_id` int(11) NOT NULL,
  `prod_price` decimal(10,2) NOT NULL,
  `prod_description` text DEFAULT NULL,
  `prod_quantity` int(11) NOT NULL DEFAULT 0,
  `prod_image` varchar(255) DEFAULT NULL,
  `stock` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`prod_id`, `prod_name`, `category_id`, `prod_price`, `prod_description`, `prod_quantity`, `prod_image`, `stock`) VALUES
(5, 'Lays Classic Potato Chips 170g', 2, 13.98, 'Lay\'s potato chips continue to be made with quality, homegrown Canadian potatoes.', 0, 'lays.png', 196),
(6, 'broccoli', 1, 7.00, 'this is a broccoli come form Japan', 0, 'broccoli_commodity-page.png', 80),
(8, 'massimo', 7, 3.60, 'this is massimo', 0, 'massimo.webp', 200),
(10, 'wall\'s', 3, 3.50, 'this is walls ice cream\r\n', 0, 'walls.jpeg', 200),
(12, 'Cola', 12, 2.90, 'one cola can buy you happy', 0, 'images.jpeg', 100),
(13, 'Massimo Chiffon [Mocha Flavor]', 7, 3.20, 'Massimo Chiffon In A Cup 35g Cupcake Kek Classic Cheese Mocha', 0, 'masimo[pandan].png', 49),
(14, 'Clorox Clean-Up Spray', 4, 14.00, 'Clorox Clean-Up All Purpose Cleaner Spray with Bleach, Spray Bottle, Original, 32 oz', 0, 'clorox[cleanup_spray].png', 97);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `user_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `user_password` varchar(255) NOT NULL,
  `user_phone_num` varchar(15) DEFAULT NULL,
  `user_created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `admin_id` int(11) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `status` varchar(20) DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `user_name`, `email`, `user_password`, `user_phone_num`, `user_created_at`, `admin_id`, `birth_date`, `status`) VALUES
(3, 'qiaoxuan', 'qiaoxuan@gmail.com', '', '0167371239', '2025-04-12 11:46:40', NULL, NULL, 'active'),
(13, 'CHEW', 'kcchew1975@gmail.com', '$2y$10$QktaoxvRJNQztZV3ahCFq.o.RkZtOL0b.uBHFnGo1t6BO983lYeo2', '01120565146', '2025-04-26 10:04:40', NULL, NULL, 'active'),
(14, 'SIOW', 'SIOW0927@gmail.com', '$2y$10$W1/GyC0aTPc4IB6EwCGwgOLp0pOIyqnHpTgj/58km1/7xLQygA7IW', '011-205-65146', '2025-04-26 04:30:24', NULL, NULL, 'active'),
(16, 'POEA', 'POEA@gmail.com', '$2y$10$cZUktvzESnuhrtH/lUzuMOzFpWGoWQNCna88OkHfjx/mZ5TQqjSXi', '011-205-65146', '2025-04-27 00:27:53', NULL, NULL, 'active'),
(17, 'SING', 'SING123@gmail.com', '$2y$10$jy7OFMnYc54856pgbCPQketBKDD1xXayNKsR3AxOddY/edlY2In/q', '011-205-65146', '2025-04-27 22:13:34', NULL, NULL, 'active'),
(18, 'AVEE PANG', 'AVEE123@gmail.com', '$2y$10$uRuDzU/iTwVOY84VYU5PMeK79UG2SdzpMhHZ9Hd81XxvSlbp5IP0m', '011-205-65146', '2025-04-28 07:49:25', NULL, NULL, 'active'),
(19, 'LAU', 'LA123@gmail.com', '$2y$10$UQJyTt6GHRLfS2rC4TIaZO01MlBAE9jFGUy/rricc4kvTbY.zMs4a', '011-205-65146', '2025-04-28 08:01:44', NULL, NULL, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `wishlist_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `prod_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `address`
--
ALTER TABLE `address`
  ADD PRIMARY KEY (`address_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_default` (`is_default`,`user_id`);

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `admin_email` (`admin_email`),
  ADD UNIQUE KEY `admin_email_2` (`admin_email`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`CART_ID`),
  ADD UNIQUE KEY `uq_cart_session` (`SESSION_ID`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`CART_ITEM_ID`),
  ADD UNIQUE KEY `CART_ID` (`CART_ID`,`prod_id`),
  ADD KEY `prod_id` (`prod_id`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indexes for table `delivery`
--
ALTER TABLE `delivery`
  ADD PRIMARY KEY (`delivery_id`),
  ADD KEY `idx_order` (`order_id`),
  ADD KEY `idx_tracking` (`tracking_number`),
  ADD KEY `idx_status` (`delivery_status`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`feedback_id`),
  ADD KEY `idx_product` (`prod_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_rating` (`rating`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_status` (`order_status`),
  ADD KEY `idx_date` (`order_date`),
  ADD KEY `shipping_address_id` (`shipping_address_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `order_item`
--
ALTER TABLE `order_item`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `idx_order` (`order_id`),
  ADD KEY `idx_product` (`prod_id`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `idx_order` (`order_id`),
  ADD KEY `idx_status` (`payment_status`),
  ADD KEY `idx_transaction` (`transaction_id`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`prod_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_admin` (`admin_id`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`wishlist_id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`prod_id`),
  ADD KEY `prod_id` (`prod_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `address`
--
ALTER TABLE `address`
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `CART_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `CART_ITEM_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `delivery`
--
ALTER TABLE `delivery`
  MODIFY `delivery_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `feedback_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1009;

--
-- AUTO_INCREMENT for table `order_item`
--
ALTER TABLE `order_item`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `prod_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `wishlist_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `address`
--
ALTER TABLE `address`
  ADD CONSTRAINT `address_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`CART_ID`) REFERENCES `cart` (`CART_ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`prod_id`) REFERENCES `product` (`prod_id`) ON DELETE CASCADE;

--
-- Constraints for table `delivery`
--
ALTER TABLE `delivery`
  ADD CONSTRAINT `delivery_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `delivery_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`admin_id`);

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `feedback_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `feedback_ibfk_3` FOREIGN KEY (`prod_id`) REFERENCES `product` (`prod_id`),
  ADD CONSTRAINT `feedback_ibfk_4` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`admin_id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`shipping_address_id`) REFERENCES `address` (`address_id`),
  ADD CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`admin_id`);

--
-- Constraints for table `order_item`
--
ALTER TABLE `order_item`
  ADD CONSTRAINT `order_item_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_item_ibfk_2` FOREIGN KEY (`prod_id`) REFERENCES `product` (`prod_id`);

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`admin_id`);

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`prod_id`) REFERENCES `product` (`prod_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
