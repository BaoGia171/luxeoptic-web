# TV3 — HIỂN THỊ VÀ TRA CỨU SẢN PHẨM

## 1. Phạm vi và bản bàn giao

Phân hệ cung cấp trang chủ, danh sách/chi tiết mắt kính, tìm kiếm, lọc, sắp xếp, phân trang, so sánh hai gọng và dữ liệu giá/tồn kho cho chatbot. Dữ liệu lấy từ MySQL theo schema chung; không còn dùng products-data.js làm nguồn tên, giá hoặc ảnh.

Bản này được hoàn thiện và kiểm thử tích hợp theo yêu cầu TV1, kế thừa seed của nhánh feature/catalog. Phần ghi nhận dưới đây mô tả sản phẩm bàn giao hiện tại, không đồng nghĩa TV3 tự viết toàn bộ phần bổ sung. Code nằm trên nhánh codex/complete-tv3-tv6; trạng thái merge xem trên Pull Request.

## 2. Chức năng đã triển khai

- Trang chủ index.php lấy các sản phẩm đang hiển thị và được đánh dấu nổi bật từ database. Ảnh, tên và nút chi tiết dùng cùng ID sản phẩm.
- products.php tìm theo tên hoặc SKU, kết hợp danh mục và khoảng giá. Danh sách chỉ lấy sản phẩm và danh mục đang hoạt động. Có 4 cách sắp xếp: mới nhất, giá tăng, giá giảm, tên A–Z; dùng thêm ID để thứ tự ổn định.
- Phân trang 12 mẫu/trang; giữ các tham số lọc trong liên kết; trang vượt giới hạn chuyển về trang cuối; danh sách rỗng có thông báo.
- Menu Kính râm, Gọng kính cận, Chống ánh sáng xanh dùng slug danh mục. Hàng mới dùng sort=newest; tiêu đề, mục đang chọn và kết quả tương ứng.
- product-detail.php kiểm tra ID số, trả 404 khi không tồn tại hoặc bị ẩn. Tên/giá/ảnh lấy từ DB; ảnh đầu theo sort_order, id giống danh sách; chọn thumbnail để đổi ảnh chính.
- compare.php nhận id1/id2 và dùng bộ giao diện so sánh chung. Chặn chọn hai mẫu trùng; có thể bỏ/thay mẫu, báo khi thiếu hoặc mẫu đã ẩn. Các cột đối chiếu tên, ảnh, giá, màu, chất liệu.
- actions/product-info.php nhận GET id, trả JSON id/price/stock mới nhất; lỗi 404/405/503 được xử lý. Chatbot chỉ trả lời FAQ hoặc tra giá/kho của mẫu đang xem, không dùng AI API.

## 3. Dữ liệu, truy vấn và an toàn

Dùng products, categories và product_images. Seed bàn giao 4 danh mục, 14 mẫu, mỗi mẫu stock 20 trừ một mẫu stock 0. Slug chuẩn hóa chữ thường, ảnh mẫu có sẵn trong repository. Seed chỉ chạy một lần trên database mới; không tạo đơn giả cho khách.

Các điều kiện SQL dùng Prepared Statements; ORDER BY lấy từ whitelist, không nối chuỗi sort tùy ý. Từ khóa % và _ được escape để tìm literal. Khoảng giá phải là số nguyên không âm và min_price không lớn hơn max_price. Văn bản xuất bằng e(); description được lọc bằng HTML Purifier trước render. Không hiển thị giá cũ khi tra sản phẩm thất bại.

Luồng dữ liệu: khách chọn tiêu chí → GET products.php → PHP kiểm tra tham số → PDO truy vấn MySQL → render HTML. So sánh dùng dữ liệu PHP xuất JSON an toàn cho renderer; chatbot gọi endpoint PHP mỗi lần hỏi giá/kho.

## 4. Tài liệu thiết kế

Sitemap ở docs/design/03-sitemap.drawio và 03-sitemap.png, có trang hồ sơ, so sánh, lịch sử và Admin; chatbot là khung nổi, không phải một trang riêng. Mũi tên chỉ đường điều hướng chính, không phải quan hệ khóa ngoại.

Từ điển dữ liệu docs/design/05-data-dictionary.md mô tả đủ 6 bảng theo schema.sql, từng trường, kiểu, NULL/default/unique, chỉ mục, FK và ý nghĩa. Tiền dùng INT UNSIGNED; không đổi schema để khớp báo cáo.

## 5. Kết quả kiểm thử thực tế

Đã thử trên PHP 8.2.12/MySQL 8.0.46: trang đầu có 12/14 mẫu, trang sau có 2; page vượt giới hạn; lọc từng danh mục; phối hợp danh mục–giá–sort khớp SQL; wildcard literal; sort không hợp lệ; chi tiết và JSON từ chối mẫu ẩn/ID sai. So sánh chọn/bỏ mẫu và chatbot giữ hai cặp hỏi–đáp đã chạy trên trình duyệt.

Đã kiểm tra 375, 768, 1024 và 1440px, không tràn ngang toàn trang trong các ca chạy. Bằng chứng chi tiết ở docs/testing/TV3-TV6-2026-11-16.md và commerce-2026-11-16/results.json; ảnh desktop/mobile và sơ đồ được đính kèm phần minh chứng.

## 6. Hướng dẫn demo và giới hạn

Import schema rồi seed, cài Composer, cấu hình DB theo README. Mở products.php, bấm từng mục menu, tìm SKU, nhập khoảng giá và đổi sort; chọn hai mẫu để so sánh. Mở chi tiết, hỏi chatbot giá/kho, đổi dữ liệu ở Admin rồi hỏi lại để thấy dữ liệu mới.

Phạm vi đã hoàn thiện là chức năng catalog theo phân công. Không thêm thử kính bằng camera hoặc AI tư vấn. GitHub Pages cũ chỉ là demo tĩnh; bản PHP cần môi trường PHP/MySQL. Chưa xác nhận MariaDB. Thành viên cần tự chạy và nắm luồng truy vấn để thuyết trình.
