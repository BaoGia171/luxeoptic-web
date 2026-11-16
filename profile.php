<?php
// Hồ sơ khách hàng: xem và sửa họ tên, số điện thoại của tài khoản đang đăng nhập.
// Email và quyền chỉ để xem. Dữ liệu gửi tới actions/profile-update.php.
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/user-helpers.php';

require_login();
$user = current_user();

$statement = $pdo->prepare('SELECT created_at FROM users WHERE id = ?');
$statement->execute([(int) $user['id']]);
$createdAt = (string) $statement->fetchColumn();

$state = user_form_take('profile');
// Có lỗi thì giữ giá trị vừa nhập; không có thì lấy từ DB.
$fullName = $state['old']['full_name'] ?? $user['full_name'];
$phone = $state['old']['phone'] ?? $user['phone'];

$activeNav = '';
$cartCount = array_sum(array_map('intval', $_SESSION['cart']));
$csrf = csrf_token();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hồ Sơ Của Tôi | LuxeOptic</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bodoni+Moda:ital,opsz,wght@0,6..96,400..700;1,6..96,400..700&family=Inter:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">
  <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
  <link rel="stylesheet" href="<?= e(url('assets/css/responsive.css')) ?>">
  <!-- Giao diện sáng/tối và hiệu ứng của TV1: kiểm tra tên file khớp thư mục assets/css -->
  <link rel="stylesheet" href="<?= e(url('assets/css/theme.css')) ?>">
  <link rel="stylesheet" href="<?= e(url('assets/css/ui-effects.css')) ?>">
  <style>
    .profile-wrap { max-width: 640px; margin: 0 auto; padding: 2rem 1rem 4rem; }
    .profile-card { padding: 1.5rem; }
    .profile-meta { display: grid; gap: .75rem; margin: 1.5rem 0; padding: 1rem 0; font-size: .75rem; }
    .profile-meta dt { font-weight: 600; text-transform: uppercase; letter-spacing: .05em; font-size: .625rem; }
    .profile-meta dd { margin: .125rem 0 0; }
    .profile-actions { display: flex; flex-wrap: wrap; gap: .75rem; align-items: center; justify-content: space-between; margin-top: 1.5rem; }
    .profile-flash { padding: .75rem 1rem; margin-bottom: 1rem; font-size: .75rem; line-height: 1.5; }
    .profile-flash.is-success { background: #d1fae5; color: #065f46; }
    .profile-flash.is-error { background: #fee2e2; color: #991b1b; }
    [aria-invalid="true"] { border-color: #dc2626; }
    @media (min-width: 768px) { .profile-card { padding: 2.5rem; } .profile-meta { grid-template-columns: 1fr 1fr; } }
  </style>
</head>
<body class="bg-main text-primary antialiased">

<?php require __DIR__ . '/includes/header.php'; ?>

  <main class="profile-wrap">
    <span class="uppercase-spaced text-muted text-10px">Tài khoản</span>
    <h1 class="font-headline text-3xl font-normal mt-1 mb-6">Hồ Sơ Của Tôi</h1>

    <?php foreach (get_flashes() as $flashMessage): ?>
      <div class="profile-flash is-<?= e($flashMessage['type']) ?>" role="<?= $flashMessage['type'] === 'error' ? 'alert' : 'status' ?>">
        <?= e($flashMessage['message']) ?>
      </div>
    <?php endforeach; ?>

    <section class="bg-white border border-light shadow-sm profile-card" aria-labelledby="profileFormTitle">
      <h2 id="profileFormTitle" class="font-headline text-xl mb-4">Thông tin cá nhân</h2>

      <form method="post" action="<?= e(url('actions/profile-update.php')) ?>" id="profileForm" novalidate>
        <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">

        <div class="form-group mb-4">
          <label for="profileName" class="form-label">Họ và Tên *</label>
          <input type="text" id="profileName" name="full_name" required maxlength="100" autocomplete="name" class="form-control" value="<?= e($fullName) ?>"<?= user_field_attrs($state, 'full_name', 'profile') ?>>
          <?= user_field_error($state, 'full_name', 'profile') ?>
        </div>

        <div class="form-group mb-4">
          <label for="profilePhone" class="form-label">Số Điện Thoại *</label>
          <input type="tel" id="profilePhone" name="phone" required maxlength="20" autocomplete="tel" class="form-control" value="<?= e($phone) ?>"<?= user_field_attrs($state, 'phone', 'profile') ?>>
          <?= user_field_error($state, 'phone', 'profile') ?>
        </div>

        <div class="form-group mb-4">
          <label for="profileEmail" class="form-label">Email (không thể thay đổi)</label>
          <input type="email" id="profileEmail" class="form-control" value="<?= e($user['email']) ?>" readonly aria-readonly="true">
        </div>

        <button type="submit" class="btn btn-primary btn-sm font-bold">LƯU THAY ĐỔI</button>
      </form>

      <dl class="profile-meta border-t border-b border-light">
        <div>
          <dt class="text-muted">Loại tài khoản</dt>
          <dd><?= $user['role'] === 'admin' ? 'Quản trị viên' : 'Khách hàng' ?></dd>
        </div>
        <div>
          <dt class="text-muted">Thành viên từ</dt>
          <dd><?= e($createdAt !== '' ? date('d/m/Y', strtotime($createdAt)) : '') ?></dd>
        </div>
      </dl>

      <div class="profile-actions">
        <a href="<?= e(url('my-orders.php')) ?>" class="btn btn-outline btn-sm">ĐƠN HÀNG CỦA TÔI</a>
        <form method="post" action="<?= e(url('actions/logout.php')) ?>">
          <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
          <button type="submit" class="btn btn-outline btn-sm">ĐĂNG XUẤT</button>
        </form>
      </div>
    </section>
  </main>

<?php require __DIR__ . '/includes/footer.php'; ?>

  <script src="<?= e(url('assets/js/theme.js')) ?>"></script>
  <script src="<?= e(url('assets/js/ui-effects.js')) ?>"></script>
  <script src="<?= e(url('assets/js/main.js')) ?>"></script>
</body>
</html>
