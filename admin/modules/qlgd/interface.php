<?php
session_start();
include('../../config/database.php');
$query = "SELECT * FROM settings WHERE id = 1";
$result = mysqli_query($conn, $query);
$settings = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Giao Diện</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <ul>
        <li><a href="#" onclick="loadContent('modules/qlgd/manhinh.php')">Màn hình</a></li>
        <li><a href="#" onclick="loadContent('modules/qlgd/banners.php')">Banner</a></li>
        <li><a href="#" onclick="loadContent('modules/qlgd/baiviet.php')">Bài viết</a></li>
    </ul>
    <script>
    function loadContent(tab) {
        // Ẩn tất cả các tab
        document.querySelectorAll('.tab-content').forEach(content => {
            content.style.display = 'none';
        });

        // Hiển thị tab được chọn
        const selectedTab = document.getElementById(tab);
        if (selectedTab) {
            selectedTab.style.display = 'block'; // Hiển thị tab
        }
    }
</script>

</body>
</html>
