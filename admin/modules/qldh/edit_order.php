<?php
include('../../config/database.php');

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = "SELECT * FROM orders WHERE order_id = $id";
    $result = mysqli_query($conn, $query);
    $order = mysqli_fetch_assoc($result);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $customer_name = $_POST['customer_name'];
    $order_date = $_POST['order_date'];
    $total_price = $_POST['total_price'];
    $status = $_POST['status'];

    $update_query = "UPDATE orders SET customer_name='$customer_name', order_date='$order_date', total_price='$total_price', status='$status' WHERE order_id=$id";
    if (mysqli_query($conn, $update_query)) {
        header("Location: orders.php");
    } else {
        echo "Lỗi: " . mysqli_error($conn);
    }
}
?>

<h2>Sửa Đơn Hàng</h2>
<form method="POST">
    <label>Tên Khách Hàng:</label>
    <input type="text" name="customer_name" value="<?= $order['customer_name'] ?>" required>
    
    <label>Ngày Đặt:</label>
    <input type="date" name="order_date" value="<?= $order['order_date'] ?>" required>
    
    <label>Tổng Tiền:</label>
    <input type="number" name="total_price" value="<?= $order['total_price'] ?>" required>
    
    <label>Trạng Thái:</label>
    <select name="status">
        <option value="Chờ xử lý" <?= $order['status'] == 'Chờ xử lý' ? 'selected' : '' ?>>Chờ xử lý</option>
        <option value="Đang giao" <?= $order['status'] == 'Đang giao' ? 'selected' : '' ?>>Đang giao</option>
        <option value="Hoàn thành" <?= $order['status'] == 'Hoàn thành' ? 'selected' : '' ?>>Hoàn thành</option>
        <option value="Hủy đơn" <?= $order['status'] == 'Hủy đơn' ? 'selected' : '' ?>>Hủy đơn</option>
    </select>

    <button type="submit">Cập nhật</button>
</form>
