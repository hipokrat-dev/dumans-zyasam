(() => {
 const mode=matchMedia('(display-mode: standalone)');
 const isStandalone=()=>mode.matches||navigator.standalone===true;
 const install=document.querySelector('#pwa-install'),dialog=document.querySelector('#pwa-install-dialog'),confirm=document.querySelector('#pwa-install-confirm');
 let installEvent=null;
 const updateMode=()=>{document.body.classList.toggle('pwa-standalone',isStandalone());if(install)install.hidden=isStandalone();};
 if(install){const target=document.querySelector('.studio-mobile-menu nav')||document.querySelector('footer');target?.append(install);}
 updateMode();mode.addEventListener('change',updateMode);
 if(/iPad|iPhone|iPod/.test(navigator.userAgent)||(navigator.platform==='MacIntel'&&navigator.maxTouchPoints>1))document.querySelector('#pwa-install-help').textContent='Paylaş menüsünü aç → “Ana Ekrana Ekle”yi seç → “Ekle”ye dokun. “Web Uygulaması Olarak Aç” seçeneği görünürse açık bırak.';
 install?.addEventListener('click',()=>dialog.showModal());
 document.querySelector('#pwa-install-close')?.addEventListener('click',()=>dialog.close());
 window.addEventListener('beforeinstallprompt',event=>{event.preventDefault();installEvent=event;confirm.hidden=false;});
 confirm?.addEventListener('click',async()=>{if(!installEvent)return;const prompt=installEvent;installEvent=null;confirm.hidden=true;try{await prompt.prompt();const result=await prompt.userChoice;if(result.outcome==='accepted')dialog.close();}catch{ /* Browser menu instructions remain available. */ }});
 window.addEventListener('appinstalled',()=>{installEvent=null;if(install)install.hidden=true;confirm.hidden=true;dialog.close();});
 if('serviceWorker' in navigator&&window.isSecureContext){navigator.serviceWorker.register('/sw.js',{scope:'/',updateViaCache:'none'}).then(async registration=>{await registration.update();await navigator.serviceWorker.ready;document.body.dataset.pwaReady='true';}).catch(()=>{});}
})();
