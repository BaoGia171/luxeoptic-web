// Tương tác riêng của trang my-orders

function selectOrder(orderId) {
      document.getElementById('emptyOrdersView').classList.add('hidden');
      document.getElementById('card-LX-8891').classList.remove('border-2', 'border-black', 'bg-amber-50-20');
      document.getElementById('card-LX-8720').classList.remove('border-2', 'border-black', 'bg-amber-50-20');
      document.getElementById('card-LX-8891').classList.add('border', 'border-light', 'bg-white');
      document.getElementById('card-LX-8720').classList.add('border', 'border-light', 'bg-white');

      const selectedCard = document.getElementById('card-' + orderId);
      if (selectedCard) {
        selectedCard.classList.remove('border-light', 'bg-white');
        selectedCard.classList.add('border-2', 'border-black', 'bg-amber-50-20');
      }

      if (orderId === 'LX-8891') {
        document.getElementById('detail-LX-8891').classList.remove('hidden');
        document.getElementById('detail-LX-8720').classList.add('hidden');
      } else {
        document.getElementById('detail-LX-8891').classList.add('hidden');
        document.getElementById('detail-LX-8720').classList.remove('hidden');
      }
    }

    function toggleEmptyState() {
      const emptyView = document.getElementById('emptyOrdersView');
      const detail8891 = document.getElementById('detail-LX-8891');
      const detail8720 = document.getElementById('detail-LX-8720');

      if (emptyView.classList.contains('hidden')) {
        emptyView.classList.remove('hidden');
        detail8891.classList.add('hidden');
        detail8720.classList.add('hidden');
      } else {
        emptyView.classList.add('hidden');
        detail8891.classList.remove('hidden');
      }
    }

document.addEventListener('DOMContentLoaded', function () {
  document.querySelector('[data-action="my-orders-action-1"]').addEventListener('click', function (event) {
    document.getElementById('successNotice').style.display='none'
  });
  document.querySelector('[data-action="my-orders-action-2"]').addEventListener('click', function (event) {
    selectOrder('LX-8891')
  });
  document.querySelector('[data-action="my-orders-action-3"]').addEventListener('click', function (event) {
    selectOrder('LX-8720')
  });
  document.querySelector('[data-action="my-orders-action-4"]').addEventListener('click', function (event) {
    toggleEmptyState()
  });
  document.querySelector('[data-action="my-orders-action-5"]').addEventListener('click', function (event) {
    window.print()
  });
  document.querySelector('[data-action="my-orders-action-6"]').addEventListener('click', function (event) {
    alert('Đã gửi yêu cầu hỗ trợ đơn hàng #LX-8891 đến bộ phận Concierge. Chúng tôi sẽ gọi lại trong 5 phút!')
  });
  document.querySelector('[data-action="my-orders-action-7"]').addEventListener('click', function (event) {
    alert('Bạn chỉ có thể hủy đơn trước khi đơn hàng được bàn giao cho đơn vị vận chuyển. Liên hệ hotline 1900 888 999 để được hỗ trợ.')
  });
  document.querySelector('[data-action="my-orders-action-8"]').addEventListener('click', function (event) {
    window.print()
  });
  document.querySelector('[data-action="my-orders-action-9"]').addEventListener('click', function (event) {
    toggleEmptyState()
  });
});
