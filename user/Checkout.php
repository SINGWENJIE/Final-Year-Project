<?php
session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
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
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

// Get user details
$user_sql = "SELECT * FROM users WHERE user_id = $user_id";
$user_result = $conn->query($user_sql);
$user = $user_result->fetch_assoc();

// Get user addresses
$address_sql = "SELECT * FROM address WHERE user_id = $user_id ORDER BY is_default DESC";
$address_result = $conn->query($address_sql);
$addresses = [];
if ($address_result->num_rows > 0) {
    while($row = $address_result->fetch_assoc()) {
        $addresses[] = $row;
    }
}

// Get cart items with product details
$cart_sql = "SELECT ci.CART_ITEM_ID, ci.QUANTITY, p.prod_id, p.prod_name, p.prod_price, p.prod_image, p.stock 
             FROM cart_items ci
             JOIN product p ON ci.prod_id = p.prod_id
             JOIN cart c ON ci.CART_ID = c.CART_ID
             WHERE c.user_id = $user_id
             ORDER BY ci.CART_ITEM_ID DESC";
$cart_result = $conn->query($cart_sql);

$cart_items = [];
$subtotal = 0;
$item_count = 0;

if ($cart_result->num_rows > 0) {
    while($row = $cart_result->fetch_assoc()) {
        $row['total_price'] = $row['prod_price'] * $row['QUANTITY'];
        $subtotal += $row['total_price'];
        $item_count += $row['QUANTITY'];
        $cart_items[] = $row;
    }
}

// Check for empty cart
if (empty($cart_items)) {
    header("Location: cart.php");
    exit();
}

// Initialize variables
$promo_error = '';
$promo_success = '';
$applied_promo = $_SESSION['applied_promo'] ?? null;

// Handle promo code application
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_promo'])) {
    $promo_code = trim($_POST['promo_code']);
    
    if (!empty($promo_code)) {
        // Check promo code against database
        $promo_sql = "SELECT * FROM promo_code 
                      WHERE CODE = ? 
                      AND VALID_FROM <= CURDATE() 
                      AND VALID_TO >= CURDATE() 
                      AND (MAX_USES IS NULL OR USES_COUNT < MAX_USES)";
        $stmt = $conn->prepare($promo_sql);
        $stmt->bind_param("s", $promo_code);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $applied_promo = $result->fetch_assoc();
            $_SESSION['applied_promo'] = $applied_promo;
            $promo_success = "Promo code applied successfully!";
        } else {
            $promo_error = "Invalid or expired promo code";
            unset($_SESSION['applied_promo']);
            $applied_promo = null;
        }
    } else {
        $promo_error = "Please enter a promo code";
    }
}

// Handle promo code removal
if (isset($_GET['remove_promo'])) {
    unset($_SESSION['applied_promo']);
    $applied_promo = null;
    header("Location: checkout.php");
    exit();
}

// Calculate delivery fee
$delivery_method = isset($_POST['delivery_method']) ? $_POST['delivery_method'] : 'standard';
$delivery_fee = ($delivery_method == 'express') ? 10.00 : 5.00;

// Calculate discount
$discount_amount = 0;
if ($applied_promo && $subtotal >= $applied_promo['MIN_ORDER']) {
    $discount_amount = $applied_promo['DISCOUNT_AMOUNT'];
}

