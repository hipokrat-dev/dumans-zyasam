(() => {
 const tabs=[...document.querySelectorAll('[data-category][role=tab]')];
 const templates=[...document.querySelectorAll('template[data-id]')];
 const topic=document.querySelector('#topic'),content=document.querySelector('#topic-content');
 let selectedCategory=tabs[0]?.dataset.category,available=[],playbackVolume=1;
 const imageDialog=document.querySelector('#studio-image-dialog');
 if(imageDialog){document.querySelector('.studio-room-expand').addEventListener('click',()=>imageDialog.showModal());document.querySelector('#studio-image-close').addEventListener('click',()=>imageDialog.close());}
 const picker=document.querySelector('#episode-picker'),pickerOpen=document.querySelector('#episode-picker-open'),pickerList=document.querySelector('#episode-picker-list');
 if(picker&&pickerOpen){
  pickerOpen.addEventListener('click',()=>{picker.showModal();pickerOpen.setAttribute('aria-expanded','true');(pickerList.querySelector('[aria-pressed="true"]')||pickerList.querySelector('button'))?.focus();});
  document.querySelector('#episode-picker-close').addEventListener('click',()=>picker.close());
  picker.addEventListener('close',()=>{pickerOpen.setAttribute('aria-expanded','false');pickerOpen.focus();});
  picker.addEventListener('click',event=>{if(event.target===picker){const rect=picker.getBoundingClientRect();if(event.clientX<rect.left||event.clientX>rect.right||event.clientY<rect.top||event.clientY>rect.bottom)picker.close();}});
  pickerList.addEventListener('keydown',event=>{const buttons=[...pickerList.querySelectorAll('button')],index=buttons.indexOf(document.activeElement);let next;if(event.key==='ArrowDown')next=(index+1)%buttons.length;else if(event.key==='ArrowUp')next=(index+buttons.length-1)%buttons.length;else if(event.key==='Home')next=0;else if(event.key==='End')next=buttons.length-1;else return;event.preventDefault();buttons[next]?.focus();});
 }
 function updatePicker(){
  if(!pickerOpen)return;
  pickerOpen.disabled=!available.length;
  document.querySelector('#episode-picker-value').textContent=available[topic.selectedIndex]?.dataset.title||'Henüz bölüm eklenmedi';
  document.querySelector('#episode-picker-category').textContent=`${tabs.find(tab=>tab.dataset.category===selectedCategory)?.getAttribute('aria-label')||''} · ${available.length} bölüm`;
  pickerList.replaceChildren(...available.map((template,index)=>{
   const button=document.createElement('button');button.type='button';button.className='episode-choice';button.setAttribute('aria-pressed',String(index===topic.selectedIndex));
   const number=document.createElement('span');number.className='episode-choice-number';number.textContent=String(index+1).padStart(2,'0');number.setAttribute('aria-hidden','true');
   const title=document.createElement('span');title.className='episode-choice-title';title.textContent=template.dataset.title;
   const indicator=document.createElement('span');indicator.className='episode-choice-indicator';indicator.textContent=index===topic.selectedIndex?'✓':'↗';indicator.setAttribute('aria-hidden','true');
   button.append(number,title,indicator);button.addEventListener('click',()=>{topic.selectedIndex=index;render();picker.close();});return button;
  }));
 }
 const studioStatus=document.querySelector('#studio-status');
 const episodeInfo=document.querySelector('#episode-info'),episodeDialog=document.querySelector('#episode-dialog');
 if(episodeInfo&&episodeDialog){episodeInfo.addEventListener('click',()=>episodeDialog.showModal());document.querySelector('#episode-dialog-close').addEventListener('click',()=>episodeDialog.close());}

 function setStudioPlaying(playing){document.body.classList.toggle('is-listening',playing);if(studioStatus)studioStatus.textContent=playing?'ŞİMDİ DİNLİYORSUN':'DİNLEMEYE HAZIR';}
 function formatTime(seconds){if(!Number.isFinite(seconds))return '0:00';return `${Math.floor(seconds/60)}:${String(Math.floor(seconds%60)).padStart(2,'0')}`;}

 function stopMedia(){document.querySelectorAll('#topic-content audio,#topic-content video').forEach(media=>media.pause());}
 function render(){
  updatePicker();stopMedia();setStudioPlaying(false);content.replaceChildren();const template=available[topic.selectedIndex];
  if(template)content.append(template.content.cloneNode(true));else{const p=document.createElement('p');p.textContent='Bu bölüme henüz başlık eklenmedi.';content.append(p);}
  if(episodeInfo){episodeInfo.hidden=!template;document.querySelector('#episode-dialog-title').textContent=content.querySelector('h2')?.textContent||'';document.querySelector('#episode-dialog-text').textContent=content.querySelector('article>p')?.textContent||'';}
  const dialog=content.querySelector('.topic-video-dialog');if(dialog){content.querySelector('.topic-video-open').addEventListener('click',()=>{stopMedia();dialog.showModal();});content.querySelector('.topic-video-close').addEventListener('click',()=>dialog.close());dialog.addEventListener('close',stopMedia);}
  const audio=content.querySelector('audio');if(audio&&document.body.classList.contains('studio-page'))enhanceAudio(audio);
  const media=[...content.querySelectorAll('audio,video')];media.forEach(item=>item.addEventListener('play',()=>media.filter(other=>other!==item).forEach(other=>other.pause())));
  document.querySelector('#topic-count').textContent=available.length?`${topic.selectedIndex+1} / ${available.length}`:'0 / 0';
  document.querySelector('#topic-prev').disabled=topic.selectedIndex<=0;
  document.querySelector('#topic-next').disabled=topic.selectedIndex>=available.length-1;
 }
 function enhanceAudio(audio){
  audio.volume=playbackVolume;audio.muted=playbackVolume===0;
  const player=document.createElement('div');player.className='studio-player';
  player.innerHTML='<div class="player-main"><button class="studio-play" type="button" aria-label="Ses kaydını oynat"><span aria-hidden="true">▶</span></button><div class="player-display"><div class="studio-wave" aria-hidden="true">'+Array.from({length:28},()=>'<i></i>').join('')+'</div><div class="player-times"><span class="elapsed">0:00</span><span class="duration">0:00</span></div></div></div><input class="studio-seek" type="range" min="0" max="100" step="0.1" value="0" disabled aria-label="Ses kaydında ilerle"><div class="player-bottom"><span class="player-hint" role="status">Dinlemek için oynat</span><label class="studio-volume"><span>Ses</span><input type="range" min="0" max="1" step="0.05" value="1" aria-label="Ses düzeyi"></label></div>';
  const play=player.querySelector('.studio-play'),seek=player.querySelector('.studio-seek'),volume=player.querySelector('.studio-volume input'),hint=player.querySelector('.player-hint');
  volume.value=String(playbackVolume);
  function update(){const duration=audio.duration;seek.disabled=!Number.isFinite(duration)||duration<=0;seek.max=seek.disabled?100:duration;seek.value=audio.currentTime;seek.setAttribute('aria-valuetext',formatTime(audio.currentTime)+' / '+formatTime(duration));player.querySelector('.elapsed').textContent=formatTime(audio.currentTime);player.querySelector('.duration').textContent=formatTime(duration);}
  function state(){const playing=!audio.paused&&!audio.ended;play.setAttribute('aria-label',playing?'Ses kaydını duraklat':'Ses kaydını oynat');play.querySelector('span').textContent=playing?'Ⅱ':'▶';player.classList.toggle('playing',playing);setStudioPlaying(playing);hint.textContent=playing?'Şimdi kendine kulak ver':audio.ended?'Bölüm tamamlandı':'Dinlemek için oynat';}
  play.addEventListener('click',async()=>{if(!audio.paused){audio.pause();return;}try{await audio.play();}catch{state();hint.textContent='Ses açılamadı. Yeniden deneyebilirsin.';}});
  seek.addEventListener('input',()=>{if(Number.isFinite(audio.duration))audio.currentTime=Number(seek.value);update();});
  volume.addEventListener('input',()=>{playbackVolume=Number(volume.value);audio.volume=playbackVolume;audio.muted=playbackVolume===0;});
  ['loadedmetadata','durationchange','timeupdate'].forEach(name=>audio.addEventListener(name,update));
  ['play','pause','ended'].forEach(name=>audio.addEventListener(name,state));
  audio.addEventListener('error',()=>{state();hint.textContent='Ses dosyası yüklenemedi.';});
  audio.after(player);audio.controls=false;audio.hidden=true;update();state();
 }
 function chooseCategory(key){
  selectedCategory=key;available=templates.filter(t=>t.dataset.category===key);
  tabs.forEach(tab=>{const selected=tab.dataset.category===key;tab.setAttribute('aria-selected',String(selected));tab.tabIndex=selected?0:-1;});
  document.querySelector('#guide-panel').setAttribute('aria-labelledby',`tab-${key}`);
  topic.replaceChildren(...available.map(t=>{const option=document.createElement('option');option.value=t.dataset.id;option.textContent=t.dataset.title;return option;}));
  topic.disabled=!available.length;render();
 }
 tabs.forEach((tab,index)=>{tab.addEventListener('click',()=>chooseCategory(tab.dataset.category));tab.addEventListener('keydown',event=>{let target;if(event.key==='ArrowRight'||event.key==='ArrowDown')target=(index+1)%tabs.length;else if(event.key==='ArrowLeft'||event.key==='ArrowUp')target=(index+tabs.length-1)%tabs.length;else if(event.key==='Home')target=0;else if(event.key==='End')target=tabs.length-1;else return;event.preventDefault();tabs[target].focus();chooseCategory(tabs[target].dataset.category);});});
 topic.addEventListener('change',render);
 document.querySelector('#topic-prev').addEventListener('click',()=>{if(topic.selectedIndex>0){topic.selectedIndex--;render();}});
 document.querySelector('#topic-next').addEventListener('click',()=>{if(topic.selectedIndex<available.length-1){topic.selectedIndex++;render();}});
 document.addEventListener('visibilitychange',()=>{if(document.hidden)stopMedia();});
 chooseCategory(selectedCategory);
})();
