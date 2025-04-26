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
            let max = parseInt(quantityInput.getAttribute('max'));
            if (value < max) {
                quantityInput.value = value + 1;
            }
        });
    }
    
    // Add to Cart
    const addToCartBtn = document.querySelector('.add-to-cart');
    if (addToCartBtn) {
        addToCartBtn.addEventListener('click', function() {
            const productId = this.getAttribute('data-id');
            const quantity = document.querySelector('.quantity').value;
            
            // Here you would typically send an AJAX request to add to cart
            alert(`Added ${quantity} item(s) to cart!`);
            
            // Example AJAX request:
            /*
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
                alert(data.message);
            });
            */
        });
    }
    
    // Add to Wishlist
    const addToWishlistBtn = document.querySelector('.add-to-wishlist');
    if (addToWishlistBtn) {
        addToWishlistBtn.addEventListener('click', function() {
            const productId = this.getAttribute('data-id');
            
            // Here you would typically send an AJAX request to add to wishlist
            alert('Added to wishlist!');
            
            // Example: Change button appearance
            this.textContent = 'In Wishlist';
            this.style.backgroundColor = '#2c3e50';
            
            /*
            fetch('add_to_wishlist.php', {
                method: 'POST',
                body: new URLSearchParams({
                    product_id: productId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.textContent = 'In Wishlist';
                    this.style.backgroundColor = '#2c3e50';
                }
            });
            */
        });
    }
    
    // Buy Now
    const buyNowBtn = document.querySelector('.buy-now');
    if (buyNowBtn) {
        buyNowBtn.addEventListener('click', function() {
            const productId = this.getAttribute('data-id');
            const quantity = document.querySelector('.quantity').value;
            
            // Here you would typically redirect to checkout or process immediately
            alert(`Buying ${quantity} item(s) now! Redirecting to checkout...`);
            
            // Example redirect:
            // window.location.href = `checkout.php?product_id=${productId}&quantity=${quantity}`;
        });
    }
});