<?php
session_start();
include('../../config/database.php');

// Truy vấn danh sách sản phẩm
$query = "SELECT * FROM products";
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
        <button class="btn-add" onclick="loadAddProductModal()">+ Thêm Sản Phẩm</button>
    </h2>
    
    <!-- Container để hiển thị modal -->
     <div id="modalContainer"></div>

    <!-- Danh sách Sản Phẩm -->
    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>ID Sản Phẩm</th>
                <th>Tên Sản Phẩm</th>
                <th>Giá</th>
                <th>Mô Tả</th>
                <th>Danh Mục</th>
                <th>Trạng Thái</th>
                <th>Thao Tác</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>
                            <td>" . $row['product_id'] . "</td>
                            <td>" . $row['name'] . "</td>
                            <td>" . $row['price'] . " VND</td>
                            <td>" . $row['description'] . "</td>
                            <td>" . $row['category_id'] . "</td>
                            <td>" . $row['status'] . "</td>
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
                        echo "<tr><td colspan='7'>Không có sản phẩm nào.</td></tr>";
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
    </script>
</body>
</html>