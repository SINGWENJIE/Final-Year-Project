<?php
session_start();
include '../db_connection.php';

$recordsPerPage = 8;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$startFrom = ($page - 1) * $recordsPerPage;

$searchQuery = "";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $searchQuery = " WHERE user_name LIKE '%$search%' ";
}

$result = $conn->query("SELECT COUNT(*) AS total FROM users $searchQuery");
$totalRecords = $result->fetch_assoc()['total'];
$totalPages = ceil($totalRecords / $recordsPerPage);

$customers = $conn->query("SELECT * FROM users $searchQuery LIMIT $startFrom, $recordsPerPage");
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $name = $_POST['user_name'];
    $email = $_POST['email'];
    $phone = $_POST['user_phone_num'];

    if ($id > 0) {
        $checkEmail = $conn->query("SELECT * FROM users WHERE email = '$email' AND user_id != $id");
        if ($checkEmail->num_rows > 0) {
            echo "<script>alert('This email already exists, please enter again.'); window.history.back();</script>";
            exit();
        }
        $sql = "UPDATE users SET user_name='$name', email='$email', user_phone_num='$phone' WHERE user_id=$id";
    } else {
        $checkEmail = $conn->query("SELECT * FROM users WHERE email = '$email'");
        if ($checkEmail->num_rows > 0) {
            echo "<script>alert('This email already exists, please enter again.'); window.history.back();</script>";
            exit();
        }

        $sql = "INSERT INTO users (user_name, email, user_phone_num) VALUES ('$name', '$email', '$phone')";
    }

    if ($conn->query($sql) === TRUE) {
        header("Location: Siderbar_CustomerList.php");
        exit();
    } else {
        echo "<script>alert('Something went wrong. Please try again.'); window.history.back();</script>";
        exit();
    }
}



if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM users WHERE user_id = $id");
    header("Location: Siderbar_CustomerList.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer List</title>
    <link rel="stylesheet" href="../assets/css/customerList.css">
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
                <button class="logout-btn" onclick="window.location.href='../admin/logout.php'">Log out</button>
            </div>

            <h2>Customer List</h2>

            <div class="search-container">
                <form method="GET">
                    <input type="text" name="search" placeholder="Search by name " value="<?= isset($_GET['search']) ? $_GET['search'] : '' ?>">
                    <button type="submit">
                        <img src="../assets/images/search.png" alt="Search" width="20" height="20">
                    </button>
                </form>
            </div>

            <div id="customerForm" style="display:none;">
                <h3 id="formTitle">Edit User</h3>
                <form method="POST">
                    <input type="hidden" id="customerId" name="id">
                    <label>Name:</label> <input type="text" id="user_name" name="user_name" required><br>
                    <label>Email:</label> <input type="email" id="email" name="email" value="" readonly><br> <!-- Readonly email -->
                    <label>Phone:</label> <input type="text" id="user_phone_num" name="user_phone_num" required><br>

                    <button type="submit">Save</button>
                    <button type="button" onclick="hideForm()">Cancel</button>
                </form>
            </div>


            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $customers->fetch_assoc()) { ?>
                        <tr>
                            <td><?= $row['user_id']; ?></td>
                            <td><?= $row['user_name']; ?></td>
                            <td><?= $row['email']; ?></td>
                            <td><?= $row['user_phone_num']; ?></td>
                            <td>
                                <a onclick="editCustomer(<?= $row['user_id']; ?>, '<?= $row['user_name']; ?>', '<?= $row['email']; ?>', '<?= $row['user_phone_num']; ?>')">
                                    <img src="../assets/images/edit.png" alt="Edit" class="icon-btn">
                                </a>
                                <a href="?delete=<?= $row['user_id']; ?>" onclick="return confirm('Are you sure?')">
                                    <img src="../assets/images/delete.png" alt="Delete" class="icon-btn">
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>

            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1; ?>">
                        <
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="?page=<?= $i; ?>" class="<?= ($i == $page) ? 'active' : ''; ?>"><?= $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= $page + 1; ?>">></a>
                <?php endif; ?>
            </div>

            <script>
                function editCustomer(id, name, email, phone) {
                    document.getElementById("customerForm").style.display = "block";
                    document.getElementById("formTitle").innerText = "Edit User";
                    document.getElementById("customerId").value = id;
                    document.getElementById("user_name").value = name;
                    document.getElementById("email").value = email; // Display email but make it readonly
                    document.getElementById("user_phone_num").value = phone;
                }


                function hideForm() {
                    document.getElementById("customerForm").style.display = "none";
                }
            </script>

</body>

</html>

<?php $conn->close(); ?>