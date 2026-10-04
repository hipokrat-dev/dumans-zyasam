const assert=require('node:assert/strict');
const {impactTotals}=require('../public/assets/impact-math.js');
let t=impactTotals(1,5,100,5);assert.equal(t.money,182500);assert.equal(t.cigarettes,36500);assert.equal(t.packYears,5);assert.equal(t.hours,36500*5/60);
assert.equal(impactTotals(0,40,1000,15).money,0);assert.equal(impactTotals(3,0,1000,15).days,0);
assert.equal(impactTotals(.5,10,100,5).packYears,5);
assert.equal(impactTotals(3,40,1000,15).money,43800000);
for(const args of [[-1,5,100,5],[1,-1,100,5],[1,5,NaN,5],[1,5,100,0],[4,5,100,5]])assert.equal(impactTotals(...args),null);
console.log('Impact calculations passed');
