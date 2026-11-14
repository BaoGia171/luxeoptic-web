<?php
// Hàm dùng chung cho phần tài khoản (TV2): chuẩn hóa/kiểm tra dữ liệu, chặn
// Admin cuối cùng, lưu lỗi form qua redirect. Không sửa includes/functions.php
// của TV1; tên hàm đều bắt đầu bằng user_ để không trùng hợp đồng chung.
// File này không dùng session ở cấp file nên scripts/create-admin.php (CLI) nạp được.

// ---------- Đọc và chuẩn hóa input ----------

// Đọc một trường POST dạng chuỗi; mảng hoặc thiếu trường đều thành ''.
function user_input(string $key): string
{
    $value = $_POST[$key] ?? '';
    return is_string($value) ? $value : '';
}

function user_normalize_name(string $value): string
{
    return trim(preg_replace('/\s+/u', ' ', $value) ?? '');
}

function user_normalize_email(string $value): string
{
    return mb_strtolower(trim($value), 'UTF-8');
}

// Bỏ khoảng trắng; chấp nhận 0 + 9 số hoặc +84 + 9 số, lưu dạng 0xxxxxxxxx.
// Trả về null nếu không hợp lệ.
function user_normalize_phone(string $value): ?string
{
    $phone = preg_replace('/\s+/u', '', $value) ?? '';
    if (preg_match('/^\+84(\d{9})$/', $phone, $m)) {
        return '0' . $m[1];
    }
    if (preg_match('/^0\d{9}$/', $phone)) {
        return $phone;
    }
    return null;
}

// ---------- Kiểm tra từng trường (trả về thông báo lỗi hoặc null) ----------

function user_check_name(string $name): ?string
{
    $length = mb_strlen($name, 'UTF-8');
    if ($length < 2 || $length > 100) {
        return 'Họ tên phải dài từ 2 đến 100 ký tự.';
    }
    return null;
}

function user_check_email(string $email): ?string
{
    if ($email === '' || mb_strlen($email, 'UTF-8') > 150
        || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        return 'Email không hợp lệ (tối đa 150 ký tự).';
    }
    return null;
}

function user_check_phone(string $raw): ?string
{
    if (user_normalize_phone($raw) === null) {
        return 'Số điện thoại phải gồm 0 + 9 chữ số hoặc +84 + 9 chữ số.';
    }
    return null;
}

function user_check_password(string $password): ?string
{
    if (mb_strlen($password, 'UTF-8') < 8) {
        return 'Mật khẩu phải có ít nhất 8 ký tự.';
    }
    // bcrypt chỉ dùng 72 byte đầu; chặn để mật khẩu dài không bị cắt âm thầm.
    if (strlen($password) > 72) {
        return 'Mật khẩu quá dài (tối đa 72 byte).';
    }
    return null;
}

// Tìm email/điện thoại đã dùng bởi tài khoản khác. $exceptId = 0 khi tạo mới.
// Trả về mảng lỗi theo tên trường (email, phone) hoặc mảng rỗng.
function user_find_conflicts(PDO $pdo, string $email, string $phone, int $exceptId = 0): array
{
    $statement = $pdo->prepare(
        'SELECT email, phone FROM users WHERE (email = ? OR phone = ?) AND id <> ?'
    );
    $statement->execute([$email, $phone, $exceptId]);

    $errors = [];
    foreach ($statement->fetchAll() as $row) {
        if (mb_strtolower($row['email'], 'UTF-8') === $email) {
            $errors['email'] = 'Email này đã được sử dụng.';
        }
        if ($row['phone'] === $phone) {
            $errors['phone'] = 'Số điện thoại này đã được sử dụng.';
        }
    }
    return $errors;
}

// Lỗi trùng khóa UNIQUE khi hai yêu cầu chạy gần như cùng lúc.
function user_is_duplicate_error(PDOException $exception): bool
{
    return ($exception->errorInfo[0] ?? '') === '23000'
        && (int) ($exception->errorInfo[1] ?? 0) === 1062;
}

// ---------- return= sau đăng nhập (chỉ nhận cart, home) ----------

const USER_RETURN_TARGETS = ['cart' => 'cart.php', 'home' => 'index.php'];

function user_return_key(mixed $value): string
{
    return is_string($value) && isset(USER_RETURN_TARGETS[$value]) ? $value : '';
}

