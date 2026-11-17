<?php
require_once __DIR__ . '/includes/shop.php';
$activeNav = 'home';
$homeCategories = query_rows(
    'SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id=c.id AND p.is_active=1) AS product_count FROM categories c WHERE c.is_active=1 ORDER BY c.sort_order,c.id',
);
$categoryImages = [
    'kinh-ram' => 'cat-sunglasses.jpg',
    'gong-kinh-can' => 'cat-optical.jpg',
    'chong-anh-sang-xanh' => 'cat-blueblock.jpg',
    'phien-ban-gioi-han' => 'cat-unisex.jpg',
];
$heroProduct =
    query_rows(product_select() . ' WHERE p.sku=? AND p.is_active=1 AND c.is_active=1', [
        'LX-AT-AV01',
    ])[0] ?? null;
$featuredProducts = query_rows(
    product_select() .
        ' WHERE p.is_active=1 AND c.is_active=1 AND p.is_featured=1 ORDER BY p.created_at DESC,p.id DESC LIMIT 4',
);
$totalVisible = array_sum(array_column($homeCategories, 'product_count'));
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
    <link
        href="https://fonts.googleapis.com/css2?family=Bodoni+Moda:ital,opsz,wght@0,6..96,400..700;1,6..96,400..700&family=Inter:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200"
        rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <link rel="stylesheet" href="assets/css/commerce.css">
    <link rel="stylesheet" href="assets/css/shopping-tools.css">
    <link rel="stylesheet" href="assets/css/theme.css">
    <link rel="stylesheet" href="assets/css/ui-effects.css">
    <script src="assets/js/theme.js"></script>
