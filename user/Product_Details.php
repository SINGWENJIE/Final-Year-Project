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

// Get product ID from URL
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch product details with category name using JOIN
$sql = "SELECT p.*, c.category_name 
        FROM product p
        JOIN category c ON p.category_id = c.category_id
        WHERE p.prod_id = $product_id";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $product = $result->fetch_assoc();
    
    // Fetch related products (same category)
    $related_sql = "SELECT * FROM product 
                   WHERE category_id = {$product['category_id']} 
                   AND prod_id != $product_id 
                   LIMIT 4";
    $related_result = $conn->query($related_sql);
} else {
    // Product not found
    header("Location: product_list.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['prod_name']); ?> - Supermarket</title>
    <link rel="stylesheet" href="../user_assets/css/product_details.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="container">
            <h1><a href="product_list.php">Supermarket</a></h1>
            <nav>
                <a href="product_list.php"><i class="fas fa-store"></i> Products</a>
                <a href="#"><i class="fas fa-heart"></i> Wishlist</a>
                <a href="#"><i class="fas fa-shopping-cart"></i> Cart</a>
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
            <a href="product_list.php?category=<?php echo $product['category_id']; ?>">
                <?php echo htmlspecialchars($product['category_name']); ?>
            </a> &gt; 
            <span><?php echo htmlspecialchars($product['prod_name']); ?></span>
        </div>

        <div class="product-details">
            <div class="product-gallery">
                <div class="main-image">
                    <img src="../assets/uploads/<?php echo htmlspecialchars($product['prod_image']); ?>" 
                         alt="<?php echo htmlspecialchars($product['prod_name']); ?>">
                </div>
            </div>
            
            <div class="product-info">
                <h1><?php echo htmlspecialchars($product['prod_name']); ?></h1>
                
                <div class="price-section">
                    <div class="price">RM <?php echo number_format($product['prod_price'], 2); ?></div>
                    <?php if ($product['prod_price'] > 20): ?>
                        <div class="save-badge">Save 5%</div>
                    <?php endif; ?>
                </div>
                
                <div class="rating">
                    <div class="stars">
                        ★★★★☆
                    </div>
                    <a href="#reviews" class="review-count">24 reviews</a>
                </div>
                
                <div class="stock">
                    <?php if ($product['stock'] > 0): ?>
                        <span class="in-stock"><i class="fas fa-check-circle"></i> In Stock (<?php echo $product['stock']; ?> available)</span>
                    <?php else: ?>
                        <span class="out-of-stock"><i class="fas fa-times-circle"></i> Out of Stock</span>
                    <?php endif; ?>
                </div>
                
                <div class="description">
                    <h3>Description</h3>
                    <p><?php echo nl2br(htmlspecialchars($product['prod_description'])); ?></p>
                </div>
                
                <div class="actions">
                    <div class="quantity-controls">
                        <button class="quantity-minus"><i class="fas fa-minus"></i></button>
                        <input type="number" min="1" max="<?php echo $product['stock']; ?>" value="1" class="quantity">
                        <button class="quantity-plus"><i class="fas fa-plus"></i></button>
                    </div>
                    
                    <div class="action-buttons">
                        <button class="add-to-cart" data-id="<?php echo $product['prod_id']; ?>">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </button>
                        <button class="buy-now" data-id="<?php echo $product['prod_id']; ?>">
                            <i class="fas fa-bolt"></i> Buy Now
                        </button>
                        <button class="add-to-wishlist" data-id="<?php echo $product['prod_id']; ?>">
                            <i class="far fa-heart"></i> Wishlist
                        </button>
                    </div>
                </div>
                
                <div class="product-meta">
                    <div><strong>Category:</strong> 
                        <a href="product_list.php?category=<?php echo $product['category_id']; ?>">
                            <?php echo htmlspecialchars($product['category_name']); ?>
                        </a>
                    </div>
                    <div><strong>Product ID:</strong> <?php echo $product['prod_id']; ?></div>
                </div>
            </div>
        </div>
        
        <?php if ($related_result->num_rows > 0): ?>
        <section class="related-products">
            <h2>You May Also Like</h2>
            <div class="related-grid">
                <?php while($related = $related_result->fetch_assoc()): ?>
                <div class="related-item">
                    <a href="product_details.php?id=<?php echo $related['prod_id']; ?>">
                        <div class="related-image">
                            <img src="../assets/uploads/<?php echo htmlspecialchars($related['prod_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($related['prod_name']); ?>">
                        </div>
                        <h3><?php echo htmlspecialchars($related['prod_name']); ?></h3>
                        <div class="price">RM <?php echo number_format($related['prod_price'], 2); ?></div>
                    </a>
                </div>
                <?php endwhile; ?>
            </div>
        </section>
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

    <script src="../user_assets/js/product_details.js"></script>
</body>
</html>
<?php $conn->close(); ?>