# Từ điển dữ liệu — LuxeOptic

Nguồn chuẩn: `database/schema.sql`. 6 bảng, InnoDB, utf8mb4_unicode_ci. Không thêm bảng giỏ hàng: giỏ lưu PHP Session. Tiền lưu **INT UNSIGNED**, không dùng DECIMAL.

## users

| Trường | Kiểu và ràng buộc SQL | Ý nghĩa |
|---|---|---|
| `id` | `INT UNSIGNED AUTO_INCREMENT PRIMARY KEY` | Khóa định danh tự tăng của bản ghi |
| `full_name` | `VARCHAR(100) NOT NULL` | Họ tên người dùng |
| `email` | `VARCHAR(150) NOT NULL UNIQUE` | Email đăng nhập, không trùng |
| `phone` | `VARCHAR(20) NOT NULL UNIQUE` | Số điện thoại chuẩn hóa dạng 0xxxxxxxxx, không trùng |
| `password_hash` | `VARCHAR(255) NOT NULL` | Hash mật khẩu do password_hash tạo; không lưu mật khẩu gốc |
| `role` | `ENUM('customer', 'admin') NOT NULL DEFAULT 'customer'` | Quyền customer hoặc admin |
| `status` | `ENUM('active', 'blocked') NOT NULL DEFAULT 'active'` | Trạng thái tài khoản hoặc đơn hàng (xem ENUM của bảng) |
| `created_at` | `DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP` | Thời điểm tạo |
| `updated_at` | `DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP` | Thời điểm cập nhật gần nhất |

## categories

| Trường | Kiểu và ràng buộc SQL | Ý nghĩa |
|---|---|---|
| `id` | `INT UNSIGNED AUTO_INCREMENT PRIMARY KEY` | Khóa định danh tự tăng của bản ghi |
| `name` | `VARCHAR(100) NOT NULL` | Tên danh mục hoặc tên mẫu kính |
| `slug` | `VARCHAR(120) NOT NULL UNIQUE` | Định danh URL duy nhất |
| `description` | `TEXT NULL` | Mô tả: sản phẩm lưu HTML đã lọc; danh mục là văn bản |
| `sort_order` | `INT NOT NULL DEFAULT 0` | Thứ tự tăng dần; ID dùng phân định khi bằng nhau |
| `is_active` | `TINYINT(1) NOT NULL DEFAULT 1` | 1 hiển thị/hoạt động; 0 ẩn |
| `created_at` | `DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP` | Thời điểm tạo |
| `updated_at` | `DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP` | Thời điểm cập nhật gần nhất |

## products

| Trường | Kiểu và ràng buộc SQL | Ý nghĩa |
|---|---|---|
| `id` | `INT UNSIGNED AUTO_INCREMENT PRIMARY KEY` | Khóa định danh tự tăng của bản ghi |
| `category_id` | `INT UNSIGNED NOT NULL` | Danh mục sở hữu sản phẩm; FK categories.id |
| `sku` | `VARCHAR(50) NOT NULL UNIQUE` | Mã sản phẩm duy nhất |
| `slug` | `VARCHAR(150) NOT NULL UNIQUE` | Định danh URL duy nhất |
| `name` | `VARCHAR(150) NOT NULL` | Tên danh mục hoặc tên mẫu kính |
| `price` | `INT UNSIGNED NOT NULL` | Giá bán hiện tại, số nguyên VND > 0 |
| `old_price` | `INT UNSIGNED NULL` | Giá tham chiếu cũ, NULL nếu không có; phải ≥ price |
| `stock` | `INT UNSIGNED NOT NULL DEFAULT 0` | Số lượng tồn kho không âm |
| `color` | `VARCHAR(100) NOT NULL DEFAULT ''` | Màu gọng |
| `material` | `VARCHAR(100) NOT NULL DEFAULT ''` | Chất liệu gọng |
| `description` | `TEXT NULL` | Mô tả: sản phẩm lưu HTML đã lọc; danh mục là văn bản |
| `is_active` | `TINYINT(1) NOT NULL DEFAULT 1` | 1 hiển thị/hoạt động; 0 ẩn |
| `is_featured` | `TINYINT(1) NOT NULL DEFAULT 0` | 1 chọn hiển thị ở nhóm nổi bật trang chủ |
| `created_at` | `DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP` | Thời điểm tạo |
| `updated_at` | `DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP` | Thời điểm cập nhật gần nhất |

Chỉ mục và khóa ngoại:

- `INDEX idx_products_category_active (category_id, is_active)`
- `INDEX idx_products_created (created_at)`
- `CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT`

## product_images

| Trường | Kiểu và ràng buộc SQL | Ý nghĩa |
|---|---|---|
| `id` | `INT UNSIGNED AUTO_INCREMENT PRIMARY KEY` | Khóa định danh tự tăng của bản ghi |
| `product_id` | `INT UNSIGNED NOT NULL` | FK products.id |
| `image_path` | `VARCHAR(255) NOT NULL` | Đường dẫn ảnh tương đối (assets/images hoặc uploads/products) |
| `alt_text` | `VARCHAR(150) NOT NULL DEFAULT ''` | Mô tả thay thế cho ảnh |
| `sort_order` | `INT NOT NULL DEFAULT 0` | Thứ tự tăng dần; ID dùng phân định khi bằng nhau |

Chỉ mục và khóa ngoại:

- `INDEX idx_product_images_sort (product_id, sort_order)`
- `CONSTRAINT fk_product_images_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE`

