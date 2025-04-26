<?php
session_start();
include '../db_connection.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $admin_input = mysqli_real_escape_string($conn, $_POST['admin_input']);
    $password = $_POST['admin_password'];

    if ($admin_input == "superadmin" && $password == "password") {
        $_SESSION['admin_name'] = "SuperAdmin"; 
        $_SESSION['admin_role'] = "Super Admin";
        
        header("Location: ../admin/superadmin_dashboard.php");
        exit();
    }

    $sql = "SELECT * FROM admin WHERE admin_email = '$admin_input'";
    $result = mysqli_query($conn, $sql); 

    if ($result) {
        $row = mysqli_fetch_assoc($result);
        
        if ($row) {
            if ($password == $row['admin_password']) {
                if ($row['status'] === 'active') {
                    $_SESSION['admin_email'] = $admin_input;
                    $_SESSION['admin_role'] = ucfirst($row['admin_role']);
                    $_SESSION['admin_name'] = $row['admin_name'];
                    echo "<pre>";
            print_r($_SESSION);
            echo "</pre>";
                    header("Location: ../admin/admin_dashboard.php");
                    exit();
                } else {
                    $error = "This admin account is inactive!";
                }
            } else {
                $error = "Incorrect password!";
            }
        } else {
            $error = "Email does not exist!";
        }
    } else {
        $error = "Error with the query!";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Login</title>
    <link rel="stylesheet" href="../assets/css/AdminStyle.css">
</head>
<body>
    <div class="login-container">
        <img src="../assets/images/logoname.png" alt="logo" class="logo"> 
        <h1>Admin Login</h1>

        <?php if (!empty($error)) { ?>
            <p style="color: red;"><?php echo $error; ?></p>
        <?php } ?>

        <form action="adminlogin.php" method="POST">
    <input type="text" name="admin_input" placeholder="Enter your email or superadmin name" required>
    <input type="password" name="admin_password" placeholder="Enter your password" required>
    <button type="submit">Login</button>
</form>

<div style="text-align: right; margin-top: 10px;">
    <a href="forgot_password.php" style="font-size: 14px; color: #555;">Forgot Password?</a>
</div>

    </div>
</body>
</html>
