document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registrationForm');
    const passwordInput = document.getElementById('user_password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const passwordStrength = document.querySelector('.password-strength');
    const passwordMatch = document.querySelector('.password-match');
    
    // Password strength indicator
    passwordInput.addEventListener('input', function() {
        const password = this.value;
        let strength = 0;
        
        // Check length
        if (password.length >= 8) strength += 1;
        
        // Check for numbers
        if (password.match(/\d/)) strength += 1;
        
        // Check for special characters
        if (password.match(/[!@#$%^&*(),.?":{}|<>]/)) strength += 1;
        
        // Check for uppercase and lowercase
        if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength += 1;
        
        // Update strength indicator
        switch(strength) {
            case 0:
                passwordStrength.style.width = '0%';
                passwordStrength.style.backgroundColor = '#e74c3c';
                break;
            case 1:
                passwordStrength.style.width = '25%';
                passwordStrength.style.backgroundColor = '#e74c3c';
                break;
            case 2:
                passwordStrength.style.width = '50%';
                passwordStrength.style.backgroundColor = '#f39c12';
                break;
            case 3:
                passwordStrength.style.width = '75%';
                passwordStrength.style.backgroundColor = '#f1c40f';
                break;
            case 4:
                passwordStrength.style.width = '100%';
                passwordStrength.style.backgroundColor = '#2ecc71';
                break;
        }
    });
    
    // Password match checker
    confirmPasswordInput.addEventListener('input', function() {
        if (this.value !== passwordInput.value) {
            passwordMatch.style.backgroundColor = '#e74c3c';
        } else {
            passwordMatch.style.backgroundColor = '#2ecc71';
        }
    });
    
    // Form validation
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        // Check if passwords match
        if (passwordInput.value !== confirmPasswordInput.value) {
            alert('Passwords do not match!');
            isValid = false;
        }
        
        // Check password strength (optional)
        if (passwordInput.value.length < 8) {
            alert('Password must be at least 8 characters long!');
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
        }
    });
    
    // Phone number formatting (optional)
    const phoneInput = document.getElementById('user_phone_num');
    phoneInput.addEventListener('input', function() {
        // Remove all non-digit characters
        let phoneNumber = this.value.replace(/\D/g, '');
        
        // Format as you type (example: 012-345-6789)
        if (phoneNumber.length > 3 && phoneNumber.length <= 6) {
            phoneNumber = phoneNumber.replace(/(\d{3})(\d{0,3})/, '$1-$2');
        } else if (phoneNumber.length > 6) {
            phoneNumber = phoneNumber.replace(/(\d{3})(\d{3})(\d{0,4})/, '$1-$2-$3');
        }
        
        this.value = phoneNumber;
    });
});