document.addEventListener('DOMContentLoaded', function() {
    // Quantity controls
    document.querySelectorAll('.quantity-minus').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-id');
            const quantityInput = document.querySelector(`.quantity[data-id="${productId}"]`);
            let value = parseInt(quantityInput.value);
            if (value > 1) {
                quantityInput.value = value - 1;
                updateCartItem(productId, quantityInput.value);
            }
        });
    });
    
    document.querySelectorAll('.quantity-plus').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-id');
            const quantityInput = document.querySelector(`.quantity[data-id="${productId}"]`);
            let value = parseInt(quantityInput.value);
            quantityInput.value = value + 1;
            updateCartItem(productId, quantityInput.value);
        });
    });
    
    // Quantity input change
    document.querySelectorAll('.quantity').forEach(input => {
        input.addEventListener('change', function() {
            const productId = this.getAttribute('data-id');
            let value = parseInt(this.value);
            if (value < 1) {
                this.value = 1;
                value = 1;
            }
            updateCartItem(productId, value);
        });
    });
    
    // Remove item
    document.querySelectorAll('.remove-item').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-id');
            if (confirm('Are you sure you want to remove this item from your cart?')) {
                removeCartItem(productId);
            }
        });
    });
    
    // Apply promo code
    document.getElementById('apply-promo')?.addEventListener('click', function() {
        const promoCode = document.getElementById('promo-code-input').value;
        if (promoCode.trim() === '') {
            alert('Please enter a promo code');
            return;
        }
        
        applyPromoCode(promoCode);
    });
    
    // Functions for cart operations
    function updateCartItem(productId, quantity) {
        fetch('update_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `product_id=${productId}&quantity=${quantity}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Refresh the page to update totals
                window.location.reload();
            } else {
                alert(data.error || 'Failed to update cart');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to update cart');
        });
    }
    
    function removeCartItem(productId) {
        fetch('remove_from_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `product_id=${productId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Refresh the page
                window.location.reload();
            } else {
                alert(data.error || 'Failed to remove item');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to remove item');
        });
    }
    
    function applyPromoCode(promoCode) {
        fetch('apply_promo.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `promo_code=${promoCode}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Promo code applied successfully!');
                window.location.reload();
            } else {
                alert(data.error || 'Failed to apply promo code');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to apply promo code');
        });
    }
});