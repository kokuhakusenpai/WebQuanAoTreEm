<?php
session_start();
include('../config/database.php');

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Lấy thông tin người dùng từ cơ sở dữ liệu
$username = $_SESSION['username'];
$sql = "SELECT avatar, username FROM users WHERE username = '$username'";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);
    $avatar = !empty($user['avatar']) ? 'assets/img/avatars/' . $user['avatar'] : 'assets/img/default-avatar.png';
    $username = $user['username'];
} else {
    $avatar = 'assets/img/default-avatar.png';
    $full_name = 'Tên không xác định';
}

// Xử lý tải ảnh nếu có yêu cầu POST
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['avatar'])) {
    $avatar = $_FILES['avatar'];
    $target_dir = "assets/img/avatars/";
    $target_file = $target_dir . basename($avatar['name']);
    $upload_ok = 1;
    $image_file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Kiểm tra định dạng ảnh
    if (!getimagesize($avatar['tmp_name'])) {
        $upload_ok = 0;
        $error_message = "Tệp không phải là hình ảnh.";
    }

    // Kiểm tra kích thước tệp
    if ($avatar['size'] > 5000000) {
        $upload_ok = 0;
        $error_message = "Tệp hình ảnh quá lớn.";
    }

    // Chỉ cho phép định dạng JPG, JPEG, PNG
    if (!in_array($image_file_type, ['jpg', 'jpeg', 'png'])) {
        $upload_ok = 0;
        $error_message = "Chỉ chấp nhận định dạng JPG, JPEG và PNG.";
    }

    // Lưu tệp nếu không có lỗi
    if ($upload_ok) {
        if (move_uploaded_file($avatar['tmp_name'], $target_file)) {
            $update_query = "UPDATE users SET avatar = '" . basename($avatar['name']) . "' WHERE username = '$username'";
            mysqli_query($conn, $update_query);

            // Cập nhật ảnh đại diện mới
            $avatar = $target_file;
            $success_message = "Tải lên ảnh đại diện thành công.";
        } else {
            $error_message = "Có lỗi xảy ra khi tải ảnh.";
        }
    }
}
?>

 
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang Quản Trị - Admin</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="home-page">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="profile-container">
            <label for="avatar-upload">
                <img src="<?php echo htmlspecialchars($avatar); ?>" alt="Avatar" class="avatar">
            </label>
            <input type="file" id="avatar-upload" name="avatar" onchange="uploadAvatar()" style="display: none;">
            <h2 class="profile-name"><?php echo htmlspecialchars($username); ?></h2>
        </div>
        <ul>
            <li><a href="#" onclick="loadContent('index.php')">Trang chủ</a></li>
            <li><a href="#" onclick="loadContent('modules/qladmin/users.php', 'Quản lý người dùng')">Quản lý người dùng</a></li>
            <li><a href="#" onclick="loadContent('modules/qlsp/product.php', 'Quản lý sản phẩm')">Quản lý sản phẩm</a></li>
            <li><a href="#" onclick="loadContent('modules/qldh/orders.php', 'Quản lý đơn hàng')">Quản lý đơn hàng</a></li>
            <li><a href="#" onclick="loadContent('modules/qlgd/interface.php', 'Quản lý giao diện')">Quản lý giao diện</a></li>
            <li><a href="#" onclick="loadContent('modules/qlttnd/Statistics_Logs.php', 'Quản lý thao tác người dùng')">Quản lý thao tác người dùng</a></li>
            <li><a href="logout.php" class="logout">Đăng xuất</a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <div class="main-content">
        <header class="main-header">
            <nav id="breadcrumb-nav">
                <span>Trang Chủ</span>
            </nav>
            <div class="search-bar">
                <input type="text" id="search" placeholder="Tìm kiếm..." oninput="performSearch()">
                <span class="search-icon"><i class="fas fa-search"></i></span>
            </div>
            <div id="search-results"></div>
        </header>
        <div id="dashboard-content">
            <h2>Chào Mừng Bạn Đến Với Trang Chủ</h2>
        </div>
    </div>

    <script>
        // AJAX tải nội dung động
        function loadContent(page, label = 'Trang Chủ') {
            const xhr = new XMLHttpRequest();
            xhr.open("GET", page, true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4) {
                    const contentArea = document.getElementById("dashboard-content");
            
                    if (page === 'index.php') {
                        contentArea.innerHTML = `
                        <h2>Chào Mừng Bạn Đến Với Trang Chủ</h2>
                        <p>Vui lòng chọn một mục từ menu để bắt đầu.</p>
                        `;

                        // Reset breadcrumb về "Trang Chủ"
                        const breadcrumb = document.getElementById("breadcrumb-nav");
                        breadcrumb.innerHTML = `<span>Trang Chủ</span>`;
                         
                        // Ẩn modal (nếu có)
                         const modal = document.getElementById('addProductModal');
                         if (modal) {
                            modal.style.display = 'none';
                        }
                    } else {
                        if (xhr.status === 200) {
                            contentArea.innerHTML = xhr.responseText;
                            const breadcrumb = document.getElementById("breadcrumb-nav");
                            breadcrumb.innerHTML = `<span>Trang Quản Trị</span> <i class="fas fa-chevron-right"></i> ${label}`;
                        } else {
                            contentArea.innerHTML = `<p style="color:red;">Lỗi tải trang: ${xhr.status} - ${xhr.statusText}</p>`;
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
                fetch('search_all.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ query: searchTerm })
                })
                
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success && data.results.length > 0) {
                        let resultsHTML = '';
                        data.results.forEach(result => {
                            resultsHTML += `<h3>Kết quả từ bảng: ${result.table}</h3>`;
                            resultsHTML += '<ul>';
                            result.data.forEach(row => {
                                resultsHTML += `
                                <li>${Object.entries(row).map(([key, value]) => `<strong>${key}:</strong> ${value}`).join('<br>')}</li>`;
                            });
                            resultsHTML += '</ul>';
                        });
                        resultsContainer.innerHTML = resultsHTML;
                    } else {
                        resultsContainer.innerHTML = '<p>Không tìm thấy kết quả nào.</p>';
                    }
                })
                .catch(error => {
                    resultsContainer.innerHTML = `<p style="color: red;">Lỗi khi tìm kiếm: ${error.message}</p>`;
                });
            } else {
                resultsContainer.innerHTML = '';
            }
        }

        // Tải avatar
        function uploadAvatar() {
            const fileInput = document.getElementById("avatar-upload");
            const formData = new FormData();
            if (fileInput.files.length > 0) {
                formData.append('avatar', fileInput.files[0]);
                fetch('upload_avatar.php', { method: 'POST', body: formData })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.querySelector('.avatar').src = data.avatar_url;
                            alert('Ảnh đại diện đã được thay đổi thành công!');
                        } else {
                            alert('Lỗi khi tải ảnh: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Lỗi kết nối:', error);
                        alert('Không thể tải ảnh. Vui lòng thử lại sau.');
                    });
            } else {
                alert('Vui lòng chọn một ảnh.');
            }
        }
    </script>
</body>
</html>