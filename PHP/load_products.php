<?php
// Kết nối cơ sở dữ liệu
$pdo = new PDO('mysql:host=127.0.0.1;dbname=baby_shop;charset=utf8', 'root', 'your_password_here');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Lấy tham số offset và limit
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 8;

// Truy vấn sản phẩm
$sql = "SELECT * FROM products ORDER BY product_id DESC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Trả về dữ liệu JSON
header('Content-Type: application/json');
echo json_encode($products);
?>