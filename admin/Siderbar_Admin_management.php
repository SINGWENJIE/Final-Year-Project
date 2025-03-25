<?php

session_start();
include 'db_connection.php';

if (isset($_SESSION['role'])) {
    $role = $_SESSION['role'];
} 


if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM admin WHERE id = $id");
    header("Location: ../admin/Siderbar_Admin_management.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $admin_id = $_POST['admin_id'];
    $email = $_POST['email'];
    $password = !empty($_POST['password']) ? $_POST['password'] : "";

    
    if ($id > 0) {
        if (!empty($password)) {
            $sql = "UPDATE admin SET admin_id='$admin_id', email='$email', password='$password' WHERE id=$id";
        } else {
            $sql = "UPDATE admin SET admin_id='$admin_id', email='$email' WHERE id=$id";
        }
    } else {
        if (!empty($password)) {
            $sql = "INSERT INTO admin (admin_id, email, password, role) VALUES ('$admin_id', '$email', '$password', 'admin')";
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
                <li><a href="Siderbar_Category_Product.php"><img src="../assets/images/product.png"  alt=""> Category & Product</a></li>
                <li><a href="Siderbar_CustomerList.php"><img src="../assets/images/customer_list.png" alt=""> Customer List</a></li>
                <li><a href="Siderbar_ViewOrders.php"><img src="../assets/images/vieworder.png" alt=""> View Orders</a></li>
                <li><a href="Siderbar_Delivery.php"><img src="../assets/images/delivery.png"  alt=""> Delivery</a></li>
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
                    <label>Admin ID:</label> <input type="text" id="admin_id" name="admin_id" required><br>
                    <label>Email:</label> <input type="email" id="email" name="email" required><br>
                    <label>Password:</label> <input type="password" id="password" name="password"><br>
                    <button type="submit">Save</button>
                    <button type="button" onclick="hideForm()">Cancel</button>
                </form>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Admin ID</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $admins->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $row['admin_id']; ?></td>
                            <td><?php echo $row['email']; ?></td>
                            <td>
                                <a href="#" onclick="editAdmin(<?php echo $row['id']; ?>, '<?php echo $row['admin_id']; ?>', '<?php echo $row['email']; ?>')">
                                    <img src="../assets/images/edit.png" alt="Edit" class="icon-btn">
                                </a>
                                <a href="?delete=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure?')">
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
        document.getElementById("admin_id").value = "";
        document.getElementById("email").value = "";
        document.getElementById("password").value = "";
    }

    function editAdmin(id, admin_id, email) {
        document.getElementById("adminForm").style.display = "block";
        document.getElementById("formTitle").innerText = "Edit Admin";
        document.getElementById("adminId").value = id;
        document.getElementById("admin_id").value = admin_id;
        document.getElementById("email").value = email;
        document.getElementById("password").value = "";
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

