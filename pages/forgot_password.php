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

// Hàm tạo mã xác thực ngẫu nhiên
function generateVerificationCode(int $length = 6): string {
    return substr(str_shuffle("0123456789"), 0, $length);
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
    $stmt = $conn->prepare("SELECT id, username, email, phone FROM user WHERE username = ? OR email = ? OR phone = ?");
    if (!$stmt) {
        sendJsonResponse(false, 'Lỗi hệ thống. Vui lòng thử lại sau.', [], 500);
    }
    $stmt->bind_param("sss", $identifier, $identifier, $identifier);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        // Lấy thông tin người dùng
        $user = $result->fetch_assoc();
        $user_id = $user['id'];
        $email = $user['email'];
        $phone = $user['phone'];
        
        // Tạo mã xác thực 6 chữ số
        $verificationCode = generateVerificationCode(6);
        $expiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));
        
        // Lưu mã xác thực vào cơ sở dữ liệu
        // Đầu tiên, kiểm tra xem bảng reset_codes đã tồn tại chưa
        $tableExists = $conn->query("SHOW TABLES LIKE 'reset_codes'")->num_rows > 0;
        
        if (!$tableExists) {
            // Tạo bảng reset_codes nếu chưa tồn tại
            $conn->query("CREATE TABLE reset_codes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                code VARCHAR(10) NOT NULL,
                expiry DATETIME NOT NULL,
                used TINYINT(1) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
            )");
        }
        
        // Xóa các mã cũ của người dùng này (nếu có)
        $deleteStmt = $conn->prepare("DELETE FROM reset_codes WHERE user_id = ?");
        $deleteStmt->bind_param("i", $user_id);
        $deleteStmt->execute();
        $deleteStmt->close();
        
        // Thêm mã mới vào cơ sở dữ liệu
        $insertStmt = $conn->prepare("INSERT INTO reset_codes (user_id, code, expiry) VALUES (?, ?, ?)");
        $insertStmt->bind_param("iss", $user_id, $verificationCode, $expiry);
        $insertStmt->execute();
        $insertStmt->close();
        
        // Lưu user_id vào session để dùng cho bước xác thực
        $_SESSION['reset_user_id'] = $user_id;
        
        // Xác định phương thức liên hệ (email hoặc điện thoại)
        $contactMethod = '';
        $maskedContact = '';
        
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL) || (!is_numeric($identifier) && $email)) {
            // Gửi qua email
            $contactMethod = 'email';
            $sendTo = $email;
            
            // Ẩn một phần email
            $emailParts = explode('@', $email);
            $username = $emailParts[0];
            $domain = $emailParts[1];
            
            $maskedUsername = substr($username, 0, 2) . str_repeat('*', strlen($username) - 2);
            $maskedContact = $maskedUsername . '@' . $domain;
            
            // Gửi email (giả lập - bạn cần tích hợp mailer như PHPMailer)
            // $subject = "Mã xác thực đặt lại mật khẩu";
            // $message = "Mã xác thực của bạn là: $verificationCode. Mã có hiệu lực trong 15 phút.";
            // mail($email, $subject, $message);
        } else {
            // Gửi qua điện thoại
            $contactMethod = 'phone';
            $sendTo = $phone;
            
            // Ẩn một phần số điện thoại
            $maskedContact = substr($phone, 0, 3) . '****' . substr($phone, -3);
            
            // Gửi SMS (giả lập - bạn cần tích hợp dịch vụ SMS)
            // $message = "Mã xác thực của bạn là: $verificationCode. Mã có hiệu lực trong 15 phút.";
            // sendSMS($phone, $message);
        }
        
        // Lưu thông tin về hình thức gửi để hiển thị
        $_SESSION['contact_method'] = $contactMethod;
        $_SESSION['masked_contact'] = $maskedContact;
        
        // Giả lập gửi mã thành công (trong thực tế, bạn sẽ tích hợp dịch vụ email/SMS)
        $_SESSION['verification_code_sent'] = true;
        
        sendJsonResponse(true, 'Mã xác thực đã được gửi đến ' . $contactMethod . ' ' . $maskedContact);
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
    <title>Quên mật khẩu - SUSU Kids</title>
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
    </style>
