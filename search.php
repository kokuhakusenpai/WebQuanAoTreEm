<?php
// search.php

// Cấu hình kết nối cơ sở dữ liệu
$host = 'localhost';
$dbname = 'baby_shop';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
} catch(PDOException $e) {
    die("Kết nối cơ sở dữ liệu thất bại: " . $e->getMessage());
}

// Lấy tham số truy vấn từ URL
$query = isset($_GET['query']) ? trim($_GET['query']) : '';

// Nếu có từ khóa tìm kiếm thì truy vấn dữ liệu
if (!empty($query)) {
    $sql = "SELECT * FROM products WHERE name LIKE :query OR description LIKE :query";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['query' => '%' . $query . '%']);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $products = [];
}

// Xử lý sắp xếp từ dropdown
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'default';

switch($sort_by) {
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
        $order_by = 'product_id DESC'; // Mặc định theo ID sản phẩm
        break;
}

// Truy vấn dữ liệu với sắp xếp
$sql = "SELECT * FROM products WHERE name LIKE :query OR description LIKE :query ORDER BY $order_by";
$stmt = $pdo->prepare($sql);
$stmt->execute(['query' => '%' . $query . '%']);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Kết quả tìm kiếm</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<header class="bg-white shadow-md p-4 flex justify-between items-center">
    <a href="trangchu.php" class="text-blue-600 text-xl font-semibold">Trang chủ</a>
    <div class="flex items-center gap-2">
        <input 
            type="text" 
            id="searchInput" 
            placeholder="Tìm kiếm sản phẩm..."
            class="border border-gray-300 rounded px-3 py-1 focus:outline-none focus:ring-2 focus:ring-blue-400"
        >
        <button onclick="searchProducts()">
          <svg xmlns="http://www.w3.org/2000/svg" height="24" width="24" viewBox="0 0 512 512">
            <path d="M416 208c0 45.9-14.9 88.3-40 122.7L502.6 457.4c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L330.7 376c-34.4 25.2-76.8 40-122.7 40C93.1 416 0 322.9 0 208S93.1 0 208 0S416 93.1 416 208zM208 352a144 144 0 1 0 0-288 144 144 0 1 0 0 288z"/>
          </svg>
        </button>
        </div>
    <span class="text-gray-700 text-lg font-medium">Kết quả tìm kiếm cho: "<?php echo htmlspecialchars($query); ?>"</span>
</header>

<div class="container mx-auto p-4">
    <div class="mb-6">
        <select class="border rounded p-2 w-full sm:w-1/3" onchange="sortby(this)">
            <option value="default">Mặc định</option>
            <option value="Tên A → Z">Tên A → Z</option>
            <option value="Tên Z → A">Tên Z → A</option>
            <option value="Giá tăng dần">Giá tăng dần</option>
            <option value="Giá giảm dần">Giá giảm dần</option>
            <option value="Hàng mới">Hàng mới</option>
        </select>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        <?php if(count($products) > 0): ?>
            <?php foreach($products as $product): ?>
                <div class="bg-white shadow-lg rounded-lg p-4">
                    <a href="product_detail.php?product_id=<?php echo $product['product_id']; ?>">
                        <img class="w-full h-50 object-cover rounded-t-lg" src="<?php echo $product['image_url']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    </a>
                    <h3 class="text-lg font-semibold text-gray-800 mt-4">
                        <a href="product_detail.php?product_id=<?php echo $product['product_id']; ?>" class="hover:text-blue-600">
                            <?php echo htmlspecialchars($product['name']); ?>
                        </a>
                    </h3>
                    <p class="text-red-600 font-bold mt-2">Giá gốc: <?php echo number_format($product['price'], 0, ',', '.'); ?>₫</p>
                    <?php if(!empty($product['discount_price'])): ?>
                        <p class="text-green-500 mt-2">Giá giảm: <?php echo number_format($product['discount_price'], 0, ',', '.'); ?>₫</p>
                    <?php endif; ?>
                    <p class="mt-4">
                        <a href="product_detail.php?product_id=<?php echo $product['product_id']; ?>" class="text-blue-600 hover:underline">Xem chi tiết</a>
                    </p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-center text-gray-500">Không tìm thấy sản phẩm nào phù hợp.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
