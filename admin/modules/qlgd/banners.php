<?php
session_start();
include('../../config/database.php');

// Xử lý form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $image_url = trim($_POST['image_url']);
    $status = isset($_POST['status']) ? 1 : 0;

    // Server-side validation
    if (empty($title)) {
        echo json_encode(['success' => false, 'message' => 'Tiêu đề banner không được để trống!']);
        exit;
    }
    if (empty($image_url) || !filter_var($image_url, FILTER_VALIDATE_URL)) {
        echo json_encode(['success' => false, 'message' => 'URL hình ảnh không hợp lệ!']);
        exit;
    }

    // Cập nhật banner bằng prepared statement
    $update_query = "UPDATE banner SET title = ?, image_url = ?, status = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    if ($stmt === false) {
        error_log("Prepare failed: " . $conn->error);
        echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống khi chuẩn bị truy vấn.']);
        exit;
    }

    $id = 1;
    $stmt->bind_param("ssii", $title, $image_url, $status, $id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Cập nhật banner thành công!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi khi cập nhật: ' . $stmt->error]);
    }
    $stmt->close();
    $conn->close();
    exit;
}

// Lấy dữ liệu hiện tại
$query = "SELECT * FROM banner WHERE id = ?";
$stmt = $conn->prepare($query);
if ($stmt === false) {
    error_log("Prepare failed: " . $conn->error);
    die("Prepare failed: " . $conn->error);
}
$id = 1;
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$banner = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>

<div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-3xl">
    <h2 class="text-2xl font-bold text-gray-700 text-center mb-6">Quản Lý Banner</h2>
    <form id="bannerForm" onsubmit="submitBannerForm(event)" class="space-y-6">
        <div>
            <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Tiêu đề Banner</label>
            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($banner['title'] ?? ''); ?>" placeholder="Nhập tiêu đề banner" required class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-gray-50">
            <p id="title_error" class="text-red-500 text-sm mt-1 hidden"></p>
        </div>
        <div>
            <label for="image_url" class="block text-sm font-medium text-gray-700 mb-2">URL Hình Ảnh</label>
            <input type="url" id="image_url" name="image_url" value="<?php echo htmlspecialchars($banner['image_url'] ?? ''); ?>" placeholder="Nhập URL hình ảnh" required class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-gray-50">
            <p id="image_url_error" class="text-red-500 text-sm mt-1 hidden"></p>
        </div>
        <div>
            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Trạng Thái Hiển Thị</label>
            <input type="checkbox" id="status" name="status" <?php echo !empty($banner['status']) ? 'checked' : ''; ?> class="h-5 w-5 text-blue-500 focus:ring-blue-500">
            <span class="ml-2 text-gray-700">Hiển Thị</span>
        </div>
        <div class="flex justify-between items-center">
            <a href="javascript:void(0)" onclick="loadContent('banners', '/WEBQUANAOTREEM/admin/modules/qlgd/banners.php')" class="text-blue-600 hover:underline text-lg font-medium">
                ← Quay lại
            </a>
            <button type="submit" id="submitBtn" class="px-6 py-3 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 flex items-center">
                <span id="submitText">Lưu Banner</span>
                <i id="submitSpinner" class="fas fa-spinner fa-spin ml-2 hidden"></i>
            </button>
        </div>
    </form>
</div>

<script>
    function submitBannerForm(event) {
        event.preventDefault();

        // Reset error messages
        document.getElementById('title_error').classList.add('hidden');
        document.getElementById('image_url_error').classList.add('hidden');

        const title = document.getElementById('title').value.trim();
        const imageUrl = document.getElementById('image_url').value.trim();

        // Client-side validation
        if (!title) {
            document.getElementById('title_error').textContent = 'Tiêu đề banner không được để trống!';
            document.getElementById('title_error').classList.remove('hidden');
            return;
        }
        const urlPattern = /^(https?:\/\/[^\s$.?#].[^\s]*)$/i;
        if (!imageUrl || !urlPattern.test(imageUrl)) {
            document.getElementById('image_url_error').textContent = 'URL hình ảnh không hợp lệ!';
            document.getElementById('image_url_error').classList.remove('hidden');
            return;
        }

        // Show loading spinner
        const submitBtn = document.getElementById('submitBtn');
        const submitText = document.getElementById('submitText');
        const submitSpinner = document.getElementById('submitSpinner');
        submitBtn.disabled = true;
        submitText.textContent = 'Đang lưu...';
        submitSpinner.classList.remove('hidden');

        const formData = new FormData(document.getElementById('bannerForm'));
        fetch('/WEBQUANAOTREEM/admin/modules/qlgd/banners.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            submitBtn.disabled = false;
            submitText.textContent = 'Lưu Banner';
            submitSpinner.classList.add('hidden');

            if (data.success) {
                showToast(data.message, "success");
            } else {
                showToast(data.message, "error");
            }
        })
        .catch(error => {
            submitBtn.disabled = false;
            submitText.textContent = 'Lưu Banner';
            submitSpinner.classList.add('hidden');
            showToast("Lỗi khi lưu banner: " + error.message, "error");
        });
    }
</script>