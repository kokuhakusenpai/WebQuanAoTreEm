<?php
session_start();
ob_start();
include 'config/database.php';

// Kiểm tra kết nối cơ sở dữ liệu
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Hàm ghi log thao tác
function logAction($user_id, $action) {
    global $conn;
    $ip_address = $_SERVER['REMOTE_ADDR'];
    
    $query = "INSERT INTO user_log (user_id, action, ip_address) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        error_log("Prepare failed for user_log: " . $conn->error);
        return false;
    }
    $stmt->bind_param("iss", $user_id, $action, $ip_address);
    $result = $stmt->execute();
    if (!$result) {
        error_log("Execute failed for user_log: " . $stmt->error);
    }
    $stmt->close();
    return $result;
}

$error_message = null; // Biến lưu thông báo lỗi

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Kiểm tra ký tự hợp lệ cho username, hỗ trợ ký tự Unicode (bao gồm tiếng Việt)
    if (!preg_match('/^[\p{L}0-9_]+$/u', $username)) {
        $error_message = "Tài khoản chứa ký tự không hợp lệ!";
    } else {
        $sql = "SELECT * FROM user WHERE username = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            error_log("Prepare failed for user query: " . $conn->error);
            $error_message = "Lỗi hệ thống. Vui lòng thử lại sau.";
        } else {
            $stmt->bind_param("s", $username);
            if (!$stmt->execute()) {
                error_log("Execute failed for user query: " . $stmt->error);
                $error_message = "Lỗi hệ thống. Vui lòng thử lại sau.";
            } else {
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    $user = $result->fetch_assoc();

                    // Kiểm tra mật khẩu
                    if (password_verify($password, $user['password'])) {
                        // Đăng nhập thành công
                        $_SESSION['admin_logged_in'] = true;
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['role'] = $user['role'];

                        // Ghi log thao tác
                        logAction($user['id'], 'Đăng nhập');

                        // Đảm bảo không có output trước header
                        ob_end_clean();
                        header("Location: ./index.php");
                        exit;
                    } else {
                        $error_message = "Sai tài khoản hoặc mật khẩu!";
                    }
                } else {
                    $error_message = "Tài khoản không tồn tại!";
                }
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập Admin</title>
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

        .login-wrapper {
            background-color: white;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            display: flex;
            max-width: 900px;
            width: 100%;
        }

        .login-illustration {
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

        .login-illustration img {
            width: 150px;
            margin-bottom: 1.5rem;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-15px); }
            60% { transform: translateY(-7px); }
        }

        .login-form {
            padding: 2rem;
            flex: 1;
        }

        .input-field {
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 0.75rem;
            width: 100%;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .input-field:focus {
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

        .login-button {
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

        .login-button:hover {
            background: linear-gradient(90deg, #4338ca 0%, #6d28d9 100%);
        }

        .login-button:disabled {
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
            .login-wrapper {
                flex-direction: column;
            }

            .login-illustration {
                padding: 1.5rem;
            }

            .login-illustration img {
                width: 100px;
            }

            .login-form {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <!-- Phần minh họa bên trái -->
        <div class="login-illustration">
            <img src="https://cdn-icons-png.flaticon.com/512/2922/2922510.png" alt="SUSU Kids Logo">
            <h2 class="text-2xl font-bold mb-2">Chào mừng đến với SUSU Hub</h2>
            <p class="text-sm opacity-80">Đăng nhập để quản lý cửa hàng của bạn!</p>
        </div>

        <!-- Form đăng nhập -->
        <div class="login-form">
            <h2 class="text-2xl font-bold text-center bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent mb-6">
                Đăng nhập 
            </h2>
            <?php if (isset($error_message)) : ?>
                <div class="error-message text-red-500 text-center mb-4 animate-pulse">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            <form method="POST" id="loginForm">
                <div class="mb-4">
                    <input 
                        type="text" 
                        name="username" 
                        class="input-field" 
                        placeholder="Tài khoản" 
                        required
                    >
                </div>
                <div class="mb-6 password-wrapper">
                    <input 
                        type="password" 
                        name="password" 
                        id="password" 
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
                <button type="submit" class="login-button" id="loginButton">
                    <span class="button-text">Đăng nhập</span>
                    <div class="loading-spinner" id="loadingSpinner"></div>
                </button>
            </form>
            <div class="text-center mt-4">
                <a href="forgot_password.php" class="text-indigo-600 hover:underline text-sm">
                    Quên mật khẩu?
                </a>
            </div>
        </div>
    </div>

    <script>
        // Hiện/Ẩn mật khẩu
        function togglePassword() {
            const passwordInput = document.getElementById('password');
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
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const loginButton = document.getElementById('loginButton');
            const buttonText = loginButton.querySelector('.button-text');
            const loadingSpinner = document.getElementById('loadingSpinner');

            loginButton.disabled = true;
            buttonText.style.opacity = '0';
            loadingSpinner.style.display = 'block';

            // Simulate loading (thay bằng logic thực tế nếu cần)
            setTimeout(() => {
                loginButton.disabled = false;
                buttonText.style.opacity = '1';
                loadingSpinner.style.display = 'none';
            }, 2000);
        });
    </script>
</body>
</html>