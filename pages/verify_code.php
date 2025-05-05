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

// Kiểm tra nếu người dùng chưa được xác định
if (!isset($_SESSION['reset_user_id'])) {
    $_SESSION['message'] = 'Phiên làm việc đã hết hạn. Vui lòng thử lại từ đầu.';
    header('Location: forgot_password.php');
    exit;
}

// Xử lý xác thực mã
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Kiểm tra CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        $_SESSION['message'] = 'Yêu cầu không hợp lệ (CSRF token không khớp).';
        header('Location: forgot_password.php');
        exit;
    }

    // Lấy mã xác thực từ form
    $verificationCode = trim($_POST['verification_code'] ?? '');
    $user_id = $_SESSION['reset_user_id'];

    // Kiểm tra mã xác thực
    if (empty($verificationCode) || strlen($verificationCode) !== 6 || !is_numeric($verificationCode)) {
        $_SESSION['verify_error'] = 'Mã xác thực không hợp lệ. Vui lòng nhập đúng mã 6 số.';
        header('Location: verify_code.php');
        exit;
    }

    // Kiểm tra mã xác thực trong cơ sở dữ liệu
    $stmt = $conn->prepare("SELECT * FROM reset_codes WHERE user_id = ? AND code = ? AND expiry > NOW() AND used = 0");
    $stmt->bind_param("is", $user_id, $verificationCode);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        // Mã xác thực hợp lệ
        $reset_code = $result->fetch_assoc();
        
        // Đánh dấu mã đã được sử dụng
        $updateStmt = $conn->prepare("UPDATE reset_codes SET used = 1 WHERE id = ?");
        $updateStmt->bind_param("i", $reset_code['id']);
        $updateStmt->execute();
        $updateStmt->close();
        
        // Lưu trạng thái đã xác thực vào session
        $_SESSION['verified'] = true;
        
        // Chuyển hướng đến trang đổi mật khẩu mới
        header('Location: reset_password.php');
        exit;
    } else {
        $_SESSION['verify_error'] = 'Mã xác thực không đúng hoặc đã hết hạn. Vui lòng kiểm tra lại hoặc yêu cầu mã mới.';
        header('Location: verify_code.php');
        exit;
    }

    $stmt->close();
}

// Kiểm tra xem người dùng đã được gửi mã xác thực chưa
if (!isset($_SESSION['verification_code_sent']) || !$_SESSION['verification_code_sent']) {
    $_SESSION['message'] = 'Bạn cần yêu cầu mã xác thực trước.';
    header('Location: forgot_password.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác thực mã - SUSU Kids</title>
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
        
        /* Verification code input styling */
        .code-input {
            letter-spacing: 0.5em;
            text-align: center;
            font-size: 1.5em;
        }
    </style>
</head>
<body class="animated-bg flex items-center justify-center min-h-screen">
    <div class="glass-container p-8 rounded-3xl w-full max-w-md shadow-2xl transform transition-all hover:scale-[1.01] hover:shadow-3xl">
        <!-- Logo and Title -->
        <div class="text-center mb-8">
            <h2 class="text-3xl font-extrabold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">Xác thực mã</h2>
            <p class="mt-2 text-sm text-gray-300">Nhập mã xác thực đã được gửi đến bạn.</p>
        </div>

        <!-- Thông báo lỗi/thành công -->
        <?php if (isset($_SESSION['verify_error'])): ?>
            <div class="bg-red-500/10 border-l-4 border-red-500 text-red-200 p-4 mb-6 rounded-r-lg animate-slide-in">
                <p><?php echo htmlspecialchars($_SESSION['verify_error']); ?></p>
                <?php unset($_SESSION['verify_error']); ?>
            </div>
        <?php endif; ?>

        <!-- Thông tin phương thức liên hệ -->
        <div class="bg-blue-500/10 border-l-4 border-blue-500 text-blue-200 p-4 mb-6 rounded-r-lg">
            <p>Mã xác thực đã được gửi đến <?php echo isset($_SESSION['contact_method']) ? $_SESSION['contact_method'] . ' ' . $_SESSION['masked_contact'] : 'email/số điện thoại của bạn'; ?>. Vui lòng kiểm tra và nhập mã bên dưới.</p>
        </div>

        <!-- Form xác thực mã -->
        <form id="verifyCodeForm" method="POST" action="verify_code.php" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            <div>
                <label for="verificationCode" class="block text-sm font-medium text-gray-200">Mã xác thực 6 số</label>
                <div class="relative">
                    <input type="text" id="verificationCode" name="verification_code" 
                           class="mt-1 p-3 pl-10 w-full bg-white/80 border border-gray-300/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-300 ease-in-out placeholder-gray-400 code-input" 
                           placeholder="______" required maxlength="6" minlength="6" inputmode="numeric" pattern="[0-9]{6}">
                    <i class="fas fa-key absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                </div>
            </div>
            <div class="text-center">
                <p class="text-sm text-gray-400">Chưa nhận được mã? <a href="forgot_password.php" class="text-blue-400 hover:text-blue-300 transition duration-200">Gửi lại</a></p>
                <p class="text-sm text-gray-400 mt-2">Mã xác thực có hiệu lực trong 15 phút</p>
            </div>
            <button type="submit" id="verifyButton" 
                    class="btn-shimmer w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white p-3 rounded-lg font-semibold hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-300 ease-in-out transform hover:-translate-y-1">
                Xác thực mã
            </button>
            <div class="text-center mt-4">
                <a href="forgot_password.php" class="text-sm text-gray-400 hover:text-gray-300 transition duration-200">
                    <i class="fas fa-arrow-left mr-1"></i> Quay lại
                </a>
            </div>
        </form>
    </div>

    <script>
        // Tự động focus vào ô nhập mã
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('verificationCode').focus();
        });

        // Chỉ cho phép nhập số
        document.getElementById('verificationCode').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        // Xử lý form xác thực mã
        document.getElementById('verifyCodeForm').addEventListener('submit', function(event) {
            const verifyButton = document.getElementById('verifyButton');
            verifyButton.disabled = true;
            verifyButton.classList.add('loading');
            verifyButton.textContent = 'Đang xác thực';
        });
    </script>
</body>
</html>