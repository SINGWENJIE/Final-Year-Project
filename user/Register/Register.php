<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = new mysqli("localhost", "root", "", "gogo_supermarket");

    if ($conn->connect_error) {
        die("Database connection failed: " . $conn->connect_error);
    }

    // Validate inputs
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $phone = $conn->real_escape_string($_POST['phone'] ?? null);

    // Password validation
    if (!preg_match('/^(?=.*[!@#$%^&*])(?=.*\d).{8,}$/', $password)) {
        die("Password must contain: 1 special character, 1 number, 8+ length");
    }

    if ($password !== $_POST['confirm_password']) {
        die("Passwords do not match");
    }

    // Check for existing email or username
    $check = $conn->prepare("SELECT email, user_name FROM users WHERE email = ? OR user_name = ?");
    $check->bind_param("ss", $email, $username);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        header("Location: Register.html?error=" . ($row['email'] === $email ? "email_exists" : "username_exists"));
        exit();
    }

    // Insert new user
    $hashed_pw = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("INSERT INTO users (user_name, email, user_password, user_phone_num) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $email, $hashed_pw, $phone);

    if ($stmt->execute()) {
        header("Location: ../Login/Login.html?register=success");
    } else {
        header("Location: Register.html?error=database_error");
    }

    $stmt->close();
    $check->close();
    $conn->close();
}
?>