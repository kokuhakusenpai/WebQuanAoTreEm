document.addEventListener('DOMContentLoaded', function () {
    const userAccount = document.getElementById('userAccount');

    fetch('PHP/check_session.php')
        .then(response => response.json())
        .then(data => {
            let dropdownContent = '';
            if (data.isLoggedIn) {
                dropdownContent = `
                    <a class="block p-2 text-black hover:bg-blue-200">${data.username}</a>
                    <a href="dh.html" class="block p-2 text-black hover:bg-blue-200">Thông tin</a>
                    <a href="logout.php" class="block p-2 text-black hover:bg-blue-200">Đăng xuất</a>
                `;
            } else {
                dropdownContent = `
                    <a href="login.html" class="block px-4 py-2 text-black hover:bg-[#81D4FA]">Đăng nhập</a>
                    <a href="register.html" class="block px-4 py-2 text-black hover:bg-[#81D4FA]">Đăng ký</a>
                `;
            }

            userAccount.innerHTML = `
                <button onclick="toggleUserInfo()" class="user-accountbtn bg-white text-black p-2 text-lg border-none rounded-md cursor-pointer transition-all duration-300 hover:bg-blue-500">
                    <span class="text-xl" style="font-size: 1.5rem;">👤</span>
                </button>
                <div id="userInfo" class="user-dropdown hidden absolute right-0 bg-white min-w-[160px] shadow-lg z-50 rounded-md mt-1">
                    ${dropdownContent}
                </div>
            `;
        })
        .catch(error => {
            console.error('Lỗi khi kiểm tra trạng thái đăng nhập:', error);
        });
});

function toggleUserInfo() {
    const userInfo = document.getElementById('userInfo');
    if (userInfo) {
        userInfo.classList.toggle('hidden');
    }
}