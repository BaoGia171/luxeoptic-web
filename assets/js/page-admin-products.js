document.querySelectorAll('[data-confirm]').forEach(form => form.addEventListener('submit', event => {
 if (!confirm(form.dataset.confirm)) event.preventDefault();
}));
const source = document.getElementById('description');
if (source && window.Quill) {
 const container = document.getElementById('editor');
 container.hidden = false;
 const editor = new Quill(container, {theme:'snow',modules:{toolbar:[['bold','italic'],[{list:'ordered'},{list:'bullet'}],['link']]}});
 editor.clipboard.dangerouslyPasteHTML(source.value);
 source.hidden = true;
 document.getElementById('productForm').addEventListener('submit', () => {
  source.value = editor.getSemanticHTML();
 });
}
