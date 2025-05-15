<?php
session_start();

// Redirect if not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gogo_supermarket";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle filters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$date_filter = isset($_GET['date']) ? $_GET['date'] : '';
$search_term = isset($_GET['search']) ? $_GET['search'] : '';

// Base query
$query = "SELECT o.order_id, o.order_date, o.total_amount, o.order_status, 
                 COUNT(oi.order_item_id) as item_count,
                 d.delivery_status, d.estimated_delivery_date
          FROM orders o
          LEFT JOIN order_item oi ON o.order_id = oi.order_id
          LEFT JOIN delivery d ON o.order_id = d.order_id
          WHERE o.user_id = $user_id";

// Add filters
if (!empty($status_filter)) {
    $query .= " AND o.order_status = '" . $conn->real_escape_string($status_filter) . "'";
}

if (!empty($date_filter)) {
    if ($date_filter == 'last30') {
        $query .= " AND o.order_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
    } elseif ($date_filter == 'last6months') {
        $query .= " AND o.order_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)";
    } elseif ($date_filter == 'lastyear') {
        $query .= " AND o.order_date >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
    }
}

if (!empty($search_term)) {
    $query .= " AND (o.order_id LIKE '%" . $conn->real_escape_string($search_term) . "%' OR 
                    EXISTS (SELECT 1 FROM order_item oi 
                            JOIN product p ON oi.prod_id = p.prod_id
                            WHERE oi.order_id = o.order_id 
                            AND p.prod_name LIKE '%" . $conn->real_escape_string($search_term) . "%'))";
}

// Group and order
$query .= " GROUP BY o.order_id ORDER BY o.order_date DESC";

$result = $conn->query($query);

// Get order counts for filter tabs
$count_query = "SELECT 
    SUM(CASE WHEN order_status = 'pending' THEN 1 ELSE 0 END) as pending_count,
    SUM(CASE WHEN order_status = 'processing' THEN 1 ELSE 0 END) as processing_count,
    SUM(CASE WHEN order_status = 'shipped' THEN 1 ELSE 0 END) as shipped_count,
    SUM(CASE WHEN order_status = 'delivered' THEN 1 ELSE 0 END) as delivered_count,
    COUNT(*) as all_count
    FROM orders WHERE user_id = $user_id";

