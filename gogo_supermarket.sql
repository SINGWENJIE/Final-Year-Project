DATABASE NAME: gogo_supermarket

CREATE TABLE `address` (
  `address_id` int(11) NOT NULL,           --ADDRESS ID
  `user_id` int(11) NOT NULL,              --USER ID
  `recipient_name` varchar(100) NOT NULL,  --RECIPIENT NAME
  `street_address` varchar(255) NOT NULL,  --STREET ADDRESS [NO7, JALAN CS 8]
  `city` varchar(100) NOT NULL,            --CITY [KLANG]
  `state` varchar(100) NOT NULL,           --STATE [SELANGOR]
  `postal_code` varchar(20) NOT NULL,      --POSTAL CODE [75250]
  `country` varchar(100) NOT NULL DEFAULT 'Malaysia', --!DELETE
  `is_default` tinyint(1) DEFAULT 0,       --DEFAULT: TRUE, This address will be automatically selected during checkout
  `phone_number` varchar(20) NOT NULL      --!DELETE
  --ADD ['note' TEXT, --Add delivery instructions]
);

--CHANGE (SQL)
[ALTER TABLE `address`
  ADD `note` TEXT AFTER `phone_number`,
  DROP COLUMN `country`;
]

INSERT INTO `address` (`address_id`, `user_id`, `recipient_name`, `street_address`, `city`, `state`, `postal_code`, `country`, `is_default`, `phone_number`) VALUES
(1, 3, 'qiaoxuan', 'No. 5, Jalan Bukit', 'Kuala Lumpur', 'ixora', '50000', 'Malaysia', 1, '0123456789');



CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `admin_name` varchar(100) NOT NULL,
  `admin_email` varchar(255) NOT NULL,
  `admin_password` varchar(255) NOT NULL,
  `admin_phone_num` varchar(20) DEFAULT NULL,
  `admin_role` enum('superadmin','admin') NOT NULL DEFAULT 'admin',
  `status` enum('active','inactive') DEFAULT 'active'
);


INSERT INTO `admin` (`admin_id`, `admin_name`, `admin_email`, `admin_password`, `admin_phone_num`, `admin_role`, `status`) VALUES
(7, 'qiaoxuan', 'qiaoxuanp@gmail.com', 'password', NULL, 'admin', 'active'),
(8, 'changhao', 'changhao@gmail.com', 'password', NULL, 'admin', 'active'),
(9, 'weixin', 'weixin@gmail.com', 'password', NULL, 'admin', 'active');


--! CHANGE THE CART AND CART ITEM TABLE FOLLOW NEWDATA-----------------------------------------------------
CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
);


CREATE TABLE `cart_item` (
  `cart_item_id` int(11) NOT NULL,
  `cart_id` int(11) NOT NULL,
  `prod_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
);

--DELETE AND CHANGE TO 
CREATE TABLE CART (
    CART_ID INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,  -- NULL for GUEST USER
    SESSION_ID VARCHAR(100) NULL,  -- For GUEST CART
    CREATED_AT TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UPDATED_AT TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    CONSTRAINT chk_user_or_session 
       CHECK( (user_id IS NOT NULL AND SESSION_ID IS NULL) 
       OR (user_id IS NULL AND SESSION_ID IS NOT NULL) ),
    UNIQUE KEY uq_cart_session (SESSION_ID)
);

CREATE TABLE CART_ITEMS (
    CART_ITEM_ID INT AUTO_INCREMENT PRIMARY KEY,
    CART_ID INT NOT NULL,  --FOREIGN KEY BY CART TABLE
    prod_id INT NOT NULL,  --FOREIGN KEY BY PRODUCT TABLE
    QUANTITY INT NOT NULL DEFAULT 1 CHECK (QUANTITY > 0),
    FOREIGN KEY (CART_ID) REFERENCES CART(CART_ID) ON DELETE CASCADE,
    FOREIGN KEY (prod_id) REFERENCES product(prod_id) ON DELETE CASCADE,
    UNIQUE KEY (CART_ID, prod_id)  -- Prevent duplicate products in cart
);

-----------------------------------------------------------------------------------------------------------

CREATE TABLE `category` (
  `category_id` int(11) NOT NULL,        --CATEGORY ID
  `category_name` varchar(100) NOT NULL  --CATEGORY NAME
);


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
  `delivery_id` int(11) NOT NULL,    --DELIVERY ID
  `order_id` int(11) NOT NULL,       --ORDER ID [FOREIGN KEY BY ORDER TABLE]
  `tracking_number` varchar(100) DEFAULT NULL, --DELIVERY TRACKING NUMBER
  `delivery_status` enum('processing','shipped','in_transit','out_for_delivery','delivered','returned','failed') DEFAULT 'processing',  --DELIVERY STATUS: PROCESSING / OUT FOR DELIVERY / DELIVERED
  `carrier_name` varchar(100) DEFAULT NULL,  --CARRIER (CHANGE THE "CARRIER NAME" TO "CARRIER" VARCHAR(50) DEFAULT 'STANDARD DELIVERY')
  `estimated_delivery_date` date DEFAULT NULL,  --ETIMATED DELIVERY
  `actual_delivery_date` date DEFAULT NULL,     --ACTUAL DELIVERY
  `notes` text DEFAULT NULL,                    --DELIVERY NOTE
  `admin_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),                               --!DELETE
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(), --!DELETE
  `shipping_method` varchar(100) DEFAULT NULL                                                --!DELETE
);

