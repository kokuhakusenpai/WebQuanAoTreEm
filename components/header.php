<?php
// Định nghĩa base URL (đường dẫn gốc của dự án)
$base_url = '/WEBQUANAOTREEM';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>susu kids - Quần Áo Trẻ Em</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <style>
        /* Glassmorphism effect */
        .glass-container {
            background: rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.5);
        }

        /* Subtle shadow for header */
        .header-shadow {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        /* Smooth hover effect */
        .hover-scale {
            transition: transform 0.2s ease;
        }
        .hover-scale:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body class="font-roboto bg-gray-100 text-gray-800">
    <!-- Header -->
    <header class="fixed top-0 left-0 w-full glass-container py-3 header-shadow z-50">
        <div class="container mx-auto px-4 flex justify-between items-center">
            <!-- Logo and Brand -->
            <div class="flex items-center space-x-3">
                <a href="<?php echo $base_url; ?>/index.php"></a>
                <h1 class="text-xl font-bold text-gray-800">SUSU KIDS</h1>
            </div>

            <!-- Desktop Search Bar -->
            <div class="hidden md:flex items-center flex-grow justify-center mx-6">
                <form method="GET" action="<?php echo $base_url; ?>/pages/search.php" class="flex items-center">
                    <input type="text" name="query" placeholder="Tìm kiếm sản phẩm..." 
                           class="p-2 border border-gray-300/50 rounded-lg bg-white/80 shadow-sm focus:outline-none focus:ring-2 focus:ring-pink-500 w-64" />
                    <button type="submit" class="ml-2 p-2 bg-pink-500 text-white rounded-lg hover:bg-pink-600 transition">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>

            <!-- Desktop Navigation and Actions -->
            <div class="hidden md:flex items-center space-x-6">
                <nav class="flex space-x-4">
                    <a href="<?php echo $base_url; ?>/index.php" class="text-gray-700 hover:text-pink-500 transition duration-200">Trang chủ</a>
                    <a href="<?php echo $base_url; ?>/pages/category.php" class="text-gray-700 hover:text-pink-500 transition duration-200">Sản phẩm</a>
                    <a href="<?php echo $base_url; ?>/pages/about.php" class="text-gray-700 hover:text-pink-500 transition duration-200">Giới thiệu</a>
                </nav>
                <a href="<?php echo $base_url; ?>/pages/cart.php" class="text-gray-700 hover:text-pink-500 transition duration-200 flex items-center space-x-1">
                    <i class="fas fa-shopping-cart"></i>
                    <span>
                        <?php echo (isset($_SESSION['id']) && isset($_SESSION['cart'])) ? count($_SESSION['cart']) : 0; ?>
                    </span>
                </a>
                <!-- User Menu -->
                <div class="relative">
                    <button id="user-menu-toggle" class="text-gray-700 hover:text-pink-500 transition duration-200">
                        <i class="fas fa-user"></i>
                    </button>
                    <div id="user-menu" class="hidden absolute right-0 mt-2 w-48 bg-white shadow-lg rounded-lg z-50">
                        <ul class="py-2">
                            <?php if (isset($_SESSION['id'])): ?>
                                <li><a href="<?php echo $base_url; ?>/pages/profile.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Tài khoản</a></li>
                                <li><a href="<?php echo $base_url; ?>/pages/logout.php" class="block px-4 py-2 text-red-500 hover:bg-gray-100">Đăng xuất</a></li>
                            <?php else: ?>
                                <li><a href="<?php echo $base_url; ?>/pages/login.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Đăng nhập</a></li>
                                <li><a href="<?php echo $base_url; ?>/pages/register.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Đăng ký</a></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Mobile Menu Toggle and Search Toggle -->
            <div class="flex items-center md:hidden space-x-4">
                <button id="search-toggle" class="text-gray-700 hover:text-pink-500 transition duration-200">
                    <i class="fas fa-search"></i>
                </button>
                <button id="mobile-menu-toggle" class="text-gray-700 hover:text-pink-500 transition duration-200">
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>
        </div>

        <!-- Mobile Search Bar -->
        <div id="mobile-search" class="hidden md:hidden bg-white/90 glass-container py-4 px-4">
            <form method="GET" action="<?php echo $base_url; ?>/pages/search.php" class="flex items-center">
                <input type="text" name="query" placeholder="Tìm kiếm sản phẩm..." 
                       class="p-2 border border-gray-300/50 rounded-lg bg-white/80 shadow-sm focus:outline-none focus:ring-2 focus:ring-pink-500 w-full" />
                <button type="submit" class="ml-2 p-2 bg-pink-500 text-white rounded-lg hover:bg-pink-600 transition">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden md:hidden bg-white/90 glass-container py-4 px-4">
            <ul class="flex flex-col space-y-4 text-gray-700 font-medium">
                <li>
                    <a href="<?php echo $base_url; ?>/index.php" class="hover:text-pink-500 transition flex items-center">
                        <i class="fas fa-home mr-2"></i> Trang chủ
                    </a>
                </li>
                <li>
                    <a href="<?php echo $base_url; ?>/pages/products.php" class="hover:text-pink-500 transition flex items-center">
                        <i class="fas fa-tshirt mr-2"></i> Sản phẩm
                    </a>
                </li>
                <li>
                    <a href="<?php echo $base_url; ?>/pages/about.php" class="hover:text-pink-500 transition flex items-center">
                        <i class="fas fa-info-circle mr-2"></i> Giới thiệu
                    </a>
                </li>
                <li>
                    <a href="<?php echo $base_url; ?>/pages/contact.php" class="hover:text-pink-500 transition flex items-center">
                        <i class="fas fa-envelope mr-2"></i> Liên hệ
                    </a>
                </li>
                <li>
                    <a href="<?php echo $base_url; ?>/pages/cart.php" class="hover:text-pink-500 transition flex items-center">
                        <i class="fas fa-shopping-cart mr-2"></i> Giỏ hàng 
                        <span class="ml-1">
                            <?php echo (isset($_SESSION['id']) && isset($_SESSION['cart'])) ? count($_SESSION['cart']) : 0; ?>
                        </span>
                    </a>
                </li>
                <li>
                    <div class="flex items-center justify-between">
                        <span class="font-medium">Tài khoản</span>
                        <button id="mobile-user-menu-toggle" class="text-gray-700 hover:text-pink-500 transition duration-200">
                            <i class="fas fa-chevron-down"></i>
                        </button>
                    </div>
                    <ul id="mobile-user-menu" class="hidden pl-4 mt-2 space-y-2">
                        <?php if (isset($_SESSION['id'])): ?>
                            <li><a href="<?php echo $base_url; ?>/pages/profile.php" class="hover:text-pink-500 transition">Tài khoản</a></li>
                            <li><a href="<?php echo $base_url; ?>/pages/logout.php" class="hover:text-red-500 transition">Đăng xuất</a></li>
                        <?php else: ?>
                            <li><a href="<?php echo $base_url; ?>/pages/login.php" class="hover:text-pink-500 transition">Đăng nhập</a></li>
                            <li><a href="<?php echo $base_url; ?>/pages/register.php" class="hover:text-pink-500 transition">Đăng ký</a></li>
                        <?php endif; ?>
                    </ul>
                </li>
            </ul>
        </div>
    </header>

    <!-- Placeholder for page content -->
    <main class="pt-20">
        <!-- Your page content goes here -->
    </main>

    <!-- JavaScript for toggling menus -->
    <script>
        // Desktop User Menu
        const userMenuToggle = document.getElementById('user-menu-toggle');
        const userMenu = document.getElementById('user-menu');
        userMenuToggle.addEventListener('click', () => {
            userMenu.classList.toggle('hidden');
        });

        // Mobile Menu
        const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
        const mobileMenu = document.getElementById('mobile-menu');
        mobileMenuToggle.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
            mobileSearch.classList.add('hidden'); // Close search if open
        });

        // Mobile User Menu
        const mobileUserMenuToggle = document.getElementById('mobile-user-menu-toggle');
        const mobileUserMenu = document.getElementById('mobile-user-menu');
        mobileUserMenuToggle.addEventListener('click', () => {
            mobileUserMenu.classList.toggle('hidden');
        });

        // Mobile Search
        const searchToggle = document.getElementById('search-toggle');
        const mobileSearch = document.getElementById('mobile-search');
        searchToggle.addEventListener('click', () => {
            mobileSearch.classList.toggle('hidden');
            mobileMenu.classList.add('hidden'); // Close menu if open
        });
    </script>
</body>
</html>