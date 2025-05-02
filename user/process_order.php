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

// Start transaction
$conn->begin_transaction();

try {
    // 1. Get cart items and calculate totals
    $cart_sql = "SELECT ci.QUANTITY, p.prod_id, p.prod_name, p.prod_price 
                 FROM cart_items ci
                 JOIN product p ON ci.prod_id = p.prod_id
                 JOIN cart c ON ci.CART_ID = c.CART_ID
                 WHERE c.user_id = $user_id";
    $cart_result = $conn->query($cart_sql);

    if ($cart_result->num_rows === 0) {
        throw new Exception("Your cart is empty");
    }

    $cart_items = [];
    $subtotal = 0;
    $item_count = 0;

    while ($row = $cart_result->fetch_assoc()) {
        $row['total_price'] = $row['prod_price'] * $row['QUANTITY'];
        $subtotal += $row['total_price'];
        $item_count += $row['QUANTITY'];
        $cart_items[] = $row;
    }

    // 2. Get delivery fee and promo code discount
    $delivery_fee = isset($_POST['delivery_fee']) ? floatval($_POST['delivery_fee']) : 5.00;
    $discount_amount = 0;
    $promo_code = null;

    if (isset($_SESSION['applied_promo'])) {
        $promo_code = $_SESSION['applied_promo']['CODE'];
        $discount_amount = $_SESSION['applied_promo']['DISCOUNT_AMOUNT'];
        
        // Verify promo code is still valid
        $promo_sql = "SELECT * FROM promo_code 
                      WHERE CODE = ? 
                      AND VALID_FROM <= CURDATE() 
                      AND VALID_TO >= CURDATE() 
                      AND (MAX_USES IS NULL OR USES_COUNT < MAX_USES)";
        $stmt = $conn->prepare($promo_sql);
        $stmt->bind_param("s", $promo_code);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("The promo code is no longer valid");
        }
        
        // Check minimum order amount
        if ($subtotal < $_SESSION['applied_promo']['MIN_ORDER']) {
            throw new Exception("Minimum order amount not met for this promo code");
        }
    }

    $total = $subtotal + $delivery_fee - $discount_amount;

    // 3. Process shipping address
    if (empty($_POST['shipping_address']) || $_POST['shipping_address'] === 'new') {
        // Validate new address fields
        $required_fields = ['recipient_name', 'street_address', 'city', 'state', 'postal_code', 'phone_number'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Please fill in all address fields");
            }
        }

        // Insert new address
        $address_sql = "INSERT INTO address (user_id, recipient_name, street_address, city, state, postal_code, phone_number, note, is_default)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($address_sql);
        $is_default = isset($_POST['set_default']) ? 1 : 0;
        $note = isset($_POST['note']) ? $_POST['note'] : null;
        $stmt->bind_param("isssssssi", 
            $user_id,
            $_POST['recipient_name'],
            $_POST['street_address'],
            $_POST['city'],
            $_POST['state'],
            $_POST['postal_code'],
            $_POST['phone_number'],
            $note,
            $is_default
        );
        $stmt->execute();
        $address_id = $conn->insert_id;
    } else {
        $address_id = intval($_POST['shipping_address']);
    }

    // Verify address exists and belongs to user
    $address_check = $conn->query("SELECT address_id FROM address WHERE address_id = $address_id AND user_id = $user_id");
    if ($address_check->num_rows === 0) {
        throw new Exception("Invalid shipping address");
    }

    // 4. Process payment method
    $payment_method = isset($_POST['payment_method']) ? $_POST['payment_method'] : 'credit_card';
    $payment_status = 'pending';

    // Simulate payment processing
    switch ($payment_method) {
        case 'credit_card':
        case 'debit_card':
            // Validate card details in a real application
            $payment_status = 'paid';
            break;
        case 'tng_ewallet':
            $payment_status = 'paid';
            break;
        case 'cash_on_delivery':
            $payment_status = 'pending';
            break;
        default:
            throw new Exception("Invalid payment method");
    }

    // 5. Create order
    $order_sql = "INSERT INTO orders (user_id, address_id, order_date, delivery_method, payment_method, payment_status, 
                  subtotal, delivery_fee, discount_amount, total_amount, promo_code)
                  VALUES (?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($order_sql);
    $delivery_method = isset($_POST['delivery_method']) ? $_POST['delivery_method'] : 'standard';
    $stmt->bind_param("iisssdddds", 
        $user_id,
        $address_id,
        $delivery_method,
        $payment_method,
        $payment_status,
        $subtotal,
        $delivery_fee,
        $discount_amount,
        $total,
        $promo_code
    );
    $stmt->execute();
    $order_id = $conn->insert_id;

    // 6. Add order items
    foreach ($cart_items as $item) {
        $order_item_sql = "INSERT INTO order_items (order_id, prod_id, quantity, price)
                           VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($order_item_sql);
        $stmt->bind_param("iiid", 
            $order_id,
            $item['prod_id'],
            $item['QUANTITY'],
            $item['prod_price']
        );
        $stmt->execute();

        // Update product stock
        $update_stock = "UPDATE product SET stock = stock - ? WHERE prod_id = ?";
        $stmt = $conn->prepare($update_stock);
        $stmt->bind_param("ii", $item['QUANTITY'], $item['prod_id']);
        $stmt->execute();
    }

    // 7. Update promo code usage if applied
    if ($promo_code) {
        $update_promo = "UPDATE promo_code SET USES_COUNT = USES_COUNT + 1 WHERE CODE = ?";
        $stmt = $conn->prepare($update_promo);
        $stmt->bind_param("s", $promo_code);
        $stmt->execute();
        unset($_SESSION['applied_promo']);
    }

    // 8. Clear the cart
    $cart_id_sql = "SELECT CART_ID FROM cart WHERE user_id = $user_id";
    $cart_id_result = $conn->query($cart_id_sql);
    if ($cart_id_result->num_rows > 0) {
        $cart_row = $cart_id_result->fetch_assoc();
        $cart_id = $cart_row['CART_ID'];
        
        $clear_cart = "DELETE FROM cart_items WHERE CART_ID = $cart_id";
        $conn->query($clear_cart);
    }

    // Commit transaction
    $conn->commit();

    // 9. Redirect to order confirmation page
    $_SESSION['order_complete'] = [
        'order_id' => $order_id,
        'total' => $total,
        'payment_status' => $payment_status
    ];
    header("Location: order_confirmation.php");
    exit();

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    $_SESSION['checkout_error'] = $e->getMessage();
    header("Location: checkout.php");
    exit();
}

$conn->close();
?>