</head>
<body class="bg-main text-primary antialiased">

    <!-- 2. SITE HEADER & NAVIGATION -->
    <?php require __DIR__ . '/includes/header.php'; ?>

    <main>
        <!-- 3. HERO BANNER SECTION -->
        <section
            class="home-hero relative min-h-85vh flex items-center justify-start bg-cover bg-center overflow-hidden">
            <div class="container relative z-10 py-20">
                <div class="max-w-2xl text-white">
                    <div
                        class="inline-flex items-center gap-2 bg-white-10 backdrop-blur-md px-3 py-1_5 mb-6 border border-white-20">
                        <span class="w-2 h-2 rounded-full bg-secondary"></span>
                        <span class="uppercase-spaced text-secondary text-11px">Bộ Sưu Tập Di Sản 2026</span>
                    </div>

                    <h1
                        class="font-headline text-4xl small-text-5xl desktop-text-6xl font-normal leading-1_15 mb-6 drop-shadow-md">
                        Nâng Tầm Phong Cách Với Ánh Nhìn Mới
                    </h1>

                    <p
                        class="font-body text-base small-text-lg text-gray-200 font-light mb-8 leading-relaxed max-w-xl">
                        Nghệ thuật chế tác kính mắt thượng đỉnh Haute Lunetterie. Tinh hoa từ Acetate vùng
                        Mazzucchelli nước Ý và Titanium siêu nhẹ của Nhật Bản, kiến tạo tuyên ngôn duy mỹ độc
                        bản.
                    </p>

                    <div class="flex flex-wrap items-center gap-4">
                        <a href="products.php" class="btn btn-secondary shadow-lg">
                            Khám Phá Bộ Sưu Tập <span class="material-symbols-outlined text-base">
                                arrow_forward</span>
                        </a>
                        <a href="<?= e(url($heroProduct ? 'product-detail.php?id='.$heroProduct['id'] : 'products.php')) ?>"
                            class="btn btn-outline border-white text-white hover-bg-white hover-text-black">
                            <?= $heroProduct ? 'Xem '.e($heroProduct['name']) : 'Xem các mẫu kính' ?>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- 4. BRAND COMMITMENTS / TRUST METRICS -->
        <section class="bg-white border-y border-light py-8">
            <div class="container grid grid-cols-2 tablet-grid-cols-4 gap-6">
                <div class="flex items-center gap-4 p-2">
                    <div
                        class="w-12 h-12 rounded bg-main border border-light flex items-center justify-center text-secondary">
                        <span class="material-symbols-outlined text-2xl">verified</span>
                    </div>
                    <div>
                        <h4 class="font-label text-xs uppercase tracking-wider font-semibold">100% Chính Hãng
                        </h4>
                        <p class="text-xs text-muted">Xuất xứ Sabae, Nhật Bản & Ý</p>
                    </div>
                </div>

                <div class="flex items-center gap-4 p-2">
                    <div
                        class="w-12 h-12 rounded bg-main border border-light flex items-center justify-center text-secondary">
                        <span class="material-symbols-outlined text-2xl">workspace_premium</span>
                    </div>
                    <div>
                        <h4 class="font-label text-xs uppercase tracking-wider font-semibold">Bảo Hành 24
                            Tháng</h4>
                        <p class="text-xs text-muted">Căn chỉnh gọng trọn đời</p>
                    </div>
                </div>

                <div class="flex items-center gap-4 p-2">
                    <div
                        class="w-12 h-12 rounded bg-main border border-light flex items-center justify-center text-secondary">
                        <span class="material-symbols-outlined text-2xl">local_shipping</span>
                    </div>
                    <div>
                        <h4 class="font-label text-xs uppercase tracking-wider font-semibold">Giao Hỏa Tốc 2H
                        </h4>
                        <p class="text-xs text-muted">Miễn phí đơn từ 500k</p>
                    </div>
                </div>

                <div class="flex items-center gap-4 p-2">
                    <div
                        class="w-12 h-12 rounded bg-main border border-light flex items-center justify-center text-secondary">
                        <span class="material-symbols-outlined text-2xl">home_repair_service</span>
                    </div>
                    <div>
                        <h4 class="font-label text-xs uppercase tracking-wider font-semibold">Thử Kính Tại Nhà
                        </h4>
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
                        <h2 class="font-headline text-3xl small-text-4xl font-normal mt-1">Kiến Trúc Tầm Nhìn
                        </h2>
                    </div>
                    <a href="products.php"
                        class="mt-4 small-mt-0 font-label text-xs uppercase tracking-widest text-primary hover-text-secondary flex items-center gap-1 font-semibold">
                        Xem Tất Cả Danh Mục <span class="material-symbols-outlined text-sm">arrow_forward
                        </span>
                    </a>
                </div>

                <div class="grid grid-cols-1 small-grid-cols-2 desktop-grid-cols-4 gap-6">
                    <?php foreach($homeCategories as $category): ?>
                    <a href="<?= e(url('products.php?category='.$category['slug'])) ?>"
                        class="group relative bg-white border border-light p-4 flex flex-col hover-border-primary transition-all">
                        <div class="aspect-4-3 bg-surface-low overflow-hidden mb-4">
                            <img src="<?= e(image_url('assets/images/'.($categoryImages[$category['slug']]??'no-image.svg'))) ?>"
                                alt="<?= e($category['name']) ?>"
                                class="w-full h-full object-cover group-hover-scale-105 transition-transform duration-500">
                        </div>
                        <h3 class="font-headline text-xl mb-1"><?= e($category['name']) ?></h3>
                        <p class="text-xs text-muted mb-4"><?= e($category['description']??'') ?></p>
                        <span
                            class="font-label text-11px uppercase tracking-wider text-primary font-semibold mt-auto">
                            Khám phá (<?= (int)$category['product_count'] ?> mẫu)</span>
                    </a>
                    <?php endforeach ?>
                </div>
            </div>
        </section>

        <!-- 6. FEATURED PRODUCTS (SẢN PHẨM NỔI BẬT) -->
        <section class="py-16 bg-surface-low border-t border-light">
            <div class="container">
                <div class="text-center max-w-xl mx-auto mb-12">
                    <span class="uppercase-spaced text-muted">Dòng sản phẩm tiêu biểu</span>
                    <h2 class="font-headline text-3xl small-text-4xl font-normal mt-1 mb-3">Sản Phẩm Nổi Bật
                    </h2>
                    <p class="text-sm text-muted">Các thiết kế kính mắt được chế tác thủ công tinh xảo, đồng
                        bộ giá niêm yết trên toàn hệ thống LuxeOptic.</p>
                </div>

                <div class="catalog-grid">
                    <?php foreach($featuredProducts as $product) product_card($product, false); ?>
                </div>
                <?php if(!$featuredProducts): ?><p class="text-center">Chưa có sản phẩm nổi bật.</p>
                <?php endif ?>
                <div class="text-center mt-12">
                    <a href="<?= e(url('products.php')) ?>" class="btn btn-outline border-black px-10">Xem
                        toàn bộ <?= (int)$totalVisible ?> mẫu kính</a>
                </div>
            </div>
        </section>

        <!-- 7. EDITORIAL ATELIER BANNER -->
        <section class="home-showroom py-24 bg-primary text-white relative overflow-hidden">
            <div class="container text-center max-w-3xl">
                <span class="uppercase-spaced text-secondary text-xs">Triết lý thiết kế</span>
                <blockquote
                    class="font-headline text-3xl small-text-4xl tablet-text-5xl font-normal my-6 leading-tight">
                    “Chiếc kính không chỉ là phụ kiện, đó là tuyên ngôn phong cách của bạn.”
                </blockquote>
                <p class="text-gray-300 font-light text-base mb-8 max-w-xl mx-auto">
                    Trải nghiệm không gian Atelier trưng bày độc bản tại trung tâm Hà Nội & TP. Hồ Chí Minh
                    với dịch vụ đo khúc xạ quang học chuẩn y khoa quốc tế.
                </p>
                <div class="flex flex-wrap justify-center gap-4">
                    <a href="products.php" class="btn btn-secondary">Khám phá các mẫu kính</a>
                    <a href="<?= e(url(current_user() ? 'profile.php' : 'auth.php')) ?>"
                        class="btn btn-outline border-white text-white hover-bg-white hover-text-black">
                        <?= current_user() ? 'Tài khoản của tôi' : 'Đăng nhập để mua sắm' ?></a>
                </div>
            </div>
        </section>
    </main>

    <!-- 8. FOOTER -->
    <?php require __DIR__ . '/includes/footer.php'; ?>

    <script src="assets/js/main.js"></script>
    <script src="assets/js/ui-effects.js"></script>
    <script src="assets/js/chatbot.js"></script>
</body>
</html>
