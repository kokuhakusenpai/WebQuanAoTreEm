<?php
// Kết nối cơ sở dữ liệu
$pdo = new PDO('mysql:host=localhost;dbname=baby_shop;charset=utf8', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Lấy loại sản phẩm từ query string
$type = $_GET['type'] ?? '';

if ($type === 'best_sellers') {
    $sql = "SELECT * FROM products WHERE is_best_seller = 1";
} elseif ($type === 'featured_products') {
    $sql = "SELECT * FROM products WHERE is_featured = 1";
} else {
    echo json_encode([]);
    exit;
}

// Thực thi truy vấn
$stmt = $pdo->query($sql);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Trả về dữ liệu JSON
header('Content-Type: application/json');
echo json_encode($products);
?>