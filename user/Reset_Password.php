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

        $token = $_POST['token'];
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Validate passwords match
        if ($password !== $confirm_password) {
            $error = "Passwords do not match";
        } else {
            // Check if token is valid and not expired
            $stmt = $pdo->prepare("SELECT * FROM users WHERE reset_token = :token AND reset_expiry > NOW()");
            $stmt->execute([':token' => $token]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // Update password and clear reset token
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET user_password = :password, reset_token = NULL, reset_expiry = NULL WHERE user_id = :user_id");
                $stmt->execute([
                    ':password' => $hashed_password,
                    ':user_id' => $user['user_id']
                ]);
                
                header("Location: login.php?reset=success");
                exit();
            } else {
                $error = "Invalid or expired reset token";
            }
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}

// Check if coming from forgot password with demo token
$token = $_GET['token'] ?? (isset($_SESSION['reset_token_demo']) ? $_SESSION['reset_token_demo'] : null);
$email = $_SESSION['reset_email'] ?? null;

if (!$token) {
    header("Location: forgot_password.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GoGo Supermarket - Reset Password</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../user_assets/css/reset_password.css">
</head>
<body>
    <div class="reset-container">
        <div class="reset-header">
            <h1>Reset Your Password</h1>
            <?php if ($email): ?>
                <p>For account: <?php echo htmlspecialchars($email); ?></p>
            <?php endif; ?>
        </div>
        
        <?php if (isset($_SESSION['reset_token_demo'])): ?>
            <div class="demo-message">
                <strong>Demo Only:</strong> Normally this link would be sent to your email
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form id="resetForm" method="post">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
            
            <div class="form-group">
                <label for="password">New Password</label>
                <div class="input-group">
                    <input type="password" id="password" name="password" class="form-control" required>
                    <span class="input-group-append toggle-password">
                        <i class="eye-icon">👁️</i>
                    </span>
                </div>
                <div class="password-strength">
                    <div class="password-strength-bar" id="passwordStrengthBar"></div>
                </div>
                <div class="password-hint">
                    Must contain at least 1 uppercase letter, 1 symbol, and minimum 4 characters
                </div>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <div class="input-group">
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                    <span class="input-group-append toggle-password">
                        <i class="eye-icon">👁️</i>
                    </span>
                </div>
                <div id="passwordMatch" class="password-hint"></div>
            </div>
            
            <button type="submit" class="btn">Reset Password</button>
        </form>
    </div>

    <script src="../user_assets/js/reset_password.js"></script>
</body>
</html>