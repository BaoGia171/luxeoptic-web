<?php
require __DIR__ . '/../../includes/admin-commerce.php';
admin_post();
$id = positive_id($_POST['id'] ?? 0);
try {
    $pdo->beginTransaction();
    query_rows('SELECT id FROM categories WHERE id=? FOR UPDATE', [$id]);
    if (query_rows('SELECT id FROM products WHERE category_id=? LIMIT 1', [$id])) {
        throw new DomainException('Danh mục còn sản phẩm. Hãy chuyển sản phẩm hoặc ẩn danh mục.');
    }
    $s = $pdo->prepare('DELETE FROM categories WHERE id=?');
    $s->execute([$id]);
    $pdo->commit();
    flash('success', 'Đã xóa danh mục trống.');
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log($e->getMessage());
    flash('error', $e instanceof DomainException ? $e->getMessage() : 'Không thể xóa danh mục.');
}
redirect('admin/categories.php');
