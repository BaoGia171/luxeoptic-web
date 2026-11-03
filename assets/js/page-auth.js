// Tương tác riêng của trang auth

function handleLoginSubmit() {
  alert('Đây là giao diện mẫu đăng nhập khách hàng.');
  window.location.href = 'index.html';
}

    function handleRegisterSubmit() {
      const p1 = document.getElementById('regPass').value;
      const p2 = document.getElementById('regConfirmPass').value;
      if (p1 !== p2) {
        alert('Lỗi: Mật khẩu xác nhận không khớp! Vui lòng nhập lại.');
        return;
      }
      const name = document.getElementById('regName').value;
      alert(`Đăng ký tài khoản thành công cho: ${name}!\nChào mừng bạn gia nhập gia đình LuxeOptic Atelier.`);
      window.location.href = 'index.html';
    }

document.addEventListener('DOMContentLoaded', function () {
  document.querySelector('[data-action="auth-action-1"]').addEventListener('submit', function (event) {
    event.preventDefault(); handleLoginSubmit();
  });
  document.querySelector('[data-action="auth-action-2"]').addEventListener('click', function (event) {
    event.preventDefault(); alert('Vui lòng liên hệ quản trị viên hoặc kiểm tra email để đặt lại mật khẩu.')
  });
  document.querySelector('[data-action="auth-action-3"]').addEventListener('click', function (event) {
    const p = document.getElementById('authPassword'); p.type = p.type === 'password' ? 'text' : 'password';
    this.setAttribute('aria-label', p.type === 'password' ? 'Hiện mật khẩu' : 'Ẩn mật khẩu');
    this.querySelector('span').textContent = p.type === 'password' ? 'visibility' : 'visibility_off';
  });
  document.querySelector('[data-action="auth-action-4"]').addEventListener('submit', function (event) {
    event.preventDefault(); handleRegisterSubmit();
  });
  document.querySelector('[data-action="auth-action-5"]').addEventListener('click', function (event) {
    alert('Đăng nhập Google chưa được kết nối trong bản giao diện này.');
  });
  document.querySelector('[data-action="auth-action-6"]').addEventListener('click', function (event) {
    alert('Đăng nhập Apple chưa được kết nối trong bản giao diện này.');
  });
});
