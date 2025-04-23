<?php
$servername = "localhost"; 
$username = "root"; 
$password = ""; 
$dbname = "baby_shop"; 

// Tạo kết nối
$conn = new mysqli("localhost", "root", "", "baby_shop");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
?>
