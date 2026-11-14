<?php
// POST: identifier (email hoặc điện thoại), password, return, csrf_token
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/user-helpers.php';

// Hash giả để thời gian xử lý không lộ việc email/điện thoại có tồn tại hay không.
const LOGIN_DUMMY_HASH = '$2y$10$ALjt57XF/5BjGpS75JbigOYOXW2ii06gABAAXysEIoLlkS00o7HiK';

verify_csrf();

$returnKey = user_return_key(user_input('return'));

if (($current = current_user()) !== null) {
    redirect(user_after_login_path($returnKey, $current['role']));
}

$identifier = trim(user_input('identifier'));
$password = user_input('password');
$failPath = user_auth_path('login', $returnKey);
$old = ['identifier' => $identifier];

if ($identifier === '' || $password === '') {
    user_form_fail('login', ['general' => 'Vui lòng nhập email/số điện thoại và mật khẩu.'], $old, $failPath);
}

// Có @ thì tìm theo email, ngược lại tìm theo số điện thoại đã chuẩn hóa.
$user = null;
if (str_contains($identifier, '@')) {
    $column = 'email';
    $value = user_normalize_email($identifier);
} else {
    $column = 'phone';
    $value = user_normalize_phone($identifier);
}
if ($value !== null) {
    $statement = $pdo->prepare(
        "SELECT id, password_hash, role, status FROM users WHERE {$column} = ? LIMIT 1"
    );
    $statement->execute([$value]);
    $user = $statement->fetch() ?: null;
}

$passwordOk = password_verify($password, $user['password_hash'] ?? LOGIN_DUMMY_HASH);

if ($user === null || !$passwordOk) {
    user_form_fail('login', ['general' => 'Thông tin đăng nhập không đúng'], $old, $failPath);
}

// Chỉ báo khóa khi đã đúng mật khẩu, không lộ tài khoản tồn tại cho người đoán.
if ($user['status'] !== 'active') {
    user_form_fail('login', ['general' => 'Tài khoản đã bị khóa. Vui lòng liên hệ quản trị viên.'], $old, $failPath);
}

if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
    $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?')
        ->execute([password_hash($password, PASSWORD_DEFAULT), (int) $user['id']]);
}

// Đổi session ID chống session fixation; giỏ hàng trong session được giữ nguyên.
session_regenerate_id(true);
$_SESSION['user_id'] = (int) $user['id'];
unset($_SESSION['csrf_token'], $_SESSION['form_state']);

redirect(user_after_login_path($returnKey, $user['role']));
