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
                <img src="../assets/images/superadmin_photo.png" alt="Superadmin foto">
                <p><span class="role">Superadmin</span></p>
            </div>
            
            <nav>
                <ul>
                    <li><a href="#"><img src="../assets/images/admin_photo.png" alt="">Admin Management</a></li>
                    <li><a href="#"><img src="../assets/images/product.png"  alt="">Category & Product</a></li>
                    <li><a href="#"><img src="../assets/images/customer_list.png" alt="">Customer List</a></li>
                    <li><a href="#"><img src="../assets/images/vieworder.png" alt="">View Orders</a></li>
                    <li><a href="#"><img src="../assets/images/delivery.png"  alt="">Delivery</a></li>
                    <li><a href="#"><img src="../assets/images/report.png" alt="">Reports</a></li>
                </ul>
            </nav>
        </aside>
        <main class="main-content">
            <div class="header">
                <button class="logout-btn">Log out</button>    
            </div>
        </main>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelector(".logout-btn").addEventListener("click", function() {
                window.location.href = "logout.php"; 
            });
        });
    </script>
</body>
</html>
