<?php
session_start();
include('../config/database.php');
include('../components/header.php');
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liên Hệ</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet" />
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

        /* Footer gradient */
        .footer-gradient {
            background-image: linear-gradient(to bottom, #fce7f3, #dbeafe);
        }
    </style>
</head>
<body class="animated-bg text-gray-800 font-['Roboto'] flex flex-col min-h-screen">

    <!-- Liên hệ -->
    <section class="py-12 flex-1">
        <div class="container mx-auto px-4 max-w-2xl glass-container shadow-md rounded-lg p-8 hover:shadow-lg transition-shadow hover-scale">
            <h2 class="text-3xl font-bold mb-4 text-center bg-gradient-to-r from-pink-400 to-blue-400 bg-clip-text text-transparent">Liên Hệ Với Chúng Tôi</h2>
            <p class="mb-8 text-center text-gray-600">
                Nếu bạn có bất kỳ câu hỏi hoặc yêu cầu nào, vui lòng điền thông tin vào form bên dưới.
            </p>

            <form action="send_contact.php" method="POST" class="space-y-6">
                <div>
                    <label for="name" class="block mb-1 font-medium">Họ và Tên:</label>
                    <input type="text" id="name" name="name" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label for="email" class="block mb-1 font-medium">Email:</label>
                    <input type="email" id="email" name="email" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label for="message" class="block mb-1 font-medium">Lời Nhắn:</label>
                    <textarea id="message" name="message" rows="5" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>

                <div class="text-center">
                    <button type="submit"
                        class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition duration-300">
                        Gửi Thông Tin
                    </button>
                </div>
            </form>
        </div>
    </section>

    <!-- Footer -->
    <?php include('../components/footer.php'); ?>

    <script>
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