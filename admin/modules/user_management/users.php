<?php
session_start();
include '../../config/database.php';

// Lấy danh sách người dùng từ cơ sở dữ liệu
$query = "SELECT * FROM user";
$result = mysqli_query($conn, $query);

$users = [];
while ($row = mysqli_fetch_assoc($result)) {
    $users[] = $row;
}
?>

<!-- Nút mở modal -->
<div class="mb-4">
    <button onclick="document.getElementById('userModal').classList.remove('hidden')" 
            class="inline-block bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
        <i class="fas fa-user-plus mr-2"></i> Thêm Người Dùng
    </button>
</div>

<!-- Modal thêm người dùng -->
<div id="userModal" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center hidden z-50">
    <div class="bg-white p-6 rounded-lg shadow-lg w-96">
        <h2 class="text-xl font-semibold mb-4">Thêm Người Dùng Mới</h2>
        <form id="addUserForm">
            <input type="text" name="username" placeholder="Tên người dùng" required class="w-full p-2 border rounded mb-3">
            <input type="email" name="email" placeholder="Email" required class="w-full p-2 border rounded mb-3">
            <input type="tel" name="phone" placeholder="Số điện thoại" required class="w-full p-2 border rounded mb-3">
            <input type="password" name="password" placeholder="Mật khẩu" required class="w-full p-2 border rounded mb-3">
            <input type="password" name="confirm_password" placeholder="Xác nhận mật khẩu" required class="w-full p-2 border rounded mb-3">
            <select name="role" required class="w-full p-2 border rounded mb-4">
                <option value="" disabled selected>Chọn phân quyền</option>
                <option value="admin">Admin</option>
                <option value="customer">Customer</option>
            </select>
            <div class="flex justify-between">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Thêm</button>
                <button type="button" onclick="document.getElementById('userModal').classList.add('hidden')" 
                        class="bg-gray-300 text-black px-4 py-2 rounded hover:bg-gray-400">Hủy</button>
            </div>
        </form>
    </div>
</div>

<!-- Danh sách người dùng -->
<table class="min-w-full table-auto border-collapse border border-gray-300">
    <thead>
        <tr>
            <th class="p-3 text-left border-b">ID</th>
            <th class="p-3 text-left border-b">Tên người dùng</th>
            <th class="p-3 text-left border-b">Email</th>
            <th class="p-3 text-left border-b">Số điện thoại</th>
            <th class="p-3 text-left border-b">Phân quyền</th>
            <th class="p-3 text-left border-b">Trạng thái</th>
            <th class="p-3 text-left border-b">Ngày đăng ký</th>
            <th class="p-3 text-left border-b">Tùy chọn</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $index => $user): ?>
            <tr>
                <td class="p-3 border-b"><?php echo $index + 1; ?></td>
                <td class="p-3 border-b"><?php echo $user['username']; ?></td>
                <td class="p-3 border-b"><?php echo $user['email']; ?></td>
                <td class="p-3 border-b"><?php echo $user['phone']; ?></td>
                <td class="p-3 border-b"><?php echo $user['role']; ?></td>
                <td class="p-3 border-b">
                    <a href="#" onclick="toggleStatus(<?php echo $user['id']; ?>, <?php echo $user['status']; ?>)" 
                       class="<?php echo $user['status'] == 1 ? 'text-green-500 hover:text-green-700' : 'text-red-500 hover:text-red-700'; ?>" 
                       title="<?php echo $user['status'] == 1 ? 'Kích hoạt (Nhấn để khóa)' : 'Khóa (Nhấn để kích hoạt)'; ?>">
                        <i class="fas <?php echo $user['status'] == 1 ? 'fa-lock-open' : 'fa-lock'; ?>"></i>
                    </a>
                </td>
                <td class="p-3 border-b"><?php echo $user['created_at']; ?></td>
                <td class="p-3 border-b">
                    <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="text-blue-500 hover:text-blue-700 mr-3" title="Chỉnh sửa">
                        <i class="fas fa-edit"></i>
                    </a>
                    <a href="modules/user_management/delete_user.php?id=<?php echo $user['id']; ?>" class="text-red-500 hover:text-red-700" title="Xóa"
                       onclick="return confirm('Bạn có chắc muốn xóa người dùng này?');">
                        <i class="fas fa-trash-alt"></i>
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<script>
document.getElementById('addUserForm').addEventListener('submit', function(event) {
    event.preventDefault();
    const formData = new FormData(this);
    for (let pair of formData.entries()) {
        console.log(pair[0] + ': ' + pair[1]); // Debug dữ liệu form
    }
    fetch('add_user_api.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message);
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => {
        console.error('Lỗi:', error);
        alert('Đã xảy ra lỗi khi thêm người dùng. Vui lòng kiểm tra console để biết thêm chi tiết.');
    });
});

function toggleStatus(userId, currentStatus) {
    if (!confirm('Bạn có chắc muốn ' + (currentStatus == 1 ? 'khóa' : 'kích hoạt') + ' người dùng này?')) {
        return;
    }

    const newStatus = currentStatus == 1 ? 0 : 1;
    fetch('toggle_status_api.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'user_id=' + encodeURIComponent(userId) + '&status=' + encodeURIComponent(newStatus)
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message);
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => {
        console.error('Lỗi:', error);
        alert('Đã xảy ra lỗi khi thay đổi trạng thái. Vui lòng kiểm tra console.');
    });
}
</script>