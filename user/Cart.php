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

// Get or create cart for user
$cart = getOrCreateCart($conn, $user_id);

// Handle remove item action
if (isset($_GET['remove']) && is_numeric($_GET['remove'])) {
    removeItemFromCart($conn, $cart['CART_ID'], $_GET['remove']);
}

// Handle quantity update
if (isset($_POST['update_quantity']) && is_array($_POST['quantity'])) {
    updateCartQuantities($conn, $cart['CART_ID'], $_POST['quantity']);
}

// Get cart items with product details
$cart_items = getCartItems($conn, $cart['CART_ID']);

// Calculate totals
$subtotal = calculateSubtotal($cart_items);
$delivery_fee = 5.00; // Fixed delivery fee
$total = $subtotal + $delivery_fee;

// Functions
function getOrCreateCart($conn, $user_id) {
    // Check if user has an active cart
    $sql = "SELECT * FROM cart WHERE user_id = ? ORDER BY CREATED_AT DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    // Create new cart
    $sql = "INSERT INTO cart (user_id) VALUES (?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    
    return [
        'CART_ID' => $stmt->insert_id,
        'user_id' => $user_id,
        'CREATED_AT' => date('Y-m-d H:i:s'),
        'UPDATED_AT' => date('Y-m-d H:i:s')
    ];
}

function getCartItems($conn, $cart_id) {
    $sql = "SELECT ci.*, p.prod_name, p.prod_price, p.prod_image, p.stock 
            FROM cart_items ci
            JOIN product p ON ci.prod_id = p.prod_id
            WHERE ci.CART_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $cart_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function removeItemFromCart($conn, $cart_id, $cart_item_id) {
    $sql = "DELETE FROM cart_items WHERE CART_ITEM_ID = ? AND CART_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $cart_item_id, $cart_id);
    $stmt->execute();
    
    // Redirect to prevent form resubmission
    header("Location: cart.php");
    exit();
}

function updateCartQuantities($conn, $cart_id, $quantities) {
    foreach ($quantities as $cart_item_id => $quantity) {
        $quantity = intval($quantity);
        if ($quantity > 0) {
            $sql = "UPDATE cart_items SET QUANTITY = ? WHERE CART_ITEM_ID = ? AND CART_ID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iii", $quantity, $cart_item_id, $cart_id);
            $stmt->execute();
        }
    }
    
    // Redirect to prevent form resubmission
    header("Location: cart.php");
    exit();
}

function calculateSubtotal($cart_items) {
    $subtotal = 0;
    foreach ($cart_items as $item) {
        $subtotal += $item['prod_price'] * $item['QUANTITY'];
    }
    return $subtotal;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Shopping Cart - Supermarket</title>
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
                <a href="cart.php" class="cart-link"><i class="fas fa-shopping-cart"></i> Cart 
                    <span class="cart-count"><?php echo count($cart_items); ?></span>
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

        <?php if (empty($cart_items)): ?>
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <h2>Your cart is empty</h2>
                <p>Looks like you haven't added any items to your cart yet.</p>
                <a href="product_list.php" class="btn btn-primary">Continue Shopping</a>
            </div>
        <?php else: ?>
            <form action="cart.php" method="post" class="cart-form">
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
                                <input type="number" name="quantity[<?php echo $item['CART_ITEM_ID']; ?>]" 
                                       min="1" max="<?php echo $item['stock']; ?>" 
                                       value="<?php echo $item['QUANTITY']; ?>" class="quantity-input">
                            </div>
                            <div class="item-total">
                                RM <?php echo number_format($item['prod_price'] * $item['QUANTITY'], 2); ?>
                            </div>
                            <div class="item-actions">
                                <a href="cart.php?remove=<?php echo $item['CART_ITEM_ID']; ?>" class="remove-btn">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="cart-actions">
                    <a href="product_list.php" class="btn continue-shopping">
                        <i class="fas fa-arrow-left"></i> Continue Shopping
                    </a>
                    <button type="submit" name="update_quantity" class="btn update-cart">
                        <i class="fas fa-sync-alt"></i> Update Cart
                    </button>
                </div>

                <div class="cart-summary">
                    <div class="summary-card">
                        <h3>Order Summary</h3>
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span>RM <?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Delivery Fee</span>
                            <span>RM <?php echo number_format($delivery_fee, 2); ?></span>
                        </div>
                        <div class="summary-row total">
                            <span>Total</span>
                            <span>RM <?php echo number_format($total, 2); ?></span>
                        </div>
                        <a href="checkout.php" class="btn btn-checkout">
                            Proceed to Checkout <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </form>
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