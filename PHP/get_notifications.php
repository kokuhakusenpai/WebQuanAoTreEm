<?php
// filepath: d:\Thực tập 1\Web\Web\WebQuanAoTreEmMain\PHP\get_notifications.php
session_start();

// Kết nối cơ sở dữ liệu
$pdo = new PDO('mysql:host=localhost;dbname=baby_shop;charset=utf8', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Lấy danh sách thông báo
$sql_notifications = "SELECT * FROM notifications ORDER BY created_at DESC";
$stmt_notifications = $pdo->prepare($sql_notifications);
$stmt_notifications->execute();
$notifications = $stmt_notifications->fetchAll(PDO::FETCH_ASSOC);

// Đếm số thông báo chưa đọc
$unreadCount = 0;
foreach ($notifications as $notification) {
    if ($notification['status'] === 'unread') {
        $unreadCount++;
    }
}

// Trả về JSON
$response = [
    'notifications' => $notifications,
    'unreadCount' => $unreadCount
];

header('Content-Type: application/json');
echo json_encode($response);
?>