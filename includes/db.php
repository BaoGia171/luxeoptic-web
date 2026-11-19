<?php
// $config duoc doc trong bootstrap.php truoc khi nap file nay.
$dsn = 'mysql:host=' . $config['host']
    . ';port=' . $config['port']
    . ';dbname=' . $config['database']
    . ';charset=utf8mb4';

try {
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    // DATETIME defaults and NOW() must use the same timezone as PHP.
    $pdo->exec("SET time_zone = '+07:00'");
} catch (PDOException $exception) {
    // Chi ghi chi tiet vao log server, khong hien tai khoan DB ra trinh duyet.
    error_log('LuxeOptic database: ' . $exception->getMessage());
    http_response_code(503);
    if (defined('JSON_RESPONSE') && JSON_RESPONSE) {
        header('Content-Type: application/json; charset=utf-8');
        exit(json_encode(['error'=>'Không lấy được thông tin mẫu này']));
    }
    exit('Không kết nối được database. Hãy kiểm tra cấu hình và dịch vụ MySQL.');
}
