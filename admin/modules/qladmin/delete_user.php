<?php
session_start();
include('../../config/database.php');

// Kiểm tra kết nối cơ sở dữ liệu
if (!$conn) {
    error_log("Database connection failed: " . mysqli_connect_error());
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

if (isset($_GET['id'])) {
    $user_id = intval($_GET['id']); // Chuyển đổi sang số nguyên để bảo mật

    // Xóa người dùng bằng prepared statement
    $delete_query = "DELETE FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($delete_query);
    if ($stmt === false) {
        error_log("Prepare failed: " . $conn->error);
        echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param("i", $user_id);
    if ($stmt->execute()) {
        error_log("User deleted successfully: user_id=$user_id");
        echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
    } else {
        error_log("Execute failed: " . $stmt->error);
        echo json_encode(['success' => false, 'message' => 'Execute failed: ' . $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
}

$conn->close();
?>