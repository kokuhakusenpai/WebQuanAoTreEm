let cartItems = [
    { id: 1, name: "Áo thun bé trai", price: 150000, quantity: 2, image: "https://placehold.co/100x100" },
    { id: 2, name: "Đầm công chúa bé gái", price: 300000, quantity: 1, image: "https://placehold.co/100x100" }
    ];
    
    function updateCartUI() {
        const cartContainer = document.getElementById("cart-container");
        const totalItems = document.getElementById("total-items");
            let total = 0;
            cartContainer.innerHTML = "";
    
            cartItems.forEach((item, index) => {
                total += item.price * item.quantity;
                cartContainer.innerHTML += `
                    <div class="flex justify-between items-center bg-white p-4 rounded-lg shadow-md mt-2">
                        <img src="${item.image}" alt="${item.name}" class="w-20 h-20 rounded-lg shadow-sm">
                        <div class="flex-1 ml-4">
                            <h2 class="text-lg font-semibold text-gray-800">${item.name}</h2>
                            <p class="text-gray-600">Giá: <span class="font-bold">${item.price.toLocaleString()}₫</span></p>
                            <div class="flex items-center mt-2 space-x-2">
                                <button class="px-3 py-1 border rounded-lg bg-gray-200 hover:bg-gray-300" onclick="updateQuantity(${index}, -1)">-</button>
                                <span class="px-4 text-lg font-medium">${item.quantity}</span>
                                <button class="px-3 py-1 border rounded-lg bg-gray-200 hover:bg-gray-300" onclick="updateQuantity(${index}, 1)">+</button>
                            </div>
                        </div>
                        <button class="bg-white text-pink-300 border border-pink-300 px-2 py-1 rounded-md hover:bg-pink-300 hover:text-white transition" onclick="removeItem(${index})">
                            ❌
                        </button>        
                    </div>
                `;
            });
            totalItems.innerText = cartItems.length;
            document.getElementById("subtotal").innerText = total.toLocaleString() + "₫";
            document.getElementById("grand-total").innerText = (total + 12000).toLocaleString() + "₫"; // Thêm phí vận chuyển
        }

        function updateQuantity(index, change) {
            cartItems[index].quantity = Math.max(1, cartItems[index].quantity + change);
            updateCartUI();
        }
    
        function removeItem(index) {
            cartItems.splice(index, 1);
            updateCartUI();
        }
        updateCartUI();

        // Hàm lấy số lượng sản phẩm từ localStorage (nếu có)
        function updateCartCount() {
            let cart = JSON.parse(localStorage.getItem('cart')) || [];
            let totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
            document.getElementById('cart-count').textContent = totalItems;
        }
        // Hàm thêm sản phẩm vào giỏ hàng
        function addToCart(productId, quantity = 1) {
            let cart = JSON.parse(localStorage.getItem('cart')) || [];
            let productIndex = cart.findIndex(item => item.id === productId);
            if (productIndex !== -1) {
                cart[productIndex].quantity += quantity;
            } else {
                cart.push({ id: productId, quantity: quantity });
            }
        localStorage.setItem('cart', JSON.stringify(cart)); // Lưu lại giỏ hàng và cập nhật hiển thị
        updateCartCount();
    }

    // Cập nhật giỏ hàng khi tải trang
    document.addEventListener('DOMContentLoaded', updateCartCount);
    let currentStep = 1;

    function checkStepCompletion(step) { // Gọi API kiểm tra hoàn thành bước
        return fetch("https://api.example.com/check-step", { 
            method: "POST", 
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ step: step }) 
        })
        .then(response => response.json());
    }

    // Cập nhật giao diện khi bước được hoàn thành
    function updateStepUI() {
        for (let i = 1; i <= 3; i++) {
            let step = document.getElementById(`step-${i}`);
            if (i <= currentStep) {
                step.classList.add("bg-pink-500", "text-white");
                step.classList.remove("bg-gray-300", "text-gray-500");
            } else {
                step.classList.add("bg-gray-300", "text-gray-500");
                step.classList.remove("bg-pink-500", "text-white");
            }
        }
        if (currentStep === 3) {
            document.getElementById("complete-order").disabled = false;
        }
    }

    // Xử lý tự động cập nhật bước
    function completeCurrentStep() {
        checkStepCompletion(currentStep).then(data => {
            if (data.success) {
                currentStep++;
                updateStepUI();
            }
        }).catch(error => console.error("Lỗi API:", error));
    }
    setInterval(() => { // Gọi kiểm tra tự động mỗi 5 giây
        if (currentStep < 3) {
            completeCurrentStep();
        }
    }, 5000);

    function showAddressForm() { 
        document.getElementById("address-form").classList.toggle("hidden"); // Hiện form ngay trên trang
    }
    
    function showAddressForm() {
        document.getElementById("address-modal").classList.remove("hidden"); // Hiện popup
    }
    
    function closeModal() {
        document.getElementById("address-modal").classList.add("hidden"); // Ẩn popup
    }
    
    function saveAddress() {
        alert("Đã lưu địa chỉ mới!"); 
        closeModal(); // Sau khi lưu, ẩn popup
    }
    
    function showAddressForm() {
        let modal = document.getElementById("address-modal");
        modal.classList.remove("hidden");
        setTimeout(() => modal.classList.add("opacity-100"), 10); // Hiệu ứng mượt
    }

    function closeModal() {
        let modal = document.getElementById("address-modal");
        modal.classList.remove("opacity-100");
        setTimeout(() => modal.classList.add("hidden"), 300); // Ẩn sau hiệu ứng
    }
    
    function saveAddress() {
        alert("Đã lưu địa chỉ mới!"); 
        closeModal();
    }

    document.addEventListener("DOMContentLoaded", function () {
        loadProvinces();
    
        document.getElementById("province").addEventListener("change", function () {
            loadDistricts();
            updateAddress();
        });
    
        document.getElementById("district").addEventListener("change", function () {
            loadWards();
            updateAddress();
        });
    
        document.getElementById("ward").addEventListener("change", updateAddress);
    });
    
    async function loadProvinces() {
        let response = await fetch("https://provinces.open-api.vn/api/?depth=1");
        let data = await response.json();
    
        let provinceSelect = document.getElementById("province");
        provinceSelect.innerHTML = '<option value="">Chọn tỉnh/thành phố</option>';
    
        data.forEach(province => {
            let option = document.createElement("option");
            option.value = province.code; // Dùng code để load quận/huyện
            option.textContent = province.name;
            provinceSelect.appendChild(option);
        });
    }
    
    async function loadDistricts() {
        let provinceCode = document.getElementById("province").value;
        if (!provinceCode) return;
    
        let response = await fetch(`https://provinces.open-api.vn/api/p/${provinceCode}?depth=2`);
        let data = await response.json();
    
        let districtSelect = document.getElementById("district");
        districtSelect.innerHTML = '<option value="">Chọn quận/huyện</option>';
    
        data.districts.forEach(district => {
            let option = document.createElement("option");
            option.value = district.code; // Dùng code để load phường/xã
            option.textContent = district.name;
            districtSelect.appendChild(option);
        });
    
        // Reset phường/xã khi chọn tỉnh mới
        document.getElementById("ward").innerHTML = '<option value="">Chọn phường/xã</option>';
    }
    
    async function loadWards() {
        let districtCode = document.getElementById("district").value;
        if (!districtCode) return;
    
        let response = await fetch(`https://provinces.open-api.vn/api/d/${districtCode}?depth=2`);
        let data = await response.json();
    
        let wardSelect = document.getElementById("ward");
        wardSelect.innerHTML = '<option value="">Chọn phường/xã</option>';
    
        data.wards.forEach(ward => {
            let option = document.createElement("option");
            option.value = ward.name;
            option.textContent = ward.name;
            wardSelect.appendChild(option);
        });
    }
    
    // 🟢 Cập nhật ô nhập địa chỉ khi chọn tỉnh, quận, phường
    function updateAddress() {
        let province = document.getElementById("province").options[document.getElementById("province").selectedIndex].text;
        let district = document.getElementById("district").options[document.getElementById("district").selectedIndex].text;
        let ward = document.getElementById("ward").value;
    
        let fullAddress = [ward, district, province].filter(Boolean).join(", ");
        document.getElementById("full-address").value = fullAddress;
    }

    function updateAddress() {
        let addressInput = document.getElementById("full-address");
    
        let province = document.getElementById("province").options[document.getElementById("province").selectedIndex].text;
        let district = document.getElementById("district").options[document.getElementById("district").selectedIndex].text;
        let ward = document.getElementById("ward").value;
    
        let selectedAddress = [ward, district, province].filter(Boolean).join(", ");
    
        // Nếu người dùng chưa nhập gì -> tự động điền địa chỉ từ dropdown
        if (!addressInput.dataset.userTyped) {
            addressInput.value = selectedAddress;
        } else {
            // Nếu người dùng đã nhập, chỉ thêm tỉnh/quận/phường vào cuối
            let currentAddress = addressInput.value.split(" - ")[0]; // Lấy phần tự nhập
            addressInput.value = currentAddress + " - " + selectedAddress;
        }
    }
    
    // 🟢 Đánh dấu khi người dùng nhập tay
    document.getElementById("full-address").addEventListener("input", function () {
        this.dataset.userTyped = true;
    });
    
    function saveAddress() {
        // Lấy giá trị từ các trường nhập
        let fullName = document.querySelector('input[placeholder="Họ và tên"]').value;
        let phone = document.querySelector('input[placeholder="Số điện thoại"]').value;
        let addressDetail = document.getElementById("full-address").value;
        let province = document.getElementById("province").selectedOptions[0].text;
        let district = document.getElementById("district").selectedOptions[0].text;
        let ward = document.getElementById("ward").selectedOptions[0].text;
    
        // Kiểm tra nếu chưa chọn tỉnh/quận/phường thì không lưu
        if (!province || province === "Chọn tỉnh/thành phố" || 
            !district || district === "Chọn quận/huyện" || 
            !ward || ward === "Chọn phường/xã") {
            alert("Vui lòng chọn đầy đủ địa chỉ!");
            return;
        }
    
        // Hiển thị địa chỉ đã nhập lên trang
        let fullAddressText = `${addressDetail}, ${ward}, ${district}, ${province}`;
        document.getElementById("selected-address").innerText = fullAddressText;
    
        // Đóng popup sau khi lưu
        closeModal();
    }
    
    function saveAddress() {
        // Lấy giá trị từ các trường nhập
        let fullName = document.querySelector('input[placeholder="Họ và tên"]').value;
        let phone = document.querySelector('input[placeholder="Số điện thoại"]').value;
        let addressDetail = document.getElementById("full-address").value;
        let province = document.getElementById("province").selectedOptions[0].text;
        let district = document.getElementById("district").selectedOptions[0].text;
        let ward = document.getElementById("ward").selectedOptions[0].text;
    
        // Kiểm tra nếu chưa chọn tỉnh/quận/phường thì không lưu
        if (!province || province === "Chọn tỉnh/thành phố" || 
            !district || district === "Chọn quận/huyện" || 
            !ward || ward === "Chọn phường/xã") {
            alert("Vui lòng chọn đầy đủ địa chỉ!");
            return;
        }
    
        // Hiển thị địa chỉ đã nhập lên trang
        let fullAddressText = `${addressDetail}, ${ward}, ${district}, ${province}`;
        document.getElementById("selected-address").innerText = fullAddressText;
    
        // Ẩn nút "Thêm địa chỉ mới"
        document.getElementById("add-address-btn").style.display = "none";
    
        // Đóng popup sau khi lưu
        closeModal();
    }
    
    function editAddress() {
        // Lấy nội dung địa chỉ đã lưu
        let savedAddress = document.getElementById("selected-address").innerText;
        if (!savedAddress) return; // Nếu chưa có địa chỉ thì không làm gì
    
        // Tách địa chỉ thành các phần (giả sử theo đúng format: "Địa chỉ chi tiết, Phường/Xã, Quận/Huyện, Tỉnh/Thành phố")
        let parts = savedAddress.split(", ").reverse(); 
    
        // Đổ dữ liệu vào các ô chọn (nếu có dữ liệu hợp lệ)
        if (parts[0]) selectOptionByText("province", parts[0]);
        if (parts[1]) selectOptionByText("district", parts[1]);
        if (parts[2]) selectOptionByText("ward", parts[2]);
    
        // Điền địa chỉ chi tiết
        document.getElementById("full-address").value = parts[3] || "";
    
        // Hiển thị popup nhập địa chỉ
        openModal();
    }
    
    // Hàm chọn option theo nội dung text
    function selectOptionByText(selectId, text) {
        let selectElement = document.getElementById(selectId);
        for (let option of selectElement.options) {
            if (option.text.trim() === text.trim()) {
                option.selected = true;
                if (selectId === "province") loadDistricts(); // Tải danh sách quận
                if (selectId === "district") loadWards(); // Tải danh sách phường
                break;
            }
        }
    }
    
    function openModal(edit = false) {
        let modal = document.getElementById("address-modal");
        modal.classList.remove("hidden"); // Hiện popup
    
        if (edit) {
            // Lấy thông tin địa chỉ hiện tại
            let savedAddress = document.getElementById("saved-address").textContent;
            document.getElementById("full-address").value = savedAddress; // Đổ dữ liệu vào ô nhập
        }
    }
    
    document.getElementById("edit-address-btn").addEventListener("click", function () {
        openModal(true); // Gọi modal với chế độ chỉnh sửa
    });
    
    
    