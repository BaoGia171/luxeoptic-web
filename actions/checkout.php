<?php
declare(strict_types=1);

// File: actions/checkout.php
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

// Bắt buộc đăng nhập trước khi đặt hàng.
require_login();

$user = current_user();

if (!$user || empty($_SESSION['user_id'])) {
    flash('error', 'Vui lòng đăng nhập để đặt hàng.');
    redirect('auth.php?return=cart');
}

$userId = (int) $_SESSION['user_id'];

// Kiểm tra token chống gửi đơn trùng.
$submittedToken = (string) ($_POST['checkout_token'] ?? '');
$sessionToken = (string) ($_SESSION['checkout_token'] ?? '');

if (
    $submittedToken === '' ||
    $sessionToken === '' ||
    !hash_equals($sessionToken, $submittedToken)
) {
    flash('error', 'Phiên đặt hàng không hợp lệ hoặc đã hết hạn.');
    redirect('cart.php');
}

// Lấy thông tin người nhận.
$recipientName = trim((string) ($_POST['recipient_name'] ?? ''));
$recipientPhone = trim((string) ($_POST['recipient_phone'] ?? ''));
$recipientEmail = trim((string) ($_POST['recipient_email'] ?? ''));
$province = trim((string) ($_POST['province'] ?? ''));
$district = trim((string) ($_POST['district'] ?? ''));
$addressDetail = trim((string) ($_POST['address_detail'] ?? ''));
$note = trim((string) ($_POST['note'] ?? ''));
$paymentMethod = trim((string) ($_POST['payment_method'] ?? ''));

// Chuẩn hóa số điện thoại Việt Nam.
$recipientPhone = preg_replace(
    '/[\s.\-]/',
    '',
    $recipientPhone
);

if (str_starts_with($recipientPhone, '+84')) {
    $recipientPhone = '0' . substr($recipientPhone, 3);
}

// Kiểm tra dữ liệu.
$errors = [];

if (
    mb_strlen($recipientName) < 2 ||
    mb_strlen($recipientName) > 100
) {
    $errors[] = 'Họ tên phải có từ 2 đến 100 ký tự.';
}

if (!preg_match('/^0[0-9]{9}$/', $recipientPhone)) {
    $errors[] = 'Số điện thoại không hợp lệ.';
}

if (
    !filter_var($recipientEmail, FILTER_VALIDATE_EMAIL) ||
    mb_strlen($recipientEmail) > 150
) {
    $errors[] = 'Email không hợp lệ.';
}

if ($province === '' || mb_strlen($province) > 100) {
    $errors[] = 'Tỉnh/thành phố không hợp lệ.';
}

if ($district === '' || mb_strlen($district) > 100) {
    $errors[] = 'Quận/huyện không hợp lệ.';
}

if (
    $addressDetail === '' ||
    mb_strlen($addressDetail) > 255
) {
    $errors[] = 'Địa chỉ chi tiết không hợp lệ.';
}

if (mb_strlen($note) > 1000) {
    $errors[] = 'Ghi chú không được quá 1000 ký tự.';
}

if (!in_array($paymentMethod, ['cod', 'bank_transfer'], true)) {
    $errors[] = 'Phương thức thanh toán không hợp lệ.';
}

// Kiểm tra giỏ hàng.
$cart = $_SESSION['cart'] ?? [];

if (!is_array($cart) || empty($cart)) {
    $errors[] = 'Giỏ hàng đang trống.';
}

// Chuẩn hóa ID và số lượng.
$cartQuantities = [];

