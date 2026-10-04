(() => {
 const picker=document.querySelector('#edit-item');
 if(picker)picker.addEventListener('change',()=>picker.form.requestSubmit());
 document.querySelectorAll('input[type=file]').forEach(input=>input.addEventListener('change',()=>{
  input.setCustomValidity(input.files[0]&&input.files[0].size>50*1024*1024?'Dosya en fazla 50 MB olabilir.':'');input.reportValidity();
 }));
 document.querySelectorAll('form[enctype]').forEach(form=>form.addEventListener('submit',()=>{const button=form.querySelector('button.primary');button.disabled=true;button.textContent='Yükleniyor…';}));
 document.querySelectorAll('audio,video').forEach(media=>media.addEventListener('play',()=>document.querySelectorAll('audio,video').forEach(other=>{if(other!==media)other.pause();})));
})();
