<?php
require_once __DIR__ . '/../db_connection.php';

// Check if product ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: products.php");
    exit();
}

$product_id = intval($_GET['id']);

// Fetch product details
$product_query = "SELECT p.*, c.category_name
    FROM product p
    JOIN category c ON p.category_id = c.category_id
    WHERE p.prod_id = ?";
$stmt = $conn->prepare($product_query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    header("Location: products.php");
    exit();
}


// Fetch related products (same category)
$related_query = "SELECT prod_id, prod_name, prod_price, prod_image 
                  FROM product 
                  WHERE category_id = ? AND prod_id != ? 
                  LIMIT 4";
$stmt = $conn->prepare($related_query);
$stmt->bind_param("ii", $product['category_id'], $product_id);
$stmt->execute();
$related_products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$page_title = $product['prod_name'] . " | GoGo Supermarket";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="../user/css/Product_Detail.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <main class="product-details-container">
        <div class="breadcrumb">
            <a href="index.php">Home</a> &gt; 
            <a href="products.php?category=<?php echo $product['category_id']; ?>"><?php echo htmlspecialchars($product['category_name']); ?></a> &gt; 
            <span><?php echo htmlspecialchars($product['prod_name']); ?></span>
        </div>

        <div class="product-main">
            <div class="product-gallery">
                <div class="main-image">
                    <img src="<?php echo htmlspecialchars($product['prod_image'] ? 'uploads/products/'.$product['prod_image'] : 'images/product-placeholder.jpg'); ?>" 
                         alt="<?php echo htmlspecialchars($product['prod_name']); ?>" id="mainProductImage">
                </div>
            </div>

            <div class="product-info">
                <h1><?php echo htmlspecialchars($product['prod_name']); ?></h1>
                
                <div class="product-meta">
                    <span class="category">Category: <?php echo htmlspecialchars($product['category_name']); ?></span>
                    <div class="rating">
                        <?php
                        // Calculate average rating (you would fetch this from your database)
                        $rating_query = "SELECT AVG(rating) as avg_rating FROM feedback WHERE prod_id = ?";
                        $stmt = $conn->prepare($rating_query);
                        $stmt->bind_param("i", $product_id);
                        $stmt->execute();
                        $rating_result = $stmt->get_result();
                        $avg_rating = $rating_result->fetch_assoc()['avg_rating'];
                        $avg_rating = $avg_rating ? round($avg_rating, 1) : 0;
                        
                        // Display stars
                        $full_stars = floor($avg_rating);
                        $has_half_star = ($avg_rating - $full_stars) >= 0.5;
                        $empty_stars = 5 - $full_stars - ($has_half_star ? 1 : 0);
                        
                        for ($i = 0; $i < $full_stars; $i++) {
                            echo '<i class="fas fa-star"></i>';
                        }
                        if ($has_half_star) {
                            echo '<i class="fas fa-star-half-alt"></i>';
                        }
                        for ($i = 0; $i < $empty_stars; $i++) {
                            echo '<i class="far fa-star"></i>';
                        }
                        ?>
                        <span class="rating-value"><?php echo $avg_rating; ?> (<?php 
                            // Count number of reviews
                            $count_query = "SELECT COUNT(*) as review_count FROM feedback WHERE prod_id = ?";
                            $stmt = $conn->prepare($count_query);
                            $stmt->bind_param("i", $product_id);
                            $stmt->execute();
                            $count_result = $stmt->get_result();
                            echo $count_result->fetch_assoc()['review_count'] ?? 0;
                        ?> reviews)</span>
                    </div>
                </div>

                <div class="price-section">
                    <span class="current-price">RM <?php echo number_format($product['prod_price'], 2); ?></span>
                </div>

                <div class="availability">
                    <?php if ($product['prod_quantity'] > 0): ?>
                        <span class="in-stock"><i class="fas fa-check-circle"></i> In Stock (<?php echo $product['prod_quantity']; ?> available)</span>
                    <?php else: ?>
                        <span class="out-of-stock"><i class="fas fa-times-circle"></i> Out of Stock</span>
                    <?php endif; ?>
                </div>

                <div class="product-description">
                    <h3>Description</h3>
                    <p><?php echo nl2br(htmlspecialchars($product['prod_description'])); ?></p>
                </div>

                <form id="addToCartForm" class="add-to-cart-form">
                    <input type="hidden" name="product_id" value="<?php echo $product['prod_id']; ?>">
                    
                    <div class="quantity-selector">
                        <button type="button" class="quantity-btn minus" aria-label="Decrease quantity">-</button>
                        <input type="number" name="quantity" value="1" min="1" max="<?php echo $product['prod_quantity']; ?>" class="quantity-input">
                        <button type="button" class="quantity-btn plus" aria-label="Increase quantity">+</button>
                    </div>

                    <button type="submit" class="add-to-cart-btn" <?php echo $product['prod_quantity'] <= 0 ? 'disabled' : ''; ?>>
                        <i class="fas fa-shopping-cart"></i> Add to Cart
                    </button>

                    <div class="wishlist-btn-container">
                        <button type="button" class="wishlist-btn" id="addToWishlist" data-product-id="<?php echo $product['prod_id']; ?>">
                            <i class="far fa-heart"></i> Add to Wishlist
                        </button>
                    </div>
                </form>

                <div class="product-meta-footer">
                    <div class="meta-item">
                        <i class="fas fa-shield-alt"></i>
                        <span>Quality Guarantee</span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-truck"></i>
                        <span>Free Shipping on orders over RM50</span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-undo"></i>
                        <span>Easy Returns</span>
                    </div>
                </div>
            </div>
        </div>

        <section class="product-tabs">
            <div class="tab-header">
                <button class="tab-link active" data-tab="description">Description</button>
                <button class="tab-link" data-tab="reviews">Reviews</button>
                <button class="tab-link" data-tab="shipping">Shipping & Returns</button>
            </div>

            <div class="tab-content active" id="description">
                <h3>Product Details</h3>
                <p><?php echo nl2br(htmlspecialchars($product['prod_description'])); ?></p>
            </div>

            <div class="tab-content" id="reviews">
                <h3>Customer Reviews</h3>
                <div class="reviews-container">
                    <?php
                    // Fetch reviews for this product
                    $reviews_query = "SELECT f.*, u.user_name 
                                     FROM feedback f 
                                     JOIN users u ON f.user_id = u.user_id 
                                     WHERE f.prod_id = ? AND f.is_approved = 1 
                                     ORDER BY f.feedback_date DESC";
                    $stmt = $conn->prepare($reviews_query);
                    $stmt->bind_param("i", $product_id);
                    $stmt->execute();
                    $reviews = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

                    if (count($reviews) > 0): 
                        foreach ($reviews as $review): ?>
                            <div class="review-item">
                                <div class="review-header">
                                    <span class="reviewer-name"><?php echo htmlspecialchars($review['user_name']); ?></span>
                                    <div class="review-rating">
                                        <?php 
                                        for ($i = 1; $i <= 5; $i++) {
                                            if ($i <= $review['rating']) {
                                                echo '<i class="fas fa-star"></i>';
                                            } else {
                                                echo '<i class="far fa-star"></i>';
                                            }
                                        }
                                        ?>
                                    </div>
                                    <span class="review-date"><?php echo date('F j, Y', strtotime($review['feedback_date'])); ?></span>
                                </div>
                                <div class="review-comment">
                                    <p><?php echo htmlspecialchars($review['comment']); ?></p>
                                </div>
                                <?php if ($review['admin_response']): ?>
                                    <div class="admin-response">
                                        <strong>Admin Response:</strong>
                                        <p><?php echo htmlspecialchars($review['admin_response']); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; 
                    else: ?>
                        <p class="no-reviews">No reviews yet. Be the first to review this product!</p>
                    <?php endif; ?>
                </div>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="add-review-form">
                        <h4>Write a Review</h4>
                        <form id="reviewForm">
                            <input type="hidden" name="product_id" value="<?php echo $product['prod_id']; ?>">
                            <div class="form-group">
                                <label for="reviewRating">Rating</label>
                                <div class="star-rating">
                                    <input type="radio" id="star5" name="rating" value="5" required>
                                    <label for="star5"><i class="fas fa-star"></i></label>
                                    <input type="radio" id="star4" name="rating" value="4">
                                    <label for="star4"><i class="fas fa-star"></i></label>
                                    <input type="radio" id="star3" name="rating" value="3">
                                    <label for="star3"><i class="fas fa-star"></i></label>
                                    <input type="radio" id="star2" name="rating" value="2">
                                    <label for="star2"><i class="fas fa-star"></i></label>
                                    <input type="radio" id="star1" name="rating" value="1">
                                    <label for="star1"><i class="fas fa-star"></i></label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="reviewComment">Review</label>
                                <textarea id="reviewComment" name="comment" rows="4" required></textarea>
                            </div>
                            <button type="submit" class="submit-review-btn">Submit Review</button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="login-prompt">
                        <p>Please <a href="login.php">login</a> to leave a review.</p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="tab-content" id="shipping">
                <h3>Shipping Information</h3>
                <p>We offer standard and express shipping options. Most orders are processed within 1-2 business days.</p>
                
                <h4>Shipping Options:</h4>
                <ul>
                    <li><strong>Standard Shipping:</strong> RM5.00 (3-5 business days)</li>
                    <li><strong>Express Shipping:</strong> RM10.00 (1-2 business days)</li>
                    <li><strong>Free Shipping:</strong> Available for orders over RM50</li>
                </ul>
                
                <h3>Returns Policy</h3>
                <p>If you're not completely satisfied with your purchase, you may return it within 14 days of receipt for a refund or exchange.</p>
                <p>To be eligible for a return, your item must be unused and in the same condition that you received it.</p>
            </div>
        </section>

        <?php if (count($related_products) > 0): ?>
            <section class="related-products">
                <h2>You May Also Like</h2>
                <div class="related-products-grid">
                    <?php foreach ($related_products as $related): ?>
                        <div class="related-product-item">
                            <a href="product_details.php?id=<?php echo $related['prod_id']; ?>">
                                <img src="<?php echo htmlspecialchars($related['prod_image'] ? 'uploads/products/'.$related['prod_image'] : 'images/product-placeholder.jpg'); ?>" 
                                     alt="<?php echo htmlspecialchars($related['prod_name']); ?>">
                                <h3><?php echo htmlspecialchars($related['prod_name']); ?></h3>
                                <p class="price">RM <?php echo number_format($related['prod_price'], 2); ?></p>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </main>

    <?php include 'footer.php'; ?>

    <script src="../user/js/Product_Detail.js"></script>
</body>
</html>