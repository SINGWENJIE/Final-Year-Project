<?php
session_start();
require_once 'db_connection.php';

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle quantity updates
if (isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $product_id => $quantity) {
        if ($quantity <= 0) {
            unset($_SESSION['cart'][$product_id]);
        } else {
            $_SESSION['cart'][$product_id] = $quantity;
        }
    }
}

// Handle remove item
if (isset($_GET['remove'])) {
    $product_id = intval($_GET['remove']);
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
    }
    header("Location: cart.php");
    exit();
}

// Fetch cart products
$cart_items = [];
$subtotal = 0;

if (!empty($_SESSION['cart'])) {
    $product_ids = implode(',', array_keys($_SESSION['cart']));
    $sql = "SELECT * FROM product WHERE prod_id IN ($product_ids)";
    $result = $conn->query($sql);
    
    while ($product = $result->fetch_assoc()) {
        $quantity = $_SESSION['cart'][$product['prod_id']];
        $total_price = $product['prod_price'] * $quantity;
        $subtotal += $total_price;
        
        $cart_items[] = [
            'product' => $product,
            'quantity' => $quantity,
            'total_price' => $total_price
        ];
    }
}

// Delivery fee
$delivery_fee = 5.00;
$grand_total = $subtotal + $delivery_fee;

// Fetch related products
$related_sql = "SELECT * FROM product ORDER BY RAND() LIMIT 3";
$related_result = $conn->query($related_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Supermarket</title>
    <link rel="stylesheet" href="../user_assets/css/cart.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <main class="container">
        <h1 class="my-4">Your Shopping Cart</h1>
        
        <?php if (empty($cart_items)): ?>
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <p>Your cart is empty</p>
                <a href="product_list.php" class="btn btn-primary">Continue Shopping</a>
            </div>
        <?php else: ?>
            <div class="row">
                <div class="col-md-8">
                    <div class="cart-items">
                        <form method="post">
                            <?php foreach ($cart_items as $item): ?>
                                <div class="cart-item">
                                    <div class="item-image">
                                        <img src="../assets/uploads/<?php echo $item['product']['prod_image']; ?>" 
                                             alt="<?php echo $item['product']['prod_name']; ?>">
                                    </div>
                                    <div class="item-details">
                                        <h3><?php echo $item['product']['prod_name']; ?></h3>
                                        <div class="price">RM <?php echo number_format($item['product']['prod_price'], 2); ?></div>
                                        <div class="quantity-control">
                                            <input type="number" name="quantity[<?php echo $item['product']['prod_id']; ?>]" 
                                                   value="<?php echo $item['quantity']; ?>" min="1" 
                                                   max="<?php echo $item['product']['stock']; ?>">
                                            <a href="cart.php?remove=<?php echo $item['product']['prod_id']; ?>" class="remove-item">
                                                <i class="fas fa-trash"></i> Remove
                                            </a>
                                        </div>
                                    </div>
                                    <div class="item-total">
                                        RM <?php echo number_format($item['total_price'], 2); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <div class="cart-actions">
                                <a href="product_list.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left"></i> Continue Shopping
                                </a>
                                <button type="submit" name="update_cart" class="btn btn-outline-primary">
                                    <i class="fas fa-sync-alt"></i> Update Cart
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="order-summary">
                        <h3>Order Summary</h3>
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span>RM <?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Delivery Fee</span>
                            <span>RM <?php echo number_format($delivery_fee, 2); ?></span>
                        </div>
                        <div class="summary-total">
                            <span>Total</span>
                            <span>RM <?php echo number_format($grand_total, 2); ?></span>
                        </div>
                        
                        <div class="promo-code">
                            <h4>Promo Code</h4>
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Enter promo code">
                                <button class="btn btn-outline-secondary" type="button">Apply</button>
                            </div>
                        </div>
                        
                        <a href="checkout.php" class="btn btn-primary btn-checkout">
                            Proceed to Checkout
                        </a>
                    </div>
                </div>
            </div>
            
            <?php if ($related_result->num_rows > 0): ?>
            <section class="related-products">
                <h2>You Might Also Like</h2>
                <div class="related-grid">
                    <?php while($related = $related_result->fetch_assoc()): ?>
                    <div class="related-item">
                        <a href="product_details.php?id=<?php echo $related['prod_id']; ?>">
                            <img src="../assets/uploads/<?php echo $related['prod_image']; ?>" 
                                 alt="<?php echo $related['prod_name']; ?>">
                            <h3><?php echo $related['prod_name']; ?></h3>
                            <div class="price">RM <?php echo number_format($related['prod_price'], 2); ?></div>
                        </a>
                    </div>
                    <?php endwhile; ?>
                </div>
            </section>
            <?php endif; ?>
        <?php endif; ?>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../user_assets/js/cart.js"></script>
</body>
</html>
<?php $conn->close(); ?>