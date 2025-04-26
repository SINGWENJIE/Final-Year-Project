<?php
session_start();

if (!isset($_SESSION['admin_email']) || $_SESSION['admin_role'] !== "Admin") {
    header("Location: ../admin/adminlogin.php");
    exit();
}

$role = $_SESSION['admin_role'];
$admin_id = $_SESSION['admin_name'];

$conn = new mysqli("localhost", "root", "", "gogo_supermarket");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$order_query = "SELECT COUNT(order_id) AS total_orders FROM `orders`";
$order_result = $conn->query($order_query);
$total_orders = $order_result->fetch_assoc()['total_orders'];

$customer_query = "SELECT COUNT(user_id) AS total_user FROM users";
$customer_result = $conn->query($customer_query);
$total_customers = $customer_result->fetch_assoc()['total_user'];

$payment_query = "SELECT COALESCE(SUM(total_amount), 0) AS total_amount FROM `orders`";
$payment_result = $conn->query($payment_query);
$total_payments = 0;
if ($payment_result && $row = $payment_result->fetch_assoc()) {
    $total_payments = $row['total_amount'];
}



$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/dashboard_Style.css">
    <link rel="stylesheet" href="../assets/css/Global_style.css">
</head>

<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="logo">
                <img src="../assets/images/logoname.png" alt="Logo">
            </div>

            <div class="profile">
                <img src="../assets/images/admin.png" alt="Admin foto">
                <p><span class="admin_role"><?php echo $role; ?></span></p>
            </div>
            <nav>
                <ul>
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
            <h1>Welcome, <?php echo $admin_id ?>!</h1>
            <div class="dashboard-cards">
                <div class="card">
                    <h3>Total Orders</h3>
                    <p><?php echo $total_orders; ?></p>
                </div>
                <div class="card">
                    <h3>Total Customers</h3>
                    <p><?php echo $total_customers; ?></p>
                </div>
                <div class="card">
                    <h3>Total Payments</h3>
                    <p>RM <?php echo number_format($total_payments, 2); ?></p>

                </div>
            </div>

            <div class="recent-orders">
                <?php
                $conn = new mysqli("localhost", "root", "", "gogo_supermarket");
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                $sql = "SELECT o.order_id, u.user_name, SUM(oi.quantity) AS prod_quantity, o.total_amount, o.order_status 
                        FROM `orders` AS o
                        JOIN order_item AS oi ON o.order_id = oi.order_id 
                        JOIN users AS u ON o.user_id = u.user_id
                        GROUP BY o.order_id, u.user_name, o.total_amount, o.order_status
                        ORDER BY o.order_id DESC 
                        LIMIT 4";

                $result = $conn->query($sql);
                ?>
                <table>
                    <thead>
                        <tr>
                            <th>Customer Name</th>
                            <th>Quantity</th>
                            <th>Total Price</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>
                                    <td>{$row['user_name']}</td>
                                    <td>{$row['prod_quantity']}</td>
                                    <td>RM{$row['total_amount']}</td>
                                    <td>{$row['order_status']}</td>
                                  </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4'>No recent orders</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </main>
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