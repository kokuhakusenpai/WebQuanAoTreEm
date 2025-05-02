<?php
include('config/database.php'); 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']); 
    $email = trim($_POST['email']);
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : null;
    $new_password = trim($_POST['new_password']);
    $role = trim($_POST['role']); // Lấy vai trò từ form

    if (!empty($username) && !empty($email) && !empty($new_password) && !empty($role)) {
        // Kiểm tra định dạng email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo "<script>alert('Email không hợp lệ!');</script>";
            exit; 
        }

        // Kiểm tra định dạng số điện thoại nếu có
        if (!empty($phone) && !preg_match('/^[0-9]+$/', $phone)) {
            echo "<script>alert('Số điện thoại không hợp lệ!');</script>";
            exit; 
        }

        // Kiểm tra giá trị hợp lệ cho role
        $valid_roles = ['customer', 'admin'];
        if (!in_array($role, $valid_roles)) {
            die("Vai trò không hợp lệ!");
        }

        // Kiểm tra xem username đã tồn tại chưa
        $query = "SELECT id FROM user WHERE username = ?";
        $checkStmt = $conn->prepare($query);
        if ($checkStmt === false) {
            die("Lỗi khi chuẩn bị truy vấn kiểm tra: " . $conn->error);
        }

        $checkStmt->bind_param("s", $username);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            echo "<script>alert('Tên đăng nhập đã tồn tại!');</script>";
            $checkStmt->close();
            exit;
        }
        $checkStmt->close();

        // Kiểm tra xem email đã tồn tại chưa
        $query = "SELECT id FROM user WHERE email = ?";
        $checkStmt = $conn->prepare($query);
        if ($checkStmt === false) {
            die("Lỗi khi chuẩn bị truy vấn kiểm tra email: " . $conn->error);
        }

        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            echo "<script>alert('Email này đã được sử dụng!');</script>";
            $checkStmt->close();
            exit;
        }
        $checkStmt->close();

        // Mã hóa mật khẩu
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Thêm tài khoản mới vào database
        $insert_query = "INSERT INTO user (username, email, phone, password, role) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        if ($stmt === false) {
            die("Lỗi khi chuẩn bị truy vấn: " . $conn->error);
        }

        $stmt->bind_param("sssss", $username, $email, $phone, $hashed_password, $role);

        if ($stmt->execute()) {
            // Lấy ID của người dùng vừa tạo
            $new_user_id = $conn->insert_id;
            
            // Ghi log thao tác nếu cần
            $action = "Đăng ký tài khoản";
            $ip_address = $_SERVER['REMOTE_ADDR'];
            $log_query = "INSERT INTO user_log (user_id, action, ip_address) VALUES (?, ?, ?)";
            $log_stmt = $conn->prepare($log_query);
            
            if ($log_stmt) {
                $log_stmt->bind_param("iss", $new_user_id, $action, $ip_address);
                $log_stmt->execute();
                $log_stmt->close();
            }
            
            $stmt->close();
            echo "<script>alert('Tạo tài khoản thành công!');</script>";
            echo "<script>window.location.href = './login.php';</script>";
            exit;
        } else {
            die("Lỗi khi tạo tài khoản: " . $stmt->error);
        }
    } else {
        echo "<script>alert('Vui lòng nhập đầy đủ thông tin!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký tài khoản - BABY Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #a5b4fc 0%, #e0e7ff 50%, #a5b4fc 100%);
            background-size: 200% 200%;
            animation: gradientAnimation 15s ease infinite;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        @keyframes gradientAnimation {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .register-wrapper {
            background-color: white;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            display: flex;
            max-width: 900px;
            width: 100%;
        }

        .register-illustration {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            flex: 1;
            text-align: center;
        }

        .register-illustration img {
            width: 150px;
            margin-bottom: 1.5rem;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-15px); }
            60% { transform: translateY(-7px); }
        }

        .register-form {
            padding: 2rem;
            flex: 1;
        }

        .input-field, .select-field {
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 0.75rem;
            width: 100%;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .input-field:focus, .select-field:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .password-wrapper {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6b7280;
        }

        .register-button {
            background: linear-gradient(90deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            border-radius: 8px;
            padding: 0.75rem;
            width: 100%;
            font-size: 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .register-button:hover {
            background: linear-gradient(90deg, #4338ca 0%, #6d28d9 100%);
        }

        .register-button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .loading-spinner {
            display: none;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top: 3px solid white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: translate(-50%, -50%) rotate(0deg); }
            100% { transform: translate(-50%, -50%) rotate(360deg); }
        }

        @media (max-width: 768px) {
            .register-wrapper {
                flex-direction: column;
            }

            .register-illustration {
                padding: 1.5rem;
            }

            .register-illustration img {
                width: 100px;
            }

            .register-form {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="register-wrapper">
        <!-- Phần minh họa bên trái -->
        <div class="register-illustration">
            <img src="https://cdn-icons-png.flaticon.com/512/2922/2922510.png" alt="BABY Store Logo">
            <h2 class="text-2xl font-bold mb-2">Tham gia BABY Store</h2>
            <p class="text-sm opacity-80">Tạo tài khoản để khám phá thế giới đồ dùng trẻ em!</p>
        </div>

        <!-- Form đăng ký -->
        <div class="register-form">
            <h2 class="text-2xl font-bold text-center bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent mb-6">
                Đăng ký tài khoản
            </h2>
            <form action="" method="POST" id="registerForm">
                <div class="mb-4">
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        class="input-field" 
                        placeholder="Tên tài khoản" 
                        required
                    >
                </div>
                <div class="mb-4">
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="input-field" 
                        placeholder="Email" 
                        required
                    >
                </div>
                <div class="mb-4">
                    <input 
                        type="text" 
                        id="phone" 
                        name="phone" 
                        class="input-field" 
                        placeholder="Số điện thoại (tùy chọn)" 
                        pattern="[0-9]+"
                        title="Vui lòng nhập số hợp lệ"
                    >
                </div>
                <div class="mb-4 password-wrapper">
                    <input 
                        type="password" 
                        id="new_password" 
                        name="new_password" 
                        class="input-field" 
                        placeholder="Mật khẩu" 
                        required
                    >
                    <span class="toggle-password" onclick="togglePassword()">
                        <svg id="eyeIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                    </span>
                </div>
                <div class="mb-6">
                    <select id="role" name="role" class="select-field" required>
                        <option value="" disabled selected>Chọn vai trò</option>
                        <option value="customer">Nhân viên</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <button type="submit" class="register-button" id="registerButton">
                    <span class="button-text">Tạo tài khoản</span>
                    <div class="loading-spinner" id="loadingSpinner"></div>
                </button>
            </form>
            <div class="text-center mt-4">
                <p class="text-sm text-gray-600">
                    Đã có tài khoản? 
                    <a href="./login.php" class="text-indigo-600 hover:underline">Đăng nhập</a>
                </p>
            </div>
        </div>
    </div>

    <script>
        // Hiện/Ẩn mật khẩu
        function togglePassword() {
            const passwordInput = document.getElementById('new_password');
            const eyeIcon = document.getElementById('eyeIcon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.977 9.977 0 011.843-3.825m3.675-3.675A10.05 10.05 0 0112 5c4.478 0 8.268 2.943 9.542 7a9.977 9.977 0 01-1.843 3.825m-3.675 3.675M3 3l18 18" />
                `;
            } else {
                passwordInput.type = 'password';
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                `;
            }
        }

        // Hiệu ứng loading khi submit form
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const registerButton = document.getElementById('registerButton');
            const buttonText = registerButton.querySelector('.button-text');
            const loadingSpinner = document.getElementById('loadingSpinner');

            registerButton.disabled = true;
            buttonText.style.opacity = '0';
            loadingSpinner.style.display = 'block';

            // Simulate loading (thay bằng logic thực tế nếu cần)
            setTimeout(() => {
                registerButton.disabled = false;
                buttonText.style.opacity = '1';
                loadingSpinner.style.display = 'none';
            }, 2000);
        });
    </script>
</body>
</html>