<?php
session_start();

if (!isset($_SESSION['admin_email']) || $_SESSION['admin_role'] !== "Admin") {
    header("Location: adminlogin.php");
    exit();
}
$role = $_SESSION['admin_role'];
$admin_id = $_SESSION['admin_name'];
$admin_email = $_SESSION['admin_email'];

$conn = new mysqli("localhost", "root", "", "gogo_supermarket");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $admin_name = $_POST['admin_name'];
    $admin_password = $_POST['admin_password'];

    if (!empty($admin_password)) {
        $update_query = "UPDATE admin SET admin_name = '$admin_name', admin_password = '$admin_password' WHERE admin_email = '$admin_email'";
    } else {
        $update_query = "UPDATE admin SET admin_name = '$admin_name' WHERE admin_email = '$admin_email'";
    }

    if ($conn->query($update_query)) {
        $_SESSION['admin_name'] = $admin_name;
        header("Location: admin_dashboard.php?status=success");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
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
            <button class="edit-profile-btn" title="Edit Profile">
                        <img src="../assets/images/admin_profile.png" alt="admin_profile">
                    </button>
                <img src="../assets/images/admin.png" alt="Admin foto">
                <p>
                    <span class="admin_role"><?php echo $role; ?></span>
                    

                </p>
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

    <!-- Profile Modal -->
    <div id="profileModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Edit Profile</h2>
            <form method="post">
                <label for="admin_name">Admin Name:</label>
                <input type="text" id="admin_name" name="admin_name" value="<?php echo $admin_id; ?>" required>
                <label for="admin_email">Admin Email:</label>
                <input type="email" id="admin_email" name="admin_email" value="<?php echo $admin_email; ?>" readonly>

                <label for="admin_password">New Password:</label>
                <input type="password" id="admin_password" name="admin_password">

                <button type="submit" class="save-btn">Save Changes</button>
            </form>
        </div>
    </div>

    <script>
        document.querySelector(".logout-btn").onclick = function() {
            location.href = "../admin/logout.php";
        };

        document.querySelector(".edit-profile-btn").onclick = function() {
            document.getElementById("profileModal").style.display = "block";
        };

        document.querySelector(".close").onclick = function() {
            document.getElementById("profileModal").style.display = "none";
        };
    </script>
</body>

</html>