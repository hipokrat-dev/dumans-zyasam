(() => {
 const $=s=>document.querySelector(s),ns='http://www.w3.org/2000/svg';
 const tabs=[...document.querySelectorAll('[data-impact]')],inputs=['#packs','#years','#pack-price','#minutes'].map($);
 let mode='money',lastMoney=-1,savingDays=365;
 const precise=n=>new Intl.NumberFormat('tr-TR',{maximumFractionDigits:2}).format(n);
 const format=n=>new Intl.NumberFormat('tr-TR',{maximumFractionDigits:1}).format(n);
 const money=n=>new Intl.NumberFormat('tr-TR',{maximumFractionDigits:0}).format(n)+' ₺';
 function svg(tag,attrs){const el=document.createElementNS(ns,tag);Object.entries(attrs).forEach(([k,v])=>el.setAttribute(k,String(v)));return el;}
 const bagPath='M30 22 23 7Q40 12 57 7L50 22C53 34 76 47 78 76Q82 103 40 105Q-2 103 2 76C4 47 27 34 30 22Z';
 function drawBags(total){
  if(total===lastMoney)return;lastMoney=total;const unit=25000,count=Math.ceil(total/unit),visible=Math.min(18,Math.max(1,count));
  $('#bags').replaceChildren();
  const rows=Math.ceil(visible/6),cols=Math.min(6,visible);
  $('#money-svg').setAttribute('viewBox',`${(660-cols*95)/2-20} ${225-(rows-1)*104-12} ${cols*95+40} ${rows*104+30}`);
  for(let i=0;i<visible;i++){
   const fill=Math.min(1,Math.max(0,(total-i*unit)/unit));const row=Math.floor(i/6),col=i%6;
   const g=svg('g',{'class':'money-bag',transform:`translate(${(660-Math.min(6,visible-row*6)*95)/2+col*95},${225-row*104}) scale(.92)`,filter:'url(#bag-shadow)'});
   const clip=svg('clipPath',{id:`bag-clip-${i}`});clip.append(svg('path',{d:bagPath}));
   g.append(clip,svg('path',{d:bagPath,fill:'url(#bag-body)',stroke:'#ddbf86','stroke-width':1}));
   const contents=svg('g',{'clip-path':`url(#bag-clip-${i})`});contents.append(svg('rect',{x:0,y:105-fill*80,width:80,height:fill*80,fill:'url(#bag-money)',opacity:.92}));
   for(let j=0;j<7;j++)contents.append(svg('path',{d:`M${12+j%3*19} ${100-fill*68+j*3}h14v7h-14Z`,fill:'none',stroke:'#fff0ca','stroke-width':1,opacity:fill}));
   const symbol=svg('text',{x:40,y:75,'text-anchor':'middle',fill:'#654122','font-size':25,'font-family':'Georgia'});symbol.textContent='₺';
   g.append(contents,svg('path',{d:'M28 23Q40 29 52 23M28 27Q40 33 52 27',fill:'none',stroke:'#745634','stroke-width':3}),symbol);$('#bags').append(g);
  }
  $('#bag-overflow').textContent=count>18?`+ ${format(count-18)} çuval`:(count===0?'Henüz harcama yok':`${format(count)} çuval`);
 }
 function update(){
  const valid=inputs.every(el=>el.value!==''&&el.checkValidity());const totals=valid?impactTotals(...inputs.map(el=>Number(el.value))):null;
  $('#packs-label').textContent=format(Number(inputs[0].value))+' paket';$('#years-label').textContent=inputs[1].value+' yıl';
  tabs.forEach(t=>{const selected=t.dataset.impact===mode;t.setAttribute('aria-selected',String(selected));t.tabIndex=selected?0:-1;});
  $('#impact-panel').setAttribute('aria-labelledby',`tab-${mode}`);
  document.body.classList.toggle('health-mode',mode==='health');
  document.body.classList.toggle('recovery-mode',mode==='recovery');
  document.body.classList.toggle('time-mode',mode==='time');
  $('#impact-heading').innerHTML=mode==='recovery'?'Bugün bırak. <br><em>Hayatın sana kalsın.</em>':'Hayatından <br><em>neler gidiyor?</em>';
  $('#quit-savings').hidden=mode!=='recovery';
  $('#years').hidden=mode==='recovery';
  $('label[for=years]').hidden=mode==='recovery';
  $('#cost-rates').hidden=mode!=='money'||!totals;
  $('.sliders').hidden=mode==='health';
  $('#assumptions-open').textContent=mode==='health'?'İstatistikler ve kaynaklar ↗':'Hesaplama ve kaynaklar ↗';
  ['money','health','time','recovery'].forEach(key=>$(`#${key}-visual`).hidden=key!==mode||(['money','time'].includes(key)&&!totals));
  const labels={money:['SİGARAYA AYRILAN PARA','Bugünkü paket fiyatıyla tahmini toplam.','Her çuval 25.000 ₺. Doluluk kalan tutarı gösterir.'],health:['SİGARANIN SAĞLIK BEDELİ','Araştırmalardaki karşılaştırmalar.','Kişisel risk hesabı değildir.'],recovery:['BIRAKINCA KAZANDIKLARIN','İlk dakikalardan yıllara uzanan değişim.','Her dumansız gün bir kazanım.'],time:['SİGARAYA AYRILAN ZAMAN','Sigara başına '+inputs[3].value+' dakika varsayımıyla.','Aynı sürenin farklı karşılıkları: 1 ay = 30 gün, 1 yıl = 365 gün.']};
  $('#metric-label').textContent=labels[mode][0];$('#metric-caption').textContent=(totals||mode==='health'||mode==='recovery')?labels[mode][1]:'Lütfen geçerli değerler gir.';$('#visual-caption').textContent=labels[mode][2];
  $('#metric-value').textContent=!totals?'—':mode==='money'?money(totals.money):mode==='health'?'Araştırma verileri':format(totals.hours)+' saat';
  const savings=quitSavings(Number(inputs[0].value),savingDays,Number(inputs[2].value),Number(inputs[3].value));
  const savingsValid=[inputs[0],inputs[2],inputs[3]].every(el=>el.value!==''&&el.checkValidity())&&savings;
  $('#saved-money').textContent=savingsValid?money(savings.money):'—';
  $('#saved-time').textContent=savingsValid?precise(savings.hours)+' saat':'—';
  if(!totals)return;
  const daily=Number(inputs[0].value)*Number(inputs[2].value);
  [['hour',daily/24],['day',daily],['month',daily*30],['year',daily*365]].forEach(([unit,value])=>$('#cost-'+unit).textContent=precise(value)+' ₺');
  [['hours',totals.hours],['total-days',totals.days],['months',totals.months],['years',totals.timeYears]].forEach(([unit,value])=>$('#time-'+unit).textContent=precise(value));
  drawBags(totals.money);
  $('#time-days').textContent=format(totals.days)+' gün';
  $('#clock-progress').setAttribute('stroke-dasharray',`${Math.min(1,totals.hours/10000)*603} 603`);
 }
 inputs.forEach(el=>el.addEventListener('input',update));
 tabs.forEach((tab,i)=>{tab.addEventListener('click',()=>{mode=tab.dataset.impact;update();});tab.addEventListener('keydown',e=>{let n;if(e.key==='ArrowRight')n=(i+1)%tabs.length;else if(e.key==='ArrowLeft')n=(i+tabs.length-1)%tabs.length;else if(e.key==='Home')n=0;else if(e.key==='End')n=tabs.length-1;else return;e.preventDefault();tabs[n].focus();mode=tabs[n].dataset.impact;update();});});
 const riskTabs=[...document.querySelectorAll('[data-risk]')];
 function selectRisk(tab){riskTabs.forEach(t=>{const active=t===tab;t.setAttribute('aria-selected',String(active));t.tabIndex=active?0:-1;$('#risk-'+t.dataset.risk).hidden=!active;});}
 riskTabs.forEach((tab,i)=>{tab.addEventListener('click',()=>selectRisk(tab));tab.addEventListener('keydown',e=>{let n;if(e.key==='ArrowRight')n=(i+1)%riskTabs.length;else if(e.key==='ArrowLeft')n=(i+riskTabs.length-1)%riskTabs.length;else if(e.key==='Home')n=0;else if(e.key==='End')n=riskTabs.length-1;else return;e.preventDefault();riskTabs[n].focus();selectRisk(riskTabs[n]);});});
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
  savingDays=Number(button.dataset.savingDays);document.querySelectorAll('[data-saving-days]').forEach(b=>b.setAttribute('aria-pressed',String(b===button)));update();
 }));
 $('#recovery-details').addEventListener('click',()=>$('#assumptions-dialog').showModal());
 $('#risk-details').addEventListener('click',()=>$('#assumptions-dialog').showModal());
 $('#assumptions-open').addEventListener('click',()=>$('#assumptions-dialog').showModal());$('#assumptions-close').addEventListener('click',()=>$('#assumptions-dialog').close());update();
})();
