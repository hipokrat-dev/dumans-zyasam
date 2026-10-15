const assert=require('node:assert/strict');
const {impactTotals,quitSavings}=require('../public/assets/impact-math.js');
let t=impactTotals(1,5,100,5);assert.equal(t.money,182500);assert.equal(t.cigarettes,36500);assert.equal(t.packYears,5);assert.equal(t.hours,36500*5/60);
assert.equal(impactTotals(0,40,1000,15).money,0);assert.equal(impactTotals(3,0,1000,15).days,0);
assert.equal(impactTotals(.5,10,100,5).packYears,5);
assert.equal(impactTotals(3,40,1000,15).money,43800000);
for(const args of [[-1,5,100,5],[1,-1,100,5],[1,5,NaN,5],[1,5,100,0],[4,5,100,5]])assert.equal(impactTotals(...args),null);
console.log('Impact calculations passed');

// Unit conversions are equivalents, not additive parts.
assert.ok(Math.abs(t.days-t.hours/24)<1e-10);
assert.ok(Math.abs(t.months-t.days/30)<1e-10);
assert.ok(Math.abs(t.timeYears-t.days/365)<1e-10);
assert.equal(quitSavings(1,1,100,5).money,100);
assert.equal(quitSavings(1,30,100,5).money,3000);
assert.equal(quitSavings(1,365,100,5).money,36500);
assert.equal(quitSavings(0,365,100,5).hours,0);
assert.equal(quitSavings(1,30,0,5).money,0);
assert.equal(quitSavings(1,1,100,6).hours,2);
assert.equal(quitSavings(1,-1,100,5),null);
assert.equal(quitSavings(1,Infinity,100,5),null);
assert.equal(quitSavings(4,365,100,5),null);
console.log('Time equivalents and quit savings passed');
