<?php
require __DIR__ . '/includes/orders.php';
require_login();
$user = current_user();
$id = positive_id($_GET['id'] ?? 0);
$order = $id
    ? query_rows('SELECT * FROM orders WHERE id=? AND user_id=?', [$id, $user['id']])[0] ?? null
    : null;
if (isset($_GET['id']) && !$order) {
    http_response_code(404);
    page_start('Không tìm thấy đơn hàng');
    page_end();
    exit();
}
$total = (int) query_rows('SELECT COUNT(*) n FROM orders WHERE user_id=?', [$user['id']])[0]['n'];
[$page, $pages, $offset] = paginate($total, 10);
$rows = query_rows(
    'SELECT * FROM orders WHERE user_id=? ORDER BY created_at DESC,id DESC LIMIT 10 OFFSET ' .
        $offset,
    [$user['id']],
);
page_start('Đơn hàng của tôi');
if ($order) {
    order_detail($order);
}
?>
<div class="panel table-scroll">
    <table class="commerce-table">
        <thead>
            <tr>
                <th>Mã đơn</th>
                <th>Ngày đặt</th>
                <th>Tổng tiền</th>
                <th>Trạng thái</th>
                <th>Chi tiết</th>
            </tr>
        </thead>
        <tbody><?php foreach($rows as $r): ?><tr>
                <td><?= e($r['order_code']) ?></td>
                <td><?= e($r['created_at']) ?></td>
                <td><?= money((int)$r['total_amount']) ?></td>
                <td><?= e(ORDER_LABELS[$r['status']]) ?></td>
                <td>
                    <a class="btn" href="?id=<?= (int)$r['id'] ?>">Xem đơn</a>
                </td>
            </tr><?php endforeach ?></tbody>
    </table><?php if(!$rows): ?><p>Bạn chưa có đơn hàng.</p>
    <a href="<?= e(url('products.php')) ?>">Chọn kính để mua sắm</a><?php endif ?>
</div>
<?php pagination($total,$page,$pages);page_end(['page-my-orders']); ?>
