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
            header("Location: MainPage.php");
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
        
        .login-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 100%;
            max-width: 450px;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header h1 {
            color: var(--primary-color);
            margin-bottom: 10px;
            font-size: 28px;
        }
        
        .login-header p {
            color: var(--light-text);
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-color);
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
            transform: translateY(-2px);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .remember-me {
            display: flex;
            align-items: center;
            font-size: 14px;
            color: var(--light-text);
        }
        
        .remember-me input {
            width: auto;
            margin-right: 8px;
            accent-color: var(--primary-color);
        }
        
        .forgot-password {
            font-size: 14px;
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .forgot-password:hover {
            text-decoration: underline;
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
        
        .register-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: var(--light-text);
        }
        
        .register-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }
        
        .register-link a:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 576px) {
            .login-container {
                padding: 30px 20px;
            }
            
            .form-options {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Welcome To GOGO Supermarket</h1>
            <p>Login to access your account and continue shopping</p>
        </div>
        
        <?php if ($registration_success): ?>
            <div class="success-message">Registration successful! Please login with your credentials.</div>
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
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            
            <div class="form-options">
                <label class="remember-me">
                    <input type="checkbox" name="remember"> Remember me
                </label>
                <a href="Forgot_password.php" class="forgot-password">Forgot password?</a>
            </div>
            
            <button type="submit" class="btn">Login</button>
        </form>
        
        <div class="register-link">
            Don't have an account? <a href="register.php">Register here</a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // If coming from registration, auto-focus the email field
            <?php if ($registration_success && isset($_SESSION['registration_email'])): ?>
                document.getElementById('email').value = '<?php echo $_SESSION['registration_email']; ?>';
                document.getElementById('password').focus();
                <?php unset($_SESSION['registration_email']); ?>
            <?php endif; ?>
        });
    </script>
</body>
</html>