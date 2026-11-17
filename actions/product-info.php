<?php
define('JSON_RESPONSE', true);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
try {
    require __DIR__ . '/../includes/shop.php';
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        header('Allow: GET');
        echo json_encode(['error' => 'Phương thức không được hỗ trợ']);
        exit();
    }
    $p = visible_product(positive_id($_GET['id'] ?? null));
    if (!$p) {
        http_response_code(404);
        echo json_encode(['error' => 'Không lấy được thông tin mẫu này']);
        exit();
    }
    echo json_encode([
        'id' => (int) $p['id'],
        'price' => (int) $p['price'],
        'stock' => (int) $p['stock'],
    ]);
} catch (Throwable $error) {
    error_log($error->getMessage());
    http_response_code(503);
    echo json_encode(['error' => 'Không lấy được thông tin mẫu này']);
}
