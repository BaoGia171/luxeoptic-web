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
            Tuyệt tác quang học thủ công và biểu tượng phong cách vượt thời gian. Đẳng cấp tinh tế dành cho những tâm hồn duy mỹ.
          </p>
          <div class="text-xs text-gray-400 space-y-1">
            <p><strong>Hotline:</strong> 1900 888 999 (8:00 - 22:00)</p>
            <p><strong>Email:</strong> contact@luxeoptic.vn</p>
            <p><strong>Flagship Store:</strong> 68 Phố Tràng Tiền, Hoàn Kiếm, Hà Nội</p>
          </div>
        </div>

        <!-- Col 2: Dịch vụ khách hàng -->
        <div>
          <h4 class="footer-title">Dịch Vụ Khách Hàng</h4>
          <ul class="footer-links">
            <li><a href="#">Giới thiệu thương hiệu</a></li>
            <li><a href="#">Chính sách bảo hành 24 tháng</a></li>
            <li><a href="#">Hướng dẫn chọn size gọng</a></li>
            <li><a href="#">Chính sách đổi trả & hoàn tiền</a></li>
            <li><a href="#">Dịch vụ đo thị lực quốc tế</a></li>
          </ul>
        </div>

        <!-- Col 3: Danh mục -->
        <div>
          <h4 class="footer-title">Bộ Sưu Tập</h4>
          <ul class="footer-links">
            <li><a href="<?= e(url('products.php?category=kinh-ram')) ?>">Kính râm phân cực</a></li>
            <li><a href="<?= e(url('products.php?category=gong-kinh-can')) ?>">Gọng Titanium cao cấp</a></li>
            <li><a href="<?= e(url('products.php?category=gong-kinh-can')) ?>">Gọng Acetate thủ công</a></li>
            <li><a href="<?= e(url('products.php?category=chong-anh-sang-xanh')) ?>">Tròng chống ánh sáng xanh</a></li>
            <li><a href="<?= e(url('products.php?sort=newest')) ?>">Phiên bản giới hạn Atelier</a></li>
          </ul>
        </div>

        <!-- Col 4: Newsletter & Admin link -->
        <div>
          <h4 class="footer-title">Đăng Ký Nhận Ưu Đãi 10%</h4>
          <p class="text-xs text-gray-400 mb-3">Nhận thông tin bộ sưu tập độc bản và các sự kiện riêng tư của LuxeOptic.</p>
          <form class="flex gap-1 mb-4" data-action="index-action-1">
            <input type="email" placeholder="Nhập địa chỉ email..." required class="h-10 px-3 text-xs bg-gray-900 border border-gray-700 text-white w-full outline-none focus-border-secondary">
            <button type="submit" class="btn btn-secondary btn-sm px-4">GỬI</button>
          </form>
          
        </div>
      </div>

      <!-- Footer Bottom -->
      <div class="footer-bottom">
        <p>© 2026 LuxeOptic Atelier. Bản quyền thuộc về môn Lập trình Web - BTL Nhóm.</p>
        <div class="flex items-center gap-6">
          <a href="#" class="hover-text-white">Điều khoản sử dụng</a>
          <a href="#" class="hover-text-white">Bảo mật thông tin</a>
          <a href="#" class="hover-text-white">Chứng chỉ quang học</a>
        </div>
      </div>
    </div>
  </footer>
