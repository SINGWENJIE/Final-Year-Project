<?php
session_start();
require_once '../db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login/Login.php");
    exit();
}

$error = '';
$email = '';

// Fetch the user's email
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT email, user_password FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($email, $hashed_password);
$stmt->fetch();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input_password = $_POST['password'];

    if (password_verify($input_password, $hashed_password)) {
        $_SESSION['verified'] = true;
        header("Location: Profile.php");
        exit();
    } else {
        $error = "Incorrect password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify Identity</title>
    <link rel="icon" type="image" href="../image/GOGO.png">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background-color: #f0f2f5;
            font-family: Arial, sans-serif;
        }

        .profile-container {
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }

        input[readonly] {
            background-color: #f0f0f0;
            cursor: not-allowed;
        }

        .action-buttons {
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }

        .save-btn, .return-btn {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            text-align: center;
            text-decoration: none;
        }

        .save-btn {
            background-color: #4CAF50;
            color: white;
        }

        .return-btn {
            background-color: #ccc;
            color: #333;
        }

        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 6px;
        }

        .error-alert {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <h1><i class='bx bx-lock-alt'></i> Verify Password</h1>

        <?php if ($error): ?>
            <div class="alert error-alert"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label><i class='bx bx-envelope'></i> Email</label>
                <input type="email" value="<?php echo htmlspecialchars($email); ?>" readonly>
            </div>

            <div class="form-group">
                <label for="password"><i class='bx bx-lock'></i> Enter Password</label>
                <input type="password" name="password" required>
            </div>

            <div class="action-buttons">
                <button type="submit" class="save-btn">Verify</button>
                <a href="MainPage.php" class="return-btn">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>
