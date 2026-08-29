const fs = require('node:fs');
const path = require('node:path');
const root = path.resolve(__dirname, '..');
const files = {
 'datatables.net/js/dataTables.min.js': 'datatables/dataTables.min.js',
 'datatables.net-dt/css/dataTables.dataTables.min.css': 'datatables/dataTables.min.css',
 'datatables.net/License.txt': 'datatables/License.txt',
 'sweetalert2/dist/sweetalert2.min.js': 'sweetalert2/sweetalert2.min.js',
 'sweetalert2/dist/sweetalert2.min.css': 'sweetalert2/sweetalert2.min.css',
 'sweetalert2/LICENSE': 'sweetalert2/LICENSE'
};
for (const [source, target] of Object.entries(files)) {
 const destination = path.join(root, 'public/assets/vendor', target);
 fs.mkdirSync(path.dirname(destination), {recursive: true});
 fs.copyFileSync(path.join(root, 'node_modules', source), destination);
}
