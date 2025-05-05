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

// Cấu hình Google OAuth
const GOOGLE_CLIENT_ID = 'YOUR_GOOGLE_CLIENT_ID'; // Thay thế bằng Client ID của bạn
const GOOGLE_CLIENT_SECRET = 'YOUR_GOOGLE_CLIENT_SECRET'; // Thay thế bằng Client Secret của bạn
const GOOGLE_REDIRECT_URI = 'http://localhost/WEBQUANAOTREEM/auth/google_callback.php'; // Điều chỉnh theo URL thực tế của bạn

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

// Hàm tạo URL đăng nhập Google
function getGoogleLoginUrl() {
    $params = [
        'client_id' => GOOGLE_CLIENT_ID,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'response_type' => 'code',
        'scope' => 'email profile',
        'access_type' => 'online',
        'state' => bin2hex(random_bytes(16)) // Tạo state ngẫu nhiên để ngăn CSRF
    ];
    
    // Lưu state vào session để kiểm tra sau này
    $_SESSION['google_oauth_state'] = $params['state'];
    
    return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
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
    <title>Đăng nhập</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        /* Nền sáng với viền nhẹ */
        body {
            background-color: #f3f4f6;
        }

        /* Container với viền và bóng nhẹ */
        .login-container {
            border: 1px solid #e5e7eb;
            background-color: #ffffff;
        }

        /* Input với viền đơn giản */
        .input-field {
            transition: border-color 0.2s ease-in-out;
        }
        .input-field:focus {
            border-color: #3b82f6;
            outline: none;
        }

        /* Nút với hover đơn giản */
        .btn-primary {
            background-color: #3b82f6;
            transition: background-color 0.2s ease-in-out;
        }
        .btn-primary:hover {
            background-color: #2563eb;
        }

        /* Nút Google với icon */
        .google-btn {
            border: 1px solid #d1d5db;
            background-color: #ffffff;
            transition: background-color 0.2s ease-in-out;
        }
        .google-btn:hover {
            background-color: #f3f4f6;
        }

        /* Divider "hoặc" */
        .or-divider {
            display: flex;
            align-items: center;
            text-align: center;
            color: #6b7280;
            font-size: 0.875rem;
            margin: 1rem 0;
        }
        .or-divider::before,
        .or-divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #d1d5db;
        }
        .or-divider::before {
            margin-right: 0.5rem;
        }
        .or-divider::after {
            margin-left: 0.5rem;
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
<body class="flex items-center justify-center min-h-screen">
    <div class="login-container p-6 rounded-lg w-full max-w-md shadow-md">
        <!-- Logo and Title -->
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Đăng nhập</h2>
            <p class="mt-1 text-sm text-gray-600">Chào mừng đến với SUSU Kids!</p>
        </div>

        <!-- Thông điệp lỗi/thành công -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="bg-<?php echo strpos($_SESSION['message'], 'thành công') !== false ? 'green' : 'red'; ?>-500/10 border-l-4 border-<?php echo strpos($_SESSION['message'], 'thành công') !== false ? 'green' : 'red'; ?>-500 text-<?php echo strpos($_SESSION['message'], 'thành công') !== false ? 'green' : 'red'; ?>-700 p-3 mb-4 rounded-r">
                <p><?php echo htmlspecialchars($_SESSION['message']); ?></p>
                <?php unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>

        <!-- Google Login Button -->
        <a href="<?php echo getGoogleLoginUrl(); ?>" class="google-btn w-full py-2 px-4 rounded-md font-medium mb-4 flex items-center justify-center gap-2">
            <i class="fab fa-google text-gray-600"></i>
            <span class="text-gray-700">Đăng nhập với Google</span>
        </a>

        <div class="or-divider">hoặc</div>

        <!-- Form đăng nhập -->
        <form id="loginForm" method="POST" action="login.php" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            <div>
                <label for="identifier" class="block text-sm font-medium text-gray-700">Tên đăng nhập, email hoặc số điện thoại</label>
                <div class="relative">
                    <input type="text" id="identifier" name="identifier" 
                           class="input-field mt-1 p-2 pl-8 w-full border border-gray-300 rounded-md placeholder-gray-400 text-sm" 
                           placeholder="Tên đăng nhập, email hoặc số điện thoại" required>
                    <i class="fas fa-user absolute left-2 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                </div>
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Mật khẩu</label>
                <div class="relative">
                    <input type="password" id="password" name="password" 
                           class="input-field mt-1 p-2 pl-8 w-full border border-gray-300 rounded-md placeholder-gray-400 text-sm" 
                           placeholder="Mật khẩu" required>
                    <i class="fas fa-lock absolute left-2 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                </div>
            </div>
            <div class="flex items-center justify-between text-sm">
                <a href="forgot_password.php" class="text-blue-600 hover:text-blue-500">Quên mật khẩu?</a>
                <a href="register.php" class="text-red-500 hover:text-red-600">Đăng ký tài khoản</a>
            </div>
            <button type="submit" id="submitButton" 
                    class="btn-primary w-full text-white py-2 rounded-md font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
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
            messageDiv.className = 'text-center mb-4 text-red-600 bg-red-100 p-3 rounded';
            messageDiv.textContent = message;
            document.querySelector('form').prepend(messageDiv);
        }
    </script>
</body>
</html>