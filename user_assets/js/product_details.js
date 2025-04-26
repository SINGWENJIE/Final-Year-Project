document.addEventListener('DOMContentLoaded', function() {
    // Quantity controls
    const minusBtn = document.querySelector('.quantity-minus');
    const plusBtn = document.querySelector('.quantity-plus');
    const quantityInput = document.querySelector('.quantity');
    
    if (minusBtn && plusBtn && quantityInput) {
        minusBtn.addEventListener('click', function() {
            let value = parseInt(quantityInput.value);
            if (value > 1) {
                quantityInput.value = value - 1;
            }
        });
        
        plusBtn.addEventListener('click', function() {
            let value = parseInt(quantityInput.value);
            let max = parseInt(quantityInput.getAttribute('max')) || 999;
            if (value < max) {
                quantityInput.value = value + 1;
            } else {
                // Show a message when maximum quantity is reached
                showToast(`Maximum quantity (${max}) reached`);
            }
        });
        
        // Validate input manually
        quantityInput.addEventListener('change', function() {
            let value = parseInt(this.value);
            let max = parseInt(this.getAttribute('max')) || 999;
            
            if (isNaN(value)) {
                this.value = 1;
            } else if (value < 1) {
                this.value = 1;
            } else if (value > max) {
                this.value = max;
                showToast(`Maximum quantity is ${max}`);
            }
        });
    }
    
    // Add to Cart with AJAX
    const addToCartBtn = document.querySelector('.add-to-cart');
    if (addToCartBtn) {
        addToCartBtn.addEventListener('click', function() {
            const productId = this.getAttribute('data-id');
            const quantity = document.querySelector('.quantity').value;
            
            // Show loading state
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
            this.disabled = true;
            
            // AJAX request to add to cart
            fetch('add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    product_id: productId,
                    quantity: quantity
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Added to cart successfully!', 'success');
                    updateCartCount(data.cart_count);
                } else {
                    showToast(data.message || 'Failed to add to cart', 'error');
                }
            })
            .catch(error => {
                showToast('An error occurred. Please try again.', 'error');
                console.error('Error:', error);
            })
            .finally(() => {
                this.innerHTML = '<i class="fas fa-shopping-cart"></i> Add to Cart';
                this.disabled = false;
            });
        });
    }

    // Buy Now button functionality
    const buyNowBtn = document.querySelector('.buy-now');
    if (buyNowBtn) {
        buyNowBtn.addEventListener('click', function() {
            const productId = this.getAttribute('data-id');
            const quantity = document.querySelector('.quantity').value;
        
            // Show loading state
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            this.disabled = true;
        
            // First add to cart via AJAX
            fetch('add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    product_id: productId,
                    quantity: quantity,
                    buy_now: true
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = 'checkout.php';
                } else {
                    showToast(data.message || 'Failed to process order', 'error');
                }
            })
            .catch(error => {
                showToast('An error occurred. Please try again.', 'error');
                console.error('Error:', error);
            })
            .finally(() => {
                this.innerHTML = '<i class="fas fa-bolt"></i> Buy Now';
                this.disabled = false;
            });
        });
    }
    
    // Add to Wishlist with AJAX
    const addToWishlistBtn = document.querySelector('.add-to-wishlist');
    if (addToWishlistBtn) {
        addToWishlistBtn.addEventListener('click', function() {
            const productId = this.getAttribute('data-id');
            
            // Show loading state
            const originalHTML = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            this.disabled = true;
            
            // AJAX request to add to wishlist
            fetch('add_to_wishlist.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    product_id: productId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.action === 'added') {
                        this.innerHTML = '<i class="fas fa-heart"></i> In Wishlist';
                        showToast('Added to wishlist!', 'success');
                    } else {
                        this.innerHTML = originalHTML;
                        showToast('Removed from wishlist', 'info');
                    }
                    this.style.color = data.action === 'added' ? '#e74c3c' : '';
                } else {
                    showToast(data.message || 'Failed to update wishlist', 'error');
                    this.innerHTML = originalHTML;
                }
            })
            .catch(error => {
                showToast('An error occurred. Please try again.', 'error');
                console.error('Error:', error);
                this.innerHTML = originalHTML;
            })
            .finally(() => {
                this.disabled = false;
            });
        });
    }
    
    // Check if product is in wishlist on page load
    checkWishlistStatus();
    
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
    
    // Update cart count in header
    function updateCartCount(count) {
        const cartCountElements = document.querySelectorAll('.cart-count');
        cartCountElements.forEach(el => {
            el.textContent = count;
        });
    }
    
    // Check wishlist status for this product
    function checkWishlistStatus() {
        const productId = document.querySelector('.add-to-wishlist')?.getAttribute('data-id');
        if (!productId) return;
        
        fetch(`check_wishlist.php?product_id=${productId}`)
            .then(response => response.json())
            .then(data => {
                if (data.in_wishlist) {
                    const btn = document.querySelector('.add-to-wishlist');
                    btn.innerHTML = '<i class="fas fa-heart"></i> In Wishlist';
                    btn.style.color = '#e74c3c';
                }
            })
            .catch(error => {
                console.error('Error checking wishlist:', error);
            });
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
