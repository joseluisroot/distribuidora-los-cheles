(() => {
  const flash = window.warehouseFlash || {};
  const message = flash.error || flash.success;
  if (message && window.Swal) {
    window.Swal.fire({icon: flash.error ? 'error' : 'success', title: flash.error ? 'No se guardó el cambio' : 'Cambio guardado', text: message, confirmButtonColor: '#164de3'});
  }
  document.querySelectorAll('[data-status-form]').forEach((form) => form.addEventListener('submit', (event) => {
    if (form.dataset.confirmed === '1') return;
    event.preventDefault();
    const confirmation = window.Swal
      ? window.Swal.fire({icon:'question',title:'¿Confirmar cambio de estado?',text:'La disponibilidad de toda la estructura dependiente puede cambiar.',showCancelButton:true,confirmButtonText:'Sí, continuar',cancelButtonText:'Cancelar',confirmButtonColor:'#164de3',focusCancel:true})
      : Promise.resolve({isConfirmed: window.confirm('¿Confirmar cambio de estado?')});
    confirmation.then((result) => {
      if (result.isConfirmed) {
        form.dataset.confirmed='1';
        const button = form.querySelector('button[type="submit"],button:not([type])');
        if (button) button.disabled=true;
        HTMLFormElement.prototype.submit.call(form);
      }
    });
  }));
})();
