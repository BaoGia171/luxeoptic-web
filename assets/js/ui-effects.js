// Chỉ thêm hiệu ứng xuất hiện, không gắn sự kiện vào nút mua hàng.
document.addEventListener('DOMContentLoaded', function () {
  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
  if (reducedMotion.matches || !('IntersectionObserver' in window)) return;

  const elements = document.querySelectorAll('[data-ui-reveal], .category-card, .product-card');
  const observer = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (!entry.isIntersecting) return;
      entry.target.classList.add('ui-reveal');
      observer.unobserve(entry.target);
    });
  }, { threshold: 0.08 });

  elements.forEach(function (element) {
    // Không đặt opacity: 0 trước khi quan sát: JS lỗi vẫn thấy nội dung.
    observer.observe(element);
    element.addEventListener('animationend', function () {
      element.classList.remove('ui-reveal');
    }, { once: true });
  });

  reducedMotion.addEventListener('change', function (event) {
    if (event.matches) {
      observer.disconnect();
      elements.forEach(function (element) { element.classList.remove('ui-reveal'); });
    }
  });
});
