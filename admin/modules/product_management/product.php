<?php
session_start();
include('../../config/database.php');

// Kiểm tra kết nối cơ sở dữ liệu
if (!$conn) {
    error_log("Database connection failed: " . mysqli_connect_error());
    die("Database connection failed: " . mysqli_connect_error());
}

// Đảm bảo kết nối cơ sở dữ liệu sử dụng UTF-8
if (!$conn->set_charset("utf8mb4")) {
    error_log("Set charset failed: " . $conn->error);
    die("Set charset failed: " . $conn->error);
}

// Xử lý AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json; charset=UTF-8');
    
    switch ($_POST['action']) {
        case 'delete':
            deleteProduct($conn);
            break;
        case 'update':
            updateProduct($conn);
            break;
    }
    exit;
}

// Lấy danh sách danh mục để sử dụng trong form sửa (Cached)
$categories = getCategories($conn);

// Thiết lập phân trang
$limit = 10; // Số sản phẩm mỗi trang
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Lấy danh sách sản phẩm có phân trang
$products = getProducts($conn, $limit, $offset);
$total_products = getTotalProducts($conn);
$total_pages = ceil($total_products / $limit);

/**
 * Hàm xóa sản phẩm
 */
function deleteProduct($conn) {
    $product_id = intval($_POST['product_id']);

    $sql = "DELETE FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log("Prepare failed: " . $conn->error);
        echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống khi chuẩn bị truy vấn.'], JSON_UNESCAPED_UNICODE);
        return;
    }

    $stmt->bind_param("i", $product_id);
    $result = $stmt->execute();
    $stmt->close();
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Xóa sản phẩm thành công!'], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi khi xóa sản phẩm: ' . $conn->error], JSON_UNESCAPED_UNICODE);
    }
}

/**
 * Hàm cập nhật sản phẩm
 */
function updateProduct($conn) {
    // Lấy và validate dữ liệu
    $product_id = intval($_POST['product_id']);
    $name = trim($_POST['name']);
    $price = floatval($_POST['price']);
    $discount_price = isset($_POST['discount_price']) && $_POST['discount_price'] !== '' ? floatval($_POST['discount_price']) : null;
    $category_id = intval($_POST['category_id']);
    $stock = intval($_POST['stock']);
    $status = $_POST['status'];
    $sizes = trim($_POST['sizes']);
    $colors = trim($_POST['colors']);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_best_seller = isset($_POST['is_best_seller']) ? 1 : 0;

    // Server-side validation
    if (empty($name)) {
        echo json_encode(['success' => false, 'message' => 'Tên sản phẩm không được để trống!'], JSON_UNESCAPED_UNICODE);
        return;
    }
    if ($price <= 0) {
        echo json_encode(['success' => false, 'message' => 'Giá sản phẩm phải lớn hơn 0!'], JSON_UNESCAPED_UNICODE);
        return;
    }
    if ($discount_price !== null && $discount_price >= $price) {
        echo json_encode(['success' => false, 'message' => 'Giá khuyến mãi phải nhỏ hơn giá gốc!'], JSON_UNESCAPED_UNICODE);
        return;
    }
    if ($stock < 0) {
        echo json_encode(['success' => false, 'message' => 'Tồn kho không được âm!'], JSON_UNESCAPED_UNICODE);
        return;
    }

    // Cập nhật sản phẩm
    $sql = "UPDATE products SET name = ?, price = ?, discount_price = ?, category_id = ?, stock = ?, status = ?, sizes = ?, colors = ?, is_featured = ?, is_best_seller = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log("Prepare failed: " . $conn->error);
        echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống khi chuẩn bị truy vấn.'], JSON_UNESCAPED_UNICODE);
        return;
    }

    $stmt->bind_param("sddiisssiii", $name, $price, $discount_price, $category_id, $stock, $status, $sizes, $colors, $is_featured, $is_best_seller, $product_id);
    $result = $stmt->execute();
    $stmt->close();
    
    if ($result) {
        // Lấy tên danh mục
        $category_name = getCategoryName($conn, $category_id);
        
        // Xử lý trạng thái để hiển thị
        $status_text = getStatusText($status);

        echo json_encode([
            'success' => true,
            'message' => 'Cập nhật sản phẩm thành công!',
            'product' => [
                'id' => $product_id,
                'name' => $name,
                'price' => $price,
                'discount_price' => $discount_price,
                'category_name' => $category_name,
                'stock' => $stock,
                'status' => $status_text,
                'status_value' => $status,
                'sizes' => $sizes,
                'colors' => $colors,
                'is_featured' => $is_featured,
                'is_best_seller' => $is_best_seller
            ]
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi khi cập nhật sản phẩm: ' . $conn->error], JSON_UNESCAPED_UNICODE);
    }
}

/**
 * Lấy danh sách danh mục
 */
function getCategories($conn) {
    // Sử dụng cache để lưu trữ danh mục
    static $cached_categories = null;
    
    if ($cached_categories !== null) {
        return $cached_categories;
    }
    
    $categories = [];
    $category_query = "SELECT id, name FROM category";
    $category_result = $conn->query($category_query);
    
    if ($category_result) {
        while ($category = $category_result->fetch_assoc()) {
            $categories[] = $category;
        }
        $category_result->close();
    }
    
    $cached_categories = $categories;
    return $categories;
}

/**
 * Lấy tên danh mục theo ID
 */
function getCategoryName($conn, $category_id) {
    $categories = getCategories($conn);
    foreach ($categories as $category) {
        if ($category['id'] == $category_id) {
            return $category['name'];
        }
    }
    return '-';
}

/**
 * Lấy danh sách sản phẩm có phân trang
 */
function getProducts($conn, $limit, $offset) {
    $products = [];
    $query = "SELECT p.*, c.name as category_name 
              FROM products p 
              LEFT JOIN category c ON p.category_id = c.id 
              ORDER BY p.id ASC 
              LIMIT ? OFFSET ?";
    
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        error_log("Prepare failed: " . $conn->error);
        return $products;
    }
    
    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
    
    $stmt->close();
    return $products;
}

