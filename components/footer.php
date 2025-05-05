<footer class="footer-gradient text-gray-700 py-12">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
            <!-- Phần 1: Thông tin cửa hàng -->
             <div class="fade-in">
                <h3 class="text-2xl font-bold mb-4 text-gray-800">SUSU KIDS</h3>
                <p class="text-gray-600 mb-4">Cung cấp quần áo, phụ kiện và đồ chơi chất lượng cao cho trẻ em với thiết kế dễ thương và an toàn.</p>
                <div class="flex gap-4">
                    <a href="https://facebook.com" target="_blank" class="text-gray-600 hover:text-pink-400 transition duration-300">
                        <i class="fab fa-facebook-f text-xl"></i>
                    </a>
                    <a href="https://instagram.com" target="_blank" class="text-gray-600 hover:text-pink-400 transition duration-300">
                        <i class="fab fa-instagram text-xl"></i>
                    </a>
                </div>
            </div>
                
            <!-- Phần 2: Liên Kết Nhanh -->
             <div class="fade-in">
                <h3 class="text-2xl font-bold mb-4 text-gray-800">Liên Kết Nhanh</h3>
                <ul class="space-y-2" role="navigation" aria-label="Menu phụ">
                    <li><a href="<?php echo $base_url; ?>/pages/news.php" class="text-gray-600 hover:text-pink-400 transition-colors">Tin tức</a></li>
                    <li><a href="<?php echo $base_url; ?>/size-guide.html" class="text-gray-600 hover:text-pink-400 transition-colors">Hướng dẫn chọn size</a></li>
                    <li><a href="<?php echo $base_url; ?>/return-policy.html" class="text-gray-600 hover:text-pink-400 transition-colors">Chính sách đổi trả</a></li>
                    <li><a href="<?php echo $base_url; ?>/privacy-policy.html" class="text-gray-600 hover:text-pink-400 transition-colors">Chính sách bảo mật</a></li>
                </ul>
            </div>
                
            <!-- Phần 3: Liên Hệ -->
             <div class="fade-in">
                <h3 class="text-2xl font-bold mb-4 text-gray-800">Liên Hệ</h3>
                <p class="text-gray-600 mb-2"><i class="fas fa-envelope mr-2 text-pink-400"></i> Email: support@susukids.com</p>
                <p class="text-gray-600 mb-2"><i class="fas fa-phone mr-2 text-pink-400"></i> Hotline: 0123 456 789</p>
                <p class="text-gray-600"><i class="fas fa-map-marker-alt mr-2 text-pink-400"></i> Địa chỉ: 123 Đường ABC, TP. Hà Nội</p>
            </div>
        </div>
    </div>
</footer>

<!-- Script cho hiệu ứng cuộn -->
<script>
    // Hiệu ứng fade-in khi cuộn
    document.addEventListener('DOMContentLoaded', function() {
        const elements = document.querySelectorAll('.fade-in');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, { threshold: 0.1 });
        elements.forEach(element => observer.observe(element));
    });
</script>

<!-- Thêm Font Awesome để sử dụng biểu tượng -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />

<style>
    /* Hiệu ứng fade-in khi cuộn */
    .fade-in {
        opacity: 0;
        transform: translateY(20px);
        transition: opacity 0.6s ease-out, transform 0.6s ease-out;
    }
    .fade-in.visible {
        opacity: 1;
        transform: translateY(0);
    }
    
    /* Màu gradient nhạt hơn */
    .footer-gradient {
        background-image: linear-gradient(to bottom, #fce7f3, #dbeafe);
    }
    
    /* Hover effect cho các liên kết */
    .transition-colors {
        transition: color 0.3s ease;
    }
</style>