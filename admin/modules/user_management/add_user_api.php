<?php
session_start();
include '../../config/database.php';

// Kiểm tra nếu dữ liệu được gửi bằng POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy dữ liệu từ form hoặc fetch()
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Kiểm tra dữ liệu không rỗng
    if (!empty($username) && !empty($email) && !empty($password)) {
        // Mã hóa mật khẩu (bắt buộc)
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Chuẩn bị câu truy vấn
        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("sss", $username, $email, $hashedPassword);
            if ($stmt->execute()) {
                echo json_encode(["status" => "success", "message" => "Thêm người dùng thành công"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Lỗi khi thêm dữ liệu: " . $stmt->error]);
            }
            $stmt->close();
        } else {
            echo json_encode(["status" => "error", "message" => "Chuẩn bị truy vấn thất bại: " . $conn->error]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Vui lòng nhập đầy đủ thông tin"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Phải gửi bằng phương thức POST"]);
}
?>
