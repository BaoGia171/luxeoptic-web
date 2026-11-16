# TV2 — Tài khoản và quyền truy cập (nhánh `feature/auth-users`)

## File của TV2
auth.php, profile.php, includes/user-helpers.php, actions/{register,login,logout,profile-update}.php,
admin/users.php, admin/actions/{user-save,user-status}.php, assets/js/{page-auth,page-admin-users}.js,
scripts/create-admin.php, docs/design/02-use-case.{drawio,png}.

## Chạy thử
1. Import `database/schema.sql`, chép `config/database.example.php` thành `config/database.php`.
2. Tạo Admin đầu tiên: `php scripts/create-admin.php` (hỏi tên, email, số điện thoại, mật khẩu; chỉ chạy CLI, qua web trả 403).
3. Mở `auth.php`: đăng ký (tạo customer), đăng nhập bằng email hoặc số điện thoại.
4. Admin vào `admin/users.php`: thêm/sửa, khóa/mở khóa.
5. Khách vào `profile.php`: sửa họ tên, số điện thoại.

## Việc cần PR riêng cho TV1 (file chung, TV2 không tự sửa)
- Gỡ mục "4. Chuyển Tab Đăng nhập / Đăng ký" khỏi `assets/js/main.js` (đã có trong page-auth.js).
- `includes/header.php`: biểu tượng tài khoản trỏ `profile.php` khi `current_user()` khác null (ngược lại `auth.php`) và có nút Đăng xuất dạng form POST kèm `csrf_token` tới `actions/logout.php`.
- Kiểm tra tên file `theme.css`, `ui-effects.css`, `theme.js`, `ui-effects.js` trong `auth.php`, `profile.php` khớp thư mục thật.

## Checklist mục 15 và minh chứng kiểm thử
Đợt đầu TV2 báo cáo 95/95 đạt trên PHP 8.3 + MariaDB 10.11; hiện chưa kèm bộ script/log gốc để tái hiện đủ 95 kiểm tra.
Đợt bổ sung ngày 16/11/2026 có **56/56 kiểm tra đạt trên PHP 8.2.12 + MySQL 8.0.46**,
gồm 38 kiểm tra HTTP/database và 18 kiểm tra trình duyệt ở 375/768/1440px.
Script, kết quả JSON, ảnh và giới hạn được ghi tại [docs/testing/TV2-2026-11-16.md](testing/TV2-2026-11-16.md).
Hai bộ số liệu khác nhau; không coi 56 kiểm tra bổ sung là tái hiện kết quả 95/95 cũ.
Đăng ký đúng customer (kể cả khi gửi role=admin); chặn trùng email/số, mật khẩu không khớp; đăng nhập email/số; sai mật khẩu báo chung;
khách vào Admin bị chuyển, customer bị 403, POST trực tiếp bị chặn; khóa có hiệu lực với phiên đang đăng nhập; không khóa mình,
không hạ/khóa Admin hoạt động cuối cùng; hồ sơ lưu đúng, chặn số trùng, thiếu CSRF, khách chưa đăng nhập, bỏ qua email/role/id gửi lên;
đơn cũ giữ nguyên người nhận; đăng nhập/đăng xuất đổi session ID; return chỉ nhận cart/home.
Đã thử các trường hợp trong bộ bổ sung trên MySQL 8.0.46. Chưa nghiệm thu toàn bộ tích hợp hoặc tải đồng thời; xem giới hạn trong biên bản bổ sung.

## Gợi ý đoạn báo cáo
Phần tài khoản dùng PHP session và PDO prepared statements. Mật khẩu băm bằng `password_hash`, kiểm tra bằng `password_verify`; session đổi ID sau đăng nhập/đăng xuất.
Mọi POST kiểm tra phương thức, quyền và CSRF trước khi ghi. `current_user()` đọc lại DB nên khóa tài khoản có hiệu lực ngay.
Đăng ký công khai luôn tạo customer. Admin không thể khóa chính mình hoặc khóa/hạ quyền Admin hoạt động cuối cùng (khóa dòng bằng `SELECT … FOR UPDATE`).
Admin đầu tiên tạo bằng `scripts/create-admin.php`. Sơ đồ Use Case: `docs/design/02-use-case.png`.
