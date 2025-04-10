<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit();
}

// 获取用户信息
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM user WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Profile</title>
    <link rel="stylesheet" href="Profile.css">
    <!-- 添加图标库 -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <header>
        <div class="auth-section">
            <ul class="auth-links">
                <li><a href="Login.html">Login</a></li>
                <li><a href="Register.html">Register</a></li>
            </ul>
            <a href="ShoppingCart.html" class="shopping-cart-link">
                <img src="image/cart.png" alt="Cart" class="shopping-cart">
            </a>
        </div>

        <div class="header-main">
            <a href="MainPage.html">
                <img src="image/gogoname.png" alt="GOGO Logo">
            </a>
            <form class="search-form">
                <div class="search">
                    <span class="search-icon material-symbols-outlined">search</span>
                    <input class="search-input" type="search" placeholder="Search">
                </div>
            </form>
        </div>
    </header>

    <!-- 已移除导航栏包含语句 -->

    <div class="profile-container">
        <h1><i class='bx bx-user-pin'></i> Edit Profile</h1>
        
        <form action="update_profile.php" method="POST">
            <!-- 用户名字段 -->
            <div class="form-group">
                <label><i class='bx bx-user'></i> Username</label>
                <input type="text" name="username" 
                       value="<?php echo htmlspecialchars($user['user_name']); ?>" 
                       required>
            </div>

            <!-- 邮箱字段 -->
            <div class="form-group">
                <label><i class='bx bx-envelope'></i> Email</label>
                <input type="email" name="email" 
                       value="<?php echo htmlspecialchars($user['email']); ?>" 
                       required>
            </div>

            <!-- 电话号码 -->
            <div class="form-group">
                <label><i class='bx bx-phone'></i> Phone</label>
                <input type="tel" name="phone" 
                       pattern="[0-9]{10,11}"
                       value="<?php echo htmlspecialchars($user['user_phone_num']); ?>" 
                       required>
                <small>Format: 012-3456789 (10-11 digits)</small>
            </div>

            <!-- 生日 -->
            <div class="form-group">
                <label><i class='bx bx-calendar'></i> Birthday</label>
                <input type="date" name="birth_date" 
                       max="<?php echo date('Y-m-d'); ?>"
                       value="<?php echo $user['birth_date'] ?? ''; ?>">
            </div>

            <!-- 地址 -->
            <div class="form-group">
                <label><i class='bx bx-home'></i> Address</label>
                <textarea name="address"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
            </div>

            <button type="submit" class="save-btn">
                <i class='bx bx-save'></i> Save Changes
            </button>
            
            <a href="MainPage.php" class="return-btn">
                <i class='bx bx-home'></i> Back to Main
            </a>
        </form>
    </div>

    <div class="footer-nav">
        <div class="footer-column">
            <h4>GOGO</h4>
            <ul>
                <li><a href="AboutUs.html">About GOGO</a></li>
                <li><a href="#">Policies</a></li>
                <li><a href="#">Terms & Conditions</a></li>
            </ul>
        </div>

        <div class="footer-column">
            <h4>Support</h4>
            <ul>
                <li><a href="#">FAQs</a></li>
                <li><a href="#">Contact Us</a></li>
            </ul>
        </div>

        <div class="footer-column">
            <h4>Legal</h4>
            <ul>
                <li><a href="#">Privacy Policy</a></li>
                <li><a href="#">Anti-Bribery</a></li>
                <li><a href="#">Loyalty Program</a></li>
            </ul>
        </div>
    </div>

</body>
</html>