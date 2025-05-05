<?php
session_start();
include('../../config/database.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);

    if (empty($title) || empty($content)) {
        echo json_encode(['success' => false, 'message' => 'Tiêu đề và nội dung không được để trống!']);
        exit;
    }

    $insert_query = "INSERT INTO articles (title, content) VALUES (?, ?)";
    $stmt = $conn->prepare($insert_query);
    if ($stmt === false) {
        echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống khi chuẩn bị truy vấn.']);
        exit;
    }
    $stmt->bind_param("ss", $title, $content);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Thêm bài viết thành công!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi khi thêm bài viết: ' . $stmt->error]);
    }
    $stmt->close();
    $conn->close();
    exit;
}
?>

<div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-3xl">
    <h3 class="text-2xl font-bold text-gray-700 mb-6 text-center">Thêm Bài Viết</h3>
    <form id="addArticleForm" onsubmit="submitAddArticleForm(event)" class="space-y-6">
        <div>
            <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Tiêu Đề</label>
            <input type="text" id="title" name="title" required class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-gray-50" placeholder="Nhập tiêu đề bài viết">
            <p id="title_error" class="text-red-500 text-sm mt-1 hidden"></p>
        </div>
        <div>
            <label for="content" class="block text-sm font-medium text-gray-700 mb-2">Nội Dung</label>
            <textarea id="content" name="content" rows="5" required class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-gray-50" placeholder="Nhập nội dung bài viết"></textarea>
            <p id="content_error" class="text-red-500 text-sm mt-1 hidden"></p>
        </div>
        <div class="flex justify-between items-center">
            <a href="javascript:void(0)" onclick="loadContent('baiviet', '/WEBQUANAOTREEM/admin/modules/qlgd/baiviet.php')" class="text-blue-600 hover:underline text-lg font-medium">
                ← Quay lại
            </a>
            <button type="submit" id="submitBtn" class="px-6 py-3 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 flex items-center">
                <span id="submitText">Thêm</span>
                <i id="submitSpinner" class="fas fa-spinner fa-spin ml-2 hidden"></i>
            </button>
        </div>
    </form>
</div>

<script>
    function submitAddArticleForm(event) {
        event.preventDefault();

        document.getElementById('title_error').classList.add('hidden');
        document.getElementById('content_error').classList.add('hidden');

        const title = document.getElementById('title').value.trim();
        const content = document.getElementById('content').value.trim();

        if (!title) {
            document.getElementById('title_error').textContent = 'Tiêu đề không được để trống!';
            document.getElementById('title_error').classList.remove('hidden');
            return;
        }
        if (!content) {
            document.getElementById('content_error').textContent = 'Nội dung không được để trống!';
            document.getElementById('content_error').classList.remove('hidden');
            return;
        }

        const submitBtn = document.getElementById('submitBtn');
        const submitText = document.getElementById('submitText');
        const submitSpinner = document.getElementById('submitSpinner');
        submitBtn.disabled = true;
        submitText.textContent = 'Đang thêm...';
        submitSpinner.classList.remove('hidden');

        const formData = new FormData(document.getElementById('addArticleForm'));
        fetch('/WEBQUANAOTREEM/admin/modules/qlgd/add_article.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            submitBtn.disabled = false;
            submitText.textContent = 'Thêm';
            submitSpinner.classList.add('hidden');

            if (data.success) {
                showToast(data.message, 'success');
                loadContent('baiviet', '/WEBQUANAOTREEM/admin/modules/qlgd/baiviet.php');
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(error => {
            submitBtn.disabled = false;
            submitText.textContent = 'Thêm';
            submitSpinner.classList.add('hidden');
            showToast('Lỗi khi thêm bài viết: ' + error.message, 'error');
        });
    }
</script>