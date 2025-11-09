<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>

<h1 class="text-2xl font-bold mb-4">Nuevo Producto</h1>

<?php
$old = fn($k,$d='') => old($k) ?? $d;
$errors = session('errors') ?? [];
?>

<?php if (!empty($errors)): ?>
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-red-700">
        <div class="font-semibold mb-1">Por favor corrige los siguientes campos:</div>
        <ul class="list-disc list-inside text-sm">
            <?php foreach ($errors as $e): ?>
                <li><?= esc($e) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post" action="<?= site_url('productos/crear') ?>" class="card p-4 space-y-4" enctype="multipart/form-data" novalidate>
    <?= csrf_field() ?>

    <div class="grid md:grid-cols-2 gap-4">
        <div>
            <label class="block mb-1 font-medium" for="sku">SKU</label>
            <input id="sku" name="sku" class="input w-full" required value="<?= esc($old('sku')) ?>" maxlength="50" placeholder="Ej.: P-001">
            <p class="text-xs text-slate-500 mt-1">Máx. 50 caracteres. Debe ser único.</p>
        </div>

        <div>
            <label class="block mb-1 font-medium" for="nombre">Nombre</label>
            <input id="nombre" name="nombre" class="input w-full" required value="<?= esc($old('nombre')) ?>" maxlength="150" placeholder="Ej.: Juego de vasos 12oz">
            <div class="text-xs text-slate-500 mt-1">
                Slug (prevista): <span id="slug-preview" class="font-mono text-slate-700">—</span>
            </div>
        </div>

        <div class="md:col-span-2">
            <label class="block mb-1 font-medium" for="descripcion">Descripción</label>
            <textarea id="descripcion" name="descripcion" class="input w-full" rows="3" placeholder="Detalles, material, medidas..."><?= esc($old('descripcion')) ?></textarea>
        </div>

        <div>
            <label class="block mb-1 font-medium" for="precio_base">Precio Base</label>
            <input id="precio_base" type="number" step="0.01" min="0" name="precio_base" class="input w-full" required
                   value="<?= esc($old('precio_base','0.00')) ?>" inputmode="decimal" placeholder="0.00">
        </div>

        <div>
            <label class="block mb-1 font-medium" for="stock">Stock Inicial</label>
            <input id="stock" type="number" min="0" step="1" name="stock" class="input w-full" value="<?= esc($old('stock','0')) ?>" placeholder="0">
        </div>

        <div class="md:col-span-2">
            <label class="block mb-1 font-medium" for="imagen_url">Imagen (URL) <span class="text-slate-400">(opcional)</span></label>
            <input id="imagen_url" name="imagen_url" class="input w-full" value="<?= esc($old('imagen_url')) ?>" placeholder="https://.../producto.jpg">
            <div class="mt-2 flex items-start gap-4">
                <img id="preview-img" src="<?= $old('imagen_url') ? esc($old('imagen_url')) : base_url('assets/placeholder-product.png') ?>" alt="Preview" class="w-full max-w-xs h-32 object-cover rounded-md border">
                <div class="text-xs text-slate-500">
                    También puedes subir imágenes desde tu equipo aquí abajo. La primera quedará como principal.
                </div>
            </div>
        </div>

        <!-- Subida de imágenes locales -->
        <div class="md:col-span-2">
            <label class="block mb-1 font-medium" for="imagenes">Imágenes (JPG/PNG/WebP) — puedes seleccionar varias</label>
            <input id="imagenes" name="imagenes[]" type="file" class="input w-full" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" multiple>
            <div id="imagenes-preview" class="mt-2 grid grid-cols-2 md:grid-cols-4 gap-2"></div>
            <p class="text-xs text-slate-500 mt-1">Máx. 5 archivos, 4MB c/u. Se generará miniatura 160x160.</p>
        </div>

        <div class="flex items-center gap-2 md:col-span-1 mt-2">
            <label class="inline-flex items-center gap-2">
                <input type="checkbox" name="is_activo" value="1" <?= $old('is_activo','1') ? 'checked' : '' ?>>
                <span>Activo</span>
            </label>
        </div>
    </div>

    <div class="flex gap-2 pt-2">
        <button class="btn btn-primary">Guardar</button>
        <a href="<?= site_url('productos') ?>" class="btn btn-outline">Cancelar</a>
    </div>
</form>

<style>
    .thumb {
        @apply w-full h-24 object-cover rounded-md border;
    }
</style>

<script>
    (function(){
        const nombre = document.getElementById('nombre');
        const slugPv = document.getElementById('slug-preview');
        const toSlug = (t) => {
            t = (t || '').normalize('NFD').replace(/[\u0300-\u036f]/g,'');
            t = t.toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-+|-+$/g,'');
            return t || 'producto';
        };
        const upd = () => slugPv.textContent = toSlug(nombre.value);
        upd(); nombre.addEventListener('input', upd);

        const url = document.getElementById('imagen_url');
        const img = document.getElementById('preview-img');
        url.addEventListener('input', () => {
            const v = (url.value||'').trim();
            img.src = v ? v : '<?= base_url('assets/placeholder-product.png') ?>';
        });

        // Previews locales
        const inputFiles = document.getElementById('imagenes');
        const grid = document.getElementById('imagenes-preview');
        inputFiles.addEventListener('change', () => {
            grid.innerHTML = '';
            const files = Array.from(inputFiles.files || []).slice(0,5);
            files.forEach(f => {
                if (!f.type.match(/^image\/(jpeg|png|webp)$/)) return;
                const r = new FileReader();
                r.onload = e => {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.alt = f.name;
                    img.className = 'thumb';
                    grid.appendChild(img);
                };
                r.readAsDataURL(f);
            });
        });

        // Clamps simples
        const precio = document.getElementById('precio_base');
        const stock  = document.getElementById('stock');
        precio.addEventListener('input', () => {
            let v = parseFloat(precio.value || '0'); if (isNaN(v) || v < 0) v = 0; precio.value = v.toFixed(2);
        }, {passive:true});
        stock.addEventListener('input', () => {
            let v = parseInt(stock.value || '0', 10); if (isNaN(v) || v < 0) v = 0; stock.value = String(v);
        }, {passive:true});
    })();
</script>

<?= $this->endSection() ?>
