/**
 * LUXEOPTIC - JAVASCRIPT CHÍNH (CLIENT STOREFRONT)
 * Điều khiển các tương tác: Gallery ảnh, Giỏ hàng, Chuyển tab Auth, Voucher
 */

document.addEventListener('DOMContentLoaded', function () {
  // 1. Thư viện ảnh sản phẩm (Product Gallery Switcher)
  const mainImage = document.getElementById('mainProductImage');
  const thumbnails = document.querySelectorAll('.gallery-thumb');

  if (mainImage && thumbnails.length > 0) {
    thumbnails.forEach((thumb) => {
      thumb.addEventListener('click', function () {
        thumbnails.forEach((t) => t.classList.remove('border-primary', 'active'));
        this.classList.add('border-primary', 'active');
        const newSrc = this.getAttribute('data-image') || this.querySelector('img')?.src;
        if (newSrc) {
          mainImage.src = newSrc;
        }
      });
    });
  }

  // 2. Chuyển đổi màu sắc (Color Swatches)
  const colorSwatches = document.querySelectorAll('.color-swatch-item');
  const selectedColorName = document.getElementById('selectedColorName');
  if (colorSwatches.length > 0) {
    colorSwatches.forEach((swatch) => {
      swatch.addEventListener('click', function () {
        colorSwatches.forEach((s) => s.classList.remove('ring-2', 'ring-primary', 'active'));
        this.classList.add('ring-2', 'ring-primary', 'active');
        const name = this.getAttribute('data-color-name');
        if (selectedColorName && name) {
          selectedColorName.textContent = name;
        }
      });
    });
  }

  // 3. Tăng / Giảm số lượng sản phẩm (Quantity Counter)
  document.querySelectorAll('.qty-btn').forEach((btn) => {
    btn.addEventListener('click', function () {
      const container = this.closest('.qty-control') || this.parentElement;
      const input = container.querySelector('.qty-input');
      if (!input) return;

      let currentVal = parseInt(input.value) || 1;
      if (this.classList.contains('qty-minus')) {
        if (currentVal > 1) currentVal--;
      } else if (this.classList.contains('qty-plus')) {
        currentVal++;
      }
      input.value = currentVal;

      // Cập nhật giá giỏ hàng nếu ở trang cart
      if (typeof updateCartTotals === 'function') {
        updateCartTotals();
      }
    });
  });

  // 4. Chuyển Tab Đăng nhập / Đăng ký (Auth Switcher)
  const tabLogin = document.getElementById('tabLogin');
  const tabRegister = document.getElementById('tabRegister');
  const formLogin = document.getElementById('formLogin');
  const formRegister = document.getElementById('formRegister');

  if (tabLogin && tabRegister && formLogin && formRegister) {
    tabLogin.addEventListener('click', function () {
      tabLogin.classList.add('active', 'border-b-2', 'border-primary', 'font-bold');
      tabRegister.classList.remove('active', 'border-b-2', 'border-primary', 'font-bold');
      formLogin.classList.remove('hidden');
      formRegister.classList.add('hidden');
    });

    tabRegister.addEventListener('click', function () {
      tabRegister.classList.add('active', 'border-b-2', 'border-primary', 'font-bold');
      tabLogin.classList.remove('active', 'border-b-2', 'border-primary', 'font-bold');
      formRegister.classList.remove('hidden');
      formLogin.classList.add('hidden');
    });
  }

  // Menu trên điện thoại và máy tính bảng.
  const menuButton = document.querySelector('.menu-toggle');
  const menu = document.getElementById('siteMenu');
  if (menuButton && menu) {
    function closeMenu() {
      menu.classList.remove('menu-open');
      menuButton.setAttribute('aria-expanded', 'false');
    }
    menuButton.addEventListener('click', function () {
      const opened = menu.classList.toggle('menu-open');
      menuButton.setAttribute('aria-expanded', String(opened));
    });
    menu.querySelectorAll('a').forEach(function (link) {
      link.addEventListener('click', closeMenu);
    });
    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') { closeMenu(); menuButton.focus(); }
    });
    document.addEventListener('click', function (event) {
      if (!menu.contains(event.target) && !menuButton.contains(event.target)) closeMenu();
    });
    window.addEventListener('resize', function () {
      if (window.innerWidth >= 1024) closeMenu();
    });
  }
});
