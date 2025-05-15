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
    <title>GOGO Supermarket</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #45ad9c;
            --primary-dark: #45a049;
            --primary-light: #d4edda;
            --secondary-color: #f8f9fa;
            --text-color: #333;
            --light-text: #777;
            --border-color: #ddd;
            --error-color: #d9534f;
            --success-color: #5cb85c;
            --white: #ffffff;
            --hover-color: #f39c12; /* Orange color for hover */
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background-color: var(--secondary-color);
            color: var(--text-color);
            line-height: 1.6;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            width: 100%;
        }
        
        /* Header Styles */
        header {
            background-color: var(--primary-color);
            color: var(--white);
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .column-layout {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .left-section {
            display: flex;
            align-items: center;
            gap: 30px;
        }
        
        .left-section img {
            width: 180px;
            height: auto;
            transition: transform 0.3s ease;
        }
        
        .left-section img:hover {
            transform: scale(1.03);
        }
        
        /* Navigation Styles */
        .main-nav {
            display: flex;
            align-items: center;
            gap: 25px;
        }

        .sub-nav .nav-links {
            display: flex;
            list-style: none;
            gap: 25px;
        }

        header nav a {
            color: var(--white);
            text-decoration: none !important; /* Added !important */
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 16px;
            font-weight: 500;
            transition: all 0.3s ease;
            padding: 8px 0;
            position: relative;
        }

        /* Orange hover effect for all nav items - without underline */
        header nav a:hover {
            color: var(--hover-color) !important;
            text-decoration: none !important; /* Added */
        }

        header nav a:hover i {
            color: var(--hover-color) !important;
            transform: translateY(-2px);
        }

        /* Keep underline effect only for sub-nav links (Menu and About GOGO) */
        .left-section .sub-nav .nav-links li a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background-color: var(--hover-color);
            transition: width 0.3s ease;
        }

        .left-section .sub-nav .nav-links li a:hover::after {
            width: 100%;
        }

        /* Explicitly remove underline from logout button */
        .user-info a.logout-btn::after,
        .user-info a.logout-btn:hover::after {
            display: none;
            content: none;
        }

        /* Also ensure no text-decoration on hover */
        .user-info a.logout-btn:hover {
            text-decoration: none;
        }

        /* User Info Styles */
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-left: 10px;
        }
        
        .user-info a {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .logout-btn {
            background-color: transparent;
            border: 2px solid var(--white);
            color: var(--white);
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none !important;
        }
        
        .logout-btn:hover {
            background-color: var(--white);
            color: var(--hover-color);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            text-decoration: none !important;
        }
        
        /* Responsive Styles */
        @media (max-width: 992px) {
            .column-layout {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .left-section {
                width: 100%;
                justify-content: space-between;
            }
            
            .main-nav {
                width: 100%;
                justify-content: space-between;
                padding-top: 15px;
                border-top: 1px solid rgba(255,255,255,0.2);
            }
        }
        
        @media (max-width: 768px) {
            .main-nav {
                flex-wrap: wrap;
                gap: 15px;
            }
            
            .sub-nav .nav-links {
                gap: 15px;
            }
            
            .user-info {
                margin-left: 0;
            }
        }
        
        @media (max-width: 576px) {
            .left-section {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .sub-nav .nav-links {
                gap: 10px;
                flex-wrap: wrap;
            }
            
            .sub-nav .nav-links li a {
                font-size: 16px;
            }
            
            header nav a {
                font-size: 14px;
                gap: 5px;
            }
            
            header nav a i {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
<header>
    <div class="container column-layout">
        <div class="left-section">
            <h1>            
                <a href="MainPage.php"><img src="../image/gogoname.png" alt="GOGO Logo"></a>
            </h1>

            <nav class="sub-nav">
                <ul class="nav-links">
                    <li><a href="Product_List.php">Menu</a></li>
                    <li><a href="AboutUs.php">About GOGO</a></li>
                </ul>
            </nav>
        </div>

        <nav class="main-nav">
            <a href="product_list.php"><i class="fas fa-store"></i> Products</a>
            <a href="#"><i class="fas fa-heart"></i> Wishlist</a>
            <a href="Cart.php"><i class="fas fa-shopping-cart"></i> Cart</a>
            <a href="order_history.php"><i class="fas fa-history"></i> History</a>
            <?php if(isset($_SESSION['user_name'])): ?>
                <span class="user-info">
                    <a href="Profile.php"><i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['user_name']); ?></a>
                    <button onclick="window.location.href='logout.php'" class="logout-btn">Logout</button>
                </span>
            <?php else: ?>
                <span class="user-info">
                    <a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
                    <a href="register.php" class="logout-btn">Register</a>
                </span>
            <?php endif; ?>
        </nav>
    </div>
</header>
</body>
</html>