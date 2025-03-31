<?php

session_start();
include '../db_connection.php';

if (isset($_SESSION['admin_role'])) {
    $role = $_SESSION['admin_role'];
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM admin WHERE admin_id = $id");
    header("Location: ../admin/Siderbar_Admin_management.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = isset($_POST['admin_id']) ? intval($_POST['admin_id']) : 0;
    $admin_name = $_POST['admin_name'];
    $email = $_POST['admin_email'];
    $password = !empty($_POST['admin_password']) ? $_POST['admin_password'] : "";


    if ($id > 0) {
        $sql = "UPDATE admin SET admin_name='$admin_name', admin_email='$email' WHERE admin_id=$id";
    } else {
        if (!empty($password)) {
            $sql = "INSERT INTO admin (admin_name, admin_email, admin_password, admin_role) VALUES ('$admin_name', '$email', '$password', 'admin')";
        } else {
            die("Error: Password cannot be empty!");
        }
    }

    if ($conn->query($sql) === TRUE) {
        header("Location: ../admin/Siderbar_Admin_management.php");
        exit();
    } else {
        die("Error: " . $conn->error);
    }
}

$admins = $conn->query("SELECT * FROM admin");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Admin Management</title>
    <link rel="stylesheet" href="../assets/css/Admin_Management.css">
</head>

<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="logo">
                <img src="../assets/images/logoname.png" alt="Logo">
            </div>
            <div class="profile">
                <img src="../assets/images/superadmin_photo.png" alt="Admin Photo">
                <p>
                    <a href="../admin/superadmin_dashboard.php" id="roleDirect">
                        <span class="role"><?php echo $role; ?></span>
                    </a>
                </p>
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

            <div class="container">
                <h2>Admin Management</h2>
                <button onclick="showForm()">+ Add New Admin</button>

                <div id="adminForm" style="display:none;">
                    <h3 id="formTitle">Add New Admin</h3>
                    <form method="POST">
                        <input type="hidden" id="adminId" name="id">
                        <label>Admin ID:</label> <input type="text" id="admin_name" name="admin_name" required><br>
                        <label>Email:</label> <input type="email" id="admin_email" name="admin_email" required><br>
                        
                        <button type="submit">Save</button>
                        <button type="button" onclick="hideForm()">Cancel</button>
                    </form>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Admin Name</th>
                            <th>Email</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $admins->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo $row['admin_name']; ?></td>
                                <td><?php echo $row['admin_email']; ?></td>
                                <td>
                                    <a onclick='editAdmin(<?php echo $row["admin_id"]; ?>, <?php echo json_encode($row["admin_name"]); ?>, <?php echo json_encode($row["admin_email"]); ?>)'>
                                        <img src="../assets/images/edit.png" alt="Edit" class="icon-btn">
                                    </a>
                                    <a href="?delete=<?php echo $row['admin_id']; ?>" onclick="return confirm('Are you sure?')">
                                        <img src="../assets/images/delete.png" alt="Delete" class="icon-btn">
                                    </a>
                                </td>

                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        function showForm() {
            document.getElementById("adminForm").style.display = "block";
            document.getElementById("formTitle").innerText = "Add New Admin";
            document.getElementById("adminId").value = "";
            document.getElementById("admin_name").value = "";
            document.getElementById("admin_email").value = "";
            document.getElementById("admin_password").value = "";
        }

        function editAdmin(id, admin_name, admin_email) {
            document.getElementById("adminForm").style.display = "block";
            document.getElementById("formTitle").innerText = "Edit Admin";
            document.getElementById("adminId").value = id;
            document.getElementById("admin_name").value = admin_name;
            document.getElementById("admin_email").value = admin_email;
        }


        function hideForm() {
            document.getElementById("adminForm").style.display = "none";
        }
    </script>
    <script>
        document.getElementById("roleDirect").addEventListener("click", function() {
            window.location.href = "../admin/superadmin_dashboard.php";
        });
    </script>

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