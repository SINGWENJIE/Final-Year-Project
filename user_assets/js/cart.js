document.addEventListener('DOMContentLoaded', function() {
    // Update item totals when quantity changes
    const quantityInputs = document.querySelectorAll('.quantity-input');
    
    quantityInputs.forEach(input => {
        input.addEventListener('change', function() {
            const cartItem = this.closest('.cart-item');
            const price = parseFloat(cartItem.querySelector('.item-price').textContent.replace('RM ', ''));
            const quantity = parseInt(this.value);
            const totalElement = cartItem.querySelector('.item-total');
            
            // Validate quantity
            const max = parseInt(this.getAttribute('max')) || 999;
            if (quantity < 1) {
                this.value = 1;
            } else if (quantity > max) {
                this.value = max;
                showToast(`Maximum quantity is ${max}`);
            }
            
            // Update total
            const total = price * quantity;
            totalElement.textContent = 'RM ' + total.toFixed(2);
        });
    });
    
    // Show confirmation before removing item
    const removeButtons = document.querySelectorAll('.remove-btn');
    
    removeButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const productName = this.closest('.cart-item').querySelector('h3').textContent;
            
            if (confirm(`Are you sure you want to remove "${productName}" from your cart?`)) {
                window.location.href = this.getAttribute('href');
            }
        });
    });
    
    // Toast notification function
    function showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `toast-notification ${type}`;
        toast.textContent = message;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.add('show');
        }, 10);
        
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => {
                document.body.removeChild(toast);
            }, 300);
        }, 3000);
    }
});

// Add toast notification styles dynamically
const toastStyles = document.createElement('style');
toastStyles.textContent = `
.toast-notification {
    position: fixed;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    background-color: #333;
    color: white;
    padding: 12px 24px;
    border-radius: 4px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    z-index: 1000;
    opacity: 0;
    transition: opacity 0.3s ease;
    max-width: 90%;
    text-align: center;
}

.toast-notification.show {
    opacity: 1;
}

.toast-notification.success {
    background-color: #27ae60;
}

.toast-notification.error {
    background-color: #e74c3c;
}

.toast-notification.info {
    background-color: #3498db;
}
`;
document.head.appendChild(toastStyles);