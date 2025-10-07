<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>

<h1 class="text-2xl font-bold mb-4">Mi Carretilla</h1>

<?php if(!$items): ?>
    <div class="card">Tu carretilla está vacía. <a class="text-primary hover:underline" href="<?= site_url('catalogo') ?>">Ir al catálogo</a></div>
<?php else: ?>
    <div class="card overflow-x-auto mb-4">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-slate-600">
            <tr>
                <th class="p-3 text-left">Producto</th>
                <th class="p-3 text-right">Precio</th>
                <th class="p-3 text-right">Cantidad</th>
                <th class="p-3 text-right">Subtotal</th>
                <th class="p-3 text-right">Acciones</th>
            </tr>
            </thead>
            <tbody class="divide-y">
            <?php foreach($items as $it): ?>
                <tr class="hover:bg-slate-50">
                    <td class="p-3">
                        <div class="font-semibold"><?= esc($it['nombre']) ?></div>
                        <div class="text-xs text-slate-500"><?= esc($it['sku']) ?> · Stock: <?= $it['stock'] ?></div>
                    </td>
                    <td class="p-3 text-right">$<?= number_format($it['precio'],2) ?></td>
                    <td class="p-3 text-right">
                        <form method="post" action="<?= site_url('carretilla/update') ?>" class="inline-flex gap-2 items-center">
                            <?= csrf_field() ?>
                            <input type="hidden" name="producto_id" value="<?= $it['id'] ?>">
                            <input type="number" name="cantidad" value="<?= $it['cantidad'] ?>" min="1" class="input w-24">
                            <button class="btn btn-outline">Actualizar</button>
                        </form>
                    </td>
                    <td class="p-3 text-right">$<?= number_format($it['subtotal'],2) ?></td>
                    <td class="p-3 text-right">
                        <a class="btn btn-outline text-red-600" href="<?= site_url('carretilla/remove/'.$it['id']) ?>">Quitar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="flex items-center justify-between">
        <a class="btn btn-outline" href="<?= site_url('carretilla/clear') ?>">Vaciar</a>
        <div class="text-right">
            <div class="text-sm text-slate-500">Total</div>
            <div class="text-2xl font-extrabold">$<?= number_format($total,2) ?></div>
            <a class="btn btn-primary mt-2" href="<?= site_url('carretilla/checkout') ?>">Continuar</a>
        </div>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>
