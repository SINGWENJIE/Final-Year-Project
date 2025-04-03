document.addEventListener('DOMContentLoaded', function() {
    // In a real application, you would fetch this data from your backend
    // For demo purposes, we're using mock data
    const orderData = {
        orderId: '123456',
        customer: {
            name: 'John Doe',
            email: 'john.doe@example.com',
            phone: '+60123456789',
            address: '123 Main Street, Apartment 4B',
            city: 'Kuala Lumpur',
            state: 'Selangor',
            postcode: '50000'
        },
        deliveryDate: '2023-06-15',
        payment: {
            method: 'Credit Card',
            details: 'ending in 4242',
            status: 'Paid'
        },
        items: [
            {
                name: 'SUNLIGHT LINE DISHWASHING LIQUID 800ML',
                quantity: 1,
                price: 4.99,
                total: 4.99
            },
            {
                name: 'BURUH COOKING OIL 5KG',
                quantity: 1,
                price: 29.90,
                total: 29.90
            },
            {
                name: 'GLO LINE 1.2L',
                quantity: 1,
                price: 5.99,
                total: 5.99
            }
        ],
        subtotal: 40.88,
        deliveryFee: 0.00,
        discount: 5.00,
        total: 35.88
    };

    // Populate order data
    populateOrderData(orderData);

    // Set up event listeners
    document.getElementById('printReceipt').addEventListener('click', printReceipt);
    document.getElementById('trackOrder').addEventListener('click', trackOrder);
});

function populateOrderData(data) {
    // Order info
    document.getElementById('orderId').textContent = data.orderId;
    document.getElementById('customerEmail').textContent = data.customer.email;
    document.getElementById('customerEmailDisplay').textContent = data.customer.email;

    // Customer info
    document.getElementById('customerName').textContent = data.customer.name;
    document.getElementById('deliveryAddress').textContent = data.customer.address;
    document.getElementById('deliveryLocation').textContent = `${data.customer.postcode} ${data.customer.city}, ${data.customer.state}`;
    document.getElementById('customerPhone').textContent = data.customer.phone;
    
    // Format delivery date
    const deliveryDate = new Date(data.deliveryDate);
    const options = { day: 'numeric', month: 'short', year: 'numeric' };
    document.getElementById('deliveryDate').textContent = deliveryDate.toLocaleDateString('en-US', options);

    // Payment info
    document.getElementById('paymentMethod').textContent = `${data.payment.method} ${data.payment.details}`;
    document.getElementById('totalAmount').textContent = `RM${data.total.toFixed(2)}`;

    // Order items
    const itemsList = document.getElementById('orderItemsList');
    itemsList.innerHTML = '';
    
    data.items.forEach(item => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${item.name}</td>
            <td>${item.quantity}</td>
            <td>RM${item.price.toFixed(2)}</td>
            <td>RM${item.total.toFixed(2)}</td>
        `;
        itemsList.appendChild(row);
    });

    // Order totals
    document.getElementById('orderSubtotal').textContent = `RM${data.subtotal.toFixed(2)}`;
    document.getElementById('deliveryFee').textContent = `RM${data.deliveryFee.toFixed(2)}`;
    document.getElementById('orderDiscount').textContent = `-RM${data.discount.toFixed(2)}`;
    document.getElementById('orderTotal').textContent = `RM${data.total.toFixed(2)}`;
}

function printReceipt() {
    alert('Printing receipt...');
    // In a real app, you would implement actual print functionality
    // window.print();
}

function trackOrder() {
    alert('Redirecting to order tracking...');
    // In a real app, you would redirect to the tracking page
    // window.location.href = 'tracking.html?id=' + document.getElementById('orderId').textContent;
}