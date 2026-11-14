<?php
// Nap file nay truoc bat ky HTML nao. require_once de tranh nap hai lan.
date_default_timezone_set('Asia/Ho_Chi_Minh');

$configFile = __DIR__ . '/../config/database.php';
if (!is_file($configFile)) {
    http_response_code(503);
    exit('Chưa có cấu hình. Hãy sao chép config/database.example.php thành config/database.php và điền thông tin MySQL.');
}

$config = require $configFile;
define('BASE_URL', rtrim($config['base_url'], '/'));

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => BASE_URL === '' ? '/' : BASE_URL . '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
