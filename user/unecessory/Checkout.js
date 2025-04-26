document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const checkoutForm = document.getElementById('checkoutForm');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function(e) {
            // In a real app, you would add more validation here
            const shippingAddress = document.querySelector('input[name="shipping_address"]:checked');
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
            
            if (!shippingAddress) {
                e.preventDefault();
                alert('Please select a shipping address');
                return;
            }
            
            if (!paymentMethod) {
                e.preventDefault();
                alert('Please select a payment method');
                return;
            }
            
            // If using a payment gateway, you would process payment here
            // For demo, we'll just submit the form to the server
        });
    }
    
    // Address selection
    const addressOptions = document.querySelectorAll('.address-option input[type="radio"]');
    addressOptions.forEach(option => {
        option.addEventListener('change', function() {
            document.querySelectorAll('.address-option label').forEach(label => {
                label.style.borderColor = '#ddd';
            });
            
            if (this.checked) {
                this.nextElementSibling.style.borderColor = '#28a745';
            }
        });
    });
    
    // Payment method selection
    const paymentOptions = document.querySelectorAll('.payment-option input[type="radio"]');
    paymentOptions.forEach(option => {
        option.addEventListener('change', function() {
            document.querySelectorAll('.payment-option label').forEach(label => {
                label.style.borderColor = '#ddd';
            });
            
            if (this.checked) {
                this.nextElementSibling.style.borderColor = '#28a745';
            }
        });
    });
    
    // Delivery option selection
    const deliveryOptions = document.querySelectorAll('.delivery-option input[type="radio"]');
    deliveryOptions.forEach(option => {
        option.addEventListener('change', function() {
            document.querySelectorAll('.delivery-option label').forEach(label => {
                label.style.borderColor = '#ddd';
            });
            
            if (this.checked) {
                this.nextElementSibling.style.borderColor = '#28a745';
            }
        });
    });
});