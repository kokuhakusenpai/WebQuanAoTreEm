document.addEventListener('DOMContentLoaded', function() {
    fetch('https://api.example.com/order-details')
        .then(response => response.json())
        .then(data => {
            // Update order status
            document.getElementById('order-status').innerHTML = `
                <h2 class="text-green-700 font-semibold">${data.orderStatus}</h2>
                <p class="text-sm text-gray-700 mt-1">
                    ${data.deliveryMessage}
                    <span class="text-blue-500">Xem thêm</span>
                </p>
            `;

            // Update shipping information
            document.getElementById('shipping-info').innerHTML = `
                <h3 class="font-semibold">Thông tin vận chuyển</h3>
                <p class="text-sm text-gray-700">${data.shippingInfo}</p>
                <div class="flex items-center mt-2">
                    <i class="fas fa-truck text-gray-500"></i>
                    <p class="text-green-500 ml-2">${data.deliveryStatus}</p>
                </div>
                <p class="text-sm text-gray-500">${data.deliveryDate}</p>
                <div class="flex items-center mt-2">
                    <img alt="Driver's avatar" class="w-10 h-10 rounded-full" height="40" src="${data.driverAvatar}" width="40"/>
                    <p class="ml-2">Chấm điểm cho tài xế?</p>
                    <div class="flex ml-2">
                        <i class="far fa-star text-gray-400"></i>
                        <i class="far fa-star text-gray-400"></i>
                        <i class="far fa-star text-gray-400"></i>
                        <i class="far fa-star text-gray-400"></i>
                        <i class="far fa-star text-gray-400"></i>
                    </div>
                </div>
            `;

            // Update delivery address
            document.getElementById('delivery-address').innerHTML = `
                <h3 class="font-semibold">Địa chỉ nhận hàng</h3>
                <p class="text-sm text-gray-700">
                    <i class="fas fa-map-marker-alt"></i>
                    ${data.deliveryAddress.name} (${data.deliveryAddress.phone})
                </p>
                <p class="text-sm text-gray-700">${data.deliveryAddress.address}</p>
            `;

            // Update product information
            document.getElementById('product-info').innerHTML = `
                <div class="flex items-center">
                    <img alt="Product image" class="w-16 h-16" height="60" src="${data.product.image}" width="60"/>
                    <div class="ml-4">
                        <h4 class="font-semibold">${data.product.name}</h4>
                        <p class="text-sm text-gray-500">${data.product.status}</p>
                        <p class="text-sm text-gray-500">x${data.product.quantity}</p>
                    </div>
                </div>
                <div class="flex justify-between items-center mt-2">
                    <p class="line-through text-gray-500">${data.product.originalPrice}</p>
                    <p class="text-red-500 font-semibold">${data.product.discountedPrice}</p>
                </div>
                <div class="flex justify-between items-center mt-2">
                    <p class="font-semibold">Thành tiền:</p>
                    <p class="text-red-500 font-semibold">${data.totalPrice}</p>
                </div>
            `;
        })
        .catch(error => console.error('Error fetching order details:', error));
});

document.getElementById("home-link").addEventListener("click", function(event) {
    event.preventDefault();
    window.location.href = "trangchu.html";
});

document.addEventListener('DOMContentLoaded', function() {
    fetch('https://api.example.com/order-details') // Gọi API
        .then(response => response.json())
        .then(data => {
            // Cập nhật trạng thái đơn hàng
            document.getElementById('order-status').innerHTML = `
                <h2 class="text-green-700 font-semibold text-lg">🎉 ${data.orderStatus}</h2>
                <p class="text-sm text-gray-700 mt-1">${data.deliveryMessage}</p>
            `;

            // Cập nhật thông tin vận chuyển
            document.getElementById('shipping-info').innerHTML = `
                <h3 class="font-semibold text-gray-800">Thông tin vận chuyển</h3>
                <p class="text-sm text-gray-600">SPX Express: <span class="font-medium text-gray-800">${data.shippingCode}</span></p>
                <div class="flex items-center mt-2 text-pink-500">
                    <i class="fas fa-truck"></i>
                    <p class="ml-2">${data.deliveryStatus}</p>
                </div>
                <p class="text-sm text-gray-500">📅 ${data.deliveryDate}</p>
            `;

            // Cập nhật địa chỉ nhận hàng
            document.getElementById('delivery-address').innerHTML = `
                <h3 class="font-semibold text-gray-800">📍Địa chỉ nhận hàng</h3>
                <p class="text-sm text-gray-700"><i class="fas fa-user-circle"></i> ${data.customerName} (${data.phone})</p>
                <p class="text-sm text-gray-700">🏠 ${data.address}</p>
            `;

            // Cập nhật thông tin sản phẩm
            document.getElementById('product-info').innerHTML = `
                <div class="flex items-center">
                    <img class="w-16 h-16 rounded-lg shadow" src="${data.product.image}" alt="Product">
                    <div class="ml-4">
                        <h4 class="font-semibold text-gray-800">${data.product.name}</h4>
                        <p class="text-sm text-gray-500">Size: ${data.product.size} | Màu: ${data.product.color}</p>
                        <p class="text-sm text-gray-500">x${data.product.quantity}</p>
                    </div>
                </div>
                <div class="flex justify-between items-center mt-2">
                    <p class="line-through text-gray-500">₫${data.product.originalPrice}</p>
                    <p class="text-red-500 font-semibold">₫${data.product.discountedPrice}</p>
                </div>
            `;
        })
        .catch(error => console.error('Lỗi khi lấy dữ liệu đơn hàng:', error));
});