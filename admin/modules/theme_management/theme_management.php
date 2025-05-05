<?php
session_start();
include('../../config/database.php');

// Lấy danh sách giao diện từ database
$query = "SELECT * FROM themes ORDER BY id ASC";
$result = mysqli_query($conn, $query);

$themes = [];
while ($row = mysqli_fetch_assoc($result)) {
    $themes[] = $row;
}
?>


<table class="min-w-full table-auto border-collapse border border-gray-300">
    <thead>
        <tr>
            <th class="p-3 text-left border-b">#</th>
            <th class="p-3 text-left border-b">Tên giao diện</th>
            <th class="p-3 text-left border-b">Ảnh minh họa</th>
            <th class="p-3 text-left border-b">Trạng thái</th>
            <th class="p-3 text-left border-b">Tùy chọn</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($themes as $index => $theme): ?>
            <tr>
                <td class="p-3 border-b"><?php echo $index + 1; ?></td>
                <td class="p-3 border-b"><?php echo htmlspecialchars($theme['name']); ?></td>
                <td class="p-3 border-b">
                    <img src="<?php echo htmlspecialchars($theme['thumbnail']); ?>" class="w-20 h-14 rounded shadow">
                </td>
                <td class="p-3 border-b">
                    <?php echo ($theme['status'] == 1) ? 'Đang sử dụng' : 'Chưa sử dụng'; ?>
                </td>
                <td class="p-3 border-b">
                    <?php if ($theme['status'] != 1): ?>
                        <a href="activate_theme.php?id=<?php echo $theme['id']; ?>" class="text-green-500 hover:underline">Kích hoạt</a>
                    <?php else: ?>
                        <span class="text-gray-400">Đang sử dụng</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
