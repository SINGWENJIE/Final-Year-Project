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
            
            // Remember me functionality
            if (isset($_POST['remember'])) {
                setcookie('remember_token', bin2hex(random_bytes(32)), time() + 86400 * 30, '/');
            }
            
            // Redirect to dashboard or home page
            header("Location: product_list.php");
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
$password_reset_success = isset($_GET['reset']) && $_GET['reset'] === 'success';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GoGo Supermarket - Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../user_assets/css/login.css">
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Welcome to GoGo Supermarket</h1>
            <p>Please login to your account</p>
        </div>
        
        <?php if ($registration_success): ?>
            <div class="success-message">Registration successful! Please login.</div>
        <?php endif; ?>
        
        <?php if ($password_reset_success): ?>
            <div class="success-message">Password reset successful! Please login with your new password.</div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form id="loginForm" method="post">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-group">
                    <input type="password" id="password" name="password" class="form-control" required>
                    <span class="input-group-append toggle-password">
                        <i class="eye-icon">👁️</i>
                    </span>
                </div>
            </div>
            
            <div class="form-options">
                <label class="remember-me">
                    <input type="checkbox" name="remember"> Remember me
                </label>
                <a href="forgot_password.php" class="forgot-password">Forgot password?</a>
            </div>
            
            <button type="submit" class="btn">Login</button>
            
            <div class="register-link">
                Don't have an account? <a href="register.php">Register here</a>
            </div>
        </form>
    </div>

    <script src="../user_assets/js/login.js"></script>
</body>
</html>