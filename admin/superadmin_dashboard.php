<?php
session_start();

if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== "Super Admin") {
    header("Location: adminlogin.php");
    exit();
}

$role = $_SESSION['role']; 

$conn = new mysqli("localhost", "root", "", "gogo_supermarket");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$admin_query = "SELECT COUNT(id) AS total_admins FROM admin";
$admin_result = $conn->query($admin_query);
$total_admins = $admin_result->fetch_assoc()['total_admins'];

$order_query = "SELECT COUNT(id) AS total_orders FROM orders";
$order_result = $conn->query($order_query);
$total_orders = $order_result->fetch_assoc()['total_orders'];

$customer_query = "SELECT COUNT(id) AS total_customers FROM customers";
$customer_result = $conn->query($customer_query);
$total_customers = $customer_result->fetch_assoc()['total_customers'];

$payment_query = "SELECT SUM(payment) AS total_payments FROM orders";
$payment_result = $conn->query($payment_query);
$total_payments = $payment_result->fetch_assoc()['total_payments'];

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Superadmin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/dashboard_Style.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo">
                <img src="../assets/images/logoname.png" alt="Logo">
            </div>
            
            <div class="profile">
                <img src="../assets/images/superadmin_photo.png" alt="Superadmin Photo">
                <p><span class="role"><?php echo $role; ?></span></p> 
                
            </div>
            
            <nav>
                <ul>
                    <li><a href="Siderbar_Admin_management.php"><img src="../assets/images/admin_photo.png" alt=""> Admin Management</a></li>
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
            <h1>Welcome, <?php echo $role; ?>!</h1>

            <div class="dashboard-cards"> 
                <div class="card">
                    <h3>Total Admins</h3>
                    <p><?php echo $total_admins; ?></p>
                </div>
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
                    <p>$<?php echo number_format($total_payments, 2); ?></p>
                </div>
            </div>

            <div class="recent-orders">
                <?php
                $conn = new mysqli("localhost", "root", "", "gogo_supermarket");
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                $sql = "SELECT id, customer_name, order_quantity, total_price, status FROM orders ORDER BY id DESC LIMIT 4";
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
                                        <td>{$row['customer_name']}</td>
                                        <td>{$row['order_quantity']}</td>
                                        <td>\${$row['total_price']}</td>
                                        <td>{$row['status']}</td>
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
