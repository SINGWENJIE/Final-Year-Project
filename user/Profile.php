<?php
session_start();
require_once '../db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login/Login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>GOGO | Edit Profile</title>
    <link rel="icon" type="image" href="../../image/GOGO.png">
    <link rel="stylesheet" href="../user_assets/css/Profile.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0">
</head>
<body>
    <?php include 'header.php'; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert success-alert">
            <?php 
                echo $_SESSION['success']; 
                unset($_SESSION['success']); 
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert error-alert">
            <?php 
                echo $_SESSION['error']; 
                unset($_SESSION['error']); 
            ?>
        </div>
    <?php endif; ?>

    <div class="profile-container">
        <h1><i class='bx bx-user-pin'></i> Edit Profile</h1>
        
        <form action="Profile/update_profile.php" method="POST">
            <div class="form-group">
                <label><i class='bx bx-user'></i> Username</label>
                <input type="text" name="username" 
                       value="<?php echo htmlspecialchars($user['user_name']); ?>" 
                       required>
            </div>

            <div class="form-group">
                <label><i class='bx bx-envelope'></i> Email</label>
                <input type="email" name="email" 
                       value="<?php echo htmlspecialchars($user['email']); ?>" 
                       required>
            </div>

            <div class="form-group">
                <label><i class='bx bx-phone'></i> Phone</label>
                <input type="tel" name="phone" 
                       pattern="[0-9]{10,11}"
                       title="Format: 0123456789 (10-11 digits)"
                       value="<?php echo htmlspecialchars($user['user_phone_num']); ?>" 
                       required>
                <small>Format: 0123456789 (10-11 digits)</small>
            </div>

            <div class="form-group">
                <label><i class='bx bx-calendar'></i> Birthday</label>
                <input type="text" id="birth_date" name="birth_date" 
                    value="<?php echo htmlspecialchars($user['birth_date'] ?? ''); ?>" 
                    placeholder="Select Date" required>
            </div>

            <div class="action-buttons">
                <button type="submit" class="save-btn">
                    <i class='bx bx-save'></i> Save Changes
                </button>

                <a href="MainPage.php" class="return-btn">
                    <i class='bx bx-home'></i> Back to Main
                </a>
            </div>
        </form>
    </div>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
       flatpickr("#birth_date", {
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "F j, Y",
        defaultDate: "<?php echo $user['birth_date'] ?? 'today'; ?>",
        maxDate: "today",
        position: "auto center",
        wrap: false,
        animate: true,
        });

    </script>
</body>
</html>