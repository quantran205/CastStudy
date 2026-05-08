<?php
// Thay đổi localhost thành localhost:3307
$host = "localhost:3307"; 
$user = "root";
$pass = ""; 
$dbname = "gtpt"; // 

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");
?>