// Calculate total
$total = $subtotal + $delivery_fee - $discount_amount;

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Supermarket</title>
    <link rel="stylesheet" href="../user_assets/css/checkout.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="container">
            <h1><a href="product_list.php">Supermarket</a></h1>
            <nav>
                <a href="product_list.php"><i class="fas fa-store"></i> Products</a>
                <a href="#"><i class="fas fa-heart"></i> Wishlist</a>
                <a href="cart.php"><i class="fas fa-shopping-cart"></i> Cart 
                    <span class="cart-count"><?php echo $item_count; ?></span>
                </a>
                <span class="user-info">
                    <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                    <a href="logout.php" class="logout-btn">Logout</a>
                </span>
            </nav>
        </div>
    </header>

    <main class="container">
        <div class="breadcrumb">
            <a href="product_list.php">Products</a> &gt; 
            <a href="cart.php">Cart</a> &gt; 
            <span>Checkout</span>
        </div>

        <h1 class="page-title">Checkout</h1>

        <div class="checkout-layout">
            <div class="checkout-form">
                <form id="checkoutForm" action="process_order.php" method="POST">
                    <!-- Hidden field for delivery fee -->
                    <input type="hidden" name="delivery_fee" id="delivery_fee" value="<?php echo $delivery_fee; ?>">
                    
                    <!-- Order Summary -->
                    <section class="checkout-section">
                        <h2><i class="fas fa-receipt"></i> Order Summary</h2>
                        <div class="order-summary">
                            <div class="summary-items">
                                <?php foreach ($cart_items as $item): ?>
                                <div class="summary-item">
                                    <div class="item-image">
                                        <img src="../assets/uploads/<?php echo htmlspecialchars($item['prod_image']); ?>" 
                                             alt="<?php echo htmlspecialchars($item['prod_name']); ?>">
                                    </div>
                                    <div class="item-details">
                                        <h4><?php echo htmlspecialchars($item['prod_name']); ?></h4>
                                        <p>Qty: <?php echo $item['QUANTITY']; ?></p>
                                    </div>
                                    <div class="item-price">
                                        RM <?php echo number_format($item['total_price'], 2); ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="summary-totals">
    <div class="summary-row">
        <span>Subtotal (<?php echo $item_count; ?> items)</span>
        <span class="subtotal">RM <?php echo number_format($subtotal, 2); ?></span>
    </div>
    <div class="summary-row">
        <span>Delivery Fee</span>
        <span class="delivery-fee">RM <?php echo number_format($delivery_fee, 2); ?></span>
    </div>
    <?php if ($applied_promo): ?>
    <div class="summary-row promo-discount">
        <span>Promo Discount (<?php echo $applied_promo['CODE']; ?>)</span>
        <span class="discount-amount">-RM <?php echo number_format($applied_promo['DISCOUNT_AMOUNT'], 2); ?></span>
    </div>
    <?php endif; ?>
    <div class="summary-divider"></div>
    <div class="summary-row total">
        <span>Total</span>
        <span class="total-amount">RM <?php echo number_format($total, 2); ?></span>
    </div>
