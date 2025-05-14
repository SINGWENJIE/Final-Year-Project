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

// Fetch categories from database
$categories_sql = "SELECT * FROM category";
$categories_result = $conn->query($categories_sql);

// Fetch products from database
$sql = "SELECT * FROM product";
$products_result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supermarket - Product List</title>
    <link rel="stylesheet" href="../user_assets/css/product_list.css">
    <link rel="stylesheet" href="../user_assets/css/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <main>
        <div class="page-header">
            <div class="search-container">
                <input type="text" id="searchInput" placeholder="Search products..." aria-label="Search products">
                <button id="searchBtn">Search</button>
            </div>
        </div>

        <div class="filter-section">
            <select id="categoryFilter" aria-label="Filter by category">
                <option value="">All Categories</option>
                <?php
                if ($categories_result->num_rows > 0) {
                    while($category = $categories_result->fetch_assoc()) {
                        echo '<option value="'.$category['category_id'].'">'.htmlspecialchars($category['category_name']).'</option>';
                    }
                }
                ?>
            </select>
            <button id="filterBtn">Filter</button>
        </div>

        <div class="products-container" id="productsContainer">
            <?php
            if ($products_result->num_rows > 0) {
                echo "<div class='product-row'>";
                $count = 0;
                while($row = $products_result->fetch_assoc()) {
                    if ($count > 0 && $count % 5 == 0) {
                        echo "</div><div class='product-row'>";
                    }
                    ?>
                    <div class="product-card" data-category="<?php echo htmlspecialchars($row['category_id']); ?>">
                        <a href="product_details.php?id=<?php echo htmlspecialchars($row['prod_id']); ?>" class="product-link">
                            <div class="product-image">
                                <img src="../assets/uploads/<?php echo htmlspecialchars($row['prod_image']); ?>" 
                                     alt="<?php echo htmlspecialchars($row['prod_name']); ?>"
                                     loading="lazy">
                            </div>
                        </a>
                        <div class="product-info">
                            <h3><?php echo htmlspecialchars($row['prod_name']); ?></h3>
                            <p class="price">RM <?php echo number_format($row['prod_price'], 2); ?></p>
                            <p class="description"><?php echo htmlspecialchars(substr($row['prod_description'], 0, 50) . '...'); ?></p>
                            <div class="product-actions">
                                <div class="quantity-controls">
                                    <button class="quantity-minus" type="button" aria-label="Decrease quantity">-</button>
                                    <input type="text" class="quantity" value="1" readonly 
                                           data-max="<?php echo htmlspecialchars($row['stock']); ?>"
                                           aria-label="Quantity">
                                    <button class="quantity-plus" type="button" aria-label="Increase quantity">+</button>
                                </div>
                                <button class="add-to-cart" data-id="<?php echo htmlspecialchars($row['prod_id']); ?>">
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php
                    $count++;
                }
                echo "</div>";
            } else {
                echo '<p class="no-products">No products found.</p>';
            }
            ?>
        </div>
    </main>

    <?php include 'footer.php'; ?>

    <script src="../user_assets/js/product_list.js"></script>
</body>
</html>