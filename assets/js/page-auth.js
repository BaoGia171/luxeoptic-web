/**
 * LUXEOPTIC - TRANG ĐĂNG NHẬP / ĐĂNG KÝ (auth.php)
 * Chỉ xử lý giao diện: chuyển tab, hiện/ẩn mật khẩu, nhắc lỗi trước khi gửi.
 * Việc kiểm tra thật và tạo tài khoản do actions/login.php, actions/register.php làm.
 *
 * Logic chuyển tab này thay cho mục "4. Chuyển Tab Đăng nhập / Đăng ký" trong
 * main.js. Khi PR gỡ mục đó khỏi main.js được merge thì file này là nơi duy nhất.
 */
document.addEventListener('DOMContentLoaded', function () {
  var tabLogin = document.getElementById('tabLogin');
  var tabRegister = document.getElementById('tabRegister');
  var formLogin = document.getElementById('formLogin');
  var formRegister = document.getElementById('formRegister');
  if (!tabLogin || !tabRegister || !formLogin || !formRegister) return;

  var ACTIVE = ['border-b-2', 'border-black', 'font-bold', 'text-black', 'active'];
  var IDLE = ['text-gray-400', 'font-medium'];
  var LABELS = { login: 'Đăng Nhập', register: 'Đăng Ký Tài Khoản' };

  function styleTab(tab, isActive, label) {
    ACTIVE.forEach(function (name) { tab.classList.toggle(name, isActive); });
    IDLE.forEach(function (name) { tab.classList.toggle(name, !isActive); });
    tab.classList.remove('border-primary');
    tab.textContent = (isActive ? '• ' : '') + label;
    tab.setAttribute('aria-selected', String(isActive));
  }

  function showTab(name) {
    var isLogin = name === 'login';
    styleTab(tabLogin, isLogin, LABELS.login);
    styleTab(tabRegister, !isLogin, LABELS.register);
    formLogin.classList.toggle('hidden', !isLogin);
    formRegister.classList.toggle('hidden', isLogin);
  }

  tabLogin.addEventListener('click', function () { showTab('login'); });
  tabRegister.addEventListener('click', function () { showTab('register'); });

  // Hiện/ẩn mật khẩu.
  document.querySelectorAll('[data-toggle-password]').forEach(function (button) {
    button.addEventListener('click', function () {
      var input = document.getElementById(button.getAttribute('data-toggle-password'));
      if (!input) return;
      var show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      button.setAttribute('aria-label', show ? 'Ẩn mật khẩu' : 'Hiện mật khẩu');
      var icon = button.querySelector('.material-symbols-outlined');
      if (icon) icon.textContent = show ? 'visibility_off' : 'visibility';
    });
  });

  // Nhắc ngay khi hai mật khẩu chưa khớp. Server vẫn kiểm tra lại khi gửi.
  var pass = document.getElementById('regPass');
  var confirm = document.getElementById('regConfirmPass');
  if (pass && confirm) {
    var hint = document.createElement('p');
    hint.className = 'text-11px text-red-600 mt-1';
    hint.setAttribute('role', 'status');
    hint.hidden = true;
    hint.style.display = 'none';
    hint.textContent = 'Mật khẩu xác nhận chưa khớp.';
    confirm.parentNode.appendChild(hint);

    var check = function () {
      var hide = confirm.value === '' || confirm.value === pass.value;
      hint.hidden = hide;
      hint.style.display = hide ? 'none' : '';
    };
    pass.addEventListener('input', check);
    confirm.addEventListener('input', check);
  }

  // Chặn bấm gửi hai lần liên tiếp.
  [formLogin, formRegister].forEach(function (form) {
    form.addEventListener('submit', function (event) {
      if (form.dataset.submitting === '1') {
        event.preventDefault();
        return;
      }
      form.dataset.submitting = '1';
    });
  });
});
