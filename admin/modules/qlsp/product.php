<?php
session_start();
include('../../config/database.php');

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../login.php');
    exit;
}

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

// Xử lý DELETE request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $product_id = intval($_POST['product_id']);

    $sql = "DELETE FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log("Prepare failed: " . $conn->error);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống khi chuẩn bị truy vấn.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stmt->bind_param("i", $product_id);
    if ($stmt->execute()) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['success' => true, 'message' => 'Xóa sản phẩm thành công!'], JSON_UNESCAPED_UNICODE);
    } else {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['success' => false, 'message' => 'Lỗi khi xóa sản phẩm: ' . $stmt->error], JSON_UNESCAPED_UNICODE);
    }
    $stmt->close();
    exit;
}

// Xử lý UPDATE request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
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
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['success' => false, 'message' => 'Tên sản phẩm không được để trống!'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    if ($price <= 0) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['success' => false, 'message' => 'Giá sản phẩm phải lớn hơn 0!'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    if ($discount_price !== null && $discount_price >= $price) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['success' => false, 'message' => 'Giá khuyến mãi phải nhỏ hơn giá gốc!'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    if ($stock < 0) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['success' => false, 'message' => 'Tồn kho không được âm!'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Cập nhật sản phẩm
    $sql = "UPDATE products SET name = ?, price = ?, discount_price = ?, category_id = ?, stock = ?, status = ?, sizes = ?, colors = ?, is_featured = ?, is_best_seller = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log("Prepare failed: " . $conn->error);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống khi chuẩn bị truy vấn.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stmt->bind_param("sddiisssiii", $name, $price, $discount_price, $category_id, $stock, $status, $sizes, $colors, $is_featured, $is_best_seller, $product_id);
    if ($stmt->execute()) {
        // Lấy tên danh mục
        $category_name_query = "SELECT name FROM category WHERE id = ?";
        $category_stmt = $conn->prepare($category_name_query);
        $category_stmt->bind_param("i", $category_id);
        $category_stmt->execute();
        $category_result = $category_stmt->get_result();
        $category_name = $category_result->fetch_assoc()['name'] ?? '-';
        $category_stmt->close();

        // Xử lý trạng thái để hiển thị
        $status_text = '';
        switch ($status) {
            case 'available':
                $status_text = 'Còn hàng';
                break;
            case 'out_of_stock':
                $status_text = 'Hết hàng';
                break;
            case 'discontinued':
                $status_text = 'Ngừng kinh doanh';
                break;
        }

        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'success' => true,
            'message' => 'Cập nhật sản phẩm thành công!',
            'product' => [
                'product_id' => $product_id,
                'name' => $name,
                'price' => $price,
                'discount_price' => $discount_price,
                'category_name' => $category_name,
                'stock' => $stock,
                'status' => $status_text,
                'sizes' => $sizes,
                'colors' => $colors,
                'is_featured' => $is_featured,
                'is_best_seller' => $is_best_seller
            ]
        ], JSON_UNESCAPED_UNICODE);
    } else {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['success' => false, 'message' => 'Lỗi khi cập nhật sản phẩm: ' . $stmt->error], JSON_UNESCAPED_UNICODE);
    }
    $stmt->close();
    exit;
}

// Lấy danh sách danh mục để sử dụng trong form sửa
$category_query = "SELECT id, name FROM category";
$category_result = $conn->query($category_query);
$categories = [];
if ($category_result) {
    while ($category = $category_result->fetch_assoc()) {
        $categories[] = $category;
    }
    $category_result->close();
}

// Truy vấn danh sách sản phẩm với tên danh mục (thêm phân trang)
$limit = 10; // Số sản phẩm mỗi trang
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

$query = "SELECT p.*, c.name as category_name 
          FROM products p 
          LEFT JOIN category c ON p.category_id = c.id 
          ORDER BY p.id ASC 
          LIMIT ? OFFSET ?";
