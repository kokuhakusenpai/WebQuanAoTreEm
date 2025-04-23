<?php
session_start();
include('../../config/database.php');

// Truy vấn danh sách sản phẩm với tên danh mục
$query = "SELECT p.*, c.name as category_name 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.category_id 
          ORDER BY p.product_id DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Sản Phẩm</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <h2 style="display: flex; justify-content: space-between; align-items: center;">
        Danh sách Sản Phẩm
        <button><a href="#" onclick="loadContent('modules/qlsp/add_product.php')">+ Thêm bài viết</a></button>
    </h2>
    
    <!-- Container để hiển thị modal -->
    <div id="modalContainer"></div>

    <!-- Danh sách Sản Phẩm -->
    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên Sản Phẩm</th>
                <th>Giá (VND)</th>
                <th>Giá KM (VND)</th>
                <th>Danh Mục</th>
                <th>Tồn kho</th>
                <th>Trạng Thái</th>
                <th>Thao Tác</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    // Xử lý hiển thị trạng thái
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
                    
                    echo "<tr>
                            <td>" . $row['product_id'] . "</td>
                            <td>" . $row['name'] . "</td>
                            <td>" . number_format($row['price']) . "</td>
                            <td>" . ($row['discount_price'] ? number_format($row['discount_price']) : '-') . "</td>
                            <td>" . ($row['category_name'] ?? '-') . "</td>
                            <td>" . $row['stock'] . "</td>
                            <td>" . $status_text . "</td>
                            <td>
                            <a href='edit_product.php?id=" . $row['product_id'] . "' class='btn-edit'>
                            <i class='fas fa-edit'></i>
                            </a>
                            <a href='#' onclick='deleteProduct(" . $row['product_id'] . ")' class='btn-delete'>
                            <i class='fas fa-trash'></i>
                            </a>
                            </td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='8'>Không có sản phẩm nào.</td></tr>";
                    }
            ?>
        </tbody>
    </table>

    <script>
    function loadAddProductModal() {
        fetch('add_product.php') 
        .then(response => {
            if (!response.ok) {
                throw new Error('Không thể tải modal.');
            }
            return response.text(); 
        })
        .then(html => {
            document.getElementById('modalContainer').innerHTML = html;
            openModal(); 
        })
        .catch(error => {
            console.error('Lỗi khi tải modal:', error);
            alert('Không thể tải giao diện thêm sản phẩm. Vui lòng thử lại sau.');
        });
    }
    
    function openModal() {
        const modal = document.getElementById('addProductModal');
        if (modal) {
            modal.style.display = 'flex';
        }
    }
    
    function closeModal() {
        const modal = document.getElementById('addProductModal');
        if (modal) {
            modal.style.display = 'none';
        }
    }

    function deleteProduct(productId) {
        if (confirm('Bạn có chắc chắn muốn xóa sản phẩm này không?')) {
            // Gửi yêu cầu xóa đến server
            fetch('delete_product.php?id=' + productId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Xóa sản phẩm thành công!');
                    location.reload(); // Tải lại trang
                } else {
                    alert('Lỗi: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Lỗi khi xóa sản phẩm:', error);
                alert('Không thể xóa sản phẩm. Vui lòng thử lại sau.');
            });
        }
    }
    </script>
</body>
</html>