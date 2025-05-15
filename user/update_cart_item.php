<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to update cart']);
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
$data = json_decode(file_get_contents('php://input'), true);
$cart_item_id = isset($data['cart_item_id']) ? intval($data['cart_item_id']) : 0;
$quantity = isset($data['quantity']) ? intval($data['quantity']) : 1;

if ($cart_item_id <= 0 || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit();
}

// Verify the cart item belongs to the user
$verify_sql = "SELECT ci.CART_ITEM_ID 
               FROM cart_items ci
               JOIN cart c ON ci.CART_ID = c.CART_ID
               WHERE ci.CART_ITEM_ID = ? AND c.user_id = ?";
$verify_stmt = $conn->prepare($verify_sql);
$verify_stmt->bind_param("ii", $cart_item_id, $_SESSION['user_id']);
$verify_stmt->execute();
$verify_result = $verify_stmt->get_result();

if ($verify_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid cart item']);
    exit();
}

// Update quantity
$update_sql = "UPDATE cart_items SET QUANTITY = ? WHERE CART_ITEM_ID = ?";
$update_stmt = $conn->prepare($update_sql);
$update_stmt->bind_param("ii", $quantity, $cart_item_id);

if ($update_stmt->execute()) {
    // Get updated cart data
    $cart_sql = "SELECT SUM(ci.QUANTITY) as item_count, SUM(ci.QUANTITY * p.prod_price) as subtotal
                 FROM cart_items ci
                 JOIN product p ON ci.prod_id = p.prod_id
                 JOIN cart c ON ci.CART_ID = c.CART_ID
                 WHERE c.user_id = ?";
    $cart_stmt = $conn->prepare($cart_sql);
    $cart_stmt->bind_param("i", $_SESSION['user_id']);
    $cart_stmt->execute();
    $cart_result = $cart_stmt->get_result();
    $cart_data = $cart_result->fetch_assoc();
    
    echo json_encode([
        'success' => true,
        'cart' => [
            'item_count' => $cart_data['item_count'] ?? 0,
            'subtotal' => $cart_data['subtotal'] ?? 0,
            'total' => $cart_data['subtotal'] ?? 0 // Add delivery fee if needed
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update cart']);
}

$conn->close();
?>