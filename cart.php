<?php
declare(strict_types=1);

// File: cart.php
// Người phụ trách: Phạm Văn Đạt

require_once __DIR__ . '/includes/shop.php';

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

    if ($id !== false && $id > 0 && $quantity !== false && $quantity >= 1 && $quantity <= 99) {
        $productIds[] = $id;
    }
}

$productIds = array_values(array_unique($productIds));
sort($productIds, SORT_NUMERIC);

if (!empty($productIds)) {
    try {
        $placeholders = implode(',', array_fill(0, count($productIds), '?'));

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
    $discountAmount = (int) round($subtotal * 0.1);
}

$shippingFee = 0;
$totalAmount = $subtotal - $discountAmount + $shippingFee;

// Chỉ cho phép thanh toán nếu mọi sản phẩm đều hợp lệ.
$canCheckout =
    !empty($cartItems) && count($cartItems) === count($productIds) && $cartError === null;

foreach ($cartItems as $item) {
    if (!$item['available']) {
        $canCheckout = false;
    }
}

// Token dành riêng cho checkout, sẽ dùng ở bước 6.
if (!isset($_SESSION['checkout_token'])) {
    $_SESSION['checkout_token'] = bin2hex(random_bytes(32));
}

$old = $_SESSION['checkout_old_input'] ?? [];
$errors = $_SESSION['checkout_errors'] ?? [];
unset($_SESSION['checkout_errors']);
$user = current_user();
page_start('Giỏ hàng & Thanh toán');
?>
<div class="commerce-columns">
    <section>
        <div class="panel">
            <h2>Giỏ hàng (<?= $totalQuantity ?> sản phẩm)</h2>
            <?php if($cartError): ?><p class="field-error"><?= e($cartError) ?></p><?php endif ?>
            <?php if(!$cartItems): ?><p>Giỏ hàng đang trống.</p>
            <a class="btn btn-primary" href="<?= e(url('products.php')) ?>">Tiếp tục mua sắm</a><?php else: ?>
            <div class="table-scroll">
                <table class="commerce-table">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Số lượng</th>
                            <th>Thành tiền</th>
                            <th>Xóa</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($cartItems as $item): ?><tr>
                            <td>
                                <img src="<?= e(image_url($item['image_path'])) ?>"
                                    alt="<?= e($item['name']) ?>">
                                <?= e($item['name']) ?><?php if(!$item['available']): ?><p
                                    class="field-error">Không đủ tồn kho hoặc đã ngừng bán.</p><?php endif ?>
                            </td>
                            <td>
                                <form class="inline-form" method="post"
                                    action="<?= e(url('actions/cart-update.php')) ?>"><?php csrf_input() ?>
                                    <input type="hidden" name="product_id" value="<?= (int)$item['id'] ?>">
                                    <input aria-label="Số lượng <?= e($item['name']) ?>" type="number"
                                        name="quantity" min="1" max="99" value="<?= $item['quantity'] ?>"
                                        required>
                                    <button class="btn" type="submit">Cập nhật</button>
                                </form>
                            </td>
                            <td><?= money($item['line_total']) ?></td>
                            <td>
                                <form method="post" action="<?= e(url('actions/cart-remove.php')) ?>">
                                    <?php csrf_input() ?>
                                    <input type="hidden" name="product_id" value="<?= (int)$item['id'] ?>">
                                    <button class="btn" type="submit">Xóa</button>
                                </form>
                            </td>
                        </tr><?php endforeach ?>
                    </tbody>
                </table>
            </div><?php endif ?>
        </div>
        <?php if($cartItems && !$canCheckout): ?><p class="notice">Vui lòng cập nhật hoặc xóa sản phẩm không
            hợp lệ trước khi đặt hàng.</p><?php endif ?>
        <?php if($canCheckout && !$user): ?><a class="btn btn-primary"
            href="<?= e(url('auth.php?return=cart')) ?>">Đăng nhập để đặt hàng</a><?php endif ?>
        <?php if($canCheckout && $user): ?>
        <form class="panel" method="post" action="<?= e(url('actions/checkout.php')) ?>" id="checkoutForm">
            <h2>Thông tin giao nhận</h2><?php csrf_input() ?>
            <input type="hidden" name="checkout_token" value="<?= e($_SESSION['checkout_token']) ?>">
            <?php field_error('cart',$errors) ?>
            <div class="form-grid"><?php foreach (
    [
        'recipient_name' => 'Họ tên người nhận',
        'recipient_phone' => 'Số điện thoại',
        'recipient_email' => 'Email',
        'province' => 'Tỉnh / Thành phố',
        'district' => 'Quận / Huyện',
        'address_detail' => 'Địa chỉ chi tiết',
    ]
    as $key => $label
) {
    form_field($key, $label, $old, $errors, $key === 'recipient_email' ? 'email' : 'text');
} ?></div>
            <div class="field">
                <label for="note">Ghi chú</label>
                <textarea id="note" name="note" maxlength="1000"
                    placeholder="Ghi chú cho đơn hàng"><?= e($old['note']??'') ?></textarea>
                <?php field_error('note',$errors) ?>
            </div>
            <h2>Phương thức thanh toán</h2>
            <?php foreach(['cod'=>'Thanh toán khi nhận hàng (COD)','bank_transfer'=>'Chuyển khoản thủ công (demo)'] as $value=>$label): ?>
            <label class="payment-option">
                <input type="radio" name="payment_method" value="<?= $value ?>"
                    <?= ($old['payment_method']??'cod')===$value?'checked':'' ?> required><?= $label ?>
            </label><?php endforeach ?><?php field_error('payment_method',$errors) ?>
            <p>Đơn chuyển khoản chỉ được xác nhận đã thanh toán bởi Admin. Bản demo không thu tiền thật.</p>
            <button class="btn btn-primary" type="submit">Xác nhận đặt hàng</button>
        </form><?php endif ?>
    </section>
    <aside class="panel">
        <h2>Tóm tắt đơn hàng</h2>
        <p>Tạm tính: <?= money($subtotal) ?></p>
        <p>Giảm giá: <?= money($discountAmount) ?></p>
        <p>Vận chuyển: <?= money($shippingFee) ?></p>
        <h3>Tổng thanh toán: <?= money($totalAmount) ?></h3>
        <form method="post" action="<?= e(url('actions/cart-voucher.php')) ?>"><?php csrf_input() ?><div
                class="field">
                <label for="voucher_code">Mã giảm giá</label>
                <input id="voucher_code" name="voucher_code" placeholder="Nhập mã (LUXE10)"
                    value="<?= e($_SESSION['voucher_code']??'') ?>">
            </div>
            <button class="btn" <?= !$cartItems?'disabled':'' ?>>Áp dụng</button>
        </form>
    </aside>
</div>
<?php page_end(['page-cart']); ?>
