<?php
// Xác định base_url tự động (dùng cho mọi link)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$project_folder = str_replace('/config', '', dirname($_SERVER['SCRIPT_NAME'])); // Xóa /config nếu cần
$base_url = $protocol . "://" . $host . $project_folder . '/';
?>
