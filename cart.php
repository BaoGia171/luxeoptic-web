<?php
declare(strict_types=1);

// File: cart.php
// Người phụ trách: Phạm Văn Đạt

require_once __DIR__ . '/includes/bootstrap.php';

// Khởi tạo giỏ hàng.
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$cartItems = [];
$subtotal = 0;
$totalQuantity = 0;
$cartError = null;

// Lấy danh sách ID sản phẩm từ session.
$productIds = [];

foreach ($_SESSION['cart'] as $id => $quantity) {
    $id = filter_var($id, FILTER_VALIDATE_INT);
    $quantity = filter_var($quantity, FILTER_VALIDATE_INT);

    if ($id !== false && $id > 0 &&
        $quantity !== false && $quantity >= 1 &&
        $quantity <= 99) {
        $productIds[] = $id;
    }
}

$productIds = array_values(array_unique($productIds));
sort($productIds, SORT_NUMERIC);

if (!empty($productIds)) {
    try {
        $placeholders = implode(
            ',',
            array_fill(0, count($productIds), '?')
        );

        $sql = "
            SELECT
                p.id,
                p.name,
                p.price,
                p.stock,
                p.color,
                p.is_active,
                c.is_active AS category_active,
                (
                    SELECT pi.image_path
                    FROM product_images pi
                    WHERE pi.product_id = p.id
                    ORDER BY pi.sort_order ASC, pi.id ASC
                    LIMIT 1
                ) AS image_path
            FROM products p
            INNER JOIN categories c
                ON c.id = p.category_id
            WHERE p.id IN ($placeholders)
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($productIds);

        $products = [];

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $product) {
            $products[(int) $product['id']] = $product;
        }

        foreach ($productIds as $productId) {
            if (!isset($products[$productId])) {
                continue;
            }

            $product = $products[$productId];
            $quantity = (int) $_SESSION['cart'][$productId];

            $price = (int) $product['price'];
            $stock = (int) $product['stock'];

            $available =
                (int) $product['is_active'] === 1 &&
                (int) $product['category_active'] === 1 &&
                $stock >= $quantity &&
                $stock > 0;

            $product['quantity'] = $quantity;
            $product['line_total'] = $price * $quantity;
            $product['available'] = $available;

            $cartItems[] = $product;

            $subtotal += $product['line_total'];
            $totalQuantity += $quantity;
        }

    } catch (PDOException $exception) {
        error_log($exception->getMessage());
        $cartError = 'Không thể tải giỏ hàng. Vui lòng thử lại.';
        $cartItems = [];
        $subtotal = 0;
        $totalQuantity = 0;
    }
}

// Tính voucher từ session.
$discountAmount = 0;

if (($_SESSION['voucher_code'] ?? '') === 'LUXE10') {
    $discountAmount = (int) round($subtotal * 0.10);
}

$shippingFee = 0;
$totalAmount = $subtotal - $discountAmount + $shippingFee;

// Chỉ cho phép thanh toán nếu mọi sản phẩm đều hợp lệ.
$canCheckout = !empty($cartItems)
    && count($cartItems) === count($productIds)
    && $cartError === null;

foreach ($cartItems as $item) {
    if (!$item['available']) {
        $canCheckout = false;
    }
}

// Token dành riêng cho checkout, sẽ dùng ở bước 6.
if (!isset($_SESSION['checkout_token'])) {
    $_SESSION['checkout_token'] = bin2hex(random_bytes(32));
}

$csrf = csrf_token();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Giỏ hàng & Thanh toán | LuxeOptic</title>

    <link rel="stylesheet"
          href="<?= e(url('assets/css/style.css')) ?>">

    <link rel="stylesheet"
          href="<?= e(url('assets/css/responsive.css')) ?>">
</head>

<body class="bg-main text-primary antialiased">

<header class="site-header">
    <div class="container header-inner">
        <a class="brand-logo" href="<?= e(url('index.php')) ?>">
            <span class="logo-mark">L</span>
            <span class="logo-text">LuxeOptic</span>
        </a>

        <nav class="site-menu desktop-flex">
            <a href="<?= e(url('index.php')) ?>">Trang chủ</a>
            <a href="<?= e(url('products.php')) ?>">Sản phẩm</a>
        </nav>

        <div class="header-actions">
            <a href="<?= e(url('cart.php')) ?>"
               class="action-icon">
                Giỏ hàng (<?= $totalQuantity ?>)
            </a>
        </div>
    </div>
