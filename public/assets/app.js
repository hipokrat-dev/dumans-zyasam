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
const audioStatus = document.querySelector('#audioStatus');
let userChoseAudio = false;
if(audio && audioButton){
 audio.volume = 0.65;
 const syncAudio = () => {
  const label = audio.paused ? 'Sesi aç' : 'Sesi kapat';
  audioButton.setAttribute('aria-label', label);
  audioButton.title = label;
  audioButton.setAttribute('aria-pressed', String(!audio.paused));
 };
 audio.addEventListener('play',syncAudio); audio.addEventListener('pause',syncAudio);
 async function playAudio(){
  try { await audio.play(); if(audioStatus) audioStatus.textContent=''; }
  catch { if(audioStatus) audioStatus.textContent='Dinlemek için ses simgesine dokun.'; syncAudio(); }
 }
 setTimeout(()=>{if(!userChoseAudio && !document.hidden) playAudio();},2500);
 audioButton.addEventListener('click',()=>{userChoseAudio=true;if(audio.paused)playAudio();else audio.pause();});
 audio.addEventListener('error',()=>{if(audioStatus) audioStatus.textContent='Ses şu anda yüklenemedi.';});
 document.addEventListener('visibilitychange',()=>{if(document.hidden)audio.pause();});
 syncAudio();
}
const video=document.querySelector('#heroVideo');
if(video){
 if(matchMedia('(prefers-reduced-motion: reduce)').matches){video.autoplay=false;video.pause();}
 video.addEventListener('timeupdate',()=>{if(video.currentTime>=15)video.currentTime=0;});
}
