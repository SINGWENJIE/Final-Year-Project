<?php
// Start session and check if user is logged in
session_start();
require_once 'db_connection.php'; // Your database connection file

// Get user ID from session
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    header("Location: login.php");
    exit();
}

// Get cart items for the user
$cart_items = [];
$subtotal = 0;
$delivery_fee = 5.00; // Fixed delivery fee as requested

// Get user's cart
$cart_query = "SELECT c.CART_ID FROM cart c WHERE c.user_id = ?";
$cart_stmt = $conn->prepare($cart_query);
$cart_stmt->bind_param("i", $user_id);
$cart_stmt->execute();
$cart_result = $cart_stmt->get_result();

if ($cart_result->num_rows > 0) {
    $cart = $cart_result->fetch_assoc();
    $cart_id = $cart['CART_ID'];
    
    // Get cart items with product details
    $items_query = "SELECT ci.*, p.prod_name, p.prod_price, p.prod_image 
                    FROM cart_items ci 
                    JOIN product p ON ci.prod_id = p.prod_id 
                    WHERE ci.CART_ID = ?";
    $items_stmt = $conn->prepare($items_query);
    $items_stmt->bind_param("i", $cart_id);
    $items_stmt->execute();
    $items_result = $items_stmt->get_result();
    
    while ($item = $items_result->fetch_assoc()) {
        $cart_items[] = $item;
        $subtotal += $item['prod_price'] * $item['QUANTITY'];
    }
}

// Calculate total
$total = $subtotal + $delivery_fee;

// Get related products (you might want to customize this query)
$related_products_query = "SELECT * FROM product ORDER BY RAND() LIMIT 4";
$related_products_result = $conn->query($related_products_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Shopping Cart - Supermarket</title>
    <link rel="stylesheet" href="../user_assets/css/cart.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>Supermarket</h1>
            <nav>
                <a href="product_list.php">Products</a>
                <a href="wishlist.php">Wishlist</a>
                <a href="cart.php">Cart</a>
            </nav>
        </div>
    </header>

    <main class="container">
        <h1>Your Shopping Cart</h1>
        
        <?php if (empty($cart_items)): ?>
            <div class="empty-cart">
                <p>Your cart is empty</p>
                <a href="product_list.php" class="continue-shopping">Continue Shopping</a>
            </div>
        <?php else: ?>
            <div class="cart-content">
                <div class="cart-items">
                    <?php foreach ($cart_items as $item): ?>
                    <div class="cart-item">
                        <div class="item-image">
                            <img src="../assets/uploads/<?php echo htmlspecialchars($item['prod_image']); ?>" alt="<?php echo htmlspecialchars($item['prod_name']); ?>">
                        </div>
                        <div class="item-details">
                            <h3><?php echo htmlspecialchars($item['prod_name']); ?></h3>
                            <div class="price">RM <?php echo number_format($item['prod_price'], 2); ?></div>
                            
                            <div class="item-actions">
                                <div class="quantity-controls">
                                    <button class="quantity-minus" data-id="<?php echo $item['prod_id']; ?>">-</button>
                                    <input type="number" class="quantity" value="<?php echo $item['QUANTITY']; ?>" min="1" data-id="<?php echo $item['prod_id']; ?>">
                                    <button class="quantity-plus" data-id="<?php echo $item['prod_id']; ?>">+</button>
                                </div>
                                <button class="remove-item" data-id="<?php echo $item['prod_id']; ?>">Remove</button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="order-summary">
                    <h2>Order Summary</h2>
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span>RM <?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Shipping</span>
                        <span>RM <?php echo number_format($delivery_fee, 2); ?></span>
                    </div>
                    <div class="summary-row total">
                        <span>Total</span>
                        <span>RM <?php echo number_format($total, 2); ?></span>
                    </div>
                    
                    <div class="promo-code">
                        <h3>Promo Code</h3>
                        <input type="text" placeholder="Enter promo code" id="promo-code-input">
                        <button id="apply-promo">Apply</button>
                    </div>
                    
                    <div class="cart-actions">
                        <a href="product_list.php" class="continue-shopping">Continue Shopping</a>
                        <a href="checkout.php" class="checkout-btn">Proceed to Checkout</a>
                    </div>
                </div>
            </div>
            
            <?php if ($related_products_result->num_rows > 0): ?>
            <section class="related-products">
                <h2>You Might Also Like</h2>
                <div class="related-grid">
                    <?php while($related = $related_products_result->fetch_assoc()): ?>
                    <div class="related-item">
                        <a href="product_details.php?id=<?php echo $related['prod_id']; ?>">
                            <img src="../assets/uploads/<?php echo htmlspecialchars($related['prod_image']); ?>" alt="<?php echo htmlspecialchars($related['prod_name']); ?>">
                            <h3><?php echo htmlspecialchars($related['prod_name']); ?></h3>
                            <div class="price">RM <?php echo number_format($related['prod_price'], 2); ?></div>
                        </a>
                    </div>
                    <?php endwhile; ?>
                </div>
            </section>
            <?php endif; ?>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; 2023 Supermarket. All rights reserved.</p>
    </footer>

    <script src="../user_assets/js/cart.js"></script>
</body>
</html>
<?php $conn->close(); ?>