<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = new mysqli("localhost", "username", "", "gogo_supermarket");
    
    $username = $_POST['username'];
    $password = $_POST['password'];

    // 查询用户
    $stmt = $conn->prepare("SELECT user_id, user_password FROM user WHERE user_name = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['user_password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            header("Location: MainPage.html");
        } else {
            echo "<script>alert('Incorrect Password！'); window.location='Login.html';</script>";
        }
    } else {
        echo "<script>alert('The user does not exist.！'); window.location='Login.html';</script>";
    }
    
    $stmt->close();
    $conn->close();
}
?>