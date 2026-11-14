<?php
// Tạo tài khoản Admin từ dòng lệnh:  php scripts/create-admin.php
// Chỉ chạy bằng CLI; qua web trả 403 trước khi làm bất cứ việc gì.
// Hỏi tên, email, số điện thoại, mật khẩu tại máy và hash mật khẩu trước khi INSERT.
// Không đọc mật khẩu từ tham số dòng lệnh để nó không nằm lại trong lịch sử shell.

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Script này chỉ chạy bằng dòng lệnh.');
}

$configFile = __DIR__ . '/../config/database.php';
if (!is_file($configFile)) {
    fwrite(STDERR, "Chưa có config/database.php. Hãy sao chép config/database.example.php và điền thông tin MySQL.\n");
    exit(1);
}

// Dùng cùng kết nối PDO với website; không cần session nên không nạp bootstrap.php.
$config = require $configFile;
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/user-helpers.php';

function ask(string $label): string
{
    echo $label;
    $line = fgets(STDIN);
    return $line === false ? '' : trim($line);
}

// Nhập mật khẩu không hiện lên màn hình khi hệ điều hành cho phép.
function ask_hidden(string $label): string
{
    echo $label;

    if (DIRECTORY_SEPARATOR === '\\') {
        $output = shell_exec(
            'powershell -NoProfile -Command "$p = Read-Host -AsSecureString; '
            . '[Runtime.InteropServices.Marshal]::PtrToStringAuto('
            . '[Runtime.InteropServices.Marshal]::SecureStringToBSTR($p))"'
        );
        if (is_string($output)) {
            return rtrim($output, "\r\n");
        }
        echo "(Không ẩn được ký tự; mật khẩu sẽ hiện khi gõ.)\n";
        $line = fgets(STDIN);
        return $line === false ? '' : rtrim($line, "\r\n");
    }

    shell_exec('stty -echo 2>/dev/null');
    $line = fgets(STDIN);
    shell_exec('stty echo 2>/dev/null');
    echo "\n";
    return $line === false ? '' : rtrim($line, "\r\n");
}

echo "=== Tạo tài khoản Admin LuxeOptic ===\n";

$fullName = user_normalize_name(ask('Họ và tên: '));
$email = user_normalize_email(ask('Email: '));
$phoneRaw = ask('Số điện thoại: ');
$password = ask_hidden('Mật khẩu (tối thiểu 8 ký tự): ');
$confirm = ask_hidden('Nhập lại mật khẩu: ');

$errors = array_filter([
    user_check_name($fullName),
    user_check_email($email),
    user_check_phone($phoneRaw),
    user_check_password($password),
    $password !== $confirm ? 'Mật khẩu xác nhận không khớp.' : null,
]);
if ($errors) {
    fwrite(STDERR, "Không tạo được tài khoản:\n - " . implode("\n - ", $errors) . "\n");
    exit(1);
}

$phone = user_normalize_phone($phoneRaw);

$conflicts = user_find_conflicts($pdo, $email, $phone);
if ($conflicts) {
    fwrite(STDERR, "Không tạo được tài khoản:\n - " . implode("\n - ", $conflicts) . "\n");
    exit(1);
}

try {
    $pdo->prepare(
        'INSERT INTO users (full_name, email, phone, password_hash, role, status)
         VALUES (?, ?, ?, ?, ?, ?)'
    )->execute([$fullName, $email, $phone, password_hash($password, PASSWORD_DEFAULT), 'admin', 'active']);
} catch (PDOException $exception) {
    fwrite(STDERR, 'Lỗi database: ' . $exception->getMessage() . "\n");
    exit(1);
}

echo "Đã tạo Admin: {$fullName} <{$email}>. Đăng nhập tại trang auth.php.\n";
