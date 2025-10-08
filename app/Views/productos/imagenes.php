<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>

<h1 class="text-2xl font-bold mb-4">Imágenes — <?= esc($producto['nombre']) ?></h1>

<div class="card p-4 mb-4">
    <form method="post" enctype="multipart/form-data" action="<?= site_url('productos/'.$producto['id'].'/imagenes/subir') ?>" class="flex items-center gap-2">
        <?= csrf_field() ?>
        <input type="file" name="imagen" accept="image/*" class="input" required>
        <button class="btn btn-primary">Subir</button>
    </form>
    <p class="text-xs text-slate-500 mt-2">JPG/PNG/WEBP · Máx 4MB. Se crea miniatura 160x160.</p>
</div>

<?php if (empty($imagenes)): ?>
    <div class="card">Aún no hay imágenes.</div>
<?php else: ?>
    <div class="grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        <?php foreach ($imagenes as $img): ?>
            <div class="card p-3">
                <img src="<?= base_url($img['thumb_path'] ?: $img['path']) ?>" alt="<?= esc($img['alt'] ?? $producto['nombre']) ?>"
                     class="w-full h-36 object-cover rounded-lg">
                <div class="mt-2 flex items-center justify-between">
                    <span class="badge"><?= $img['is_primary'] ? 'Principal' : 'Secundaria' ?></span>
                    <span class="text-xs text-slate-500">#<?= (int)$img['sort_order'] ?></span>
                </div>
                <div class="mt-2 flex gap-2">
                    <form method="post" action="<?= site_url('productos/'.$producto['id'].'/imagenes/'.$img['id'].'/principal') ?>">
                        <?= csrf_field() ?>
                        <button class="btn btn-outline" <?= $img['is_primary']?'disabled':'' ?>>Hacer principal</button>
                    </form>
                    <form method="post" action="<?= site_url('productos/'.$producto['id'].'/imagenes/'.$img['id'].'/eliminar') ?>" onsubmit="return confirm('¿Eliminar imagen?')">
                        <?= csrf_field() ?>
                        <button class="btn btn-outline">Eliminar</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>
