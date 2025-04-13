<?php
session_start();
include '../db_connection.php';

if (isset($_SESSION['admin_role'])) {
    $role = $_SESSION['admin_role'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery</title>
    <link rel="stylesheet" href="../assets/css/delivery.css">
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
                <li><a href="Siderbar_Category_Product.php"><img src="../assets/images/product.png" alt=""> Category & Product</a></li>
                <li><a href="Siderbar_CustomerList.php"><img src="../assets/images/customer_list.png" alt=""> Customer List</a></li>
                <li><a href="Siderbar_ViewOrders.php"><img src="../assets/images/vieworder.png" alt=""> View Orders</a></li>
                <li><a href="Siderbar_Delivery.php"><img src="../assets/images/delivery.png" alt=""> Delivery</a></li>
                <li><a href="Siderbar_Reports.php"><img src="../assets/images/report.png" alt=""> Reports</a></li>
            </ul>
        </nav>
    </aside>

        <main class="main-content">
            <div class="header">
                <button class="logout-btn">Log out</button>
            </div>

            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    document.querySelector(".logout-btn").addEventListener("click", function() {
                        window.location.href = "../admin/logout.php";
                    });
                });
            </script>
</body>

</html>

<?php $conn->close(); ?>