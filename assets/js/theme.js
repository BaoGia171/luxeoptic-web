// Chạy trong head để khôi phục giao diện trước khi trang được vẽ.
(function () {
  const storageKey = 'luxeoptic-theme';
  const systemTheme = window.matchMedia('(prefers-color-scheme: dark)');
  let savedTheme = null;

  try {
    savedTheme = localStorage.getItem(storageKey);
  } catch (error) {
    // Trình duyệt chặn lưu trữ: nút chuyển chế độ vẫn dùng được.
  }
  if (savedTheme !== 'light' && savedTheme !== 'dark') savedTheme = null;

  function applyTheme(theme) {
    document.documentElement.dataset.theme = theme;
    document.querySelectorAll('.theme-toggle').forEach(function (button) {
      const isDark = theme === 'dark';
      const label = isDark ? 'Bật chế độ sáng' : 'Bật chế độ tối';
      button.setAttribute('aria-label', label);
      button.setAttribute('aria-pressed', String(isDark));
      button.title = label;
      button.querySelector('span').textContent = isDark ? 'light_mode' : 'dark_mode';
    });
  }

  function preferredTheme() {
    return savedTheme || (systemTheme.matches ? 'dark' : 'light');
  }

  applyTheme(preferredTheme());
  document.addEventListener('DOMContentLoaded', function () {
    applyTheme(preferredTheme());
    document.querySelectorAll('.theme-toggle').forEach(function (button) {
      button.addEventListener('click', function () {
        savedTheme = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
        applyTheme(savedTheme);
        try { localStorage.setItem(storageKey, savedTheme); } catch (error) {}
      });
    });
  });

  systemTheme.addEventListener('change', function () {
    if (!savedTheme) applyTheme(preferredTheme());
  });
  window.addEventListener('storage', function (event) {
    if (event.key !== storageKey && event.key !== null) return;
    savedTheme = event.newValue === 'dark' || event.newValue === 'light' ? event.newValue : null;
    applyTheme(preferredTheme());
  });
})();
