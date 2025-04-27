<?php
session_start();
header('Content-Type: application/json');

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gogo_supermarket";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

// Get input data
$data = json_decode(file_get_contents('php://input'), true);
$cart_item_id = isset($data['cart_item_id']) ? intval($data['cart_item_id']) : 0;

// Remove item
$delete_item = $conn->prepare("DELETE FROM cart_items WHERE CART_ITEM_ID = ?");
$delete_item->bind_param("i", $cart_item_id);
$delete_item->execute();

// Get updated cart data
$user_id = $_SESSION['user_id'];
$cart_query = $conn->prepare("SELECT COUNT(*) as cart_count, 
                             SUM(ci.QUANTITY) as item_count, 
                             SUM(ci.QUANTITY * p.prod_price) as subtotal
                             FROM cart_items ci
                             JOIN product p ON ci.prod_id = p.prod_id
                             JOIN cart c ON ci.CART_ID = c.CART_ID
                             WHERE c.user_id = ?");
$cart_query->bind_param("i", $user_id);
$cart_query->execute();
$cart_result = $cart_query->get_result();
$cart_data = $cart_result->fetch_assoc();

$delivery_fee = 5.00;
$total = $cart_data['subtotal'] + $delivery_fee;

echo json_encode([
    'success' => true,
    'message' => 'Item removed from cart',
    'cart_count' => $cart_data['cart_count'],
    'cart' => [
        'item_count' => $cart_data['item_count'] ?? 0,
        'subtotal' => $cart_data['subtotal'] ?? 0,
        'delivery_fee' => $delivery_fee,
        'total' => $total
    ]
]);

$conn->close();
?>