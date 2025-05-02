<?php
session_start(); 
include('config/database.php');
include('components/header.php');

// Tải dữ liệu động từ PHP
$vouchers = [];
$featuredProducts = [];

$vouchersStmt = $conn->prepare("SELECT id, code, discount_value, min_order_value, expires_at, image FROM voucher WHERE expires_at >= CURDATE() LIMIT 3");
if ($vouchersStmt) {
    $vouchersStmt->execute();
    $vouchersResult = $vouchersStmt->get_result();
    $vouchers = $vouchersResult->fetch_all(MYSQLI_ASSOC);
    $vouchersStmt->close();
} else {
    error_log("Error preparing vouchers query: " . $conn->error);
    $vouchers = [];
}

$featuredStmt = $conn->prepare("SELECT id, name, image, price, discount_price, best_seller FROM products WHERE featured = 1 LIMIT 8");
if ($featuredStmt) {
    $featuredStmt->execute();
    $featuredProducts = $featuredStmt->get_result();
    $featuredStmt->close();
} else {
    error_log("Error preparing featured products query: " . $conn->error);
    $featuredProducts = null;
}

$base_url = '/WEBQUANAOTREEM';
$isLoggedIn = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>BABY Store - Trang chủ</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <style>
        /* Bright animated background */
        .animated-bg {
            background: linear-gradient(135deg, #fce7f3, #e0f2fe, #f3e8ff);
            background-size: 400%;
            animation: gradientBG 15s ease infinite;
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

        /* Slider styles */
        #sliderContainer {
            background-color: #f0f0f0; /* Fallback background color */
            position: relative;
            height: 600px; /* Tăng chiều cao để hiển thị đầy đủ nội dung */
            width: 100%;
            overflow: hidden;
            margin-top: 0;
        }
        #sliderContainer .slide {
            opacity: 0;
            transition: opacity 1s ease-in-out;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: contain; /* Thay đổi từ cover sang contain để hiển thị đầy đủ nội dung */
        }
        #sliderContainer .slide.active {
            opacity: 1;
        }
        .dot-container {
            margin-top: 0;
        }
        .dot-container .dot.active {
            background-color: #f472b6; /* pink-400 */
        }

        /* Đảm bảo header không có margin bottom */
        header {
            margin-bottom: 0;
            padding-bottom: 0;
        }

        /* Đảm bảo không có khoảng trắng giữa header và slider */
        body {
            margin: 0;
            padding: 0;
        }

        /* Điều chỉnh CSS cho phần header trong components/header.php (nếu có) */
        .navbar, nav, .header-container {
            margin-bottom: 0 !important;
            padding-bottom: 0 !important;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            #sliderContainer {
                height: 400px; /* Chiều cao nhỏ hơn cho thiết bị di động */
            }
        }

        /* Chat styles */
        .chat-box.hidden {
            transform: scale(0.95);
            opacity: 0;
        }
        .chat-box {
            transform: scale(1);
            opacity: 1;
        }
    </style>