</head>
<body class="animated-bg flex items-center justify-center min-h-screen">
    <div class="glass-container p-8 rounded-3xl w-full max-w-md shadow-2xl transform transition-all hover:scale-[1.01] hover:shadow-3xl">
        <!-- Logo and Title -->
        <div class="text-center mb-8">
            <h2 class="text-3xl font-extrabold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">Quên mật khẩu</h2>
            <p class="mt-2 text-sm text-gray-300">Nhập thông tin để nhận mã xác thực đặt lại mật khẩu.</p>
        </div>

        <!-- Thông báo kết quả -->
        <div id="messageContainer" class="mb-6"></div>

        <!-- Form quên mật khẩu (Hiển thị ban đầu) -->
        <div id="step1Container">
            <form id="forgotPasswordForm" method="POST" action="forgot_password.php" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                <div>
                    <label for="identifier" class="block text-sm font-medium text-gray-200">Email, số điện thoại hoặc tên đăng nhập</label>
                    <div class="relative">
                        <input type="text" id="identifier" name="identifier" 
                               class="mt-1 p-3 pl-10 w-full bg-white/80 border border-gray-300/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-300 ease-in-out placeholder-gray-400" 
                               placeholder="Nhập email, số điện thoại hoặc tên đăng nhập" required>
                        <i class="fas fa-envelope absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <a href="login.php" class="text-blue-400 hover:text-blue-300 transition duration-200">Quay lại đăng nhập</a>
                    <a href="register.php" class="text-blue-400 hover:text-blue-300 transition duration-200">Đăng ký tài khoản</a>
                </div>
                <button type="submit" id="submitButton" 
                        class="btn-shimmer w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white p-3 rounded-lg font-semibold hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-300 ease-in-out transform hover:-translate-y-1">
                    Gửi mã xác thực
                </button>
            </form>
        </div>

        <!-- Form nhập mã xác thực (Hiển thị sau khi gửi mã thành công) -->
        <div id="step2Container" class="hidden">
            <div class="bg-blue-500/10 border-l-4 border-blue-500 text-blue-200 p-4 mb-6 rounded-r-lg">
                <p>Mã xác thực đã được gửi đến <span id="contactMethodText"></span>. Vui lòng kiểm tra và nhập mã bên dưới.</p>
            </div>
            <form id="verifyCodeForm" action="verify_code.php" method="POST" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                <div>
                    <label for="verificationCode" class="block text-sm font-medium text-gray-200">Mã xác thực</label>
                    <div class="relative">
                        <input type="text" id="verificationCode" name="verification_code" 
                               class="mt-1 p-3 pl-10 w-full bg-white/80 border border-gray-300/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-300 ease-in-out placeholder-gray-400" 
                               placeholder="Nhập mã xác thực 6 số" required maxlength="6" minlength="6">
                        <i class="fas fa-key absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>
                <div class="text-center">
                    <p class="text-sm text-gray-400">Chưa nhận được mã? <button type="button" id="resendCodeBtn" class="text-blue-400 hover:text-blue-300 transition duration-200">Gửi lại</button></p>
                    <p class="text-sm text-gray-400 mt-2">Mã xác thực có hiệu lực trong 15 phút</p>
                </div>
                <button type="submit" id="verifyButton" 
                        class="btn-shimmer w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white p-3 rounded-lg font-semibold hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-300 ease-in-out transform hover:-translate-y-1">
                    Xác thực mã
                </button>
            </form>
        </div>
    </div>

    <script>
        // Kiểm tra nếu đã gửi mã xác thực thành công trước đó
        <?php if (isset($_SESSION['verification_code_sent']) && $_SESSION['verification_code_sent']): ?>
            // Hiển thị form nhập mã xác thực
            document.getElementById('step1Container').classList.add('hidden');
            document.getElementById('step2Container').classList.remove('hidden');
            
            // Hiển thị thông tin phương thức liên hệ
            const contactMethod = "<?php echo isset($_SESSION['contact_method']) ? $_SESSION['contact_method'] : 'email'; ?>";
            const maskedContact = "<?php echo isset($_SESSION['masked_contact']) ? $_SESSION['masked_contact'] : ''; ?>";
            document.getElementById('contactMethodText').textContent = contactMethod + " " + maskedContact;
        <?php endif; ?>

        // Xử lý submit form quên mật khẩu
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

                const messageContainer = document.getElementById('messageContainer');
                messageContainer.innerHTML = '';

                if (result.success) {
                    // Hiển thị thông báo thành công
                    const messageDiv = document.createElement('div');
                    messageDiv.className = 'bg-green-500/10 border-l-4 border-green-500 text-green-200 p-4 rounded-r-lg animate-slide-in';
                    messageDiv.textContent = result.message;
                    messageContainer.appendChild(messageDiv);
                    
                    // Chuyển sang bước tiếp theo sau 1 giây
                    setTimeout(() => {
                        document.getElementById('step1Container').classList.add('hidden');
                        document.getElementById('step2Container').classList.remove('hidden');
                        
                        // Parse thông tin phương thức liên hệ từ thông báo
                        const contactMatch = result.message.match(/đến\s+(\w+)\s+([^\s]+)/);
                        if (contactMatch && contactMatch.length >= 3) {
                            document.getElementById('contactMethodText').textContent = contactMatch[1] + " " + contactMatch[2];
                        }
                    }, 1000);
                } else {
                    // Hiển thị thông báo lỗi
                    const messageDiv = document.createElement('div');
                    messageDiv.className = 'bg-red-500/10 border-l-4 border-red-500 text-red-200 p-4 rounded-r-lg animate-slide-in';
                    messageDiv.textContent = result.message || 'Đã có lỗi xảy ra. Vui lòng thử lại.';
                    messageContainer.appendChild(messageDiv);
                }
            } catch (error) {
                const messageContainer = document.getElementById('messageContainer');
                messageContainer.innerHTML = '';
                
                const messageDiv = document.createElement('div');
                messageDiv.className = 'bg-red-500/10 border-l-4 border-red-500 text-red-200 p-4 rounded-r-lg animate-slide-in';
                messageDiv.textContent = 'Lỗi kết nối. Vui lòng thử lại.';
                messageContainer.appendChild(messageDiv);
            } finally {
                submitButton.disabled = false;
                submitButton.classList.remove('loading');
                submitButton.textContent = 'Gửi mã xác thực';
            }
        });

        // Xử lý sự kiện gửi lại mã
        document.getElementById('resendCodeBtn').addEventListener('click', async function() {
            const identifier = document.getElementById('identifier').value;
            const resendBtn = this;
            
            // Vô hiệu hóa nút trong khi xử lý
            resendBtn.disabled = true;
            resendBtn.textContent = 'Đang gửi...';
            
            try {
                // Gửi yêu cầu tạo mã mới
                const formData = new FormData();
                formData.append('csrf_token', document.querySelector('input[name="csrf_token"]').value);
                formData.append('identifier', identifier);
                
                const response = await fetch('forgot_password.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                const messageContainer = document.getElementById('messageContainer');
                messageContainer.innerHTML = '';
                
                const messageDiv = document.createElement('div');
                if (result.success) {
                    messageDiv.className = 'bg-green-500/10 border-l-4 border-green-500 text-green-200 p-4 rounded-r-lg animate-slide-in';
                    messageDiv.textContent = 'Mã xác thực mới đã được gửi thành công.';
                } else {
                    messageDiv.className = 'bg-red-500/10 border-l-4 border-red-500 text-red-200 p-4 rounded-r-lg animate-slide-in';
                    messageDiv.textContent = result.message || 'Không thể gửi lại mã. Vui lòng thử lại.';
                }
                messageContainer.appendChild(messageDiv);
            } catch (error) {
                const messageContainer = document.getElementById('messageContainer');
                messageContainer.innerHTML = '';
                
                const messageDiv = document.createElement('div');
                messageDiv.className = 'bg-red-500/10 border-l-4 border-red-500 text-red-200 p-4 rounded-r-lg animate-slide-in';
                messageDiv.textContent = 'Lỗi kết nối. Vui lòng thử lại.';
                messageContainer.appendChild(messageDiv);
            } finally {
                // Kích hoạt lại nút
                resendBtn.disabled = false;
                resendBtn.textContent = 'Gửi lại';
            }
        });

        // Xử lý form xác thực mã
        document.getElementById('verifyCodeForm').addEventListener('submit', function(event) {
            event.preventDefault();
            
            const verifyButton = document.getElementById('verifyButton');
            verifyButton.disabled = true;
            verifyButton.classList.add('loading');
            verifyButton.textContent = 'Đang xác thực';
            
            // Chuyển hướng đến trang xác thực mã
            this.submit();
        });
    </script>
</body>
</html>