<?php
session_start();
include('../../config/database.php');

// Kiểm tra kết nối cơ sở dữ liệu
if (!$conn) {
    error_log("Database connection failed: " . mysqli_connect_error());
    die("Database connection failed: " . mysqli_connect_error());
}

// Kiểm tra ID đơn hàng có được truyền vào không
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: orders.php");
    exit;
}

$order_id = intval($_GET['id']);

// Truy vấn thông tin đơn hàng
$query = "SELECT o.*, u.username as customer_name, u.email, u.phone, u.address
          FROM orders o 
          LEFT JOIN user u ON o.user_id = u.id 
          WHERE o.id = ?";
$stmt = $conn->prepare($query);
if ($stmt === false) {
    error_log("Prepare failed: " . $conn->error);
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_result = $stmt->get_result();

if ($order_result->num_rows === 0) {
    header("Location: orders.php");
    exit;
}

$order = $order_result->fetch_assoc();

// Truy vấn chi tiết đơn hàng
$query = "SELECT od.*, p.name as product_name, p.price as product_price, p.image as product_image
          FROM order_details od
          LEFT JOIN products p ON od.product_id = p.id
          WHERE od.order_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_details_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi Tiết Đơn Hàng #<?php echo $order_id; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="styles.css" />
</head>
<body class="bg-gray-100">
    <div class="max-w-6xl mx-auto p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Chi Tiết Đơn Hàng #<?php echo $order_id; ?></h2>
            <a href="orders.php" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition">
                <i class="fas fa-arrow-left mr-2"></i>Quay lại
            </a>
        </div>

        <!-- Thông tin đơn hàng -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h3 class="text-lg font-semibold mb-4 text-gray-700 border-b pb-2">Thông tin đơn hàng</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="mb-2"><span class="font-medium">Mã đơn hàng:</span> #<?php echo htmlspecialchars($order['id']); ?></p>
                    <p class="mb-2"><span class="font-medium">Ngày đặt:</span> <?php echo htmlspecialchars($order['created_at']); ?></p>
                    <p class="mb-2"><span class="font-medium">Trạng thái:</span> 
                        <span class="inline-block px-2 py-1 text-xs font-semibold rounded
                        <?php 
                        switch($order['status']) {
                            case 'pending':
                                echo ' bg-yellow-100 text-yellow-800';
                                break;
                            case 'processing':
                                echo ' bg-blue-100 text-blue-800';
                                break;
                            case 'completed':
                                echo ' bg-green-100 text-green-800';
                                break;
                            case 'cancelled':
                                echo ' bg-red-100 text-red-800';
                                break;
                            default:
                                echo ' bg-gray-100 text-gray-800';
                        }
                        ?>">
                            <?php echo htmlspecialchars($order['status']); ?>
                        </span>
                    </p>
                </div>
                <div>
                    <p class="mb-2"><span class="font-medium">Khách hàng:</span> <?php echo htmlspecialchars($order['customer_name'] ?? 'Khách hàng không xác định'); ?></p>
                    <?php if(!empty($order['email'])): ?>
                    <p class="mb-2"><span class="font-medium">Email:</span> <?php echo htmlspecialchars($order['email']); ?></p>
                    <?php endif; ?>
                    <?php if(!empty($order['phone'])): ?>
                    <p class="mb-2"><span class="font-medium">Số điện thoại:</span> <?php echo htmlspecialchars($order['phone']); ?></p>
                    <?php endif; ?>
                    <?php if(!empty($order['address'])): ?>
                    <p class="mb-2"><span class="font-medium">Địa chỉ:</span> <?php echo htmlspecialchars($order['address']); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Chi tiết sản phẩm -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h3 class="text-lg font-semibold mb-4 text-gray-700 border-b pb-2">Chi tiết sản phẩm</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sản phẩm</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Đơn giá</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Số lượng</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php 
                        $total = 0;
                        if($order_details_result->num_rows > 0): 
                            while($item = $order_details_result->fetch_assoc()): 
                                $subtotal = $item['price'] * $item['quantity'];
                                $total += $subtotal;
                        ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <?php if(!empty($item['product_image'])): ?>
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <img class="h-10 w-10 rounded-full object-cover" src="<?php echo htmlspecialchars($item['product_image']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                                    </div>
                                    <?php endif; ?>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo number_format($item['price']); ?> VND</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo $item['quantity']; ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo number_format($subtotal); ?> VND</div>
                            </td>
                        </tr>
                        <?php 
                            endwhile; 
                        else: 
                        ?>
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">Không có chi tiết sản phẩm nào.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-50">
                            <td colspan="3" class="px-6 py-3 text-right font-semibold">Tổng cộng:</td>
                            <td class="px-6 py-3 font-semibold"><?php echo number_format($order['total_price']); ?> VND</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Nút cập nhật trạng thái -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold mb-4 text-gray-700 border-b pb-2">Cập nhật trạng thái</h3>
            <form action="update_status.php" method="post" class="flex items-center space-x-4">
                <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                <select name="status" class="form-select rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <option value="pending" <?php echo ($order['status'] == 'pending') ? 'selected' : ''; ?>>Đang chờ xử lý</option>
                    <option value="processing" <?php echo ($order['status'] == 'processing') ? 'selected' : ''; ?>>Đang xử lý</option>
                    <option value="completed" <?php echo ($order['status'] == 'completed') ? 'selected' : ''; ?>>Hoàn thành</option>
                    <option value="cancelled" <?php echo ($order['status'] == 'cancelled') ? 'selected' : ''; ?>>Đã hủy</option>
                </select>
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-md transition">
                    Cập nhật
                </button>
            </form>
        </div>
    </div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>