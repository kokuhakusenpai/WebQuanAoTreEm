<?php
session_start();
include('../config/database.php');

// Khởi tạo session nếu chưa có
if (session_status() == PHP_SESSION_NONE) {
}

// Danh mục cha và mục con (phong phú với danh mục mới) - copy từ category.php
$categories = [
    "Bé Gái" => ["Váy", "Áo thun", "Quần jeans", "Áo khoác", "Đồ bộ"],
    "Bé Trai" => ["Áo thun", "Quần short", "Quần jeans", "Áo sơ mi", "Áo hoodie"],
    "Sơ Sinh" => ["Bộ liền thân", "Mũ len", "Tã lót", "Yếm", "Bao tay"],
    "Phụ Kiện" => ["Nơ tóc", "Vớ", "Mũ beret", "Kính mát", "Túi xách nhỏ"],
    "Theo Mùa" => ["Đồ bơi", "Áo len", "Đồ Giáng Sinh", "Đồ Tết"],
    "Đồ Chơi" => ["Gấu bông", "Xe đẩy đồ chơi", "Búp bê"],
    "Giày Dép" => ["Sandal", "Giày thể thao", "Dép"],
    "Đồ Dùng" => ["Bình sữa", "Ghế ăn", "Xe đẩy"]
];

// Verify database connection
if (!$conn) {
    error_log("Database connection failed: " . mysqli_connect_error());
    die("Kết nối cơ sở dữ liệu thất bại: " . mysqli_connect_error());
}

// Khởi tạo giỏ hàng nếu chưa có
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Xử lý thêm vào giỏ hàng - giống với category.php
if (isset($_POST['add_to_cart'])) {
    $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
    
    // Lấy thông tin sản phẩm từ cơ sở dữ liệu
    $query = "SELECT * FROM products WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();

    if ($product) {
        // Khởi tạo giỏ hàng nếu chưa có
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // Kiểm tra xem sản phẩm đã có trong giỏ hàng chưa
        $found = false;
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['id'] == $product_id) {
                $item['quantity'] += 1; // Tăng số lượng nếu sản phẩm đã có
                $found = true;
                break;
            }
        }

        // Nếu sản phẩm chưa có trong giỏ hàng, thêm mới
        if (!$found) {
            $_SESSION['cart'][] = [
                'id' => $product['id'],
                'name' => $product['name'],
                'price' => $product['price'],
                'image' => $product['image'],
                'quantity' => 1
            ];
        }

        // Xử lý AJAX request nếu có
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            $cart_count = 0;
            foreach ($_SESSION['cart'] as $item) {
                $cart_count += $item['quantity'];
            }
            echo json_encode([
                'cart_count' => $cart_count,
                'message' => 'Sản phẩm đã được thêm vào giỏ hàng!'
            ]);
            exit();
        }

        // Chuyển hướng lại trang để tránh resubmit form
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    }
}

// Lấy id sản phẩm từ URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Truy vấn sản phẩm
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND status = 'available'");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

