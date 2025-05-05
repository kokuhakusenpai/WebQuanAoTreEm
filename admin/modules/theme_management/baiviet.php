<?php
session_start();
include('../../config/database.php');

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../login.php');
    exit;
}

// Kiểm tra kết nối cơ sở dữ liệu
if (!$conn) {
    error_log("Database connection failed: " . mysqli_connect_error());
    die("Database connection failed: " . mysqli_connect_error());
}

// Đảm bảo kết nối cơ sở dữ liệu sử dụng UTF-8
if (!$conn->set_charset("utf8mb4")) {
    error_log("Set charset failed: " . $conn->error);
    die("Set charset failed: " . $conn->error);
}

// Xử lý xóa bài viết
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = intval($_POST['delete_id']);
    $delete_query = "DELETE FROM article WHERE id = ?";
    $stmt = $conn->prepare($delete_query);
    if ($stmt === false) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống khi chuẩn bị truy vấn.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['success' => true, 'message' => 'Xóa bài viết thành công!'], JSON_UNESCAPED_UNICODE);
    } else {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['success' => false, 'message' => 'Lỗi khi xóa bài viết: ' . $stmt->error], JSON_UNESCAPED_UNICODE);
    }
    $stmt->close();
    exit;
}

// Truy vấn danh sách bài viết
$query = "SELECT * FROM article";
$result = mysqli_query($conn, $query);

// Kiểm tra lỗi truy vấn
if ($result === false) {
    error_log("Query failed: " . mysqli_error($conn));
    die("Query failed: " . mysqli_error($conn));
}
?>

<div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-3xl">
    <h3 class="text-2xl font-bold text-gray-700 mb-6 text-center">Quản Lý Bài Viết</h3>
    <div class="flex justify-end mb-4">
        <a href="javascript:void(0)" onclick="loadContent('add_article', '/WEBQUANAOTREEM/admin/modules/qlgd/add_article.php')" class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors">
            <i class="fas fa-plus mr-2"></i> Thêm Bài Viết
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200 rounded-lg">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-700 border-b">ID</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-700 border-b">Tiêu Đề</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-700 border-b">Nội Dung</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-700 border-b">Thao Tác</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $content_preview = htmlspecialchars(substr($row['content'], 0, 50)) . (strlen($row['content']) > 50 ? '...' : '');
                        echo "<tr class='border-b'>
                                <td class='px-4 py-2 text-sm text-gray-600'>{$row['id']}</td>
                                <td class='px-4 py-2 text-sm text-gray-600'>" . htmlspecialchars($row['title']) . "</td>
                                <td class='px-4 py-2 text-sm text-gray-600'>{$content_preview}</td>
                                <td class='px-4 py-2 text-sm'>
                                    <a href='javascript:void(0)' onclick=\"loadContent('edit_article', '/WEBQUANAOTREEM/admin/modules/qlgd/edit_article.php?id={$row['id']}')\" class='text-blue-500 hover:underline mr-2'>Sửa</a>
                                    <a href='javascript:void(0)' onclick=\"deleteArticle({$row['id']})\" class='text-red-500 hover:underline'>Xóa</a>
                                </td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='4' class='px-4 py-2 text-center text-gray-600'>Không có bài viết nào.</td></tr>";
                }
                mysqli_free_result($result);
                ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function deleteArticle(id) {
        if (!confirm('Bạn có chắc chắn muốn xóa bài viết này?')) {
            return;
        }

        const formData = new FormData();
        formData.append('delete_id', id);

        fetch('/WEBQUANAOTREEM/admin/modules/qlgd/baiviet.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                loadContent('baiviet', '/WEBQUANAOTREEM/admin/modules/qlgd/baiviet.php'); // Reload the tab
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(error => {
            showToast('Lỗi khi xóa bài viết: ' + error.message, 'error');
        });
    }
</script>

<?php
$conn->close();
?>