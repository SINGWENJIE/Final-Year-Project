document.addEventListener('DOMContentLoaded', function() {
    // Store original product cards on initial load
    const originalProducts = Array.from(document.querySelectorAll('.product-card')).map(card => card.cloneNode(true));
    let currentProducts = [...originalProducts];

    // Common reorganization function
    function reorganizeProducts(products) {
        const productsContainer = document.getElementById('productsContainer');
        productsContainer.innerHTML = '';

        for (let i = 0; i < products.length; i += 5) {
            const row = document.createElement('div');
            row.className = 'product-row';
            
            for (let j = 0; j < 5 && (i + j) < products.length; j++) {
                row.appendChild(products[i + j].cloneNode(true));
            }
            
            productsContainer.appendChild(row);
        }
        reattachEventListeners();
    }

    // Search functionality
    const searchBtn = document.getElementById('searchBtn');
    const searchInput = document.getElementById('searchInput');

    function filterProducts() {
        const searchTerm = searchInput.value.toLowerCase();
        currentProducts = originalProducts.filter(card => {
            const productName = card.querySelector('h3').textContent.toLowerCase();
            const productDesc = card.querySelector('.description').textContent.toLowerCase();
            return productName.includes(searchTerm) || productDesc.includes(searchTerm);
        });
        reorganizeProducts(currentProducts);
    }

    // Category filter functionality
    const categoryFilter = document.getElementById('categoryFilter');
    const filterBtn = document.getElementById('filterBtn');

    function filterByCategory() {
        const selectedCategory = categoryFilter.value;
        currentProducts = originalProducts.filter(card => {
            const cardCategory = card.getAttribute('data-category');
            return selectedCategory === '' || cardCategory === selectedCategory;
        });
        reorganizeProducts(currentProducts);
    }

    // Reattach event listeners
    function reattachEventListeners() {
        document.querySelectorAll('.quantity-plus, .quantity-minus').forEach(button => {
            button.addEventListener('click', handleQuantity);
        });

        document.querySelectorAll('.add-to-cart').forEach(button => {
            button.addEventListener('click', handleAddToCart);
        });
    }

    // Quantity control handler
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

    // Add to cart handler
    function handleAddToCart() {
        const productId = this.getAttribute('data-id');
        const quantity = this.parentElement.querySelector('.quantity').value;
        const productName = this.closest('.product-card').querySelector('h3').textContent;
        alert(`Added ${quantity} ${productName}(s) to cart!`);
    }

    // Event listeners
    searchBtn.addEventListener('click', filterProducts);
    searchInput.addEventListener('keyup', function(e) {
        if (e.key === 'Enter') filterProducts();
    });
    filterBtn.addEventListener('click', filterByCategory);

    // Initial load
    reorganizeProducts(originalProducts);
});