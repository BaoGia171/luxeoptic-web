// Tương tác riêng của trang admin-orders

function openOrderModal(orderId) {
      openModal('orderDetailModal');
    }

document.addEventListener('DOMContentLoaded', function () {
  document.querySelector('[data-action="admin-orders-action-1"]').addEventListener('click', function (event) {
    alert('Đang xuất danh sách 148 đơn hàng ra file Excel (XLSX)...')
  });
  document.querySelector('[data-action="admin-orders-action-2"]').addEventListener('click', function (event) {
    openOrderModal('LX-8891')
  });
  document.querySelector('[data-action="admin-orders-action-3"]').addEventListener('click', function (event) {
    openOrderModal('LX-8891')
  });
  document.querySelector('[data-action="admin-orders-action-4"]').addEventListener('click', function (event) {
    alert('Đã cập nhật trạng thái đơn #LX-8891: Đã bàn giao GHTK Express')
  });
  document.querySelector('[data-action="admin-orders-action-5"]').addEventListener('click', function (event) {
    openOrderModal('LX-8890')
  });
  document.querySelector('[data-action="admin-orders-action-6"]').addEventListener('click', function (event) {
    openOrderModal('LX-8890')
  });
  document.querySelector('[data-action="admin-orders-action-7"]').addEventListener('click', function (event) {
    alert('Đang in phiếu đóng hàng đơn #LX-8890')
  });
  document.querySelector('[data-action="admin-orders-action-8"]').addEventListener('click', function (event) {
    openOrderModal('LX-8889')
  });
  document.querySelector('[data-action="admin-orders-action-9"]').addEventListener('click', function (event) {
    openOrderModal('LX-8889')
  });
  document.querySelector('[data-action="admin-orders-action-10"]').addEventListener('click', function (event) {
    alert('Mã vận đơn: GHTK28491823 - Đang trên đường tới bưu cục Hải Châu Đà Nẵng')
  });
  document.querySelector('[data-action="admin-orders-action-11"]').addEventListener('click', function (event) {
    openOrderModal('LX-8888')
  });
  document.querySelector('[data-action="admin-orders-action-12"]').addEventListener('click', function (event) {
    openOrderModal('LX-8888')
  });
  document.querySelector('[data-action="admin-orders-action-13"]').addEventListener('click', function (event) {
    alert('Đang tải hóa đơn điện tử e-Invoice #VAT-001294')
  });
  document.querySelector('[data-action="admin-orders-action-14"]').addEventListener('click', function (event) {
    openOrderModal('LX-8887')
  });
  document.querySelector('[data-action="admin-orders-action-15"]').addEventListener('click', function (event) {
    openOrderModal('LX-8887')
  });
  document.querySelector('[data-action="admin-orders-action-16"]').addEventListener('click', function (event) {
    alert('Đang tải hóa đơn điện tử e-Invoice #VAT-001293')
  });
  document.querySelector('[data-action="admin-orders-action-17"]').addEventListener('click', function (event) {
    closeModal('orderDetailModal')
  });
  document.querySelector('[data-action="admin-orders-action-18"]').addEventListener('click', function (event) {
    alert('Đang in phiếu xuất kho & nhãn gửi hàng GHTK cho đơn #LX-8891...')
  });
  document.querySelector('[data-action="admin-orders-action-19"]').addEventListener('click', function (event) {
    alert('Đã chuyển trạng thái sang [Đang vận chuyển]!'); closeModal('orderDetailModal');
  });
});
