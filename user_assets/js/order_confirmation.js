document.addEventListener('DOMContentLoaded', function() {
    // Animation for order items
    const orderItems = document.querySelectorAll('.order-item');
    orderItems.forEach((item, index) => {
        item.style.opacity = '0';
        item.style.transform = 'translateY(20px)';
        item.style.transition = `all 0.3s ease ${index * 0.1}s`;
        
        setTimeout(() => {
            item.style.opacity = '1';
            item.style.transform = 'translateY(0)';
        }, 100);
    });
    
    // Print order functionality
    const printButton = document.createElement('button');
    printButton.innerHTML = '<i class="fas fa-print"></i> Print Order';
    printButton.className = 'btn btn-print';
    printButton.addEventListener('click', function() {
        window.print();
    });
    
    const actionsContainer = document.querySelector('.confirmation-actions');
    if (actionsContainer) {
        actionsContainer.appendChild(printButton);
    }
    
    // Add to calendar functionality (for delivery date)
    const estimatedDelivery = document.querySelector('.estimated-delivery');
    if (estimatedDelivery) {
        const deliveryDate = estimatedDelivery.textContent.replace('Estimated Delivery: ', '');
        const addToCalendar = document.createElement('a');
        addToCalendar.href = '#';
        addToCalendar.innerHTML = '<i class="far fa-calendar-plus"></i> Add to Calendar';
        addToCalendar.className = 'add-to-calendar';
        addToCalendar.style.marginLeft = '10px';
        addToCalendar.style.fontSize = '0.8rem';
        addToCalendar.addEventListener('click', function(e) {
            e.preventDefault();
            // In a real app, this would open a calendar event dialog
            alert('Add this delivery date to your calendar:\n' + deliveryDate);
        });
        
        estimatedDelivery.appendChild(addToCalendar);
    }
    
    // Track order button
    if (document.querySelector('.delivery-status')) {
        const trackButton = document.createElement('a');
        trackButton.href = 'track_order.php?order_id=' + window.location.search.split('=')[1];
        trackButton.innerHTML = '<i class="fas fa-map-marker-alt"></i> Track Order';
        trackButton.className = 'btn btn-track';
        trackButton.style.marginLeft = '10px';
        
        const actionsContainer = document.querySelector('.confirmation-actions');
        if (actionsContainer) {
            actionsContainer.insertBefore(trackButton, actionsContainer.firstChild);
        }
    }
});