function user_auth_path(string $tab, string $returnKey = ''): string
{
    $query = ['tab' => $tab];
    if ($returnKey !== '') {
        $query['return'] = $returnKey;
    }
    return 'auth.php?' . http_build_query($query);
}

// Nơi chuyển tới sau đăng nhập: return hợp lệ, nếu không thì theo vai trò.
function user_after_login_path(string $returnKey, string $role): string
{
    if ($returnKey !== '') {
        return USER_RETURN_TARGETS[$returnKey];
    }
    return $role === 'admin' ? 'admin/users.php' : 'index.php';
}

// ---------- Lỗi form qua redirect (POST → lưu session → redirect → hiện 1 lần) ----------

function user_form_fail(string $form, array $errors, array $old, string $redirectPath): void
{
    $_SESSION['form_state'][$form] = ['errors' => $errors, 'old' => $old];
    redirect($redirectPath);
}

// Lấy và xóa trạng thái form; luôn trả về đủ hai khóa errors/old.
function user_form_take(string $form): array
{
    $state = $_SESSION['form_state'][$form] ?? [];
    unset($_SESSION['form_state'][$form]);
    return [
        'errors' => is_array($state['errors'] ?? null) ? $state['errors'] : [],
        'old' => is_array($state['old'] ?? null) ? $state['old'] : [],
    ];
}

// Thuộc tính aria cho ô nhập có lỗi và đoạn lỗi hiện ngay dưới ô.
function user_field_attrs(array $state, string $key, string $prefix): string
{
    if (!isset($state['errors'][$key])) {
        return '';
    }
    return ' aria-invalid="true" aria-describedby="' . e($prefix . '-' . $key . '-error') . '"';
}

function user_field_error(array $state, string $key, string $prefix): string
{
    if (!isset($state['errors'][$key])) {
        return '';
    }
    return '<p class="text-11px text-red-600 mt-1 field-error" id="'
        . e($prefix . '-' . $key . '-error') . '" role="alert">'
        . e((string) $state['errors'][$key]) . '</p>';
}

// ---------- Quy tắc Admin trong quản lý tài khoản ----------

// Khóa mọi Admin đang hoạt động theo thứ tự id; gọi ngay sau beginTransaction()
// ở mọi action đổi role/status để hai Admin sửa nhau cùng lúc không làm hết Admin.
function user_lock_active_admins(PDO $pdo): int
{
    $statement = $pdo->query(
        "SELECT id FROM users WHERE role = 'admin' AND status = 'active' ORDER BY id FOR UPDATE"
    );
    return count($statement->fetchAll());
}

// Trả về thông báo lỗi nếu thay đổi vi phạm quy tắc, ngược lại null.
function user_admin_change_error(
    array $target,
    string $newRole,
    string $newStatus,
    int $actorId,
    int $activeAdminCount
): ?string {
    if ((int) $target['id'] === $actorId && $newStatus !== 'active') {
        return 'Bạn không thể khóa chính tài khoản của mình.';
    }

    $isActiveAdmin = $target['role'] === 'admin' && $target['status'] === 'active';
    $staysActiveAdmin = $newRole === 'admin' && $newStatus === 'active';
    if ($isActiveAdmin && !$staysActiveAdmin && $activeAdminCount <= 1) {
        return 'Không thể khóa hoặc hạ quyền quản trị viên hoạt động cuối cùng.';
    }
    return null;
}

// Đường dẫn quay lại danh sách Admin với bộ lọc/trang cũ; chỉ giữ khóa đã biết.
function user_admin_back_path(): string
{
    $raw = $_POST['back'] ?? '';
    $params = [];
    parse_str(is_string($raw) ? $raw : '', $params);

    $query = [];
    $q = isset($params['q']) && is_string($params['q']) ? trim($params['q']) : '';
    if ($q !== '' && mb_strlen($q, 'UTF-8') <= 100) {
        $query['q'] = $q;
    }
    if (in_array($params['role'] ?? null, ['admin', 'customer'], true)) {
        $query['role'] = $params['role'];
    }
    if (in_array($params['status'] ?? null, ['active', 'blocked'], true)) {
        $query['status'] = $params['status'];
    }
    $page = isset($params['page']) && is_string($params['page']) && ctype_digit($params['page'])
        ? (int) $params['page'] : 1;
    if ($page > 1) {
        $query['page'] = $page;
    }

    return 'admin/users.php' . ($query ? '?' . http_build_query($query) : '');
}
