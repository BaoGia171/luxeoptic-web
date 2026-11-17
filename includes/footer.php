<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <!-- Col 1: Brand Info -->
            <div>
                <div class="brand-logo mb-4">
                    <div class="logo-mark">L</div>
                    <span class="logo-text text-white">LuxeOptic</span>
                </div>
                <p class="text-xs text-gray-400 leading-relaxed mb-6">
                    Tuyệt tác quang học thủ công và biểu tượng phong cách vượt thời gian. Đẳng cấp tinh tế
                    dành cho những tâm hồn duy mỹ.
                </p>
                <div class="text-xs text-gray-400 space-y-1">
                    <p>
                        <strong>Hotline:</strong> 1900 888 999 (8:00 - 22:00)
                    </p>
                    <p>
                        <strong>Email:</strong> contact@luxeoptic.vn
                    </p>
                    <p>
                        <strong>Flagship Store:</strong> 68 Phố Tràng Tiền, Hoàn Kiếm, Hà Nội
                    </p>
                </div>
            </div>

            <!-- Col 2: Dịch vụ khách hàng -->
            <div>
                <h4 class="footer-title">Dịch Vụ Khách Hàng</h4>
                <ul class="footer-links">
                    <li>
                        <a href="<?= e(url('information.php#about')) ?>">Giới thiệu LuxeOptic</a>
                    </li>
                    <li>
                        <a href="<?= e(url('information.php#sizing')) ?>">Hướng dẫn chọn kích thước gọng</a>
                    </li>
                    <li>
                        <a href="<?= e(url('information.php#terms')) ?>">Điều khoản sử dụng</a>
                    </li>
                    <li>
                        <a href="<?= e(url('information.php#privacy')) ?>">Thông tin dữ liệu tài khoản</a>
                    </li>
                </ul>
            </div>

            <!-- Col 3: Danh mục -->
            <div>
                <h4 class="footer-title">Bộ Sưu Tập</h4>
                <ul class="footer-links">
                    <li>
                        <a href="<?= e(url('products.php?category=kinh-ram')) ?>">Kính râm phân cực</a>
                    </li>
                    <li>
                        <a href="<?= e(url('products.php?category=gong-kinh-can')) ?>">Gọng kính cận</a>
                    </li>
                    <li>
                        <a href="<?= e(url('products.php?category=chong-anh-sang-xanh')) ?>">Tròng chống ánh sáng xanh</a>
                    </li>
                    <li>
                        <a href="<?= e(url('products.php?category=phien-ban-gioi-han')) ?>">Phiên bản giới hạn Atelier</a>
                    </li>
                </ul>
            </div>

            <!-- Col 4: Liên kết mua sắm và tài khoản -->
            <div>
                <h4 class="footer-title">Mua Sắm &amp; Tài Khoản</h4>
                <ul class="footer-links">
                    <li>
                        <a href="<?= e(url('products.php')) ?>">Xem sản phẩm</a>
                    </li>
                    <li>
                        <a href="<?= e(url('cart.php')) ?>">Giỏ hàng</a>
                    </li>
                    <li>
                        <a href="<?= e(url('my-orders.php')) ?>">Đơn hàng của tôi</a>
                    </li>
                    <li>
                        <a href="<?= e(url('profile.php')) ?>">Hồ sơ cá nhân</a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Footer Bottom -->
        <div class="footer-bottom">
            <p>© 2026 LuxeOptic Atelier. Bản quyền thuộc về môn Lập trình Web - BTL Nhóm.</p>
            <div class="flex items-center gap-6">
                <a href="<?= e(url('information.php#terms')) ?>" class="hover-text-white">Điều khoản sử dụng</a>
                <a href="<?= e(url('information.php#privacy')) ?>" class="hover-text-white">Dữ liệu tài khoản</a>
            </div>
        </div>
    </div>
</footer>
