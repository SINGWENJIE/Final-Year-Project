<?php
// Database configuration
$servername = "localhost"; 
$username = "root"; 
$password = "";  
$dbname = "gogo_supermarket"; 

session_start();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Step 1: Email verification
        if (isset($_POST['email'])) {
            $email = $_POST['email'];
            
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
            $stmt->execute([':email' => $email]);
            if ($stmt->fetchColumn() > 0) {
                throw new Exception("Email already registered. Please use a different email.");
            }
            
            // Generate OTP (6 digits)
            $otp = rand(100000, 999999);
            $_SESSION['registration_otp'] = $otp;
            $_SESSION['registration_email'] = $email;
            
            // In a real application, you would send this OTP to the user's email
            // For this example, we'll just display it
            $_SESSION['otp_display'] = $otp;
            
            header("Location: register.php?step=verify");
            exit();
        }
        
        // Step 2: OTP verification
        if (isset($_POST['otp'])) {
            if ($_POST['otp'] != $_SESSION['registration_otp']) {
                throw new Exception("Invalid OTP. Please try again.");
            }
            
            $_SESSION['otp_verified'] = true;
            header("Location: register.php?step=details");
            exit();
        }
        
        // Step 3: User details
        if (isset($_POST['user_name']) && isset($_SESSION['otp_verified'])) {
            $pdo->beginTransaction();

            // Get user form data
            $user_name = $_POST['user_name'];
            $email = $_SESSION['registration_email'];
            $user_password = password_hash($_POST['user_password'], PASSWORD_DEFAULT);
            $user_phone_num = $_POST['user_phone_num'];
            $birth_date = $_POST['birth_date'];
            $status = 'active';
            $user_created_at = date('Y-m-d H:i:s');

            // Calculate age
            $today = new DateTime();
            $birthday = new DateTime($birth_date);
            $age = $today->diff($birthday)->y;
            
            if ($age < 18) {
                throw new Exception("You must be at least 18 years old to register.");
            }

            // Insert user into database
            $stmt = $pdo->prepare("INSERT INTO users (user_name, email, user_password, user_phone_num, user_created_at, status, birth_date) 
                                  VALUES (:user_name, :email, :user_password, :user_phone_num, :user_created_at, :status, :birth_date)");
            
            $stmt->execute([
                ':user_name' => $user_name,
                ':email' => $email,
                ':user_password' => $user_password,
                ':user_phone_num' => $user_phone_num,
                ':user_created_at' => $user_created_at,
                ':status' => $status,
                ':birth_date' => $birth_date
            ]);

            $user_id = $pdo->lastInsertId();

            // Get address form data if provided
            if (isset($_POST['recipient_name'])) {
                $recipient_name = $_POST['recipient_name'];
                $street_address = $_POST['street_address'];
                $city = $_POST['city'];
                $state = $_POST['state'];
                $postal_code = $_POST['postal_code'];
                $phone_number = $_POST['phone_number'];
                $note = $_POST['note'] ?? null;

                // Insert address into database
                $stmt = $pdo->prepare("INSERT INTO address (user_id, recipient_name, street_address, city, state, postal_code, is_default, phone_number, note) 
                                      VALUES (:user_id, :recipient_name, :street_address, :city, :state, :postal_code, 1, :phone_number, :note)");
                
                $stmt->execute([
                    ':user_id' => $user_id,
                    ':recipient_name' => $recipient_name,
                    ':street_address' => $street_address,
                    ':city' => $city,
                    ':state' => $state,
                    ':postal_code' => $postal_code,
                    ':phone_number' => $phone_number,
                    ':note' => $note
                ]);
            }

            $pdo->commit();
            
            // Clear session data
            unset($_SESSION['registration_otp']);
            unset($_SESSION['registration_email']);
            unset($_SESSION['otp_verified']);
            unset($_SESSION['otp_display']);
            
            // Redirect to success page
            header("Location: login.php?registration=success");
            exit();
        }
    } catch (Exception $e) {
        // Rollback transaction if error occurs
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $error = $e->getMessage();
    }
}

