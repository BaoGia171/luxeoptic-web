<?php
require __DIR__ . '/../../includes/admin-commerce.php';
admin_post();
$id = positive_id($_POST['id'] ?? 0);
$s = $pdo->prepare('UPDATE products SET is_active=0 WHERE id=?');
$s->execute([$id]);
flash('success', 'Đã ẩn sản phẩm. Lịch sử đơn hàng được giữ nguyên.');
redirect('admin/products.php');
