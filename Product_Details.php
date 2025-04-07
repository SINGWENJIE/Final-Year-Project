<?php
// Product_Details.php
require_once 'db_connection.php'; // Include your existing connection file

// Handle actions (Edit, Copy, Delete)
if (isset($_POST['action'])) {
    $prod_id = $_POST['prod_id'];
    
    switch ($_POST['action']) {
        case 'Edit':
            header("Location: edit_product.php?id=$prod_id");
            exit();
            break;
            
        case 'Copy':
            $sql = "INSERT INTO products (prod_name, category_id, prod_price, prod_guariffly, prod_image, prod_desc) 
                    SELECT prod_name, category_id, prod_price, prod_guariffly, prod_image, prod_desc 
                    FROM products WHERE prod_id = $prod_id";
            if ($conn->query($sql)) {
                $message = "Product copied successfully!";
            } else {
                $message = "Error copying product: " . $conn->error;
            }
            break;
            
        case 'Delete':
            $sql = "DELETE FROM products WHERE prod_id = $prod_id";
            if ($conn->query($sql)) {
                $message = "Product deleted successfully!";
            } else {
                $message = "Error deleting product: " . $conn->error;
            }
            break;
    }
}

// Fetch products from database
$sql = "SELECT * FROM products LIMIT 25";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Details - Gogo Supermarket</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            position: sticky;
            top: 0;
        }
        .action-buttons {
            margin-top: 20px;
        }
        .message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .success {
            background-color: #dff0d8;
            color: #3c763d;
        }
        .error {
            background-color: #f2dede;
            color: #a94442;
        }
        button {
            padding: 5px 10px;
            margin: 2px;
            cursor: pointer;
        }
        input[type="text"] {
            padding: 5px;
        }
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
            }
            table {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <h1>Gogo Supermarket - Product Details</h1>
    
    <?php if (isset($message)): ?>
        <div class="message <?php echo strpos($message, 'Error') !== false ? 'error' : 'success'; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    
    <div class="header">
        <div>
            <strong>Profiling</strong> | <a href="#">Edit mine</a> | <a href="#">Edit</a> | 
            <a href="#">Explain SQL</a> | <a href="#">Create PHP code</a> | <a href="#">Refresh</a>
        </div>
        <div>
            <span>Show all</span> | 
            <span>Number of rows: <?php echo $result->num_rows; ?></span> | 
            <span>Filter rows</span> | 
            <input type="text" placeholder="Search this table" id="searchInput">
        </div>
    </div>
    
    <table id="productsTable">
        <thead>
            <tr>
                <th><input type="checkbox" id="select-all"> Check all</th>
                <th>Product ID</th>
                <th>Product Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Description</th>
                <th>Image</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><input type="checkbox" name="selected[]" value="<?php echo $row['prod_id']; ?>"></td>
                        <td><?php echo htmlspecialchars($row['prod_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['prod_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['category_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['prod_price']); ?></td>
                        <td><?php echo htmlspecialchars($row['prod_desc'] ?? ''); ?></td>
                        <td>
                            <?php if (!empty($row['prod_image'])): ?>
                                <img src="<?php echo htmlspecialchars($row['prod_image']); ?>" alt="Product Image" width="50">
                            <?php endif; ?>
                        </td>
                        <td>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="prod_id" value="<?php echo $row['prod_id']; ?>">
                                <button type="submit" name="action" value="Edit">Edit</button>
                                <button type="submit" name="action" value="Copy">Copy</button>
                                <button type="submit" name="action" value="Delete" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8">No products found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    
    <div class="action-buttons">
        <strong>With selected:</strong>
        <button form="bulk-form" type="submit" name="action" value="Edit">Edit</button>
        <button form="bulk-form" type="submit" name="action" value="Copy">Copy</button>
        <button form="bulk-form" type="submit" name="action" value="Delete" onclick="return confirm('Are you sure you want to delete selected items?')">Delete</button>
    </div>
    
    <form id="bulk-form" method="post"></form>
    
    <script>
        // Select all checkbox functionality
        document.getElementById('select-all').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('tbody input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });

        // Simple search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const input = this.value.toLowerCase();
            const rows = document.querySelectorAll('#productsTable tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(input) ? '' : 'none';
            });
        });
    </script>
</body>
</html>