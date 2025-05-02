<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "baby_shop";

// Tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi kết nối database. Vui lòng thử lại sau.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Thiết lập charset utf8mb4 để hỗ trợ đầy đủ Unicode
$conn->set_charset("utf8mb4");
?>