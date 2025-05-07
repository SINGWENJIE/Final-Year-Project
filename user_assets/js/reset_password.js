document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('resetForm');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const passwordStrengthBar = document.getElementById('passwordStrengthBar');
    const passwordMatch = document.getElementById('passwordMatch');
    const togglePassword = document.querySelectorAll('.toggle-password');
    
    // Toggle password visibility
    togglePassword.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const input = this.closest('.input-group').querySelector('input');
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            this.innerHTML = type === 'password' ? '👁️' : '👁️‍🗨️';
        });
    });
    
    // Password strength indicator
    passwordInput.addEventListener('input', function() {
        const password = this.value;
        let strength = 0;
        
        // Check length
        if (password.length >= 4) strength += 20;
        if (password.length >= 8) strength += 20;
        
        // Check for uppercase letters
        if (/[A-Z]/.test(password)) strength += 20;
        
        // Check for numbers
        if (/[0-9]/.test(password)) strength += 20;
        
        // Check for special characters
        if (/[^A-Za-z0-9]/.test(password)) strength += 20;
        
        // Update strength bar
        passwordStrengthBar.style.width = strength + '%';
        
        // Update color based on strength
        if (strength < 40) {
            passwordStrengthBar.style.backgroundColor = '#d9534f'; // Red
        } else if (strength < 80) {
            passwordStrengthBar.style.backgroundColor = '#f0ad4e'; // Yellow
        } else {
            passwordStrengthBar.style.backgroundColor = '#5cb85c'; // Green
        }
    });
    
    // Password match validation
    confirmPasswordInput.addEventListener('input', function() {
        if (this.value !== passwordInput.value) {
            passwordMatch.textContent = 'Passwords do not match!';
            passwordMatch.style.color = 'var(--error-color)';
        } else {
            passwordMatch.textContent = 'Passwords match!';
            passwordMatch.style.color = 'var(--success-color)';
        }
    });
    
    // Form validation
    form.addEventListener('submit', function(e) {
        // Check if passwords match
        if (passwordInput.value !== confirmPasswordInput.value) {
            e.preventDefault();
            alert('Passwords do not match!');
            return;
        }
        
        // Check password strength
        const hasUpperCase = /[A-Z]/.test(passwordInput.value);
        const hasSymbol = /[^A-Za-z0-9]/.test(passwordInput.value);
        const hasMinLength = passwordInput.value.length >= 4;
        
        if (!hasUpperCase || !hasSymbol || !hasMinLength) {
            e.preventDefault();
            alert('Password must contain at least 1 uppercase letter, 1 symbol, and minimum 4 characters');
            return;
        }
    });
});