# TV4 — GIỎ HÀNG VÀ TẠO ĐƠN

## 1. Phạm vi và bản bàn giao

Phân hệ quản lý giỏ PHP Session, thêm/sửa/xóa sản phẩm, voucher LUXE10, thông tin giao nhận và tạo đơn. Khách chưa đăng nhập được dùng giỏ; đặt hàng bắt buộc đăng nhập. Chỉ có COD và chuyển khoản thủ công, không VietQR tự động hoặc cổng thanh toán thật.

Bản này hoàn thiện trên code nhánh feature/cart-checkout và được kiểm thử tích hợp theo yêu cầu TV1. Các lỗi form/hiển thị/ghép dữ liệu trong nhận xét đã được xử lý trong nhánh codex/complete-tv3-tv6. Đây là báo cáo sản phẩm hiện tại, không phải khẳng định TV4 tự viết toàn bộ phần bổ sung.

## 2. Chức năng đã triển khai

- Giỏ mặc định trống, badge và tổng tiền bằng 0; Session chỉ giữ product_id → quantity, không tin giá từ trình duyệt.
- Thêm cùng mẫu cộng dồn số lượng. Số lượng nguyên từ 1–99 và không vượt stock; kiểm tra trạng thái sản phẩm/danh mục. Sửa/xóa bằng POST có CSRF.
- cart.php đọc giá/ảnh/tên hiện tại từ DB, tính subtotal và total trên server. Header chung lấy tổng số lượng từ Session; đã gỡ cart-store.js và giỏ localStorage.
- LUXE10 không phân biệt hoa/thường, giảm round(subtotal × 0,10), không cộng dồn. shipping_fee = 0; total_amount = subtotal − discount_amount + shipping_fee.
- Form giao nhận ban đầu chỉ có placeholder, không điền sẵn thông tin người mẫu. Khi lỗi, giữ dữ liệu đã nhập và báo gần từng trường; ghi chú là textarea. Lựa chọn thanh toán có viền theo radio đang chọn.
- Khách chưa đăng nhập hoặc phiên hết hạn được chuyển auth.php?return=cart. Sau đặt thành công chuyển my-orders.php?id=ID vừa tạo.

## 3. Kiểm tra và transaction

Checkout xác minh POST, CSRF, tài khoản Session và checkout_token. Họ tên 2–100 ký tự, số điện thoại Việt Nam chuẩn hóa, email hợp lệ tối đa 150 ký tự; địa chỉ/tỉnh/quận bắt buộc; note tối đa 1000 ký tự. Không nhận user_id, giá hoặc tổng tiền từ client làm nguồn chính thức.

Trong transaction: sắp xếp ID sản phẩm tăng dần → SELECT FOR UPDATE → kiểm tra lại trạng thái/kho → tính tiền/voucher → INSERT orders → đặt order_code LX- + ID → INSERT order_items → trừ stock → COMMIT. Đơn mới pending/unpaid. order_items giữ tên/SKU/ảnh/màu/đơn giá lúc mua; thay giá trong catalog không làm đổi đơn cũ.

Chỉ sau COMMIT mới xóa giỏ, voucher và token. Nếu có lỗi thì ROLLBACK, giữ giỏ; lỗi DB chi tiết ghi log, khách chỉ nhận thông báo an toàn. Token đã dùng không thể tạo thêm đơn khi gửi lại/refresh. Khóa dòng bảo vệ trường hợp hai khách cùng mua phần tồn kho cuối.

## 4. File và sơ đồ bàn giao

cart.php; actions/cart-add.php, cart-update.php, cart-remove.php, cart-voucher.php, checkout.php; assets/js/page-cart.js. Tích hợp với header/footer và helper chung, không tạo schema hoặc hệ thống đơn riêng.

Activity Diagram: docs/design/04-checkout-activity.drawio và 04-checkout-activity.png. Sơ đồ thể hiện kiểm tra đăng nhập/form/tồn kho, transaction, nhánh rollback và kết thúc một lượt yêu cầu. Thất bại giữ giỏ để sửa và thử lại. Không cần thay Use Case tổng quát của TV2.

## 5. Kết quả kiểm thử thực tế

Đã chạy: giỏ mới bằng 0; cộng số lượng; từ chối vượt kho; áp dụng voucher; dữ liệu giao nhận lỗi được giữ và báo đúng trường; thay price/total_amount ở request không đổi giá server; ghi đơn và trừ kho đúng; gửi lại token không tạo đơn trùng; chỉ xóa giỏ sau thành công.

Đã tạo lỗi DB có kiểm soát trong database thử nghiệm để xác nhận không còn orders/order_items dở dang và kho không đổi khi rollback. Hai PHP server độc lập cùng mua sản phẩm còn 1 đơn vị: chỉ một đơn được tạo, stock cuối bằng 0.

Các kích thước 375, 768, 1024, 1440px đã được kiểm tra bằng trình duyệt; bảng giỏ có thể cuộn trong khung. Minh chứng và kết quả từng ca nằm tại docs/testing/TV3-TV6-2026-11-16.md, results.json và ảnh checkout đính kèm.

## 6. Demo và phạm vi còn lại

Theo README dựng môi trường, đăng ký tài khoản khách, thêm sản phẩm từ chi tiết, đổi số lượng, áp dụng LUXE10, nhập giao nhận và chọn phương thức. Đặt đơn rồi kiểm tra đơn trong lịch sử và Admin. Thử bỏ trống email hoặc vượt kho để minh họa kiểm tra server.

Phần tạo đơn được nối với quản lý đơn TV6; không có bộ dữ liệu đơn riêng. Chuyển khoản vẫn là demo thủ công, không chuyển tiền thật. Báo cáo ghi kết quả bản tích hợp; các thành viên cần tự chạy lại trên máy mình trước nghiệm thu nhóm và trình bày.
