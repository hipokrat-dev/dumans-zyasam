const assert=require('node:assert/strict');
const {listenedSeconds:seconds}=require('../public/assets/listening.js');
assert.equal(seconds(0,.25,.25,false,false,false),.25);
assert.equal(seconds(0,60,.25,true,false,false),0);
assert.equal(seconds(0,60,.25,false,false,false),0);
assert.equal(seconds(0,1,1,false,true,false),0);
assert.equal(seconds(0,1,1,false,false,true),0);
assert.equal(seconds(null,1,1,false,false,false),0);
assert.equal(seconds(5,1,.25,false,false,false),0);
assert.equal(seconds(0,30,30,false,false,false),0);
assert.equal(seconds(0,2,1,false,false,false),1);
console.log('Listening time checks passed');
const vm=require('node:vm'),fs=require('node:fs');
(async()=>{
 let now=0,requests=[];
 const context={window:{},performance:{now:()=>now},fetch:async(url,options)=>{requests.push(JSON.parse(options.body));return {ok:true,json:async()=>requests.length===1?{ticket:'test-ticket',counted:false}:{counted:true}};}};
 vm.runInNewContext(fs.readFileSync('public/assets/listening.js','utf8'),context);
 const listeners={},audio={currentTime:0,paused:false,seeking:false,muted:false,volume:1,addEventListener:(event,handler)=>listeners[event]=handler};
 const settle=()=>new Promise(resolve=>setImmediate(resolve));
 context.window.DumansizListening.track(audio,{id:'episode',audio:'uploads/example.mp3'});
 listeners.playing();await settle();
 for(let i=0;i<9;i++){now+=1000;audio.currentTime++;listeners.timeupdate();}
 assert.equal(requests.length,1,'Quick or partial listening must not count');
 audio.seeking=true;listeners.seeking();audio.currentTime=60;listeners.timeupdate();audio.seeking=false;
 now+=1000;audio.currentTime++;listeners.timeupdate();await settle();
 assert.equal(requests.length,2);assert.equal(requests[1].action,'listen');assert.equal(requests[1].ticket,'test-ticket');
 now+=1000;audio.currentTime++;listeners.timeupdate();await settle();assert.equal(requests.length,2,'No repeated reports once counted');
 console.log('Playback reporting integration passed');
})().catch(error=>{console.error(error);process.exitCode=1;});
