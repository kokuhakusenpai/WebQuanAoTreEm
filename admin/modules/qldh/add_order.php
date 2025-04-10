<?php
include('../../config/database.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $customer_name = $_POST['customer_name'];
    $order_date = $_POST['order_date'];
    $total_price = $_POST['total_price'];
    $status = $_POST['status'];

    $query = "INSERT INTO orders (customer_name, order_date, total_price, status) VALUES ('$customer_name', '$order_date', '$total_price', '$status')";
    if (mysqli_query($conn, $query)) {
        header("Location: orders.php");
    } else {
        echo "Lỗi: " . mysqli_error($conn);
    }
}
?>

<h2>Thêm Đơn Hàng</h2>
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
</form>