--CHANGE (SQL)
[ ALTER TABLE `delivery`
  MODIFY `delivery_status` ENUM('processing', 'out_for_delivery', 'delivered') DEFAULT 'processing';

  ALTER TABLE `delivery`
  CHANGE `carrier_name` `carrier` VARCHAR(50) DEFAULT 'STANDARD DELIVERY';

  ALTER TABLE `delivery`
  DROP COLUMN `created_at`,
  DROP COLUMN `updated_at`,
  DROP COLUMN `shipping_method`;
]

--! AFTER SEE HOW TO DO----------------------------------------------------------------------------------
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
);
---------------------------------------------------------------------------------------------------------

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,                                 --ORDER ID
  `user_id` int(11) NOT NULL,                                  --USER ID [FOREIGN KEY BY USERS TABLE]
  `shipping_address_id` int(11) NOT NULL,                      --ADDRESS ID [FOREIGN KEY BY ADDRESS TABLE]
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(), --ORDER DATE
  `total_amount` decimal(12,2) NOT NULL,                       --TOTAL AMOUNT = SUBTOTAL + DELIVERY FEE - DISCOUNT
  `discount_amount` decimal(10,2) DEFAULT 0.00,                --DISCOUNT
  `shipping_cost` decimal(10,2) DEFAULT 0.00,                  --DELIVERY FEE [CHANGE "SHIPPING COST" TO "DELIVERY FEE"]
  `tax_amount` decimal(10,2) DEFAULT 0.00,                     --!DELETE
  `final_amount` decimal(12,2) NOT NULL,                       --!DELETE
  `order_status` enum('pending','processing','shipped','delivered','cancelled','refunded') DEFAULT 'pending', --ORDER STATUS: PENDING / PROCESSING / SHIPPED / DELIVERED
  `notes` text DEFAULT NULL,                                   --!DELETE
  `admin_id` int(11) DEFAULT NULL,                             --ADMIN ID [FOREIGN KEY BY ADMIN TABLE]
  `expected_delivery_date` date DEFAULT NULL,                  --!DELETE
  `status_updated_at` timestamp NULL DEFAULT NULL,             --!DELETE
  `cancellation_reason` text DEFAULT NULL                      --!DELETE
  --ADD ['Subtotal' DECIMAL(10,2) NOT NULL]
);

--CHANGE (SQL)
[ ALTER TABLE `orders`
  CHANGE `shipping_cost` `delivery_fee` DECIMAL(10,2) DEFAULT 5.00;

  ALTER TABLE `orders`
  DROP COLUMN `tax_amount`,
  DROP COLUMN `final_amount`,
  DROP COLUMN `notes`,
  DROP COLUMN `expected_delivery_date`,
  DROP COLUMN `status_updated_at`,
  DROP COLUMN `cancellation_reason`;

  ALTER TABLE `orders`
  ADD `subtotal` DECIMAL(10,2) NOT NULL AFTER `order_date`;
]

