<?php
// filepath: d:\Thực tập 1\Web\Web\WebQuanAoTreEmMain\PHP\get_vouchers.php
session_start();

// Kết nối cơ sở dữ liệu
$pdo = new PDO('mysql:host=localhost;dbname=baby_shop;charset=utf8', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Lấy danh sách voucher còn hạn sử dụng
$sql_vouchers = "SELECT * FROM vouchers WHERE expiry_date >= CURDATE()";
$stmt_vouchers = $pdo->prepare($sql_vouchers);
$stmt_vouchers->execute();
$vouchers = $stmt_vouchers->fetchAll(PDO::FETCH_ASSOC);

// Trả về JSON
header('Content-Type: application/json');
echo json_encode($vouchers);
?>