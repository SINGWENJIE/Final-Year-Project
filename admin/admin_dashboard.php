<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="dashboard_style.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="logo">
                <img src="../assets/images/logoname.png" alt="Logo">
            </div>
            
            <div class="profile">
                <img src="../assets/images/admin.png" alt="Admin foto">
                <p><span class="role">Admin</span></p>
            </div>
            
            <nav>
                <ul>
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
                window.location.href = "../admin/logout.php"; 
            });
        });
    </script>
</body>
</html>
