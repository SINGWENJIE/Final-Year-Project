<?php
session_start();

// Database configuration
$servername = "localhost"; 
$username = "root"; 
$password = "";  
$dbname = "gogo_supermarket"; 

// Set timezone to match database
date_default_timezone_set('Asia/Kuala_Lumpur');

$error = '';
$success = '';

// Check if token is provided
$token = $_GET['token'] ?? null;

if (!$token) {
    header("Location: forgot_password.php");
    exit();
}

// Debug output
error_log("Reset Password Attempt - Token: $token");
error_log("Current Time: " . date('Y-m-d H:i:s'));

// Verify token is valid when page loads
try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check token validity
    $stmt = $pdo->prepare("SELECT * FROM users WHERE reset_token = :token AND reset_expiry > NOW()");
    $stmt->execute([':token' => $token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        // Get debug info
        $debug_stmt = $pdo->prepare("SELECT email, reset_token, reset_expiry, NOW() as db_time FROM users WHERE reset_token IS NOT NULL");
        $debug_stmt->execute();
        $debug_data = $debug_stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("Token Debug Data: " . print_r($debug_data, true));
        
        $error = "Invalid or expired reset token. Please request a new password reset.";
    } else {
        $_SESSION['reset_user_id'] = $user['user_id'];
    }
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
    error_log("Database Error: " . $e->getMessage());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['reset_user_id'])) {
    try {
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Validate passwords
        if ($password !== $confirm_password) {
            throw new Exception("Passwords do not match.");
        }
        
        if (strlen($password) < 8) {
            throw new Exception("Password must be at least 8 characters long.");
        }
        
        if (!preg_match('/[A-Z]/', $password) || !preg_match('/[^a-zA-Z0-9]/', $password)) {
            throw new Exception("Password must contain at least one uppercase letter and one special character.");
        }
        
        // Update password and clear reset token
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET user_password = :password, reset_token = NULL, reset_expiry = NULL WHERE user_id = :user_id");
        $stmt->execute([
            ':password' => $hashed_password,
            ':user_id' => $_SESSION['reset_user_id']
        ]);
        
        // Clear session
        unset($_SESSION['reset_user_id']);
        
        $_SESSION['password_reset_success'] = true;
        header("Location: login.php?reset=success");
        exit();
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Verify token is valid when page loads
try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE reset_token = :token AND reset_expiry > NOW()");
    $stmt->execute([':token' => $token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        $error = "Invalid or expired reset token. Please request a new password reset.";
    }
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GoGo Supermarket - Reset Password</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4CAF50;
            --primary-dark: #45a049;
            --secondary-color: #f5f5f5;
            --text-color: #333;
            --light-text: #777;
            --border-color: #ddd;
            --error-color: #d9534f;
            --success-color: #5cb85c;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--secondary-color);
            color: var(--text-color);
            line-height: 1.6;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .reset-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            padding: 30px;
            width: 100%;
            max-width: 500px;
        }
        
        .reset-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .reset-header h1 {
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .reset-header p {
            color: var(--light-text);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 15px;
            font-family: 'Poppins', sans-serif;
            transition: border-color 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(76, 175, 80, 0.2);
        }
        
        .input-group {
            position: relative;
        }
        
        .input-group-append {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--light-text);
            cursor: pointer;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 20px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .btn:hover {
            background-color: var(--primary-dark);
        }
        
        .password-strength {
            margin-top: 5px;
            height: 5px;
            background-color: #eee;
            border-radius: 5px;
            overflow: hidden;
        }
        
        .password-strength-bar {
            height: 100%;
            width: 0;
            transition: width 0.3s ease, background-color 0.3s ease;
        }
        
        .password-hint {
            font-size: 12px;
            color: var(--light-text);
            margin-top: 5px;
        }
        
        .error-message {
            color: var(--error-color);
            background-color: #fdf7f7;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #ebccd1;
            font-size: 14px;
        }
        
        .success-message {
            color: var(--success-color);
            background-color: #f7fdf7;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #d4edda;
            font-size: 14px;
        }
        
        .invalid-token {
            text-align: center;
            padding: 30px;
        }
        
        .invalid-token a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }
        
        .invalid-token a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <?php if ($error && strpos($error, 'Invalid or expired') !== false): ?>
        <div class="reset-container">
            <div class="invalid-token">
                <h2>Invalid or Expired Link</h2>
                <p><?php echo htmlspecialchars($error); ?></p>
                <p><a href="forgot_password.php">Request a new password reset</a></p>
            </div>
        </div>
    <?php else: ?>
        <div class="reset-container">
            <div class="reset-header">
                <h1>Reset Your Password</h1>
                <p>Create a new password for your account</p>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <form method="post">
                <div class="form-group">
                    <label for="password">New Password</label>
                    <div class="input-group">
                        <input type="password" id="password" name="password" class="form-control" required>
                        <span class="input-group-append toggle-password" onclick="togglePassword('password')">
                            👁️
                        </span>
                    </div>
                    <div class="password-strength">
                        <div class="password-strength-bar" id="passwordStrengthBar"></div>
                    </div>
                    <div class="password-hint">
                        Must contain at least 1 uppercase letter, 1 symbol, and minimum 8 characters
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <div class="input-group">
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                        <span class="input-group-append toggle-password" onclick="togglePassword('confirm_password')">
                            👁️
                        </span>
                    </div>
                    <div id="passwordMatch" class="password-hint"></div>
                </div>
                
                <button type="submit" class="btn">Reset Password</button>
            </form>
        </div>
        
        <script>
            function togglePassword(id) {
                const input = document.getElementById(id);
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
            }
            
            // Password strength indicator
            const passwordInput = document.getElementById('password');
            const passwordStrengthBar = document.getElementById('passwordStrengthBar');
            
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                let strength = 0;
                
                // Check length
                if (password.length >= 4) strength += 20;
                if (password.length >= 8) strength += 20;
                
                // Check for uppercase letters
                if (/[A-Z]/.test(password)) strength += 20;
                
                // Check for numbers
                if (/[0-9]/.test(password)) strength += 20;
                
                // Check for special characters
                if (/[^A-Za-z0-9]/.test(password)) strength += 20;
                
                // Update strength bar
                passwordStrengthBar.style.width = strength + '%';
                
                // Update color based on strength
                if (strength < 40) {
                    passwordStrengthBar.style.backgroundColor = '#d9534f'; // Red
                } else if (strength < 80) {
                    passwordStrengthBar.style.backgroundColor = '#f0ad4e'; // Yellow
                } else {
                    passwordStrengthBar.style.backgroundColor = '#5cb85c'; // Green
                }
            });
            
            // Password match validation
            const confirmPasswordInput = document.getElementById('confirm_password');
            const passwordMatch = document.getElementById('passwordMatch');
            
            confirmPasswordInput.addEventListener('input', function() {
                if (this.value !== passwordInput.value) {
                    passwordMatch.textContent = 'Passwords do not match!';
                    passwordMatch.style.color = 'var(--error-color)';
                } else {
                    passwordMatch.textContent = 'Passwords match!';
                    passwordMatch.style.color = 'var(--success-color)';
                }
            });
        </script>
    <?php endif; ?>
</body>
</html>