<?php
session_start();
include('../../config/database.php');

// Đường dẫn để lưu trữ hình nền
$upload_dir = __DIR__ . '/../../uploads/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Xử lý cập nhật cài đặt giao diện
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $background_color = trim($_POST['background_color']);
    $text_color = trim($_POST['text_color']);
    $font_family = trim($_POST['font_family']);
    $font_size = intval($_POST['font_size']);
    $background_image = isset($_POST['background_image']) ? trim($_POST['background_image']) : '';
    $use_uploaded_image = isset($_POST['image_source']) && $_POST['image_source'] === 'upload';

    // Server-side validation
    if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $background_color)) {
        echo json_encode(['success' => false, 'message' => 'Màu nền không hợp lệ! Phải là mã màu hex (e.g., #FFFFFF).']);
        exit;
    }
    if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $text_color)) {
        echo json_encode(['success' => false, 'message' => 'Màu chữ không hợp lệ! Phải là mã màu hex (e.g., #000000).']);
        exit;
    }
    if (empty($font_family)) {
        echo json_encode(['success' => false, 'message' => 'Font chữ không được để trống!']);
        exit;
    }
    if ($font_size < 10 || $font_size > 50) {
        echo json_encode(['success' => false, 'message' => 'Kích thước chữ phải từ 10 đến 50!']);
        exit;
    }

    $final_background_image = $background_image;
    if ($use_uploaded_image && isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['image_file'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5MB

        // Validate file type and size
        if (!in_array($file['type'], $allowed_types)) {
            echo json_encode(['success' => false, 'message' => 'Định dạng hình không hợp lệ! Chỉ chấp nhận JPEG, PNG, GIF.']);
            exit;
        }
        if ($file['size'] > $max_size) {
            echo json_encode(['success' => false, 'message' => 'Kích thước hình vượt quá 5MB!']);
            exit;
        }

        // Generate a unique filename
        $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $new_filename = 'background_' . time() . '.' . $file_ext;
        $upload_path = $upload_dir . $new_filename;

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
            $final_background_image = '/WEBQUANAOTREEM/uploads/' . $new_filename;

            // Delete old image if it exists and is a file
            $query = "SELECT background_image FROM site_config WHERE id = ?";
            $stmt = $conn->prepare($query);
            if ($stmt === false) {
                echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống khi chuẩn bị truy vấn.']);
                exit;
            }
            $id = 1;
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $old_settings = $result->fetch_assoc();
            $stmt->close();

            if ($old_settings['background_image'] && file_exists(__DIR__ . '/../../' . ltrim($old_settings['background_image'], '/WEBQUANAOTREEM'))) {
                unlink(__DIR__ . '/../../' . ltrim($old_settings['background_image'], '/WEBQUANAOTREEM'));
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi khi tải lên hình nền!']);
            exit;
        }
    } elseif (!$use_uploaded_image && empty($background_image)) {
        echo json_encode(['success' => false, 'message' => 'URL hình nền không được để trống nếu không tải lên file!']);
        exit;
    }

    // Validate image URL if provided
    if (!$use_uploaded_image && $background_image && !filter_var($background_image, FILTER_VALIDATE_URL)) {
        echo json_encode(['success' => false, 'message' => 'URL hình nền không hợp lệ!']);
        exit;
    }

    // Cập nhật cài đặt
    $update_query = "UPDATE site_config SET background_color = ?, text_color = ?, font_family = ?, font_size = ?, background_image = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    if ($stmt === false) {
        error_log("Prepare failed: " . $conn->error);
        echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống khi chuẩn bị truy vấn.']);
        exit;
    }

    $id = 1;
    $stmt->bind_param("sssisi", $background_color, $text_color, $font_family, $font_size, $final_background_image, $id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Cập nhật giao diện thành công!', 'background_image' => $final_background_image]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi khi cập nhật: ' . $stmt->error]);
    }
    $stmt->close();
    $conn->close();
    exit;
}

// Lấy dữ liệu hiện tại
$query = "SELECT background_color, text_color, font_family, font_size, background_image_url FROM site_config WHERE id = ?";
$stmt = $conn->prepare($query);
if ($stmt === false) {
    error_log("Prepare failed: " . $conn->error);
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $id);
$id = 1;
$stmt->execute();
$result = $stmt->get_result();
$settings = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>

