document.addEventListener('DOMContentLoaded', function() {
    // Initialize elements
    const reviewButtons = document.querySelectorAll('.btn-review');
    const modal = document.getElementById('reviewModal');
    const closeModal = document.querySelector('.close-modal');
    const reviewForm = document.getElementById('reviewForm');
    const ratingStars = document.querySelectorAll('.rating-stars i');
    const ratingValue = document.getElementById('ratingValue');
    const reviewProductSelect = document.getElementById('reviewProduct');
    
    // Rating stars interaction
    ratingStars.forEach(star => {
        star.addEventListener('click', function() {
            const rating = parseInt(this.getAttribute('data-rating'));
            ratingValue.value = rating;
            highlightStars(rating);
        });
        
        star.addEventListener('mouseover', function() {
            const rating = parseInt(this.getAttribute('data-rating'));
            highlightStars(rating);
        });
        
        star.addEventListener('mouseout', function() {
            const currentRating = parseInt(ratingValue.value) || 0;
            highlightStars(currentRating);
        });
    });
    
    function highlightStars(rating) {
        ratingStars.forEach(star => {
            const starRating = parseInt(star.getAttribute('data-rating'));
            star.classList.toggle('fas', starRating <= rating);
            star.classList.toggle('far', starRating > rating);
        });
    }
    
    // Review modal handling
    reviewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const orderId = this.getAttribute('data-order-id');
            document.getElementById('reviewOrderId').value = orderId;
            
            // Clear previous selection
            reviewProductSelect.innerHTML = '<option value="" disabled selected>Loading products...</option>';
            
            // Load products for this order via AJAX
            fetch(`get_order_products.php?order_id=${orderId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (Array.isArray(data)) {
                        reviewProductSelect.innerHTML = '';
                        
                        if (data.length === 0) {
                            reviewProductSelect.innerHTML = '<option value="" disabled>No products available for review</option>';
                            return;
                        }
                        
                        // Add default option
                        const defaultOption = document.createElement('option');
                        defaultOption.value = '';
                        defaultOption.textContent = 'Select a product';
                        defaultOption.disabled = true;
                        defaultOption.selected = true;
                        reviewProductSelect.appendChild(defaultOption);
                        
                        // Add product options
                        data.forEach(product => {
                            const option = document.createElement('option');
                            option.value = product.prod_id;
                            option.textContent = product.prod_name;
                            reviewProductSelect.appendChild(option);
                        });
                        
                        // Show modal
                        modal.style.display = 'block';
                    } else {
                        throw new Error(data.error || 'Invalid response');
                    }
                })
                .catch(error => {
                    console.error('Error loading products:', error);
                    showToast(error.message || 'Error loading products', 'error');
                });
        });
    });
    
    // Close modal
    function closeReviewModal() {
        modal.style.display = 'none';
        resetReviewForm();
    }
    
    closeModal.addEventListener('click', closeReviewModal);
    
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            closeReviewModal();
        }
    });
    
    // Form submission
    reviewForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validate form
        if (!ratingValue.value) {
            showToast('Please select a rating', 'error');
            return;
        }
        
        if (!reviewProductSelect.value) {
            showToast('Please select a product', 'error');
            return;
        }
        
        const textarea = reviewForm.querySelector('textarea');
        if (!textarea.value.trim()) {
            showToast('Please enter your review comments', 'error');
            return;
        }
        
        // Show loading state
        const submitButton = reviewForm.querySelector('button[type="submit"]');
        const originalButtonText = submitButton.innerHTML;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
        submitButton.disabled = true;
        
        const formData = new FormData(this);
        
        fetch('submit_review.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showToast(data.message || 'Thank you for your review!', 'success');
                closeReviewModal();
                
                // Reload the page after a short delay
                setTimeout(() => location.reload(), 1500);
            } else {
                throw new Error(data.message || 'Failed to submit review');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast(error.message || 'Failed to submit review', 'error');
            submitButton.innerHTML = originalButtonText;
            submitButton.disabled = false;
        });
    });
    
    // Helper function to reset review form
    function resetReviewForm() {
        reviewForm.reset();
        ratingValue.value = '';
        highlightStars(0);
    }
    
    // Helper function to show toast messages
    function showToast(message, type = 'info') {
        // Remove existing toasts
        const existingToasts = document.querySelectorAll('.toast');
        existingToasts.forEach(toast => toast.remove());
        
        // Create toast element
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.textContent = message;
        
        // Add to body
        document.body.appendChild(toast);
        
        // Show toast
        setTimeout(() => {
            toast.classList.add('show');
        }, 10);
        
        // Hide after 3 seconds
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
});