$stmt = $conn->prepare($query);
if ($stmt === false) {
    error_log("Prepare failed: " . $conn->error);
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

// Đếm tổng số sản phẩm để phân trang
$total_query = "SELECT COUNT(*) as total FROM products";
$total_result = $conn->query($total_query);
$total_products = $total_result ? $total_result->fetch_assoc()['total'] : 0;
$total_pages = ceil($total_products / $limit);
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
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-6xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Danh sách Sản Phẩm</h2>
            <button onclick="loadContent('modules/qlsp/add_product.php', 'Thêm Sản Phẩm')" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors flex items-center">
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
                        <th class="p-3 text-left">Thao Tác</th>
                    </tr>
                </thead>
                <tbody id="productTableBody">
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $status_text = '';
                            switch ($row['status']) {
                                case 'available':
                                    $status_text = 'Còn hàng';
                                    break;
                                case 'out_of_stock':
                                    $status_text = 'Hết hàng';
                                    break;
                                case 'discontinued':
                                    $status_text = 'Ngừng kinh doanh';
                                    break;
                            }
                            echo "<tr class='border-b hover:bg-gray-50' data-product-id='{$row['id']}'>
                                    <td class='p-3'>" . htmlspecialchars($row['id']) . "</td>
                                    <td class='p-3'>" . htmlspecialchars($row['name']) . "</td>
                                    <td class='p-3'>" . number_format($row['price']) . "</td>
                                    <td class='p-3'>" . ($row['discount_price'] ? number_format($row['discount_price']) : '-') . "</td>
                                    <td class='p-3'>" . htmlspecialchars($row['category_name'] ?? '-') . "</td>
                                    <td class='p-3'>" . htmlspecialchars($row['stock']) . "</td>
                                    <td class='p-3'>" . htmlspecialchars($row['sizes']) . "</td>
                                    <td class='p-3'>" . htmlspecialchars($row['colors']) . "</td>
                                    <td class='p-3'>" . htmlspecialchars($status_text) . "</td>
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
                        echo "<tr><td colspan='10' class='p-3 text-center text-gray-500'>Không có sản phẩm nào.</td></tr>";
                    }
                    $stmt->close();
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
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?php echo $i; ?>" class="px-3 py-2 <?php echo $i === $page ? 'bg-blue-500 text-white' : 'bg-white text-gray-500'; ?> border border-gray-300 hover:bg-gray-100"><?php echo $i; ?></a>
                <?php endfor; ?>
                <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo $page + 1; ?>" class="px-3 py-2 bg-white border border-gray-300 text-gray-500 hover:bg-gray-100 rounded-r-md">Sau</a>
                <?php endif; ?>
            </nav>
        </div>
        <?php endif; ?>
    </div>

    <!-- Modal chỉnh sửa sản phẩm -->
    <div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden">
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

    <script>
        // Hàm hiển thị thông báo
        function showToast(message, type) {
            const toast = document.createElement('div');
            toast.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg text-white ${type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
            toast.textContent = message;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }

        // Xóa sản phẩm bằng AJAX
        function deleteProduct(productId) {
            if (confirm('Bạn có chắc chắn muốn xóa sản phẩm này không?')) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('product_id', productId);

                fetch('product.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast("Xóa sản phẩm thành công!", "success");
                        const row = document.querySelector(`tr[data-product-id="${productId}"]`);
                        if (row) {
                            row.remove();
                        }
                        const tbody = document.getElementById('productTableBody');
                        if (tbody.children.length === 0) {
                            tbody.innerHTML = "<tr><td colspan='10' class='p-3 text-center text-gray-500'>Không có sản phẩm nào.</td></tr>";
                        }
                    } else {
                        showToast("Lỗi khi xóa sản phẩm: " + data.message, "error");
                    }
                })
                .catch(error => {
                    showToast("Lỗi khi xóa sản phẩm: " + error.message, "error");
                });
            }
        }

        // Gắn sự kiện cho các nút Delete
        function attachDeleteEvents() {
            const deleteButtons = document.querySelectorAll('.delete-btn');
            deleteButtons.forEach(button => {
                button.removeEventListener('click', handleDeleteClick);
                button.addEventListener('click', handleDeleteClick);
            });
        }

        function handleDeleteClick() {
            const productId = this.getAttribute('data-product-id');
            deleteProduct(productId);
        }

        // Hàm để thêm sản phẩm mới vào bảng mà không cần tải lại trang
        function addProductToTable(product) {
            const tbody = document.getElementById('productTableBody');
            if (!tbody) return;

            // Xóa thông báo "Không có sản phẩm nào" nếu có
            if (tbody.querySelector('tr td[colspan="10"]')) {
                tbody.innerHTML = '';
            }

            // Tạo hàng mới cho sản phẩm
            const newRow = document.createElement('tr');
            newRow.className = 'border-b hover:bg-gray-50';
            newRow.setAttribute('data-product-id', product.product_id);
            newRow.innerHTML = `
                <td class='p-3'>${product.product_id}</td>
                <td class='p-3'>${product.name}</td>
                <td class='p-3'>${Number(product.price).toLocaleString()}</td>
                <td class='p-3'>${product.discount_price ? Number(product.discount_price).toLocaleString() : '-'}</td>
                <td class='p-3'>${product.category_name}</td>
                <td class='p-3'>${product.stock}</td>
                <td class='p-3'>${product.sizes}</td>
                <td class='p-3'>${product.colors}</td>
                <td class='p-3'>${product.status}</td>
                <td class='p-3'>
                    <button class='text-blue-500 hover:text-blue-700 mr-3 edit-btn' data-product-id='${product.product_id}'>
                        <i class='fas fa-edit'></i>
                    </button>
                    <button class='text-red-500 hover:text-red-700 delete-btn' data-product-id='${product.product_id}'>
                        <i class='fas fa-trash-alt'></i>
                    </button>
                </td>
            `;
            tbody.appendChild(newRow);

            // Gắn lại sự kiện cho nút Delete và Edit mới
            attachDeleteEvents();
            attachEditEvents();
        }

        // Gắn sự kiện cho các nút Edit
        function attachEditEvents() {
            const editButtons = document.querySelectorAll('.edit-btn');
            editButtons.forEach(button => {
                button.removeEventListener('click', handleEditClick);
                button.addEventListener('click', handleEditClick);
            });
        }

        function handleEditClick() {
            const productId = this.getAttribute('data-product-id');
            const row = document.querySelector(`tr[data-product-id="${productId}"]`);
            if (!row) return;

            // Lấy dữ liệu từ hàng hiện tại
            const name = row.cells[1].textContent;
            const price = parseFloat(row.cells[2].textContent.replace(/,/g, ''));
            const discountPrice = row.cells[3].textContent === '-' ? '' : parseFloat(row.cells[3].textContent.replace(/,/g, ''));
            const categoryName = row.cells[4].textContent;
            const stock = parseInt(row.cells[5].textContent);
            const sizes = row.cells[6].textContent;
            const colors = row.cells[7].textContent;
            const statusText = row.cells[8].textContent;

            // Điền dữ liệu vào form
            document.getElementById('editProductId').value = productId;
            document.getElementById('editName').value = name;
            document.getElementById('editPrice').value = price;
            document.getElementById('editDiscountPrice').value = discountPrice;
            document.getElementById('editStock').value = stock;
            document.getElementById('editSizes').value = sizes;
            document.getElementById('editColors').value = colors;

            // Chọn danh mục
            const categorySelect = document.getElementById('editCategoryId');
            for (let option of categorySelect.options) {
                if (option.text === categoryName) {
                    option.selected = true;
                    break;
                }
            }

            // Chọn trạng thái
            const statusSelect = document.getElementById('editStatus');
            const statusMap = {
                'Còn hàng': 'available',
                'Hết hàng': 'out_of_stock',
                'Ngừng kinh doanh': 'discontinued'
            };
            statusSelect.value = statusMap[statusText] || 'available';

            // Lấy dữ liệu is_featured và is_best_seller từ server
            fetch(`get_product_details.php?product_id=${productId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('editIsFeatured').checked = data.product.is_featured;
                        document.getElementById('editIsBestSeller').checked = data.product.is_best_seller;
                    }
                });

            // Hiển thị modal
            document.getElementById('editModal').classList.remove('hidden');
        }

        // Xử lý form chỉnh sửa
        document.getElementById('editProductForm').addEventListener('submit', function(event) {
            event.preventDefault();

            const updateButton = document.getElementById('updateButton');
            const submitText = updateButton.querySelector('.submit-text');
            const submitSpinner = updateButton.querySelector('.submit-spinner');
            updateButton.disabled = true;
            submitText.textContent = 'Đang cập nhật...';
            submitSpinner.classList.remove('hidden');

            const formData = new FormData(this);
            fetch('product.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                updateButton.disabled = false;
                submitText.textContent = 'Cập Nhật';
                submitSpinner.classList.add('hidden');

                if (data.success) {
                    showToast(data.message, "success");

                    // Cập nhật hàng trong bảng
                    const productId = formData.get('product_id');
                    const row = document.querySelector(`tr[data-product-id="${productId}"]`);
                    if (row) {
                        row.cells[1].textContent = data.product.name;
                        row.cells[2].textContent = Number(data.product.price).toLocaleString();
                        row.cells[3].textContent = data.product.discount_price ? Number(data.product.discount_price).toLocaleString() : '-';
                        row.cells[4].textContent = data.product.category_name;
                        row.cells[5].textContent = data.product.stock;
                        row.cells[6].textContent = data.product.sizes;
                        row.cells[7].textContent = data.product.colors;
                        row.cells[8].textContent = data.product.status;
                    }

                    // Đóng modal
                    document.getElementById('editModal').classList.add('hidden');
                } else {
                    showToast(data.message, "error");
                }
            })
            .catch(error => {
                updateButton.disabled = false;
                submitText.textContent = 'Cập Nhật';
                submitSpinner.classList.add('hidden');
                showToast("Lỗi khi cập nhật sản phẩm: " + error.message, "error");
            });
        });

        // Đóng modal khi nhấn nút Hủy
        document.getElementById('closeModal').addEventListener('click', () => {
            document.getElementById('editModal').classList.add('hidden');
        });

        // Gắn sự kiện ban đầu khi trang tải
        document.addEventListener('DOMContentLoaded', () => {
            attachDeleteEvents();
            attachEditEvents();
        });
    </script>
</body>
</html>
<?php
$conn->close();
?>