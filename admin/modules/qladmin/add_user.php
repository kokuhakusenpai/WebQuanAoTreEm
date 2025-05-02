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

// Xử lý AJAX request để thêm người dùng
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $status = $_POST['status'] ?? '';
    $password = $_POST['password'] ?? '';

    // Server-side validation
    if (empty($username)) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['success' => false, 'message' => 'Tên người dùng không được để trống!'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['success' => false, 'message' => 'Tên người dùng chỉ được chứa chữ cái, số và dấu gạch dưới!'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['success' => false, 'message' => 'Email không hợp lệ!'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    if (empty($phone) || !preg_match('/^[0-9]{10,11}$/', $phone)) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['success' => false, 'message' => 'Số điện thoại không hợp lệ!'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    if (strlen($password) < 8) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['success' => false, 'message' => 'Mật khẩu phải có ít nhất 8 ký tự!'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Bắt đầu transaction để đảm bảo tính toàn vẹn dữ liệu
    $conn->begin_transaction();
    try {
        // Mã hóa mật khẩu
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);
        if ($password_hashed === false) {
            throw new Exception('Lỗi khi mã hóa mật khẩu!');
        }

        // Thêm người dùng vào cơ sở dữ liệu
        $sql = "INSERT INTO users (username, email, role, phone, status, password) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            error_log("Prepare failed: " . $conn->error);
            throw new Exception('Lỗi hệ thống khi chuẩn bị truy vấn.');
        }

        $stmt->bind_param("ssssss", $username, $email, $role, $phone, $status, $password_hashed);
        if (!$stmt->execute()) {
            $error_message = $stmt->error;
            if (strpos($error_message, "Duplicate entry") !== false) {
                if (strpos($error_message, "'username'") !== false) {
                    throw new Exception("Tên người dùng '$username' đã tồn tại!");
                } elseif (strpos($error_message, "'email'") !== false) {
                    throw new Exception("Email '$email' đã tồn tại!");
                }
            }
            throw new Exception('Lỗi khi thêm người dùng: ' . $error_message);
        }

        $new_user_id = $stmt->insert_id;
        $stmt->close();

        // Xử lý trạng thái để hiển thị
        $status_text = '';
        switch ($status) {
            case 'active':
                $status_text = 'Hoạt động';
                break;
            case 'inactive':
                $status_text = 'Ngừng hoạt động';
                break;
        }

        // Commit transaction
        $conn->commit();

        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'success' => true,
            'message' => 'Người dùng đã được thêm thành công!',
            'user' => [
                'user_id' => $new_user_id,
                'username' => $username,
                'email' => $email,
                'role' => $role,
                'phone' => $phone,
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
    <title>Thêm Người Dùng</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
<div class="bg-white p-8 rounded-lg shadow-lg max-w-md w-full mx-auto">
    <h1 class="text-2xl font-bold text-gray-800 mb-6 text-center">Thêm Người Dùng</h1>
    <form id="addUserFormAjax" action="/WEBQUANAOTREEM/admin/modules/qladmin/add_user.php" method="POST" onsubmit="submitAddUserForm(event)">
        <div class="mb-4">
            <label for="username" class="block text-sm text-gray-600 mb-2">Tên Người Dùng</label>
            <input type="text" id="username" name="username" required class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" placeholder="Nhập tên người dùng">
        </div>
        <div class="mb-4">
            <label for="email" class="block text-sm text-gray-600 mb-2">Email</label>
            <input type="email" id="email" name="email" required class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" placeholder="Nhập email">
        </div>
        <div class="mb-4">
            <label for="phone" class="block text-sm text-gray-600 mb-2">Số Điện Thoại</label>
            <input type="text" id="phone" name="phone" required class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" placeholder="Nhập số điện thoại">
        </div>
        <div class="mb-4">
            <label for="role" class="block text-sm text-gray-600 mb-2">Vai Trò</label>
            <select name="role" id="role" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                <option value="admin">Admin</option>
                <option value="customer">Customer</option>
            </select>
        </div>
        <div class="mb-4">
            <label for="status" class="block text-sm text-gray-600 mb-2">Trạng Thái</label>
            <select name="status" id="status" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                <option value="active">Hoạt động</option>
                <option value="inactive">Ngừng hoạt động</option>
            </select>
        </div>
        <div class="mb-6">
            <label for="password" class="block text-sm text-gray-600 mb-2">Mật Khẩu</label>
            <input type="password" id="password" name="password" required class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" placeholder="Nhập mật khẩu">
        </div>
        <div class="flex justify-end space-x-3">
            <button type="submit" id="addButton" class="bg-blue-500 text-white px-5 py-2 rounded-lg hover:bg-blue-600 transition-colors flex items-center">
                <span class="submit-text">Thêm</span>
                <i class="fas fa-spinner fa-spin ml-2 hidden submit-spinner"></i>
            </button>
            <button type="button" onclick="loadContentWithFallback('modules/qladmin/users.php', 'Quản lý Người Dùng')" class="bg-gray-300 text-gray-800 px-5 py-2 rounded-lg hover:bg-gray-400 transition-colors">Hủy</button>
        </div>
    </form>
</div>

<script>
    // Fallback for loadContent if not defined
    if (typeof loadContent !== 'function') {
        function loadContent(url, title) {
            console.log(`Loading content: ${url} (${title})`);
            window.location.href = url;
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

    function submitAddUserForm(event) {
        event.preventDefault();

        const addButton = document.getElementById('addButton');
        const submitText = addButton.querySelector('.submit-text');
        const submitSpinner = addButton.querySelector('.submit-spinner');
        addButton.disabled = true;
        submitText.textContent = 'Đang thêm...';
        submitSpinner.classList.remove('hidden');

        const username = document.getElementById('username').value.trim();
        const email = document.getElementById('email').value.trim();
        const phone = document.getElementById('phone').value.trim();
        const password = document.getElementById('password').value;
        const role = document.getElementById('role').value;
        const status = document.getElementById('status').value;

        // Client-side validation
        if (!username) {
            showToast("Tên người dùng không được để trống!", "error");
            addButton.disabled = false;
            submitText.textContent = 'Thêm';
            submitSpinner.classList.add('hidden');
            return;
        }
        if (!/^[a-zA-Z0-9_]+$/.test(username)) {
            showToast("Tên người dùng chỉ được chứa chữ cái, số và dấu gạch dưới!", "error");
            addButton.disabled = false;
            submitText.textContent = 'Thêm';
            submitSpinner.classList.add('hidden');
            return;
        }
        if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            showToast("Email không hợp lệ!", "error");
            addButton.disabled = false;
            submitText.textContent = 'Thêm';
            submitSpinner.classList.add('hidden');
            return;
        }
        if (!phone || !/^[0-9]{10,11}$/.test(phone)) {
            showToast("Số điện thoại không hợp lệ!", "error");
            addButton.disabled = false;
            submitText.textContent = 'Thêm';
            submitSpinner.classList.add('hidden');
            return;
        }
        if (password.length < 8) {
            showToast("Mật khẩu phải có ít nhất 8 ký tự!", "error");
            addButton.disabled = false;
            submitText.textContent = 'Thêm';
            submitSpinner.classList.add('hidden');
            return;
        }

        const formData = new FormData(document.getElementById('addUserFormAjax'));
        console.log("Submitting form data:", Object.fromEntries(formData));

        fetch('/WEBQUANAOTREEM/admin/modules/qladmin/add_user.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log("Response status:", response.status);
            return response.json();
        })
        .then(data => {
            console.log("Response data:", data);
            addButton.disabled = false;
            submitText.textContent = 'Thêm';
            submitSpinner.classList.add('hidden');

            if (data.success) {
                showToast(data.message, "success");

                // Thêm người dùng mới vào bảng trên users.php
                if (typeof addUserToTable === 'function') {
                    addUserToTable(data.user);
                }

                // Reset form và quay lại danh sách người dùng
                document.getElementById('addUserFormAjax').reset();
                setTimeout(() => {
                    loadContentWithFallback('modules/qladmin/users.php', 'Quản lý Người Dùng');
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
            showToast("Lỗi khi thêm người dùng: " + error.message, "error");
        });
    }
</script>
</body>
</html>