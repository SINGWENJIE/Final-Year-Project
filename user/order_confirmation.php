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

// Get the latest order for this user
$order_sql = "SELECT o.*, a.*, p.payment_method 
              FROM orders o
              JOIN address a ON o.shipping_address_id = a.address_id
              JOIN payment p ON o.order_id = p.order_id
              WHERE o.user_id = $user_id
              ORDER BY o.order_date DESC LIMIT 1";
$order_result = $conn->query($order_sql);

if ($order_result->num_rows === 0) {
    header("Location: product_list.php");
    exit();
}

$order = $order_result->fetch_assoc();

// Get order items
$items_sql = "SELECT oi.*, p.prod_name, p.prod_image 
              FROM order_item oi
              JOIN product p ON oi.prod_id = p.prod_id
              WHERE oi.order_id = " . $order['order_id'];
$items_result = $conn->query($items_sql);
$order_items = [];

if ($items_result->num_rows > 0) {
    while($row = $items_result->fetch_assoc()) {
        $order_items[] = $row;
    }
}

// Get delivery info
$delivery_sql = "SELECT * FROM delivery WHERE order_id = " . $order['order_id'];
$delivery_result = $conn->query($delivery_sql);
$delivery = $delivery_result->fetch_assoc();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Supermarket</title>
    <link rel="stylesheet" href="../user_assets/css/order_confirmation.css">
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
        <div class="breadcrumb">
            <a href="product_list.php">Products</a> &gt; 
            <a href="cart.php">Cart</a> &gt; 
            <a href="checkout.php">Checkout</a> &gt; 
            <span>Order Confirmation</span>
        </div>

        <div class="confirmation-container">
            <div class="confirmation-header">
                <div class="confirmation-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h1>Thank You for Your Order!</h1>
                <p class="order-number">Order #<?php echo $order['order_id']; ?></p>
                <p class="confirmation-message">We've received your order and will process it shortly.</p>
            </div>

            <div class="confirmation-details">
                <div class="order-summary">
                    <h2><i class="fas fa-receipt"></i> Order Summary</h2>
                    <div class="summary-items">
                        <?php foreach ($order_items as $item): ?>
                        <div class="summary-item">
                            <div class="item-image">
                                <img src="../assets/uploads/<?php echo htmlspecialchars($item['prod_image']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['prod_name']); ?>">
                            </div>
                            <div class="item-details">
                                <h4><?php echo htmlspecialchars($item['prod_name']); ?></h4>
                                <p>Qty: <?php echo $item['quantity']; ?></p>
                            </div>
                            <div class="item-price">
                                RM <?php echo number_format($item['order_item_price'], 2); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="summary-totals">
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span>RM <?php echo number_format($order['subtotal'], 2); ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Delivery Fee</span>
                            <span>RM <?php echo number_format($order['delivery_fee'], 2); ?></span>
                        </div>
                        <?php if ($order['DISCOUNT_AMOUNT'] > 0): ?>
                        <div class="summary-row">
                            <span>Promo Discount</span>
                            <span>-RM <?php echo number_format($order['DISCOUNT_AMOUNT'], 2); ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="summary-divider"></div>
                        <div class="summary-row total">
                            <span>Total</span>
                            <span>RM <?php echo number_format($order['total_amount'], 2); ?></span>
                        </div>
                    </div>
                </div>

                <div class="delivery-info">
                    <h2><i class="fas fa-truck"></i> Delivery Information</h2>
                    <div class="info-card">
                        <div class="info-row">
                            <span class="info-label">Delivery Method:</span>
                            <span class="info-value"><?php echo $delivery['carrier']; ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Estimated Delivery:</span>
                            <span class="info-value">
                                <?php 
                                if ($delivery['estimated_delivery_date']) {
                                    echo date('j M Y', strtotime($delivery['estimated_delivery_date']));
                                } else {
                                    echo $delivery['carrier'] == 'STANDARD DELIVERY' ? '2-3 business days' : 'Next business day';
                                }
                                ?>
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Tracking Number:</span>
                            <span class="info-value">
                                <?php echo $delivery['tracking_number'] ? $delivery['tracking_number'] : 'Not available yet'; ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="shipping-info">
                    <h2><i class="fas fa-map-marker-alt"></i> Shipping Address</h2>
                    <div class="info-card">
                        <div class="info-row">
                            <span class="info-label">Recipient:</span>
                            <span class="info-value"><?php echo htmlspecialchars($order['recipient_name']); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Address:</span>
                            <span class="info-value">
                                <?php 
                                echo htmlspecialchars($order['street_address']) . ',<br>';
                                echo htmlspecialchars($order['city']) . ', ';
                                echo htmlspecialchars($order['state']) . ' ';
                                echo htmlspecialchars($order['postal_code']);
                                ?>
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Phone:</span>
                            <span class="info-value"><?php echo htmlspecialchars($order['phone_number']); ?></span>
                        </div>
                    </div>
                </div>

                <div class="payment-info">
                    <h2><i class="fas fa-credit-card"></i> Payment Information</h2>
                    <div class="info-card">
                        <div class="info-row">
                            <span class="info-label">Payment Method:</span>
                            <span class="info-value">
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
                        <div class="info-row">
                            <span class="info-label">Payment Status:</span>
                            <span class="info-value">Completed</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Payment Date:</span>
                            <span class="info-value"><?php echo date('j M Y, g:i a', strtotime($order['order_date'])); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="confirmation-actions">
                <a href="product_list.php" class="btn btn-primary">
                    <i class="fas fa-shopping-bag"></i> Continue Shopping
                </a>
                <a href="order_history.php" class="btn btn-secondary">
                    <i class="fas fa-history"></i> View Order History
                </a>
            </div>
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
            <p>&copy; 2023 Supermarket. All rights reserved.</p>
        </div>
    </footer>

    <script src="../user_assets/js/order_confirmation.js"></script>
</body>
</html>