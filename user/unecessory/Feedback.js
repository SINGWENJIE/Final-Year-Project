document.addEventListener('DOMContentLoaded', function() {
    const feedbackModal = document.getElementById('feedbackModal');
    const stars = document.querySelectorAll('.star-rating i');
    const ratingInput = document.getElementById('ratingValue');
    const feedbackForm = document.getElementById('feedbackForm');
    const skipButton = document.getElementById('skipFeedback');
    
    let currentRating = 0;
    let hoverRating = 0;
    
    // Initialize the modal
    function initFeedbackModal() {
        // Set up star rating interaction
        stars.forEach(star => {
            star.addEventListener('mouseover', handleStarHover);
            star.addEventListener('mouseout', handleStarHoverOut);
            star.addEventListener('click', handleStarClick);
        });
        
        // Form submission
        feedbackForm.addEventListener('submit', handleFormSubmit);
        
        // Skip button
        skipButton.addEventListener('click', handleSkipFeedback);
    }
    
    // Star hover effect
    function handleStarHover(e) {
        const rating = parseInt(e.target.getAttribute('data-rating'));
        hoverRating = rating;
        updateStarDisplay();
    }
    
    function handleStarHoverOut() {
        hoverRating = 0;
        updateStarDisplay();
    }
    
    // Star click handler
    function handleStarClick(e) {
        const rating = parseInt(e.target.getAttribute('data-rating'));
        currentRating = rating;
        ratingInput.value = rating;
        updateStarDisplay();
    }
    
    // Update star display based on current and hover states
    function updateStarDisplay() {
        stars.forEach(star => {
            const starRating = parseInt(star.getAttribute('data-rating'));
            
            star.classList.remove('active', 'hover');
            
            if (hoverRating > 0) {
                if (starRating <= hoverRating) {
                    star.classList.add('hover');
                }
            } else if (starRating <= currentRating) {
                star.classList.add('active');
            }
        });
    }
    
    // Form submission handler
    function handleFormSubmit(e) {
        e.preventDefault();
        
        const rating = currentRating;
        const comment = document.getElementById('feedbackComment').value;
        const recommend = document.querySelector('input[name="recommend"]:checked')?.value || 'not answered';
        
        // Validate rating
        if (rating === 0) {
            alert('Please provide a star rating before submitting');
            return;
        }
        
        // In a real app, you would send this data to your server
        const feedbackData = {
            orderId: getOrderIdFromURL(), // You would need to implement this
            rating: rating,
            comment: comment,
            recommend: recommend,
            timestamp: new Date().toISOString()
        };
        
        console.log('Feedback submitted:', feedbackData);
        
        // Here you would typically make an AJAX call to your backend
        // For this example, we'll just show a thank you message
        showThankYouMessage();
    }
    
    // Skip feedback handler
    function handleSkipFeedback() {
        // In a real app, you might want to track skipped feedback
        console.log('Feedback skipped');
        closeFeedbackModal();
    }
    
    // Show thank you message and close modal
    function showThankYouMessage() {
        // Replace the form content with a thank you message
        const feedbackBody = document.querySelector('.feedback-body');
        feedbackBody.innerHTML = `
            <div class="thank-you-message">
                <i class="fas fa-check-circle"></i>
                <h3>Thank you for your feedback!</h3>
                <p>We appreciate you taking the time to help us improve.</p>
                <button id="closeFeedback" class="btn primary">Close</button>
            </div>
        `;
        
        // Add event listener to close button
        document.getElementById('closeFeedback').addEventListener('click', closeFeedbackModal);
    }
    
    // Close the feedback modal
    function closeFeedbackModal() {
        feedbackModal.style.animation = 'fadeOut 0.3s ease';
        setTimeout(() => {
            feedbackModal.remove();
            // Redirect or do something else after closing
            window.location.href = 'thankyou.html';
        }, 300);
    }
    
    // Helper function to get order ID from URL (example)
    function getOrderIdFromURL() {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('order_id') || 'unknown';
    }
    
    // Initialize the feedback modal
    initFeedbackModal();
    
    // Add fadeOut animation to stylesheet dynamically
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
    `;
    document.head.appendChild(style);
});