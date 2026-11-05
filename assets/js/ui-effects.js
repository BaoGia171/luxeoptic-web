document.addEventListener('DOMContentLoaded', function () {
  const motion = window.matchMedia('(prefers-reduced-motion: reduce)');
  if (motion.matches || !('IntersectionObserver' in window)) return;

  const elements = document.querySelectorAll('[data-ui-reveal], .category-card, .product-card');
  const observer = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (!entry.isIntersecting) return;
      entry.target.classList.remove('reveal-pending');
      observer.unobserve(entry.target);
    });
  }, { rootMargin: '0px 0px 32px', threshold: 0 });

  // Giữ nội dung đang nằm trong màn hình ở trạng thái hiển thị.
  const upcoming = Array.from(elements).filter(element => element.getBoundingClientRect().top > innerHeight);
  upcoming.forEach(function (element) {
    element.classList.add('reveal-item', 'reveal-pending');
    observer.observe(element);
  });
  motion.addEventListener('change', function () {
    if (motion.matches) {
      observer.disconnect();
      upcoming.forEach(element => element.classList.remove('reveal-pending'));
    }
  });
});

document.addEventListener('DOMContentLoaded', function () {
  const button = document.createElement('button');
  button.type = 'button';
  button.className = 'back-to-top';
  button.title = 'Lên đầu trang';
  button.setAttribute('aria-label', button.title);
  button.textContent = '↑';
  document.body.append(button);

  function updateVisibility() {
    button.hidden = window.scrollY < Math.min(320, window.innerHeight / 2);
  }
  updateVisibility();
  window.addEventListener('scroll', updateVisibility, { passive: true });
  window.addEventListener('pageshow', updateVisibility);
  button.addEventListener('click', function () {
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    window.scrollTo({ top: 0, behavior: reducedMotion ? 'instant' : 'smooth' });
  });
});
