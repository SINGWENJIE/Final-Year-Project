document.addEventListener('DOMContentLoaded', function() {
    // Quantity controls
    document.querySelectorAll('.quantity-btn').forEach(button => {
        button.addEventListener('click', function() {
            const cartItemId = this.getAttribute('data-id');
            const input = document.querySelector(`.quantity-input[data-id="${cartItemId}"]`);
            let quantity = parseInt(input.value);
            const max = parseInt(input.getAttribute('max')) || 999;
            
            if (this.classList.contains('minus')) {
                if (quantity > 1) {
                    quantity--;
                }
            } else if (this.classList.contains('plus')) {
                if (quantity < max) {
                    quantity++;
                } else {
                    showToast(`Maximum quantity (${max}) reached`);
                    return;
                }
            }
            
            input.value = quantity;
            updateCartItem(cartItemId, quantity);
        });
    });
    
    // Manual quantity input
    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('change', function() {
            const cartItemId = this.getAttribute('data-id');
            let quantity = parseInt(this.value);
            const max = parseInt(this.getAttribute('max')) || 999;
            
            if (isNaN(quantity) || quantity < 1) {
                this.value = 1;
                quantity = 1;
            } else if (quantity > max) {
                this.value = max;
                quantity = max;
                showToast(`Maximum quantity is ${max}`);
            }
            
            updateCartItem(cartItemId, quantity);
        });
    });
    
    // Remove item from cart
    document.querySelectorAll('.remove-btn').forEach(button => {
        button.addEventListener('click', function() {
            const cartItemId = this.getAttribute('data-id');
            if (confirm('Are you sure you want to remove this item from your cart?')) {
                removeCartItem(cartItemId);
            }
        });
    });
    
    // Add to wishlist from cart
    document.querySelectorAll('.wishlist-btn').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-id');
            addToWishlist(productId, this);
        });
    });
    
    // Proceed to checkout
    const checkoutBtn = document.querySelector('.checkout-btn');
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', function() {
            window.location.href = 'checkout.php';
        });
    }
    
    // Update cart item quantity via AJAX
    function updateCartItem(cartItemId, quantity) {
        fetch('update_cart_item.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                cart_item_id: cartItemId,
                quantity: quantity
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Update the displayed total for this item
                const price = parseFloat(document.querySelector(`.quantity-input[data-id="${cartItemId}"]`).getAttribute('data-price'));
                const total = price * quantity;
                document.querySelector(`.item-total[data-id="${cartItemId}"]`).textContent = `RM ${total.toFixed(2)}`;
                
                // Update the summary
                updateCartSummary(data.cart);
                
                showToast('Cart updated successfully', 'success');
            } else {
                showToast(data.message || 'Failed to update cart', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred. Please try again.', 'error');
            // Revert the quantity change
            document.querySelector(`.quantity-input[data-id="${cartItemId}"]`).value = quantity - 1;
        });
    }
    
    // Remove cart item via AJAX
    function removeCartItem(cartItemId) {
        fetch('remove_cart_item.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                cart_item_id: cartItemId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove the item from the DOM
                document.querySelector(`.cart-item[data-id="${cartItemId}"]`).remove();
                
                // Update the summary
                updateCartSummary(data.cart);
                
                // Update cart count in header
                updateCartCount(data.cart_count);
                
                // Check if cart is now empty
                if (data.cart_count === 0) {
                    location.reload(); // Reload to show empty cart message
                }
                
                showToast('Item removed from cart', 'success');
            } else {
                showToast(data.message || 'Failed to remove item', 'error');
            }
        })
        .catch(error => {
            showToast('An error occurred. Please try again.', 'error');
            console.error('Error:', error);
        });
    }
    
    // Add to wishlist via AJAX
    function addToWishlist(productId, button) {
        const originalHTML = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        button.disabled = true;
        
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
                    button.innerHTML = '<i class="fas fa-heart"></i> In Wishlist';
                    showToast('Added to wishlist!', 'success');
                } else {
                    button.innerHTML = originalHTML;
                    showToast('Removed from wishlist', 'info');
                }
            } else {
                showToast(data.message || 'Failed to update wishlist', 'error');
                button.innerHTML = originalHTML;
            }
        })
        .catch(error => {
            showToast('An error occurred. Please try again.', 'error');
            console.error('Error:', error);
            button.innerHTML = originalHTML;
        })
        .finally(() => {
            button.disabled = false;
        });
    }
    
    // Update cart summary section
    function updateCartSummary(cartData) {
        if (cartData) {
            document.querySelector('.subtotal').textContent = `RM ${parseFloat(cartData.subtotal).toFixed(2)}`;
            document.querySelector('.total-amount').textContent = `RM ${parseFloat(cartData.total).toFixed(2)}`;
            
            // Update item count in summary
            const itemCount = cartData.item_count || 0;
            document.querySelector('.summary-row span:first-child').innerHTML = 
                `Subtotal (${itemCount} ${itemCount === 1 ? 'item' : 'items'})`;
        }
    }
    
    // Update cart count in header
    function updateCartCount(count) {
        const cartCountElements = document.querySelectorAll('.cart-count');
        const cartLink = document.querySelector('nav a[href="cart.php"]');
        
        if (count > 0) {
            cartCountElements.forEach(el => {
                el.textContent = count;
            });
            
            if (cartCountElements.length === 0 && cartLink) {
                // Create cart count badge if it doesn't exist
                const countBadge = document.createElement('span');
                countBadge.className = 'cart-count';
                countBadge.textContent = count;
                cartLink.appendChild(countBadge);
            }
        } else {
            // Remove cart count badge if count is 0
            cartCountElements.forEach(el => {
                el.remove();
            });
        }
    }
    
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