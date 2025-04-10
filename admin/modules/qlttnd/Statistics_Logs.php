<?php
session_start();
include('../../config/database.php');

// Kiểm tra nếu session chưa có thông tin user_id
if (!isset($_SESSION['user_id'])) {
    die("Bạn cần đăng nhập để truy cập!");
}

// Kiểm tra kết nối cơ sở dữ liệu
if (!$conn) {
    die("Kết nối cơ sở dữ liệu thất bại: " . mysqli_connect_error());
}

// Hàm lấy dữ liệu cho các bảng
function getRecentLogs() {
    global $conn;
    $query = "SELECT users.username, action, timestamp, ip_address 
              FROM user_logs 
              JOIN users ON user_logs.user_id = users.user_id 
              ORDER BY timestamp DESC 
              LIMIT 10";
    return mysqli_query($conn, $query);
}

function getOldestLogs() {
    global $conn;
    $query = "SELECT users.username, action, timestamp, ip_address 
              FROM user_logs 
              JOIN users ON user_logs.user_id = users.user_id 
              ORDER BY timestamp ASC 
              LIMIT 10";
    return mysqli_query($conn, $query);
}

function getActionStatistics() {
    global $conn;
    $query = "SELECT action, COUNT(*) as count 
              FROM user_logs 
              GROUP BY action 
              ORDER BY count DESC";
    return mysqli_query($conn, $query);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Thao Tác Người Dùng</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <ul>
        <li><a href="#" onclick="loadContent('modules/qlttnd/user_activity.php')">Hoạt Động Của Người Dùng</a></li>
        <li><a href="#" onclick="loadContent('modules/qlttnd/role_management.php')">Quản Lý Quyền Hạn</a></li>
        <li><a href="#" onclick="loadContent('modules/qlttnd/admin_log.php')">Nhật Ký Thao Tác Và Báo Cáo Cho Admin</a></li>
    </ul>
    <script>
    function showTab(tabId) {
        document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
        const selectedTab = document.getElementById(tabId);
        if (selectedTab) {
            selectedTab.classList.add('active');
        }
        }
    </script>
</body>
</html>