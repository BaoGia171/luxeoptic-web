<?php
// Common rendering and validation for catalog, cart and order pages.
require_once __DIR__ . '/bootstrap.php';

function input_text(array $source, string $key): string
{
    return isset($source[$key]) && is_string($source[$key]) ? trim($source[$key]) : '';
}
function positive_id(mixed $value): int
{
    return is_scalar($value)
        ? (int) (filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0)
        : 0;
}
function query_rows(string $sql, array $params = []): array
{
    global $pdo;
    $s = $pdo->prepare($sql);
    $s->execute($params);
    return $s->fetchAll();
}
function product_select(): string
{
    return 'SELECT p.*, c.name AS category_name, c.slug AS category_slug, (SELECT image_path FROM product_images WHERE product_id=p.id ORDER BY sort_order,id LIMIT 1) AS image FROM products p JOIN categories c ON c.id=p.category_id';
}
function visible_product(int $id): ?array
{
    return query_rows(product_select() . ' WHERE p.id=? AND p.is_active=1 AND c.is_active=1', [
        $id,
    ])[0] ?? null;
}
function image_url(?string $path): string
{
    if (
        !$path ||
        !preg_match('~^(assets/images|uploads/products)/[a-zA-Z0-9_./-]+$~', $path) ||
        str_contains($path, '..')
    ) {
        return url('assets/images/no-image.svg');
    }
    return url($path);
}
function csrf_input(): void
{
    echo '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}
function notices(): void
{
    foreach (get_flashes() as $msg) {
        echo '<p role="alert" class="notice">' . e($msg['message']) . '</p>';
    }
}
function paginate(int $total, int $size): array
{
    $pages = max(1, (int) ceil($total / $size));
    $page = min($pages, max(1, positive_id($_GET['page'] ?? 1)));
    return [$page, $pages, ($page - 1) * $size];
}
function pagination(int $total, int $page, int $pages, array $params = []): void
{
    if (!$total || $pages < 2) {
        return;
    }
    echo '<nav class="pagination" aria-label="Phân trang">';
    $link = function (int $n, string $label, bool $current = false) use ($params): void {
        echo '<a ' .
            ($current ? 'aria-current="page"' : '') .
            ' href="?' .
            e(http_build_query(array_merge($params, ['page' => $n]))) .
            '">' .
            e($label) .
            '</a>';
    };
    if ($page > 1) {
        $link(1, 'Đầu');
        $link($page - 1, 'Trước');
    }
    for ($n = max(1, $page - 2); $n <= min($pages, $page + 2); $n++) {
        $link($n, (string) $n, $n === $page);
    }
    if ($page < $pages) {
        $link($page + 1, 'Sau');
        $link($pages, 'Cuối');
    }
    echo '</nav>';
}

function page_start(string $title, bool $admin=false): void {
    $GLOBALS['shop_admin']=$admin;
    ?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= e($title) ?> | LuxeOptic</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Bodoni+Moda:wght@400;600&family=Inter:wght@400;500;600&family=Montserrat:wght@500;600&display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">
    <?php foreach (['style', ...($admin?['admin']:[]), 'responsive', 'shopping-tools','theme','ui-effects','commerce'] as $css): ?>
    <link rel="stylesheet" href="<?= e(url('assets/css/'.$css.'.css')) ?>"><?php endforeach ?>
    <script src="<?= e(url('assets/js/theme.js')) ?>"></script>
</head>
<body class="<?= $admin ? 'admin-body' : '' ?>" data-base-url="<?= e(BASE_URL) ?>">
    <?php if ($admin):
        $activeAdmin=pathinfo($_SERVER['SCRIPT_NAME'],PATHINFO_FILENAME);
        require __DIR__.'/../admin/includes/sidebar.php'; ?>
    <div class="admin-main">
        <header class="management-header">
            <button class="admin-menu-toggle btn" aria-controls="adminMenu" aria-expanded="false"
                type="button">☰ Menu</button>
            <span class="logo-text">LuxeOptic / Quản trị</span>
        </header>
        <?php else: $activeNav=$GLOBALS['activeNav']??''; require __DIR__.'/header.php'; endif; ?>
        <main class="container commerce-main">
            <div class="section-heading">
                <p class="uppercase-spaced">LuxeOptic Atelier</p>
                <h1><?= e($title) ?></h1>
            </div><?php notices();
}
function page_end(array $scripts=[]): void {
    ?>
        </main>
        <?php if (!empty($GLOBALS['shop_admin'])) echo '</div>'; if (empty($GLOBALS['shop_admin'])) require __DIR__.'/footer.php'; ?>
        <?php foreach (['main','ui-effects', ...($GLOBALS['shop_admin']?['admin']:['chatbot']), ...$scripts] as $js): ?>
        <script src="<?= e(url('assets/js/'.$js.'.js')) ?>"
            <?= $js==='chatbot' && basename($_SERVER['SCRIPT_NAME'])==='product-detail.php' ? 'data-product-detail' : '' ?>
            defer></script><?php endforeach ?>
</body>
</html><?php
}
function product_card(array $p, bool $compare=true): void {
    $link=url('product-detail.php?id='.$p['id']); ?>
<article class="product-card">
    <a href="<?= e($link) ?>">
        <img class="catalog-image" src="<?= e(image_url($p['image'])) ?>" alt="<?= e($p['name']) ?>"
            loading="lazy">
    </a>
    <div class="product-info">
        <p class="uppercase-spaced"><?= e($p['material']) ?></p>
        <h2>
            <a href="<?= e($link) ?>"><?= e($p['name']) ?></a>
        </h2>
        <p><?= e($p['color']) ?></p>
        <div class="product-bottom">
            <strong><?= money((int)$p['price']) ?></strong>
            <a class="btn btn-primary" href="<?= e($link) ?>">Xem chi tiết</a>
        </div><?php if($compare): ?><label class="compare-pick">
            <input type="checkbox" name="compare" value="<?= (int)$p['id'] ?>"> Chọn so sánh
        </label><?php endif ?>
    </div>
</article>
<?php
}
function form_field(string $name, string $label, array $values, array $errors=[], string $type='text', bool $required=true): void {
    ?><div class="field">
    <label for="<?= e($name) ?>"><?= e($label) ?><?= $required?' *':'' ?></label>
    <input class="form-control" id="<?= e($name) ?>" name="<?= e($name) ?>" type="<?= e($type) ?>"
        value="<?= e((string)($values[$name]??'')) ?>" placeholder="<?= e($label) ?>"
        <?= $required?'required':'' ?>
        <?= isset($errors[$name])?'aria-invalid="true" aria-describedby="error-'.$name.'"':'' ?>>
    <?php field_error($name,$errors) ?>
</div><?php
}
function field_error(string $name,array $errors): void {
    if(isset($errors[$name])) echo '<p class="field-error" id="error-'.e($name).'" role="alert">'.e($errors[$name]).'</p>';
}
