<?php
require __DIR__ . '/includes/shop.php';
$q = mb_substr(input_text($_GET, 'q'), 0, 100);
$category = input_text($_GET, 'category');
$sort = input_text($_GET, 'sort');
$orders = [
    'newest' => 'p.created_at DESC,p.id DESC',
    'price_asc' => 'p.price ASC,p.id ASC',
    'price_desc' => 'p.price DESC,p.id DESC',
    'name_asc' => 'p.name ASC,p.id ASC',
];
if (!isset($orders[$sort])) {
    $sort = 'newest';
}
$filters = ['q' => $q, 'category' => $category, 'sort' => $sort];
$where = ['p.is_active=1', 'c.is_active=1'];
$params = [];
$error = '';
if ($q !== '') {
    $where[] = '(p.name LIKE ? OR p.sku LIKE ?)';
    $like = '%' . addcslashes($q, '%_\\') . '%';
    array_push($params, $like, $like);
}
if ($category !== '') {
    $where[] = 'c.slug=?';
    $params[] = $category;
}
foreach (['min_price' => '>=', 'max_price' => '<='] as $key => $op) {
    $raw = input_text($_GET, $key);
    $filters[$key] = $raw;
    if ($raw !== '') {
        $v = filter_var($raw, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 0, 'max_range' => 4294967295],
        ]);
        if ($v === false) {
            $error = 'Khoảng giá phải là số nguyên không âm.';
        } else {
            $where[] = "p.price $op ?";
            $params[] = $v;
        }
    }
}
if (
    $filters['min_price'] !== '' &&
    $filters['max_price'] !== '' &&
    (int) $filters['min_price'] > (int) $filters['max_price']
) {
    $error = 'Giá tối thiểu không được lớn hơn giá tối đa.';
}
$categories = query_rows('SELECT * FROM categories WHERE is_active=1 ORDER BY sort_order,id');
$title = 'Tất cả mắt kính';
foreach ($categories as $c) {
    if ($c['slug'] === $category) {
        $title = $c['name'];
    }
}
if ($category === '' && $sort === 'newest') {
    $title = 'Hàng mới';
}
$activeNav = $category ?: ($sort === 'newest' ? 'newest' : '');
$sqlWhere = ' WHERE ' . implode(' AND ', $where);
$total = $error
    ? 0
    : (int) query_rows(
        'SELECT COUNT(*) n FROM products p JOIN categories c ON c.id=p.category_id' . $sqlWhere,
        $params,
    )[0]['n'];
[$page, $pages, $offset] = paginate($total, 12);
$products = $error
    ? []
    : query_rows(
        product_select() .
            $sqlWhere .
            ' ORDER BY ' .
            $orders[$sort] .
            ' LIMIT 12 OFFSET ' .
            $offset,
        $params,
    );
page_start($title);
?>
<form class="filters" method="get">
    <label>Tìm tên hoặc SKU
        <input name="q" value="<?= e($q) ?>" maxlength="100">
    </label>
    <label>Danh mục<select name="category">
            <option value="">Tất cả</option><?php foreach($categories as $c): ?><option
                value="<?= e($c['slug']) ?>" <?= $category===$c['slug']?'selected':'' ?>><?= e($c['name']) ?>
            </option><?php endforeach ?>
        </select>
    </label>
    <?php foreach(['min_price'=>'Giá từ','max_price'=>'Giá đến'] as $key=>$label): ?><label><?= $label ?>
        <input type="number" min="0" name="<?= $key ?>" value="<?= e($filters[$key]) ?>">
    </label><?php endforeach ?>
    <label>Sắp xếp<select name="sort">
            <?php foreach(['newest'=>'Mới nhất','price_asc'=>'Giá tăng dần','price_desc'=>'Giá giảm dần','name_asc'=>'Tên A–Z'] as $key=>$label): ?>
            <option value="<?= $key ?>" <?= $sort===$key?'selected':'' ?>><?= $label ?></option>
            <?php endforeach ?></select>
    </label>
    <button class="btn btn-primary">Áp dụng</button>
</form>
<?php if($error): ?><p class="field-error" role="alert"><?= e($error) ?></p><?php endif ?>
<p><?= $total ?> mẫu kính</p>
<div class="compare-toolbar">
    <span id="compareStatus" role="status">Chọn tối đa hai mẫu để so sánh.</span>
    <button id="compareButton" class="btn btn-primary" type="button" disabled>So sánh hai gọng</button>
</div>
<div class="catalog-grid"><?php foreach($products as $product) product_card($product); ?></div>
<?php if(!$products && !$error): ?><p class="notice">
    <?= $category?'Chưa có mẫu trong danh mục này':'Không tìm thấy mẫu phù hợp' ?>.</p><?php endif ?>
<?php pagination($total,$page,$pages,$filters); page_end(['page-products']); ?>
