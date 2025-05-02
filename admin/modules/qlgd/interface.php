<?php
session_start();
include('../../config/database.php');

// Kiểm tra kết nối cơ sở dữ liệu
if (!$conn) {
    error_log("Database connection failed: " . mysqli_connect_error());
    die("Database connection failed: " . mysqli_connect_error());
}

// Truy vấn cài đặt bằng prepared statement
$query = "SELECT * FROM site_config WHERE id = ?";
$stmt = $conn->prepare($query);
if ($stmt === false) {
    error_log("Prepare failed: " . $conn->error);
    die("Prepare failed: " . $conn->error);
}
$id = 1;
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$settings = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Giao Diện</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="styles.css" />
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-6xl mx-auto">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Quản Lý Giao Diện</h2>

        <!-- Tab Navigation -->
        <div class="flex border-b border-gray-200 mb-4">
            <button onclick="loadContent('modules/qlgd/manhinh.php', 'Màn hình')" class="tab-btn flex items-center px-4 py-2 text-gray-600 hover:text-blue-600 border-b-2 border-transparent hover:border-blue-600 transition-colors">
                <i class="fas fa-home mr-2"></i> Màn hình
            </button>
            <button onclick="loadContent('modules/qlgd/banners.php', 'Banners')" class="tab-btn flex items-center px-4 py-2 text-gray-600 hover:text-blue-600 border-b-2 border-transparent hover:border-blue-600 transition-colors">
                <i class="fas fa-image mr-2"></i> Banners
            </button>
            <button onclick="loadContent('modules/qlgd/baiviet.php', 'Bài viết')" class="tab-btn flex items-center px-4 py-2 text-gray-600 hover:text-blue-600 border-b-2 border-transparent hover:border-blue-600 transition-colors">
                <i class="fas fa-file-alt mr-2"></i> Bài viết
            </button>
        </div>

        <!-- Tab Content -->
        <div id="tabContent" class="bg-white p-6 rounded-lg shadow-sm">
            <div id="loadingSpinner" class="flex justify-center items-center h-32 hidden">
                <i class="fas fa-spinner fa-spin text-3xl text-blue-500"></i>
            </div>
            <div id="tabContentInner"></div>
        </div>
    </div>

    <script>
        let currentTab = null;

        // Load tab content dynamically
        function loadContent(tabName, url) {
            // Update active tab styling
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('tab-active');
            });
            const activeBtn = document.querySelector(`button[onclick="loadContent('${tabName}', '${url}')"]`);
            if (activeBtn) {
                activeBtn.classList.add('tab-active');
            }

            // Show loading spinner
            const spinner = document.getElementById('loadingSpinner');
            const tabContentInner = document.getElementById('tabContentInner');
            spinner.classList.remove('hidden');
            tabContentInner.innerHTML = ''; // Clear previous content

            // Fetch tab content
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
                    currentTab = tabName;
                })
                .catch(error => {
                    spinner.classList.add('hidden');
                    tabContentInner.innerHTML = '<p class="text-red-500">Lỗi khi tải nội dung tab: ' + error.message + '</p>';
                    showToast("Lỗi khi tải nội dung: " + error.message, "error");
                });
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

        // Load default tab on page load
        document.addEventListener('DOMContentLoaded', () => {
            loadContent('manhinh', '/WEBQUANAOTREEM/admin/modules/qlgd/manhinh.php');
        });
    </script>
</body>
</html>