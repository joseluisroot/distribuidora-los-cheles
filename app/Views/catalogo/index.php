<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>

<h1 class="text-2xl font-bold mb-4">Catálogo</h1>

<form method="get" action="<?= site_url('catalogo') ?>" class="mb-4">
    <div class="grid md:grid-cols-4 gap-2">
        <input type="text" name="q" value="<?= esc($q ?? '') ?>" class="input md:col-span-2"
               placeholder="Buscar por nombre o SKU...">
        <select name="sort" class="input">
            <option value="recientes" <?= ($sort === 'recientes' ? 'selected' : '') ?>>Más recientes</option>
            <option value="precio_asc" <?= ($sort === 'precio_asc' ? 'selected' : '') ?>>Precio: menor a mayor</option>
            <option value="precio_desc" <?= ($sort === 'precio_desc' ? 'selected' : '') ?>>Precio: mayor a menor
            </option>
            <option value="nombre_asc" <?= ($sort === 'nombre_asc' ? 'selected' : '') ?>>Nombre A–Z</option>
            <option value="nombre_desc" <?= ($sort === 'nombre_desc' ? 'selected' : '') ?>>Nombre Z–A</option>
            <option value="stock_desc" <?= ($sort === 'stock_desc' ? 'selected' : '') ?>>Stock: mayor a menor</option>
        </select>
        <select name="perPage" class="input">
            <?php foreach ([6, 12, 18, 24, 36, 48] as $pp): ?>
                <option value="<?= $pp ?>" <?= ($perPage === $pp ? 'selected' : '') ?>><?= $pp ?>/pág</option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="mt-2">
        <button class="btn btn-outline">Aplicar</button>
    </div>
</form>

<?php if (empty($productos)): ?>
    <div class="card text-center">
        <p class="text-muted">No hay productos que coincidan con tu búsqueda.</p>
    </div>
<?php else: ?>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php foreach ($productos as $p):
            $stock = (int)($p['stock'] ?? 0);
            $agotado = $stock <= 0;

            $precio1 = (float)$p['precio_q1'];
            $precio3 = (float)$p['precio_q3'];
            $precio10 = (float)$p['precio_q10'];
            ?>

            <div class="card">
                <?php
                // carga una sola imagen principal si existe (evitar N+1 en producción con un join cacheado más adelante)
                $imgModel = new \App\Models\ProductImageModel();
                $img = $imgModel->where('producto_id', (int)$p['id'])->orderBy('is_primary', 'DESC')->orderBy('sort_order', 'ASC')->first();
                $cardImg = $img['thumb_path'] ?? $img['path'] ?? ($p['imagen_url'] ?? base_url('assets/placeholder-product.png'));
                ?>
                <div class="mb-2 -mt-2 -mx-2">
                    <img src="<?= is_string($cardImg) && strpos($cardImg, 'http') === 0 ? $cardImg : base_url($cardImg) ?>"
                         alt="<?= esc($p['nombre']) ?>" class="w-full h-40 object-cover rounded-lg" loading="lazy">
                </div>

                <div class="flex items-start justify-between">
                    <div class="font-semibold leading-tight"><?= esc($p['nombre']) ?></div>
                    <span class="badge <?= $agotado ? 'bg-red-100 text-red-700' : '' ?>">
          <?= $agotado ? 'Agotado' : 'Stock: ' . $stock ?>
        </span>
                </div>
                <div class="text-xs text-slate-500 mt-1">SKU: <?= esc($p['sku']) ?></div>

                <?php if (!empty($p['descripcion'])): ?>
                    <p class="mt-2 text-sm text-slate-600 line-clamp-2"><?= esc($p['descripcion']) ?></p>
                <?php endif; ?>

                <div class="mt-3 space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="text-xl font-extrabold">$<?= number_format($precio1, 2) ?></span>
                        <span class="text-xs text-slate-500">x1</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="font-bold">$<?= number_format($precio3, 2) ?></span>
                        <span class="text-xs text-slate-500">x3 (escala)</span>
                        <?php if ($precio3 < $precio1): ?>
                            <span class="badge">ahorra <?= number_format(100 - ($precio3 / $precio1 * 100), 0) ?>%</span>
                        <?php endif; ?>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="font-bold">$<?= number_format($precio10, 2) ?></span>
                        <span class="text-xs text-slate-500">x10 (mayorista)</span>
                        <?php if ($precio10 < $precio1): ?>
                            <span class="badge">ahorra <?= number_format(100 - ($precio10 / $precio1 * 100), 0) ?>%</span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Acciones: POST + CSRF -->
                <form method="post" action="<?= site_url('carretilla/agregar') ?>" class="mt-3 grid grid-cols-3 gap-2">
                    <?= csrf_field() ?>
                    <input type="hidden" name="producto_id" value="<?= (int)$p['id'] ?>">

                    <button name="cant" value="1"
                            class="btn btn-outline <?= $agotado ? 'pointer-events-none opacity-50' : '' ?>" <?= $agotado ? 'disabled' : '' ?>
                            title="Precio x1: $<?= number_format($precio1, 2) ?>">+1
                    </button>

                    <button name="cant" value="3"
                            class="btn btn-outline <?= $agotado ? 'pointer-events-none opacity-50' : '' ?>" <?= $agotado ? 'disabled' : '' ?>
                            title="Precio x3 (escala): $<?= number_format($precio3, 2) ?>">+3
                    </button>

                    <button name="cant" value="10"
                            class="btn btn-outline <?= $agotado ? 'pointer-events-none opacity-50' : '' ?>" <?= $agotado ? 'disabled' : '' ?>
                            title="Precio x10 (mayorista): $<?= number_format($precio10, 2) ?>">+10
                    </button>
                </form>
                <button type="button" class="btn btn-outline" onclick="quickView(<?= (int)$p['id'] ?>)">Vista rápida
                </button>

            </div>
        <?php endforeach; ?>
    </div>


    <!-- Paginación -->
    <div class="mt-6 flex justify-center">
        <nav class="inline-flex items-center text-sm">
            <?= $pager->links('catalogo', 'tailwind', ['query' => $query]) ?>
        </nav>
    </div>
