<?php
session_start();
include('../../config/database.php');

// Kiểm tra kết nối cơ sở dữ liệu
if (!$conn) {
    error_log("Database connection failed: " . mysqli_connect_error());
    die("Database connection failed: " . mysqli_connect_error());
}

// Truy vấn danh sách khách hàng
$user_query = "SELECT id, username FROM user";
$user_result = $conn->query($user_query);

// Truy vấn danh sách đơn hàng với tên khách hàng
$query = "SELECT o.*, u.username as customer_name 
          FROM orders o 
          LEFT JOIN user u ON o.id = u.id 
          ORDER BY o.id DESC";
$stmt = $conn->prepare($query);
if ($stmt === false) {
    error_log("Prepare failed: " . $conn->error);
    die("Prepare failed: " . $conn->error);
}
$stmt->execute();
$result = $stmt->get_result();

// Xử lý AJAX request để thêm đơn hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = intval($_POST['user_id']);
    $created_at = $_POST['created_at'];
    $total_price = floatval($_POST['total_price']);
    $status = $_POST['status'];

    // Server-side validation
    if ($user_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng chọn khách hàng!']);
        exit;
    }
    if (empty($created_at)) {
        echo json_encode(['success' => false, 'message' => 'Ngày đặt hàng không được để trống!']);
        exit;
    }
    if ($total_price <= 0) {
        echo json_encode(['success' => false, 'message' => 'Tổng tiền phải lớn hơn 0!']);
        exit;
    }

    // Thêm đơn hàng vào cơ sở dữ liệu
    $insert_query = "INSERT INTO orders (user_id, created_at, total_price, status) VALUES (?, ?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_query);
    if ($insert_stmt === false) {
        error_log("Prepare failed: " . $conn->error);
        echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống khi chuẩn bị truy vấn.']);
        exit;
    }

    $insert_stmt->bind_param("isds", $user_id, $created_at, $total_price, $status);
    if ($insert_stmt->execute()) {
        $new_order_id = $insert_stmt->insert_id;
        $user_stmt = $conn->prepare("SELECT username FROM users WHERE user_id = ?");
        $user_stmt->bind_param("i", $user_id);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();
        $customer_name = $user_result->fetch_assoc()['username'] ?? 'Khách hàng không xác định';
        $user_stmt->close();

        echo json_encode([
            'success' => true,
            'message' => 'Đơn hàng đã được thêm thành công!',
            'order' => [
                'order_id' => $new_order_id,
                'user_id' => $user_id,
                'customer_name' => $customer_name,
                'created_at' => $created_at,
                'total_price' => $total_price,
                'status' => $status
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi khi thêm đơn hàng: ' . $insert_stmt->error]);
    }
    $insert_stmt->close();
    $conn->close();
    exit;
}
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
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-6xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Danh sách Đơn Hàng</h2>
            <button onclick="loadContent('modules/qldh/add_order.php', 'Thêm Đơn hàng')" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors flex items-center">
                <i class="fas fa-plus mr-2"></i> Thêm Đơn hàng
            </button>
        </div>

        <!-- Form thêm đơn hàng -->
        <div id="addOrderForm" class="bg-white p-6 rounded-lg shadow-lg mb-6 hidden">
            <h3 class="text-xl font-semibold text-gray-800 mb-4">Thêm Đơn Hàng</h3>
            <form id="addOrderFormAjax" onsubmit="submitAddOrderForm(event)">
                <div class="mb-4">
                    <label for="user_id" class="block text-sm text-gray-600 mb-2">Khách Hàng</label>
                    <select name="user_id" id="user_id" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                        <option value="">Chọn khách hàng</option>
                        <?php
                        if ($user_result->num_rows > 0) {
                            while ($user = $user_result->fetch_assoc()) {
                                echo "<option value='{$user['user_id']}'>" . htmlspecialchars($user['username']) . "</option>";
                            }
                        }
                        $user_result->close();
                        ?>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="created_at" class="block text-sm text-gray-600 mb-2">Ngày Đặt</label>
                    <input type="date" id="created_at" name="created_at" required class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                </div>
                <div class="mb-4">
                    <label for="total_price" class="block text-sm text-gray-600 mb-2">Tổng Tiền (VND)</label>
                    <input type="number" id="total_price" name="total_price" required class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" min="1">
                </div>
                <div class="mb-4">
                    <label for="status" class="block text-sm text-gray-600 mb-2">Trạng Thái</label>
                    <select name="status" id="status" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                        <option value="Chờ xử lý">Chờ xử lý</option>
                        <option value="Đang giao">Đang giao</option>
                        <option value="Hoàn thành">Hoàn thành</option>
                        <option value="Hủy đơn">Hủy đơn</option>
                    </select>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors">Thêm</button>
                    <button type="button" onclick="toggleForm()" class="bg-gray-300 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-400 transition-colors">Hủy</button>
                </div>
            </form>
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
                            echo "<tr class='border-b hover:bg-gray-50' data-id='{$row['id']}'>
                                    <td class='p-3'>" . htmlspecialchars($row['id']) . "</td>
                                    <td class='p-3'>" . htmlspecialchars($row['customer_name'] ?? 'Khách hàng không xác định') . "</td>
                                    <td class='p-3'>" . htmlspecialchars($row['created_at']) . "</td>
                                    <td class='p-3'>" . number_format($row['total_price']) . "</td>
                                    <td class='p-3'>" . htmlspecialchars($row['status']) . "</td>
                                    <td class='p-3'>
                                        <button class='text-blue-500 hover:text-blue-700 mr-3' onclick=\"window.location.href='edit_order.php?id={$row['id']}'\">
                                            <i class='fas fa-edit'></i>
                                        </button>
                                        <button class='text-red-500 hover:text-red-700 delete-btn' data-order-id='{$row['id']}'>
                                            <i class='fas fa-trash-alt'></i>
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

    <script>
        // Hiển thị/Ẩn form thêm đơn hàng
        function toggleForm() {
            const form = document.getElementById('addOrderForm');
            if (form.classList.contains('hidden')) {
                form.classList.remove('hidden');
            } else {
                form.classList.add('hidden');
                document.getElementById('addOrderFormAjax').reset();
            }
        }

        // Gửi form thêm đơn hàng bằng AJAX
        function submitAddOrderForm(event) {
            event.preventDefault();

            const userId = parseInt(document.getElementById('user_id').value);
            const createdAt = document.getElementById('created_at').value;
            const totalPrice = parseFloat(document.getElementById('total_price').value);

            // Client-side validation
            if (!userId) {
                showToast("Vui lòng chọn khách hàng!", "error");
                return;
            }
            if (!createdAt) {
                showToast("Ngày đặt hàng không được để trống!", "error");
                return;
            }
            if (totalPrice <= 0) {
                showToast("Tổng tiền phải lớn hơn 0!", "error");
                return;
            }

            const formData = new FormData(document.getElementById('addOrderFormAjax'));
            fetch('modules/qlorder/orders.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, "success");

                    // Thêm đơn hàng mới vào bảng
                    const tbody = document.getElementById('orderTableBody');
                    if (tbody) {
                        if (tbody.querySelector('tr td[colspan="6"]')) {
                            tbody.innerHTML = '';
                        }
                        const newRow = document.createElement('tr');
                        newRow.className = 'border-b hover:bg-gray-50';
                        newRow.setAttribute('data-order-id', data.order.order_id);
                        newRow.innerHTML = `
                            <td class='p-3'>${data.order.order_id}</td>
                            <td class='p-3'>${data.order.customer_name}</td>
                            <td class='p-3'>${data.order.created_at}</td>
                            <td class='p-3'>${Number(data.order.total_price).toLocaleString()}</td>
                            <td class='p-3'>${data.order.status}</td>
                            <td class='p-3'>
                                <button class='text-blue-500 hover:text-blue-700 mr-3' onclick="window.location.href='edit_order.php?id=${data.order.order_id}'">
                                    <i class='fas fa-edit'></i>
                                </button>
                                <button class='text-red-500 hover:text-red-700 delete-btn' data-order-id='${data.order.order_id}'>
                                    <i class='fas fa-trash-alt'></i>
                                </button>
                            </td>
                        `;
                        tbody.insertBefore(newRow, tbody.firstChild);

                        // Gắn lại sự kiện cho nút Delete mới
                        attachDeleteEvents();
                    }

                    // Ẩn form và reset
                    toggleForm();
                } else {
                    showToast(data.message, "error");
                }
            })
            .catch(error => {
                showToast("Lỗi khi thêm đơn hàng: " + error.message, "error");
            });
        }

        // Xóa đơn hàng bằng AJAX
        function deleteOrder(orderId) {
            if (confirm('Bạn có chắc chắn muốn xóa đơn hàng này không?')) {
                fetch(`delete_order.php?id=${orderId}`, { method: 'GET' })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! Status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            showToast("Xóa đơn hàng thành công!", "success");
                            const row = document.querySelector(`tr[data-order-id="${orderId}"]`);
                            if (row) {
                                row.remove();
                            }
                            const tbody = document.getElementById('orderTableBody');
                            if (tbody.children.length === 0) {
                                tbody.innerHTML = "<tr><td colspan='6' class='p-3 text-center text-gray-500'>Không có đơn hàng nào.</td></tr>";
                            }
                        } else {
                            showToast("Lỗi khi xóa đơn hàng: " + data.message, "error");
                        }
                    })
                    .catch(error => {
                        showToast("Lỗi khi xóa đơn hàng: " + error.message, "error");
                    });
            }
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
            deleteOrder(orderId);
        }

        // Hiển thị toast notification
        function showToast(message, type) {
            const toast = document.createElement('div');
            toast.className = `toast p-4 rounded-lg text-white ${type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
            toast.textContent = message;
            document.body.appendChild(toast);
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