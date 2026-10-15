(() => {
 const $=s=>document.querySelector(s);
 const inputs=['#packs','#pack-price','#minutes'].map($);
 let savingDays=365;
 const format=n=>new Intl.NumberFormat('tr-TR',{maximumFractionDigits:2}).format(n);
 function updateSavings(){
  const valid=inputs.every(el=>el.value!==''&&el.checkValidity());
  const totals=valid?quitSavings(Number(inputs[0].value),savingDays,Number(inputs[1].value),Number(inputs[2].value)):null;
  $('#packs-label').textContent=format(Number(inputs[0].value))+' paket';
  $('#saved-money').textContent=totals?format(totals.money)+' ₺':'—';
  $('#saved-time').textContent=totals?format(totals.hours)+' saat':'—';
  $('#savings-note').textContent=totals?'Seçtiğin tüketimi tamamen bıraktığında tahmini kazancın.':'Lütfen geçerli tüketim ve fiyat bilgileri gir.';
 }
 inputs.forEach(el=>el.addEventListener('input',updateSavings));
 const who='https://www.who.int/news-room/questions-and-answers/item/tobacco-health-benefits-of-smoking-cessation';
 const cdc='https://www.cdc.gov/tobacco/about/benefits-of-quitting.html';
 const milestones=[
  ['20 dakika','İlk değişim kalbinde.','Nabzın ve tansiyonun düşmeye başlar.',who],
  ['Birkaç gün','Kanındaki yük hafifler.','Karbonmonoksit düzeyi, sigara içmeyen birinin düzeyine iner.',cdc],
  ['2–12 hafta','Hareket etmek kolaylaşır.','Dolaşımın iyileşir, akciğer işlevlerin artar.',who],
  ['1–12 ay','Nefesine alan açılır.','Öksürük ve nefes darlığı azalır.',cdc],
  ['1–2 yıl','Kalbin için büyük bir adım.','Kalp krizi riski belirgin biçimde düşer.',cdc],
  ['3–6 yıl','Riskin yönü değişir.','Koroner kalp hastalığı için ek risk yaklaşık yarıya iner.',cdc],
  ['10–15 yıl','Uzun vadede de kazanırsın.','Akciğer kanseri için ek risk yaklaşık yarıya iner. Ek risk, temel riskin üzerindeki kısımdır.',cdc],
  ['15 yıl','Kalbin yeni bir geleceğe yaklaşır.','Koroner kalp hastalığı riski, sigara içmeyen birinin riskine yaklaşır.',cdc]
 ];
 function showRecovery(index){
  index=Math.max(0,Math.min(milestones.length-1,index));
  const [time,title,description,source]=milestones[index];
  $('#recovery-time').textContent=time;$('#recovery-title').textContent=title;$('#recovery-description').textContent=description;
  $('#recovery-source').href=source;$('#recovery-source').textContent='Kaynak · '+(source===who?'Dünya Sağlık Örgütü':'CDC')+' ↗';
  $('#recovery-count').textContent=(index+1)+' / '+milestones.length;
  $('#recovery-step').value=index;$('#recovery-step').setAttribute('aria-valuetext',time);
  $('#recovery-prev').disabled=index===0;$('#recovery-next').disabled=index===milestones.length-1;
 }
 $('#recovery-step').addEventListener('input',e=>showRecovery(Number(e.target.value)));
 $('#recovery-prev').addEventListener('click',()=>showRecovery(Number($('#recovery-step').value)-1));
 $('#recovery-next').addEventListener('click',()=>showRecovery(Number($('#recovery-step').value)+1));
 document.querySelectorAll('[data-saving-days]').forEach(button=>button.addEventListener('click',()=>{
  savingDays=Number(button.dataset.savingDays);document.querySelectorAll('[data-saving-days]').forEach(b=>b.setAttribute('aria-pressed',String(b===button)));updateSavings();
 }));
 $('#recovery-details').addEventListener('click',()=>$('#assumptions-dialog').showModal());

 $('#assumptions-close').addEventListener('click',()=>$('#assumptions-dialog').close());
 showRecovery(0);updateSavings();
})();
