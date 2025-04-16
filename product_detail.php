<?php
// product_detail.php

// Kết nối cơ sở dữ liệu
$host = 'localhost';
$dbname = 'baby_shop';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
} catch(PDOException $e) {
    die("Kết nối cơ sở dữ liệu thất bại: " . $e->getMessage());
}

// Lấy product_id từ URL
$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;

if ($product_id > 0) {
    $sql = "SELECT * FROM products WHERE product_id = :product_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['product_id' => $product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    $product = false;
}

$sizes = explode(',', $product['size']); // Tách các size bằng dấu phẩy
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
  <title>
   Product Page
  </title>
  <script src="https://cdn.tailwindcss.com">
  </script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
<header>
    <a href="index.php">Trang chủ</a>
    <h2>Chi tiết sản phẩm</h2>
</header>

<?php if($product): ?>
    <body class="bg-white text-gray-800">
  <div class="container mx-auto p-4">
   <div class="flex flex-col md:flex-row">
    <!-- Left Column: Image and Thumbnails -->
    <div class="w-full md:w-1/2">
        <div class="mb-4">
        <img class="w-full" src="<?= $product['image_url'] ?>" alt="<?= htmlspecialchars($product['name']) ?>">
        </div>
        <div class="flex space-x-2">
         <img alt="Children's clothing thumbnail 1" class="w-16 h-16" height="100" src="https://storage.googleapis.com/a1aa/image/Jh4hPe-OjGSiQlEM7G15_MxyKfGOerYkCt8iRFPZsyA.jpg" width="100"/>
         <img alt="Children's clothing thumbnail 2" class="w-16 h-16" height="100" src="https://storage.googleapis.com/a1aa/image/YNr7BsouXig4r4XDU5GKpCbn5AFdU9bqGRN9czU570o.jpg" width="100"/>
         <img alt="Children's clothing thumbnail 3" class="w-16 h-16" height="100" src="https://storage.googleapis.com/a1aa/image/MxKLFkpsF0BApjTqXVnT0-2R7poHGJ1tKgG_AylpBgM.jpg" width="100"/>
         <img alt="Children's clothing thumbnail 4" class="w-16 h-16" height="100" src="https://storage.googleapis.com/a1aa/image/o4mBwZyBuVJU57eF0NQgINC_6CZRRSjKYZ8bn1tuES8.jpg" width="100"/>
         <img alt="Children's clothing thumbnail 5" class="w-16 h-16" height="100" src="https://storage.googleapis.com/a1aa/image/zoe-VcuE-OkxBs8-6JGy9HPOXK2IethN3PLuRD_PzL4.jpg" width="100"/>
         <img alt="Children's clothing thumbnail 6" class="w-16 h-16" height="100" src="https://storage.googleapis.com/a1aa/image/KVZuWRsP2sYMPq59HbEFZp-EscYBa_yHbMkpKtXnRJE.jpg" width="100"/>
        </div>
    </div>
    <!-- Right Column: Product Details -->
    <div class="w-full md:w-1/2 md:pl-8">

    <h1 class="text-2xl font-bold mb-2 text-[#4169E1]">
    <?= htmlspecialchars($product['name']) ?>
    </h1>

     <p class="text-gray-600 mb-4">
      Mã sản phẩm:
      <span class="text-teal-500">
      <?= htmlspecialchars($product['product_id']) ?>
      </span>
      | Tình trạng:
      <span class="text-red-500">
      <?= ($product['status'] === 'available') ? 'Còn hàng' : 'Hết hàng' ?>
      </span>
     </p>
     <div class="text-3xl text-red-500 font-bold mb-4">
     <?= number_format($product['discount_price'] ?? $product['price'], 0, ',', '.') ?>đ
     </div>
     
      <!-- Kích thước-->
     <div class="mb-4">
    <p class="font-bold mb-2 text-[#4169E1]">Kích thước:</p>
    <div class="grid grid-cols-3 gap-2">
        <?php
        // Tách các size thành mảng
        $sizes = explode(',', $product['size']);
        // Duyệt qua từng size và tạo nút
        foreach ($sizes as $size):
        ?>
            <button class="border border-gray-300 py-2 px-4">
                <?= htmlspecialchars(trim($size)) ?>
            </button>
        <?php endforeach; ?>
    </div>
</div>

<!-- Màu sắc-->
<div class="mb-4">
    <p class="font-bold mb-2 text-[#4169E1]">Màu sắc:</p>
    <div class="grid grid-cols-3 gap-2">
        <?php
        // Tách các size thành mảng
        $colors = explode(',',$product['color']);
        // Duyệt qua từng size và tạo nút
        foreach ($colors as $color):
        ?>
            <button class="border border-gray-300 py-2 px-4">
                <?= htmlspecialchars(trim($color)) ?>
            </button>
        <?php endforeach; ?>
    </div>
</div>

     <!-- Số lượng -->
     <div class="mb-4">
        <p class="font-bold mb-2 text-[#4169E1]">Số lượng:</p>
        <div class="flex items-center">
            <button class="border border-gray-300 py-2 px-4" onclick="changeQuantity(-1)">-</button>
            <input id="quantity" class="w-12 text-center border border-gray-300 py-2" type="text" value="1"/>
            <button class="border border-gray-300 py-2 px-4" onclick="changeQuantity(1)">+</button>
        </div>
    </div>

    <!-- Nút thao tác -->
    <div class="flex space-x-4">
        <a href="cart.html" class="bg-red-500 text-white py-2 px-6">THÊM VÀO GIỎ</a>
        <button class="bg-red-500 text-white py-2 px-6">MUA NGAY</button>
    </div>

    <div class="mt-6">
        <h2 class="text-lg font-bold mb-2 text-[#4169E1]">Mô tả sản phẩm</h2>
        <p class="text-gray-600"><?= htmlspecialchars($product['description']) ?></p>
    </div>
</div>
</div>
</div>


<?php else: ?>
    <p>Sản phẩm không tồn tại.</p>
<?php endif; ?>

<script>
let currentRating = 0;

function setRating(stars) {
currentRating = stars;
let starElements = document.querySelectorAll("span.text-yellow-400");
starElements.forEach((star, index) => {
    if (index < stars) {
        star.style.color = "#FFD700"; // Màu vàng
    } else {
        star.style.color = "#D1D5DB"; // Màu xám nhạt
    }
});
}
</script>
</body>
</html>