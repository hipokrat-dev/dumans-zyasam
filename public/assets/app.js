'use strict';
const daily = document.querySelector('#daily');
const price = document.querySelector('#price');
const money = n => new Intl.NumberFormat('tr-TR', {style:'currency', currency:'TRY',maximumFractionDigits:0}).format(n);
function calculate(){
  const valid = daily.checkValidity() && price.checkValidity() && daily.value !== '' && price.value !== '';
  const cost = Number(daily.value) / 20 * Number(price.value);
  for(const [id,days] of [['yearly',365],['monthly',30],['fiveYears',1825]]) document.getElementById(id).textContent = valid ? money(cost * days) : '—';
}
if(daily && price){daily.addEventListener('input',calculate);price.addEventListener('input',calculate);calculate();}
const audio = document.querySelector('#ambience');
const audioButton = document.querySelector('#audioToggle');
let userChoseAudio = false;
if(audio){
 audio.volume = 0.25;
 const syncAudio = ()=>{audioButton.textContent=audio.paused?'♫ Sesi aç':'♫ Sesi kapat';audioButton.setAttribute('aria-pressed',String(!audio.paused));};
 audio.addEventListener('play',syncAudio);audio.addEventListener('pause',syncAudio);
 async function playAudio(){try{await audio.play();document.querySelector('#audioStatus').textContent='';}catch{document.querySelector('#audioStatus').textContent='Dinlemek için “Sesi aç”a dokun.';syncAudio();}}
 setTimeout(()=>{if(!userChoseAudio&&!document.hidden) playAudio();},2500);
 audioButton.addEventListener('click',()=>{userChoseAudio=true;if(audio.paused)playAudio();else audio.pause();});
 audio.addEventListener('error',()=>{document.querySelector('#audioStatus').textContent='Ses şu anda yüklenemedi.';});
 document.addEventListener('visibilitychange',()=>{if(document.hidden)audio.pause();});
}
const video=document.querySelector('#heroVideo');
if(video){
 const button=document.querySelector('#videoToggle');
 const sync=()=>button.textContent=video.paused?'Videoyu oynat':'Videoyu duraklat';
 if(matchMedia('(prefers-reduced-motion: reduce)').matches){video.autoplay=false;video.pause();}
 video.addEventListener('play',sync);video.addEventListener('pause',sync);sync();
 button.addEventListener('click',async()=>{if(video.paused){try{await video.play();}catch{button.textContent='Video yüklenemedi';}}else video.pause();});
 video.addEventListener('timeupdate',()=>{if(video.currentTime>=15)video.currentTime=0;});
}