</header>

<main class="py-8">
<div class="container">

    <div class="border-b border-light pb-4 mb-8">
        <h1 class="font-headline text-3xl">
            Giỏ Hàng & Thanh Toán
        </h1>
    </div>


<?php foreach (get_flashes() as $message): ?>
    <div role="alert" class="cart-flash">
        <?= e((string) ($message['message'] ?? '')) ?>
    </div>
<?php endforeach; ?>


    <?php if ($cartError !== null): ?>
        <p role="alert"><?= e($cartError) ?></p>
    <?php endif; ?>

    <div class="grid grid-cols-1 desktop-grid-cols-12 gap-8">

        <section class="desktop-col-span-8">
            <div class="bg-white border border-light p-6">

                <h2 class="font-headline text-2xl mb-6">
                    Sản phẩm trong giỏ
                </h2>

                <?php if (empty($cartItems)): ?>

                    <div class="text-center py-8">
                        <p>Giỏ hàng của bạn đang trống.</p>

                        <a href="<?= e(url('products.php')) ?>"
                           class="btn btn-primary mt-4">
                            Tiếp tục mua sắm
                        </a>
                    </div>

                <?php else: ?>

                    <?php foreach ($cartItems as $item): ?>

                        <div class="cart-item flex flex-col small-flex-row
                                    items-center gap-4 py-4 border-b border-light">

                            <?php if (!empty($item['image_path'])): ?>
                                <img
                                    src="<?= e(url($item['image_path'])) ?>"
                                    alt="<?= e($item['name']) ?>"
                                    style="width:100px;height:100px;object-fit:contain">
                            <?php endif; ?>

                            <div class="flex-grow">
                                <h3 class="font-headline text-lg">
                                    <?= e($item['name']) ?>
                                </h3>

                                <p class="text-xs text-muted">
                                    Màu: <?= e($item['color']) ?>
                                </p>

                                <p class="font-semibold">
                                    <?= money((int) $item['price']) ?>
                                </p>

                                <?php if (!$item['available']): ?>
                                    <p style="color:#b91c1c" role="alert">
                                        Sản phẩm đã ẩn, hết hàng
                                        hoặc số lượng vượt tồn kho.
                                        Vui lòng điều chỉnh giỏ.
                                    </p>
                                <?php endif; ?>
                            </div>

                            <div>
                                <form
                                    action="<?= e(url('actions/cart-update.php')) ?>"
                                    method="POST">

                                    <input type="hidden"
                                           name="csrf_token"
                                           value="<?= e($csrf) ?>">

                                    <input type="hidden"
                                           name="product_id"
                                           value="<?= (int) $item['id'] ?>">

                                    <label>
                                        Số lượng
                                        <input
                                            type="number"
                                            name="quantity"
                                            min="1"
                                            max="99"
                                            value="<?= (int) $item['quantity'] ?>"
                                            class="form-control"
                                            style="width:85px"
                                            required>
                                    </label>

                                    <button type="submit"
                                            class="btn btn-primary btn-sm mt-2">
                                        Cập nhật
                                    </button>
                                </form>

                                <form
                                    action="<?= e(url('actions/cart-remove.php')) ?>"
                                    method="POST"
                                    class="mt-2">

                                    <input type="hidden"
                                           name="csrf_token"
                                           value="<?= e($csrf) ?>">

                                    <input type="hidden"
                                           name="product_id"
                                           value="<?= (int) $item['id'] ?>">

                                    <button type="submit"
                                            class="btn btn-sm">
                                        Xóa
                                    </button>
                                </form>
                            </div>

                            <strong>
                                <?= money((int) $item['line_total']) ?>
                            </strong>

                        </div>

                    <?php endforeach; ?>

                <?php endif; ?>
            </div>
        </section>

        <aside class="desktop-col-span-4">
            <div class="bg-white border border-light p-6 shadow-sm">

                <h2 class="font-headline text-2xl mb-6">
                    Tóm Tắt Đơn Hàng
                </h2>

                <div class="flex justify-between mb-4">
                    <span>Số lượng:</span>
                    <strong><?= $totalQuantity ?></strong>
                </div>

                <div class="flex justify-between mb-4">
                    <span>Tạm tính:</span>
                    <strong><?= money($subtotal) ?></strong>
                </div>

                <div class="flex justify-between mb-4">
                    <span>Phí giao hàng:</span>
                    <strong>0đ</strong>
                </div>

                <?php if ($discountAmount > 0): ?>
                    <div class="flex justify-between mb-4">
                        <span>Giảm giá LUXE10:</span>
                        <strong>
                            -<?= money($discountAmount) ?>
                        </strong>
                    </div>
                <?php endif; ?>

                <form
                    action="<?= e(url('actions/cart-voucher.php')) ?>"
                    method="POST"
                    class="mb-6">

                    <input type="hidden"
                           name="csrf_token"
                           value="<?= e($csrf) ?>">

                    <label for="voucher_code" class="form-label">
                        Mã giảm giá
                    </label>

                    <input
                        type="text"
                        id="voucher_code"
                        name="voucher_code"
                        maxlength="30"
                        class="form-control"
                        placeholder="Nhập LUXE10"
                        value="<?= e($_SESSION['voucher_code'] ?? '') ?>">

                    <button type="submit"
                            class="btn btn-primary btn-sm mt-2">
                        Áp dụng
                    </button>
                </form>

                <div class="border-t border-light pt-4">
                    <p class="font-semibold">
                        Tổng thanh toán:
                    </p>

                    <p class="font-headline text-3xl">
                        <?= money($totalAmount) ?>
                    </p>
                </div>


