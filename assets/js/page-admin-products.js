// Tương tác riêng của trang admin-products

document.addEventListener('DOMContentLoaded', function () {
  document.querySelector('[data-action="admin-products-action-1"]').addEventListener('click', function (event) {
    openModal('productModal')
  });
  document.querySelector('[data-action="admin-products-action-2"]').addEventListener('click', function (event) {
    alert('Đã xuất dữ liệu Excel thành công!')
  });
  document.querySelector('[data-action="admin-products-action-3"]').addEventListener('click', function (event) {
    openModal('productModal')
  });
  document.querySelector('[data-action="admin-products-action-4"]').addEventListener('click', function (event) {
    if(confirmDelete('Aviator Titanium Gold 01')) this.closest('tr').remove();
  });
  document.querySelector('[data-action="admin-products-action-5"]').addEventListener('click', function (event) {
    openModal('productModal')
  });
  document.querySelector('[data-action="admin-products-action-6"]').addEventListener('click', function (event) {
    if(confirmDelete('Titanium Minimalist Noir')) this.closest('tr').remove();
  });
  document.querySelector('[data-action="admin-products-action-7"]').addEventListener('click', function (event) {
    openModal('productModal')
  });
  document.querySelector('[data-action="admin-products-action-8"]').addEventListener('click', function (event) {
    if(confirmDelete('Cat-Eye Havana Acetate')) this.closest('tr').remove();
  });
  document.querySelector('[data-action="admin-products-action-9"]').addEventListener('click', function (event) {
    openModal('productModal')
  });
  document.querySelector('[data-action="admin-products-action-10"]').addEventListener('click', function (event) {
    if(confirmDelete('Round Blue-Blocker Silver')) this.closest('tr').remove();
  });
  document.querySelector('[data-action="admin-products-action-11"]').addEventListener('click', function (event) {
    openModal('productModal')
  });
  document.querySelector('[data-action="admin-products-action-12"]').addEventListener('click', function (event) {
    if(confirmDelete('Pilot Master 18K Gold')) this.closest('tr').remove();
  });
  document.querySelector('[data-action="admin-products-action-13"]').addEventListener('click', function (event) {
    closeModal('productModal')
  });
  document.querySelector('[data-action="admin-products-action-14"]').addEventListener('submit', function (event) {
    event.preventDefault(); alert('Lưu sản phẩm thành công vào hệ thống!'); closeModal('productModal');
  });
  document.querySelector('[data-action="admin-products-action-15"]').addEventListener('click', function (event) {
    document.getElementById('productImageInput').click();
  });
  document.querySelector('[data-action="admin-products-action-16"]').addEventListener('click', function (event) {
    document.getElementById('productImageInput').click();
  });
  document.querySelector('[data-action="admin-products-action-17"]').addEventListener('click', function (event) {
    closeModal('productModal')
  });
  document.querySelector('[data-action="admin-products-action-18"]').addEventListener('click', function (event) {
    alert('Đã lưu bản nháp thành công!');
  });
});
