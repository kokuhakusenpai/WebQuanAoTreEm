<?php
// filepath: c:\xampp\htdocs\webqa\WebQuanAoTreEmMain\PHP\get_products_by_category.php

$host = 'localhost';
$dbname = 'baby_shop';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
} catch (PDOException $e) {
    die("Kết nối cơ sở dữ liệu thất bại: " . $e->getMessage());
}

$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
$products = [];

if ($category_id > 0) {
    // Lấy danh sách category_id con (bao gồm cả chính nó)
    $sql = "SELECT category_id FROM categories WHERE parent_id = :category_id OR category_id = :category_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['category_id' => $category_id]);
    $categoryIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!empty($categoryIds)) {
        // Tạo chuỗi placeholders như (?, ?, ?)
        $placeholders = implode(',', array_fill(0, count($categoryIds), '?'));

        // Truy vấn sản phẩm theo danh sách category_id
        $sql = "SELECT * FROM products WHERE category_id IN ($placeholders) AND status = 'available'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($categoryIds);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

header('Content-Type: application/json');
echo json_encode($products);
?>
