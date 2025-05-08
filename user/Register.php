<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gogo_supermarket";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->beginTransaction();

        $user_name = $_POST['user_name'];
        $email = $_POST['email'];
        $user_password = password_hash($_POST['user_password'], PASSWORD_DEFAULT);
        $user_phone_num = $_POST['user_phone_num'];
        $status = 'active';
        $user_created_at = date('Y-m-d H:i:s');

        $stmt = $pdo->prepare("INSERT INTO users (user_name, email, user_password, user_phone_num, user_created_at, status) 
                               VALUES (:user_name, :email, :user_password, :user_phone_num, :user_created_at, :status)");
        $stmt->execute([
            ':user_name' => $user_name,
            ':email' => $email,
            ':user_password' => $user_password,
            ':user_phone_num' => $user_phone_num,
            ':user_created_at' => $user_created_at,
            ':status' => $status
        ]);

        $user_id = $pdo->lastInsertId();

        // Check if the user already has an address
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM address WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $user_id]);
        $user_has_address = $stmt->fetchColumn() > 0;
        $is_default = $user_has_address ? 0 : 1;

        if (isset($_POST['recipient_name'])) {
            $stmt = $pdo->prepare("INSERT INTO address (user_id, recipient_name, street_address, city, state, postal_code, is_default, phone_number, note) 
                                  VALUES (:user_id, :recipient_name, :street_address, :city, :state, :postal_code, :is_default, :phone_number, :note)");
            $stmt->execute([
                ':user_id' => $user_id,
                ':recipient_name' => $_POST['recipient_name'],
                ':street_address' => $_POST['street_address'],
                ':city' => $_POST['city'],
                ':state' => $_POST['state'],
                ':postal_code' => $_POST['postal_code'],
                ':is_default' => $is_default,
                ':phone_number' => $_POST['phone_number'],
                ':note' => $_POST['note'] ?? null
            ]);
        }

        $pdo->commit();
        header("Location: login.php?registration=success");
        exit();
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $error = "Database error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Supermarket - User Registration</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background-color: #f5f5f5;
            padding-top: 160px; /* avoid header overlap */
        }

        header {
        background-color: #45ad9c;
        padding: 20px;
        color: white;
        position: fixed;
        top: 0;
        width: 100%;
        z-index: 1000;
        }

        .header-main {
            display: flex;
            align-items: center;
            gap: 30px;
        }

        .header-main img {
            width: 180px;
            height: 70px;
        }

        .nav-links {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
            gap: 20px;
        }

        .nav-links li a {
            font-size: 20px;
            font-family: 'Impact', sans-serif;
            text-decoration: none;
            color: white;
        }


        .registration-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
        }

        input, select, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
        }

        .register-btn {
            width: 100%;
            padding: 12px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
        }

        .register-btn:hover {
            background-color: #45a049;
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
        }

        .login-link a {
            color: #0066cc;
            text-decoration: none;
        }

        .address-section {
            margin-top: 25px;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }

        .add-address-btn {
            background-color: transparent;
            border: none;
            color: #0066cc;
            cursor: pointer;
            font-size: 16px;
            padding: 5px 0;
            margin-bottom: 15px;
        }

        .save-address-btn {
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }

        .error-message {
            color: #d9534f;
            background-color: #fdf7f7;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #ebccd1;
        }
    </style>
</head>

<body>

<header>
        <div class="header-main">
            <a href="MainPage/MainPage.php">
                <img src="../image/gogoname.png" alt="GOGO Logo">
            </a>
        </div>

        <nav>
            <ul class="nav-links">
                <li><a href="MainPage/MainPage.php">Menu</a></li>
                <li><a href="AboutUs/AboutUs.html">About GOGO</a></li>
                <li><a href="CustomerService.html">Customer Service</a></li>
            </ul>
        </nav>
</header>

