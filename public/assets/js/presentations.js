(function(){
  document.querySelectorAll('[data-presentation-toggle]').forEach(function(button){
    button.addEventListener('click',function(){
      const editor=document.getElementById(button.getAttribute('data-presentation-toggle'));
      if(!editor)return;
      editor.hidden=!editor.hidden;
      button.textContent=editor.hidden?'Editar':'Cerrar';
    });
  });
  const flash=window.presentationFlash||{};
  if(!flash.success&&!flash.error)return;
  const options={icon:flash.error?'error':'success',title:flash.error?'No se pudo guardar':'Presentación guardada',text:flash.error||flash.success,confirmButtonText:'Entendido'};
  if(window.Swal&&typeof window.Swal.fire==='function')window.Swal.fire(options);
})(window);
