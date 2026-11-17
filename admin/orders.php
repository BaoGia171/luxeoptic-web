<?php
require __DIR__ . '/../includes/orders.php';
require_admin();
$id = positive_id($_GET['id'] ?? 0);
$status = input_text($_GET, 'status');
$q = mb_substr(input_text($_GET, 'q'), 0, 100);
$order = $id ? query_rows('SELECT * FROM orders WHERE id=?', [$id])[0] ?? null : null;
if (isset($_GET['id']) && !$order) {
    http_response_code(404);
    exit('Không tìm thấy đơn.');
}
$where = [];
$params = [];
if (isset(ORDER_LABELS[$status])) {
    $where[] = 'status=?';
    $params[] = $status;
} else {
    $status = '';
}
if ($q !== '') {
    $where[] = '(order_code LIKE ? OR recipient_name LIKE ? OR recipient_phone LIKE ?)';
    $like = '%' . addcslashes($q, '%_\\') . '%';
    array_push($params, $like, $like, $like);
}
$w = $where ? ' WHERE ' . implode(' AND ', $where) : '';
$total = (int) query_rows('SELECT COUNT(*) n FROM orders' . $w, $params)[0]['n'];
[$page, $pages, $offset] = paginate($total, 10);
$rows = query_rows(
    'SELECT * FROM orders' . $w . ' ORDER BY created_at DESC,id DESC LIMIT 10 OFFSET ' . $offset,
    $params,
);
page_start('Quản lý đơn hàng', true);
if ($order) {
    order_detail($order, true);
} ?>
<form class="filters" method="get">
    <label>Mã đơn / Tên / Điện thoại
        <input name="q" value="<?= e($q) ?>">
    </label>
    <label>Trạng thái<select name="status">
            <option value="">Tất cả</option><?php foreach(ORDER_LABELS as $key=>$label): ?><option
                value="<?= $key ?>" <?= $status===$key?'selected':'' ?>><?= $label ?></option>
            <?php endforeach ?>
        </select>
    </label>
    <button class="btn">Lọc đơn</button>
</form>
<div class="panel table-scroll">
    <table class="commerce-table">
        <thead>
            <tr>
                <th>Mã đơn</th>
                <th>Người nhận</th>
                <th>Tổng tiền</th>
                <th>Trạng thái / Thanh toán</th>
                <th>Chi tiết</th>
            </tr>
        </thead>
        <tbody><?php foreach($rows as $r): ?><tr>
                <td><?= e($r['order_code']) ?></td>
                <td><?= e($r['recipient_name']) ?></td>
                <td><?= money((int)$r['total_amount']) ?></td>
                <td><?= e(ORDER_LABELS[$r['status']]) ?> / <?= e($r['payment_status']) ?></td>
                <td>
                    <a class="btn" href="?id=<?= (int)$r['id'] ?>">Xem / Cập nhật</a>
                </td>
            </tr><?php endforeach ?></tbody>
    </table><?php if(!$rows): ?><p>Chưa có đơn phù hợp.</p><?php endif ?>
</div>
<?php pagination($total,$page,$pages,['q'=>$q,'status'=>$status]);page_end(['page-admin-orders']); ?>
