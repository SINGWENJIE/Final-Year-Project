<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['in_wishlist' => false]);
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gogo_supermarket";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode(['in_wishlist' => false]);
    exit();
}

// Get product ID from query string
$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
$user_id = $_SESSION['user_id'];

if ($product_id <= 0) {
    echo json_encode(['in_wishlist' => false]);
    exit();
}

// Check if in wishlist
$check = $conn->prepare("SELECT wishlist_id FROM wishlist WHERE user_id = ? AND prod_id = ?");
$check->bind_param("ii", $user_id, $product_id);
$check->execute();
$check->store_result();

echo json_encode(['in_wishlist' => $check->num_rows > 0]);

$conn->close();
?>