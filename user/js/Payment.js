document.addEventListener('DOMContentLoaded', function() {
    // Initialize the page
    initPaymentPage();
    
    // Form submission handler
    document.getElementById('paymentForm').addEventListener('submit', function(e) {
        e.preventDefault();
        processPayment();
    });
});

function initPaymentPage() {
    // Set up event listeners for payment methods
    document.querySelectorAll('.payment-method').forEach(method => {
        method.addEventListener('click', function() {
            selectPaymentMethod(this.querySelector('input').value);
        });
    });
    
    // Select first payment method by default
    const firstMethod = document.querySelector('.payment-method');
    firstMethod.classList.add('selected');
}

function selectPaymentMethod(method) {
    const creditCardForm = document.getElementById('creditCardForm');
    
    if (method === 'credit_card') {
        creditCardForm.classList.add('active');
        // Make credit card fields required
        setCreditCardFieldsRequired(true);
    } else {
        creditCardForm.classList.remove('active');
        // Make credit card fields not required
        setCreditCardFieldsRequired(false);
    }
    
    // Update selected style
    document.querySelectorAll('.payment-method').forEach(el => {
        el.classList.remove('selected');
    });
    event.currentTarget.classList.add('selected');
}

function setCreditCardFieldsRequired(required) {
    document.getElementById('card_number').required = required;
    document.getElementById('card_name').required = required;
    document.getElementById('card_expiry').required = required;
    document.getElementById('card_cvv').required = required;
}

function formatCardNumber(input) {
    // Remove all non-digits
    let value = input.value.replace(/\D/g, '');
    
    // Add space after every 4 digits
    value = value.replace(/(\d{4})(?=\d)/g, '$1 ');
    
    // Update the input value
    input.value = value;
}

function formatExpiryDate(input) {
    let value = input.value.replace(/\D/g, '');
    
    if (value.length > 2) {
        value = value.substring(0, 2) + '/' + value.substring(2, 4);
    }
    
    input.value = value;
}

function processPayment() {
    // Get selected payment method
    const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
    
    // Validate form
    if (paymentMethod === 'credit_card') {
        if (!validateCreditCard()) {
            return;
        }
    }
    
    // In a real app, you would process the payment here
    // For demo purposes, we'll just show a success message
    alert('Payment processed successfully! Redirecting to confirmation page...');
    
    // Redirect to confirmation page
    window.location.href = 'confirmation.html';
}

function validateCreditCard() {
    const cardNumber = document.getElementById('card_number').value.replace(/\s/g, '');
    const cardName = document.getElementById('card_name').value.trim();
    const cardExpiry = document.getElementById('card_expiry').value;
    const cardCvv = document.getElementById('card_cvv').value.trim();
    
    // Simple validation
    if (cardNumber.length < 16) {
        alert('Please enter a valid 16-digit card number');
        return false;
    }
    
    if (cardName === '') {
        alert('Please enter the name on card');
        return false;
    }
    
    if (!/^\d{2}\/\d{2}$/.test(cardExpiry)) {
        alert('Please enter a valid expiry date in MM/YY format');
        return false;
    }
    
    if (cardCvv.length < 3) {
        alert('Please enter a valid CVV');
        return false;
    }
    
    return true;
}