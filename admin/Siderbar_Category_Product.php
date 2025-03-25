<?php
session_start();
include 'db_connection.php';

if (isset($_POST['add_category'])) {
    $category_name = $_POST['category_name'];
    $conn->query("INSERT INTO category (name) VALUES ('$category_name')");
    header("Location: ../admin/Siderbar_Category_Product.php");
    exit();
}

if (isset($_POST['add_product'])) {
    $category_id = $_POST['category_id'];
    $product_name = $_POST['product_name'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];  
    $image = $_FILES['image']['name'];

    if (!empty($image)) {
        $target = "../assets/uploads/" . basename($image);
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            $sql = "INSERT INTO product (category_id, name, price, stock, image) 
                    VALUES ('$category_id', '$product_name', '$price', '$stock', '$image')";
            if ($conn->query($sql) === TRUE) {
                echo "<script>alert('Product added successfully!'); window.location.href='Siderbar_Category_Product.php?category_id=$category_id';</script>";
                exit();
            } else {
                echo "<script>alert('Error: " . $conn->error . "');</script>";
            }
        } else {
            echo "<script>alert('Image upload failed!');</script>";
        }
    } else {
        echo "<script>alert('Please upload an image!');</script>";
    }

}
if (isset($_GET['delete_product']) && isset($_GET['category_id'])) {
    $product_id = $_GET['delete_product'];
    $category_id = $_GET['category_id'];

    $sql = "DELETE FROM product WHERE id='$product_id'";
    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Product deleted successfully!'); window.location.href='Siderbar_Category_Product.php?category_id=$category_id';</script>";
        exit();
    } else {
        echo "<script>alert('Error: " . $conn->error . "');</script>";
    }
}

if (isset($_GET['delete_category'])) {
    $category_id = $_GET['delete_category'];

    $conn->query("DELETE FROM product WHERE category_id='$category_id'");

    $sql = "DELETE FROM category WHERE id='$category_id'";
    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Category deleted successfully!'); window.location.href='Siderbar_Category_Product.php';</script>";
    } else {
        echo "<script>alert('Error: " . $conn->error . "');</script>";
    }
    exit();
}



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Category & Product</title>
    <link rel="stylesheet" href="../assets/css/Category&product.css">
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
            <button class="logout-btn" onclick="window.location.href='../admin/logout.php'">Log out</button>
        </div>

        <h2>Manage Categories</h2>
        <form method="POST">
            <input type="text" name="category_name" placeholder="Category Name" required>
            <button type="submit" name="add_category">Add Category</button>
        </form>
        <div class="container">
        <?php
        $categories = $conn->query("SELECT * FROM category");
        while ($row = $categories->fetch_assoc()) { ?>
        <div class="card">
            <p><?= $row['name'] ?></p>
            <button onclick="window.location.href='../admin/Siderbar_Category_Product.php?category_id=<?= $row['id'] ?>'">Manage Products</button>
            <button onclick="confirmDeleteCategory(<?= $row['id'] ?>)">Delete Category</button> 
        </div>
    <?php } ?>
</div>



<?php 
if (isset($_GET['category_id'])) { 
    $category_id = $_GET['category_id'];

    $category_query = $conn->query("SELECT name FROM category WHERE id='$category_id'");
    
    if ($category_query && $category_query->num_rows > 0) {
        $category_data = $category_query->fetch_assoc();
        $category_name = $category_data['name'];
    } else {
        $category_name = "Unknown Category";
    }

    $products = $conn->query("SELECT * FROM product WHERE category_id='$category_id'");
} else {
    $category_name = "No Category Selected"; 
    $products = false; 
}
?>

    <h2>Manage Products of <?php echo $category_name; ?></h2>

    <form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="category_id" value="<?= $category_id ?? '' ?>">
    <input type="text" name="product_name" placeholder="Product Name" required>
    <input type="number" name="price" placeholder="Price" required>
    <input type="number" name="stock" placeholder="Stock" required>  
    <input type="file" name="image" required>
    <button type="submit" name="add_product">Add Product</button>
</form>

<div class="container">
    <?php 
    if ($products && $products->num_rows > 0) {
        while ($row = $products->fetch_assoc()) { ?>
            <div class="card">
                <p><?= $row['name'] ?></p>
                <button onclick="confirmDelete(<?= $row['id'] ?>, <?= $category_id ?>)">Delete</button>
            </div>
    <?php 
        }
    } else { 
        echo "<p>No products available for this category.</p>";
    } 
    ?>
</div>

<script>
function confirmDelete(productId, categoryId) {
    if (confirm("Are you sure you want to delete this product?")) {
        window.location.href = "Siderbar_Category_Product.php?delete_product=" + productId + "&category_id=" + categoryId;
    }
}
</script>

<script>
function confirmDeleteCategory(categoryId) {
    if (confirm("Are you sure you want to delete this category? All products in this category will be deleted!")) {
        window.location.href = "Siderbar_Category_Product.php?delete_category=" + categoryId;
    }
}
</script>

</body>
</html>

<?php $conn->close(); ?>
