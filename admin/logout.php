<?php
session_start();
session_unset(); // clear
session_destroy(); // delete
header("Location: ../admin/adminlogin.php"); //bank to loginpage
exit();
?>
