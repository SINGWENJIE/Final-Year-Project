document.addEventListener('DOMContentLoaded', function() {
    // Show/hide new address form
    const newAddressRadio = document.getElementById('new_address');
    const newAddressForm = document.getElementById('newAddressForm');
    
    if (newAddressRadio && newAddressForm) {
        newAddressRadio.addEventListener('change', function() {
            if (this.checked) {
                newAddressForm.style.display = 'block';
            }
        });
        
        // Hide new address form if another address is selected
        document.querySelectorAll('input[name="shipping_address"]').forEach(radio => {
            if (radio.id !== 'new_address') {
                radio.addEventListener('change', function() {
                    newAddressForm.style.display = 'none';
                });
            }
        });
    }
    
    // Show/hide credit card form based on payment method
    const creditCardForm = document.getElementById('creditCardForm');
    if (creditCardForm) {
        function toggleCreditCardForm() {
            const selectedMethod = document.querySelector('input[name="payment_method"]:checked').value;
            if (selectedMethod === 'credit_card' || selectedMethod === 'debit_card') {
                creditCardForm.style.display = 'block';
            } else {
                creditCardForm.style.display = 'none';
            }
        }
        
        // Initial toggle
        toggleCreditCardForm();
        
        // Toggle when payment method changes
        document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
            radio.addEventListener('change', toggleCreditCardForm);
        });
    }
    
    // Apply voucher button functionality
    const applyVoucherBtn = document.getElementById('applyVoucher');
    if (applyVoucherBtn) {
        applyVoucherBtn.addEventListener('click', function() {
            const voucherCode = document.getElementById('voucher_code').value.trim();
            
            if (!voucherCode) {
                showToast('Please enter a voucher code', 'error');
                return;
            }
            
            // In a real application, you would validate the voucher with the server
            // Here we'll simulate a successful application
            const voucherDiscount = document.querySelector('.voucher-discount');
            const discountAmount = document.querySelector('.discount-amount');
            const subtotal = parseFloat(document.querySelector('.subtotal').textContent.replace('RM ', ''));
            const deliveryFee = parseFloat(document.querySelector('.delivery-fee').textContent.replace('RM ', ''));
            const totalAmount = document.querySelector('.total-amount');
            
            // Simulate different voucher types
            if (voucherCode.toUpperCase() === 'FREESHIP') {
                // Free shipping voucher
                const newDeliveryFee = 0.00;
                const newTotal = subtotal + newDeliveryFee;
                
                document.querySelector('.delivery-fee').textContent = 'RM ' + newDeliveryFee.toFixed(2);
                voucherDiscount.style.display = 'flex';
                discountAmount.textContent = '-RM ' + deliveryFee.toFixed(2);
                totalAmount.textContent = 'RM ' + newTotal.toFixed(2);
                
                showToast('Free shipping applied!', 'success');
            } else if (voucherCode.toUpperCase() === '10OFF') {
                // 10% off voucher
                const discount = subtotal * 0.10;
                const newTotal = (subtotal - discount) + deliveryFee;
                
                voucherDiscount.style.display = 'flex';
                discountAmount.textContent = '-RM ' + discount.toFixed(2);
                totalAmount.textContent = 'RM ' + newTotal.toFixed(2);
                
                showToast('10% discount applied!', 'success');
            } else if (voucherCode.toUpperCase() === '5RM') {
                // RM5 off voucher
                const discount = 5.00;
                const newTotal = (subtotal - discount) + deliveryFee;
                
                voucherDiscount.style.display = 'flex';
                discountAmount.textContent = '-RM ' + discount.toFixed(2);
                totalAmount.textContent = 'RM ' + (newTotal > 0 ? newTotal.toFixed(2) : '0.00');
                
                showToast('RM5 discount applied!', 'success');
            } else {
                showToast('Invalid voucher code', 'error');
            }
        });
    }
    
    // Apply selected voucher from available vouchers
    document.querySelectorAll('input[name="selected_voucher"]').forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.checked) {
                const voucherId = this.value;
                const voucherOption = document.querySelector(`.voucher-option[data-id="${voucherId}"]`);
                
                if (voucherOption) {
                    const voucherName = voucherOption.querySelector('h4').textContent;
                    const voucherTerms = voucherOption.querySelector('.voucher-terms').textContent;
                    
                    // In a real application, you would get the voucher details from the server
                    // Here we'll simulate applying the selected voucher
                    const voucherDiscount = document.querySelector('.voucher-discount');
                    const discountAmount = document.querySelector('.discount-amount');
                    const subtotal = parseFloat(document.querySelector('.subtotal').textContent.replace('RM ', ''));
                    const deliveryFee = parseFloat(document.querySelector('.delivery-fee').textContent.replace('RM ', ''));
                    const totalAmount = document.querySelector('.total-amount');
                    
                    // Simulate applying a 10% discount voucher
                    const discount = subtotal * 0.10;
                    const newTotal = (subtotal - discount) + deliveryFee;
                    
                    voucherDiscount.style.display = 'flex';
                    discountAmount.textContent = '-RM ' + discount.toFixed(2);
                    totalAmount.textContent = 'RM ' + newTotal.toFixed(2);
                    
                    showToast(`${voucherName} applied!`, 'success');
                }
            }
        });
    });
    
    // Form validation before submission
    const checkoutForm = document.getElementById('checkoutForm');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function(e) {
            // In a real application, you would do more thorough validation
            const selectedAddress = document.querySelector('input[name="shipping_address"]:checked');
            
            if (!selectedAddress) {
                e.preventDefault();
                showToast('Please select a delivery address', 'error');
                return;
            }
            
            if (selectedAddress.value === 'new') {
                // Validate new address fields
                const recipientName = document.getElementById('recipient_name').value.trim();
                const streetAddress = document.getElementById('street_address').value.trim();
                const city = document.getElementById('city').value.trim();
                const state = document.getElementById('state').value.trim();
                const postalCode = document.getElementById('postal_code').value.trim();
                const phoneNumber = document.getElementById('phone_number').value.trim();
                
                if (!recipientName || !streetAddress || !city || !state || !postalCode || !phoneNumber) {
                    e.preventDefault();
                    showToast('Please fill in all required address fields', 'error');
                    return;
                }
            }
            
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
            
            if ((paymentMethod === 'credit_card' || paymentMethod === 'debit_card') && 
                !validateCreditCard()) {
                e.preventDefault();
                return;
            }
            
            // If everything is valid, show a success message
            showToast('Order placed successfully!', 'success');
            
            // In a real application, the form would submit to the server
            // For demo purposes, we'll prevent actual submission
            // e.preventDefault();
        });
    }
    
    // Credit card validation
    function validateCreditCard() {
        const cardNumber = document.getElementById('card_number').value.trim();
        const expiryDate = document.getElementById('expiry_date').value.trim();
        const cvv = document.getElementById('cvv').value.trim();
        const cardName = document.getElementById('card_name').value.trim();
        
        if (!cardNumber || !expiryDate || !cvv || !cardName) {
            showToast('Please fill in all credit card details', 'error');
            return false;
        }
        
        // Simple validation for demo purposes
        if (!/^\d{16}$/.test(cardNumber.replace(/\s/g, ''))) {
            showToast('Please enter a valid 16-digit card number', 'error');
            return false;
        }
        
        if (!/^\d{2}\/\d{2}$/.test(expiryDate)) {
            showToast('Please enter expiry date in MM/YY format', 'error');
            return false;
        }
        
        if (!/^\d{3,4}$/.test(cvv)) {
            showToast('Please enter a valid CVV (3 or 4 digits)', 'error');
            return false;
        }
        
        return true;
    }
    
    // Toast notification function
    function showToast(message, type) {
        const toast = document.createElement('div');
        toast.className = `toast-notification ${type}`;
        toast.textContent = message;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.add('show');
        }, 100);
        
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => {
                document.body.removeChild(toast);
            }, 300);
        }, 3000);
    }
    
    // Update delivery fee when delivery method changes
    document.querySelectorAll('input[name="delivery_method"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const deliveryFeeElement = document.querySelector('.delivery-fee');
            const voucherDiscount = document.querySelector('.voucher-discount');
            const subtotal = parseFloat(document.querySelector('.subtotal').textContent.replace('RM ', ''));
            let deliveryFee = 5.00; // Standard delivery
            
            if (this.value === 'express') {
                deliveryFee = 10.00; // Express delivery
            }
            
            // Check if free shipping is applied
            if (voucherDiscount.style.display === 'flex' && 
                discountAmount.textContent.includes('-' + deliveryFeeElement.textContent.replace('RM ', ''))) {
                // Keep delivery free if free shipping voucher was applied
                deliveryFee = 0.00;
            }
            
            deliveryFeeElement.textContent = 'RM ' + deliveryFee.toFixed(2);
            
            // Recalculate total
            const discount = voucherDiscount.style.display === 'flex' ? 
                parseFloat(discountAmount.textContent.replace('-RM ', '')) : 0;
            const newTotal = (subtotal - discount) + deliveryFee;
            document.querySelector('.total-amount').textContent = 'RM ' + newTotal.toFixed(2);
        });
    });
});