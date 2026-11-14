<?php
// POST: csrf_token
// Xóa thông tin đăng nhập và đổi session ID; giỏ hàng/voucher của khách được giữ lại.
require_once __DIR__ . '/../includes/bootstrap.php';

verify_csrf();

unset($_SESSION['user_id'], $_SESSION['checkout_token'], $_SESSION['form_state'], $_SESSION['csrf_token']);
session_regenerate_id(true);

flash('success', 'Bạn đã đăng xuất.');
redirect('auth.php');
