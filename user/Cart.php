<?php
session_start();
require_once '../db_connection.php'; // Your database connection file

// Check if user is logged in (optional)
$user_id = $_SESSION['user_id'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../user/css/Cart.css">
</head>
<body>
    <!-- Header with cart icon -->
    <header>
        <div class="logo">My Store</div>
        <div class="cart-icon-container">
            <i class="fas fa-shopping-cart" id="cartIcon"></i>
            <span class="cart-count">
                <?php 
                if ($user_id) {
                    $stmt = $pdo->prepare("SELECT SUM(quantity) FROM cart_item ci JOIN cart c ON ci.cart_id = c.cart_id WHERE c.user_id = ? AND c.is_active = 1");
                    $stmt->execute([$user_id]);
                    echo $stmt->fetchColumn() ?? 0;
                } else {
                    echo count($_SESSION['cart'] ?? []);
                }
                ?>
            </span>
        </div>
    </header>

    <!-- Shopping Cart Sidebar -->
    <div class="cart-overlay" id="cartOverlay"></div>
    <div class="cart-sidebar" id="cartSidebar">
        <div class="cart-header">
            <h2>Your Cart</h2>
            <button class="close-cart" id="closeCart">&times;</button>
        </div>
        <div class="cart-items" id="cartItems">
            <?php if ($user_id): ?>
                <!-- For logged in users - load from database -->
                <?php
                $stmt = $pdo->prepare("
                    SELECT p.prod_id, p.prod_name, p.prod_price, p.prod_image, ci.quantity, ci.cart_item_id 
                    FROM cart_item ci 
                    JOIN product p ON ci.prod_id = p.prod_id 
                    JOIN cart c ON ci.cart_id = c.cart_id 
                    WHERE c.user_id = ? AND c.is_active = 1
                ");
                $stmt->execute([$user_id]);
                $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (empty($cartItems)): ?>
                    <div class="empty-cart">Your cart is empty</div>
                <?php else: ?>
                    <?php foreach ($cartItems as $item): ?>
                        <div class="cart-item" data-id="<?= $item['prod_id'] ?>">
                            <img src="<?= htmlspecialchars($item['prod_image']) ?>" alt="<?= htmlspecialchars($item['prod_name']) ?>" class="cart-item-img">
                            <div class="cart-item-details">
                                <h3 class="cart-item-title"><?= htmlspecialchars($item['prod_name']) ?></h3>
                                <div class="cart-item-price">RM<?= number_format($item['prod_price'], 2) ?></div>
                                <button class="cart-item-remove" data-cart-item-id="<?= $item['cart_item_id'] ?>">Remove</button>
                                <div class="cart-item-quantity">
                                    <button class="quantity-btn minus">-</button>
                                    <span class="quantity-value"><?= $item['quantity'] ?></span>
                                    <button class="quantity-btn plus">+</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php else: ?>
                <!-- For guests - load from session -->
                <?php if (empty($_SESSION['cart'])): ?>
                    <div class="empty-cart">Your cart is empty</div>
                <?php else: ?>
                    <?php foreach ($_SESSION['cart'] as $id => $item): ?>
                        <div class="cart-item" data-id="<?= $id ?>">
                            <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="cart-item-img">
                            <div class="cart-item-details">
                                <h3 class="cart-item-title"><?= htmlspecialchars($item['name']) ?></h3>
                                <div class="cart-item-price">RM<?= number_format($item['price'], 2) ?></div>
                                <button class="cart-item-remove">Remove</button>
                                <div class="cart-item-quantity">
                                    <button class="quantity-btn minus">-</button>
                                    <span class="quantity-value"><?= $item['quantity'] ?></span>
                                    <button class="quantity-btn plus">+</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <div class="cart-footer">
            <div class="cart-total">
                <span>Total:</span>
                <span class="total-price">
                    RM<?php 
                    if ($user_id) {
                        $stmt = $pdo->prepare("
                            SELECT SUM(p.prod_price * ci.quantity) 
                            FROM cart_item ci 
                            JOIN product p ON ci.prod_id = p.prod_id 
                            JOIN cart c ON ci.cart_id = c.cart_id 
                            WHERE c.user_id = ? AND c.is_active = 1
                        ");
                        $stmt->execute([$user_id]);
                        echo number_format($stmt->fetchColumn() ?? 0, 2);
                    } else {
                        $total = 0;
                        foreach ($_SESSION['cart'] ?? [] as $item) {
                            $total += $item['price'] * $item['quantity'];
                        }
                        echo number_format($total, 2);
                    }
                    ?>
                </span>
            </div>
            <button class="checkout-btn">Checkout</button>
        </div>
    </div>

    <script src="../user/js/Cart.js"></script>
</body>
</html>