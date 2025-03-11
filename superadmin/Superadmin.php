<?php
include 'db_connection.php';

$superadmin_password = password_hash("password", PASSWORD_DEFAULT);

$sql = "INSERT INTO admin (admin_id, password, role) VALUES ('superadmin', '$superadmin_password', 'superadmin')";

if ($conn->query($sql) === TRUE) {
    echo "Superadmin created successfully!";
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>
