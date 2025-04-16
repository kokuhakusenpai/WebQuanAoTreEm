<?php
// filepath: d:\Thực tập 1\Web\Web\WebQuanAoTreEmMain\PHP\search_data.php
header('Content-Type: application/json');

// Cấu hình kết nối cơ sở dữ liệu
$host = 'localhost';
$dbname = 'baby_shop';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
} catch (PDOException $e) {
    die("Kết nối cơ sở dữ liệu thất bại: " . $e->getMessage());
}

// Lấy tham số truy vấn từ URL
$query = isset($_GET['query']) ? trim($_GET['query']) : '';
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'default';

// Xử lý sắp xếp
switch ($sort_by) {
    case 'Tên A → Z':
        $order_by = 'name ASC';
        break;
    case 'Tên Z → A':
        $order_by = 'name DESC';
        break;
    case 'Giá tăng dần':
        $order_by = 'price ASC';
        break;
    case 'Giá giảm dần':
        $order_by = 'price DESC';
        break;
    case 'Hàng mới':
        $order_by = 'product_id DESC';
        break;
    default:
        $order_by = 'product_id DESC';
        break;
}

// Truy vấn dữ liệu
$sql = "SELECT * FROM products WHERE name LIKE :query OR description LIKE :query ORDER BY $order_by";
$stmt = $pdo->prepare($sql);
$stmt->execute(['query' => '%' . $query . '%']);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Trả về dữ liệu JSON
echo json_encode([
    'query' => $query,
    'products' => $products
]);
?>