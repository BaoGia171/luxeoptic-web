(() => {
  const data = JSON.parse(document.getElementById('comparisonData').textContent);
  renderFrameComparison(document.getElementById('frameComparison'), data.products, data.ids);
})();
