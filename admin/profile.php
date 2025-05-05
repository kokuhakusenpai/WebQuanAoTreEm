<?php
session_start();
include('config/database.php');

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Check database connection
if (!$conn) {
    error_log("Database connection failed: " . mysqli_connect_error());
    die("Database connection failed: " . mysqli_connect_error());
}

// Fetch admin's profile data
$user_id = $_SESSION['user_id'];
$query = "SELECT username, email, phone FROM user WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Không tìm thấy thông tin người dùng.";
    header("Location: ../index.php");
    exit;
}

$user = $result->fetch_assoc();
$stmt->close();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Validate inputs
    if (empty($username) || empty($email) || empty($phone)) {
        $_SESSION['error'] = "Vui lòng điền đầy đủ thông tin bắt buộc.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Email không hợp lệ.";
    } elseif (!preg_match("/^[0-9]{10,11}$/", $phone)) {
        $_SESSION['error'] = "Số điện thoại không hợp lệ.";
    } elseif (!empty($password) && $password !== $confirm_password) {
        $_SESSION['error'] = "Mật khẩu xác nhận không khớp.";
    } else {
        // Update profile
        try {
            if (!empty($password)) {
                // Update with new password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $update_query = "UPDATE user SET username = ?, email = ?, phone = ?, password = ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_query);
                $update_stmt->bind_param("ssssi", $username, $email, $phone, $hashed_password, $user_id);
            } else {
                // Update without changing password
                $update_query = "UPDATE user SET username = ?, email = ?, phone = ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_query);
                $update_stmt->bind_param("ssi", $username, $email, $phone, $user_id);
            }

            if ($update_stmt->execute()) {
                $_SESSION['success'] = "Cập nhật thông tin thành công.";
                // Update session variables
                $_SESSION['username'] = $username;
                header("Location: profile.php");
                exit;
            } else {
                $_SESSION['error'] = "Lỗi khi cập nhật thông tin: " . $update_stmt->error;
            }
            $update_stmt->close();
        } catch (Exception $e) {
            error_log("Exception when updating profile: " . $e->getMessage());
            $_SESSION['error'] = "Đã xảy ra lỗi: " . $e->getMessage();
        }
    }
    header("Location: profile.php");
    exit;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông Tin Admin - SUSU Hub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.5s ease;
            z-index: 9999;
        }
        .toast.show {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-3xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Thông Tin Admin</h2>
            <a href="../index.php" class="text-blue-500 hover:text-blue-700">
                <i class="fas fa-arrow-left mr-2"></i>Quay lại
            </a>
        </div>

        <?php if(isset($_SESSION['success'])): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
            <p><?php echo $_SESSION['success']; ?></p>
        </div>
        <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if(isset($_SESSION['error'])): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
            <p><?php echo $_SESSION['error']; ?></p>
        </div>
        <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Profile Form -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <form method="POST" class="space-y-4">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700">Tên người dùng</label>
                    <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" 
                           required class="mt-1 w-full p-2 border rounded focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" 
                           required class="mt-1 w-full p-2 border rounded focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">Số điện thoại</label>
                    <input type="tel" name="phone" id="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" 
                           required class="mt-1 w-full p-2 border rounded focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Mật khẩu mới (để trống nếu không thay đổi)</label>
                    <input type="password" name="password" id="password" placeholder="Nhập mật khẩu mới" 
                           class="mt-1 w-full p-2 border rounded focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700">Xác nhận mật khẩu mới</label>
                    <input type="password" name="confirm_password" id="confirm_password" placeholder="Xác nhận mật khẩu" 
                           class="mt-1 w-full p-2 border rounded focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        <i class="fas fa-save mr-2"></i>Cập nhật
                    </button>
                    <a href="../index.php" class="bg-gray-300 text-black px-4 py-2 rounded hover:bg-gray-400">
                        <i class="fas fa-times mr-2"></i>Hủy
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Toast container -->
    <div id="toast-container"></div>

    <script>
        // Hiển thị toast notification
        function showToast(message, type) {
            const toastContainer = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `toast p-4 rounded-lg text-white ${type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
            toast.textContent = message;
            toastContainer.appendChild(toast);
            setTimeout(() => toast.classList.add('show'), 100);
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 500);
            }, 3000);
        }

        // Show toasts if there are session messages
        document.addEventListener('DOMContentLoaded', () => {
            <?php if(isset($_SESSION['success'])): ?>
                showToast("<?php echo $_SESSION['success']; ?>", "success");
            <?php endif; ?>
            <?php if(isset($_SESSION['error'])): ?>
                showToast("<?php echo $_SESSION['error']; ?>", "error");
            <?php endif; ?>
        });
    </script>
</body>
</html>