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

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

body {
    background-color: #f8f9fa;
    color: #333;
    line-height: 1.6;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

/* Header Styles */
header {
    background-color: #2c3e50;
    color: white;
    padding: 1rem 0;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

header .container {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

header h1 a {
    color: white;
    text-decoration: none;
    font-size: 1.5rem;
}

header nav {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

header nav a {
    color: white;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.95rem;
    transition: color 0.3s;
}

header nav a:hover {
    color: #f39c12;
}

header nav a.active {
    color: #f39c12;
    font-weight: bold;
}

.cart-count {
    background-color: #e74c3c;
    color: white;
    border-radius: 50%;
    font-size: 0.7rem;
    padding: 0.2rem 0.4rem;
    margin-left: 0.3rem;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-left: 1rem;
}

.logout-btn {
    color: #f39c12;
    text-decoration: none;
    font-size: 0.85rem;
}

.logout-btn:hover {
    text-decoration: underline;
}

</style>