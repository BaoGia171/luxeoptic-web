# TV6 — QUẢN LÝ ĐƠN HÀNG VÀ LỊCH SỬ MUA

## 1. Phạm vi và bản bàn giao

Phân hệ đọc đơn thật do checkout TV4 tạo, cho khách xem đơn của mình và Admin xem/cập nhật đơn toàn hệ thống. Không dùng dữ liệu GHTK, vận chuyển thời gian thực hoặc trạng thái mô phỏng.

Code, sơ đồ và minh chứng dưới đây được hoàn thiện theo yêu cầu TV1 trên nhánh codex/complete-tv3-tv6. Báo cáo đã chuyển từ mô tả dự kiến sang kết quả bản tích hợp; không mặc định đây là toàn bộ công việc TV6 tự viết trước đó.

## 2. Danh sách, chi tiết và quyền truy cập

my-orders.php yêu cầu đăng nhập, lọc user_id từ Session, phân trang 10 dòng và đọc chi tiết theo ID kèm điều kiện chủ sở hữu. Đổi ID sang đơn người khác nhận 404; không tin user_id gửi từ trình duyệt. Chưa có đơn hiển thị trạng thái rỗng và liên kết mua sắm.

admin/orders.php yêu cầu Admin, tìm theo mã đơn/tên/số điện thoại, lọc trạng thái và phân trang 10 dòng; xem chi tiết và gửi form cập nhật. Action bắt buộc POST, quyền và CSRF; JS chỉ xác nhận thao tác, không quyết định quyền thay server.

Chi tiết dùng orders cho giao nhận/tổng tiền và order_items cho tên, SKU, ảnh, màu, đơn giá, số lượng tại lúc mua. Sửa tên/giá sản phẩm hoặc hồ sơ sau đó không viết lại đơn cũ.

## 3. Quy tắc trạng thái và thanh toán

- pending → confirmed: Admin xác nhận đơn.
- confirmed → shipping: Admin bắt đầu giao; bank_transfer phải paid, COD được giao khi chưa thu tiền.
- shipping → completed: Admin hoàn tất; COD đồng thời chuyển payment_status sang paid trong cùng transaction.
- pending → cancelled: khách sở hữu hoặc Admin, chỉ khi unpaid.
- confirmed → cancelled: chỉ Admin, chỉ khi unpaid.
- completed và cancelled là trạng thái kết thúc; không mở lại, không bỏ qua/quay ngược trạng thái.
- Admin xác nhận tiền thủ công: unpaid → paid khi đơn chưa kết thúc. Không paid → unpaid và không hủy đơn đã paid; không có luồng hoàn tiền thật trong phạm vi bài.

Cùng một hàm change_order xử lý cả hủy của khách và cập nhật của Admin. Mỗi thao tác BEGIN → SELECT orders FOR UPDATE → kiểm tra trạng thái mới nhất và quyền → cập nhật/hoàn kho → COMMIT. Các dòng sản phẩm được cập nhật theo thứ tự ID. Nếu lỗi, rollback toàn bộ.

Khóa cùng bản ghi orders giúp hai yêu cầu hủy chỉ hoàn kho một lần, hoặc hủy chạy đồng thời với xác nhận paid không tạo đơn cancelled nhưng paid. Không chỉ kiểm tra trạng thái trước transaction rồi ghi mù.

## 4. Dữ liệu và file bàn giao

my-orders.php, admin/orders.php, actions/order-cancel.php, admin/actions/order-status.php, includes/orders.php; page-my-orders.js và page-admin-orders.js. Dùng helpers, Session và sidebar chung, không tạo schema riêng.

Các trường tiền subtotal, discount_amount, shipping_fee, total_amount, unit_price, line_total là INT UNSIGNED. recipient_email là VARCHAR(150) NOT NULL; product_image VARCHAR(255) NOT NULL; product_color VARCHAR(100) NOT NULL DEFAULT ''. Từ điển đủ trường và FK nằm trong docs/design/05-data-dictionary.md.

Sơ đồ trạng thái đã bàn giao docs/design/06-order-state.drawio và 06-order-state.png. Sơ đồ thể hiện tác nhân, điều kiện thanh toán/hủy, hoàn kho khi vào cancelled và các trạng thái cuối; không thay Use Case của TV2.

## 5. Kết quả kiểm thử thực tế

Đã kiểm tra đọc đơn thật sau checkout; chủ khác không xem/hủy được đơn; đơn cũ giữ tên/giá snapshot; không nhảy trạng thái; COD hoàn tất đồng thời paid; bank unpaid không giao, paid được giao; paid không hủy; đơn kết thúc không mở lại.

Hủy lần hai không cộng kho. Hai tiến trình PHP hủy đồng thời cho kết quả một lần thành công, một lần từ chối và chỉ hoàn kho một lần. Ba lần chạy hủy đua với xác nhận thanh toán cho kết quả hợp lệ: cancelled/unpaid có hoàn kho, hoặc pending/paid không hoàn kho.

Đã ép lỗi DB lúc hoàn kho để xác nhận status và stock cùng rollback. Tất cả action Admin đã thử sai phương thức, sai quyền và CSRF. UI trên 375, 768, 1024, 1440px không tràn ngang toàn trang trong ca chạy; bảng cuộn trong khung. Kết quả chi tiết và ảnh ở docs/testing/TV3-TV6-2026-11-16.md và commerce-2026-11-16/results.json.

## 6. Luồng demo và giới hạn

Khách tạo đơn qua cart.php rồi mở lịch sử; Admin mở đúng đơn đó, xác nhận → giao → hoàn tất; khách tải lại để thấy trạng thái mới. Với đơn mới unpaid, minh họa hủy và kho được hoàn; với chuyển khoản, thử giao trước khi xác nhận paid để thấy server từ chối.

Thanh toán và vận chuyển vẫn theo phạm vi học tập: Admin xác nhận thủ công, không kết nối ngân hàng/đơn vị giao hàng. Chưa kiểm chứng tải lớn hoặc hosting production. Thành viên cần tự chạy và giải thích transaction, khóa dòng và điều kiện hủy trước nghiệm thu nhóm.