/**
 * Đếm tổng số sản phẩm
 */
function getTotalProducts($conn) {
    $total_query = "SELECT COUNT(*) as total FROM products";
    $total_result = $conn->query($total_query);
    $total = $total_result ? $total_result->fetch_assoc()['total'] : 0;
    $total_result->close();
    return $total;
}

/**
 * Lấy văn bản trạng thái
 */
function getStatusText($status) {
    switch ($status) {
        case 'available':
            return 'Còn hàng';
        case 'out_of_stock':
            return 'Hết hàng';
        case 'discontinued':
            return 'Ngừng kinh doanh';
        default:
            return '-';
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Sản Phẩm</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="styles.css" />
    <style>
        .sidebar {
            width: 64px;
            transition: width 0.3s ease;
        }
        .sidebar:hover {
            width: 200px;
        }
        .main-content {
            margin-left: 64px;
            transition: margin-left 0.3s ease;
        }
        .sidebar:hover + .main-content {
            margin-left: 200px;
        }
        @media (max-width: 768px) {
            .sidebar {
                width: 0;
            }
            .main-content {
                margin-left: 0;
            }
        }
        /* Thêm các styles cho toast messages */
        .toast {
            position: fixed;
            top: 1rem;
            right: 1rem;
            padding: 1rem;
            border-radius: 0.5rem;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            animation: fadein 0.5s, fadeout 0.5s 2.5s;
            opacity: 0;
        }
        @keyframes fadein {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes fadeout {
            from { opacity: 1; }
            to { opacity: 0; }
        }
        /* Thêm loading indicator */
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.7);
            z-index: 100;
            justify-content: center;
            align-items: center;
        }
        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="flex min-h-screen bg-gray-100">
    <!-- Main Content -->
    <div class="main-content flex-1 p-6 ml-6 transition-all duration-300">
        <div class="max-w-7xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <button onclick="document.getElementById('productModal').classList.remove('hidden')" class="inline-block bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    <i class="fas fa-plus mr-2"></i> Thêm Sản Phẩm
                </button>
            </div>

            <!-- Danh sách Sản Phẩm -->
            <div class="bg-white rounded-lg shadow-sm overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="bg-gray-200 text-gray-700">
                            <th class="p-3 text-left">ID</th>
                            <th class="p-3 text-left">Tên Sản Phẩm</th>
                            <th class="p-3 text-left">Giá (VND)</th>
                            <th class="p-3 text-left">Giá KM (VND)</th>
                            <th class="p-3 text-left">Danh Mục</th>
                            <th class="p-3 text-left">Tồn Kho</th>
                            <th class="p-3 text-left">Kích Cỡ</th>
                            <th class="p-3 text-left">Màu Sắc</th>
                            <th class="p-3 text-left">Trạng Thái</th>
                            <th class="p-3 text-left">Nổi Bật</th>
                            <th class="p-3 text-left">Bán Chạy</th>
                            <th class="p-3 text-left">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody id="productTableBody">
                        <?php
                        if (!empty($products)) {
                            foreach ($products as $row) {
                                $status_text = getStatusText($row['status']);
                                $is_featured_text = $row['featured'] ? 'Có' : 'Không';
                                $is_best_seller_text = $row['best_seller'] ? 'Có' : 'Không';
                                echo "<tr class='border-b hover:bg-gray-50' data-product-id='{$row['id']}' 
                                            data-is-featured='{$row['featured']}' 
                                            data-is-best-seller='{$row['best_seller']}'
                                            data-status-value='{$row['status']}'>
                                        <td class='p-3'>" . htmlspecialchars($row['id']) . "</td>
                                        <td class='p-3'>" . htmlspecialchars($row['name']) . "</td>
                                        <td class='p-3'>" . number_format($row['price']) . "</td>
                                        <td class='p-3'>" . ($row['discount_price'] ? number_format($row['discount_price']) : '-') . "</td>
                                        <td class='p-3'>" . htmlspecialchars($row['category_name'] ?? '-') . "</td>
                                        <td class='p-3'>" . htmlspecialchars($row['stock']) . "</td>
                                        <td class='p-3'>" . htmlspecialchars($row['sizes']) . "</td>
                                        <td class='p-3'>" . htmlspecialchars($row['colors']) . "</td>
                                        <td class='p-3'>" . htmlspecialchars($status_text) . "</td>
                                        <td class='p-3'>" . htmlspecialchars($is_featured_text) . "</td>
                                        <td class='p-3'>" . htmlspecialchars($is_best_seller_text) . "</td>
                                        <td class='p-3'>
                                            <button class='text-blue-500 hover:text-blue-700 mr-3 edit-btn' data-product-id='{$row['id']}'>
                                                <i class='fas fa-edit'></i>
                                            </button>
                                            <button class='text-red-500 hover:text-red-700 delete-btn' data-product-id='{$row['id']}'>
                                                <i class='fas fa-trash-alt'></i>
                                            </button>
                                        </td>
                                    </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='12' class='p-3 text-center text-gray-500'>Không có sản phẩm nào.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Phân trang -->
            <?php if ($total_pages > 1): ?>
            <div class="mt-4 flex justify-center">
                <nav class="inline-flex rounded-md shadow">
                    <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>" class="px-3 py-2 bg-white border border-gray-300 text-gray-500 hover:bg-gray-100 rounded-l-md">Trước</a>
                    <?php endif; ?>
                    <?php 
                    // Tối ưu hiển thị phân trang
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);
                    
                    if ($start_page > 1) {
                        echo '<a href="?page=1" class="px-3 py-2 bg-white border border-gray-300 text-gray-500 hover:bg-gray-100">1</a>';
                        if ($start_page > 2) {
                            echo '<span class="px-3 py-2 border border-gray-300">...</span>';
                        }
                    }
                    
                    for ($i = $start_page; $i <= $end_page; $i++): 
                    ?>
                    <a href="?page=<?php echo $i; ?>" class="px-3 py-2 <?php echo $i === $page ? 'bg-blue-500 text-white' : 'bg-white text-gray-500'; ?> border border-gray-300 hover:bg-gray-100"><?php echo $i; ?></a>
                    <?php 
                    endfor;
                    
                    if ($end_page < $total_pages) {
                        if ($end_page < $total_pages - 1) {
                            echo '<span class="px-3 py-2 border border-gray-300">...</span>';
                        }
                        echo '<a href="?page=' . $total_pages . '" class="px-3 py-2 bg-white border border-gray-300 text-gray-500 hover:bg-gray-100">' . $total_pages . '</a>';
                    }
                    ?>
                    <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>" class="px-3 py-2 bg-white border border-gray-300 text-gray-500 hover:bg-gray-100 rounded-r-md">Sau</a>
                    <?php endif; ?>
                </nav>
            </div>
            <?php endif; ?>
        </div>

        <!-- Modal chỉnh sửa sản phẩm -->
        <div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden z-50">
            <div class="bg-white p-8 rounded-lg shadow-lg max-w-md w-full">
                <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Chỉnh Sửa Sản Phẩm</h2>
                <form id="editProductForm" method="POST">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" id="editProductId" name="product_id">
                    <div class="mb-4">
                        <label for="editName" class="block text-sm text-gray-600 mb-2">Tên Sản Phẩm</label>
                        <input type="text" id="editName" name="name" required class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" placeholder="Nhập tên sản phẩm">
                    </div>
                    <div class="mb-4">
                        <label for="editPrice" class="block text-sm text-gray-600 mb-2">Giá (VND)</label>
                        <input type="number" id="editPrice" name="price" required class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" placeholder="Nhập giá sản phẩm" min="1">
                    </div>
                    <div class="mb-4">
                        <label for="editDiscountPrice" class="block text-sm text-gray-600 mb-2">Giá Khuyến Mãi (VND)</label>
                        <input type="number" id="editDiscountPrice" name="discount_price" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" placeholder="Nhập giá khuyến mãi (nếu có)" min="0">
                    </div>
                    <div class="mb-4">
                        <label for="editCategoryId" class="block text-sm text-gray-600 mb-2">Danh Mục</label>
                        <select name="category_id" id="editCategoryId" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                            <?php
                            foreach ($categories as $category) {
                                echo "<option value='{$category['id']}'>" . htmlspecialchars($category['name']) . "</option>";
                            }
                            if (empty($categories)) {
                                echo "<option value=''>Không có danh mục</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="editStock" class="block text-sm text-gray-600 mb-2">Tồn Kho</label>
                        <input type="number" id="editStock" name="stock" required class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" placeholder="Nhập số lượng tồn kho" min="0">
                    </div>
                    <div class="mb-4">
                        <label for="editSizes" class="block text-sm text-gray-600 mb-2">Kích Cỡ</label>
                        <input type="text" id="editSizes" name="sizes" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" placeholder="Nhập kích cỡ (e.g., S,M,L)">
                    </div>
                    <div class="mb-4">
                        <label for="editColors" class="block text-sm text-gray-600 mb-2">Màu Sắc</label>
                        <input type="text" id="editColors" name="colors" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" placeholder="Nhập màu sắc (e.g., Xanh,Đỏ)">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm text-gray-600 mb-2">Tùy Chọn</label>
                        <label class="inline-flex items-center">
                            <input type="checkbox" id="editIsFeatured" name="is_featured" class="form-checkbox">
                            <span class="ml-2">Sản phẩm nổi bật</span>
                        </label>
                        <label class="inline-flex items-center ml-4">
                            <input type="checkbox" id="editIsBestSeller" name="is_best_seller" class="form-checkbox">
                            <span class="ml-2">Sản phẩm bán chạy</span>
                        </label>
                    </div>
                    <div class="mb-4">
                        <label for="editStatus" class="block text-sm text-gray-600 mb-2">Trạng Thái</label>
                        <select name="status" id="editStatus" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                            <option value="available">Còn hàng</option>
                            <option value="out_of_stock">Hết hàng</option>
                            <option value="discontinued">Ngừng kinh doanh</option>
                        </select>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="submit" id="updateButton" class="bg-blue-500 text-white px-5 py-2 rounded-lg hover:bg-blue-600 transition-colors flex items-center">
                            <span class="submit-text">Cập Nhật</span>
                            <i class="fas fa-spinner fa-spin ml-2 hidden submit-spinner"></i>
                    </button>
                    <button type="button" id="closeModal" class="bg-gray-300 text-gray-800 px-5 py-2 rounded-lg hover:bg-gray-400 transition-colors">Hủy</button>
                </div>
            </form>
        </div>
    </div>
</div>

