<?php
session_start(); 

// Cấu hình timezone
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Cấu hình kết nối MySQL
$servername = "localhost";
$username = "root";
$password = "";
$database = "datatong";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

$conn->set_charset("utf8");

// Các cấu hình khác (ví dụ: bảo mật)
define("BASE_URL", "http://localhost/WebQuanAoTreEm/");
?>
