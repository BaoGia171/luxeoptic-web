<?php
require_once __DIR__ . '/includes/bootstrap.php';
$activeNav = 'home';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>LuxeOptic | Haute Lunetterie - Kính Thời Trang Cao Cấp</title>

  <!-- Google Fonts: Bodoni Moda, Inter, Montserrat & Material Icons -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bodoni+Moda:ital,opsz,wght@0,6..96,400..700;1,6..96,400..700&family=Inter:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/responsive.css">
</head>
<body class="bg-main text-primary antialiased">

  <!-- 2. SITE HEADER & NAVIGATION -->
  <?php require __DIR__ . '/includes/header.php'; ?>

  <main>
    <!-- 3. HERO BANNER SECTION -->
    <section class="home-hero relative min-h-85vh flex items-center justify-start bg-cover bg-center overflow-hidden" 
            >
      <div class="container relative z-10 py-20">
        <div class="max-w-2xl text-white">
          <div class="inline-flex items-center gap-2 bg-white-10 backdrop-blur-md px-3 py-1_5 mb-6 border border-white-20">
            <span class="w-2 h-2 rounded-full bg-secondary"></span>
            <span class="uppercase-spaced text-secondary text-11px">Bộ Sưu Tập Di Sản 2026</span>
          </div>
          
          <h1 class="font-headline text-4xl small-text-5xl desktop-text-6xl font-normal leading-1_15 mb-6 drop-shadow-md">
            Nâng Tầm Phong Cách Với Ánh Nhìn Mới
          </h1>
          
          <p class="font-body text-base small-text-lg text-gray-200 font-light mb-8 leading-relaxed max-w-xl">
            Nghệ thuật chế tác kính mắt thượng đỉnh Haute Lunetterie. Tinh hoa từ Acetate vùng Mazzucchelli nước Ý và Titanium siêu nhẹ của Nhật Bản, kiến tạo tuyên ngôn duy mỹ độc bản.
          </p>
          
          <div class="flex flex-wrap items-center gap-4">
            <a href="products.html" class="btn btn-secondary shadow-lg">
              Khám Phá Bộ Sưu Tập <span class="material-symbols-outlined text-base">arrow_forward</span>
            </a>
            <a href="product-detail.html?id=LX-AT-AV01" class="btn btn-outline border-white text-white hover-bg-white hover-text-black">
              Xem Kính Aviator Gold 01
            </a>
          </div>
        </div>
      </div>
    </section>

    <!-- 4. BRAND COMMITMENTS / TRUST METRICS -->
    <section class="bg-white border-y border-light py-8">
      <div class="container grid grid-cols-2 tablet-grid-cols-4 gap-6">
        <div class="flex items-center gap-4 p-2">
          <div class="w-12 h-12 rounded bg-main border border-light flex items-center justify-center text-secondary">
            <span class="material-symbols-outlined text-2xl">verified</span>
          </div>
          <div>
            <h4 class="font-label text-xs uppercase tracking-wider font-semibold">100% Chính Hãng</h4>
            <p class="text-xs text-muted">Xuất xứ Sabae, Nhật Bản & Ý</p>
          </div>
        </div>

        <div class="flex items-center gap-4 p-2">
          <div class="w-12 h-12 rounded bg-main border border-light flex items-center justify-center text-secondary">
            <span class="material-symbols-outlined text-2xl">workspace_premium</span>
          </div>
          <div>
            <h4 class="font-label text-xs uppercase tracking-wider font-semibold">Bảo Hành 24 Tháng</h4>
            <p class="text-xs text-muted">Căn chỉnh gọng trọn đời</p>
          </div>
        </div>

        <div class="flex items-center gap-4 p-2">
          <div class="w-12 h-12 rounded bg-main border border-light flex items-center justify-center text-secondary">
            <span class="material-symbols-outlined text-2xl">local_shipping</span>
          </div>
          <div>
            <h4 class="font-label text-xs uppercase tracking-wider font-semibold">Giao Hỏa Tốc 2H</h4>
            <p class="text-xs text-muted">Miễn phí đơn từ 500k</p>
          </div>
        </div>

        <div class="flex items-center gap-4 p-2">
          <div class="w-12 h-12 rounded bg-main border border-light flex items-center justify-center text-secondary">
            <span class="material-symbols-outlined text-2xl">home_repair_service</span>
          </div>
          <div>
            <h4 class="font-label text-xs uppercase tracking-wider font-semibold">Thử Kính Tại Nhà</h4>
            <p class="text-xs text-muted">Chọn 5 gọng thử miễn phí</p>
          </div>
        </div>
      </div>
    </section>

    <!-- 5. CATEGORY SHOWCASE -->
    <section class="py-20">
      <div class="container">
        <div class="flex flex-col small-flex-row small-items-end justify-between mb-12">
          <div>
            <span class="uppercase-spaced text-muted text-xs">Danh mục chế tác</span>
            <h2 class="font-headline text-3xl small-text-4xl font-normal mt-1">Kiến Trúc Tầm Nhìn</h2>
          </div>
          <a href="products.html" class="mt-4 small-mt-0 font-label text-xs uppercase tracking-widest text-primary hover-text-secondary flex items-center gap-1 font-semibold">
            Xem Tất Cả Danh Mục <span class="material-symbols-outlined text-sm">arrow_forward</span>
          </a>
        </div>

        <div class="grid grid-cols-1 small-grid-cols-2 desktop-grid-cols-4 gap-6">
          <!-- Category 1 -->
          <a href="products.html?category=kinh-ram" class="group relative bg-white border border-light p-4 flex flex-col hover-border-primary transition-all">
            <div class="aspect-4-3 bg-surface-low overflow-hidden mb-4">
              <img src="assets/images/cat-sunglasses.jpg" alt="Kính Râm" class="w-full h-full object-cover group-hover-scale-105 transition-transform duration-500">
            </div>
            <span class="badge badge-dark self-start mb-2">Bán chạy nhất</span>
            <h3 class="font-headline text-xl mb-1">Kính Râm</h3>
            <p class="text-xs text-muted mb-4">Tròng chống lóa UV400 phân cực cao cấp mạ gương.</p>
            <span class="font-label text-11px uppercase tracking-wider text-primary font-semibold mt-auto flex items-center gap-1 group-hover-text-secondary">
              Khám Phá (18 SP) <span class="material-symbols-outlined text-xs">arrow_forward</span>
            </span>
          </a>

          <!-- Category 2 -->
          <a href="products.html?category=gong-kinh-can" class="group relative bg-white border border-light p-4 flex flex-col hover-border-primary transition-all">
            <div class="aspect-4-3 bg-surface-low overflow-hidden mb-4">
              <img src="assets/images/cat-optical.jpg" alt="Gọng Kính Cận" class="w-full h-full object-cover group-hover-scale-105 transition-transform duration-500">
            </div>
            <span class="badge badge-gold self-start mb-2">Titanium Nhật</span>
            <h3 class="font-headline text-xl mb-1">Gọng Kính Cận</h3>
            <p class="text-xs text-muted mb-4">Gọng siêu nhẹ từ 4.8g cho trải nghiệm êm ái cả ngày.</p>
            <span class="font-label text-11px uppercase tracking-wider text-primary font-semibold mt-auto flex items-center gap-1 group-hover-text-secondary">
              Khám Phá (14 SP) <span class="material-symbols-outlined text-xs">arrow_forward</span>
            </span>
          </a>

          <!-- Category 3 -->
          <a href="products.html?category=chong-anh-sang-xanh" class="group relative bg-white border border-light p-4 flex flex-col hover-border-primary transition-all">
            <div class="aspect-4-3 bg-surface-low overflow-hidden mb-4">
              <img src="assets/images/cat-blueblock.jpg" alt="Chống Ánh Sáng Xanh" class="w-full h-full object-cover group-hover-scale-105 transition-transform duration-500">
            </div>
            <span class="badge badge-dark self-start mb-2">Bảo vệ mắt</span>
            <h3 class="font-headline text-xl mb-1">Chống Ánh Sáng Xanh</h3>
            <p class="text-xs text-muted mb-4">Bảo vệ mắt tối ưu trước máy tính và điện thoại thông minh.</p>
            <span class="font-label text-11px uppercase tracking-wider text-primary font-semibold mt-auto flex items-center gap-1 group-hover-text-secondary">
              Khám Phá (10 SP) <span class="material-symbols-outlined text-xs">arrow_forward</span>
            </span>
          </a>

          <!-- Category 4 -->
          <a href="products.html?category=unisex" class="group relative bg-white border border-light p-4 flex flex-col hover-border-primary transition-all">
            <div class="aspect-4-3 bg-surface-low overflow-hidden mb-4">
              <img src="assets/images/cat-unisex.jpg" alt="Kính Unisex" class="w-full h-full object-cover group-hover-scale-105 transition-transform duration-500">
            </div>
            <span class="badge badge-gold self-start mb-2">Atelier Limited</span>
            <h3 class="font-headline text-xl mb-1">Kính Unisex</h3>
            <p class="text-xs text-muted mb-4">Phong cách phi giới tính, góc cạnh điêu khắc thời thượng.</p>
            <span class="font-label text-11px uppercase tracking-wider text-primary font-semibold mt-auto flex items-center gap-1 group-hover-text-secondary">
              Khám Phá (6 SP) <span class="material-symbols-outlined text-xs">arrow_forward</span>
            </span>
          </a>
        </div>
      </div>
    </section>

    <!-- 6. FEATURED PRODUCTS (SẢN PHẨM NỔI BẬT) -->
    <section class="py-16 bg-surface-low border-t border-light">
      <div class="container">
        <div class="text-center max-w-xl mx-auto mb-12">
          <span class="uppercase-spaced text-muted">Dòng sản phẩm tiêu biểu</span>
          <h2 class="font-headline text-3xl small-text-4xl font-normal mt-1 mb-3">Sản Phẩm Nổi Bật</h2>
          <p class="text-sm text-muted">Các thiết kế kính mắt được chế tác thủ công tinh xảo, đồng bộ giá niêm yết trên toàn hệ thống LuxeOptic.</p>
        </div>

        <div class="product-grid grid-4">
          <!-- Card 1: Aviator Titanium Gold 01 -->
          <div class="product-card">
            <div class="product-thumb">
              <div class="product-badge-group">
                <span class="badge badge-gold">Best Seller</span>
                <span class="badge badge-sale">-18%</span>
              </div>
              <button class="product-wishlist-btn" title="Yêu thích">
                <span class="material-symbols-outlined text-sm">favorite</span>
              </button>
              <a href="product-detail.html?id=LX-AT-AV01">
                <img src="assets/images/aviator-main.jpg" alt="Aviator Titanium Gold 01">
              </a>
            </div>
            <div class="product-info">
              <span class="product-series">Atelier Titanium Series</span>
              <h3 class="product-title"><a href="product-detail.html?id=LX-AT-AV01">Aviator Titanium Gold 01</a></h3>
              <div class="product-swatches">
                <span class="swatch-secondary swatch-dot active" title="Vàng Gold 18K"></span>
                <span class="swatch-primary swatch-dot" title="Đen Matte"></span>
                <span class="swatch-silver swatch-dot" title="Bạc Platinum"></span>
              </div>
              <div class="product-price-row">
                <div>
                  <span class="product-price">1.350.000đ</span>
                  <span class="product-old-price">1.650.000đ</span>
                </div>
                <a href="product-detail.html?id=LX-AT-AV01" class="btn btn-primary btn-sm">Xem Chi Tiết</a>
              </div>
            </div>
          </div>

          <!-- Card 2: Titanium Minimalist Noir -->
          <div class="product-card">
            <div class="product-thumb">
              <div class="product-badge-group">
                <span class="badge badge-dark">Mới Về</span>
              </div>
              <button class="product-wishlist-btn" title="Yêu thích">
                <span class="material-symbols-outlined text-sm">favorite</span>
              </button>
              <a href="product-detail.html?id=titanium-minimalist-noir">
                <img src="assets/images/round-noir.jpg" alt="Titanium Minimalist Noir">
              </a>
            </div>
            <div class="product-info">
              <span class="product-series">Bauhaus Minimal Series</span>
              <h3 class="product-title"><a href="product-detail.html?id=titanium-minimalist-noir">Titanium Minimalist Noir</a></h3>
              <div class="product-swatches">
                <span class="swatch-primary swatch-dot active" title="Đen"></span>
                <span class="swatch-secondary swatch-dot" title="Vàng"></span>
              </div>
              <div class="product-price-row">
                <div>
                  <span class="product-price">1.150.000đ</span>
                </div>
                <a href="product-detail.html?id=titanium-minimalist-noir" class="btn btn-primary btn-sm">Xem Chi Tiết</a>
              </div>
            </div>
          </div>

          <!-- Card 3: Cat-Eye Havana Acetate -->
          <div class="product-card">
            <div class="product-thumb">
              <div class="product-badge-group">
                <span class="badge badge-sale">-15%</span>
              </div>
              <button class="product-wishlist-btn" title="Yêu thích">
                <span class="material-symbols-outlined text-sm">favorite</span>
              </button>
              <a href="product-detail.html?id=cat-eye-havana-acetate">
                <img src="assets/images/cat-eye.jpg" alt="Cat-Eye Havana Acetate">
              </a>
            </div>
            <div class="product-info">
              <span class="product-series">Italian Acetate Couture</span>
              <h3 class="product-title"><a href="product-detail.html?id=cat-eye-havana-acetate">Cat-Eye Havana Acetate</a></h3>
              <div class="product-swatches">
                <span class="swatch-tortoise swatch-dot active" title="Đồi mồi"></span>
                <span class="swatch-primary swatch-dot" title="Đen tuyền"></span>
              </div>
              <div class="product-price-row">
                <div>
                  <span class="product-price">980.000đ</span>
                  <span class="product-old-price">1.150.000đ</span>
                </div>
                <a href="product-detail.html?id=cat-eye-havana-acetate" class="btn btn-primary btn-sm">Xem Chi Tiết</a>
              </div>
            </div>
          </div>

          <!-- Card 4: Round Blue-Blocker Silver -->
          <div class="product-card">
            <div class="product-thumb">
              <div class="product-badge-group">
                <span class="badge badge-dark">Chống Ánh Sáng Xanh</span>
              </div>
              <button class="product-wishlist-btn" title="Yêu thích">
                <span class="material-symbols-outlined text-sm">favorite</span>
              </button>
              <a href="product-detail.html?id=round-blue-blocker-silver">
                <img src="assets/images/blue-blocker.jpg" alt="Round Blue-Blocker Silver">
              </a>
            </div>
            <div class="product-info">
              <span class="product-series">Digital Optical Shield</span>
              <h3 class="product-title"><a href="product-detail.html?id=round-blue-blocker-silver">Round Blue-Blocker Silver</a></h3>
              <div class="product-swatches">
                <span class="swatch-silver swatch-dot active" title="Bạc"></span>
                <span class="swatch-secondary swatch-dot" title="Vàng"></span>
              </div>
              <div class="product-price-row">
                <div>
                  <span class="product-price">850.000đ</span>
                </div>
                <a href="product-detail.html?id=round-blue-blocker-silver" class="btn btn-primary btn-sm">Xem Chi Tiết</a>
              </div>
            </div>
          </div>
        </div>

        <div class="text-center mt-12">
          <a href="products.html" class="btn btn-outline border-black px-10">
            Xem Toàn Bộ 48 Mẫu Kính Mắt <span class="material-symbols-outlined text-sm">arrow_forward</span>
          </a>
        </div>
      </div>
    </section>

    <!-- 7. EDITORIAL ATELIER BANNER -->
    <section class="home-showroom py-24 bg-primary text-white relative overflow-hidden" 
            >
      <div class="container text-center max-w-3xl">
        <span class="uppercase-spaced text-secondary text-xs">Triết lý thiết kế</span>
        <blockquote class="font-headline text-3xl small-text-4xl tablet-text-5xl font-normal my-6 leading-tight">
          “Chiếc kính không chỉ là phụ kiện, đó là tuyên ngôn phong cách của bạn.”
        </blockquote>
        <p class="text-gray-300 font-light text-base mb-8 max-w-xl mx-auto">
          Trải nghiệm không gian Atelier trưng bày độc bản tại trung tâm Hà Nội & TP. Hồ Chí Minh với dịch vụ đo khúc xạ quang học chuẩn y khoa quốc tế.
        </p>
        <div class="flex flex-wrap justify-center gap-4">
          <a href="products.html" class="btn btn-secondary">Đặt Lịch Thử Kính Tại Nhà</a>
          <a href="auth.html" class="btn btn-outline border-white text-white hover-bg-white hover-text-black">Đăng nhập để mua sắm</a>
        </div>
      </div>
    </section>
  </main>

  <!-- 8. FOOTER -->
  <?php require __DIR__ . '/includes/footer.php'; ?>

  <script src="assets/js/main.js"></script>
  <script src="assets/js/page-index.js"></script>
</body>
</html>
