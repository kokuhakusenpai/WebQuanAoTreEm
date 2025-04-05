document.addEventListener("DOMContentLoaded", function () {
    const menuItems = document.querySelectorAll(".category-menu ul li > a");
    
    menuItems.forEach((item) => {
      item.addEventListener("click", function (event) {
        event.preventDefault();
        const parent = this.parentElement;
    
        // Đóng tất cả menu khác trước khi mở menu được chọn
        document.querySelectorAll(".category-menu ul li").forEach((li) => {
          if (li !== parent) {
            li.classList.remove("active");
          }
        });
    
          // Toggle class active để hiển thị danh mục con
          parent.classList.toggle("active");
        });
      });
    });

    // Xử lý tìm kiếm sản phẩm
  function searchProducts() {
    const input = document.getElementById("searchInput").value;
    window.location.href = "search.php?query=" + encodeURIComponent(input);
  }

  function toggleVoucher(id) {
      var details = document.getElementById("voucher-" + id);
      details.classList.toggle("active");
  }
  
  //Trang cá nhân
  document.getElementById('edit-btn').addEventListener('click', function() {
    document.querySelectorAll('input').forEach(input => input.disabled = false);
    document.getElementById('edit-btn').style.display = 'none';
    document.getElementById('save-btn').style.display = 'block';
  });
  
  document.getElementById('profile-form').addEventListener('submit', function(event) {
    event.preventDefault();
    alert('Thông tin đã được lưu!');
    document.querySelectorAll('input').forEach(input => input.disabled = true);
    document.getElementById('edit-btn').style.display = 'block';
    document.getElementById('save-btn').style.display = 'none';
  });

  //Tìm kiếm sản phẩm
    function searchProducts() {
      const query = document.getElementById("searchInput").value.trim();
      const productContainer = document.getElementById("productContainer");
  
      if (query === "") {
          alert("Vui lòng nhập từ khóa tìm kiếm.");
          return;
      }
  
      fetch(`search.php?query=${encodeURIComponent(query)}`)
          .then(response => response.json())
          .then(data => {
              if (data.success) {
                  productContainer.innerHTML = ""; // Xóa kết quả cũ
                  data.products.forEach(product => {
                      const productElement = document.createElement("div");
                      productElement.classList.add("product");
  
                      productElement.innerHTML = `
                          <img src="${product.image_url}" alt="${product.name}">
                          <h3>${product.name}</h3>
                          <p>${product.description}</p>
                          <p>Giá: ${product.discount_price || product.price}đ</p>
                          <a href="product_detail.php?product_id=${product.product_id}">Xem chi tiết</a>
                      `;
  
                      productContainer.appendChild(productElement);
                  });
              } else {
                  productContainer.innerHTML = `<p>${data.message}</p>`;
              }
          })
          .catch(error => console.error("Lỗi tìm kiếm:", error));
  }