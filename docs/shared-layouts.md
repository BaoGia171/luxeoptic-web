# Giao diện PHP dùng chung — bàn giao TV1

## Header và footer khách hàng

Trang phải nạp `includes/bootstrap.php` trước mọi HTML. Header và footer chỉ
chứa phần giao diện tương ứng; trang tự giữ `<head>`, CSS, JS và phần nội dung.
`index.php` đã lấy sản phẩm nổi bật từ database. Các trang thương mại mới dùng `includes/shop.php` để nạp cùng header/footer/sidebar; không có bộ dữ liệu JavaScript riêng.

```php
<?php
require_once __DIR__ . '/includes/bootstrap.php';
$activeNav = 'home';
// Header tự lấy tổng số lượng từ giỏ Session.
?>
<!-- Giữ doctype, head và thẻ body của trang -->
<?php require __DIR__ . '/includes/header.php'; ?>
<main><!-- Nội dung trang --></main>
<?php require __DIR__ . '/includes/footer.php'; ?>
<!-- Giữ script và thẻ đóng body/html của trang -->
```

TV3 xử lý tham số và truyền `$activeNav` theo bảng dưới. Partial không tự
lọc dữ liệu hay ghi đè tiêu đề; khi không truyền biến, không mục nào được chọn.

| Mục | URL PHP | activeNav |
|---|---|---|
| Trang chủ | index.php | home |
| Kính râm | products.php?category=kinh-ram | kinh-ram |
| Gọng kính cận | products.php?category=gong-kinh-can | gong-kinh-can |
| Chống ánh sáng xanh | products.php?category=chong-anh-sang-xanh | chong-anh-sang-xanh |
| Hàng mới | products.php?sort=newest | newest |

Các trang PHP đã được ghép trong nhánh codex/complete-tv3-tv6. Các file HTML mô phỏng, cart-store.js và products-data.js đã gỡ khỏi nhánh tích hợp.

## Sidebar Admin

TV2/TV5/TV6 giữ nội dung trang đang phụ trách, thay sidebar cũ bằng partial.
Phải kiểm tra quyền trước khi xuất HTML để chuyển hướng/HTTP status hoạt động.

```php
<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_admin();
$activeAdmin = 'products';
?>
<!-- Giữ head, admin.css và body.admin-body -->
<?php require __DIR__ . '/includes/sidebar.php'; ?>
<!-- Giữ .admin-main, nút admin-menu-toggle và admin.js -->
```

`$activeAdmin`: `products`, `categories`, `orders`, `users`.
Sidebar lấy tên từ tài khoản đăng nhập và escape trước khi hiển thị.
Đăng xuất gửi POST đến `actions/logout.php`, kèm `csrf_token`; TV2 xử lý action.
Không có số đơn hàng giả hoặc vai trò Staff/VIP/Super Admin trong sidebar chung.

Không sửa action đăng nhập, CRUD, bộ lọc, schema hoặc dữ liệu seed trong đợt bàn giao này.
