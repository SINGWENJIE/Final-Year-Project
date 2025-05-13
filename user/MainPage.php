<?php
session_start();
// db_connection.php
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "gogo_supermarket";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>GOGO | Shop Conveniently</title>
    <link rel="icon" type="image" href="../image/GOGO.png">
    <link rel="stylesheet" href="../user_assets/css/MainPage.css">
    <link rel="stylesheet" href="../user_assets/js/MainPage.js">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&icon_names=search" />
</head>

<body>
<?php include 'header.php'; ?>

    <nav class="category-nav">
        <div class="dropdown">
            <button>Category</button>
            <div class="content">
                <a href="Product_List.php">Bakery & Breakfast</a>
                <a href="Product_List.php">Beauty & Personal Care</a>
                <a href="Product_List.php">Cleaning & Laundry</a>
                <a href="Product_List.php">Drinks</a>
                <a href="Product_List.php">Food</a>
                <a href="Product_List.php">Fruit</a>
                <a href="Product_List.php">Health & Wellness</a>
                <a href="Product_List.php">Ice Cream</a>
                <a href="Product_List.php">Snacks</a>
                <a href="Product_List.php">Vegetables</a>
            </div>
        </div>
    </nav>

    <section class="container">
        <div class="slider-wrapper">
            <div class="slider">
                <img src="../image/Slide-1.png" alt="广告1">
                <img src="../image/Slide-2.jpg" alt="广告2">
                <img src="../image/Slide-3.jpg" alt="广告3">
            </div>
    
            <div class="slider-nav">
                <a class="active"></a>
                <a></a>
                <a></a>
            </div>
        </div>
    </section>

    <div class="products-container">
        <?php
        $categories = ['Bakery&Breakfast', 'Drinks', 'Snacks'];

        foreach ($categories as $categoryName) {
            $stmt = $conn->prepare("SELECT category_id FROM category WHERE category_name = ?");
            $stmt->bind_param("s", $categoryName);
            $stmt->execute();
            $result = $stmt->get_result();
            $category = $result->fetch_assoc();

            if ($category) {
                $category_id = $category['category_id'];

                $stmt2 = $conn->prepare("SELECT * FROM product WHERE category_id = ? LIMIT 8");
                $stmt2->bind_param("i", $category_id);
                $stmt2->execute();
                $products = $stmt2->get_result();

                echo "<div>";

                // 分类标题 + More 按钮
                echo "<div class='section-header'>";
                echo "<div class='section-title'>" . htmlspecialchars($categoryName) . "</div>";
                echo "<a href='Product_List.php' class='more-btn'>More</a>";  // ✅ 固定跳转
                echo "</div>";                

                echo "<div class='product-row'>";
                while ($row = $products->fetch_assoc()) {
                    echo '<div class="product-card">';
                    echo '<a href="product_details.php?id=' . $row['prod_id'] . '">';
                    echo '<div class="product-image">';
                    echo '<img src="/Final-Year-Project/assets/uploads/' . htmlspecialchars($row['prod_image']) . '" alt="' . htmlspecialchars($row['prod_name']) . '">';
                    echo '</div>';
                    echo '<div class="product-info">';
                    echo '<h3>' . htmlspecialchars($row['prod_name']) . '</h3>';
                    echo '<p class="price">RM' . number_format($row['prod_price'], 2) . '</p>';
                    echo '</div>';
                    echo '</a>';
                    echo '</div>';
                }
                echo "</div></div>";
                
            }
        }
        ?>
    </div>

    <?php include 'footer.php'; ?>
</body>

<script>
    // 修复后的核心逻辑
    let currentIndex = 0;
    const slider = document.querySelector('.slider');
    const dots = document.querySelectorAll('.slider-nav a');
    let autoSlideInterval;
    
    // 修正切换计算
    function updateSlide() {
    const translateValue = -currentIndex * (100); // 精确到小数点后两位
    slider.style.transform = `translateX(${translateValue}%)`;
    // 强制硬件加速优化
    slider.style.willChange = 'transform'; 
    }
    
    function nextSlide() {
        currentIndex = (currentIndex + 1) % 3;
        updateSlide();
    }
    
    // 强化点击事件处理
    dots.forEach((dot, index) => {
        dot.addEventListener('click', (e) => {
            e.preventDefault();
            clearInterval(autoSlideInterval);
            currentIndex = index;
            updateSlide();
            autoSlideInterval = setInterval(nextSlide, 5000);
        });
    });
    
    // 可靠自动播放控制
    function startAutoSlide() {
        autoSlideInterval = setInterval(() => {
            currentIndex = (currentIndex + 1) % 3;
            updateSlide();
        }, 5000);
    }
    
    // 初始化
    startAutoSlide();
    
    // 优化窗口焦点管理
    window.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            clearInterval(autoSlideInterval);
        } else {
            startAutoSlide();
        }
    });
</script>

</html>
