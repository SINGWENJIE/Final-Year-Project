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

// Fetch wishlist items with product details
$sql = "SELECT p.* 
        FROM wishlist w
        JOIN product p ON w.prod_id = p.prod_id
        WHERE w.user_id = $user_id
        ORDER BY w.wishlist_id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist - Supermarket</title>
    <link rel="stylesheet" href="../user_assets/css/wishlist.css">
    <link rel="stylesheet" href="../user_assets/css/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <main class="container">
        <div class="breadcrumb">
            <a href="product_list.php">Products</a> &gt; 
            <span>My Wishlist</span>
        </div>

        <h1 class="page-title">My Wishlist</h1>

        <?php if ($result->num_rows > 0): ?>
        <div class="wishlist-items">
            <?php while($product = $result->fetch_assoc()): ?>
            <div class="wishlist-item" data-id="<?php echo $product['prod_id']; ?>">
                <div class="item-image">
                    <a href="product_details.php?id=<?php echo $product['prod_id']; ?>">
                        <img src="../assets/uploads/<?php echo htmlspecialchars($product['prod_image']); ?>" 
                             alt="<?php echo htmlspecialchars($product['prod_name']); ?>">
                    </a>
                </div>
                <div class="item-details">
                    <h3>
                        <a href="product_details.php?id=<?php echo $product['prod_id']; ?>">
                            <?php echo htmlspecialchars($product['prod_name']); ?>
                        </a>
                    </h3>
                    <div class="price">RM <?php echo number_format($product['prod_price'], 2); ?></div>
                    <div class="stock">
                        <?php if ($product['stock'] > 0): ?>
                            <span class="in-stock"><i class="fas fa-check-circle"></i> In Stock</span>
                        <?php else: ?>
                            <span class="out-of-stock"><i class="fas fa-times-circle"></i> Out of Stock</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="item-actions">
                    <button class="remove-from-wishlist" data-id="<?php echo $product['prod_id']; ?>">
                        <i class="fas fa-trash"></i> Remove
                    </button>
                    <button class="add-to-cart" data-id="<?php echo $product['prod_id']; ?>">
                        <i class="fas fa-shopping-cart"></i> Add to Cart
                    </button>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php else: ?>
        <div class="empty-wishlist">
            <div class="empty-icon">
                <i class="far fa-heart"></i>
            </div>
            <h2>Your wishlist is empty</h2>
            <p>Looks like you haven't added any items to your wishlist yet.</p>
            <a href="product_list.php" class="btn btn-primary">Browse Products</a>
        </div>
        <?php endif; ?>
    </main>

    <?php include 'footer.php'; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Remove from wishlist functionality
        document.querySelectorAll('.remove-from-wishlist').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-id');
                const wishlistItem = this.closest('.wishlist-item');
                
                // Show loading state
                const originalHTML = this.innerHTML;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Removing...';
                this.disabled = true;
                
                // AJAX request to remove from wishlist
                fetch('remove_from_wishlist.php', {
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
                        // Remove item from DOM
                        wishlistItem.style.opacity = '0';
                        setTimeout(() => {
                            wishlistItem.remove();
                            
                            // Update item count
                            const itemCount = document.querySelectorAll('.wishlist-item').length;
                            document.querySelector('.wishlist-header p').textContent = 
                                itemCount + ' item(s)';
                                
                            // If no items left, show empty state
                            if (itemCount === 0) {
                                document.querySelector('.wishlist-items').innerHTML = `
                                    <div class="empty-wishlist">
                                        <i class="far fa-heart"></i>
                                        <h2>Your wishlist is empty</h2>
                                        <p>You haven't added any items to your wishlist yet.</p>
                                        <a href="product_list.php" class="browse-btn">Browse Products</a>
                                    </div>
                                `;
                            }
                        }, 300);
                    } else {
                        alert(data.message || 'Failed to remove item');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                })
                .finally(() => {
                    this.innerHTML = originalHTML;
                    this.disabled = false;
                });
            });
        });
        
        // Add to cart from wishlist
        document.querySelectorAll('.add-to-cart').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-id');
                
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
                        quantity: 1
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Added to cart successfully!');
                        updateCartCount(data.cart_count);
                    } else {
                        alert(data.message || 'Failed to add to cart');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                })
                .finally(() => {
                    this.innerHTML = '<i class="fas fa-shopping-cart"></i> Add to Cart';
                    this.disabled = false;
                });
            });
        });
        
        // Update cart count in header
        function updateCartCount(count) {
            const cartCountElements = document.querySelectorAll('.cart-count');
            cartCountElements.forEach(el => {
                el.textContent = count;
            });
        }
    });
    </script>
</body>
</html>
<?php $conn->close(); ?>