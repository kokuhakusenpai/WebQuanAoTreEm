<?php
session_start();
include('../../config/database.php');

// Kiểm tra kết nối cơ sở dữ liệu
if (!$conn) {
    error_log("Database connection failed: " . mysqli_connect_error());
    die("Database connection failed: " . mysqli_connect_error());
}

// Kiểm tra dữ liệu POST
if (!isset($_POST['order_id']) || !isset($_POST['status'])) {
    header("Location: orders.php");
    exit;
}

$order_id = intval($_POST['order_id']);
$status = $_POST['status'];

// Danh sách trạng thái hợp lệ
$valid_statuses = ['pending', 'processing', 'completed', 'cancelled'];

// Kiểm tra tính hợp lệ của trạng thái
if (!in_array($status, $valid_statuses)) {
    $_SESSION['error'] = "Trạng thái không hợp lệ";
    header("Location: view_order.php?id=" . $order_id);
    exit;
}

// Cập nhật trạng thái đơn hàng
$query = "UPDATE orders SET status = ? WHERE id = ?";
$stmt = $conn->prepare($query);
if ($stmt === false) {
    error_log("Prepare failed: " . $conn->error);
    $_SESSION['error'] = "Lỗi hệ thống";
    header("Location: view_order.php?id=" . $order_id);
    exit;
}

$stmt->bind_param("si", $status, $order_id);
if ($stmt->execute()) {
    $_SESSION['success'] = "Cập nhật trạng thái đơn hàng thành công";
} else {
    $_SESSION['error'] = "Lỗi khi cập nhật trạng thái đơn hàng: " . $stmt->error;
}

$stmt->close();
$conn->close();

header("Location: view_order.php?id=" . $order_id);
exit;
?>