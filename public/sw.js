const CACHE='dumansiz-pwa-v1';
const OFFLINE='/offline.html';
const SHELL=[OFFLINE,'/assets/pwa.css?v=1','/assets/icons/app-192.png'];
const PUBLIC_PAGES=new Set(['/','/index.php','/rehber.php','/etkiler.php','/poliklinikler.php']);
self.addEventListener('install',event=>{event.waitUntil(caches.open(CACHE).then(cache=>cache.addAll(SHELL)).then(()=>self.skipWaiting()));});
self.addEventListener('activate',event=>{event.waitUntil(caches.keys().then(keys=>Promise.all(keys.filter(key=>key.startsWith('dumansiz-pwa-')&&key!==CACHE).map(key=>caches.delete(key)))).then(()=>self.clients.claim()));});
self.addEventListener('fetch',event=>{
 const request=event.request,url=new URL(request.url);
 if(request.method!=='GET'||url.origin!==self.location.origin)return;
 // Never intercept administration, uploads, audio/video or other private routes.
 if(request.mode==='navigate'&&PUBLIC_PAGES.has(url.pathname)){
  event.respondWith(fetch(request).then(response=>response.status>=500?caches.match(OFFLINE).then(fallback=>fallback||response):response).catch(()=>caches.match(OFFLINE)));return;
 }
 if(SHELL.includes(url.pathname+url.search))event.respondWith(caches.match(request).then(cached=>cached||fetch(request)));
});
