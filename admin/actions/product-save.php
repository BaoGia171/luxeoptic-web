<?php
require __DIR__.'/../../includes/admin-commerce.php';
require __DIR__.'/../../includes/product-content.php';
admin_post(); $id=positive_id($_POST['id']??0);$back='admin/products.php'.($id?'?edit='.$id:'');
$errors=[];$data=[];
foreach(['sku'=>50,'slug'=>150,'name'=>150,'color'=>100,'material'=>100] as $key=>$max){
    $data[$key]=input_text($_POST,$key);
    if(mb_strlen($data[$key])>$max || (in_array($key,['sku','slug','name'],true)&&$data[$key]===''))$errors[$key]='Bắt buộc, tối đa '.$max.' ký tự.';
}
if(!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/',$data['slug']))$errors['slug']='Slug gồm chữ thường, số và dấu gạch nối.';
$data['category_id']=positive_id($_POST['category_id']??0);
if(!$data['category_id'])$errors['category_id']='Chọn danh mục hợp lệ.';
$data['price']=integer_field('price',1,4294967295,$errors);
$data['old_price']=integer_field('old_price',1,4294967295,$errors,true);
$data['stock']=integer_field('stock',0,4294967295,$errors);
if($data['old_price']!==null && $data['old_price']<$data['price'])$errors['old_price']='Giá cũ không được nhỏ hơn giá bán.';
$data['is_active']=checked_field('is_active');$data['is_featured']=checked_field('is_featured');
$raw=input_text($_POST,'description');if(strlen($raw)>60000)$errors['description']='Mô tả quá dài (tối đa 60000 byte).';
try{$uploads=validated_uploads();}catch(DomainException $e){$errors['images']=$e->getMessage();$uploads=[];}
if($errors)admin_failure($back,$errors);
$data['description']=clean_description($raw);$moved=[];
try{
    $pdo->beginTransaction();
    if(!query_rows('SELECT id FROM categories WHERE id=? FOR UPDATE',[$data['category_id']]))throw new DomainException('Danh mục không tồn tại.');
    if($id && !query_rows('SELECT id FROM products WHERE id=? FOR UPDATE',[$id]))throw new DomainException('Sản phẩm không tồn tại.');
    $images=$id?query_rows('SELECT id FROM product_images WHERE product_id=?',[$id]):[];
    if(count($images)+count($uploads)>5 || count($images)+count($uploads)<1)throw new DomainException('Mỗi sản phẩm cần từ 1 đến 5 ảnh (bao gồm ảnh cũ).');
    if(query_rows('SELECT id FROM products WHERE (sku=? OR slug=?) AND id<>?',[$data['sku'],$data['slug'],$id])){
        $pdo->rollBack();admin_failure($back,['sku'=>'SKU hoặc slug đã tồn tại.','slug'=>'SKU hoặc slug đã tồn tại.']);
    }
    $columns=array_keys($data);
    if($id){$s=$pdo->prepare('UPDATE products SET '.implode(',',array_map(fn($k)=>$k.'=?',$columns)).' WHERE id=?');$s->execute([...array_values($data),$id]);}
    else{$s=$pdo->prepare('INSERT INTO products ('.implode(',',$columns).') VALUES ('.implode(',',array_fill(0,count($columns),'?')).')');$s->execute(array_values($data));$id=(int)$pdo->lastInsertId();}
    $order=(int)query_rows('SELECT COALESCE(MAX(sort_order),0) n FROM product_images WHERE product_id=?',[$id])[0]['n'];
    foreach($uploads as $upload){
        if(!move_uploaded_file($upload['tmp'],__DIR__.'/../../'.$upload['path']))throw new RuntimeException('Unable to move upload');
        $moved[]=$upload['path'];
        $s=$pdo->prepare('INSERT INTO product_images(product_id,image_path,alt_text,sort_order) VALUES(?,?,?,?)');$s->execute([$id,$upload['path'],$data['name'],++$order]);
    }
    $pdo->commit();unset($_SESSION['admin_old'],$_SESSION['admin_errors']);flash('success','Đã lưu sản phẩm.');redirect('admin/products.php?edit='.$id);
}catch(Throwable $e){
    if($pdo->inTransaction())$pdo->rollBack();foreach($moved as $path)delete_uploaded_image($path);
    error_log($e->getMessage());
    admin_failure($back,['images'=>$e instanceof DomainException?$e->getMessage():($e instanceof PDOException && $e->getCode()==='23000'?'SKU/slug trùng hoặc dữ liệu tham chiếu không hợp lệ.':'Không thể lưu sản phẩm. Vui lòng thử lại.')]);
}
