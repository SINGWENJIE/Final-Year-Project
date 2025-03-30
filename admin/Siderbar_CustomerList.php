<?php
session_start();
include '../db_connection.php';

$recordsPerPage = 8;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$startFrom = ($page - 1) * $recordsPerPage;

$searchQuery = "";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $searchQuery = " WHERE name LIKE '%$search%' ";
}

$result = $conn->query("SELECT COUNT(*) AS total FROM customers $searchQuery");
$totalRecords = $result->fetch_assoc()['total'];
$totalPages = ceil($totalRecords / $recordsPerPage);

$customers = $conn->query("SELECT * FROM customers $searchQuery LIMIT $startFrom, $recordsPerPage");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    if ($id > 0) 
    {
        $sql = "UPDATE customers SET name='$name', email='$email', phone='$phone' WHERE id=$id";
    } else 
    {
        $sql = "INSERT INTO customers (name, email, phone) VALUES ('$name', '$email', '$phone')";
    }

    if ($conn->query($sql) === TRUE) 
    {
        header("Location: Siderbar_CustomerList.php");
        exit();
    } else {
        die("Error: " . $conn->error);
    }
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM customers WHERE id = $id");
    header("Location: Siderbar_CustomerList.php");
    exit();
}
?>

<html lang="en">
<head>
    <title>Customer List</title>
    <link rel="stylesheet" href="../assets/css/customerList.css">
</head>
<body>
<div class="dashboard-container">
    <aside class="sidebar">
        <div class="logo">
            <img src="../assets/images/logoname.png" alt="Logo">
        </div>
        <div class="profile">
            <a href="<?= ($_SESSION['role'] === 'Super Admin') ? 'superadmin_dashboard.php' : 'admin_dashboard.php'; ?>">
            <img src="<?= ($_SESSION['role'] === 'Super Admin') ? '../assets/images/superadmin_photo.png' : '../assets/images/admin.png'; ?>">
            <p><span class="role"><?= $_SESSION['role']; ?></span></p>
            </a>
        </div>

        <nav>
        <ul>
        <?php if ($_SESSION['role'] === 'Super Admin') : ?>
        <li><a href="Siderbar_Admin_management.php"><img src="../assets/images/admin_photo.png" alt=""> Admin Management</a></li>
        <?php endif; ?>
    
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
        <h2>Customer List</h2>
        
<div class="search-container">
    <form method="GET">
        <input type="text" name="search" placeholder="Search by name " value="<?= isset($_GET['search']) ? $_GET['search'] : '' ?>">
        <button type="submit">
        <img src="../assets/images/search.png" alt="Search" width="20" height="20">
    </button>
    </form>
</div>

<button class="add-btn" onclick="showForm()">+ Add New Customer</button>

<div id="customerForm" style="display:none;">
    <h3 id="formTitle">Add New Customer</h3>
    <form method="POST">
        <input type="hidden" id="customerId" name="id">
        <label>Name:</label> <input type="text" id="name" name="name" required><br>
        <label>Email:</label> <input type="email" id="email" name="email" required><br>
        <label>Phone:</label> <input type="text" id="phone" name="phone" required><br>
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
                <td><?= $row['id']; ?></td>
                <td><?= $row['name']; ?></td>
                <td><?= $row['email']; ?></td>
                <td><?= $row['phone']; ?></td>
                <td>
                    <a onclick="editCustomer(<?= $row['id']; ?>, '<?= $row['name']; ?>', '<?= $row['email']; ?>', '<?= $row['phone']; ?>')">
                        <img src="../assets/images/edit.png" alt="Edit">
                    </a>
                    <a href="?delete=<?= $row['id']; ?>" onclick="return confirm('Are you sure?')">
                        <img src="../assets/images/delete.png" alt="Delete">
                    </a>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>

<div class="pagination">
    <?php if ($page > 1): ?>
        <a href="?page=<?= $page - 1; ?>"><</a>
    <?php endif; ?>

    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="?page=<?= $i; ?>" class="<?= ($i == $page) ? 'active' : ''; ?>"><?= $i; ?></a>
    <?php endfor; ?>

    <?php if ($page < $totalPages): ?>
        <a href="?page=<?= $page + 1; ?>">></a>
    <?php endif; ?>
</div>

<script>
    function showForm() {
        document.getElementById("customerForm").style.display = "block";
        document.getElementById("formTitle").innerText = "Add New Customer";
        document.getElementById("customerId").value = "";
        document.getElementById("name").value = "";
        document.getElementById("email").value = "";
        document.getElementById("phone").value = "";
    }

    function editCustomer(id, name, email, phone) {
        document.getElementById("customerForm").style.display = "block";
        document.getElementById("formTitle").innerText = "Edit Customer";
        document.getElementById("customerId").value = id;
        document.getElementById("name").value = name;
        document.getElementById("email").value = email;
        document.getElementById("phone").value = phone;
    }

    function hideForm() {
        document.getElementById("customerForm").style.display = "none";
    }
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