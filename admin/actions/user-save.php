<?php
// POST: id nếu sửa, full_name, email, phone, role, status, password khi tạo, csrf_token
// Chỉ Admin. Có id = sửa (không đổi mật khẩu), không id = tạo mới (bắt buộc mật khẩu).
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/user-helpers.php';

verify_csrf();
require_admin();

$actor = current_user();
$backPath = user_admin_back_path();

$idRaw = user_input('id');
if ($idRaw !== '' && (!ctype_digit($idRaw) || (int) $idRaw < 1)) {
    http_response_code(404);
    exit('Không tìm thấy tài khoản.');
}
$id = $idRaw === '' ? 0 : (int) $idRaw;
$isEdit = $id > 0;

$fullName = user_normalize_name(user_input('full_name'));
$email = user_normalize_email(user_input('email'));
$phoneRaw = trim(user_input('phone'));
$role = user_input('role');
$status = user_input('status');
$password = user_input('password');

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
// Không tin giá trị trình duyệt gửi: chỉ nhận đúng hai role và hai trạng thái.
if (!in_array($role, ['customer', 'admin'], true)) {
    $errors['role'] = 'Vai trò chỉ có thể là customer hoặc admin.';
}
if (!in_array($status, ['active', 'blocked'], true)) {
    $errors['status'] = 'Trạng thái chỉ có thể là active hoặc blocked.';
}
if (!$isEdit && ($message = user_check_password($password)) !== null) {
    $errors['password'] = $message;
}

$phone = user_normalize_phone($phoneRaw) ?? '';
$old = [
    'id' => $isEdit ? (string) $id : '',
    'full_name' => $fullName,
    'email' => $email,
    'phone' => $phoneRaw,
    'role' => $role,
    'status' => $status,
];

if ($errors) {
    user_form_fail('user_save', $errors, $old, $backPath);
}

try {
    $pdo->beginTransaction();

    // Khóa Admin hoạt động trước, rồi mới khóa dòng đích: thứ tự cố định tránh deadlock.
    $activeAdmins = user_lock_active_admins($pdo);

    $target = null;
    if ($isEdit) {
        $statement = $pdo->prepare('SELECT id, role, status FROM users WHERE id = ? FOR UPDATE');
        $statement->execute([$id]);
        $target = $statement->fetch() ?: null;
        if ($target === null) {
            $pdo->rollBack();
            http_response_code(404);
            exit('Không tìm thấy tài khoản.');
        }

        $ruleError = user_admin_change_error($target, $role, $status, (int) $actor['id'], $activeAdmins);
        if ($ruleError !== null) {
            $pdo->rollBack();
            user_form_fail('user_save', ['general' => $ruleError], $old, $backPath);
        }
    }

    $conflicts = user_find_conflicts($pdo, $email, $phone, $id);
    if ($conflicts) {
        $pdo->rollBack();
        user_form_fail('user_save', $conflicts, $old, $backPath);
    }

    if ($isEdit) {
        $pdo->prepare(
            'UPDATE users SET full_name = ?, email = ?, phone = ?, role = ?, status = ? WHERE id = ?'
        )->execute([$fullName, $email, $phone, $role, $status, $id]);
    } else {
        $pdo->prepare(
            'INSERT INTO users (full_name, email, phone, password_hash, role, status)
             VALUES (?, ?, ?, ?, ?, ?)'
        )->execute([$fullName, $email, $phone, password_hash($password, PASSWORD_DEFAULT), $role, $status]);
    }

    $pdo->commit();
} catch (PDOException $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    if (user_is_duplicate_error($exception)) {
        $conflicts = user_find_conflicts($pdo, $email, $phone, $id)
            ?: ['email' => 'Email hoặc số điện thoại đã được sử dụng.'];
        user_form_fail('user_save', $conflicts, $old, $backPath);
    }
    error_log('LuxeOptic user-save: ' . $exception->getMessage());
    user_form_fail('user_save', ['general' => 'Không thể lưu tài khoản lúc này. Vui lòng thử lại.'], $old, $backPath);
}

// Admin tự hạ quyền mình (được phép khi còn Admin khác): không còn vào được trang Admin.
if ($isEdit && $id === (int) $actor['id'] && $role !== 'admin') {
    flash('success', 'Đã lưu. Tài khoản của bạn không còn quyền quản trị.');
    redirect('index.php');
}

flash('success', $isEdit ? 'Đã cập nhật tài khoản.' : 'Đã tạo tài khoản mới.');
redirect($backPath);