<?php endif; ?>

<!-- Modal Vista Rápida -->
<div id="modal-quickview" class="fixed inset-0 z-50 hidden">
    <!-- overlay -->
    <div class="absolute inset-0 bg-black/40" onclick="closeModal('modal-quickview')"></div>

    <!-- contenedor -->
    <div class="absolute inset-x-4 top-6 md:inset-x-auto md:left-1/2 md:-translate-x-1/2 md:w-[760px]">
        <div class="card relative p-0 overflow-hidden">

            <!-- botón cerrar -->
            <button class="absolute top-3 right-3 z-10 text-slate-500 hover:text-slate-700"
                    aria-label="Cerrar" onclick="closeModal('modal-quickview')">✕</button>

            <!-- bloque imagen principal (más grande) -->
            <div class="p-4 pb-2">
                <img id="qv-img"
                     src="<?= base_url('assets/placeholder-product.png') ?>"
                     alt=""
                     class="w-full h-64 md:h-80 object-cover rounded-xl shadow-sm">
            </div>

            <!-- tira de miniaturas (más pequeñas) -->
            <div class="px-4 pb-2">
                <div id="qv-thumbs" class="flex gap-2 overflow-x-auto">
                    <!-- se llena por JS: buttons 64x64 -->
                    <!-- ejemplo de template de item (lo genera tu JS):
                    <button type="button" class="border rounded-lg overflow-hidden">
                      <img src="..." class="w-16 h-16 object-cover" alt="">
                    </button>
                    -->
                </div>
            </div>

            <!-- información del producto -->
            <div class="p-6 pt-4">
                <div class="text-xs text-slate-500" id="qv-sku"></div>
                <h2 class="text-xl font-bold" id="qv-nombre">Producto</h2>
                <p class="text-sm text-slate-600 mt-1" id="qv-desc"></p>

                <!-- precios -->
                <div class="mt-3 space-y-1">
                    <div class="flex items-center gap-2">
                        <span id="qv-p1" class="text-xl font-extrabold">$0.00</span>
                        <span class="text-xs text-slate-500">x1</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span id="qv-p3" class="font-bold">$0.00</span>
                        <span class="text-xs text-slate-500">x3 (escala)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span id="qv-p10" class="font-bold">$0.00</span>
                        <span class="text-xs text-slate-500">x10 (mayorista)</span>
                    </div>
                </div>

                <!-- acciones -->
                <form method="post" action="<?= site_url('carretilla/agregar') ?>" class="mt-4 flex flex-wrap items-center gap-2">
                    <?= csrf_field() ?>
                    <input type="hidden" name="producto_id" id="qv-producto-id" value="">
                    <input type="number" min="1" step="1" value="1" name="cant" id="qv-cant" class="input w-24" aria-label="Cantidad" >
                    <button class="btn btn-primary" id="qv-add-btn">Agregar a carretilla</button>
                    <a id="qv-ver" href="#" class="btn btn-outline">Ver detalle</a>
                </form>

                <div class="mt-2 text-xs" id="qv-stock"></div>
            </div>

        </div>
    </div>
