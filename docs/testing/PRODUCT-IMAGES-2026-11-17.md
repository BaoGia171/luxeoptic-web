# Cập nhật ảnh sản phẩm — 17/11/2026

## Phạm vi

Đã kiểm tra 13 file ảnh được seed tham chiếu cho 14 sản phẩm. Bốn file chứa giao diện/chữ bên ngoài ảnh sản phẩm được thay bằng bản chỉnh qua công cụ image_gen tích hợp:

| File | Sản phẩm | Xử lý |
|---|---|---|
| assets/images/cat-eye.jpg | Cat-Eye Havana Acetate | Bỏ dòng breadcrumb trên ảnh |
| assets/images/hexagon-silver.jpg | Hexagon Ultra Thin Titanium | Bỏ menu, sidebar và tên/giá của trang mẫu |
| assets/images/pilot-gold.jpg | Pilot Master 18K Gold | Bỏ giao diện quản trị và chữ mẫu |
| assets/images/img33.jpg | Clubmaster Heritage Brown | Bỏ header, mô tả và giá dưới ảnh |

Đây là ảnh minh họa cho đồ án, được AI tái tạo từ ảnh tham chiếu; không phải ảnh xác thực sản phẩm thương mại. Đã đối chiếu dáng/màu tổng thể, nhưng không tuyên bố bảo toàn từng chi tiết chế tác. Ảnh JPEG 1448 × 1086, khoảng 123–280 KiB/file. Ảnh sạch còn lại giữ nguyên.

## Đồng bộ

Giữ nguyên đường dẫn: seed, database đã import, danh sách, chi tiết, so sánh, giỏ/đơn và Admin tiếp tục dùng cùng file. Không import lại seed, không thay tên/giá/SKU hay dữ liệu đơn. Bản ZIP nộp được cập nhật ảnh và ghi chú này. Bản gốc sao lưu ngoài repository tại D:/LuxeOptic-image-backup-2026-11-17.

Minh chứng mới nằm trong product-images-2026-11-17/. Các ảnh kiểm thử ngày 16/11 là bằng chứng lịch sử, không ghi đè. Nếu thay minh chứng trong slide, dùng ảnh mới; ảnh đã chèn vào Canva/Google Docs không tự đổi theo file nguồn. Các checkout frontend cũ chưa tự đồng bộ theo nhánh tích hợp.

## Kiểm tra

Database MySQL tạm mới, seed nguyên bản 14 sản phẩm. Kiểm tra trang chủ, danh sách, chi tiết 4 mẫu, so sánh Hexagon/Pilot ở 375 và 1440px (14 lượt). Tất cả ảnh thay thế tải thành công đúng kích thước mới; không tràn ngang toàn trang. Đã xem trực quan danh sách mobile và chi tiết desktop. Xem image-checks.json và ảnh đi kèm. Đây là kiểm tra ảnh, không chạy lại hoặc thay kết luận của 200 ca nghiệp vụ trước.

## Prompt chỉnh ảnh đã dùng

1. Cat-Eye: Edit this exact eyewear product photograph for a student eyewear storefront. Remove the entire top website breadcrumb/header strip and all text. Preserve the same Havana tortoiseshell acetate cat-eye clear-lens glasses, their exact shape, colors, angle, arms, stone slab and soft daylight. Fill the removed strip naturally with the photograph background. Clean product photo only, no text, no UI, no borders, no watermark. Landscape 4:3 composition, entire glasses comfortably inside frame. Do not redesign the glasses.
2. Hexagon: Edit target is this product screenshot. Extract/reconstruct ONLY its silver thin titanium hexagonal clear-lens eyeglasses resting on white marble, same exact goggle frame shape and silver color, arms and three-quarter view. Remove ALL website UI, header, sidebar, buttons, labels, prices and text. Expand the existing marble photo to fill a landscape 4:3 clean product photograph, soft neutral natural light, full glasses comfortably visible. Do not change product design. No text, no watermark, no interface.
3. Pilot: Edit target screenshot: keep ONLY the actual photo of the gold aviator sunglasses with dark green lenses on pale travertine stone. Preserve same aviator shape, bridge, thin engraved gold frame and black temple tips, three-quarter angle. Remove every website/admin header, sidebar, text, labels and price. Extend the neutral stone and softly blurred warm gray background to fill a landscape 4:3 clean high-resolution product photograph. Full glasses comfortably visible, matching original photo lighting and product, not a redesign. Absolutely no text or UI.
4. Clubmaster: Edit this screenshot into ONLY its clean product photograph. Preserve the brown/dark tortoiseshell browline Clubmaster eyeglasses with gold lower rims, clear lenses, folded temples and exact original frame silhouette resting on the warm dark wooden desk. Keep an open cream notebook at upper right but no readable writing. Remove all webpage UI, logo, menu, title, paragraph and price. Expand the photograph background naturally into landscape 4:3. Glasses clearly prominent and completely within frame; tasteful soft daylight. No text, UI, captions, border, watermark. Do not redesign product.
