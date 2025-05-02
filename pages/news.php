<?php
session_start(); 
include('../config/database.php');

// Kiểm tra các biến cấu hình
if (!isset($servername) || !isset($username) || !isset($password) || !isset($dbname)) {
    die("Lỗi: Các thông số kết nối cơ sở dữ liệu chưa được định nghĩa trong config/database.php");
}

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("SET NAMES utf8");
} catch(PDOException $e) {
    echo "Kết nối thất bại: " . $e->getMessage();
    exit();
}

// Thiết lập phân trang
$itemsPerPage = 3;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($currentPage - 1) * $itemsPerPage;

// Lấy tổng số tin tức
$totalQuery = "SELECT COUNT(*) FROM news";
$totalStmt = $conn->prepare($totalQuery);
$totalStmt->execute();
$totalItems = $totalStmt->fetchColumn();
$totalPages = ceil($totalItems / $itemsPerPage);

// Lấy dữ liệu tin tức theo trang
$query = "SELECT * FROM news ORDER BY published_at DESC LIMIT :offset, :itemsPerPage";
$stmt = $conn->prepare($query);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':itemsPerPage', $itemsPerPage, PDO::PARAM_INT);
$stmt->execute();
$newsItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Tin tức & Khuyến mãi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://unpkg.com/feather-icons@4.28.0/dist/feather-sprite.svg" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <style>
        /* Animated background */
        .animated-bg {
            background: linear-gradient(135deg, #fce7f3, #e0f2fe, #f3e8ff);
            background-size: 400%;
            animation: gradientBG 15s ease infinite;
            min-height: 100vh;
        }
        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Glassmorphism effect */
        .glass-container {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.7);
        }

        /* Hover scale effect */
        .hover-scale {
            transition: transform 0.3s ease;
        }
        .hover-scale:hover {
            transform: scale(1.05);
        }

        /* Fade-in animation for footer */
        .fade-in {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.6s ease-out, transform 0.6s ease-out;
        }
        .fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>