## orders

| Trường | Kiểu và ràng buộc SQL | Ý nghĩa |
|---|---|---|
| `id` | `INT UNSIGNED AUTO_INCREMENT PRIMARY KEY` | Khóa định danh tự tăng của bản ghi |
| `order_code` | `VARCHAR(30) NOT NULL UNIQUE` | Mã đơn LX- + ID đệm đủ tối thiểu 6 số |
| `user_id` | `INT UNSIGNED NOT NULL` | Chủ đơn, lấy ID từ Session; FK users.id |
| `recipient_name` | `VARCHAR(100) NOT NULL` | Tên người nhận tại thời điểm mua |
| `recipient_phone` | `VARCHAR(20) NOT NULL` | Số người nhận đã chuẩn hóa |
| `recipient_email` | `VARCHAR(150) NOT NULL` | Email nhận thông tin, bắt buộc |
| `province` | `VARCHAR(100) NOT NULL` | Tỉnh/thành người nhận nhập |
| `district` | `VARCHAR(100) NOT NULL` | Quận/huyện người nhận nhập |
| `address_detail` | `VARCHAR(255) NOT NULL` | Số nhà, đường, địa chỉ chi tiết |
| `note` | `TEXT NULL` | Ghi chú giao nhận, ứng dụng giới hạn 1000 ký tự |
| `payment_method` | `ENUM('cod', 'bank_transfer') NOT NULL` | cod hoặc bank_transfer (thủ công) |
| `payment_status` | `ENUM('unpaid', 'paid') NOT NULL DEFAULT 'unpaid'` | unpaid hoặc paid; không đảo paid về unpaid |
| `status` | `ENUM('pending', 'confirmed', 'shipping', 'completed', 'cancelled') NOT NULL DEFAULT 'pending'` | Trạng thái tài khoản hoặc đơn hàng (xem ENUM của bảng) |
| `subtotal` | `INT UNSIGNED NOT NULL` | Tổng giá dòng hàng trước giảm, VND |
| `discount_amount` | `INT UNSIGNED NOT NULL DEFAULT 0` | LUXE10: round(subtotal × 10%), không cộng dồn |
| `shipping_fee` | `INT UNSIGNED NOT NULL DEFAULT 0` | Phí giao hàng, phạm vi bài cố định 0 VND |
| `total_amount` | `INT UNSIGNED NOT NULL` | subtotal − discount_amount + shipping_fee |
| `voucher_code` | `VARCHAR(30) NULL` | Mã đã áp dụng; NULL khi không dùng |
| `created_at` | `DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP` | Thời điểm tạo |
| `updated_at` | `DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP` | Thời điểm cập nhật gần nhất |

Chỉ mục và khóa ngoại:

- `INDEX idx_orders_user_created (user_id, created_at)`
- `INDEX idx_orders_status_created (status, created_at)`
- `CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT`

## order_items

| Trường | Kiểu và ràng buộc SQL | Ý nghĩa |
|---|---|---|
| `id` | `INT UNSIGNED AUTO_INCREMENT PRIMARY KEY` | Khóa định danh tự tăng của bản ghi |
| `order_id` | `INT UNSIGNED NOT NULL` | FK orders.id |
| `product_id` | `INT UNSIGNED NOT NULL` | FK products.id |
| `product_name` | `VARCHAR(150) NOT NULL` | Snapshot tên sản phẩm khi mua |
| `product_sku` | `VARCHAR(50) NOT NULL` | Snapshot SKU khi mua |
| `product_image` | `VARCHAR(255) NOT NULL` | Snapshot đường dẫn ảnh khi mua; bắt buộc |
| `product_color` | `VARCHAR(100) NOT NULL DEFAULT ''` | Snapshot màu khi mua; không NULL |
| `unit_price` | `INT UNSIGNED NOT NULL` | Snapshot đơn giá VND khi mua |
| `quantity` | `INT UNSIGNED NOT NULL` | Số lượng mua, PHP kiểm tra 1–99 |
| `line_total` | `INT UNSIGNED NOT NULL` | unit_price × quantity, số nguyên VND |

Chỉ mục và khóa ngoại:

- `INDEX idx_order_items_order (order_id)`
- `CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE`
- `CONSTRAINT fk_order_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT`

## Ràng buộc ở ứng dụng

- Tài khoản: họ tên 2–100 ký tự, email hợp lệ; mật khẩu ≥ 8 ký tự và lưu hash.
- Sản phẩm: giá dương, giá cũ không nhỏ hơn giá mới, kho không âm; SKU/slug không trùng. Mỗi sản phẩm 1–5 ảnh, không xóa ảnh cuối đang hiển thị.
- Danh mục có sản phẩm không được xóa. Ẩn sản phẩm bằng is_active=0; giữ tham chiếu đơn cũ.
- Checkout khóa sản phẩm theo ID tăng dần, đọc lại giá/kho, ghi đơn + dòng hàng + trừ kho trong một transaction.
- Hủy đơn khóa dòng orders; chỉ pending/confirmed chưa thanh toán (khách chỉ pending). Hoàn kho một lần cùng transaction.
- Snapshot order_items giữ tên/SKU/màu/đơn giá và đường dẫn ảnh tại lúc mua; không dùng giá mới để tính lại đơn cũ. Đường dẫn là snapshot, không phải bản sao nhị phân của ảnh.
- Chỉ render HTML description sau HTML Purifier; mọi trường văn bản khác escape bằng e().