<?php if ($canCheckout): ?>

    <?php $checkoutUser = current_user();

$checkoutOldInput = $_SESSION['checkout_old_input'] ?? [];

$checkoutErrors = $_SESSION['checkout_errors'] ?? [];

if (!is_array($checkoutErrors)) {
    $checkoutErrors = [];
}

unset($_SESSION['checkout_errors']);


if (!is_array($checkoutOldInput)) {
    $checkoutOldInput = [];
}

$oldCheckoutValue = static function (
    string $field,
    string $default = ''
) use ($checkoutOldInput): string {
    $value = $checkoutOldInput[$field] ?? $default;

    return is_string($value) ? $value : $default;
};
?>



    <?php if ($checkoutUser === null): ?>

        <div class="mt-6">
            <p class="text-sm mb-4">
                Bạn cần đăng nhập để tiếp tục đặt hàng.
                Giỏ hàng sẽ được giữ nguyên.
            </p>

            <a href="<?= e(url('auth.php?return=cart')) ?>"
               class="btn btn-primary btn-block">
                Đăng nhập để đặt hàng
            </a>
        </div>

    <?php else: ?>

        <div class="mt-6">
            <h2 class="font-headline text-xl mb-4">
                Thông Tin Giao Nhận
            </h2>

            <form
                id="checkoutForm"
                action="<?= e(url('actions/checkout.php')) ?>"
                method="POST">

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= e($csrf) ?>">

                <input
                    type="hidden"
                    name="checkout_token"
                    value="<?= e($_SESSION['checkout_token']) ?>">

                <div class="mb-4">
                    <label for="recipient_name"
                           class="form-label">
                        Họ và tên người nhận *
                    </label>

                    <input
                        type="text"
                        id="recipient_name"
                        name="recipient_name"
                        class="form-control"
                        maxlength="100"
                        minlength="2"
                        value="<?= e($oldCheckoutValue('recipient_name', (string) ($checkoutUser['full_name'] ?? ''))) ?>"
                        autocomplete="name"
                        required>
                </div>

                <div class="mb-4">
                    <label for="recipient_phone"
                           class="form-label">
                        Số điện thoại *
                    </label>

                    <input
                        type="tel"
                        id="recipient_phone"
                        name="recipient_phone"
                        class="form-control"
                        maxlength="20"
                        value="<?= e($oldCheckoutValue('recipient_phone', (string) ($checkoutUser['phone'] ?? ''))) ?>"
                        autocomplete="tel"
                        placeholder="0912345678"
                        required>

