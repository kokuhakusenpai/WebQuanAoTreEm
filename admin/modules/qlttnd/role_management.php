<?php
session_start();
include('../../config/database.php');

// Kiểm tra kết nối cơ sở dữ liệu
if (!$conn) {
    header('HTTP/1.1 500 Internal Server Error');
    die(json_encode(['success' => false, 'message' => 'Kết nối cơ sở dữ liệu thất bại: ' . mysqli_connect_error()]));
}

// Kiểm tra nếu session chưa có thông tin user_id
if (!isset($_SESSION['user_id'])) {
    header('HTTP/1.1 403 Forbidden');
    die(json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập để truy cập!']));
}

// Kiểm tra quyền admin
$query = "SELECT role FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
if ($stmt === false) {
    error_log("Prepare failed: " . $conn->error);
    header('HTTP/1.1 500 Internal Server Error');
    die(json_encode(['success' => false, 'message' => 'Lỗi hệ thống khi kiểm tra quyền hạn.']));
}
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user || $user['role'] !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    die(json_encode(['success' => false, 'message' => 'Chỉ có admin mới có quyền truy cập vào trang này!']));
}

// Hàm lấy danh sách người dùng
function getUsersWithRoles($conn) {
    $query = "SELECT user_id, username, email, role FROM users ORDER BY role DESC, username ASC";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        error_log("Prepare failed: " . $conn->error);
        return false;
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $data;
}

// Xử lý cập nhật quyền hạn
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['user_id']) && isset($_POST['new_role'])) {
    $user_id = intval($_POST['user_id']);
    $new_role = trim($_POST['new_role']);

    // Kiểm tra tính hợp lệ của role
    $valid_roles = ['customer', 'admin'];
    if (!in_array($new_role, $valid_roles)) {
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['success' => false, 'message' => 'Quyền hạn không hợp lệ!']);
        exit;
    }

    // Prevent demoting the current admin user
    if ($user_id === $_SESSION['user_id'] && $new_role !== 'admin') {
        header('HTTP/1.1 403 Forbidden');
        echo json_encode(['success' => false, 'message' => 'Bạn không thể tự hạ quyền admin của mình!']);
        exit;
    }

    $query = "UPDATE users SET role = ? WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        error_log("Prepare failed: " . $conn->error);
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống khi chuẩn bị truy vấn.']);
        exit;
    }
    $stmt->bind_param("si", $new_role, $user_id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Quyền hạn đã được cập nhật!']);
    } else {
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['success' => false, 'message' => 'Cập nhật quyền hạn thất bại: ' . $stmt->error]);
    }
    $stmt->close();
    exit;
}

// Lấy danh sách người dùng
$users = getUsersWithRoles($conn);

