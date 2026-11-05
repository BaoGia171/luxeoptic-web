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
    <button type="button" class="help-toggle" aria-controls="helpPanel" aria-expanded="false">Hỏi LuxeOptic</button>
    <section id="helpPanel" class="help-panel" aria-label="Hỏi LuxeOptic" hidden>
      <header><h2>Hỏi LuxeOptic</h2><button type="button" class="help-close" aria-label="Đóng trợ giúp">×</button></header>
      <div class="help-options"></div>
      <div class="help-answer" hidden><strong></strong><p role="status" aria-live="polite"></p>
        <button type="button" class="help-back">Quay lại câu hỏi</button>
      </div>
    </section>`;
  document.body.append(widget);
  const toggle = widget.querySelector('.help-toggle');
  const panel = widget.querySelector('.help-panel');
  const options = widget.querySelector('.help-options');
  const answer = widget.querySelector('.help-answer');
  const message = answer.querySelector('p');
  const back = widget.querySelector('.help-back');
  let lastQuestion;
  let request;
  function showOptions() {
    request?.abort();
    options.hidden = false;
    answer.hidden = true;
  }
  function close() {
    showOptions();
    panel.hidden = true;
    toggle.setAttribute('aria-expanded', 'false');
    toggle.focus();
  }
  function question(title, respond) {
    const button = document.createElement('button');
    button.type = 'button';
    button.textContent = title;
    button.addEventListener('click', function () {
      lastQuestion = button;
      options.hidden = true;
      answer.hidden = false;
      answer.querySelector('strong').textContent = title;
      respond();
      back.focus();
    });
    options.append(button);
  }
  question('Cách đọc kích thước gọng?', () => {
    message.textContent = 'Ví dụ 49 □ 20 145: tròng rộng 49 mm, cầu mũi rộng 20 mm và càng kính dài 145 mm. Bạn có thể đối chiếu với gọng đang đeo.';
  });
  question('Làm sao so sánh hai gọng?', () => {
    message.textContent = 'Mở danh sách kính và chọn hai mẫu khác nhau trong phần so sánh. Bạn có thể đổi từng mẫu để đối chiếu giá, màu và chất liệu.';
  });
  async function productAnswer(field) {
    message.textContent = 'Đang tra thông tin mẫu kính…';
    const controller = new AbortController();
    request = controller;
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
      if (!controller.signal.aborted) message.textContent = 'Không lấy được thông tin mẫu này. Bạn có thể quay lại và thử lại.';
    }
  }
  if (detail) {
    question('Mẫu này giá bao nhiêu?', () => productAnswer('price'));
    question('Mẫu này còn hàng không?', () => productAnswer('stock'));
  }
  toggle.addEventListener('click', function () {
    if (!panel.hidden) return close();
    panel.hidden = false;
    toggle.setAttribute('aria-expanded', 'true');
    options.firstElementChild.focus();
  });
  widget.querySelector('.help-close').addEventListener('click', close);
  back.addEventListener('click', function () {
    showOptions();
    lastQuestion.focus();
  });
  widget.addEventListener('keydown', event => { if (event.key === 'Escape' && !panel.hidden) close(); });
})();
