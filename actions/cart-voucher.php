<?php
declare(strict_types=1);

// File: actions/cart-voucher.php
// Người phụ trách: Phạm Văn Đạt

require_once __DIR__ . '/../includes/bootstrap.php';

// Chỉ chấp nhận phương thức POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Phương thức không được hỗ trợ.');
}

// Kiểm tra CSRF.
verify_csrf();

// Lấy mã voucher người dùng nhập.
$voucherCode = strtoupper(
    trim((string) ($_POST['voucher_code'] ?? ''))
);

// Kiểm tra giỏ hàng có sản phẩm.
if (
    !isset($_SESSION['cart']) ||
    !is_array($_SESSION['cart']) ||
    empty($_SESSION['cart'])
) {
    unset($_SESSION['voucher_code']);

    flash('error', 'Giỏ hàng đang trống, không thể áp dụng mã.');
    redirect('cart.php');
}

// Chỉ hỗ trợ voucher LUXE10.
if ($voucherCode !== 'LUXE10') {
    flash('error', 'Mã giảm giá không hợp lệ.');
    redirect('cart.php');
}

// Chỉ lưu mã, không cộng dồn tiền giảm.
$_SESSION['voucher_code'] = 'LUXE10';

flash('success', 'Áp dụng mã LUXE10 thành công!');

redirect('cart.php');
