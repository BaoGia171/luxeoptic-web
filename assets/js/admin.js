/**
 * LUXEOPTIC - JAVASCRIPT ADMIN PORTAL
 * Quản lý mở/đóng Modal CRUD, xác nhận xóa, preview ảnh upload, đổi trạng thái
 */

function openModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.classList.add('open');
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
  }
}

function closeModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.classList.remove('open');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
  }
}

function confirmDelete(itemTitle) {
  return confirm(`Bạn có chắc chắn muốn xóa "${itemTitle}" không?\nHành động này không thể hoàn tác!`);
}

document.addEventListener('DOMContentLoaded', function () {
  // Đóng modal khi click ra ngoài vùng backdrop
  document.querySelectorAll('.modal-overlay').forEach((overlay) => {
    overlay.addEventListener('click', function (e) {
      if (e.target === this) {
        this.classList.remove('open');
        this.style.display = 'none';
        document.body.style.overflow = 'auto';
      }
    });
  });


});

// Thu gọn menu quản trị trên màn hình nhỏ.
document.addEventListener('DOMContentLoaded', function () {
  const button = document.querySelector('.admin-menu-toggle');
  const sidebar = document.getElementById('adminMenu');
  const backdrop = document.querySelector('.sidebar-backdrop');
  if (!button || !sidebar || !backdrop) return;

  function closeSidebar() {
    sidebar.classList.remove('sidebar-open');
    backdrop.hidden = true;
    button.setAttribute('aria-expanded', 'false');
    document.body.classList.remove('menu-visible');
  }
  button.addEventListener('click', function () {
    const opened = sidebar.classList.toggle('sidebar-open');
    backdrop.hidden = !opened;
    button.setAttribute('aria-expanded', String(opened));
    document.body.classList.toggle('menu-visible', opened);
  });
  backdrop.addEventListener('click', closeSidebar);
  document.querySelector('.sidebar-close').addEventListener('click', function () {
    closeSidebar(); button.focus();
  });
  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
      closeSidebar();
      document.querySelectorAll('.modal-overlay.open').forEach(function (modal) { closeModal(modal.id); });
      button.focus();
    }
  });
  window.addEventListener('resize', function () {
    if (window.innerWidth >= 1024) closeSidebar();
  });
});
