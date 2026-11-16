<?php

// Chi ghep duong dan noi bo. Khong nhan URL ben ngoai hoac ../.
function url(string $path = ''): string
{
    $decodedPath = rawurldecode($path);
    if (preg_match('/[\x00-\x20\x7f\\\\]/', $decodedPath)
        || str_starts_with($decodedPath, '//')
        || preg_match('/^[a-z][a-z0-9+.-]*:/i', $decodedPath)
        || preg_match('~(^|/)\.\.(/|$|[?#])~', $decodedPath)) {
        throw new InvalidArgumentException('Chỉ chấp nhận đường dẫn nội bộ.');
    }

    return BASE_URL . '/' . ltrim($path, '/');
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function money(int $amount): string
{
    return number_format($amount, 0, ',', '.') . 'đ';
}

function redirect(string $path): void
{
    $status = ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' ? 303 : 302;
    header('Location: ' . url($path), true, $status);
    exit;
}

function current_user(): ?array
{
    global $pdo;

    $userId = $_SESSION['user_id'] ?? null;
    if (!is_scalar($userId) || !ctype_digit((string) $userId) || (int) $userId < 1) {
        unset($_SESSION['user_id']);
        return null;
    }

    // Doc lai DB de tai khoan vua bi khoa khong tiep tuc dung quyen cu.
    $statement = $pdo->prepare(
        'SELECT id, full_name, email, phone, role, status FROM users WHERE id = ?'
    );
    $statement->execute([(int) $userId]);
    $user = $statement->fetch();

    if (!$user || $user['status'] !== 'active') {
        unset($_SESSION['user_id']);
        return null;
    }

    return $user;
}

function require_login(): void
{
    if (current_user() === null) {
        flash('error', 'Vui lòng đăng nhập để tiếp tục.');
        redirect('auth.php');
    }
}

function require_admin(): void
{
    $user = current_user();
    if ($user === null) {
        flash('error', 'Vui lòng đăng nhập để tiếp tục.');
        redirect('auth.php');
    }

    if ($user['role'] !== 'admin') {
        http_response_code(403);
        exit('Bạn không có quyền truy cập trang này.');
    }
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        header('Allow: POST');
        http_response_code(405);
        exit('Yêu cầu phải sử dụng phương thức POST.');
    }

    $token = $_POST['csrf_token'] ?? null;
    $sessionToken = $_SESSION['csrf_token'] ?? null;
    if (!is_string($token) || !is_string($sessionToken)
        || $sessionToken === '' || !hash_equals($sessionToken, $token)) {
        http_response_code(403);
        exit('Phiên gửi biểu mẫu không hợp lệ. Vui lòng tải lại trang.');
    }
}

function flash(string $type, string $message): void
{
    if (!in_array($type, ['success', 'error'], true)) {
        throw new InvalidArgumentException('Thông báo chỉ có success hoặc error.');
    }
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

// Ham bo sung de header doc thong bao mot lan sau khi redirect.
function get_flashes(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}
