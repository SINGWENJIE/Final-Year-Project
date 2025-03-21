<?php

$host = "localhost";
$user = "root";
$password = "";
$database = "gogo shopping";

$conn = new mysqli($host, $user, $password, $database);

if ($conn-> connect-error){
    die("Connection failed: ". $conn->connect_error);
}

?>