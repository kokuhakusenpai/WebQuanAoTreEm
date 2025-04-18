let currentPage = 1;
let currentGenderId = null; // Track selected gender (1 for Bé gái, 2 for Bé trai)

function fetchProducts(page = 1, genderId = null) {
    let url = `/PHP/get_all_products.php?page=${page}`;
    if (genderId) {
        url += `&genderId=${genderId}`;
    }
    fetch(url)
        .then(res => res.json())
        .then(data => {
            renderProducts(data.products);
            renderPagination(data.totalPages, page);
        });
}

function renderProducts(products) {
    const container = document.getElementById('product-list');
    container.innerHTML = products.map(p => `
                <div class="border rounded p-2 shadow hover:shadow-lg">
            <a href="product_detail.html?product_id=${p.product_id}">
                <img src="${p.image}" class="w-full h-40 object-cover mb-2">
            </a>
            <h2 class="font-semibold text-sm">${p.name}</h2>
            <p class="text-lg font-semibold text-blue-600">
                ${new Intl.NumberFormat('vi-VN').format(p.discount_price)}₫
                <del class="text-gray-500 text-sm ml-2">${new Intl.NumberFormat('vi-VN').format(p.price)}₫</del>
            </p>
            <p class="mt-4">
                <a href="product_detail.html?product_id=${p.product_id}" class="text-blue-600 hover:underline font-semibold transition-colors duration-300 hover:text-blue-800">
                    Xem chi tiết
                </a>
            </p>
        </div>
    `).join('');
}

function renderPagination(totalPages, current) {
    const container = document.getElementById('pagination');
    let html = '';

    if (current > 1) {
        html += `<button onclick="changePage(${current - 1})" class="px-3 py-1 bg-gray-200 rounded">«</button>`;
    }

    for (let i = 1; i <= totalPages; i++) {
        html += `<button onclick="changePage(${i})" class="px-3 py-1 rounded ${i === current ? 'bg-blue-500 text-white' : 'bg-gray-200'}">${i}</button>`;
    }

    if (current < totalPages) {
        html += `<button onclick="changePage(${current + 1})" class="px-3 py-1 bg-gray-200 rounded">»</button>`;
    }

    container.innerHTML = html;
}

function changePage(page) {
    currentPage = page;
    fetchProducts(page, currentGenderId);
}

function filterByGender(genderId) {
    currentGenderId = genderId;
    currentPage = 1; // Reset to first page when changing gender
    fetchProducts(1, genderId);
}

// Initial fetch (no gender filter)
fetchProducts();