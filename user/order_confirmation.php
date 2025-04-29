<?php
session_start();

if (!isset($_SESSION['order_id'])) {
    header("Location: product_list.php");
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

$order_id = $_SESSION['order_id'];
$user_id = $_SESSION['user_id'];

// Get order details
$order_sql = "SELECT o.*, a.* FROM orders o
              LEFT JOIN address a ON o.shipping_address_id = a.address_id
              WHERE o.order_id = ? AND o.user_id = ?";
$stmt = $conn->prepare($order_sql);
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order_result = $stmt->get_result();
$order = $order_result->fetch_assoc();

// Get order items
$items_sql = "SELECT oi.*, p.prod_name, p.prod_image FROM order_items oi
              JOIN product p ON oi.product_id = p.prod_id
              WHERE oi.order_id = ?";
$stmt = $conn->prepare($items_sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items_result = $stmt->get_result();
$items = [];
while($row = $items_result->fetch_assoc()) {
    $items[] = $row;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Supermarket</title>
    <link rel="stylesheet" href="../user_assets/css/checkout.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="container">
            <h1><a href="product_list.php">Supermarket</a></h1>
            <nav>
                <a href="product_list.php"><i class="fas fa-store"></i> Products</a>
                <a href="#"><i class="fas fa-heart"></i> Wishlist</a>
                <a href="cart.php"><i class="fas fa-shopping-cart"></i> Cart</a>
                <span class="user-info">
                    <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                    <a href="logout.php" class="logout-btn">Logout</a>
                </span>
            </nav>
        </div>
    </header>

    <main class="container">
        <div class="confirmation-message">
            <h1><i class="fas fa-check-circle"></i> Thank You for Your Order!</h1>
            <p>Your order #<?php echo $order_id; ?> has been placed successfully.</p>
            <p>We've sent a confirmation email to <?php echo htmlspecialchars($_SESSION['user_email']); ?></p>
        </div>

        <div class="order-details">
            <h2>Order Summary</h2>
            <div class="order-info">
                <div class="info-row">
                    <span>Order Number:</span>
                    <span><?php echo $order_id; ?></span>
                </div>
                <div class="info-row">
                    <span>Order Date:</span>
                    <span><?php echo date('F j, Y', strtotime($order['order_date'])); ?></span>
                </div>
                <div class="info-row">
                    <span>Delivery Method:</span>
                    <span><?php echo $order['delivery_method'] === 'express' ? 'Express Delivery' : 'Standard Delivery'; ?></span>
                </div>
                <div class="info-row">
                    <span>Payment Method:</span>
                    <span>
                        <?php 
                        switch($order['payment_method']) {
                            case 'credit_card': echo 'Credit Card'; break;
                            case 'debit_card': echo 'Debit Card'; break;
                            case 'tng_ewallet': echo 'Touch \'n Go eWallet'; break;
                            case 'cash_on_delivery': echo 'Cash on Delivery'; break;
                            default: echo $order['payment_method'];
                        }
                        ?>
                    </span>
                </div>
            </div>

            <div class="shipping-info">
                <h3>Shipping Address</h3>
                <p><?php echo htmlspecialchars($order['recipient_name']); ?></p>
                <p><?php echo htmlspecialchars($order['street_address']); ?></p>
                <p><?php echo htmlspecialchars($order['city'] . ', ' . $order['state'] . ' ' . $order['postal_code']); ?></p>
                <p>Phone: <?php echo htmlspecialchars($order['phone_number']); ?></p>
            </div>

            <div class="order-items">
                <h3>Order Items</h3>
                <?php foreach ($items as $item): ?>
                <div class="order-item">
                    <div class="item-image">
                        <img src="../assets/uploads/<?php echo htmlspecialchars($item['prod_image']); ?>" 
                             alt="<?php echo htmlspecialchars($item['prod_name']); ?>">
                    </div>
                    <div class="item-details">
                        <h4><?php echo htmlspecialchars($item['prod_name']); ?></h4>
                        <p>Quantity: <?php echo $item['quantity']; ?></p>
                    </div>
                    <div class="item-price">
                        RM <?php echo number_format($item['unit_price'] * $item['quantity'], 2); ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="order-totals">
                <div class="total-row">
                    <span>Subtotal:</span>
                    <span>RM <?php echo number_format($order['subtotal'], 2); ?></span>
                </div>
                <div class="total-row">
                    <span>Delivery Fee:</span>
                    <span>RM <?php echo number_format($order['delivery_fee'], 2); ?></span>
                </div>
                <?php if ($order['discount_amount'] > 0): ?>
                <div class="total-row">
                    <span>Discount:</span>
                    <span>-RM <?php echo number_format($order['discount_amount'], 2); ?></span>
                </div>
                <?php endif; ?>
                <div class="total-row grand-total">
                    <span>Total:</span>
                    <span>RM <?php echo number_format($order['total_amount'], 2); ?></span>
                </div>
            </div>
        </div>

        <div class="confirmation-actions">
            <a href="product_list.php" class="btn btn-primary">Continue Shopping</a>
            <a href="order_history.php" class="btn">View Order History</a>
        </div>
    </main>

    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3>About Us</h3>
                <p>Your one-stop supermarket for all daily needs.</p>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="product_list.php">Products</a></li>
                    <li><a href="#">Special Offers</a></li>
                    <li><a href="#">Contact Us</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Supermarket. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>