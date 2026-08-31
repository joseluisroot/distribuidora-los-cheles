(function(){
  const name=document.getElementById('nombre'),slug=document.getElementById('slug-preview'),url=document.getElementById('imagen_url'),preview=document.getElementById('preview-img'),files=document.getElementById('imagenes'),grid=document.getElementById('imagenes-preview');
  const toSlug=value=>(value||'').normalize('NFD').replace(/[\u0300-\u036f]/g,'').toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-+|-+$/g,'')||'producto';
  if(name&&slug){const update=()=>slug.textContent=toSlug(name.value);update();name.addEventListener('input',update);}
  if(url&&preview)url.addEventListener('input',()=>{preview.src=url.value.trim()||preview.dataset.fallback||'/assets/placeholder-product.png';});
  if(files&&grid)files.addEventListener('change',()=>{grid.innerHTML='';Array.from(files.files||[]).slice(0,5).forEach(file=>{if(!/^image\/(jpeg|png|webp)$/.test(file.type))return;const reader=new FileReader();reader.onload=e=>{const image=document.createElement('img');image.src=e.target.result;image.alt=file.name;grid.appendChild(image);};reader.readAsDataURL(file);});});
})();
