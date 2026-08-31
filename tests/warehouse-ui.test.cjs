const {test} = require('node:test');
const assert = require('node:assert/strict');
const vm = require('node:vm');
const fs = require('node:fs');
const path = require('node:path');
const source = fs.readFileSync(path.join(__dirname, '../public/assets/js/warehouse.js'), 'utf8');

function fixture({confirmed = false, sweetAlert = true, flash = {}} = {}) {
  const events = {}; const calls = []; let submissions = 0;
  const button = {disabled:false};
  const form = {dataset:{}, addEventListener:(name, handler) => { events[name]=handler; }, querySelector:() => button};
  const Swal = {fire:(options) => { calls.push(options); return Promise.resolve({isConfirmed:confirmed}); }};
  const context = {Promise, window:{warehouseFlash:flash, Swal:sweetAlert ? Swal : null, confirm:() => confirmed},
    document:{querySelectorAll:() => [form]}, HTMLFormElement:{prototype:{submit(){submissions++;}}}};
  vm.runInNewContext(source, context);
  return {events,calls,button,form,submissions:() => submissions};
}

test('cancel keeps the status form unchanged', async () => {
  const f=fixture(); await f.events.submit({preventDefault(){}}); await Promise.resolve();
  assert.equal(f.submissions(),0); assert.equal(f.button.disabled,false); assert.equal(f.calls[0].focusCancel,true);
});

test('confirmation submits once and disables the action', async () => {
  const f=fixture({confirmed:true}); await f.events.submit({preventDefault(){}}); await Promise.resolve();
  assert.equal(f.submissions(),1); assert.equal(f.button.disabled,true); assert.equal(f.form.dataset.confirmed,'1');
});

test('native confirmation fallback still protects the mutation', async () => {
  const f=fixture({confirmed:false,sweetAlert:false}); await f.events.submit({preventDefault(){}}); await Promise.resolve();
  assert.equal(f.submissions(),0);
});

test('flash messages are sent as text, never HTML', () => {
  const f=fixture({flash:{error:'<img src=x onerror=alert(1)>'}});
  assert.equal(f.calls[0].text,'<img src=x onerror=alert(1)>'); assert.equal(f.calls[0].html,undefined);
});
