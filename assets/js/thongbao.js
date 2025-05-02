document.addEventListener('DOMContentLoaded', function () {
    const unreadCountElement = document.getElementById('unreadCount');
    const notificationList = document.getElementById('notification-list');
    const notificationDropdown = document.getElementById('notification-dropdown');
    // Gọi API để lấy thông báo
    fetch('PHP/get_notifications.php')
        .then(response => response.json())
        .then(data => {
            // Hiển thị số lượng thông báo chưa đọc
            if (data.unreadCount > 0) {
                unreadCountElement.textContent = data.unreadCount;
                unreadCountElement.classList.remove('hidden');
            }

            // Hiển thị danh sách thông báo
            if (data.notifications.length > 0) {
                notificationList.innerHTML = data.notifications.map(notification => `
                    <li class="mb-2">${notification.message}</li>
                `).join('');
            } else {
                notificationList.innerHTML = '<p>Không có thông báo mới.</p>';
            }
        })
        .catch(error => {
            console.error('Lỗi khi tải thông báo:', error);
        });
});
// Hiển thị/ẩn dropdown thông báo
function toggleNotifications(event) {
    event.stopPropagation(); // Ngăn chặn sự kiện lan ra ngoài
    const notificationDropdown = document.getElementById('notification-dropdown');
    notificationDropdown.classList.toggle('hidden');
}
// Đóng dropdown khi nhấp ra ngoài
document.addEventListener('click', function () {
    const notificationDropdown = document.getElementById('notification-dropdown');
    if (!notificationDropdown.classList.contains('hidden')) {
        notificationDropdown.classList.add('hidden');
    }
});