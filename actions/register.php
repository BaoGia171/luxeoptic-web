<?php
// POST: full_name, email, phone, password, password_confirm, agree_terms, return, csrf_token
// Đăng ký công khai luôn tạo customer/active, bỏ qua mọi role/status trình duyệt gửi.
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/user-helpers.php';

verify_csrf();

$returnKey = user_return_key(user_input('return'));

if (current_user() !== null) {
    redirect(user_after_login_path($returnKey, current_user()['role']));
}

$fullName = user_normalize_name(user_input('full_name'));
$email = user_normalize_email(user_input('email'));
$phoneRaw = trim(user_input('phone'));
$password = user_input('password');
$passwordConfirm = user_input('password_confirm');
$agreed = user_input('agree_terms') !== '';

$errors = [];
if (($message = user_check_name($fullName)) !== null) {
    $errors['full_name'] = $message;
}
if (($message = user_check_email($email)) !== null) {
    $errors['email'] = $message;
}
if (($message = user_check_phone($phoneRaw)) !== null) {
    $errors['phone'] = $message;
}
if (($message = user_check_password($password)) !== null) {
    $errors['password'] = $message;
} elseif ($password !== $passwordConfirm) {
    $errors['password_confirm'] = 'Mật khẩu xác nhận không khớp.';
}
if (!$agreed) {
    $errors['agree_terms'] = 'Bạn cần đồng ý với điều khoản thành viên.';
}

$phone = user_normalize_phone($phoneRaw) ?? '';

if (!isset($errors['email']) && !isset($errors['phone'])) {
    $errors += user_find_conflicts($pdo, $email, $phone);
}

// Không đưa mật khẩu trở lại form.
$old = ['full_name' => $fullName, 'email' => $email, 'phone' => $phoneRaw];
$failPath = user_auth_path('register', $returnKey);

if ($errors) {
    user_form_fail('register', $errors, $old, $failPath);
}

try {
    $statement = $pdo->prepare(
        'INSERT INTO users (full_name, email, phone, password_hash, role, status)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    $statement->execute([
        $fullName,
        $email,
        $phone,
        password_hash($password, PASSWORD_DEFAULT),
        'customer',
        'active',
    ]);
} catch (PDOException $exception) {
    if (user_is_duplicate_error($exception)) {
        // Hai yêu cầu trùng nhau lọt qua bước kiểm tra phía trên.
        $errors = user_find_conflicts($pdo, $email, $phone)
            ?: ['email' => 'Email hoặc số điện thoại đã được sử dụng.'];
        user_form_fail('register', $errors, $old, $failPath);
    }
    error_log('LuxeOptic register: ' . $exception->getMessage());
    flash('error', 'Không thể đăng ký lúc này. Vui lòng thử lại sau.');
    user_form_fail('register', [], $old, $failPath);
}

flash('success', 'Đăng ký thành công. Vui lòng đăng nhập.');
redirect(user_auth_path('login', $returnKey));
