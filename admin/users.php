<?php
// Quản lý tài khoản (Admin). Chỉ có hai vai trò customer/admin và hai trạng thái
// active/blocked. Tìm kiếm/lọc dùng GET: q, role, status, page. 10 dòng/trang.
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/user-helpers.php';

require_admin(); // phải chạy trước mọi HTML để còn gửi được header chuyển hướng
$me = current_user();

const USERS_PER_PAGE = 10;

$q = isset($_GET['q']) && is_string($_GET['q']) ? trim($_GET['q']) : '';
$q = mb_substr($q, 0, 100, 'UTF-8');
$roleFilter = in_array($_GET['role'] ?? null, ['admin', 'customer'], true) ? $_GET['role'] : '';
$statusFilter = in_array($_GET['status'] ?? null, ['active', 'blocked'], true) ? $_GET['status'] : '';
$page = isset($_GET['page']) && is_string($_GET['page']) && ctype_digit($_GET['page'])
    ? max(1, (int) $_GET['page']) : 1;

$where = [];
$params = [];
if ($q !== '') {
    $like = '%' . addcslashes($q, '%_\\') . '%';
    $phoneLike = '%' . addcslashes(preg_replace('/\s+/u', '', $q) ?? '', '%_\\') . '%';
    $where[] = '(full_name LIKE ? OR email LIKE ? OR phone LIKE ?)';
    array_push($params, $like, $like, $phoneLike);
}
if ($roleFilter !== '') {
    $where[] = 'role = ?';
    $params[] = $roleFilter;
}
if ($statusFilter !== '') {
    $where[] = 'status = ?';
    $params[] = $statusFilter;
}
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$statement = $pdo->prepare("SELECT COUNT(*) FROM users {$whereSql}");
$statement->execute($params);
$totalRows = (int) $statement->fetchColumn();
$totalPages = max(1, (int) ceil($totalRows / USERS_PER_PAGE));
$page = min($page, $totalPages);
$offset = ($page - 1) * USERS_PER_PAGE;

$statement = $pdo->prepare(
    "SELECT id, full_name, email, phone, role, status, created_at
     FROM users {$whereSql}
     ORDER BY created_at DESC, id DESC
     LIMIT " . USERS_PER_PAGE . " OFFSET {$offset}"
);
$statement->execute($params);
$users = $statement->fetchAll();

$stats = $pdo->query(
    "SELECT COUNT(*) AS total,
            COALESCE(SUM(role = 'admin'), 0) AS admins,
            COALESCE(SUM(role = 'customer'), 0) AS customers,
            COALESCE(SUM(status = 'blocked'), 0) AS blocked,
            COALESCE(SUM(role = 'admin' AND status = 'active'), 0) AS active_admins
     FROM users"
)->fetch();
$activeAdmins = (int) $stats['active_admins'];

// Giữ bộ lọc khi chuyển trang và khi quay lại sau thao tác.
$listQuery = array_filter(
    ['q' => $q, 'role' => $roleFilter, 'status' => $statusFilter],
    static fn ($value) => $value !== ''
);
$backQuery = http_build_query($listQuery + ($page > 1 ? ['page' => $page] : []));
$pageUrl = static function (int $targetPage) use ($listQuery): string {
    $query = $listQuery + ($targetPage > 1 ? ['page' => $targetPage] : []);
    return url('admin/users.php') . ($query ? '?' . http_build_query($query) : '');
};
$tabUrl = static function (string $role) use ($q, $statusFilter): string {
    $query = array_filter(['q' => $q, 'role' => $role, 'status' => $statusFilter], static fn ($v) => $v !== '');
    return url('admin/users.php') . ($query ? '?' . http_build_query($query) : '');
};

// Form thêm/sửa bị lỗi thì mở lại modal với giá trị đã nhập.
$formState = user_form_take('user_save');
$reopen = $formState['errors'] || $formState['old'];
$old = $formState['old'];
$editingId = $reopen && isset($old['id']) && ctype_digit((string) $old['id']) ? (int) $old['id'] : 0;
$formValue = static fn (string $key, string $default = '') => (string) ($old[$key] ?? $default);

$flashes = get_flashes();
$csrf = csrf_token();

