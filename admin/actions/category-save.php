<?php
require __DIR__.'/../../includes/admin-commerce.php';admin_post();$id=positive_id($_POST['id']??0);$errors=[];$back='admin/categories.php'.($id?'?edit='.$id:'');
$name=input_text($_POST,'name');$slug=input_text($_POST,'slug');$description=input_text($_POST,'description');
if($name===''||mb_strlen($name)>100)$errors['name']='Tên bắt buộc, tối đa 100 ký tự.';
if(strlen($slug)>120||!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/',$slug))$errors['slug']='Slug tối đa 120 ký tự, dùng chữ thường, số, gạch nối.';
if(strlen($description)>60000)$errors['description']='Mô tả quá dài.';
$order=integer_field('sort_order',0,2147483647,$errors);if($errors)admin_failure($back,$errors);
try{
    if($id && !query_rows('SELECT id FROM categories WHERE id=?',[$id])){http_response_code(404);exit('Danh mục không tồn tại.');}
    $data=[$name,$slug,$description,$order,checked_field('is_active')];
    $s=$pdo->prepare($id?'UPDATE categories SET name=?,slug=?,description=?,sort_order=?,is_active=? WHERE id=?':'INSERT INTO categories(name,slug,description,sort_order,is_active) VALUES(?,?,?,?,?)');
    $s->execute($id?[...$data,$id]:$data);unset($_SESSION['admin_old'],$_SESSION['admin_errors']);flash('success','Đã lưu danh mục.');redirect('admin/categories.php');
}catch(PDOException $e){error_log($e->getMessage());admin_failure($back,['slug'=>$e->getCode()==='23000'?'Slug đã tồn tại.':'Không thể lưu danh mục.']);}
