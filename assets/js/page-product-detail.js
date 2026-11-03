// Tương tác riêng của trang product-detail

document.addEventListener('DOMContentLoaded', function () {
  const id = new URLSearchParams(window.location.search).get('id') || 'LX-AT-AV01';
  const product = productCatalog.find(function (item) { return item.id === id; });
  if (!product) {
    window.location.replace('products.html');
    return;
  }
  document.title = product.name + ' | LuxeOptic';
  document.getElementById('detailName').textContent = product.name;
  document.getElementById('detailBreadcrumb').textContent = product.name;
  document.getElementById('detailPrice').textContent = product.price.toLocaleString('vi-VN') + 'đ';
  document.getElementById('detailSeries').textContent = product.series;
  document.getElementById('detailSku').textContent = product.id;
  const image = document.getElementById('mainProductImage');
  image.src = product.image;
  image.alt = product.name;

  const oldPrice = document.getElementById('detailOldPrice');
  const discount = document.getElementById('detailDiscount');
  oldPrice.hidden = product.oldPrice <= product.price;
  discount.hidden = product.oldPrice <= product.price;
  if (product.oldPrice > product.price) {
    oldPrice.textContent = product.oldPrice.toLocaleString('vi-VN') + 'đ';
    discount.textContent = 'Tiết kiệm ' + (product.oldPrice - product.price).toLocaleString('vi-VN') + 'đ';
  }

  // Các mẫu khác hiện chỉ có một ảnh, không dùng ảnh phụ của Aviator.
  if (product.id !== 'LX-AT-AV01') {
    document.querySelectorAll('.gallery-thumb').forEach(function (thumb, index) {
      thumb.hidden = index > 0;
      if (index === 0) {
        thumb.dataset.image = product.image;
        thumb.querySelector('img').src = product.image;
        thumb.querySelector('img').alt = product.name;
      }
    });
    document.getElementById('selectedColorName').textContent = 'Theo ảnh sản phẩm';
    document.querySelectorAll('.color-swatch-item').forEach(function (swatch) { swatch.hidden = true; });
  }
  function addProduct(event) {
    event.preventDefault();
    const color = document.getElementById('selectedColorName').textContent.trim();
    const quantity = Number(document.querySelector('.qty-input').value) || 1;
    const items = readCart();
    const existing = items.find(function (item) {
      return item.id === product.id && item.color === color;
    });
    if (existing) existing.quantity += quantity;
    else items.push({
      id: product.id,
      name: product.name,
      color: color,
      price: product.price,
      quantity: quantity,
      image: product.image
    });
    saveCart(items);
    window.location.href = 'cart.html';
  }
  document.getElementById('addToCart').addEventListener('click', addProduct);
  document.getElementById('buyNow').addEventListener('click', addProduct);
  document.querySelector('[data-action="product-detail-action-1"]').addEventListener('click', function (event) {
    alert('Đã ghi nhận yêu cầu thử kính tận nơi! Chuyên viên LuxeOptic sẽ liên hệ với bạn trong 15 phút.')
  });
});
