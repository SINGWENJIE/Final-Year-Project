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
    <link rel="stylesheet" href="../user_assets/css/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Toast Notification Styles */
        .toast-notification {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #333;
            color: white;
            padding: 12px 24px;
            border-radius: 4px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 1000;
            opacity: 0;
            transition: opacity 0.3s ease;
            max-width: 90%;
            text-align: center;
        }

        .toast-notification.show {
            opacity: 1;
        }

        .toast-notification.success {
            background-color: #27ae60;
        }

        .toast-notification.error {
            background-color: #e74c3c;
        }

        .toast-notification.info {
            background-color: #3498db;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

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

    <?php include 'footer.php'; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Quantity controls
        const minusBtn = document.querySelector('.quantity-minus');
        const plusBtn = document.querySelector('.quantity-plus');
        const quantityInput = document.querySelector('.quantity');
        
        if (minusBtn && plusBtn && quantityInput) {
            minusBtn.addEventListener('click', function() {
                let value = parseInt(quantityInput.value);
                if (value > 1) {
                    quantityInput.value = value - 1;
                }
            });
            
            plusBtn.addEventListener('click', function() {
                let value = parseInt(quantityInput.value);
                let max = parseInt(quantityInput.getAttribute('max')) || 999;
                if (value < max) {
                    quantityInput.value = value + 1;
                } else {
                    showToast(`Maximum quantity (${max}) reached`);
                }
            });
            
            // Validate input manually
            quantityInput.addEventListener('change', function() {
                let value = parseInt(this.value);
                let max = parseInt(this.getAttribute('max')) || 999;
                
                if (isNaN(value)) {
                    this.value = 1;
                } else if (value < 1) {
                    this.value = 1;
                } else if (value > max) {
                    this.value = max;
                    showToast(`Maximum quantity is ${max}`);
                }
            });
        }
        
        // Add to Cart with AJAX
        const addToCartBtn = document.querySelector('.add-to-cart');
        if (addToCartBtn) {
            addToCartBtn.addEventListener('click', function() {
                const productId = this.getAttribute('data-id');
                const quantity = document.querySelector('.quantity').value;
                
                // Show loading state
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
                this.disabled = true;
                
                // AJAX request to add to cart
                fetch('add_to_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        product_id: productId,
                        quantity: quantity
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Added to cart successfully!', 'success');
                        updateCartCount(data.cart_count);
                    } else {
                        showToast(data.message || 'Failed to add to cart', 'error');
                    }
                })
                .catch(error => {
                    showToast('An error occurred. Please try again.', 'error');
                    console.error('Error:', error);
                })
                .finally(() => {
                    this.innerHTML = '<i class="fas fa-shopping-cart"></i> Add to Cart';
                    this.disabled = false;
                });
            });
        }

        // Buy Now button functionality
        const buyNowBtn = document.querySelector('.buy-now');
        if (buyNowBtn) {
            buyNowBtn.addEventListener('click', function() {
                const productId = this.getAttribute('data-id');
                const quantity = document.querySelector('.quantity').value;
            
                // Show loading state
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                this.disabled = true;
            
                // First add to cart via AJAX
                fetch('add_to_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        product_id: productId,
                        quantity: quantity,
                        buy_now: true
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = 'checkout.php';
                    } else {
                        showToast(data.message || 'Failed to process order', 'error');
                    }
                })
                .catch(error => {
                    showToast('An error occurred. Please try again.', 'error');
                    console.error('Error:', error);
                })
                .finally(() => {
                    this.innerHTML = '<i class="fas fa-bolt"></i> Buy Now';
                    this.disabled = false;
                });
            });
        }
        
        // Add to Wishlist with AJAX
        const addToWishlistBtn = document.querySelector('.add-to-wishlist');
        if (addToWishlistBtn) {
            addToWishlistBtn.addEventListener('click', function() {
                const productId = this.getAttribute('data-id');
                
                // Show loading state
                const originalHTML = this.innerHTML;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                this.disabled = true;
                
                // AJAX request to add to wishlist
                fetch('add_to_wishlist.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        product_id: productId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (data.action === 'added') {
                            this.innerHTML = '<i class="fas fa-heart"></i> Wishlist';
                            showToast('Added to wishlist!', 'success');
                        } else {
                            this.innerHTML = originalHTML;
                            showToast('Removed from wishlist', 'info');
                        }
                        this.style.color = data.action === 'added' ? '#e74c3c' : '';
                    } else {
                        showToast(data.message || 'Failed to update wishlist', 'error');
                        this.innerHTML = originalHTML;
                    }
                })
                .catch(error => {
                    showToast('An error occurred. Please try again.', 'error');
                    console.error('Error:', error);
                    this.innerHTML = originalHTML;
                })
                .finally(() => {
                    this.disabled = false;
                });
            });
        }
        
        // Check if product is in wishlist on page load
        checkWishlistStatus();
        
        // Toast notification function
        function showToast(message, type = 'info') {
            const toast = document.createElement('div');
            toast.className = `toast-notification ${type}`;
            toast.textContent = message;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.classList.add('show');
            }, 10);
            
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => {
                    document.body.removeChild(toast);
                }, 300);
            }, 3000);
        }
        
        // Update cart count in header
        function updateCartCount(count) {
            const cartCountElements = document.querySelectorAll('.cart-count');
            cartCountElements.forEach(el => {
                el.textContent = count;
            });
        }
        
        // Check wishlist status for this product
        function checkWishlistStatus() {
            const productId = document.querySelector('.add-to-wishlist')?.getAttribute('data-id');
            if (!productId) return;
            
            fetch(`check_wishlist.php?product_id=${productId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.in_wishlist) {
                        const btn = document.querySelector('.add-to-wishlist');
                        btn.innerHTML = '<i class="fas fa-heart"></i> Wishlist';
                        btn.style.color = '#e74c3c';
                    }
                })
                .catch(error => {
                    console.error('Error checking wishlist:', error);
                });
        }
    });
    
    </script>
</body>
</html>
<?php $conn->close(); ?>