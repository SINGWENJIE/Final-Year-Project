<?php
session_start();

// Redirect if not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['order_id'])) {
    header("Location: product_list.php");
    exit();
}

$order_id = (int)$_GET['order_id'];
$user_id = $_SESSION['user_id'];

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gogo_supermarket";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get order details
$order_sql = "SELECT o.*, a.* 
              FROM orders o
              JOIN address a ON o.shipping_address_id = a.address_id
              WHERE o.order_id = $order_id AND o.user_id = $user_id";
$order_result = $conn->query($order_sql);

if ($order_result->num_rows == 0) {
    $conn->close();
    header("Location: product_list.php");
    exit();
}

$order = $order_result->fetch_assoc();

// Get order items
$items_sql = "SELECT oi.*, p.prod_name, p.prod_image 
              FROM order_item oi
              JOIN product p ON oi.prod_id = p.prod_id
              WHERE oi.order_id = $order_id";
$items_result = $conn->query($items_sql);
$order_items = [];
while($row = $items_result->fetch_assoc()) {
    $order_items[] = $row;
}

// Get payment details
$payment_sql = "SELECT * FROM payment WHERE order_id = $order_id";
$payment_result = $conn->query($payment_sql);
$payment = $payment_result->fetch_assoc();

// Get delivery details
$delivery_sql = "SELECT * FROM delivery WHERE order_id = $order_id";
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
                <a href="cart.php"><i class="fas fa-shopping-cart"></i> Cart <span class="cart-count">0</span></a>
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
                <i class="fas fa-check-circle"></i>
                <h1>Thank You for Your Order!</h1>
                <p>Your order has been placed successfully. Here are your order details:</p>
                <div class="order-summary-box">
                    <div class="summary-item">
                        <span>Order Number:</span>
                        <strong>#<?php echo $order_id; ?></strong>
                    </div>
                    <div class="summary-item">
                        <span>Order Date:</span>
                        <strong><?php echo date('F j, Y', strtotime($order['order_date'])); ?></strong>
                    </div>
                    <div class="summary-item">
                        <span>Total Amount:</span>
                        <strong>RM <?php echo number_format($order['total_amount'], 2); ?></strong>
                    </div>
                    <div class="summary-item">
                        <span>Payment Method:</span>
                        <strong>
                            <?php 
                            switch($payment['payment_method']) {
                                case 'credit_card': echo 'Credit Card'; break;
                                case 'debit_card': echo 'Debit Card'; break;
                                case 'tng_ewallet': echo 'Touch \'n Go eWallet'; break;
                                case 'cash_on_delivery': echo 'Cash on Delivery'; break;
                                default: echo $payment['payment_method'];
                            }
                            ?>
                        </strong>
                    </div>
                </div>
            </div>

            <div class="confirmation-details">
                <div class="detail-section">
                    <h2><i class="fas fa-box-open"></i> Order Items</h2>
                    <div class="order-items">
                        <?php foreach ($order_items as $item): ?>
                        <div class="order-item">
                            <div class="item-image">
                                <img src="../assets/uploads/<?php echo htmlspecialchars($item['prod_image']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['prod_name']); ?>">
                            </div>
                            <div class="item-details">
                                <h4><?php echo htmlspecialchars($item['prod_name']); ?></h4>
                                <p>Quantity: <?php echo $item['quantity']; ?></p>
                                <p>Price: RM <?php echo number_format($item['order_item_price'], 2); ?></p>
                            </div>
                            <div class="item-total">
                                RM <?php echo number_format($item['order_item_price'] * $item['quantity'], 2); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="detail-section">
                    <h2><i class="fas fa-truck"></i> Delivery Information</h2>
                    <div class="delivery-info">
                        <div class="address-card">
                            <h3>Shipping Address</h3>
                            <p><strong><?php echo htmlspecialchars($order['recipient_name']); ?></strong></p>
                            <p><?php echo htmlspecialchars($order['street_address']); ?></p>
                            <p><?php echo htmlspecialchars($order['city'] . ', ' . $order['state'] . ' ' . $order['postal_code']); ?></p>
                            <p>Phone: <?php echo htmlspecialchars($order['phone_number']); ?></p>
                            <?php if (!empty($order['note'])): ?>
                            <p class="delivery-note">Note: <?php echo htmlspecialchars($order['note']); ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="delivery-status">
                            <h3>Delivery Status</h3>
                            <div class="status-timeline">
                                <div class="status-step <?php echo $delivery['delivery_status'] == 'processing' ? 'active' : ''; ?>">
                                    <div class="step-icon">
                                        <i class="fas fa-box"></i>
                                    </div>
                                    <div class="step-details">
                                        <h4>Processing</h4>
                                        <p>Your order is being prepared</p>
                                    </div>
                                </div>
                                <div class="status-step <?php echo $delivery['delivery_status'] == 'out_for_delivery' ? 'active' : ''; ?>">
                                    <div class="step-icon">
                                        <i class="fas fa-shipping-fast"></i>
                                    </div>
                                    <div class="step-details">
                                        <h4>Out for Delivery</h4>
                                        <p>Your order is on its way</p>
                                    </div>
                                </div>
                                <div class="status-step <?php echo $delivery['delivery_status'] == 'delivered' ? 'active' : ''; ?>">
                                    <div class="step-icon">
                                        <i class="fas fa-check"></i>
                                    </div>
                                    <div class="step-details">
                                        <h4>Delivered</h4>
                                        <p>Your order has arrived</p>
                                    </div>
                                </div>
                            </div>
                            <div class="delivery-method">
                                <p><strong>Delivery Method:</strong> <?php echo $delivery['carrier']; ?></p>
                                <p><strong>Estimated Delivery:</strong> <?php echo date('F j, Y', strtotime($delivery['estimated_delivery_date'])); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="detail-section">
                    <h2><i class="fas fa-receipt"></i> Order Summary</h2>
                    <div class="order-summary">
                        <div class="summary-row">
                            <span>Subtotal (<?php echo count($order_items); ?> items)</span>
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
            </div>

            <div class="confirmation-actions">
                <a href="product_list.php" class="btn btn-continue">
                    <i class="fas fa-shopping-bag"></i> Continue Shopping
                </a>
                <a href="order_history.php" class="btn btn-orders">
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

    <script>
        // Update cart count
        document.addEventListener('DOMContentLoaded', function() {
            // In a real app, you might fetch this from the server
            document.querySelector('.cart-count').textContent = '0';
        });
    </script>
</body>
</html>