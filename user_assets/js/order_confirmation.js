document.addEventListener('DOMContentLoaded', function() {
    // Print order functionality
    const printButton = document.createElement('button');
    printButton.className = 'btn btn-secondary';
    printButton.innerHTML = '<i class="fas fa-print"></i> Print Order';
    printButton.addEventListener('click', function() {
        window.print();
    });
    
    document.querySelector('.confirmation-actions').appendChild(printButton);
    
    // Show toast message if order was just placed
    if (window.location.search.includes('success=1')) {
        showToast('Order placed successfully!', 'success');
    }
    
    // Track order button functionality
    const trackButton = document.createElement('a');
    trackButton.className = 'btn btn-secondary';
    trackButton.innerHTML = '<i class="fas fa-truck"></i> Track Order';
    trackButton.href = '#';
    trackButton.addEventListener('click', function(e) {
        e.preventDefault();
        showToast('Tracking information will be available once your order is shipped.', 'info');
    });
    
    document.querySelector('.confirmation-actions').appendChild(trackButton);
    
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
});