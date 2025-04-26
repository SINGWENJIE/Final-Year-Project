<?php
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

// Fetch product details
$sql = "SELECT * FROM product WHERE prod_id = $product_id";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $product = $result->fetch_assoc();
    
    // Fetch related products (same category)
    $related_sql = "SELECT * FROM product WHERE category_id = {$product['category_id']} AND prod_id != $product_id LIMIT 4";
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
    <title><?php echo $product['prod_name']; ?> - Supermarket</title>
    <link rel="stylesheet" href="../user_assets/css/product_details.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>Supermarket</h1>
            <nav>
                <a href="product_list.php">Products</a>
                <a href="#">Wishlist</a>
                <a href="#">Cart</a>
            </nav>
        </div>
    </header>

    <main class="container">
        <div class="product-details">
            <div class="product-gallery">
                <div class="main-image">
                    <img src="../assets/uploads/<?php echo $product['prod_image']; ?>" alt="<?php echo $product['prod_name']; ?>">
                </div>
            </div>
            
            <div class="product-info">
                <h1><?php echo $product['prod_name']; ?></h1>
                <div class="price">RM <?php echo number_format($product['prod_price'], 2); ?></div>
                <div class="rating">
                    ★★★★☆ <span class="review-count">(24 reviews)</span>
                </div>
                
                <div class="description">
                    <h3>Description</h3>
                    <p><?php echo nl2br($product['prod_description']); ?></p>
                </div>
                
                <div class="stock">
                    <?php if ($product['stock'] > 0): ?>
                        <span class="in-stock">In Stock (<?php echo $product['stock']; ?> available)</span>
                    <?php else: ?>
                        <span class="out-of-stock">Out of Stock</span>
                    <?php endif; ?>
                </div>
                
                <div class="actions">
                    <div class="quantity-controls">
                        <button class="quantity-minus">-</button>
                        <input type="number" min="1" max="<?php echo $product['stock']; ?>" value="1" class="quantity">
                        <button class="quantity-plus">+</button>
                    </div>
                    
                    <div class="action-buttons">
                        <button class="add-to-cart" data-id="<?php echo $product['prod_id']; ?>">Add to Cart</button>
                        <button class="add-to-wishlist" data-id="<?php echo $product['prod_id']; ?>">Wishlist</button>
                        <button class="buy-now" data-id="<?php echo $product['prod_id']; ?>">Buy Now</button>
                    </div>
                </div>
                
                <div class="product-meta">
                    <div><strong>Category:</strong> 
                        <?php 
                        $cat_sql = "SELECT category_name FROM category WHERE category_id = {$product['category_id']}";
                        $cat_result = $conn->query($cat_sql);
                        if ($cat_result->num_rows > 0) {
                            $category = $cat_result->fetch_assoc();
                            echo $category['category_name'];
                        }
                        ?>
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
                        <img src="../assets/uploads/<?php echo $related['prod_image']; ?>" alt="<?php echo $related['prod_name']; ?>">
                        <h3><?php echo $related['prod_name']; ?></h3>
                        <div class="price">RM <?php echo number_format($related['prod_price'], 2); ?></div>
                    </a>
                </div>
                <?php endwhile; ?>
            </div>
        </section>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; 2023 Supermarket. All rights reserved.</p>
    </footer>

    <script src="../user_assets/js/product_details.js"></script>
</body>
</html>
<?php $conn->close(); ?>