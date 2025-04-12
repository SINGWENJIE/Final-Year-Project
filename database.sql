-- ADMIN TABLE (unchanged as requested)
CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `admin_name` varchar(100) NOT NULL,
  `admin_email` varchar(255) NOT NULL,
  `admin_password` varchar(255) NOT NULL,
  `admin_phone_num` varchar(20) DEFAULT NULL,
  `admin_role` enum('superadmin','admin') NOT NULL DEFAULT 'admin'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert initial admin data (unchanged)
INSERT INTO `admin` (`admin_id`, `admin_name`, `admin_email`, `admin_password`, `admin_phone_num`, `admin_role`) VALUES
(1, 'qiaoxuan', 'qx@gmail.com', 'password', '0167371239', 'admin');

-- CATEGORY TABLE (unchanged as requested)
CREATE TABLE `category` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert initial category data (unchanged)
INSERT INTO `category` (`category_id`, `category_name`) VALUES
(3, 'ice cream'),
(2, 'snacks');

-- PRODUCT TABLE (unchanged as requested)
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

-- USERS TABLE (enhanced with indexes)
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    user_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    user_password VARCHAR(255) NOT NULL,
    password_reset_token VARCHAR(255),
    token_expiry TIMESTAMP NULL,
    user_phone_num VARCHAR(20),
    user_created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    user_updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    admin_id INT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    email_verified BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (admin_id) REFERENCES admin(admin_id),
    INDEX idx_email (email),
    INDEX idx_admin (admin_id)
);

-- ADDRESS TABLE (enhanced with indexes)
CREATE TABLE address (
    address_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    recipient_name VARCHAR(100) NOT NULL,
    street_address VARCHAR(255) NOT NULL,
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100) NOT NULL,
    postal_code VARCHAR(20) NOT NULL,
    country VARCHAR(100) NOT NULL DEFAULT 'Malaysia',
    is_default BOOLEAN DEFAULT FALSE,
    phone_number VARCHAR(20) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_default (is_default, user_id)
);

-- ORDERS TABLE (enhanced with indexes and status tracking)
CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    shipping_address_id INT NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(12,2) NOT NULL,
    discount_amount DECIMAL(10,2) DEFAULT 0.00,
    shipping_cost DECIMAL(10,2) DEFAULT 0.00,
    tax_amount DECIMAL(10,2) DEFAULT 0.00,
    final_amount DECIMAL(12,2) NOT NULL,
    order_status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded') DEFAULT 'pending',
    notes TEXT,
    admin_id INT NULL,
    expected_delivery_date DATE,
    status_updated_at TIMESTAMP NULL,
    cancellation_reason TEXT,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (shipping_address_id) REFERENCES address(address_id),
    FOREIGN KEY (admin_id) REFERENCES admin(admin_id),
    INDEX idx_user (user_id),
    INDEX idx_status (order_status),
    INDEX idx_date (order_date)
);

CREATE TABLE order_item (
    order_item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    prod_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    discount_amount DECIMAL(10,2) DEFAULT 0.00,
    subtotal DECIMAL(12,2) NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    product_image VARCHAR(255),
    product_description TEXT,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (prod_id) REFERENCES product(prod_id),
    INDEX idx_order (order_id),
    INDEX idx_product (prod_id)
);

-- CART TABLE (unchanged)
CREATE TABLE cart (
    cart_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user (user_id)
);

-- CART ITEM TABLE (updated product_id to prod_id)
CREATE TABLE cart_item (
    cart_item_id INT AUTO_INCREMENT PRIMARY KEY,
    cart_id INT NOT NULL,
    prod_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cart_id) REFERENCES cart(cart_id) ON DELETE CASCADE,
    FOREIGN KEY (prod_id) REFERENCES product(prod_id),
    UNIQUE KEY (cart_id, prod_id)
);

-- PAYMENT TABLE (enhanced with more payment methods and tracking)
CREATE TABLE payment (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    payment_method ENUM('credit_card', 'debit_card', 'paypal', 'bank_transfer', 'cash_on_delivery', 'ewallet', 'buy_now_pay_later') NOT NULL,
    transaction_id VARCHAR(255),
    payment_status ENUM('pending', 'completed', 'failed', 'refunded', 'partially_refunded') DEFAULT 'pending',
    payment_date TIMESTAMP NULL,
    receipt_url VARCHAR(255),
    payment_details JSON,
    failure_reason TEXT,
    FOREIGN KEY (order_id) REFERENCES orders(order_id),
    INDEX idx_order (order_id),
    INDEX idx_status (payment_status),
    INDEX idx_transaction (transaction_id)
);

