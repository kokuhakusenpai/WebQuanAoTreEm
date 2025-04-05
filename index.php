<!-- filepath: c:\xampp\htdocs\WebQuanAoTreEmMain\index.php -->
<?php
session_start();
$username = isset($_COOKIE['username']) ? $_COOKIE['username'] : null;
?>
<main>
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

        <div class="user-account">
          <button class="user-accountbtn">
              <svg xmlns="http://www.w3.org/2000/svg" height="20" width="20.75" viewBox="0 0 448 512">
                  <path d="M224 256A128 128 0 1 0 224 0a128 128 0 1 0 0 256zm-45.7 48C79.8 304 0 383.8 0 482.3C0 498.7 13.3 512 29.7 512h388.6c16.4 0 29.7-13.3 29.7-29.7C448 383.8 368.2 304 269.7 304h-91.4z"/>
              </svg>
          </button>
           <!-- Hiển thị thông tin người dùng nếu đã đăng nhập -->
          <div class="user-info" id="userInfo">
            <a href="dh.html"><?php echo $username ? htmlspecialchars($username) : 'bạn'; ?></a>
            <a>Thông tin</a>
            <a href="logout.php">Đăng xuất</a>
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
    <?php include 'trangchu.html'; ?>
</main>
<style>
/* Dropdown Styling */
.user-account {
  position: relative;
  display: inline-block;
}

button.user-accountbtn {
  background-color: #fff; /* Light pink */
  color: black;
  padding: 10px;
  font-size: 16px;
  border: none;
  cursor: pointer;
  border-radius: 5px;
  transition: background-color 0.3s;
}

.button.user-accountbtn:hover {
  background-color: #0288D1; /* Darker pink */
}

.user-info{
  display: none;
  position: absolute;
  background-color: #fff;
  min-width: 160px;
  box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
  z-index: 1;
}

.user-info a {
  color: black;
  padding: 10px;
  text-decoration: none;
  display: block;
}

.user-info a:hover {
  background-color: #81D4FA;
}

.user-account:hover .user-info {
  display: block;
}
</style>
<script>
      function searchProducts(){
        var input = document.getElementById("searchInput").value;
        // Chuyển hướng đến trang search.php với tham số truy vấn (query)
        window.location.href = "search.php?query=" + encodeURIComponent(input);
      }
    </script>
<script src="js/script.js"></script>