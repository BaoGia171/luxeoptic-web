<?php
require_once __DIR__ . '/shop.php';
function admin_post(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        header('Allow: POST');
        exit('Chỉ nhận POST.');
    }
    require_admin();
    verify_csrf();
}
function admin_failure(string $path, array $errors): never
{
    $_SESSION['admin_old'] = array_filter($_POST, fn($value) => is_string($value));
    $_SESSION['admin_errors'] = $errors;
    flash('error', 'Vui lòng kiểm tra các trường được báo lỗi.');
    redirect($path);
    exit();
}
function integer_field(
    string $key,
    int $min,
    int $max,
    array &$errors,
    bool $optional = false,
): ?int {
    $value = input_text($_POST, $key);
    if ($optional && $value === '') {
        return null;
    }
    $n = filter_var($value, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => $min, 'max_range' => $max],
    ]);
    if ($n === false) {
        $errors[$key] = 'Phải là số nguyên từ ' . $min . ' đến ' . $max . '.';
        return null;
    }
    return $n;
}
function checked_field(string $name): int
{
    return input_text($_POST, $name) === '1' ? 1 : 0;
}
function delete_uploaded_image(string $path): void
{
    // Only our random names; never delete shared assets or another location.
    if (preg_match('~^uploads/products/[a-f0-9]{32}\.(jpg|png|webp)$~', $path)) {
        // Keep an uploaded image referenced by an existing order snapshot.
        if (query_rows('SELECT id FROM order_items WHERE product_image=? LIMIT 1', [$path])) {
            return;
        }
        $file = __DIR__ . '/../' . $path;
        if (is_file($file) && !unlink($file)) {
            error_log('Could not remove image: ' . $path);
        }
    }
}
function validated_uploads(): array
{
    $f = $_FILES['images'] ?? null;
    if (!$f) {
        return [];
    }
    if (!is_array($f['name'] ?? null) || !is_array($f['error'] ?? null)) {
        throw new DomainException('Định dạng upload không hợp lệ.');
    }
    $images = [];
    $types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    foreach ($f['error'] as $i => $err) {
        if ($err === UPLOAD_ERR_NO_FILE) {
            continue;
        }
        if ($err !== UPLOAD_ERR_OK) {
            throw new DomainException('Upload thất bại. Kiểm tra giới hạn dung lượng máy chủ.');
        }
        $tmp = $f['tmp_name'][$i] ?? '';
        if (
            !is_string($tmp) ||
            !is_uploaded_file($tmp) ||
            filesize($tmp) > 2 * 1024 * 1024 ||
            filesize($tmp) < 1
        ) {
            throw new DomainException('Ảnh phải nhỏ hơn hoặc bằng 2MB.');
        }
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmp);
        $size = @getimagesize($tmp);
        if (!isset($types[$mime]) || !$size || ($size['mime'] ?? '') !== $mime) {
            throw new DomainException('Chỉ nhận ảnh JPEG, PNG hoặc WebP hợp lệ.');
        }
        $images[] = [
            'tmp' => $tmp,
            'path' => 'uploads/products/' . bin2hex(random_bytes(16)) . '.' . $types[$mime],
        ];
    }
    return $images;
}
