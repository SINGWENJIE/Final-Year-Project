document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const cartIcon = document.getElementById('cartIcon');
    const cartOverlay = document.getElementById('cartOverlay');
    const cartSidebar = document.getElementById('cartSidebar');
    const closeCart = document.getElementById('closeCart');
    const cartItemsContainer = document.getElementById('cartItems');
    const cartCount = document.querySelector('.cart-count');
    const cartTotal = document.querySelector('.total-price');
    
    // Sample product data (in a real app, this would come from your database)
    const products = [
        {
            id: 1,
            name: 'BURUH COOKING OIL 5KG',
            price: 29.90,
            originalPrice: 30.90,
            image: 'https://via.placeholder.com/80'
        },
        {
            id: 2,
            name: 'SUNLIGHT LINE DISHWASHING LIQUID 800ML',
            price: 4.99,
            originalPrice: 6.49,
            image: 'https://via.placeholder.com/80'
        },
        {
            id: 3,
            name: 'GLO LINE 1.2L',
            price: 5.99,
            originalPrice: 9.90,
            image: 'https://via.placeholder.com/80'
        }
    ];
    
    // Cart state
    let cart = [];
    
    // Initialize the app
    function init() {
        // Load cart from localStorage if available
        const savedCart = localStorage.getItem('cart');
        if (savedCart) {
            cart = JSON.parse(savedCart);
            updateCart();
        }
        
        // Set up event listeners
        setupEventListeners();
    }
    
    // Set up all event listeners
    function setupEventListeners() {
        // Cart icon click
        cartIcon.addEventListener('click', toggleCart);
        
        // Close cart button
        closeCart.addEventListener('click', toggleCart);
        
        // Overlay click
        cartOverlay.addEventListener('click', toggleCart);
        
        // You would also add event listeners for "Add to Cart" buttons on product cards
        // For this example, we'll manually add some items
        setTimeout(() => {
            addToCart(1); // Add cooking oil
            addToCart(2); // Add dishwashing liquid
            addToCart(3); // Add GLO line
        }, 1000);
    }
    
    // Toggle cart visibility
    function toggleCart() {
        cartOverlay.classList.toggle('active');
        cartSidebar.classList.toggle('active');
        document.body.style.overflow = cartSidebar.classList.contains('active') ? 'hidden' : '';
    }
    
    // Add item to cart
    function addToCart(productId) {
        const product = products.find(p => p.id === productId);
        if (!product) return;
        
        const existingItem = cart.find(item => item.id === productId);
        
        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            cart.push({
                ...product,
                quantity: 1
            });
        }
        
        saveCart();
        updateCart();
    }
    
    // Remove item from cart
    function removeFromCart(productId) {
        cart = cart.filter(item => item.id !== productId);
        saveCart();
        updateCart();
    }
    
    // Update item quantity
    function updateQuantity(productId, newQuantity) {
        const item = cart.find(item => item.id === productId);
        if (item) {
            item.quantity = newQuantity;
            if (newQuantity <= 0) {
                removeFromCart(productId);
            } else {
                saveCart();
                updateCart();
            }
        }
    }
    
    // Save cart to localStorage
    function saveCart() {
        localStorage.setItem('cart', JSON.stringify(cart));
    }
    
    // Update cart UI
    function updateCart() {
        updateCartCount();
        renderCartItems();
        updateCartTotal();
    }
    
    // Update cart count in header
    function updateCartCount() {
        const totalItems = cart.reduce((total, item) => total + item.quantity, 0);
        cartCount.textContent = totalItems;
    }
    
    // Render cart items
    function renderCartItems() {
        if (cart.length === 0) {
            cartItemsContainer.innerHTML = '<div class="empty-cart">Your cart is empty</div>';
            return;
        }
        
        cartItemsContainer.innerHTML = cart.map(item => `
            <div class="cart-item" data-id="${item.id}">
                <img src="${item.image}" alt="${item.name}" class="cart-item-img">
                <div class="cart-item-details">
                    <h3 class="cart-item-title">${item.name}</h3>
                    <div class="cart-item-price">RM${item.price.toFixed(2)}</div>
                    <button class="cart-item-remove">Remove</button>
                    <div class="cart-item-quantity">
                        <button class="quantity-btn minus">-</button>
                        <span class="quantity-value">${item.quantity}</span>
                        <button class="quantity-btn plus">+</button>
                    </div>
                </div>
            </div>
        `).join('');
        
        // Add event listeners to quantity buttons
        document.querySelectorAll('.minus').forEach(button => {
            button.addEventListener('click', function() {
                const itemId = parseInt(this.closest('.cart-item').dataset.id);
                const item = cart.find(item => item.id === itemId);
                if (item) {
                    updateQuantity(itemId, item.quantity - 1);
                }
            });
        });
        
        document.querySelectorAll('.plus').forEach(button => {
            button.addEventListener('click', function() {
                const itemId = parseInt(this.closest('.cart-item').dataset.id);
                const item = cart.find(item => item.id === itemId);
                if (item) {
                    updateQuantity(itemId, item.quantity + 1);
                }
            });
        });
        
        // Add event listeners to remove buttons
        document.querySelectorAll('.cart-item-remove').forEach(button => {
            button.addEventListener('click', function() {
                const itemId = parseInt(this.closest('.cart-item').dataset.id);
                removeFromCart(itemId);
            });
        });
    }
    
    // Update cart total
    function updateCartTotal() {
        const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        cartTotal.textContent = `RM${total.toFixed(2)}`;
    }
    
    // Initialize the app
    init();
});