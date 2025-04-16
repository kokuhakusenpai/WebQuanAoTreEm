<?php
include('../../config/database.php');

// Truy vấn danh sách bài viết từ bảng `articles` (hoặc tên bảng phù hợp với hệ thống của bạn)
$query = "SELECT * FROM articles";
$result = mysqli_query($conn, $query);

// Xóa bài viết nếu nhận được yêu cầu xóa
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $delete_query = "DELETE FROM articles WHERE id = $delete_id";
    mysqli_query($conn, $delete_query);
    header("Location: baiviet.php"); // Tải lại trang sau khi xóa
    exit;
}
?>

<h3>Quản Lý Bài Viết</h3>

<!-- Hiển thị danh sách bài viết -->
<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr>
            <th>ID</th>
            <th>Tiêu đề</th>
            <th>Nội dung</th>
            <th>Thao tác</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>
                        <td>{$row['id']}</td>
                        <td>{$row['title']}</td>
                        <td>" . substr($row['content'], 0, 50) . "...</td>
                        <td>
                            <a href='edit_article.php?id={$row['id']}'>Sửa</a> |
                            <a href='?delete_id={$row['id']}' onclick=\"return confirm('Bạn có chắc chắn muốn xóa bài viết này?');\">Xóa</a>
                        </td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='4'>Không có bài viết nào.</td></tr>";
        }
        ?>
    </tbody>
</table>

<br>
<!-- Nút thêm bài viết -->
<li><a href="#" onclick="loadContent('modules/qlgd/add_article.php')">+ Thêm bài viết</a></li>
