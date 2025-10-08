<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>

<?php
$absUrl = function (?string $path) {
    if (!$path) return null;
    return (preg_match('~^https?://~i', $path)) ? $path : base_url($path);
};
// Construye el array de imágenes para JS (full-size)
$imagesForJs = [];
if (!empty($imagenes)) {
    foreach ($imagenes as $im) {
        $imagesForJs[] = [
                'src'     => $absUrl($im['path']),
                'alt'     => $im['alt'] ?? $producto['nombre'],
                'primary' => (int)$im['is_primary'] === 1,
        ];
    }
} else {
    // fallback: imagen_url del producto o placeholder
    $imagesForJs[] = [
            'src' => $absUrl($producto['imagen_url'] ?? null) ?: base_url('assets/placeholder-product.png'),
            'alt' => $producto['nombre'],
            'primary' => true,
    ];
}
?>


<a href="<?= site_url('catalogo') ?>" class="text-sm text-primary hover:underline">&larr; Volver al catálogo</a>

<div class="grid md:grid-cols-2 gap-6 mt-3">
    <div class="card p-4">
        <?php
        $imgs = (new \App\Models\ProductImageModel())->byProducto((int)$producto['id']);
        $primary = $imgs[0]['path'] ?? ($producto['imagen_url'] ?? base_url('assets/placeholder-product.png'));
        ?>
        <div class="relative overflow-hidden rounded-lg">
            <img id="gal-main" src="<?= $primary ?>" alt="<?= esc($producto['nombre']) ?>"
                 class="w-full h-72 object-cover transition-transform duration-200 hover:scale-[1.05]">
            <!--<img id="gal-main" src="<?php /*= base_url($primary) */?>" alt="<?php /*= esc($producto['nombre']) */?>"
                 class="w-full h-72 object-cover transition-transform duration-200 hover:scale-[1.05]">-->
        </div>

        <?php if (!empty($imgs)): ?>
            <div class="mt-3 grid grid-cols-5 gap-2">
                <?php foreach ($imgs as $gi): ?>
                    <button type="button" class="border rounded-lg overflow-hidden hover:ring-2 hover:ring-primary"
                            onclick="document.getElementById('gal-main').src='<?= $gi['path'] //base_url($gi['path']) ?>'">
                        <img src="<?= $gi['thumb_path'] ?: $gi['path'] ?>" alt="<?= esc($gi['alt'] ?? $producto['nombre']) ?>"
                             class="w-full h-16 object-cover">
                        <!--<img src="<?php /*= base_url($gi['thumb_path'] ?: $gi['path']) */?>" alt="<?php /*= esc($gi['alt'] ?? $producto['nombre']) */?>"
                             class="w-full h-16 object-cover">-->
                    </button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>


    <div>
        <div class="text-xs text-slate-500">SKU: <?= esc($producto['sku']) ?></div>
        <h1 class="text-2xl font-bold"><?= esc($producto['nombre']) ?></h1>
        <p class="mt-2 text-slate-700"><?= nl2br(esc($producto['descripcion'] ?? '')) ?></p>

        <div class="mt-4">
            <span class="text-2xl font-extrabold">$<?= number_format((float)$producto['precio_base'],2) ?></span>
            <span class="text-xs ml-2 text-slate-500">Precio base</span>
        </div>

        <?php if (!empty($escalas)): ?>
            <div class="mt-3">
                <h3 class="text-sm font-semibold mb-1">Escalas de precio</h3>
                <div class="overflow-x-auto">
                    <table class="text-sm min-w-full">
                        <thead><tr class="text-slate-500">
                            <th class="py-2 text-left">Desde</th>
                            <th class="py-2 text-left">Precio unit.</th>
                        </tr></thead>
                        <tbody>
                        <?php foreach ($escalas as $e): ?>
                            <tr class="border-t">
                                <td class="py-2">x<?= (int)$e['min_cantidad'] ?></td>
                                <td class="py-2">$<?= number_format((float)$e['precio'],2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= site_url('carretilla/agregar') ?>" class="mt-4 flex items-center gap-2">
            <?= csrf_field() ?>
            <input type="hidden" name="producto_id" value="<?= (int)$producto['id'] ?>">
            <input type="number" min="1" step="1" value="1" name="cant" class="input w-28" aria-label="Cantidad">
            <button class="btn btn-primary">Agregar a carretilla</button>
            <span class="text-sm ml-2 <?= ((int)($producto['stock'] ?? 0) <= 0) ? 'text-red-600' : 'text-slate-500' ?>">
        <?= ((int)($producto['stock'] ?? 0) <= 0) ? 'Agotado' : 'Stock: '.(int)$producto['stock'] ?>
      </span>
        </form>
    </div>
</div>

<!-- Lightbox -->
<div id="lightbox" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/70" onclick="closeModal('lightbox')"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="relative w-full max-w-4xl">
            <button class="absolute -top-10 right-0 text-white text-2xl" aria-label="Cerrar" onclick="closeModal('lightbox')">✕</button>
            <img id="lb-img" src="" alt="" class="w-full max-h-[80vh] object-contain rounded-md shadow-lg bg-black/20">
            <div class="absolute inset-y-0 left-0 flex items-center">
                <button id="lb-prev" class="text-white text-3xl px-3" aria-label="Anterior">‹</button>
            </div>
            <div class="absolute inset-y-0 right-0 flex items-center">
                <button id="lb-next" class="text-white text-3xl px-3" aria-label="Siguiente">›</button>
            </div>
        </div>
    </div>
</div>

<script>
    (function(){
        const images = <?= json_encode($imagesForJs, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) ?>;
        let idx = Math.max(0, images.findIndex(i => i.primary)) || 0;

        const elMain = document.getElementById('gal-main');
        const lbImg  = document.getElementById('lb-img');
        const prev   = document.getElementById('lb-prev');
        const next   = document.getElementById('lb-next');
        const modalId= 'lightbox';

        function show(i){
            if (!images.length) return;
            idx = (i + images.length) % images.length;
            lbImg.src = images[idx].src;
            lbImg.alt = images[idx].alt || '';
        }

        // Abrir al hacer click en la imagen principal
        if (elMain) {
            elMain.style.cursor = 'zoom-in';
            elMain.addEventListener('click', () => {
                show(idx);
                openModal(modalId);
            });
        }

        // Navegación
        prev?.addEventListener('click', () => show(idx - 1));
        next?.addEventListener('click', () => show(idx + 1));

        // Teclado (← → Esc)
        document.addEventListener('keydown', (e) => {
            const visible = !document.getElementById(modalId).classList.contains('hidden');
            if (!visible) return;
            if (e.key === 'ArrowLeft')  show(idx - 1);
            if (e.key === 'ArrowRight') show(idx + 1);
            if (e.key === 'Escape')     closeModal(modalId);
        });

        // Si cambias imagen con thumbnails, actualiza idx
        window.__chelesSetMainFromThumb = function(url){
            const i = images.findIndex(im => im.src === url);
            if (i >= 0) idx = i;
            const main = document.getElementById('gal-main');
            if (main) { main.src = url; }
        };
    })();
</script>


<?= $this->endSection() ?>
