<?php
session_start();
include('../config/database.php');

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Lấy thông tin người dùng từ cơ sở dữ liệu bằng prepared statement
$username = $_SESSION['username'];
$sql = "SELECT username FROM user WHERE username = ?"; 
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $avatar = 'assets/img/default-avatar.png'; // Không có cột avatar, sử dụng ảnh mặc định
    $username = $user['username'];
} else {
    $avatar = 'assets/img/default-avatar.png';
    $username = 'Tên không xác định';
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang Quản Trị - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="styles.css" />
</head>
<body class="flex min-h-screen bg-gray-100">
    <!-- Sidebar -->
    <aside class="sidebar w-64 bg-gradient-to-b from-blue-500 to-blue-200 text-white p-5 fixed h-full shadow-lg hover:w-72 transition-all duration-300">
        <div class="text-center mb-8">
            <label for="avatar-upload">
                <img src="<?php echo htmlspecialchars($avatar); ?>" alt="Avatar" class="avatar w-24 h-24 rounded-full border-4 border-white shadow-lg cursor-pointer hover:scale-105 transition-transform duration-300 mx-auto">
            </label>
            <input type="file" id="avatar-upload" name="avatar" accept="image/*" class="hidden">
            <h2 class="mt-4 text-xl font-semibold"><?php echo htmlspecialchars($username); ?></h2>
        </div>
        <ul class="space-y-2">
            <li><a href="#" onclick="loadContent('index.php')" class="flex items-center p-3 rounded-lg hover:bg-white/20 transition-all duration-300"><i class="fas fa-home mr-3"></i> <span>Trang chủ</span></a></li>
            <li><a href="#" onclick="loadContent('modules/qladmin/users.php', 'Quản lý người dùng')" class="flex items-center p-3 rounded-lg hover:bg-white/20 transition-all duration-300"><i class="fas fa-users mr-3"></i> <span>Quản lý người dùng</span></a></li>
            <li><a href="#" onclick="loadContent('modules/qlsp/product.php', 'Quản lý sản phẩm')" class="flex items-center p-3 rounded-lg hover:bg-white/20 transition-all duration-300"><i class="fas fa-box mr-3"></i> <span>Quản lý sản phẩm</span></a></li>
            <li><a href="#" onclick="loadContent('modules/qldh/orders.php', 'Quản lý đơn hàng')" class="flex items-center p-3 rounded-lg hover:bg-white/20 transition-all duration-300"><i class="fas fa-shopping-cart mr-3"></i> <span>Quản lý đơn hàng</span></a></li>
            <li><a href="#" onclick="loadContent('modules/qlgd/interface.php', 'Quản lý giao diện')" class="flex items-center p-3 rounded-lg hover:bg-white/20 transition-all duration-300"><i class="fas fa-paint-brush mr-3"></i> <span>Quản lý giao diện</span></a></li>
            <li><a href="#" onclick="loadContent('modules/qlttnd/Statistics_Logs.php', 'Quản lý thao tác người dùng')" class="flex items-center p-3 rounded-lg hover:bg-white/20 transition-all duration-300"><i class="fas fa-chart-line mr-3"></i> <span>Quản lý thao tác người dùng</span></a></li>
            <li><a href="logout.php" class="flex items-center p-3 rounded-lg text-red-400 font-medium hover:bg-white/20 transition-all duration-300"><i class="fas fa-sign-out-alt mr-3"></i> <span>Đăng xuất</span></a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <div class="main-content ml-64 flex-1 p-6 sm:ml-64">
        <header class="flex justify-between items-center bg-white p-4 rounded-lg shadow-sm mb-5">
            <nav id="breadcrumb-nav" class="text-gray-600">
                <span>Trang Chủ</span>
            </nav>
            <div class="relative w-72">
                <input type="text" id="search" placeholder="Tìm kiếm..." oninput="performSearch()" class="w-full p-2 pl-4 pr-10 border border-gray-300 rounded-full focus:outline-none focus:border-blue-500 transition-colors">
                <span class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500"><i class="fas fa-search"></i></span>
            </div>
            <div id="search-results" class="bg-white rounded-lg shadow-md mt-2 p-4 max-h-80 overflow-y-auto"></div>
        </header>
        <div id="dashboard-content" class="bg-white p-6 rounded-lg shadow-sm">
            <h2 class="text-2xl font-bold text-gray-800 mb-3">Chào Mừng Bạn Đến Với Trang Chủ</h2>
            <p class="text-gray-600">Vui lòng chọn một mục từ menu để bắt đầu.</p>
        </div>
    </div>

    <!-- Modal for Avatar Preview -->
    <div id="avatar-modal" class="fixed inset-0 bg-black/50 hidden flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg text-center max-w-md w-full">
            <img id="avatar-preview" src="" alt="Avatar Preview" class="max-w-full rounded-lg mb-4">
            <button onclick="confirmUpload()" class="bg-blue-500 text-white px-5 py-2 rounded-lg hover:bg-blue-600 transition-colors">Xác nhận</button>
            <button onclick="cancelUpload()" class="bg-gray-300 text-gray-800 px-5 py-2 rounded-lg hover:bg-gray-400 transition-colors ml-3">Hủy</button>
        </div>
    </div>

    <script>
        let selectedFile = null;

        // AJAX tải nội dung động
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
                        } else {
                            contentArea.innerHTML = `<p class="text-red-500">Lỗi tải trang: ${xhr.status} - ${xhr.statusText}</p>`;
                        }
                    }
                }
            };
            xhr.send();
        }

        // Tìm kiếm
        function performSearch() {
            const searchTerm = document.getElementById('search').value.trim();
            const resultsContainer = document.getElementById('search-results');

            if (searchTerm !== "") {
                resultsContainer.innerHTML = '<div class="flex justify-center"><i class="fas fa-spinner fa-spin text-blue-500"></i></div>';
                fetch('search_all.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ query: searchTerm })
                })
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
                    return response.json();
                })
                .then(data => {
                    if (data.success && data.results.length > 0) {
                        let resultsHTML = '';
                        data.results.forEach(result => {
                            resultsHTML += `<h3 class="text-lg font-semibold text-gray-700">Kết quả từ bảng: ${result.table}</h3>`;
                            resultsHTML += '<ul class="list-disc pl-5">';
                            result.data.forEach(row => {
                                resultsHTML += `
                                    <li class="mb-2">${Object.entries(row).map(([key, value]) => `<strong>${key}:</strong> ${value}`).join('<br>')}</li>`;
                            });
                            resultsHTML += '</ul>';
                        });
                        resultsContainer.innerHTML = resultsHTML;
                    } else {
                        resultsContainer.innerHTML = '<p class="text-gray-500">Không tìm thấy kết quả nào.</p>';
                    }
                })
                .catch(error => {
                    resultsContainer.innerHTML = `<p class="text-red-500">Lỗi khi tìm kiếm: ${error.message}</p>`;
                });
            } else {
                resultsContainer.innerHTML = '';
            }
        }

        // Xử lý chọn và preview ảnh đại diện
        document.getElementById('avatar-upload').addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file) {
                selectedFile = file;
                const reader = new FileReader();
                reader.onload = function (event) {
                    document.getElementById('avatar-preview').src = event.target.result;
                    document.getElementById('avatar-modal').classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            }
        });

        // Xác nhận tải ảnh
        function confirmUpload() {
            if (!selectedFile) {
                showToast('Vui lòng chọn một ảnh.', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('avatar', selectedFile);

            fetch('modules/upload_avatar.php', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.querySelector('.avatar').src = data.avatar_url;
                        showToast('Ảnh đại diện đã được thay đổi thành công!', 'success');
                    } else {
                        showToast('Lỗi khi tải ảnh: ' + data.message, 'error');
                    }
                    document.getElementById('avatar-modal').classList.add('hidden');
                    selectedFile = null;
                    document.getElementById('avatar-upload').value = '';
                })
                .catch(error => {
                    showToast('Không thể tải ảnh. Vui lòng thử lại sau.', 'error');
                    document.getElementById('avatar-modal').classList.add('hidden');
                });
        }

        // Hủy tải ảnh
        function cancelUpload() {
            document.getElementById('avatar-modal').classList.add('hidden');
            document.getElementById('avatar-upload').value = '';
            selectedFile = null;
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
            }, 3000);
        }
    </script>
</body>
</html>