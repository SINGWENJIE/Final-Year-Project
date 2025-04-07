<?php
// Product_Details.php
// Database connection
$servername = "localhost";
$username = "root"; // Replace with your database username
$password = ""; // Replace with your database password
$dbname = "gogo_supermarket";

// Create connection
$conn = new mysqli("localhost", "root", "", "gogo_supermarket");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle actions (Edit, Copy, Delete)
if (isset($_POST['action'])) {
    $prod_id = $_POST['prod_id'];
    
    switch ($_POST['action']) {
        case 'Edit':
            // Redirect to edit page or show edit form
            header("Location: edit_product.php?id=$prod_id");
            exit();
            break;
            
        case 'Copy':
            // Handle copy logic
            $sql = "INSERT INTO products (prod_name, category_id, prod_price, prod_guariffly, prod_image, prod_desc) 
                    SELECT prod_name, category_id, prod_price, prod_guariffly, prod_image, prod_desc 
                    FROM products WHERE prod_id = $prod_id";
            if ($conn->query($sql) === TRUE) {
                $message = "Product copied successfully!";
            } else {
                $message = "Error copying product: " . $conn->error;
            }
            break;
            
        case 'Delete':
            // Handle delete logic
            $sql = "DELETE FROM products WHERE prod_id = $prod_id";
            if ($conn->query($sql) === TRUE) {
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

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Details - 9999 Supermarket</title>
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
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
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
    </style>
</head>
<body>
    <h1>GoGo Supermarket - Product Details</h1>
    
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
            <span>Number of rows: 25</span> | 
            <span>Filter rows</span> | 
            <input type="text" placeholder="Search this table">
        </div>
    </div>
    
    <table>
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
                        <td><?php echo $row['prod_id']; ?></td>
                        <td><?php echo $row['prod_name']; ?></td>
                        <td><?php echo $row['category_id']; ?></td>
                        <td><?php echo $row['prod_price']; ?></td>
                        <td><?php echo $row['prod_desc']; ?></td>
                        <td>
                            <?php if (!empty($row['prod_image'])): ?>
                                <img src="<?php echo $row['prod_image']; ?>" alt="Product Image" width="50">
                            <?php endif; ?>
                        </td>
                        <td>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="prod_id" value="<?php echo $row['prod_id']; ?>">
                                <button type="submit" name="action" value="Edit">Edit</button>
                                <button type="submit" name="action" value="Copy">Copy</button>
                                <button type="submit" name="action" value="Delete">Delete</button>
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
        <button form="bulk-form" type="submit" name="action" value="Delete">Delete</button>
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
    </script>
</body>
</html>