<?php
session_start();
include('../config/database.php');
include('../components/header.php');

// Khởi tạo session nếu chưa có
if (session_status() == PHP_SESSION_NONE) {
}

// Kiểm tra nếu người dùng chưa đăng nhập
if (!isset($_SESSION['id'])) {
    header("Location: login.php?redirect=order_history.php");
    exit();
}

$user_id = $_SESSION['id'];

// Kiểm tra kết nối cơ sở dữ liệu
if (!$conn) {
    die("Lỗi kết nối cơ sở dữ liệu: " . mysqli_connect_error());
}

// Xử lý hủy đơn hàng
if (isset($_POST['cancel_order'])) {
    $order_id = mysqli_real_escape_string($conn, $_POST['order_id']);
    $query = "UPDATE orders SET status = 'cancelled' WHERE id = '$order_id' AND user_id = '$user_id' AND status = 'pending'";
    if (mysqli_query($conn, $query)) {
        header("Location: order_history.php?success=Đơn hàng đã được hủy thành công!");
        exit();
    } else {
        $error_message = "Lỗi khi hủy đơn hàng: " . mysqli_error($conn);
    }
}

// Lấy danh sách đơn hàng của người dùng
$query = "SELECT * FROM orders WHERE user_id = '$user_id' ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);

// Kiểm tra nếu truy vấn thất bại
if ($result === false) {
    $error_message = "Lỗi truy vấn cơ sở dữ liệu: " . mysqli_error($conn);
}

// Xử lý hiển thị chi tiết đơn hàng
$selected_order = null;
if (isset($_GET['view_details'])) {
    $selected_order_id = mysqli_real_escape_string($conn, $_GET['view_details']);
    $query = "SELECT * FROM orders WHERE id = '$selected_order_id' AND user_id = '$user_id'";
    $selected_result = mysqli_query($conn, $query);
    if ($selected_result && mysqli_num_rows($selected_result) > 0) {
        $selected_order = mysqli_fetch_assoc($selected_result);
    }
}

