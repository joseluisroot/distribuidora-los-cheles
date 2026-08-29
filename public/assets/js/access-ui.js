(() => {
 if (window.DataTable) document.querySelectorAll('.access-page table').forEach(table => {
  new DataTable(table, {
   order: [], pageLength: 10, lengthMenu: [10, 25, 50], stateSave: false,
   language: {search: 'Buscar:', lengthMenu: 'Mostrar _MENU_ registros',
    info: 'Mostrando _START_ a _END_ de _TOTAL_ registros', infoEmpty: 'Sin registros',
    infoFiltered: '(de _MAX_ registros cargados)', emptyTable: 'No hay registros disponibles',
    zeroRecords: 'No hay coincidencias',
    paginate: {first: 'Primera', previous: 'Anterior', next: 'Siguiente', last: 'Última'},
    aria: {orderable: 'Ordenar por esta columna', orderableReverse: 'Invertir orden'}}
  });
 });
 const form = document.querySelector('.access-edit');
 if (form) {
  let awaiting = false;
  form.addEventListener('submit', async event => {
   event.preventDefault();
   if (awaiting || !form.reportValidity()) return;
   const field = name => form.elements.namedItem(name);
   const label = name => field(name).selectedOptions[0].textContent;
   const text = `${label('effect')}: ${label('permission')}. Destino: ${label('subject_type')} #${field('subject_id').value}. Ámbito: ${field('scope').value}. Motivo: ${field('reason').value}`;
   awaiting = true;
   try {
    const confirmed = window.Swal ? (await Swal.fire({title: '¿Confirmar cambio de acceso?', text,
     icon: 'warning', showCancelButton: true, confirmButtonText: 'Confirmar cambio',
     cancelButtonText: 'Cancelar', focusCancel: true, confirmButtonColor: '#1d4ed8'})).isConfirmed : window.confirm(text);
    if (confirmed) {
     form.querySelector('button').disabled = true;
     HTMLFormElement.prototype.submit.call(form);
    }
   } finally { awaiting = false; }
  });
  window.addEventListener('pageshow', () => { form.querySelector('button').disabled = false; });
 }
 // Keep inline feedback: it remains readable without JavaScript or after dismissal.
 if (window.Swal) {
  const feedback = document.querySelector('.alert-error, .alert-success');
  if (feedback) {
   const error = feedback.classList.contains('alert-error');
   Swal.fire({text: feedback.textContent.trim(), icon: error ? 'error' : 'success',
    toast: !error, position: error ? 'center' : 'top-end', showConfirmButton: true,
    confirmButtonText: 'Entendido', confirmButtonColor: '#1d4ed8'});
  }
 }
})();
