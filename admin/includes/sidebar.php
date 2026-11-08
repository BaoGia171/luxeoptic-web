<?php
// Trang Admin phai nap bootstrap va goi require_admin() truoc khi xuat HTML.
require_admin();
$sidebarUser = current_user();
$activeAdmin = $activeAdmin ?? '';
?>
<button type="button" class="sidebar-backdrop" aria-label="Đóng menu" hidden></button>
  <aside class="admin-sidebar" id="adminMenu">
    <button type="button" class="sidebar-close" aria-label="Đóng menu">×</button>
    <div class="sidebar-brand">
      <div class="brand-logo-badge">L</div>
      <div class="flex flex-col">
        <span class="brand-name">LuxeOptic</span>
        <span class="portal-sub">Admin Portal</span>
      </div>
    </div>

    <!-- User Badge -->
    <div class="sidebar-user">
      <div class="sidebar-user-avatar">
        <span class="material-symbols-outlined text-lg">admin_panel_settings</span>
        <span class="status-dot"></span>
      </div>
      <div class="sidebar-user-info">
        <span class="sidebar-user-name"><?= e($sidebarUser['full_name']) ?></span>
        <span class="sidebar-user-role">Quản trị viên</span>
      </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="sidebar-nav">
      <div class="nav-section-title">Điều hướng quản trị</div>
      <a href="<?= e(url('admin/products.php')) ?>" class="sidebar-nav-item<?= $activeAdmin === 'products' ? ' active' : '' ?>"<?= $activeAdmin === 'products' ? ' aria-current="page"' : '' ?>>
        <div class="flex items-center gap-3">
          <span class="material-symbols-outlined text-lg">eyeglasses</span>
          <span>Quản lý Kính mắt</span>
        </div>
      </a>
      <a href="<?= e(url('admin/categories.php')) ?>" class="sidebar-nav-item<?= $activeAdmin === 'categories' ? ' active' : '' ?>"<?= $activeAdmin === 'categories' ? ' aria-current="page"' : '' ?>>
        <div class="flex items-center gap-3">
          <span class="material-symbols-outlined text-lg">category</span>
          <span>Quản lý Danh mục</span>
        </div>
      </a>
      <a href="<?= e(url('admin/orders.php')) ?>" class="sidebar-nav-item<?= $activeAdmin === 'orders' ? ' active' : '' ?>"<?= $activeAdmin === 'orders' ? ' aria-current="page"' : '' ?>>
        <div class="flex items-center gap-3">
          <span class="material-symbols-outlined text-lg">shopping_bag</span>
          <span>Quản lý Đơn hàng</span>
        </div>
      </a>
      <a href="<?= e(url('admin/users.php')) ?>" class="sidebar-nav-item<?= $activeAdmin === 'users' ? ' active' : '' ?>"<?= $activeAdmin === 'users' ? ' aria-current="page"' : '' ?>>
        <div class="flex items-center gap-3">
          <span class="material-symbols-outlined text-lg">manage_accounts</span>
          <span>Quản lý Tài khoản & Phân quyền</span>
        </div>
      </a>
    </nav>

    <!-- Sidebar Footer -->
    <div class="sidebar-footer space-y-1">
      <a href="<?= e(url('index.php')) ?>" class="sidebar-nav-item text-gray-400 hover-text-white">
        <span class="flex items-center gap-2"><span class="material-symbols-outlined text-sm">arrow_back</span> Xem trang bán hàng</span>
      </a>
      <form method="post" action="<?= e(url('actions/logout.php')) ?>">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <button type="submit" class="sidebar-nav-item text-red-400 hover-text-red-300">
          <span class="flex items-center gap-2"><span class="material-symbols-outlined text-sm">logout</span> Đăng xuất</span>
        </button>
      </form>
    </div>
  </aside>
