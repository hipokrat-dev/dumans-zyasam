(() => {
  const form = document.querySelector('.filters');
  if (!form) return;
  const search = document.querySelector('#clinic-search');
  const district = document.querySelector('#district');
  const type = document.querySelector('#clinic-type');
  const cards = [...document.querySelectorAll('.clinic-card')];
  const quickButtons = [...document.querySelectorAll('[data-quick-district]')];
  const normalize = text => text.toLocaleLowerCase('tr-TR').replaceAll('ı', 'i').normalize('NFD').replace(/[\u0300-\u036f]/g, '');
  function filter() {
    const terms = normalize(search.value.trim()).split(/\s+/).filter(Boolean);
    let count = 0;
    cards.forEach(card => {
      const matches = (!district.value || card.dataset.district === district.value) && (!type.value || card.dataset.type === type.value) && terms.every(term => normalize(card.dataset.search).includes(term));
      card.hidden = !matches;
      if (matches) count++;
    });
    quickButtons.forEach(button => button.setAttribute('aria-pressed', String(button.dataset.quickDistrict === district.value)));
    document.querySelector('#result-count').textContent = `${count} destek noktası${district.value ? ' · ' + district.value : ' · Bursa'}`;
    document.querySelector('#no-results').hidden = count > 0;
  }
  function reset() { search.value = ''; district.value = ''; type.value = ''; filter(); }
  form.hidden = false;
  document.querySelector('.quick-districts').hidden = false;
  document.querySelector('.view-switch').hidden = false;
  form.addEventListener('submit', event => event.preventDefault());
  search.addEventListener('input', filter);
  district.addEventListener('change', filter);
  type.addEventListener('change', filter);
  quickButtons.forEach(button => button.addEventListener('click', () => { district.value = button.dataset.quickDistrict; filter(); }));
  document.querySelectorAll('[data-view]').forEach(button => button.addEventListener('click', () => {
    document.querySelector('.clinic-grid').classList.toggle('list-view', button.dataset.view === 'list');
    document.querySelectorAll('[data-view]').forEach(item => item.setAttribute('aria-pressed',String(item === button)));
  }));
  form.addEventListener('reset', event => { event.preventDefault(); reset(); });
  document.querySelector('#show-all').addEventListener('click', () => { reset(); search.focus(); });
  filter();
})();
