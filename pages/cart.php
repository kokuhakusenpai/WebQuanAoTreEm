<?php
// Bắt đầu session trước khi có bất kỳ output nào
session_start();

// Nhập file cơ sở dữ liệu
include('../config/database.php');

// Khởi tạo session nếu chưa có
if (session_status() == PHP_SESSION_NONE) {
    // Đã bị loại bỏ vì session_start() đã được gọi ở trên
}

// Biến để lưu thông báo thành công hoặc lỗi
$error_message = "";
$success_message = "";

// Xử lý cập nhật số lượng sản phẩm
if (isset($_POST['update_quantity'])) {
    $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
    $new_quantity = (int)$_POST['quantity'];

    if ($new_quantity > 0) {
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['id'] == $product_id) {
                $item['quantity'] = $new_quantity;
                break;
            }
        }
    }
    // Thay vì chuyển hướng, đặt một thông báo thành công
    $success_message = "Số lượng đã được cập nhật";
    // Để tránh việc gửi lại form, sử dụng PRG pattern
    header("Location: cart.php?msg=updated");
    exit();
}

// Xử lý xóa sản phẩm khỏi giỏ hàng
if (isset($_POST['remove_item'])) {
    $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
    foreach ($_SESSION['cart'] as $key => $item) {
        if ($item['id'] == $product_id) {
            unset($_SESSION['cart'][$key]);
            break;
        }
    }
    // Đặt lại chỉ số mảng
    $_SESSION['cart'] = array_values($_SESSION['cart']);
    // Thay vì chuyển hướng, đặt một thông báo thành công
    $success_message = "Sản phẩm đã được xóa khỏi giỏ hàng";
    // Để tránh việc gửi lại form, sử dụng PRG pattern
    header("Location: cart.php?msg=removed");
    exit();
}

// Xử lý đặt hàng
if (isset($_POST['checkout'])) {
    if (isset($_SESSION['user_id']) && !empty($_SESSION['cart'])) {
        $user_id = $_SESSION['user_id'];
        $customer_name = mysqli_real_escape_string($conn, $_POST['customer_name']);
        $customer_email = mysqli_real_escape_string($conn, $_POST['customer_email']);
        $customer_phone = mysqli_real_escape_string($conn, $_POST['customer_phone']);
        $customer_address = mysqli_real_escape_string($conn, $_POST['customer_address']);
        $total = 0;

        // Tính tổng tiền
        foreach ($_SESSION['cart'] as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        // Thêm đơn hàng vào bảng orders
        $status = 'pending';
        $query = "INSERT INTO orders (user_id, customer_name, customer_email, customer_phone, customer_address, total_price, status) 
                  VALUES ('$user_id', '$customer_name', '$customer_email', '$customer_phone', '$customer_address', '$total', '$status')";
        if (mysqli_query($conn, $query)) {
            $order_id = mysqli_insert_id($conn);

            // Thêm chi tiết đơn hàng vào bảng order_details
            foreach ($_SESSION['cart'] as $item) {
                $product_id = $item['id'];
                $quantity = $item['quantity'];
                $price = $item['price'];
                $subtotal = $price * $quantity;
                $query = "INSERT INTO order_details (order_id, product_id, quantity, price, subtotal) 
                          VALUES ('$order_id', '$product_id', '$quantity', '$price', '$subtotal')";
                mysqli_query($conn, $query);
            }

            // Xóa giỏ hàng sau khi đặt hàng
            unset($_SESSION['cart']);
            // Lưu thông báo thành công vào session để hiển thị ở trang đích
            $_SESSION['order_success'] = "Đơn hàng đã được đặt thành công!";
            // Chuyển hướng đến trang lịch sử đơn hàng
            header("Location: order_history.php");
            exit();
        } else {
            $error_message = "Lỗi khi đặt hàng: " . mysqli_error($conn);
        }
    } else if (!isset($_SESSION['user_id'])) {
        // Lưu URL hiện tại vào session để sau khi đăng nhập có thể quay lại
        $_SESSION['redirect_after_login'] = "cart.php";
        // Chuyển hướng đến trang đăng nhập
        header("Location: login.php");
        exit();
    }
}

// Hiển thị các thông báo từ tham số URL
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'updated') {
        $success_message = "Số lượng đã được cập nhật";
    } else if ($_GET['msg'] === 'removed') {
        $success_message = "Sản phẩm đã được xóa khỏi giỏ hàng";
    }
}

