document.addEventListener('DOMContentLoaded', function() {
    // Tab functionality
    const tabLinks = document.querySelectorAll('.tab-link');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabLinks.forEach(link => {
        link.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            
            // Remove active class from all tabs and contents
            tabLinks.forEach(tab => tab.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Add active class to clicked tab and corresponding content
            this.classList.add('active');
            document.getElementById(tabId).classList.add('active');
        });
    });
    
    // Quantity selector functionality
    const minusBtn = document.querySelector('.quantity-btn.minus');
    const plusBtn = document.querySelector('.quantity-btn.plus');
    const quantityInput = document.querySelector('.quantity-input');
    
    minusBtn.addEventListener('click', function() {
        let currentValue = parseInt(quantityInput.value);
        if (currentValue > 1) {
            quantityInput.value = currentValue - 1;
        }
    });
    
    plusBtn.addEventListener('click', function() {
        let currentValue = parseInt(quantityInput.value);
        const maxValue = parseInt(quantityInput.getAttribute('max'));
        if (currentValue < maxValue) {
            quantityInput.value = currentValue + 1;
        } else {
            showAlert('error', 'Maximum available quantity reached');
        }
    });
    
    // Add to cart functionality
    const addToCartForm = document.getElementById('addToCartForm');
    
    if (addToCartForm) {
        addToCartForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const productId = this.querySelector('input[name="product_id"]').value;
            const quantity = this.querySelector('input[name="quantity"]').value;
            
            // Check if user is logged in
            fetch('check_session.php')
                .then(response => response.json())
                .then(data => {
                    if (data.loggedIn) {
                        // User is logged in, add to cart via AJAX
                        addToCart(productId, quantity);
                    } else {
                        // User not logged in, redirect to login
                        window.location.href = 'login.php?redirect=' + encodeURIComponent(window.location.pathname + window.location.search);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('error', 'An error occurred. Please try again.');
                });
        });
    }
    
    // Add to wishlist functionality
    const addToWishlistBtn = document.getElementById('addToWishlist');
    
    if (addToWishlistBtn) {
        addToWishlistBtn.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            
            // Check if user is logged in
            fetch('check_session.php')
                .then(response => response.json())
                .then(data => {
                    if (data.loggedIn) {
                        // User is logged in, add to wishlist via AJAX
                        addToWishlist(productId);
                    } else {
                        // User not logged in, redirect to login
                        window.location.href = 'login.php?redirect=' + encodeURIComponent(window.location.pathname + window.location.search);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('error', 'An error occurred. Please try again.');
                });
        });
    }
    
    // Review form submission
    const reviewForm = document.getElementById('reviewForm');
    
    if (reviewForm) {
        reviewForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('submit_review.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', 'Thank you for your review! It will be visible after approval.');
                    reviewForm.reset();
                    // Reset star rating
                    document.querySelectorAll('.star-rating input').forEach(input => {
                        input.checked = false;
                    });
                } else {
                    showAlert('error', data.message || 'Failed to submit review. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('error', 'An error occurred. Please try again.');
            });
        });
    }
    
    // Function to add item to cart via AJAX
    function addToCart(productId, quantity) {
        fetch('add_to_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `product_id=${productId}&quantity=${quantity}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', 'Product added to cart successfully!');
                // Update cart count in header
                updateCartCount(data.cartCount);
            } else {
                showAlert('error', data.message || 'Failed to add product to cart.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'An error occurred. Please try again.');
        });
    }
    
    // Function to add item to wishlist via AJAX
    function addToWishlist(productId) {
        fetch('add_to_wishlist.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `product_id=${productId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', 'Product added to your wishlist!');
                const wishlistBtn = document.getElementById('addToWishlist');
                if (wishlistBtn) {
                    wishlistBtn.innerHTML = '<i class="fas fa-heart"></i> In Wishlist';
                    wishlistBtn.style.color = '#d32f2f';
                }
            } else {
                showAlert('error', data.message || 'Failed to add product to wishlist.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'An error occurred. Please try again.');
        });
    }
    
    // Function to show alert messages
    function showAlert(type, message) {
        // Remove any existing alerts
        const existingAlert = document.querySelector('.custom-alert');
        if (existingAlert) {
            existingAlert.remove();
        }
        
        const alertDiv = document.createElement('div');
        alertDiv.className = `custom-alert ${type}`;
        alertDiv.textContent = message;
        
        document.body.appendChild(alertDiv);
        
        // Position the alert
        const headerHeight = document.querySelector('header')?.offsetHeight || 0;
        alertDiv.style.top = `${headerHeight + 20}px`;
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }
    
    // Function to update cart count in header
    function updateCartCount(count) {
        const cartCountElements = document.querySelectorAll('.cart-count');
        cartCountElements.forEach(element => {
            element.textContent = count;
        });
    }
    
    // Check if product is in wishlist and update button accordingly
    function checkWishlistStatus(productId) {
        fetch('check_wishlist.php?product_id=' + productId)
            .then(response => response.json())
            .then(data => {
                if (data.inWishlist) {
                    const wishlistBtn = document.getElementById('addToWishlist');
                    if (wishlistBtn) {
                        wishlistBtn.innerHTML = '<i class="fas fa-heart"></i> In Wishlist';
                        wishlistBtn.style.color = '#d32f2f';
                    }
                }
            })
            .catch(error => console.error('Error checking wishlist:', error));
    }
    
    // Initialize wishlist status check if user is logged in
    fetch('check_session.php')
        .then(response => response.json())
        .then(data => {
            if (data.loggedIn) {
                const productId = document.getElementById('addToWishlist')?.getAttribute('data-product-id');
                if (productId) {
                    checkWishlistStatus(productId);
                }
            }
        })
        .catch(error => console.error('Error checking session:', error));
});

// Add some basic styling for alerts
const style = document.createElement('style');
style.textContent = `
.custom-alert {
    position: fixed;
    left: 50%;
    transform: translateX(-50%);
    padding: 15px 25px;
    border-radius: 4px;
    color: white;
    font-weight: bold;
    z-index: 1000;
    box-shadow: 0 3px 10px rgba(0,0,0,0.2);
    animation: slideIn 0.3s ease-out;
}

.custom-alert.success {
    background-color: #4CAF50;
}

.custom-alert.error {
    background-color: #f44336;
}

@keyframes slideIn {
    from { top: -50px; opacity: 0; }
    to { opacity: 1; }
}
`;
document.head.appendChild(style);