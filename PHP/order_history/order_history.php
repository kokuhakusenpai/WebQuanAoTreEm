<?php
session_start();
include('../../config/database.php');

// Kiểm tra kết nối cơ sở dữ liệu
if (!$conn) {
    header('HTTP/1.1 500 Internal Server Error');
    exit(json_encode(['error' => 'Database connection failed']));
}

// Hàm bảo vệ dữ liệu đầu ra
function safe_output($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

// Lấy thông tin người dùng bằng prepared statement
$user = ['username' => 'Khách hàng', 'email' => '', 'phone' => ''];
$user_id = 1;

// Chuẩn bị truy vấn - Loại bỏ cột avatar vì không tồn tại
$stmt = $conn->prepare("SELECT username, email, phone FROM users WHERE user_id = ?");
if ($stmt === false) {
    error_log("Prepare failed: " . $conn->error);
    header('HTTP/1.1 500 Internal Server Error');
    exit(json_encode(['error' => 'Failed to prepare user query: ' . $conn->error]));
}

$stmt->bind_param("i", $user_id);
if (!$stmt->execute()) {
    error_log("Execute failed: " . $stmt->error);
    header('HTTP/1.1 500 Internal Server Error');
    exit(json_encode(['error' => 'Failed to execute user query: ' . $stmt->error]));
}

$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
}
$stmt->close();

// Lấy danh sách đơn hàng
$orders = [];
$stmt = $conn->prepare("SELECT order_id, total_price, created_at, status FROM orders WHERE user_id = ? ORDER BY created_at DESC");
if ($stmt === false) {
    error_log("Prepare failed: " . $conn->error);
    header('HTTP/1.1 500 Internal Server Error');
    exit(json_encode(['error' => 'Failed to prepare orders query: ' . $conn->error]));
}

$stmt->bind_param("i", $user_id);
if (!$stmt->execute()) {
    error_log("Execute failed: " . $stmt->error);
    header('HTTP/1.1 500 Internal Server Error');
    exit(json_encode(['error' => 'Failed to execute orders query: ' . $stmt->error]));
}

