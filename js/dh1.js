// Danh sách đơn hàng giả lập
const orders = [
    {
        id: "250301FHHPY85T",
        date: "12-03-2025",
        total: "₫399.000",
        products: [
            { name: "Áo bé gái dễ thương", image: "https://placehold.co/60x60" },
            { name: "Quần short bé trai", image: "https://placehold.co/60x60" }
        ]
    },
    {
        id: "250302GHJTY99K",
        date: "10-03-2025",
        total: "₫259.000",
        products: [
            { name: "Váy công chúa", image: "https://placehold.co/60x60" }
        ]
    }
];

// Hiển thị danh sách đơn hàng
const ordersContainer = document.getElementById("orders");
orders.forEach(order => {
    const orderElement = document.createElement("div");
    orderElement.classList = "p-4 bg-white rounded-lg shadow-md";
    orderElement.innerHTML = `
        <h2 class="text-lg font-semibold text-gray-800">Mã đơn: ${order.id}</h2>
        <p class="text-sm text-gray-600">📅 Ngày đặt: ${order.date}</p>
        <p class="text-sm text-gray-800 font-medium">💰 Tổng tiền: ${order.total}</p>
        <div class="flex gap-2 mt-2">
            ${order.products.map(p => `<img src="${p.image}" alt="${p.name}" class="w-12 h-12 rounded-md">`).join(" ")}
        </div>
        <button onclick="viewOrder('${order.id}')" class="mt-2 block w-full text-center bg-pink-500 text-white py-1 rounded-md">Xem chi tiết</button>
    `;
    ordersContainer.appendChild(orderElement);
});

function viewOrder(orderId) {
    localStorage.setItem("selectedOrderId", orderId);
    window.location.href = "order_detail.html";
}