<body class="animated-bg text-gray-800 font-['Roboto'] flex flex-col min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-md sticky top-0 z-50 p-4 flex justify-between items-center">
        <div class="flex items-center gap-2">
            <a href="../index.php">
                <img src="../assets/images/logo1.jpg" class="w-[120px] h-auto" loading="lazy" />
            </a>
        </div>
        <nav class="hidden md:flex gap-4" id="main-nav">
            <a href="#" class="text-transparent bg-gradient-to-r from-pink-400 to-blue-400 bg-clip-text font-semibold">Tin tức</a>
            <a href="../size-guide.html" class="text-gray-600 hover:text-pink-400 transition-colors">Chọn size</a>
            <a href="../return-policy.html" class="text-gray-600 hover:text-pink-400 transition-colors">Đổi trả</a>
            <a href="../privacy-policy.html" class="text-gray-600 hover:text-pink-400 transition-colors">Bảo mật</a>
        </nav>
        <!-- Nút hamburger cho mobile -->
        <button id="menu-toggle" class="md:hidden text-gray-600 focus:outline-none">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>
    </header>

    <!-- Tiêu đề -->
    <section class="text-center py-12">
        <h1 class="text-4xl font-bold bg-gradient-to-r from-pink-400 to-blue-400 bg-clip-text text-transparent mb-3">Tin tức & Khuyến mãi</h1>
        <p class="text-gray-600 text-base max-w-2xl mx-auto">Cập nhật thông tin mới nhất từ BABY Store về thời trang trẻ em, mẹo chăm sóc bé và các chương trình ưu đãi hấp dẫn.</p>
    </section>

    <!-- Nội dung tin tức -->
    <main class="max-w-7xl mx-auto px-4 py-12 flex-1 pb-5">
        <div id="news-container" class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
            <?php if (count($newsItems) > 0): ?>
                <?php foreach ($newsItems as $news): ?>
                    <!-- Bài viết -->
                    <article class="glass-container rounded-2xl shadow-lg overflow-hidden relative hover-scale" itemscope itemtype="https://schema.org/Article">
                        <meta itemprop="Publishedat" content="<?php echo htmlspecialchars($news['published_at']); ?>">
                        <meta itemprop="author" content="BABY Store">
                        <div class="relative">
                            <img src="<?php echo htmlspecialchars($news['image']); ?>" alt="<?php echo htmlspecialchars($news['title']); ?>" class="w-full h-56 object-cover transition-transform duration-500" loading="lazy" itemprop="image" />
                            <span class="absolute top-4 left-4 <?php echo htmlspecialchars($news['tag_color']); ?> text-white text-xs font-semibold px-3 py-1 rounded-full"><?php echo htmlspecialchars($news['tag']); ?></span>
                        </div>
                        <div class="p-6">
                            <h2 class="text-xl font-semibold text-gray-800 mb-3 line-clamp-2" itemprop="headline"><?php echo htmlspecialchars($news['title']); ?></h2>
                            <p class="text-sm text-gray-600 mb-4 line-clamp-3" itemprop="description"><?php echo htmlspecialchars($news['description']); ?></p>
                            <a href="#" class="inline-flex items-center text-pink-600 hover:text-pink-800 text-sm font-medium transition-colors">
                                Đọc thêm
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="col-span-3 text-center text-gray-600">Không có tin tức nào để hiển thị.</p>
            <?php endif; ?>
        </div>

        <!-- Phân trang -->
        <?php if ($totalPages > 1): ?>
        <div class="flex justify-center mt-12">
            <nav aria-label="Phân trang">
                <ul class="flex gap-3">
                    <li>
                        <a href="?page=<?php echo max(1, $currentPage - 1); ?>" class="px-4 py-2 bg-white text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-100 hover:scale-105 transition-all duration-200 <?php echo $currentPage == 1 ? 'opacity-50 cursor-not-allowed hover:scale-100' : ''; ?>">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </a>
                    </li>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li>
                        <a href="?page=<?php echo $i; ?>" class="px-4 py-2 <?php echo $i == $currentPage ? 'bg-gradient-to-r from-pink-400 to-blue-400 text-white border-blue-500' : 'bg-white text-gray-700 border-gray-300'; ?> border rounded-lg hover:bg-gray-100 hover:scale-105 transition-all duration-200"><?php echo $i; ?></a>
                    </li>
                    <?php endfor; ?>
                    <li>
                        <a href="?page=<?php echo min($totalPages, $currentPage + 1); ?>" class="px-4 py-2 bg-white text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-100 hover:scale-105 transition-all duration-200 <?php echo $currentPage == $totalPages ? 'opacity-50 cursor-not-allowed hover:scale-100' : ''; ?>">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </main>
    
    <!-- Footer -->
    <?php include('../components/footer.php'); ?>

    <!-- Scripts -->
    <script src="https://unpkg.com/feather-icons@4.28.0/dist/feather.min.js"></script>
    <script>
        // Khởi tạo Feather Icons
        feather.replace();

        // Mở/đóng menu trên mobile
        const menuToggle = document.getElementById('menu-toggle');
        const mainNav = document.getElementById('main-nav');

        menuToggle.addEventListener('click', () => {
            mainNav.classList.toggle('hidden');
            mainNav.classList.toggle('flex');
            mainNav.classList.toggle('flex-col');
            mainNav.classList.toggle('absolute');
            mainNav.classList.toggle('top-16');
            mainNav.classList.toggle('left-0');
            mainNav.classList.toggle('w-full');
            mainNav.classList.toggle('bg-white');
            mainNav.classList.toggle('p-4');
            mainNav.classList.toggle('shadow-md');
        });

        // Hiệu ứng fade-in khi cuộn
        const elements = document.querySelectorAll('.fade-in');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, { threshold: 0.1 });
        elements.forEach(element => observer.observe(element));
    </script>
</body>
</html>
