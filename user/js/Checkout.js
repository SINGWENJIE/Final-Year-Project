document.addEventListener('DOMContentLoaded', function() {
    // Form submission
    const deliveryForm = document.getElementById('deliveryForm');
    
    deliveryForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validate form
        if (validateForm()) {
            // In a real app, you would process the form data here
            // For this example, we'll just proceed to payment
            window.location.href = 'payment.html';
        }
    });
    
    // Back to cart button
    document.querySelector('.btn-back').addEventListener('click', function() {
        window.location.href = 'index.html';
    });
    
    // Promo code application
    document.querySelector('.btn-apply').addEventListener('click', function() {
        const promoInput = document.querySelector('.promo-input input');
        const promoCode = promoInput.value.trim();
        
        if (promoCode) {
            // In a real app, you would validate the promo code with your backend
            if (promoCode.toUpperCase() === 'FREESHIP') {
                applyDiscount(5.00);
                showAlert('Promo code applied successfully!', 'success');
            } else {
                showAlert('Invalid promo code', 'error');
            }
        } else {
            showAlert('Please enter a promo code', 'error');
        }
    });
    
    // Form validation
    function validateForm() {
        let isValid = true;
        const requiredFields = deliveryForm.querySelectorAll('[required]');
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                isValid = false;
                field.style.borderColor = '#dc3545';
                
                // Reset border color when user starts typing
                field.addEventListener('input', function() {
                    this.style.borderColor = '#ddd';
                });
            }
        });
        
        // Validate email format
        const email = document.getElementById('email');
        if (email.value && !validateEmail(email.value)) {
            isValid = false;
            email.style.borderColor = '#dc3545';
            showAlert('Please enter a valid email address', 'error');
        }
        
        // Validate phone number
        const phone = document.getElementById('phone');
        if (phone.value && !validatePhone(phone.value)) {
            isValid = false;
            phone.style.borderColor = '#dc3545';
            showAlert('Please enter a valid phone number', 'error');
        }
        
        return isValid;
    }
    
    // Email validation helper
    function validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
    
    // Phone validation helper (simple Malaysian phone number validation)
    function validatePhone(phone) {
        const re = /^[0-9]{10,11}$/;
        return re.test(phone);
    }
    
    // Apply discount to order total
    function applyDiscount(amount) {
        const discountRow = document.querySelector('.total-row.discount');
        const grandTotalRow = document.querySelector('.grand-total');
        
        // Update discount display
        discountRow.querySelector('span:last-child').textContent = `-RM${amount.toFixed(2)}`;
        
        // Calculate new total
        const subtotal = 40.88; // In real app, get from cart
        const delivery = 0.00;
        const total = subtotal + delivery - amount;
        
        // Update grand total
        grandTotalRow.querySelector('span:last-child').textContent = `RM${total.toFixed(2)}`;
    }
    
    // Show alert message
    function showAlert(message, type) {
        // Remove existing alerts
        const existingAlert = document.querySelector('.alert');
        if (existingAlert) existingAlert.remove();
        
        // Create alert element
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.textContent = message;
        
        // Add to page
        document.body.appendChild(alert);
        
        // Position alert
        alert.style.position = 'fixed';
        alert.style.top = '20px';
        alert.style.left = '50%';
        alert.style.transform = 'translateX(-50%)';
        alert.style.padding = '15px 25px';
        alert.style.borderRadius = '4px';
        alert.style.zIndex = '1000';
        
        if (type === 'success') {
            alert.style.backgroundColor = '#d4edda';
            alert.style.color = '#155724';
            alert.style.border = '1px solid #c3e6cb';
        } else {
            alert.style.backgroundColor = '#f8d7da';
            alert.style.color = '#721c24';
            alert.style.border = '1px solid #f5c6cb';
        }
        
        // Remove after 3 seconds
        setTimeout(() => {
            alert.remove();
        }, 3000);
    }
    
    // In a real app, you would load the cart items from storage/backend
    // and calculate totals dynamically
});