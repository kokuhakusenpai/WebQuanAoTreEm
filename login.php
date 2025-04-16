<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier']);
    $password = trim($_POST['password']);

    if (empty($identifier) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đầy đủ thông tin.']);
        exit;
    }

    $stmt = $conn->prepare("SELECT user_id, username, password, role, status FROM users WHERE (username = ? OR phone = ?) AND status = 'active'");
    $stmt->bind_param("ss", $identifier, $identifier);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            setcookie("user_id", $user['user_id'], time() + 3600, "/");
            setcookie("username", $user['username'], time() + 3600, "/");
            setcookie("role", $user['role'], time() + 3600, "/");

            echo json_encode(['success' => true, 'message' => 'Đăng nhập thành công.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Mật khẩu không chính xác.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Tên đăng nhập hoặc số điện thoại không tồn tại.']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ.']);
}

$conn->close();
?>
