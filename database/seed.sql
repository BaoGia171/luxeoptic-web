-- LUXEOPTIC SEED DATA

SET NAMES utf8mb4;

INSERT INTO categories
(name, slug, description, sort_order, is_active)
VALUES
('Kính râm', 'kinh-ram', 'Các mẫu kính râm thời trang', 1, 1),
('Gọng kính cận', 'gong-kinh-can', 'Các mẫu gọng kính cận cao cấp', 2, 1),
('Chống ánh sáng xanh', 'chong-anh-sang-xanh', 'Kính bảo vệ mắt khi sử dụng thiết bị số', 3, 1),
('Phiên bản giới hạn', 'phien-ban-gioi-han', 'Các mẫu kính đặc biệt giới hạn', 4, 1);


INSERT INTO products
(category_id, sku, slug, name, price, old_price, stock, color, material, description, is_active, is_featured)
VALUES

(3, 'LX-BB-001', 'round-blue-blocker-silver',
'Round Blue-Blocker Silver',
850000, NULL, 20,
'Bạc', 'Titanium',
'<p>Kính chống ánh sáng xanh cao cấp.</p>',
1,1),

(2, 'LX-CE-002', 'cat-eye-havana-acetate',
'Cat-Eye Havana Acetate',
980000,1150000,20,
'Havana','Acetate',
'<p>Gọng kính phong cách cổ điển.</p>',
1,1),

(2, 'LX-TM-003','titanium-minimalist-noir',
'Titanium Minimalist Noir',
1150000,NULL,20,
'Đen','Titanium',
'<p>Thiết kế tối giản cao cấp.</p>',
1,0),

(4,'LX-AT-AV01','LX-AT-AV01',
'Aviator Titanium Gold 01',
1350000,1650000,20,
'Vàng','Titanium',
'<p>Mẫu kính phi công sang trọng.</p>',
1,1),

(4,'LX-BG-005','butterfly-glamour-acetate',
'Butterfly Glamour Acetate',
1050000,1300000,20,
'Nâu','Acetate',
'<p>Thiết kế butterfly nổi bật.</p>',
1,0),

(2,'LX-BR-006','bold-rimless-horizon',
'Bold Rimless Horizon',
1800000,NULL,20,
'Trong suốt','Titanium',
'<p>Gọng không viền hiện đại.</p>',
1,0),

(1,'LX-WC-007','wayfarer-classic-polarized',
'Wayfarer Classic Polarized',
920000,1150000,20,
'Đen','Acetate',
'<p>Mẫu kính phân cực cổ điển.</p>',
1,0),

(2,'LX-HT-008','hexagon-ultra-thin-titanium',
'Hexagon Ultra Thin Titanium',
1450000,NULL,20,
'Bạc','Titanium',
'<p>Gọng kính titanium siêu nhẹ.</p>',
1,0),

(4,'LX-PM-009','pilot-master-18k-gold',
'Pilot Master 18K Gold',
2850000,NULL,20,
'Vàng','18K Gold',
'<p>Thiết kế cao cấp phiên bản giới hạn.</p>',
1,0),

(3,'LX-OB-010','oval-blue-blocker-pure',
'Oval Blue Blocker Pure',
790000,NULL,20,
'Trắng','Acetate',
'<p>Kính bảo vệ mắt chống ánh sáng xanh.</p>',
1,0),

(1,'LX-CH-011','clubmaster-heritage-brown',
'Clubmaster Heritage Brown',
1350000,1600000,20,
'Nâu','Acetate',
'<p>Phong cách vintage sang trọng.</p>',
1,0),

(1,'LX-GW-012','geometric-wireframe-bronze',
'Geometric Wireframe Bronze',
1100000,NULL,0,
'Đồng','Kim loại',
'<p>Thiết kế wireframe độc đáo.</p>',
1,0),

(1,'LX-SP-013','square-polarized-eclipse',
'Square Polarized Eclipse',
1450000,NULL,20,
'Đen','Acetate',
'<p>Kính phân cực công nghệ cao.</p>',
1,0),

(2,'LX-RM-014','round-minimalist-noir',
'Round Minimalist Noir',
850000,1000000,20,
'Đen','Titanium',
'<p>Thiết kế tối giản Bauhaus.</p>',
1,0);

-- PRODUCT IMAGES

INSERT INTO product_images
(product_id, image_path, alt_text, sort_order)
SELECT id, 'assets/images/blue-blocker.jpg', 'Round Blue-Blocker Silver', 1
FROM products
WHERE sku='LX-BB-001';

INSERT INTO product_images
(product_id, image_path, alt_text, sort_order)
SELECT id, 'assets/images/cat-eye.jpg', 'Cat-Eye Havana Acetate', 1
FROM products
WHERE sku='LX-CE-002';

INSERT INTO product_images
(product_id, image_path, alt_text, sort_order)
SELECT id, 'assets/images/round-noir.jpg', 'Titanium Minimalist Noir', 1
FROM products
WHERE sku='LX-TM-003';

INSERT INTO product_images
(product_id, image_path, alt_text, sort_order)
SELECT id, 'assets/images/aviator-main.jpg', 'Aviator Titanium Gold 01', 1
FROM products
WHERE sku='LX-AT-AV01';
-- PRODUCT IMAGES CONTINUE

INSERT INTO product_images
(product_id, image_path, alt_text, sort_order)
SELECT id, 'assets/images/img39.jpg', 'Butterfly Glamour Acetate', 1
FROM products
WHERE sku='LX-BG-005';

INSERT INTO product_images
(product_id, image_path, alt_text, sort_order)
SELECT id, 'assets/images/img38.jpg', 'Bold Rimless Horizon', 1
FROM products
WHERE sku='LX-BR-006';

INSERT INTO product_images
(product_id, image_path, alt_text, sort_order)
SELECT id, 'assets/images/img37.jpg', 'Wayfarer Classic Polarized', 1
FROM products
WHERE sku='LX-WC-007';

INSERT INTO product_images
(product_id, image_path, alt_text, sort_order)
SELECT id, 'assets/images/hexagon-silver.jpg', 'Hexagon Ultra Thin Titanium', 1
FROM products
WHERE sku='LX-HT-008';

INSERT INTO product_images
(product_id, image_path, alt_text, sort_order)
SELECT id, 'assets/images/pilot-gold.jpg', 'Pilot Master 18K Gold', 1
FROM products
WHERE sku='LX-PM-009';
INSERT INTO product_images
(product_id, image_path, alt_text, sort_order)
SELECT id, 'assets/images/img34.jpg', 'Oval Blue Blocker Pure', 1
FROM products
WHERE sku='LX-OB-010';

INSERT INTO product_images
(product_id, image_path, alt_text, sort_order)
SELECT id, 'assets/images/img33.jpg', 'Clubmaster Heritage Brown', 1
FROM products
WHERE sku='LX-CH-011';

INSERT INTO product_images
(product_id, image_path, alt_text, sort_order)
SELECT id, 'assets/images/img32.jpg', 'Geometric Wireframe Bronze', 1
FROM products
WHERE sku='LX-GW-012';

INSERT INTO product_images
(product_id, image_path, alt_text, sort_order)
SELECT id, 'assets/images/img31.jpg', 'Square Polarized Eclipse', 1
FROM products
WHERE sku='LX-SP-013';

INSERT INTO product_images
(product_id, image_path, alt_text, sort_order)
SELECT id, 'assets/images/round-noir.jpg', 'Round Minimalist Noir', 1
FROM products
WHERE sku='LX-RM-014';