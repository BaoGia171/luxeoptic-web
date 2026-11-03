// Tương tác riêng của trang admin-categories

document.addEventListener('DOMContentLoaded', function () {
  document.querySelector('[data-action="admin-categories-action-1"]').addEventListener('click', function (event) {
    openModal('categoryModal')
  });
  document.querySelector('[data-action="admin-categories-action-2"]').addEventListener('click', function (event) {
    alert('Đã xuất danh sách danh mục!');
  });
  document.querySelector('[data-action="admin-categories-action-3"]').addEventListener('click', function (event) {
    openModal('categoryModal')
  });
  document.querySelector('[data-action="admin-categories-action-4"]').addEventListener('click', function (event) {
    if(confirmDelete('Kính Râm')) this.closest('tr').remove();
  });
  document.querySelector('[data-action="admin-categories-action-5"]').addEventListener('click', function (event) {
    openModal('categoryModal')
  });
  document.querySelector('[data-action="admin-categories-action-6"]').addEventListener('click', function (event) {
    if(confirmDelete('Gọng Kính Cận')) this.closest('tr').remove();
  });
  document.querySelector('[data-action="admin-categories-action-7"]').addEventListener('click', function (event) {
    openModal('categoryModal')
  });
  document.querySelector('[data-action="admin-categories-action-8"]').addEventListener('click', function (event) {
    if(confirmDelete('Kính Chống Ánh Sáng Xanh')) this.closest('tr').remove();
  });
  document.querySelector('[data-action="admin-categories-action-9"]').addEventListener('click', function (event) {
    openModal('categoryModal')
  });
  document.querySelector('[data-action="admin-categories-action-10"]').addEventListener('click', function (event) {
    if(confirmDelete('Kính Phiên Bản Giới Hạn')) this.closest('tr').remove();
  });
  document.querySelector('[data-action="admin-categories-action-11"]').addEventListener('click', function (event) {
    closeModal('categoryModal')
  });
  document.querySelector('[data-action="admin-categories-action-12"]').addEventListener('submit', function (event) {
    event.preventDefault(); alert('Lưu danh mục thành công!'); closeModal('categoryModal');
  });
  document.querySelector('[data-action="admin-categories-action-13"]').addEventListener('click', function (event) {
    closeModal('categoryModal')
  });
});
