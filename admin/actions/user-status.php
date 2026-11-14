<?php
// POST: id, status (active|blocked), back, csrf_token
// Khóa/mở khóa tài khoản. Chỉ Admin. Không khóa chính mình, không khóa Admin
// hoạt động cuối cùng. Tài khoản bị khóa mất quyền ngay vì current_user() đọc lại DB.
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/user-helpers.php';

verify_csrf();
require_admin();

$actor = current_user();
$backPath = user_admin_back_path();

$idRaw = user_input('id');
$status = user_input('status');
if (!ctype_digit($idRaw) || (int) $idRaw < 1) {
    http_response_code(404);
    exit('Không tìm thấy tài khoản.');
}
$id = (int) $idRaw;

if (!in_array($status, ['active', 'blocked'], true)) {
    flash('error', 'Trạng thái không hợp lệ.');
    redirect($backPath);
}

try {
    $pdo->beginTransaction();

    $activeAdmins = user_lock_active_admins($pdo);

    $statement = $pdo->prepare('SELECT id, role, status FROM users WHERE id = ? FOR UPDATE');
    $statement->execute([$id]);
    $target = $statement->fetch() ?: null;
    if ($target === null) {
        $pdo->rollBack();
        http_response_code(404);
        exit('Không tìm thấy tài khoản.');
    }

    $ruleError = user_admin_change_error($target, $target['role'], $status, (int) $actor['id'], $activeAdmins);
    if ($ruleError !== null) {
        $pdo->rollBack();
        flash('error', $ruleError);
        redirect($backPath);
    }

    if ($target['status'] !== $status) {
        $pdo->prepare('UPDATE users SET status = ? WHERE id = ?')->execute([$status, $id]);
    }
    $pdo->commit();
} catch (PDOException $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('LuxeOptic user-status: ' . $exception->getMessage());
    flash('error', 'Không thể đổi trạng thái lúc này. Vui lòng thử lại.');
    redirect($backPath);
}

flash('success', $status === 'blocked' ? 'Đã khóa tài khoản.' : 'Đã mở khóa tài khoản.');
redirect($backPath);
