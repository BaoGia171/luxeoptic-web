<?php
require __DIR__.'/includes/shop.php';
$products=query_rows(product_select().' WHERE p.is_active=1 AND c.is_active=1 ORDER BY p.name,p.id');
$id1=positive_id($_GET['id1']??null);$id2=positive_id($_GET['id2']??null);
$data=array_map(fn($p)=>['id'=>(int)$p['id'],'name'=>$p['name'],'image'=>image_url($p['image']),'price'=>(int)$p['price'],'material'=>$p['material'],'color'=>$p['color']],$products);
page_start('So sánh hai gọng kính'); ?>
<p>Chọn hai mẫu khác nhau để đối chiếu thông tin. Bạn có thể bỏ hoặc thay mẫu trong từng cột.</p><div id="frameComparison" class="frame-comparison"></div>
<script type="application/json" id="comparisonData"><?= json_encode(['products'=>$data,'ids'=>[$id1,$id2]],JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE) ?></script>
<?php page_end(['frame-comparison','page-compare']); ?>