INSERT INTO `orders` (`order_id`, `user_id`, `shipping_address_id`, `order_date`, `total_amount`, `discount_amount`, `shipping_cost`, `tax_amount`, `final_amount`, `order_status`, `notes`, `admin_id`, `expected_delivery_date`, `status_updated_at`, `cancellation_reason`) VALUES
(1004, 3, 1, '2025-04-23 07:37:36', 28.00, 0.00, 0.00, 0.00, 28.00, 'processing', NULL, NULL, NULL, NULL, NULL);


CREATE TABLE `order_item` (
  `order_item_id` int(11) NOT NULL,              --ORDER ITEM ID
  `order_id` int(11) NOT NULL,                   --ORDER ID [FOREIGN KEY BY ORDER TABLE]
  `prod_id` int(11) NOT NULL,                    --PRODUCT ID [FOREIGN KEY BY PRODUCT TABLE]
  `quantity` int(11) NOT NULL,                   --ORDER ITEM QUANTITY
  `unit_price` decimal(10,2) NOT NULL,           --ORDER ITEM PRICE [CHANGE "UNIT PRICE" TO "ORDER_ITEM_PRICE"]
  `discount_amount` decimal(10,2) DEFAULT 0.00,  --!DELETE
  `subtotal` decimal(12,2) NOT NULL,             --!DELETE
  `product_name` varchar(255) NOT NULL,          --!DELETE
  `product_image` varchar(255) DEFAULT NULL,     --!DELETE
  `product_description` text DEFAULT NULL        --!DELETE
);

--CHANGE(SQL)
[ ALTER TABLE `order_item`
  CHANGE `unit_price` `order_item_price` DECIMAL(10,2) NOT NULL;

  ALTER TABLE `order_item`
  DROP COLUMN `discount_amount`,
  DROP COLUMN `subtotal`,
  DROP COLUMN `product_name`,
  DROP COLUMN `product_image`,
  DROP COLUMN `product_description`;
]


INSERT INTO `order_item` (`order_item_id`, `order_id`, `prod_id`, `quantity`, `unit_price`, `discount_amount`, `subtotal`, `product_name`, `product_image`, `product_description`) VALUES
(7, 1004, 5, 2, 14.00, 0.00, 0.00, '', NULL, NULL);


CREATE TABLE `payment` (
  `payment_id` int(11) NOT NULL,                 --PAYMENT ID
  `order_id` int(11) NOT NULL,                   --ORDER ID [FOREIGN KEY BY ORDER TABLE]
  `amount` decimal(12,2) NOT NULL,               --PAYMENT AMOUNT
  `payment_method` enum('credit_card','debit_card','paypal','bank_transfer','cash_on_delivery','ewallet','buy_now_pay_later') NOT NULL, --PAYMENT METHOD: CREDIT DEBIT CARD / TNGEWALLET / CASH ON DELIVERY
  `transaction_id` varchar(100) DEFAULT NULL,    --TRANSACTION ID
  `payment_status` enum('pending','completed','failed','refunded','partially_refunded') DEFAULT 'pending',  --PAYMENT STATUS: COMPLETE, AFTER USER PLACE ORDER, PAYMENT STATUS DIRECTLY SHOW COMPLETED
  `payment_date` timestamp NULL DEFAULT NULL,    --PAYMENT DATE
  `receipt_url` varchar(255) DEFAULT NULL,       --!DELETE
  `payment_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payment_details`)), --DELETE
  `failure_reason` text DEFAULT NULL             --!DELETE
);

--CHANGE(SQL)
[ ALTER TABLE `payment`
  DROP COLUMN `receipt_url`,
  DROP COLUMN `payment_details`,
  DROP COLUMN `failure_reason`;

  ALTER TABLE `payment`
  MODIFY `payment_method` ENUM('credit_card', 'debit_card', 'tng_ewallet', 'cash_on_delivery') NOT NULL;

  ALTER TABLE `payment`
  MODIFY `payment_status` ENUM('completed') DEFAULT 'completed';
]


CREATE TABLE `product` (
  `prod_id` int(11) NOT NULL,                   --PRODUCT ID
  `prod_name` varchar(255) NOT NULL,            --PRODUCT NAME
  `category_id` int(11) NOT NULL,               --CATEGORY ID [FOREIGN KEY BY CATEGORY TABLE]
  `prod_price` decimal(10,2) NOT NULL,          --PRODUCT PRICE
  `prod_description` text DEFAULT NULL,         --PRODUCT DESCRIPTION
  `prod_quantity` int(11) NOT NULL DEFAULT 0,   --可有可无, 再看！
  `prod_image` varchar(255) DEFAULT NULL,       --PRODUCT IMAGE
  `stock` int(11) DEFAULT NULL                  --PRODUCT STOCK
);


