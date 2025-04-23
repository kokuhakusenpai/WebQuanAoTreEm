<?php
include('../../config/database.php'); // Kết nối cơ sở dữ liệu

// Xử lý thêm sản phẩm khi có request POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy dữ liệu từ form
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $category_id = mysqli_real_escape_string($conn, $_POST['category']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $size = mysqli_real_escape_string($conn, $_POST['size']);
    $color = mysqli_real_escape_string($conn, $_POST['color']);
    $stock = mysqli_real_escape_string($conn, $_POST['stock']);
    
    // Xử lý giá khuyến mãi nếu có
    $discount_price = !empty($_POST['discount_price']) ? mysqli_real_escape_string($conn, $_POST['discount_price']) : "NULL";
    
    // Xử lý featured và best seller
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_best_seller = isset($_POST['is_best_seller']) ? 1 : 0;
    
    // Thêm sản phẩm vào cơ sở dữ liệu
    $query = "INSERT INTO products (name, description, price, discount_price, stock, category_id, size, color, status, is_featured, is_best_seller) 
              VALUES ('$product_name', '$description', '$price', $discount_price, '$stock', '$category_id', '$size', '$color', '$status', $is_featured, $is_best_seller)";
    
    if (mysqli_query($conn, $query)) {
        // Lấy ID của sản phẩm vừa thêm
        $product_id = mysqli_insert_id($conn);
        
        // Trả về kết quả thành công
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'product_id' => $product_id,
            'name' => $product_name,
            'price' => $price,
            'description' => $description,
            'category_id' => $category_id,
            'status' => $status
        ]);
        exit;
    } else {
        // Trả về lỗi
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi khi thêm sản phẩm: ' . mysqli_error($conn)
        ]);
        exit;
    }
}

// Lấy danh sách danh mục từ cơ sở dữ liệu
$categories_query = "SELECT * FROM categories ORDER BY parent_id, name";
$categories_result = mysqli_query($conn, $categories_query);
$categories = [];
while ($category = mysqli_fetch_assoc($categories_result)) {
    $categories[] = $category;
}
?>

<div id="addProductModal" class="modal" style="display: flex;">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h3>Thêm Sản Phẩm</h3>
        <form id="addProductForm">
            <label for="product_name">Tên Sản Phẩm:</label>
            <input type="text" id="product_name" name="product_name" required>
        
            <label for="price">Giá:</label>
            <input type="number" id="price" name="price" required min="0">
            
            <label for="discount_price">Giá Khuyến Mãi (nếu có):</label>
            <input type="number" id="discount_price" name="discount_price" min="0">
        
            <label for="description">Mô Tả</label>
            <textarea id="description" name="description" required></textarea>
        
            <label for="category">Danh Mục</label>
            <select id="category" name="category" required>
                <option value="">-- Chọn danh mục --</option>
                <?php foreach ($categories as $category): ?>
                    <?php if ($category['parent_id'] === NULL): ?>
                        <option value="<?= $category['category_id'] ?>" disabled><?= $category['name'] ?></option>
                        <?php foreach ($categories as $subcategory): ?>
                            <?php if ($subcategory['parent_id'] === $category['category_id']): ?>
                                <option value="<?= $subcategory['category_id'] ?>">-- <?= $subcategory['name'] ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
            
            <label for="size">Kích Thước</label>
            <input type="text" id="size" name="size">
            
            <label for="color">Màu sắc</label>
            <input type="text" id="color" name="color">
            
            <label for="stock">Số lượng tồn kho:</label>
            <input type="number" id="stock" name="stock" min="0" value="0" required>
        
            <label for="status">Trạng Thái:</label>
            <select id="status" name="status" required>
                <option value="available">Còn hàng</option>
                <option value="out_of_stock">Hết hàng</option>
                <option value="discontinued">Ngừng kinh doanh</option>
            </select>
            
            <div style="margin: 10px 0;">
                <input type="checkbox" id="is_featured" name="is_featured" value="1">
                <label for="is_featured">Sản phẩm nổi bật</label>
            </div>
            
            <div style="margin: 10px 0;">
                <input type="checkbox" id="is_best_seller" name="is_best_seller" value="1">
                <label for="is_best_seller">Sản phẩm bán chạy</label>
            </div>
        
            <button><a href="#" onclick="loadContent('modules/qlsp/product.php')">Thêm</a></button>
        </form>
    </div>
</div>

<script>
// Gửi dữ liệu form đến server bằng AJAX
function submitProduct() {
    const formData = new FormData(document.getElementById('addProductForm'));

    fetch('add_product.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Sản phẩm đã được thêm thành công!');

            // Kiểm tra xem đang ở trang product.php không
            const tableBody = document.querySelector('table tbody');
            if (tableBody) {
                // Lấy giá trị trạng thái hiển thị
                let statusText = '';
                switch(data.status) {
                    case 'available':
                        statusText = 'Còn hàng';
                        break;
                    case 'out_of_stock':
                        statusText = 'Hết hàng';
                        break;
                    case 'discontinued':
                        statusText = 'Ngừng kinh doanh';
                        break;
                }
                
                // Thêm sản phẩm mới vào bảng trong product.php
                const newRow = `
                    <tr>
                        <td>${data.product_id}</td>
                        <td>${data.name}</td>
                        <td>${data.price} VND</td>
                        <td>${data.description}</td>
                        <td>${data.category_id}</td>
                        <td>${statusText}</td>
                        <td>
                            <a href="edit_product.php?id=${data.product_id}" class="btn-edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="#" onclick="deleteProduct(${data.product_id})" class="btn-delete">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                `;
                tableBody.insertAdjacentHTML('beforeend', newRow);
            }

            closeModal(); // Đóng modal
            document.getElementById('addProductForm').reset(); // Xóa dữ liệu form
        } else {
            alert('Lỗi: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Lỗi khi thêm sản phẩm:', error);
        alert('Không thể thêm sản phẩm. Vui lòng thử lại sau.');
    });
}
</script>