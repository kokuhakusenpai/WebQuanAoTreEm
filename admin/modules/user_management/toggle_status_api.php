<?php
session_start();
include '../../config/database.php';

// Đặt header để trả về JSON
header('Content-Type: application/json');

// Kiểm tra xem request có phải là POST không
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ']);
    exit;
}

// Kiểm tra quyền admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
    exit;
}

// Lấy dữ liệu từ request
$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$new_status = isset($_POST['status']) ? intval($_POST['status']) : null;

// Kiểm tra dữ liệu đầu vào
if ($user_id <= 0 || !in_array($new_status, [0, 1])) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
    exit;
}

// Kiểm tra xem user có tồn tại
$query = "SELECT id, role FROM user WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Người dùng không tồn tại']);
    $stmt->close();
    exit;
}

$user = $result->fetch_assoc();
$stmt->close();

// Ngăn admin khóa chính mình
if ($user_id === $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'message' => 'Không thể khóa tài khoản của chính bạn']);
    exit;
}

// Cập nhật trạng thái
$query = "UPDATE user SET status = ? WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('ii', $new_status, $user_id);

if ($stmt->execute()) {
    // Ghi log vào user_log
    $admin_id = $_SESSION['user_id'];
    $action = $new_status == 1 ? 'Kích hoạt người dùng' : 'Khóa người dùng';
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $log_query = "INSERT INTO user_log (user_id, action, created_at, ip_address) VALUES (?, ?, NOW(), ?)";
    $log_stmt = $conn->prepare($log_query);
    $log_stmt->bind_param('iss', $admin_id, $action, $ip_address);
    $log_stmt->execute();
    $log_stmt->close();

    echo json_encode(['success' => true, 'message' => 'Cập nhật trạng thái thành công']);
} else {
    echo json_encode(['success' => false, 'message' => 'Lỗi khi cập nhật trạng thái: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>