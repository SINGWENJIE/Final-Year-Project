<!DOCTYPE html>
<html lang="en">

<head>
    <title>GOGO | Shop Conveniently</title>
    <link rel="icon" type="image" href="../image/GOGO.png">
    <link rel="stylesheet" href="../user_assets/css/HomePage.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&icon_names=search" />
</head>

<body>
<?php include 'header.php'; ?>

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

    <!-- Promotion Boxes Section -->
    <div class="promo-section">
        <div class="promo-grid">
          <div class="promo-box">
            <img src="../image/promo1.png" alt="" />
            <h3>This Weeks Offer</h3>
            <p>View our latest promotion.</p>
            <a href="catalogue.html">
                <button>View catalogue</button>
            </a>
          </div>

          <div class="promo-box">
            <img src="../image/promo2.png" alt="" />
            <h3>Promotions</h3>
            <p>Check out our latest promotions here.</p>
            <a href="catalogue.html">
                <button>View Now</button>
            </a>
          </div>

          <div class="promo-box">
            <img src="../image/p9.jpg" alt="" />
            <h3>Our Products</h3>
            <p>Check out our product range.</p>
            <a href="Product_List.php">
                <button>Shop Now</button>
            </a>
          </div>

          <div class="promo-box">
            <img src="../image/promo4.png" alt="" />
            <h3>Gifting</h3>
            <p>Discover gifting options and ideas.</p>
            <a href="catalogue.html">
                <button>Learn More</button>
            </a>
          </div> 
        </div>
      </div>
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