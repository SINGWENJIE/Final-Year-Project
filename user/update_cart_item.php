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
$quantity = isset($data['quantity']) ? intval($data['quantity']) : 1;

// Validate cart item exists
$item_check = $conn->prepare("SELECT ci.CART_ITEM_ID, p.prod_id, p.stock 
                             FROM cart_items ci
                             JOIN product p ON ci.prod_id = p.prod_id
                             WHERE ci.CART_ITEM_ID = ?");
$item_check->bind_param("i", $cart_item_id);
$item_check->execute();
$item_result = $item_check->get_result();

if ($item_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Cart item not found']);
    exit;
}

$item = $item_result->fetch_assoc();

// Check stock
if ($quantity > $item['stock']) {
    echo json_encode(['success' => false, 'message' => 'Not enough stock available']);
    exit;
}

// Update quantity
$update_item = $conn->prepare("UPDATE cart_items SET QUANTITY = ? WHERE CART_ITEM_ID = ?");
$update_item->bind_param("ii", $quantity, $cart_item_id);
$update_item->execute();

// Get updated cart data
$user_id = $_SESSION['user_id'];
$cart_query = $conn->prepare("SELECT SUM(ci.QUANTITY) as item_count, 
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
    'message' => 'Cart updated successfully',
    'cart' => [
        'item_count' => $cart_data['item_count'],
        'subtotal' => $cart_data['subtotal'],
        'delivery_fee' => $delivery_fee,
        'total' => $total
    ]
]);

$conn->close();
?>