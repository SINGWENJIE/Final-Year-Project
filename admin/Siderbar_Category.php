<?php
session_start();
include '../db_connection.php';

$isAdmin = $_SESSION['admin_role'] === 'Admin';
$isSuperAdmin = $_SESSION['admin_role'] === 'Super Admin';

if (isset($_POST['add_category']) && $isAdmin) {
    $category_name = $_POST['category_name'];
    $conn->query("INSERT INTO category (category_name) VALUES ('$category_name')");
    header("Location: ../admin/Siderbar_Category.php");
    exit();
}

if (isset($_GET['delete_category']) && $isAdmin) {
    $category_id = $_GET['delete_category'];

    $conn->query("DELETE FROM product WHERE category_id='$category_id'");

    $sql = "DELETE FROM category WHERE category_id='$category_id'";
    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Category deleted successfully!'); window.location.href='Siderbar_Category.php';</script>";
    } else {
        echo "<script>alert('Error: " . $conn->error . "');</script>";
    }
    exit();
}
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
                <button class="logout-btn" onclick="window.location.href='../admin/logout.php'">Log out</button>
            </div>

            <h2>Manage Categories</h2>
            <?php if ($isAdmin) : ?>
                <form method="POST">
                    <input type="text" name="category_name" placeholder="Category Name" required>
                    <button type="submit" name="add_category">Add Category</button>
                </form>

            <?php endif; ?>

            <div class="container">
                <?php
                $categories = $conn->query("SELECT * FROM category");
                while ($row = $categories->fetch_assoc()) { ?>
                    <div class="card">
                        <p><?= $row['category_name'] ?></p>
                        
                            <button onclick="window.location.href='../admin/Siderbar_Product.php?category_filter=<?= $row['category_id'] ?>'">
                                <img src="../assets/images/manage.png" alt="Manage Products" class="icon-btn">
                            </button>
                            <?php if ($isAdmin) : ?>
                            <button onclick="confirmDeleteCategory(<?= $row['category_id'] ?>)">
                                <img src="../assets/images/delete.png" alt="Delete" class="icon-btn">
                            </button>
                        <?php endif; ?>
                    </div>
                <?php } ?>
            </div>

            <script>
                function confirmDeleteCategory(categoryId) {
                    if (confirm("Are you sure you want to delete this category? All products in this category will be deleted!")) {
                        window.location.href = "Siderbar_Category.php?delete_category=" + categoryId;
                    }
                }
            </script>

        </main>
    </div>
</body>

</html>

<?php $conn->close(); ?>