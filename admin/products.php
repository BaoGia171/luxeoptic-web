<?php
require __DIR__ . '/../includes/admin-commerce.php';
require __DIR__ . '/../includes/product-content.php';
require_admin();
$id = positive_id($_GET['edit'] ?? 0);
$item = $id
    ? query_rows('SELECT * FROM products WHERE id=?', [$id])[0] ?? null
    : ['is_active' => 1, 'stock' => 0];
if (!$item && $id) {
    http_response_code(404);
    exit('Không tìm thấy sản phẩm.');
}
$errors = $_SESSION['admin_errors'] ?? [];
$values = $_SESSION['admin_old'] ?? $item;
unset($_SESSION['admin_errors'], $_SESSION['admin_old']);
$q = mb_substr(input_text($_GET, 'q'), 0, 100);
$like = '%' . addcslashes($q, '%_\\') . '%';
$total = (int) query_rows('SELECT COUNT(*) n FROM products WHERE name LIKE ? OR sku LIKE ?', [
    $like,
    $like,
])[0]['n'];
[$page, $pages, $offset] = paginate($total, 10);
$rows = query_rows(
    product_select() .
        ' WHERE p.name LIKE ? OR p.sku LIKE ? ORDER BY p.id DESC LIMIT 10 OFFSET ' .
        $offset,
    [$like, $like],
);
$categories = query_rows('SELECT * FROM categories ORDER BY sort_order,id');
page_start('Quản lý sản phẩm', true); ?>
<form class="filters" method="get">
    <label>Tìm tên / SKU
        <input name="q" value="<?= e($q) ?>">
    </label>
    <button class="btn">Tìm kiếm</button>
    <a class="btn" href="<?= e(url('admin/products.php')) ?>">Thêm sản phẩm</a>
</form>
<div class="panel table-scroll">
    <table class="commerce-table">
        <thead>
            <tr>
                <th>Ảnh</th>
                <th>Sản phẩm</th>
                <th>Giá / Kho</th>
                <th>Trạng thái</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody><?php foreach($rows as $p): ?><tr>
                <td>
                    <img src="<?= e(image_url($p['image'])) ?>" alt="<?= e($p['name']) ?>">
                </td>
                <td><?= e($p['name']) ?>
                    <br><?= e($p['sku']) ?>
                </td>
                <td><?= money((int)$p['price']) ?>
                    <br><?= (int)$p['stock'] ?>
                </td>
                <td><?= $p['is_active']?'Hiển thị':'Ẩn' ?></td>
                <td>
                    <a class="btn" href="?edit=<?= (int)$p['id'] ?>">Sửa</a>
                    <form class="inline-form" method="post"
                        action="<?= e(url('admin/actions/product-delete.php')) ?>"
                        data-confirm="Ẩn sản phẩm này?"><?php csrf_input() ?>
                        <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                        <button class="btn">Ẩn</button>
                    </form>
                </td>
            </tr><?php endforeach ?></tbody>
    </table><?php if(!$rows): ?><p>Chưa có sản phẩm phù hợp.</p><?php endif ?>
</div>
<?php pagination($total,$page,$pages,['q'=>$q]); ?>
<form class="panel" id="productForm" enctype="multipart/form-data" method="post"
    action="<?= e(url('admin/actions/product-save.php')) ?>">
    <h2><?= $id?'Sửa':'Thêm' ?> sản phẩm</h2><?php csrf_input() ?>
    <input type="hidden" name="id" value="<?= $id ?>">
    <div class="form-grid">
        <?php foreach (
    [
        'name' => 'Tên kính',
        'sku' => 'SKU',
        'slug' => 'Slug',
        'price' => 'Giá bán (VND)',
        'old_price' => 'Giá cũ (VND)',
        'stock' => 'Tồn kho',
        'color' => 'Màu',
        'material' => 'Chất liệu',
    ]
    as $key => $label
) {
    form_field(
        $key,
        $label,
        $values,
        $errors,
        in_array($key, ['price', 'old_price', 'stock']) ? 'number' : 'text',
        !in_array($key, ['old_price', 'color', 'material']),
    );
} ?>
        <div class="field">
            <label for="category_id">Danh mục *</label>
            <select id="category_id" name="category_id" required>
                <option value="">Chọn danh mục</option><?php foreach($categories as $c): ?><option
                    value="<?= (int)$c['id'] ?>" <?= ($values['category_id']??'')==$c['id']?'selected':'' ?>>
                    <?= e($c['name']) ?><?= !$c['is_active']?' (đang ẩn)':'' ?></option><?php endforeach ?>
            </select><?php field_error('category_id',$errors) ?>
        </div>
    </div>
    <label>
        <input type="checkbox" name="is_active" value="1" <?= !empty($values['is_active'])?'checked':'' ?>>
        Hiển thị
    </label>
    <label>
        <input type="checkbox" name="is_featured" value="1"
            <?= !empty($values['is_featured'])?'checked':'' ?>> Nổi bật trên trang chủ
    </label>
    <div class="field">
        <label for="description">Mô tả có định dạng</label>
        <textarea id="description" name="description"><?= e($values['description']??'') ?></textarea>
        <div id="editor" class="editor-shell" hidden></div><?php field_error('description',$errors) ?>
    </div>
    <div class="field">
        <label for="images">Ảnh (JPEG/PNG/WebP, tối đa 2MB mỗi ảnh, tổng 1–5 ảnh)</label>
        <input type="file" id="images" name="images[]" multiple accept="image/jpeg,image/png,image/webp">
        <?php field_error('images',$errors) ?>
    </div>
    <button class="btn btn-primary" type="submit">Lưu sản phẩm</button>
</form>
<?php if($id): ?><section class="panel">
    <h2>Ảnh hiện tại (ảnh đầu là đại diện)</h2>
    <?php foreach(query_rows('SELECT * FROM product_images WHERE product_id=? ORDER BY sort_order,id',[$id]) as $im): ?>
    <div class="gallery-item">
        <img src="<?= e(image_url($im['image_path'])) ?>" alt="<?= e($im['alt_text']) ?>">
        <form method="post" action="<?= e(url('admin/actions/product-image-delete.php')) ?>"
            data-confirm="Xóa ảnh này?"><?php csrf_input() ?>
            <input type="hidden" name="image_id" value="<?= (int)$im['id'] ?>">
            <button class="btn">Xóa ảnh</button>
        </form>
    </div><?php endforeach ?>
</section><?php endif ?>
<link rel="stylesheet" href="<?= e(url('assets/vendor/quill/quill.snow.css')) ?>">
<script src="<?= e(url('assets/vendor/quill/quill.js')) ?>"></script>
<?php page_end(['page-admin-products']); ?>
