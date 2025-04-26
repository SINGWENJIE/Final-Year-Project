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
</head>
<body>
    <header>
        <div class="user-info">
            Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
        <h1>Our Products</h1>
        <div class="search-container">
            <input type="text" id="searchInput" placeholder="Search products...">
            <button id="searchBtn">Search</button>
        </div>
    </header>

    <main>
        <div class="filter-section">
            <select id="categoryFilter">
                <option value="">All Categories</option>
                <?php
                if ($categories_result->num_rows > 0) {
                    while($category = $categories_result->fetch_assoc()) {
                        echo '<option value="'.$category['category_id'].'">'.$category['category_name'].'</option>';
                    }
                }
                ?>
            </select>
            <button id="filterBtn">Filter</button>
        </div>

        <div class="products-container" id="productsContainer">
            <?php
            if ($products_result->num_rows > 0) {
                $count = 0;
                while($row = $products_result->fetch_assoc()) {
                    if ($count % 5 == 0) {
                        if ($count != 0) echo "</div>";
                        echo "<div class='product-row'>";
                    }
                    ?>
                    <div class="product-card" data-category="<?php echo $row['category_id']; ?>">
                        <a href="product_details.php?id=<?php echo $row['prod_id']; ?>" class="product-link">
                            <div class="product-image">
                                <img src="../assets/uploads/<?php echo $row['prod_image']; ?>" alt="<?php echo $row['prod_name']; ?>">
                            </div>
                        </a>
                        <div class="product-info">
                            <h3><?php echo $row['prod_name']; ?></h3>
                            <p class="price">RM <?php echo number_format($row['prod_price'], 2); ?></p>
                            <p class="description"><?php echo substr($row['prod_description'], 0, 50) . '...'; ?></p>
                            <div class="product-actions">
                                <div class="quantity-controls">
                                    <button class="quantity-minus" type="button" aria-label="Decrease quantity">-</button>
                                    <input type="text" class="quantity" value="1" readonly data-max="<?php echo $row['stock']; ?>">
                                    <button class="quantity-plus" type="button" aria-label="Increase quantity">+</button>
                                </div>
                                <button class="add-to-cart" data-id="<?php echo $row['prod_id']; ?>">Add to Cart</button>
                            </div>
                        </div>
                    </div>
                    <?php
                    $count++;
                }
                echo "</div>"; // Close last row
            } else {
                echo "<p>No products found.</p>";
            }
            $conn->close();
            ?>
        </div>
    </main>

    <footer>
        <p>&copy; 2023 Supermarket. All rights reserved.</p>
    </footer>

    <script src="../user_assets/js/product_list.js"></script>
</body>
</html>