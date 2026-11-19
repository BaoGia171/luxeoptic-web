# LuxeOptic — Website bán mắt kính (Nhóm 10)

PHP 8.2 + PDO/MySQL 8.0 hoặc MariaDB 10.4.32, HTML/CSS/JavaScript thuần. Bản tích hợp hoàn thiện TV3–TV6 dùng chung nền tảng TV1 và tài khoản TV2. Không dùng Tailwind hoặc framework frontend/backend.

## Cài và chạy

1. Cài PHP 8.2/Apache (có thể dùng XAMPP), **MySQL 8.0 hoặc MariaDB 10.4.32 đi kèm XAMPP**, Composer. Kiểm tra `php -v`; trên Windows có thể dùng `C:\xampp\php\php.exe`. Bật `pdo_mysql`, `mbstring`, `fileinfo`, `dom` trong php.ini của cả CLI và Apache. Khởi động lại Apache khi đổi php.ini.
2. Tại thư mục dự án chạy `composer install` để lấy **HTML Purifier 4.19.1** đúng composer.lock. Không tự chạy composer update. `vendor/` không commit Git nhưng **phải có trong ZIP nộp bài**.
3. Trên database mới: import `database/schema.sql`, chọn database `luxeoptic`, rồi import `database/seed.sql`. Seed chạy **một lần** trên schema rỗng: 4 danh mục, 14 sản phẩm, ảnh đã commit; một mẫu hết hàng. Không chạy lại seed trên dữ liệu đang dùng.
4. Tạo tài khoản MySQL riêng cho ứng dụng với quyền SELECT, INSERT, UPDATE, DELETE trên `luxeoptic.*`. Dùng tài khoản quản trị DB chỉ để import/cấp quyền, không ghi mật khẩu thật vào repo.
5. Copy `config/database.example.php` thành `config/database.php`; điền host/port/database/username/password. `base_url` là đường dẫn URL: `/luxeoptic` nếu mở `http://localhost/luxeoptic/`, hoặc chuỗi rỗng khi website nằm tại DocumentRoot. Không điền đường dẫn ổ đĩa.
6. Chạy `php scripts/create-admin.php` tại terminal, nhập thông tin Admin theo lời nhắc. Không có tài khoản/mật khẩu mặc định. Khách tạo tài khoản bằng `auth.php`.
7. Apache phải được phép đọc dự án, ghi `uploads/products/` và áp dụng `.htaccess` (cấu hình phía dưới). Mở `index.php`; Admin đăng nhập qua `auth.php` rồi vào `admin/users.php` và điều hướng bằng sidebar chung.

Ví dụ lệnh import (nhập mật khẩu tại lời nhắc, không đặt mật khẩu trong lệnh):

```sh
mysql -u root -p < database/schema.sql
mysql -u root -p luxeoptic < database/seed.sql
```

Nếu chạy PHP built-in để thử nhanh: `php -S 127.0.0.1:8080 -t .`, đặt base_url rỗng. **Built-in server không thực thi .htaccess**; kiểm thử bảo vệ upload phải chạy Apache.

## Apache và upload

Áp dụng vào cấu hình Apache/VirtualHost cho đúng thư mục cài (thay đường dẫn bằng máy của bạn), rồi kiểm tra cấu hình và khởi động lại Apache:

```apache
<Directory "D:/BTLWEB1-complete">
    Require all granted
    AllowOverride All
</Directory>
DirectoryIndex index.php
```

Giữ `uploads/products/.htaccess` trong bản clone/ZIP. Quy tắc chỉ cho phục vụ tên ảnh ngẫu nhiên 32 ký tự hex + jpg/png/webp, chặn các tên còn lại; vô hiệu handler script cho ảnh. Apache cần các module mime, authz_core và rewrite. Không chỉ dựa vào việc đổi đuôi file. Đặt `upload_max_filesize = 2M`, `post_max_size = 12M`, `max_file_uploads = 5` hoặc lớn hơn; ứng dụng vẫn kiểm tra lại 2MB/ảnh và tổng tối đa 5 ảnh kể cả ảnh cũ.

Đã kiểm tra trên Apache của XAMPP: PHP hoạt động bên ngoài uploads; probe.php/probe.phtml/probe.php.png trong uploads nhận 403, không thực thi/không lộ source; ảnh PNG hợp lệ nhận 200. Script thử chỉ tạo fixture trong thư mục tạm và xóa file probe sau thử.

