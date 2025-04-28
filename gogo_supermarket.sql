CREATE TABLE `address` (
  `address_id` int(11) NOT NULL,--ADDRESS ID
  `user_id` int(11) NOT NULL,--USER ID [FOREIGN KEY BY USER TABLE]
  `recipient_name` varchar(100) NOT NULL,--RECIPIENT NAME
  `street_address` varchar(255) NOT NULL,--STREET ADDRESS [NO7, JALAN CS 8]
  `city` varchar(100) NOT NULL,--CITY [MELAKA TENGAH / ALOR GAJAH / JASIN]
  `state` varchar(100) NOT NULL,--STATE [MALACCA]
  `postal_code` varchar(20) NOT NULL,--POSTAL CODE [75250]
  `is_default` tinyint(1) DEFAULT 0,--DEFAULT: TRUE, This address will be automatically selected during checkout
  `phone_number` varchar(20) NOT NULL,--MALAYSIA PHONE NUMBER IS VARCHAR(15)
  `note` text DEFAULT NULL--ADD DELIVERY INSTRUCTIONS
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT INTO `address` (`address_id`, `user_id`, `recipient_name`, `street_address`, `city`, `state`, `postal_code`, `is_default`, `phone_number`, `note`) VALUES
(1, 3, 'qiaoxuan', 'No. 5, Jalan Bukit', 'Kuala Lumpur', 'ixora', '50000', 1, '0123456789', NULL);


CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `admin_name` varchar(100) NOT NULL,
  `admin_email` varchar(255) NOT NULL,
  `admin_password` varchar(255) NOT NULL,
  `admin_phone_num` varchar(20) DEFAULT NULL,
  `admin_role` enum('superadmin','admin') NOT NULL DEFAULT 'admin',
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



INSERT INTO `admin` (`admin_id`, `admin_name`, `admin_email`, `admin_password`, `admin_phone_num`, `admin_role`, `status`) VALUES
(7, 'qiaoxuan', 'qiaoxuanp@gmail.com', 'password', NULL, 'admin', 'active'),
(8, 'changhao', 'changhao@gmail.com', 'password', NULL, 'admin', 'active'),
(9, 'weixin', 'weixin@gmail.com', 'password', NULL, 'admin', 'active'),
(10, 'CHEW', 'CHEW@gmail.com', 'password', NULL, 'admin', 'active');



CREATE TABLE `cart` (
  `CART_ID` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,-- NULL for GUEST USER
  `SESSION_ID` varchar(100) DEFAULT NULL,-- For GUEST CART
  `CREATED_AT` timestamp NOT NULL DEFAULT current_timestamp(),
  `UPDATED_AT` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ;



CREATE TABLE `cart_items` (
  `CART_ITEM_ID` int(11) NOT NULL,
  `CART_ID` int(11) NOT NULL,--CART ID [FOREIGN KEY BY CART TABLE]
  `prod_id` int(11) NOT NULL,--PROD ID [FOREIGN KEY BY PRODUCT TABLE]
  `QUANTITY` int(11) NOT NULL DEFAULT 1 CHECK (`QUANTITY` > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



CREATE TABLE `category` (
  `category_id` int(11) NOT NULL,--CATEGORY ID
  `category_name` varchar(100) NOT NULL--CATEGORY NAME
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



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


CREATE TABLE `delivery` (
  `delivery_id` int(11) NOT NULL,--DELIVERY ID
  `order_id` int(11) NOT NULL,--ORDER ID [FOREIGN KEY BY ORDER TABLE]
  `tracking_number` varchar(100) DEFAULT NULL,--DELIVERY TRACKING NUMBER
  `delivery_status` enum('processing','out_for_delivery','delivered') DEFAULT 'processing',--DELIVERY STATUS: PROCESSING / OUT OF DELIVERY / DELIVERED
  `carrier` varchar(50) DEFAULT 'STANDARD DELIVERY',--GOT 'STANDARD DELIVERY' IS RM5.00 AND 'EXPRESS DELIVERY' IS RM10.00
  `estimated_delivery_date` date DEFAULT NULL,
  `actual_delivery_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


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


CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,--ORDER ID
  `user_id` int(11) NOT NULL,--USER ID [FOREIGN KEY BY USERS TABLE]
  `shipping_address_id` int(11) NOT NULL,--ADDRESS ID [FOREIGN KEY BY ADDRESS TABLE]
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),--ORDER DATE
  `subtotal` decimal(10,2) NOT NULL,--PRODUCT PRICE TOTAL
  `total_amount` decimal(12,2) NOT NULL,--TOTAL AMOUNT = SUBTOTAL + DELIVERY FEE - DISCOUNT_AMOUNT
  `DISCOUNT_AMOUNT` decimal(10,2) DEFAULT 0.00,--DISCOUNT: SAME AS PROMO_CODE TABLE
  `delivery_fee` decimal(10,2) DEFAULT 5.00,--DELIVERY FEE GOT TWO 1.STANDARD DELIVERY 'RM5.00' / 2.EXPRESS DELIVERY 'RM10.00'
  `order_status` enum('pending','processing','shipped','delivered') DEFAULT 'pending',----ORDER STATUS: PENDING / PROCESSING / SHIPPED / DELIVERED
  `admin_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `order_item` (
  `order_item_id` int(11) NOT NULL,--ORDER ITEM ID
  `order_id` int(11) NOT NULL,--ORDER ID [FOREIGN KEY BY ORDER TABLE]
  `prod_id` int(11) NOT NULL,--PRODUCT ID [FOREIGN KEY BY PRODUCT TABLE]
  `quantity` int(11) NOT NULL,--ORDER ITEM QUANTITY
  `order_item_price` decimal(10,2) NOT NULL--ORDER ITEM PRICE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `payment` (
  `payment_id` int(11) NOT NULL,--PAYMENT ID
  `order_id` int(11) NOT NULL,--ORDER ID [FOREIGN KEY BY ORDER TABLE]
  `amount` decimal(12,2) NOT NULL,--PAYMENT AMOUNT
  `payment_method` enum('credit_card','debit_card','tng_ewallet','cash_on_delivery') NOT NULL,--PAYMENT METHOD: CREDIT DEBIT CARD / TNGEWALLET / CASH ON DELIVERY
  `transaction_id` varchar(255) DEFAULT NULL,--TRANSACTION ID
  `payment_status` enum('completed') DEFAULT 'completed',--PAYMENT STATUS: COMPLETE (AFTER USER PLACE ORDER, PAYMENT STATUS DIRECTLY SHOW COMPLETED)
  `payment_date` timestamp NULL DEFAULT NULL--PAYMENT DATE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `product` (
  `prod_id` int(11) NOT NULL,--PRODUCT ID
  `prod_name` varchar(255) NOT NULL,--PRODUCT NAME
  `category_id` int(11) NOT NULL,--CATEGORY ID [FOREIGN KEY BY CATEGORY TABLE]
  `prod_price` decimal(10,2) NOT NULL,--PRODUCT PRICE
  `prod_description` text DEFAULT NULL,--PRODUCT DESCRIPTION
  `prod_quantity` int(11) NOT NULL DEFAULT 0,--可有可无, 再看！
  `prod_image` varchar(255) DEFAULT NULL,--PRODUCT IMAGE
  `stock` int(11) DEFAULT NULL--PRODUCT STOCK
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



INSERT INTO `product` (`prod_id`, `prod_name`, `category_id`, `prod_price`, `prod_description`, `prod_quantity`, `prod_image`, `stock`) VALUES
(5, 'Lays Classic Potato Chips 170g', 2, 13.98, 'Lay\'s potato chips continue to be made with quality, homegrown Canadian potatoes.', 0, 'lays.png', 200),
(6, 'broccoli', 9, 6.50, 'this is a broccoli come form Japan', 0, 'broccoli_commodity-page.png', 80),
(8, 'massimo', 7, 3.60, 'this is massimo', 0, 'massimo.webp', 200),
(10, 'wall\'s', 3, 3.50, 'this is walls ice cream\r\n', 0, 'walls.jpeg', 200),
(12, 'Cola', 12, 2.90, 'one cola can buy you happy', 0, 'images.jpeg', 100),
(13, 'Massimo Chiffon [Mocha Flavor]', 7, 3.20, 'Massimo Chiffon In A Cup 35g Cupcake Kek Classic Cheese Mocha', 0, 'masimo[pandan].png', 50),
(14, 'Clorox Clean-Up Spray', 4, 14.00, 'Clorox Clean-Up All Purpose Cleaner Spray with Bleach, Spray Bottle, Original, 32 oz', 0, 'clorox[cleanup_spray].png', 100);



CREATE TABLE `promo_code` (
  `CODE` varchar(20) NOT NULL,-- e.g. "RM5OFF"
  `DISCOUNT_AMOUNT` decimal(10,2) NOT NULL,-- e.g. 5.00 (RM5 discount)
  `MIN_ORDER` decimal(10,2) DEFAULT 0.00,-- e.g. 50.00 (min RM50 purchase)
  `VALID_FROM` date NOT NULL,-- Start date
  `VALID_TO` date NOT NULL,-- Expiry date
  `MAX_USES` int(11) DEFAULT NULL,-- NULL = unlimited uses
  `USES_COUNT` int(11) DEFAULT 0 -- Track redemption count
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,--USER ID
  `user_name` varchar(100) NOT NULL,--USER NAME
  `email` varchar(255) NOT NULL,--USER EMAIL
  `user_password` varchar(255) NOT NULL,--USER PASSWORD
  `user_phone_num` varchar(15) DEFAULT NULL,--USER PHONE NUMBER (MALAYSIA PHONE NUMBER IS VARCHAR(15))
  `user_created_at` timestamp NOT NULL DEFAULT current_timestamp(),--USER CREATED TIME
  `admin_id` int(11) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,--USER BIRTH_DATE
  `status` varchar(20) DEFAULT 'active' --USER STATUS [FOR ADMIN CONTROL]
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT INTO `users` (`user_id`, `user_name`, `email`, `user_password`, `user_phone_num`, `user_created_at`, `admin_id`, `birth_date`, `status`) VALUES
(3, 'qiaoxuan', 'qiaoxuan@gmail.com', '', '0167371239', '2025-04-12 11:46:40', NULL, NULL, 'active'),
(5, 'siqi', 'siqi@gmail.com', '', '23124', '2025-04-12 11:53:04', NULL, NULL, 'active');


CREATE TABLE `wishlist` (
  `wishlist_id` int(11) NOT NULL,--WISHLIST ID
  `user_id` int(11) NOT NULL,--USER ID [FOREIGN KEY BY USER TABLE]
  `prod_id` int(11) NOT NULL--PROD ID [FOREIGN KEY BY PRODUCT TABLE]
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
-- Indexes for table `promo_code`
--
ALTER TABLE `promo_code`
  ADD PRIMARY KEY (`CODE`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_admin` (`admin_id`);

--
-- Indexes for table `user_voucher`
--
ALTER TABLE `user_voucher`
  ADD PRIMARY KEY (`user_voucher_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_voucher` (`voucher_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `voucher`
--
ALTER TABLE `voucher`
  ADD PRIMARY KEY (`voucher_id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `idx_validity` (`valid_from`,`valid_to`),
  ADD KEY `admin_id` (`admin_id`);

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
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
  MODIFY `CART_ITEM_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `delivery`
--
ALTER TABLE `delivery`
  MODIFY `delivery_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `feedback_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1005;

--
-- AUTO_INCREMENT for table `order_item`
--
ALTER TABLE `order_item`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `prod_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `user_voucher`
--
ALTER TABLE `user_voucher`
  MODIFY `user_voucher_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `voucher`
--
ALTER TABLE `voucher`
  MODIFY `voucher_id` int(11) NOT NULL AUTO_INCREMENT;

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
-- Constraints for table `user_voucher`
--
ALTER TABLE `user_voucher`
  ADD CONSTRAINT `user_voucher_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `user_voucher_ibfk_2` FOREIGN KEY (`voucher_id`) REFERENCES `voucher` (`voucher_id`),
  ADD CONSTRAINT `user_voucher_ibfk_3` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`);

--
-- Constraints for table `voucher`
--
ALTER TABLE `voucher`
  ADD CONSTRAINT `voucher_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`admin_id`);

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
