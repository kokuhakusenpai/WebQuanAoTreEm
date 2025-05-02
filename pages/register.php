<?php
session_start(); 
include('../config/database.php');

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
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    // Validate input
    if (empty($username) || empty($email) || empty($phone) || empty($password) || empty($confirm_password)) {
        $error = "Vui lòng điền đầy đủ các trường";
    } elseif ($password !== $confirm_password) {
        $error = "Mật khẩu không khớp";
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
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - BABY Store</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
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
    </style>
</head>
<body class="animated-bg flex items-center justify-center min-h-screen">
    <div class="glass-container p-8 rounded-3xl w-full max-w-md shadow-2xl transform transition-all hover:scale-[1.01] hover:shadow-3xl">
        <div class="text-center mb-8">
            <h2 class="text-4xl font-extrabold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">Đăng ký tài khoản</h2>
            <p class="mt-2 text-sm text-gray-300">Tạo tài khoản mới để bắt đầu hành trình!</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="bg-red-500/10 border-l-4 border-red-500 text-red-200 p-4 mb-6 rounded-r-lg animate-slide-in">
                <p><?php echo $error; ?></p>
            </div>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <div class="bg-green-500/10 border-l-4 border-green-500 text-green-200 p-4 mb-6 rounded-r-lg animate-slide-in">
                <p><?php echo $success; ?></p>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="space-y-5">
            <div>
                <label for="username" class="block text-sm font-medium text-gray-200">Tên đăng nhập</label>
                <div class="relative">
                    <input type="text" name="username" id="username" value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>" 
                           class="mt-1 p-3 pl-10 w-full bg-white/80 border border-gray-300/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-300 ease-in-out placeholder-gray-400" 
                           placeholder="Nhập tên đăng nhập" required>
                    <i class="fas fa-user absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 group-focus-within:text-blue-500 transition-colors"></i>
                </div>
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-200">Email</label>
                <div class="relative">
                    <input type="email" name="email" id="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" 
                           class="mt-1 p-3 pl-10 w-full bg-white/80 border border-gray-300/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-300 ease-in-out placeholder-gray-400" 
                           placeholder="Nhập email" required>
                    <i class="fas fa-envelope absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 group-focus-within:text-blue-500 transition-colors"></i>
                </div>
            </div>

            <div>
                <label for="phone" class="block text-sm font-medium text-gray-200">Số điện thoại</label>
                <div class="relative">
                    <input type="text" name="phone" id="phone" value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>" 
                           class="mt-1 p-3 pl-10 w-full bg-white/80 border border-gray-300/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-300 ease-in-out placeholder-gray-400" 
                           placeholder="Nhập số điện thoại" required>
                    <i class="fas fa-phone absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 group-focus-within:text-blue-500 transition-colors"></i>
                </div>
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-200">Mật khẩu</label>
                <div class="relative">
                    <input type="password" name="password" id="password" 
                           class="mt-1 p-3 pl-10 w-full bg-white/80 border border-gray-300/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-300 ease-in-out placeholder-gray-400" 
                           placeholder="Nhập mật khẩu" required>
                    <i class="fas fa-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 group-focus-within:text-blue-500 transition-colors"></i>
                </div>
            </div>

            <div>
                <label for="confirm_password" class="block text-sm font-medium text-gray-200">Xác nhận mật khẩu</label>
                <div class="relative">
                    <input type="password" name="confirm_password" id="confirm_password" 
                           class="mt-1 p-3 pl-10 w-full bg-white/80 border border-gray-300/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-300 ease-in-out placeholder-gray-400" 
                           placeholder="Xác nhận mật khẩu" required>
                    <i class="fas fa-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 group-focus-within:text-blue-500 transition-colors"></i>
                </div>
            </div>

            <button type="submit" 
                    class="btn-shimmer w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white p-3 rounded-lg font-semibold hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-300 ease-in-out transform hover:-translate-y-1">
                Đăng ký
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-gray-300">
            Bạn đã có tài khoản? 
            <a href="login.php" class="text-red-400 font-medium hover:text-blue-300 transition duration-200">Đăng nhập ngay</a>
        </p>
    </div>
</body>
</html>