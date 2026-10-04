(() => {
 const $=s=>document.querySelector(s),ns='http://www.w3.org/2000/svg';
 const tabs=[...document.querySelectorAll('[data-impact]')],inputs=['#packs','#years','#pack-price','#minutes'].map($);
 let mode='money',lastMoney=-1;
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
 for(let i=0;i<52;i++)$('#lung-soot').append(svg('ellipse',{cx:65+(i*71)%310,cy:73+(i*47)%219,rx:8+i%7*3,ry:7+i%5*4,fill:'url(#soot-color)'}));
 function update(){
  const valid=inputs.every(el=>el.value!==''&&el.checkValidity());const totals=valid?impactTotals(...inputs.map(el=>Number(el.value))):null;
  $('#packs-label').textContent=format(Number(inputs[0].value))+' paket';$('#years-label').textContent=inputs[1].value+' yıl';
  tabs.forEach(t=>{const selected=t.dataset.impact===mode;t.setAttribute('aria-selected',String(selected));t.tabIndex=selected?0:-1;});
  $('#impact-panel').setAttribute('aria-labelledby',`tab-${mode}`);
  ['money','health','time'].forEach(key=>$(`#${key}-visual`).hidden=key!==mode||!totals);
  const labels={money:['SİGARAYA AYRILAN PARA','Bugünkü paket fiyatıyla tahmini toplam.','Her çuval 25.000 ₺. Doluluk kalan tutarı gösterir.'],health:['BİRİKEN MARUZİYET','Günlük paket × kullanım yılı.','Miktar ve süre arttıkça sağlık riski artar.'],time:['SİGARAYA AYRILAN ZAMAN','Sigara başına '+inputs[3].value+' dakika varsayımıyla.','Bu süre, sigara içmeye ayrılan zamandır.']};
  $('#metric-label').textContent=labels[mode][0];$('#metric-caption').textContent=totals?labels[mode][1]:'Lütfen geçerli değerler gir.';$('#visual-caption').textContent=labels[mode][2];
  $('#metric-value').textContent=!totals?'—':mode==='money'?money(totals.money):mode==='health'?format(totals.packYears)+' paket-yıl':format(totals.hours)+' saat';
  if(!totals)return;
  drawBags(totals.money);
  const exposure=1-Math.exp(-totals.packYears/22);
  $('#lung-soot').setAttribute('opacity',String(exposure*.9));$('#lung-shade').setAttribute('opacity',String(exposure*.55));
  $('.lung-organ').style.setProperty('--breath-scale',String(1.04-exposure*.025));
  $('#time-days').textContent=format(totals.days)+' gün';
  $('#clock-progress').setAttribute('stroke-dasharray',`${Math.min(1,totals.hours/10000)*603} 603`);
 }
 inputs.forEach(el=>el.addEventListener('input',update));
 tabs.forEach((tab,i)=>{tab.addEventListener('click',()=>{mode=tab.dataset.impact;update();});tab.addEventListener('keydown',e=>{let n;if(e.key==='ArrowRight')n=(i+1)%3;else if(e.key==='ArrowLeft')n=(i+2)%3;else return;e.preventDefault();tabs[n].focus();mode=tabs[n].dataset.impact;update();});});
 $('#assumptions-open').addEventListener('click',()=>$('#assumptions-dialog').showModal());$('#assumptions-close').addEventListener('click',()=>$('#assumptions-dialog').close());update();
})();
