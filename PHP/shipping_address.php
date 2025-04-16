<?php
// Giả sử $user_id là ID của người dùng hiện tại
$user_id = 1; // Lấy từ session hoặc database

// Kết nối database (giả sử đã có file kết nối db)
include 'config.php';

// Kiểm tra xem user có địa chỉ chưa
$query = "SELECT * FROM addresses WHERE user_id = $user_id";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    // Nếu có địa chỉ, hiển thị danh sách
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<p>{$row['name']} - {$row['phone']}</p>";
        echo "<p>{$row['address']}</p>";
    }
    echo '<button class="text-pink-500" onclick="showAddressForm()">Thêm địa chỉ mới</button>';
} else {
    // Nếu chưa có địa chỉ, hiển thị giao diện giống ảnh
    ?>
    <div class="border p-4 rounded-md shadow-md">
        <h2 class="font-bold flex items-center">
            <span class="mr-2">🛍️</span> Đăng nhập/ Đăng ký tài khoản
        </h2>
        <p class="text-gray-600 text-sm mt-2">
            Đăng nhập/ Đăng ký để nhận ưu đãi cho đơn hàng đầu tiên & chiết khấu các hạng thẻ lên tới 20%
        </p>
        <button class="bg-black text-white px-4 py-2 rounded-md mt-3">Đăng nhập/ Đăng ký</button>
    </div>
    <?php
}
?>