document.addEventListener("DOMContentLoaded", function () {
    const avatarInput = document.getElementById("upload-avatar");
    const avatarImage = document.getElementById("avatar");

    if (!avatarInput || !avatarImage) {
        console.error("Không tìm thấy phần tử upload-avatar hoặc avatar.");
        return;
    }

    avatarInput.addEventListener("change", function (event) {
        let file = event.target.files[0];
        if (file) {
            let reader = new FileReader();
            reader.onload = function (e) {
                avatarImage.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });
});

// Danh sách sản phẩm giả lập
const allProducts = [
    { name: "Áo bé gái dễ thương", category: "Áo", image: "https://placehold.co/80x80" },
    { name: "Quần short bé trai", category: "Quần", image: "https://placehold.co/80x80" },
    { name: "Váy công chúa", category: "Váy", image: "https://placehold.co/80x80" },
    { name: "Áo hoodie trẻ em", category: "Áo", image: "https://placehold.co/80x80" },
    { name: "Quần dài bé gái", category: "Quần", image: "https://placehold.co/80x80" },
];

// Lấy sản phẩm từ đơn hàng cũ của khách hàng
let purchasedCategories = new Set();
orders.forEach(order => {
    order.products.forEach(product => {
        let category = allProducts.find(p => p.name === product.name)?.category;
        if (category) purchasedCategories.add(category);
    });
});

// Lọc gợi ý sản phẩm dựa trên danh mục đã mua
let suggestedProducts = allProducts.filter(p => purchasedCategories.has(p.category) && !orders.some(order => order.products.some(prod => prod.name === p.name)));

// Hiển thị gợi ý sản phẩm
const suggestionsContainer = document.getElementById("suggestions");
suggestedProducts.forEach(product => {
    const productElement = document.createElement("div");
    productElement.classList = "w-20 text-center";
    productElement.innerHTML = `
        <img src="${product.image}" class="rounded-md w-20 h-20">
        <p class="text-sm">${product.name}</p>
    `;
    suggestionsContainer.appendChild(productElement);
});

// Hiển thị đánh giá mẫu
const reviewsContainer = document.getElementById("reviews");
const reviews = [
    { name: "Nguyễn A", rating: "⭐⭐⭐⭐⭐", comment: "Chất lượng tuyệt vời!" },
    { name: "Trần B", rating: "⭐⭐⭐⭐", comment: "Hàng đẹp, đúng như mô tả." }
];

if (reviewsContainer) {
    reviewsContainer.innerHTML = reviews.map(r => `
        <div class="border-b py-2">
            <p class="text-sm font-medium">${r.name} - ${r.rating}</p>
            <p class="text-sm text-gray-600">${r.comment}</p>
        </div>
    `).join("");
}

// Hiển thị voucher ngẫu nhiên
const vouchers = ["Giảm 10% đơn hàng", "Freeship toàn quốc", "Giảm ₫50K cho đơn từ ₫500K"];
const voucherContainer = document.getElementById("voucher");
if (voucherContainer) {
    voucherContainer.textContent = vouchers[Math.floor(Math.random() * vouchers.length)];
}

function showOrderHistory() {
    document.getElementById("content").innerHTML = `
        <h1 class="text-2xl font-semibold text-gray-700">📦 Đơn hàng</h1>
        <div class="mt-4 space-y-4">
            <!-- Đơn hàng 1 -->
            <div class="bg-white p-4 rounded-xl shadow-md hover:shadow-lg transition-shadow">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Đơn hàng #250301FHHPY85T</h3>
                        <p class="text-sm text-gray-500">📅 Ngày đặt: 12-03-2025</p>
                        <p class="text-sm text-gray-500">💰 Tổng tiền: <span class="text-red-500 font-semibold">₫199.000</span></p>
                        <p class="text-sm text-green-500 font-medium mt-1">✅ Trạng thái: Đã giao</p>
                    </div>
                    <button onclick="showOrderDetails('250301FHHPY85T')" class="px-4 py-2 bg-blue-500 hover:bg-pink-600 text-white rounded-lg transition">Xem chi tiết</button>
                </div>
            </div>

            <!-- Đơn hàng 2 -->
            <div class="bg-white p-4 rounded-xl shadow-md hover:shadow-lg transition-shadow">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Đơn hàng #250302GHJTY99K</h3>
                        <p class="text-sm text-gray-500">📅 Ngày đặt: 10-03-2025</p>
                        <p class="text-sm text-gray-500">💰 Tổng tiền: <span class="text-red-500 font-semibold">₫259.000</span></p>
                        <p class="text-sm text-yellow-500 font-medium mt-1">⏳ Trạng thái: Đang xử lý</p>
                    </div>
                    <button onclick="showOrderDetails('250302GHJTY99K')" class="px-4 py-2 bg-blue-500 hover:bg-pink-600 text-white rounded-lg transition">Xem chi tiết</button>
                </div>
            </div>
        </div>
    `;
}

function showOrderDetails(orderId) {
    document.getElementById("content").innerHTML = `
        <h1 class="text-2xl font-semibold text-gray-700">📦 Chi tiết đơn hàng ${orderId}</h1>
        
        <!-- Thông tin đơn hàng -->
        <div class="mt-4 bg-white p-6 rounded-lg shadow-md">
            <p class="text-gray-600">📅 Ngày đặt: 12-03-2025</p>
            <p class="text-gray-600">💰 Tổng tiền: <span class="text-red-500 font-semibold">₫199.000</span></p>
            <p class="text-green-500 font-medium">✅ Trạng thái: Đã giao</p>
            
            <hr class="my-4">

            <!-- Danh sách sản phẩm -->
            <h3 class="text-lg font-semibold">🛒 Sản phẩm trong đơn hàng</h3>
            <div class="mt-2 space-y-2">
                <div class="flex items-center justify-between bg-gray-100 p-3 rounded-md">
                    <span>👕 Áo thun bé gái</span>
                    <span>₫99.000</span>
                </div>
                <div class="flex items-center justify-between bg-gray-100 p-3 rounded-md">
                    <span>👖 Quần jean bé trai</span>
                    <span>₫100.000</span>
                </div>
            </div>

            <hr class="my-4">

            <!-- Tiến trình đơn hàng -->
            <h3 class="text-lg font-semibold">⏳ Tiến trình giao hàng</h3>
            <div class="mt-2">
                <div class="flex items-center justify-between text-sm font-medium text-gray-600">
                    <span>📦 Chờ xác nhận</span>
                    <span>🚚 Đang giao</span>
                    <span>✅ Hoàn thành</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2.5 mt-1">
                    <div class="bg-pink-500 h-2.5 rounded-full" style="width: 100%;"></div> 
                </div>
            </div>

            <hr class="my-4">

            <!-- Đánh giá đơn hàng -->
            <h3 class="text-lg font-semibold">⭐ Đánh giá sản phẩm</h3>
            <div id="reviews" class="mt-2">
                <p class="text-sm text-gray-600">Chưa có đánh giá nào.</p>
            </div>
            <button onclick="addReview('${orderId}')" class="mt-2 block w-full text-center bg-pink-500 text-white py-1 rounded-md">Thêm đánh giá</button>

            <!-- Nút quay lại -->
            <button onclick="showOrderHistory()" class="mt-4 px-4 py-2 bg-gray-500 text-white rounded-lg">⬅ Quay lại</button>
        </div>
    `;
}

function addReview(orderId) {
    let reviewBox = document.getElementById("reviews");
    reviewBox.innerHTML = `
        <textarea id="reviewText" class="w-full p-2 border rounded-md" placeholder="Nhập đánh giá của bạn..."></textarea>
        <button onclick="submitReview('${orderId}')" class="mt-2 px-4 py-2 bg-green-500 text-white rounded-md">Gửi đánh giá</button>
    `;
}

function submitReview(orderId) {
    let reviewText = document.getElementById("reviewText").value;
    if (reviewText.trim() === "") {
        alert("Vui lòng nhập đánh giá!");
        return;
    }
    
    document.getElementById("reviews").innerHTML = `
        <p class="text-sm text-gray-600">📝 ${reviewText}</p>
        <p class="text-xs text-gray-400">Cảm ơn bạn đã đánh giá!</p>
    `;
}


// thông tin cá nhân
function showProfile() {
    document.getElementById("content").innerHTML = `
        <h1 class="text-2xl font-semibold text-gray-700">👤 Thông tin cá nhân</h1>
        <div class="mt-4 p-4 bg-white shadow-md rounded-lg">
            <div class="flex flex-col items-center">
                <img id="profile-avatar" src="https://placehold.co/120x120" class="w-24 h-24 rounded-full border-2 border-gray-300" alt="Avatar">
                <input type="file" id="upload-avatar" class="hidden" accept="image/*">
                <label for="upload-avatar" class="mt-2 px-3 py-1 bg-pink-500 text-white rounded-lg cursor-pointer">📷 Đổi ảnh đại diện</label>
            </div>
            <div class="mt-4">
                <label class="text-gray-700 font-semibold">👤 Họ và Tên</label>
                <input type="text" class="w-full mt-1 p-2 border rounded-lg" value="Nguyễn Văn A">
            </div>
            <div class="mt-4">
                <label class="text-gray-700 font-semibold">📧 Email</label>
                <input type="email" class="w-full mt-1 p-2 border rounded-lg" value="nguyenvana@example.com">
            </div>
            <div class="mt-4">
                <label class="text-gray-700 font-semibold">📱 Số điện thoại</label>
                <input type="text" class="w-full mt-1 p-2 border rounded-lg" value="0987654321">
            </div>
            <div class="mt-4">
                <button class="w-full px-4 py-2 bg-pink-500 hover:bg-pink-600 text-white rounded-lg transition">💾 Lưu thông tin</button>
            </div>
        </div>
    `;
}



