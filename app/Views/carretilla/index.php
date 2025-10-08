<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<h1 class="text-2xl font-bold mb-4">Mi Carretilla</h1>

<?php if (empty($items)): ?>
    <div class="card">Tu carretilla está vacía.</div>
<?php else: ?>
    <div class="card">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                <tr class="text-left text-slate-500">
                    <th class="py-2">Producto</th>
                    <th class="py-2">SKU</th>
                    <th class="py-2">Precio (aplicado)</th>
                    <th class="py-2">Cant</th>
                    <th class="py-2">Subtotal</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $it): ?>
                    <tr class="border-t">
                        <td class="py-2">
                            <div class="font-semibold"><?= esc($it['nombre']) ?></div>
                            <?php if ($it['ahorroPct'] > 0): ?>
                                <div class="text-xs text-green-700">Ahorro escala: <?= $it['ahorroPct'] ?>%</div>
                            <?php endif; ?>
                        </td>
                        <td class="py-2 text-slate-500"><?= esc($it['sku']) ?></td>
                        <td class="py-2">
                            $<?= number_format($it['precio'],2) ?>
                            <?php if ($it['ahorroPct'] > 0): ?>
                                <span class="text-xs line-through ml-1 text-slate-400">$<?= number_format($it['precioBase'],2) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="py-2"><?= (int)$it['qty'] ?></td>
                        <td class="py-2 font-semibold">$<?= number_format($it['sub'],2) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="mt-4 text-right">
            <span class="text-lg font-bold">Total: $<?= number_format($total,2) ?></span>
        </div>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>