</div>


<script>
    async function quickView(id) {
        try {

            const res = await fetch('<?= site_url('catalogo/json/') ?>' + id, {headers: {'Accept': 'application/json'}});

            if (!res.ok) throw new Error('No encontrado');
            const p = await res.json();

            document.getElementById('qv-producto-id').value = p.id;
            document.getElementById('qv-nombre').textContent = p.nombre || '';
            document.getElementById('qv-sku').textContent = 'SKU: ' + (p.sku || '');
            document.getElementById('qv-desc').textContent = p.descripcion || '';
            document.getElementById('qv-img').src = p.imagen_url || '<?= base_url('assets/placeholder-product.png') ?>';
            document.getElementById('qv-p1').textContent = '$' + Number(p.precio_q1).toFixed(2);
            document.getElementById('qv-p3').textContent = '$' + Number(p.precio_q3).toFixed(2);
            document.getElementById('qv-p10').textContent = '$' + Number(p.precio_q10).toFixed(2);
            document.getElementById('qv-ver').href = p.url || '#';
            document.getElementById('qv-stock').textContent = (p.stock > 0) ? ('Stock: ' + p.stock) : 'Agotado';


            const addBtn = document.getElementById('qv-add-btn');
            const stockTxt = document.getElementById('qv-stock');
            const cantInput = document.getElementById('qv-cant');

            const agotado = !(p.stock > 0);
            stockTxt.textContent = agotado ? 'Agotado' : ('Stock: ' + p.stock);

            // Toggle accesible
            addBtn.disabled = agotado;
            addBtn.classList.toggle('is-disabled', agotado); // aplica estilo si agregaste el CSS
            addBtn.classList.toggle('btn-primary', !agotado);
            addBtn.classList.toggle('btn-outline', agotado); // opcional: cambia a “outline” cuando no hay stock
            addBtn.title = agotado ? 'Sin existencias' : 'Agregar a carretilla';

            // Toggle para input cantidad
            cantInput.disabled = agotado;
            cantInput.classList.toggle('opacity-60', agotado);
            cantInput.classList.toggle('cursor-not-allowed', agotado);
            cantInput.max = p.stock > 0 ? p.stock : 1;
            cantInput.title = agotado ? 'Sin existencias' : 'Cantidad a agregar';


            const thumbs = document.getElementById('qv-thumbs');
            thumbs.innerHTML = '';
            if (Array.isArray(p.images) && p.images.length) {
                // main src = primary o el primero
                const main = p.images.find(i => i.primary) || p.images[0];
                document.getElementById('qv-img').src = main.src;
                p.images.forEach(im => {
                    const b = document.createElement('button');
                    b.type = 'button';
                    b.className = 'border rounded-lg overflow-hidden';
                    b.innerHTML = `<img src="${im.thumb}" alt="${im.alt || ''}" class="w-16 h-16 object-cover">`;
                    b.onclick = () => {
                        document.getElementById('qv-img').src = im.src;
                    };
                    thumbs.appendChild(b);
                });
            }


            openModal('modal-quickview');
            trapFocus('modal-quickview');
        } catch (e) {
            console.log(e);
            alert('No se pudo cargar la vista rápida.');
        }
    }

    // Focus trap mínimo (accesibilidad)
    function trapFocus(id) {
        const m = document.getElementById(id);
        const focusables = m.querySelectorAll('a,button,input,select,textarea,[tabindex]:not([tabindex="-1"])');
        if (!focusables.length) return;
        let first = focusables[0], last = focusables[focusables.length - 1];
        first.focus();

        function handler(e) {
            if (e.key === 'Escape') {
                closeModal(id);
                m.removeEventListener('keydown', handler);
            }
            if (e.key === 'Tab') {
                if (e.shiftKey && document.activeElement === first) {
                    e.preventDefault();
                    last.focus();
                } else if (!e.shiftKey && document.activeElement === last) {
                    e.preventDefault();
                    first.focus();
                }
            }
        }

        m.addEventListener('keydown', handler);
    }
</script>

<?= $this->endSection() ?>
