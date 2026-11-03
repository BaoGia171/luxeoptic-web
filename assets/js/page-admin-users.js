// Tương tác riêng của trang admin-users

function openEditUserModal(name, email, phone, role) {
      document.getElementById('modalTitle').innerText = 'Chỉnh Sửa Tài Khoản: ' + name;
      document.getElementById('userNameInput').value = name;
      document.getElementById('userEmailInput').value = email;
      document.getElementById('userPhoneInput').value = phone;
      document.getElementById('userRoleSelect').value = role;

      const permProducts = document.getElementById('permProducts');
      const permOrders = document.getElementById('permOrders');
      const permCategories = document.getElementById('permCategories');
      const permUsers = document.getElementById('permUsers');

      if (role === 'ADMIN') {
        permProducts.checked = true;
        permOrders.checked = true;
        permCategories.checked = true;
        permUsers.checked = true;
      } else if (role === 'STAFF') {
        permProducts.checked = true;
        permOrders.checked = true;
        permCategories.checked = true;
        permUsers.checked = false;
      } else {
        permProducts.checked = false;
        permOrders.checked = false;
        permCategories.checked = false;
        permUsers.checked = false;
      }

      openModal('userModal');
    }

document.addEventListener('DOMContentLoaded', function () {
  document.querySelector('[data-action="admin-users-action-1"]').addEventListener('click', function (event) {
    openModal('userModal')
  });
  document.querySelector('[data-action="admin-users-action-2"]').addEventListener('click', function (event) {
    openEditUserModal('Super Admin', 'admin@luxeoptic.vn', '0988 888 888', 'ADMIN')
  });
  document.querySelector('[data-action="admin-users-action-3"]').addEventListener('click', function (event) {
    alert('Đã gửi liên kết khôi phục mật khẩu đến email admin@luxeoptic.vn')
  });
  document.querySelector('[data-action="admin-users-action-4"]').addEventListener('click', function (event) {
    openEditUserModal('Hoàng Thị Ngọc Bích', 'bich.hoang@luxeoptic.vn', '0912 889 999', 'STAFF')
  });
  document.querySelector('[data-action="admin-users-action-5"]').addEventListener('click', function (event) {
    if(confirm('Khóa quyền truy cập của nhân viên này?')) alert('Đã khóa tài khoản thành công!');
  });
  document.querySelector('[data-action="admin-users-action-6"]').addEventListener('click', function (event) {
    openEditUserModal('Nguyễn Hoàng Minh Triết', 'triet.nguyen@example.com', '0908 123 456', 'VIP')
  });
  document.querySelector('[data-action="admin-users-action-7"]').addEventListener('click', function (event) {
    openEditUserModal('Lê Thị Thanh Thảo', 'thao.le@gmail.com', '0912 345 678', 'CUSTOMER')
  });
  document.querySelector('[data-action="admin-users-action-8"]').addEventListener('click', function (event) {
    if(confirmDelete('Lê Thị Thanh Thảo')) this.closest('tr').remove();
  });
  document.querySelector('[data-action="admin-users-action-9"]').addEventListener('click', function (event) {
    openEditUserModal('Trần Đình Phong', 'phong.tran@gmail.com', '0987 654 321', 'CUSTOMER')
  });
  document.querySelector('[data-action="admin-users-action-10"]').addEventListener('click', function (event) {
    if(confirmDelete('Trần Đình Phong')) this.closest('tr').remove();
  });
  document.querySelector('[data-action="admin-users-action-11"]').addEventListener('click', function (event) {
    alert('Đã mở khóa tài khoản thành công!')
  });
  document.querySelector('[data-action="admin-users-action-12"]').addEventListener('click', function (event) {
    if(confirmDelete('Đặng Tuấn Anh')) this.closest('tr').remove();
  });
  document.querySelector('[data-action="admin-users-action-13"]').addEventListener('click', function (event) {
    closeModal('userModal')
  });
  document.querySelector('[data-action="admin-users-action-14"]').addEventListener('submit', function (event) {
    event.preventDefault(); alert('Đã lưu thông tin tài khoản và cấu hình phân quyền RBAC thành công!'); closeModal('userModal');
  });
  document.querySelector('[data-action="admin-users-action-15"]').addEventListener('click', function (event) {
    closeModal('userModal')
  });
});