// Bây giờ mới include header.php sau khi đã xử lý tất cả logic và redirect
include('../components/header.php');
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SUSU KIDS - Giỏ Hàng</title>
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

        /* Cart item hover effect */
        .cart-item {
            transition: all 0.3s ease;
        }
        .cart-item:hover {
            background-color: #f1f5f9;
        }

        /* Quantity input styling */
        .quantity-input {
            width: 4rem;
            text-align: center;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            padding: 0.25rem;
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
<body class="animated-bg text-gray-800 font-['Roboto']">
<section class="py-6 mt-6">
        <div class="container mx-auto px-4">
            <h2 class="text-4xl font-bold text-center bg-gradient-to-r from-pink-400 to-blue-400 bg-clip-text text-transparent mb-8">Giỏ Hàng Của Bạn</h2>

            <!-- Hiển thị thông báo thành công nếu có -->
            <?php if (!empty($success_message)) { ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg">
                    <p><?php echo htmlspecialchars($success_message); ?></p>
                </div>
            <?php } ?>

            <!-- Hiển thị thông báo lỗi nếu có -->
            <?php if (!empty($error_message)) { ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg">
                    <p><?php echo htmlspecialchars($error_message); ?></p>
                </div>
            <?php } ?>

            <?php if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) { ?>
                <!-- Layout 2 cột: Giỏ hàng và Thông tin thanh toán -->
                <div class="flex flex-col lg:flex-row gap-6">
                    <!-- Cột 1: Thông tin giỏ hàng -->
                    <div class="lg:w-3/5 bg-white glass-container rounded-lg shadow-lg p-6">
                        <h3 class="text-2xl font-semibold mb-4 text-gray-900 border-b pb-2">Chi Tiết Đơn Hàng</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead>
                                    <tr class="border-b">
                                        <th class="p-4 text-gray-900">Sản Phẩm</th>
                                        <th class="p-4 text-gray-900">Tên</th>
                                        <th class="p-4 text-gray-900">Giá</th>
                                        <th class="p-4 text-gray-900">Số Lượng</th>
                                        <th class="p-4 text-gray-900">Tổng</th>
                                        <th class="p-4 text-gray-900"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $total = 0;
                                    foreach ($_SESSION['cart'] as $item) {
                                        $subtotal = $item['price'] * $item['quantity'];
                                        $total += $subtotal;
                                    ?>
                                        <tr class="cart-item border-b">
                                            <td class="p-4">
                                                <img src="../assets/images/<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" class="w-16 h-16 object-cover rounded-lg">
                                            </td>
                                            <td class="p-4 text-gray-800"><?php echo $item['name']; ?></td>
                                            <td class="p-4 text-pink-600 font-semibold"><?php echo number_format($item['price'], 0, ',', '.') . ' VNĐ'; ?></td>
                                            <td class="p-4">
                                                <form method="POST" action="">
                                                    <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                                    <div class="flex items-center space-x-2">
                                                        <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" class="quantity-input">
                                                        <button type="submit" name="update_quantity" class="text-blue-400 hover:text-blue-500 transition duration-300">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </form>
                                            </td>
                                            <td class="p-4 text-pink-600 font-semibold"><?php echo number_format($subtotal, 0, ',', '.') . ' VNĐ'; ?></td>
                                            <td class="p-4">
                                                <form method="POST" action="">
                                                    <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                                    <button type="submit" name="remove_item" class="text-red-400 hover:text-red-500 transition duration-300">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                        </svg>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Tổng đơn hàng -->
                        <div class="mt-6 flex justify-end">
                            <div class="bg-gray-50 p-4 rounded-lg w-64">
                                <div class="flex justify-between items-center text-gray-700">
                                    <span>Tạm tính:</span>
                                    <span><?php echo number_format($total, 0, ',', '.') . ' VNĐ'; ?></span>
                                </div>
                                <div class="flex justify-between items-center text-gray-700 mt-2">
                                    <span>Phí vận chuyển:</span>
                                    <span>Miễn phí</span>
                                </div>
                                <div class="border-t mt-2 pt-2 flex justify-between items-center">
                                    <span class="font-semibold">Tổng cộng:</span>
                                    <span class="text-pink-600 font-bold text-lg"><?php echo number_format($total, 0, ',', '.') . ' VNĐ'; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Cột 2: Form thông tin thanh toán -->
                    <div class="lg:w-2/5 bg-white glass-container rounded-lg shadow-lg p-6">
                        <h3 class="text-2xl font-semibold mb-4 text-gray-900 border-b pb-2">Thông Tin Thanh Toán</h3>
                        <form method="POST" action="">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-gray-800 mb-2">Họ Tên</label>
                                    <input type="text" name="customer_name" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-400" required>
                                </div>
                                <div>
                                    <label class="block text-gray-800 mb-2">Email</label>
                                    <input type="email" name="customer_email" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-400" required>
                                </div>
                                <div>
                                    <label class="block text-gray-800 mb-2">Số Điện Thoại</label>
                                    <input type="text" name="customer_phone" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-400" required>
                                </div>
                                <div>
                                    <label class="block text-gray-800 mb-2">Địa Chỉ</label>
                                    <textarea name="customer_address" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-400" rows="3" required></textarea>
                                </div>
                                
                                <!-- Phương thức thanh toán -->
                                <div class="mt-4">
                                    <label class="block text-gray-800 mb-2">Phương Thức Thanh Toán</label>
                                    <div class="space-y-2">
                                        <label class="flex items-center space-x-2">
                                            <input type="radio" name="payment_method" value="cod" checked class="text-pink-500">
                                            <span>Thanh toán khi nhận hàng (COD)</span>
                                        </label>
                                        <label class="flex items-center space-x-2">
                                            <input type="radio" name="payment_method" value="bank" class="text-pink-500">
                                            <span>Chuyển khoản ngân hàng</span>
                                        </label>
                                        <label class="flex items-center space-x-2">
                                            <input type="radio" name="payment_method" value="momo" class="text-pink-500">
                                            <span>Ví điện tử (Momo, ZaloPay)</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Button Thanh toán -->
                            <div class="mt-6">
                                <button type="submit" name="checkout" class="w-full bg-gradient-to-r from-pink-400 to-blue-400 text-white py-3 px-6 rounded-lg hover:opacity-90 transition duration-300 font-semibold text-lg">
                                    Hoàn Tất Đặt Hàng
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php } else { ?>
                <div class="text-center">
                    <p class="text-gray-500 text-lg">Giỏ hàng của bạn hiện đang trống.</p>
                    <a href="category.php" class="mt-4 inline-block bg-blue-400 text-white px-6 py-2 rounded-lg hover:bg-blue-500 transition duration-300">Tiếp Tục Mua Sắm</a>
                </div>
            <?php } ?>
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