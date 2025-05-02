<?php
session_start();
include('../../config/database.php');

// Kiểm tra kết nối cơ sở dữ liệu
if (!$conn) {
    error_log("Database connection failed: " . mysqli_connect_error());
    die("Database connection failed: " . mysqli_connect_error());
}

// Truy vấn danh sách người dùng bằng prepared statement
$query = "SELECT * FROM user";
$stmt = $conn->prepare($query);
if ($stmt === false) {
    error_log("Prepare failed for SELECT query: " . $conn->error);
    die("Prepare failed: " . $conn->error);
}
$stmt->execute();
$result = $stmt->get_result();

// Xử lý thêm người dùng
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $phone = trim($_POST['phone']);
    $status = $_POST['status'];
    $password = password_hash('default123', PASSWORD_DEFAULT); // Mật khẩu mặc định

    // Ghi log dữ liệu đầu vào để kiểm tra
    error_log("Form submitted with: username=$username, email=$email, role=$role, phone=$phone, status=$status");

    // Kiểm tra dữ liệu đầu vào
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Email không hợp lệ!";
        error_log("Validation failed: Invalid email");
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $error_message = "Tên người dùng chỉ được chứa chữ cái, số và dấu gạch dưới!";
        error_log("Validation failed: Invalid username");
    } elseif (!preg_match('/^[0-9]{10,11}$/', $phone)) {
        $error_message = "Số điện thoại không hợp lệ!";
        error_log("Validation failed: Invalid phone");
    } else {
        // Thêm người dùng vào cơ sở dữ liệu
        $insert_query = "INSERT INTO user (username, email, role, phone, status, password) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        if ($stmt === false) {
            $error_message = "Prepare failed: " . $conn->error;
            error_log("Prepare failed for INSERT query: " . $conn->error);
        } else {
            $stmt->bind_param("ssssss", $username, $email, $role, $phone, $status, $password);
            if ($stmt->execute()) {
                error_log("User added successfully: username=$username");
                header("Location: users.php?success=User added successfully");
                exit;
            } else {
                $error_message = "Lỗi khi thêm người dùng: " . $stmt->error;
                error_log("Execute failed for INSERT query: " . $stmt->error);
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản Lý Người Dùng</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="styles.css" />
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-6xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Danh sách Người Dùng</h2>
            <button onclick="loadContent('modules/qladmin/add_user.php', 'Thêm Người Dùng')" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors flex items-center">
                <i class="fas fa-plus mr-2"></i> Thêm Người Dùng
            </button>
        </div>

        <!-- Modal Thêm Người Dùng -->
        <div id="addUserModal" class="fixed inset-0 bg-black/50 hidden flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded-lg max-w-md w-full">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Thêm Người Dùng</h3>
                <form method="POST" id="addUserForm" onsubmit="return validateForm()">
                    <div class="mb-4">
                        <label class="block text-sm text-gray-600 mb-1">Tên Người Dùng</label>
                        <input type="text" name="username" id="username" required class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm text-gray-600 mb-1">Email</label>
                        <input type="email" name="email" id="email" required class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm text-gray-600 mb-1">Số Điện Thoại</label>
                        <input type="text" name="phone" id="phone" required class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm text-gray-600 mb-1">Vai Trò</label>
                        <select name="role" class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                            <option value="admin">Admin</option>
                            <option value="customer">Customer</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm text-gray-600 mb-1">Trạng Thái</label>
                        <select name="status" class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                            <option value="active">Hoạt động</option>
                            <option value="inactive">Ngừng hoạt động</option>
                        </select>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors">Thêm</button>
                        <button type="button" onclick="hideAddUserModal()" class="bg-gray-300 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-400 transition-colors">Hủy</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Danh sách Người Dùng -->
        <div class="bg-white rounded-lg shadow-sm overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="bg-gray-200 text-gray-700">
                        <th class="p-3 text-left">ID Người Dùng</th>
                        <th class="p-3 text-left">Tên Người Dùng</th>
                        <th class="p-3 text-left">Email</th>
                        <th class="p-3 text-left">Số Điện Thoại</th>
                        <th class="p-3 text-left">Vai Trò</th>
                        <th class="p-3 text-left">Trạng Thái</th>
                        <th class="p-3 text-left">Thao Tác</th>
                    </tr>
                </thead>
                <tbody id="userTableBody">
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr class='border-b hover:bg-gray-50' data-id='{$row['id']}'>
                                <td class='p-3'>" . htmlspecialchars($row['id']) . "</td>
                                <td class='p-3'>" . htmlspecialchars(trim($row['username'])) . "</td>
                                <td class='p-3'>" . htmlspecialchars($row['email']) . "</td>
                                <td class='p-3'>" . htmlspecialchars($row['phone']) . "</td>
                                <td class='p-3'>" . htmlspecialchars($row['role']) . "</td>
                                <td class='p-3'>" . htmlspecialchars($row['status']) . "</td>
                                <td class='p-3'>
                                    <button class='text-blue-500 hover:text-blue-700 mr-3' onclick=\"window.location.href='edit_user.php?id=" . $row['id'] . "'\">
                                        <i class='fas fa-edit'></i>
                                    </button>
                                    <button class='text-red-500 hover:text-red-700 delete-btn' data-user-id='{$row['id']}'>
                                        <i class='fas fa-trash-alt'></i>
                                    </button>
                                </td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7' class='p-3 text-center text-gray-500'>Không có người dùng nào.</td></tr>";
                    }
                    $result->close();
                    $conn->close();
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Hiển thị thông báo nếu có -->
        <?php if (isset($error_message)): ?>
            <script>
                console.log("Error message: <?php echo addslashes($error_message); ?>");
                showToast("<?php echo addslashes($error_message); ?>", "error");
            </script>
        <?php endif; ?>
        <?php if (isset($_GET['success'])): ?>
            <script>
                console.log("Success message: <?php echo addslashes($_GET['success']); ?>");
                showToast("<?php echo addslashes($_GET['success']); ?>", "success");
            </script>
        <?php endif; ?>
    </div>

    <script>
    // Hiển thị modal thêm người dùng
    function showAddUserModal() {
        console.log("Opening add user modal");
        const modal = document.getElementById('addUserModal');
        if (modal) {
            modal.classList.remove('hidden');
        } else {
            console.error("Add user modal not found!");
        }
    }

    // Ẩn modal thêm người dùng
    function hideAddUserModal() {
        console.log("Closing add user modal");
        const modal = document.getElementById('addUserModal');
        if (modal) {
            modal.classList.add('hidden');
            const form = document.getElementById('addUserForm');
            if (form) {
                form.reset();
            }
        }
    }

    // Validate form trước khi submit
    function validateForm() {
        console.log("Validating form");
        const username = document.getElementById('username').value.trim();
        const email = document.getElementById('email').value.trim();
        const phone = document.getElementById('phone').value.trim();

        const usernamePattern = /^[a-zA-Z0-9_]+$/;
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        const phonePattern = /^[0-9]{10,11}$/;

        if (!usernamePattern.test(username)) {
            showToast("Tên người dùng chỉ được chứa chữ cái, số và dấu gạch dưới!", "error");
            console.log("Validation failed: Invalid username");
            return false;
        }
        if (!emailPattern.test(email)) {
            showToast("Email không hợp lệ!", "error");
            console.log("Validation failed: Invalid email");
            return false;
        }
        if (!phonePattern.test(phone)) {
            showToast("Số điện thoại không hợp lệ!", "error");
            console.log("Validation failed: Invalid phone");
            return false;
        }
        console.log("Form validation passed");
        return true;
    }

    // Xóa người dùng bằng AJAX
    function deleteUser(userId) {
        console.log(`Initiating delete for user ID: ${userId}`);
        if (confirm("Bạn có chắc chắn muốn xóa người dùng này?")) {
            console.log("Delete confirmed, sending AJAX request");
            fetch(`delete_user.php?id=${userId}`, { method: 'GET' })
                .then(response => {
                    console.log(`Fetch response status: ${response.status}`);
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log("Fetch response data:", data);
                    if (data.success) {
                        showToast("Xóa người dùng thành công!", "success");
                        const row = document.querySelector(`tr[data-user-id="${userId}"]`);
                        if (row) {
                            row.remove();
                            console.log(`Removed row for user ID: ${userId}`);
                        }
                        const tbody = document.getElementById('userTableBody');
                        if (tbody.children.length === 0) {
                            tbody.innerHTML = "<tr><td colspan='7' class='p-3 text-center text-gray-500'>Không có người dùng nào.</td></tr>";
                            console.log("Table is now empty");
                        }
                    } else {
                        showToast("Lỗi khi xóa người dùng: " + data.message, "error");
                        console.log("Delete failed:", data.message);
                    }
                })
                .catch(error => {
                    showToast("Lỗi khi xóa người dùng: " + error.message, "error");
                    console.error("Fetch error:", error);
                });
        } else {
            console.log("Delete cancelled by user");
        }
    }

    // Gắn sự kiện cho các nút Delete
    function attachDeleteEvents() {
        const deleteButtons = document.querySelectorAll('.delete-btn');
        console.log(`Found ${deleteButtons.length} delete buttons`);
        deleteButtons.forEach(button => {
            // Xóa sự kiện cũ để tránh trùng lặp
            button.removeEventListener('click', handleDeleteClick);
            button.addEventListener('click', handleDeleteClick);
        });
    }

    function handleDeleteClick() {
        const userId = this.getAttribute('data-user-id');
        console.log(`Delete button clicked for user ID: ${userId}`);
        deleteUser(userId);
    }

    // Hiển thị toast notification
    function showToast(message, type) {
        console.log(`Showing toast: ${message} (${type})`);
        const toast = document.createElement('div');
        toast.className = `toast p-4 rounded-lg text-white ${type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
        toast.textContent = message;
        document.body.appendChild(toast);
        setTimeout(() => toast.classList.add('show'), 100);
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 500);
        }, 3000);
    }

    // Cập nhật loadContent để gắn lại sự kiện sau khi tải nội dung
    function loadContent(page, label = 'Trang Chủ') {
        const contentArea = document.getElementById("dashboard-content");
        contentArea.innerHTML = '<div class="flex justify-center items-center h-64"><i class="fas fa-spinner fa-spin text-3xl text-blue-500"></i></div>';

        const xhr = new XMLHttpRequest();
        xhr.open("GET", page, true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                if (page === 'index.php') {
                    contentArea.innerHTML = `
                        <h2 class="text-2xl font-bold text-gray-800 mb-3">Chào Mừng Bạn Đến Với Trang Chủ</h2>
                        <p class="text-gray-600">Vui lòng chọn một mục từ menu để bắt đầu.</p>
                    `;
                    document.getElementById("breadcrumb-nav").innerHTML = `<span>Trang Chủ</span>`;
                    const modal = document.getElementById('addProductModal');
                    if (modal) modal.style.display = 'none';
                } else {
                    if (xhr.status === 200) {
                        contentArea.innerHTML = xhr.responseText;
                        document.getElementById("breadcrumb-nav").innerHTML = `<span>Trang Quản Trị</span> <i class="fas fa-chevron-right mx-2 text-gray-400"></i> ${label}`;
                        // Gắn lại sự kiện cho các nút Delete sau khi tải nội dung
                        attachDeleteEvents();
                    } else {
                        contentArea.innerHTML = `<p class="text-red-500">Lỗi tải trang: ${xhr.status} - ${xhr.statusText}</p>`;
                    }
                }
            }
        };
        xhr.send();
    }

    // Gắn sự kiện ban đầu khi trang tải
    document.addEventListener('DOMContentLoaded', () => {
        console.log("DOMContentLoaded event fired");
        attachDeleteEvents();
    });
</script>
</body>
</html>