## Thư viện

- Quill **2.0.3**, lưu cục bộ tại `assets/vendor/quill/`, giấy phép BSD-3-Clause trong `LICENSE`; chỉ bật đậm/nghiêng/danh sách/liên kết. Không cần npm để chạy website.
- HTML Purifier **4.19.1**, LGPL, cài bằng Composer. Chỉ giữ p, br, strong, em, ul, ol, li và a[href http/https tuyệt đối]. Server lọc cả mô tả cũ trước render.
- Font Google là tài nguyên ngoài; nếu không có mạng thì dùng font dự phòng. Chức năng PHP và editor không cần CDN JavaScript.

## Phần được hoàn thiện

- TV3: catalog DB, tìm tên/SKU, lọc danh mục/khoảng giá, 4 kiểu sắp xếp, 12 mẫu/trang; chi tiết đúng ID, so sánh 2 mẫu và dữ liệu giá/kho cho chatbot.
- TV4: Session cart, sửa/xóa/cộng số lượng, voucher LUXE10, giao nhận và COD/chuyển khoản thủ công; transaction đặt hàng, snapshot và chống gửi lặp.
- TV5: Admin CRUD sản phẩm/danh mục, ẩn sản phẩm, upload 1–5 ảnh, Quill + lọc HTML phía server. Các danh sách Admin 10 dòng/trang.
- TV6: lịch sử/chi tiết đúng chủ đơn, Admin quản lý đơn, chuyển trạng thái/thanh toán, hủy hợp lệ và hoàn kho đúng một lần.

Các trang HTML mô phỏng và JS dữ liệu/giỏ localStorage đã gỡ khỏi nhánh tích hợp để tránh chạy nhầm. Bản GitHub Pages cũ chỉ là demo giao diện; **không chạy được PHP/MySQL** và không đại diện bản tích hợp này. Chuyển khoản là demo thủ công, không chuyển tiền thật; không AI API, OAuth hoặc thanh toán online thật.

## Kiểm thử và tài liệu

- `scripts/test-commerce.py`: tạo MySQL/database và bản sao ứng dụng dùng riêng, không kết nối DB hiện tại; HTTP tests, transaction/race/rollback, tùy chọn Playwright kiểm tra giao diện.
- `scripts/test-tv2.py`: kiểm tra lại chức năng tài khoản/hồ sơ/phân quyền sau ghép.
- `scripts/test-upload-apache.py`: Apache cô lập kiểm tra quy tắc upload.
- Kết quả, môi trường và ảnh: `docs/testing/TV3-TV6-2026-11-16.md`.
- Sơ đồ chỉnh sửa bằng draw.io và ảnh: `docs/design/03-sitemap.*`, `04-checkout-activity.*`, `06-order-state.*`. Từ điển đủ 6 bảng: `05-data-dictionary.md`.
- Báo cáo nội dung từng phần: `docs/reports/TV3.md` đến `TV6.md` (được đồng bộ vào đúng thẻ Google Docs).

Ví dụ chạy (thay đường dẫn executable/package của máy):

```sh
python scripts/test-commerce.py --php C:/xampp/php/php.exe --mysqld C:/mysql/bin/mysqld.exe
python scripts/test-tv2.py --php C:/xampp/php/php.exe --mysqld C:/mysql/bin/mysqld.exe
python scripts/test-upload-apache.py --xampp C:/xampp
```

Thêm `--playwright <đường-dẫn-package-playwright> --browser <đường-dẫn-chrome>` để kiểm tra UI. Chỉ công cụ kiểm thử cần Python/Node/Playwright, website không phụ thuộc chúng. Đã kiểm thử MySQL 8.0.46 và MariaDB 10.4.32 của XAMPP. Trên MariaDB đạt 81 ca nghiệp vụ, 76 ca UI và 38 ca tài khoản; xem `docs/testing/MARIADB-2026-11-17.md`.

## Bản nộp

Chạy composer install theo lock; ZIP kèm vendor/, assets/vendor/quill/, uploads/products/.htaccess, schema/seed, ảnh mẫu, config mẫu và README. Không kèm config/database.php cá nhân, mật khẩu, dữ liệu khách thật hoặc thư mục .git. Làm theo README trên thư mục/máy mới trước khi nộp. Các thành viên cần tự chạy và nắm phần phụ trách để demo/thuyết trình; không xem việc có source là thay thế cho việc hiểu code.
