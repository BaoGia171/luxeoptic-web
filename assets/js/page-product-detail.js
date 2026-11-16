document.querySelectorAll('[data-image]').forEach(button => button.addEventListener('click', () => {
 document.getElementById('mainProductImage').src = button.dataset.image;
}));