if (is_array($cart)) {
    foreach ($cart as $productId => $quantity) {
        $validId = filter_var(
            $productId,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        $validQuantity = filter_var(
            $quantity,
            FILTER_VALIDATE_INT,
            ['options' => [
                'min_range' => 1,
                'max_range' => 99
            ]]
        );

        if (
            $validId === false ||
            $validId === null ||
            $validQuantity === false ||
            $validQuantity === null
        ) {
            $errors[] = 'Giỏ hàng chứa dữ liệu không hợp lệ.';
            break;
        }

        $cartQuantities[(int) $validId] = (int) $validQuantity;
    }
}

if (!empty($errors)) {
    flash('error', implode(' ', $errors));
    redirect('cart.php');
}

// Sắp xếp ID để khóa sản phẩm theo cùng thứ tự.
ksort($cartQuantities, SORT_NUMERIC);

try {
    $pdo->beginTransaction();

    $orderItems = [];
    $subtotal = 0;

    foreach ($cartQuantities as $productId => $quantity) {
        // Khóa dòng sản phẩm trong transaction.
        $sql = "
            SELECT
                p.id,
                p.sku,
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
            WHERE p.id = :product_id
            FOR UPDATE
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['product_id' => $productId]);

        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            throw new RuntimeException(
                'Một sản phẩm trong giỏ không còn tồn tại.'
            );
        }

        if (
            (int) $product['is_active'] !== 1 ||
            (int) $product['category_active'] !== 1
        ) {
            throw new RuntimeException(
                'Sản phẩm ' . $product['name'] .
                ' hiện không được bán.'
            );
        }

        if ((int) $product['stock'] < $quantity) {
            throw new RuntimeException(
                'Sản phẩm ' . $product['name'] .
                ' không đủ số lượng tồn kho.'
            );
        }

        $price = (int) $product['price'];
        $lineTotal = $price * $quantity;

        $subtotal += $lineTotal;

        $orderItems[] = [
            'product_id' => (int) $product['id'],
            'product_name' => $product['name'],
            'product_sku' => $product['sku'],
            'product_image' => $product['image_path'],
            'product_color' => $product['color'],
            'unit_price' => $price,
            'quantity' => $quantity,
            'line_total' => $lineTotal
        ];
    }

    // Voucher được tính lại ở server.
    $voucherCode = (
        ($_SESSION['voucher_code'] ?? '') === 'LUXE10'
    ) ? 'LUXE10' : null;

    $discountAmount = $voucherCode === 'LUXE10'
        ? (int) round($subtotal * 0.10)
        : 0;

    $shippingFee = 0;
    $totalAmount = $subtotal - $discountAmount;

    // Tạo mã tạm duy nhất để tránh trùng UNIQUE.
    $temporaryCode = 'TMP-' . bin2hex(random_bytes(12));

    $orderSql = "
        INSERT INTO orders (
            order_code,
            user_id,
            recipient_name,
            recipient_phone,
            recipient_email,
            province,
            district,
            address_detail,
            note,
            payment_method,
            payment_status,
            status,
            subtotal,
            discount_amount,
            shipping_fee,
            total_amount,
            voucher_code
        ) VALUES (
            :order_code,
            :user_id,
            :recipient_name,
            :recipient_phone,
            :recipient_email,
            :province,
            :district,
            :address_detail,
            :note,
            :payment_method,
            'unpaid',
            'pending',
            :subtotal,
            :discount_amount,
            :shipping_fee,
            :total_amount,
            :voucher_code
        )
    ";

    $orderStmt = $pdo->prepare($orderSql);

    $orderStmt->execute([
        'order_code' => $temporaryCode,
        'user_id' => $userId,
        'recipient_name' => $recipientName,
        'recipient_phone' => $recipientPhone,
        'recipient_email' => $recipientEmail,
        'province' => $province,
        'district' => $district,
        'address_detail' => $addressDetail,
        'note' => $note,
        'payment_method' => $paymentMethod,
        'subtotal' => $subtotal,
        'discount_amount' => $discountAmount,
        'shipping_fee' => $shippingFee,
        'total_amount' => $totalAmount,
        'voucher_code' => $voucherCode
    ]);

    $orderId = (int) $pdo->lastInsertId();

    // Mã đơn dựa trên ID thật vừa được tạo.
    $orderCode = 'LX-' . str_pad(
        (string) $orderId,
        6,
        '0',
        STR_PAD_LEFT
    );

    $codeStmt = $pdo->prepare(
        "UPDATE orders SET order_code = :code WHERE id = :id"
    );

    $codeStmt->execute([
        'code' => $orderCode,
        'id' => $orderId
    ]);

    // Lưu từng sản phẩm vào order_items.
    $itemSql = "
        INSERT INTO order_items (
            order_id,
            product_id,
            product_name,
            product_sku,
            product_image,
            product_color,
            unit_price,
            quantity,
            line_total
        ) VALUES (
            :order_id,
            :product_id,
            :product_name,
            :product_sku,
            :product_image,
            :product_color,
            :unit_price,
            :quantity,
            :line_total
        )
    ";

    $itemStmt = $pdo->prepare($itemSql);

    $stockStmt = $pdo->prepare(
        "UPDATE products
         SET stock = stock - :quantity
         WHERE id = :product_id
           AND stock >= :stock_check"
    );

    foreach ($orderItems as $item) {
        $itemStmt->execute([
            'order_id' => $orderId,
            'product_id' => $item['product_id'],
            'product_name' => $item['product_name'],
            'product_sku' => $item['product_sku'],
            'product_image' => $item['product_image'],
            'product_color' => $item['product_color'],
            'unit_price' => $item['unit_price'],
            'quantity' => $item['quantity'],
            'line_total' => $item['line_total']
        ]);

        $stockStmt->execute([
            'quantity' => $item['quantity'],
            'product_id' => $item['product_id'],
            'stock_check' => $item['quantity']
        ]);

        if ($stockStmt->rowCount() !== 1) {
            throw new RuntimeException(
                'Tồn kho thay đổi. Vui lòng thử lại.'
            );
        }
    }

    // Chỉ commit khi mọi thao tác thành công.
    $pdo->commit();

    // Xóa giỏ và voucher SAU KHI commit.
    unset($_SESSION['cart'], $_SESSION['voucher_code']);

    // Vô hiệu hóa token cũ để tránh gửi đơn lần hai.
    unset($_SESSION['checkout_token']);

    flash('success', 'Đặt hàng thành công! Mã đơn: ' . $orderCode);

    redirect('my-orders.php?id=' . $orderId);

} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    if ($exception instanceof RuntimeException) {
        flash('error', $exception->getMessage());
    } else {
        error_log($exception->getMessage());
        flash(
            'error',
            'Đặt hàng thất bại. Vui lòng thử lại sau.'
        );
    }

    redirect('cart.php');
}
