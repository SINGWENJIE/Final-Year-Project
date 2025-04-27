<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to add items to cart']);
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
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

$user_id = $_SESSION['user_id'];
$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

// Validate quantity
if ($quantity < 1) {
    $quantity = 1;
}

// Check if product exists and has stock
$product_check = $conn->prepare("SELECT stock FROM product WHERE prod_id = ?");
$product_check->bind_param("i", $product_id);
$product_check->execute();
$product_result = $product_check->get_result();

if ($product_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit();
}

$product = $product_result->fetch_assoc();
if ($product['stock'] < $quantity) {
    echo json_encode(['success' => false, 'message' => 'Not enough stock available']);
    exit();
}

// Get or create cart for user
$cart = getOrCreateCart($conn, $user_id);

// Check if product already exists in cart
$existing_item = $conn->prepare("SELECT CART_ITEM_ID, QUANTITY FROM cart_items WHERE CART_ID = ? AND prod_id = ?");
$existing_item->bind_param("ii", $cart['CART_ID'], $product_id);
$existing_item->execute();
$existing_result = $existing_item->get_result();

if ($existing_result->num_rows > 0) {
    // Update existing item
    $existing = $existing_result->fetch_assoc();
    $new_quantity = $existing['QUANTITY'] + $quantity;
    
    // Check stock again with new quantity
    if ($product['stock'] < $new_quantity) {
        echo json_encode(['success' => false, 'message' => 'Cannot add more than available stock']);
        exit();
    }
    
    $update = $conn->prepare("UPDATE cart_items SET QUANTITY = ? WHERE CART_ITEM_ID = ?");
    $update->bind_param("ii", $new_quantity, $existing['CART_ITEM_ID']);
    $update->execute();
} else {
    // Add new item to cart
    $insert = $conn->prepare("INSERT INTO cart_items (CART_ID, prod_id, QUANTITY) VALUES (?, ?, ?)");
    $insert->bind_param("iii", $cart['CART_ID'], $product_id, $quantity);
    $insert->execute();
}

// Get updated cart count
$count_query = $conn->prepare("SELECT COUNT(*) as count FROM cart_items WHERE CART_ID = ?");
$count_query->bind_param("i", $cart['CART_ID']);
$count_query->execute();
$count_result = $count_query->get_result();
$count = $count_result->fetch_assoc()['count'];

echo json_encode([
    'success' => true,
    'message' => 'Product added to cart',
    'cart_count' => $count
]);

$conn->close();

function getOrCreateCart($conn, $user_id) {
    // Check if user has an active cart
    $sql = "SELECT * FROM cart WHERE user_id = ? ORDER BY CREATED_AT DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    // Create new cart
    $sql = "INSERT INTO cart (user_id) VALUES (?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    
    return [
        'CART_ID' => $stmt->insert_id,
        'user_id' => $user_id
    ];
}
?>