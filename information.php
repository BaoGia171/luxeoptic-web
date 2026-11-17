<?php
require __DIR__ . '/includes/shop.php';
page_start('Thông tin và hướng dẫn');
?>

<nav class="information-nav" aria-label="Nội dung hướng dẫn">
    <a href="#about">Giới thiệu</a>
    <a href="#sizing">Chọn kích thước</a>
    <a href="#terms">Điều khoản sử dụng</a>
    <a href="#privacy">Dữ liệu tài khoản</a>
</nav>

<article class="information-content">
    <section id="about" class="panel information-section">
        <h2>Giới thiệu LuxeOptic</h2>
        <p>LuxeOptic là website bán mắt kính mô phỏng của Nhóm 10 trong môn Lập trình Web. Bạn có thể tìm
            kiếm, so sánh kính, tạo tài khoản và trải nghiệm quy trình đặt hàng.</p>
        <p>Sản phẩm, hình ảnh và thông tin cửa hàng dùng để minh họa cho bài tập. Website không tiếp nhận giao
            dịch mua bán hoặc giao hàng thực tế.</p>
        <a href="<?= e(url('products.php')) ?>" class="btn btn-primary">Khám phá sản phẩm</a>
    </section>

    <section id="sizing" class="panel information-section">
        <h2>Hướng dẫn đọc kích thước gọng</h2>
        <p>Kích thước gọng thường gồm ba số đo. Ví dụ: chiều rộng một tròng 49 mm, chiều rộng cầu mũi 20 mm và
            chiều dài càng kính 145 mm.</p>
        <ul>
            <li>
                <strong>Chiều rộng tròng:</strong> khoảng ngang của một mắt kính.
            </li>
            <li>
                <strong>Chiều rộng cầu mũi:</strong> khoảng cách giữa hai tròng kính.
            </li>
            <li>
                <strong>Chiều dài càng:</strong> độ dài phần càng từ bản lề đến đuôi càng.
            </li>
        </ul>
        <p>Bạn có thể đối chiếu các số đo này với gọng đang đeo vừa. Những con số trên chỉ là ví dụ, không
            phải thông số của tất cả sản phẩm trên website. Khi mua kính thực tế, nên thử trực tiếp để kiểm
            tra độ vừa vặn.</p>
    </section>

    <section id="terms" class="panel information-section">
        <h2>Điều khoản sử dụng bản demo</h2>
        <ul>
            <li>Chỉ sử dụng dữ liệu thử khi đăng ký tài khoản và nhập thông tin giao nhận; không nhập dữ liệu
                cá nhân nhạy cảm.</li>
            <li>Giữ kín mật khẩu và sử dụng đúng tài khoản, quyền truy cập được cấp.</li>
            <li>Đơn hàng, voucher, COD và chuyển khoản đều phục vụ mô phỏng. Không chuyển tiền thật qua bản
                demo này.</li>
            <li>Khách có thể hủy đơn của mình khi đơn còn chờ xác nhận và chưa thanh toán. Đơn đã hoàn tất
                không có chức năng hoàn tiền.</li>
        </ul>
        <p>Các thông điệp quảng bá trong giao diện không phải cam kết bảo hành, vận chuyển hoặc dịch vụ thương
            mại thực tế.</p>
    </section>

    <section id="privacy" class="panel information-section">
        <h2>Thông tin về dữ liệu tài khoản</h2>
        <p>Hệ thống lưu họ tên, email, số điện thoại và mật khẩu đã băm để phục vụ đăng nhập, hồ sơ và phân
            quyền. Khi đặt đơn, hệ thống lưu thông tin giao nhận cùng các sản phẩm, giá và số lượng tại thời
            điểm đặt.</p>
        <p>Cookie phiên được dùng để giữ trạng thái đăng nhập và giỏ hàng. Trình duyệt có thể lưu lựa chọn
            giao diện sáng/tối và sản phẩm so sánh.</p>
        <p>Khách chỉ được xem hồ sơ, đơn hàng của mình; Admin được quản lý dữ liệu theo chức năng của hệ
            thống. Việc sửa hồ sơ không thay đổi thông tin giao nhận đã lưu trong các đơn cũ.</p>
        <p>Đây là môi trường học tập. Hãy dùng dữ liệu giả; liên hệ nhóm phụ trách nếu cần dọn dữ liệu thử.
        </p>
        <a href="<?= e(url('profile.php')) ?>" class="btn">Xem hồ sơ của tôi</a>
    </section>
</article>

<?php page_end(); ?>
