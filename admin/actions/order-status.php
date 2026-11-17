<?php
require __DIR__ . '/../../includes/admin-commerce.php';
require __DIR__ . '/../../includes/orders.php';
admin_post();
$id = positive_id($_POST['order_id'] ?? 0);
try {
    change_order($id, input_text($_POST, 'status'), input_text($_POST, 'payment_status'), null);
    flash('success', 'Đã cập nhật đơn hàng.');
} catch (Throwable $e) {
    error_log($e->getMessage());
    flash('error', $e instanceof DomainException ? $e->getMessage() : 'Không thể cập nhật đơn.');
}
redirect('admin/orders.php?id=' . $id);
