<?php
session_start(); 
include('../config/database.php');

// Google OAuth Configuration
$google_client_id = 'YOUR_GOOGLE_CLIENT_ID'; // Thay bằng client ID của bạn
$google_client_secret = 'YOUR_GOOGLE_CLIENT_SECRET'; // Thay bằng client secret của bạn
$google_redirect_url = 'http://localhost/baby-store/auth/google-callback.php'; // Điều chỉnh URL redirect

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit();
}

// Process registration
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Validate input
    if (empty($username) || empty($email) || empty($phone) || empty($password)) {
        $error = "Vui lòng điền đầy đủ các trường";
    } elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
        $error = "Số điện thoại phải có 10 chữ số";
    } else {
        // Check if username, email, or phone already exists
        $stmt = $conn->prepare("SELECT id FROM user WHERE username = ? OR email = ? OR phone = ?");
        $stmt->execute([$username, $email, $phone]);
        
        if ($stmt->rowCount() > 0) {
            $error = "Tên đăng nhập, email hoặc số điện thoại đã tồn tại";
        } else {
            // Insert new user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO user (username, email, phone, password) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$username, $email, $phone, $hashed_password])) {
                $success = "Đăng ký thành công! Bạn có thể đăng nhập ngay.";
            } else {
                $error = "Đăng ký thất bại. Vui lòng thử lại.";
            }
        }
    }
}

// Tạo URL đăng nhập Google
$google_login_url = "https://accounts.google.com/o/oauth2/v2/auth?scope=email profile&access_type=offline&include_granted_scopes=true&response_type=code&state=state_parameter_passthrough_value&redirect_uri=$google_redirect_url&client_id=$google_client_id";
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - BABY Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        /* Nền sáng với viền nhẹ */
        body {
            background-color: #f3f4f6;
        }

        /* Container với viền và bóng nhẹ */
        .register-container {
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
    </style>
</head>
<body class="flex items-center justify-center min-h-screen">
    <div class="register-container p-6 rounded-lg w-full max-w-lg shadow-md">
        <!-- Logo and Title -->
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Đăng ký tài khoản</h2>
            <p class="mt-1 text-sm text-gray-600">Tạo tài khoản mới để bắt đầu!</p>
        </div>

        <!-- Thông điệp lỗi/thành công -->
        <?php if (isset($error)): ?>
            <div class="bg-red-500/10 border-l-4 border-red-500 text-red-700 p-3 mb-4 rounded-r">
                <p><?php echo $error; ?></p>
            </div>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <div class="bg-green-500/10 border-l-4 border-green-500 text-green-700 p-3 mb-4 rounded-r">
                <p><?php echo $success; ?></p>
            </div>
        <?php endif; ?>

        <!-- Google Sign-Up Button -->
        <a href="<?php echo htmlspecialchars($google_login_url); ?>" class="google-btn w-full py-2 px-4 rounded-md font-medium mb-4 flex items-center justify-center gap-2">
            <i class="fab fa-google text-gray-600"></i>
            <span class="text-gray-700">Đăng ký với Google</span>
        </a>

        <div class="or-divider">hoặc</div>

        <!-- Form đăng ký -->
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="space-y-4">
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700">Tên đăng nhập</label>
                <div class="relative">
                    <input type="text" name="username" id="username" value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>" 
                           class="input-field mt-1 p-2 pl-8 w-full border border-gray-300 rounded-md placeholder-gray-400 text-sm" 
                           placeholder="Tên đăng nhập" required>
                    <i class="fas fa-user absolute left-2 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                </div>
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <div class="relative">
                    <input type="email" name="email" id="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" 
                           class="input-field mt-1 p-2 pl-8 w-full border border-gray-300 rounded-md placeholder-gray-400 text-sm" 
                           placeholder="Email" required>
                    <i class="fas fa-envelope absolute left-2 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                </div>
            </div>

            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700">Số điện thoại</label>
                <div class="relative">
                    <input type="text" name="phone" id="phone" value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>" 
                           class="input-field mt-1 p-2 pl-8 w-full border border-gray-300 rounded-md placeholder-gray-400 text-sm" 
                           placeholder="Số điện thoại" required>
                    <i class="fas fa-phone absolute left-2 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                </div>
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Mật khẩu</label>
                <div class="relative">
                    <input type="password" name="password" id="password" 
                           class="input-field mt-1 p-2 pl-8 w-full border border-gray-300 rounded-md placeholder-gray-400 text-sm" 
                           placeholder="Mật khẩu" required>
                    <i class="fas fa-lock absolute left-2 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                </div>
            </div>

            <button type="submit" 
                    class="btn-primary w-full text-white py-2 rounded-md font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                Đăng ký
            </button>
        </form>

        <p class="mt-4 text-center text-sm text-gray-600">
            Bạn đã có tài khoản? 
            <a href="login.php" class="text-red-500 hover:text-red-600">Đăng nhập ngay</a>
        </p>
    </div>
</body>
</html>