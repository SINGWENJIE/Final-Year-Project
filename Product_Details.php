<?php
require_once 'db_connection.php';

// Fetch products
$sql = "SELECT * FROM product ORDER BY prod_id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Product Details - Gogo Supermarket</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<h1>Gogo Supermarket - Product Details</h1>

<input type="text" id="searchInput" placeholder="Search for a product...">

<table id="productsTable">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Category ID</th>
            <th>Price (RM)</th>
            <th>Description</th>
            <th>Quantity</th>
            <th>Stock</th>
            <th>Image</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['prod_id']) ?></td>
                    <td><?= htmlspecialchars($row['prod_name']) ?></td>
                    <td><?= htmlspecialchars($row['category_id']) ?></td>
                    <td><?= number_format($row['prod_price'], 2) ?></td>
                    <td><?= htmlspecialchars($row['prod_description']) ?></td>
                    <td><?= htmlspecialchars($row['prod_quantity']) ?></td>
                    <td><?= htmlspecialchars($row['stock']) ?></td>
                    <td>
                        <?php if (!empty($row['prod_image'])): ?>
                            <img src="images/<?= htmlspecialchars($row['prod_image']) ?>" alt="Product Image" width="60">
                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="8">No products found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<script src="script.js"></script>
</body>
</html>
