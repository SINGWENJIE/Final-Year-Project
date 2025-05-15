<?php
session_start();
include '../db_connection.php';

$products_per_page = 3;

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $products_per_page;

if (isset($_POST['add_product'])) {
    $category_id = $_POST['category_id'];
    $product_name = mysqli_real_escape_string($conn, $_POST['prod_name']);
    $price = floatval($_POST['prod_price']);
    $stock = max(0, $_POST['stock']);

    $image = $_FILES['prod_image']['name'];
    $description = mysqli_real_escape_string($conn, $_POST['prod_description']);

    if (!empty($image)) {
        $target = "../assets/uploads/" . basename($image);
        if (move_uploaded_file($_FILES['prod_image']['tmp_name'], $target)) {
            $sql = "INSERT INTO product (category_id, prod_name, prod_price, stock, prod_image, prod_description) 
                    VALUES ('$category_id', '$product_name', '$price', '$stock', '$image', '$description')";
            if ($conn->query($sql) === TRUE) {
                echo "<script>alert('Product added successfully!'); window.location.href='Siderbar_Product.php';</script>";
                exit();
            } else {
                echo "<script>alert('Error: " . $conn->error . "');</script>";
            }
        } else {
            echo "<script>alert('Image upload failed!');</script>";
        }
    } else {
        echo "<script>alert('Please upload an image!');</script>";
    }
}

if (isset($_GET['delete_product']) && isset($_GET['category_id'])) {
    $product_id = $_GET['delete_product'];
    $category_id = $_GET['category_id'];

    $conn->begin_transaction();
    try {
        $conn->query("DELETE FROM order_item WHERE prod_id='$product_id'");
        $conn->query("DELETE FROM product WHERE prod_id='$product_id'");
        $conn->commit();
        echo "<script>alert('Product deleted successfully!'); window.location.href='Siderbar_Product.php?category_id=$category_id';</script>";
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>alert('Error deleting product: " . $conn->error . "');</script>";
    }
}

$categories = $conn->query("SELECT * FROM category");


$filter_category = isset($_GET['category_filter']) ? $_GET['category_filter'] : '';
$search_term = isset($_GET['search']) ? strtolower(trim($_GET['search'])) : '';

$sql = "SELECT p.*, c.category_name 
        FROM product p 
        INNER JOIN category c ON p.category_id = c.category_id";

$conditions = [];
if (!empty($filter_category)) $conditions[] = "p.category_id = '$filter_category'";
if (!empty($search_term)) $conditions[] = "LOWER(p.prod_name) LIKE '%$search_term%'";
if ($conditions) $sql .= " WHERE " . implode(" AND ", $conditions);

$sql .= " LIMIT $offset, $products_per_page";

$products = $conn->query($sql);

$count_sql = "SELECT COUNT(*) AS total_products FROM product p";
$conditions = [];
if (!empty($filter_category)) $conditions[] = "p.category_id = '$filter_category'";
if (!empty($search_term)) $conditions[] = "LOWER(p.prod_name) LIKE '%$search_term%'";

if ($conditions) {
    $count_sql .= " WHERE " . implode(" AND ", $conditions);
}

$count_result = $conn->query($count_sql);
$total_products = $count_result->fetch_assoc()['total_products'];

$totalPages = ceil($total_products / $products_per_page);


