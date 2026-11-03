// Dữ liệu giỏ hàng dùng chung giữa các trang, lưu trên trình duyệt.
const cartStorageKey = 'luxeoptic-cart';

function readCart() {
  try {
    const items = JSON.parse(localStorage.getItem(cartStorageKey) || '[]');
    if (!Array.isArray(items)) return [];
    return items.filter(function (item) {
      return typeof item.name === 'string' && typeof item.color === 'string' &&
        Number.isFinite(item.price) && item.price >= 0 &&
        Number.isInteger(item.quantity) && item.quantity > 0;
    });
  } catch (error) {
    return [];
  }
}

function saveCart(items) {
  localStorage.setItem(cartStorageKey, JSON.stringify(items));
  updateCartBadge();
}

function updateCartBadge() {
  let count = 0;
  readCart().forEach(function (item) { count += item.quantity; });
  document.querySelectorAll('.action-badge').forEach(function (badge) {
    badge.textContent = count;
  });
}

document.addEventListener('DOMContentLoaded', updateCartBadge);
window.addEventListener('pageshow', updateCartBadge);
window.addEventListener('storage', function (event) {
  if (event.key === cartStorageKey) {
    updateCartBadge();
    if (typeof renderCart === 'function') renderCart();
  }
});
