(() => {
  const form = document.querySelector('.filters');
  if (!form) return;
  const search = document.querySelector('#clinic-search');
  const district = document.querySelector('#district');
  const cards = [...document.querySelectorAll('.clinic-card')];
  const normalize = text => text.toLocaleLowerCase('tr-TR').replaceAll('ı', 'i').normalize('NFD').replace(/[\u0300-\u036f]/g, '');
  function filter() {
    const terms = normalize(search.value.trim()).split(/\s+/).filter(Boolean);
    let count = 0;
    cards.forEach(card => {
      const matches = (!district.value || card.dataset.district === district.value) && terms.every(term => normalize(card.dataset.search).includes(term));
      card.hidden = !matches;
      if (matches) count++;
    });
    document.querySelector('#result-count').textContent = `${count} merkez gösteriliyor`;
    document.querySelector('#no-results').hidden = count > 0;
  }
  function reset() { search.value = ''; district.value = ''; filter(); }
  form.hidden = false;
  form.addEventListener('submit', event => event.preventDefault());
  search.addEventListener('input', filter);
  district.addEventListener('change', filter);
  form.addEventListener('reset', event => { event.preventDefault(); reset(); });
  document.querySelector('#show-all').addEventListener('click', () => { reset(); search.focus(); });
})();
