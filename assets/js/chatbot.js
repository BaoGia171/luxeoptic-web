// Trên trang chi tiết PHP, đặt data-product-detail và data-product-id trên thẻ script.
// data-endpoint có thể ghi đè đường dẫn khi trang nằm trong thư mục con.
(function () {
  const script = document.currentScript;
  const detail = script.hasAttribute('data-product-detail');
  const id = script.dataset.productId || new URLSearchParams(location.search).get('id');
  const endpoint = script.dataset.endpoint || 'actions/product-info.php';
  const widget = document.createElement('aside');
  widget.className = 'shopping-help';
  widget.setAttribute('aria-label', 'Trợ giúp mua kính');
  widget.innerHTML = `
    <button type="button" class="help-toggle" aria-controls="helpPanel" aria-expanded="false" aria-label="Hỏi LuxeOptic" title="Hỏi LuxeOptic">
      <svg width="27" height="27" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.6">
        <path d="M20.5 11.5a8.5 8.5 0 0 1-12.4 7.6L3 20.5l1.5-5A8.5 8.5 0 1 1 20.5 11.5Z"/>
        <path d="M8 10h8m-8 3.5h5" stroke-linecap="round"/>
      </svg>
    </button>
    <section id="helpPanel" class="help-panel" aria-label="Hỏi LuxeOptic" hidden>
      <header><span class="help-avatar" aria-hidden="true">L</span><h2>Hỏi LuxeOptic</h2><button type="button" class="help-close" aria-label="Đóng trợ giúp">×</button></header>
      <div class="help-thread" role="log" aria-label="Cuộc trò chuyện" aria-live="polite" aria-relevant="additions text" tabindex="0">
        <p class="help-bubble">Chào bạn! Bạn muốn tìm hiểu gì về gọng kính?</p>
      </div>
      <div class="help-options" aria-label="Câu hỏi gợi ý"></div>
    </section>`;
  document.body.append(widget);
  const toggle = widget.querySelector('.help-toggle');
  const panel = widget.querySelector('.help-panel');
  const options = widget.querySelector('.help-options');
  const thread = widget.querySelector('.help-thread');
  function addMessage(text, fromCustomer) {
    const bubble = document.createElement('p');
    bubble.className = fromCustomer ? 'help-bubble from-customer' : 'help-bubble';
    bubble.textContent = text;
    thread.append(bubble);
    return bubble;
  }
  function close() {
    panel.hidden = true;
    toggle.setAttribute('aria-expanded', 'false');
    toggle.focus();
  }
  function question(title, respond) {
    const button = document.createElement('button');
    button.type = 'button';
    button.textContent = title;
    button.addEventListener('click', function () {
      addMessage(title, true);
      respond(addMessage('', false));
      thread.scrollTop = thread.scrollHeight;
    });
    options.append(button);
  }
  question('Cách đọc kích thước gọng?', message => {
    message.textContent = 'Bạn có thể xem ba số ở mặt trong càng kính. Ví dụ, nếu thấy các số 49, 20 và 145, thì mỗi tròng kính rộng 49 mm, khoảng cách giữa hai tròng là 20 mm và càng kính dài 145 mm.';
  });
  question('Làm sao so sánh hai gọng?', message => {
    message.textContent = 'Mở danh sách kính và chọn hai mẫu khác nhau trong phần so sánh. Bạn có thể đổi từng mẫu để đối chiếu giá, màu và chất liệu.';
  });
  async function productAnswer(field, message) {
    message.textContent = 'Đang tra thông tin mẫu kính…';
    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), 8000);
    try {
      if (!/^[1-9]\d*$/.test(id || '')) throw new Error();
      const url = new URL(endpoint, document.baseURI);
      url.searchParams.set('id', id);
      const response = await fetch(url, { signal: controller.signal, cache: 'no-store', headers: { Accept: 'application/json' } });
      if (!response.ok) throw new Error();
      const data = await response.json();
      if (String(data.id) !== String(id) || !/^\d+$/.test(String(data[field]))) throw new Error();
      const value = Number(data[field]);
      if (field === 'stock') message.textContent = value ? 'Mẫu này còn hàng.' : 'Mẫu này hiện đã hết hàng.';
      else message.textContent = 'Giá hiện tại: ' + value.toLocaleString('vi-VN') + ' ₫.';
    } catch (error) {
      message.textContent = 'Mình chưa tra được thông tin mẫu này. Bạn có thể thử lại sau.';
    } finally {
      clearTimeout(timeout);
    }
  }
  if (detail) {
    question('Mẫu này giá bao nhiêu?', message => productAnswer('price', message));
    question('Mẫu này còn hàng không?', message => productAnswer('stock', message));
  }
  toggle.addEventListener('click', function () {
    if (!panel.hidden) return close();
    panel.hidden = false;
    toggle.setAttribute('aria-expanded', 'true');
    options.firstElementChild.focus();
  });
  widget.querySelector('.help-close').addEventListener('click', close);
  widget.addEventListener('keydown', event => { if (event.key === 'Escape' && !panel.hidden) close(); });
})();
