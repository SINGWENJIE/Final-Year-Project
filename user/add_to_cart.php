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
$product_id = isset($data['product_id']) ? intval($data['product_id']) : 0;
$quantity = isset($data['quantity']) ? intval($data['quantity']) : 1;
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

// Validate product exists
$product_check = $conn->prepare("SELECT prod_id, prod_name, prod_price, stock FROM product WHERE prod_id = ?");
$product_check->bind_param("i", $product_id);
$product_check->execute();
$product_result = $product_check->get_result();

if ($product_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit;
}

$product = $product_result->fetch_assoc();

// Check stock
if ($quantity > $product['stock']) {
    echo json_encode(['success' => false, 'message' => 'Not enough stock available']);
    exit;
}

// Get or create cart
if ($user_id > 0) {
    // For logged-in users
    $cart_query = $conn->prepare("SELECT CART_ID FROM cart WHERE user_id = ?");
    $cart_query->bind_param("i", $user_id);
    $cart_query->execute();
    $cart_result = $cart_query->get_result();
    
    if ($cart_result->num_rows > 0) {
        $cart = $cart_result->fetch_assoc();
        $cart_id = $cart['CART_ID'];
    } else {
        $insert_cart = $conn->prepare("INSERT INTO cart (user_id) VALUES (?)");
        $insert_cart->bind_param("i", $user_id);
        $insert_cart->execute();
        $cart_id = $conn->insert_id;
    }
} else {
    // For guests (using session)
    if (!isset($_SESSION['cart_id'])) {
        $insert_cart = $conn->prepare("INSERT INTO cart (SESSION_ID) VALUES (?)");
        $session_id = session_id();
        $insert_cart->bind_param("s", $session_id);
        $insert_cart->execute();
        $_SESSION['cart_id'] = $conn->insert_id;
    }
    $cart_id = $_SESSION['cart_id'];
}

// Add item to cart
$check_item = $conn->prepare("SELECT CART_ITEM_ID, QUANTITY FROM cart_items WHERE CART_ID = ? AND prod_id = ?");
$check_item->bind_param("ii", $cart_id, $product_id);
$check_item->execute();
$item_result = $check_item->get_result();

if ($item_result->num_rows > 0) {
    // Update existing item
    $item = $item_result->fetch_assoc();
    $new_quantity = $item['QUANTITY'] + $quantity;
    $update_item = $conn->prepare("UPDATE cart_items SET QUANTITY = ? WHERE CART_ITEM_ID = ?");
    $update_item->bind_param("ii", $new_quantity, $item['CART_ITEM_ID']);
    $update_item->execute();
} else {
    // Add new item
    $insert_item = $conn->prepare("INSERT INTO cart_items (CART_ID, prod_id, QUANTITY) VALUES (?, ?, ?)");
    $insert_item->bind_param("iii", $cart_id, $product_id, $quantity);
    $insert_item->execute();
}

// Get cart count
$count_query = $conn->prepare("SELECT SUM(QUANTITY) as total FROM cart_items WHERE CART_ID = ?");
$count_query->bind_param("i", $cart_id);
$count_query->execute();
$count_result = $count_query->get_result();
$count = $count_result->fetch_assoc()['total'] ?? 0;

echo json_encode([
    'success' => true,
    'message' => 'Product added to cart',
    'cart_count' => $count
]);

$conn->close();
?>