<?php
require_once __DIR__.'/shop.php';
const ORDER_LABELS=['pending'=>'Chờ xác nhận','confirmed'=>'Đã xác nhận','shipping'=>'Đang giao','completed'=>'Hoàn tất','cancelled'=>'Đã hủy'];

// Every order mutation takes the same row lock, including payment-only changes.
function change_order(int $id,string $target,?string $payment,?int $owner): void {
    global $pdo;
    try{
        $pdo->beginTransaction();
        $order=query_rows('SELECT * FROM orders WHERE id=? FOR UPDATE',[$id])[0]??null;
        if(!$order || ($owner!==null && (int)$order['user_id']!==$owner))throw new DomainException('Không tìm thấy đơn hàng.');
        $current=$order['status'];$paid=$order['payment_status'];
        if(!isset(ORDER_LABELS[$target]))throw new DomainException('Trạng thái không hợp lệ.');
        if($payment!==null && !in_array($payment,['unpaid','paid'],true))throw new DomainException('Thanh toán không hợp lệ.');
        $newPaid=$payment??$paid;
        if(in_array($current,['completed','cancelled'],true))throw new DomainException('Đơn đã kết thúc, không được cập nhật lại.');
        if($paid==='paid' && $newPaid!=='paid')throw new DomainException('Không được chuyển paid về unpaid.');
        if($owner!==null && ($target!=='cancelled'||$current!=='pending'||$paid!=='unpaid'))throw new DomainException('Chỉ được hủy đơn chờ xác nhận, chưa thanh toán.');
        $allowed=['pending'=>['confirmed','cancelled'],'confirmed'=>['shipping','cancelled'],'shipping'=>['completed']];
        if($target!==$current && !in_array($target,$allowed[$current],true))throw new DomainException('Không được bỏ qua hoặc quay ngược trạng thái.');
        if($target==='cancelled' && ($paid!=='unpaid'||$newPaid!=='unpaid'))throw new DomainException('Không hủy đơn đã thanh toán.');
        if($target==='shipping' && $order['payment_method']==='bank_transfer' && $newPaid!=='paid')throw new DomainException('Đơn chuyển khoản phải được xác nhận thanh toán trước khi giao.');
        if($target==='completed' && $order['payment_method']==='cod')$newPaid='paid';
        if($target==='cancelled'){
            $items=query_rows('SELECT product_id,SUM(quantity) quantity FROM order_items WHERE order_id=? GROUP BY product_id ORDER BY product_id',[$id]);
            foreach($items as $item){
                $s=$pdo->prepare('UPDATE products SET stock=stock+? WHERE id=?');$s->execute([(int)$item['quantity'],(int)$item['product_id']]);
                if($s->rowCount()!==1)throw new RuntimeException('Missing stock row');
            }
        }
        $s=$pdo->prepare('UPDATE orders SET status=?,payment_status=? WHERE id=?');$s->execute([$target,$newPaid,$id]);$pdo->commit();
    }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}
}
function order_detail(array $order,bool $admin=false): void {
    $items=query_rows('SELECT * FROM order_items WHERE order_id=? ORDER BY id',[$order['id']]); ?>
<section class="panel">
    <h2>Đơn <?= e($order['order_code']) ?></h2>
    <p class="order-status"><?= e(ORDER_LABELS[$order['status']]) ?> ·
        <?= $order['payment_status']==='paid'?'Đã thanh toán':'Chưa thanh toán' ?></p>
    <p><?= e($order['recipient_name']) ?> · <?= e($order['recipient_phone']) ?> ·
        <?= e($order['recipient_email']) ?></p>
    <p><?= e($order['address_detail'].', '.$order['district'].', '.$order['province']) ?></p>
    <p>Ghi chú: <?= e($order['note']) ?></p>
    <div class="table-scroll">
        <table class="commerce-table">
            <thead>
                <tr>
                    <th>Sản phẩm</th>
                    <th>Màu</th>
                    <th>Đơn giá</th>
                    <th>Số lượng</th>
                    <th>Thành tiền</th>
                </tr>
            </thead>
            <tbody><?php foreach($items as $it): ?><tr>
                    <td>
                        <img src="<?= e(image_url($it['product_image'])) ?>"
                            alt="<?= e($it['product_name']) ?>"><?= e($it['product_name']) ?>
                        <br><?= e($it['product_sku']) ?>
                    </td>
                    <td><?= e($it['product_color']) ?></td>
                    <td><?= money((int)$it['unit_price']) ?></td>
                    <td><?= (int)$it['quantity'] ?></td>
                    <td><?= money((int)$it['line_total']) ?></td>
                </tr><?php endforeach ?></tbody>
        </table>
    </div>
    <p>Tạm tính: <?= money((int)$order['subtotal']) ?> · Giảm: <?= money((int)$order['discount_amount']) ?> ·
        Phí giao: <?= money((int)$order['shipping_fee']) ?></p>
    <h3>Tổng: <?= money((int)$order['total_amount']) ?></h3>
    <p>Thanh toán: <?= $order['payment_method']==='cod'?'COD':'Chuyển khoản thủ công' ?></p>
    <?php if($order['payment_method']==='bank_transfer' && $order['payment_status']==='unpaid' && $order['status']!=='cancelled'): ?>
    <p class="notice">Demo chuyển khoản: tài khoản mẫu 0000000000 — LUXEOPTIC DEMO. Nội dung:
        <?= e($order['order_code']) ?>. Không chuyển tiền thật; Admin xác nhận thủ công trong bài thực hành.
    </p><?php endif ?>
    <?php if(!$admin && $order['status']==='pending' && $order['payment_status']==='unpaid'): ?><form
        method="post" action="<?= e(url('actions/order-cancel.php')) ?>" data-confirm="Bạn muốn hủy đơn này?">
        <?php csrf_input() ?>
        <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">
        <button class="btn">Hủy đơn</button>
    </form><?php endif ?>
    <?php if($admin && !in_array($order['status'],['completed','cancelled'])): ?><form class="filters"
        method="post" action="<?= e(url('admin/actions/order-status.php')) ?>"
        data-confirm="Xác nhận cập nhật đơn hàng?"><?php csrf_input() ?>
        <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">
        <label>Trạng thái<select name="status"><?php foreach(ORDER_LABELS as $key=>$label): ?><option
                    value="<?= $key ?>" <?= $order['status']===$key?'selected':'' ?>><?= $label ?></option>
                <?php endforeach ?></select>
        </label>
        <label>Thanh toán<select name="payment_status">
                <option value="unpaid" <?= $order['payment_status']==='unpaid'?'selected':'' ?>>Chưa thanh
                    toán</option>
                <option value="paid" <?= $order['payment_status']==='paid'?'selected':'' ?>>Đã thanh toán
                    (Admin xác nhận)</option>
            </select>
        </label>
        <button class="btn btn-primary">Cập nhật</button>
    </form><?php endif ?>
</section><?php
}