// Hàm chuyển đổi trạng thái sang tiếng Việt
function translateStatus($status) {
    switch ($status) {
        case 'pending':
            return 'Chờ xử lý';
        case 'processing':
            return 'Đang xử lý';
        case 'shipped':
            return 'Đang giao';
        case 'delivered':
            return 'Đã giao';
        case 'cancelled':
            return 'Đã hủy';
        default:
            return $status;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch Sử Đơn Hàng - SUSU Kids</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #fce7f3, #e0f2fe, #f3e8ff);
            background-size: 400%;
            animation: gradientBG 15s ease infinite;
            min-height: 100vh;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .glass-container {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.7);
        }

        .order-item {
            transition: all 0.3s ease;
        }

        .order-item:hover {
            transform: scale(1.05);
        }

        .details-table {
            min-width: 100%;
        }

        .details-table-container {
            overflow-x: auto;
        }

        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
            transition: transform 0.3s ease;
        }

        .status-badge:hover {
            transform: scale(1.1);
        }

        .action-button {
            display: flex;
            align-items: center;
            color: #60a5fa;
            transition: all 0.3s ease;
        }

        .action-button:hover {
            color: #3b82f6;
            transform: scale(1.1);
        }

        .action-button:active {
            transform: scale(0.95);
        }

        .cancel-button {
            background: linear-gradient(90deg, #ef4444 0%, #dc2626 100%);
            color: white;
            border-radius: 8px;
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
        }

        .cancel-button:hover {
            background: linear-gradient(90deg, #dc2626 0%, #b91c1c 100%);
            transform: scale(1.1);
        }

        .cancel-button:active {
            transform: scale(0.95);
        }

        .cancel-button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .loading-spinner {
            display: none;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top: 3px solid white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
            margin-left: 0.5rem;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .shopping-button {
            background: linear-gradient(90deg, #f472b6 0%, #ec4899 100%);
            color: white;
            border-radius: 8px;
            padding: 0.5rem 1.5rem;
            transition: all 0.3s ease;
        }

        .shopping-button:hover {
            background: linear-gradient(90deg, #ec4899 0%, #db2777 100%);
            transform: scale(1.1);
        }

        .shopping-button:active {
            transform: scale(0.95);
        }

        @media (max-width: 768px) {
            .details-table-container {
                overflow-x: auto;
            }

            table thead {
                display: none;
            }

            table tbody tr {
                display: block;
                margin-bottom: 1rem;
                border: 1px solid #d1d5db;
                border-radius: 8px;
                padding: 1rem;
            }

            table tbody td {
                display: block;
                text-align: left;
                padding: 0.25rem 0;
            }

            table tbody td:before {
                content: attr(data-label);
                font-weight: 500;
                display: inline-block;
                width: 40%;
                color: #4b5563;
            }
        }
    </style>
</head>
<body>
    <section class="py-6 mt-6">
        <div class="container mx-auto px-4">
            <h2 class="text-4xl font-bold text-center bg-gradient-to-r from-pink-400 to-blue-400 bg-clip-text text-transparent mb-8">Lịch Sử Đơn Hàng</h2>

            <!-- Hiển thị thông báo thành công nếu có -->
            <?php if (isset($_GET['success'])) { ?>
                <div class="glass-container border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg animate-pulse">
                    <p><?php echo htmlspecialchars($_GET['success']); ?></p>
                </div>
            <?php } ?>

            <!-- Hiển thị thông báo lỗi nếu có -->
            <?php if (isset($error_message)) { ?>
                <div class="glass-container border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg animate-pulse">
                    <p><?php echo htmlspecialchars($error_message); ?></p>
                </div>
            <?php } else if (mysqli_num_rows($result) > 0) { ?>
                <!-- Bảng danh sách đơn hàng -->
                <div class="glass-container rounded-lg shadow-lg p-6 mb-6">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b">
                                <th class="p-4">Mã Đơn Hàng</th>
                                <th class="p-4">Ngày Đặt</th>
                                <th class="p-4">Tổng Tiền</th>
                                <th class="p-4">Trạng Thái</th>
                                <th class="p-4">Hành Động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($order = mysqli_fetch_assoc($result)) { ?>
                                <tr class="order-item border-b">
                                    <td class="p-4" data-label="Mã Đơn Hàng">#<?php echo $order['id']; ?></td>
                                    <td class="p-4" data-label="Ngày Đặt"><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                    <td class="p-4 text-pink-600 font-semibold" data-label="Tổng Tiền"><?php echo number_format($order['total_price'], 0, ',', '.') . ' VNĐ'; ?></td>
                                    <td class="p-4" data-label="Trạng Thái">
                                        <span class="status-badge <?php
                                            if ($order['status'] == 'pending') echo 'bg-yellow-100 text-yellow-800';
                                            elseif ($order['status'] == 'processing') echo 'bg-orange-100 text-orange-800';
                                            elseif ($order['status'] == 'shipped') echo 'bg-blue-100 text-blue-800';
                                            elseif ($order['status'] == 'delivered') echo 'bg-green-100 text-green-800';
                                            elseif ($order['status'] == 'cancelled') echo 'bg-red-100 text-red-800';
                                        ?>">
                                            <?php echo translateStatus($order['status']); ?>
                                        </span>
                                    </td>
                                    <td class="p-4" data-label="Hành Động">
                                        <?php if (isset($_GET['view_details']) && $_GET['view_details'] == $order['id']) { ?>
                                            <a href="order_history.php" class="action-button">
                                                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                                Ẩn Chi Tiết
                                            </a>
                                        <?php } else { ?>
                                            <a href="order_history.php?view_details=<?php echo $order['id']; ?>" class="action-button">
                                                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                                Xem Chi Tiết
                                            </a>
                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                <!-- Khu vực hiển thị chi tiết đơn hàng -->
                <?php if ($selected_order) { ?>
                    <div class="glass-container rounded-lg shadow-lg p-6">
                        <h4 class="text-lg font-semibold bg-gradient-to-r from-pink-400 to-blue-400 bg-clip-text text-transparent mb-4">Chi Tiết Đơn Hàng #<?php echo $selected_order['id']; ?></h4>

                        <!-- Thông tin khách hàng -->
                        <div class="mb-6">
                            <h5 class="text-md font-semibold mb-2">Thông Tin Khách Hàng</h5>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-gray-700">
                                <p><strong>Họ Tên:</strong> <?php echo htmlspecialchars($selected_order['customer_name']); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($selected_order['customer_email']); ?></p>
                                <p><strong>Số Điện Thoại:</strong> <?php echo htmlspecialchars($selected_order['customer_phone']); ?></p>
                                <p><strong>Địa Chỉ:</strong> <?php echo htmlspecialchars($selected_order['customer_address']); ?></p>
                            </div>
                        </div>

                        <!-- Danh sách sản phẩm -->
                        <div class="mb-6">
                            <h5 class="text-md font-semibold mb-2">Danh Sách Sản Phẩm</h5>
                            <div class="details-table-container">
                                <table class="details-table w-full text-left">
                                    <thead>
                                        <tr class="border-b">
                                            <th class="p-2">Sản Phẩm</th>
                                            <th class="p-2">Tên</th>
                                            <th class="p-2">Giá</th>
                                            <th class="p-2">Số Lượng</th>
                                            <th class="p-2">Tổng</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $order_id = $selected_order['id'];
                                        $details_query = "SELECT od.*, p.name, p.image FROM order_details od JOIN products p ON od.product_id = p.id WHERE od.order_id = '$order_id'";
                                        $details_result = mysqli_query($conn, $details_query);
                                        if ($details_result) {
                                            $subtotal = 0;
                                            while ($detail = mysqli_fetch_assoc($details_result)) {
                                                $subtotal += $detail['subtotal'];
                                        ?>
                                                <tr class="border-b">
                                                    <td class="p-2">
                                                        <img src="../assets/images/<?php echo $detail['image']; ?>" alt="<?php echo $detail['name']; ?>" class="w-16 h-16 object-cover rounded-lg">
                                                    </td>
                                                    <td class="p-2"><?php echo $detail['name']; ?></td>
                                                    <td class="p-2 text-pink-600 font-semibold"><?php echo number_format($detail['price'], 0, ',', '.') . ' VNĐ'; ?></td>
                                                    <td class="p-2"><?php echo $detail['quantity']; ?></td>
                                                    <td class="p-2 text-pink-600 font-semibold"><?php echo number_format($detail['subtotal'], 0, ',', '.') . ' VNĐ'; ?></td>
                                                </tr>
                                        <?php
                                            }
                                        } else {
                                            echo "<tr><td colspan='5' class='p-2 text-red-500'>Lỗi khi lấy chi tiết đơn hàng: " . mysqli_error($conn) . "</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Tổng quan đơn hàng -->
                        <div class="mb-6">
                            <h5 class="text-md font-semibold mb-2">Tổng Quan Đơn Hàng</h5>
                            <div class="space-y-2 text-gray-700">
                                <p><strong>Tổng Tiền Sản Phẩm:</strong> <span class="text-pink-600 font-semibold"><?php echo number_format($subtotal, 0, ',', '.') . ' VNĐ'; ?></span></p>
                                <p><strong>Phí Vận Chuyển:</strong> <span class="text-pink-600 font-semibold">30,000 VNĐ</span></p>
                                <p><strong>Tổng Cộng:</strong> <span class="text-pink-600 font-semibold"><?php echo number_format($subtotal + 30000, 0, ',', '.') . ' VNĐ'; ?></span></p>
                            </div>
                        </div>

                        <!-- Hành động -->
                        <?php if ($selected_order['status'] == 'pending') { ?>
                            <div>
                                <h5 class="text-md font-semibold mb-2">Hành Động</h5>
                                <form method="POST" action="" onsubmit="return confirmCancel(this);">
                                    <input type="hidden" name="order_id" value="<?php echo $selected_order['id']; ?>">
                                    <button type="submit" name="cancel_order" class="cancel-button">
                                        <span class="button-text">Hủy Đơn Hàng</span>
                                        <div class="loading-spinner inline-block" id="loadingSpinner"></div>
                                    </button>
                                </form>
                            </div>
                        <?php } ?>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <div class="text-center">
                    <img src="https://cdn-icons-png.flaticon.com/512/2922/2922510.png" alt="No Orders" class="w-32 mx-auto mb-4 animate-bounce">
                    <p class="text-gray-500 text-lg">Bạn chưa có đơn hàng nào.</p>
                    <a href="category.php" class="mt-4 inline-block shopping-button">Tiếp Tục Mua Sắm</a>
                </div>
            <?php } ?>
        </div>
    </section>

    <!-- Footer -->
    <?php include('../components/footer.php'); ?>

    <script>
        function confirmCancel(form) {
            if (confirm('Bạn có chắc chắn muốn hủy đơn hàng này không?')) {
                const button = form.querySelector('button');
                const buttonText = button.querySelector('.button-text');
                const loadingSpinner = button.querySelector('.loading-spinner');

                button.disabled = true;
                buttonText.style.opacity = '0';
                loadingSpinner.style.display = 'inline-block';

                setTimeout(() => {
                    button.disabled = false;
                    buttonText.style.opacity = '1';
                    loadingSpinner.style.display = 'none';
                }, 2000);

                return true;
            }
            return false;
        }
    </script>
</body>
</html>