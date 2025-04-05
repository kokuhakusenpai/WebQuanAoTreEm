<?php
// Kết nối cơ sở dữ liệu
$pdo = new PDO('mysql:host=localhost;dbname=baby_shop;charset=utf8', 'root', '');

// Truy vấn sản phẩm bán chạy
$sql_best_seller = "SELECT * FROM products WHERE is_best_seller = 1 ORDER BY price DESC LIMIT 4";
$stmt_best_seller = $pdo->prepare($sql_best_seller);
$stmt_best_seller->execute();
$best_sellers = $stmt_best_seller->fetchAll(PDO::FETCH_ASSOC);

// Truy vấn sản phẩm nổi bật
$sql_featured = "SELECT * FROM products WHERE is_featured = 1 ORDER BY price DESC LIMIT 4";
$stmt_featured = $pdo->prepare($sql_featured);
$stmt_featured->execute();
$featured_products = $stmt_featured->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Shop Online</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header >
        <div class="logo">
            <img class="img-fluid"
                 src="images/logo1.jpg" 
                 alt="logo ABC Babyshop"
                 width="134"
                 height="45">
        </div>
        <div class="dropacc">
            <a href="#">Bé gái</a>
            <div class="dropacc-content">
                <a href="">Váy đầm bé gái</a>
                <a href="">Áo bé gái</a>
                <a href="">Áo khoác bé gái</a>
                <a href="">Quần trẻ em gái</a>
                <a href="">Đồ bộ bé gái</a>
                <a href="">Đồ bơi bé gái</a>
            </div>
        </div> 
        <div class="dropacc">
            <a href="#">Bé trai</a>
            <div class="dropacc-content">
                <a href="">Áo thun bé trai</a>
                <a href="">Áo sơ mi bé trai</a>
                <a href="">Áo khoác bé trai</a>
                <a href="">Quần kiểu bé trai</a>
                <a href="">Quần jeans bé trai</a>
                <a href="">Đồ bộ bé trai</a>
                <a href="">Đồ bơi bé trai</a>
                <a href="">Quần lót bé trai</a>
                <a href="">Phụ kiện bé trai</a>
            </div>
        </div> 
        <a href="#">Đồ dùng mẹ và bé</a>
        <a href="#">Trẻ em</a>

        <div class="search-bar" id="searchBar"> 
        <input type="text" id="searchInput" placeholder="Tìm kiếm sản phẩm...">
        <button onclick="searchProducts()">
          <svg xmlns="http://www.w3.org/2000/svg" height="24" width="24" viewBox="0 0 512 512">
            <path d="M416 208c0 45.9-14.9 88.3-40 122.7L502.6 457.4c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L330.7 376c-34.4 25.2-76.8 40-122.7 40C93.1 416 0 322.9 0 208S93.1 0 208 0S416 93.1 416 208zM208 352a144 144 0 1 0 0-288 144 144 0 1 0 0 288z"/>
          </svg>
        </button>
        </div>

        <div class="dropacc">
          <button class="dropaccbtn">
              <svg xmlns="http://www.w3.org/2000/svg" height="20" width="20.75" viewBox="0 0 448 512">
                  <path d="M224 256A128 128 0 1 0 224 0a128 128 0 1 0 0 256zm-45.7 48C79.8 304 0 383.8 0 482.3C0 498.7 13.3 512 29.7 512h388.6c16.4 0 29.7-13.3 29.7-29.7C448 383.8 368.2 304 269.7 304h-91.4z"/>
              </svg>
          </button>
          <div class="dropacc-content" id="loginMenu">
            <a href="login.html">Đăng nhập</a>
            <a href="register.html">Đăng ký</a>  
          </div>
        </div>
            
      <div class="nav-item">
        <a href="#">
          <svg xmlns="http://www.w3.org/2000/svg" height="20" width="20.75" viewBox="0 0 448 512">
            <path d="M224 512a64 64 0 0 0 64-64H160a64 64 0 0 0 64 64zm215.03-149.25c-20.38-24.38-55.25-61.75-55.25-175.75 0-91.5-62.75-167.75-144-188.25V0c0-17.67-14.33-32-32-32s-32 14.33-32 32v9.75c-81.25 20.5-144 96.75-144 188.25 0 114-34.88 151.38-55.25 175.75-6.75 8-8.75 18.88-5.38 28.5C29.12 400.5 39.75 408 51.25 408h345.5c11.5 0 22.12-7.5 25.63-17.75 3.38-9.62 1.38-20.5-5.35-28.5z"/>
          </svg>
        </a>
      </div>
      <div class="header-icons">
        <a href="#">
          <svg xmlns="http://www.w3.org/2000/svg" height="20" width="20.75" viewBox="0 0 576 512">
            <path d="M528.1 301.3L576 93.3c1.6-6.2 .3-12.8-3.5-18.2s-9.8-8-16.1-8H120l-9.4-41.5C107.4 13.3 96.4 4 83.7 4H8C3.6 4 0 7.6 0 12s3.6 8 8 8h75.7c4.2 0 7.8 2.7 9 6.6L148 317.4c1.7 7.4 8.2 12.6 15.8 12.6h306.6c7.6 0 14.1-5.2 15.8-12.6l10.8-47.2h30.2c8.3 0 15.4-6 16.9-14.2zM432 480a48 48 0 1 0 96 0 48 48 0 1 0 -96 0zM160 432a48 48 0 1 0 96 0 48 48 0 1 0 -96 0z"/>
          </svg>
        </a> 
      </div>
    </header>

    <section id="Slider">
      <div class="aspect-ratio-169">
        <img src="images/slider_1.webp" class="slide active" />
        <img src="images/Ảnh9.jpg" class="slide" />
        <img src="images/slider_5.webp" class="slide" />
      </div>
      <div class="dot-container">
        <span class="dot active" onclick="changeSlide(0)"></span>
        <span class="dot" onclick="changeSlide(1)"></span>
        <span class="dot" onclick="changeSlide(2)"></span>
      </div>
    </section>

    <div class="main-content">
    <h2>Voucher tháng 3</h2>
      <div class="voucher-container">
          <div class="voucher" onclick="toggleVoucher(1)">
              <div class="voucher-title">NHẬP MÃ: BABI15</div>
              <div class="voucher-details" id="voucher-1">
                  Giảm 15,000đ khi mua hóa đơn từ 0đ
              </div>
          </div>
          <div class="voucher" onclick="toggleVoucher(2)">
              <div class="voucher-title">NHẬP MÃ: BABI50</div>
              <div class="voucher-details" id="voucher-2">
              Giảm 50,000đ khi mua hóa đơn từ 550,000đ
              </div>
          </div>
      </div>

      <h2>Ưu đãi tháng 3</h2>

      <div class="product-list">
        <div class="product-list">
          <?php foreach($best_sellers as $product): ?>
              <div class="product">
                  <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" />
                  <p><?php echo htmlspecialchars($product['name']); ?></p>
                  <p class="price"><?php echo number_format($product['price'], 0, ',', '.'); ?>₫ <del><?php echo number_format($product['discount_price'], 0, ',', '.'); ?>₫</del></p>
              </div>
          <?php endforeach; ?>
      </div>

      <h2>Sản phẩm nổi bật</h2>
      <div class="product-list">
          <?php foreach($featured_products as $product): ?>
              <div class="product">
                  <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" />
                  <p><?php echo htmlspecialchars($product['name']); ?></p>
                  <p class="price"><?php echo number_format($product['price'], 0, ',', '.'); ?>₫ <del><?php echo number_format($product['discount_price'], 0, ',', '.'); ?>₫</del></p>
              </div>
          <?php endforeach; ?>
      </div>
    </div>

    <footer>
      <p>BABY Store</p>
      <p>Địa chỉ: 123 Đường ABC, Hà Nội</p>
      <p>Điện thoại:0123456789</p>
    </footer>

    <div class="chat-container">
      <div class="chat-icon" onclick="toggleChat()">💬</div>
      <div class="chat-box" id="chatBox">
          <div class="chat-header">
              <span>💬 Hỗ trợ khách hàng</span>
              <button class="close-btn" onclick="toggleChat()">❌</button>
          </div>
          <div class="chat-body" id="chatBody">
              <p><strong>Bot:</strong> Xin chào! Bạn cần hỗ trợ gì?</p>
          </div>
          <div class="chat-input">
              <input type="text" id="userInput" placeholder="Nhập câu hỏi...">
              <button onclick="sendMessage()">Gửi</button>
          </div>
          <div class="chat-links">
              <a href="https://m.me/yourpage" target="_blank">
                  <img src="images/messenger.webp" alt="Chat Messenger"> <!-- lưu ý đổi đường link-->
              </a>
              <a href="https://chat.zalo.me/" target="_blank">
                  <img src="images/addthis-zalo.svg" alt="Chat Zalo">
              </a>
          </div>
      </div>
  </div>

    <script>
      function searchProducts(){
        var input = document.getElementById("searchInput").value;
        // Chuyển hướng đến trang search.php với tham số truy vấn (query)
        window.location.href = "search.php?query=" + encodeURIComponent(input);
      }

      function toggleChat() {
        const chatBox = document.getElementById("chatBox");
        chatBox.style.display =
          chatBox.style.display === "none" || chatBox.style.display === ""
            ? "block"
            : "none";
      }

      function sendMessage() {
        const userInput = document.getElementById("userInput").value;
        const chatBody = document.getElementById("chatBody");

        if (userInput.trim() === "") return;

        chatBody.innerHTML += `<p><strong>Bạn:</strong> ${userInput}</p>`;
        document.getElementById("userInput").value = "";

        setTimeout(() => {
          chatBody.innerHTML += `<p><strong>Bot:</strong> ${getResponse(
            userInput
          )}</p>`;
          chatBody.scrollTop = chatBody.scrollHeight;
        }, 500);
      }

function getResponse(question) {
    const faq = {
        "shop có miễn phí vận chuyển không?": "Shop miễn phí vận chuyển cho đơn hàng từ 500.000đ trở lên.",
        "thời gian giao hàng bao lâu?": "Thời gian giao hàng từ 2-5 ngày tùy khu vực.",
        "shop có đổi trả không?": "Shop hỗ trợ đổi trả trong vòng 7 ngày nếu sản phẩm lỗi hoặc không đúng mô tả.",
        "shop có size cho bé 2 tuổi không?": "Shop có đầy đủ size cho bé từ sơ sinh đến 12 tuổi.",
        "cách đặt hàng thế nào?": "Bạn có thể đặt hàng trực tiếp trên website hoặc inbox fanpage của shop."
    };

    return faq[question.toLowerCase()] || "Xin lỗi, shop chưa có thông tin cho câu hỏi này. Bạn có thể liên hệ trực tiếp để được hỗ trợ!";
}
    </script>
    <script src="js/slider.js"></script>
  </body>
</html>

