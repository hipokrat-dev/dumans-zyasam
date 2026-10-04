const fs=require('node:fs'),vm=require('node:vm'),assert=require('node:assert/strict');
const code=fs.readFileSync('public/assets/app.js','utf8');
function fixture(blocked=false){
 const events={},attrs={},docEvents={};let denied=blocked;
 const audio={paused:true,muted:false,volume:1,plays:0,addEventListener(k,f){events[k]=f},async play(){this.plays++;if(denied)throw Error('NotAllowedError');this.paused=false;events.play?.()},pause(){this.paused=true;events.pause?.()}};
 const button={setAttribute(k,v){attrs[k]=v},addEventListener(k,f){this[k]=f}};
 const status={textContent:''};
 const video={muted:true,paused:false,plays:0,addEventListener(){},async play(){this.plays++;this.paused=false},pause(){this.paused=true}};
 const document={hidden:false,querySelector:s=>({'#heroVideo':video,'#ambience':audio,'#audioToggle':button,'#audioStatus':status}[s]||null),addEventListener(k,f){docEvents[k]=f}};
 vm.runInNewContext(code,{document,Intl,matchMedia:()=>({matches:false})});
 return {audio,video,button,status,attrs,document,docEvents,allow(){denied=false}};
}
(async()=>{
 const a=fixture();await new Promise(setImmediate);
 assert.equal(a.audio.plays,1,'Sound starts without a timer');assert.equal(a.audio.paused,false);assert.equal(a.attrs['aria-pressed'],'true');assert.equal(a.video.muted,true,'Original video track stays muted');
 a.button.click();assert.equal(a.audio.paused,true);assert.equal(a.video.paused,false,'Muting does not pause background video');assert.equal(a.attrs['aria-label'],'Sesi aç');
 a.button.click();await new Promise(setImmediate);assert.equal(a.audio.paused,false);
 a.document.hidden=true;a.docEvents.visibilitychange();assert.equal(a.audio.paused,true);
 const b=fixture(true);await new Promise(setImmediate);assert.equal(b.audio.paused,true);assert.equal(b.attrs['aria-pressed'],'false');assert.equal(b.video.paused,false);assert.match(b.status.textContent,/simgesine/);
 b.allow();b.button.click();await new Promise(setImmediate);assert.equal(b.audio.paused,false);assert.equal(b.attrs['aria-label'],'Sesi kapat');assert.equal(b.status.textContent,'');
 console.log('Homepage audio checks passed');
})().catch(err=>{console.error(err);process.exitCode=1});
