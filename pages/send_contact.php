<?php
// Kết nối cơ sở dữ liệu
include('../config/database.php');

// Kiểm tra nếu form được gửi
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Lấy dữ liệu từ form
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    // Truy vấn để lưu thông tin vào cơ sở dữ liệu (nếu cần)
    $query = "INSERT INTO contact_requests (name, email, message) VALUES ('$name', '$email', '$message')";
    if (mysqli_query($conn, $query)) {
        header('Location: ../index.php');
    } else {
        // Nếu có lỗi xảy ra, hiển thị thông báo
        echo "Có lỗi xảy ra. Vui lòng thử lại.";
    }
}
?>
