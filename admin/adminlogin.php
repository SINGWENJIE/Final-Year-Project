<?php
session_start();
include '../db_connection.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
   
    $admin_name = mysqli_real_escape_string($conn, $_POST['admin_name']);
    $password = $_POST['admin_password'];

    $superadmin = [
        "admin_name" => "superadmin", 
        "admin_password" => "password" 
    ];

    if ($admin_name == $superadmin['admin_name'] && $password == $superadmin['admin_password']) {
        $_SESSION['admin_name'] = "superAdmin"; 
        $_SESSION['admin_role'] = "Super Admin";
        header("Location: ../admin/superadmin_dashboard.php");
        exit();
    }

    $sql = "SELECT * FROM admin WHERE admin_name = '$admin_name'";
    $result = mysqli_query($conn, $sql);

    if ($row = mysqli_fetch_assoc($result)) { 
       
        if ($password == $row['admin_password']) {  
            $_SESSION['admin_name'] = $admin_name;  
            $_SESSION['admin_role'] = ucfirst($row['admin_role']); 
            header("Location: ../admin/admin_dashboard.php");
            exit();
        } else {
            $error = "Incorrect password!";
        }
    } else {
        $error = "Username does not exist!";
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
            <input type="text" name="admin_name" placeholder="Enter your ID" required>
            <input type="password" name="admin_password" placeholder="Enter your password" required>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
