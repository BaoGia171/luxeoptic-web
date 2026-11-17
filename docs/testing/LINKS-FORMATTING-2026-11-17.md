# Kiểm tra liên kết và định dạng mã nguồn — 17/11/2026

## Thay đổi
- Thay liên kết rỗng trong footer và trang đăng nhập/đăng ký bằng `information.php` với các mục giới thiệu, kích thước, điều khoản demo và dữ liệu tài khoản.
- Bỏ các liên kết dịch vụ chưa triển khai; thay khối nhận tin chưa hoạt động bằng liên kết mua sắm/tài khoản.
- Căn lề, tách dòng các file PHP/HTML và `commerce.css`; giữ nguyên nghiệp vụ.

## Kiểm chứng
- Toàn bộ PHP qua kiểm tra cú pháp.
- 81 kiểm thử nghiệp vụ mua hàng/quản trị: đạt.
- 76 kiểm thử giao diện/tương tác trên 375, 768, 1024, 1440 px: đạt.
- 38 kiểm thử tài khoản/phân quyền: đạt.
- Kiểm tra information.php, auth.php, index.php ở 375, 768, 1440 px: không tràn ngang; không còn liên kết href="#"; liên kết đến information.php và các anchor đều hợp lệ.
- Đã xem ảnh chụp trang thông tin trên desktop và mobile.

Kiểm thử chạy trên bản sao và database tạm, không thay dữ liệu của nhóm. Lượt UI đầu bị gián đoạn do thiếu ClassLoader.php trong bản sao thư viện vendor; lượt chạy lại từ bản sao mới đạt đủ 76/76.
