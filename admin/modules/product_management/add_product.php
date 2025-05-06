<?php
session_start();
include('../../config/database.php');

// Kiểm tra kết nối cơ sở dữ liệu
if (!$conn) {
    header('HTTP/1.1 500 Internal Server Error');
    header('Content-Type: application/json; charset=UTF-8');
    die(json_encode(['success' => false, 'message' => 'Kết nối cơ sở dữ liệu thất bại: ' . mysqli_connect_error()], JSON_UNESCAPED_UNICODE));
}

// Đảm bảo kết nối cơ sở dữ liệu sử dụng UTF-8
if (!$conn->set_charset("utf8mb4")) {
    header('HTTP/1.1 500 Internal Server Error');
    header('Content-Type: application/json; charset=UTF-8');
    die(json_encode(['success' => false, 'message' => 'Lỗi thiết lập mã hóa UTF-8: ' . $conn->error], JSON_UNESCAPED_UNICODE));
}

// Lấy danh sách danh mục
$category_query = "SELECT * FROM categories";
$category_result = $conn->query($category_query);
if (!$category_result) {
    error_log("Query failed: " . $conn->error);
    header('HTTP/1.1 500 Internal Server Error');
    header('Content-Type: application/json; charset=UTF-8');
    die(json_encode(['success' => false, 'message' => 'Lỗi khi lấy danh sách danh mục: ' . $conn->error], JSON_UNESCAPED_UNICODE));
}

// Xử lý AJAX request để thêm sản phẩm
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $price = floatval($_POST['price']);
    $discount_price = isset($_POST['discount_price']) ? floatval($_POST['discount_price']) : null;
    $category_id = intval($_POST['category_id']);
    $stock = intval($_POST['stock']);
    $status = $_POST['status'];

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

    // Bắt đầu transaction để đảm bảo tính toàn vẹn dữ liệu
    $conn->begin_transaction();
    try {
        // Thêm sản phẩm vào cơ sở dữ liệu
        $sql = "INSERT INTO products (name, price, discount_price, category_id, stock, status) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            error_log("Prepare failed: " . $conn->error);
            throw new Exception('Lỗi hệ thống khi chuẩn bị truy vấn.');
        }

        $stmt->bind_param("sddiis", $name, $price, $discount_price, $category_id, $stock, $status);
        if (!$stmt->execute()) {
            throw new Exception('Lỗi khi thêm sản phẩm: ' . $stmt->error);
        }

        $new_product_id = $stmt->insert_id;
        $stmt->close();

        // Lấy tên danh mục
        $category_name_query = "SELECT name FROM categories WHERE category_id = ?";
        $category_stmt = $conn->prepare($category_name_query);
        if ($category_stmt === false) {
            error_log("Prepare failed: " . $conn->error);
            throw new Exception('Lỗi hệ thống khi lấy tên danh mục.');
        }
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

        // Commit transaction
        $conn->commit();

        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'success' => true,
            'message' => 'Sản phẩm đã thêm thành công!',
            'product' => [
                'product_id' => $new_product_id,
                'name' => $name,
                'price' => $price,
                'discount_price' => $discount_price,
                'category_name' => $category_name,
                'stock' => $stock,
                'status' => $status_text
            ]
        ], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        // Rollback transaction nếu có lỗi
        $conn->rollback();
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }

    $conn->close();
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Sản Phẩm</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
<div class="bg-white p-8 rounded-lg shadow-lg max-w-md w-full mx-auto">
    <h1 class="text-2xl font-bold text-gray-800 mb-6 text-center">Thêm Sản Phẩm</h1>
    <form id="addProductFormAjax" action="modules/product_management/add_product.php" method="POST" onsubmit="submitAddProductForm(event)">
        <div class="mb-4">
            <label for="name" class="block text-sm text-gray-600 mb-2">Tên Sản Phẩm</label>
            <input type="text" id="name" name="name" required class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" placeholder="Nhập tên sản phẩm">
        </div>
        <div class="mb-4">
            <label for="price" class="block text-sm text-gray-600 mb-2">Giá (VND)</label>
            <input type="number" id="price" name="price" required class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" placeholder="Nhập giá sản phẩm" min="1">
        </div>
        <div class="mb-4">
            <label for="discount_price" class="block text-sm text-gray-600 mb-2">Giá Khuyến Mãi (VND)</label>
            <input type="number" id="discount_price" name="discount_price" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" placeholder="Nhập giá khuyến mãi (nếu có)" min="0">
        </div>
        <div class="mb-4">
            <label for="category_id" class="block text-sm text-gray-600 mb-2">Danh Mục</label>
            <select name="category_id" id="category_id" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                <?php
                if ($category_result->num_rows > 0) {
                    while ($category = $category_result->fetch_assoc()) {
                        echo "<option value='{$category['category_id']}'>" . htmlspecialchars($category['name']) . "</option>";
                    }
                } else {
                    echo "<option value=''>Không có danh mục</option>";
                }
                $category_result->close();
                ?>
            </select>
        </div>
        <div class="mb-4">
            <label for="stock" class="block text-sm text-gray-600 mb-2">Tồn Kho</label>
            <input type="number" id="stock" name="stock" required class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" placeholder="Nhập số lượng tồn kho" min="0">
        </div>
        <div class="mb-4">
            <label for="status" class="block text-sm text-gray-600 mb-2">Trạng Thái</label>
            <select name="status" id="status" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                <option value="available">Còn hàng</option>
                <option value="out_of_stock">Hết hàng</option>
                <option value="discontinued">Ngừng kinh doanh</option>
            </select>
        </div>
        <div class="flex justify-end space-x-3">
            <button type="submit" id="addButton" class="bg-blue-500 text-white px-5 py-2 rounded-lg hover:bg-blue-600 transition-colors flex items-center">
                <span class="submit-text">Thêm</span>
                <i class="fas fa-spinner fa-spin ml-2 hidden submit-spinner"></i>
            </button>
            <button type="button" onclick="loadContentWithFallback('modules/qlsp/product.php', 'Quản lý Sản Phẩm')" class="bg-gray-300 text-gray-800 px-5 py-2 rounded-lg hover:bg-gray-400 transition-colors">Hủy</button>
        </div>
    </form>
