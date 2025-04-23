<?php
include('config/database.php'); 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']); 
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $new_password = trim($_POST['new_password']);
    $role = trim($_POST['role']); // Lấy vai trò từ form

    if (!empty($username) && !empty($email) && !empty($phone) && !empty($new_password) && !empty($role)) {
        // Kiểm tra định dạng email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo "Email không hợp lệ!";
            exit; 
        }

        // Kiểm tra định dạng số điện thoại
        if (!preg_match('/^[0-9]+$/', $phone)) {
            echo "Số điện thoại không hợp lệ!";
            exit; 
        }

        // Kiểm tra giá trị hợp lệ cho role
        $valid_roles = ['customer', 'admin'];
        if (!in_array($role, $valid_roles)) {
            die("Vai trò không hợp lệ!");
        }

        // Kiểm tra xem username đã tồn tại chưa
        $query = "SELECT user_id FROM users WHERE username = ?";
        $checkStmt = $conn->prepare($query);
        if ($checkStmt === false) {
            die("Lỗi khi chuẩn bị truy vấn kiểm tra: " . $conn->error);
        }

        $checkStmt->bind_param("s", $username);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            echo "Tên đăng nhập đã tồn tại!";
            $checkStmt->close();
            exit;
        }
        $checkStmt->close();

        // Mã hóa mật khẩu
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Thêm tài khoản mới vào database
        $insert_query = "INSERT INTO users (username, email, phone, password, role) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        if ($stmt === false) {
            die("Lỗi khi chuẩn bị truy vấn: " . $conn->error);
        }

        $stmt->bind_param("sssss", $username, $email, $phone, $hashed_password, $role);

        if ($stmt->execute()) {
            $stmt->close();
            echo "<script>alert('Tạo tài khoản thành công!');</script>";
            echo "<script>window.location.href = './login.php';</script>";
            exit;
        } else {
            die("Lỗi khi tạo tài khoản: " . $stmt->error);
        }
    } else {
        echo "Vui lòng nhập đầy đủ thông tin!";
    }
}
?>


<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng ký tài khoản</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="register-page">
    <form action="" method="POST">
        <h2>Đăng ký tài khoản</h2> 
    
        <label for="username">Tên tài khoản:</label>
        <input type="text" id="username" name="username" required>
    
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
    
        <label for="phone">Số điện thoại:</label>
        <input type="text" id="phone" name="phone" pattern="[0-9]+" title="Vui lòng nhập số hợp lệ" required>
    
        <label for="new_password">Mật khẩu:</label>
        <input type="password" id="new_password" name="new_password" required>

        <label for="role">Vai trò:</label>
        <select id="role" name="role" required>
            <option value="customer">Nhân viên</option>
            <option value="admin">Admin</option>
        </select>
    
        <button type="submit">Tạo tài khoản</button>
    </form>
</body>
</html>