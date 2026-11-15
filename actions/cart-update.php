<?php
declare(strict_types=1);

// File: actions/cart-update.php
// Người phụ trách: Phạm Văn Đạt

require_once __DIR__ . '/../includes/bootstrap.php';

// Chỉ chấp nhận phương thức POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Phương thức không được hỗ trợ.');
}

// Kiểm tra mã CSRF.
verify_csrf();

// Kiểm tra ID sản phẩm.
$productId = filter_var(
    $_POST['product_id'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

// Kiểm tra số lượng từ 1 đến 99.
$quantity = filter_var(
    $_POST['quantity'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => [
        'min_range' => 1,
        'max_range' => 99
    ]]
);

if (
    $productId === false || $productId === null ||
    $quantity === false || $quantity === null
) {
    flash('error', 'Sản phẩm hoặc số lượng không hợp lệ.');
    redirect('cart.php');
}

// Kiểm tra giỏ hàng tồn tại.
if (
    !isset($_SESSION['cart']) ||
    !is_array($_SESSION['cart']) ||
    !isset($_SESSION['cart'][$productId])
) {
    flash('error', 'Sản phẩm không có trong giỏ hàng.');
    redirect('cart.php');
}

try {
    // Lấy tồn kho mới nhất từ database.
    $sql = "
        SELECT p.id, p.stock, p.is_active,
               c.is_active AS category_active
        FROM products AS p
        INNER JOIN categories AS c
            ON c.id = p.category_id
        WHERE p.id = :product_id
        LIMIT 1
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'product_id' => $productId
    ]);

    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        flash('error', 'Sản phẩm không còn tồn tại.');
        redirect('cart.php');
    }

    if (
        (int) $product['is_active'] !== 1 ||
        (int) $product['category_active'] !== 1
    ) {
        flash('error', 'Sản phẩm hiện không được bán.');
        redirect('cart.php');
    }

    $stock = (int) $product['stock'];

    if ($stock < $quantity) {
        flash(
            'error',
            'Không đủ hàng. Số lượng tồn kho hiện tại: ' . $stock
        );
        redirect('cart.php');
    }

    // Cập nhật số lượng trong PHP Session.
    $_SESSION['cart'][$productId] = $quantity;

    flash('success', 'Đã cập nhật số lượng sản phẩm.');
    redirect('cart.php');

} catch (PDOException $exception) {
    error_log($exception->getMessage());

    flash('error', 'Không thể cập nhật giỏ hàng.');
    redirect('cart.php');
}
