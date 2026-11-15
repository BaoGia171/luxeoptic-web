<?php
declare(strict_types=1);

// File: actions/cart-add.php
// Người phụ trách: Phạm Văn Đạt

require_once __DIR__ . '/../includes/bootstrap.php';

// Chỉ cho phép gửi bằng POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Phương thức không được hỗ trợ.');
}

// Kiểm tra CSRF bằng hàm chung của nhóm.
verify_csrf();

// Đọc ID và số lượng dưới dạng số nguyên hợp lệ.
$productId = filter_var(
    $_POST['product_id'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

$quantity = filter_var(
    $_POST['quantity'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => [
        'min_range' => 1,
        'max_range' => 99
    ]]
);

if ($productId === false || $productId === null ||
    $quantity === false || $quantity === null) {
    flash('error', 'Sản phẩm hoặc số lượng không hợp lệ.');
    redirect('cart.php');
}

// Tạo giỏ trống nếu chưa tồn tại.
if (!isset($_SESSION['cart']) ||
    !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

try {
    // Đọc dữ liệu sản phẩm thật từ MySQL.
    $sql = "
        SELECT p.id, p.name, p.stock
        FROM products AS p
        INNER JOIN categories AS c
            ON c.id = p.category_id
        WHERE p.id = :product_id
          AND p.is_active = 1
          AND c.is_active = 1
        LIMIT 1
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'product_id' => $productId
    ]);

    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        flash('error', 'Sản phẩm không tồn tại hoặc đã bị ẩn.');
        redirect('cart.php');
    }

    $stock = (int) $product['stock'];

    if ($stock <= 0) {
        flash('error', 'Sản phẩm hiện đã hết hàng.');
        redirect('cart.php');
    }

    // Lấy số lượng đã có trong giỏ.
    $currentQuantity = (int) (
        $_SESSION['cart'][$productId] ?? 0
    );

    $newQuantity = $currentQuantity + $quantity;

    if ($newQuantity > 99) {
        flash('error', 'Mỗi sản phẩm chỉ được mua tối đa 99 chiếc.');
        redirect('cart.php');
    }

    if ($newQuantity > $stock) {
        flash('error', 'Số lượng vượt quá tồn kho hiện tại.');
        redirect('cart.php');
    }

    // Session chỉ lưu ID sản phẩm và số lượng.
    $_SESSION['cart'][$productId] = $newQuantity;

    flash('success', 'Đã thêm sản phẩm vào giỏ hàng.');
    redirect('cart.php');

} catch (PDOException $exception) {
    // Không hiển thị thông tin database cho khách.
    error_log($exception->getMessage());

    flash('error', 'Không thể thêm sản phẩm. Vui lòng thử lại.');
    redirect('cart.php');
}
