<?php
$host = 'localhost';
$dbname = 'baby_shop';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
} catch (PDOException $e) {
    die("Kết nối cơ sở dữ liệu thất bại: " . $e->getMessage());
}

$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;

if ($product_id > 0) {
    $sql = "SELECT * FROM products WHERE product_id = :product_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['product_id' => $product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch reviews for the product
    $review_sql = "SELECT r.rating, r.comment, r.created_at, u.username 
                   FROM reviews r 
                   LEFT JOIN users u ON r.user_id = u.user_id 
                   WHERE r.product_id = :product_id 
                   ORDER BY r.created_at DESC";
    $review_stmt = $pdo->prepare($review_sql);
    $review_stmt->execute(['product_id' => $product_id]);
    $reviews = $review_stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $product = false;
    $reviews = [];
}

header('Content-Type: application/json');
echo json_encode(['product' => $product, 'reviews' => $reviews]);
?>