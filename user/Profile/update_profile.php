<?php
session_start();
require_once '../../db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    // 数据过滤
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone = preg_replace('/[^0-9]/', '', $_POST['phone']);
    $birth_date = $_POST['birth_date'];

    // 数据验证
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format");
    }

    if (!preg_match('/^[0-9]{10,11}$/', $phone)) {
        die("Phone number must be 10-11 digits");
    }

    // 更新数据库
    $stmt = $conn->prepare("UPDATE users SET 
    user_name = ?, 
    email = ?, 
    user_phone_num = ?, 
    birth_date = ?, 
    WHERE user_id = ?");

    $stmt->bind_param("sssssi", 
        $username, 
        $email, 
        $phone, 
        $birth_date, 
        $user_id
    );

    if ($stmt->execute()) {
        $_SESSION['success'] = "Profile updated successfully!";
        header("Location: profile.php");
        exit();
    } else {
        $_SESSION['error'] = "Error updating profile: " . $conn->error;
        header("Location: profile.php");
        exit();
    }
    
    $stmt->close();
}