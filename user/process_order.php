<?php
session_start();

// Redirect if not authenticated
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

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

$user_id = $_SESSION['user_id'];

// Process the order
try {
    $conn->autocommit(FALSE); // Start transaction
    
    // 1. Get cart items and calculate total
    $cart_sql = "SELECT ci.CART_ITEM_ID, ci.QUANTITY, p.prod_id, p.prod_price 
                 FROM cart_items ci
                 JOIN product p ON ci.prod_id = p.prod_id
                 JOIN cart c ON ci.CART_ID = c.CART_ID
                 WHERE c.user_id = $user_id";
    $cart_result = $conn->query($cart_sql);
    
    if ($cart_result->num_rows == 0) {
        throw new Exception("Your cart is empty");
    }
    
    $subtotal = 0;
    $cart_items = [];
    while($row = $cart_result->fetch_assoc()) {
        $row['total_price'] = $row['prod_price'] * $row['QUANTITY'];
        $subtotal += $row['total_price'];
        $cart_items[] = $row;
    }
    
    // Get delivery fee and discount from form
    $delivery_fee = isset($_POST['delivery_fee']) ? (float)$_POST['delivery_fee'] : 5.00;
    $discount_amount = isset($_POST['discount_amount']) ? (float)$_POST['discount_amount'] : 0.00;
    $promo_code = isset($_POST['promo_code']) ? $conn->real_escape_string($_POST['promo_code']) : '';
    $total_amount = $subtotal + $delivery_fee - $discount_amount;
    
    // 2. Handle shipping address
    $shipping_address_id = null;
    if ($_POST['shipping_address'] == 'new') {
        // Insert new address
        $recipient_name = $conn->real_escape_string($_POST['recipient_name']);
        $street_address = $conn->real_escape_string($_POST['street_address']);
        $city = $conn->real_escape_string($_POST['city']);
        $state = $conn->real_escape_string($_POST['state']);
        $postal_code = $conn->real_escape_string($_POST['postal_code']);
        $phone_number = $conn->real_escape_string($_POST['phone_number']);
        $note = isset($_POST['note']) ? $conn->real_escape_string($_POST['note']) : '';
        $is_default = isset($_POST['set_default']) ? 1 : 0;
        
        // If setting as default, first unset any existing default
        if ($is_default) {
            $conn->query("UPDATE address SET is_default = 0 WHERE user_id = $user_id");
        }
        
        $address_sql = "INSERT INTO address (user_id, recipient_name, street_address, city, state, postal_code, phone_number, note, is_default)
                        VALUES ($user_id, '$recipient_name', '$street_address', '$city', '$state', '$postal_code', '$phone_number', '$note', $is_default)";
        
        if (!$conn->query($address_sql)) {
            throw new Exception("Failed to save address: " . $conn->error);
        }
        
        $shipping_address_id = $conn->insert_id;
    } else {
        $shipping_address_id = (int)$_POST['shipping_address'];
    }
    
    // 3. Create order record
    $payment_method = $conn->real_escape_string($_POST['payment_method']);
    $order_status = 'pending'; // Default status
    
    $order_sql = "INSERT INTO orders (user_id, shipping_address_id, subtotal, total_amount, DISCOUNT_AMOUNT, delivery_fee, order_status)
                  VALUES ($user_id, $shipping_address_id, $subtotal, $total_amount, $discount_amount, $delivery_fee, '$order_status')";
    
    if (!$conn->query($order_sql)) {
        throw new Exception("Failed to create order: " . $conn->error);
    }
    
    $order_id = $conn->insert_id;
    
    // 4. Add order items
    foreach ($cart_items as $item) {
        $prod_id = $item['prod_id'];
        $quantity = $item['QUANTITY'];
        $price = $item['prod_price'];
        
        $order_item_sql = "INSERT INTO order_item (order_id, prod_id, quantity, order_item_price)
                           VALUES ($order_id, $prod_id, $quantity, $price)";
        
        if (!$conn->query($order_item_sql)) {
            throw new Exception("Failed to add order items: " . $conn->error);
        }
        
        // Update product stock
        $update_stock_sql = "UPDATE product SET stock = stock - $quantity WHERE prod_id = $prod_id";
        $conn->query($update_stock_sql);
    }
    
    // 5. Generate transaction ID (format: TRANS-YYYYMMDD-ORDERID-RANDOM4DIGITS)
    $transaction_id = 'TRANS-' . date('Ymd') . '-' . $order_id . '-' . sprintf('%04d', rand(0, 9999));
    
    // 6. Calculate estimated delivery date
    $delivery_method = ($delivery_fee == 10.00) ? 'EXPRESS DELIVERY' : 'STANDARD DELIVERY';
    
    // Get current date and time in Malaysia timezone
    date_default_timezone_set('Asia/Kuala_Lumpur');
    $current_date = new DateTime();
    
    // Calculate estimated delivery date
    if ($delivery_method == 'EXPRESS DELIVERY') {
        // Next business day (skip weekends)
        $estimated_date = clone $current_date;
        $estimated_date->modify('+1 weekday');
    } else {
        // Standard delivery (2-3 business days)
        $estimated_date = clone $current_date;
        $estimated_date->modify('+3 weekdays');
    }
    
    $estimated_delivery_date = $estimated_date->format('Y-m-d');
    
    // 7. Create payment record
    $payment_sql = "INSERT INTO payment (order_id, amount, payment_method, transaction_id, payment_status, payment_date)
                    VALUES ($order_id, $total_amount, '$payment_method', '$transaction_id', 'completed', NOW())";
    
    if (!$conn->query($payment_sql)) {
        throw new Exception("Failed to record payment: " . $conn->error);
    }
    
    // 8. Create delivery record
    $delivery_sql = "INSERT INTO delivery (order_id, delivery_status, carrier, estimated_delivery_date)
                      VALUES ($order_id, 'processing', '$delivery_method', '$estimated_delivery_date')";
    
    if (!$conn->query($delivery_sql)) {
        throw new Exception("Failed to create delivery record: " . $conn->error);
    }
    
    // 9. Clear the cart
    $cart_id_sql = "SELECT CART_ID FROM cart WHERE user_id = $user_id";
    $cart_id_result = $conn->query($cart_id_sql);
    $cart_id = $cart_id_result->fetch_assoc()['CART_ID'];
    
    $clear_cart_sql = "DELETE FROM cart_items WHERE CART_ID = $cart_id";
    $conn->query($clear_cart_sql);
    
    // Commit transaction
    $conn->commit();
    
    // Redirect to confirmation page
    header("Location: order_confirmation.php?order_id=$order_id");
    exit();
    
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error_message'] = $e->getMessage();
    header("Location: checkout.php");
    exit();
} finally {
    $conn->autocommit(TRUE);
    $conn->close();
}
?>