<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gogo_supermarket";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
$user_id = $_SESSION['user_id'];

// Verify the order belongs to the user
$verify_sql = "SELECT order_id FROM orders WHERE order_id = ? AND user_id = ?";
$verify_stmt = $conn->prepare($verify_sql);
$verify_stmt->bind_param("ii", $order_id, $user_id);
$verify_stmt->execute();
$verify_result = $verify_stmt->get_result();

if ($verify_result->num_rows === 0) {
    echo json_encode(['error' => 'Order not found or unauthorized']);
    exit();
}

// Get products from this order that haven't been reviewed yet
$sql = "SELECT oi.prod_id, p.prod_name 
        FROM order_item oi
        JOIN product p ON oi.prod_id = p.prod_id
        LEFT JOIN feedback f ON oi.prod_id = f.prod_id AND f.order_id = oi.order_id AND f.user_id = ?
        WHERE oi.order_id = ? AND f.feedback_id IS NULL";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $order_id);
$stmt->execute();
$result = $stmt->get_result();

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

echo json_encode($products);
$conn->close();
?>