// Hàm tải danh sách voucher từ API
async function loadVouchers() {
  try {
    const response = await fetch('PHP/get_vouchers.php'); // Gọi API PHP
    const vouchers = await response.json(); // Chuyển đổi dữ liệu JSON

    const container = document.getElementById('voucher-container');
    container.innerHTML = ''; // Xóa nội dung cũ

    vouchers.forEach((voucher, index) => {
      // Tạo HTML cho từng voucher
      const voucherDiv = document.createElement('div');
      voucherDiv.className = 'bg-sky-200 px-4 py-3 rounded-lg shadow-md text-center w-52 cursor-pointer hover:bg-sky-300 hover:scale-105 transform transition-all duration-300';
      voucherDiv.setAttribute('onclick', `toggleVoucher(${index})`);

      voucherDiv.innerHTML = `
        <div class="font-bold text-blue-700">NHẬP MÃ: ${voucher.code}</div>
        <div class="text-sm text-gray-700 mt-2 hidden voucher-details" id="voucher-${index}">
          ${voucher.description}
        </div>
      `;

      container.appendChild(voucherDiv);
    });
  } catch (error) {
    console.error('Lỗi khi tải danh sách voucher:', error);
  }
}

// Gọi hàm khi trang được tải
document.addEventListener('DOMContentLoaded', loadVouchers);

// Hàm hiển thị hoặc ẩn thông tin voucher
function toggleVoucher(index) {
  const voucherDetails = document.getElementById(`voucher-${index}`);
  if (voucherDetails.classList.contains('hidden')) {
    voucherDetails.classList.remove('hidden');
  } else {
    voucherDetails.classList.add('hidden');
  }
}