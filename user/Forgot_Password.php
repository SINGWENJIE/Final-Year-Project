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

        $email = $_POST['email'];
        
        // Check if email exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email AND status = 'active'");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Generate reset token (in a real app, this would be sent via email)
            $reset_token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', time() + 3600); // 1 hour expiry
            
            // Store token in database
            $stmt = $pdo->prepare("UPDATE users SET reset_token = :token, reset_expiry = :expiry WHERE user_id = :user_id");
            $stmt->execute([
                ':token' => $reset_token,
                ':expiry' => $expiry,
                ':user_id' => $user['user_id']
            ]);
            
            // In a real app, you would send an email with the reset link
            // For demo purposes, we'll show the link
            $_SESSION['reset_token_demo'] = $reset_token;
            $_SESSION['reset_email'] = $email;
            
            header("Location: reset_password.php");
            exit();
        } else {
            $error = "No account found with that email address";
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GoGo Supermarket - Forgot Password</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../user_assets/css/forgot_password.css">
</head>
<body>
    <div class="forgot-container">
        <div class="forgot-header">
            <h1>Forgot Your Password?</h1>
            <p>Enter your email to receive a password reset link</p>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form id="forgotForm" method="post">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>
            
            <button type="submit" class="btn">Send Reset Link</button>
            
            <div class="back-to-login">
                Remember your password? <a href="login.php">Login here</a>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('forgotForm');
            const emailInput = document.getElementById('email');
    
            // Form validation
            form.addEventListener('submit', function(e) {
                if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value)) {
                    alert('Please enter a valid email address');
                    e.preventDefault();
                }
            });
    
            // Focus on email field when page loads
           emailInput.focus();
        });
    </script>
</body>
</html>