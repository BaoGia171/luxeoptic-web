// Tương tác riêng của trang index

document.addEventListener('DOMContentLoaded', function () {
  document.querySelector('[data-action="index-action-1"]').addEventListener('submit', function (event) {
    event.preventDefault(); alert('Cảm ơn quý khách đã đăng ký nhận tin!');
  });
});
