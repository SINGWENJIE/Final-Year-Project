<?php
session_start();
include '../db_connection.php';

$recordsPerPage = 11;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$startFrom = ($page - 1) * $recordsPerPage;

if (isset($_SESSION['admin_role'])) {
    $role = $_SESSION['admin_role'];
}

if (isset($_POST['order_id'], $_POST['new_status'])) {
    $order_id = (int)$_POST['order_id'];
    $new_status = $_POST['new_status'];

    $allowed = ['processing', 'out_for_delivery', 'delivered'];
    if (in_array($new_status, $allowed)) {
        $stmt = $conn->prepare("UPDATE delivery SET delivery_status = ? WHERE order_id = ?");
        $stmt->bind_param("si", $new_status, $order_id);
        $stmt->execute();
        $stmt->close();
    }
}
$countSql = "SELECT COUNT(*) AS total FROM delivery";
$countResult = $conn->query($countSql);
$countRow = $countResult->fetch_assoc();
$totalRecords = $countRow['total'];
$totalPages = ceil($totalRecords / $recordsPerPage);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
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
                        <li><a href="Siderbar_Admin_management.php"><img src="../assets/images/admin_photo.png"> Admin Management</a></li>
                    <?php endif; ?>
                    <li><a href="Siderbar_Category.php"><img src="../assets/images/category.png"> Category</a></li>
                    <li><a href="Siderbar_Product.php"><img src="../assets/images/product.png"> Product</a></li>
                    <li><a href="Siderbar_CustomerList.php"><img src="../assets/images/customer_list.png"> Customer List</a></li>
                    <li><a href="Siderbar_ViewOrders.php"><img src="../assets/images/vieworder.png"> View Orders</a></li>
                    <li><a href="Siderbar_Delivery.php" class="active"><img src="../assets/images/delivery.png"> Delivery</a></li>
                    <li><a href="Siderbar_Reports.php"><img src="../assets/images/report.png"> Reports</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <div class="header">
                <button class="logout-btn">Log out</button>
            </div>

            <script>
                document.querySelector(".logout-btn").addEventListener("click", function() {
                    window.location.href = "../admin/logout.php";
                });
            </script>

            <div class="delivery-table-container">
                <h2>Delivery Management</h2>
                <table class="delivery-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Username</th>
                            <th>Current Status</th>
                            <th>Update Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT d.order_id, d.delivery_status, u.user_name
    FROM delivery d
    JOIN orders o ON d.order_id = o.order_id
    JOIN users u ON o.user_id = u.user_id
    ORDER BY d.order_id DESC
    LIMIT $startFrom, $recordsPerPage";

                        $result = $conn->query($sql);

                        while ($row = $result->fetch_assoc()):
                        ?>
                            <tr>
                                <td><?php echo $row['order_id']; ?></td>
                                <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                                <td><?php echo ucfirst(str_replace('_', ' ', $row['delivery_status'])); ?></td>
                                <td>
                                    <form method="post">
                                        <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                                        <select name="new_status" onchange="this.form.submit()">
                                            <option value="processing" <?php if ($row['delivery_status'] == 'processing') echo 'selected'; ?>>Processing</option>
                                            <option value="out_for_delivery" <?php if ($row['delivery_status'] == 'out_for_delivery') echo 'selected'; ?>>Out for Delivery</option>
                                            <option value="delivered" <?php if ($row['delivery_status'] == 'delivered') echo 'selected'; ?>>Delivered</option>
                                        </select>
                                    </form>

                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
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
            </div>
        </main>
    </div>
</body>

</html>

<?php $conn->close(); ?>