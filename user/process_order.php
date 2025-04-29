<?php
session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gogo_supermarket";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

// Validate and sanitize all inputs
$shipping_address_id = isset($_POST['shipping_address']) ? intval($_POST['shipping_address']) : 0;
$delivery_method = isset($_POST['delivery_method']) ? $conn->real_escape_string($_POST['delivery_method']) : '';
$payment_method = isset($_POST['payment_method']) ? $conn->real_escape_string($_POST['payment_method']) : '';
$promo_code = isset($_POST['promo_code']) ? $conn->real_escape_string($_POST['promo_code']) : null;
$card_number = isset($_POST['card_number']) ? $conn->real_escape_string($_POST['card_number']) : null;
$expiry_date = isset($_POST['expiry_date']) ? $conn->real_escape_string($_POST['expiry_date']) : null;
$cvv = isset($_POST['cvv']) ? $conn->real_escape_string($_POST['cvv']) : null;
$card_name = isset($_POST['card_name']) ? $conn->real_escape_string($_POST['card_name']) : null;

// Get cart items and calculate total
$cart_sql = "SELECT ci.CART_ITEM_ID, ci.QUANTITY, p.prod_id, p.prod_name, p.prod_price, p.stock 
             FROM cart_items ci
             JOIN product p ON ci.prod_id = p.prod_id
             JOIN cart c ON ci.CART_ID = c.CART_ID
             WHERE c.user_id = $user_id";
$cart_result = $conn->query($cart_sql);

$cart_items = [];
$subtotal = 0;
$item_count = 0;

if ($cart_result->num_rows > 0) {
    while($row = $cart_result->fetch_assoc()) {
        $row['total_price'] = $row['prod_price'] * $row['QUANTITY'];
        $subtotal += $row['total_price'];
        $item_count += $row['QUANTITY'];
        $cart_items[] = $row;
    }
}

// Check for empty cart
if (empty($cart_items)) {
    $_SESSION['error'] = "Your cart is empty";
    header("Location: cart.php");
    exit();
}

// Calculate delivery fee
$delivery_fee = ($delivery_method === 'express') ? 10.00 : 5.00;

// Initialize discount variables
$discount_amount = 0;
$promo_code_id = null;

// Validate promo code if provided
if (!empty($promo_code)) {
    $promo_sql = "SELECT * FROM promo_code 
                  WHERE CODE = ? 
                  AND VALID_FROM <= CURDATE() 
                  AND VALID_TO >= CURDATE() 
                  AND (MAX_USES IS NULL OR USES_COUNT < MAX_USES)
                  FOR UPDATE"; // Lock the row for update
    
    $stmt = $conn->prepare($promo_sql);
    $stmt->bind_param("s", $promo_code);
    $stmt->execute();
    $promo_result = $stmt->get_result();
    
    if ($promo_result->num_rows > 0) {
        $promo = $promo_result->fetch_assoc();
        
        // Check minimum order amount
        if ($subtotal >= $promo['MIN_ORDER']) {
            $discount_amount = $promo['DISCOUNT_AMOUNT'];
            $promo_code_id = $promo['CODE'];
            
            // Increment promo code usage
            $update_sql = "UPDATE promo_code SET USES_COUNT = USES_COUNT + 1 WHERE CODE = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("s", $promo_code);
            $stmt->execute();
        }
    }
}

// Calculate final total
$total = $subtotal + $delivery_fee - $discount_amount;

// Validate payment method specific requirements
if (($payment_method === 'credit_card' || $payment_method === 'debit_card') && 
    (empty($card_number) || empty($expiry_date) || empty($cvv) || empty($card_name))) {
    $_SESSION['error'] = "Please fill in all credit card details";
    header("Location: checkout.php");
    exit();
}

// Start transaction
$conn->begin_transaction();

try {
    // Create order
    $order_sql = "INSERT INTO orders (user_id, order_date, subtotal, delivery_fee, discount_amount, total_amount, 
                  payment_method, promo_code, shipping_address_id, delivery_method, status)
                  VALUES (?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, 'processing')";
    
    $stmt = $conn->prepare($order_sql);
    $stmt->bind_param("iddddssis", $user_id, $subtotal, $delivery_fee, $discount_amount, $total, 
                      $payment_method, $promo_code_id, $shipping_address_id, $delivery_method);
    $stmt->execute();
    
    $order_id = $conn->insert_id;
    
    // Add order items
    foreach ($cart_items as $item) {
        $order_item_sql = "INSERT INTO order_items (order_id, product_id, quantity, unit_price)
                           VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($order_item_sql);
        $stmt->bind_param("iiid", $order_id, $item['prod_id'], $item['QUANTITY'], $item['prod_price']);
        $stmt->execute();
        
        // Update product stock
        $update_stock_sql = "UPDATE product SET stock = stock - ? WHERE prod_id = ?";
        $stmt = $conn->prepare($update_stock_sql);
        $stmt->bind_param("ii", $item['QUANTITY'], $item['prod_id']);
        $stmt->execute();
    }
    
    // Clear cart
    $clear_cart_sql = "DELETE ci FROM cart_items ci
                       JOIN cart c ON ci.CART_ID = c.CART_ID
                       WHERE c.user_id = ?";
    $stmt = $conn->prepare($clear_cart_sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    
    // Commit transaction
    $conn->commit();
    
    // Store order ID in session for confirmation page
    $_SESSION['order_id'] = $order_id;
    header("Location: order_confirmation.php");
    exit();
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    $_SESSION['error'] = "Error processing your order: " . $e->getMessage();
    header("Location: checkout.php");
    exit();
}

$conn->close();
?>