INSERT INTO `product` (`prod_id`, `prod_name`, `category_id`, `prod_price`, `prod_description`, `prod_quantity`, `prod_image`, `stock`) VALUES
(5, 'Lays Classic Potato Chips 170g', 2, 13.98, 'Lay\'s potato chips continue to be made with quality, homegrown Canadian potatoes.', 0, 'lays.png', 200),
(6, 'broccoli', 9, 6.50, 'this is a broccoli come form Japan', 0, 'broccoli_commodity-page.png', 80),
(8, 'massimo', 7, 3.60, 'this is massimo', 0, 'massimo.webp', 200),
(10, 'wall\'s', 3, 3.50, 'this is walls ice cream\r\n', 0, 'walls.jpeg', 200),
(12, 'Cola', 12, 2.90, 'one cola can buy you happy', 0, 'images.jpeg', 100);


--!DELETE-----------------------------------------------------------------------------------------------------
CREATE TABLE `promotion` (
  `promotion_id` int(11) NOT NULL,         
  `prod_id` int(11) DEFAULT NULL,          
  `category_id` int(11) DEFAULT NULL,
  `promo_code` varchar(50) NOT NULL,
  `discount_type` enum('percentage','fixed_amount','buy_x_get_y') NOT NULL DEFAULT 'percentage',
  `discount_value` decimal(10,2) NOT NULL,
  `start_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `end_date` timestamp NOT NULL DEFAULT (current_timestamp() + interval 30 day),
  `promo_status` enum('active','inactive','expired') DEFAULT 'active',
  `max_uses` int(11) DEFAULT NULL,
  `current_uses` int(11) DEFAULT 0,
  `min_order_amount` decimal(10,2) DEFAULT 0.00,
  `admin_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `promo_name` varchar(100) NOT NULL,
  `promo_description` text DEFAULT NULL
);

--CHANGE PROMOTION TABLE TO 
CREATE TABLE PROMO_CODE (
  CODE VARCHAR(20) PRIMARY KEY,          -- e.g. "RM5OFF"
  DISCOUNT_AMOUNT DECIMAL(10,2) NOT NULL,-- e.g. 5.00 (RM5 discount)
  MIN_ORDER DECIMAL(10,2) DEFAULT 0,     -- e.g. 50.00 (min RM50 purchase)
  VALID_FROM DATE NOT NULL,              -- Start date
  VALID_TO DATE NOT NULL,                -- Expiry date
  MAX_USES INT DEFAULT NULL,             -- NULL = unlimited uses
  USES_COUNT INT DEFAULT 0               -- Track redemption count
);

----------------------------------------------------------------------------------------------------------------------------

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,                --USER ID
  `user_name` varchar(100) NOT NULL,         --USER NAME
  `email` varchar(255) NOT NULL,             --USER EMAIL
  `user_password` varchar(255) NOT NULL,     --USER PASSWORD
  `password_reset_token` varchar(255) DEFAULT NULL,  --!DELETE
  `token_expiry` timestamp NULL DEFAULT NULL,        --!DELETE
  `user_phone_num` varchar(20) DEFAULT NULL, --USER PHONE NUMBER (VARCHER CHANGE TO 15)
  `user_created_at` timestamp NOT NULL DEFAULT current_timestamp(),  --USER CREATED TIME
  `user_updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(), --!DELETE
  `last_login` timestamp NULL DEFAULT NULL,  --!DELETE
  `admin_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,          --!DELETE
  `email_verified` tinyint(1) DEFAULT 0,     --!DELETE
  `birth_date` date DEFAULT NULL,            --USER BIRTH_DATE          
  `address` varchar(255) DEFAULT NULL,       --!DELETE
  `status` varchar(20) DEFAULT 'active'      --USER STATUS [FOR ADMIN CONTROL]
);