</div>
                    </section>
                    
                    <!-- Delivery Information -->
                    <section class="checkout-section">
                        <h2><i class="fas fa-truck"></i> Delivery Information</h2>
                        
                        <div class="address-selection">
                            <div class="address-options">
                                <?php foreach ($addresses as $address): ?>
                                <div class="address-option">
                                    <input type="radio" name="shipping_address" id="address_<?php echo $address['address_id']; ?>" 
                                           value="<?php echo $address['address_id']; ?>" 
                                           <?php echo $address['is_default'] ? 'checked' : ''; ?> required>
                                    <label for="address_<?php echo $address['address_id']; ?>">
                                        <div class="address-details">
                                            <strong><?php echo htmlspecialchars($address['recipient_name']); ?></strong>
                                            <p><?php echo htmlspecialchars($address['street_address']); ?></p>
                                            <p><?php echo htmlspecialchars($address['city'] . ', ' . $address['state'] . ' ' . $address['postal_code']); ?></p>
                                            <p>Phone: <?php echo htmlspecialchars($address['phone_number']); ?></p>
                                            <?php if ($address['is_default']): ?>
                                                <span class="default-badge">Default</span>
                                            <?php endif; ?>
                                        </div>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                                
                                <div class="address-option new-address">
                                    <input type="radio" name="shipping_address" id="new_address" value="new">
                                    <label for="new_address">
                                        <i class="fas fa-plus"></i> Add New Address
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <!-- New Address Form (hidden by default) -->
                        <div class="new-address-form" id="newAddressForm" style="display: none;">
                            <div class="form-group">
                                <label for="recipient_name">Recipient Name</label>
                                <input type="text" id="recipient_name" name="recipient_name" required>
                            </div>
                            <div class="form-group">
                                <label for="street_address">Street Address</label>
                                <input type="text" id="street_address" name="street_address" required>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="city">City</label>
                                    <input type="text" id="city" name="city" required>
                                </div>
                                <div class="form-group">
                                    <label for="state">State</label>
                                    <input type="text" id="state" name="state" required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="postal_code">Postal Code</label>
                                    <input type="text" id="postal_code" name="postal_code" required>
                                </div>
                                <div class="form-group">
                                    <label for="phone_number">Phone Number</label>
                                    <input type="tel" id="phone_number" name="phone_number" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="note">Delivery Notes (Optional)</label>
                                <textarea id="note" name="note" rows="3"></textarea>
                            </div>
                            <div class="form-group">
                                <input type="checkbox" id="set_default" name="set_default">
                                <label for="set_default">Set as default address</label>
                            </div>
                        </div>
                    </section>
                    
                    <!-- Delivery Method -->
                    <section class="checkout-section">
                        <h2><i class="fas fa-shipping-fast"></i> Delivery Method</h2>
                        <div class="delivery-options">
                            <div class="delivery-option">
                                <input type="radio" name="delivery_method" id="standard_delivery" value="standard" <?php echo $delivery_method == 'standard' ? 'checked' : ''; ?> required>
                                <label for="standard_delivery">
                                    <div class="delivery-details">
                                        <h3>Standard Delivery</h3>
                                        <p>Delivery within 2-3 business days</p>
                                        <p class="delivery-fee-text">RM 5.00</p>
                                    </div>
                                </label>
                            </div>
                            <div class="delivery-option">
                                <input type="radio" name="delivery_method" id="express_delivery" value="express" <?php echo $delivery_method == 'express' ? 'checked' : ''; ?>>
                                <label for="express_delivery">
                                    <div class="delivery-details">
                                        <h3>Express Delivery</h3>
                                        <p>Next business day delivery</p>
                                        <p class="delivery-fee-text">RM 10.00</p>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </section>
                    
                    <!-- Payment Method -->
                    <section class="checkout-section">
                        <h2><i class="fas fa-credit-card"></i> Payment Method</h2>
                        <div class="payment-options">
                            <div class="payment-option">
                                <input type="radio" name="payment_method" id="credit_card" value="credit_card" checked required>
                                <label for="credit_card">
                                    <i class="fab fa-cc-visa"></i> Credit Card
                                </label>
                            </div>
                            <div class="payment-option">
                                <input type="radio" name="payment_method" id="debit_card" value="debit_card">
                                <label for="debit_card">
                                    <i class="fas fa-credit-card"></i> Debit Card
                                </label>
                            </div>
                            <div class="payment-option">
                                <input type="radio" name="payment_method" id="tng_ewallet" value="tng_ewallet">
                                <label for="tng_ewallet">
                                    <i class="fas fa-wallet"></i> Touch 'n Go eWallet
                                </label>
                            </div>
                            <div class="payment-option">
                                <input type="radio" name="payment_method" id="cash_on_delivery" value="cash_on_delivery">
                                <label for="cash_on_delivery">
                                    <i class="fas fa-money-bill-wave"></i> Cash on Delivery
                                </label>
                            </div>
                        </div>
                        
                        <!-- Credit Card Form (shown when credit/debit card is selected) -->
                        <div class="credit-card-form" id="creditCardForm">
                            <div class="form-group">
                                <label for="card_number">Card Number</label>
                                <input type="text" id="card_number" name="card_number" placeholder="1234 5678 9012 3456">
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="expiry_date">Expiry Date</label>
                                    <input type="text" id="expiry_date" name="expiry_date" placeholder="MM/YY">
                                </div>
                                <div class="form-group">
                                    <label for="cvv">CVV</label>
                                    <input type="text" id="cvv" name="cvv" placeholder="123">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="card_name">Name on Card</label>
                                <input type="text" id="card_name" name="card_name" placeholder="John Doe">
                            </div>
                        </div>
                    </section>
                    
                    <!-- Promo Code Section -->
                    <section class="checkout-section">
                        <h2><i class="fas fa-tag"></i> Promo Code</h2>
                        <div class="promo-section">
                            <?php if ($applied_promo): ?>
                                <div class="applied-promo">
                                    <span>Applied: <?php echo $applied_promo['CODE']; ?> (-RM <?php echo number_format($applied_promo['DISCOUNT_AMOUNT'], 2); ?>)</span>
                                    <a href="checkout.php?remove_promo=1" class="remove-promo">Remove</a>
                                </div>
                            <?php else: ?>
                                <form method="post" action="checkout.php">
                                    <div class="promo-input">
                                        <input type="text" id="promo_code" name="promo_code" 
                                               placeholder="Enter promo code" 
                                               value="<?php echo isset($_POST['promo_code']) ? htmlspecialchars($_POST['promo_code']) : ''; ?>">
                                        <button type="submit" name="apply_promo" class="btn">Apply</button>
                                    </div>
                                    <?php if (!empty($promo_error)): ?>
                                        <div class="promo-message error"><?php echo $promo_error; ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($promo_success)): ?>
                                        <div class="promo-message success"><?php echo $promo_success; ?></div>
                                    <?php endif; ?>
                                </form>
                            <?php endif; ?>
                        </div>
                    </section>
                    <div class="checkout-actions">
                        <a href="cart.php" class="btn btn-back"><i class="fas fa-arrow-left"></i> Back to Cart</a>
                        <button type="submit" class="btn btn-primary" id="placeOrderBtn">
                            <i class="fas fa-lock"></i> Place Order
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3>About Us</h3>
                <p>Your one-stop supermarket for all daily needs.</p>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="product_list.php">Products</a></li>
                    <li><a href="#">Special Offers</a></li>
                    <li><a href="#">Contact Us</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2023 Supermarket. All rights reserved.</p>
        </div>
    </footer>

    <script src="../user_assets/js/checkout.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Show/hide new address form
            const newAddressRadio = document.getElementById('new_address');
            const newAddressForm = document.getElementById('newAddressForm');
            
            if (newAddressRadio && newAddressForm) {
                newAddressRadio.addEventListener('change', function() {
                    if (this.checked) {
                        newAddressForm.style.display = 'block';
                    }
                });
                
                // Hide new address form if another address is selected
                document.querySelectorAll('input[name="shipping_address"]').forEach(radio => {
                    if (radio.id !== 'new_address') {
                        radio.addEventListener('change', function() {
                            newAddressForm.style.display = 'none';
                        });
                    }
                });
            }
            
            // Show/hide credit card form based on payment method
            const creditCardForm = document.getElementById('creditCardForm');
            if (creditCardForm) {
                function toggleCreditCardForm() {
                    const selectedMethod = document.querySelector('input[name="payment_method"]:checked').value;
                    if (selectedMethod === 'credit_card' || selectedMethod === 'debit_card') {
                        creditCardForm.style.display = 'block';
                    } else {
                        creditCardForm.style.display = 'none';
                    }
                }
                
                // Initial toggle
                toggleCreditCardForm();
                
                // Toggle when payment method changes
                document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
                    radio.addEventListener('change', toggleCreditCardForm);
                });
            }
            
            // Update delivery fee when delivery method changes
            document.querySelectorAll('input[name="delivery_method"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    const deliveryFeeElement = document.querySelector('.delivery-fee');
                    const deliveryFeeInput = document.getElementById('delivery_fee');
                    let deliveryFee = 5.00; // Standard delivery
                    
                    if (this.value === 'express') {
                        deliveryFee = 10.00; // Express delivery
                    }
                    
                    // Update display
                    deliveryFeeElement.textContent = 'RM ' + deliveryFee.toFixed(2);
                    
                    // Update hidden input
                    deliveryFeeInput.value = deliveryFee;
                    
                    // Recalculate total
                    const subtotal = parseFloat(document.querySelector('.subtotal').textContent.replace('RM ', ''));
                    const discountRow = document.querySelector('.summary-row.promo-discount');
                    let discount = 0;
                    
                    if (discountRow) {
                        discount = parseFloat(discountRow.querySelector('.discount-amount').textContent.replace('-RM ', ''));
                    }
                    
                    const newTotal = (subtotal - discount) + deliveryFee;
                    document.querySelector('.total-amount').textContent = 'RM ' + newTotal.toFixed(2);
                });
            });
            
            // Form validation before submission
            const checkoutForm = document.getElementById('checkoutForm');
            if (checkoutForm) {
                checkoutForm.addEventListener('submit', function(e) {
                    // In a real application, you would do more thorough validation
                    const selectedAddress = document.querySelector('input[name="shipping_address"]:checked');
                    
                    if (!selectedAddress) {
                        e.preventDefault();
                        showToast('Please select a delivery address', 'error');
                        return;
                    }
                    
                    if (selectedAddress.value === 'new') {
                        // Validate new address fields
                        const recipientName = document.getElementById('recipient_name').value.trim();
                        const streetAddress = document.getElementById('street_address').value.trim();
                        const city = document.getElementById('city').value.trim();
                        const state = document.getElementById('state').value.trim();
                        const postalCode = document.getElementById('postal_code').value.trim();
                        const phoneNumber = document.getElementById('phone_number').value.trim();
                        
                        if (!recipientName || !streetAddress || !city || !state || !postalCode || !phoneNumber) {
                            e.preventDefault();
                            showToast('Please fill in all required address fields', 'error');
                            return;
                        }
                    }
                    
                    const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
                    
                    if ((paymentMethod === 'credit_card' || paymentMethod === 'debit_card') && 
                        !validateCreditCard()) {
                        e.preventDefault();
                        return;
                    }
                    
                    // If everything is valid, show a success message
                    showToast('Order placed successfully!', 'success');
                });
            }
            
            // Credit card validation
            function validateCreditCard() {
                const cardNumber = document.getElementById('card_number').value.trim();
                const expiryDate = document.getElementById('expiry_date').value.trim();
                const cvv = document.getElementById('cvv').value.trim();
                const cardName = document.getElementById('card_name').value.trim();
                
                if (!cardNumber || !expiryDate || !cvv || !cardName) {
                    showToast('Please fill in all credit card details', 'error');
                    return false;
                }
                
                // Simple validation for demo purposes
                if (!/^\d{16}$/.test(cardNumber.replace(/\s/g, ''))) {
                    showToast('Please enter a valid 16-digit card number', 'error');
                    return false;
                }
                
                if (!/^\d{2}\/\d{2}$/.test(expiryDate)) {
                    showToast('Please enter expiry date in MM/YY format', 'error');
                    return false;
                }
                
                if (!/^\d{3,4}$/.test(cvv)) {
                    showToast('Please enter a valid CVV (3 or 4 digits)', 'error');
                    return false;
                }
                
                return true;
            }
            
            // Toast notification function
            function showToast(message, type) {
                const toast = document.createElement('div');
                toast.className = `toast-notification ${type}`;
                toast.textContent = message;
                document.body.appendChild(toast);
                
                setTimeout(() => {
                    toast.classList.add('show');
                }, 100);
                
                setTimeout(() => {
                    toast.classList.remove('show');
                    setTimeout(() => {
                        document.body.removeChild(toast);
                    }, 300);
                }, 3000);
            }
        });
    </script>
</body>
</html>