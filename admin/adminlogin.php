<?php
session_start();
include 'db_connection.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $admin_id = $_POST['admin_id'];
    $password = $_POST['password'];

    $superadmin = [
        "id" => "superadmin",
        "password" => "password" 
    ];

    if ($admin_id == $superadmin['id'] && $password == $superadmin['password']) {
        $_SESSION['admin_id'] = "superadmin";
        $_SESSION['role'] = "superadmin";
        header("Location: ../superadmin/superadmin_dashboard.php");
        exit();
    }

    $sql = "SELECT * FROM admin WHERE username = '$admin_id'";
    $result = mysqli_query($conn, $sql);//check

    if ($row = mysqli_fetch_assoc($result)) {//get the result first row
        if ($password == $row['password']) {  
            $_SESSION['admin_id'] = $admin_id;
            $_SESSION['role'] = "admin";
            header("Location: admin_dashboard.php");
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
            <input type="text" name="admin_id" placeholder="Enter your ID" required>
            <input type="password" name="password" placeholder="Enter your password" required>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>