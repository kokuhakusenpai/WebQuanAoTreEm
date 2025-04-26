<?php
include 'config.php';

$page = intval($_GET['page'] ?? 1);
$genderId = intval($_GET['genderId'] ?? 0); // Get genderId from query string
$limit = 8; // Hiển thị 8 sản phẩm trên mỗi trang
$offset = ($page - 1) * $limit;

// Build the SQL query
$sql = "SELECT p.* FROM products p JOIN categories c ON p.category_id = c.category_id";
$countSql = "SELECT COUNT(*) AS total FROM products p JOIN categories c ON p.category_id = c.category_id";

if ($genderId > 0) {
    $sql .= " WHERE c.parent_id = ? OR c.category_id = ?";
    $countSql .= " WHERE c.parent_id = ? OR c.category_id = ?";
}

// Prepare and execute the count query
$totalStmt = $conn->prepare($countSql);
if ($genderId > 0) {
    $totalStmt->bind_param("ii", $genderId, $genderId);
}
$totalStmt->execute();
$total = $totalStmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($total / $limit); // Tính tổng số trang

// Prepare and execute the product query
$sql .= " LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
if ($genderId > 0) {
    $stmt->bind_param("iiii", $genderId, $genderId, $limit, $offset);
} else {
    $stmt->bind_param("ii", $limit, $offset);
}
$stmt->execute();
$result = $stmt->get_result();

$products = [];
while ($row = $result->fetch_assoc()) {
    // Lấy ảnh đầu tiên từ image_url
    $images = explode(',', $row['image_url']); // Tách chuỗi image_url thành mảng
    $row['image'] = $images[0]; // Lấy ảnh đầu tiên
    $products[] = $row;
}

// Return JSON response
echo json_encode([
    'products' => $products,
    'totalPages' => $totalPages // Tổng số trang
]);
?>