<div class="registration-container">
    <h1>Create Your Account</h1>

    <?php if (isset($error)): ?>
        <div class="error-message"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form id="registrationForm" action="register.php" method="post">
        <div class="form-group">
            <label for="user_name">Full Name</label>
            <input type="text" id="user_name" name="user_name" required>
        </div>

        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" required>
        </div>

        <div class="form-group">
            <label for="user_password">Password</label>
            <input type="password" id="user_password" name="user_password" required>
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" required>
        </div>

        <div class="form-group">
            <label for="user_phone_num">Phone Number</label>
            <input type="tel" id="user_phone_num" name="user_phone_num" required>
        </div>

        <!-- Address Section -->
        <div class="address-section">
            <h3>Add Shipping Address</h3>
            <button type="button" id="addAddressBtn" class="add-address-btn">+ Add Address</button>
            <div id="addressForm" style="display: none;">
                <div class="form-group">
                    <label for="recipient_name">Recipient Name</label>
                    <input type="text" id="recipient_name" name="recipient_name">
                </div>
                <div class="form-group">
                    <label for="street_address">Shipping Address</label>
                    <input type="text" id="street_address" name="street_address">
                </div>
                <div class="form-group">
                    <label for="city">City</label>
                    <select id="city" name="city">
                        <option value="MELAKA TENGAH">MELAKA TENGAH</option>
                        <option value="ALOR GAJAH">ALOR GAJAH</option>
                        <option value="JASIN">JASIN</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="state">State</label>
                    <input type="text" id="state" name="state" value="MALACCA" readonly>
                </div>
                <div class="form-group">
                    <label for="postal_code">Postal Code</label>
                    <input type="text" id="postal_code" name="postal_code">
                </div>
                <div class="form-group">
                    <label for="phone_number">Phone Number</label>
                    <input type="tel" id="phone_number" name="phone_number">
                </div>
                <button type="button" id="saveAddressBtn" class="save-address-btn">Save Address</button>
            </div>
        </div>

        <button type="submit" class="register-btn">Register</button>
    </form>

    <div class="login-link">
        Already have an account? <a href="login.php">Login here</a>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('registrationForm');
        const passwordInput = document.getElementById('user_password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const addAddressBtn = document.getElementById('addAddressBtn');
        const addressForm = document.getElementById('addressForm');
        const saveAddressBtn = document.getElementById('saveAddressBtn');

        addAddressBtn.addEventListener('click', function () {
            addressForm.style.display = addressForm.style.display === 'none' ? 'block' : 'none';
            this.textContent = addressForm.style.display === 'none' ? '+ Add Address' : 'Cancel';
        });

        saveAddressBtn.addEventListener('click', function () {
            addressForm.style.display = 'none';
            addAddressBtn.textContent = '✓ Address Added';
        });

        form.addEventListener('submit', function (e) {
            if (passwordInput.value !== confirmPasswordInput.value) {
                alert('Passwords do not match!');
                e.preventDefault();
                return;
            }

            if (passwordInput.value.length < 8) {
                alert('Password must be at least 8 characters long!');
                e.preventDefault();
                return;
            }

            const email = document.getElementById('email').value;
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                alert('Please enter a valid email address');
                e.preventDefault();
                return;
            }
        });

        const phoneInputs = [document.getElementById('user_phone_num'), document.getElementById('phone_number')];
        phoneInputs.forEach(input => {
            input.addEventListener('input', function () {
                let phoneNumber = this.value.replace(/\D/g, '');
                if (phoneNumber.length > 3 && phoneNumber.length <= 6) {
                    phoneNumber = phoneNumber.replace(/(\d{3})(\d{0,3})/, '$1-$2');
                } else if (phoneNumber.length > 6) {
                    phoneNumber = phoneNumber.replace(/(\d{3})(\d{3})(\d{0,4})/, '$1-$2-$3');
                }
                this.value = phoneNumber;
            });
        });
    });
</script>

</body>
</html>