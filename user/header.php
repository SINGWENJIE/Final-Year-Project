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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GOGO- Supermarket</title>
    <link rel="stylesheet" href="../user_assets/css/header.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<header>
    <div class="container column-layout">
        <div class="left-section">
            <h1>            
                <a href="MainPage.html"><img src="../image/gogoname.png" alt="GOGO Logo"></a>
            </h1>

            <nav class="sub-nav">
                <ul class="nav-links">
                    <li><a href="Product_List.php">Menu</a></li>
                    <li><a href="AboutUs/AboutUs.html">About GOGO</a></li>
                </ul>
            </nav>
        </div>

        <nav class="main-nav">
            <a href="product_list.php"><i class="fas fa-store"></i> Products</a>
            <a href="#"><i class="fas fa-heart"></i> Wishlist</a>
            <a href="cart.php" class="active"><i class="fas fa-shopping-cart"></i> Cart 
                    <?php if (!$is_cart_empty): ?>
                        <span class="cart-count"><?php echo $item_count; ?></span>
                    <?php endif; ?>
            </a>
            <span class="user-info">
                <a href="Profile/Profile.php"><i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['user_name']); ?></a>
                <a href="logout.php" class="logout-btn">Logout</a>
            </span>
        </nav>
    </div>
</header>
</body>
</html>