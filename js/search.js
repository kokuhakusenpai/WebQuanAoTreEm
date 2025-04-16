function searchProducts() {
    const query = document.getElementById('searchInput').value;

    if (!query.trim()) {
        alert('Vui lòng nhập từ khóa tìm kiếm.');
        return;
    }

    // Chuyển hướng sang trang search.html với tham số truy vấn
    window.location.href = `search.html?query=${encodeURIComponent(query)}`;
}