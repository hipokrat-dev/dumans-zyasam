(() => {
 const tabs=[...document.querySelectorAll('[data-category][role=tab]')];
 const templates=[...document.querySelectorAll('template[data-id]')];
 const topic=document.querySelector('#topic'),content=document.querySelector('#topic-content');
 let selectedCategory='tetikleyiciler',available=[];
 function stopMedia(){document.querySelectorAll('#topic-content audio,#topic-content video').forEach(media=>media.pause());}
 function render(){
  stopMedia();content.replaceChildren();const template=available[topic.selectedIndex];
  if(template)content.append(template.content.cloneNode(true));else{const p=document.createElement('p');p.textContent='Bu bölüme henüz başlık eklenmedi.';content.append(p);}
  const dialog=content.querySelector('.topic-video-dialog');if(dialog){content.querySelector('.topic-video-open').addEventListener('click',()=>{stopMedia();dialog.showModal();});content.querySelector('.topic-video-close').addEventListener('click',()=>dialog.close());dialog.addEventListener('close',stopMedia);}
  const media=[...content.querySelectorAll('audio,video')];media.forEach(item=>item.addEventListener('play',()=>media.filter(other=>other!==item).forEach(other=>other.pause())));
  document.querySelector('#topic-count').textContent=available.length?`${topic.selectedIndex+1} / ${available.length}`:'0 / 0';
  document.querySelector('#topic-prev').disabled=topic.selectedIndex<=0;
  document.querySelector('#topic-next').disabled=topic.selectedIndex>=available.length-1;
 }
 function chooseCategory(key){
  selectedCategory=key;available=templates.filter(t=>t.dataset.category===key);
  tabs.forEach(tab=>{const selected=tab.dataset.category===key;tab.setAttribute('aria-selected',String(selected));tab.tabIndex=selected?0:-1;});
  document.querySelector('#guide-panel').setAttribute('aria-labelledby',`tab-${key}`);
  topic.replaceChildren(...available.map(t=>{const option=document.createElement('option');option.value=t.dataset.id;option.textContent=t.dataset.title;return option;}));
  topic.disabled=!available.length;render();
 }
 tabs.forEach((tab,index)=>{tab.addEventListener('click',()=>chooseCategory(tab.dataset.category));tab.addEventListener('keydown',event=>{let target;if(event.key==='ArrowRight')target=(index+1)%tabs.length;else if(event.key==='ArrowLeft')target=(index+tabs.length-1)%tabs.length;else if(event.key==='Home')target=0;else if(event.key==='End')target=tabs.length-1;else return;event.preventDefault();tabs[target].focus();chooseCategory(tabs[target].dataset.category);});});
 topic.addEventListener('change',render);
 document.querySelector('#topic-prev').addEventListener('click',()=>{if(topic.selectedIndex>0){topic.selectedIndex--;render();}});
 document.querySelector('#topic-next').addEventListener('click',()=>{if(topic.selectedIndex<available.length-1){topic.selectedIndex++;render();}});
 document.addEventListener('visibilitychange',()=>{if(document.hidden)stopMedia();});
 chooseCategory(selectedCategory);
})();
