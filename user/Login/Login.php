<?php
session_start();

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 使用正确的数据库凭据 ✅
    $conn = new mysqli("localhost", "root", "", "gogo_supermarket");
    
    // 检查连接是否成功
    if ($conn->connect_error) {
        die("数据库连接失败: " . $conn->connect_error);
    }
    
    $username = $_POST['username'];
    $password = $_POST['password'];

    // 查询用户
    $stmt = $conn->prepare("SELECT user_id, user_password FROM users WHERE user_name = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['user_password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            header("Location: ../MainPage/MainPage.html");
        } else {
            echo "<script>alert('Incorrect Password！'); window.location='../MainPage/MainPage.html';</script>";
        }
    } else {
        echo "<script>alert('The user does not exist.！'); window.location='../MainPage/MainPage.html';</script>";
    }
    
    $stmt->close();
    $conn->close();
}
?>