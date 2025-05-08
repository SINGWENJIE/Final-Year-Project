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


// Calculate delivery fee
$delivery_method = isset($_POST['delivery_method']) ? $_POST['delivery_method'] : 'standard';
$delivery_fee = ($delivery_method == 'express') ? 10.00 : 5.00;

$total = $subtotal + $delivery_fee - (isset($_POST['discount_amount']) ? (float)$_POST['discount_amount'] : 0);

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
        <div class="auth-section">
            <ul class="auth-links">
                <li><a href="../Profile/Profile.php">My Account</a></li>
                <li><a href="#">All Orders</a></li>
                <li><a href="#">Member</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
            <a href="Cart.php" class="shopping-cart-link">
                <img src="../image/cart.png" alt="Cart" class="shopping-cart">
            </a>
        </div>

        <div class="header-main">
            <a href="MainPage/MainPage.php">
                <img src="../image/gogoname.png" alt="GOGO Logo">
            </a>
        </div>

        <nav>
            <ul class="nav-links">
                <li><a href="MainPage/MainPage.php">Menu</a></li>
                <li><a href="AboutUs/AboutUs.html">About GOGO</a></li>
                <li><a href="CustomerService.html">Customer Service</a></li>
            </ul>
        </nav>
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
                    <input type="hidden" name="discount_amount" id="discount_amount" value="0">
                    <input type="hidden" name="promo_code" id="promo_code_field" value="">
                    
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
                                <div class="summary-row promo-discount" style="display: none;">
                                    <span>Promo Discount</span>
                                    <span class="discount-amount">-RM 0.00</span>
                                </div>
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
                                <input type="text" id="recipient_name" name="recipient_name">
                            </div>
                            <div class="form-group">
                                <label for="street_address">Street Address</label>
                                <input type="text" id="street_address" name="street_address">
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="city">City</label>
                                    <input type="text" id="city" name="city">
                                </div>
                                <div class="form-group">
                                    <label for="state">State</label>
                                    <input type="text" id="state" name="state">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="postal_code">Postal Code</label>
                                    <input type="text" id="postal_code" name="postal_code">
                                </div>
                                <div class="form-group">
                                    <label for="phone_number">Phone Number</label>
                                    <input type="tel" id="phone_number" name="phone_number">
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
                                    <i class="fab fa-cc-visa"></i> <i class="fab fa-cc-mastercard"></i> Credit Card
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
                                <input type="text" id="card_number" name="card_number" 
                                    placeholder="4242 4242 4242 4242" 
                                    maxlength="19"
                                    pattern="[0-9\s]{16,19}">
                                <small class="card-hint">Visa (starts with 4) or Mastercard (starts with 5)</small>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="expiry_date">Expiry Date</label>
                                    <input type="text" id="expiry_date" name="expiry_date" 
                                        placeholder="MM/YY" maxlength="5"
                                        pattern="(0[1-9]|1[0-2])\/[0-9]{2}">
                                </div>
                                <div class="form-group">
                                    <label for="cvv">CVV</label>
                                    <input type="text" id="cvv" name="cvv" 
                                        placeholder="123" maxlength="3"
                                        pattern="[0-9]{3}">
                                    <small class="cvv-hint">3 digits on back of card</small>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="card_name">Name on Card</label>
                                <input type="text" id="card_name" name="card_name" 
                                    placeholder="e.g. AHMAD BIN ABDULLAH"
                                    pattern="[a-zA-Z\s\.\']+">
                            </div>
                            <div class="form-group">
                                <input type="checkbox" id="save_card" name="save_card">
                                <label for="save_card">Save this card for future purchases</label>
                            </div>
                        </div>
                    </section>
                    
                    <!-- Promo Code Section -->
                    <section class="checkout-section">
                        <h2><i class="fas fa-tag"></i> Promo Code</h2>
                        <div class="promo-section">
                            <div class="promo-input">
                                <input type="text" id="promoCode" placeholder="Enter Promo Code">
                                <button type="button" onclick="applyPromo()" class="btn">Apply</button>
                            </div>
                            <p id="discountInfo" class="promo-message"></p>
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

    <div class="footer-nav">
        <div class="footer-column">
            <h4>Our Helpline</h4>
            <ul>
                <li><a href="">MR.SING</a></li>
                <li><a href="">MR.PIOW</a></li>
                <li><a href="">MR.CHEW</a></li>
            </ul>
        </div>
    
        <div class="footer-column">
            <h4>News & Media</h4>
            <ul>
                <li><a href="#">Press Release</a></li>
                <li><a href="#">News Article</a></li>
            </ul>
        </div>
    
        <div class="footer-column">
            <h4>Policies</h4>
            <ul>
                <li><a href="#">Privacy Policy</a></li>
                <li><a href="../TermsConditions/TermsConditions.html">Terms & Conditions</a></li>
                <li><a href="#">Anti Bribery Policies</a></li>
                <li><a href="#">Electrical Policy</a></li>
            </ul>
        </div>
    
        <div class="footer-column">
            <h4>&nbsp;</h4>
            <ul>
                <li><a href="#">Return Policy</a></li>
                <li><a href="#">Product Policy</a></li>
                <li><a href="#">Halal Statement</a></li>
            </ul>
        </div>
    
        <div class="footer-column">
            <ul>
                <li>
                    <a href="https://www.instagram.com/cheeew.05?igsh=MTBvcTQ5MXR0emNidQ%3D%3D&utm_source=qr">
                        <i class="fab fa-instagram" style="font-size: 30px; margin-top: 75px;"></i>
                      </a>                      
                </li>
                <li>&copy; GOGO_SUPERMARKET</li>
            </ul>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
    // Define valid promo codes
    const validPromoCodes = {
        "PROMOCODE": 20.00,
        "WELCOME10": 10.00,
        "CHEW": 20.00,
        "RM5OFF": 5.00,
        "DISCOUNT10": 10.00,
        "SAVE5": 5.00
    };

    let discount = 0;
    
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
            updateOrderTotal();
        });
    });
    
    // Promo code application
    window.applyPromo = function() {
        const promoCode = document.getElementById("promoCode").value.trim().toUpperCase();
        const discountInfo = document.getElementById("discountInfo");
    
        if (validPromoCodes.hasOwnProperty(promoCode)) {
            // Valid promo code
            discount = validPromoCodes[promoCode];
            discountInfo.innerText = `Promo Applied: ${promoCode} (-RM ${discount.toFixed(2)})`;
            discountInfo.style.color = "green";
            discountInfo.className = "promo-message success";
        
            // Update hidden fields
            document.getElementById('promo_code_field').value = promoCode;
            document.getElementById('discount_amount').value = discount;
        
            updateOrderTotal();
        } else {
            // Invalid promo code
            discount = 0;
            discountInfo.innerText = "❌ Invalid or expired promo code!";
            discountInfo.style.color = "red";
            discountInfo.className = "promo-message error";
            document.getElementById('promo_code_field').value = "";
            document.getElementById('discount_amount').value = 0;
            updateOrderTotal();
        }
    };
    
    // Function to update order total
    function updateOrderTotal() {
    const subtotal = parseFloat(document.querySelector('.subtotal').textContent.replace('RM ', ''));
    const deliveryFee = parseFloat(document.querySelector('.delivery-fee').textContent.replace('RM ', ''));
    const total = (subtotal - discount) + deliveryFee;
    
    // Update discount row
    const discountRow = document.querySelector('.summary-row.promo-discount');
    const discountAmount = document.querySelector('.discount-amount');
    
    if (discount > 0) {
        discountRow.style.display = 'flex';
        discountAmount.textContent = `-RM ${discount.toFixed(2)}`;
    } else {
        discountRow.style.display = 'none';
    }
    
    // Update total
    document.querySelector('.total-amount').textContent = 'RM ' + total.toFixed(2);
    
    // Update hidden form values if needed
    document.getElementById('delivery_fee').value = deliveryFee;

    document.getElementById('discount_amount').value = discount;
    }
    
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
    
    // Enhanced Malaysian credit card validation
    function validateCreditCard() {
        const cardNumber = document.getElementById('card_number').value.trim().replace(/\s/g, '');
        const expiryDate = document.getElementById('expiry_date').value.trim();
        const cvv = document.getElementById('cvv').value.trim();
        const cardName = document.getElementById('card_name').value.trim();
    
        // Validate card number (Malaysian cards typically start with 4 or 5)
        if (!/^[45]\d{15}$/.test(cardNumber)) {
            showToast('Please enter a valid 16-digit Visa (starts with 4) or Mastercard (starts with 5)', 'error');
            return false;
        }
    
        // Validate expiry date
        if (!/^(0[1-9]|1[0-2])\/\d{2}$/.test(expiryDate)) {
            showToast('Please enter expiry date in MM/YY format (e.g. 12/25)', 'error');
            return false;
        }
    
        // Check if card is expired
        const [month, year] = expiryDate.split('/');
        const currentYear = new Date().getFullYear() % 100;
        const currentMonth = new Date().getMonth() + 1;
    
        if (parseInt(year) < currentYear || 
            (parseInt(year) === currentYear && parseInt(month) < currentMonth)) {
            showToast('This card has expired. Please use a valid card.', 'error');
            return false;
        }
    
        // Validate CVV
        if (!/^\d{3}$/.test(cvv)) {
            showToast('Please enter a valid 3-digit CVV', 'error');
            return false;
        }
    
        // Validate card name (allowing for Malaysian names with bin/binti)
        if (!/^[a-zA-Z\s\.\'\-]+$/.test(cardName) || cardName.length < 3) {
            showToast('Please enter the name as it appears on your card', 'error');
            return false;
        }
    
        return true;
    }

    // Add input formatting for card number
    document.getElementById('card_number').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\s/g, '');
        if (value.length > 0) {
            value = value.match(new RegExp('.{1,4}', 'g')).join(' ');
        }
        e.target.value = value;
    });

    // Add input formatting for expiry date
    document.getElementById('expiry_date').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 2) {
            value = value.substring(0, 2) + '/' + value.substring(2, 4);
        }
        e.target.value = value;
    });

    // Restrict CVV input to numbers only
    document.getElementById('cvv').addEventListener('input', function(e) {
        e.target.value = e.target.value.replace(/\D/g, '');
    });
    
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
    
    // Initialize order total
    updateOrderTotal();
    });
    </script>
</body>
</html>