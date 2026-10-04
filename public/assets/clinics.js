(() => {
  const form = document.querySelector('.selector-form');
  if (!form) return;
  const district = document.querySelector('#district');
  const clinic = document.querySelector('#clinic');
  const stage = document.querySelector('#clinic-stage');
  const stack = document.querySelector('#card-stack');
  const options = [...clinic.options].map(option => option.cloneNode(true));
  const previous = document.querySelector('#previous-clinic');
  const next = document.querySelector('#next-clinic');
  const reducedMotion = matchMedia('(prefers-reduced-motion: reduce)');
  function showClinic(animate = true) {
    const template = document.querySelector(`#clinic-template-${clinic.value}`);
    if (!template) return;
    stage.replaceChildren(template.content.cloneNode(true));
    if (animate && !reducedMotion.matches) {
      stage.classList.remove('enter');
      void stage.offsetWidth;
      stage.classList.add('enter');
    }
    stack.style.setProperty('--tilt-x', '0deg');
    stack.style.setProperty('--tilt-y', '0deg');
    document.querySelector('#result-count').textContent = `${district.value} · ${clinic.selectedIndex + 1} / ${clinic.options.length}`;
    document.querySelector('#choice-count').textContent = `${clinic.options.length} seçenek`;
    previous.disabled = clinic.selectedIndex === 0;
    next.disabled = clinic.selectedIndex === clinic.options.length - 1;
  }
  function chooseDistrict(preserve = false) {
    const selected = clinic.value;
    clinic.replaceChildren(...options.filter(option => option.dataset.district === district.value).map(option => option.cloneNode(true)));
    if (preserve && [...clinic.options].some(option => option.value === selected)) clinic.value = selected;
    else clinic.selectedIndex = 0;
    showClinic(!preserve);
  }
  form.addEventListener('submit', event => { event.preventDefault(); showClinic(); });
  district.addEventListener('change', () => chooseDistrict());
  clinic.addEventListener('change', () => showClinic());
  previous.addEventListener('click', () => { if (clinic.selectedIndex > 0) { clinic.selectedIndex--; showClinic(); } });
  next.addEventListener('click', () => { if (clinic.selectedIndex < clinic.options.length - 1) { clinic.selectedIndex++; showClinic(); } });
  document.querySelector('.pager').hidden = false;
  chooseDistrict(true);
  // Tilt the card only for a precise pointer; information stays still on touch devices.
  const finePointer = matchMedia('(hover: hover) and (pointer: fine)');
  stack.addEventListener('pointermove', event => {
    if (reducedMotion.matches || !finePointer.matches) return;
    const rect = stack.getBoundingClientRect();
    stack.style.setProperty('--tilt-x', `${-(event.clientY - rect.top - rect.height / 2) / rect.height * 5}deg`);
    stack.style.setProperty('--tilt-y', `${(event.clientX - rect.left - rect.width / 2) / rect.width * 7}deg`);
  });
  function clearTilt() { stack.style.setProperty('--tilt-x', '0deg'); stack.style.setProperty('--tilt-y', '0deg'); }
  stack.addEventListener('pointerleave', clearTilt);
  reducedMotion.addEventListener('change', clearTilt);
  const dialog = document.querySelector('#sources-dialog');
  const open = document.querySelector('#sources-open');
  open.hidden = false;
  open.addEventListener('click', () => dialog.showModal());
  document.querySelector('#sources-close').addEventListener('click', () => dialog.close());
})();
