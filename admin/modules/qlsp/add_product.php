<?php
include('../../config/database.php'); // Kết nối cơ sở dữ liệu
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
        
            <label for="description">Mô Tả:</label>
            <textarea id="description" name="description" required></textarea>
        
            <label for="category">Danh Mục:</label>
            <select id="category" name="category">
                <option value="Thời trang trẻ em">Thời trang trẻ em</option>
                <option value="Đồ chơi">Đồ chơi</option>
                <option value="Dụng cụ học tập">Dụng cụ học tập</option>
            </select>
        
            <label for="status">Trạng Thái:</label>
            <select id="status" name="status">
                <option value="active">Hoạt động</option>
                <option value="inactive">Ngừng hoạt động</option>
            </select>
        
            <button type="button" onclick="submitProduct()">Thêm</button>
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

            // Thêm sản phẩm mới vào bảng trong product.php
            const tableBody = document.querySelector('table tbody');
            const newRow = `
                <tr>
                    <td>${data.product_id}</td>
                    <td>${data.name}</td>
                    <td>${data.price} VND</td>
                    <td>${data.description}</td>
                    <td>${data.category}</td>
                    <td>${data.status}</td>
                    <td>
                        <a href="edit_product.php?id=${data.product_id}" class="btn-edit">Sửa</a>
                        <a href="#" onclick="deleteProduct(${data.product_id})" class="btn-delete">Xóa</a>
                    </td>
                </tr>
            `;
            tableBody.insertAdjacentHTML('beforeend', newRow);

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