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

// Get cart items with product details
$sql = "SELECT ci.CART_ITEM_ID, ci.QUANTITY, p.prod_id, p.prod_name, p.prod_price, p.prod_image, p.stock 
        FROM cart_items ci
        JOIN product p ON ci.prod_id = p.prod_id
        JOIN cart c ON ci.CART_ID = c.CART_ID
        WHERE c.user_id = $user_id
        ORDER BY ci.CART_ITEM_ID DESC";
$result = $conn->query($sql);

$cart_items = [];
$subtotal = 0;
$item_count = 0;

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $row['total_price'] = $row['prod_price'] * $row['QUANTITY'];
        $subtotal += $row['total_price'];
        $item_count += $row['QUANTITY'];
        $cart_items[] = $row;
    }
}

// Calculate total to subtotal
$total = $subtotal;

// Check for empty cart
$is_cart_empty = empty($cart_items);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Supermarket</title>
    <link rel="stylesheet" href="../user_assets/css/cart.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="container">
            <h1><a href="product_list.php">Supermarket</a></h1>
            <nav>
                <a href="product_list.php"><i class="fas fa-store"></i> Products</a>
                <a href="#"><i class="fas fa-heart"></i> Wishlist</a>
                <a href="cart.php" class="active"><i class="fas fa-shopping-cart"></i> Cart 
                    <?php if (!$is_cart_empty): ?>
                        <span class="cart-count"><?php echo $item_count; ?></span>
                    <?php endif; ?>
                </a>
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
            <span>Shopping Cart</span>
        </div>

        <h1 class="page-title">Your Shopping Cart</h1>

        <?php if ($is_cart_empty): ?>
            <div class="empty-cart">
                <div class="empty-cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h2>Your cart is empty</h2>
                <p>Looks like you haven't added any items to your cart yet.</p>
                <a href="product_list.php" class="btn btn-primary">Continue Shopping</a>
            </div>
        <?php else: ?>
            <div class="cart-layout">
                <div class="cart-items">
                    <div class="cart-header">
                        <div class="header-product">Product</div>
                        <div class="header-price">Price</div>
                        <div class="header-quantity">Quantity</div>
                        <div class="header-total">Total</div>
                        <div class="header-actions">Actions</div>
                    </div>

                    <?php foreach ($cart_items as $item): ?>
                    <div class="cart-item" data-id="<?php echo $item['CART_ITEM_ID']; ?>">
                        <div class="item-product">
                            <div class="product-image">
                                <img src="../assets/uploads/<?php echo htmlspecialchars($item['prod_image']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['prod_name']); ?>">
                            </div>
                            <div class="product-details">
                                <h3><?php echo htmlspecialchars($item['prod_name']); ?></h3>
                                <div class="product-id">ID: <?php echo $item['prod_id']; ?></div>
                                <div class="stock-status">
                                    <?php if ($item['stock'] > 0): ?>
                                        <span class="in-stock"><i class="fas fa-check-circle"></i> In Stock</span>
                                    <?php else: ?>
                                        <span class="out-of-stock"><i class="fas fa-times-circle"></i> Out of Stock</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="item-price">
                            RM <?php echo number_format($item['prod_price'], 2); ?>
                        </div>
                        <div class="item-quantity">
                            <button class="quantity-btn minus" data-id="<?php echo $item['CART_ITEM_ID']; ?>">
                                <i class="fas fa-minus"></i>
                            </button>
                            <input type="number" class="quantity-input" 
                                   value="<?php echo $item['QUANTITY']; ?>" 
                                   min="1" max="<?php echo $item['stock']; ?>"
                                   data-id="<?php echo $item['CART_ITEM_ID']; ?>"
                                   data-price="<?php echo $item['prod_price']; ?>">
                            <button class="quantity-btn plus" data-id="<?php echo $item['CART_ITEM_ID']; ?>">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        <div class="item-total" data-id="<?php echo $item['CART_ITEM_ID']; ?>">
                            RM <?php echo number_format($item['total_price'], 2); ?>
                        </div>
                        <div class="item-actions">
                            <button class="remove-btn" data-id="<?php echo $item['CART_ITEM_ID']; ?>">
                                <i class="fas fa-trash"></i> Remove
                            </button>
                            <button class="wishlist-btn" data-id="<?php echo $item['prod_id']; ?>">
                                <i class="far fa-heart"></i> Wishlist
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="cart-summary">
                    <h2>Order Summary</h2>
                    <div class="summary-row">
                        <span>Subtotal (<?php echo $item_count; ?> items)</span>
                        <span class="subtotal">RM <?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    <div class="summary-divider"></div>
                    <div class="summary-row total">
                        <span>Total</span>
                        <span class="total-amount">RM <?php echo number_format($total, 2); ?></span>
                    </div>
                    <button class="checkout-btn">Proceed to Checkout</button>
                    <a href="product_list.php" class="continue-shopping">
                        <i class="fas fa-arrow-left"></i> Continue Shopping
                    </a>
                </div>
            </div>
        <?php endif; ?>
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

    <script src="../user_assets/js/cart.js"></script>
</body>
</html>
<?php $conn->close(); ?>