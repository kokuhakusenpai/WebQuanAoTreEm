function renderProductCard(product) {
  return `
    <div class="product bg-white rounded-lg shadow-md p-4 text-center transition-transform transform hover:scale-105">
      <img src="${product.image_url}" alt="${product.name}" class="w-full h-auto rounded-lg mb-4" />
      <p class="text-gray-700">${product.name}</p>
      <p class="text-lg font-semibold text-blue-600">
        ${Number(product.price).toLocaleString()}₫
        ${product.discount_price ? `<del class="text-gray-500 text-sm ml-2">${Number(product.discount_price).toLocaleString()}₫</del>` : ''}
      </p>
      <p class="mt-4">
        <a href="product_detail.php?product_id=${product.product_id}" class="text-blue-600 hover:underline">Xem chi tiết</a>
      </p>
    </div>
  `;
}