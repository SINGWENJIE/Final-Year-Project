<?php
session_start();
include '../db_connection.php';

//update status
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['order_id']) && isset($_POST['order_status'])) {
    $order_id = $_POST['order_id'];
    $order_status = $_POST['order_status'];

    $update_sql = "UPDATE `orders` SET order_status = '$order_status' WHERE order_id = '$order_id'";
    if ($conn->query($update_sql) === TRUE) {
        echo "<script>alert('Order status updated successfully');</script>";
    } else {
        echo "<script>alert('Failed to update order status');</script>";
    }
}
//Fetch order details for a specific user
function getOrderDetails($conn, $user_id)
{
    $sql_orders = "SELECT * FROM `orders` WHERE user_id = '$user_id' ORDER BY order_date DESC";
    $orders_result = $conn->query($sql_orders);
    $order_details = [];

    while ($order = $orders_result->fetch_assoc()) {
        $order_id = $order['order_id'];
        $order_status = $order['order_status'];
        $order_date = $order['order_date'];

        $items_result = $conn->query("SELECT oi.*, p.prod_name 
                                      FROM order_item oi 
                                      JOIN product p ON oi.prod_id = p.prod_id 
                                      WHERE oi.order_id = '$order_id'");
        $items = [];

        while ($item = $items_result->fetch_assoc()) {
            $product_name = $item['prod_name'];
            $quantity = $item['quantity'];
            $price = $item['unit_price'];  // unit_price is in the order_item table
            $total_price = $price * $quantity;
            $items[] = [
                'product_name' => $product_name,
                'quantity' => $quantity,
                'unit_price' => $price,
                'total_price' => number_format($total_price, 2)
            ];
        }

        $order_details[] = [
            'order_id' => $order_id,
            'order_status' => $order_status,
            'order_date' => $order_date,
            'items' => $items
        ];
    }

    return $order_details;
}

/*get order status*/
function getOrderStatuses($conn)
{
    $sql_enum = "SELECT COLUMN_TYPE 
                 FROM INFORMATION_SCHEMA.COLUMNS 
                 WHERE TABLE_NAME = 'orders' AND COLUMN_NAME = 'order_status'";
    $result = $conn->query($sql_enum);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $enum_values = substr($row['COLUMN_TYPE'], 5, -1);
        $statuses = explode("','", $enum_values);
        foreach ($statuses as &$status) {
            $status = trim($status, "'");
        }
        return $statuses;
    } else {
        return [];
    }
}                           /*get order status*/

