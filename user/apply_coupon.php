<?php
session_start();

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

// Get the coupon code from POST data
$data = json_decode(file_get_contents('php://input'), true);
$couponCode = $conn->real_escape_string($data['coupon_code'] ?? '');

// Validate coupon code
if (empty($couponCode)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a coupon code']);
    exit;
}

// Get user's cart
$user_id = $_SESSION['user_id'];

// Get cart subtotal and item count
$cart_sql = "SELECT c.CART_ID, 
             SUM(p.prod_price * ci.QUANTITY) as subtotal,
             SUM(ci.QUANTITY) as item_count
             FROM cart c
             JOIN cart_items ci ON c.CART_ID = ci.CART_ID
             JOIN product p ON ci.prod_id = p.prod_id
             WHERE c.user_id = $user_id
             GROUP BY c.CART_ID";
$cart_result = $conn->query($cart_sql);

if ($cart_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Your cart is empty']);
    exit;
}

$cart = $cart_result->fetch_assoc();
$subtotal = $cart['subtotal'];
$item_count = $cart['item_count'];
$cart_id = $cart['CART_ID'];

// Check coupon validity
$current_date = date('Y-m-d');
$coupon_sql = "SELECT * FROM promo_code 
               WHERE CODE = '$couponCode' 
               AND VALID_FROM <= '$current_date' 
               AND (VALID_TO IS NULL OR VALID_TO >= '$current_date')
               AND (MAX_USES IS NULL OR USES_COUNT < MAX_USES)";
$coupon_result = $conn->query($coupon_sql);

if ($coupon_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid or expired coupon code']);
    exit;
}

$coupon = $coupon_result->fetch_assoc();

// Check minimum order amount
if ($subtotal < $coupon['MIN_ORDER']) {
    echo json_encode([
        'success' => false, 
        'message' => 'This coupon requires a minimum order of RM ' . number_format($coupon['MIN_ORDER'], 2)
    ]);
    exit;
}

// Calculate discount
$discount = $coupon['DISCOUNT_AMOUNT'];
$delivery_fee = 5.00; // Your default delivery fee
$total = max(0, $subtotal - $discount + $delivery_fee); // Ensure total doesn't go negative

// Store the applied coupon in session
$_SESSION['applied_coupon'] = [
    'code' => $coupon['CODE'],
    'discount' => $discount
];

// Return updated cart data
echo json_encode([
    'success' => true,
    'message' => 'Coupon applied successfully',
    'cart' => [
        'subtotal' => number_format($subtotal, 2),
        'discount' => number_format($discount, 2),
        'delivery_fee' => number_format($delivery_fee, 2),
        'total' => number_format($total, 2),
        'item_count' => $item_count,
        'coupon_code' => $coupon['CODE']
    ]
]);

$conn->close();
?>