<?php
session_start();
include '../db_connection.php';

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

$products = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Products</title>
    <link rel="stylesheet" href="../assets/css/Category&product.css">
    <link rel="stylesheet" href="../assets/css/Global_style.css">
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

            <!-- Category Filter + Search -->
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

            <!-- Products Table -->
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
                            <?php if ($_SESSION['admin_role'] === 'Admin') : ?>
                                <th>Action</th>
                            <?php endif; ?>
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
                                    <td><?= number_format($row['prod_price'], 2) ?></td>
                                    <td><?= $row['prod_description'] ?></td>
                                    <?php if ($_SESSION['admin_role'] === 'Admin') : ?>
                                        <td>
                                            <a href="Siderbar_Product.php?edit_product=<?= $row['prod_id'] ?>"><img src="../assets/images/edit.png" alt="Edit" class="icon-btn"></a>
                                            <a href="Siderbar_Product.php?delete_product=<?= $row['prod_id'] ?>&category_id=<?= $row['category_id'] ?>" onclick="return confirm('Are you sure you want to delete this product?')">
                                                <img src="../assets/images/delete.png" alt="Delete" class="icon-btn">
                                            </a>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                        <?php }
                        } else {
                            echo "<tr><td colspan='7'>No products found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

        </main>
    </div>

</body>

</html>

<?php $conn->close(); ?>