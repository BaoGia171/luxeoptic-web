<?php
// Chay: php scripts/check-foundation.php. Khong them/sua/xoa du lieu.
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Chỉ chạy file kiểm tra này bằng dòng lệnh.');
}

require_once __DIR__ . '/../includes/bootstrap.php';

$tables = ['users', 'categories', 'products', 'product_images', 'orders', 'order_items'];
foreach ($tables as $table) {
    // Ten bang lay tu danh sach co dinh o tren, khong lay tu input.
    $pdo->query('SELECT 1 FROM `' . $table . '` LIMIT 1');
    echo 'OK: ' . $table . PHP_EOL;
}

echo 'Database: ' . $pdo->query('SELECT VERSION()')->fetchColumn() . PHP_EOL;
echo 'URL: ' . url('products.php') . PHP_EOL;
echo 'Tien: ' . money(1350000) . PHP_EOL;
echo 'Session: ' . (session_status() === PHP_SESSION_ACTIVE ? 'OK' : 'LOI') . PHP_EOL;
echo 'CSRF: ' . (strlen(csrf_token()) === 64 ? 'OK' : 'LOI') . PHP_EOL;
echo 'Da kiem tra ket noi va 6 bang. Day khong phai kiem thu toan bo website.' . PHP_EOL;
