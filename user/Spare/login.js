document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('loginForm');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const togglePassword = document.querySelector('.toggle-password');
    
    // Toggle password visibility
    togglePassword.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        this.innerHTML = type === 'password' ? '👁️' : '👁️‍🗨️';
    });
    
    // Form validation
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        // Email validation
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value)) {
            alert('Please enter a valid email address');
            isValid = false;
        }
        
        // Password length check
        if (passwordInput.value.length < 6) {
            alert('Password must be at least 6 characters long');
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
        }
    });
    
    // If coming from registration with success, focus on email field
    if (window.location.search.includes('registration=success') || 
        window.location.search.includes('reset=success')) {
        emailInput.focus();
    }
    
    // Remember me functionality
    const rememberCheckbox = document.querySelector('input[name="remember"]');
    
    // Check if there are saved credentials
    if (localStorage.getItem('rememberedEmail')) {
        emailInput.value = localStorage.getItem('rememberedEmail');
        rememberCheckbox.checked = true;
    }
    
    // Save credentials if "Remember me" is checked
    rememberCheckbox.addEventListener('change', function() {
        if (this.checked && emailInput.value) {
            localStorage.setItem('rememberedEmail', emailInput.value);
        } else {
            localStorage.removeItem('rememberedEmail');
        }
    });
});