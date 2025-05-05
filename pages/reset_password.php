<?php
session_start();

// Sử dụng đường dẫn tuyệt đối để bao gồm database.php
$root_path = $_SERVER['DOCUMENT_ROOT'] . '/WEBQUANAOTREEM';
include($root_path . '/config/database.php');

// Kiểm tra kết nối cơ sở dữ liệu
if (!$conn) {
    die("Kết nối cơ sở dữ liệu thất bại: " . mysqli_connect_error());
}

// Hằng số cho thông điệp và cấu hình
const SESSION_CSRF_TOKEN = 'csrf_token';
const RESPONSE_SUCCESS = 'success';
const RESPONSE_MESSAGE = 'message';
const RESPONSE_ERRORS = 'errors';

// Hàm tạo CSRF token
function generateCsrfToken(): string {
    $token = bin2hex(random_bytes(32));
    $_SESSION[SESSION_CSRF_TOKEN] = $token;
    return $token;
}

// Hàm kiểm tra CSRF token
function verifyCsrfToken(string $token): bool {
    return isset($_SESSION[SESSION_CSRF_TOKEN]) && hash_equals($_SESSION[SESSION_CSRF_TOKEN], $token);
}

// Hàm trả về JSON response
function sendJsonResponse(bool $success, string $message, array $errors = [], int $httpCode = 200): void {
    http_response_code($httpCode);
    header('Content-Type: application/json');
    echo json_encode([
        RESPONSE_SUCCESS => $success,
        RESPONSE_MESSAGE => $message,
        RESPONSE_ERRORS => $errors
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Kiểm tra xem người dùng đã xác thực mã chưa
if (!isset($_SESSION['verified']) || $_SESSION['verified'] !== true || !isset($_SESSION['reset_user_id'])) {
    $_SESSION['message'] = 'Bạn cần xác thực mã trước khi đặt lại mật khẩu.';
    header('Location: forgot_password.php');
    exit;
}

// Xử lý đặt lại mật khẩu
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Kiểm tra CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        $_SESSION['reset_error'] = 'Yêu cầu không hợp lệ (CSRF token không khớp).';
        header('Location: reset_password.php');
        exit;
    }

    // Lấy dữ liệu từ form
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $user_id = $_SESSION['reset_user_id'];

    // Kiểm tra dữ liệu
    $errors = [];
    
    if (empty($password)) {
        $errors['password'] = 'Vui lòng nhập mật khẩu mới.';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'Mật khẩu phải có ít nhất 8 ký tự.';
    }
    
    if ($password !== $confirmPassword) {
        $errors['confirm_password'] = 'Xác nhận mật khẩu không khớp.';
    }
    
    if (!empty($errors)) {
        $_SESSION['reset_errors'] = $errors;
        header('Location: reset_password.php');
        exit;
    }

    // Cập nhật mật khẩu mới
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("UPDATE user SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hashedPassword, $user_id);
    
    if ($stmt->execute()) {
        // Xóa các thông tin đặt lại mật khẩu từ session
        unset($_SESSION['reset_user_id']);
        unset($_SESSION['verified']);
        unset($_SESSION['verification_code_sent']);
        unset($_SESSION['contact_method']);
        unset($_SESSION['masked_contact']);
        
        // Thông báo thành công
        $_SESSION['message'] = 'Đặt lại mật khẩu thành công. Vui lòng đăng nhập bằng mật khẩu mới.';
        
        // Chuyển hướng đến trang đăng nhập
        header('Location: login.php');
        exit;
    } else {
        $_SESSION['reset_error'] = 'Có lỗi xảy ra khi cập nhật mật khẩu. Vui lòng thử lại.';
        header('Location: reset_password.php');
        exit;
    }
    
    $stmt->close();
}

// Lấy thông tin người dùng để hiển thị
$user_id = $_SESSION['reset_user_id'];
$stmt = $conn->prepare("SELECT username FROM user WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$username = $user ? $user['username'] : 'người dùng';
$stmt->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lại mật khẩu - SUSU Kids</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        /* Glassmorphism effect */
        .glass-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        /* Gradient background animation */
        .animated-bg {
            background: linear-gradient(135deg, #6b7280, #3b82f6, #a855f7);
            background-size: 400%;
            animation: gradientBG 15s ease infinite;
        }
        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Button shimmer effect */
        .btn-shimmer {
            position: relative;
            overflow: hidden;
        }
        .btn-shimmer::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: 0.5s;
        }
        .btn-shimmer:hover::after {
            left: 100%;
        }

        /* Loading spinner */
        .loading::after {
            content: '';
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid #fff;
            border-top: 2px solid transparent;
            border-radius: 50%;
            animation: spin 0.5s linear infinite;
            margin-left: 8px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Slide in animation */
        .animate-slide-in {
            animation: slideIn 0.3s ease-out forwards;
        }
        @keyframes slideIn {
            0% { opacity: 0; transform: translateY(-10px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        
        /* Password strength meter */
        .strength-meter {
            height: 4px;
            width: 100%;
            background: #ddd;
            border-radius: 2px;
            margin-top: 8px;
        }
        
        .strength-meter-fill {
            height: 100%;
            border-radius: 2px;
            transition: width 0.3s, background-color 0.3s;
        }
        
        .strength-meter-fill.weak { background-color: #ff4d4d; width: 33.33%; }
        .strength-meter-fill.medium { background-color: #ffa700; width: 66.66%; }
        .strength-meter-fill.strong { background-color: #32cd32; width: 100%; }
    </style>
</head>
<body class="animated-bg flex items-center justify-center min-h-screen">
    <div class="glass-container p-8 rounded-3xl w-full max-w-md shadow-2xl transform transition-all hover:scale-[1.01] hover:shadow-3xl">
        <!-- Logo and Title -->
        <div class="text-center mb-8">
            <h2 class="text-3xl font-extrabold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">Đặt lại mật khẩu</h2>
            <p class="mt-2 text-sm text-gray-300">Xin chào <span class="font-semibold"><?php echo htmlspecialchars($username); ?></span>, hãy tạo mật khẩu mới cho tài khoản của bạn.</p>
        </div>

        <!-- Thông báo lỗi -->
        <?php if (isset($_SESSION['reset_error'])): ?>
            <div class="bg-red-500/10 border-l-4 border-red-500 text-red-200 p-4 mb-6 rounded-r-lg animate-slide-in">
                <p><?php echo htmlspecialchars($_SESSION['reset_error']); ?></p>
                <?php unset($_SESSION['reset_error']); ?>
            </div>
        <?php endif; ?>

        <!-- Form đặt lại mật khẩu -->
        <form id="resetPasswordForm" method="POST" action="reset_password.php" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            
            <div>
                <label for="password" class="block text-sm font-medium text-gray-200">Mật khẩu mới</label>
                <div class="relative">
                    <input type="password" id="password" name="password" 
                           class="mt-1 p-3 pl-10 w-full bg-white/80 border border-gray-300/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-300 ease-in-out placeholder-gray-400" 
                           placeholder="Nhập mật khẩu mới" required minlength="8">
                    <i class="fas fa-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <button type="button" id="togglePassword" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <?php if (isset($_SESSION['reset_errors']['password'])): ?>
                    <p class="mt-1 text-sm text-red-400"><?php echo htmlspecialchars($_SESSION['reset_errors']['password']); ?></p>
                <?php endif; ?>
                
                <!-- Hiển thị độ mạnh mật khẩu -->
                <div class="mt-2">
                    <div class="strength-meter">
                        <div id="strength-meter-fill" class="strength-meter-fill"></div>
                    </div>
                    <p id="password-strength-text" class="mt-1 text-xs text-gray-400">Độ mạnh mật khẩu</p>
                </div>
            </div>
            
            <div>
                <label for="confirm_password" class="block text-sm font-medium text-gray-200">Xác nhận mật khẩu mới</label>
                <div class="relative">
                    <input type="password" id="confirm_password" name="confirm_password" 
                           class="mt-1 p-3 pl-10 w-full bg-white/80 border border-gray-300/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-300 ease-in-out placeholder-gray-400" 
                           placeholder="Nhập lại mật khẩu mới" required>
                    <i class="fas fa-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <button type="button" id="toggleConfirmPassword" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <?php if (isset($_SESSION['reset_errors']['confirm_password'])): ?>
                    <p class="mt-1 text-sm text-red-400"><?php echo htmlspecialchars($_SESSION['reset_errors']['confirm_password']); ?></p>
                <?php endif; ?>
                
                <!-- Thông báo trạng thái khớp mật khẩu -->
                <p id="password-match" class="mt-1 text-xs hidden"></p>
            </div>

            <button type="submit" id="resetButton" 
                    class="btn-shimmer w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white p-3 rounded-lg font-semibold hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-300 ease-in-out transform hover:-translate-y-1">
                Đặt lại mật khẩu
            </button>
        </form>
    </div>

    <script>
        // Xóa thông báo lỗi sau khi hiển thị
        <?php unset($_SESSION['reset_errors']); ?>
        
        // Hiển thị/ẩn mật khẩu
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
        
        document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
            const confirmPasswordInput = document.getElementById('confirm_password');
            const icon = this.querySelector('i');
            
            if (confirmPasswordInput.type === 'password') {
                confirmPasswordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                confirmPasswordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
        
        // Kiểm tra độ mạnh mật khẩu
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthMeter = document.getElementById('strength-meter-fill');
            const strengthText = document.getElementById('password-strength-text');
            
            // Xóa các lớp trước đó
            strengthMeter.classList.remove('weak', 'medium', 'strong');
            
            // Kiểm tra độ mạnh
            let strength = 0;
            if (password.length >= 8) strength++;
            if (password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^A-Za-z0-9]/)) strength++;
            
            // Cập nhật hiển thị
            if (password.length === 0) {
                strengthMeter.style.width = '0';
                strengthText.textContent = 'Độ mạnh mật khẩu';
                strengthText.className = 'mt-1 text-xs text-gray-400';
            } else if (strength < 2) {
                strengthMeter.classList.add('weak');
                strengthText.textContent = 'Yếu';
                strengthText.className = 'mt-1 text-xs text-red-400';
            } else if (strength < 4) {
                strengthMeter.classList.add('medium');
                strengthText.textContent = 'Trung bình';
                strengthText.className = 'mt-1 text-xs text-yellow-400';
            } else {
                strengthMeter.classList.add('strong');
                strengthText.textContent = 'Mạnh';
                strengthText.className = 'mt-1 text-xs text-green-400';
            }
            
            // Kiểm tra khớp mật khẩu
            checkPasswordMatch();
        });
        
        // Kiểm tra mật khẩu khớp nhau
        function checkPasswordMatch() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const matchStatus = document.getElementById('password-match');
            
            if (confirmPassword.length === 0) {
                matchStatus.classList.add('hidden');
                return;
            }
            
            matchStatus.classList.remove('hidden');
            
            if (password === confirmPassword) {
                matchStatus.textContent = 'Mật khẩu khớp';
                matchStatus.className = 'mt-1 text-xs text-green-400';
            } else {
                matchStatus.textContent = 'Mật khẩu không khớp';
                matchStatus.className = 'mt-1 text-xs text-red-400';
            }
        }
        
        document.getElementById('confirm_password').addEventListener('input', checkPasswordMatch);
        
        // Xử lý form đặt lại mật khẩu
        document.getElementById('resetPasswordForm').addEventListener('submit', function(event) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            let hasError = false;
            
            // Kiểm tra mật khẩu
            if (password.length < 8) {
                event.preventDefault();
                alert('Mật khẩu phải có ít nhất 8 ký tự.');
                hasError = true;
            }
            
            // Kiểm tra mật khẩu khớp nhau
            if (password !== confirmPassword) {
                event.preventDefault();
                alert('Xác nhận mật khẩu không khớp.');
                hasError = true;
            }
            
            if (!hasError) {
                const resetButton = document.getElementById('resetButton');
                resetButton.disabled = true;
                resetButton.classList.add('loading');
                resetButton.textContent = 'Đang xử lý';
            }
        });
    </script>
</body>
</html>