<?php
session_start();
include('../../config/database.php');

// Kiểm tra kết nối cơ sở dữ liệu
if (!$conn) {
    die("Lỗi kết nối cơ sở dữ liệu: " . mysqli_connect_error());
}

// Lấy thông tin người dùng
$sql_user = "SELECT username, email, phone FROM users WHERE user_id = 1";
$result_user = $conn->query($sql_user);

if (!$result_user) {
    die("Lỗi truy vấn SQL: " . $conn->error);
}

$user = [];
if ($result_user->num_rows > 0) {
    $user = $result_user->fetch_assoc();
} else {
    $user = [
        'name' => 'Khách hàng',
        'email' => '',
        'phone' => '',
        'avatar' => null
    ];
}

// Lấy danh sách đơn hàng
$sql_orders = "SELECT order_id, total_price FROM orders WHERE user_id = 1";
$result_orders = $conn->query($sql_orders);

if (!$result_orders) {
    die("Lỗi truy vấn SQL: " . $conn->error);
}

$orders = [];
if ($result_orders->num_rows > 0) {
    while ($row = $result_orders->fetch_assoc()) {
        $orders[] = $row;
    }
}

// Đóng kết nối
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
        aside { background-color: #B3E5FC; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2); border-radius: 10px; }
        nav a { color: #000; transition: 0.3s; background-color: #E0F7FA; border-radius: 8px; }
        nav a:hover { background-color: #81D4FA; color: #01579B; }
        #content h1 { color: #4169E1; }
    </style>
</head>
<body>
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 p-6">
            <div class="flex flex-col items-center mb-6">
                <img id="avatar" src="<?= isset($user['avatar']) && $user['avatar'] ? $user['avatar'] : 'https://placehold.co/100x100'; ?>" class="w-24 h-24 rounded-full shadow-md border-2" alt="Avatar">
                <h2 id="customer-name" class="mt-3 text-lg font-semibold text-gray-900"><?= htmlspecialchars($user['name']); ?></h2>
            </div>
            <nav class="space-y-2">
                <a href="trangchu.html" class="flex items-center p-2"><i class="fas fa-home mr-2"></i> Trang chủ</a>
                <a href="#" onclick="showProfile()" class="flex items-center p-2"><i class="fas fa-user mr-2"></i> Thông tin cá nhân</a>
                <a href="#" onclick="showOrderHistory()" class="flex items-center p-2"><i class="fas fa-box mr-2"></i> Đơn hàng</a>
                <a href="#" class="flex items-center p-2 logout mt-4"><i class="fas fa-sign-out-alt mr-2"></i> Yêu thích</a>
            </nav>            
        </aside>
        
        <!-- Main Content -->
        <div class="flex-1 p-6">
            <div id="content">
                <h1 class="text-2xl font-semibold">Danh sách đơn hàng</h1>   
                <!-- Danh sách đơn hàng -->
                <div id="orders">
                    <?php foreach ($orders as $order): ?>
                        <div class="p-4 bg-white rounded-lg shadow-md mb-4">
                            <h2 class="text-lg font-semibold text-gray-800">Mã đơn: <?= htmlspecialchars($order['id']); ?></h2>
                            <p class="text-sm text-gray-600">📅 Ngày đặt: <?= htmlspecialchars($order['date']); ?></p>
                            <p class="text-sm text-gray-800 font-medium">💰 Tổng tiền: <?= htmlspecialchars($order['total']); ?></p>
                            <button onclick="viewOrder(<?= $order['id']; ?>)" class="mt-2 block w-full text-center bg-pink-500 text-white py-1 rounded-md">Xem chi tiết</button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>            
        </div>
    </div>
    
    <script>
    function showProfile() {
        document.getElementById("content").innerHTML = `
            <div class="max-w-xl mx-auto bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-2xl font-bold text-center text-indigo-600 mb-4">Chỉnh sửa thông tin cá nhân</h2>
                <form method="POST" action="update_profile.php" class="space-y-4">
                    <div class="flex flex-col items-center">
                        <img src="<?= isset($user['avatar']) && $user['avatar'] ? $user['avatar'] : 'https://placehold.co/120x120'; ?>" 
                             class="w-28 h-28 rounded-full border-2 border-indigo-500 shadow mb-3" alt="Avatar">
                        <label class="text-sm text-gray-600 mb-1">Tên người dùng</label>
                        <input type="text" name="name" value="<?= htmlspecialchars($user['name']); ?>" required 
                               class="w-full border border-gray-300 rounded px-4 py-2 focus:ring focus:ring-indigo-200">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Email</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($user['email']); ?>" required 
                               class="w-full border border-gray-300 rounded px-4 py-2 focus:ring focus:ring-indigo-200">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Số điện thoại</label>
                        <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']); ?>" required 
                               class="w-full border border-gray-300 rounded px-4 py-2 focus:ring focus:ring-indigo-200">
                    </div>
                    <div class="text-center pt-2">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded shadow-md">
                            Lưu thay đổi
                        </button>
                    </div>
                </form>
            </div>
        `;
    }

    function showOrderHistory() {
        document.getElementById("content").innerHTML = `
            <h1 class="text-2xl font-semibold text-indigo-700 mb-4">Danh sách đơn hàng</h1>
            <?php foreach ($orders as $order): ?>
                <div class="p-4 bg-white rounded-lg shadow-md mb-4">
                    <h2 class="text-lg font-semibold text-gray-800">Mã đơn: <?= htmlspecialchars($order['order_id']); ?></h2>
                    <p class="text-sm text-gray-600">📅 Ngày đặt: <?= date('d/m/Y', strtotime($order['created_at'] ?? 'now')); ?></p>
                    <p class="text-sm text-gray-800 font-medium">💰 Tổng tiền: <?= number_format($order['total_price'], 0, ',', '.'); ?>đ</p>
                    <button onclick="viewOrder(<?= $order['order_id']; ?>)" 
                        class="mt-2 block w-full text-center bg-pink-500 hover:bg-pink-600 text-white py-2 rounded-md">
                        Xem chi tiết
                    </button>
                </div>
            <?php endforeach; ?>
        `;
    }
</script>


</body>
</html>