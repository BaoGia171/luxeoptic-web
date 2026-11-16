(() => {
 const picks = [...document.querySelectorAll('[name="compare"]')];
 const button = document.getElementById('compareButton');
 const status = document.getElementById('compareStatus');
 if (!button) return;
 picks.forEach(p => p.addEventListener('change', () => {
  let selected = picks.filter(x => x.checked);
  if (selected.length > 2) { p.checked = false; selected = picks.filter(x => x.checked); }
  button.disabled = selected.length !== 2;
  status.textContent = `Đã chọn ${selected.length}/2 mẫu. Bỏ chọn để thay mẫu khác.`;
 }));
 button.addEventListener('click', () => {
  const ids = picks.filter(x => x.checked).map(x => x.value);
  if (ids.length === 2) location.href = 'compare.php?' + new URLSearchParams({id1:ids[0],id2:ids[1]});
 });
})();