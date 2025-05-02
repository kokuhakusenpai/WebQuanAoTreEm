<?php
session_start();
include('../config/database.php');

// Kiểm tra đăng nhập
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'Bạn chưa đăng nhập.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {
    $avatar = $_FILES['avatar'];
    $target_dir = "assets/img/avatars/";
    $username = $_SESSION['username'];
    $unique_name = uniqid() . '-' . basename($avatar['name']);
    $target_file = $target_dir . $unique_name;
    $upload_ok = 1;
    $image_file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Kiểm tra định dạng ảnh
    if (!getimagesize($avatar['tmp_name'])) {
        echo json_encode(['success' => false, 'message' => 'Tệp không phải là hình ảnh.']);
        exit;
    }

    // Kiểm tra kích thước tệp
    if ($avatar['size'] > 5000000) {
        echo json_encode(['success' => false, 'message' => 'Tệp hình ảnh quá lớn.']);
        exit;
    }

    // Chỉ cho phép định dạng JPG, JPEG, PNG
    if (!in_array($image_file_type, ['jpg', 'jpeg', 'png'])) {
        echo json_encode(['success' => false, 'message' => 'Chỉ chấp nhận định dạng JPG, JPEG và PNG.']);
        exit;
    }

    // Lưu tệp nếu không có lỗi
    if (move_uploaded_file($avatar['tmp_name'], $target_file)) {
        // Cập nhật cơ sở dữ liệu bằng prepared statement
        $update_query = "UPDATE users SET avatar = ? WHERE username = ?";
        $stmt = $conn->prepare($update_query);
        if ($stmt === false) {
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống.']);
            exit;
        }
        $stmt->bind_param("ss", $unique_name, $username);
        $stmt->execute();
        $stmt->close();

        echo json_encode([
            'success' => true,
            'avatar_url' => $target_file,
            'message' => 'Tải lên ảnh đại diện thành công.'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi tải ảnh.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ.']);
}
$conn->close();
?>