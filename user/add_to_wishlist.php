<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to add items to wishlist']);
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
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$product_id = isset($data['product_id']) ? intval($data['product_id']) : 0;
$user_id = $_SESSION['user_id'];

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product']);
    exit();
}

// Check if product exists
$product_check = $conn->prepare("SELECT prod_id FROM product WHERE prod_id = ?");
$product_check->bind_param("i", $product_id);
$product_check->execute();
$product_check->store_result();

if ($product_check->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit();
}

// Check if already in wishlist
$wishlist_check = $conn->prepare("SELECT wishlist_id FROM wishlist WHERE user_id = ? AND prod_id = ?");
$wishlist_check->bind_param("ii", $user_id, $product_id);
$wishlist_check->execute();
$wishlist_check->store_result();

if ($wishlist_check->num_rows > 0) {
    // Remove from wishlist
    $remove = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND prod_id = ?");
    $remove->bind_param("ii", $user_id, $product_id);
    
    if ($remove->execute()) {
        echo json_encode(['success' => true, 'action' => 'removed']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to remove from wishlist']);
    }
} else {
    // Add to wishlist
    $add = $conn->prepare("INSERT INTO wishlist (user_id, prod_id) VALUES (?, ?)");
    $add->bind_param("ii", $user_id, $product_id);
    
    if ($add->execute()) {
        echo json_encode(['success' => true, 'action' => 'added']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add to wishlist']);
    }
}

$conn->close();
?>