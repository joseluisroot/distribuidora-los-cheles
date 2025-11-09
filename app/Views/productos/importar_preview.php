<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>

<h1 class="text-2xl font-bold mb-4">Previsualización de importación</h1>

<?php if (!empty($csvErrors)): ?>
    <div class="mb-4 p-3 rounded border border-red-200 bg-red-50 text-red-800">
        <div class="font-semibold mb-1">Errores de CSV:</div>
        <ul class="list-disc list-inside text-sm">
            <?php foreach ($csvErrors as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="card p-4">
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
            <tr class="text-slate-500">
                <th class="py-2 text-left">Línea</th>
                <th class="py-2 text-left">SKU</th>
                <th class="py-2 text-left">Nombre</th>
                <th class="py-2 text-left">Precio</th>
                <th class="py-2 text-left">Stock</th>
                <th class="py-2 text-left">Activo</th>
                <th class="py-2 text-left">Estado</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($results as $r): ?>
                <?php
                $d = $r['data'];
                $state = $r['ok'] ? 'OK' : ('Error: '.implode('; ', $r['errors']));
                ?>
                <tr class="border-t <?= $r['ok'] ? '' : 'bg-red-50' ?>">
                    <td class="py-2"><?= (int)$r['line'] ?></td>
                    <td class="py-2"><?= esc($d['sku']) ?></td>
                    <td class="py-2"><?= esc($d['nombre']) ?></td>
                    <td class="py-2">$<?= number_format((float)$d['precio_base'],2) ?></td>
                    <td class="py-2"><?= (int)$d['stock'] ?></td>
                    <td class="py-2"><?= (int)$d['is_activo'] ?></td>
                    <td class="py-2"><?= esc($state) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <form method="post" action="<?= site_url('productos/importar/procesar') ?>" class="mt-4">
        <?= csrf_field() ?>
        <input type="hidden" name="payload" value="<?= esc($payloadJson) ?>">
        <div class="flex items-center gap-2">
            <button class="btn btn-primary" <?= empty(array_filter($results, fn($x)=>$x['ok'])) ? 'disabled' : '' ?>>
                Confirmar importación
            </button>
            <a href="<?= site_url('productos/importar') ?>" class="btn btn-outline">Regresar</a>
        </div>
        <p class="text-xs text-slate-500 mt-2">
            <?= $updateExisting ? 'Se actualizarán productos existentes por SKU.' : 'Los productos existentes por SKU se omitirán.' ?>
        </p>
    </form>
</div>

<?= $this->endSection() ?>