<?php if (!empty($checkoutErrors['recipient_phone'])): ?>
    <p class="field-error" role="alert">
        <?= e((string) $checkoutErrors['recipient_phone']) ?>
    </p>
<?php endif; ?>

                </div>

                <div class="mb-4">
                    <label for="recipient_email"
                           class="form-label">
                        Email *
                    </label>

                    <input
                        type="email"
                        id="recipient_email"
                        name="recipient_email"
                        class="form-control"
                        maxlength="150"
                        value="<?= e($oldCheckoutValue('recipient_email', (string) ($checkoutUser['email'] ?? ''))) ?>"
                        autocomplete="email"
                        required>
                </div>

                <div class="mb-4">
                    <label for="province"
                           class="form-label">
                        Tỉnh / Thành phố *
                    </label>

                    <input
                        type="text"
                        id="province"
                        name="province"
                        class="form-control"
                        maxlength="100"
                        value="<?= e($oldCheckoutValue('province')) ?>"
                        placeholder="Ví dụ: TP. Hồ Chí Minh"
                        required>
                </div>

                <div class="mb-4">
                    <label for="district"
                           class="form-label">
                        Quận / Huyện *
                    </label>

                    <input
                        type="text"
                        id="district"
                        name="district"
                        class="form-control"
                        maxlength="100"
                        value="<?= e($oldCheckoutValue('district')) ?>"
                        placeholder="Nhập quận hoặc huyện"
                        required>
                </div>

                <div class="mb-4">
                    <label for="address_detail"
                           class="form-label">
                        Địa chỉ chi tiết *
                    </label>

                    <input
                        type="text"
                        id="address_detail"
                        name="address_detail"
                        class="form-control"
                        maxlength="255"
                        value="<?= e($oldCheckoutValue('address_detail')) ?>"
                        placeholder="Số nhà, tên đường, phường/xã"
                        required>
                </div>

                <div class="mb-4">
                    <label for="note"
                           class="form-label">
                        Ghi chú đơn hàng
                    </label>

                    <textarea
                        id="note"
                        name="note"
                        class="form-control"
                        maxlength="1000"
                        rows="3"
                        placeholder="Ghi chú thêm cho đơn hàng"><?= e($oldCheckoutValue('note')) ?>
                </div>

                <div class="border-t border-light pt-4 mt-6">
                    <h3 class="font-headline text-xl mb-4">
                        Phương Thức Thanh Toán
                    </h3>

                    <div class="mb-4">
                        <label>
                            <input
                                type="radio"
                                name="payment_method"
                                value="cod"
                                <?= $oldCheckoutValue('payment_method', 'cod') === 'cod' ? 'checked' : '' ?>
                                required>

                            Thanh toán khi nhận hàng (COD)
                        </label>
                    </div>

                    <div class="mb-4">
                        <label>
                            <input
                                type="radio"
                                name="payment_method"
                                value="bank_transfer"
                                <?= $oldCheckoutValue('payment_method', 'cod') === 'bank_transfer' ? 'checked' : '' ?>>

                            Chuyển khoản ngân hàng thủ công
                        </label>
                    </div>

                    <p class="text-xs text-muted mb-4">
                        Nếu chọn chuyển khoản, thông tin
                        thanh toán demo sẽ được hiển thị
                        sau khi đơn hàng được tạo.
                        Hệ thống không tự xác nhận đã
                        nhận tiền.
                    </p>
                </div>

                <button
                    type="submit"
                    class="btn btn-primary btn-block mt-6">
                    Xác Nhận Đặt Hàng
                </button>

            </form>
        </div>

    <?php endif; ?>

<?php else: ?>

    <p class="text-xs mt-4" role="status">
        Chưa thể đặt hàng. Vui lòng kiểm tra
        sản phẩm và số lượng tồn kho.
    </p>

<?php endif; ?>


                <a href="<?= e(url('products.php')) ?>"
                   class="btn btn-primary btn-block mt-4">
                    Tiếp tục mua sắm
                </a>

            </div>
        </aside>

    </div>
</div>
</main>

<footer class="site-footer">
    <div class="container">
        <div class="footer-bottom">
            <p>© 2026 LuxeOptic — Bài tập nhóm Lập trình Web</p>
        </div>
    </div>
</footer>

</body>
</html>
