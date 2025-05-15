<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gogo_supermarket";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Get POST data
$order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
$prod_id = isset($_POST['prod_id']) ? intval($_POST['prod_id']) : 0;
$rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
$comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';
$user_id = $_SESSION['user_id'];

// Validate inputs
if ($order_id <= 0 || $prod_id <= 0 || $rating < 1 || $rating > 5 || empty($comment)) {
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    exit();
}

// Verify the order belongs to the user and contains the product
$verify_sql = "SELECT oi.order_item_id 
               FROM order_item oi
               JOIN orders o ON oi.order_id = o.order_id
               WHERE oi.order_id = ? AND oi.prod_id = ? AND o.user_id = ?";
$verify_stmt = $conn->prepare($verify_sql);
$verify_stmt->bind_param("iii", $order_id, $prod_id, $user_id);
$verify_stmt->execute();
$verify_result = $verify_stmt->get_result();

if ($verify_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid order or product']);
    exit();
}

// Check if review already exists for this product in this order
$check_sql = "SELECT feedback_id FROM feedback 
              WHERE order_id = ? AND prod_id = ? AND user_id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("iii", $order_id, $prod_id, $user_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'You have already reviewed this product']);
    exit();
}

// Insert the review
$insert_sql = "INSERT INTO feedback (user_id, order_id, prod_id, rating, comment, feedback_date, is_approved)
               VALUES (?, ?, ?, ?, ?, NOW(), 0)";
$insert_stmt = $conn->prepare($insert_sql);
$insert_stmt->bind_param("iiiis", $user_id, $order_id, $prod_id, $rating, $comment);

if ($insert_stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Thank you for your review!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to submit review']);
}

$conn->close();
?>