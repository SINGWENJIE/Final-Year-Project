<?php
session_start();
include '../db_connection.php';

if (isset($_SESSION['admin_role'])) {
    $role = $_SESSION['admin_role'];
}

$reportData = null;

if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
    $start = $_GET['start_date'];
    $end = $_GET['end_date'];

    $sql = "SELECT 
                SUM(total_amount) AS total_revenue,
                COUNT(*) AS total_orders,
                AVG(total_amount) AS average_order
            FROM `orders`
            WHERE order_date BETWEEN '$start' AND '$end'";

    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $reportData = $result->fetch_assoc();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report</title>
    <link rel="stylesheet" href="../assets/css/report.css">
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
                <button class="logout-btn">Log out</button>
            </div>

            <div class="report-container">
                <h2>Sales Report</h2>
                <form method="get" class="report-form">
                    <label for="start_date">From:</label>
                    <input type="date" name="start_date" required>
                    <label for="end_date">To:</label>
                    <input type="date" name="end_date" required>
                    <button type="submit">Generate</button>
                </form>

                <?php if ($reportData): ?>
                    <div class="report-results">
                        <h3>Report from <?= htmlspecialchars($start) ?> to <?= htmlspecialchars($end) ?>:</h3>
                        <ul>
                            <li><strong>Total Revenue:</strong> RM <?= number_format($reportData['total_revenue'] ?? 0, 2) ?></li>
                            <li><strong>Total Orders:</strong> <?= $reportData['total_orders'] ?? 0 ?></li>
                            <li><strong>Average Order:</strong> RM <?= number_format($reportData['average_order'] ?? 0, 2) ?></li>

                        </ul>
                    </div>
                <?php elseif (isset($_GET['start_date'])): ?>
                    <p>No data found for this period.</p>
                <?php endif; ?>
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