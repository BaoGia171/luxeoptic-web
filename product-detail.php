<?php
require __DIR__.'/includes/shop.php';
require __DIR__.'/includes/product-content.php';
$p=visible_product(positive_id($_GET['id']??null));
if(!$p){http_response_code(404);page_start('Không tìm thấy sản phẩm');echo '<p>Mẫu này không tồn tại hoặc đã ngừng hiển thị.</p>';page_end();exit;}
$activeNav=$p['category_slug']; $images=query_rows('SELECT * FROM product_images WHERE product_id=? ORDER BY sort_order,id',[$p['id']]);
page_start($p['name']);
?>
<div class="detail-grid"><section><img id="mainProductImage" class="detail-image" src="<?= e(image_url($p['image'])) ?>" alt="<?= e($p['name']) ?>"><div class="thumbnails"><?php foreach($images as $im): ?><button type="button" data-image="<?= e(image_url($im['image_path'])) ?>" aria-label="Xem ảnh <?= e($im['alt_text']?:$p['name']) ?>"><img src="<?= e(image_url($im['image_path'])) ?>" alt="<?= e($im['alt_text']?:$p['name']) ?>"></button><?php endforeach ?></div></section>
<section><p class="uppercase-spaced"><?= e($p['sku']) ?> · <?= e($p['category_name']) ?></p><h2><?= money((int)$p['price']) ?></h2><?php if($p['old_price'] && $p['old_price']>$p['price']): ?><del><?= money((int)$p['old_price']) ?></del><?php endif ?><p>Chất liệu: <?= e($p['material']) ?></p><p>Màu gọng: <?= e($p['color']) ?></p><p><?= $p['stock']>0?'Còn '.(int)$p['stock'].' sản phẩm':'Tạm hết hàng' ?></p>
<form class="panel" method="post" action="<?= e(url('actions/cart-add.php')) ?>"><?php csrf_input() ?><input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>"><div class="field"><label for="quantity">Số lượng</label><input id="quantity" name="quantity" type="number" value="1" min="1" max="<?= min(99,max(1,(int)$p['stock'])) ?>" required></div><button class="btn btn-primary" <?= $p['stock']<1?'disabled':'' ?>>Thêm vào giỏ</button></form>
<a href="<?= e(url('compare.php?id1='.$p['id'])) ?>">So sánh với gọng khác →</a><div class="description"><?= clean_description($p['description']??'') ?></div></section></div>
<?php page_end(['page-product-detail']); ?>
