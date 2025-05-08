<?php
session_start();

// Database configuration
$servername = "localhost"; 
$username = "root"; 
$password = "";  
$dbname = "gogo_supermarket"; 

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Get form data
        $email = $_POST['email'];
        $password = $_POST['password'];

        // Find user by email
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email AND status = 'active'");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['user_password'])) {
            // Login successful
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['user_name'];
            $_SESSION['email'] = $user['email'];
            
            // Redirect to dashboard or home page
            header("Location: MainPage/MainPage.php");
            exit();
        } else {
            $error = "Invalid email or password";
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}

// Check if user is coming from registration
$registration_success = isset($_GET['registration']) && $_GET['registration'] === 'success';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supermarket - User Login</title>
    <link rel="stylesheet" href="../user_assets/css/login.css">
</head>
<body>
    <div class="login-container">
        <h1>Welcome Back</h1>
        
        <?php if ($registration_success): ?>
            <div class="success-message">Registration successful! Please login.</div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form id="loginForm" action="login.php" method="post">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-options">
                <label class="remember-me">
                    <input type="checkbox" name="remember"> Remember me
                </label>
                <a href="Forgot_password.php" class="forgot-password">Forgot password?</a>
            </div>
            
            <button type="submit" class="login-btn">Login</button>
        </form>
        
        <div class="register-link">
            Don't have an account? <a href="register.php">Register here</a>
        </div>
    </div>

    <script src="../user_assets/js/login.js"></script>
</body>
</html>