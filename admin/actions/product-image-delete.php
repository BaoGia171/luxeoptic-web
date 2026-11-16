<?php
require __DIR__.'/../../includes/admin-commerce.php';admin_post();$id=positive_id($_POST['image_id']??0);$productId=0;
try{
    $image=query_rows('SELECT * FROM product_images WHERE id=?',[$id])[0]??null;
    if(!$image)throw new DomainException('Ảnh không tồn tại.');$productId=(int)$image['product_id'];
    $pdo->beginTransaction();$p=query_rows('SELECT * FROM products WHERE id=? FOR UPDATE',[$productId])[0]??null;
    $images=query_rows('SELECT * FROM product_images WHERE product_id=?',[$productId]);
    if(!$p || ($p['is_active'] && count($images)<=1))throw new DomainException('Thêm ảnh thay thế trước khi xóa ảnh cuối của sản phẩm đang hiển thị.');
    $s=$pdo->prepare('DELETE FROM product_images WHERE id=? AND product_id=?');$s->execute([$id,$productId]);
    $pdo->commit();if($s->rowCount())delete_uploaded_image($image['image_path']);flash('success','Đã xóa ảnh.');
}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();error_log($e->getMessage());flash('error',$e instanceof DomainException?$e->getMessage():'Không thể xóa ảnh.');}
redirect('admin/products.php'.($productId?'?edit='.$productId:''));