function user_initials(string $name): string
{
    $words = preg_split('/\s+/u', trim($name), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    if (count($words) >= 2) {
        $letters = mb_substr($words[0], 0, 1, 'UTF-8') . mb_substr($words[count($words) - 1], 0, 1, 'UTF-8');
    } else {
        $letters = mb_substr($name, 0, 2, 'UTF-8');
    }
    return mb_strtoupper($letters, 'UTF-8');
}

// Dải số trang quanh trang hiện tại, có trang đầu/cuối và dấu "…".
function user_page_window(int $current, int $total): array
{
    $pages = array_unique(array_filter(
        array_merge([1, $total], range($current - 2, $current + 2)),
        static fn ($p) => $p >= 1 && $p <= $total
    ));
    sort($pages);
    $result = [];
    $previous = 0;
    foreach ($pages as $p) {
        if ($previous && $p - $previous > 1) {
            $result[] = null;
        }
        $result[] = $p;
        $previous = $p;
    }
    return $result;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quản Lý Tài Khoản | LuxeOptic Admin Portal</title>

  <!-- Google Fonts & Material Icons -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bodoni+Moda:ital,opsz,wght@0,6..96,400..700;1,6..96,400..700&family=Inter:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">
  <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
  <link rel="stylesheet" href="<?= e(url('assets/css/admin.css')) ?>">
  <link rel="stylesheet" href="<?= e(url('assets/css/responsive.css')) ?>">
  <style>
    [aria-invalid="true"] { border-color: #dc2626; }
    .admin-flash { padding: .75rem 1rem; margin-bottom: 1rem; font-size: .75rem; line-height: 1.5; }
    .admin-flash.is-success { background: #d1fae5; color: #065f46; }
    .admin-flash.is-error { background: #fee2e2; color: #991b1b; }
    .inline-form { display: inline; margin: 0; }
    .action-btn[disabled] { opacity: .4; cursor: not-allowed; }
    .page-link { display: inline-block; }
  </style>
</head>
<body class="admin-body">

  <!-- ADMIN SIDEBAR -->
  <?php $activeAdmin = 'users'; require __DIR__ . '/includes/sidebar.php'; ?>

  <!-- MAIN CONTENT AREA -->
  <div class="admin-main">

    <!-- TOPBAR -->
    <header class="admin-topbar">
      <button type="button" class="admin-menu-toggle" aria-controls="adminMenu" aria-expanded="false" aria-label="Mở menu quản trị">
        <span class="material-symbols-outlined">menu</span>
      </button>
      <div class="admin-breadcrumbs">
        <span>Admin</span>
        <span>/</span>
        <span class="active">Quản lý tài khoản</span>
      </div>

      <div class="flex items-center gap-3 small-gap-4">
        <button type="button" class="btn btn-primary btn-sm flex items-center gap-1_5 py-2" data-user-create>
          <span class="material-symbols-outlined text-sm">person_add</span> Thêm Tài Khoản Mới
        </button>
        <div class="flex items-center gap-2 pl-2 border-l border-gray-200">
          <div class="w-8 h-8 rounded-full bg-black text-white flex items-center justify-center font-bold text-xs"><?= e(user_initials($me['full_name'])) ?></div>
          <span class="text-xs font-semibold hidden small-inline"><?= e($me['full_name']) ?></span>
        </div>
      </div>
    </header>

    <!-- CONTENT BODY -->
    <div class="admin-content-inner">

      <?php foreach ($flashes as $flashMessage): ?>
        <div class="admin-flash is-<?= e($flashMessage['type']) ?>" role="<?= $flashMessage['type'] === 'error' ? 'alert' : 'status' ?>">
          <?= e($flashMessage['message']) ?>
        </div>
      <?php endforeach; ?>

      <!-- THỐNG KÊ (đọc từ database) -->
      <div class="metric-grid">
        <div class="metric-card">
          <div class="metric-header">
            <span class="metric-label">Tổng tài khoản</span>
            <span class="material-symbols-outlined text-gray-400">group</span>
          </div>
          <div class="metric-number"><?= number_format((int) $stats['total'], 0, ',', '.') ?></div>
          <div class="metric-footer text-gray-500">Gồm khách hàng và quản trị viên</div>
        </div>

        <div class="metric-card">
          <div class="metric-header">
            <span class="metric-label">Quản trị viên (Admin)</span>
            <span class="material-symbols-outlined text-secondary">shield_person</span>
          </div>
          <div class="metric-number"><?= number_format((int) $stats['admins'], 0, ',', '.') ?></div>
          <div class="metric-footer text-gray-500"><?= $activeAdmins ?> đang hoạt động</div>
        </div>

        <div class="metric-card">
          <div class="metric-header">
            <span class="metric-label">Khách hàng (Customer)</span>
            <span class="material-symbols-outlined text-blue-600">person</span>
          </div>
          <div class="metric-number"><?= number_format((int) $stats['customers'], 0, ',', '.') ?></div>
          <div class="metric-footer text-gray-500">Tài khoản đăng ký công khai</div>
        </div>

        <div class="metric-card">
          <div class="metric-header">
            <span class="metric-label">Đang bị khóa</span>
            <span class="material-symbols-outlined text-secondary">block</span>
          </div>
          <div class="metric-number"><?= number_format((int) $stats['blocked'], 0, ',', '.') ?></div>
          <div class="metric-footer text-gray-500">Không thể đăng nhập</div>
        </div>
      </div>

      <!-- BỘ LỌC -->
      <div class="bg-white border border-light p-4 mb-6 shadow-sm">
        <div class="flex flex-col desktop-flex-row desktop-items-center justify-between gap-4">
          <!-- Lọc theo vai trò -->
          <div class="flex items-center gap-1 overflow-x-auto pb-1 desktop-pb-0 text-xs font-semibold">
            <?php
            $roleTabs = ['' => 'Tất cả', 'admin' => 'Quản trị viên', 'customer' => 'Khách hàng'];
            foreach ($roleTabs as $value => $label):
                $isActive = $roleFilter === $value;
            ?>
              <a href="<?= e($tabUrl($value)) ?>" class="px-3 py-1_5 <?= $isActive ? 'bg-black text-white' : 'text-gray-600 hover-bg-gray-100' ?>"<?= $isActive ? ' aria-current="true"' : '' ?>><?= e($label) ?></a>
            <?php endforeach; ?>
          </div>

          <!-- Tìm kiếm & trạng thái -->
          <form method="get" action="<?= e(url('admin/users.php')) ?>" class="flex items-center gap-3 flex-wrap">
            <?php if ($roleFilter !== ''): ?>
              <input type="hidden" name="role" value="<?= e($roleFilter) ?>">
            <?php endif; ?>
            <div class="relative flex-1 small-w-64">
              <span class="material-symbols-outlined absolute left-3 top-2_5 text-gray-400 text-sm">search</span>
              <input type="text" name="q" value="<?= e($q) ?>" maxlength="100" placeholder="Tìm kiếm theo tên, email, SĐT..." aria-label="Tìm tài khoản" class="h-9 pl-9 pr-3 text-xs bg-gray-50 border border-gray-200 outline-none w-full focus-border-black">
            </div>
            <select name="status" aria-label="Lọc theo trạng thái" class="h-9 px-3 text-xs bg-gray-50 border border-gray-200 outline-none">
              <option value="">Trạng thái: Tất cả</option>
              <option value="active"<?= $statusFilter === 'active' ? ' selected' : '' ?>>Đang hoạt động</option>
              <option value="blocked"<?= $statusFilter === 'blocked' ? ' selected' : '' ?>>Đã khóa</option>
            </select>
            <button type="submit" class="btn btn-primary btn-sm">LỌC</button>
          </form>
        </div>
      </div>

      <!-- BẢNG TÀI KHOẢN -->
      <div class="table-card">
        <div class="table-header">
          <div>
            <h3 class="font-headline text-lg font-bold text-black">Danh Sách Tài Khoản</h3>
            <p class="text-xs text-gray-500 mt-0_5">Hai vai trò: khách hàng (customer) và quản trị viên (admin). Tài khoản có đơn hàng chỉ khóa, không xóa.</p>
          </div>
          <div class="text-xs text-gray-500 font-medium">
            Trang <span class="font-bold text-black"><?= $page ?>/<?= $totalPages ?></span>
          </div>
        </div>

        <div class="table-responsive">
          <table class="admin-table">
            <thead>
              <tr>
                <th class="table-column-250">Người Dùng & Email</th>
                <th class="table-column-140">Số Điện Thoại</th>
                <th class="table-column-170">Vai Trò (Role)</th>
                <th class="table-column-140">Ngày Đăng Ký</th>
                <th class="table-column-130">Trạng Thái</th>
                <th class="table-column-120-right">Thao Tác</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!$users): ?>
                <tr>
                  <td colspan="6" class="text-xs text-gray-500" style="text-align:center;padding:2rem 1rem;">Không có tài khoản nào phù hợp.</td>
                </tr>
              <?php endif; ?>
              <?php foreach ($users as $row):
                  $isAdmin = $row['role'] === 'admin';
                  $isBlocked = $row['status'] === 'blocked';
                  $isSelf = (int) $row['id'] === (int) $me['id'];
                  $isLastAdmin = $isAdmin && !$isBlocked && $activeAdmins <= 1;
                  $lockReason = $isSelf ? 'Không thể khóa chính mình' : ($isLastAdmin ? 'Không thể khóa quản trị viên hoạt động cuối cùng' : '');
              ?>
              <tr<?= $isBlocked ? ' class="opacity-60 bg-gray-50"' : '' ?>>
                <td>
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full <?= $isAdmin ? 'bg-black text-secondary border border-secondary' : 'bg-gray-100 text-gray-800' ?> flex items-center justify-center font-bold text-sm">
                      <?= e(user_initials($row['full_name'])) ?>
                    </div>
                    <div>
                      <b class="text-black text-xs block"><?= e($row['full_name']) ?><?= $isSelf ? ' (bạn)' : '' ?></b>
                      <span class="text-11px text-gray-500 font-mono"><?= e($row['email']) ?></span>
                    </div>
                  </div>
                </td>
                <td class="text-xs font-mono font-medium text-gray-700"><?= e($row['phone']) ?></td>
                <td>
                  <?php if ($isAdmin): ?>
                    <span class="px-2_5 py-1 bg-black text-secondary text-11px font-bold rounded inline-flex items-center gap-1 border border-secondary-30">
                      <span class="material-symbols-outlined text-13px">shield</span> ADMIN
                    </span>
                  <?php else: ?>
                    <span class="px-2_5 py-1 bg-gray-100 text-gray-700 text-11px font-semibold rounded inline-flex items-center gap-1">CUSTOMER</span>
                  <?php endif; ?>
                </td>
                <td class="text-xs text-gray-500"><?= e(date('d/m/Y', strtotime($row['created_at']))) ?></td>
                <td>
                  <?php if ($isBlocked): ?>
                    <span class="px-2_5 py-1 bg-red-100 text-red-800 text-11px font-bold rounded inline-flex items-center gap-1">
                      <span class="w-1_5 h-1_5 rounded-full bg-red-600"></span> ĐÃ KHÓA
                    </span>
                  <?php else: ?>
                    <span class="px-2_5 py-1 bg-emerald-100 text-emerald-800 text-11px font-bold rounded inline-flex items-center gap-1">
                      <span class="w-1_5 h-1_5 rounded-full bg-emerald-600"></span> HOẠT ĐỘNG
                    </span>
                  <?php endif; ?>
                </td>
                <td>
                  <div class="action-btn-group justify-end">
                    <button type="button" class="action-btn" title="Chỉnh sửa tài khoản" aria-label="Chỉnh sửa <?= e($row['full_name']) ?>"
                      data-user-edit
                      data-id="<?= (int) $row['id'] ?>"
                      data-name="<?= e($row['full_name']) ?>"
                      data-email="<?= e($row['email']) ?>"
                      data-phone="<?= e($row['phone']) ?>"
                      data-role="<?= e($row['role']) ?>"
                      data-status="<?= e($row['status']) ?>">
                      <span class="material-symbols-outlined text-sm">edit</span>
                    </button>
                    <form method="post" action="<?= e(url('admin/actions/user-status.php')) ?>" class="inline-form"
                      data-confirm="<?= e(($isBlocked ? 'Mở khóa' : 'Khóa') . ' tài khoản "' . $row['full_name'] . '"?') ?>">
                      <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                      <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                      <input type="hidden" name="status" value="<?= $isBlocked ? 'active' : 'blocked' ?>">
                      <input type="hidden" name="back" value="<?= e($backQuery) ?>">
                      <?php if ($isBlocked): ?>
                        <button type="submit" class="action-btn" title="Mở khóa tài khoản" aria-label="Mở khóa <?= e($row['full_name']) ?>">
                          <span class="material-symbols-outlined text-sm">lock_open</span>
                        </button>
                      <?php else: ?>
                        <button type="submit" class="action-btn btn-delete" title="<?= e($lockReason !== '' ? $lockReason : 'Khóa tài khoản') ?>" aria-label="Khóa <?= e($row['full_name']) ?>"<?= $lockReason !== '' ? ' disabled' : '' ?>>
                          <span class="material-symbols-outlined text-sm">block</span>
                        </button>
                      <?php endif; ?>
                    </form>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <!-- Phân trang -->
        <div class="admin-pagination p-4 border-t border-light flex flex-col small-flex-row items-center justify-between gap-3 text-xs text-gray-500">
          <div>
            <?php if ($totalRows > 0): ?>
              Hiển thị <span class="font-bold text-black"><?= $offset + 1 ?> - <?= $offset + count($users) ?></span>
              của <span class="font-bold text-black"><?= number_format($totalRows, 0, ',', '.') ?></span> tài khoản
            <?php else: ?>
              Không có kết quả
            <?php endif; ?>
          </div>

          <?php if ($totalPages > 1): ?>
          <nav class="flex items-center gap-1 flex-wrap justify-center" aria-label="Phân trang">
            <?php if ($page > 1): ?>
              <a href="<?= e($pageUrl($page - 1)) ?>" class="page-link px-2_5 py-1_5 border border-gray-200 hover-bg-gray-100 text-black text-xs">&laquo; Trước</a>
            <?php else: ?>
              <span class="page-link px-2_5 py-1_5 border border-gray-200 bg-gray-50 text-gray-400 cursor-not-allowed text-xs">&laquo; Trước</span>
            <?php endif; ?>

            <?php foreach (user_page_window($page, $totalPages) as $p): ?>
              <?php if ($p === null): ?>
                <span class="page-link px-2 py-1_5 text-gray-400 text-xs">…</span>
              <?php elseif ($p === $page): ?>
                <span class="page-link px-3 py-1_5 border border-black bg-black text-white font-bold text-xs" aria-current="page"><?= $p ?></span>
              <?php else: ?>
                <a href="<?= e($pageUrl($p)) ?>" class="page-link px-3 py-1_5 border border-gray-200 hover-bg-gray-100 text-black text-xs"><?= $p ?></a>
              <?php endif; ?>
            <?php endforeach; ?>

            <?php if ($page < $totalPages): ?>
              <a href="<?= e($pageUrl($page + 1)) ?>" class="page-link px-2_5 py-1_5 border border-gray-200 hover-bg-gray-100 text-black text-xs">Sau &raquo;</a>
            <?php else: ?>
              <span class="page-link px-2_5 py-1_5 border border-gray-200 bg-gray-50 text-gray-400 cursor-not-allowed text-xs">Sau &raquo;</span>
            <?php endif; ?>
          </nav>
          <?php endif; ?>
        </div>
      </div>

    </div>
  </div>

  <!-- MODAL: THÊM / CHỈNH SỬA TÀI KHOẢN -->
  <div id="userModal" class="modal-overlay" data-reopen="<?= $reopen ? '1' : '0' ?>" data-mode="<?= $editingId > 0 ? 'edit' : 'create' ?>">
    <div class="modal-container max-w-xl" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
      <div class="modal-header">
        <h3 id="modalTitle" class="font-headline text-xl text-black">Thêm Tài Khoản Mới</h3>
        <button type="button" class="text-gray-400 hover-text-black text-2xl" aria-label="Đóng" data-user-close>&times;</button>
      </div>

      <form method="post" action="<?= e(url('admin/actions/user-save.php')) ?>" id="userForm" novalidate>
        <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
        <input type="hidden" name="back" value="<?= e($backQuery) ?>">
        <input type="hidden" name="id" id="userIdInput" value="<?= $editingId > 0 ? $editingId : '' ?>">

        <div class="modal-body space-y-4">
          <div class="grid grid-cols-1 small-grid-cols-2 gap-4">
            <div class="form-group mb-0">
              <label for="userNameInput" class="form-label">Họ và Tên *</label>
              <input type="text" id="userNameInput" name="full_name" required maxlength="100" class="form-control" value="<?= e($formValue('full_name')) ?>"<?= user_field_attrs($formState, 'full_name', 'user') ?>>
              <?= user_field_error($formState, 'full_name', 'user') ?>
            </div>

            <div class="form-group mb-0">
              <label for="userPhoneInput" class="form-label">Số Điện Thoại *</label>
              <input type="tel" id="userPhoneInput" name="phone" required maxlength="20" class="form-control font-mono" value="<?= e($formValue('phone')) ?>"<?= user_field_attrs($formState, 'phone', 'user') ?>>
              <?= user_field_error($formState, 'phone', 'user') ?>
            </div>
          </div>

          <div class="grid grid-cols-1 small-grid-cols-2 gap-4">
            <div class="form-group mb-0">
              <label for="userEmailInput" class="form-label">Email Đăng Nhập *</label>
              <input type="email" id="userEmailInput" name="email" required maxlength="150" class="form-control font-mono text-xs" value="<?= e($formValue('email')) ?>"<?= user_field_attrs($formState, 'email', 'user') ?>>
              <?= user_field_error($formState, 'email', 'user') ?>
            </div>

            <!-- Mật khẩu chỉ nhập khi tạo mới; JS ẩn nhóm này ở chế độ sửa -->
            <div class="form-group mb-0" id="userPasswordGroup">
              <label for="userPasswordInput" class="form-label">Mật Khẩu (tối thiểu 8 ký tự) *</label>
              <input type="password" id="userPasswordInput" name="password" autocomplete="new-password" class="form-control text-xs"<?= user_field_attrs($formState, 'password', 'user') ?>>
              <?= user_field_error($formState, 'password', 'user') ?>
            </div>
          </div>

          <div class="grid grid-cols-1 small-grid-cols-2 gap-4">
            <div class="form-group mb-0">
              <label for="userRoleSelect" class="form-label">Vai Trò (Role) *</label>
              <select id="userRoleSelect" name="role" class="form-control font-medium"<?= user_field_attrs($formState, 'role', 'user') ?>>
                <option value="customer"<?= $formValue('role', 'customer') === 'customer' ? ' selected' : '' ?>>Khách hàng (customer)</option>
                <option value="admin"<?= $formValue('role', 'customer') === 'admin' ? ' selected' : '' ?>>Quản trị viên (admin)</option>
              </select>
              <?= user_field_error($formState, 'role', 'user') ?>
            </div>

            <div class="form-group mb-0">
              <label for="userStatusSelect" class="form-label">Trạng Thái Tài Khoản</label>
              <select id="userStatusSelect" name="status" class="form-control"<?= user_field_attrs($formState, 'status', 'user') ?>>
                <option value="active"<?= $formValue('status', 'active') === 'active' ? ' selected' : '' ?>>Đang hoạt động (active)</option>
                <option value="blocked"<?= $formValue('status', 'active') === 'blocked' ? ' selected' : '' ?>>Đã khóa (blocked)</option>
              </select>
              <?= user_field_error($formState, 'status', 'user') ?>
            </div>
          </div>

          <?php if (isset($formState['errors']['general'])): ?>
            <p class="text-11px text-red-600" role="alert"><?= e($formState['errors']['general']) ?></p>
          <?php endif; ?>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-outline btn-sm" data-user-close>HỦY BỎ</button>
          <button type="submit" class="btn btn-primary btn-sm font-bold">LƯU THAY ĐỔI</button>
        </div>
      </form>
    </div>
  </div>

  <script src="<?= e(url('assets/js/admin.js')) ?>"></script>
  <script src="<?= e(url('assets/js/page-admin-users.js')) ?>"></script>
</body>
</html>
