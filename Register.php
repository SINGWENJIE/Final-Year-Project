<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = new mysqli("localhost", "root", "", "gogo_supermarket");
    
    // 验证密码复杂度
    $password = $_POST['password'];
    if (!preg_match('/^(?=.*[!@#$%^&*])(?=.*\d).{8,}$/', $password)) {
        die("The password must contain at least 1 special character, 1 number, and be at least 8 characters long");
    }

    // 检查重复密码
    if ($password !== $_POST['confirm_password']) {
        die("Password not match");
    }

    // 哈希密码
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // 预处理语句防止SQL注入
    $stmt = $conn->prepare("INSERT INTO user (user_name, email, user_password, user_phone_num) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $_POST['username'], $_POST['email'], $hashed_password, $_POST['phone']);

    if ($stmt->execute()) {
        header("Location: Login.html?register=success");
    } else {
        echo "Registration failde: " . $conn->error;
    }
    
    $stmt->close();
    $conn->close();
}
?>