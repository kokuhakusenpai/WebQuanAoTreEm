<?php
session_start();
$base_url = '/WEBQUANAOTREEM';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Giới Thiệu - SUSU Kids</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <link rel="stylesheet" href="../assets/css/styles.css"/>
</head>
<body class="animated-bg font-roboto text-gray-800 about-page">
    <!-- Include Header -->
    <?php include('../components/header.php'); ?>

    <!-- Main Content -->
    <main class="pt-6 pb-6">
        <!-- Hero Section -->
        <section class="py-12">
            <div class="container mx-auto px-4 text-center">
                <h1 class="text-4xl md:text-5xl font-extrabold bg-gradient-to-r from-pink-400 to-blue-400 bg-clip-text text-transparent mb-4">
                    Giới Thiệu SUSU Kids
                </h1>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    Chào mừng bạn đến với SUSU Kids - nơi mang đến những bộ quần áo trẻ em đáng yêu, an toàn và chất lượng cao. Chúng tôi tự hào là người bạn đồng hành của các bậc phụ huynh trong hành trình chăm sóc bé yêu!
                </p>
            </div>
        </section>

        <!-- Our Story Section -->
        <section class="py-12 bg-gray-100/90 glass-container">
            <div class="container mx-auto px-4">
                <div class="flex flex-col md:flex-row items-center gap-8">
                    <div class="md:w-1/2">
                        <img src="<?php echo $base_url; ?>/assets/images/about1.jpg" alt="Câu chuyện SUSU Kids" class="w-full h-64 object-cover rounded-lg shadow-lg hover-scale" loading="lazy" />
                    </div>
                    <div class="md:w-1/2">
                        <h2 class="text-3xl font-bold bg-gradient-to-r from-pink-400 to-blue-400 bg-clip-text text-transparent mb-4">
                            Câu Chuyện Của Chúng Tôi
                        </h2>
                        <p class="text-gray-600">
                            SUSU Kids được thành lập từ tình yêu dành cho trẻ em và mong muốn mang đến những sản phẩm tốt nhất cho các bé. Từ những ngày đầu, chúng tôi đã không ngừng nỗ lực để tạo ra những bộ sưu tập quần áo vừa thời trang, vừa thoải mái, giúp các bé tự tin tỏa sáng mỗi ngày.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Our Mission Section -->
        <section class="py-12">
            <div class="container mx-auto px-4 text-center">
                <h2 class="text-3xl font-bold bg-gradient-to-r from-pink-400 to-blue-400 bg-clip-text text-transparent mb-4">
                    Sứ Mệnh Của SUSU Kids
                </h2>
                <p class="text-gray-600 max-w-3xl mx-auto mb-6">
                    Chúng tôi cam kết mang đến những sản phẩm an toàn, chất lượng cao với thiết kế đáng yêu, đồng thời cung cấp trải nghiệm mua sắm dễ dàng và dịch vụ khách hàng tận tâm. SUSU Kids không chỉ bán quần áo, mà còn lan tỏa niềm vui và sự chăm sóc đến từng gia đình.
                </p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white/90 glass-container p-6 rounded-lg shadow-lg hover-scale">
                        <i class="fas fa-heart text-pink-400 text-3xl mb-4"></i>
                        <h3 class="text-xl font-semibold text-gray-800">Tình Yêu Dành Cho Bé</h3>
                        <p class="text-gray-600">Mọi sản phẩm đều được thiết kế với sự yêu thương và chăm chút.</p>
                    </div>
                    <div class="bg-white/90 glass-container p-6 rounded-lg shadow-lg hover-scale">
                        <i class="fas fa-shield-alt text-pink-400 text-3xl mb-4"></i>
                        <h3 class="text-xl font-semibold text-gray-800">Chất Lượng Đảm Bảo</h3>
                        <p class="text-gray-600">Vải an toàn, thân thiện với làn da nhạy cảm của trẻ.</p>
                    </div>
                    <div class="bg-white/90 glass-container p-6 rounded-lg shadow-lg hover-scale">
                        <i class="fas fa-smile text-pink-400 text-3xl mb-4"></i>
                        <h3 class="text-xl font-semibold text-gray-800">Niềm Vui Gia Đình</h3>
                        <p class="text-gray-600">Mang đến nụ cười cho bé và sự hài lòng cho bố mẹ.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Product Highlights Section -->
        <section class="py-12 bg-gray-100/90 glass-container">
            <div class="container mx-auto px-4">
                <h2 class="text-3xl font-bold bg-gradient-to-r from-pink-400 to-blue-400 bg-clip-text text-transparent mb-8 text-center">
                    Sản Phẩm Đa Dạng Của SUSU Kids
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-white/90 rounded-lg shadow-lg overflow-hidden hover-scale">
                        <img src="<?php echo $base_url; ?>/assets/images/vaycongchua1.jpg" alt="Váy bé gái" class="w-full h-48 object-cover" loading="lazy" />
                        <div class="p-4 text-center">
                            <h4 class="text-lg font-semibold text-gray-800">Váy Công Chúa</h4>
                            <p class="text-gray-600">Lộng lẫy cho những dịp đặc biệt.</p>
                        </div>
                    </div>
                    <div class="bg-white/90 rounded-lg shadow-lg overflow-hidden hover-scale">
                        <img src="<?php echo $base_url; ?>/assets/images/aothun4.jpg" alt="Áo thun bé trai" class="w-full h-48 object-cover" loading="lazy" />
                        <div class="p-4 text-center">
                            <h4 class="text-lg font-semibold text-gray-800">Áo Thun Bé Trai</h4>
                            <p class="text-gray-600">Phong cách năng động, chất liệu thoáng mát.</p>
                        </div>
                    </div>
                    <div class="bg-white/90 rounded-lg shadow-lg overflow-hidden hover-scale">
                        <img src="<?php echo $base_url; ?>/assets/images/boquanao1.jpg" alt="Bộ quần áo bé gái" class="w-full h-48 object-cover" loading="lazy" />
                        <div class="p-4 text-center">
                            <h4 class="text-lg font-semibold text-gray-800">Bộ Quần Áo Bé Gái</h4>
                            <p class="text-gray-600">Thiết kế đáng yêu, thoải mái cho bé yêu.</p>
                        </div>
                    </div>
                    <div class="bg-white/90 rounded-lg shadow-lg overflow-hidden hover-scale">
                        <img src="<?php echo $base_url; ?>/assets/images/pijama1.jpg" alt="Pijama cho bé" class="w-full h-48 object-cover" loading="lazy" />
                        <div class="p-4 text-center">
                            <h4 class="text-lg font-semibold text-gray-800">Bộ Pijama Bé</h4>
                            <p class="text-gray-600">Mềm mại, lý tưởng cho giấc ngủ ngon.</p>
                        </div>
                    </div>
                </div>
                <div class="text-center mt-8">
                    <a href="<?php echo $base_url; ?>/pages/category.php" 
                       class="btn-shimmer inline-block bg-gradient-to-r from-pink-400 to-blue-400 text-white py-3 px-6 rounded-lg font-semibold hover:from-pink-500 hover:to-blue-500 focus:outline-none focus:ring-2 focus:ring-pink-400 focus:ring-offset-2 transition duration-300 transform hover:-translate-y-1">
                        Khám Phá Thêm
                    </a>
                </div>
            </div>
        </section>

        <!-- Customer Commitment Section -->
        <section class="py-12">
            <div class="container mx-auto px-4 text-center">
                <h2 class="text-3xl font-bold bg-gradient-to-r from-pink-400 to-blue-400 bg-clip-text text-transparent mb-4">
                    Cam Kết Với Khách Hàng
                </h2>
                <p class="text-gray-600 max-w-2xl mx-auto mb-6">
                    Tại SUSU Kids, sự hài lòng của bạn là ưu tiên hàng đầu. Chúng tôi cung cấp dịch vụ hỗ trợ tận tình, chính sách đổi trả linh hoạt, và giao hàng nhanh chóng để đảm bảo trải nghiệm mua sắm tuyệt vời.
                </p>
                <img src="<?php echo $base_url; ?>/assets/images/about2.jpg" alt="Cam kết khách hàng" class="w-full max-w-2xl mx-auto h-64 object-cover rounded-lg shadow-lg hover-scale" loading="lazy" />
            </div>
        </section>

        <!-- Team Section -->
        <section class="py-12 bg-gray-100/90 glass-container">
            <div class="container mx-auto px-4 text-center">
                <h2 class="text-3xl font-bold bg-gradient-to-r from-pink-400 to-blue-400 bg-clip-text text-transparent mb-4">
                    Đội Ngũ SUSU Kids
                </h2>
                <p class="text-gray-600 max-w-2xl mx-auto mb-6">
                    Đội ngũ của chúng tôi là những người đam mê thời trang trẻ em, luôn nỗ lực để mang đến những sản phẩm tốt nhất và dịch vụ chu đáo nhất cho bạn.
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                    <div class="bg-white/90 glass-container p-6 rounded-lg shadow-lg hover-scale">
                        <img src="<?php echo $base_url; ?>/assets/images/team1.jpg" alt="Thành viên đội ngũ" class="w-24 h-24 rounded-full mx-auto mb-4 object-cover" loading="lazy" />
                        <h3 class="text-lg font-semibold text-gray-800">Nguyễn Thị Thu Cúc</h3>
                        <p class="text-gray-600">Nhà thiết kế</p>
                    </div>
                    <div class="bg-white/90 glass-container p-6 rounded-lg shadow-lg hover-scale">
                        <img src="<?php echo $base_url; ?>/assets/images/team2.jpg" alt="Thành viên đội ngũ" class="w-24 h-24 rounded-full mx-auto mb-4 object-cover" loading="lazy" />
                        <h3 class="text-lg font-semibold text-gray-800">Nguyễn Đức Hiếu</h3>
                        <p class="text-gray-600">Quản lý sản phẩm</p>
                    </div>
                    <div class="bg-white/90 glass-container p-6 rounded-lg shadow-lg hover-scale">
                        <img src="<?php echo $base_url; ?>/assets/images/team3.jpg" alt="Thành viên đội ngũ" class="w-24 h-24 rounded-full mx-auto mb-4 object-cover" loading="lazy" />
                        <h3 class="text-lg font-semibold text-gray-800">Đỗ Thị Thủy Tiên</h3>
                        <p class="text-gray-600">Chăm sóc khách hàng</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Call to Action -->
        <section class="py-12">
            <div class="container mx-auto px-4 text-center">
                <h2 class="text-3xl font-bold bg-gradient-to-r from-pink-400 to-blue-400 bg-clip-text text-transparent mb-4">
                    Tham Gia Cùng SUSU Kids
                </h2>
                <p class="text-gray-600 max-w-2xl mx-auto mb-6">
                    Khám phá ngay bộ sưu tập quần áo trẻ em của chúng tôi và mang đến cho bé yêu những bộ trang phục đáng yêu nhất!
                </p>
                <a href="<?php echo $base_url; ?>/pages/category.php" 
                   class="btn-shimmer inline-block bg-gradient-to-r from-pink-400 to-blue-400 text-white py-3 px-6 rounded-lg font-semibold hover:from-pink-500 hover:to-blue-500 focus:outline-none focus:ring-2 focus:ring-pink-400 focus:ring-offset-2 transition duration-300 transform hover:-translate-y-1">
                    Mua Sắm Ngay
                </a>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <?php include('../components/footer.php'); ?>
</body>
</html>