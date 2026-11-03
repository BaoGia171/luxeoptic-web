// Giỏ hàng bắt đầu trống, chỉ hiển thị sản phẩm đã được thêm.
let voucherApplied = false;
let cartTotal = 0;

function formatMoney(value) {
  return value.toLocaleString('vi-VN') + 'đ';
}

function renderCart() {
  const items = readCart();
  const container = document.getElementById('cartItems');
  const template = document.getElementById('cartItemTemplate');
  container.replaceChildren();
  items.forEach(function (item, index) {
    const row = template.content.firstElementChild.cloneNode(true);
    row.querySelector('img').src = item.image;
    row.querySelector('img').alt = item.name;
    row.querySelector('.cart-item-name').textContent = item.name;
    row.querySelector('.cart-item-color').textContent = item.color;
    row.querySelector('.qty-input').value = item.quantity;
    row.querySelector('.cart-item-total').textContent = formatMoney(item.price * item.quantity);
    row.querySelector('.qty-minus').disabled = item.quantity <= 1;
    row.querySelector('.qty-minus').addEventListener('click', function () {
      if (items[index].quantity > 1) items[index].quantity--;
      saveCart(items);
      renderCart();
    });
    row.querySelector('.qty-plus').addEventListener('click', function () {
      items[index].quantity++;
      saveCart(items);
      renderCart();
    });
    row.querySelector('button[title="Xóa"]').addEventListener('click', function () {
      items.splice(index, 1);
      saveCart(items);
      renderCart();
    });
    container.appendChild(row);
  });
  updateCartTotals();
}

function updateCartTotals() {
  let subtotal = 0;
  let count = 0;
  const items = readCart();
  items.forEach(function (item) {
    count += item.quantity;
    subtotal += item.price * item.quantity;
  });
  const discount = voucherApplied ? Math.round(subtotal * 0.1) : 0;
  cartTotal = subtotal - discount;
  document.getElementById('cartCount').textContent = count;
  document.getElementById('summaryCount').textContent = count;
  document.getElementById('subtotalAmount').textContent = formatMoney(subtotal);
  document.getElementById('discountAmount').textContent = '-' + formatMoney(discount);
  document.getElementById('totalAmount').textContent = formatMoney(cartTotal);
  document.getElementById('voucherDiscountRow').classList.toggle('hidden', !voucherApplied || !count);
  document.getElementById('emptyCart').classList.toggle('hidden', count > 0);
  document.getElementById('checkoutButton').disabled = count === 0;
  updateCartBadge();
}

window.addEventListener('pageshow', renderCart);
document.addEventListener('DOMContentLoaded', function () {
  document.getElementById('btnApplyVoucher').addEventListener('click', function () {
    const code = document.getElementById('voucherInput').value.trim().toUpperCase();
    voucherApplied = code === 'LUXE10';
    const message = document.getElementById('voucherMessage');
    if (voucherApplied) message.textContent = 'Đã áp dụng mã LUXE10, giảm 10%.';
    else if (code === '') message.textContent = 'Đã bỏ mã giảm giá.';
    else message.textContent = 'Mã không hợp lệ. Đơn hàng không được giảm giá.';
    updateCartTotals();
  });
  document.getElementById('checkoutForm').addEventListener('submit', function (event) {
    event.preventDefault();
    if (!document.querySelector('.cart-item')) return;
    const name = document.getElementById('custName').value;
    const payment = document.querySelector('input[type="radio"]:checked');
    const methods = { cod: 'Thanh toán khi nhận hàng (COD)', vietqr: 'Chuyển khoản VietQR', card: 'Thẻ quốc tế / Apple Pay' };
    const method = payment ? methods[payment.value] : '';
    alert('ĐẶT HÀNG MẪU\nCảm ơn ' + name + '.\nTổng thanh toán: ' + formatMoney(cartTotal) +
      (method ? '\nPhương thức: ' + method : '') +
      '\nĐây là bản giao diện, đơn hàng chưa được lưu vào hệ thống.');
  });
  const paymentInputs = document.querySelectorAll('input[name="payment_method"]');
  function updatePayment() {
    paymentInputs.forEach(function (input) {
      input.closest('label').classList.toggle('selected', input.checked);
    });
  }
  paymentInputs.forEach(function (input) {
    input.addEventListener('change', updatePayment);
  });
  updatePayment();
  renderCart();
});