-- DELIVERY TABLE (enhanced with more tracking info)
CREATE TABLE delivery (
    delivery_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    tracking_number VARCHAR(100),
    delivery_status ENUM('processing', 'shipped', 'in_transit', 'out_for_delivery', 'delivered', 'returned', 'failed') DEFAULT 'processing',
    carrier_name VARCHAR(100),
    estimated_delivery_date DATE,
    actual_delivery_date DATE NULL,
    notes TEXT,
    admin_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    shipping_method VARCHAR(100),
    FOREIGN KEY (order_id) REFERENCES orders(order_id),
    FOREIGN KEY (admin_id) REFERENCES admin(admin_id),
    INDEX idx_order (order_id),
    INDEX idx_tracking (tracking_number),
    INDEX idx_status (delivery_status)
);

-- VOUCHER TABLE (enhanced with more voucher types)
CREATE TABLE voucher (
    voucher_id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    discount_type ENUM('percentage', 'fixed_amount', 'free_shipping') NOT NULL,
    discount_value DECIMAL(10,2) NOT NULL,
    min_order_amount DECIMAL(10,2) DEFAULT 0.00,
    valid_from TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    valid_to TIMESTAMP NOT NULL,
    max_uses INT,
    current_uses INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    voucher_name VARCHAR(100),
    description TEXT,
    admin_id INT NOT NULL COMMENT 'Admin who created voucher',
    FOREIGN KEY (admin_id) REFERENCES admin(admin_id),
    INDEX idx_code (code),
    INDEX idx_validity (valid_from, valid_to)
);

-- USER VOUCHER TABLE (unchanged)
CREATE TABLE user_voucher (
    user_voucher_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    voucher_id INT NOT NULL,
    claimed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    used_at TIMESTAMP NULL,
    order_id INT NULL,
    is_used BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (voucher_id) REFERENCES voucher(voucher_id),
    FOREIGN KEY (order_id) REFERENCES orders(order_id),
    INDEX idx_user (user_id),
    INDEX idx_voucher (voucher_id)
);

-- PROMOTION TABLE (updated product_id to prod_id)
CREATE TABLE promotion (
    promotion_id INT AUTO_INCREMENT PRIMARY KEY,
    prod_id INT NULL,
    category_id INT NULL,
    promo_code VARCHAR(50) NOT NULL UNIQUE,
    discount_type ENUM('percentage', 'fixed_amount', 'buy_x_get_y') NOT NULL DEFAULT 'percentage',
    discount_value DECIMAL(10,2) NOT NULL,
    start_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    end_date TIMESTAMP NOT NULL DEFAULT (CURRENT_TIMESTAMP + INTERVAL 30 DAY),
    promo_status ENUM('active', 'inactive', 'expired') DEFAULT 'active',
    max_uses INT,
    current_uses INT DEFAULT 0,
    min_order_amount DECIMAL(10,2) DEFAULT 0.00,
    admin_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    promo_name VARCHAR(100) NOT NULL,
    promo_description TEXT,
    FOREIGN KEY (prod_id) REFERENCES product(prod_id),
    FOREIGN KEY (category_id) REFERENCES category(category_id),
    FOREIGN KEY (admin_id) REFERENCES admin(admin_id),
    INDEX idx_product (prod_id),
    INDEX idx_category (category_id),
    INDEX idx_dates (start_date, end_date)
);

-- FEEDBACK TABLE (updated product_id to prod_id)
CREATE TABLE feedback (
    feedback_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_id INT NOT NULL,
    prod_id INT NOT NULL,
    rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    feedback_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_approved BOOLEAN DEFAULT FALSE,
    admin_id INT NULL,
    admin_response TEXT,
    response_date TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (order_id) REFERENCES orders(order_id),
    FOREIGN KEY (prod_id) REFERENCES product(prod_id),
    FOREIGN KEY (admin_id) REFERENCES admin(admin_id),
    INDEX idx_product (prod_id),
    INDEX idx_user (user_id),
    INDEX idx_rating (rating)
);

-- WISHLIST TABLE (updated product_id to prod_id)
CREATE TABLE wishlist (
    wishlist_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    prod_id INT NOT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (prod_id) REFERENCES product(prod_id) ON DELETE CASCADE,
    UNIQUE KEY (user_id, prod_id)
);

-- Add primary key constraints to the tables you provided
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `admin_email` (`admin_email`);

ALTER TABLE `category`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

ALTER TABLE `product`
  ADD PRIMARY KEY (`prod_id`),
  ADD KEY `category_id` (`category_id`);