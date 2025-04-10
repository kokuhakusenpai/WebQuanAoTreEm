<?php
include('../../config/database.php');

// Xử lý khi gửi biểu mẫu
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = mysqli_real_escape_string($conn, $_POST['title']); // Tiêu đề bài viết
    $content = mysqli_real_escape_string($conn, $_POST['content']); // Nội dung bài viết

    // Kiểm tra tiêu đề và nội dung không được để trống
    if (!empty($title) && !empty($content)) {
        $query = "INSERT INTO articles (title, content) VALUES ('$title', '$content')";
        if (mysqli_query($conn, $query)) {
            // Chuyển hướng về trang quản lý bài viết sau khi lưu
            header("Location: baiviet.php");
            exit;
        } else {
            echo "Lỗi: Không thể thêm bài viết. " . mysqli_error($conn);
        }
    } else {
        echo "Lỗi: Tiêu đề và nội dung không được để trống.";
    }
}
?>


<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Bài Viết</title>
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
        <h2 class="text-2xl font-bold text-gray-700 text-center mb-6">Thêm Bài Viết</h2>
        <form method="POST" action="baiviet.php" class="space-y-6">
            <!-- Tiêu đề bài viết -->
            <div>
                <label for="title" class="field-label">Tiêu đề bài viết:</label>
                <input type="text" id="title" name="title" placeholder="Nhập tiêu đề bài viết" required
                       class="input-field">
            </div>

            <!-- Nội dung bài viết -->
            <div>
                <label for="content" class="field-label">Nội dung bài viết:</label>
                <textarea id="content" name="content" rows="6" placeholder="Nhập nội dung bài viết" required
                          class="input-field resize-none"></textarea>
            </div>

            <!-- Nút hành động -->
            <div class="flex justify-between items-center">
                <!-- Nút Lưu Bài Viết -->
                <div class="w-1/2 text-right">
                    <button type="submit" class="px-6 py-3 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700">
                        Lưu Bài Viết
                    </button>
                </div>
                <!-- Nút Quay lại -->
                <div class="w-1/2 text-left">
                    <a href="baiviet.php" class="text-blue-600 hover:underline text-lg font-medium">
                        &#8592; Quay lại
                    </a>
                </div>
            </div>
        </form>
    </div>
</body>
</html>