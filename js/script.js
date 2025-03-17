document.addEventListener("DOMContentLoaded", function () {
  const menuItems = document.querySelectorAll("nav > ul > li > a");

  menuItems.forEach((item) => {
    item.addEventListener("click", function (event) {
      const submenu = this.nextElementSibling;
      if (submenu && submenu.classList.contains("top-menu-item")) {
        event.preventDefault();
        submenu.classList.toggle("show");
      }
    });
  });
});
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
//quantity-product
const quanPlus = document.querySelector(".ri-add-line");
const quanMinus = document.querySelector(".ri-subtract-line");
const quanInput = document.querySelector(".quantity-input");
let qty = 1;
quanPlus.addEventListener("click", () => {
  inputValue = qty++;
  quanInput.value = qty;
});
quanMinus.addEventListener("click", () => {
  if (qty <= 1) {
    return false;
  } else {
    inputValue = qty--;
    quanInput.value = qty;
  }
});