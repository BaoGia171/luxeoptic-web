/**
 * LUXEOPTIC - ADMIN / QUẢN LÝ TÀI KHOẢN (admin/users.php)
 * Mở modal thêm/sửa, điền sẵn dữ liệu dòng được chọn, xác nhận khóa/mở khóa.
 * Dùng openModal/closeModal từ admin.js. Quyền và dữ liệu do PHP kiểm tra lại.
 */
document.addEventListener('DOMContentLoaded', function () {
  var modal = document.getElementById('userModal');
  var form = document.getElementById('userForm');
  if (!modal || !form) return;

  var title = document.getElementById('modalTitle');
  var idInput = document.getElementById('userIdInput');
  var nameInput = document.getElementById('userNameInput');
  var phoneInput = document.getElementById('userPhoneInput');
  var emailInput = document.getElementById('userEmailInput');
  var passwordGroup = document.getElementById('userPasswordGroup');
  var passwordInput = document.getElementById('userPasswordInput');
  var roleSelect = document.getElementById('userRoleSelect');
  var statusSelect = document.getElementById('userStatusSelect');

  // Chế độ sửa: ẩn và vô hiệu ô mật khẩu để không gửi lên (server cũng bỏ qua).
  function setMode(mode) {
    var isEdit = mode === 'edit';
    title.textContent = isEdit ? 'Chỉnh Sửa Tài Khoản' : 'Thêm Tài Khoản Mới';
    passwordGroup.hidden = isEdit;
    passwordGroup.style.display = isEdit ? 'none' : ''; // chắc chắn ẩn dù CSS đặt display
    passwordInput.disabled = isEdit;
    passwordInput.required = !isEdit;
  }

  function clearErrors() {
    form.querySelectorAll('.field-error').forEach(function (node) { node.remove(); });
    form.querySelectorAll('[aria-invalid]').forEach(function (node) {
      node.removeAttribute('aria-invalid');
      node.removeAttribute('aria-describedby');
    });
  }

  function openCreate() {
    clearErrors();
    form.reset();
    idInput.value = '';
    passwordInput.value = '';
    roleSelect.value = 'customer';
    statusSelect.value = 'active';
    setMode('create');
    openModal('userModal');
    nameInput.focus();
  }

  function openEdit(button) {
    clearErrors();
    idInput.value = button.dataset.id || '';
    nameInput.value = button.dataset.name || '';
    phoneInput.value = button.dataset.phone || '';
    emailInput.value = button.dataset.email || '';
    roleSelect.value = button.dataset.role || 'customer';
    statusSelect.value = button.dataset.status || 'active';
    passwordInput.value = '';
    setMode('edit');
    openModal('userModal');
    nameInput.focus();
  }

  document.querySelectorAll('[data-user-create]').forEach(function (button) {
    button.addEventListener('click', openCreate);
  });
  document.querySelectorAll('[data-user-edit]').forEach(function (button) {
    button.addEventListener('click', function () { openEdit(button); });
  });
  document.querySelectorAll('[data-user-close]').forEach(function (button) {
    button.addEventListener('click', function () { closeModal('userModal'); });
  });

  // Khóa/mở khóa: hỏi lại trước khi gửi, và chặn bấm hai lần.
  document.querySelectorAll('form[data-confirm]').forEach(function (statusForm) {
    statusForm.addEventListener('submit', function (event) {
      if (statusForm.dataset.submitting === '1' || !window.confirm(statusForm.dataset.confirm)) {
        event.preventDefault();
        return;
      }
      statusForm.dataset.submitting = '1';
    });
  });

  form.addEventListener('submit', function (event) {
    if (form.dataset.submitting === '1') {
      event.preventDefault();
      return;
    }
    form.dataset.submitting = '1';
  });

  // Server trả lỗi: mở lại modal với giá trị đã nhập (PHP đã điền sẵn và gắn lỗi).
  if (modal.dataset.reopen === '1') {
    setMode(modal.dataset.mode);
    openModal('userModal');
  } else {
    setMode('create');
  }
});
