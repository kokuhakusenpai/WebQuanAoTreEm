<?php
session_start();
include('../config/database.php');

// Kiểm tra xem người dùng đã đăng nhập chưa
$isLoggedIn = isset($_SESSION['username']);
$username = $isLoggedIn ? $_SESSION['username'] : '';
$avatar = 'assets/img/default-avatar.png'; // Ảnh mặc định

if ($isLoggedIn) {
    // Lấy thông tin người dùng từ cơ sở dữ liệu bằng prepared statement
    $sql = "SELECT username FROM user WHERE username = ?"; 
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $username = $user['username'];
    } else {
        $username = 'Tên không xác định';
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cửa Hàng</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="styles.css" />
</head>
<body class="min-h-screen bg-gray-100">
    <!-- Header -->
    <header class="bg-blue-600 text-white shadow-md">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <!-- Logo và tên cửa hàng -->
                <div class="flex items-center">
                    <a href="index.php" class="flex items-center">
                        <i class="fas fa-store text-2xl mr-2"></i>
                        <span class="text-xl font-bold">SUSU Hub</span>
                    </a>
                </div>

                <!-- Thanh tìm kiếm -->
                <div class="relative w-1/3 hidden md:block">
                    <input type="text" id="header-search" placeholder="Tìm kiếm sản phẩm..." 
                        class="w-full p-2 pl-4 pr-10 border border-gray-300 rounded-full focus:outline-none focus:border-blue-300 text-gray-800">
                    <button class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500">
                        <i class="fas fa-search"></i>
                    </button>
                </div>

                <!-- Menu người dùng -->
                <div class="flex items-center">
                    <?php if ($isLoggedIn): ?>
                    <!-- Đã đăng nhập -->
                    <div class="relative group">
                        <button class="flex items-center space-x-2 focus:outline-none" id="userMenuButton">
                            <img src="<?php echo htmlspecialchars($avatar); ?>" alt="Avatar" class="w-8 h-8 rounded-full border-2 border-white">
                            <span class="hidden md:inline"><?php echo htmlspecialchars($username); ?></span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div id="userDropdown" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-10 hidden">
                            <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                                <a href="admin/index.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-user-shield mr-2"></i>Quản trị
                                </a>
                            <?php endif; ?>
                            <a href="profile.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-user mr-2"></i>Hồ sơ
                            </a>
                            <div class="border-t border-gray-100"></div>
                            <a href="logout.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-sign-out-alt mr-2"></i>Đăng xuất
                            </a>
                        </div>
                    </div>
                    <?php else: ?>
                    <!-- Chưa đăng nhập -->
                    <div class="flex items-center">
                        <a href="login.php" class="flex items-center px-4 py-2 hover:text-blue-200">
                            <i class="fas fa-sign-in-alt mr-2"></i>
                            <span>Đăng nhập</span>
                        </a>
                        <span class="mx-1">|</span>
                        <a href="register.php" class="flex items-center px-4 py-2 hover:text-blue-200">
                            <i class="fas fa-user-plus mr-2"></i>
                            <span>Đăng ký</span>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Navbar -->
    <nav class="bg-gray-800 text-white shadow-md">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between">
                <div class="hidden md:flex space-x-1">
                    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                    <?php else: ?>
                        <a href="#" id="nav-users" class="py-4 px-3 hover:bg-blue-700 transition duration-300 flex items-center nav-link">
                            <i class="fas fa-users mr-2"></i> Quản lý người dùng
                        </a>
                        <a href="#" id="nav-products" class="py-4 px-3 hover:bg-blue-700 transition duration-300 flex items-center nav-link">
                            <i class="fas fa-box mr-2"></i> Quản lý sản phẩm
                        </a>
                        <a href="#" id="nav-orders" class="py-4 px-3 hover:bg-blue-700 transition duration-300 flex items-center nav-link">
                            <i class="fas fa-shopping-cart mr-2"></i> Quản lý đơn hàng
                        </a>
                        <a href="#" id="nav-interface" class="py-4 px-3 hover:bg-blue-700 transition duration-300 flex items-center nav-link">
                            <i class="fas fa-paint-brush mr-2"></i> Quản lý giao diện
                        </a>
                        <a href="#" id="nav-statistics" class="py-4 px-3 hover:bg-blue-700 transition duration-300 flex items-center nav-link">
                            <i class="fas fa-chart-line mr-2"></i> Quản lý thao tác
                        </a>
                    <?php endif; ?>
                </div>
                
                <!-- Mobile menu button -->
                <div class="md:hidden flex items-center">
                    <button id="mobile-menu-button" class="text-white focus:outline-none">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
            
            <!-- Mobile menu -->
            <div id="mobile-menu" class="md:hidden hidden pb-2">
                <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                <?php else: ?>
                    <a href="#" id="mobile-nav-home" class="block py-2 px-4 hover:bg-blue-700 nav-link">
                        <i class="fas fa-home mr-2"></i> Trang chủ
                    </a>
                    <a href="#" id="mobile-nav-users" class="block py-2 px-4 hover:bg-blue-700 nav-link">
                        <i class="fas fa-users mr-2"></i> Quản lý người dùng
                    </a>
                    <a href="#" id="mobile-nav-products" class="block py-2 px-4 hover:bg-blue-700 nav-link">
                        <i class="fas fa-box mr-2"></i> Quản lý sản phẩm
                    </a>
                    <a href="#" id="mobile-nav-orders" class="block py-2 px-4 hover:bg-blue-700 nav-link">
                        <i class="fas fa-shopping-cart mr-2"></i> Quản lý đơn hàng
                    </a>
                    <a href="#" id="mobile-nav-interface" class="block py-2 px-4 hover:bg-blue-700 nav-link">
                        <i class="fas fa-paint-brush mr-2"></i> Quản lý giao diện
                    </a>
                    <a href="#" id="mobile-nav-statistics" class="block py-2 px-4 hover:bg-blue-700 nav-link">
                        <i class="fas fa-chart-line mr-2"></i> Quản lý thao tác
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white p-6 rounded-lg shadow-sm">
            <h2 id="content-title" class="text-2xl font-bold text-gray-800 mb-3">Chào Mừng Bạn Đến Với Cửa Hàng</h2>
            <p id="content-subtitle" class="text-gray-600">Vui lòng khám phá các sản phẩm của chúng tôi.</p>
            
            <!-- Phần nội dung chính của trang sẽ hiển thị ở đây -->
            <div id="main-content" class="mt-8">
                <!-- Nội dung trang chủ -->
                <div class="text-center py-6">
                    <i class="fas fa-store text-6xl text-blue-500 mb-4"></i>
                    <h3 class="text-xl font-semibold mb-2">SUSU Hub - Hệ thống quản lý bán hàng</h3>
                    <p class="text-gray-600 mb-4">Chọn một mục từ thanh điều hướng để bắt đầu.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Thêm script để xử lý AJAX -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        // Toggle user dropdown menu
        document.getElementById('userMenuButton')?.addEventListener('click', function() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('hidden');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('userDropdown');
            const button = document.getElementById('userMenuButton');
            
            if (dropdown && button && !button.contains(event.target) && !dropdown.contains(event.target)) {
                dropdown.classList.add('hidden');
            }
        });

        // Toggle mobile menu
        document.getElementById('mobile-menu-button')?.addEventListener('click', function() {
            const mobileMenu = document.getElementById('mobile-menu');
            mobileMenu.classList.toggle('hidden');
        });

        // Tìm kiếm
        document.getElementById('header-search')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const searchTerm = this.value.trim();
                if (searchTerm) {
                    window.location.href = `search.php?q=${encodeURIComponent(searchTerm)}`;
                }
            }
        });

        // Xử lý tải nội dung qua AJAX khi click vào các mục trong navbar
        document.addEventListener('DOMContentLoaded', function() {
            // Tạo đối tượng mapping giữa ID và URL
            const navMapping = {
                'nav-home': 'home_content.php',
                'nav-users': 'modules/user_management/users.php',
                'nav-products': 'modules/product_management/product.php',
                'nav-orders': 'modules/order_management/orders.php',
                'nav-interface': 'modules/theme_management/theme_management.php',
                'nav-statistics': 'modules/log_management/log_management.php',
                
                // Mobile versions
                'mobile-nav-home': 'home_content.php',
                'mobile-nav-users': 'modules/qladmin/users.php',
                'mobile-nav-products': 'modules/qlsp/product.php',
                'mobile-nav-orders': 'modules/qldh/orders.php',
                'mobile-nav-interface': 'modules/qlgd/interface.php',
                'mobile-nav-statistics': 'modules/qlttnd/Statistics_Logs.php'
            };
            
            // Tạo đối tượng mapping cho tiêu đề và phụ đề
            const titleMapping = {
                'nav-home': {title: 'Trang Chủ', subtitle: 'Chào mừng bạn đến với SUSU Hub'},
                'nav-users': {title: 'Quản Lý Người Dùng', subtitle: 'Quản lý thông tin và quyền của người dùng'},
                'nav-products': {title: 'Quản Lý Sản Phẩm', subtitle: 'Quản lý danh sách sản phẩm'},
                'nav-orders': {title: 'Quản Lý Đơn Hàng', subtitle: 'Quản lý đơn hàng và trạng thái'},
                'nav-interface': {title: 'Quản Lý Giao Diện', subtitle: 'Tùy chỉnh giao diện hệ thống'},
                'nav-statistics': {title: 'Quản Lý Thao Tác', subtitle: 'Theo dõi và phân tích thao tác người dùng'},
                
                // Mobile versions
                'mobile-nav-home': {title: 'Trang Chủ', subtitle: 'Chào mừng bạn đến với SUSU Hub'},
                'mobile-nav-users': {title: 'Quản Lý Người Dùng', subtitle: 'Quản lý thông tin và quyền của người dùng'},
                'mobile-nav-products': {title: 'Quản Lý Sản Phẩm', subtitle: 'Quản lý danh sách sản phẩm'},
                'mobile-nav-orders': {title: 'Quản Lý Đơn Hàng', subtitle: 'Quản lý đơn hàng và trạng thái'},
                'mobile-nav-interface': {title: 'Quản Lý Giao Diện', subtitle: 'Tùy chỉnh giao diện hệ thống'},
                'mobile-nav-statistics': {title: 'Quản Lý Thao Tác', subtitle: 'Theo dõi và phân tích thao tác người dùng'}
            };

            // Thêm sự kiện click cho tất cả các liên kết trong navbar
            document.querySelectorAll('.nav-link').forEach(function(link) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Lấy ID của liên kết được nhấn
                    const linkId = this.id;
                    
                    // Lấy URL tương ứng từ đối tượng mapping
                    const url = navMapping[linkId];
                    
                    // Lấy tiêu đề và phụ đề tương ứng
                    const contentInfo = titleMapping[linkId];
                    
                    // Cập nhật tiêu đề và phụ đề
                    document.getElementById('content-title').textContent = contentInfo.title;
                    document.getElementById('content-subtitle').textContent = contentInfo.subtitle;
                    
                    // Hiển thị loading
                    document.getElementById('main-content').innerHTML = '<div class="flex justify-center items-center py-12"><i class="fas fa-spinner fa-spin text-4xl text-blue-500"></i></div>';
                    
                    // Thực hiện request AJAX để tải nội dung
                    fetch(url)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.text();
                        })
                        .then(data => {
                            // Cập nhật nội dung chính
                            document.getElementById('main-content').innerHTML = data;
                            
                            // Đóng menu mobile nếu đang mở
                            document.getElementById('mobile-menu').classList.add('hidden');
                        })
                        .catch(error => {
                            console.error('Error loading content:', error);
                            document.getElementById('main-content').innerHTML = `
                                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                                    <strong class="font-bold">Lỗi!</strong>
                                    <span class="block sm:inline"> Không thể tải nội dung. Vui lòng thử lại sau.</span>
                                </div>
                            `;
                        });
                });
            });
        });
    </script>
</body>
</html>