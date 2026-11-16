<?php
// Đăng nhập / đăng ký (TV2). Giao diện lấy từ auth.html của TV1.
// Dữ liệu gửi tới actions/login.php và actions/register.php.
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/user-helpers.php';

$returnKey = user_return_key($_GET['return'] ?? null);

// Đã đăng nhập thì không hiện form nữa.
if (($current = current_user()) !== null) {
    redirect(user_after_login_path($returnKey, $current['role']));
}

$loginState = user_form_take('login');
$registerState = user_form_take('register');

// Đăng ký bị lỗi thì mở lại đúng tab Đăng ký.
$tab = (($_GET['tab'] ?? '') === 'register' || $registerState['errors'] || $registerState['old'])
    ? 'register' : 'login';

$loginOld = $loginState['old'];
$registerOld = $registerState['old'];
$flashes = get_flashes();
$csrf = csrf_token();

$tabClasses = static fn (bool $active): string => $active
    ? 'border-b-2 border-black font-bold text-black active'
    : 'text-gray-400 hover-text-black font-medium';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Xác Thực Tài Khoản | LuxeOptic Member Access</title>

  <!-- Google Fonts & Material Icons -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bodoni+Moda:ital,opsz,wght@0,6..96,400..700;1,6..96,400..700&family=Inter:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">
  <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
  <link rel="stylesheet" href="<?= e(url('assets/css/responsive.css')) ?>">
  <link rel="stylesheet" href="<?= e(url('assets/css/auth.css')) ?>">
  <!-- Giao diện sáng/tối và hiệu ứng của TV1: kiểm tra tên file khớp thư mục assets/css -->
  <link rel="stylesheet" href="<?= e(url('assets/css/theme.css')) ?>">
  <link rel="stylesheet" href="<?= e(url('assets/css/ui-effects.css')) ?>">
  <style>
    [aria-invalid="true"] { border-color: #dc2626; }
    .auth-flash { padding: .75rem 1rem; margin-bottom: 1rem; font-size: .75rem; line-height: 1.5; }
    .auth-flash.is-success { background: #d1fae5; color: #065f46; }
    .auth-flash.is-error { background: #fee2e2; color: #991b1b; }
  </style>
</head>
<body class="bg-main text-primary antialiased min-h-screen flex items-center justify-center p-0 tablet-p-6">

  <div class="w-full max-w-6xl bg-white border border-light shadow-xl overflow-hidden min-h-700px grid grid-cols-1 desktop-grid-cols-12">

    <!-- LEFT COLUMN: EDITORIAL VISUAL (50%) -->
    <div class="auth-cover desktop-col-span-6 relative bg-cover bg-center hidden desktop-flex flex-col justify-between p-12 text-white">

      <!-- Top Brand Tag -->
      <div class="flex items-center justify-between">
        <div class="brand-logo">
          <div class="logo-mark text-secondary border border-secondary">L</div>
          <div class="flex flex-col">
            <span class="logo-text text-white">LuxeOptic</span>
            <span class="text-9px uppercase tracking-widest text-secondary font-semibold">Atelier Haute Lunetterie</span>
          </div>
        </div>
        <span class="badge border border-white-30 text-white text-10px uppercase tracking-widest">Paris • Geneva • Saigon</span>
      </div>

      <!-- Center & Bottom Quote -->
      <div class="space-y-6">
        <div class="inline-flex items-center gap-2 bg-white-10 backdrop-blur-md px-3 py-1_5 border border-white-20">
          <span class="material-symbols-outlined text-secondary text-sm">workspace_premium</span>
          <span class="font-label text-10px uppercase tracking-widest text-white font-semibold">Tuyên ngôn thẩm mỹ</span>
        </div>

        <blockquote class="font-headline text-2xl small-text-3xl font-normal leading-snug">
          “Tầm nhìn định hình phong cách. Đăng nhập để trải nghiệm đặc quyền thượng lưu cùng LuxeOptic.”
        </blockquote>

        <p class="text-xs text-gray-300 font-light leading-relaxed max-w-md">
          Trải nghiệm may đo quang học riêng biệt, chế tác titan quý và dịch vụ chăm sóc hoàn mỹ trọn đời.
        </p>
      </div>

    </div>

    <!-- RIGHT COLUMN: AUTHENTICATION FORMS (50%) -->
    <div class="desktop-col-span-6 p-8 small-p-12 flex flex-col justify-between">

      <div>
        <!-- Top Controls: Back -->
        <div class="flex items-center justify-between mb-8 pb-4 border-b border-light">
          <a href="<?= e(url('index.php')) ?>" class="inline-flex items-center gap-1_5 text-xs font-label uppercase tracking-wider text-muted hover-text-black font-semibold">
            <span class="material-symbols-outlined text-base">arrow_back</span> Quay lại trang chủ
          </a>
        </div>

        <!-- Section Title -->
        <div class="mb-6">
          <span class="uppercase-spaced text-muted text-10px">Atelier Member Access</span>
          <h2 class="font-headline text-3xl font-normal mt-1 mb-2">Chào Mừng Trở Lại</h2>
          <p class="text-xs text-muted">Nhập thông tin xác thực để truy cập không gian mua sắm cá nhân hóa của bạn.</p>
        </div>

        <?php foreach ($flashes as $flashMessage): ?>
          <div class="auth-flash is-<?= e($flashMessage['type']) ?>" role="<?= $flashMessage['type'] === 'error' ? 'alert' : 'status' ?>">
            <?= e($flashMessage['message']) ?>
          </div>
        <?php endforeach; ?>

        <!-- TABS SWITCHER: ĐĂNG NHẬP / ĐĂNG KÝ -->
        <div class="flex border-b border-light mb-6 text-xs font-label uppercase tracking-wider">
          <button type="button" id="tabLogin" aria-selected="<?= $tab === 'login' ? 'true' : 'false' ?>" class="pb-3 px-4 flex-1 text-center <?= $tabClasses($tab === 'login') ?>">
            <?= $tab === 'login' ? '• ' : '' ?>Đăng Nhập
          </button>
          <button type="button" id="tabRegister" aria-selected="<?= $tab === 'register' ? 'true' : 'false' ?>" class="pb-3 px-4 flex-1 text-center <?= $tabClasses($tab === 'register') ?>">
            <?= $tab === 'register' ? '• ' : '' ?>Đăng Ký Tài Khoản
          </button>
        </div>

        <!-- FORM 1: ĐĂNG NHẬP (LOGIN) -->
        <form id="formLogin" method="post" action="<?= e(url('actions/login.php')) ?>" class="<?= $tab === 'login' ? '' : 'hidden' ?>" novalidate>
          <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
          <input type="hidden" name="return" value="<?= e($returnKey) ?>">

          <?php if (isset($loginState['errors']['general'])): ?>
            <div class="auth-flash is-error" role="alert"><?= e($loginState['errors']['general']) ?></div>
          <?php endif; ?>

          <div class="form-group mb-4">
            <label for="authEmail" class="form-label">Email hoặc Số Điện Thoại *</label>
            <div class="auth-input">
              <span class="material-symbols-outlined absolute left-3 text-gray-400 text-lg">mail</span>
              <input type="text" id="authEmail" name="identifier" required maxlength="150" autocomplete="username"
                     class="form-control" placeholder="Nhập email hoặc số điện thoại"
                     value="<?= e((string) ($loginOld['identifier'] ?? '')) ?>">
            </div>
          </div>

          <div class="form-group mb-6">
            <label for="authPassword" class="form-label">Mật Khẩu Bảo Mật *</label>
            <div class="auth-input">
              <span class="material-symbols-outlined absolute left-3 text-gray-400 text-lg">lock</span>
              <input type="password" id="authPassword" name="password" required autocomplete="current-password"
                     class="form-control auth-password" placeholder="Nhập mật khẩu...">
              <button type="button" class="auth-password-toggle" aria-label="Hiện mật khẩu" data-toggle-password="authPassword">
                <span class="material-symbols-outlined text-lg">visibility</span>
              </button>
            </div>
          </div>

          <button type="submit" class="btn btn-primary btn-block py-3_5 text-xs font-bold mb-4 flex items-center justify-center gap-2">
            ĐĂNG NHẬP HỆ THỐNG <span class="material-symbols-outlined text-sm">arrow_forward</span>
          </button>
        </form>

        <!-- FORM 2: ĐĂNG KÝ TÀI KHOẢN -->
        <form id="formRegister" method="post" action="<?= e(url('actions/register.php')) ?>" class="<?= $tab === 'register' ? '' : 'hidden' ?>" novalidate>
          <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
          <input type="hidden" name="return" value="<?= e($returnKey) ?>">

          <div class="form-group mb-3">
            <label for="regName" class="form-label">Họ và Tên Đầy Đủ *</label>
            <input type="text" id="regName" name="full_name" required maxlength="100" autocomplete="name"
                   class="form-control" placeholder="Ví dụ: Nguyễn Văn A"
                   value="<?= e((string) ($registerOld['full_name'] ?? '')) ?>"<?= user_field_attrs($registerState, 'full_name', 'register') ?>>
            <?= user_field_error($registerState, 'full_name', 'register') ?>
          </div>

          <div class="form-group mb-3">
            <label for="regEmail" class="form-label">Địa Chỉ Email *</label>
            <input type="email" id="regEmail" name="email" required maxlength="150" autocomplete="email"
                   class="form-control" placeholder="email@domain.com"
                   value="<?= e((string) ($registerOld['email'] ?? '')) ?>"<?= user_field_attrs($registerState, 'email', 'register') ?>>
            <?= user_field_error($registerState, 'email', 'register') ?>
          </div>

          <div class="form-group mb-3">
            <label for="regPhone" class="form-label">Số Điện Thoại Liên Hệ *</label>
            <input type="tel" id="regPhone" name="phone" required maxlength="20" autocomplete="tel"
                   class="form-control" placeholder="0912 345 678"
                   value="<?= e((string) ($registerOld['phone'] ?? '')) ?>"<?= user_field_attrs($registerState, 'phone', 'register') ?>>
            <?= user_field_error($registerState, 'phone', 'register') ?>
          </div>

          <div class="grid grid-cols-1 small-grid-cols-2 gap-3 mb-4">
            <div class="form-group mb-0">
              <label for="regPass" class="form-label">Mật Khẩu *</label>
              <input type="password" id="regPass" name="password" required minlength="8" autocomplete="new-password"
                     class="form-control" placeholder="Tối thiểu 8 ký tự"<?= user_field_attrs($registerState, 'password', 'register') ?>>
              <?= user_field_error($registerState, 'password', 'register') ?>
            </div>
            <div class="form-group mb-0">
              <label for="regConfirmPass" class="form-label">Nhập Lại Mật Khẩu *</label>
              <input type="password" id="regConfirmPass" name="password_confirm" required minlength="8" autocomplete="new-password"
                     class="form-control" placeholder="Xác nhận mật khẩu"<?= user_field_attrs($registerState, 'password_confirm', 'register') ?>>
              <?= user_field_error($registerState, 'password_confirm', 'register') ?>
            </div>
          </div>

          <div class="mb-5 text-xs text-secondary">
            <div class="flex items-center gap-2">
              <input type="checkbox" id="agreeTerms" name="agree_terms" value="1" required class="accent-black"
                     <?= !empty($registerOld['agree_terms']) ? 'checked' : '' ?><?= user_field_attrs($registerState, 'agree_terms', 'register') ?>>
              <label for="agreeTerms" class="cursor-pointer">Tôi đồng ý với <a href="#" class="underline font-semibold">Điều khoản thành viên Atelier</a></label>
            </div>
            <?= user_field_error($registerState, 'agree_terms', 'register') ?>
          </div>

          <button type="submit" class="btn btn-primary btn-block py-3_5 text-xs font-bold mb-4 flex items-center justify-center gap-2">
            ĐĂNG KÝ THÀNH VIÊN ATELIER <span class="material-symbols-outlined text-sm">person_add</span>
          </button>
        </form>
      </div>

      <!-- Footer Info -->
      <div class="auth-footer text-11px text-muted">
        <div class="flex gap-4">
          <a href="#" class="hover-underline">Điều khoản</a>
          <a href="#" class="hover-underline">Quyền riêng tư</a>
        </div>
      </div>

    </div>

  </div>

  <script src="<?= e(url('assets/js/theme.js')) ?>"></script>
  <script src="<?= e(url('assets/js/ui-effects.js')) ?>"></script>
  <script src="<?= e(url('assets/js/page-auth.js')) ?>"></script>
</body>
</html>
