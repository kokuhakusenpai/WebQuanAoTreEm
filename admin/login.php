<?php
session_start();
ob_start();
include 'config/database.php';

// Hàm ghi log thao tác
function logAction($user_id, $action) {
    global $conn;
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $timestamp = date("Y-m-d H:i:s");

    $query = "INSERT INTO user_logs (user_id, action, ip_address, timestamp) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("isss", $user_id, $action, $ip_address, $timestamp);
    $stmt->execute();
    $stmt->close();
}

$error_message = null; // Biến lưu thông báo lỗi

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $error_message = "Tài khoản chứa ký tự không hợp lệ!";
    } else {
        $sql = "SELECT * FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_id'] = $user['user_id']; 
                
                logAction($user['id'], 'Đăng nhập');

                header("Location: ./index.php");
                exit;
            } else {
                $error_message = "Sai tài khoản hoặc mật khẩu!";
            }
        } else {
            $error_message = "Tài khoản không tồn tại!";
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập Admin</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <h2 class="login-title">Đăng nhập</h2>
        <?php if (isset($error_message)) : ?>
            <div class="error-message" style="color: red; text-align: center;">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        <form method="POST">
            <input type="text" name="username" class="input-field" placeholder="Tài khoản" required>
            <input type="password" name="password" class="input-field" placeholder="Mật khẩu" required>
            <button type="submit" class="login-button">Đăng nhập</button>
        </form>
    </div>
</body>
</html>