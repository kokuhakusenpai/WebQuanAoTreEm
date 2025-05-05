<?php
session_start();
include('../../config/database.php');

// Kiểm tra kết nối cơ sở dữ liệu
if (!$conn) {
    error_log("Database connection failed: " . mysqli_connect_error());
    die("Database connection failed: " . mysqli_connect_error());
}

// Truy vấn danh sách đơn hàng với tên khách hàng
$query = "SELECT o.*, u.username as customer_name 
          FROM orders o 
          LEFT JOIN user u ON o.user_id = u.id 
          ORDER BY o.id DESC";
$stmt = $conn->prepare($query);
if ($stmt === false) {
    error_log("Prepare failed: " . $conn->error);
    die("Prepare failed: " . $conn->error);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Đơn Hàng</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="styles.css" />
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
    <div class="max-w-6xl mx-auto">
        <div class="flex justify-between items-center mb-6"></div>

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

        <!-- Modal xác nhận xóa -->
        <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center hidden z-50">
            <div class="bg-white p-6 rounded-lg shadow-lg w-96">
                <h2 class="text-xl font-semibold mb-4">Xác nhận xóa</h2>
                <p class="mb-4">Bạn có chắc muốn xóa đơn hàng này?</p>
                <div class="flex justify-end space-x-4">
                    <button onclick="cancelDelete()" class="bg-gray-300 text-black px-4 py-2 rounded hover:bg-gray-400">Hủy</button>
                    <button id="confirmDeleteBtn" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">OK</button>
                </div>
            </div>
        </div>

        <!-- Danh sách Đơn Hàng -->
        <div class="bg-white rounded-lg shadow-sm overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="bg-gray-200 text-gray-700">
                        <th class="p-3 text-left">ID Đơn Hàng</th>
                        <th class="p-3 text-left">Khách Hàng</th>
                        <th class="p-3 text-left">Ngày Đặt</th>
                        <th class="p-3 text-left">Tổng Tiền (VND)</th>
                        <th class="p-3 text-left">Trạng Thái</th>
                        <th class="p-3 text-left">Thao Tác</th>
                    </tr>
                </thead>
                <tbody id="orderTableBody">
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            // Tạo lớp CSS cho trạng thái
                            $statusClass = '';
                            switch($row['status']) {
                                case 'pending':
                                    $statusClass = 'bg-yellow-100 text-yellow-800';
                                    break;
                                case 'processing':
                                    $statusClass = 'bg-blue-100 text-blue-800';
                                    break;
                                case 'completed':
                                    $statusClass = 'bg-green-100 text-green-800';
                                    break;
                                case 'cancelled':
                                    $statusClass = 'bg-red-100 text-red-800';
                                    break;
                                default:
                                    $statusClass = 'bg-gray-100 text-gray-800';
                            }
                            
                            echo "<tr class='border-b hover:bg-gray-50' data-id='{$row['id']}'>
                                    <td class='p-3'>" . htmlspecialchars($row['id']) . "</td>
                                    <td class='p-3'>" . htmlspecialchars($row['customer_name'] ?? 'Khách hàng không xác định') . "</td>
                                    <td class='p-3'>" . htmlspecialchars($row['created_at']) . "</td>
                                    <td class='p-3'>" . number_format($row['total_price']) . "</td>
                                    <td class='p-3'>
                                        <span class='px-2 py-1 text-xs font-semibold rounded $statusClass'>
                                            " . htmlspecialchars($row['status']) . "
                                        </span>
                                    </td>
                                    <td class='p-3 flex space-x-2'>
                                        <a href='view_order.php?id={$row['id']}' class='text-blue-500 hover:text-blue-700'>
                                            <i class='fas fa-eye'></i> Xem
                                        </a>
                                        <button class='text-red-500 hover:text-red-700 delete-btn ml-3' data-order-id='{$row['id']}'>
                                            <i class='fas fa-trash-alt'></i> Xóa
                                        </button>
                                    </td>
                                </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' class='p-3 text-center text-gray-500'>Không có đơn hàng nào.</td></tr>";
                    }
                    $stmt->close();
                    $conn->close();
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Toast container -->
    <div id="toast-container"></div>

    <script>
        let currentOrderId = null;

        // Hiển thị modal xác nhận xóa
        function showDeleteModal(orderId) {
            currentOrderId = orderId;
            document.getElementById('deleteModal').classList.remove('hidden');
            document.getElementById('confirmDeleteBtn').onclick = function() {
                deleteOrder(orderId);
            };
        }

        // Hủy xóa và đóng modal
        function cancelDelete() {
            document.getElementById('deleteModal').classList.add('hidden');
            currentOrderId = null;
        }

        // Xóa đơn hàng bằng AJAX
        function deleteOrder(orderId) {
            fetch(`delete_order.php?id=${orderId}`, { 
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    showToast("Xóa đơn hàng thành công!", "success");
                    const row = document.querySelector(`tr[data-id="${orderId}"]`);
                    if (row) {
                        row.remove();
                    }
                    const tbody = document.getElementById('orderTableBody');
                    if (tbody.children.length === 0) {
                        tbody.innerHTML = "<tr><td colspan='6' class='p-3 text-center text-gray-500'>Không có đơn hàng nào.</td></tr>";
                    }
                    document.getElementById('deleteModal').classList.add('hidden');
                } else {
                    showToast("Lỗi khi xóa đơn hàng: " + data.message, "error");
                    document.getElementById('deleteModal').classList.add('hidden');
                }
            })
            .catch(error => {
                showToast("Lỗi khi xóa đơn hàng: " + error.message, "error");
                document.getElementById('deleteModal').classList.add('hidden');
            });
        }

        // Gắn sự kiện cho các nút Delete
        function attachDeleteEvents() {
            const deleteButtons = document.querySelectorAll('.delete-btn');
            deleteButtons.forEach(button => {
                button.removeEventListener('click', handleDeleteClick);
                button.addEventListener('click', handleDeleteClick);
            });
        }

        function handleDeleteClick() {
            const orderId = this.getAttribute('data-order-id');
            showDeleteModal(orderId);
        }

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

        // Gắn sự kiện ban đầu khi trang tải
        document.addEventListener('DOMContentLoaded', () => {
            attachDeleteEvents();
        });
    </script>
</body>
</html>