<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>

<h1 class="text-2xl font-bold mb-4">Editar Producto</h1>

<?php
$errors = session('errors') ?? [];
$stock = (int)($inventario['stock'] ?? 0);
?>

<?php if (!empty($errors)): ?>
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-red-700">
        <div class="font-semibold mb-1">Corrige los siguientes campos:</div>
        <ul class="list-disc list-inside text-sm">
            <?php foreach ($errors as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post" action="<?= site_url('productos/editar/'.$producto['id']) ?>" class="card p-4 space-y-4" novalidate>
    <?= csrf_field() ?>

    <div class="grid md:grid-cols-2 gap-4">
        <div>
            <label class="block mb-1 font-medium" for="sku">SKU</label>
            <input id="sku" name="sku" class="input w-full" value="<?= esc($producto['sku']) ?>" required maxlength="50">
            <p class="text-xs text-slate-500 mt-1">Debe ser único.</p>
        </div>

        <div>
            <label class="block mb-1 font-medium" for="nombre">Nombre</label>
            <input id="nombre" name="nombre" class="input w-full" value="<?= esc($producto['nombre']) ?>" required maxlength="150">
            <div class="text-xs text-slate-500 mt-1">
                Slug (actual): <span class="font-mono"><?= esc($producto['slug'] ?? '—') ?></span>
            </div>
        </div>

        <div class="md:col-span-2">
            <label class="block mb-1 font-medium" for="descripcion">Descripción</label>
            <textarea id="descripcion" name="descripcion" class="input w-full" rows="3"><?= esc($producto['descripcion']) ?></textarea>
        </div>

        <div>
            <label class="block mb-1 font-medium" for="precio_base">Precio Base</label>
            <input id="precio_base" type="number" step="0.01" min="0" name="precio_base" class="input w-full" required
                   value="<?= number_format((float)$producto['precio_base'],2,'.','') ?>">
        </div>

        <div>
            <label class="block mb-1 font-medium" for="stock">Stock</label>
            <input id="stock" type="number" min="0" step="1" name="stock" class="input w-full" value="<?= $stock ?>">
        </div>

        <div class="md:col-span-2">
            <label class="block mb-1 font-medium" for="imagen_url">Imagen (URL) <span class="text-slate-400">(opcional)</span></label>
            <input id="imagen_url" name="imagen_url" class="input w-full"
                   value="<?= esc($producto['imagen_url'] ?? '') ?>" placeholder="https://.../producto.jpg">
            <div class="mt-2 flex items-start gap-4">
                <?php
                $previewUrl = $producto['imagen_url'] ?: base_url('assets/placeholder-product.png');
                ?>
                <img id="preview-img" src="<?= esc($previewUrl) ?>" alt="Preview" class="w-full max-w-xs h-32 object-cover rounded-md border">
                <p class="text-xs text-slate-500">
                    Esta imagen se usa como fallback. Puedes administrar la galería abajo (subir, principal, eliminar).
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 md:col-span-1 mt-2">
            <label class="inline-flex items-center gap-2">
                <input type="checkbox" name="is_activo" value="1" <?= $producto['is_activo'] ? 'checked' : '' ?>>
                <span>Activo</span>
            </label>
        </div>
    </div>

    <div class="flex gap-2 pt-2">
        <button class="btn btn-primary">Actualizar</button>
        <a href="<?= site_url('productos') ?>" class="btn btn-outline">Cancelar</a>
    </div>
</form>

<!-- === Galería de imágenes === -->
<div class="card p-4 mt-6">
    <div class="flex items-center justify-between mb-3">
        <h2 class="text-lg font-semibold">Imágenes del producto</h2>
        <a href="<?= site_url('productos/'.$producto['id'].'/imagenes') ?>" class="btn btn-outline">
            Pantalla completa de imágenes
        </a>
    </div>

    <?php
    $imgs = (new \App\Models\ProductImageModel())->byProducto((int)$producto['id']);
    ?>

    <?php if (empty($imgs)): ?>
        <p class="text-sm text-slate-500">Sin imágenes. Usa el cargador de abajo o la URL de imagen.</p>
    <?php else: ?>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <?php foreach ($imgs as $im): ?>
                <div class="border rounded-lg p-2">
                    <img src="<?= base_url(esc($im['thumb_path'] ?: $im['path'])) ?>" alt="<?= esc($im['alt'] ?? $producto['nombre']) ?>"
                         class="w-full h-28 object-cover rounded-md mb-2">
                    <div class="flex items-center justify-between text-xs">
            <span class="badge <?= $im['is_primary'] ? '' : 'bg-slate-100' ?>">
              <?= $im['is_primary'] ? 'Principal' : 'Secundaria' ?>
            </span>
                        <span>#<?= (int)$im['id'] ?></span>
                    </div>
                    <div class="mt-2 grid grid-cols-2 gap-2">
                        <form method="post" action="<?= site_url('productos/'.$producto['id'].'/imagenes/'.$im['id'].'/principal') ?>">
                            <?= csrf_field() ?>
                            <button class="btn btn-outline w-full" <?= $im['is_primary'] ? 'disabled' : '' ?>>Principal</button>
                        </form>
                        <form method="post" action="<?= site_url('productos/'.$producto['id'].'/imagenes/'.$im['id'].'/eliminar') ?>"
                              onsubmit="return confirm('¿Eliminar esta imagen?')">
                            <?= csrf_field() ?>
                            <button class="btn btn-outline w-full">Eliminar</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <hr class="my-4">

    <h3 class="font-medium mb-2">Subir nuevas imágenes</h3>
    <div class="space-y-2">
        <input id="edit-files" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" multiple class="input w-full">
        <div id="edit-previews" class="grid grid-cols-2 md:grid-cols-4 gap-2"></div>
        <div class="flex gap-2">
            <button id="btn-upload" class="btn btn-primary">Subir seleccionadas</button>
            <span id="upload-msg" class="text-sm text-slate-500"></span>
        </div>
        <p class="text-xs text-slate-500">Se enviarán una por una al endpoint existente. Máx. 4MB c/u. La primera del producto sin imágenes se marcará como principal.</p>
    </div>
</div>

<style>
    .thumb { width: 100%; height: 96px; object-fit: cover; border-radius: .5rem; border: 1px solid #e5e7eb; }
    .btn[disabled], .btn.is-disabled { opacity: .5; pointer-events: none; cursor: not-allowed; filter: grayscale(30%); }
</style>

<script>
    // Preview de imagen URL
    (function(){
        const url = document.getElementById('imagen_url');
        const img = document.getElementById('preview-img');
        if (url && img) {
            url.addEventListener('input', () => {
                const v = (url.value||'').trim();
                img.src = v ? v : '<?= base_url('assets/placeholder-product.png') ?>';
            });
        }
    })();

    // Previews y subida múltiple (a /productos/{id}/imagenes/subir)
    (function(){
        const input = document.getElementById('edit-files');
        const grid  = document.getElementById('edit-previews');
        const btn   = document.getElementById('btn-upload');
        const msg   = document.getElementById('upload-msg');
        const pid   = <?= (int)$producto['id'] ?>;

        if (!input || !btn) return;

        input.addEventListener('change', () => {
            grid.innerHTML = '';
            const files = Array.from(input.files || []);
            files.forEach(f => {
                const r = new FileReader();
                r.onload = e => {
                    const im = document.createElement('img');
                    im.src = e.target.result; im.alt = f.name; im.className = 'thumb';
                    grid.appendChild(im);
                };
                r.readAsDataURL(f);
            });
        });

        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            const files = Array.from(input.files || []);
            if (!files.length) return;

            btn.disabled = true;
            msg.textContent = 'Subiendo...';

            // Subimos de a uno reutilizando el endpoint existente
            let ok = 0, fail = 0;
            for (const f of files) {
                const fd = new FormData();
                fd.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
                fd.append('imagen', f); // subirImagen espera "imagen" singular

                try {
                    const res = await fetch('<?= site_url('productos') ?>/'+pid+'/imagenes/subir', { method: 'POST', body: fd });
                    if (res.ok) ok++; else fail++;
                } catch (err) {
                    console.error(err); fail++;
                }
            }
            msg.textContent = `Completado. Éxitos: ${ok}, Fallos: ${fail}`;
            // Recargar para ver cambios (o podríamos volver a leer por AJAX la galería)
            setTimeout(() => location.reload(), 800);
        });
    })();
</script>

<?= $this->endSection() ?>
