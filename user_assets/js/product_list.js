document.addEventListener('DOMContentLoaded', function() {
    // Common function to reorganize products into rows
    function reorganizeProducts(visibleProducts) {
        const productsContainer = document.getElementById('productsContainer');
        productsContainer.innerHTML = ''; // Clear current content

        for (let i = 0; i < visibleProducts.length; i += 5) {
            const row = document.createElement('div');
            row.className = 'product-row';
            
            for (let j = 0; j < 5 && (i + j) < visibleProducts.length; j++) {
                row.appendChild(visibleProducts[i + j]);
            }
            
            productsContainer.appendChild(row);
        }
    }

    // Search functionality
    const searchBtn = document.getElementById('searchBtn');
    const searchInput = document.getElementById('searchInput');
    
    function filterProducts() {
        const searchTerm = searchInput.value.toLowerCase();
        const visibleProducts = [];

        document.querySelectorAll('.product-card').forEach(card => {
            const productName = card.querySelector('h3').textContent.toLowerCase();
            const productDesc = card.querySelector('.description').textContent.toLowerCase();
            
            if (productName.includes(searchTerm) || productDesc.includes(searchTerm)) {
                visibleProducts.push(card.cloneNode(true)); // Clone the node to preserve
            }
        });

        reorganizeProducts(visibleProducts);
        reattachEventListeners(); // Reattach event listeners to new elements
    }

    // Category filter functionality
    const categoryFilter = document.getElementById('categoryFilter');
    const filterBtn = document.getElementById('filterBtn');
    
    function filterByCategory() {
        const selectedCategory = categoryFilter.value;
        const visibleProducts = [];

        document.querySelectorAll('.product-card').forEach(card => {
            const cardCategory = card.getAttribute('data-category');
            if (selectedCategory === '' || cardCategory === selectedCategory) {
                visibleProducts.push(card.cloneNode(true));
            }
        });

        reorganizeProducts(visibleProducts);
        reattachEventListeners();
    }

    // Reattach event listeners after DOM changes
    function reattachEventListeners() {
        // Quantity controls
        document.querySelectorAll('.quantity-plus, .quantity-minus').forEach(button => {
            button.addEventListener('click', handleQuantity);
        });

        // Add to cart buttons
        document.querySelectorAll('.add-to-cart').forEach(button => {
            button.addEventListener('click', handleAddToCart);
        });
    }

    // Event handlers
    function handleQuantity(e) {
        const wrapper = e.target.closest('.quantity-controls');
        const input = wrapper.querySelector('.quantity');
        const current = parseInt(input.value);
        const max = parseInt(input.dataset.max) || 999;

        if (e.target.classList.contains('quantity-plus')) {
            if (current < max) input.value = current + 1;
        } else if (e.target.classList.contains('quantity-minus')) {
            if (current > 1) input.value = current - 1;
        }
    }

    function handleAddToCart() {
        const productId = this.getAttribute('data-id');
        const quantity = this.parentElement.querySelector('.quantity').value;
        const productName = this.closest('.product-card').querySelector('h3').textContent;
        alert(`Added ${quantity} ${productName}(s) to cart!`);
    }

    // Initial event listeners
    searchBtn.addEventListener('click', filterProducts);
    searchInput.addEventListener('keyup', function(e) {
        if (e.key === 'Enter') filterProducts();
    });
    filterBtn.addEventListener('click', filterByCategory);
    
    // Initialize
    filterByCategory();
    reattachEventListeners();
});
