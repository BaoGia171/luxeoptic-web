<?php
declare(strict_types=1);

// File: actions/cart-remove.php
// Người phụ trách: Phạm Văn Đạt

require_once __DIR__ . '/../includes/bootstrap.php';

// Chỉ cho phép POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Phương thức không được hỗ trợ.');
}

// Kiểm tra CSRF.
verify_csrf();

// Đọc ID sản phẩm.
$productId = filter_var(
    $_POST['product_id'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

if ($productId === false || $productId === null) {
    flash('error', 'ID sản phẩm không hợp lệ.');
    redirect('cart.php');
}

// Kiểm tra giỏ hàng.
if (
    !isset($_SESSION['cart']) ||
    !is_array($_SESSION['cart']) ||
    !isset($_SESSION['cart'][$productId])
) {
    flash('error', 'Sản phẩm không có trong giỏ hàng.');
    redirect('cart.php');
}

// Xóa sản phẩm khỏi Session.
unset($_SESSION['cart'][$productId]);

// Nếu giỏ trống, xóa voucher đã áp dụng.
if (empty($_SESSION['cart'])) {
    unset($_SESSION['voucher_code']);
}

flash('success', 'Đã xóa sản phẩm khỏi giỏ hàng.');

redirect('cart.php');
