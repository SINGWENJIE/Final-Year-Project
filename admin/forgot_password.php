<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $admin_name = htmlspecialchars($_POST['admin_name']);

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Forgot Password</title>
    <link rel="stylesheet" href="../assets/css/AdminStyle.css">
</head>
<body>
    <div class="login-container">
        <img src="../assets/images/logoname.png" alt="logo" class="logo">
        <h1>Forgot Password</h1>

        <form action="forgot_password.php" method="POST">
            <input type="text" name="admin_name" placeholder="Enter your Email" required>
            <button type="submit">Send OTP</button>
        </form>

        <div style="margin-top: 15px;">
            <a href="adminlogin.php" style="font-size: 14px; color: #555;">Back to Login</a>
        </div>
    </div>
</body>
</html>