/*view order table*/
if (isset($_GET['ajax']) && isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
    $orders = getOrderDetails($conn, $user_id);

    foreach ($orders as $order) {
        echo "<div class='order-detail-container'>";
        echo "<h3>Order ID: {$order['order_id']}</h3>";
        echo "<table class='order-details-table'>";
        echo "<thead><tr><th>Product</th><th>Quantity</th><th>Unit Price</th><th>Status</th><th>Date</th></tr></thead>";
        echo "<tbody>";

        $rowspan = count($order['items']);
        $firstRow = true;
        foreach ($order['items'] as $item) {
            echo "<tr>";

            echo "<td>{$item['product_name']}</td>";

            echo "<td>{$item['quantity']}</td>";

            echo "<td>RM {$item['unit_price']}</td>";

            if ($firstRow) {
                echo "<td rowspan='{$rowspan}'>{$order['order_status']}</td>";
                echo "<td rowspan='{$rowspan}'>{$order['order_date']}</td>";
            }

            echo "</tr>";
            $firstRow = false;
        }
        echo "</tbody>";
        echo "</table>";
        echo "</div>";
    }
    exit;
}
/*view order table*/
$orderStatuses = getOrderStatuses($conn);
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Order</title>
    <link rel="stylesheet" href="../assets/css/ViewOrder.css">
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

            <div class="order-section">
                <h2>Order List</h2>

                <div class="search-container">
                    <form method="GET">
                        <input type="text" name="search" placeholder="Search by name or order id" value="<?= isset($_GET['search']) ? $_GET['search'] : '' ?>">
                        <button type="submit">
                            <img src="../assets/images/search.png" alt="Search" width="20" height="20">
                        </button>
                    </form>
                </div>
                <table cellpadding="10" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>User Name</th>
                            <th>Total Price</th>
                            <th>Order Status</th>
                            <th>Order Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>

                        <?php
                        //search fucntion
                        $search = $_GET['search'] ?? '';
                        $search = trim($search);

                        $sql = "SELECT o.order_id, u.user_name, o.total_amount, o.order_status, o.order_date, o.user_id
                     FROM `orders` o
                     JOIN `users` u ON o.user_id = u.user_id";


                        if ($search !== '') {
                            $search = trim($search);
                            $sql = "SELECT o.order_id, u.user_name, o.total_amount, o.order_status, o.order_date, o.user_id
                                    FROM `orders` o
                                    JOIN `users` u ON o.user_id = u.user_id
                                    WHERE o.order_id LIKE ? OR u.user_name LIKE ?"; //orde id and user name
                            $stmt = $conn->prepare($sql);
                            $searchParam = "%$search%";
                            $stmt->bind_param("ss", $searchParam, $searchParam);
                        } else {
                            $sql = "SELECT o.order_id, u.user_name, o.total_amount, o.order_status, o.order_date, o.user_id
                                    FROM `orders` o
                                    JOIN `users` u ON o.user_id = u.user_id";
                            $stmt = $conn->prepare($sql);
                        }

                        $stmt->execute();
                        $result = $stmt->get_result();
                        //search fucntion

                        //order table
                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . $row['order_id'] . "</td>";
                                echo "<td>" . $row['user_name'] . "</td>";
                                echo "<td>RM " . number_format($row['total_amount'], 2) . "</td>";
                                echo "<td>";
                                echo "<form method='POST' action='' id='statusForm_{$row['order_id']}'>";
                                echo "<input type='hidden' name='order_id' value='" . $row['order_id'] . "'>";
                                echo "<select name='order_status' onchange='submitForm({$row['order_id']})'>";
                                foreach ($orderStatuses as $status) {
                                    $selected = ($status == $row['order_status']) ? "selected" : "";
                                    echo "<option value='$status' $selected>$status</option>";
                                }
                                echo "</select>";
                                echo "</form>";
                                echo "</td>";
                                echo "<td>" . $row['order_date'] . "</td>";
                                echo "<td><a href='javascript:void(0);' class='view-order-btn' onclick='showOrderDetails(" . $row['user_id'] . ")'>View Orders</a></td>";
                                echo "</tr>";
                            }
                        }
                        //order table
                        ?>
                    </tbody>
                </table>
            </div>

            <script>
                function submitForm(orderId) {
                    // Submit the form when the status changes
                    var form = document.getElementById('statusForm_' + orderId);
                    form.submit();
                }


                // view order,ajax
                function showOrderDetails(userId) {
                    var xhr = new XMLHttpRequest();
                    xhr.open('GET', 'Siderbar_ViewOrders.php?user_id=' + userId + '&ajax=true', true);
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState == 4 && xhr.status == 200) {
                            document.getElementById('orderDetails').innerHTML = xhr.responseText;
                            document.getElementById('orderModal').style.display = "block";
                        }
                    };
                    xhr.send();
                }

                function closeModal() {
                    document.getElementById('orderModal').style.display = "none";
                }

                document.addEventListener("DOMContentLoaded", function() {
                    document.querySelector(".logout-btn").addEventListener("click", function() {
                        window.location.href = "../admin/logout.php";
                    });
                });
            </script>

            <div id="orderModal" class="modal" style="display: none;">
                <div class="modal-content">
                    <span class="close" onclick="closeModal()">&times;</span>
                    <h2>Order Details</h2>
                    <div id="orderDetails">
                        <!-- Order details will be loaded here via AJAX -->
                    </div>
                </div>
            </div>

        </main>
    </div>
</body>

</html>

<?php
$conn->close();
?>