$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách đơn hàng</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        aside {
            background: linear-gradient(to bottom, #B3E5FC, #81D4FA);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border-radius: 12px;
        }
        nav a {
            color: #1A237E;
            transition: all 0.3s ease;
            border-radius: 8px;
            margin: 4px 0;
        }
        nav a:hover {
            background-color: #0288D1;
            color: white;
            transform: translateX(5px);
        }
        .order-card {
            transition: transform 0.2s;
        }
        .order-card:hover {
            transform: translateY(-2px);
        }
        .status-pending { background-color: #FFF3E0; color: #EF6C00; }
        .status-completed { background-color: #E8F5E9; color: #2E7D32; }
        .status-cancelled { background-color: #FFEBEE; color: #C62828; }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 p-6 fixed h-full">
            <div class="flex flex-col items-center mb-8">
                <div class="relative">
                    <img id="avatar" src="https://placehold.co/100x100" 
                         class="w-24 h-24 rounded-full shadow-lg border-4 border-white" alt="Avatar">
                    <div class="absolute bottom-0 right-0 w-6 h-6 bg-green-500 rounded-full border-2 border-white"></div>
                </div>
                <h2 id="customer-name" class="mt-4 text-xl font-bold text-gray-900"><?= safe_output($user['username']); ?></h2>
            </div>
            <nav class="space-y-2">
                <a href="../../trangchu.html" class="flex items-center p-3"><i class="fas fa-home mr-3"></i> Trang chủ</a>
                <a href="#" onclick="showProfile()" class="flex items-center p-3"><i class="fas fa-user mr-3"></i> Thông tin cá nhân</a>
                <a href="#" onclick="showOrderHistory()" class="flex items-center p-3"><i class="fas fa-box mr-3"></i> Đơn hàng</a>
                <a href="#" class="flex items-center p-3"><i class="fas fa-heart mr-3"></i> Yêu thích</a>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <div class="flex-1 p-8 ml-64">
            <div id="content" class="max-w-4xl mx-auto">
                <h1 class="text-3xl font-bold text-indigo-700 mb-6">Danh sách đơn hàng</h1>
                <div id="orders" class="space-y-4">
                    <?php foreach ($orders as $order): ?>
                        <div class="order-card p-6 bg-white rounded-xl shadow-md">
                            <div class="flex justify-between items-center">
                                <div>
                                    <h2 class="text-lg font-semibold text-gray-800">Mã đơn: #<?= safe_output($order['order_id']); ?></h2>
                                    <p class="text-sm text-gray-600">📅 Ngày đặt: <?= date('d/m/Y H:i', strtotime($order['created_at'])); ?></p>
                                    <p class="text-sm text-gray-800 font-medium">💰 Tổng tiền: <?= number_format($order['total_price'], 0, ',', '.'); ?>đ</p>
                                </div>
                                <span class="px-3 py-1 rounded-full text-sm font-medium status-<?= strtolower($order['status']); ?>">
                                    <?= safe_output($order['status']); ?>
                                </span>
                            </div>
                            <button onclick="viewOrder(<?= $order['order_id']; ?>)" 
                                    class="mt-4 w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded-lg transition-colors">
                                Xem chi tiết
                            </button>
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($orders)): ?>
                        <div class="text-center py-8">
                            <p class="text-gray-500 text-lg">Bạn chưa có đơn hàng nào.</p>
                            <a href="../../trangchu.html" class="text-indigo-600 hover:underline">Mua sắm ngay!</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Hàm hiển thị loading state
        function showLoading(elementId) {
            document.getElementById(elementId).innerHTML = `
                <div class="flex justify-center items-center h-64">
                    <i class="fas fa-spinner fa-spin text-3xl text-indigo-600"></i>
                </div>
            `;
        }

        // Hàm hiển thị profile
        function showProfile() {
            showLoading('content');
            setTimeout(() => {
                document.getElementById('content').innerHTML = `
                    <div class="max-w-xl mx-auto bg-white p-8 rounded-xl shadow-md">
                        <h2 class="text-2xl font-bold text-center text-indigo-600 mb-6">Chỉnh sửa thông tin cá nhân</h2>
                        <form method="POST" action="update_profile.php" class="space-y-6" onsubmit="return validateForm()">
                            <div class="flex flex-col items-center">
                                <img src="https://placehold.co/120x120" 
                                     class="w-32 h-32 rounded-full border-4 border-indigo-500 shadow mb-4" alt="Avatar">
                                <label class="text-sm text-gray-600 mb-2">Tên người dùng</label>
                                <input type="text" name="username" value="<?= safe_output($user['username']); ?>" required 
                                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-200">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-2">Email</label>
                                <input type="email" name="email" value="<?= safe_output($user['email']); ?>" required 
                                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-200">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-2">Số điện thoại</label>
                                <input type="tel" name="phone" value="<?= safe_output($user['phone']); ?>" 
                                       pattern="[0-9]{10,11}" required 
                                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-200">
                            </div>
                            <div class="text-center pt-4">
                                <button type="submit" 
                                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-3 rounded-lg shadow-md transition-colors">
                                    Lưu thay đổi
                                </button>
                            </div>
                        </form>
                    </div>
                `;
            }, 300);
        }

        // Hàm hiển thị lịch sử đơn hàng
        function showOrderHistory() {
            showLoading('content');
            setTimeout(() => {
                document.getElementById('content').innerHTML = `
                    <h1 class="text-3xl font-bold text-indigo-700 mb-6">Danh sách đơn hàng</h1>
                    <div class="space-y-4">
                        <?php foreach ($orders as $order): ?>
                            <div class="order-card p-6 bg-white rounded-xl shadow-md">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <h2 class="text-lg font-semibold text-gray-800">Mã đơn: #<?= safe_output($order['order_id']); ?></h2>
                                        <p class="text-sm text-gray-600">📅 Ngày đặt: <?= date('d/m/Y H:i', strtotime($order['created_at'])); ?></p>
                                        <p class="text-sm text-gray-800 font-medium">💰 Tổng tiền: <?= number_format($order['total_price'], 0, ',', '.'); ?>đ</p>
                                    </div>
                                    <span class="px-3 py-1 rounded-full text-sm font-medium status-<?= strtolower($order['status']); ?>">
                                        <?= safe_output($order['status']); ?>
                                    </span>
                                </div>
                                <button onclick="viewOrder(<?= $order['order_id']; ?>)" 
                                        class="mt-4 w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded-lg transition-colors">
                                    Xem chi tiết
                                </button>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($orders)): ?>
                            <div class="text-center py-8">
                                <p class="text-gray-500 text-lg">Bạn chưa có đơn hàng nào.</p>
                                <a href="../../trangchu.html" class="text-indigo-600 hover:underline">Mua sắm ngay!</a>
                            </div>
                        <?php endif; ?>
                    </div>
                `;
            }, 300);
        }

        // Hàm validate form
        function validateForm() {
            const phoneInput = document.querySelector('input[name="phone"]');
            const phonePattern = /^[0-9]{10,11}$/;
            if (!phonePattern.test(phoneInput.value)) {
                alert('Số điện thoại không hợp lệ. Vui lòng nhập 10-11 số.');
                return false;
            }
            return true;
        }

        // Hàm xem chi tiết đơn hàng
        function viewOrder(orderId) {
            window.location.href = `order_detail.php?id=${orderId}`;
        }
    </script>
</body>
</html>