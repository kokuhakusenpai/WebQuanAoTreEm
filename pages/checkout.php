<?php
session_start();
include('../config/database.php');
include('../components/header.php');

// Khởi tạo giỏ hàng nếu chưa có
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Xử lý form thanh toán
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $customer_name = mysqli_real_escape_string($conn, $_POST['customer_name']);
    $customer_email = mysqli_real_escape_string($conn, $_POST['customer_email']);
    $customer_phone = mysqli_real_escape_string($conn, $_POST['customer_phone']);
    $customer_address = mysqli_real_escape_string($conn, $_POST['customer_address']);

    // Tính tổng tiền
    $total_price = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total_price += $item['price'] * $item['quantity'];
    }

    // Lưu đơn hàng vào bảng orders
    $query = "INSERT INTO orders (customer_name, customer_email, customer_phone, customer_address, total_price) 
              VALUES ('$customer_name', '$customer_email', '$customer_phone', '$customer_address', '$total_price')";
    if (mysqli_query($conn, $query)) {
        $order_id = mysqli_insert_id($conn);

        // Lưu chi tiết đơn hàng vào bảng order_details
        foreach ($_SESSION['cart'] as $product_id => $item) {
            $product_name = mysqli_real_escape_string($conn, $item['name']);
            $product_price = $item['price'];
            $quantity = $item['quantity'];
            $query = "INSERT INTO order_item (order_id, product_id, product_name, product_price, quantity) 
                      VALUES ('$order_id', '$product_id', '$product_name', '$product_price', '$quantity')";
            mysqli_query($conn, $query);
        }

        // Xóa giỏ hàng sau khi đặt hàng
        $_SESSION['cart'] = [];
        header("Location: order_success.php?order_id=$order_id");
        exit();
    } else {
        $error = "Lỗi khi đặt hàng. Vui lòng thử lại!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SUSU KIDS - Thanh Toán</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs" defer></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <link rel="stylesheet" href="../assets/css/styles.css"/>
</head>
<body class="animated-bg text-gray-800 font-['Roboto'] checkout-page">
    <section class="py-16 mt-32">
        <div class="container mx-auto px-4">
            <!-- Thanh toán -->
            <div class="bg-white glass-container rounded-xl shadow-lg p-8">
                <h2 class="text-3xl font-bold text-center bg-gradient-to-r from-pink-400 to-blue-400 bg-clip-text text-transparent mb-8">Thanh Toán</h2>
                <?php if (!empty($_SESSION['cart'])) { ?>
                    <div class="flex flex-col md:flex-row gap-8">
                        <!-- Form thông tin khách hàng -->
                        <div class="md:w-1/2">
                            <h3 class="text-xl font-semibold text-gray-900 mb-4">Thông Tin Khách Hàng</h3>
                            <?php if (isset($error)) { ?>
                                <p class="text-red-600 mb-4"><?php echo htmlspecialchars($error); ?></p>
                            <?php } ?>
                            <form method="POST" action="checkout.php">
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-gray-800 font-medium mb-1">Họ và tên</label>
                                        <input type="text" name="customer_name" required 
                                               class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
                                    </div>
                                    <div>
                                        <label class="block text-gray-800 font-medium mb-1">Email</label>
                                        <input type="email" name="customer_email" required 
                                               class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
                                    </div>
                                    <div>
                                        <label class="block text-gray-800 font-medium mb-1">Số điện thoại</label>
                                        <input type="tel" name="customer_phone" required 
                                               class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
                                    </div>
                                    <div>
                                        <label class="block text-gray-800 font-medium mb-1">Địa chỉ</label>
                                        <textarea name="customer_address" required 
                                                  class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400" rows="4"></textarea>
                                    </div>
                                </div>
                                <div class="mt-6 flex gap-4">
                                    <a href="cart.php" class="px-6 py-3 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition duration-300">Quay lại giỏ hàng</a>
                                    <button type="submit" name="place_order" 
                                            class="px-6 py-3 bg-blue-400 text-white rounded-lg hover:bg-blue-500 transition duration-300">Xác nhận đơn hàng</button>
                                </div>
                            </form>
                        </div>
                        <!-- Tóm tắt đơn hàng -->
                        <div class="md:w-1/2">
                            <h3 class="text-xl font-semibold text-gray-900 mb-4">Tóm Tắt Đơn Hàng</h3>
                            <div class="space-y-4">
                                <?php
                                $total = 0;
                                foreach ($_SESSION['cart'] as $product_id => $item) {
                                    $subtotal = $item['price'] * $item['quantity'];
                                    $total += $subtotal;
                                ?>
                                    <div class="flex items-center gap-4 border-b border-gray-200 pb-4 hover-scale">
                                        <img src="../assets/images/<?php echo htmlspecialchars($item['image']); ?>" 
                                             alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                             class="w-16 h-16 object-cover rounded-lg">
                                        <div class="flex-1">
                                            <h4 class="text-lg font-semibold text-gray-800"><?php echo htmlspecialchars($item['name']); ?></h4>
                                            <p class="text-gray-600"><?php echo number_format($item['price'], 0, ',', '.') . ' VNĐ'; ?> x <?php echo $item['quantity']; ?></p>
                                        </div>
                                        <p class="text-lg font-semibold text-pink-600"><?php echo number_format($subtotal, 0, ',', '.') . ' VNĐ'; ?></p>
                                    </div>
                                <?php } ?>
                                <div class="flex justify-between items-center pt-4">
                                    <p class="text-xl font-semibold text-gray-800">Tổng cộng:</p>
                                    <p class="text-xl font-semibold text-pink-600"><?php echo number_format($total, 0, ',', '.') . ' VNĐ'; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } else { ?>
                    <p class="text-center text-gray-500 text-lg">Giỏ hàng của bạn đang trống!</p>
                    <div class="mt-8 text-center">
                        <a href="category.php" class="px-6 py-3 bg-blue-400 text-white rounded-lg hover:bg-blue-500 transition duration-300">Quay lại cửa hàng</a>
                    </div>
                <?php } ?>
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