const assert=require('node:assert/strict'),fs=require('node:fs'),vm=require('node:vm');
const manifest=JSON.parse(fs.readFileSync('public/manifest.webmanifest','utf8'));
assert.equal(manifest.display,'standalone');assert.equal(manifest.scope,'/');
for(const size of [192,512]){const icon=manifest.icons.find(i=>i.sizes===`${size}x${size}`);assert(icon);const png=fs.readFileSync('public'+icon.src);assert.equal(png.readUInt32BE(16),size);assert.equal(png.readUInt32BE(20),size);}
const handlers={},cache=new Map(),deleted=[];let fail=false,status=200,fetches=0,claimed=false;
const offline=new Response('offline page');
const context={URL,Set,Promise,self:{location:{origin:'https://example.test'},addEventListener:(name,fn)=>handlers[name]=fn,skipWaiting:async()=>{},clients:{claim:async()=>{claimed=true}}},caches:{open:async()=>({addAll:async urls=>{urls.forEach(url=>cache.set(url,offline))}}),keys:async()=>['dumansiz-pwa-v0','unrelated-cache'],delete:async name=>deleted.push(name),match:async input=>cache.get(typeof input==='string'?input:new URL(input.url).pathname+new URL(input.url).search)?.clone()},fetch:async()=>{fetches++;if(fail)throw Error('offline');return new Response('fresh',{status});}};
vm.runInNewContext(fs.readFileSync('public/sw.js','utf8'),context);
async function lifecycle(name){let result;handlers[name]({waitUntil:p=>result=p});await result;}
function request(path,mode='navigate',method='GET'){let result;handlers.fetch({request:{url:new URL(path,'https://example.test').href,mode,method},respondWith:p=>result=p});return result;}
(async()=>{
 await lifecycle('install');await lifecycle('activate');assert(claimed);assert.deepEqual(deleted,['dumansiz-pwa-v0']);
 assert.equal(await (await request('/rehber.php')).text(),'fresh');
 fail=true;assert.equal(await (await request('/rehber.php')).text(),'offline page');assert.equal(await (await request('/poliklinikler.php?district=Nil%C3%BCfer')).text(),'offline page');
 for(const path of ['/admin.php','/sifre-yenile.php','/setup.php','/uploads/example.mp3','/assets/media/ambience.mp3','https://other.test/'])assert.equal(request(path),undefined,path);
 assert.equal(request('/rehber.php','navigate','POST'),undefined);
 fail=false;status=503;assert.equal(await (await request('/')).text(),'offline page');
 const count=fetches;assert(await request('/assets/pwa.css?v=1','cors'));assert.equal(fetches,count);
 assert(!cache.has('/rehber.php'));assert.equal(cache.size,3);
 console.log('PWA manifest, icon sizes, offline navigation and private/media exclusions passed');
})().catch(error=>{console.error(error);process.exitCode=1});

const rootRules=fs.readFileSync(".htaccess","utf8");
for(const route of ["manifest\\.webmanifest","sw\\.js","offline\\.html"])assert.ok(rootRules.includes(route), `Root routing missing ${route}`);
