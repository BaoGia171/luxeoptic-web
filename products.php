<?php
require_once __DIR__ . '/includes/bootstrap.php';

$activeNav = '';

// Lấy số lượng giỏ hàng
$cartCount = array_sum($_SESSION['cart'] ?? []);
$stmt = $pdo->query(
    "SELECT 
        p.id,
        p.name,
        p.price,
        p.old_price,
        p.color,
        p.material,
        p.description,
        pi.image_path
    FROM products p
    LEFT JOIN product_images pi
        ON p.id = pi.product_id
    WHERE p.is_active = 1
    ORDER BY p.created_at DESC"
);

$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>LuxeOptic | Sản phẩm</title>

    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
</head>

<body class="bg-main text-primary">

<?php require __DIR__ . '/includes/header.php'; ?>


<main>

    <section class="container py-20">

        <h1 class="font-headline text-4xl">
            Bộ Sưu Tập Kính
        </h1>

        <p class="text-muted mt-4">
            Danh sách sản phẩm LuxeOptic
        <div class="product-grid grid-4 mt-10">

<?php foreach ($products as $product): ?>

    <div class="product-card">

        <div class="product-thumb">

            <a href="product-detail.php?id=<?= e($product['id']) ?>">
                <img 
                    src="<?= e($product['image_path']) ?>"
                    alt="<?= e($product['name']) ?>"
                >
            </a>

        </div>


        <div class="product-info">

            <span class="product-series">
                <?= e($product['material']) ?>
            </span>


            <h3 class="product-title">
                <?= e($product['name']) ?>
            </h3>


            <div class="product-price-row">

                <div>

                    <span class="product-price">
                        <?= money((int)$product['price']) ?>
                    </span>


                    <?php if (!empty($product['old_price'])): ?>

                        <span class="product-old-price">
                            <?= money((int)$product['old_price']) ?>
                        </span>

                    <?php endif; ?>

                </div>


                <a href="product-detail.php?id=<?= e($product['id']) ?>"
                   class="btn btn-primary btn-sm">
                    Xem Chi Tiết
                </a>

            </div>

        </div>

    </div>


<?php endforeach; ?>

</div>
</p>

    </section>

</main>


<?php require __DIR__ . '/includes/footer.php'; ?>


<script src="assets/js/main.js"></script>

</body>
</html>
