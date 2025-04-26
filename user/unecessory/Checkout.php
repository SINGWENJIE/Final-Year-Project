<?php
session_start();
require_once '../db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=checkout.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$user = [];
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Fetch user addresses
$addresses = [];
$stmt = $conn->prepare("SELECT * FROM address WHERE user_id = ? ORDER BY is_default DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$addresses = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch cart items
$cart_items = [];
$cart_total = 0;
$cart_subtotal = 0;
$shipping_cost = 5.00; // Default shipping cost

// First get the active cart
$stmt = $conn->prepare("SELECT cart_id FROM cart WHERE user_id = ? AND is_active = 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_result = $stmt->get_result();
$cart = $cart_result->fetch_assoc();
$stmt->close();

if ($cart) {
    $cart_id = $cart['cart_id'];
    
    // Get cart items with product details
    $stmt = $conn->prepare("
        SELECT ci.*, p.prod_name, p.prod_price, p.prod_image, p.prod_description 
        FROM cart_item ci
        JOIN product p ON ci.prod_id = p.prod_id
        WHERE ci.cart_id = ?
    ");
    $stmt->bind_param("i", $cart_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $cart_items = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    // Calculate totals
    foreach ($cart_items as $item) {
        $cart_subtotal += $item['prod_price'] * $item['quantity'];
    }
    
    // Free shipping for orders over RM50
    if ($cart_subtotal >= 50) {
        $shipping_cost = 0.00;
    }
    
    $cart_total = $cart_subtotal + $shipping_cost;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and process the order
    $shipping_address_id = intval($_POST['shipping_address']);
    $payment_method = $_POST['payment_method'];
    $notes = $_POST['notes'] ?? '';
    
    // Validate address belongs to user
    $valid_address = false;
    foreach ($addresses as $address) {
        if ($address['address_id'] == $shipping_address_id) {
            $valid_address = true;
            break;
        }
    }
    
    if (!$valid_address) {
        $error = "Invalid shipping address selected.";
    } elseif (empty($cart_items)) {
        $error = "Your cart is empty.";
    } else {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Create order
            $stmt = $conn->prepare("
                INSERT INTO orders (
                    user_id, 
                    shipping_address_id, 
                    total_amount, 
                    shipping_cost, 
                    final_amount, 
                    order_status, 
                    notes
                ) VALUES (?, ?, ?, ?, ?, 'pending', ?)
            ");
            $stmt->bind_param(
                "iiddds", 
                $user_id, 
                $shipping_address_id, 
                $cart_subtotal, 
                $shipping_cost, 
                $cart_total, 
                $notes
            );
            $stmt->execute();
            $order_id = $conn->insert_id;
            $stmt->close();
            
            // Add order items
            foreach ($cart_items as $item) {
                $stmt = $conn->prepare("
                    INSERT INTO order_item (
                        order_id, 
                        prod_id, 
                        quantity, 
                        unit_price, 
                        subtotal, 
                        product_name, 
                        product_image, 
                        product_description
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $subtotal = $item['prod_price'] * $item['quantity'];
                $stmt->bind_param(
                    "iiiddsss", 
                    $order_id, 
                    $item['prod_id'], 
                    $item['quantity'], 
                    $item['prod_price'], 
                    $subtotal, 
                    $item['prod_name'], 
                    $item['prod_image'], 
                    $item['prod_description']
                );
                $stmt->execute();
                $stmt->close();
            }
            
            // Create payment record
            $stmt = $conn->prepare("
                INSERT INTO payment (
                    order_id, 
                    amount, 
                    payment_method, 
                    payment_status
                ) VALUES (?, ?, ?, 'pending')
            ");
            $stmt->bind_param(
                "ids", 
                $order_id, 
                $cart_total, 
                $payment_method
            );
            $stmt->execute();
            $stmt->close();
            
            // Mark cart as inactive
            $stmt = $conn->prepare("UPDATE cart SET is_active = 0 WHERE cart_id = ?");
            $stmt->bind_param("i", $cart_id);
            $stmt->execute();
            $stmt->close();
            
            // Commit transaction
            $conn->commit();
            
            // Redirect to confirmation page
            header("Location: confirmation.php?order_id=$order_id");
            exit();
            
        } catch (Exception $e) {
            // Rollback on error
            $conn->rollback();
            $error = "An error occurred while processing your order. Please try again.";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - My Lotus's</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../user/css/Checkout.css">
</head>
<body>
    <div class="checkout-container">
        <!-- Header -->
        <header class="checkout-header">
            <div class="logo">My Lotus's</div>
            <div class="checkout-steps">
                <div class="step active">
                    <span class="step-number">1</span>
                    <span class="step-title">Shipping</span>
                </div>
                <div class="step">
                    <span class="step-number">2</span>
                    <span class="step-title">Payment</span>
                </div>
                <div class="step">
                    <span class="step-number">3</span>
                    <span class="step-title">Confirmation</span>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="checkout-content">
            <?php if (isset($error)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form id="checkoutForm" method="POST">
                <div class="checkout-grid">
                    <!-- Shipping Information -->
                    <div class="checkout-section">
                        <h2>Shipping Information</h2>
                        
                        <div class="address-selection">
                            <h3>Select Shipping Address</h3>
                            
                            <?php if (empty($addresses)): ?>
                                <p>No addresses found. Please <a href="add_address.php">add a shipping address</a>.</p>
                            <?php else: ?>
                                <?php foreach ($addresses as $address): ?>
                                    <div class="address-option">
                                        <input type="radio" 
                                               name="shipping_address" 
                                               id="address_<?php echo $address['address_id']; ?>" 
                                               value="<?php echo $address['address_id']; ?>"
                                               <?php echo $address['is_default'] ? 'checked' : ''; ?> required>
                                        <label for="address_<?php echo $address['address_id']; ?>">
                                            <div class="address-details">
                                                <strong><?php echo htmlspecialchars($address['recipient_name']); ?></strong>
                                                <p><?php echo htmlspecialchars($address['street_address']); ?></p>
                                                <p><?php echo htmlspecialchars($address['city'] . ', ' . $address['state'] . ' ' . $address['postal_code']); ?></p>
                                                <p>Phone: <?php echo htmlspecialchars($address['phone_number']); ?></p>
                                                <?php if ($address['is_default']): ?>
                                                    <span class="default-badge">Default</span>
                                                <?php endif; ?>
                                            </div>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                                <a href="add_address.php" class="add-address-link">
                                    <i class="fas fa-plus"></i> Add New Address
                                </a>
                            <?php endif; ?>
                        </div>
                        
                        <div class="delivery-options">
                            <h3>Delivery Options</h3>
                            <div class="delivery-option selected">
                                <input type="radio" name="delivery_option" id="standard_delivery" checked>
                                <label for="standard_delivery">
                                    <div class="option-details">
                                        <span class="option-name">Standard Delivery</span>
                                        <span class="option-price">
                                            <?php echo $shipping_cost == 0 ? 'FREE' : 'RM' . number_format($shipping_cost, 2); ?>
                                        </span>
                                        <span class="option-time">3-5 business days</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Order Summary -->
                    <div class="order-summary">
                        <h2>Order Summary</h2>
                        
                        <div class="order-items">
                            <?php if (empty($cart_items)): ?>
                                <p>Your cart is empty.</p>
                            <?php else: ?>
                                <?php foreach ($cart_items as $item): ?>
                                    <div class="order-item">
                                        <div class="item-image">
                                            <img src="<?php echo htmlspecialchars($item['prod_image']); ?>" 
                                                 alt="<?php echo htmlspecialchars($item['prod_name']); ?>">
                                        </div>
                                        <div class="item-details">
                                            <h4><?php echo htmlspecialchars($item['prod_name']); ?></h4>
                                            <p>Quantity: <?php echo $item['quantity']; ?></p>
                                        </div>
                                        <div class="item-price">
                                            RM<?php echo number_format($item['prod_price'] * $item['quantity'], 2); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        
                        <div class="order-totals">
                            <div class="total-row">
                                <span>Subtotal</span>
                                <span>RM<?php echo number_format($cart_subtotal, 2); ?></span>
                            </div>
                            <div class="total-row">
                                <span>Shipping</span>
                                <span><?php echo $shipping_cost == 0 ? 'FREE' : 'RM' . number_format($shipping_cost, 2); ?></span>
                            </div>
                            <div class="total-row grand-total">
                                <span>Total</span>
                                <span>RM<?php echo number_format($cart_total, 2); ?></span>
                            </div>
                        </div>
                        
                        <div class="payment-method">
                            <h3>Payment Method</h3>
                            <div class="payment-option">
                                <input type="radio" name="payment_method" id="credit_card" value="credit_card" checked required>
                                <label for="credit_card">
                                    <i class="fas fa-credit-card"></i> Credit Card
                                </label>
                            </div>
                            <div class="payment-option">
                                <input type="radio" name="payment_method" id="debit_card" value="debit_card" required>
                                <label for="debit_card">
                                    <i class="fas fa-credit-card"></i> Debit Card
                                </label>
                            </div>
                            <div class="payment-option">
                                <input type="radio" name="payment_method" id="paypal" value="paypal" required>
                                <label for="paypal">
                                    <i class="fab fa-paypal"></i> PayPal
                                </label>
                            </div>
                            <div class="payment-option">
                                <input type="radio" name="payment_method" id="bank_transfer" value="bank_transfer" required>
                                <label for="bank_transfer">
                                    <i class="fas fa-university"></i> Bank Transfer
                                </label>
                            </div>
                        </div>
                        
                        <div class="order-notes">
                            <h3>Order Notes (Optional)</h3>
                            <textarea name="notes" placeholder="Special instructions for delivery..."></textarea>
                        </div>
                        
                        <button type="submit" class="place-order-btn" <?php echo empty($cart_items) ? 'disabled' : ''; ?>>
                            Place Order
                        </button>
                    </div>
                </div>
            </form>
        </main>

        <!-- Footer -->
        <footer class="checkout-footer">
            <p>© 2023 My Lotus's. All rights reserved.</p>
        </footer>
    </div>

    <script src="../user/js/Checkout.js"></script>
</body>
</html>