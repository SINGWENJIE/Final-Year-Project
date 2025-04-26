document.addEventListener('DOMContentLoaded', function() {
    // Quantity input controls
    document.querySelectorAll('.quantity-control input').forEach(input => {
        const minusBtn = document.createElement('button');
        minusBtn.type = 'button';
        minusBtn.className = 'quantity-minus';
        minusBtn.innerHTML = '-';
        
        const plusBtn = document.createElement('button');
        plusBtn.type = 'button';
        plusBtn.className = 'quantity-plus';
        plusBtn.innerHTML = '+';
        
        input.parentNode.insertBefore(minusBtn, input);
        input.parentNode.appendChild(plusBtn);
        
        minusBtn.addEventListener('click', function() {
            let value = parseInt(input.value);
            if (value > 1) {
                input.value = value - 1;
            }
        });
        
        plusBtn.addEventListener('click', function() {
            let value = parseInt(input.value);
            let max = parseInt(input.getAttribute('max'));
            if (value < max) {
                input.value = value + 1;
            }
        });
    });
    
    // Promo code apply button
    const applyPromoBtn = document.querySelector('.promo-code button');
    if (applyPromoBtn) {
        applyPromoBtn.addEventListener('click', function() {
            const promoInput = this.previousElementSibling;
            if (promoInput.value.trim() === '') {
                alert('Please enter a promo code');
            } else {
                alert('Promo code applied successfully!');
                // Here you would typically send an AJAX request to validate promo code
            }
        });
    }
    
    // Remove item confirmation
    document.querySelectorAll('.remove-item').forEach(link => {
        link.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to remove this item from your cart?')) {
                e.preventDefault();
            }
        });
    });
});