--CHANGE(SQL)
[ ALTER TABLE `users`
  CHANGE `user_phone_num` `user_phone_num` VARCHAR(15) DEFAULT NULL;

  ALTER TABLE `users`
  DROP COLUMN `password_reset_token`,
  DROP COLUMN `token_expiry`,
  DROP COLUMN `user_updated_at`,
  DROP COLUMN `last_login`,
  DROP COLUMN `is_active`,
  DROP COLUMN `email_verified`,
  DROP COLUMN `address`;
]

INSERT INTO `users` (`user_id`, `user_name`, `email`, `user_password`, `password_reset_token`, `token_expiry`, `user_phone_num`, `user_created_at`, `user_updated_at`, `last_login`, `admin_id`, `is_active`, `email_verified`, `birth_date`, `address`, `status`) VALUES
(3, 'qiaoxuan', 'qiaoxuan@gmail.com', '', NULL, NULL, '0167371239', '2025-04-12 11:46:40', '2025-04-21 06:58:11', NULL, NULL, 1, 0, NULL, NULL, 'active'),
(5, 'siqi', 'siqi@gmail.com', '', NULL, NULL, '23124', '2025-04-12 11:53:04', '2025-04-12 11:53:04', NULL, NULL, 1, 0, NULL, NULL, 'active');

--!DELETE-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
CREATE TABLE `user_voucher` (
  `user_voucher_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `voucher_id` int(11) NOT NULL,
  `claimed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `used_at` timestamp NULL DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `is_used` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



CREATE TABLE `voucher` (
  `voucher_id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `discount_type` enum('percentage','fixed_amount','free_shipping') NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `min_order_amount` decimal(10,2) DEFAULT 0.00,
  `valid_from` timestamp NOT NULL DEFAULT current_timestamp(),
  `valid_to` timestamp NOT NULL DEFAULT (current_timestamp() + interval 30 day),
  `max_uses` int(11) DEFAULT NULL,
  `current_uses` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `voucher_name` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `admin_id` int(11) NOT NULL COMMENT 'Admin who created voucher'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--------------------------------------------------------------------------------------------------------------


CREATE TABLE `wishlist` (
  `wishlist_id` int(11) NOT NULL,                              --WISHLIST ID
  `user_id` int(11) NOT NULL,                                  --USER ID [FOREIGN KEY BY USER TABLE]
  `prod_id` int(11) NOT NULL,                                  --PROD ID [FOREIGN KEY BY PRODUCT TABLE]
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()    --!DELETE
);

--CHANGE(SQL)
[
  ALTER TABLE `wishlist`
  DROP COLUMN `added_at`;
]

---------------------------------------------------------------------------------------------------------------

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
  ADD UNIQUE KEY `admin_email` (`admin_email`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `idx_user` (`user_id`);

--
-- Indexes for table `cart_item`
--
ALTER TABLE `cart_item`
  ADD PRIMARY KEY (`cart_item_id`),
  ADD UNIQUE KEY `cart_id` (`cart_id`,`prod_id`),
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
-- Indexes for table `promotion`
--
ALTER TABLE `promotion`
  ADD PRIMARY KEY (`promotion_id`),
  ADD UNIQUE KEY `promo_code` (`promo_code`),
  ADD KEY `idx_product` (`prod_id`),
  ADD KEY `idx_category` (`category_id`),
  ADD KEY `idx_dates` (`start_date`,`end_date`),
  ADD KEY `admin_id` (`admin_id`);

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
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cart_item`
--
ALTER TABLE `cart_item`
  MODIFY `cart_item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

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
  MODIFY `prod_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `promotion`
--
ALTER TABLE `promotion`
  MODIFY `promotion_id` int(11) NOT NULL AUTO_INCREMENT;

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
-- Constraints for table `cart_item`
--
ALTER TABLE `cart_item`
  ADD CONSTRAINT `cart_item_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `cart` (`cart_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_item_ibfk_2` FOREIGN KEY (`prod_id`) REFERENCES `product` (`prod_id`);

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
-- Constraints for table `promotion`
--
ALTER TABLE `promotion`
  ADD CONSTRAINT `promotion_ibfk_1` FOREIGN KEY (`prod_id`) REFERENCES `product` (`prod_id`),
  ADD CONSTRAINT `promotion_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`),
  ADD CONSTRAINT `promotion_ibfk_3` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`admin_id`);

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
