<?php
// Nap bootstrap.php truoc partial nay. TV3 truyen $activeNav; TV4 truyen $cartCount.
$activeNav = $activeNav ?? '';
?>
<header class="site-header">
    <div class="container header-inner">
      <!-- Logo -->
      <a href="<?= e(url('index.php')) ?>" class="brand-logo">
        <div class="logo-mark">L</div>
        <div class="flex flex-col">
          <span class="logo-text">LuxeOptic</span>
          <span class="text-9px uppercase tracking-widest text-muted font-semibold negative-mt-1">Haute Lunetterie</span>
        </div>
      </a>

      <!-- Menu Navigation -->
      <nav id="siteMenu" class="site-menu hidden desktop-flex items-center gap-8">
        <a href="<?= e(url('index.php')) ?>" class="nav-link<?= $activeNav === 'home' ? ' active' : '' ?>"<?= $activeNav === 'home' ? ' aria-current="page"' : '' ?>>Trang chủ</a>
        <a href="<?= e(url('products.php?category=kinh-ram')) ?>" class="nav-link<?= $activeNav === 'kinh-ram' ? ' active' : '' ?>"<?= $activeNav === 'kinh-ram' ? ' aria-current="page"' : '' ?>>Kính Râm</a>
        <a href="<?= e(url('products.php?category=gong-kinh-can')) ?>" class="nav-link<?= $activeNav === 'gong-kinh-can' ? ' active' : '' ?>"<?= $activeNav === 'gong-kinh-can' ? ' aria-current="page"' : '' ?>>Gọng Kính Cận</a>
        <a href="<?= e(url('products.php?category=chong-anh-sang-xanh')) ?>" class="nav-link<?= $activeNav === 'chong-anh-sang-xanh' ? ' active' : '' ?>"<?= $activeNav === 'chong-anh-sang-xanh' ? ' aria-current="page"' : '' ?>>Chống Ánh Sáng Xanh</a>
        <a href="<?= e(url('products.php?sort=newest')) ?>" class="nav-link<?= $activeNav === 'newest' ? ' active' : '' ?>"<?= $activeNav === 'newest' ? ' aria-current="page"' : '' ?>>Hàng mới</a>
      </nav>

      <!-- Utilities (Search, Wishlist, Account, Cart) -->
      <div class="header-actions">
        <button type="button" class="menu-toggle" aria-controls="siteMenu" aria-expanded="false" aria-label="Mở menu">
          <span class="material-symbols-outlined">menu</span>
        </button>
        <div class="search-box">
          <span class="material-symbols-outlined text-gray-500 text-lg">search</span>
          <input type="text" placeholder="Tìm kiếm gọng kính, mã SKU...">
        </div>
        
        <a href="<?= e(url('auth.php')) ?>" class="action-icon" title="Tài khoản">
          <span class="material-symbols-outlined text-2xl">account_circle</span>
        </a>

        <a href="<?= e(url('cart.php')) ?>" class="action-icon" title="Giỏ hàng">
          <span class="material-symbols-outlined text-2xl">shopping_bag</span>
          <span class="action-badge"><?= max(0, (int) ($cartCount ?? 0)) ?></span>
        </a>
      </div>
    </div>
  </header>
