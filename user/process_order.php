<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gogo_supermarket";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Start transaction
$conn->begin_transaction();

try {
    $user_id = $_SESSION['user_id'];
    $shipping_address_id = $_POST['shipping_address'];
    $delivery_method = $_POST['delivery_method'];
    $payment_method = $_POST['payment_method'];
    $delivery_fee = $_POST['delivery_fee'];
    
    // Get cart items
    $cart_sql = "SELECT ci.QUANTITY, p.prod_id, p.prod_price 
                 FROM cart_items ci
                 JOIN product p ON ci.prod_id = p.prod_id
                 JOIN cart c ON ci.CART_ID = c.CART_ID
                 WHERE c.user_id = $user_id";
    $cart_result = $conn->query($cart_sql);
    
    if ($cart_result->num_rows === 0) {
        throw new Exception("Your cart is empty");
    }
    
    // Calculate subtotal
    $subtotal = 0;
    $cart_items = [];
    while($row = $cart_result->fetch_assoc()) {
        $row['total_price'] = $row['prod_price'] * $row['QUANTITY'];
        $subtotal += $row['total_price'];
        $cart_items[] = $row;
    }
    
    // Apply promo discount if available
    $discount_amount = 0;
    if (isset($_SESSION['applied_promo'])) {
        $discount_amount = $_SESSION['applied_promo']['DISCOUNT_AMOUNT'];
        unset($_SESSION['applied_promo']);
    }
    
    // Calculate total
    $total_amount = $subtotal - $discount_amount + $delivery_fee;
    
    // Create order
    $order_sql = "INSERT INTO orders (user_id, shipping_address_id, subtotal, total_amount, DISCOUNT_AMOUNT, delivery_fee, order_status)
                  VALUES (?, ?, ?, ?, ?, ?, 'pending')";
    $stmt = $conn->prepare($order_sql);
    $stmt->bind_param("iidddd", $user_id, $shipping_address_id, $subtotal, $total_amount, $discount_amount, $delivery_fee);
    $stmt->execute();
    $order_id = $stmt->insert_id;
    
    // Add order items
    foreach ($cart_items as $item) {
        $item_sql = "INSERT INTO order_item (order_id, prod_id, quantity, order_item_price)
                     VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($item_sql);
        $total_price = $item['prod_price'] * $item['QUANTITY'];
        $stmt->bind_param("iiid", $order_id, $item['prod_id'], $item['QUANTITY'], $total_price);
        $stmt->execute();
        
        // Update product stock
        $update_sql = "UPDATE product SET stock = stock - ? WHERE prod_id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("ii", $item['QUANTITY'], $item['prod_id']);
        $stmt->execute();
    }
    
    // Record payment
    $payment_sql = "INSERT INTO payment (order_id, amount, payment_method, payment_status, payment_date)
                    VALUES (?, ?, ?, 'completed', NOW())";
    $stmt = $conn->prepare($payment_sql);
    $stmt->bind_param("ids", $order_id, $total_amount, $payment_method);
    $stmt->execute();
    
    // Create delivery record
    $delivery_date = $delivery_method == 'express' ? date('Y-m-d', strtotime('+1 weekday')) : date('Y-m-d', strtotime('+3 weekdays'));
    
    $delivery_sql = "INSERT INTO delivery (order_id, delivery_status, carrier, estimated_delivery_date)
                     VALUES (?, 'processing', ?, ?)";
    $stmt = $conn->prepare($delivery_sql);
    $carrier = $delivery_method == 'express' ? 'EXPRESS DELIVERY' : 'STANDARD DELIVERY';
    $stmt->bind_param("iss", $order_id, $carrier, $delivery_date);
    $stmt->execute();
    
    // Clear cart
    $clear_cart_sql = "DELETE ci FROM cart_items ci
                       JOIN cart c ON ci.CART_ID = c.CART_ID
                       WHERE c.user_id = $user_id";
    $conn->query($clear_cart_sql);
    
    // Commit transaction
    $conn->commit();
    
    // Redirect to confirmation page
    header("Location: order_confirmation.php?success=1");
    exit();
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    $_SESSION['error'] = $e->getMessage();
    header("Location: checkout.php");
    exit();
}

$conn->close();
?>