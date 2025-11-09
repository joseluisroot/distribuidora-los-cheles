<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>

<h1 class="text-2xl font-bold mb-4">Importar productos (CSV)</h1>

<?php if ($m = session('message')): ?>
    <div class="mb-3 p-3 rounded border border-green-200 bg-green-50 text-green-800"><?= esc($m) ?></div>
<?php endif; ?>
<?php if ($e = session('error')): ?>
    <div class="mb-3 p-3 rounded border border-red-200 bg-red-50 text-red-800"><?= esc($e) ?></div>
<?php endif; ?>
<?php if ($errs = session('errors')): ?>
    <div class="mb-3 p-3 rounded border border-amber-200 bg-amber-50 text-amber-800">
        <div class="font-semibold mb-1">Errores:</div>
        <ul class="list-disc list-inside text-sm">
            <?php foreach ($errs as $x): ?><li><?= esc($x) ?></li><?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="card p-4 space-y-4">
    <p class="text-sm text-slate-600">
        Descarga la <a href="<?= site_url('productos/importar/plantilla') ?>" class="link">plantilla CSV</a>.
        Columnas requeridas: <code class="font-mono">sku,nombre,precio_base,stock</code>.
        Opcionales: <code class="font-mono">descripcion,is_activo</code> (0 o 1, por defecto 1).
    </p>

    <form method="post" action="<?= site_url('productos/importar/previsualizar') ?>" enctype="multipart/form-data" class="space-y-3">
        <?= csrf_field() ?>
        <div>
            <label class="block mb-1 font-medium">Archivo CSV</label>
            <input type="file" name="csv" accept=".csv,text/csv" class="input w-full" required>
            <p class="text-xs text-slate-500 mt-1">Delimitador coma (,) o punto y coma (;). Codificación UTF-8 recomendada.</p>
        </div>

        <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="update_existing" value="1">
            <span>Actualizar productos existentes por SKU</span>
        </label>

        <div class="flex gap-2">
            <button class="btn btn-primary">Previsualizar</button>
            <a href="<?= site_url('productos') ?>" class="btn btn-outline">Cancelar</a>
        </div>
    </form>
</div>

<?= $this->endSection() ?>
