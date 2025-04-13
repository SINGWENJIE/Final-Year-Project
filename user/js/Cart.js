document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements (same as before)
    const cartIcon = document.getElementById('cartIcon');
    const cartOverlay = document.getElementById('cartOverlay');
    const cartSidebar = document.getElementById('cartSidebar');
    const closeCart = document.getElementById('closeCart');
    const cartItemsContainer = document.getElementById('cartItems');
    const cartCount = document.querySelector('.cart-count');
    const cartTotal = document.querySelector('.total-price');
    
    // Toggle cart visibility (same as before)
    function toggleCart() {
        cartOverlay.classList.toggle('active');
        cartSidebar.classList.toggle('active');
        document.body.style.overflow = cartSidebar.classList.contains('active') ? 'hidden' : '';
    }
    
    // Add event listeners (same as before)
    function setupEventListeners() {
        cartIcon.addEventListener('click', toggleCart);
        closeCart.addEventListener('click', toggleCart);
        cartOverlay.addEventListener('click', toggleCart);
        
        // Add event listeners to quantity buttons
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('minus')) {
                const itemId = parseInt(e.target.closest('.cart-item').dataset.id);
                updateQuantity(itemId, 'decrease');
            }
            
            if (e.target.classList.contains('plus')) {
                const itemId = parseInt(e.target.closest('.cart-item').dataset.id);
                updateQuantity(itemId, 'increase');
            }
            
            if (e.target.classList.contains('cart-item-remove')) {
                const itemId = e.target.dataset.cartItemId ? parseInt(e.target.dataset.cartItemId) : parseInt(e.target.closest('.cart-item').dataset.id);
                removeFromCart(itemId, e.target.dataset.cartItemId ? true : false);
            }
        });
    }
    
    // Update quantity via AJAX
    function updateQuantity(productId, action) {
        const formData = new FormData();
        formData.append('product_id', productId);
        formData.append('action', action);
        
        fetch('cart_actions.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Refresh cart
                window.location.reload();
            } else {
                alert(data.message || 'Error updating quantity');
            }
        })
        .catch(error => console.error('Error:', error));
    }
    
    // Remove item via AJAX
    function removeFromCart(itemId, isDatabaseItem) {
        const formData = new FormData();
        formData.append(isDatabaseItem ? 'cart_item_id' : 'product_id', itemId);
        formData.append('action', 'remove');
        
        fetch('cart_actions.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Refresh cart
                window.location.reload();
            } else {
                alert(data.message || 'Error removing item');
            }
        })
        .catch(error => console.error('Error:', error));
    }
    
    // Initialize
    setupEventListeners();
});