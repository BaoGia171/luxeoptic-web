# TV5 — QUẢN TRỊ SẢN PHẨM, DANH MỤC VÀ ẢNH

## 1. Phạm vi và bản bàn giao

Phân hệ cho Admin quản lý danh mục, sản phẩm, trạng thái hiển thị, nhiều ảnh và mô tả có định dạng. Catalog của TV3 đọc cùng dữ liệu; không tạo danh sách sản phẩm hoặc database riêng.

Phần chức năng và minh chứng dưới đây được hoàn thiện theo yêu cầu TV1 trên nhánh codex/complete-tv3-tv6. Đây là báo cáo bản tích hợp đã triển khai, thay cho tình trạng chỉ có README trước đó; không ghi nhận rằng TV5 tự thực hiện toàn bộ code bổ sung.

## 2. Chức năng đã triển khai

- admin/categories.php liệt kê, thêm và sửa danh mục: tên, slug, mô tả, sort_order, is_active. Slug duy nhất; danh mục đang chứa sản phẩm không được xóa.
- admin/products.php tìm theo tên/SKU, phân trang 10 dòng, thêm/sửa sản phẩm với giá bán/giá cũ, kho, màu, chất liệu, danh mục, mô tả, trạng thái và đánh dấu nổi bật.
- Ẩn sản phẩm bằng is_active=0, không xóa lịch sử đơn. Catalog và endpoint chatbot chỉ trả sản phẩm/danh mục đang hoạt động.
- SKU/slug trùng bị từ chối; giá bán phải dương, giá cũ không nhỏ hơn giá bán, kho không âm, ID danh mục phải tồn tại. Form giữ dữ liệu hợp lệ và hiển thị lỗi theo trường.
- Mỗi sản phẩm 1–5 ảnh, ảnh đầu theo sort_order/id làm đại diện. Form sửa tính cả ảnh cũ trong giới hạn. Không xóa ảnh cuối khi sản phẩm đang hiển thị.

## 3. Upload và tính nhất quán

Server kiểm tra UPLOAD_ERR, is_uploaded_file, dung lượng không quá 2MB, MIME qua finfo và nội dung qua getimagesize. Chỉ JPEG, PNG, WebP; không tin tên file hoặc Content-Type từ trình duyệt. Tên mới là 32 ký tự hex từ random_bytes và đuôi theo MIME.

Các file chỉ lưu uploads/products/. Transaction ghi sản phẩm và product_images; nếu thao tác DB thất bại thì rollback và dọn file mới đã chuyển. Khi xóa ảnh, chỉ được đụng đường dẫn upload đúng mẫu; không xóa assets/images dùng chung. Ảnh upload còn được order_items tham chiếu được giữ để lịch sử đơn không mất ảnh.

uploads/products/.htaccess đã được commit; cấu hình Apache phải cho phép áp dụng file này. Quy tắc chặn tên file ngoài mẫu ảnh và vô hiệu script handler đối với ảnh. Đã thử trên Apache có PHP thật: script chạy ngoài uploads, nhưng probe.php/probe.phtml/probe.php.png bên trong nhận 403, không chạy/không lộ source; ảnh hợp lệ vẫn tải được. File probe thử nghiệm đã dọn.

## 4. Editor và lọc HTML

Dùng Quill 2.0.3 cục bộ tại assets/vendor/quill/, kèm LICENSE BSD-3-Clause; toolbar đậm, nghiêng, danh sách, liên kết. Khi submit, getSemanticHTML xuất HTML đúng ul/ol/li, không phụ thuộc class của Quill.

Server sử dụng HTML Purifier 4.19.1 cài bằng Composer theo composer.lock. Chỉ giữ p, br, strong, em, ul, ol, li và a. Chỉ href tuyệt đối http/https được giữ; bỏ style/class/id/on*, script, iframe, ảnh và URL nguy hiểm. Nội dung cũ cũng được lọc trước khi render. Các trường văn bản khác dùng e().

composer.json/composer.lock được commit; vendor/ không đưa lên Git nhưng phải kèm ZIP nộp. README ghi extensions, cách composer install, Apache/upload và các thư viện; không cần Node/npm để chạy editor.

## 5. File và dữ liệu sử dụng

admin/products.php, admin/categories.php; các action product-save/delete/image-delete và category-save/delete; includes/admin-commerce.php, includes/product-content.php; page-admin-products.js, page-admin-categories.js; assets/vendor/quill/; uploads/products/.htaccess.

Dùng categories, products, product_images và kiểm tra tham chiếu order_items khi dọn ảnh. Từ điển dữ liệu 05-data-dictionary.md đã đối chiếu với schema chung. TV5 không cần vẽ thêm ERD hoặc sơ đồ mới ngoài phân công.

## 6. Kết quả kiểm thử và demo

Đã chạy HTTP upload thật: tạo sản phẩm với hai ảnh, sửa, từ chối giá âm, ảnh giả, ảnh quá dung lượng, tổng ảnh vượt 5, không xóa ảnh cuối; từ chối slug danh mục trùng và xóa danh mục có sản phẩm. Các action chặn GET, customer gửi POST trực tiếp và CSRF sai.

Đã gửi HTML độc hại trực tiếp để xác nhận server lọc lại; Quill lưu → mở sửa → trang chi tiết giữ đậm/nghiêng/danh sách/liên kết. Đã ép DB lỗi ở bước thêm ảnh để xác nhận không còn sản phẩm/file mới dở dang. UI kiểm tra 375/768/1024/1440px, sidebar mobile và chế độ tối.

Minh chứng nằm tại docs/testing/TV3-TV6-2026-11-16.md, results.json, apache-upload.json và ảnh Admin/editor. Khi demo: đăng nhập Admin → tạo danh mục → thêm mẫu với 2 ảnh và mô tả → mở catalog/chi tiết → sửa giá → thử ẩn sản phẩm và thử file không hợp lệ.

Phạm vi chỉ quản trị sản phẩm theo phân công; không mở rộng phân quyền Staff/VIP hoặc trình soạn thảo nhúng video. Các thành viên cần hiểu quyền, upload và transaction để tự trình bày, không chỉ dựa vào ảnh báo cáo.