// Determine current step
$step = isset($_GET['step']) ? $_GET['step'] : 'email';
if ($step == 'verify' && !isset($_SESSION['registration_otp'])) {
    $step = 'email';
}
if ($step == 'details' && (!isset($_SESSION['otp_verified']) || !$_SESSION['otp_verified'])) {
    $step = 'email';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supermarket - User Registration</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4CAF50;
            --primary-dark: #45a049;
            --secondary-color: #f5f5f5;
            --text-color: #333;
            --light-text: #777;
            --border-color: #ddd;
            --error-color: #d9534f;
            --success-color: #5cb85c;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--secondary-color);
            color: var(--text-color);
            line-height: 1.6;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .registration-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            padding: 30px;
            width: 100%;
            max-width: 600px;
            transition: all 0.3s ease;
        }
        
        .registration-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .registration-header h1 {
            color: var(--primary-color);
            margin-bottom: 10px;
            font-size: 28px;
        }
        
        .registration-header p {
            color: var(--light-text);
            font-size: 14px;
        }
        
        .progress-steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            position: relative;
        }
        
        .progress-steps::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 0;
            right: 0;
            height: 2px;
            background-color: var(--border-color);
            z-index: 1;
        }
        
        .progress-bar {
            position: absolute;
            top: 15px;
            left: 0;
            height: 2px;
            background-color: var(--primary-color);
            z-index: 2;
            transition: width 0.3s ease;
        }
        
        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            z-index: 3;
        }
        
        .step-number {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: var(--border-color);
            color: var(--light-text);
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: 600;
            margin-bottom: 5px;
            transition: all 0.3s ease;
        }
        
        .step.active .step-number {
            background-color: var(--primary-color);
            color: white;
        }
        
        .step.completed .step-number {
            background-color: var(--success-color);
            color: white;
        }
        
        .step-label {
            font-size: 12px;
            color: var(--light-text);
            text-align: center;
        }
        
        .step.active .step-label {
            color: var(--primary-color);
            font-weight: 500;
        }
        
        .step.completed .step-label {
            color: var(--success-color);
        }
        
        .form-step {
            display: none;
        }
        
        .form-step.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-color);
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 15px;
            font-family: 'Poppins', sans-serif;
            transition: border-color 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(76, 175, 80, 0.2);
        }
        
        .input-group {
            position: relative;
        }
        
        .input-group-append {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--light-text);
            cursor: pointer;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 20px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--primary-color);
            color: var(--primary-color);
        }
        
        .btn-outline:hover {
            background-color: var(--primary-color);
            color: white;
        }
        
        .text-center {
            text-align: center;
        }
        
        .mt-3 {
            margin-top: 15px;
        }
        
        .mb-4 {
            margin-bottom: 20px;
        }
        
        .error-message {
            color: var(--error-color);
            background-color: #fdf7f7;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #ebccd1;
            font-size: 14px;
        }
        
        .success-message {
            color: var(--success-color);
            background-color: #f7fdf7;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #d4edda;
            font-size: 14px;
        }
        
        .password-strength {
            margin-top: 5px;
            height: 5px;
            background-color: #eee;
            border-radius: 5px;
            overflow: hidden;
        }
        
        .password-strength-bar {
            height: 100%;
            width: 0;
            transition: width 0.3s ease, background-color 0.3s ease;
        }
        
        .password-hint {
            font-size: 12px;
            color: var(--light-text);
            margin-top: 5px;
        }
        
        .otp-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        
        .otp-input {
            width: 15%;
            height: 50px;
            text-align: center;
            font-size: 20px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
        }
        
        .otp-input:focus {
            border-color: var(--primary-color);
            outline: none;
        }
        
        .resend-otp {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 14px;
            cursor: pointer;
        }
        
        .resend-otp:hover {
            text-decoration: underline;
        }
        
        .login-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }
        
        .login-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
        
        .dob-container {
            display: flex;
            gap: 10px;
        }
        
        .dob-container select {
            flex: 1;
        }
        
        .tooltip {
            position: relative;
            display: inline-block;
            margin-left: 5px;
            color: var(--light-text);
            cursor: pointer;
        }
        
        .tooltip .tooltiptext {
            visibility: hidden;
            width: 200px;
            background-color: #555;
            color: #fff;
            text-align: center;
            border-radius: 6px;
            padding: 5px;
            position: absolute;
            z-index: 1;
            bottom: 125%;
            left: 50%;
            margin-left: -100px;
            opacity: 0;
            transition: opacity 0.3s;
            font-size: 12px;
        }
        
        .tooltip:hover .tooltiptext {
            visibility: visible;
            opacity: 1;
        }
        
        @media (max-width: 576px) {
            .registration-container {
                padding: 20px;
            }
            
            .step-label {
                display: none;
            }
            
            .dob-container {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="registration-container">
        <div class="registration-header">
            <h1>Create Your Account</h1>
            <p>Join our supermarket to enjoy exclusive deals and convenient shopping</p>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['otp_display'])): ?>
            <div class="success-message">
                For demonstration purposes, your OTP is: <strong><?php echo $_SESSION['otp_display']; ?></strong>
            </div>
        <?php endif; ?>
        
        <div class="progress-steps">
            <div class="progress-bar" style="width: 
                <?php 
                    if ($step == 'email') echo '0%';
                    elseif ($step == 'verify') echo '33%';
                    elseif ($step == 'details') echo '66%';
                    else echo '100%';
                ?>">
            </div>
            <div class="step <?php echo $step == 'email' ? 'active' : ($step == 'verify' || $step == 'details' || $step == 'complete' ? 'completed' : ''); ?>">
                <div class="step-number">1</div>
                <div class="step-label">Email</div>
            </div>
            <div class="step <?php echo $step == 'verify' ? 'active' : ($step == 'details' || $step == 'complete' ? 'completed' : ''); ?>">
                <div class="step-number">2</div>
                <div class="step-label">Verify</div>
            </div>
            <div class="step <?php echo $step == 'details' ? 'active' : ($step == 'complete' ? 'completed' : ''); ?>">
                <div class="step-number">3</div>
                <div class="step-label">Details</div>
            </div>
        </div>
        
        <!-- Step 1: Email Verification -->
        <form id="emailForm" class="form-step <?php echo $step == 'email' ? 'active' : ''; ?>" method="post">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>
            
            <button type="submit" class="btn">Continue</button>
            
            <div class="login-link">
                Already have an account? <a href="login.php">Login here</a>
            </div>
        </form>
        
        <!-- Step 2: OTP Verification -->
        <form id="otpForm" class="form-step <?php echo $step == 'verify' ? 'active' : ''; ?>" method="post">
            <div class="form-group">
                <label for="otp">Enter 6-digit OTP</label>
                <p class="text-center mb-4">We've sent a verification code to <strong><?php echo isset($_SESSION['registration_email']) ? htmlspecialchars($_SESSION['registration_email']) : ''; ?></strong></p>
                
                <div class="otp-container">
                    <input type="text" id="otp1" name="otp1" class="otp-input" maxlength="1" required>
                    <input type="text" id="otp2" name="otp2" class="otp-input" maxlength="1" required>
                    <input type="text" id="otp3" name="otp3" class="otp-input" maxlength="1" required>
                    <input type="text" id="otp4" name="otp4" class="otp-input" maxlength="1" required>
                    <input type="text" id="otp5" name="otp5" class="otp-input" maxlength="1" required>
                    <input type="text" id="otp6" name="otp6" class="otp-input" maxlength="1" required>
                </div>
                
                <input type="hidden" id="otp" name="otp">
                
                <p class="text-center">
                    Didn't receive code? <a href="#" class="resend-otp">Resend OTP</a>
                </p>
            </div>
            
            <button type="submit" class="btn">Verify</button>
            
            <button type="button" class="btn btn-outline mt-3" onclick="window.location.href='register.php'">
                Change Email
            </button>
        </form>
        
        <!-- Step 3: User Details -->
        <form id="detailsForm" class="form-step <?php echo $step == 'details' ? 'active' : ''; ?>" method="post">
            <div class="form-group">
                <label for="user_name">Full Name</label>
                <input type="text" id="user_name" name="user_name" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="birth_date">Date of Birth</label>
                <input type="date" id="birth_date" name="birth_date" class="form-control" required>
                <small class="text-muted">You must be at least 18 years old to register.</small>
            </div>
            
            <div class="form-group">
                <label for="user_phone_num">Phone Number</label>
                <input type="tel" id="user_phone_num" name="user_phone_num" class="form-control" placeholder="e.g. 012-345 6789" required>
            </div>
            
            <div class="form-group">
                <label for="user_password">Password</label>
                <input type="password" id="user_password" name="user_password" class="form-control" required>
                <div class="password-strength">
                    <div class="password-strength-bar" id="passwordStrengthBar"></div>
                </div>
                <div class="password-hint">
                    Must contain at least 1 uppercase letter, 1 symbol, and minimum 4 characters
                </div>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                <div id="passwordMatch" class="password-hint"></div>
            </div>
            
            <div class="form-group">
                <h3>Delivery Address</h3>
            </div>
            
            <div class="form-group">
                <label for="recipient_name">Recipient Name</label>
                <input type="text" id="recipient_name" name="recipient_name" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="street_address">Shipping Address</label>
                <input type="text" id="street_address" name="street_address" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="city">City</label>
                <select id="city" name="city" class="form-control" required>
                    <option value="">Select City</option>
                    <option value="MELAKA TENGAH">MELAKA TENGAH</option>
                    <option value="ALOR GAJAH">ALOR GAJAH</option>
                    <option value="JASIN">JASIN</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="state">State</label>
                <input type="text" id="state" name="state" class="form-control" value="MALACCA" readonly>
            </div>
            
            <div class="form-group">
                <label for="postal_code">Postal Code</label>
                <input type="text" id="postal_code" name="postal_code" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="phone_number">Contact Number</label>
                <input type="tel" id="phone_number" name="phone_number" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="note">Delivery Instructions (Optional)</label>
                <textarea id="note" name="note" class="form-control" rows="3"></textarea>
            </div>
            
            <button type="submit" class="btn">Complete Registration</button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // OTP input handling
            const otpInputs = document.querySelectorAll('.otp-input');
            const otpHiddenInput = document.getElementById('otp');
            
            otpInputs.forEach((input, index) => {
                input.addEventListener('input', function() {
                    if (this.value.length === 1) {
                        if (index < otpInputs.length - 1) {
                            otpInputs[index + 1].focus();
                        }
                    }
                    
                    // Update hidden input with complete OTP
                    let otp = '';
                    otpInputs.forEach(otpInput => {
                        otp += otpInput.value;
                    });
                    otpHiddenInput.value = otp;
                });
                
                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Backspace' && this.value.length === 0) {
                        if (index > 0) {
                            otpInputs[index - 1].focus();
                        }
                    }
                });
            });
            
            // Password strength indicator
            const passwordInput = document.getElementById('user_password');
            const passwordStrengthBar = document.getElementById('passwordStrengthBar');
            
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                let strength = 0;
                
                // Check length
                if (password.length >= 4) strength += 20;
                if (password.length >= 8) strength += 20;
                
                // Check for uppercase letters
                if (/[A-Z]/.test(password)) strength += 20;
                
                // Check for numbers
                if (/[0-9]/.test(password)) strength += 20;
                
                // Check for special characters
                if (/[^A-Za-z0-9]/.test(password)) strength += 20;
                
                // Update strength bar
                passwordStrengthBar.style.width = strength + '%';
                
                // Update color based on strength
                if (strength < 40) {
                    passwordStrengthBar.style.backgroundColor = '#d9534f'; // Red
                } else if (strength < 80) {
                    passwordStrengthBar.style.backgroundColor = '#f0ad4e'; // Yellow
                } else {
                    passwordStrengthBar.style.backgroundColor = '#5cb85c'; // Green
                }
            });
            
            // Password match validation
            const confirmPasswordInput = document.getElementById('confirm_password');
            const passwordMatch = document.getElementById('passwordMatch');
            
            confirmPasswordInput.addEventListener('input', function() {
                if (this.value !== passwordInput.value) {
                    passwordMatch.textContent = 'Passwords do not match!';
                    passwordMatch.style.color = 'var(--error-color)';
                } else {
                    passwordMatch.textContent = 'Passwords match!';
                    passwordMatch.style.color = 'var(--success-color)';
                }
            });
            
            // Phone number formatting
            const phoneInputs = [document.getElementById('user_phone_num'), document.getElementById('phone_number')];
            phoneInputs.forEach(input => {
                if (input) {
                    input.addEventListener('input', function() {
                        let phoneNumber = this.value.replace(/\D/g, '');
                        if (phoneNumber.length > 3 && phoneNumber.length <= 6) {
                            phoneNumber = phoneNumber.replace(/(\d{3})(\d{0,3})/, '$1-$2');
                        } else if (phoneNumber.length > 6) {
                            phoneNumber = phoneNumber.replace(/(\d{3})(\d{3})(\d{0,4})/, '$1-$2 $3');
                        }
                        this.value = phoneNumber;
                    });
                }
            });
            
            // Postal code validation (Malaysian postal codes are 5 digits)
            const postalCodeInput = document.getElementById('postal_code');
            postalCodeInput.addEventListener('input', function() {
                this.value = this.value.replace(/\D/g, '').substring(0, 5);
            });
            
            // Age validation
            const birthDateInput = document.getElementById('birth_date');
            birthDateInput.addEventListener('change', function() {
                const today = new Date();
                const birthDate = new Date(this.value);
                const age = today.getFullYear() - birthDate.getFullYear();
                const monthDiff = today.getMonth() - birthDate.getMonth();
                
                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                    age--;
                }
                
                if (age < 18) {
                    alert('You must be at least 18 years old to register.');
                    this.value = '';
                }
            });
            
            // Form validation
            const detailsForm = document.getElementById('detailsForm');
            detailsForm.addEventListener('submit', function(e) {
                // Check if passwords match
                if (passwordInput.value !== confirmPasswordInput.value) {
                    e.preventDefault();
                    alert('Passwords do not match!');
                    return;
                }
                
                // Check password strength
                const hasUpperCase = /[A-Z]/.test(passwordInput.value);
                const hasSymbol = /[^A-Za-z0-9]/.test(passwordInput.value);
                const hasMinLength = passwordInput.value.length >= 4;
                
                if (!hasUpperCase || !hasSymbol || !hasMinLength) {
                    e.preventDefault();
                    alert('Password must contain at least 1 uppercase letter, 1 symbol, and minimum 4 characters');
                    return;
                }
                
                // Check if birth date is filled
                if (!birthDateInput.value) {
                    e.preventDefault();
                    alert('Please enter your date of birth');
                    return;
                }
            });
            
            // Resend OTP button
            const resendOtpBtn = document.querySelector('.resend-otp');
            if (resendOtpBtn) {
                resendOtpBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    alert('A new OTP has been sent to your email. For demo purposes, the OTP remains the same.');
                });
            }
        });
    </script>
</body>
</html>