/* Aggregate listening counts only; no names, addresses or persistent visitor IDs. */
(() => {
 function listenedSeconds(previous,current,wallSeconds,seeking,paused,muted){
  if(seeking||paused||muted||previous===null)return 0;
  const delta=current-previous;
  if(delta<=0||wallSeconds<=0||wallSeconds>2||delta>wallSeconds*4+.5)return 0;
  return Math.min(delta,wallSeconds);
 }
 function track(audio,item){
  let seconds=0,previous=null,last=performance.now(),ticket='',done=false,pending=false,retryAt=0;
  const payload={id:item.id,audio:item.audio};
  async function send(action){
   if(pending||done||performance.now()<retryAt)return;
   pending=true;
   try{
    const response=await fetch('dinlenme.php',{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json'},body:JSON.stringify({...payload,action,ticket}),keepalive:true});
    if(!response.ok)throw new Error('unavailable');
    const result=await response.json();
    if(result.ticket)ticket=result.ticket;
    if(result.counted)done=true;
   }catch{retryAt=performance.now()+5000;}finally{pending=false;}
  }
  audio.addEventListener('playing',()=>{previous=audio.currentTime;last=performance.now();if(!ticket)send('start');});
  audio.addEventListener('seeking',()=>{previous=null;});
  audio.addEventListener('pause',()=>{previous=null;});
  audio.addEventListener('timeupdate',()=>{
   const now=performance.now();
   seconds+=listenedSeconds(previous,audio.currentTime,(now-last)/1000,audio.seeking,audio.paused,audio.muted||audio.volume===0);
   previous=audio.currentTime;last=now;
   if(!audio.paused&&!audio.seeking&&!done){if(!ticket)send('start');else if(seconds>=10)send('listen');}
  });
 }
 if(typeof module!=='undefined'&&module.exports)module.exports={listenedSeconds};
 else window.DumansizListening={track};
})();
