<?php
session_start();
include('../config/database.php');
include('../components/header.php');

// Danh mục cha và mục con
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

// Xử lý thêm sản phẩm vào giỏ hàng
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
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        $found = false;
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['id'] == $product_id) {
                $item['quantity'] += 1;
                $found = true;
                break;
            }
        }

        if (!$found) {
            $_SESSION['cart'][] = [
                'id' => $product['id'],
                'name' => $product['name'],
                'price' => $product['price'],
                'image' => $product['image'],
                'quantity' => 1
            ];
        }

        // Thay đổi ở đây: sử dụng JavaScript thay vì header() PHP
        echo "<script>window.location.href = '" . $_SERVER['REQUEST_URI'] . "';</script>";
        exit();
    }
}

// Xử lý lọc sản phẩm theo category hoặc subcategory
$category_filter = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : '';
$subfilter = isset($_GET['subcategory']) ? mysqli_real_escape_string($conn, $_GET['subcategory']) : '';

$query = "SELECT p.* FROM products p LEFT JOIN category c ON p.category_id = c.id";
$conditions = [];

if ($category_filter) {
    $parent_id = null;
    $stmt = $conn->prepare("SELECT id FROM category WHERE name = ? AND parent_id IS NULL");
    $stmt->bind_param("s", $category_filter);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $parent_id = $row['id'];
    }
    $stmt->close();
    if ($parent_id) {
        $conditions[] = "p.category_id = $parent_id OR p.category_id IN (SELECT id FROM category WHERE parent_id = $parent_id)";
    }
}

if ($subfilter) {
    $stmt = $conn->prepare("SELECT id FROM category WHERE name = ?");
    $stmt->bind_param("s", $subfilter);
    $stmt->execute();
    $result = $stmt->get_result();
    $subcategory_id = $result->fetch_assoc()['id'] ?? null;
    $stmt->close();
    if ($subcategory_id) {
        $conditions[] = "p.category_id = $subcategory_id";
    }
}

$query .= (count($conditions) > 0) ? " WHERE " . implode(" OR ", $conditions) : "";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SUSU KIDS - Sản Phẩm</title>
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
            min-height: calc(100vh - 20rem);
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
<body class="animated-bg text-gray-800 font-['Roboto']">
    <section class="py-6 mt-6">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row gap-8">
                <!-- Sidebar Danh mục - Đã được chỉnh sửa -->
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
                                <a href="?category=<?php echo urlencode($parent); ?>" class="category-item block text-gray-800 font-semibold text-base py-1 <?php echo ($category_filter == $parent && !$subfilter) ? 'active-category' : ''; ?>">
                                    <?php echo $parent; ?>
                                </a>
                                <ul class="ml-4 mt-2 space-y-2">
                                    <?php foreach ($subcategories as $sub) { ?>
                                        <li>
                                            <a href="?subcategory=<?php echo urlencode($sub); ?>" class="subcategory-item block text-gray-600 hover:text-pink-600 text-sm py-1 <?php echo ($subfilter == $sub) ? 'active-subcategory' : ''; ?>">
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
            
                <!-- Danh sách sản phẩm -->
                <div class="md:ml-8 flex-1">
                    <h2 class="text-4xl font-bold text-center bg-gradient-to-r from-pink-400 to-blue-400 bg-clip-text text-transparent mb-8">Tất Cả Sản Phẩm</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
                        <?php if ($result && mysqli_num_rows($result) > 0) { ?>
                            <?php while ($product = mysqli_fetch_assoc($result)) { ?>
                                <div class="bg-white/90 glass-container rounded-lg shadow-lg overflow-hidden transform transition duration-300 hover-scale">
                                <img src="<?php echo !empty($product['image']) ? (strpos($product['image'], '/') === 0 ? htmlspecialchars($product['image']) : '../' . htmlspecialchars($product['image'])) : '../assets/images/product-placeholder.jpg'; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-64 object-cover rounded-t-lg">
                                    <div class="p-4 flex justify-between items-center">
                                        <div>
                                            <h3 class="text-xl font-semibold text-gray-800"><?php echo htmlspecialchars($product['name']); ?></h3>
                                            <p class="text-lg text-pink-600 font-semibold my-2"><?php echo number_format($product['price'], 0, ',', '.') . ' VNĐ'; ?></p>
                                        </div>
                                        <div class="action-buttons">
                                            <!-- Nút Xem Chi Tiết (biểu tượng con mắt) -->
                                            <a href="product_details.php?id=<?php echo htmlspecialchars($product['id']); ?>" 
                                               class="action-button p-2 bg-blue-400 text-white rounded-lg hover:bg-blue-500 transition duration-300" 
                                               title="Xem chi tiết">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                            </a>
                                            <!-- Nút Thêm vào giỏ hàng -->
                                            <form method="POST" action="">
                                                <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['id']); ?>">
                                                <button type="submit" name="add_to_cart" 
                                                        class="action-button p-2 bg-pink-400 text-white rounded-lg hover:bg-pink-500 transition duration-300" 
                                                        title="Thêm vào giỏ hàng">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        <?php } else { ?>
                            <p class="col-span-4 text-center text-gray-500">Không có sản phẩm nào phù hợp.</p>
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

    <!-- Footer -->
    <?php include('../components/footer.php'); ?>

    <script>
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('open');
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