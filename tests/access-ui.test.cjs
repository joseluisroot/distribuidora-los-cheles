const {test} = require('node:test');
const assert = require('node:assert/strict');
const vm = require('node:vm');
const fs = require('node:fs');
const source = fs.readFileSync(require('node:path').join(__dirname, '../public/assets/js/access-ui.js'), 'utf8');
function fixture(confirmed, fallback = false) {
 const events = {}, calls = [], tables = []; let submitted = 0;
 const button = {disabled: false};
 const form = {reportValidity: () => true, querySelector: () => button,
  elements: {namedItem: name => ({value: name === 'reason' ? '<img src=x onerror=alert(1)>' : '2', selectedOptions: [{textContent: name}]})},
  addEventListener: (name, handler) => {events[name] = handler;}};
 const Swal = {fire: async options => {calls.push(options); return {isConfirmed: confirmed};}};
 const context = {window: {DataTable: true, Swal: fallback ? null : Swal, confirm: () => confirmed, addEventListener() {}},
  Swal, DataTable: function(table, options) { tables.push(options); },
  HTMLFormElement: {prototype: {submit() {submitted++;}}},
  document: {querySelectorAll: () => [{}], querySelector: selector => selector === '.access-edit' ? form : null}};
 vm.runInNewContext(source, context);
 return {events, calls, tables, button, count: () => submitted};
}
test('Cancel does not submit or disable the form; confirmation uses text, not HTML', async () => {
 const f = fixture(false); await f.events.submit({preventDefault() {}});
 assert.equal(f.count(), 0); assert.equal(f.button.disabled, false);
 assert.match(f.calls[0].text, /<img/); assert.equal(f.calls[0].html, undefined);
 assert.equal(f.calls[0].focusCancel, true);
});
test('Confirm submits once and disables the button', async () => {
 const f = fixture(true); await f.events.submit({preventDefault() {}});
 assert.equal(f.count(), 1); assert.equal(f.button.disabled, true);
});
test('Native confirmation fallback can cancel if SweetAlert fails to load', async () => {
 const f = fixture(false, true); await f.events.submit({preventDefault() {}}); assert.equal(f.count(), 0);
});
test('Tables do not persist access data and preserve server ordering initially', () => {
 const f = fixture(false); assert.equal(f.tables[0].stateSave, false);
 assert.equal(f.tables[0].order.length, 0); assert.equal(f.tables[0].language.search, 'Buscar:');
});
