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

// Xử lý logic quên mật khẩu nếu yêu cầu là POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Kiểm tra CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        sendJsonResponse(false, 'Yêu cầu không hợp lệ (CSRF token không khớp).', [], 403);
    }

    // Lấy và làm sạch dữ liệu đầu vào
    $identifier = trim($_POST['identifier'] ?? '');

    // Kiểm tra dữ liệu đầu vào
    $errors = [];
    if (empty($identifier)) {
        $errors['identifier'] = 'Vui lòng nhập email, số điện thoại hoặc tên đăng nhập.';
    }
    if (!empty($errors)) {
        sendJsonResponse(false, 'Dữ liệu đầu vào không hợp lệ.', $errors, 400);
    }

    // Kiểm tra định dạng số điện thoại (nếu identifier là số điện thoại)
    if (is_numeric($identifier) && !preg_match('/^[0-9]{10}$/', $identifier)) {
        sendJsonResponse(false, 'Số điện thoại không hợp lệ. Vui lòng nhập 10 chữ số.', [], 400);
    }

    // Kiểm tra xem identifier tồn tại trong cơ sở dữ liệu
    $stmt = $conn->prepare("SELECT id, username, email FROM user WHERE username = ? OR email = ? OR phone = ?");
    if (!$stmt) {
        sendJsonResponse(false, 'Lỗi hệ thống. Vui lòng thử lại sau.', [], 500);
    }
    $stmt->bind_param("sss", $identifier, $identifier, $identifier);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        // Tạo token đặt lại mật khẩu (mock logic)
        $user = $result->fetch_assoc();
        $resetToken = bin2hex(random_bytes(16));
        $resetLink = "http://yourdomain.com/reset_password.php?token=$resetToken";

        // Lưu token vào database (giả lập - bạn cần thêm bảng reset_tokens)
        // $stmt = $conn->prepare("INSERT INTO reset_tokens (user_id, token, expiry) VALUES (?, ?, ?)");
        // $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        // $stmt->bind_param("iss", $user['id'], $resetToken, $expiry);
        // $stmt->execute();

        // Gửi email (giả lập - bạn cần tích hợp mailer như PHPMailer)
        // mail($user['email'], "Đặt lại mật khẩu", "Nhấp vào liên kết để đặt lại mật khẩu: $resetLink");

        sendJsonResponse(true, 'Liên kết đặt lại mật khẩu đã được gửi đến email hoặc số điện thoại của bạn.');
    } else {
        sendJsonResponse(false, 'Email, số điện thoại hoặc tên đăng nhập không tồn tại.', [], 404);
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu - BABY Store</title>
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
            <h2 class="text-3xl font-extrabold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">Quên mật khẩu</h2>
            <p class="mt-2 text-sm text-gray-300">Nhập thông tin để nhận liên kết đặt lại mật khẩu.</p>
        </div>

        <!-- Thông điệp lỗi/thành công -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="bg-<?php echo strpos($_SESSION['message'], 'thành công') !== false ? 'green' : 'red'; ?>-500/10 border-l-4 border-<?php echo strpos($_SESSION['message'], 'thành công') !== false ? 'green' : 'red'; ?>-500 text-<?php echo strpos($_SESSION['message'], 'thành công') !== false ? 'green' : 'red'; ?>-200 p-4 mb-6 rounded-r-lg animate-slide-in">
                <p><?php echo htmlspecialchars($_SESSION['message']); ?></p>
                <?php unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>

        <!-- Form quên mật khẩu -->
        <form id="forgotPasswordForm" method="POST" action="forgot_password.php" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            <div>
                <label for="identifier" class="block text-sm font-medium text-gray-200">Email, số điện thoại hoặc tên đăng nhập</label>
                <div class="relative">
                    <input type="text" id="identifier" name="identifier" 
                           class="mt-1 p-3 pl-10 w-full bg-white/80 border border-gray-300/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-300 ease-in-out placeholder-gray-400" 
                           placeholder="Nhập email, số điện thoại hoặc tên đăng nhập" required>
                    <i class="fas fa-envelope absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 group-focus-within:text-blue-500 transition-colors"></i>
                </div>
            </div>
            <div class="flex items-center justify-between text-sm">
                <a href="login.php" class="text-blue-400 hover:text-blue-300 transition duration-200">Quay lại đăng nhập</a>
                <a href="register.php" class="text-blue-400 hover:text-blue-300 transition duration-200">Đăng ký tài khoản</a>
            </div>
            <button type="submit" id="submitButton" 
                    class="btn-shimmer w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white p-3 rounded-lg font-semibold hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-300 ease-in-out transform hover:-translate-y-1">
                Gửi liên kết đặt lại
            </button>
        </form>
    </div>

    <script>
        document.getElementById('forgotPasswordForm').addEventListener('submit', async function(event) {
            event.preventDefault();

            const submitButton = document.getElementById('submitButton');
            submitButton.disabled = true;
            submitButton.classList.add('loading');
            submitButton.textContent = 'Đang xử lý';

            const formData = new FormData(this);
            try {
                const response = await fetch('forgot_password.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    const messageDiv = document.createElement('div');
                    messageDiv.className = 'bg-green-500/10 border-l-4 border-green-500 text-green-200 p-4 mb-6 rounded-r-lg animate-slide-in';
                    messageDiv.textContent = result.message;
                    document.querySelector('form').prepend(messageDiv);
                } else {
                    const messageDiv = document.createElement('div');
                    messageDiv.className = 'bg-red-500/10 border-l-4 border-red-500 text-red-200 p-4 mb-6 rounded-r-lg animate-slide-in';
                    messageDiv.textContent = result.message || 'Đã có lỗi xảy ra. Vui lòng thử lại.';
                    document.querySelector('form').prepend(messageDiv);
                }
            } catch (error) {
                const messageDiv = document.createElement('div');
                messageDiv.className = 'bg-red-500/10 border-l-4 border-red-500 text-red-200 p-4 mb-6 rounded-r-lg animate-slide-in';
                messageDiv.textContent = 'Lỗi kết nối. Vui lòng thử lại.';
                document.querySelector('form').prepend(messageDiv);
            } finally {
                submitButton.disabled = false;
                submitButton.classList.remove('loading');
                submitButton.textContent = 'Gửi liên kết đặt lại';
            }
        });
    </script>
</body>
</html>