</div>

<script>
    // Fallback for loadContent if not defined
    if (typeof loadContent !== 'function') {
        function loadContent(url, title) {
            console.log(`Loading content: ${url} (${title})`);
            window.location.href = url; // Fallback to full page redirect
        }
    }

    // Wrapper for loadContent with fallback
    function loadContentWithFallback(url, title) {
        if (typeof loadContent === 'function') {
            loadContent(url, title);
        } else {
            window.location.href = url;
        }
    }

    function submitAddProductForm(event) {
        event.preventDefault();

        const addButton = document.getElementById('addButton');
        const submitText = addButton.querySelector('.submit-text');
        const submitSpinner = addButton.querySelector('.submit-spinner');
        addButton.disabled = true;
        submitText.textContent = 'Đang thêm...';
        submitSpinner.classList.remove('hidden');

        const name = document.getElementById('name').value.trim();
        const price = parseFloat(document.getElementById('price').value);
        const discountPrice = document.getElementById('discount_price').value ? parseFloat(document.getElementById('discount_price').value) : null;
        const stock = parseInt(document.getElementById('stock').value);

        // Client-side validation
        if (!name) {
            showToast("Tên sản phẩm không được để trống!", "error");
            addButton.disabled = false;
            submitText.textContent = 'Thêm';
            submitSpinner.classList.add('hidden');
            return;
        }
        if (price <= 0) {
            showToast("Giá sản phẩm phải lớn hơn 0!", "error");
            addButton.disabled = false;
            submitText.textContent = 'Thêm';
            submitSpinner.classList.add('hidden');
            return;
        }
        if (discountPrice !== null && discountPrice >= price) {
            showToast("Giá khuyến mãi phải nhỏ hơn giá gốc!", "error");
            addButton.disabled = false;
            submitText.textContent = 'Thêm';
            submitSpinner.classList.add('hidden');
            return;
        }
        if (stock < 0) {
            showToast("Tồn kho không được âm!", "error");
            addButton.disabled = false;
            submitText.textContent = 'Thêm';
            submitSpinner.classList.add('hidden');
            return;
        }

        const formData = new FormData(document.getElementById('addProductFormAjax'));
        console.log("Submitting form data:", Object.fromEntries(formData)); // Debugging log

        fetch('modules/product_management/add_product.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log("Response status:", response.status); // Debugging log
            return response.json();
        })
        .then(data => {
            console.log("Response data:", data); // Debugging log
            addButton.disabled = false;
            submitText.textContent = 'Thêm';
            submitSpinner.classList.add('hidden');

            if (data.success) {
                showToast(data.message, "success");

                // Thêm sản phẩm mới vào bảng trên product.php
                if (typeof addProductToTable === 'function') {
                    addProductToTable(data.product);
                }

                // Reset form và quay lại danh sách sản phẩm
                document.getElementById('addProductFormAjax').reset();
                setTimeout(() => {
                    loadContentWithFallback('modules/qlsp/product.php', 'Quản lý Sản Phẩm');
                }, 1500);
            } else {
                showToast(data.message, "error");
            }
        })
        .catch(error => {
            console.error("Error during fetch:", error); // Debugging log
            addButton.disabled = false;
            submitText.textContent = 'Thêm';
            submitSpinner.classList.add('hidden');
            showToast("Lỗi khi thêm sản phẩm: " + error.message, "error");
        });
    }
    function showToast(message, type) {
    // Tạo toast element
    const toast = document.createElement('div');
    toast.className = `toast ${type === 'success' ? 'bg-green-500' : 'bg-red-500'} text-white`;
    toast.textContent = message;
    
    // Thêm toast vào body
    document.body.appendChild(toast);
    
    // Hiển thị toast
    setTimeout(() => {
        toast.style.opacity = '1';
    }, 10);
    
    // Xóa toast sau 3 giây
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => {
            document.body.removeChild(toast);
        }, 500);
    }, 3000);
    }
    function submitAddProductForm(event) {
    event.preventDefault();

    const addButton = document.getElementById('addButton');
    const submitText = addButton.querySelector('.submit-text');
    const submitSpinner = addButton.querySelector('.submit-spinner');
    addButton.disabled = true;
    submitText.textContent = 'Đang thêm...';
    submitSpinner.classList.remove('hidden');

    const name = document.getElementById('name').value.trim();
    const price = parseFloat(document.getElementById('price').value);
    const discountPrice = document.getElementById('discount_price').value ? parseFloat(document.getElementById('discount_price').value) : null;
    const stock = parseInt(document.getElementById('stock').value);

    // Client-side validation
    if (!name) {
        showToast("Tên sản phẩm không được để trống!", "error");
        addButton.disabled = false;
        submitText.textContent = 'Thêm';
        submitSpinner.classList.add('hidden');
        return;
    }
    if (price <= 0) {
        showToast("Giá sản phẩm phải lớn hơn 0!", "error");
        addButton.disabled = false;
        submitText.textContent = 'Thêm';
        submitSpinner.classList.add('hidden');
        return;
    }
    if (discountPrice !== null && discountPrice >= price) {
        showToast("Giá khuyến mãi phải nhỏ hơn giá gốc!", "error");
        addButton.disabled = false;
        submitText.textContent = 'Thêm';
        submitSpinner.classList.add('hidden');
        return;
    }
    if (stock < 0) {
        showToast("Tồn kho không được âm!", "error");
        addButton.disabled = false;
        submitText.textContent = 'Thêm';
        submitSpinner.classList.add('hidden');
        return;
    }

    const formData = new FormData(document.getElementById('addProductFormAjax'));
    console.log("Submitting form data:", Object.fromEntries(formData)); // Debugging log

    // Sửa lại đường dẫn cho khớp với action trong form
    fetch(document.getElementById('addProductFormAjax').action, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log("Response status:", response.status); // Debugging log
        return response.json();
    })
    .then(data => {
        console.log("Response data:", data); // Debugging log
        addButton.disabled = false;
        submitText.textContent = 'Thêm';
        submitSpinner.classList.add('hidden');

        if (data.success) {
            showToast(data.message, "success");
            document.getElementById('addProductFormAjax').reset();
            setTimeout(() => {
                // Kiểm tra vị trí thực tế của file product.php
                const productPath = 'modules/product_management/product.php';
                loadContentWithFallback(productPath, 'Quản lý Sản Phẩm');
            }, 1500);
        } else {
            showToast(data.message, "error");
        }
    })
    .catch(error => {
        console.error("Error during fetch:", error);
        addButton.disabled = false;
        submitText.textContent = 'Thêm';
        submitSpinner.classList.add('hidden');
        showToast("Lỗi khi thêm sản phẩm: " + error.message, "error");
    });
}
</script>
</body>
</html>