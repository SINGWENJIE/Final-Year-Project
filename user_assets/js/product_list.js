document.addEventListener('DOMContentLoaded', function() {
    // Search functionality
    const searchBtn = document.getElementById('searchBtn');
    const searchInput = document.getElementById('searchInput');
    const productCards = document.querySelectorAll('.product-card');
    
    searchBtn.addEventListener('click', filterProducts);
    searchInput.addEventListener('keyup', function(e) {
        if (e.key === 'Enter') {
            filterProducts();
        }
    });
    
    function filterProducts() {
        const searchTerm = searchInput.value.toLowerCase();
        
        productCards.forEach(card => {
            const productName = card.querySelector('h3').textContent.toLowerCase();
            const productDesc = card.querySelector('.description').textContent.toLowerCase();
            
            if (productName.includes(searchTerm) || productDesc.includes(searchTerm)) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }
    
    // Category filter functionality
    const categoryFilter = document.getElementById('categoryFilter');
    const filterBtn = document.getElementById('filterBtn');
    
    filterBtn.addEventListener('click', filterByCategory);
    
    function filterByCategory() {
        const selectedCategory = categoryFilter.value;
        const productsContainer = document.getElementById('productsContainer');
        const visibleProducts = [];
        
        // First collect all visible products
        productCards.forEach(card => {
            const cardCategory = card.getAttribute('data-category');
            
            if (selectedCategory === '' || cardCategory === selectedCategory) {
                visibleProducts.push(card);
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
        
        // Now reorganize into rows of 5
        productsContainer.innerHTML = ''; // Clear current content
        
        for (let i = 0; i < visibleProducts.length; i += 5) {
            const row = document.createElement('div');
            row.className = 'product-row';
            
            // Add up to 5 products to this row
            for (let j = 0; j < 5 && (i + j) < visibleProducts.length; j++) {
                row.appendChild(visibleProducts[i + j]);
            }
            
            productsContainer.appendChild(row);
        }
    }

    // Quantity controls using event delegation
    document.addEventListener('click', function(e) {
    if (e.target.classList.contains('quantity-plus')) {
        const wrapper = e.target.closest('.quantity-controls');
        const input = wrapper.querySelector('.quantity');
        const current = parseInt(input.value);
        const max = parseInt(input.dataset.max) || 999; // Fallback value
        
        if (current < max) {
            input.value = current + 1;
        }
    }
    
    if (e.target.classList.contains('quantity-minus')) {
        const wrapper = e.target.closest('.quantity-controls');
        const input = wrapper.querySelector('.quantity');
        const current = parseInt(input.value);
        
        if (current > 1) {
            input.value = current - 1;
        }
    }
    });
    
    // Add to cart functionality
    const addToCartButtons = document.querySelectorAll('.add-to-cart');
    
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-id');
            const quantity = this.parentElement.querySelector('.quantity').value;
            
            // Here you would typically send this data to your server
            // For now, we'll just show an alert
            const productName = this.closest('.product-card').querySelector('h3').textContent;
            alert(`Added ${quantity} ${productName}(s) to cart!`);
            
            // In a real implementation, you would:
            // 1. Send an AJAX request to your server
            // 2. Update the cart count in the UI
            // 3. Possibly show a notification
        });
    });
    
    // Initialize products display
    filterByCategory();
});
