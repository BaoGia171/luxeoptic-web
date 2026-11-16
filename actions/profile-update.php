<?php
// POST: full_name, phone, csrf_token
// Chỉ cập nhật full_name và phone của tài khoản trong session. Không nhận id,
// email, role từ trình duyệt; đơn cũ lưu bản sao người nhận nên không đổi.
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/user-helpers.php';

verify_csrf();
require_login();

$user = current_user();

$fullName = user_normalize_name(user_input('full_name'));
$phoneRaw = trim(user_input('phone'));

$errors = [];
if (($message = user_check_name($fullName)) !== null) {
    $errors['full_name'] = $message;
}
if (($message = user_check_phone($phoneRaw)) !== null) {
    $errors['phone'] = $message;
}

$phone = user_normalize_phone($phoneRaw) ?? '';
if (!isset($errors['phone'])) {
    // Loại chính tài khoản hiện tại khi kiểm tra trùng số.
    $statement = $pdo->prepare('SELECT 1 FROM users WHERE phone = ? AND id <> ? LIMIT 1');
    $statement->execute([$phone, (int) $user['id']]);
    if ($statement->fetchColumn()) {
        $errors['phone'] = 'Số điện thoại này đã được sử dụng bởi tài khoản khác.';
    }
}

$old = ['full_name' => $fullName, 'phone' => $phoneRaw];

if ($errors) {
    user_form_fail('profile', $errors, $old, 'profile.php');
}

try {
    $pdo->prepare('UPDATE users SET full_name = ?, phone = ? WHERE id = ?')
        ->execute([$fullName, $phone, (int) $user['id']]);
} catch (PDOException $exception) {
    if (user_is_duplicate_error($exception)) {
        user_form_fail('profile', ['phone' => 'Số điện thoại này đã được sử dụng bởi tài khoản khác.'], $old, 'profile.php');
    }
    error_log('LuxeOptic profile-update: ' . $exception->getMessage());
    flash('error', 'Không thể cập nhật hồ sơ lúc này. Vui lòng thử lại sau.');
    user_form_fail('profile', [], $old, 'profile.php');
}

flash('success', 'Đã cập nhật hồ sơ.');
redirect('profile.php');
