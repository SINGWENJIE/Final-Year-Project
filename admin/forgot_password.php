<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';
session_start();

require '../db_connection.php'; 

if (isset($_POST['send_otp'])) {
    $email = htmlspecialchars($_POST['admin_email']);

//check the email in admin_email or no 
    $check_email_query = "SELECT * FROM admin WHERE admin_email = '$email'";
    $result = $conn->query($check_email_query);

    if ($result->num_rows == 0) {
        $error = "Email not found in admin records.";

    } else {
        $_SESSION['admin_email'] = $email;

        $otp = rand(100000, 999999);
        $_SESSION['otp'] = $otp;
        $_SESSION['otp_created_time'] = time();

        $subject = "Your OTP Code";
        $message = "Hello,

We are GoGo Supermarket. You have requested to reset your password.

Your One-Time Password (OTP) is: $otp

Please enter this OTP within 60 second to proceed with resetting your password. 
For your account security, do not share this OTP with anyone.

If you did not request this, please ignore this email or contact our support team immediately.

Thank you,
GoGo Supermarket Team";


        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'qiaoxuanp@gmail.com';
            $mail->Password = 'cguc amid omyn lxcs';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('qiaoxuanp@gmail.com', 'qiaoxuan');
            $mail->addAddress($email);
            $mail->addReplyTo('qiaoxuanp@gmail.com', 'qiaoxuan');

            $mail->isHTML(false);
            $mail->Subject = $subject;
            $mail->Body    = $message;

            $mail->send();
            $_SESSION['otp_sent'] = true;
            $msg = "OTP has been sent to your email.";
        } catch (Exception $e) {
            $error = "Failed to send OTP. Mailer Error: {$mail->ErrorInfo}";
        }
    }
}


if (isset($_POST['verify_otp'])) {
    $user_otp = $_POST['otp'];

    if (time() - $_SESSION['otp_created_time'] > 60) {
        $error = "OTP expired. Please request a new one.";
        unset($_SESSION['otp']);
        unset($_SESSION['otp_sent']);
    } else {
        if ($user_otp == $_SESSION['otp']) {
            unset($_SESSION['otp']);
            $_SESSION['otp_verified'] = true;
            $msg = "OTP verified! Please reset your password.";
        } else {
            $error = "Invalid OTP. Please try again.";
        }
    }
}

if (isset($_POST['reset_password'])) {
    $admin_password = $_POST['admin_password'];

    if (empty($admin_password)) {
        $error = "Password cannot be empty.";
    } else {
        if (!isset($_SESSION['admin_email'])) {
            $error = "Session expired. Please start again.";
        } else {
            $email = $_SESSION['admin_email'];

            $update_query = "UPDATE admin SET admin_password = '$admin_password' WHERE admin_email = '$email'";

            if ($conn->query($update_query)) {
                unset($_SESSION['otp_verified']);
                unset($_SESSION['otp_sent']);
                unset($_SESSION['admin_email']);
                echo "<script>alert('Password reset successful! Please login.');window.location.href='adminlogin.php';</script>";
                exit();
            } else {
                $error = "Failed to reset password. Error: " . $conn->error;
            }
        }
    }
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Forgot Password</title>
    <link rel="stylesheet" href="../assets/css/AdminStyle.css">
</head>

<body>
    <div class="login-container">
        <img src="../assets/images/logoname.png" alt="logo" class="logo">
        <h1>Forgot Password</h1>

        <?php if (isset($msg)) {
            echo "<p style='color:green;'>$msg</p>";
        } ?>
        <?php if (isset($error)) {
            echo "<p style='color:red;'>$error</p>";
        } ?>

        <?php if (!isset($_SESSION['otp_sent'])) { ?>
            <form action="forgot_password.php" method="POST">
                <input type="email" name="admin_email" placeholder="Enter your Email" required>
                <button type="submit" name="send_otp">Send OTP</button>
            </form>

        <?php } elseif (!isset($_SESSION['otp_verified'])) { ?>
            <form action="forgot_password.php" method="POST">
                <input type="text" name="otp" placeholder="Enter OTP" required>
                <button type="submit" name="verify_otp">Verify OTP</button>
            </form>

        <?php } else { ?>
            <form action="forgot_password.php" method="POST">
                <input type="password" name="admin_password" placeholder="New Password" required>
                <button type="submit" name="reset_password">Reset Password</button>
            </form>

        <?php } ?>

        <div style="margin-top: 15px;">
            <a href="adminlogin.php" style="font-size: 14px; color: #555;">Back to Login</a>
        </div>
    </div>
</body>

</html>