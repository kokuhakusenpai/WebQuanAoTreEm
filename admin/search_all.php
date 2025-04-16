<?php
header('Content-Type: application/json'); // Đảm bảo trả về dữ liệu JSON
include('../config/database.php');

// Đọc từ khóa tìm kiếm từ AJAX
$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['query'])) {
    echo json_encode(['success' => false, 'message' => 'Từ khóa tìm kiếm không hợp lệ.']);
    exit;
}

// Từ khóa tìm kiếm
$query = $data['query'];

// Kiểm tra danh sách bảng
$tables = ['users', 'products', 'orders'];
$results = [];

foreach ($tables as $table) {
    $searchQuery = "SELECT * FROM $table WHERE CONCAT_WS('', username, email, role, status) LIKE ?";
    $stmt = $conn->prepare($searchQuery);
    $searchTerm = "%" . $query . "%";
    $stmt->bind_param("s", $searchTerm);
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'message' => "Lỗi trong bảng $table."]);
        exit;
    }
    $result = $stmt->get_result();

    $tableResults = [];
    while ($row = $result->fetch_assoc()) {
        $tableResults[] = $row;
    }

    if (!empty($tableResults)) {
        $results[] = [
            'table' => $table,
            'data' => $tableResults
        ];
    }
}

// Trả về kết quả tìm kiếm dạng JSON
echo json_encode(['success' => true, 'results' => $results]);
exit;