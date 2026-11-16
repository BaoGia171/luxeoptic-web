// TV3 có thể gọi hàm này từ compare.php với dữ liệu catalog và hai ID đã chọn.
// Mỗi mẫu gồm id, name, image (URL), price (VND), material và color.
function renderFrameComparison(container, products, initialIds = []) {
  const table = document.createElement('table');
  table.className = 'frame-table';
  table.setAttribute('aria-label', 'So sánh thông số hai gọng kính');
  const header = table.createTHead().insertRow();
  const label = document.createElement('th');
  label.textContent = 'Gọng kính';
  header.append(label);
  const picks = [0, 1].map(function (index) {
    const cell = document.createElement('th');
    cell.scope = 'col';
    const select = document.createElement('select');
    select.setAttribute('aria-label', 'Gọng kính thứ ' + (index + 1));
    select.add(new Option('Chọn mẫu ' + (index + 1), ''));
    products.forEach(item => select.add(new Option(item.name, String(item.id))));
    select.value = String(initialIds[index] ?? '');
    if (select.selectedIndex < 0) select.value = '';
    cell.append(select);
    header.append(cell);
    return select;
  });
  if (picks[0].value === picks[1].value) picks[1].value = '';
  const body = table.createTBody();
  const note = document.createElement('p');
  note.setAttribute('role', 'status');
  container.replaceChildren(table, note);
  function update() {
    const pair = picks.map(select => products.find(item => String(item.id) === select.value));
    picks.forEach(function (select, i) {
      for (const option of select.options) {
        option.disabled = Boolean(option.value && option.value === picks[1 - i].value);
      }
    });
    body.replaceChildren();
    for (const [field, title] of Object.entries({ image: 'Ảnh', name: 'Tên kính', price: 'Giá', material: 'Chất liệu', color: 'Màu' })) {
      const row = body.insertRow();
      const heading = document.createElement('th');
      heading.scope = 'row';
      heading.textContent = title;
      row.append(heading);
      pair.forEach(function (item) {
        const cell = row.insertCell();
        const value = item?.[field];
        if (field === 'image' && value) {
          const image = document.createElement('img');
          image.src = value;
          image.alt = item.name;
          cell.append(image);
        } else if (field === 'price' && value != null) {
          cell.textContent = Number(value).toLocaleString('vi-VN') + ' ₫';
        } else {
          cell.textContent = value || (item ? 'Chưa cập nhật' : '—');
        }
      });
    }
    note.textContent = pair.every(Boolean) ? '' : 'Chọn hai mẫu khác nhau để so sánh.';
  }
  picks.forEach(select => select.addEventListener('change', update));
  update();
  if (initialIds.some(id => id && !products.some(item => String(item.id) === String(id)))) {
    note.textContent = 'Một mẫu đã ngừng hiển thị. Vui lòng chọn lại.';
  }
}
