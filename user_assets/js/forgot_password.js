document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('forgotForm');
    const emailInput = document.getElementById('email');
    
    // Form validation
    form.addEventListener('submit', function(e) {
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value)) {
            alert('Please enter a valid email address');
            e.preventDefault();
        }
    });
    
    // Focus on email field when page loads
    emailInput.focus();
});