$count_result = $conn->query($count_query);
$counts = $count_result->fetch_assoc();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History - Supermarket</title>
    <link rel="stylesheet" href="../user_assets/css/order_history.css">
    <link rel="stylesheet" href="../user_assets/css/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <main class="container">
        <div class="breadcrumb">
            <a href="product_list.php">Products</a> &gt; 
            <span>Order History</span>
        </div>

        <h1 class="page-title">Your Order History</h1>

        <div class="order-history-container">
            <!-- Filters Section -->
            <div class="filters-section">
                <div class="filter-tabs">
                    <a href="order_history.php" class="filter-tab <?php echo empty($status_filter) ? 'active' : ''; ?>">
                        All Orders <span class="count-badge"><?php echo $counts['all_count']; ?></span>
                    </a>
                    <a href="order_history.php?status=pending" class="filter-tab <?php echo $status_filter == 'pending' ? 'active' : ''; ?>">
                        Pending <span class="count-badge"><?php echo $counts['pending_count']; ?></span>
                    </a>
                    <a href="order_history.php?status=processing" class="filter-tab <?php echo $status_filter == 'processing' ? 'active' : ''; ?>">
                        Processing <span class="count-badge"><?php echo $counts['processing_count']; ?></span>
                    </a>
                    <a href="order_history.php?status=shipped" class="filter-tab <?php echo $status_filter == 'shipped' ? 'active' : ''; ?>">
                        Shipped <span class="count-badge"><?php echo $counts['shipped_count']; ?></span>
                    </a>
                    <a href="order_history.php?status=delivered" class="filter-tab <?php echo $status_filter == 'delivered' ? 'active' : ''; ?>">
                        Delivered <span class="count-badge"><?php echo $counts['delivered_count']; ?></span>
                    </a>
                </div>

                <div class="filter-controls">
                    <form method="get" class="filter-form">
                        <div class="form-group">
                            <label for="date">Time Period:</label>
                            <select name="date" id="date" onchange="this.form.submit()">
                                <option value="">All Time</option>
                                <option value="last30" <?php echo $date_filter == 'last30' ? 'selected' : ''; ?>>Last 30 Days</option>
                                <option value="last6months" <?php echo $date_filter == 'last6months' ? 'selected' : ''; ?>>Last 6 Months</option>
                                <option value="lastyear" <?php echo $date_filter == 'lastyear' ? 'selected' : ''; ?>>Last Year</option>
                            </select>
                        </div>
                        
                        <div class="form-group search-group">
                            <input type="text" name="search" placeholder="Search orders..." value="<?php echo htmlspecialchars($search_term); ?>">
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Orders List -->
            <div class="orders-list">
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($order = $result->fetch_assoc()): ?>
                        <div class="order-card">
                            <div class="order-header">
                                <div class="order-info">
                                    <h3>Order #<?php echo $order['order_id']; ?></h3>
                                    <span class="order-date"><?php echo date('F j, Y', strtotime($order['order_date'])); ?></span>
                                </div>
                                <div class="order-status-badge <?php echo strtolower($order['order_status']); ?>">
                                    <?php echo ucfirst($order['order_status']); ?>
                                </div>
                            </div>
                            
                            <div class="order-details">
                                <div class="detail-item">
                                    <span>Items</span>
                                    <span><?php echo $order['item_count']; ?></span>
                                </div>
                                <div class="detail-item">
                                    <span>Total Amount</span>
                                    <span>RM <?php echo number_format($order['total_amount'], 2); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span>Delivery Status</span>
                                    <span class="delivery-status <?php echo strtolower($order['delivery_status']); ?>">
                                        <?php 
                                        switch($order['delivery_status']) {
                                            case 'processing': echo 'Preparing'; break;
                                            case 'out_for_delivery': echo 'On the way'; break;
                                            case 'delivered': echo 'Delivered'; break;
                                            default: echo ucfirst($order['delivery_status']);
                                        }
                                        ?>
                                    </span>
                                </div>
                                <?php if ($order['delivery_status'] != 'delivered' && !empty($order['estimated_delivery_date'])): ?>
                                <div class="detail-item">
                                    <span>Estimated Delivery</span>
                                    <span><?php echo date('M j, Y', strtotime($order['estimated_delivery_date'])); ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="order-actions">
                                <a href="order_confirmation.php?order_id=<?php echo $order['order_id']; ?>" class="btn btn-view">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                                <?php if ($order['order_status'] == 'delivered'): ?>
                                <button class="btn btn-review" data-order-id="<?php echo $order['order_id']; ?>">
                                    <i class="fas fa-star"></i> Leave Review
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-orders">
                        <i class="fas fa-box-open"></i>
                        <h3>No orders found</h3>
                        <p>You haven't placed any orders yet.</p>
                        <a href="product_list.php" class="btn btn-shop">Start Shopping</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include 'footer.php'; ?>

    <!-- Review Modal -->
    <div class="modal" id="reviewModal">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <h2>Leave a Review</h2>
        <form id="reviewForm">
            <input type="hidden" id="reviewOrderId" name="order_id">
            <div class="form-group">
                <label>Select Product:</label>
                <select id="reviewProduct" name="prod_id" class="form-control" required>
                    <option value="" disabled selected>Loading products...</option>
                </select>
            </div>
            <div class="form-group">
                <label>Rating:</label>
                <div class="rating-stars">
                    <i class="far fa-star" data-rating="1"></i>
                    <i class="far fa-star" data-rating="2"></i>
                    <i class="far fa-star" data-rating="3"></i>
                    <i class="far fa-star" data-rating="4"></i>
                    <i class="far fa-star" data-rating="5"></i>
                </div>
                <input type="hidden" id="ratingValue" name="rating" required>
            </div>
            <div class="form-group">
                <label>Your Review:</label>
                <textarea name="comment" rows="4" class="form-control" placeholder="Share your experience with this product..." required></textarea>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-cancel" onclick="closeReviewModal()">Cancel</button>
                <button type="submit" class="btn btn-submit">
                    <i class="fas fa-paper-plane"></i> Submit Review
                </button>
            </div>
        </form>
    </div>
    </div>

    <script src="../user_assets/js/order_history.js"></script>
</body>
</html>