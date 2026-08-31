(function(){
  const flash=window.commerceFlash||{};
  if((flash.success||flash.error)&&window.Swal)window.Swal.fire({icon:flash.error?'error':'success',title:flash.error?'No se pudo completar':'Operación completada',text:flash.error||flash.success,confirmButtonText:'Entendido'});
  document.querySelectorAll('[data-delete-product]').forEach(function(form){form.addEventListener('submit',function(event){
    event.preventDefault();const name=form.getAttribute('data-delete-product')||'este producto';
    const proceed=function(){form.submit();};
    if(!window.Swal){if(window.confirm('¿Eliminar '+name+'?'))proceed();return;}
    window.Swal.fire({icon:'warning',title:'¿Eliminar producto?',text:name+' dejará de estar disponible. Esta acción se registrará como baja lógica.',showCancelButton:true,confirmButtonText:'Sí, eliminar',cancelButtonText:'Cancelar',confirmButtonColor:'#b42318'}).then(function(result){if(result.isConfirmed)proceed();});
  });});
})();
