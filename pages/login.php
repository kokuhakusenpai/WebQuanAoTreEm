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
const LOGIN_ATTEMPT_LIMIT = 5;
const LOGIN_LOCKOUT_TIME = 900; // 15 phút
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

// Hàm kiểm tra giới hạn số lần thử đăng nhập
function checkLoginAttempts(string $identifier): array {
    $key = 'login_attempts_' . md5($identifier);
    $attempts = $_SESSION[$key] ?? 0;
    $lockoutTime = $_SESSION[$key . '_lockout'] ?? 0;

    if ($lockoutTime > time()) {
        return [
            'allowed' => false,
            'message' => sprintf('Tài khoản bị khóa tạm thời. Thử lại sau %d phút.', ceil(($lockoutTime - time()) / 60))
        ];
    }

    if ($attempts >= LOGIN_ATTEMPT_LIMIT) {
        $_SESSION[$key . '_lockout'] = time() + LOGIN_LOCKOUT_TIME;
        unset($_SESSION[$key]);
        return [
            'allowed' => false,
            'message' => 'Quá nhiều lần thử đăng nhập. Tài khoản bị khóa tạm thời trong 15 phút.'
        ];
    }

    return ['allowed' => true];
}

// Hàm trả về JSON response
function sendJsonResponse(bool $success, string $message, array $errors = [], int $httpCode = 200): void {
    http_response_code($httpCode);
    header('Content-Type: application/json');
    echo json_encode([
        RESPONSE_SUCCESS => $success,
        RESPONSE_MESSAGE => $message,
        RESPONSE_ERRORS => $errors,
        'debug' => [
            'method' => $_SERVER['REQUEST_METHOD'],
            'post_data' => $_POST
        ]
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Xử lý logic đăng nhập nếu yêu cầu là POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Kiểm tra CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        sendJsonResponse(false, 'Yêu cầu không hợp lệ (CSRF token không khớp).', [], 403);
    }

    // Lấy và làm sạch dữ liệu đầu vào
    $identifier = trim($_POST['identifier'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Kiểm tra dữ liệu đầu vào
    $errors = [];
    if (empty($identifier)) {
        $errors['identifier'] = 'Vui lòng nhập tên đăng nhập hoặc email.';
    }
    if (empty($password)) {
        $errors['password'] = 'Vui lòng nhập mật khẩu.';
    }
    if (!empty($errors)) {
        sendJsonResponse(false, 'Dữ liệu đầu vào không hợp lệ.', $errors, 400);
    }

    // Kiểm tra định dạng số điện thoại (nếu identifier là số điện thoại)
    if (is_numeric($identifier) && !preg_match('/^[0-9]{10}$/', $identifier)) {
        sendJsonResponse(false, 'Số điện thoại không hợp lệ. Vui lòng nhập 10 chữ số.', [], 400);
    }

    // Kiểm tra giới hạn số lần thử đăng nhập
    $loginCheck = checkLoginAttempts($identifier);
    if (!$loginCheck['allowed']) {
        sendJsonResponse(false, $loginCheck['message'], [], 429);
    }

    // Truy vấn database - Đã sửa lại để phù hợp với cấu trúc bảng user
    $stmt = $conn->prepare("SELECT id, username, password, role FROM user WHERE (username = ? OR email = ? OR phone = ?)");
    if (!$stmt) {
        sendJsonResponse(false, 'Lỗi hệ thống. Vui lòng thử lại sau.', [], 500);
    }
    $stmt->bind_param("sss", $identifier, $identifier, $identifier);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Kiểm tra xem mật khẩu đã băm hay chưa
        if (password_verify($password, $user['password']) || $password === $user['password']) {
            // Đăng nhập thành công
            $_SESSION['id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Ghi log đăng nhập
            $logStmt = $conn->prepare("INSERT INTO user_log (user_id, action, ip_address) VALUES (?, 'Đăng nhập', ?)");
            if ($logStmt) {
                $ip = $_SERVER['REMOTE_ADDR'];
                $logStmt->bind_param("is", $user['id'], $ip);
                $logStmt->execute();
                $logStmt->close();
            }

            // Xóa thông tin số lần thử
            unset($_SESSION['login_attempts_' . md5($identifier)]);
            unset($_SESSION['login_attempts_' . md5($identifier) . '_lockout']);

            sendJsonResponse(true, 'Đăng nhập thành công.');
        } else {
            // Mật khẩu sai
            $_SESSION['login_attempts_' . md5($identifier)] = ($_SESSION['login_attempts_' . md5($identifier)] ?? 0) + 1;
            sendJsonResponse(false, 'Mật khẩu không chính xác.', [], 401);
        }
    } else {
        // Tài khoản không tồn tại
        $_SESSION['login_attempts_' . md5($identifier)] = ($_SESSION['login_attempts_' . md5($identifier)] ?? 0) + 1;
        sendJsonResponse(false, 'Tên đăng nhập, email hoặc số điện thoại không tồn tại.', [], 401);
    }

    $stmt->close();
    $conn->close();
}

// Nếu không phải POST (ví dụ: GET), hiển thị form đăng nhập
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - BABY Store</title>
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
    </style>
</head>
<body class="animated-bg flex items-center justify-center min-h-screen">
    <div class="glass-container p-8 rounded-3xl w-full max-w-md shadow-2xl transform transition-all hover:scale-[1.01] hover:shadow-3xl">
        <!-- Logo and Title -->
        <div class="text-center mb-8">
            <h2 class="text-3xl font-extrabold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">Đăng nhập</h2>
            <p class="mt-2 text-sm text-gray-300">Đăng nhập để khám phá BABY Store!</p>
        </div>

        <!-- Thông điệp lỗi/thành công -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="bg-<?php echo strpos($_SESSION['message'], 'thành công') !== false ? 'green' : 'red'; ?>-500/10 border-l-4 border-<?php echo strpos($_SESSION['message'], 'thành công') !== false ? 'green' : 'red'; ?>-500 text-<?php echo strpos($_SESSION['message'], 'thành công') !== false ? 'green' : 'red'; ?>-200 p-4 mb-6 rounded-r-lg animate-slide-in">
                <p><?php echo htmlspecialchars($_SESSION['message']); ?></p>
                <?php unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>

        <!-- Form đăng nhập -->
        <form id="loginForm" method="POST" action="login.php" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            <div>
                <label for="identifier" class="block text-sm font-medium text-gray-200">Tên đăng nhập, email hoặc số điện thoại</label>
                <div class="relative">
                    <input type="text" id="identifier" name="identifier" 
                           class="mt-1 p-3 pl-10 w-full bg-white/80 border border-gray-300/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-300 ease-in-out placeholder-gray-400" 
                           placeholder="Nhập tên đăng nhập, email hoặc số điện thoại" required>
                    <i class="fas fa-user absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 group-focus-within:text-blue-500 transition-colors"></i>
                </div>
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-200">Mật khẩu</label>
                <div class="relative">
                    <input type="password" id="password" name="password" 
                           class="mt-1 p-3 pl-10 w-full bg-white/80 border border-gray-300/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-300 ease-in-out placeholder-gray-400" 
                           placeholder="Nhập mật khẩu" required>
                    <i class="fas fa-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 group-focus-within:text-blue-500 transition-colors"></i>
                </div>
            </div>
            <div class="flex items-center justify-between text-sm">
                <a href="forgot_password.php" class="text-blue-400 hover:text-blue-300 transition duration-200">Quên mật khẩu?</a>
                <a href="register.php" class="text-red-400 hover:text-blue-300 transition duration-200">Đăng ký tài khoản</a>
            </div>
            <button type="submit" id="submitButton" 
                    class="btn-shimmer w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white p-3 rounded-lg font-semibold hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-300 ease-in-out transform hover:-translate-y-1">
                Đăng nhập
            </button>
        </form>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', async function(event) {
            event.preventDefault();

            const submitButton = document.getElementById('submitButton');
            submitButton.disabled = true;
            submitButton.classList.add('loading');
            submitButton.textContent = 'Đang xử lý';

            const formData = new FormData(this);
            try {
                const response = await fetch('login.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    window.location.href = '../index.php';
                } else {
                    const message = result.message || 'Đã có lỗi xảy ra. Vui lòng thử lại.';
                    window.location.href = 'login.php?message=' + encodeURIComponent(message);
                }
            } catch (error) {
                window.location.href = 'login.php?message=' + encodeURIComponent('Lỗi kết nối. Vui lòng thử lại.');
            } finally {
                submitButton.disabled = false;
                submitButton.classList.remove('loading');
                submitButton.textContent = 'Đăng nhập';
            }
        });

        // Lấy thông điệp từ URL (nếu có) và hiển thị
        const urlParams = new URLSearchParams(window.location.search);
        const message = urlParams.get('message');
        if (message) {
            const messageDiv = document.createElement('p');
            messageDiv.className = 'text-center mb-4 text-red-200 bg-red-500/10 p-4 rounded-lg';
            messageDiv.textContent = message;
            document.querySelector('form').prepend(messageDiv);
        }
    </script>
</body>
</html>