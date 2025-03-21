<!DOCTYPE html>
<html lang="en">

<head>
    <title>GOGO | Register Page</title>
    <link rel="icon" type="image" href="image/GOGO.png">
    <link rel="stylesheet" href="Register.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&icon_names=search" />
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>
    <header>
        <div class="header-main">
            <a href="MainPage.html">
                <img src="image/gogoname.png" alt="GOGO Logo">
            </a>
            <form class="search-form">
                <div class="search">
                    <span class="search-icon material-symbols-outlined">search</span>
                    <input class="search-input" type="search" placeholder="Search">
                </div>
            </form>
        </div>

        <nav>
            <ul class="nav-links">
                <li><a href="MainPage.html">Menu</a></li>
                <li><a href="AboutUs.html">About Lotus</a></li>
                <li><a href="CustomerService.html">Customer Service</a></li>
            </ul>
        </nav>
    </header>

    <div class="register">
        <form action="" method="POST">
            <div class="close">
                <a href="MainPage.html">
                    <i class='bx bx-x-circle'></i>
                </a>
            </div>

            <h1>Register</h1>
            <div class="divider"></div>
            <h2>
                <img src="image/logoname(green).png" alt="GOGO Logo">
            </h2>

            <div class="input-box">
                <input type="text" placeholder="Username" required>
                <i class='bx bx-user'></i>
            </div>

            <div class="input-box">
                <input type="email" placeholder="Email" required>
                <i class='bx bx-envelope'></i>
            </div>

            <div class="input-box">
                <input type="password" placeholder="Password" required>
                <i class='bx bx-lock-alt'></i>
            </div>

            <div class="input-box">
                <input type="password" placeholder="Confirm Password" required>
                <i class='bx bx-lock'></i>
            </div>

            <button type="submit" class="btn">Create Account</button>

            <div class="additional-links">
                <p>Already have an account? <a href="Login.php">Login Now</a></p>
            </div>
        </form>
    </div>

    <div class="footer-nav">
        <div class="footer-column">
            <h4>GOGO</h4>
            <ul>
                <li><a href="AboutUs.html">About GOGO</a></li>
                <li><a href="#">Policies</a></li>
                <li><a href="#">Terms & Conditions</a></li>
            </ul>
        </div>

        <div class="footer-column">
            <h4>Support</h4>
            <ul>
                <li><a href="#">FAQs</a></li>
                <li><a href="#">Download App</a></li>
                <li><a href="#">Contact Us</a></li>
            </ul>
        </div>

        <div class="footer-column">
            <h4>Legal</h4>
            <ul>
                <li><a href="#">Privacy Policy</a></li>
                <li><a href="#">Anti-Bribery</a></li>
                <li><a href="#">Loyalty Program</a></li>
            </ul>
        </div>
    </div>

</body>

</html>