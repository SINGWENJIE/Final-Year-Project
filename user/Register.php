<?php
// Database configuration
$servername = "localhost"; 
$username = "root"; 
$password = "";  
$dbname = "gogo_supermarket"; 

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Get form data
        $user_name = $_POST['user_name'];
        $email = $_POST['email'];
        $user_password = password_hash($_POST['user_password'], PASSWORD_DEFAULT);
        $user_phone_num = $_POST['user_phone_num'];
        $status = 'active'; // Default status
        $user_created_at = date('Y-m-d H:i:s'); // Current timestamp

        // Insert into database
        $stmt = $pdo->prepare("INSERT INTO users (user_name, email, user_password, user_phone_num, user_created_at, status) 
                              VALUES (:user_name, :email, :user_password, :user_phone_num, :user_created_at, :status)");
        
        $stmt->execute([
            ':user_name' => $user_name,
            ':email' => $email,
            ':user_password' => $user_password,
            ':user_phone_num' => $user_phone_num,
            ':user_created_at' => $user_created_at,
            ':status' => $status
        ]);

        // Redirect to success page or login
        header("Location: login.php?registration=success");
        exit();
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
    <title>Supermarket - User Registration</title>
    <link rel="stylesheet" href="../user_assets/css/register.css">
</head>
<body>
    <div class="registration-container">
        <h1>Create Your Account</h1>
        
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form id="registrationForm" action="register.php" method="post">
            <div class="form-group">
                <label for="user_name">Full Name</label>
                <input type="text" id="user_name" name="user_name" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="user_password">Password</label>
                <input type="password" id="user_password" name="user_password" required>
                <div class="password-strength"></div>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
                <div class="password-match"></div>
            </div>
            
            <div class="form-group">
                <label for="user_phone_num">Phone Number</label>
                <input type="tel" id="user_phone_num" name="user_phone_num" required>
            </div>
            
            <button type="submit" class="register-btn">Register</button>
        </form>
        
        <div class="login-link">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>

    <script src="../user_assets/js/register.js"></script>
</body>
</html>