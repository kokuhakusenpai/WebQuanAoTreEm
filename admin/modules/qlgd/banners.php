<?php
include('../../config/database.php');

// Lấy dữ liệu hiện tại
$query = "SELECT * FROM banners WHERE banner_id = 1"; // Bảng `banners` chứa thông tin banner
$result = mysqli_query($conn, $query);
$banner = mysqli_fetch_assoc($result);

// Xử lý form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = mysqli_real_escape_string($conn, $_POST['title']); // Tiêu đề banner
    $image_url = mysqli_real_escape_string($conn, $_POST['image_url']); // URL hình ảnh banner
    $status = isset($_POST['status']) ? 1 : 0; // Trạng thái banner (hiển thị hoặc ẩn)

    $update = "UPDATE banners SET title = '$title', image_url = '$image_url', status = $status WHERE id = 1";
    mysqli_query($conn, $update);
    header("Location: banners.php"); // Chuyển hướng về trang quản lý banner
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản Lý Banner</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .field-label {
            font-size: 1rem;
            font-weight: 600;
            color: #4A5568;
            margin-bottom: 0.5rem;
            display: block;
        }
        .input-field {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #E2E8F0;
            background-color: #F7FAFC;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        .input-field:focus {
            outline: none;
            border-color: #4F46E5;
            background-color: #fff;
        }
    </style>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen p-4">
    <div class="bg-white p-6 rounded-lg custom-shadow w-full max-w-3xl">
        <h2 class="text-2xl font-bold text-gray-700 text-center mb-6">Quản Lý Banner</h2>
        <form method="POST" action="banners.php" class="space-y-6">
            <!-- Tiêu đề banner -->
            <div>
                <label for="title" class="field-label">Tiêu đề Banner:</label>
                <input type="text" id="title" name="title" value="<?= $banner['title'] ?? '' ?>" placeholder="Nhập tiêu đề banner" required class="input-field">
            </div>

            <!-- URL hình ảnh banner -->
            <div>
                <label for="image_url" class="field-label">URL Hình Ảnh:</label>
                <input type="text" id="image_url" name="image_url" value="<?= $banner['image_url'] ?? '' ?>" placeholder="Nhập URL hình ảnh" required class="input-field">
            </div>

            <!-- Trạng thái banner -->
            <div>
                <label for="status" class="field-label">Trạng Thái Hiển Thị:</label>
                <input type="checkbox" id="status" name="status" <?= !empty($banner['status']) ? 'checked' : '' ?>> Hiển Thị
            </div>

            <!-- Nút hành động -->
            <div class="flex justify-between items-center">
                <a href="banners.php" class="text-blue-600 hover:underline text-lg font-medium">
                    &#8592; Quay lại
                </a>
                <button type="submit" class="px-6 py-3 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700">
                    Lưu Banner
                </button>
            </div>
        </form>
    </div>
</body>
</html>