// Include header sau khi xử lý logic để tránh lỗi header already sent
include('../components/header.php');
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi Tiết Sản Phẩm</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <style>
        /* Animated background */
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

        /* Điều chỉnh sidebar trên mobile */
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                top: 0;
                left: 0;
                height: 100vh;
                width: 80%;
                max-width: 300px;
                transform: translateX(-100%);
                transition: transform 0.3s ease-in-out;
                z-index: 50;
                padding-top: 4rem;
            }
    
            .sidebar.open {
                transform: translateX(0);
            }
    
            /* Overlay khi sidebar mở */
            .sidebar-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 40;
                opacity: 0;
                visibility: hidden;
                transition: opacity 0.3s ease-in-out, visibility 0.3s ease-in-out;
            }
    
            .sidebar-overlay.open {
                opacity: 1;
                visibility: visible;
            }
        }

        /* Styles cho sidebar */
        .sidebar {
            position: sticky;
            top: 5rem;
            height: calc(100vh - 5rem);
        }

        /* Để đảm bảo footer không bị che khuất */
        .main-content {
            min-height: calc(100vh - 20rem); /* Điều chỉnh theo chiều cao của header và footer */
        }

        /* Hiệu ứng khi thêm vào giỏ hàng và xem chi tiết */
        .action-button {
            transition: all 0.3s ease;
        }
        .action-button:hover {
            transform: scale(1.1);
        }
        .action-button:active {
            transform: scale(0.95);
        }

        /* Căn chỉnh đồng đều cho các nút */
        .action-buttons {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        /* Hover scale effect */
        .hover-scale {
            transition: transform 0.3s ease;
        }
        .hover-scale:hover {
            transform: scale(1.05);
        }

        /* Toast notification */
        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #f472b6; /* Đồng bộ với pink-400 */
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
            z-index: 1000;
        }
        .toast.show {
            opacity: 1;
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
<body class="animated-bg text-gray-800 font-['Roboto'] index-page">
    <section class="py-6 mt-6">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row gap-8">
                <!-- Sidebar Danh mục - Copy từ category.php -->
                <aside class="sidebar w-full md:w-64 bg-white rounded-xl shadow-lg p-6 md:sticky md:top-20 md:h-auto md:max-h-[calc(100vh-6rem)] overflow-y-auto md:transform-none fixed top-16 left-0 z-30 max-h-[calc(100vh-4rem)] md:static">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-bold bg-gradient-to-r from-pink-400 to-blue-400 bg-clip-text text-transparent">Danh Mục</h3>
                        <button class="md:hidden text-gray-600 hover:text-pink-600" onclick="toggleSidebar()">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <ul class="space-y-4">
                        <?php foreach ($categories as $parent => $subcategories) { ?>
                            <li>
                                <a href="category.php?category=<?php echo urlencode($parent); ?>" class="category-item block text-gray-800 font-semibold text-base py-1">
                                    <?php echo $parent; ?>
                                </a>
                                <ul class="ml-4 mt-2 space-y-2">
                                    <?php foreach ($subcategories as $sub) { ?>
                                        <li>
                                            <a href="category.php?subcategory=<?php echo urlencode($sub); ?>" class="subcategory-item block text-gray-600 hover:text-pink-600 text-sm py-1">
                                                <?php echo $sub; ?>
                                            </a>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </li>
                        <?php } ?>
                    </ul>
                </aside>
            
                <!-- Nút toggle sidebar trên mobile -->
                <button class="md:hidden fixed top-20 left-4 z-50 bg-pink-400 text-white p-2 rounded-full hover:bg-pink-500" onclick="toggleSidebar()">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                
                <!-- Chi tiết sản phẩm -->
                <div class="md:ml-8 flex-1">
                    <div class="glass-container rounded-xl shadow-lg p-8 hover-scale">
                        <?php if ($product = $result->fetch_assoc()) { ?>
                            <div class="flex flex-col md:flex-row gap-8">
                                <div class="md:w-1/2">
                                    <img src="<?php echo !empty($product['image']) ? (strpos($product['image'], '/') === 0 ? htmlspecialchars($product['image']) : '../' . htmlspecialchars($product['image'])) : '../assets/images/default.jpg'; ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                         class="w-full h-auto rounded-lg object-cover max-h-[500px]">
                                </div>
                                <div class="md:w-1/2">
                                    <h2 class="text-3xl font-bold bg-gradient-to-r from-pink-400 to-blue-400 bg-clip-text text-transparent mb-4"><?php echo htmlspecialchars($product['name']); ?></h2>
                                    <p class="text-lg text-pink-600 font-semibold mb-4">
                                        <?php echo number_format($product['discount_price'] ?? $product['price'], 0, ',', '.') . ' VNĐ'; ?>
                                        <?php if ($product['discount_price'] && $product['discount_price'] < $product['price']) { ?>
                                            <span class="text-sm text-gray-500 line-through"><?php echo number_format($product['price'], 0, ',', '.') . ' VNĐ'; ?></span>
                                        <?php } ?>
                                    </p>
                                    <div class="mb-6">
                                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Mô tả sản phẩm</h3>
                                        <p class="text-gray-600"><?php echo htmlspecialchars($product['description'] ?: 'Không có mô tả.'); ?></p>
                                    </div>
                                    <?php if (!empty($product['size'])) { ?>
                                        <div class="mb-6">
                                            <h3 class="text-lg font-semibold text-gray-800 mb-2">Kích thước</h3>
                                            <p class="text-gray-600"><?php echo htmlspecialchars($product['size']); ?></p>
                                        </div>
                                    <?php } ?>
                                    <?php if (!empty($product['color'])) { ?>
                                        <div class="mb-6">
                                            <h3 class="text-lg font-semibold text-gray-800 mb-2">Màu sắc</h3>
                                            <p class="text-gray-600"><?php echo htmlspecialchars($product['color']); ?></p>
                                        </div>
                                    <?php } ?>
                                    <div class="mb-6">
                                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Tình trạng</h3>
                                        <p class="text-gray-600"><?php echo $product['stock'] > 0 ? 'Còn hàng (' . $product['stock'] . ')' : 'Hết hàng'; ?></p>
                                    </div>
                                    <form method="POST" action="" onsubmit="updateCartCount(event)">
                                        <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['id']); ?>">
                                        <div class="flex gap-4">
                                            <button type="submit" name="add_to_cart" 
                                                    class="action-button px-6 py-3 bg-pink-400 text-white rounded-lg hover:bg-pink-500 transition duration-300 <?php echo $product['stock'] == 0 ? 'opacity-50 cursor-not-allowed' : ''; ?>" 
                                                    <?php echo $product['stock'] == 0 ? 'disabled' : ''; ?>>
                                                Thêm vào giỏ hàng
                                            </button>
                                            <a href="category.php" class="action-button px-6 py-3 bg-gradient-to-r from-gray-300 to-gray-400 text-gray-800 rounded-lg hover:from-gray-400 hover:to-gray-500 transition duration-300">
                                                Quay lại danh sách sản phẩm
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php } else { ?>
                            <p class="text-center text-gray-500 text-lg">Sản phẩm không tồn tại hoặc không khả dụng!</p>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

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
                    <img src="../assets/images/logo1.jpg" alt="Bot Avatar" class="w-8 h-8 rounded-full object-cover" loading="lazy" />
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

    <!-- Toast notification -->
    <div id="toast" class="toast"></div>

    <!-- Footer -->
    <?php include('../components/footer.php'); ?>

    <script>
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('open');
        }

        function updateCartCount(event) {
            event.preventDefault();

            const form = event.target;
            const formData = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                const cartCountElements = document.querySelectorAll('#cart-count');
                cartCountElements.forEach(element => {
                    element.textContent = data.cart_count;
                });

                const toast = document.getElementById('toast');
                toast.textContent = data.message;
                toast.classList.add('show');
                setTimeout(() => {
                    toast.classList.remove('show');
                }, 3000);
            })
            .catch(error => console.error('Error:', error));
        }

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

        // Hiệu ứng fade-in khi cuộn cho footer
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