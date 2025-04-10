<?php
session_start();
include('../../config/database.php');

// Truy vấn danh sách người dùng
$query = "SELECT * FROM users";
$result = mysqli_query($conn, $query);

// Kiểm tra nếu form đã được submit để thêm người dùng
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $phone = $_POST['phone'];

    // Thêm người dùng vào cơ sở dữ liệu
    $insert_query = "INSERT INTO users (username, email, role, phone) VALUES ('$username', '$email', '$role', '$phone')";
    mysqli_query($conn, $insert_query);

    // Tải lại trang sau khi thêm người dùng
    header("Location: users.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản Lý Người Dùng</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <h2 style="display: flex; justify-content: space-between; align-items: center;">
        Danh sách Người Dùng
        <button class="btn-add" onclick="toggleForm()">+ Thêm Người Dùng</button>
    </h2>
    
    <!-- Form thêm người dùng -->
     <div id="addUserForm">
        <h3>Thêm Người Dùng</h3>
        <form method="POST">
            <label>Tên Người Dùng:</label>
            <input type="text" name="username" required>
        
            <label>Email:</label>
            <input type="email" name="email" required>
        
            <label>Vai Trò:</label>
            <select name="role">
                <option value="admin">Admin</option>
                <option value="customer">Customer</option>
            </select>
        
            <label>Trạng Thái:</label>
            <select name="status">
                <option value="active">Hoạt động</option>
                <option value="inactive">Ngừng hoạt động</option>
            </select>
        
            <button type="submit">Thêm</button>
            <button type="button" onclick="toggleForm()">Hủy</button>
        </form>
    </div>

    <!-- Danh sách Người Dùng -->
     <table>
        <thead>
            <tr>
                <th>ID Người Dùng</th>
                <th>Tên Người Dùng</th>
                <th>Email</th>
                <th>Vai Trò</th>
                <th>Trạng Thái</th>
                <th>Thao Tác</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>
                    <td>" . htmlspecialchars($row['user_id']) . "</td>
                    <td>" . htmlspecialchars(trim($row['username'])) . "</td> <!-- Loại bỏ khoảng trắng -->
                    <td>" . htmlspecialchars($row['email']) . "</td>
                    <td>" . htmlspecialchars($row['role']) . "</td>
                    <td>" . htmlspecialchars($row['phone']) . "</td>
                    <td>
                        <button class='btn-edit' onclick=\"window.location.href='edit_user.php?id=" . $row['user_id'] . "'\">
                            <i class='fas fa-edit'></i>
                        </button>
                        <button class='btn-delete' onclick=\"deleteUser(" . $row['user_id'] . ")\">
                            <i class='fas fa-trash-alt'></i>
                        </button>
                    </td>
                  </tr>";
                }
            } else {
                echo "<tr><td colspan='6'>Không có người dùng nào.</td></tr>";
            }
            ?>
        </tbody>
    </table>
    <script>
    function toggleForm() { // Hàm toggle form
        const form = document.getElementById('addUserForm');
        form.style.display = (form.style.display === 'none' || form.style.display === '') ? 'block' : 'none';
    }
    
    function deleteUser(userId) {
        if (confirm("Bạn có chắc chắn muốn xóa người dùng này?")) {
            window.location.href = "delete_user.php?id=" + userId;
        }
        }
    </script>
</body>
</html>