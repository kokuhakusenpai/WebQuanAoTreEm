<?php
session_start();
include('../config/database.php');

// Lấy từ khóa tìm kiếm từ URL và xử lý
$search_query = isset($_GET['query']) ? trim($_GET['query']) : '';
$search_query = mysqli_real_escape_string($conn, $search_query);

// Truy vấn sản phẩm theo từ khóa
if ($search_query) {
    // Tìm kiếm trong cột name (và description nếu có)
    $query = "SELECT * FROM products WHERE name LIKE '%$search_query%'";
    // Nếu bảng products có cột description, mở rộng truy vấn
    // $query = "SELECT * FROM products WHERE name LIKE '%$search_query%' OR description LIKE '%$search_query%'";
    $result = mysqli_query($conn, $query);
    $num_results = $result ? mysqli_num_rows($result) : 0;
} else {
    $result = false;
    $num_results = 0;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết Quả Tìm Kiếm</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs" defer></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet" />
</head>
<body class="bg-gray-100 text-gray-800 font-['Roboto']">

    <!-- Header cố định -->
    <header class="fixed top-0 left-0 w-full bg-blue-600 text-white py-4 shadow-md z-50">
        <div class="container mx-auto px-4 flex justify-between items-center">
            <h1 class="text-2xl font-semibold">🌟 Website Bán Hàng</h1>
            <p class="text-sm">Uy tín - Chất lượng - Giá tốt</p>
        </div>
    </header>

    <!-- Navbar cố định -->
    <nav class="fixed top-16 left-0 w-full bg-white shadow-md z-40">
        <?php include('../components/navbar.php'); ?>
    </nav>

    <!-- Nội dung chính -->
    <section class="py-16 mt-32">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent mb-4">
                Kết Quả Tìm Kiếm: "<?php echo htmlspecialchars($search_query); ?>"
            </h2>
            <p class="text-center text-gray-600 mb-8">
                Tìm thấy <span class="font-semibold"><?php echo $num_results; ?></span> sản phẩm
            </p>
            <?php if ($search_query && $result && $num_results > 0) { ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    <?php while ($product = mysqli_fetch_assoc($result)) { ?>
                        <div class="bg-white p-4 rounded-lg shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition duration-300">
                            <img src="../assets/images/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-48 object-cover rounded-lg">
                            <h3 class="mt-4 text-lg font-semibold text-gray-800"><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="text-orange-500 font-bold mt-2"><?php echo number_format($product['price'], 0, ',', '.') . ' VNĐ'; ?></p>
                            <a href="product_details.php?id=<?php echo htmlspecialchars($product['id']); ?>" class="inline-block mt-4 bg-orange-500 hover:bg-orange-600 text-white py-2 px-4 rounded-lg transition duration-300">Xem Chi Tiết</a>
                        </div>
                    <?php } ?>
                </div>
            <?php } else { ?>
                <p class="text-center text-gray-500 text-lg">Không tìm thấy sản phẩm nào với từ khóa: "<?php echo htmlspecialchars($search_query); ?>"</p>
                <div class="mt-8 text-center">
                    <a href="shop.php" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-300">Quay lại cửa hàng</a>
                </div>
            <?php } ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-xl font-semibold mb-4">Website Bán Hàng</h3>
                    <p class="text-gray-300">Cung cấp quần áo, phụ kiện và đồ chơi chất lượng cao cho trẻ em.</p>
                </div>
                <div>
                    <h3 class="text-xl font-semibold mb-4">Liên Hệ</h3>
                    <p class="text-gray-300">Email: support@websitebanhang.com</p>
                    <p class="text-gray-300">Hotline: 0123 456 789</p>
                    <p class="text-gray-300">Địa chỉ: 123 Đường ABC, TP. HCM</p>
                </div>
                <div>
                    <h3 class="text-xl font-semibold mb-4">Theo Dõi Chúng Tôi</h3>
                    <div class="flex gap-4">
                        <a href="#" class="text-gray-300 hover:text-blue-400">Facebook</a>
                        <a href="#" class="text-gray-300 hover:text-blue-400">Instagram</a>
                        <a href="#" class="text-gray-300 hover:text-blue-400">Zalo</a>
                    </div>
                </div>
            </div>
            <div class="mt-8 border-t border-gray-700 pt-4 text-center">
                <p class="text-gray-300">© 2025 Website Bán Hàng. All rights reserved.</p>
            </div>
        </div>
    </footer>

</body>
</html>