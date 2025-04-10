<?php
session_start();
include('../../config/database.php');

// Xử lý dữ liệu từ form "Thêm Đơn Hàng"
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $customer_name = $_POST['user_id'];
    $order_date = $_POST['created_at'];
    $total_price = $_POST['total_price'];
    $status = $_POST['status'];

    // Truy vấn thêm đơn hàng vào cơ sở dữ liệu
    $insert_query = "INSERT INTO orders (user_id, created_at, total_price, status) 
                     VALUES ('$user_id', '$created_at', '$total_price', '$status')";
    mysqli_query($conn, $insert_query);

    // Tải lại trang sau khi thêm đơn hàng
    header("Location: orders.php");
    exit;
}

// Truy vấn danh sách đơn hàng
$query = "SELECT * FROM orders";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Đơn Hàng</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <h2 style="display: flex; justify-content: space-between; align-items: center;">
        Danh sách Đơn Hàng
        <button class="btn-add" onclick="toggleForm()">+ Thêm Đơn Hàng</button>
    </h2>

    <!-- Form thêm đơn hàng -->
    <div id="addOrderForm" style="display: none;">
        <h3>Thêm Đơn Hàng</h3>
        <form method="POST">
            <label>Tên Khách Hàng:</label>
            <input type="text" name="customer_name" required>
            
            <label>Ngày Đặt:</label>
            <input type="date" name="order_date" required>
        
            <label>Tổng Tiền:</label>
            <input type="number" name="total_price" required>
        
            <label>Trạng Thái:</label>
            <select name="status">
                <option value="Chờ xử lý">Chờ xử lý</option>
                <option value="Đang giao">Đang giao</option>
                <option value="Hoàn thành">Hoàn thành</option>
                <option value="Hủy đơn">Hủy đơn</option>
            </select>
        
            <button type="submit">Thêm</button>
            <button type="button" onclick="toggleForm()">Hủy</button>
        </form>
    </div>

    <!-- Danh sách Đơn Hàng -->
    <table class="orders-table">
        <thead>
            <tr>
                <th>ID Đơn Hàng</th>
                <th>Khách Hàng</th>
                <th>Ngày Đặt</th>
                <th>Tổng Tiền</th>
                <th>Trạng Thái</th>
                <th>Thao Tác</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (mysqli_num_rows($result) > 0) {
                while($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>
                            <td>" . $row['order_id'] . "</td>
                            <td>" . $row['customer_name'] . "</td>
                            <td>" . $row['order_date'] . "</td>
                            <td>" . $row['total_price'] . " VND</td>
                            <td>" . $row['status'] . "</td>
                            <td>
                                <a href='edit_order.php?id=" . $row['order_id'] . "' class='btn-edit'>Sửa</a>
                                <a href='#' onclick='deleteOrder(" . $row['order_id'] . ")' class='btn-delete'>Xóa</a>
                            </td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='6'>Không có đơn hàng nào.</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const form = document.getElementById('addOrderForm');
        form.style.display = 'none'; // Đảm bảo form ẩn lúc đầu
    
        window.toggleForm = function () {
            if (form.style.display === 'none' || form.style.display === '') {
                form.style.display = 'block';
            } else {
                form.style.display = 'none';
            }
        };
    });
    </script>
</body>
</html>