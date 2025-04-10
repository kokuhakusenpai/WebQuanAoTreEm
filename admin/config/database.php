<?php
$servername = "localhost"; 
$username = "root"; 
$password = ""; 
$dbname = "baby_shop"; 

// Tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

if (!$conn) {
    die("Lỗi kết nối cơ sở dữ liệu: " . mysqli_connect_error());
} else {
    // echo "Kết nối thành công!";
}

?>

