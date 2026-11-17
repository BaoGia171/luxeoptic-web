<?php
require __DIR__ . '/../includes/orders.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit();
}
require_login();
verify_csrf();
$user = current_user();
$id = positive_id($_POST['order_id'] ?? 0);
if (!query_rows('SELECT id FROM orders WHERE id=? AND user_id=?', [$id, $user['id']])) {
    http_response_code(404);
    exit('Không tìm thấy đơn hàng.');
}
try {
    change_order($id, 'cancelled', null, (int) $user['id']);
    flash('success', 'Đã hủy đơn và hoàn tồn kho.');
} catch (Throwable $e) {
    error_log($e->getMessage());
    flash(
        'error',
        $e instanceof DomainException ? $e->getMessage() : 'Không thể hủy đơn. Vui lòng thử lại.',
    );
}
redirect('my-orders.php?id=' . $id);