<div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-3xl">
    <h3 class="text-2xl font-bold text-gray-700 mb-6 text-center">Cài Đặt Giao Diện</h3>
    <form id="manhinhForm" onsubmit="submitManhinhForm(event)" enctype="multipart/form-data" class="space-y-6">
        <div>
            <label for="background_color" class="block text-sm font-medium text-gray-700 mb-2">Màu Nền (Hex)</label>
            <input type="text" id="background_color" name="background_color" value="<?php echo htmlspecialchars($settings['background_color'] ?? '#FFFFFF'); ?>" required class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-gray-50" placeholder="e.g., #FFFFFF">
            <p id="background_color_error" class="text-red-500 text-sm mt-1 hidden"></p>
        </div>
        <div>
            <label for="text_color" class="block text-sm font-medium text-gray-700 mb-2">Màu Chữ (Hex)</label>
            <input type="text" id="text_color" name="text_color" value="<?php echo htmlspecialchars($settings['text_color'] ?? '#000000'); ?>" required class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-gray-50" placeholder="e.g., #000000">
            <p id="text_color_error" class="text-red-500 text-sm mt-1 hidden"></p>
        </div>
        <div>
            <label for="font_family" class="block text-sm font-medium text-gray-700 mb-2">Font Chữ</label>
            <input type="text" id="font_family" name="font_family" value="<?php echo htmlspecialchars($settings['font_family'] ?? 'Arial'); ?>" required class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-gray-50" placeholder="e.g., Arial">
            <p id="font_family_error" class="text-red-500 text-sm mt-1 hidden"></p>
        </div>
        <div>
            <label for="font_size" class="block text-sm font-medium text-gray-700 mb-2">Kích Thước Chữ (px)</label>
            <input type="number" id="font_size" name="font_size" value="<?php echo htmlspecialchars($settings['font_size'] ?? 16); ?>" required class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-gray-50" min="10" max="50">
            <p id="font_size_error" class="text-red-500 text-sm mt-1 hidden"></p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Nguồn Hình Nền</label>
            <div class="flex items-center space-x-4">
                <label class="flex items-center">
                    <input type="radio" name="image_source" value="url" class="mr-2 text-blue-500 focus:ring-blue-500" checked onchange="toggleImageInput()">
                    <span class="text-gray-700">URL</span>
                </label>
                <label class="flex items-center">
                    <input type="radio" name="image_source" value="upload" class="mr-2 text-blue-500 focus:ring-blue-500" onchange="toggleImageInput()">
                    <span class="text-gray-700">Tải lên</span>
                </label>
            </div>
        </div>
        <div id="image_url_input">
            <label for="background_image" class="block text-sm font-medium text-gray-700 mb-2">URL Hình Nền</label>
            <input type="url" id="background_image" name="background_image" value="<?php echo htmlspecialchars($settings['background_image'] ?? ''); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-gray-50" placeholder="Nhập URL hình nền">
            <p id="background_image_error" class="text-red-500 text-sm mt-1 hidden"></p>
        </div>
        <div id="image_file_input" class="hidden">
            <label for="image_file" class="block text-sm font-medium text-gray-700 mb-2">Tải Lên Hình Nền</label>
            <input type="file" id="image_file" name="image_file" accept="image/jpeg,image/png,image/gif" class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-gray-50">
            <p id="image_file_error" class="text-red-500 text-sm mt-1 hidden"></p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Xem Trước Hình Nền</label>
            <div class="flex justify-center">
                <img id="image_preview" src="<?php echo htmlspecialchars($settings['background_image'] ?? ''); ?>" alt="Background Preview" class="max-w-full h-32 object-contain rounded-lg border border-gray-200 shadow-sm <?php echo empty($settings['background_image']) ? 'hidden' : ''; ?>">
            </div>
        </div>
        <div class="flex justify-between items-center">
            <a href="javascript:void(0)" onclick="loadContent('manhinh', '/WEBQUANAOTREEM/admin/modules/qlgd/manhinh.php')" class="text-blue-600 hover:underline text-lg font-medium">
                ← Quay lại
            </a>
            <button type="submit" id="submitBtn" class="px-6 py-3 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 flex items-center">
                <span id="submitText">Lưu</span>
                <i id="submitSpinner" class="fas fa-spinner fa-spin ml-2 hidden"></i>
            </button>
        </div>
    </form>
</div>