// Đóng kết nối
$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Quyền Hạn</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            opacity: 0;
            transition: opacity 0.5s ease;
        }
        .toast.show {
            opacity: 1;
        }
        .table-container {
            overflow-x: auto;
        }
    </style>
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-6xl mx-auto">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Quản Lý Quyền Hạn</h2>

        <!-- Tab Navigation -->
        <div class="flex border-b border-gray-200 mb-4">
            <button onclick="loadContent('modules/qlttnd/user_activity.php')" class="tab-btn flex items-center px-4 py-2 text-gray-600 hover:text-blue-600 border-b-2 border-transparent hover:border-blue-600 transition-colors">
                <i class="fas fa-user mr-2"></i> Hoạt Động Của Người Dùng
            </button>
            <button onclick="showTab('role_management')" class="tab-btn flex items-center px-4 py-2 text-gray-600 hover:text-blue-600 border-b-2 border-transparent hover:border-blue-600 transition-colors tab-active">
                <i class="fas fa-lock mr-2"></i> Quản Lý Quyền Hạn
            </button>
        </div>

        <!-- Tab Content -->
        <div id="tabContent" class="bg-white p-6 rounded-lg shadow-sm">
            <div id="loadingSpinner" class="flex justify-center items-center h-32 hidden">
                <i class="fas fa-spinner fa-spin text-3xl text-blue-500"></i>
            </div>
            <div id="tabContentInner">
                <div id="role_management" class="tab-content active">
                    <div class="table-container">
                        <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                            <thead>
                                <tr class="bg-indigo-600 text-white">
                                    <th class="py-3 px-4 border-b text-left">ID</th>
                                    <th class="py-3 px-4 border-b text-left">Tên Người Dùng</th>
                                    <th class="py-3 px-4 border-b text-left">Email</th>
                                    <th class="py-3 px-4 border-b text-left">Quyền Hạn</th>
                                    <th class="py-3 px-4 border-b text-left">Thay Đổi Quyền Hạn</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($users): ?>
                                    <?php foreach ($users as $user): ?>
                                        <tr class="hover:bg-gray-100">
                                            <td class="py-2 px-4 border-b text-gray-700"><?php echo htmlspecialchars($user['user_id']); ?></td>
                                            <td class="py-2 px-4 border-b text-gray-700"><?php echo htmlspecialchars($user['username']); ?></td>
                                            <td class="py-2 px-4 border-b text-gray-700"><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td class="py-2 px-4 border-b text-gray-700"><?php echo htmlspecialchars($user['role']); ?></td>
                                            <td class="py-2 px-4 border-b text-gray-700">
                                                <form class="update-role-form flex items-center space-x-2" data-user-id="<?php echo htmlspecialchars($user['user_id']); ?>">
                                                    <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['user_id']); ?>">
                                                    <select name="new_role" class="border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                                        <option value="customer" <?php echo $user['role'] === 'customer' ? 'selected' : ''; ?>>Customer</option>
                                                        <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                                    </select>
                                                    <button type="submit" class="px-3 py-1 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 flex items-center">
                                                        <span class="submit-text">Cập Nhật</span>
                                                        <i class="fas fa-spinner fa-spin ml-2 hidden submit-spinner"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="py-2 px-4 border-b text-center text-gray-500">Không có người dùng nào trong hệ thống.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentTab = null;

        // Load tab content dynamically
        function loadContent(url) {
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('tab-active');
            });
            const activeBtn = document.querySelector(`button[onclick="loadContent('${url}')"]`);
            if (activeBtn) {
                activeBtn.classList.add('tab-active');
            }

            const spinner = document.getElementById('loadingSpinner');
            const tabContentInner = document.getElementById('tabContentInner');
            spinner.classList.remove('hidden');
            tabContentInner.innerHTML = '';

            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Không thể tải nội dung tab: ${response.status} - ${response.statusText}`);
                    }
                    return response.text();
                })
                .then(html => {
                    tabContentInner.innerHTML = html;
                    spinner.classList.add('hidden');
                    currentTab = url;
                })
                .catch(error => {
                    spinner.classList.add('hidden');
                    tabContentInner.innerHTML = '<p class="text-red-500">Lỗi khi tải nội dung tab: ' + error.message + '</p>';
                    showToast("Lỗi khi tải nội dung: " + error.message, "error");
                });
        }

        // Show tab
        function showTab(tabId) {
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('tab-active');
            });
            const activeBtn = document.querySelector(`button[onclick="showTab('${tabId}')"]`);
            if (activeBtn) {
                activeBtn.classList.add('tab-active');
            }

            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
                tab.classList.add('hidden');
            });
            const selectedTab = document.getElementById(tabId);
            if (selectedTab) {
                selectedTab.classList.add('active');
                selectedTab.classList.remove('hidden');
            }
        }

        // Hiển thị toast notification
        function showToast(message, type) {
            const toast = document.createElement('div');
            toast.className = `toast p-4 rounded-lg text-white ${type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
            toast.textContent = message;
            document.body.appendChild(toast);
            setTimeout(() => toast.classList.add('show'), 100);
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 500);
            }, 1500); // Reduced duration to 1.5 seconds for quicker reload
        }

        // Handle form submission with AJAX and reload page on success
        document.querySelectorAll('.update-role-form').forEach(form => {
            form.addEventListener('submit', function(event) {
                event.preventDefault();

                const submitBtn = form.querySelector('button[type="submit"]');
                const submitText = submitBtn.querySelector('.submit-text');
                const submitSpinner = submitBtn.querySelector('.submit-spinner');
                submitBtn.disabled = true;
                submitText.textContent = 'Đang cập nhật...';
                submitSpinner.classList.remove('hidden');

                const formData = new FormData(form);
                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    submitBtn.disabled = false;
                    submitText.textContent = 'Cập Nhật';
                    submitSpinner.classList.add('hidden');

                    if (data.success) {
                        showToast(data.message, 'success');
                        // Reload the page after a short delay to allow the toast to be seen
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000); // Delay of 2 seconds to show the toast
                    } else {
                        showToast(data.message, 'error');
                    }
                })
                .catch(error => {
                    submitBtn.disabled = false;
                    submitText.textContent = 'Cập Nhật';
                    submitSpinner.classList.add('hidden');
                    showToast("Lỗi khi cập nhật: " + error.message, "error");
                });
            });
        });
    </script>
</body>
</html>