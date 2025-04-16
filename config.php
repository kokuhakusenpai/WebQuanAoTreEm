<?php
$servername = "localhost";
$username = "root"; // Thay bằng tài khoản database của bạn
$password = ""; // Thay bằng mật khẩu database của bạn
$dbname = "baby_shop"; // Thay bằng tên database của bạn

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Thiết lập charset UTF-8 để hỗ trợ tiếng Việt
$conn->set_charset("utf8");

?>