<script>
    function toggleImageInput() {
        const imageUrlInput = document.getElementById('image_url_input');
        const imageFileInput = document.getElementById('image_file_input');
        const imageSource = document.querySelector('input[name="image_source"]:checked').value;

        if (imageSource === 'url') {
            imageUrlInput.classList.remove('hidden');
            imageFileInput.classList.add('hidden');
            document.getElementById('image_file').value = ''; // Clear file input
        } else {
            imageUrlInput.classList.add('hidden');
            imageFileInput.classList.remove('hidden');
            document.getElementById('background_image').value = ''; // Clear URL input
        }
    }

    function submitManhinhForm(event) {
        event.preventDefault();

        // Reset error messages
        document.getElementById('background_color_error').classList.add('hidden');
        document.getElementById('text_color_error').classList.add('hidden');
        document.getElementById('font_family_error').classList.add('hidden');
        document.getElementById('font_size_error').classList.add('hidden');
        document.getElementById('background_image_error').classList.add('hidden');
        document.getElementById('image_file_error').classList.add('hidden');

        const backgroundColor = document.getElementById('background_color').value.trim();
        const textColor = document.getElementById('text_color').value.trim();
        const fontFamily = document.getElementById('font_family').value.trim();
        const fontSize = parseInt(document.getElementById('font_size').value);
        const backgroundImage = document.getElementById('background_image').value.trim();
        const imageFile = document.getElementById('image_file').files[0];
        const imageSource = document.querySelector('input[name="image_source"]:checked').value;

        // Client-side validation
        const hexPattern = /^#[0-9A-Fa-f]{6}$/;
        if (!hexPattern.test(backgroundColor)) {
            document.getElementById('background_color_error').textContent = 'Màu nền không hợp lệ! Phải là mã màu hex (e.g., #FFFFFF).';
            document.getElementById('background_color_error').classList.remove('hidden');
            return;
        }
        if (!hexPattern.test(textColor)) {
            document.getElementById('text_color_error').textContent = 'Màu chữ không hợp lệ! Phải là mã màu hex (e.g., #000000).';
            document.getElementById('text_color_error').classList.remove('hidden');
            return;
        }
        if (!fontFamily) {
            document.getElementById('font_family_error').textContent = 'Font chữ không được để trống!';
            document.getElementById('font_family_error').classList.remove('hidden');
            return;
        }
        if (fontSize < 10 || fontSize > 50) {
            document.getElementById('font_size_error').textContent = 'Kích thước chữ phải từ 10 đến 50!';
            document.getElementById('font_size_error').classList.remove('hidden');
            return;
        }
        if (imageSource === 'url') {
            if (!backgroundImage) {
                document.getElementById('background_image_error').textContent = 'URL hình nền không được để trống!';
                document.getElementById('background_image_error').classList.remove('hidden');
                return;
            }
            const urlPattern = /^(https?:\/\/[^\s$.?#].[^\s]*)$/i;
            if (!urlPattern.test(backgroundImage)) {
                document.getElementById('background_image_error').textContent = 'URL hình nền không hợp lệ!';
                document.getElementById('background_image_error').classList.remove('hidden');
                return;
            }
        } else if (imageSource === 'upload' && !imageFile) {
            document.getElementById('image_file_error').textContent = 'Vui lòng chọn một file hình nền!';
            document.getElementById('image_file_error').classList.remove('hidden');
            return;
        }

        // Show loading spinner
        const submitBtn = document.getElementById('submitBtn');
        const submitText = document.getElementById('submitText');
        const submitSpinner = document.getElementById('submitSpinner');
        submitBtn.disabled = true;
        submitText.textContent = 'Đang lưu...';
        submitSpinner.classList.remove('hidden');

        const formData = new FormData(document.getElementById('manhinhForm'));
        fetch('/WEBQUANAOTREEM/admin/modules/qlgd/manhinh.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            submitBtn.disabled = false;
            submitText.textContent = 'Lưu';
            submitSpinner.classList.add('hidden');

            if (data.success) {
                showToast(data.message, "success");
                if (data.background_image) {
                    const imagePreview = document.getElementById('image_preview');
                    imagePreview.src = data.background_image;
                    imagePreview.classList.remove('hidden');
                }
            } else {
                showToast(data.message, "error");
            }
        })
        .catch(error => {
            submitBtn.disabled = false;
            submitText.textContent = 'Lưu';
            submitSpinner.classList.add('hidden');
            showToast("Lỗi khi lưu cài đặt: " + error.message, "error");
        });
    }

    document.getElementById('image_file').addEventListener('change', function(event) {
        const file = event.target.files[0];
        const imagePreview = document.getElementById('image_preview');
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.src = e.target.result;
                imagePreview.classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        }
    });

    document.getElementById('background_image').addEventListener('input', function(event) {
        const backgroundImage = event.target.value.trim();
        const imagePreview = document.getElementById('image_preview');
        if (backgroundImage) {
            imagePreview.src = backgroundImage;
            imagePreview.classList.remove('hidden');
        } else {
            imagePreview.src = '';
            imagePreview.classList.add('hidden');
        }
    });
</script>