</head>
<body class="animated-bg font-roboto text-gray-800">
    <!-- Slider -->
    <section id="Slider" class="w-full relative overflow-hidden">
        <div class="relative w-full h-[600px] max-w-full" id="sliderContainer">
            <img src="assets/images/slider_1.webp" class="absolute w-full h-full top-0 left-0 object-contain slide active" alt="Khuyến mãi 1" loading="lazy" />
            <img src="assets/images/anh9.jpg" class="absolute w-full h-full top-0 left-0 object-contain slide" alt="Khuyến mãi 2" loading="lazy" />
            <img src="assets/images/slider_5.webp" class="absolute w-full h-full top-0 left-0 object-contain slide" alt="Khuyến mãi 3" loading="lazy" />
        </div>
        <div class="absolute w-full flex items-center justify-center bottom-4 left-0 z-10 dot-container" role="tablist">
            <span class="h-4 w-4 bg-gray-300 rounded-full mr-2 cursor-pointer dot active" role="tab" aria-selected="true" data-slide="0"></span>
            <span class="h-4 w-4 bg-gray-300 rounded-full mr-2 cursor-pointer dot" role="tab" aria-selected="false" data-slide="1"></span>
            <span class="h-4 w-4 bg-gray-300 rounded-full mr-2 cursor-pointer dot" role="tab" aria-selected="false" data-slide="2"></span>
        </div>
    </section>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-12">
        <!-- Voucher Section -->
        <section class="py-12 bg-gray-100/90 glass-container rounded-lg">
            <h2 class="text-center text-3xl font-bold bg-gradient-to-r from-pink-400 to-blue-400 bg-clip-text text-transparent mb-8">
                Voucher Đang Áp Dụng
            </h2>
            <div id="voucher-container" class="flex flex-wrap justify-center gap-6">
                <?php if (empty($vouchers)): ?>
                    <p class="text-center text-gray-600 col-span-full">Hiện tại không có voucher nào.</p>
                <?php else: ?>
                    <?php foreach ($vouchers as $voucher): ?>
                        <div class="bg-white/90 glass-container shadow-lg rounded-lg overflow-hidden w-80 h-48 relative hover-scale">
                            <div class="absolute inset-0 bg-gradient-to-r from-pink-400 to-blue-400 opacity-10"></div>
                            <div class="p-6 relative z-10">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <span class="bg-pink-100 text-pink-600 font-semibold px-3 py-1 rounded-full text-sm">
                                            <?php echo htmlspecialchars($voucher['code']); ?>
                                        </span>
                                        <h3 class="text-xl font-bold text-gray-800 mt-3">
                                            Giảm <?php echo htmlspecialchars(number_format($voucher['discount_value'], 0)); ?>%
                                        </h3>
                                        <p class="text-gray-600 mt-2">
                                            Cho đơn hàng từ <?php echo htmlspecialchars(number_format($voucher['min_order_value'], 0, ',', '.')); ?>đ
                                        </p>
                                        <p class="text-sm text-gray-500 mt-2">
                                            Hết hạn: <?php echo date('d/m/Y', strtotime($voucher['expires_at'])); ?>
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <button class="bg-pink-400 hover:bg-pink-500 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                            Lưu mã
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- Sản phẩm nổi bật -->
        <section class="py-12">
            <div class="container mx-auto px-4">
                <h2 class="text-3xl font-bold text-center bg-gradient-to-r from-pink-400 to-blue-400 bg-clip-text text-transparent mb-10">
                    Sản Phẩm Nổi Bật
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    <?php if (!$featuredProducts || mysqli_num_rows($featuredProducts) == 0) { ?>
                        <p class="text-center text-gray-500 col-span-full">Không có sản phẩm nổi bật nào.</p>
                    <?php } else { ?>
                        <?php while ($product = mysqli_fetch_assoc($featuredProducts)) { ?>
                            <div class="bg-white/90 glass-container p-4 rounded-lg shadow-lg transition-all duration-300 hover:shadow-xl hover-scale">
                                <div class="relative overflow-hidden rounded-lg">
                                    <img src="<?php echo !empty($product['image']) ? htmlspecialchars($product['image']) : 'assets/images/product-placeholder.jpg'; ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                         class="w-full h-48 object-cover rounded-lg transition-transform duration-300 hover:scale-105">
                                    <?php if (!empty($product['discount_price']) && $product['discount_price'] > 0 && $product['discount_price'] < $product['price']) { ?>
                                        <span class="absolute top-2 left-2 bg-pink-400 text-white text-xs font-semibold px-2 py-1 rounded">
                                            Giảm <?php echo htmlspecialchars(round((($product['price'] - $product['discount_price']) / $product['price']) * 100)); ?>%
                                        </span>
                                    <?php } ?>
                                    <?php if (!empty($product['best_seller']) && $product['best_seller'] == 1) { ?>
                                        <span class="absolute top-2 right-2 bg-blue-400 text-white text-xs font-semibold px-2 py-1 rounded">Bán Chạy</span>
                                    <?php } ?>
                                </div>
                                <h3 class="mt-4 text-lg font-semibold text-gray-800 line-clamp-2 h-14">
                                    <?php echo htmlspecialchars($product['name']); ?>
                                </h3>
                                <div class="mt-2 flex items-center">
                                    <p class="text-pink-400 font-bold">
                                        <?php 
                                        if (!empty($product['discount_price']) && $product['discount_price'] > 0 && $product['discount_price'] < $product['price']) {
                                            echo number_format($product['discount_price'], 0, ',', '.') . ' VNĐ'; 
                                        } else {
                                            echo number_format($product['price'], 0, ',', '.') . ' VNĐ';
                                        }
                                        ?>
                                    </p>
                                    <?php if (!empty($product['discount_price']) && $product['discount_price'] > 0 && $product['discount_price'] < $product['price']) { ?>
                                        <span class="text-gray-400 line-through ml-2 text-sm">
                                            <?php echo number_format($product['price'], 0, ',', '.') . ' VNĐ'; ?>
                                        </span>
                                    <?php } ?>
                                </div>
                                <div class="mt-4 flex space-x-2">
                                    <a href="product_details.php?id=<?php echo htmlspecialchars($product['id']); ?>" 
                                       class="flex-1 bg-blue-400 hover:bg-blue-500 text-white py-2 px-4 rounded-lg transition duration-300 text-center">
                                        Xem Chi Tiết
                                    </a>
                                    <button onclick="addToCart(<?php echo htmlspecialchars($product['id']); ?>)" 
                                            class="bg-pink-400 hover:bg-pink-500 text-white p-2 rounded-lg transition duration-300">
                                        <i class="fas fa-cart-plus"></i>
                                    </button>
                                </div>
                            </div>
                        <?php } ?>
                    <?php } ?>
                </div>
            </div>
        </section>
    </main>

    <!-- Chat -->
    <div class="chat-container fixed bottom-5 right-5 z-50">
        <button class="chat-icon w-14 h-14 bg-pink-400 text-white flex items-center justify-center rounded-full shadow-lg cursor-pointer transform transition-all duration-300 hover:bg-pink-500 hover-scale" 
                onclick="toggleChat()" aria-label="Mở hộp chat">
            <i class="fas fa-comments w-6 h-6"></i>
        </button>
        <div class="chat-box fixed bottom-24 right-5 w-96 max-w-[90%] bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col transition-all duration-300 ease-in-out hidden" 
             id="chatBox" role="dialog" aria-labelledby="chat-header">
            <div class="chat-header bg-gradient-to-r from-pink-400 to-blue-400 text-white p-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <img src="assets/images/logo1.jpg" alt="Bot Avatar" class="w-8 h-8 rounded-full object-cover" loading="lazy" />
                    <span class="font-semibold text-lg">Hỗ trợ khách hàng</span>
                </div>
                <button class="close-btn text-white hover:text-gray-200 transition-colors" onclick="toggleChat()" aria-label="Đóng hộp chat">
                    <i class="fas fa-times w-6 h-6"></i>
                </button>
            </div>
            <div class="chat-body bg-gray-50 p-4 flex flex-col gap-3 h-80 overflow-y-auto" id="chatBody" aria-live="polite">
                <div class="flex items-start gap-2">
                    <div class="bg-pink-100 text-black p-3 rounded-lg rounded-tl-none max-w-[80%]">
                        <p>Xin chào! Bạn cần hỗ trợ gì?</p>
                        <span class="text-xs text-gray-500 mt-1 block"><?php echo date('H:i'); ?></span>
                    </div>
                </div>
            </div>
            <div class="chat-input flex items-center p-4 bg-white border-t border-gray-200">
                <input type="text" id="userInput" placeholder="Nhập câu hỏi..." 
                       class="flex-1 p-3 bg-gray-100 rounded-full border-none outline-none text-sm placeholder-gray-500" 
                       aria-label="Nhập câu hỏi hỗ trợ" />
                <button onclick="sendMessage()" class="bg-pink-400 text-white p-3 rounded-full ml-3 hover:bg-pink-500 transition-colors" 
                        aria-label="Gửi tin nhắn">
                    <i class="fas fa-paper-plane w-5 h-5"></i>
                </button>
            </div>
            <div class="chat-links flex justify-between items-center p-4 bg-gray-50 border-t border-gray-200 gap-4">
                <a href="https://m.me/yourpage" target="_blank" 
                   class="flex-1 bg-blue-400 text-white p-3 rounded-lg flex items-center justify-center gap-2 hover:bg-blue-500 transition-colors" 
                   aria-label="Chat qua Messenger">
                    <i class="fab fa-facebook-messenger w-6 h-6"></i> Messenger
                </a>
                <a href="https://chat.zalo.me/" target="_blank" 
                   class="flex-1 bg-blue-400 text-white p-3 rounded-lg flex items-center justify-center gap-2 hover:bg-blue-500 transition-colors" 
                   aria-label="Chat qua Zalo">
                    <i class="fas fa-comment-alt w-6 h-6"></i> Zalo
                </a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include('components/footer.php'); ?>

    <!-- Pass PHP variables to JavaScript -->
    <script>
        const baseUrl = '<?php echo $base_url; ?>';
        const isLoggedIn = <?php echo json_encode($isLoggedIn); ?>;
    </script>

    <!-- Scripts -->
    <script>
        // Hàm toggle giỏ hàng
        function toggleCart() {
            if (isLoggedIn) {
                window.location.href = `${baseUrl}/pages/cart.php`;
            } else {
                if (confirm('Bạn cần đăng nhập để xem giỏ hàng. Đến trang đăng nhập?')) {
                    window.location.href = `${baseUrl}/pages/login.php`;
                }
            }
        }

        // Hàm thêm vào giỏ hàng
        function addToCart(productId) {
            if (isLoggedIn) {
                fetch(`${baseUrl}/pages/add_to_cart.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `product_id=${productId}&quantity=1`
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        alert('Đã thêm sản phẩm vào giỏ hàng!');
                    } else {
                        alert(data.message || 'Có lỗi khi thêm vào giỏ hàng.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra. Vui lòng thử lại.');
                });
            } else {
                if (confirm('Bạn cần đăng nhập để thêm sản phẩm vào giỏ hàng. Đến trang đăng nhập?')) {
                    window.location.href = `${baseUrl}/pages/login.php`;
                }
            }
        }

        // Chat functions
        function toggleChat() {
            const chatBox = document.getElementById('chatBox');
            chatBox.classList.toggle('hidden');
            scrollToBottom();
        }

        function sendMessage() {
            const userInput = document.getElementById('userInput');
            const chatBody = document.getElementById('chatBody');
            const message = userInput.value.trim();

            if (message === '') return;

            const userMessage = document.createElement('div');
            userMessage.className = 'flex items-end justify-end gap-2';
            userMessage.innerHTML = `
                <div class="bg-blue-400 text-white p-3 rounded-lg rounded-tr-none max-w-[80%]">
                    <p>${message}</p>
                    <span class="text-xs text-gray-200 mt-1 block text-right">${new Date().toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' })}</span>
                </div>
            `;
            chatBody.appendChild(userMessage);

            userInput.value = '';

            setTimeout(() => {
                const botMessage = document.createElement('div');
                botMessage.className = 'flex items-start gap-2';
                botMessage.innerHTML = `
                    <div class="bg-pink-100 text-black p-3 rounded-lg rounded-tl-none max-w-[80%]">
                        <p>Cảm ơn bạn đã liên hệ! Chúng tôi sẽ trả lời sớm nhất có thể.</p>
                        <span class="text-xs text-gray-500 mt-1 block">${new Date().toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' })}</span>
                    </div>
                `;
                chatBody.appendChild(botMessage);
                scrollToBottom();
            }, 1000);

            scrollToBottom();
        }

        function scrollToBottom() {
            const chatBody = document.getElementById('chatBody');
            chatBody.scrollTop = chatBody.scrollHeight;
        }

        document.getElementById('userInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });

        // Slider functionality
        const slides = document.querySelectorAll('#sliderContainer .slide');
        const dots = document.querySelectorAll('.dot-container .dot');
        let currentSlide = 0;
        let slideInterval;

        // Debugging
        console.log('Slides found:', slides.length);
        console.log('Dots found:', dots.length);

        function showSlide(index) {
            console.log('Showing slide:', index);
            slides.forEach(slide => slide.classList.remove('active'));
            dots.forEach(dot => {
                dot.classList.remove('active');
                dot.setAttribute('aria-selected', 'false');
            });

            slides[index].classList.add('active');
            dots[index].classList.add('active');
            dots[index].setAttribute('aria-selected', 'true');
            currentSlide = index;
        }

        function startSlider() {
            console.log('Starting slider');
            slideInterval = setInterval(() => {
                currentSlide = (currentSlide + 1) % slides.length;
                showSlide(currentSlide);
            }, 5000);
        }

        function stopSlider() {
            console.log('Stopping slider');
            clearInterval(slideInterval);
        }

        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                stopSlider();
                showSlide(index);
                startSlider();
            });
        });

        if (slides.length > 0) {
            startSlider();
        } else {
            console.error('No slides found. Check #sliderContainer and .slide elements.');
        }
    </script>
</body>
</html>