if (isset($_POST['update_product'])) {
    $id = $_POST['edit_prod_id'];
    $stock = max(0, $_POST['edit_stock']);
    $category_id = $_POST['edit_category_id'];
    $description = mysqli_real_escape_string($conn, $_POST['edit_description']);
    $price = floatval($_POST['edit_prod_price']);
    $prod_name = mysqli_real_escape_string($conn, $_POST['edit_prod_name']);

    $check_order_sql = "SELECT COUNT(*) AS order_count FROM order_item WHERE prod_id = '$id'";
    $check_order_result = $conn->query($check_order_sql);
    $order_count = $check_order_result->fetch_assoc()['order_count'];

    if ($order_count > 0) {
        $update_sql = "UPDATE product 
                       SET prod_name='$prod_name',prod_price='$price', stock='$stock', category_id='$category_id', prod_description='$description'
                       WHERE prod_id='$id'";
    } else {
        $update_sql = "UPDATE product 
                       SET prod_name='$prod_name',prod_price='$price', stock='$stock', category_id='$category_id', prod_description='$description'
                       WHERE prod_id='$id'";
    }

    if ($conn->query($update_sql) === TRUE) {
        echo "<script>alert('Product updated successfully!'); window.location.href='Siderbar_Product.php';</script>";
        exit();
    } else {
        echo "<script>alert('Error updating product: " . $conn->error . "');</script>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Products</title>
    <link rel="stylesheet" href="../assets/css/Category&product.css">
    <link rel="stylesheet" href="../assets/css/Global_style.css">
    <style>
        #add-product-form {
            display: none;
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="logo">
                <img src="../assets/images/logoname.png" alt="Logo">
            </div>
            <div class="profile">
                <a href="<?= ($_SESSION['admin_role'] === 'Super Admin') ? 'superadmin_dashboard.php' : 'admin_dashboard.php'; ?>">
                    <img src="<?= ($_SESSION['admin_role'] === 'Super Admin') ? '../assets/images/superadmin_photo.png' : '../assets/images/admin.png'; ?>">
                    <p><span class="admin_role"><?= $_SESSION['admin_role']; ?></span></p>
                </a>
            </div>
            <nav>
                <ul>
                    <?php if ($_SESSION['admin_role'] === 'Super Admin') : ?>
                        <li><a href="Siderbar_Admin_management.php"><img src="../assets/images/admin_photo.png" alt=""> Admin Management</a></li>
                    <?php endif ?>
                    <li><a href="Siderbar_Category.php"><img src="../assets/images/category.png" alt=""> Category</a></li>
                    <li><a href="Siderbar_Product.php"><img src="../assets/images/product.png" alt=""> Product</a></li>
                    <li><a href="Siderbar_CustomerList.php"><img src="../assets/images/customer_list.png" alt=""> Customer List</a></li>
                    <li><a href="Siderbar_ViewOrders.php"><img src="../assets/images/vieworder.png" alt=""> View Orders</a></li>
                    <li><a href="Siderbar_Delivery.php"><img src="../assets/images/delivery.png" alt=""> Delivery</a></li>
                    <li><a href="Siderbar_Reports.php"><img src="../assets/images/report.png" alt=""> Reports</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <div class="header">
                <button class="logout-btn" onclick="window.location.href='logout.php'">Log out</button>
            </div>

            <h2>Manage Products</h2>

            <button class="add-product-btn" onclick="toggleForm()">Add Product</button>

            <div id="add-product-form" class="popup-form" style="display:none;">
                <div class="popup-content">
                    <span class="close-btn" onclick="toggleForm()">&times;</span>
                    <form method="POST" enctype="multipart/form-data" class="product-form">
                        <div class="form-group">
                            <label for="category_id">Category</label>
                            <select name="category_id" id="category_id" required>
                                <option value="">Select Category</option>
                                <?php while ($cat = $categories->fetch_assoc()) { ?>
                                    <option value="<?= $cat['category_id'] ?>"><?= $cat['category_name'] ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="prod_name">Product Name</label>
                                <input type="text" name="prod_name" id="prod_name" required>
                            </div>
                            <div class="form-group">
                                <label for="prod_price">Price</label>
                                <input type="number" name="prod_price" id="prod_price" min="0" step="0.01" required>
                            </div>

                            <div class="form-group">
                                <label for="stock">Stock</label>
                                <input type="number" name="stock" id="stock" min="0" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="prod_image">Product Image</label>
                            <input type="file" name="prod_image" id="prod_image" required>
                        </div>

                        <div class="form-group">
                            <label for="prod_description">Product Description</label>
                            <textarea name="prod_description" id="prod_description" required></textarea>
                        </div>

                        <button type="submit" name="add_product" class="submit-btn">Add Product</button>
                    </form>
                </div>
            </div>

            <div>
                <form method="GET" id="filter-form">
                    <select name="category_filter" id="category_filter" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        <?php
                        $categories->data_seek(0);
                        while ($cat = $categories->fetch_assoc()) {
                            $selected = ($filter_category == $cat['category_id']) ? "selected" : "";
                            echo "<option value='{$cat['category_id']}' $selected>{$cat['category_name']}</option>";
                        }
                        ?>
                    </select>

                    <div class="search-container">
                        <input type="text" name="search" placeholder="Search by product name" value="<?= htmlspecialchars($search_term) ?>">
                        <button type="submit" style="padding: 5px 10px;">
                            <img src="../assets/images/search.png" alt="Search" width="20" height="20">
                        </button>
                    </div>
                </form>
            </div>

            <div class="product-table">
                <table>
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Stock</th>
                            <th>Price</th>
                            <th>Description</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($products && $products->num_rows > 0) {
                            while ($row = $products->fetch_assoc()) { ?>
                                <tr>
                                    <td><img src="../assets/uploads/<?= $row['prod_image'] ?>" alt="<?= $row['prod_name'] ?>" class="product-image"></td>
                                    <td><?= $row['prod_name'] ?></td>
                                    <td><?= $row['category_name'] ?></td>
                                    <td><?= $row['stock'] ?></td>
                                    <td><?= $row['prod_price'] ?></td>
                                    <td><?= $row['prod_description'] ?></td>
                                    <td>
                                    <img src="../assets/images/edit.png" alt="Edit" class="icon-btn" 
     data-id="<?= $row['prod_id'] ?>"
     data-name="<?= htmlspecialchars($row['prod_name'], ENT_QUOTES) ?>"
     data-price="<?= $row['prod_price'] ?>"
     data-stock="<?= $row['stock'] ?>"
     data-category="<?= $row['category_id'] ?>"
     data-description="<?= htmlspecialchars($row['prod_description'], ENT_QUOTES) ?>"
     onclick="handleEditClick(this)" 
     style="cursor: pointer;">





                                        <button onclick="confirmDelete(<?= $row['prod_id'] ?>)">
                                            <img src="../assets/images/delete.png" alt="Delete" class="icon-btn">
                                        </button>
                                    </td>
                                </tr>
                        <?php }
                        } else {
                            echo "<tr><td colspan='8'>No products found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1; ?>"> &lt; </a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?= $i; ?>" class="<?= ($i == $page) ? 'active' : ''; ?>"><?= $i; ?></a>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= $page + 1; ?>"> &gt; </a>
                <?php endif; ?>
            </div>
            <div id="edit-form" class="popup-form" style="display:none;">
                <div class="popup-content">
                    <span class="close-btn" onclick="toggleEditForm()">&times;</span>
                    <form method="POST" class="product-form">
                        <input type="hidden" name="edit_prod_id" id="edit_prod_id">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="edit_category_id">Category</label>
                                <select name="edit_category_id" id="edit_category_id" required>
                                    <?php
                                    $categories->data_seek(0);
                                    while ($cat = $categories->fetch_assoc()) {
                                        echo "<option value='{$cat['category_id']}'>{$cat['category_name']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="edit_prod_name">Product Name</label>
                                <input type="text" name="edit_prod_name" id="edit_prod_name" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_prod_price">Price</label>
                                <input type="number" name="edit_prod_price" id="edit_prod_price" min="0" step="0.01" required>
                            </div>

                            <div class="form-group">
                                <label for="edit_stock">Stock</label>
                                <input type="number" name="edit_stock" id="edit_stock" min="0" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="edit_description">Description</label>
                            <textarea name="edit_description" id="edit_description" required></textarea>
                        </div>
                        <button type="submit" name="update_product" class="submit-btn">Update</button>
                    </form>
                </div>
            </div>

        </main>
    </div>

    <script>
        function toggleForm() {
            const form = document.getElementById('add-product-form');
            form.style.display = (form.style.display === 'none' || form.style.display === '') ? 'flex' : 'none';

            const searchFilter = document.querySelector('.search-container');
            searchFilter.style.display = (form.style.display === 'none') ? 'block' : 'none';
        }

        function openEditForm(id, name, price, stock, categoryId, description) {
            document.getElementById('edit_prod_id').value = id;
            document.getElementById('edit_prod_name').value = name;
            document.getElementById('edit_prod_price').value = price;
            document.getElementById('edit_stock').value = stock;
            document.getElementById('edit_category_id').value = categoryId;
            document.getElementById('edit_description').value = description;

            document.getElementById('edit-form').style.display = 'flex';
        }
        function handleEditClick(element) {
    const id = element.getAttribute('data-id');
    const name = element.getAttribute('data-name');
    const price = element.getAttribute('data-price');
    const stock = element.getAttribute('data-stock');
    const categoryId = element.getAttribute('data-category');
    const description = element.getAttribute('data-description');

    openEditForm(id, name, price, stock, categoryId, description);
}


        function toggleEditForm() {
            document.getElementById('edit-form').style.display = 'none';
        }
    </script>
</body>

</html>