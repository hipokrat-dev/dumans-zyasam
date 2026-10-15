'use strict';
function impactTotals(packs, years, price, minutes) {
 if (![packs,years,price,minutes].every(Number.isFinite) || packs<0 || packs>3 || years<0 || years>40 || price<0 || price>1000 || minutes<1 || minutes>15) return null;
 const cigarettes=packs*20*365*years;
 return {money:packs*365*years*price,hours:cigarettes*minutes/60,days:cigarettes*minutes/1440,months:cigarettes*minutes/43200,timeYears:cigarettes*minutes/525600,packYears:packs*years,cigarettes};
}
function quitSavings(packs, days, price, minutes) {
 if (!Number.isFinite(days) || days<0 || days>14600) return null;
 return impactTotals(packs,days/365,price,minutes);
}
if(typeof module!=='undefined') module.exports={impactTotals,quitSavings};
