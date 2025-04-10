<?php
// Product_Details.php
require_once 'db_connection.php';

// Handle product actions
if (isset($_GET['id'])) {
    $product_id = intval($_GET['id']);
    $sql = "SELECT * FROM product WHERE prod_id = $product_id";
    $result = $conn->query($sql);
    $product = $result->fetch_assoc();
    
    if (!$product) {
        die("Product not found");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['prod_name'] ?? 'Product Details'); ?> - Gogo Supermarket</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-color: #2e7d32;
            --secondary-color: #f5f5f5;
            --discount-color: #e53935;
            --price-color: #212121;
            --old-price-color: #757575;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f9f9f9;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .breadcrumb {
            padding: 15px 0;
            font-size: 14px;
            color: #666;
        }
        
        .breadcrumb a {
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .breadcrumb a:hover {
            text-decoration: underline;
        }
        
        .breadcrumb span {
            margin: 0 5px;
            color: #999;
        }
        
        .product-container {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 30px;
            margin-top: 20px;
        }
        
        .product-image {
            flex: 1;
            min-width: 300px;
            max-width: 500px;
            text-align: center;
        }
        
        .product-image img {
            max-width: 100%;
            height: auto;
            border-radius: 4px;
        }
        
        .product-details {
            flex: 1;
            min-width: 300px;
        }
        
        .discount-badge {
            display: inline-block;
            background-color: var(--discount-color);
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .product-title {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 15px;
            color: #222;
        }
        
        .product-meta {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .product-meta span {
            color: #666;
        }
        
        .product-meta strong {
            color: #444;
        }
        
        .price-container {
            margin: 25px 0;
        }
        
        .current-price {
            font-size: 28px;
            font-weight: bold;
            color: var(--price-color);
        }
        
        .original-price {
            font-size: 18px;
            color: var(--old-price-color);
            text-decoration: line-through;
            margin-left: 10px;
        }
        
        .quantity-selector {
            display: flex;
            align-items: center;
            margin: 25px 0;
        }
        
        .quantity-btn {
            width: 40px;
            height: 40px;
            border: 1px solid #ddd;
            background: #f5f5f5;
            font-size: 18px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .quantity-input {
            width: 60px;
            height: 40px;
            text-align: center;
            border: 1px solid #ddd;
            border-left: none;
            border-right: none;
            font-size: 16px;
        }
        
        .add-to-cart {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 12px 25px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-bottom: 20px;
            width: 100%;
            max-width: 300px;
        }
        
        .add-to-cart:hover {
            background-color: #1b5e20;
        }
        
        .special-offer {
            color: var(--primary-color);
            font-weight: 500;
            margin-bottom: 10px;
        }
        
        .points-earned {
            color: #666;
            font-size: 14px;
            margin-bottom: 15px;
        }
        
        .delivery-info {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #666;
            font-size: 14px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .delivery-info i {
            color: var(--primary-color);
        }
        
        @media (max-width: 768px) {
            .product-container {
                flex-direction: column;
                padding: 20px;
            }
            
            .product-image, .product-details {
                min-width: 100%;
            }
            
            .add-to-cart {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="breadcrumb">
            <a href="#">Homepage</a>
            <span>></span>
            <a href="#">Grocery</a>
            <span>></span>
            <a href="#">Commodities</a>
            <span>></span>
            <a href="#">Oil</a>
        </div>
        
        <?php if (isset($product)): ?>
        <div class="product-container">
            <div class="product-image">
                <img src="<?php echo htmlspecialchars($product['prod_image'] ?? 'placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($product['prod_name']); ?>">
            </div>
            
            <div class="product-details">
                <div class="discount-badge">-3%</div>
                <h1 class="product-title"><?php echo htmlspecialchars($product['prod_name']); ?></h1>
                
                <div class="product-meta">
                    <div><span>Brand:</span> <strong>LABOUR</strong></div>
                    <div><span>Category:</span> <strong><?php echo htmlspecialchars($product['category_id']); ?></strong></div>
                </div>
                
                <div class="price-container">
                    <span class="current-price">RM<?php echo number_format($product['prod_price'] * 0.97, 2); ?>/Each</span>
                    <span class="original-price">RM<?php echo number_format($product['prod_price'], 2); ?></span>
                </div>
                
                <div class="quantity-selector">
                    <button class="quantity-btn minus">-</button>
                    <input type="number" class="quantity-input" value="1" min="1">
                    <button class="quantity-btn plus">+</button>
                </div>
                
                <button class="add-to-cart">Add to Cart</button>
                
                <p class="special-offer">Special discounted price</p>
                <p class="points-earned">Receive <?php echo number_format($product['prod_price'] * 0.97, 2); ?> Points/Each</p>
                
                <div class="member-info">
                    <span>Member: Chew Wei Xin</span>
                </div>
                
                <div class="delivery-info">
                    <i class="fas fa-truck"></i>
                    <span>Next-Day Delivery delivered the next day</span>
                </div>
            </div>
        </div>
        <?php else: ?>
            <div class="error-message">Product not found</div>
        <?php endif; ?>
    </div>

    <script>
        // Quantity selector functionality
        document.querySelector('.minus').addEventListener('click', function() {
            const input = document.querySelector('.quantity-input');
            if (parseInt(input.value) > 1) {
                input.value = parseInt(input.value) - 1;
            }
        });
        
        document.querySelector('.plus').addEventListener('click', function() {
            const input = document.querySelector('.quantity-input');
            input.value = parseInt(input.value) + 1;
        });
        
        // Validate quantity input
        document.querySelector('.quantity-input').addEventListener('change', function() {
            if (this.value < 1 || isNaN(this.value)) {
                this.value = 1;
            }
        });
        
        // Add to cart functionality
        document.querySelector('.add-to-cart').addEventListener('click', function() {
            const quantity = document.querySelector('.quantity-input').value;
            const productId = <?php echo $product['prod_id'] ?? 'null'; ?>;
            
            // Here you would typically make an AJAX call to add to cart
            alert(`Added ${quantity} item(s) to cart!`);
            
            // Example AJAX call:
            /*
            fetch('add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    product_id: productId,
                    quantity: quantity
                })
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
            });
            */
        });
    </script>
</body>
</html>