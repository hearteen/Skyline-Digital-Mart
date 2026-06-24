<?php
$host = "";
$user = "";
$pass = "";
$dbname = "";

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset to UTF-8 to support Tamil letters properly
mysqli_set_charset($conn, "utf8mb4"); 
?>