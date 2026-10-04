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
const video = document.querySelector('#heroVideo');
const audio = document.querySelector('#ambience');
const audioButton = document.querySelector('#audioToggle');
const audioStatus = document.querySelector('#audioStatus');
const sound = audio || video;
const reducedMotion = video && matchMedia('(prefers-reduced-motion: reduce)').matches;
let soundAction = 0;
if(video){
 if(reducedMotion){video.autoplay=false;video.pause();}
 video.addEventListener('timeupdate',()=>{if(video.currentTime>=15)video.currentTime=0;});
}
if(sound && audioButton){
 sound.volume=0.65;
 const audible=()=>!sound.paused&&!sound.muted&&sound.volume>0;
 const syncSound=()=>{
  const label=audible()?'Sesi kapat':'Sesi aç';
  audioButton.setAttribute('aria-label',label);audioButton.title=label;
  audioButton.setAttribute('aria-pressed',String(audible()));
 };
 ['play','pause','volumechange','ended'].forEach(event=>sound.addEventListener(event,syncSound));
 async function playSound(){
  const action=++soundAction;
  sound.muted=false;
  try{await sound.play();if(action===soundAction&&audioStatus)audioStatus.textContent='';}
  catch{
   if(action!==soundAction)return;
   if(audioStatus)audioStatus.textContent='Sesi açmak için ses simgesine dokun.';
   if(sound===video){video.muted=true;if(!reducedMotion&&!document.hidden){try{await video.play();}catch{}}}
  }
  syncSound();
 }
 function muteSound(){
  soundAction++;
  if(sound===video)sound.muted=true;else sound.pause();
  if(audioStatus)audioStatus.textContent='';syncSound();
 }
 audioButton.addEventListener('click',()=>{if(audible())muteSound();else playSound();});
 sound.addEventListener('error',()=>{if(audioStatus)audioStatus.textContent='Ses şu anda yüklenemedi.';syncSound();});
 document.addEventListener('visibilitychange',()=>{if(document.hidden)muteSound();});
 if(!document.hidden&&!(sound===video&&reducedMotion))playSound();
 syncSound();
}
if(video&&audio&&!reducedMotion){video.muted=true;video.play().catch(()=>{});}
