<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>

<h1 class="text-2xl font-bold mb-4">Confirmar Pedido</h1>

<?php if($errores): ?>
    <div class="alert alert-error mb-4">
        <?= implode('<br>', array_map('esc', $errores)) ?>
    </div>
<?php endif; ?>

<div class="card overflow-x-auto mb-4">
    <table class="min-w-full text-sm">
        <thead class="bg-slate-50">
        <tr>
            <th class="p-3 text-left">Producto</th>
            <th class="p-3 text-right">Precio</th>
            <th class="p-3 text-right">Cantidad</th>
            <th class="p-3 text-right">Subtotal</th>
        </tr>
        </thead>
        <tbody class="divide-y">
        <?php foreach($items as $it): ?>
            <tr>
                <td class="p-3"><?= esc($it['nombre']) ?></td>
                <td class="p-3 text-right">$<?= number_format($it['precio'],2) ?></td>
                <td class="p-3 text-right"><?= $it['cantidad'] ?></td>
                <td class="p-3 text-right">$<?= number_format($it['subtotal'],2) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="flex items-center justify-between">
    <a class="btn btn-outline" href="<?= site_url('carretilla') ?>">Regresar</a>
    <div class="text-right">
        <div class="text-sm text-slate-500">Total</div>
        <div class="text-2xl font-extrabold">$<?= number_format($total,2) ?></div>

        <form method="post" action="<?= site_url('carretilla/place-order') ?>" class="mt-3 space-y-2">
            <?= csrf_field() ?>
            <textarea name="observaciones" class="input" placeholder="Instrucciones u observaciones (opcional)"></textarea>
            <button class="btn btn-primary" <?= $errores ? 'disabled' : '' ?>>Confirmar pedido</button>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
